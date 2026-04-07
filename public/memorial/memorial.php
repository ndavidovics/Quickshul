<?php 
// $html = file_get_contents('https://yimemp.joltone.com/screen/');
// //$html = str_replace('load_slide.php','https://yimemp.joltone.com/screen/load_slide.php',$html);

// $html = str_replace('img/','https://yimemp.joltone.com/screen/img/',$html);
// $html = str_replace('<a href="search.php" class="goldbtn homesearch" style="margin-top:900px;">search</a>','',$html);
// $html = str_replace('../','https://yimemp.joltone.com/',$html);
// $html = str_replace('<title>Memorial Board</title>','<title>Memorial Board</title>
// <style> .yarmem {
//     border: 7px solid yellow !Important;
// }
// </style>',$html);

// echo $html;
?>


<!--2022 08 17 15:385782-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  

<META >
<title>Memorial Board</title>
<style> .yarmem {
    border: 7px solid yellow !Important;
}
</style>
<link rel="stylesheet" type="text/css" href="assets/screen.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body  >
<div id="main_box">
<div id="left_candle" style="background-image:url(assets/candle.gif);
    background-size: cover;  
    color: #E8CB8A;
    display: flex;
    font-size: 40px;
    justify-content: center;
    align-items: flex-end;     direction: rtl;  font-family:WE Siddur Reg; 

    ">
	<?php $data = file_get_contents("https://www.torahcalc.com/api/dailylearning");
		$data = json_decode($data,true);
		echo $data['data']['mishnaYomi']['hebrewName'];
		?>
</div>

<div id="right_side"> 

</div>

</div>

<script>


 

	

function events_update(){	
	var  arr = [  "load_slide.php?num=1&mode=yizkor&limita=0&limitb=44&rand=1001375",  "load_slide.php?num=2&mode=yizkor&limita=44&limitb=44&rand=1054792", "load_slide.php?num=3&mode=yizkor&limita=44&limitb=44&rand=1054632"];
	//console.log("Loop Start"); 
	$.each(arr, function(index, value) {
		var delaytime = 0;
		if(index >= 1){delaytime = 1;}
			$("#right_side").delay(8000 * delaytime).queue(function( nxt ) {
			$(this).load(value);
			 //console.log("8000 * index("+index+")"+8000 * index +" // Value = "+ value +
			 //" // arr.length = "+ arr.length);
			nxt();

		});
	});
		  
	 
} 
	
$(document).ready(function(){
  events_update();
  var  arr = [  "load_slide.php?num=1&mode=yizkor&limita=0&limitb=44&rand=1001375",  "load_slide.php?num=2&mode=yizkor&limita=44&limitb=44&rand=1054792"];
  setInterval(function(){ events_update();}, 8000 * (arr.length + 1));
  
	
});
 
</script>
</body>
</html>
