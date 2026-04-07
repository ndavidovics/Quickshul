<meta charset="UTF-8" />
<?php
//first set the timezone that PHP will use
date_default_timezone_set('America/Chicago');

$number_of_days_to_show = 7;
//show all the dates from today, until seven days from now
$day_end = ($number_of_days_to_show*60*60*24)+time(); 

$json_this_month = file_get_contents("hebcal.json"); //http://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year=".date("Y")."&month=".(date("n"))."&ss=on&mf=on&s=on&i=off&lg=a");
$this_month = json_decode($json_this_month);

$events_array = $this_month->items;

//go through all of the chagim obtained and adjust minyan times accordingly
foreach($events_array as $event)
{
	if ($event->category == 'holiday') {
		$info['holidays'][date('Y',strtotime($event->date))][$event->title] = $event->date;
	}
}
	
$cal_year = date('Y');

for($day = time(); date('Y-m-d',$day_end) > date('Y-m-d',$day); $day += (60*60*24))
{
	$date = date('Y-m-d',$day);
	$info[$date]['display_date'] = date('l F j, Y',$day);
	$info[$date]['hebrew_date'] = iconv ('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd($day),true,CAL_JEWISH_ADD_GERESHAYIM));
	$info[$date]['sunset'] = date('g:i',get_sunset($day));
	$info[$date]['sunrise'] = date('g:i',get_sunrise($day));
	$school_starts = strtotime("third wednesday of August ".$cal_year);
	$school_ends = strtotime("third wednesday of June ".$cal_year) + (60*60*24);
	if (date('D',$day) == 'Fri') {
		$timestamp	= $day;
		
		$shkiah_timestamp = get_sunset($timestamp);	
		$info = erev_chag($info,$date);		
		$info[$date]['shacharis'] = '6:35';
		if ($day < $school_starts && $day > $school_ends) {
			$info[$date]['shacharis'] = '6:35, 8';
		}
		//get sunset of next week, and see which sunset is earlier
		$min_sunset = min(strtotime('-1 day',get_sunset(strtotime('+1 day',$timestamp))),strtotime('-5 days',get_sunset(strtotime('+5 days',$timestamp))));
		//echo date('g:i',$min_sunset).' ';
		// then subtract 12 minutes from the earlier one, and round to the nearest 5 minute mark
		$mincha_this_week = (5*60)*(round((strtotime('-13 minutes',$min_sunset)/(5*60))));
		if ($mincha_this_week > ($min_sunset - (60*13))) { // if there is less than a 12 minute gap, add five minutes
			$mincha_this_week -= 5*60;
		}
		$mincha_this_week = date('g:i',$mincha_this_week);
		$day_timestamp = strtotime('+1 day',$timestamp);;
		for($day_x = 0; $day_x < 5;$day_x++)
		{
			$day_timestamp = strtotime('+1 day',$day_timestamp);
			$info[date('Y-m-d',$day_timestamp)]['mincha'] = $mincha_this_week; 
		}
		//echo $mincha_this_week.' ';
		//get sunset of last week, and see which sunset is earlier
		$min_sunset = min(strtotime('+1 day',get_sunset(strtotime('-1 day',$timestamp))),strtotime('+5 days',get_sunset(strtotime('-5 days',$timestamp))));
		// then subtract 12 minutes from the earlier one, and round to the nearest 5 minute mark
		$mincha_last_week = (5*60)*(round((strtotime('-13 minutes',$min_sunset)/(5*60))));
		//echo $mincha_last_week.' '.($min_sunset-600) . ' | ';
		//echo date('g:i',$mincha_last_week).' '.date('g:i',$min_sunset).' | ';
		if ($mincha_last_week > ($min_sunset - (60*13))) { // if there is less than a 12 minute gap, add five minutes
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
		$shabbos_mincha = strtotime('-30 minutes',$sunset);
		$shabbos_mincha = (5*60)*(round($shabbos_mincha/(5*60)));
		$shabbos_mincha = date('g:i',$shabbos_mincha);	
		
		$info[$date]['display_date'] = 'Shabbos '.date('F j, Y',$shabbos_day);
		if (date('gi',get_by_hour($day,3)) > 920) {
			$info[$date]['shacharis'] = '7:45, 8:45'; 
		} else {
			$shabbos_shacharis = date('g:i',(5*60)*(round(((get_by_hour($day,3)-(35*60))/(5*60)))));
			$info[$date]['shacharis'] = '7:45, '.$shabbos_shacharis; 
		}
		$info[$date]['mincha'] = $shabbos_mincha; 
		if ($shabbos_mincha > '7:00') {
			$info[$date]['mincha'] = '3, '.$shabbos_mincha; 
		}
		$havdala = date('g:i',strtotime('+50 minutes',$sunset));
		$info[$date]['havdala'] = $havdala;
	} else {
		// if it is a weekday
		// maariv is at shkiah
		// shacharis is 8 on sundays, 6:35 on weekdays
		$sunset = get_sunset($day);
		$info[$date]['maariv'] = date('g:i',$sunset);
		$info[$date]['shacharis'] = '6:35';
		if (date('D',$day) == 'Sun') {
			$info[$date]['shacharis']= '8:00';
		} else if ($day < $school_starts && $day > $school_ends) {
			$info[$date]['shacharis'] = '6:35, 8';
		}
	} 
}

//get list of legal holidays for the year
$holidays = json_decode(file_get_contents('secular_holidays.json'));
//go through list of holidays and change shacharis for those days
foreach($holidays as $legal_holiday)
	{
		if (in_array($legal_holiday->name[0]->text,array("The Friday after Thanksgiving Day","Good Friday","Columbus Day","Washington's Birthday","Easter","Easter Monday"))) {continue;}
		if ($legal_holiday->observedOn->year && $legal_holiday->name[0]->text != "New Year's Day") { $legal_holiday->date = $legal_holiday->observedOn;}
		$date = $legal_holiday->date->year.'-'.sprintf("%02d", $legal_holiday->date->month).'-'.sprintf("%02d", $legal_holiday->date->day);
		if (in_array($legal_holiday->name[0]->text,array("New Year's Day","Memorial Day","Independence Day","Labor Day","Thanksgiving","Christmas Day")))
		{
			//set time for shacharis on a legal holiday to 8 
			if (date('D',strtotime($date)) != 'Sat') {
				$info[$date]['shacharis'] = '8:00';
			}
		}
		if ($legal_holiday->name[0]->text == 'Christmas Day') {$holiday_name = 'Legal Holiday';} else {$holiday_name = $legal_holiday->name[0]->text;}
		$info[$date]['title'] = $info[$date]['title'] ? $info[$date]['title'] . '<br/> '.$holiday_name : $holiday_name;
	}

// get the holidays for this month


//go through all of the chagim obtained and adjust minyan times accordingly
foreach($events_array as $event)
	{
		//add parsha info
		if ($event->category == 'parashat') {
			$info[$event->date]['parsha'] = $event->title;
			if ($info[$event->date]['parsha'] == 'Parshas Sazria') {
				$info[$event->date]['parsha'] = 'Parshas Tazria';
			}
			if ($info[$event->date]['parsha'] == 'Pasrhas Sazria-Metzora') {
				$info[$event->date]['parsha'] = 'Parshas Tazria-Metzora';
			}
		}
		//$info[$event->date]['kel_maleh_mincha'] = 0;
		if(strpos($info[$event->date]['parsha'],'Vayeilech'))	{
			if ($info[$event->date]['kel_maleh_minchaT'] != 1) {
				$info[$event->date]['mincha'] = date('g:i',strtotime($event->date. ' '.$info[$event->date]['mincha']. ' pm')-(5*60));
				$info[$event->date]['kel_maleh_minchaT'] = 1;
			}
		}

		if ($event->category == 'dafyomi') {
			$info[$event->date]['dafyomi'] = str_replace('Daf Yomi: ', '', $event->title);
		}
		if ($event->category == 'holiday') {
			
			if($event->title == "Asara B'Tevet") {
				$event->title = "Asara B'Teves";
			}
			$info[$event->date]['title'] = $info[$event->date]['title'] ? $info[$event->date]['title'].'<br/>'.$event->title : $event->title;
			
			if($event->title == 'Sigd') {
				$info[$event->date]['title'] = '';
			}
			if($event->title == 'Yom HaAliyah') {
				$info[$event->date]['title'] = '';
			}
			
			
			if ($event->title == 'Shabbos HaChodesh') {
				
				if (strpos($info[$event->date]['title'],'osh Chodesh')) {
					$new_mincha_date =  date('Y-m-d',strtotime('-7 days',strtotime($event->date)));
					$info[$new_mincha_date]['sel_mincha'] = date('g:i',strtotime($new_mincha_date. ' '.$info[$new_mincha_date]['mincha']. ' pm')-(5*60));
					
				} else {
					$info[$event->date]['sel_mincha'] = date('g:i',strtotime($event->date. ' '.$info[$event->date]['mincha']. ' pm')-(5*60));
				}
				
			}
			
			if($event->title == 'Leil Selichos') {
				$info[$event->date]['title'] = '';
				$selichot_start_a = $event->date;
				$selichot_end_a = date('Y-m-d',strtotime('+7 days',strtotime($event->date)));
			}
			
			if (strpos($event->title,"CH''M") && date('D',strtotime($event->date)) == 'Sat') {
				$info[$event->date]['shacharis'] = '8:45';
			}
			
			
	
			if($event->title == 'Erev Rosh Hashana') {
				$selichot_end_a = $event->date;
				$info[$event->date]['selichos'] = 'y';
				if (date('D',strtotime($event->date)) == 'Sun' || $info[$event->date]['shacharis'] == '8:00') {
					$info[$event->date]['shacharis'] = '7:30';
				} else {
					$info[$event->date]['shacharis'] = '6:00';
				}
				$info = erev_chag($info,$event->date);
				$sunset = get_sunset(strtotime($event->date));
				
				$rh1_timestamp = strtotime('+1 day',strtotime($event->date));
				$rh1 = date('Y-m-d',$rh1_timestamp);
				
				$info[$rh1]['shacharis'] = '8:00';
				$sunset = get_sunset($rh1_timestamp);
				$rh2_timestamp = strtotime('+2 day',strtotime($event->date));
				$mincha_sunset = get_sunset($rh2_timestamp);
				$mincha = strtotime('-30 minutes',$mincha_sunset);
				$mincha = (5*60)*(round($mincha/(5*60)));
				$mincha = date('g:i',$mincha);	
				$info[$rh1]['mincha'] = $mincha;
				$info[$rh1]['maariv'] = '';
				$info[$rh1]['havdala'] = '';
				
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
				$info[$event->date]['selichos'] = 'y';
				$info = erev_chag($info,$event->date);
				$info[$event->date]['mincha'] = "4:30";
				if ($info[$event->date]['candlelighting'] < "6:20") {
					$info[$event->date]['mincha'] = "4:15";
				}
				$info[$event->date]['shacharis'] = "6:20";
				if (date('D',strtotime($event->date)) == 'Sun' ) {
					$info[$event->date]['shacharis'] = "7:30";
				}
				$info[$event->date]['kol_nidre'] = date('g:i',get_sunset(strtotime($event->date),(-10*60)));
				
			}
			if($event->title == 'Yom Kippur') {
				$yk_timestamp = strtotime($event->date);
				$sunset = get_sunset($yk_timestamp);
				$mincha = strtotime('-135 minutes',$sunset);
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
						$info[date('Y-m-d',$time)]['selichos'] = 'y';
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
					$b_date = date('Y-m-d',$time);
					if(date("D",$time) != 'Sat') {
						$info[date('Y-m-d',$time)]['selichos'] = 'y';
						if($info[date('Y-m-d',$time)]['shacharis'] > '7:00') {
							$info[date('Y-m-d',$time)]['shacharis'] = '7:30';
						} else {
							$info[date('Y-m-d',$time)]['shacharis'] = '6:10';
						}
						if (date("D",$time) != 'Fri') {
						    if ($info[$b_date]['title'] == 'Tzom Gedaliah') { 
						    	$info[$b_date]['sel_mincha'] = date('g:i',strtotime($b_date. ' '.$info[$b_date]['mincha']. ' pm'));
						    } else {
								$info[$b_date]['sel_mincha'] = date('g:i',strtotime($b_date. ' '.$info[$b_date]['mincha']. ' pm')-(5*60));
						    }
						}
					}
				}
			}
			
			
			if ($sefira_start && !$info[$sefira_start]['sefira']) {
				$sefira_end = strtotime('+49 days',strtotime($sefira_start));
				//echo $sefira_end;
				$sefira_count = 0;
				for($time = strtotime($sefira_start); $time < $sefira_end; $time = strtotime('+1 day',$time))
				{
					$sefira_count++; //echo ' '.$time;
					$info[date('Y-m-d',$time)]['sefira'] = $sefira_count;
				}
			}
			if ($event->title == "Erev Tish'a B'Av" && date('D',strtotime($event->date)) == 'Sat') {
				$info[$event->date]['shabbos_ends_ind'] = 'y'; 
				$fast_timestamp = strtotime($event->date);
				$mincha_time = ((5*60)*(round((get_sunset($fast_timestamp)-(130*60))/(5*60))));
				$info[$event->date]['mincha'] = date('g:i',$mincha_time);
			}
			if ($event->title == "Erev Tish'a B'Av") {
				$fast_timestamp = strtotime($event->date);
				$sunset = get_sunset($fast_timestamp);
				$info[$event->date]['mincha'] = '6:00';
				$info[$event->date]['fast_starts_night'] = date('g:i',$sunset);	
			}

			if($event->title == 'Shabbos HaGadol' || $event->title == 'Shabbos Shuvah') {
				//print_r($info[$event->date]);
				$info[$event->date]['drasha'] = date('g:i',strtotime($event->date. ' '.$info[$event->date]['mincha']. ' pm')-(55*60));
			}
			
			if(strpos($event->title,"Erev ") !== FALSE && $event->title != "Erev Tish'a B'Av" && $event->title != 'Erev Purim' && $event->title != 'Erev Yom Kippur' || $event->title == "Pesach VI (CH''M)" ) {
				$info = erev_chag($info,$event->date);
				if (isset($info[$event->date]['drasha'])) {
					$info[$event->date]['drasha'] = date('g:i',strtotime($event->date. ' '.$info[$event->date]['mincha']. ' pm')-(55*60));
				}
			}

			
			
			// if($event->title == 'Erev Sukkos') {
				// $info = erev_chag($info,$event->date);
			// }
			if($event->title == 'Sukkos VII (Hoshana Raba)') {
				$info = erev_chag($info,$event->date);
				if (date('D',strtotime($event->date)) != 'Sun') {
					$info[$event->date]['shacharis'] = '6:30, 7:45';
				} else {
					$info[$event->date]['shacharis'] = '7:00, 8:00';
				}
			}
			
			if($event->title == 'Erev Pesach') {
				if (date('D',strtotime($event->date)) == 'Sat') {
					$info[$event->date]['shacharis'] = '7:45';
				}
				$info[$event->date]['eat_by'] = date('g:i',get_by_hour(strtotime($event->date),4));
				$info[$event->date]['burn_by'] = date('g:i',get_by_hour(strtotime($event->date),5));
			}
			
			if($event->title == 'Sukkos I' || $event->title == 'Shmini Atzeres' || $event->title == 'Pesach VII' || $event->title == 'Pesach I' || $event->title == 'Shavuos I') {
				$info = yomtov($info,$event->date,true);
				$info[$event->date]['maariv'] = '';
			}
			if($event->title == 'Shavuos I') {
				$info[$event->date]['shacharis'] = date('g:i',get_sunrise(strtotime($event->date))-(40*60)).', '.$info[$event->date]['shacharis'];
			}
			
			if ($event->title == 'Pesach I' || $event->title == 'Pesach II') {
				$info[$event->date]['chatzos_halaila'] = date('g:i',get_by_hour(strtotime($event->date),6)); 
			}
			
			if ($event->title == 'Pesach II') {
				$sefira_start = $event->date;
			}
			
			if ($event->title == 'Erev Shavuos') {
				$sefira_stop = $event->date;
			}
			
			if($event->title == 'Sukkos II' || $event->title == 'Simchas Torah' || $event->title == 'Pesach VIII' || $event->title == 'Pesach II' || $event->title == 'Shavuos II') {
				$info = yomtov($info,$event->date,false);
				$info[$event->date]['maariv'] = '';
			}

			if($event->title == 'Simchas Torah' || $event->title == 'Pesach VIII' ||  $event->title == 'Shavuos II') {
				//make room for neilas hachag
				$shkiah_timestamp = get_sunset(strtotime($event->date));
				if (date('D',strtotime($event->date)) != 'Sat' && date('D',strtotime($event->date)) != 'Fri'){
					$info[$event->date]['mincha'] = date('g:i',((60*5)*(round(($shkiah_timestamp-(30*60))/(60*5)))));
				}
			}
			
			if(strpos($event->title,"CH''M") && date('D',strtotime($event->date)) != 'Sat') {
				$cm_timestamp = strtotime($event->date);
				if (date('D',$cm_timestamp) != 'Sun') {
					$info[$event->date]['shacharis'] = '6:30, 8';
				} else {
					$info[$event->date]['shacharis'] = '8:00';
				}
			}
			// minor fast days
			if($event->title == 'Tzom Gedaliah' || $event->title == "Asara B'Tevet" || $event->title == "Asara B'Teves"  || $event->title == "Tzom Tammuz" || $event->title == "Tish'a B'Av" || $event->title == "Ta'anis Esther" || ($event->title =='Erev Purim' && date('D',strtotime($event->date)) != 'Sat')) {
				$fast_timestamp = strtotime($event->date);
				if (date('D',$fast_timestamp) != 'Sun') {
					if ($info[$event->date]['shacharis'] == '8:00') {
						$info[$event->date]['shacharis'] = '6:20,8';
					} else {
						$info[$event->date]['shacharis'] = '6:20';
					}
				}
				


				$sunset = get_sunset($fast_timestamp);
				$alos = get_alos($fast_timestamp);
				$mincha = strtotime('-30 minutes',$sunset);
				$mincha = (5*60)*(round($mincha/(5*60)));
				$mincha = date('g:i',$mincha);	
				if (date('D',$fast_timestamp) == 'Fri') {
					$mincha = date('g:i',((60*5)*(round(($sunset-(35*60))/(60*5)))));
				}
				$info[$event->date]['mincha'] = $mincha;
				if ($event->title == "Tish'a B'Av") {
					$info[$event->date]['shacharis'] = '8:30';
					$info[$event->date]['chatzos'] = date('g:i',get_by_hour($fast_timestamp,6));
					$info[$event->date]['mincha'] = date('g:i',(5*60)*(ceil(get_by_hour($fast_timestamp,6.5)/(5*60))));
					$info[$event->date]['maariv'] = date('g:i',(5*60)*(round(strtotime('+30 minutes',$sunset)/(5*60))));
					$info[$event->date]['fast_ends'] = date('g:i',strtotime('+50 minutes',$sunset));
				} else {
					$info[$event->date]['fast_ends'] = date('g:i',strtotime('+42 minutes',$sunset));
					$info[$event->date]['fast_starts'] = date('g:i',$alos);
				}
				if ($event->title =='Erev Purim') {
					$info[$event->date]['title'] = "Ta'anis Esther";
				}
			}
			if($event->title == "Erev Purim") {
				//$info[$event->date]['maariv'] = $info[$event->date]['fast_ends'];
				//$info[$event->date]['fast_ends'] = '';
				//unset($info[$event->date]['fast_ends']);
				$info[$event->date]['megilla1']= date('g:i',strtotime('+42 minutes',$sunset));
				if (date('D',strtotime($event->date)) == 'Sat') {
					$info[$event->date]['megilla1']= date('g:i',(5*60)*(ceil(strtotime('+70 minutes',$sunset)/(5*60))));
				}
			}
			if($event->title == "Purim") {
				if (date('D',strtotime($event->date)) == 'Sun') {
					$info[$event->date]['shacharis'] = '8:00';
					$info[$event->date]['megilla2'] = '8:30';
				} else {
					$info[$event->date]['shacharis'] = '6:30, 8';
					$info[$event->date]['megilla2'] = '7:00, 8:30';
				}
				$info[$event->date]['mincha'] = date('g:i',(5*60)*(ceil(get_by_hour(strtotime($event->date),6.5)/(5*60))));
				$info[$event->date]['maariv'] = '7:30';
				if (date('D',strtotime($event->date)) == 'Fri') {
					$info[$event->date]['maariv'] = ''; //date('g:i',(5*60)*(ceil(strtotime('+20 minutes',$sunset)/(5*60))));
				}
			}
			
			
			
			if(strpos($event->title,"Chanukah") !== FALSE && strpos($event->title,"Chanukah: 1 Candle") === FALSE && date('D',strtotime($event->date)) != 'Sun' && date('D',strtotime($event->date)) != 'Sat') {
				if($info[$event->date]['shacharis'] == '6:35') {
					$info[$event->date]['shacharis'] = '6:25';
				}
			}
		}
		//for Rosh Chodesh
		if($event->category == 'roshchodesh') {
			if($event->title == 'Rosh Chodesh Tevet') {
				$event->title = 'Rosh Chodesh Teves';
			}
			
			if($event->title == "Rosh Chodesh Iyyar") {
				$event->title = "Rosh Chodesh Iyar";
			}
			$info[$event->date]['title'] = $info[$event->date]['title'] ? $info[$event->date]['title'].'<br/>'.$event->title : $event->title;
			
			if(strpos($event->title,"Rosh Chodesh") !== FALSE && date('D',strtotime($event->date)) != 'Sun' && date('D',strtotime($event->date)) != 'Sat') {
				if($info[$event->date]['shacharis'] < '7:00') {
					$school_starts = strtotime("third wednesday of August ".$cal_year);
					$school_ends = strtotime("third wednesday of June ".$cal_year) + (60*60*24);
					$info[$event->date]['shacharis'] = '6:20';
					if (strtotime($event->date) < $school_starts && $day > $school_ends) {
						$info[$event->date]['shacharis'] = '6:20, 8';
					}
					
				}
			}
		}
		
	}



$count = 0;
//sort the array in date order
ksort($info);
//spit out the information from the array in readable format
if (!$nodisplay) {
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
			echo 'Mincha: '. ($info_day['sel_mincha'] ? $info_day['sel_mincha'] : $info_day['mincha']).'<br/>';
			echo $info_day['maariv'] ? 'Maariv: '. $info_day['maariv'].'<br/>' : '';
			echo $info_day['candlelighting'] ? 'Candlelighting: '. $info_day['candlelighting'].'<br/>' : '';
			echo $info_day['kol_nidre'] ? 'Kol Nidre: '. $info_day['kol_nidre'].'<br/>' : '';
			echo $info_day['havdala'] ? 'Havdala/Maariv: '. $info_day['havdala'].'<br/>' : '';
			echo $info_day['fast_ends'] ? 'Fast Ends: '. $info_day['fast_ends'].'<br/>' : '';
			echo '<br/>';
			$count++;
		}
	}
}


