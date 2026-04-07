@php
    // Hebrew name font size — matches the original's sizing by character length
    function hebFontSize(string $name): string {
        $len = mb_strlen($name);
        if ($len >= 61) return 'font-size:24px;';
        if ($len >= 54) return 'font-size:26px;';
        if ($len >= 46) return 'font-size:28px;';
        return '';
    }
@endphp

@foreach($records as $y)
@php
    $isToday   = ($y->hebrew_month == $today['month']);
    $hebStyle  = hebFontSize($y->hebrew_name ?? '');
    $engDate   = $y->date_of_death?->format('M j, Y') ?? '';
@endphp
<div class="plaque {{ $sizeClass }}{{ $isToday ? ' yarmem' : '' }}"
     style="background-image:url(/memorial/assets/indv_plaque.jpg);color:#000000">
    <p class="name">
        @if($y->hebrew_name)
        <span class="heb" style="font-family:'WE Siddur Reg';{{ $hebStyle }}">{{ $y->hebrew_name }}</span>
        <br />
        @endif
        <span style="font-family:Times;">{{ $y->full_name }}</span>
        <br />
        <span class="smeng" style="font-family:Times;">{{ $engDate }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="smheb heb" style="font-family:'WE Siddur Reg';">{{ $y->hebrew_date_of_death }}</span>
        </span>
    </p>
</div>
@endforeach
<br clear="all"/>

<script>
$('a').hover(function(){ $(this).attr('title', ' '); });
$('a').click(function(){ $(this).attr('title', ' '); });
$('body').attr('unselectable', 'on')
    .css('user-select', 'none')
    .on('selectstart', false);
$(document).on('contextmenu', function(e){ e.preventDefault(); });
</script>
