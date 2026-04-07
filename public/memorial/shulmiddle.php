<?php
// header('Location: https://www.yiom.org/shavuos');
// die();
date_default_timezone_set('America/Chicago');

$tz = new DateTimeZone('America/Chicago');
$now = new DateTime('now', $tz);

// Get numeric representations
$dayOfWeek = (int) $now->format('w'); // 0 = Sunday, 5 = Friday
$time      = $now->format('H:i');

// Define schedule boundaries
$isInWindow = false;

if ($dayOfWeek === 0) {
    // Sunday: after 2:00 AM
    if ($time <= '02:00') {
        $isInWindow = true;
    }
} elseif ($dayOfWeek >= 1 && $dayOfWeek <= 4) {
    // Monday–Thursday: always in window
    $isInWindow = false;
} elseif ($dayOfWeek === 5) {
    // Friday: before 11:00 AM
    if ($time > '11:00') {
        $isInWindow = true;
    }
}

if ($isInWindow) {
    header('Location: https://www.yiom.org/announcements', true, 302);
    exit;
}

// Optional: fallback behavior
// echo "Outside scheduled redirect window.";

	function convertNumberToHebrew($text) {
		
		
		// split off daf
		$lastChar = strtolower( $text { strlen( $text ) - 1 } );
		if ( $lastChar == 'a' or $lastChar == 'b' ) {
			$daf = $lastChar;
		}
	
		$number = intval( $text );
		$output = "";
		
		// Do thousands
		$thousands = calcOnes( intval( $number / 1000 ) % 10 );
		if ( $thousands != null )
			$output .= $thousands . unichr( hexdec( "5F3" ) );
		
		// Do hundreds
		$hundreds = calcHundreds( intval( $number / 100 ) % 10 );
		$output .= $hundreds;
		
		// fix exceptions
		if ( $number % 100 == 15 ) {
			$ones = calcOnes( 9 );
			$output .= $ones;
			$ones = calcOnes( 6 );
			$output .= $ones;
		}
		elseif ( $number % 100 == 16 ) {
			$ones = calcOnes( 9 );
			$output .= $ones;
			$ones = calcOnes( 7 );
			$output .= $ones;
		}
		else {
			// Do tens
			$tens = calcTens( intval( $number / 10 ) % 10 );
			$output .= $tens;
	
			// Do ones
			$ones = calcOnes( intval( $number ) % 10 );
			$output .= $ones;
		}
		// Add dot or apostrophe
		if ( $daf == 'a' ) {
				$output .= '.'; // This is really the wrong symbol.
		} elseif ( $daf == 'b' ) {
				$output .= unichr( hexdec( "5C3" ) );
		} elseif ( strlen( $output ) > 1 ) {
				//$output = substr( $output, 0, - 1 ) . unichr( hexdec( "5F4" ) ) . substr( $output, - 1, strlen( $output ) + 1 );
		}	elseif ( strlen( $output ) == 1 ) {
				$output .= ' '.unichr( hexdec( "5F3" ) );
		}
// 		
		return $output;
	}

	function calcHundreds( $digit ) {
		if ( $digit != null ) {
			$output = "";
			while ( $digit >= 4 ) {
				$output .= unichr( hexdec( "5EA" ) );
				$digit -= 4;
			}
			// add number to numerical value for unicode character before "kuf"
			if ( $digit > 0 )
				$output .= unichr( $digit + 1510 );
			
			return $output;
		}
	}
	
	function calcTens( $digit ) {
		if ( $digit != null ) 
		{
			// store the unicode value in decimal of hebrew representation for tens
			$tensUnicodes = array( "5D9", "5DB", "5DC", "5DE", "5E0", "5E1", "5E2", "5E4", "5E6" );
			return unichr( hexdec( $tensUnicodes[$digit - 1] ) ). unichr( hexdec( "5F4" ) ) ;
		}
	}
	
	function calcOnes( $digit ) {
		if ( $digit != null )
			// add number to numerical value for unicode character before "aleph"
			return unichr( $digit + 1487 );
	}
	
	function unichr( $unicode ) {
		return '&#' . $unicode . ';';
		//return htmlspecialchars_decode(utf8_decode(htmlentities('&#' . $unicode . ';', ENT_COMPAT, 'utf-8', false)));
		//return mb_convert_encoding( '&#' . $unicode . ';', 'UTF-8', 'HTML-ENTITIES' );
	}

	function get_this_shabbos($json)
 	{
 		$shabbos_date = date("Y-m-d", strtotime("this Saturday"));
		$next_shabbos_date = date("Y-m-d", strtotime("this Saturday")+(60*60*24*7));
		$two_shabbos_date = date("Y-m-d", strtotime("this Saturday")+(60*60*24*14));
		
		$three_shabbos_date = date("Y-m-d", strtotime("this Saturday")+(60*60*24*21));
		
 		foreach($json->items as $event)
		{
			if(($event->date == $shabbos_date ||$event->date == $next_shabbos_date ||$event->date == $two_shabbos_date||$event->date == $three_shabbos_date) && $event->category == 'parashat') {
				$fontsize = '';
				if (strlen($event->hebrew) > 30) {
					$fontsize = 'font-size:152px;';
				}
				$hebdate = explode('/',jdtojewish(unixtojd(time()-(60*60*10))));
				if ($event->hebrew == 'פרשת בראשית' && $hebdate[0] == 1 && $hebdate[1] < 23) {
					$event->hebrew = 'פרשת וזאת הברכה';
				}
				return '<li lang="he" style="white-space:nowrap; '.$fontsize.'">'.$event->hebrew.'</li>';
			}
		}
 	}
	
	function get_today($json)
 	{
 		if (get_sunset(time()) > time()) {
 			$today = date("Y-m-d");
		} else {
			$today = date("Y-m-d",strtotime('tomorrow'));
		}
 		foreach($json->items as $event)
		{
			if (strpos($event->title,'Chanukah') !== FALSE && strpos($event->title,'Candles')) {
				$today = date("Y-m-d");
				$event->hebrew .= ' בלילה';
			}
			if($event->date == $today && $event->hebrew) {
				$fontsize = '';
				if (strlen($event->hebrew) > 30) {
					$fontsize = 'font-size:122px;';
				}
				$events .= '<li lang="he" style="display:none;'.$fontsize.'">'.str_replace(array('(',')'),'',$event->hebrew).'</li>';
				if (get_sunset(time()) > time()) {
 					$today = date("Y-m-d");
				} else {
					$today = date("Y-m-d",strtotime('tomorrow'));
				}
			}
			if ($event->title == 'Pesach II') {
				$start = $event->date;
				if (get_sunset(time()) > time()) {
		 			$now = time();
				} else {
					$now = strtotime('tomorrow');
				}
				//$now = time(); // or your date as well
			     $your_date = strtotime($start);
			     $datediff = $now - $your_date;
			     $sefira =  floor($datediff/(60*60*24)) + 1;
				 if ($sefira > 0 && $sefira < 50) {
				 	$events .= '<li lang="he" style="display:none;"><span>'.convertNumberToHebrew($sefira).'</span> <span>לעומר  </span></li>';
				 }
			}
			$yesterday = $event;

		}
		return $events;
 	}
	
	function get_daf($json)
 	{
 		if (get_tzeis(time()) > time()) {
 			$today = date("Y-m-d");
		} else {
			$today = date("Y-m-d",strtotime('tomorrow'));
		}
		
 		foreach($json->items as $event)
		{
			if($event->date == $today && $event->category == 'dafyomi') {
				
				return $event->title;
			}
		}
 	}
	
	function google_edit($id) {
		$html =  '<span class="google_doc_class">';
		$page = str_replace('type="text/css">','type="text/css"> .google_doc_class ',str_replace('}', '} .google_doc_class ', file_get_contents('https://docs.google.com/feeds/download/documents/export/Export?id='.$id.'&exportFormat=html')));
		//$page = explode('</title>',$page);
		$html .= strip_tags($page,'<a><p><body><span><div><ul><ol><li><style><img>');
		$html .= '</span>';
		return $html;
	}
	
	$nodisplay = 1;
	include('minyan_times.php');
	
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Shul Display</title>
    <!-- <link href='https://fonts.googleapis.com/css?family=Tinos:400&subset=hebrew,latin' rel='stylesheet' type='text/css'> -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Cinzel:480,700' rel='stylesheet' type='text/css'> -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Merriweather' rel='stylesheet' type='text/css'> -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Marcellus+SC' rel='stylesheet' type='text/css'> -->
    <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
	<link href='https://fonts.googleapis.com/css?family=Cinzel' rel='stylesheet'>
 <style>
    	
    	body {
    		font-family: 'Cinzel';
    		/*font-family: 'Marcellus SC', serif;*/
    		font-size: 60px;
    		padding: 0 0 0 0;
    		margin: 0 0 0 0;
    		/*text-shadow: 0px 2px 0px rgba(255,255,255,.3), 0px -2px 0px rgba(0,0,0,.7);*/
    		overflow:hidden;
    		/*color: #aaa;*/
    	}
		.backgroundImage {
    background-image: url('bg-middle.png');
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size:  cover;
    background-repeat: no-repeat;
    width: 100%;
    height: 100vh;
}
    	div {
    		width:100%;
    		text-align:center;
    	}
    	
    	hr {
    		height:6px;
    	}
    	
		/*#top_line::before {
			content: "";
			position: absolute;
			top: 0; 
			left: 0;
			width: 100%; 
			z-index: -1;
			opacity: 0.5;
			height: 100%;
			background-image: url('/images/marble.jpg');
	        -webkit-background-size: cover;
	        -moz-background-size: cover;
	        -o-background-size: cover;
	        background-size: cover;
			background-color:rgba(0, 0, 0, 0.5);
		}*/

    	#top_line {
    		font-size:110px;
			font-weight: bold;
    	}
    	#top_line span:lang(he) {
    		font-size: 155px;
    		direction: rtl;
			font-weight: normal;
    	}
    	#parsha {
    		font-size:120px;
    	}
    	#parsha li:lang(he) {
    		font-size: 160px;
			font-weight: normal;
    	}
    	#daven {
    		font-size: 70px;
			font-weight: bold;
    	}
    	#daven span:lang(he) {
    		font-size: 100px;
			font-weight: normal;
    	}
		#daven_tom {
    		font-size: 57px;
			font-weight: bold;
    	}
    	#daven_tom span:lang(he) {
    		font-size: 80px;
			font-weight: normal;
    	}
    	#zmanim {
    		font-size: 60px;
			font-weight: bold;
    	}
    	#zmanim span:lang(he) {
    		font-size: 78px; 
			font-weight: normal;
    	}
    </style>
    
  </head>
  <body class="backgroundImage" style="letter-spacing: -4px;">
  <div style="margin-top:40px;"><b><h2 style="margin-bottom: -100px;">Welcome to the</h2><h1 style="font-size:97px;">Young Israel of Memphis</h1></b>
	</div>
  <div><span style="font-size:50px;">
  ​Rabbi Akiva Males, Rabbi <br/>
  ​Rabbi Uriel Nashofer, Assistant Rabbi <br/>