//////// FUNCTIONS


function erev_chag($info,$date)
{
	$info[$date]['candlelighting'] = date('g:i',get_sunset(strtotime($date),(-18*60)));
	if ($info[$date]['candlelighting'] > '7:00' 
		&& date('D',strtotime($date)) != 'Sat' 
		&& $date  > $info['holidays'][date('Y',strtotime($date))]['Pesach II']  
		&& $date != $info['holidays'][date('Y',strtotime($date))]['Erev Shavuos']
		&& $date < date('Y',strtotime($date)).'-12-31' ) {
			$info[$date]['mincha'] = '7:00';
			$info[$date]['candlelighting'] = '7:00';
	} else if (date('D',strtotime($date)) == 'Sat'){
		//$info[$date]['mincha'] = date('g:i',((60*5)*get_sunset(strtotime($date),(-25*60)))/(60*5));
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
		$info[$date]['candlelighting'] = $info[$date]['candlelighting'] ? $info[$date]['candlelighting'] : date('g:i',$shkiah_timestamp-(18*60));
		$info[$date]['mincha'] = $info[$date]['candlelighting'];
	} else if ($first_day) {
		$info[$date]['candlelighting'] = $tzeis;
		$info[$date]['havdala'] = '';
	} else {
		$info[$date]['havdala'] = $tzeis;
	}
	$info[$date]['maariv'] = '';
	return $info;
}

