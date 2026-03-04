<?php
// Test various April dates to see which one is Nissan 11
$months_leap = [1=>'Tishrei', 2=>'Cheshvan', 3=>'Kislev', 4=>'Tevet', 5=>'Shevat', 6=>'Adar I', 7=>'Adar II', 8=>'Nisan', 9=>'Iyar', 10=>'Sivan'];
$months_regular = [1=>'Tishrei', 2=>'Cheshvan', 3=>'Kislev', 4=>'Tevet', 5=>'Shevat', 6=>'Adar', 7=>'Nisan', 8=>'Iyar', 9=>'Sivan'];

echo "Testing April 1979 dates:\n";
for ($day = 1; $day <= 30; $day++) {
    $jd = gregoriantojd(4, $day, 1979);
    $result = jdtojewish($jd);
    list($m, $d, $y) = explode('/', $result);
    $m = (int)$m;
    $d = (int)$d;
    
    $isLeap = ((7 * $y + 1) % 19) < 7;
    $monthArray = $isLeap ? $months_leap : $months_regular;
    $month_name = $monthArray[$m] ?? "Month $m";
    
    echo "April $day, 1979 -> $d $month_name, $y";
    if ($d === 11 && str_contains($month_name, 'Nissan')) {
        echo " <-- FOUND IT!";
    }
    echo "\n";
}
