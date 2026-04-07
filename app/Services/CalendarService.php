<?php

namespace App\Services;

use App\Models\CalendarMinyan;
use App\Models\CalendarSetting;
use Illuminate\Database\Eloquent\Collection;

class CalendarService
{
    protected float $lat;
    protected float $lng;
    protected string $timezone;
    protected int $candleLightingOffset;   // minutes before sunset
    protected int $havdalaOffset;          // minutes after sunset
    protected int $fastEndsMinorOffset;    // minutes after sunset for minor fasts
    protected int $fastEndsTishabavOffset; // minutes after sunset for Tisha B'Av
    protected string $schoolYearStart;
    protected string $schoolYearEnd;
    protected string $erevYkMinchaDefault;
    protected string $erevYkMinchaEarly;
    protected string $erevYkMinchaThreshold;

    public function __construct()
    {
        $this->lat                    = (float) CalendarSetting::get('lat', 35.11666);
        $this->lng                    = (float) CalendarSetting::get('lng', -89.87740);
        $this->timezone               = CalendarSetting::get('timezone', 'America/Chicago');
        $this->candleLightingOffset   = (int)   CalendarSetting::get('candle_lighting_offset', 18);
        $this->havdalaOffset          = (int)   CalendarSetting::get('havdala_offset', 50);
        $this->fastEndsMinorOffset    = (int)   CalendarSetting::get('fast_ends_minor_offset', 42);
        $this->fastEndsTishabavOffset = (int)   CalendarSetting::get('fast_ends_tishabav_offset', 50);
        $this->schoolYearStart        = CalendarSetting::get('school_year_start', 'third wednesday of August');
        $this->schoolYearEnd          = CalendarSetting::get('school_year_end', 'third wednesday of June');
        $this->erevYkMinchaDefault    = CalendarSetting::get('erev_yk_mincha_default', '4:30');
        $this->erevYkMinchaEarly      = CalendarSetting::get('erev_yk_mincha_early', '4:15');
        $this->erevYkMinchaThreshold  = CalendarSetting::get('erev_yk_mincha_threshold', '6:20');
    }

    // =========================================================================
    // Solar calculations (ported from calendar-year.php)
    // =========================================================================

    public function getSunset(int $timestamp, int $offsetSeconds = 0): int
    {
        $raw = @date_sunset(
            $timestamp,
            SUNFUNCS_RET_TIMESTAMP,
            $this->lat,
            $this->lng,
            90 + (2 / 6)
        );
        return (60 * round($raw / 60)) + $offsetSeconds;
    }

    public function getSunrise(int $timestamp, int $offsetSeconds = 0): int
    {
        return @date_sunrise(
            $timestamp,
            SUNFUNCS_RET_TIMESTAMP,
            $this->lat,
            $this->lng,
            90 + (3.5 / 6)
        ) + $offsetSeconds;
    }

    public function getAlos(int $timestamp, int $offsetSeconds = 0): int
    {
        return (60 * round(
            @date_sunrise($timestamp, SUNFUNCS_RET_TIMESTAMP, $this->lat, $this->lng, 90 + 16.1) / 60
        )) + $offsetSeconds;
    }

    /**
     * Get the time corresponding to a seasonal "hour" of the day.
     * Hour 0 = sunrise, Hour 12 = sunset.
     * Fractional hours accepted (e.g. 6.5 = halfway between chatzos and sunset).
     */
    public function getByHour(int $timestamp, float $hour): int
    {
        $sunrise          = $this->getSunrise($timestamp);
        $sunset           = $this->getSunset($timestamp);
        $totalSeconds     = $sunset - $sunrise;
        $lengthEachHour   = $totalSeconds / 12;
        return (int) ($sunrise + ($hour * $lengthEachHour));
    }

    // =========================================================================
    // Main calendar generator
    // =========================================================================

    /**
     * Generate calendar data for a school year.
     * calYear = the first year (e.g. 2025 → September 2025 – August 2026).
     * Returns array of month-blocks: ['month' => 'September', 'year' => 2025, 'days' => [...]]
     */
    public function generateYear(int $calYear): array
    {
        date_default_timezone_set($this->timezone);

        $minyanim = CalendarMinyan::where('active', true)->orderBy('sort_order')->get();

        // ── Fetch Hebcal data for both years of the school year ──
        /** @var HebcalService $hebcal */
        $hebcal     = app(HebcalService::class);
        $items1     = $hebcal->getForYear($calYear);
        $items2     = $hebcal->getForYear($calYear + 1);
        $allItems   = array_merge($items1, $items2);

        // Build holiday date maps keyed by [year][title] = date string
        $jholidays = [];
        foreach ($allItems as $event) {
            if (($event['category'] ?? '') === 'holiday') {
                $eYear = (int) date('Y', strtotime($event['date']));
                $jholidays[$eYear][$event['title']] = $event['date'];
            }
        }

        // School-year months: September calYear through August calYear+1
        $months = [];
        foreach (['September', 'October', 'November', 'December'] as $m) {
            $months[$m] = $calYear;
        }
        foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August'] as $m) {
            $months[$m] = $calYear + 1;
        }

        // ── Build $info array (same as original script) ──
        $info = [];
        $info['holidays'] = $jholidays;

        $schoolStarts = strtotime($this->schoolYearStart . ' ' . $calYear);
        $schoolEnds   = strtotime($this->schoolYearEnd   . ' ' . ($calYear + 1)) + (60 * 60 * 24);

