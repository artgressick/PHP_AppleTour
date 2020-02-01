#!/usr/bin/php
<?php
	$begin_time = microtime(true);
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');
	require($BF. 'includes/_emailer.php');

	// Lets pull all the dates for this Event Series
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
			FROM Events 
			JOIN EventDates ON EventDates.idEvent=Events.ID 
			WHERE !Events.bDeleted AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) = date_format(adddate(now(),3),'%Y-%m-%d')
			ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
			
	$results = database_query($q,"Grabbing All Dates for this Event Series");
	$eventDates = array();
	$fullDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	while ($row = mysqli_fetch_assoc($results)) {

		if($prevID != $row['idEvent']) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $row['idEvent'];
			$eventDates['chrDates'.$row['idEvent']] = "";
			$fullDates[$row['idEvent']] = '';
			$prevDate = "";
		}
			if($prevDate != date('F',strtotime($row['dDate']))) {
				if($day != 1) { $eventDates['chrDates'.$row['idEvent']] .= ", "; }
				$eventDates['chrDates'.$row['idEvent']] .= date('F',strtotime($row['dDate']))." ".date('jS',strtotime($row['dDate']));
			} else {
				if($day != 1) { $eventDates['chrDates'.$row['idEvent']] .= ", "; }
				$eventDates['chrDates'.$row['idEvent']] .= date('jS',strtotime($row['dDate']));
			}
			
		$prevDate = date('F',strtotime($row['dDate']));
		$fullDates[$row['idEvent']] .= date('l, F jS, Y',strtotime($row['dDate'])).' from '.date('g:i a',strtotime($row['tBegin'])).' to '.date('g:i a',strtotime($row['tEnd'])).'<br />';
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));	

	$q = "SELECT Attendees.ID, Events.ID AS idEvent, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS dDate, EventTitles.chrName, EventTitles.txtShort, EventTitles.txtLong, Signups.idStatus, Attendees.chrFirst, Attendees.chrLast, Attendees.chrEmail, Signups.chrCancel, 	
		EventSeries.chrTitle, Venues.chrVenue, Venues.chrAddress, Venues.chrAddress2, Venues.chrCity, Venues.chrState, Venues.chrZip, Venues.chrCountry, Venues.chrPhone, Venues.chrRoom, Venues.intCapacity, Venues.chrContact, Venues.chrGoogle, 
		Venues.intDropOff, Venues.txtDirections, Venues.chrTravel, TimeZone.chrLocation, Venues.txtNotes, EventSeries.ID as idEventSeries, EventSeries.chrEmailName, EventSeries.chrFromEmail, TimeZone.chrLocation
		FROM Events
		JOIN Venues ON Events.idVenue=Venues.ID
		JOIN Signups ON Signups.idEvent=Events.ID
		JOIN EventTitles on Events.idEventTitle=EventTitles.ID
		JOIN Attendees ON Signups.idUser=Attendees.ID
		JOIN EventSeries on Events.idEventSeries=EventSeries.ID
		JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
		WHERE !Events.bDeleted AND !EventSeries.bDeleted AND !Venues.bDeleted AND Signups.idStatus=1 AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) = date_format(adddate(now(),3),'%Y-%m-%d')
		ORDER BY Attendees.ID, idEventSeries, dDate";
		
	$Emails = database_query($q,"Getting Email Data Info");

	$prev_userid = 0;
	$count = 0;
	$pre_toemail = '';
	$EventList = "";
	$emailcnt=0;
	while ($row = mysqli_fetch_assoc($Emails)) {

		if($prev_userid != $row['ID']) {
			if($count > 0) {
				$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
				$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
				$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
//				sendemail('sobsmb@gmail.com',$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
				sendemail($pre_toemail,$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
				$emailcnt++;			
			}
			$allcancel = $row['idEvent'];
			$pre_toemail = $row['chrEmail'];
			$prev_userid = $row['ID'];
			$pre = $row;
			$EventList = '';
			$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");

			$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($row['chrName']),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($row['txtShort'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($row['txtLong'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
			$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
			$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
			
			$EventList .= $eventemail['txtBody'];
			
			$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=4","Get reminder E-mail Body");
			$pre['chrSubject'] = $email['chrSubject'];
			$email['txtBody'] = str_replace('$FIRST_NAME',encode($row['chrFirst']),$email['txtBody']); 
			$email['txtBody'] = str_replace('$LAST_NAME',encode($row['chrLast']),$email['txtBody']);
			$email['txtBody'] = str_replace('$SERIES_TITLE',encode($row['chrTitle']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_NAME',encode($row['chrVenue']),$email['txtBody']);
			if($row['chrAddress2'] != '') { $row['chrAddress'] .= '<br />'.$row['chrAddress2']; }
			$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($row['chrAddress']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_CITY',encode($row['chrCity']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_STATE',encode($row['chrState']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($row['chrZip']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($row['chrCountry']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_PHONE',encode($row['chrPhone']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_ROOM',encode($row['chrRoom']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$row['chrGoogle'],$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$row['chrTravel'],$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($row['txtDirections'])),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_NOTES',encode($row['txtNotes']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($row['chrContact']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($row['chrLocation']),$email['txtBody']);
												
		} else {
			if ($allcancel != "") { $allcancel .= ","; } // Comma Seperates for Cancel all Link
			$prev_userid = $row['ID'];
			$allcancel .= $row['idEvent'];
			$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
	
			$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($row['chrName']),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($row['txtShort'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($row['txtLong'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
			$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
			$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
			
			$EventList .= $eventemail['txtBody'];
		}
		$count++;
	}

	if ($count > 0) { 
		$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
		$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
		$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
//		sendemail('sobsmb@gmail.com',$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
		sendemail($pre['chrEmail'],$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);
		$emailcnt++;
	} // Send E-mail for last record


	$end_time = microtime(true);
	echo (round(($end_time-$begin_time)*1000)/1000)." Seconds | ".$emailcnt." e-mails sent;";
?>