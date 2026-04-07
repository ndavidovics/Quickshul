<?php
echo 'Loading...';
$full_out = '{"items":[';
for($x = 2019; $x < 2035; $x++) {
    $year = json_decode(file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=$x&month=&ss=on&mf=on&s=on&i=off&lg=a&F=on"));
    foreach($year->items as $key => $item) {
        unset($year->items[$key]->leyning);
        unset($year->items[$key]->link);
        unset($year->items[$key]->memo);
    }
    $full_out .= substr(substr(json_encode($year->items),0,-1),1).',';
    //$full_out = array_combine($full_out,$year->items);
}
//echo json_encode($full_out);
$full_out = substr($full_out, 0, -1);
$full_out .= ']}';
echo $full_out;
file_put_contents('hebcal.json',$full_out);
echo ' Done!'
?>
