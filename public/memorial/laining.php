<?php

/* questions
 * Lat and lon of location
 * How is shkiah calculated? WHat zenith?
 * what time is regular daily shachris?  Based on sunrise?
 * What time is regular daily mincha? Based on sunset? Same time all week but changes every week?
 * What time is regular daily maariv? Based on sunset? same time all week but changes every week?
 * How soon before shkiah is candlelighting for shabbos?
 * What time is Mincha before shabbos? Does it ever have a "latest" time?
 * What time is shachris on shabbos morning?
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */

header('Content-type: text/html; charset=utf-8'); 
//first set the timezone that PHP will use
date_default_timezone_set('America/Chicago');

$number_of_days_to_show = 450;
//show all the dates from today, until seven days from now
$day_end = ($number_of_days_to_show*60*60*24)+time(); 
$cal_month = $_GET['month'] ? $_GET['month'] : date('F');
$cal_year = $_GET['year'] ? $_GET['year'] : date('Y');
for($day = strtotime($cal_month." 1 ".$cal_year.' 9 AM'); $day < $day_end; $day += (60*60*24))
{
		$date = date('Y-m-d',$day);
		$info[$date]['display_date'] = date('l F j, Y',$day);
		$info[$date]['hebrew_date'] = iconv ('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($day),true,CAL_JEWISH_ADD_GERESHAYIM));
		$info[$date]['sunset'] = date('g:i',get_sunset($day));
		$info[$date]['sunrise'] = date('g:i',get_sunrise($day));
		if (date('D',$day) == 'Fri') {
			$timestamp	= $day;
			
			$shkiah_timestamp = get_sunset($timestamp);
						
			$info = erev_chag($info,$date);
			$info[$date]['shacharis'] = '6:35';
			//get sunset of next week, and see which sunset is earlier
			$min_sunset = min(strtotime('-1 day',get_sunset(strtotime('+1 day',$timestamp))),strtotime('-5 days',get_sunset(strtotime('+5 days',$timestamp))));
			//echo date('g:i',$min_sunset).' ';
			// then subtract 10 minutes from the earlier one, and round to the nearest 5 minute mark
			$mincha_this_week = (5*60)*(round((strtotime('-10 minutes',$min_sunset)/(5*60))));
			if ($mincha_this_week > ($min_sunset - (60*10))) { // if there is less than a 10 minute gap, add five minutes
				$mincha_this_week -= 5*60;
			}
			$mincha_this_week = date('g:i',$mincha_this_week);
			$day_timestamp = strtotime('+1 day',$timestamp);;
			for($day_x = 0; $day_x < 5;$day_x++)
			{
				$day_timestamp = strtotime('+1 day',$day_timestamp);
				$info[date('Y-m-d',$day_timestamp)]['mincha'] = $mincha_this_week; 
			}

			//get sunset of last week, and see which sunset is earlier
			$min_sunset = min(strtotime('+1 day',get_sunset(strtotime('-1 day',$timestamp))),strtotime('+5 days',get_sunset(strtotime('-5 days',$timestamp))));
			// then subtract 10 minutes from the earlier one, and round to the nearest 5 minute mark
			$mincha_last_week = (5*60)*(round((strtotime('-10 minutes',$min_sunset)/(5*60))));
			//echo $mincha_last_week.' '.($min_sunset-600) . ' | ';
			//echo date('g:i',$mincha_last_week).' '.date('g:i',$min_sunset).' | ';
			if ($mincha_last_week > ($min_sunset - (60*10))) { // if there is less than a 10 minute gap, add five minutes
				$mincha_last_week -= 5*60;
			}
			$mincha_last_week = date('g:i',$mincha_last_week);
			$day_timestamp = strtotime('-6 days',$timestamp);;
			for($day_x = 0; $day_x < 5;$day_x++)
			{
				//go to next day
				$day_timestamp = $day_timestamp+(24*60*60);
				$info[date('Y-m-d',$day_timestamp)]['mincha'] = $mincha_last_week; 
			}
			
		} else if (date('D',$day) == 'Sat') {
			//subtract 25 minutes from shkiah and round to the nearest 5 minute mark to get mincha time
			$shabbos_day = $day;
			$sunset = get_sunset($shabbos_day);
			$shabbos_mincha = strtotime('-25 minutes',$sunset);
			$shabbos_mincha = (5*60)*(round($shabbos_mincha/(5*60)));
			$shabbos_mincha = date('g:i',$shabbos_mincha);	
			
			$info[$date]['display_date'] = 'Shabbos '.date('F j, Y',$shabbos_day);
			if (date('gi',get_by_hour($day,3)) > 920) {
				$info[$date]['shacharis'] = '7, 8:45'; 
			} else {
				$shabbos_shacharis = date('g:i',(5*60)*(round(((get_by_hour($day,3)-(35*60))/(5*60)))));
				$info[$date]['shacharis'] = '7, '.$shabbos_shacharis; 
			}
			$info[$date]['mincha'] = $shabbos_mincha; 
			$havdala = date('g:i',strtotime('+50 minutes',$sunset));
			$info[$date]['havdala'] = $havdala;
		} else {
			// if it is a weekday
			// maariv is 15 minutes after shkiah
			// shacharis is 8 on sundays, 6:35 on weekdays
			$sunset = get_sunset($day);
			$info[$date]['maariv'] = date('g:i',strtotime('+15 minutes',$sunset));
			$info[$date]['shacharis'] = date('D',$day) == 'Sun' ? '8:00' : '6:35';
		} 
}

//get list of legal holidays for the year
//$holidays = json_decode(file_get_contents("http://kayaposoft.com/enrico/json/v1.0/?action=getPublicHolidaysForYear&country=usa&year=".$cal_year));
$holidays = json_decode('[{"date":{"day":1,"month":1,"year":2014,"dayOfWeek":3},"localName":"New Year\'s Day","englishName":"New Year\'s Day"},{"date":{"day":20,"month":1,"year":2014,"dayOfWeek":1},"localName":"Birthday of Martin Luther King, Jr.","englishName":"Birthday of Martin Luther King, Jr."},{"date":{"day":17,"month":2,"year":2014,"dayOfWeek":1},"localName":"Washington\'s Birthday","englishName":"Washington\'s Birthday"},{"date":{"day":26,"month":5,"year":2014,"dayOfWeek":1},"localName":"Memorial Day","englishName":"Memorial Day"},{"date":{"day":4,"month":7,"year":2014,"dayOfWeek":5},"localName":"Independence Day","englishName":"Independence Day"},{"date":{"day":1,"month":9,"year":2014,"dayOfWeek":1},"localName":"Labor Day","englishName":"Labor Day"},{"date":{"day":13,"month":10,"year":2014,"dayOfWeek":1},"localName":"Columbus Day","englishName":"Columbus Day"},{"date":{"day":11,"month":11,"year":2014,"dayOfWeek":2},"localName":"Veterans\' Day","englishName":"Veterans\' Day"},{"date":{"day":27,"month":11,"year":2014,"dayOfWeek":4},"localName":"Thanksgiving Day","englishName":"Thanksgiving Day"},{"date":{"day":25,"month":12,"year":2014,"dayOfWeek":4},"localName":"Christmas Day","englishName":"Christmas Day"},{"date":{"day":1,"month":1,"year":2015,"dayOfWeek":4},"localName":"New Year\'s Day","englishName":"New Year\'s Day"},{"date":{"day":19,"month":1,"year":2015,"dayOfWeek":1},"localName":"Birthday of Martin Luther King, Jr.","englishName":"Birthday of Martin Luther King, Jr."},{"date":{"day":16,"month":2,"year":2015,"dayOfWeek":1},"localName":"Washington\'s Birthday","englishName":"Washington\'s Birthday"},{"date":{"day":25,"month":5,"year":2015,"dayOfWeek":1},"localName":"Memorial Day","englishName":"Memorial Day"},{"date":{"day":3,"month":7,"year":2015,"dayOfWeek":5},"localName":"Independence Day","englishName":"Independence Day","note":"Holiday in lieu of 4 Jul 2015"},{"date":{"day":7,"month":9,"year":2015,"dayOfWeek":1},"localName":"Labor Day","englishName":"Labor Day"},{"date":{"day":12,"month":10,"year":2015,"dayOfWeek":1},"localName":"Columbus Day","englishName":"Columbus Day"},{"date":{"day":11,"month":11,"year":2015,"dayOfWeek":3},"localName":"Veterans\' Day","englishName":"Veterans\' Day"},{"date":{"day":26,"month":11,"year":2015,"dayOfWeek":4},"localName":"Thanksgiving Day","englishName":"Thanksgiving Day"},{"date":{"day":25,"month":12,"year":2015,"dayOfWeek":5},"localName":"Christmas Day","englishName":"Christmas Day"},{"date":{"day":1,"month":1,"year":2016,"dayOfWeek":5},"localName":"New Year\'s Day","englishName":"New Year\'s Day"},{"date":{"day":18,"month":1,"year":2016,"dayOfWeek":1},"localName":"Birthday of Martin Luther King, Jr.","englishName":"Birthday of Martin Luther King, Jr."},{"date":{"day":15,"month":2,"year":2016,"dayOfWeek":1},"localName":"Washington\'s Birthday","englishName":"Washington\'s Birthday"},{"date":{"day":30,"month":5,"year":2016,"dayOfWeek":1},"localName":"Memorial Day","englishName":"Memorial Day"},{"date":{"day":4,"month":7,"year":2016,"dayOfWeek":1},"localName":"Independence Day","englishName":"Independence Day"},{"date":{"day":5,"month":9,"year":2016,"dayOfWeek":1},"localName":"Labor Day","englishName":"Labor Day"},{"date":{"day":10,"month":10,"year":2016,"dayOfWeek":1},"localName":"Columbus Day","englishName":"Columbus Day"},{"date":{"day":11,"month":11,"year":2016,"dayOfWeek":5},"localName":"Veterans\' Day","englishName":"Veterans\' Day"},{"date":{"day":24,"month":11,"year":2016,"dayOfWeek":4},"localName":"Thanksgiving Day","englishName":"Thanksgiving Day"},{"date":{"day":26,"month":12,"year":2016,"dayOfWeek":1},"localName":"Christmas Day","englishName":"Christmas Day","note":"Holiday in lieu of 25 Dec 2016"},{"date":{"day":2,"month":1,"year":2017,"dayOfWeek":1},"localName":"New Year\'s Day","englishName":"New Year\'s Day","note":"Holiday in lieu of 1 Jan 2017"},{"date":{"day":16,"month":1,"year":2017,"dayOfWeek":1},"localName":"Birthday of Martin Luther King, Jr.","englishName":"Birthday of Martin Luther King, Jr."},{"date":{"day":20,"month":2,"year":2017,"dayOfWeek":1},"localName":"Washington\'s Birthday","englishName":"Washington\'s Birthday"},{"date":{"day":29,"month":5,"year":2017,"dayOfWeek":1},"localName":"Memorial Day","englishName":"Memorial Day"},{"date":{"day":4,"month":7,"year":2017,"dayOfWeek":2},"localName":"Independence Day","englishName":"Independence Day"},{"date":{"day":4,"month":9,"year":2017,"dayOfWeek":1},"localName":"Labor Day","englishName":"Labor Day"},{"date":{"day":9,"month":10,"year":2017,"dayOfWeek":1},"localName":"Columbus Day","englishName":"Columbus Day"},{"date":{"day":10,"month":11,"year":2017,"dayOfWeek":5},"localName":"Veterans\' Day","englishName":"Veterans\' Day","note":"Holiday in lieu of 11 Nov 2017"},{"date":{"day":23,"month":11,"year":2017,"dayOfWeek":4},"localName":"Thanksgiving Day","englishName":"Thanksgiving Day"},{"date":{"day":25,"month":12,"year":2017,"dayOfWeek":1},"localName":"Christmas Day","englishName":"Christmas Day"}]');

//go through list of holidays and change shacharis for those days
foreach($holidays as $legal_holiday)
{
	if (in_array($legal_holiday->englishName,array("New Year's Day","Memorial Day","Independence Day","Labor Day","Thanksgiving Day","Christmas Day")))
	{
		//set time for shacharis on a legal holiday to 8
		$date = date('Y-m-d',strtotime($legal_holiday->date->year.'-'.$legal_holiday->date->month.'-'.$legal_holiday->date->day));
		$info[$date]['shacharis'] = '8:00';
		$info[$date]['title'] = 'Legal Holiday';
	}
}

// get the holidays for this month
$json_this_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=$cal_year&month=x&ss=on&mf=on&s=on&i=off&lg=a&F=on");
$this_month = json_decode($json_this_month);

$json_next_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=".date("Y",strtotime("+1 Year"))."&month=x&ss=on&mf=on&s=on&i=off&lg=a");
$next_month = json_decode($json_next_month);
$events_array = array_merge($this_month->items,$next_month->items);

//if todays date is within 7 days of the next month, also get the holidays for next month
// if (date("n",time()) != date("n",strtotime("+7 days",time()))) {
	// $json_next_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=".date("Y",strtotime("+1 month"))."&month=".(date("n",strtotime("+1 month")))."&ss=on&mf=on&s=on&i=off&lg=a");
	// $next_month = json_decode($json_next_month);
	// $events_array = array_merge($this_month->items,$next_month->items);
// } else {
	//$events_array = $this_month->items;
//}

//go through all of the chagim obtained and adjust minyan times accordingly
foreach($events_array as $event)
{
	//add parsha info
	if ($event->category == 'parashat') {
		$info[$event->date]['parsha'] = $event->title;
	}
	if ($event->category == 'dafyomi') {
		$info[$event->date]['dafyomi'] = str_replace('Daf Yomi: ', '', $event->title);
	}
	if ($event->category == 'holiday') {
		
		$info[$event->date]['title'] = $info[$event->date]['title'] ? $info[$event->date]['title'].'<br/>'.$event->title : $event->title;
		
		if($event->title == 'Leil Selichot') {
			$selichot_start_a = $event->date;
			$selichot_end_a = date('Y-m-d',strtotime('+7 days',strtotime($event->date)));
		}

		if($event->title == 'Erev Rosh Hashana') {
			$selichot_end_a = $event->date;
			if (date('D',strtotime($event->date)) != 'Sun') {
				$info[$event->date]['shacharis'] = '6:00';
			} else {
				$info[$event->date]['shacharis'] = '7:30';
			}
			$info = erev_chag($info,$event->date);
			$sunset = get_sunset(strtotime($event->date));
			
			$rh1_timestamp = strtotime('+1 day',strtotime($event->date));
			$rh1 = date('Y-m-d',$rh1_timestamp);
			
			$info[$rh1]['shacharis'] = '8:00';
			$sunset = get_sunset($rh1_timestamp);
			$mincha = strtotime('-30 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);	
			$info[$rh1]['mincha'] = $mincha;
			$info[$rh1]['maariv'] = '';
			
			if (date('D',$rh1_timestamp) == 'Fri') {
				$info[$rh1]['candlelighting'] = date('g:i',strtotime('-18 minutes',$sunset));
			} else {
				$info[$rh1]['candlelighting'] = date('g:i',strtotime('+50 minutes',$sunset));
			}
			
			$rh2_timestamp = strtotime('+2 day',strtotime($event->date));
			$rh2 = date('Y-m-d',$rh2_timestamp);
			$info[$rh2]['shacharis'] = '8:00';
			$sunset = get_sunset($rh2_timestamp);
			$mincha = strtotime('-30 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);	
			$info[$rh2]['mincha'] = $mincha;
			
			if (date('D',$rh2_timestamp) == 'Fri') {
				$info[$rh2]['candlelighting'] = date('g:i',strtotime('-18 minutes',$sunset));
			} else {
				$info[$rh2]['havdala'] = date('g:i',strtotime('+50 minutes',$sunset));
			}
			$info[$rh2]['maariv'] = '';
			$selichot_start_b = $rh2;
			$selichot_end_b = date('Y-m-d',strtotime('+7 days',strtotime($rh2)));
		}

		if($event->title == 'Erev Yom Kippur') {
			$selichot_end_b = $event->date;
			$info = erev_chag($info,$event->date);
			$info[$event->date]['mincha'] = "4:30";
			$info[$event->date]['shacharis'] = "6:20";
			$info[$event->date]['kol_nidre'] = date('g:i',get_sunset(strtotime($event->date),(-10*60)));
			
		}
		if($event->title == 'Yom Kippur') {
			$yk_timestamp = strtotime($event->date);
			$sunset = get_sunset($yk_timestamp);
			$mincha = strtotime('-130 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);
			$info[$event->date]['mincha'] = $mincha;
			$info[$event->date]['maariv'] = '';
			$info[$event->date]['shacharis'] = "8:30";
			$info[$event->date]['havdala'] = date('g:i',strtotime('+50 minutes',$sunset));
			
		}
		
		if ($selichot_end_a) {
			$selichot_start_a = $selichot_start_a ? $selichot_start_a : date('Y-m-d',time()-(24*60*60));
			for($time = strtotime('+1 day',strtotime($selichot_start_a)); date('Y-m-d',$time) < $selichot_end_a; $time = strtotime('+1 day',$time))
			{
				if(date("D",$time) != 'Sat') {
					if($info[date('Y-m-d',$time)]['shacharis'] > '7:00') {
						$info[date('Y-m-d',$time)]['shacharis'] = '7:30';
					} else {
						$info[date('Y-m-d',$time)]['shacharis'] = '6:10';
					}
				}
			}
		}
		
		if ($selichot_end_b) {
			$selichot_start_b = $selichot_start_b ? $selichot_start_b : date('Y-m-d',time()-(24*60*60));
			for($time = strtotime('+1 day',strtotime($selichot_start_b)); date('Y-m-d',$time) < $selichot_end_b; $time = strtotime('+1 day',$time))
			{
				if(date("D",$time) != 'Sat') {
					if($info[date('Y-m-d',$time)]['shacharis'] > '7:00') {
						$info[date('Y-m-d',$time)]['shacharis'] = '7:30';
					} else {
						$info[date('Y-m-d',$time)]['shacharis'] = '6:10';
					}
				}
			}
		}
		
		if(strpos($event->title,"Erev ") !== FALSE && $event->title != "Erev Tish'a B'Av" && $event->title != 'Erev Purim' || $event->title == "Pesach VI (CH''M)" ) {
			$info = erev_chag($info,$event->date);
		}
		
		// if($event->title == 'Erev Sukkos') {
			// $info = erev_chag($info,$event->date);
		// }
		if($event->title == 'Sukkos VII (Hoshana Raba)') {
			$info = erev_chag($info,$event->date);
			$info[$event->date]['shacharis'] = '7:30';
		}
		
		if($event->title == 'Sukkos I' || $event->title == 'Shmini Atzeres' || $event->title == 'Pesach VII' || $event->title == 'Pesach I' || $event->title == 'Shavuos I') {
			$info = yomtov($info,$event->date,true);
			$info[$event->date]['maariv'] = '';
		}
		if($event->title == 'Shavuos I') {
			$info[$event->date]['shacharis'] = date('g:i',get_sunrise(strtotime($event->date))-(40*60)).', '.$info[$event->date]['shacharis'];
		}
		if($event->title == 'Sukkos II' || $event->title == 'Simchas Torah' || $event->title == 'Pesach VIII' || $event->title == 'Pesach II' || $event->title == 'Shavuos II') {
			$info = yomtov($info,$event->date,false);
			$info[$event->date]['maariv'] = '';
		}
		
		if(strpos($event->title,"CH''M") && date('D',strtotime($event->date)) != 'Sat') {
			$info[$event->date]['shacharis'] = '8:00';
		}
		// minor fast days
		if($event->title == 'Tzom Gedaliah' || $event->title == "Asara B'Tevet"  || $event->title == "Tzom Tammuz" || $event->title == "Tish'a B'Av" || $event->title == "Ta'anis Esther" || ($event->title =='Erev Purim' && date('D',strtotime($event->date)) != 'Sat')) {
			$fast_timestamp = strtotime($event->date);
			if (date('D',$fast_timestamp) != 'Sun') {
				$info[$event->date]['shacharis'] = '6:20';
			}
			$sunset = get_sunset($fast_timestamp);
			$alos = get_alos($fast_timestamp);
			$mincha = strtotime('-25 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);	
			$info[$event->date]['mincha'] = $mincha;
			if ($event->title == "Tish'a B'Av") {
				$info[$event->date]['mincha'] = date('g:i',get_by_hour($fast_timestamp,6)+(30*60));
				$info[$event->date]['fast_ends'] = date('g:i',strtotime('+50 minutes',$sunset));
			} else {
				$info[$event->date]['fast_ends'] = date('g:i',strtotime('+35 minutes',$sunset));
				$info[$event->date]['fast_starts'] = date('g:i',$alos);
			}
			if ($event->title =='Erev Purim') {
				$info[$event->date]['title'] = "Ta'anis Esther";
			}
		}
		if($event->title == "Ta'anis Esther" && date('D',$fast_timestamp) != 'Thu') {
			$info[$event->date]['maariv'] = $info[$event->date]['fast_ends'];
			$info[$event->date]['fast_ends'] = '';
			unset($info[$event->date]['fast_ends']);
			$info[$event->date]['megilla1']= date('g:i',strtotime('+45 minutes',$sunset));
		}
		if($event->title == "Purim") {
			$info[$event->date]['shacharis'] = '7:30';
			$info[$event->date]['megilla2'] = '8:00';
		}
		
		//for Rosh Chodesh
		if(strpos($event->title,"Rosh Chodesh") !== FALSE && date('D',strtotime($event->date)) != 'Sun' && date('D',strtotime($event->date)) != 'Sat') {
			if($info[date('Y-m-d',$time)]['shacharis'] < '7:00') {
				$info[$event->date]['shacharis'] = '6:20';
			}
		}
		
		if(strpos($event->title,"Chanukah") !== FALSE && strpos($event->title,"Chanukah: 1 Candle") === FALSE && date('D',strtotime($event->date)) != 'Sun' && date('D',strtotime($event->date)) != 'Sat') {
			if($info[date('Y-m-d',$time)]['shacharis'] < '7:00') {
				$info[$event->date]['shacharis'] = '6:25';
			}
		}
	}
	
}


?>
<html>
	<head>
		<title>YIOM Laining</title>
	
<style>
	table {
	    border-collapse: collapse;
	    font-family: 'Verdana';
		font-size: 10px;
	}
	
	 #main_table>thead>th, #main_table>tbody>tr>td {
	    border: 1px solid black;
	    vertical-align:top;
	    height: 15px;
	    width: 150px;
	}
	body {
		
	}
</style>
</head>
<body >

<h1 style="font-size:20px;">Laining</h1>
<table id="main_table">
	<thead><th>Date</th><th>Parsha</th><th>Person</th></thead>
	<tbody>

<?php
$count = 0;
//sort the array in date order
ksort($info);
//print_r($info);
//spit out the information from the array in readable format
foreach($info as $date => $info_day) {
	// if ($date >= date('Y-m-d')) {
		// if ($count >= $number_of_days_to_show) {
			// break;
		// }
		$info_date = strtotime($date);
		if ($info_day['parsha'] && $info_day['display_date']) {
			echo '<tr><td>'.str_replace('Shabbos ','',$info_day['display_date']).'</td><td>'.str_replace('Parshas', '', $info_day['parsha']).'</td><td> </td></tr>';
		}
		
	//}
	
}

?>
	</tbody>
</table>
</body>
</html>
<?php

//////// FUNCTIONS


function erev_chag($info,$date)
{
	$info[$date]['candlelighting'] = date('g:i',get_sunset(strtotime($date),(-18*60)));
	if ($info[$date]['candlelighting'] > '7:00' && date('D',strtotime($date)) != 'Sat') {
		$info[$date]['mincha'] = '7:00';
		$info[$date]['candlelighting'] = '7:00';
	} else if (date('D',strtotime($date)) == 'Sat'){
		//$info[$date]['mincha'] = date('g:i',((60*5)*get_sunset(strtotime($date),(-25*60)))/(60*5));
		$info[$date]['candlelighting'] = $info[$date]['havdala'];
	} else {
		$info[$date]['mincha'] = $info[$date]['candlelighting'];
	}
	$info[$date]['havdala'] = '';
	$info[$date]['maariv'] = '';
	return $info;
}

function yomtov($info,$date,$first_day=true)
{
	$info[$date]['shacharis'] = '8:45';;
	$shkiah_timestamp = get_sunset(strtotime($date));
	if (date('D',strtotime($date)) != 'Sat'){
		$info[$date]['mincha'] = date('g:i',((60*5)*(round(($shkiah_timestamp-(15*60))/(60*5)))));
	}
	$tzeis = date('g:i',$shkiah_timestamp+(50*60));
	if (date('D',strtotime($date)) == 'Fri') {
		$info[$date]['candlelighting'] = date('g:i',$shkiah_timestamp-(18*60));
		$info[$date]['mincha'] = $info[$date]['candlelighting'];
	} else if ($first_day) {
		$info[$date]['candlelighting'] = $tzeis;
		$info[$date]['havdala'] = '';
	} else {
		$info[$date]['havdala'] = $tzeis;
	}
	//$info[$date]['maariv'] = 0;
	return $info;
}

function get_sunset($timestamp,$offset = 0)
{
	return (60*(round(date_sunset($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(3.5/6))/(60))))+$offset;
}

function get_sunrise($timestamp,$offset = 0)
{
	return (60*(round(date_sunrise($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90.6)/(60))))+$offset;
}

function get_alos($timestamp,$offset = 0)
{
	return (60*(round(date_sunrise($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90 + 16.1)/(60))))+$offset;
}

function get_by_hour($timestamp,$hour=3) //get time of hour of day
{
	$sunrise = get_sunrise($timestamp);
	$sunset = get_sunset($timestamp);
	$total_seconds_in_day = $sunset-$sunrise;
	$length_each_hour = $total_seconds_in_day / 12;
	$time_offset = $hour * $length_each_hour;
	return $sunrise+$time_offset;
}


?>