        foreach ($months as $calMonth => $yr) {
            $monthStart = strtotime($calMonth . ' 1 ' . $yr);
            $lastSat    = strtotime('last saturday of this month', $monthStart);
            if (date('Y-m-d', $lastSat) === date('Y-m-t', $monthStart)) {
                $lastDayInMonth = date('Y-m-d', $lastSat);
            } else {
                $lastDayInMonth = date('Y-m-d', strtotime('+7 days', $lastSat));
            }

            for ($day = strtotime($calMonth . ' 1 ' . $yr . ' 9 AM');
                 date('Y-m-d', $day) <= $lastDayInMonth;
                 $day += 86400) {

                $date = date('Y-m-d', $day);
                $info[$date]['display_date']  = date('l F j, Y', $day);
                $info[$date]['hebrew_date']   = $this->getHebrewDate($day);
                $info[$date]['sunset']        = date('g:i', $this->getSunset($day));
                $info[$date]['sunrise']       = date('g:i', $this->getSunrise($day));
                $info[$date]['_month']        = $calMonth;
                $info[$date]['_year']         = $yr;
                $info[$date]['_timestamp']    = $day;

                $dow = (int) date('w', $day); // 0=Sun … 6=Sat

                if (date('D', $day) === 'Fri') {
                    $info = $this->erevChag($info, $date);
                    $info[$date]['shacharis'] = '6:35';
                    if ($day < $schoolStarts && $day > $schoolEnds) {
                        $info[$date]['shacharis'] = '6:35, 8';
                    }

                    // Compute mincha for this Shabbos week (Fri–Thu of next week)
                    $minSunsetNext = min(
                        strtotime('-1 day', $this->getSunset(strtotime('+1 day', $day))),
                        strtotime('-5 days', $this->getSunset(strtotime('+5 days', $day)))
                    );
                    $minchaThisWeek = (5 * 60) * round((strtotime('-13 minutes', $minSunsetNext) / (5 * 60)));
                    if ($minchaThisWeek > ($minSunsetNext - (60 * 13))) {
                        $minchaThisWeek -= 5 * 60;
                    }
                    $minchaThisWeekStr = date('g:i', $minchaThisWeek);

                    $dayTs = strtotime('+1 day', $day);
                    for ($dx = 0; $dx < 5; $dx++) {
                        $dayTs = strtotime('+1 day', $dayTs);
                        $info[date('Y-m-d', $dayTs)]['mincha'] = $minchaThisWeekStr;
                    }

                    // Compute mincha for previous week (Sat–Wed)
                    $minSunsetPrev = min(
                        strtotime('+1 day', $this->getSunset(strtotime('-1 day', $day))),
                        strtotime('+5 days', $this->getSunset(strtotime('-5 days', $day)))
                    );
                    $minchaLastWeek = (5 * 60) * round((strtotime('-13 minutes', $minSunsetPrev) / (5 * 60)));
                    if ($minchaLastWeek > ($minSunsetPrev - (60 * 13))) {
                        $minchaLastWeek -= 5 * 60;
                    }
                    $minchaLastWeekStr = date('g:i', $minchaLastWeek);

                    $dayTs = strtotime('-6 days', $day);
                    for ($dx = 0; $dx < 5; $dx++) {
                        $dayTs = $dayTs + 86400;
                        $info[date('Y-m-d', $dayTs)]['mincha'] = $minchaLastWeekStr;
                    }

                } elseif (date('D', $day) === 'Sat') {
                    $sunset         = $this->getSunset($day);
                    $shabbosMincha  = (5 * 60) * round(strtotime('-30 minutes', $sunset) / (5 * 60));
                    $shabbosMinchaStr = date('g:i', $shabbosMincha);

                    $info[$date]['display_date'] = 'Shabbos ' . date('F j, Y', $day);

                    // Shacharis: based on 3rd seasonal hour
                    if ((int) date('Gi', $this->getByHour($day, 3)) > 920) {
                        $info[$date]['shacharis'] = '7:45, 8:45';
                    } else {
                        $shabShach = date('g:i', (5 * 60) * round(
                            ($this->getByHour($day, 3) - (35 * 60)) / (5 * 60)
                        ));
                        $info[$date]['shacharis'] = '7:45, ' . $shabShach;
                    }

                    $info[$date]['mincha'] = $shabbosMinchaStr;
                    if ($shabbosMinchaStr > '7:00') {
                        $info[$date]['mincha'] = '3, ' . $shabbosMinchaStr;
                    }
                    $info[$date]['havdala'] = date('g:i', strtotime('+' . $this->havdalaOffset . ' minutes', $sunset));

                } else {
                    // Weekday
                    $sunset = $this->getSunset($day);
                    $info[$date]['maariv']   = date('g:i', $sunset);
                    $info[$date]['shacharis'] = '6:35';
                    if (date('D', $day) === 'Sun') {
                        $info[$date]['shacharis'] = '8:00';
                    } elseif ($day < $schoolStarts && $day > $schoolEnds) {
                        $info[$date]['shacharis'] = '6:35, 8';
                    }
                }
            }
        }

        // ── Process Hebcal events ──
        $selichotStartA = null;
        $selichotEndA   = null;
        $selichotStartB = null;
        $selichotEndB   = null;
        $sefiraStart    = null;

