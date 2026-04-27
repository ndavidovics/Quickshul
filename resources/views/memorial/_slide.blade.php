@php
    // Hebrew name font size — scales down for very long names
    function hebFontSize(string $name, int $baseSize): string {
        $len = mb_strlen($name);
        if ($len >= 35) return 'font-size:' . max(12, (int)($baseSize * 0.80)) . 'px;';
        if ($len >= 30) return 'font-size:' . max(12, (int)($baseSize * 0.90)) . 'px;';
        return 'font-size:' . $baseSize . 'px;';
    }
@endphp

@foreach($records as $y)
@php
    $isToday     = ($y->hebrew_month == $today['month']);
    $engDate     = $y->date_of_death?->format('M j, Y') ?? '';
    $plaqueStyle = 'background-image:url(/memorial/assets/indv_plaque.jpg);color:#000000;'
        . 'width:' . $plaqueWidth . 'px;height:' . $plaqueHeight . 'px;';
    $nameStyle   = 'font-size:' . $nameSize . 'px;line-height:' . $nameSize . 'px;margin-top:' . $nameMargin . 'px;';
    $hebStyle    = hebFontSize($y->hebrew_name ?? '', $hebSize);
    $smengStyle  = 'font-size:' . $smengSize . 'px;';
    $smhebStyle  = 'font-size:' . $smhebSize . 'px;';
@endphp
<div class="plaque{{ $isToday ? ' yarmem' : '' }}" style="{{ $plaqueStyle }}">
    <p class="name" style="{{ $nameStyle }}">
        @if($y->hebrew_name)
        <span class="heb" style="font-family:'WE Siddur Reg';{{ $hebStyle }}">{{ $y->hebrew_name }}</span>
        <br />
        @endif
        <span style="font-family:Times;">{{ $y->full_name }}</span>
        <br />
        <span class="smeng" style="font-family:Times;{{ $smengStyle }}">{{ $engDate }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="smheb heb" style="font-family:'WE Siddur Reg';{{ $smhebStyle }}">{{ $y->hebrew_date_of_death }}</span>
        </span>
    </p>
</div>
@endforeach
<br clear="all"/>
