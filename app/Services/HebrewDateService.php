<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\FamilyMember;

class HebrewDateService
{
    // Hebrew month names (1=Tishrei ... 13=Adar II in leap years)
    private const MONTH_NAMES = [
        1  => 'Tishrei',
        2  => 'Cheshvan',
        3  => 'Kislev',
        4  => 'Tevet',
        5  => 'Shevat',
        6  => 'Adar I',   // Adar I in leap year, Adar in non-leap
        7  => 'Adar II',  // Adar II in leap year, Nisan in non-leap
        8  => 'Nisan',
        9  => 'Iyar',
        10 => 'Sivan',
        11 => 'Tammuz',
        12 => 'Av',
        13 => 'Elul',
    ];

    // Hebrew month names for non-leap years (7 months shift)
    private const MONTH_NAMES_REGULAR = [
        1  => 'Tishrei',
        2  => 'Cheshvan',
        3  => 'Kislev',
        4  => 'Tevet',
        5  => 'Shevat',
        6  => 'Adar',
        7  => 'Nisan',
        8  => 'Iyar',
        9  => 'Sivan',
        10 => 'Tammuz',
        11 => 'Av',
        12 => 'Elul',
    ];

    private const HEBREW_LETTERS = [
        1=>'א', 2=>'ב', 3=>'ג', 4=>'ד', 5=>'ה', 6=>'ו', 7=>'ז', 8=>'ח', 9=>'ט',
        10=>'י', 20=>'כ', 30=>'ל', 40=>'מ', 50=>'נ', 60=>'ס', 70=>'ע', 80=>'פ', 90=>'צ',
        100=>'ק', 200=>'ר', 300=>'ש', 400=>'ת',
    ];

    public function gregorianToHebrew(\DateTimeInterface|string $date): array
    {
        $carbon = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

        $jd = gregoriantojd($carbon->month, $carbon->day, $carbon->year);
        [$month, $day, $year] = explode('/', jdtojewish($jd));

        $month = (int)$month;
        $day   = (int)$day;
        $year  = (int)$year;

        $isLeap    = $this->isLeapYear($year);
        $monthName = $this->getMonthName($month, $isLeap);
        $formatted = $this->numberToHebrewLetters($day) . ' ' . $monthName . ' ' . $this->yearToHebrew($year);

        return [
            'day'        => $day,
            'month'      => $month,
            'year'       => $year,
            'month_name' => $monthName,
            'formatted'  => $formatted,
            'is_leap'    => $isLeap,
        ];
    }

    public function hebrewToGregorian(int $day, int $month, int $year): Carbon
    {
        $jd = jewishtojd($month, $day, $year);
        [$gregMonth, $gregDay, $gregYear] = explode('/', jdtogregorian($jd));
        return Carbon::create((int)$gregYear, (int)$gregMonth, (int)$gregDay);
    }

    public function getCurrentHebrewYear(): int
    {
        return $this->gregorianToHebrew(now())['year'];
    }

    public function formatHebrewDate(int $day, int $month, int $year): string
    {
        $isLeap    = $this->isLeapYear($year);
        $monthName = $this->getMonthName($month, $isLeap);
        return $this->numberToHebrewLetters($day) . ' ' . $monthName . ' ' . $this->yearToHebrew($year);
    }

    public function upcomingYahrzeits(int $familyId, int $days = 60): Collection
    {
        $members = FamilyMember::where('family_id', $familyId)
            ->whereNotNull('date_of_death')
            ->orWhere(function ($q) use ($familyId) {
                $q->where('family_id', $familyId)->whereNotNull('hebrew_date_of_death');
            })
            ->get();

        return $this->computeUpcoming($members, 'death', $days);
    }

    public function upcomingBirthdays(int $familyId, int $days = 60): Collection
    {
        $members = FamilyMember::where('family_id', $familyId)
            ->living()
            ->where(function ($q) {
                $q->whereNotNull('date_of_birth')
                  ->orWhereNotNull('hebrew_date_of_birth');
            })
            ->get();

        return $this->computeUpcoming($members, 'birth', $days);
    }

