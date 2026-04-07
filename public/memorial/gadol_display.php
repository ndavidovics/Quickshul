<?php

$gedolim = json_decode(file_get_contents('https://clients6.google.com/calendar/v3/calendars/idjoj8ovak0e0qcu9g7slsq1pk@group.calendar.google.com/events?calendarId=idjoj8ovak0e0qcu9g7slsq1pk@group.calendar.google.com&singleEvents=true&timeZone=America%2FChicago&maxAttendees=1&maxResults=500&sanitizeHtml=true&key=AIzaSyBNlYH01_9Hc5S1J9vuFmu2nUqBZJNAXxs&orderBy=starttime&timeMin='.date("Y-m-d\TH:i:s\Z", strtotime("today")).'&timeMax='.date("Y-m-d\TH:i:s\Z", strtotime("today +6 hours"))));

//print_r($gedolim->items[0]);

?>
<img height="300" ALIGN="left" HSPACE="50" src="https://drive.google.com/uc?id=<?php echo $gedolim->items[0]->attachments[0]->fileId;?>"/> <h1><?php echo $gedolim->items[0]->summary;?></h1><br/>

<?php echo $gedolim->items[0]->description;?>