        foreach ($allItems as $event) {
            $evDate     = $event['date'];
            $evTitle    = $event['title'];
            $evCategory = $event['category'] ?? '';

            if (!isset($info[$evDate]) || !isset($info[$evDate]['display_date'])) {
                continue;
            }

            // ── Parsha ──
            if ($evCategory === 'parashat') {
                $parsha = $evTitle;
                if ($parsha === 'Parshas Sazria')          { $parsha = 'Parshas Tazria'; }
                if ($parsha === 'Parshas Sazria-Metzora')  { $parsha = 'Parshas Tazria-Metzora'; }
                $info[$evDate]['parsha'] = $parsha;

                // Vayeilech: mincha -5 min
                if (strpos($info[$evDate]['parsha'] ?? '', 'Vayeilech') !== false) {
                    if (empty($info[$evDate]['kel_maleh_minchaT'])) {
                        $info[$evDate]['mincha'] = date('g:i',
                            strtotime($evDate . ' ' . ($info[$evDate]['mincha'] ?? '6:00') . ' pm') - (5 * 60)
                        );
                        $info[$evDate]['kel_maleh_minchaT'] = 1;
                    }
                }
            }

            // ── Daf Yomi ──
            if ($evCategory === 'dafyomi') {
                $info[$evDate]['dafyomi'] = str_replace('Daf Yomi: ', '', $evTitle);
            }

            // ── Holidays ──
            if ($evCategory === 'holiday') {

                // Title name fixes
                if ($evTitle === "Asara B'Tevet") {
                    $event['title'] = $evTitle = "Asara B'Teves";
                }

                // Append title
                if ($evTitle !== 'Sigd' && $evTitle !== 'Yom HaAliyah') {
                    $info[$evDate]['title'] = !empty($info[$evDate]['title'])
                        ? $info[$evDate]['title'] . '<br/>' . $evTitle
                        : $evTitle;
                }

                // ── Shabbos HaChodesh ──
                if ($evTitle === 'Shabbos HaChodesh') {
                    if (strpos($info[$evDate]['title'] ?? '', 'osh Chodesh') !== false) {
                        $newMinchaDate = date('Y-m-d', strtotime('-7 days', strtotime($evDate)));
                        if (isset($info[$newMinchaDate])) {
                            $info[$newMinchaDate]['sel_mincha'] = date('g:i',
                                strtotime($newMinchaDate . ' ' . ($info[$newMinchaDate]['mincha'] ?? '6:00') . ' pm') - (5 * 60)
                            );
                        }
                    } else {
                        $info[$evDate]['sel_mincha'] = date('g:i',
                            strtotime($evDate . ' ' . ($info[$evDate]['mincha'] ?? '6:00') . ' pm') - (5 * 60)
                        );
                    }
                }

                // ── Leil Selichos ──
                if ($evTitle === 'Leil Selichos') {
                    $info[$evDate]['title'] = '';
                    $selichotStartA = $evDate;
                    $selichotEndA   = date('Y-m-d', strtotime('+7 days', strtotime($evDate)));
                }

                // ── CHM on Shabbos ──
                if (strpos($evTitle, "CH''M") !== false && date('D', strtotime($evDate)) === 'Sat') {
                    $info[$evDate]['shacharis'] = '8:45';
                }

                // ── Erev Rosh Hashana ──
                if ($evTitle === 'Erev Rosh Hashana') {
                    $selichotEndA = $evDate;
                    $info[$evDate]['selichos'] = 'y';
                    if (date('D', strtotime($evDate)) === 'Sun' || ($info[$evDate]['shacharis'] ?? '') === '8:00') {
                        $info[$evDate]['shacharis'] = '7:30';
                    } else {
                        $info[$evDate]['shacharis'] = '6:00';
                    }
                    $info = $this->erevChag($info, $evDate);

                    $rh1Ts  = strtotime('+1 day', strtotime($evDate));
                    $rh1    = date('Y-m-d', $rh1Ts);
                    $rh2Ts  = strtotime('+2 days', strtotime($evDate));
                    $rh2    = date('Y-m-d', $rh2Ts);

                    if (isset($info[$rh1])) {
                        $info[$rh1]['shacharis'] = '8:00';
                        $sunset = $this->getSunset($rh1Ts);
                        $minchaSunset = $this->getSunset($rh2Ts);
                        $mincha  = (5 * 60) * round(strtotime('-30 minutes', $minchaSunset) / (5 * 60));
                        $info[$rh1]['mincha']  = date('g:i', $mincha);
                        $info[$rh1]['maariv']  = '';
                        $info[$rh1]['havdala'] = '';

                        if (date('D', $rh1Ts) === 'Fri') {
                            $info[$rh1]['candlelighting'] = date('g:i',
                                strtotime('-' . $this->candleLightingOffset . ' minutes', $sunset)
                            );
                        } else {
                            $info[$rh1]['candlelighting'] = date('g:i',
                                strtotime('+' . $this->havdalaOffset . ' minutes', $sunset)
                            );
                        }
                    }

                    if (isset($info[$rh2])) {
                        $info[$rh2]['shacharis'] = '8:00';
                        $sunset2 = $this->getSunset($rh2Ts);
                        $mincha2 = (5 * 60) * round(strtotime('-30 minutes', $sunset2) / (5 * 60));
                        $info[$rh2]['mincha'] = date('g:i', $mincha2);

                        if (date('D', $rh2Ts) === 'Fri') {
                            $info[$rh2]['candlelighting'] = date('g:i',
                                strtotime('-' . $this->candleLightingOffset . ' minutes', $sunset2)
                            );
                        } else {
                            $info[$rh2]['havdala'] = date('g:i',
                                strtotime('+' . $this->havdalaOffset . ' minutes', $sunset2)
                            );
                        }
                        $info[$rh2]['maariv'] = '';
                    }

                    $selichotStartB = $rh2;
                    $selichotEndB   = date('Y-m-d', strtotime('+7 days', strtotime($rh2)));
                }

                // ── Erev Yom Kippur ──
                if ($evTitle === 'Erev Yom Kippur') {
                    $selichotEndB = $evDate;
                    $info[$evDate]['selichos'] = 'y';
                    $info = $this->erevChag($info, $evDate);
                    $info[$evDate]['mincha'] = $this->erevYkMinchaDefault;
                    if (($info[$evDate]['candlelighting'] ?? '') < $this->erevYkMinchaThreshold) {
                        $info[$evDate]['mincha'] = $this->erevYkMinchaEarly;
                    }
                    $info[$evDate]['shacharis'] = '6:20';
                    if (date('D', strtotime($evDate)) === 'Sun') {
                        $info[$evDate]['shacharis'] = '7:30';
                    }
                    $info[$evDate]['kol_nidre'] = date('g:i',
                        $this->getSunset(strtotime($evDate), -(10 * 60))
                    );
                }

                // ── Yom Kippur ──
                if ($evTitle === 'Yom Kippur') {
                    $ykTs   = strtotime($evDate);
                    $sunset = $this->getSunset($ykTs);
                    $mincha = (5 * 60) * round(strtotime('-145 minutes', $sunset) / (5 * 60));
                    $info[$evDate]['mincha']   = date('g:i', $mincha);
                    $info[$evDate]['maariv']   = '';
                    $info[$evDate]['shacharis'] = '8:30';
                    $info[$evDate]['havdala']   = date('g:i',
                        strtotime('+' . $this->fastEndsTishabavOffset . ' minutes', $sunset)
                    );
                }

                // ── Apply Selichot A (Leil Selichos → Erev RH) ──
                if ($selichotEndA) {
                    $start = $selichotStartA ? strtotime('+1 day', strtotime($selichotStartA))
                                             : strtotime(date('Y-m-d', time() - 86400));
                    for ($t = $start; date('Y-m-d', $t) < $selichotEndA; $t = strtotime('+1 day', $t)) {
                        $d = date('Y-m-d', $t);
                        if (!isset($info[$d]) || date('D', $t) === 'Sat') { continue; }
                        $info[$d]['selichos'] = 'y';
                        if (($info[$d]['shacharis'] ?? '0:00') > '7:00') {
                            $info[$d]['shacharis'] = '7:30';
                        } else {
                            $info[$d]['shacharis'] = '6:10';
                        }
                    }
                    // Only do this once
                    $selichotEndA = null;
                    $selichotStartA = null;
                }

                // ── Apply Selichot B (RH II → Erev YK) ──
                if ($selichotEndB) {
                    $start = $selichotStartB ? strtotime('+1 day', strtotime($selichotStartB))
                                             : strtotime(date('Y-m-d', time() - 86400));
                    for ($t = $start; date('Y-m-d', $t) < $selichotEndB; $t = strtotime('+1 day', $t)) {
                        $d = date('Y-m-d', $t);
                        if (!isset($info[$d]) || date('D', $t) === 'Sat') { continue; }
                        $info[$d]['selichos'] = 'y';
                        if (($info[$d]['shacharis'] ?? '0:00') > '7:00') {
                            $info[$d]['shacharis'] = '7:30';
                        } else {
                            $info[$d]['shacharis'] = '6:10';
                        }
                        if (date('D', $t) !== 'Fri') {
                            if (($info[$d]['title'] ?? '') === 'Tzom Gedaliah') {
                                $info[$d]['sel_mincha'] = date('g:i',
                                    strtotime($d . ' ' . ($info[$d]['mincha'] ?? '6:00') . ' pm')
                                );
                            } else {
                                $info[$d]['sel_mincha'] = date('g:i',
                                    strtotime($d . ' ' . ($info[$d]['mincha'] ?? '6:00') . ' pm') - (5 * 60)
                                );
                            }
                        }
                    }
                    $selichotEndB   = null;
                    $selichotStartB = null;
                }

                // ── Sefira counting ──
                if ($sefiraStart && empty($info[$sefiraStart]['sefira'])) {
                    $sefiraEnd   = strtotime('+49 days', strtotime($sefiraStart));
                    $sefiraCount = 0;
                    for ($t = strtotime($sefiraStart); $t < $sefiraEnd; $t = strtotime('+1 day', $t)) {
                        $sefiraCount++;
                        $sd = date('Y-m-d', $t);
                        if (isset($info[$sd])) {
                            $info[$sd]['sefira'] = $sefiraCount;
                        }
                    }
                    $sefiraStart = null;
                }

                // ── Erev Tisha B'Av on Shabbos ──
                if ($evTitle === "Erev Tish'a B'Av" && date('D', strtotime($evDate)) === 'Sat') {
                    $info[$evDate]['shabbos_ends_ind'] = 'y';
                    $minchaTime = (5 * 60) * round(
                        ($this->getSunset(strtotime($evDate)) - (130 * 60)) / (5 * 60)
                    );
                    $info[$evDate]['mincha'] = date('g:i', $minchaTime);
                }

                // ── Erev Tisha B'Av (any day) ──
                if ($evTitle === "Erev Tish'a B'Av") {
                    $sunset = $this->getSunset(strtotime($evDate));
                    $info[$evDate]['mincha']           = '6:00';
                    $info[$evDate]['fast_starts_night'] = date('g:i', $sunset);
                }

                // ── Shabbos HaGadol / Shabbos Shuvah ──
                if ($evTitle === 'Shabbos HaGadol' || $evTitle === 'Shabbos Shuvah') {
                    $info[$evDate]['drasha'] = date('g:i',
                        strtotime($evDate . ' ' . ($info[$evDate]['mincha'] ?? '6:00') . ' pm') - (55 * 60)
                    );
                }

                // ── Generic Erev Chag (not Erev Tisha B'Av / Purim / YK) ──
                if (
                    (strpos($evTitle, 'Erev ') === 0
                        && $evTitle !== "Erev Tish'a B'Av"
                        && $evTitle !== 'Erev Purim'
                        && $evTitle !== 'Erev Yom Kippur')
                    || $evTitle === "Pesach VI (CH''M)"
                ) {
                    $info = $this->erevChag($info, $evDate);
                    if (isset($info[$evDate]['drasha'])) {
                        $info[$evDate]['drasha'] = date('g:i',
                            strtotime($evDate . ' ' . ($info[$evDate]['mincha'] ?? '6:00') . ' pm') - (55 * 60)
                        );
                    }
                }

                // ── Hoshana Raba ──
                if ($evTitle === 'Sukkos VII (Hoshana Raba)') {
                    $info = $this->erevChag($info, $evDate);
                    if (date('D', strtotime($evDate)) !== 'Sun') {
                        $info[$evDate]['shacharis'] = '6:30, 7:45';
                    } else {
                        $info[$evDate]['shacharis'] = '7:00, 8:00';
                    }
                }

                // ── Erev Pesach ──
                if ($evTitle === 'Erev Pesach') {
                    if (date('D', strtotime($evDate)) === 'Sat') {
                        $info[$evDate]['shacharis'] = '7:45';
                    }
                    $info[$evDate]['eat_by']  = date('g:i', $this->getByHour(strtotime($evDate), 4));
                    $info[$evDate]['burn_by'] = date('g:i', $this->getByHour(strtotime($evDate), 5));
                }

                // ── First-day Yom Tov (Sukkos I, Shmini Atzeres, Pesach VII, Pesach I, Shavuos I) ──
                if (in_array($evTitle, ['Sukkos I', 'Shmini Atzeres', 'Pesach VII', 'Pesach I', 'Shavuos I'])) {
                    $info = $this->yomtov($info, $evDate, true);
                    $info[$evDate]['maariv'] = '';
                }

                // ── Shavuos I: add vasikin ──
                if ($evTitle === 'Shavuos I') {
                    $vasikin = date('g:i', $this->getSunrise(strtotime($evDate)) - (40 * 60));
                    $info[$evDate]['shacharis'] = $vasikin . ', ' . $info[$evDate]['shacharis'];
                }

                // ── Pesach chatzos halailah ──
                if ($evTitle === 'Pesach I' || $evTitle === 'Pesach II') {
                    $info[$evDate]['chatzos_halaila'] = date('g:i',
                        $this->getByHour(strtotime($evDate), 6)
                    );
                }

                // ── Sefira start ──
                if ($evTitle === 'Pesach II') {
                    $sefiraStart = $evDate;
                }

                // ── Second-day Yom Tov ──
                if (in_array($evTitle, ['Sukkos II', 'Simchas Torah', 'Pesach VIII', 'Pesach II', 'Shavuos II'])) {
                    $info = $this->yomtov($info, $evDate, false);
                    $info[$evDate]['maariv'] = '';
                }

                // ── Simchas Torah / Pesach VIII / Shavuos II: neilas hachag ──
                if (in_array($evTitle, ['Simchas Torah', 'Pesach VIII', 'Shavuos II'])) {
                    $shkTs = $this->getSunset(strtotime($evDate));
                    if (date('D', strtotime($evDate)) !== 'Sat' && date('D', strtotime($evDate)) !== 'Fri') {
                        $info[$evDate]['mincha'] = date('g:i',
                            (60 * 5) * round(($shkTs - (30 * 60)) / (60 * 5))
                        );
                    }
                }

                // ── Chol HaMoed (weekday) ──
                if (strpos($evTitle, "CH''M") !== false && date('D', strtotime($evDate)) !== 'Sat') {
                    if (date('D', strtotime($evDate)) !== 'Sun') {
                        $info[$evDate]['shacharis'] = '6:30, 8';
                    } else {
                        $info[$evDate]['shacharis'] = '8:00';
                    }
                }

                // ── Minor fasts & Ta'anis Esther ──
                $minorFasts = [
                    'Tzom Gedaliah', "Asara B'Tevet", "Asara B'Teves",
                    'Tzom Tammuz', "Tish'a B'Av", "Ta'anis Esther",
                ];
                $isErevPurimFast = ($evTitle === 'Erev Purim' && date('D', strtotime($evDate)) !== 'Sat');

                if (in_array($evTitle, $minorFasts) || $isErevPurimFast) {
                    $fastTs = strtotime($evDate);
                    if (date('D', $fastTs) !== 'Sun') {
                        if (($info[$evDate]['shacharis'] ?? '') === '8:00') {
                            $info[$evDate]['shacharis'] = '6:20,8';
                        } else {
                            $info[$evDate]['shacharis'] = '6:20';
                        }
                    }

                    $sunset = $this->getSunset($fastTs);
                    $alos   = $this->getAlos($fastTs);
                    $mincha = (5 * 60) * round(strtotime('-30 minutes', $sunset) / (5 * 60));
                    if (date('D', $fastTs) === 'Fri') {
                        $mincha = (60 * 5) * round(($sunset - (35 * 60)) / (60 * 5));
                    }
                    $info[$evDate]['mincha'] = date('g:i', $mincha);

                    if ($evTitle === "Tish'a B'Av") {
                        $info[$evDate]['shacharis'] = '8:30';
                        $info[$evDate]['chatzos']   = date('g:i', $this->getByHour($fastTs, 6));
                        $info[$evDate]['mincha']    = date('g:i',
                            (5 * 60) * ceil($this->getByHour($fastTs, 6.5) / (5 * 60))
                        );
                        $info[$evDate]['maariv']    = date('g:i',
                            (5 * 60) * round(strtotime('+30 minutes', $sunset) / (5 * 60))
                        );
                        $info[$evDate]['fast_ends'] = date('g:i',
                            strtotime('+' . $this->fastEndsTishabavOffset . ' minutes', $sunset)
                        );
                    } else {
                        $info[$evDate]['fast_ends']   = date('g:i',
                            strtotime('+' . $this->fastEndsMinorOffset . ' minutes', $sunset)
                        );
                        $info[$evDate]['fast_starts'] = date('g:i', $alos);
                    }

                    if ($evTitle === 'Erev Purim') {
                        $info[$evDate]['title'] = "Ta'anis Esther";
                    }
                }

                // ── Erev Purim: megilla ──
                if ($evTitle === 'Erev Purim') {
                    $fastTs = strtotime($evDate);
                    $sunset = $this->getSunset($fastTs);
                    $info[$evDate]['megilla1'] = date('g:i',
                        strtotime('+' . $this->fastEndsMinorOffset . ' minutes', $sunset)
                    );
                    if (date('D', strtotime($evDate)) === 'Sat') {
                        $info[$evDate]['megilla1'] = date('g:i',
                            (5 * 60) * ceil(strtotime('+70 minutes', $sunset) / (5 * 60))
                        );
                    }
                }

                // ── Purim ──
                if ($evTitle === 'Purim') {
                    if (date('D', strtotime($evDate)) === 'Sun') {
                        $info[$evDate]['shacharis'] = '8:00';
                        $info[$evDate]['megilla2']  = '8:30';
                    } else {
                        $info[$evDate]['shacharis'] = '6:30, 8';
                        $info[$evDate]['megilla2']  = '7:00, 8:30';
                    }
                    $info[$evDate]['mincha'] = date('g:i',
                        (5 * 60) * ceil($this->getByHour(strtotime($evDate), 6.5) / (5 * 60))
                    );
                    $info[$evDate]['maariv'] = '7:30';
                    if (date('D', strtotime($evDate)) === 'Fri') {
                        $info[$evDate]['maariv'] = '';
                    }
                }

                // ── Chanukah (earlier shacharis) ──
                if (strpos($evTitle, 'Chanukah') !== false
                    && strpos($evTitle, 'Chanukah: 1 Candle') === false
                    && date('D', strtotime($evDate)) !== 'Sun'
                    && date('D', strtotime($evDate)) !== 'Sat'
                ) {
                    if (($info[$evDate]['shacharis'] ?? '') === '6:35') {
                        $info[$evDate]['shacharis'] = '6:25';
                    }
                }
            }

            // ── Rosh Chodesh ──
            if ($evCategory === 'roshchodesh') {
                $rcTitle = $evTitle;
                if ($rcTitle === 'Rosh Chodesh Tevet')  { $rcTitle = 'Rosh Chodesh Teves'; }
                if ($rcTitle === 'Rosh Chodesh Iyyar')   { $rcTitle = 'Rosh Chodesh Iyar'; }

                $info[$evDate]['title'] = !empty($info[$evDate]['title'])
                    ? $info[$evDate]['title'] . '<br/>' . $rcTitle
                    : $rcTitle;

                if (strpos($rcTitle, 'Rosh Chodesh') !== false
                    && date('D', strtotime($evDate)) !== 'Sun'
                    && date('D', strtotime($evDate)) !== 'Sat'
                ) {
                    if (($info[$evDate]['shacharis'] ?? '9:00') < '7:00') {
                        $info[$evDate]['shacharis'] = '6:20';
                        if (strtotime($evDate) < $schoolStarts && strtotime($evDate) > $schoolEnds) {
                            $info[$evDate]['shacharis'] = '6:20, 8';
                        }
                    }
                }
            }
        }