    private function computeUpcoming(Collection $members, string $type, int $days): Collection
    {
        $today   = Carbon::today();
        $results = collect();

        foreach ($members as $member) {
            $hebrewDate = $this->resolveHebrewDate($member, $type);
            if (!$hebrewDate) {
                continue;
            }

            [$hDay, $hMonth, $sourceYear] = $hebrewDate;
            $currentHYear    = $this->getCurrentHebrewYear();

            // Try current Hebrew year first, then next year
            foreach ([$currentHYear, $currentHYear + 1] as $hYear) {
                // Map the month number from source year format to target year format
                $targetMonth = $this->mapMonthBetweenYears($hMonth, $sourceYear, $hYear);
                $normalizedDay   = $this->clampDayToMonth($hDay, $targetMonth, $hYear);

                try {
                    $gregorianDate = $this->hebrewToGregorian($normalizedDay, $targetMonth, $hYear);
                } catch (\Throwable $e) {
                    continue;
                }

                if ($gregorianDate->gte($today) && $gregorianDate->lte($today->copy()->addDays($days))) {
                    $results->push([
                        'member'         => $member,
                        'gregorian_date' => $gregorianDate,
                        'hebrew_date'    => [
                            'day'        => $normalizedDay,
                            'month'      => $targetMonth,
                            'year'       => $hYear,
                            'month_name' => $this->getMonthName($targetMonth, $this->isLeapYear($hYear)),
                        ],
                        'type'           => $type,
                    ]);
                    break;
                }
            }
        }

        return $results->sortBy(fn($r) => $r['gregorian_date']->timestamp)->values();
    }

    private function resolveHebrewDate(FamilyMember $member, string $type): ?array
    {
        $overrideField = $type === 'death' ? 'hebrew_dod_override' : 'hebrew_dob_override';
        $hebrewField   = $type === 'death' ? 'hebrew_date_of_death' : 'hebrew_date_of_birth';
        $gregField     = $type === 'death' ? 'date_of_death' : 'date_of_birth';

        // First priority: manually overridden Hebrew date
        if ($member->$overrideField && $member->$hebrewField) {
            $parsed = $this->parseStoredHebrewDate($member->$hebrewField);
            if ($parsed) {
                return array_merge($parsed, [null]); // Day, month, null for sourceYear
            }
        }

        // Second priority: auto-generated or manually entered Hebrew date (without override flag)
        // These were generated from a Gregorian date so we use them going forward
        if ($member->$hebrewField) {
            $parsed = $this->parseStoredHebrewDate($member->$hebrewField);
            if ($parsed) {
                return array_merge($parsed, [null]); // Day, month, null for sourceYear
            }
        }

        // Last priority: only if no Hebrew date exists, convert from Gregorian
        if ($member->$gregField) {
            $h = $this->gregorianToHebrew($member->$gregField);
            return [$h['day'], $h['month'], $h['year']]; // Day, month, sourceYear
        }

        return null;
    }

    private function parseStoredHebrewDate(string $stored): ?array
    {
        // Stored format: "15 Tishrei 5785"
        $parts = explode(' ', trim($stored));
        if (count($parts) < 2) {
            return null;
        }

        $day   = (int)$parts[0];
        $month = $this->monthNameToNumber($parts[1]);

        if ($day < 1 || $month < 1) {
            return null;
        }

        return [$day, $month];
    }

    private function monthNameToNumber(string $name): int
    {
        $map = [
            'tishrei'  => 1, 'tishri' => 1,
            'cheshvan' => 2, 'heshvan' => 2, 'marcheshvan' => 2,
            'kislev'   => 3,
            'tevet'    => 4,
            'shevat'   => 5,
            'adar'     => 6, 'adar i' => 6, 'adar 1' => 6,
            'adar ii'  => 7, 'adar 2' => 7,
            'nisan'    => 8,
            'iyar'     => 9,
            'sivan'    => 10,
            'tammuz'   => 11,
            'av'       => 12,
            'elul'     => 13,
        ];

        return $map[strtolower(trim($name))] ?? 0;
    }

    public function getMonthName(int $month, bool $isLeapYear): string
    {
        if ($isLeapYear) {
            return self::MONTH_NAMES[$month] ?? "Month {$month}";
        }

        return self::MONTH_NAMES_REGULAR[$month] ?? "Month {$month}";
    }

    public function isLeapYear(int $hebrewYear): bool
    {
        return ((7 * $hebrewYear) + 1) % 19 < 7;
    }

