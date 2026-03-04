<?php
// Test Hebrew date conversion for April 8, 1979
$jd = gregoriantojd(4, 8, 1979);
$result = jdtojewish($jd);
echo "April 8, 1979 converts to: " . $result . PHP_EOL;
list($m, $d, $y) = explode('/', $result);
echo "Month: " . $m . ", Day: " . $d . ", Year: " . $y . PHP_EOL;

// Check months  
$months_leap = [1=>'Tishrei', 2=>'Cheshvan', 3=>'Kislev', 4=>'Tevet', 5=>'Shevat', 6=>'Adar I', 7=>'Adar II', 8=>'Nisan', 9=>'Iyar', 10=>'Sivan', 11=>'Tammuz', 12=>'Av', 13=>'Elul'];
$months_regular = [1=>'Tishrei', 2=>'Cheshvan', 3=>'Kislev', 4=>'Tevet', 5=>'Shevat', 6=>'Adar', 7=>'Nisan', 8=>'Iyar', 9=>'Sivan', 10=>'Tammuz', 11=>'Av', 12=>'Elul'];

// Check if year is leap
$isLeap = ((7 * (int)$y + 1) % 19) < 7;
echo "Year " . $y . " is leap: " . ($isLeap ? 'yes' : 'no') . PHP_EOL;
$monthArray = $isLeap ? $months_leap : $months_regular;
echo "Month name: " . $monthArray[(int)$m] . PHP_EOL;

echo "\nNow let's test current year (5786):\n";
$currentYear = 5786;
$currentIsLeap = ((7 * $currentYear + 1) % 19) < 7;
echo "Year " . $currentYear . " is leap: " . ($currentIsLeap ? 'yes' : 'no') . PHP_EOL;

// Try to find Nissan 11 in 5786
echo "\nTrying to convert Nissan 11, 5786 back to Gregorian:\n";

// In 5739 (leap), Nissan is month 8. In 5786 (non-leap), Nissan is month 7.
// So we need month 7 for 5786
$jd2 = jewishtojd(7, 11, 5786);
$greg = jdtogregorian($jd2);
echo "Nissan 11, 5786 -> " . $greg . PHP_EOL;

// Also try Iyar 11, 5786
$jd3 = jewishtojd(8, 11, 5786);
$greg2 = jdtogregorian($jd3);
echo "Iyar 11, 5786 -> " . $greg2 . PHP_EOL;

// What about if we use month 9 (Iyar in leap)?
$jd4 = jewishtojd(9, 11, 5786);
$greg3 = jdtogregorian($jd4);
echo "Month 9, day 11, 5786 -> " . $greg3 . PHP_EOL;