        // ── Build selichos date set (for getEventTagsForDay) ──
        $selichosDates = [];
        foreach ($info as $date => $dayInfo) {
            if (isset($dayInfo['selichos'])) {
                $selichosDates[] = $date;
            }
        }

        // ── Load minyanim with exceptions eager-loaded ──
        $minyanimWithExceptions = CalendarMinyan::where('active', true)
            ->orderBy('sort_order')
            ->with('exceptions')
            ->get();

        // ── Build minyanim_display for each day and apply exception overrides ──
        foreach ($info as $date => &$dayInfo) {
            if (!isset($dayInfo['display_date'])) { continue; }
            $ts  = strtotime($date);
            $dow = (int) date('w', $ts);

            // Build base minyanim_display from time rules
            $minyanimDisplay = [];
            foreach ($minyanimWithExceptions as $minyan) {
                $rule = $minyan->getTimeRuleForDay($dow);
                $time = $this->computeTimeFromRule($rule, $ts);
                if ($time !== null) {
                    $minyanimDisplay[] = [
                        'label' => $minyan->name,
                        'times' => $time,
                    ];
                }
            }
            $dayInfo['minyanim_display'] = $minyanimDisplay;

            // Compute event tags for this date
            $eventTags = $this->getEventTagsForDay($date, $allItems, false, $selichosDates);
            $dayInfo['event_tags'] = $eventTags;

            // Apply exception overrides
            $this->applyMinyanExceptions($info, $date, $eventTags, $dow, $minyanimWithExceptions);

            // If overrides exist, rebuild minyanim_display incorporating them
            if (!empty($dayInfo['minyanim_override'])) {
                $merged = [];
                foreach ($minyanimWithExceptions as $minyan) {
                    if (array_key_exists($minyan->id, $dayInfo['minyanim_override'])) {
                        $overrideTime = $dayInfo['minyanim_override'][$minyan->id];
                        $merged[] = [
                            'id'      => $minyan->id,
                            'name'    => $minyan->name,
                            'type'    => $minyan->type,
                            'time'    => $overrideTime,
                            'visible' => $overrideTime !== null,
                        ];
                    } else {
                        $rule = $minyan->getTimeRuleForDay($dow);
                        $time = $this->computeTimeFromRule($rule, $ts);
                        $merged[] = [
                            'id'      => $minyan->id,
                            'name'    => $minyan->name,
                            'type'    => $minyan->type,
                            'time'    => $time,
                            'visible' => $time !== null,
                        ];
                    }
                }
                $dayInfo['minyanim_display'] = $merged;
            }
        }
        unset($dayInfo);