    // When a person died in Adar of a non-leap year and current year is a leap year,
    // yahrzeit falls in Adar II (standard Ashkenazi practice).
    private function normalizeAdar(int $month, int $targetYear): int
    {
        // This function maps a Hebrew month number from one year to another,
        // accounting for leap year differences.
        // In a leap year: 6=Adar I, 7=Adar II, 8=Nisan, 9=Iyar...
        // In a non-leap: 6=Adar, 7=Nisan, 8=Iyar...
        
        // For practical purposes, just return the month as-is since jdtoJewish
        // already handles the conversion when we convert Hebrew to Gregorian.
        // The key is to use jewishtojd/jdtogregorian which automatically handles leap years.
        return $month;
    }

    private function clampDayToMonth(int $day, int $month, int $year): int
    {
        // Get days in the month by checking last valid JD
        try {
            $jd = jewishtojd($month, $day, $year);
            if ($jd > 0) return $day;
        } catch (\Throwable) {}

        // Clamp: try 29 then 28
        foreach ([29, 28] as $clampDay) {
            if ($clampDay < $day) {
                try {
                    $jd = jewishtojd($month, $clampDay, $year);
                    if ($jd > 0) return $clampDay;
                } catch (\Throwable) {}
            }
        }

        return $day;
    }

    /**
     * Map a Hebrew month number from one year's calendar to another year's calendar.
     * This accounts for leap year differences where month numbers shift.
     * 
     * For example:
     * - In year 5739 (leap): Iyar = 9
     * - In year 5786 (non-leap): Iyar = 8
     * This function ensures the month maps correctly between these systems.
     */
    private function mapMonthBetweenYears(int $month, ?int $sourceYear, int $targetYear): int
    {
        // If we don't know the source year, can't map properly - just use the month as-is
        if ($sourceYear === null) {
            return $month;
        }

        $sourceIsLeap = $this->isLeapYear($sourceYear);
        $targetIsLeap = $this->isLeapYear($targetYear);

        // If both have same leap status, no mapping needed
        if ($sourceIsLeap === $targetIsLeap) {
            return $month;
        }

        // Map based on the logical months:
        // Non-leap: 1=Tishrei, 2=Cheshvan, 3=Kislev, 4=Tevet, 5=Shevat, 6=Adar, 7=Nisan, 8=Iyar...
        // Leap: 1=Tishrei, 2=Cheshvan, 3=Kislev, 4=Tevet, 5=Shevat, 6=Adar I, 7=Adar II, 8=Nisan, 9=Iyar...

        if ($sourceIsLeap && !$targetIsLeap) {
            // Map from leap to non-leap
            // 6=Adar I -> 6=Adar (stays the same, accounts for both)
            // 7=Adar II -> 6=Adar (both refer to the "main" Adar)
            // 8=Nisan -> 7=Nisan
            // 9=Iyar -> 8=Iyar
            if ($month === 7) return 6;      // Adar II -> Adar
            if ($month >= 8) return $month - 1;  // Shift everything after Adar II down by 1
            return $month;
        } else {
            // Map from non-leap to leap
            // 6=Adar -> 7=Adar II (Ashkenazi tradition: non-leap Adar matches leap Adar II)
            // 7=Nisan -> 8=Nisan
            // 8=Iyar -> 9=Iyar
            if ($month === 6) return 7;      // Adar -> Adar II
            if ($month >= 7) return $month + 1;  // Shift everything after Adar up by 1
            return $month;
        }
    }

    public function numberToHebrewLetters(int $n): string
    {
        // Special cases to avoid the divine names YH (15) and YV (16)
        if ($n === 15) return 'ט"ו';
        if ($n === 16) return 'ט"ז';

        $result = '';
        $values = [400, 300, 200, 100, 90, 80, 70, 60, 50, 40, 30, 20, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

        foreach ($values as $val) {
            while ($n >= $val) {
                $result .= self::HEBREW_LETTERS[$val];
                $n -= $val;
            }
        }

        // Insert geresh/gershayim
        if (strlen($result) === 1) {
            $result .= "'";
        } elseif (strlen($result) > 1) {
            $result = substr($result, 0, -1) . '"' . substr($result, -1);
        }

        return $result;
    }

    private function yearToHebrew(int $year): string
    {
        // Display last 3 digits of the year in Hebrew
        $shortYear = $year % 1000;
        return $this->numberToHebrewLetters($shortYear);
    }

    public function formatForStorage(\DateTimeInterface|string $date): string
    {
        $h = $this->gregorianToHebrew($date);
        return "{$h['day']} {$h['month_name']} {$h['year']}";
    }
}
