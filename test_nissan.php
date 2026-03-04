<?php
// Find Nissan 11 in 1979
echo "Testing March 1979 dates to find Nissan 11:\n";
for ($day = 1; $day <= 31; $day++) {
    $jd = gregoriantojd(3, $day, 1979);
    $result = jdtojewish($jd);
    list($m, $d, $y) = explode('/', $result);
    $m = (int)$m;
    $d = (int)$d;
    if ($d === 11 && $m === 7) {
        echo "March $day, 1979 -> Day $d, Month $m (Nissan), Year $y <-- FOUND IT!\n";
    }
}