        // ── Sort by date ──
        ksort($info);

        // ── Group into months ──
        $monthBlocks = [];
        foreach ($months as $calMonth => $yr) {
            $block = [
                'month' => $calMonth,
                'year'  => $yr,
                'days'  => [],
            ];
            foreach ($info as $date => $dayInfo) {
                if (!isset($dayInfo['display_date'])) { continue; }
                if (($dayInfo['_month'] ?? '') === $calMonth && ($dayInfo['_year'] ?? 0) === $yr) {
                    $block['days'][$date] = $dayInfo;
                }
            }
            $monthBlocks[] = $block;
        }

        return $monthBlocks;
    }

    // =========================================================================
    // Dynamic time rule computation
    // =========================================================================

    /**
     * Compute a display time string from a time rule spec.
     *
     * @param  array       $rule      e.g. ['type'=>'static','time'=>'8:00'] or ['type'=>'dynamic','ref'=>'sunset','offset_min'=>-13,'round_to'=>5,'round_dir'=>'nearest']
     * @param  int         $timestamp Unix timestamp for the day (9 AM local)
     * @param  string|null $basetime  The already-computed base time string (used by relative/prepend)
     * @return string|null            Formatted time string, or null if hidden
     */
    public function computeTimeFromRule(array $rule, int $timestamp, ?string $basetime = null): ?string
    {
        $type = $rule['type'] ?? 'static';

        if ($type === 'hidden') {
            return null;
        }

        if ($type === 'static') {
            return isset($rule['time']) && $rule['time'] !== '' ? $rule['time'] : null;
        }

        if ($type === 'dynamic') {
            $ref        = $rule['ref']        ?? 'sunset';
            $offsetMin  = (int) ($rule['offset_min'] ?? 0);
            $roundTo    = (int) ($rule['round_to']   ?? 0);
            $roundDir   = $rule['round_dir']          ?? 'nearest';

            // Compute base timestamp from ref
            $baseTs = match ($ref) {
                'sunrise' => $this->getSunrise($timestamp),
                'alos'    => $this->getAlos($timestamp),
                'hour3'   => $this->getByHour($timestamp, 3),
                default   => $this->getSunset($timestamp),   // 'sunset'
            };

            $computed = $baseTs + ($offsetMin * 60);

            if ($roundTo > 0) {
                $unit = $roundTo * 60;
                $computed = match ($roundDir) {
                    'floor'   => (int) floor($computed / $unit) * $unit,
                    'ceiling' => (int) ceil($computed  / $unit) * $unit,
                    default   => (int) round($computed / $unit) * $unit,  // nearest
                };
            }

            return date('g:i', $computed);
        }

        if ($type === 'relative') {
            if ($basetime === null || $basetime === '') {
                return null;
            }
            $offsetMin = (int) ($rule['offset_min'] ?? 0);
            // Parse basetime — it might be "6:35" or "6:35, 8:00" (take first token)
            $first  = trim(explode(',', $basetime)[0]);
            $parsed = strtotime('today ' . $first . (strpos($first, ' ') === false && strlen($first) <= 5 ? ' am' : ''));
            if ($parsed === false) {
                return $basetime;
            }
            return date('g:i', $parsed + ($offsetMin * 60));
        }

        if ($type === 'prepend') {
            $prependTime = $rule['time'] ?? null;
            if (!$prependTime) {
                return $basetime;
            }
            if ($basetime !== null && $basetime !== '') {
                return $prependTime . ', ' . $basetime;
            }
            return $prependTime;
        }

        return null;
    }

    // =========================================================================
    // Event tag helpers
    // =========================================================================

    /**
     * Return array of event_type tag strings for a given calendar date.
     *
     * @param  string $date            'Y-m-d'
     * @param  array  $allEvents       Raw hebcal items array (all items, any date)
     * @param  bool   $isCivilHoliday  Whether this date is a US civil holiday
     * @param  array  $selichosDates   Set of 'Y-m-d' strings that fall in a selichot period
     * @return array
     */
    public function getEventTagsForDay(
        string $date,
        array  $allEvents,
        bool   $isCivilHoliday = false,
        array  $selichosDates  = []
    ): array {
        $tags = [];

        $yomTovTitles = [
            'Sukkos I', 'Sukkos II', 'Shmini Atzeres', 'Simchas Torah',
            'Pesach I', 'Pesach II', 'Pesach VII', 'Pesach VIII',
            'Shavuos I', 'Shavuos II',
        ];

        $minorFastTitles = [
            'Tzom Gedaliah', "Asara B'Tevet", "Asara B'Teves",
            'Tzom Tammuz', "Ta'anis Esther", 'Erev Purim',
        ];

        foreach ($allEvents as $event) {
            if (($event['date'] ?? '') !== $date) {
                continue;
            }

            $title    = $event['title']    ?? '';
            $category = $event['category'] ?? '';

            // Rosh Hashana
            if (
                stripos($title, 'Rosh Hashana') !== false
                || $title === 'Rosh Hashana I'
                || $title === 'Rosh Hashana II'
            ) {
                $tags[] = 'rosh_hashana';
            }

            // Yom Kippur (exact)
            if ($title === 'Yom Kippur') {
                $tags[] = 'yom_kippur';
            }

            // Erev Yom Kippur (exact)
            if ($title === 'Erev Yom Kippur') {
                $tags[] = 'erev_yom_kippur';
            }

            // Major Yom Tov
            if (in_array($title, $yomTovTitles)) {
                $tags[] = 'yom_tov';
            }

            // Erev Yom Tov (starts with Erev, but not Erev Purim or Erev Tisha B'Av or Erev YK)
            if (
                strpos($title, 'Erev ') === 0
                && $title !== 'Erev Purim'
                && $title !== "Erev Tish'a B'Av"
                && $title !== 'Erev Yom Kippur'
            ) {
                $tags[] = 'erev_yom_tov';
            }

            // Chol HaMoed
            if (strpos($title, "CH''M") !== false) {
                $tags[] = 'chol_hamoed';
            }

            // Hoshana Raba
            if ($title === 'Sukkos VII (Hoshana Raba)') {
                $tags[] = 'hoshana_raba';
            }

            // Rosh Chodesh
            if ($category === 'roshchodesh') {
                $tags[] = 'rosh_chodesh';
            }

            // Chanukah
            if (strpos($title, 'Chanukah') !== false) {
                $tags[] = 'chanukah';
            }

            // Minor fasts (include Erev Purim = Ta'anis Esther)
            if (in_array($title, $minorFastTitles)) {
                $tags[] = 'fast_minor';
            }

            // Tisha B'Av
            if ($title === "Tish'a B'Av") {
                $tags[] = 'tisha_bav';
            }

            // Purim
            if ($title === 'Purim') {
                $tags[] = 'purim';
            }
        }

        // Selichot period
        if (in_array($date, $selichosDates)) {
            $tags[] = 'selichos';
        }

        // Civil holiday
        if ($isCivilHoliday) {
            $tags[] = 'civil_holiday';
        }

        return array_values(array_unique($tags));
    }

    // =========================================================================
    // Apply minyan exception overrides
    // =========================================================================

    /**
     * For each minyan, find the best-matching exception rule and compute the
     * override time, storing results in $info[$date]['minyanim_override'][$minyan->id].
     * A null value means the minyan should be hidden for this day.
     *
     * @param  array      $info
     * @param  string     $date
     * @param  array      $eventTags
     * @param  int        $dow       0=Sun … 6=Sat
     * @param  Collection $minyanim  Eager-loaded with ->exceptions
     */
    public function applyMinyanExceptions(
        array      &$info,
        string      $date,
        array       $eventTags,
        int         $dow,
        Collection  $minyanim
    ): void {
        if (empty($eventTags)) {
            return;
        }

        $ts = $info[$date]['_timestamp'] ?? strtotime($date);

        foreach ($minyanim as $minyan) {
            // Find all exceptions where event_type is in $eventTags AND day_type matches $dow
            $matched = $minyan->exceptions->filter(function ($ex) use ($eventTags, $dow) {
                if (!in_array($ex->event_type, $eventTags)) {
                    return false;
                }
                return match ($ex->day_type) {
                    'any'     => true,
                    'weekday' => $dow >= 1 && $dow <= 5,
                    'sunday'  => $dow === 0,
                    'shabbos' => $dow === 6,
                    default   => false,
                };
            });

            if ($matched->isEmpty()) {
                continue;
            }

            // Take highest priority rule
            $best = $matched->sortByDesc('priority')->first();

            // Determine the base computed time for this minyan/day
            $baseTime = $this->computeTimeFromRule(
                $minyan->getTimeRuleForDay($dow),
                $ts
            );

            // Compute the override time
            $overrideTime = $this->computeTimeFromRule(
                array_merge(['type' => $best->override_type], (array) ($best->override_value ?? [])),
                $ts,
                $baseTime
            );

            $info[$date]['minyanim_override'][$minyan->id] = $overrideTime;
        }
    }

    // =========================================================================
    // Helper: erev_chag (ported from original)
    // =========================================================================

    protected function erevChag(array $info, string $date): array
    {
        $ts       = strtotime($date);
        $shkTs    = $this->getSunset($ts);
        $candles  = date('g:i', $this->getSunset($ts, -($this->candleLightingOffset * 60)));

        $info[$date]['candlelighting'] = $candles;

        $pesach2Date = $info['holidays'][date('Y', $ts)]['Pesach II'] ?? null;
        $erevShavuos = $info['holidays'][date('Y', $ts)]['Erev Shavuos'] ?? null;

        if ($candles > '7:00'
            && date('D', $ts) !== 'Sat'
            && ($pesach2Date !== null && $date > $pesach2Date)
            && ($erevShavuos === null || $date !== $erevShavuos)
            && $date < date('Y', $ts) . '-12-31'
        ) {
            $info[$date]['mincha']         = '7:00';
            $info[$date]['candlelighting'] = '7:00';
        } elseif (date('D', $ts) === 'Sat') {
            $info[$date]['mincha'] = date('g:i',
                (60 * 5) * round(($shkTs - (25 * 60)) / (60 * 5))
            );
            $info[$date]['candlelighting'] = date('g:i', $shkTs + ($this->havdalaOffset * 60));
        } else {
            $info[$date]['mincha'] = $info[$date]['candlelighting'];
        }

        $info[$date]['havdala'] = '';
        $info[$date]['maariv']  = '';
        return $info;
    }

    // =========================================================================
    // Helper: yomtov (ported from original)
    // =========================================================================

    protected function yomtov(array $info, string $date, bool $firstDay = true): array
    {
        $ts    = strtotime($date);
        $shkTs = $this->getSunset($ts);
        $tzeis = date('g:i', $shkTs + ($this->havdalaOffset * 60));

        $info[$date]['shacharis'] = '8:45';

        if (date('D', $ts) !== 'Sat') {
            $info[$date]['mincha'] = date('g:i',
                (60 * 5) * round(($shkTs - (20 * 60)) / (60 * 5))
            );
        }

        if (date('D', $ts) === 'Fri') {
            $candles = $info[$date]['candlelighting'] ?? date('g:i', $shkTs - ($this->candleLightingOffset * 60));
            $info[$date]['candlelighting'] = $candles;
            $info[$date]['mincha']         = $candles;
        } elseif ($firstDay) {
            $info[$date]['candlelighting'] = $tzeis;
            $info[$date]['havdala']        = '';
        } else {
            $info[$date]['havdala'] = $tzeis;
        }

        $info[$date]['maariv'] = '';
        return $info;
    }

    // =========================================================================
    // Helper: Hebrew date string
    // =========================================================================

    protected function getHebrewDate(int $timestamp): string
    {
        return iconv(
            'WINDOWS-1255',
            'UTF-8',
            jdtojewish(unixtojd($timestamp), true, CAL_JEWISH_ADD_GERESHAYIM)
        );
    }
}
