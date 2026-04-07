<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$html = file_get_contents('load_slide'.$_GET['num'].'.php'); //?mode='.$_GET['mode'].'&limita='.$_GET['limita'].'&limitb='.$_GET['limitb'].'&rand='.$_GET['rand']);
//echo 'load_slide'.$_GET['num'].'.php?mode='.$_GET['mode'].'&limita='.$_GET['limita'].'&limitb='.$_GET['limitb'].'&rand='.$_GET['rand'];
// $html = str_replace('img/','https://yimemp.joltone.com/screen/img/',$html);
// $html = str_replace('<div style="float:right ; width:200px; position: absolute; right: 10px; bottom: 10px;"><img src="https://yimemp.joltone.com/screen/img/poweredby.png"    title="Powered By W&E Baum 800.922.7377"  /></div>','',$html);
$names = explode('<a href="profile.php?pid=',$html);
$new_html = '';
$jdate = mb_convert_encoding(jdtojewish(gregoriantojd( date('m'), date('d'), date('Y')), true, CAL_JEWISH_ADD_GERESHAYIM),"UTF-8", "ISO-8859-8");
$parse_m = explode('התש',$jdate);
$month_parts = explode(' ',$parse_m[0]);
// print_r($month_parts);
foreach($names as $name) {
    if (strpos($name,' '.$month_parts[1].' ')) {
        //print_r($month_parts);
        $name = str_replace('plaque ','plaque yarmem',$name);
        //$name = str_replace('</p>','<img src="https://png.pngtree.com/element_our/20200610/ourmid/pngtree-candle-flame-image_2244854.jpg" style="height:40px; position:relative; left:30px; bottom:20px;"/></p>', $name);
    }
    $new_html .= $name.'<a href="profile.php?pid=';
}

echo $new_html;
?>