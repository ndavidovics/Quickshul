<?php
//first set the timezone that PHP will use
date_default_timezone_set('America/Chicago');

$number_of_days_to_show = 7;
//show all the dates from today, until seven days from now
$day_end = ($number_of_days_to_show*60*60*24)+time(); 
for($day = time(); date('Y-m-d',$day_end) > date('Y-m-d',$day); $day += (60*60*24))
{
		$date = date('Y-m-d',$day);
		$info[$date]['display_date'] = date('l F j, Y',$day);
		$info[$date]['hebrew_date'] = iconv ('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($day),true,CAL_JEWISH_ADD_GERESHAYIM));
		
		if (date('D',$day) == 'Fri') {
			$timestamp	= $day;
			
			$shkiah_timestamp = get_sunset($timestamp);
						
			$info = erev_chag($info,$date);
			$info[$date]['shacharis'] = '6:35';
			//get sunset of next week, and see which sunset is earlier
			$mincha_this_week = min($shkiah_timestamp,strtotime('-7 days',get_sunset(strtotime('+7 days',$timestamp))));
			// then subtract 10 minutes from the earlier one, and round to the nearest 5 minute mark
			$mincha_this_week = (5*60)*(round((strtotime('-10 minutes',$mincha_this_week)/(5*60))));
			$mincha_this_week = date('g:i',$mincha_this_week);
			$day_timestamp = strtotime('+1 day',$timestamp);;
			for($day_x = 0; $day_x < 5;$day_x++)
			{
				$day_timestamp = strtotime('+1 day',$day_timestamp);
				$info[date('Y-m-d',$day_timestamp)]['mincha'] = $mincha_this_week; 
			}

			//get sunset of last week, and see which sunset is earlier
			$mincha_last_week = min($shkiah_timestamp,strtotime('+7 days',get_sunset(strtotime('-7 days',$timestamp))));
			// then subtract 10 minutes from the earlier one, and round to the nearest 5 minute mark
			$mincha_last_week = (5*60)*(round((strtotime('-10 minutes',$mincha_last_week)/(5*60))));
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
			$info[$date]['shacharis'] = '7, 8:45'; 
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
$holidays = json_decode(file_get_contents("http://kayaposoft.com/enrico/json/v1.0/?action=getPublicHolidaysForYear&country=usa&year=".date("Y")));

//go through list of holidays and change shacharis for those days
foreach($holidays as $legal_holiday)
{
	if (in_array($legal_holiday->englishName,array("New Year's Day","Memorial Day","Independence Day","Labor Day","Veterans' Day","Thanksgiving Day","Christmas Day")))
	{
		//set time for shacharis on a legal holiday to 8
		$date = date('Y-m-d',strtotime($legal_holiday->date->year.'-'.$legal_holiday->date->month.'-'.$legal_holiday->date->day));
		$info[$date]['shacharis'] = '8:00';
		$info[$date]['title'] = 'Legal Holiday';
	}
}

// get the holidays for this month
$json_this_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=".date("Y")."&month=".(date("n"))."&ss=on&mf=on&s=on&i=off&lg=a");
$this_month = json_decode($json_this_month);

//if todays date is within 7 days of the next month, also get the holidays for next month
if (date("n",time()) != date("n",strtotime("+7 days",time()))) {
	$json_next_month = file_get_contents("http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=".date("Y",strtotime("+1 month"))."&month=".(date("n",strtotime("+1 month")))."&ss=on&mf=on&s=on&i=off&lg=a");
	$next_month = json_decode($json_next_month);
	$events_array = array_merge($this_month->items,$next_month->items);
} else {
	$events_array = $this_month->items;
}

//go through all of the chagim obtained and adjust minyan times accordingly
foreach($events_array as $event)
{
	//add parsha info
	if ($event->category == 'parashat') {
		$info[$event->date]['parsha'] = $event->title;
	}
	if ($event->category == 'holiday') {
		$info[$event->date]['title'] = $event->title;
		if($event->title == 'Leil Selichot') {
			$selichot_start_a = $event->date;
		}
		if($event->title == 'Erev Rosh Hashana') {
			$selichot_end_a = $event->date;
			$info[$event->date]['shacharis'] = '6:00';
			$info = erev_chag($info,$event->date);
			$sunset = get_sunset(strtotime($event->date));
			
			$rh1_timestamp = strtotime('+1 day',strtotime($event->date));
			$rh1 = date('Y-m-d',$rh1_timestamp);
			
			$info[$rh1]['shacharis'] = '8:00';
			$sunset = get_sunset($rh1_timestamp);
			$mincha = strtotime('-25 minutes',$sunset);
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
			$mincha = strtotime('-25 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);	
			$info[$rh2]['mincha'] = $mincha;
			
			if (date('D',$rh2_timestamp) == 'Fri') {
				$info[$rh2]['candlelighting'] = date('g:i',strtotime('-18 minutes',$sunset));
			} else {
				$info[$rh2]['havdala'] = date('g:i',strtotime('+50 minutes',$sunset));
			}
			$selichot_start_b = $rh2;
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
			$info[$event->date]['shacharis'] = "8:30";
			$info[$event->date]['havdala'] = date('g:i',strtotime('+50 minutes',$sunset));
			
		}

		if($event->title == 'Erev Sukkos') {
			$info = erev_chag($info,$event->date);
		}
		if($event->title == 'Sukkos VII (Hoshana Raba)') {
			$info = erev_chag($info,$event->date);
			$info[$event->date]['shacharis'] = '7:30';
		}
		
		if($event->title == 'Sukkos I' || $event->title == 'Shmini Atzeres') {
			$info = yomtov($info,$event->date,true);
		}
		
		if($event->title == 'Sukkos II' || $event->title == 'Simchas Torah') {
			$info = yomtov($info,$event->date,false);
		}
		
		if(strpos($event->title,"CH''M") && date('D',strtotime($event->title)) != 'Sat') {
			$info[$event->date]['shacharis'] = '8:00';
		}
		// minor fast days
		if($event->title == 'Tzom Gedaliah') {
			$fast_timestamp = strtotime($event->date);
			if (date('D',$fast_timestamp) != 'Sun') {
				$info[$event->date]['shacharis'] = '6:20';
			}
			$sunset = get_sunset($fast_timestamp);
			$mincha = strtotime('-25 minutes',$sunset);
			$mincha = (5*60)*(round($mincha/(5*60)));
			$mincha = date('g:i',$mincha);	
			$info[$event->date]['mincha'] = $mincha;
			$info[$event->date]['fast_ends'] = date('g:i',strtotime('+30 minutes',$sunset));
			
		}
		
		//for Rosh Chodesh
		if(strpos($event->title,"Rosh Chodesh") !== FALSE && date('D',strtotime($event->title)) != 'Sun' && date('D',strtotime($event->title)) != 'Sat') {
			if($info[date('Y-m-d',$time)]['shacharis'] < '7:00') {
				$info[$event->date]['shacharis'] = '6:20';
			}
		}
	}
	
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

$count = 0;
//sort the array in date order
ksort($info);
//spit out the information from the array in readable format
foreach($info as $date => $info_day) {
	if ($date >= date('Y-m-d')) {
		if ($count >= $number_of_days_to_show) {
			break;
		}
		echo '<strong>'.$info_day['display_date'].'</strong><br/>';
		echo '<strong>'.$info_day['hebrew_date'].'</strong><br/>';
		echo $info_day['parsha'] ? '<strong>'.$info_day['parsha'].'</strong><br/>' : '';
		echo $info_day['title'] ? '<strong>'.$info_day['title'].'</strong><br/>' : '';
		echo 'Shacharis: '.$info_day['shacharis'].'<br/>';
		echo 'Mincha: '. $info_day['mincha'].'<br/>';
		echo $info_day['maariv'] ? 'Maariv: '. $info_day['maariv'].'<br/>' : '';
		echo $info_day['candlelighting'] ? 'Candlelighting: '. $info_day['candlelighting'].'<br/>' : '';
		echo $info_day['kol_nidre'] ? 'Kol Nidre: '. $info_day['kol_nidre'].'<br/>' : '';
		echo $info_day['havdala'] ? 'Havdala/Maariv: '. $info_day['havdala'].'<br/>' : '';
		echo $info_day['fast_ends'] ? 'Fast Ends: '. $info_day['fast_ends'].'<br/>' : '';
		echo '<br/>';
		$count++;
	}
	
}


//////// FUNCTIONS


function erev_chag($info,$date)
{
	$info[$date]['candlelighting'] = date('g:i',get_sunset(strtotime($date),(-18*60)));
	if ($info[$date]['candlelighting'] > '7:00') {
		$info[$date]['mincha'] = '7:00';
	} else {
		$info[$date]['mincha'] = $info[$date]['candlelighting'];
	}
	$info[$date]['maariv'] = '';
	return $info;
}

function yomtov($info,$date,$first_day=true)
{
	$info[$date]['shacharis'] = '8:45';;
	$shkiah_timestamp = get_sunset(strtotime($date));
	$info[$date]['mincha'] = date('g:i',(60*5)*($shkiah_timestamp-(15*60)/(60*5)));
	$tzeis = date('g:i',$shkiah_timestamp+(50*60));
	if (date('D',strtotime($date)) == 'Fri') {
		$info[$date]['candlelighting'] = date('g:i',$shkiah_timestamp-(18*60));
		$info[$date]['mincha'] = $info[$date]['candlelighting'];
	} else if ($first_day) {
		$info[$date]['candlelighting'] = $tzeis;
	} else {
		$info[$date]['havdala'] = $tzeis;
	}
	return $info;
}

function get_sunset($timestamp,$offset = 0)
{
	return (60*(round(date_sunset($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(5/6))/(60))))+$offset;
}
?>