Noam Davidovics, President<br/>
Sarah Bauer, Executive Director<br/>
	</span>
	</div>
	<br/>
  	<div id="top_line" ><?php echo date('M j, Y');?>&nbsp;&nbsp;<span lang="el" id="time"></span><br/><span lang="he" dir="rtl">
  		
    	<?php 
    	$heb_day =  (get_tzeis(time())-(90*60)) > time() ? time() : strtotime('tomorrow');
    	echo str_replace('הת','ת', iconv ('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($heb_day ),true,CAL_JEWISH_ADD_GERESHAYIM)));?>
    	</span></div>
    	<hr/>
    	<?php
    	$json = json_decode(file_get_contents("hebcal.json"));
    	$shabbos = get_this_shabbos($json);
    	?>
    	<div id="parsha" ><ul id="scroll" style="
    height: 80px;
    margin-top: -40px;
"><?php echo $shabbos;?>
    	<?php
    	$json = json_decode(file_get_contents("hebcal.json"));
		
		$date = date('Y-m-d');
		$tomorrow = date('Y-m-d',strtotime('tomorrow'));
    	$today = get_today($json);
    	echo $today;
		?>
		</ul>
    	</div>
    	<hr/>
    	<div id="daven" style="white-space: nowrap;"><span lang="he"></span><span lang="he">שחרית:</span><?=$info[$date]['shacharis'] ?>&nbsp;&nbsp;
      <span lang="he">מנחה:</span><?php echo $info[$date]['sel_mincha'] ? $info[$date]['sel_mincha'] : $info[$date]['mincha'] ?>&nbsp;&nbsp;
	  <?php if ($info[$date]['maariv']) { ?>
	  <span lang="he">מעריב:</span><?=$info[$date]['maariv'] ?>
       <?php } ?>
       
       
       
       <?php echo $info[$date]['candlelighting'] ? '<span lang="he">נרות</span>:'. $info[$date]['candlelighting'].' &nbsp;' : ''; ?>
       <?php echo $info[$date]['havdala'] ? '<span lang="he">הבדלה</span>:'. $info[$date]['havdala'].'' : ''; ?>
       <?php echo $info[$date]['fast_ends'] ? '<br/>Fast Ends:'. $info[$date]['fast_ends'].'' : ''; ?>
       <br/><div style="height:20px;"></div>
       <div style="text-align:center; margin: auto; font-size:40px;border: 4px solid black; padding: 4px; width:270px">TOMORROW</div>
	   <span id="daven_tom">
      <span lang="he">שחרית:</span><?=$info[$tomorrow]['shacharis'] ?>&nbsp;&nbsp;
      <span lang="he">מנחה:</span><?=$info[$tomorrow]['mincha'] ?>&nbsp;&nbsp;
       <?php if ($info[$tomorrow]['maariv']) { ?>
       	<span lang="he">מעריב:</span><?=$info[$tomorrow]['maariv'] ?>
		   <?php } ?>
       <?php echo $info[$tomorrow]['candlelighting'] ? '<span lang="he">נרות</span>:'. $info[$tomorrow]['candlelighting'].' &nbsp; ' : ''; ?>
       <?php echo $info[$tomorrow]['havdala'] ? '<span lang="he">הבדלה</span>:'. $info[$tomorrow]['havdala'].'' : ''; ?>
	   </span>
    	</div>
    	<hr/>
    	<div id="zmanim">
       <span lang="he">עלות:</span><?=date('g:i',get_alos($heb_day));?>&nbsp;
       <span lang="he">טלית:</span><?=date('g:i',get_misheyakir($heb_day));?>&nbsp;
       <span lang="he">הנץ:</span><?=date('g:i',get_sunrise($heb_day)) ?>&nbsp;
	   <span lang="he">מ"א:</span><?=date('g:i',get_ma_by_hour($heb_day,3)) ?>&nbsp;
       <span lang="he">שמע:</span><?=date('g:i',get_by_hour($heb_day,3)) ?><br/> 
	   <span lang="he">תפילה:</span><?=date('g:i',get_by_hour($heb_day,4)) ?> &nbsp;
       <span lang="he">חצות:</span><?=date('g:i',get_by_hour($heb_day,6)) ?> &nbsp;
       <span lang="he">מנחה גד':</span><?=date('g:i',get_by_hour($heb_day,6.5)) ?><br/>
       
       <span lang="he">פלג:</span><?=date('g:i',get_by_hour($heb_day,10.75)) ?> &nbsp;
       <span lang="he">שקיעה:</span><?=date('g:i',get_sunset($heb_day)) ?> &nbsp;
       <span lang="he">צאת:</span><?=date('g:i',get_tzeis($heb_day)) ?> &nbsp;
       <span lang="he">ר"ת:</span><?=date('g:i',get_rttzeis($heb_day)) ?></div>
    	<hr/>
    	<div style="font-family: Georgia; font-size: 90px;">
    		<?php
    		$json_this_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=now&month=".date('m')."&ss=on&mf=on&s=on&i=off&lg=h&F=on");
			$this_month = json_decode($json_this_month);
			echo str_replace('דף יומי: ','',get_daf($this_month));
    		?>
    	</div>
    	<hr/>
    	<div>
    		<?php
    		// $html = google_edit('1yrigsG4IeVwv5wM_znlPNccTux_cLEQVr1NswL-LON0');
			// $body = explode('</style>',$html);
			//echo $html;
			// $body = strip_tags($body[1]);
			// $test = file_get_contents('display.txt');
			// if ($body != $test) {
			// 	file_put_contents('display.txt',$body);
			// 	file_put_contents('display_date.txt',time());
			// }
			// $test_date = file_get_contents('display_date.txt');
    		// if ($body &&  $test_date > strtotime('-7 days')) {
    		// 	echo $html;
    		// } else {
    		// 	echo '<img style="opacity:.5" src="images/YIM_logo_small.jpg"/>';
			// }
    		?>
			<!-- <img src="YIM_logo_small.jpg"/> -->
			<!-- Kiddush  is sponsored by the Kahane Family in honor of Sarah's Bas Mitzvah<br/>
Welcome to all family and friends who have come to celebrate -->
    	</div>
  
  </body>
  <script>
	function startTime() {
	    var today = new Date();
	    var h = today.getHours();
	    var m = today.getMinutes();
	    //var s = today.getSeconds();
	    m = checkTime(m);
	    //s = checkTime(s);
	    var h = h%24; 
	    var mid='AM';
	    if(h==0){ //At 00 hours we need to show 12 am
	    	h=12;
	    } else if (h == 12) {
	    	mid = 'PM';
	    }
	    	else if(h>12)
	    {
		    h=h%12;
		    mid='PM';
	    }
	    document.getElementById('time').innerHTML = h + ":" + m + " " + mid;
	    var t = setTimeout(startTime, 1000);
	}
	function checkTime(i) {
	    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
	    return i;
	}
	startTime();
	
	function check_connect(waitTime)
	{
	    var testURL = "/robots.txt?test=" + Math.random();
	    //var waitTime = 12000000  // recheck every 15 seconds
	
	    $.ajax({
	      type: 'GET',
	      url: testURL,
	      timeout: 10000,  // allow this many milisecs for network connect to succeed
	      success: function(data) {
	        // we have a connection, reload page then try again after waitTime
			location.reload();  // reloads entire page
			//console.log('connected');
	        //window.setTimeout(check_connect, waitTime)  // try again after waitTime miliseconds
	      },
	      error: function(XMLHttpRequest, textStatus, errorThrown) {
	        // no connection, try again after waitTime
			//window.setTimeout(check_connect, (waitTime/2)) 
			console.log('disconnected');
	      }
	      });
	  }
	  
	  window.setInterval(check_connect, 1000*60*25);
	  
	  	setInterval(show_next,10000);
	  function show_next() {
	  	if($('#scroll>li:visible').next().length) {
	  		var el = $('#scroll>li:visible').next();
	  		$('#scroll>li').hide();
	  		el.css( "display", "list-item")
	  	} else {
	  		$('#scroll>li').hide();
	  		$('#scroll>li').first().show();
	  	}
	  }
	</script>
</html>