<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name ?? config("app.name") }} Calendar {{ $year }}–{{ $year + 1 }}</title>
    <style>
        /* ── Print setup ── */
        @media print {
            @page { size: landscape; margin: 6mm; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; break-after: page; }
            body { font-size: 8px; }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 9px;
            background: #fff;
            color: #000;
        }

        /* ── Toolbar (screen only) ── */
        .toolbar {
            position: sticky;
            top: 0;
            background: #1a2d5a;
            color: #fff;
            padding: 0.6rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 100;
        }
        .toolbar h2 { font-size: 1rem; font-family: Georgia, serif; color: #c9a84c; }
        .toolbar button {
            background: #c9a84c;
            color: #1a2d5a;
            border: none;
            padding: 0.4rem 1rem;
            font-weight: 700;
            cursor: pointer;
            border-radius: 4px;
        }
        .toolbar a { color: #c9a84c; text-decoration: none; font-size: 0.85rem; }
        .toolbar a:hover { text-decoration: underline; }

        /* ── Month wrapper ── */
        .month-block {
            padding: 6px 0 12px;
        }

        /* ── Month header ── */
        .month-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 4px;
            width: 100%;
        }
        .month-header .month-name {
            font-size: 20px;
            font-weight: bold;
            font-family: Georgia, serif;
        }
        .month-header .heb-range {
            font-size: 14px;
            direction: rtl;
            text-align: right;
        }

        /* ── Calendar grid ── */
        .cal-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .cal-table thead th {
            border: 1px solid #555;
            background: #1a2d5a;
            color: #fff;
            text-align: center;
            padding: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .cal-table tbody td {
            border: 1px solid #888;
            vertical-align: top;
            height: 95px;
            width: 14.28%;
            padding: 0;
        }

        /* ── Day cell inner table ── */
        .day-inner {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .day-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1px 3px;
            border-bottom: 1px solid #ddd;
        }
        .day-num {
            font-size: 13px;
            font-weight: bold;
            line-height: 1;
        }
        .day-sun-set {
            font-size: 7px;
            color: #555;
            text-align: right;
            line-height: 1.2;
        }
        .day-body {
            padding: 1px 2px;
            font-size: 7.5px;
            line-height: 1.35;
            text-align: center;
        }
        .day-body .parsha { font-weight: bold; font-size: 7.5px; }
        .day-body .title  { font-weight: bold; font-size: 7px; color: #333; }
        .day-footer {
            padding: 1px 2px;
            font-size: 7px;
            text-align: center;
            color: #555;
            border-top: 1px solid #eee;
            margin-top: auto;
        }
        .day-heb  { font-size: 8px; color: #222; font-weight: 600; }
        .day-daf  { font-size: 6.5px; color: #777; }
        .sefira   { font-style: italic; font-size: 6.5px; color: #555; }

        .gray { color: #aaa; }

        /* ── Shaded / special rows ── */
        td.shabbos { background: #fffef5; }

        /* ── Empty spacer cell ── */
        td.spacer { border: 1px solid #ccc; }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <h2>{{ $tenant->name ?? config("app.name") }} Calendar {{ $year }}–{{ $year + 1 }}</h2>
    <button onclick="window.print()">Print / Save PDF</button>
    <a href="{{ route('admin.calendar.generate') }}">← Back to Generator</a>
</div>

@foreach($calendar as $block)
@php
    $calMonth = $block['month'];
    $calYear  = $block['year'];
    $days     = $block['days'];
    // Hebrew range for month header
    $firstTs = strtotime($calMonth . ' 1, ' . $calYear);
    $lastTs  = strtotime(date('Y-m-t', $firstTs));
    $hebStart = iconv('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($firstTs), true, CAL_JEWISH_ADD_GERESHAYIM));
    $hebEnd   = iconv('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($lastTs),  true, CAL_JEWISH_ADD_GERESHAYIM));
    // Day-of-week of first day (0=Sun)
    $firstDow = (int) date('w', $firstTs);
    // Find first and last actual date keys in this block
    $dateKeys = array_keys($days);
    $lastDayInMonth = date('Y-m-t', $firstTs);
@endphp

<div class="month-block page-break">
    <div class="month-header">
        <span class="month-name">{{ $calMonth }} {{ $calYear }}</span>
        <span class="heb-range">{{ $hebEnd }} &mdash; {{ $hebStart }}</span>
    </div>

    <table class="cal-table">
        <thead>
            <tr>
                <th>Sunday</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Shabbos</th>
            </tr>
        </thead>
        <tbody>
        @php
            $count  = 0;
            $inRow  = false;
            echo '<tr>';
            $inRow = true;
            // Fill leading empty cells
            for ($x = 0; $x < $firstDow; $x++) {
                echo '<td class="spacer"></td>';
            }
        @endphp

        @foreach($days as $date => $day)
        @php
            $ts  = strtotime($date);
            $dow = (int) date('w', $ts);
            // Only show days within this month (plus the overflow days already in $days)
            $isThisMonth = (date('Y-m', $ts) === date('Y-m', $firstTs));
            $grayClass   = $isThisMonth ? '' : ' gray';
            $isShabbos   = strpos($day['display_date'] ?? '', 'Shabbos') !== false;
            $tdClass     = $isShabbos ? 'shabbos' : '';
        @endphp
        <td class="{{ $tdClass }}">
            <div class="day-top">
                <span class="day-num{{ $grayClass ? ' gray' : '' }}">{{ date('j', $ts) }}</span>
                <span class="day-sun-set">
                    {{ $day['sunrise'] ?? '' }}<br>{{ $day['sunset'] ?? '' }}
                </span>
            </div>
            <div class="day-body">
                @if(!empty($day['parsha']))
                    <div class="parsha">{{ $day['parsha'] }}</div>
                @endif
                @if(!empty($day['title']))
                    <div class="title">{!! $day['title'] !!}</div>
                @endif
                @if(!empty($day['chatzos_halaila']))
                    <div>Chatzos: {{ $day['chatzos_halaila'] }}</div>
                @endif
                @if(!empty($day['fast_starts']))
                    <div>Fast Starts: {{ $day['fast_starts'] }}</div>
                @endif
                <div>
                    @if(!empty($day['selichos']) && $day['selichos'] === 'y')
                        Selichos/Shacharis:
                    @else
                        Shacharis:
                    @endif
                    {{ $day['shacharis'] ?? '' }}
                </div>
                @if(!empty($day['megilla2']))
                    <div><b>Megilla</b>: {{ $day['megilla2'] }}</div>
                @endif
                @if(!empty($day['eat_by']))
                    <div>Eat Chametz Until: {{ $day['eat_by'] }}</div>
                @endif
                @if(!empty($day['burn_by']))
                    <div>Destroy Chametz By: {{ $day['burn_by'] }}</div>
                @endif
                @if(!empty($day['chatzos']))
                    <div>Chatzos: {{ $day['chatzos'] }}</div>
                @endif
                @if(!empty($day['drasha']))
                    <div>Drasha: {{ $day['drasha'] }}</div>
                @endif
                @php
                    $showMincha = ($day['candlelighting'] ?? '') !== ($day['mincha'] ?? '') && !empty($day['mincha'] ?? $day['shacharis'] ?? null);
                    $minchaTime = !empty($day['sel_mincha']) ? $day['sel_mincha'] : ($day['mincha'] ?? '');
                @endphp
                @if($showMincha && !empty($minchaTime))
                    <div>Mincha: {{ $minchaTime }}</div>
                @endif
                @if(!empty($day['fast_starts_night']))
                    <div>Fast Starts: {{ $day['fast_starts_night'] }}</div>
                @endif
                @if(!empty($day['maariv']))
                    <div>Maariv: {{ $day['maariv'] }}</div>
                @endif
                @if(!empty($day['candlelighting']))
                    @if(($day['candlelighting'] ?? '') !== ($day['mincha'] ?? ''))
                        <div>Candles: {{ $day['candlelighting'] }}</div>
                    @else
                        <div>Candles/Mincha: {{ $day['candlelighting'] }}</div>
                    @endif
                @endif
                @if(!empty($day['kol_nidre']))
                    <div>Kol Nidre: {{ $day['kol_nidre'] }}</div>
                @endif
                @if(!empty($day['havdala']))
                    @if(!empty($day['shabbos_ends_ind']) && $day['shabbos_ends_ind'] === 'y')
                        <div>Shabbos Ends: {{ $day['havdala'] }}</div>
                    @else
                        <div>Havdala: {{ $day['havdala'] }}</div>
                    @endif
                @endif
                @if(!empty($day['fast_ends']))
                    <div>Fast Ends: {{ $day['fast_ends'] }}</div>
                @endif
                @if(!empty($day['megilla1']))
                    <div><b>Megilla</b>: {{ $day['megilla1'] }}</div>
                @endif
            </div>
            <div class="day-footer">
                @if(!empty($day['sefira']))
                    <div class="sefira">Sefiras Ha'Omer {{ $day['sefira'] }}</div>
                @endif
                <div class="day-heb">{{ $day['hebrew_date'] ?? '' }}</div>
                @if(!empty($day['dafyomi']))
                    <div class="day-daf">{{ $day['dafyomi'] }}</div>
                @endif
            </div>
        </td>

        @php
            if ($isShabbos) {
                echo '</tr><tr>';
            }
            $count++;
        @endphp
        @endforeach

        @php
            // Close final row with padding cells
            if ($count > 0) {
                $lastDow = (int) date('w', strtotime(array_key_last($days)));
                if ($lastDow < 6) {
                    for ($x = $lastDow + 1; $x <= 6; $x++) {
                        echo '<td class="spacer"></td>';
                    }
                }
                echo '</tr>';
            }
        @endphp
        </tbody>
    </table>
</div>
@endforeach

</body>
</html>