function get_sunset($timestamp,$offset = 0)
{
	//$suninfo = date_sun_info($timestamp, 35.11666,-89.87740);
	//print_r($suninfo);
	//return (60*(round($suninfo['sunset']/60)))+$offset;
	return (60*(round(date_sunset($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(2/6))/(60))))+$offset;
}

function get_tzeis($timestamp,$offset = 0) {
	return (60*(round(date_sunset($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(2/6))/(60))))+(50*60)+$offset;
}

function get_rttzeis($timestamp,$offset = 0) {
	return (60*(round(date_sunset($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(2/6))/(60))))+(72*60)+$offset;
}

function get_sunrise($timestamp,$offset = 0)
{
	return (60*(round(date_sunrise($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(3.5/6))/(60))))+$offset;
}

function get_alos($timestamp,$offset = 0)
{
	return (60*(round(date_sunrise($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90+(3.5/6) + 16.1)/(60))))+$offset;
}

function get_misheyakir($timestamp,$offset = 0)
{
	return get_sunrise($timestamp)-(60*60)+$offset; //return (60*(round(date_sunrise($timestamp,SUNFUNCS_RET_TIMESTAMP,35.11666,-89.87740,90.6 + 11.5)/(60))))+$offset;
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

function get_ma_by_hour($timestamp,$hour=3) //get time of hour of day
{
	$sunrise = get_sunrise($timestamp)-(72*60);
	$sunset = get_rttzeis($timestamp);
	$total_seconds_in_day = $sunset-$sunrise;
	$length_each_hour = $total_seconds_in_day / 12;
	$time_offset = $hour * $length_each_hour;
	return $sunrise+$time_offset;
}
?>