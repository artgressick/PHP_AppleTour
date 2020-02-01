<?	
	$BF = '../';
	require($BF. '_lib.php');

	//dtn: THis is the mail Mime includes
	$er = error_reporting(0); 		//dtn: This is added in so that we don't get spammed with PEAR::isError() messages in our tail -f ..
	include_once('Mail.php');		//dtn: This is the main mail addon so that we can use the mime emailer
	include_once('Mail/mime.php');	//dtn: This is the actual mime part of the emailer		
	require($BF. 'includes/_emailer.php');
	include($BF. 'includes/meta_admin.php');
?>
	</head>
<?
	$seriesinfo = fetch_database_query("SELECT ID, chrTitle, chrEmailName, chrFromEmail
									FROM EventSeries
									WHERE !EventSeries.bDeleted AND EventSeries.ID=".$_SESSION['email']['idEventSeries'], "Getting EventSeries");

	$q = "SELECT chrVenue, chrAddress, chrAddress2, chrCity, chrState, chrZip, chrCountry, chrPhone, chrRoom, intCapacity, chrContact, chrGoogle, intDropOff, txtDirections, chrTravel, chrLocation, txtNotes
		FROM Venues
		JOIN Events ON Events.idVenue=Venues.ID
		LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
		WHERE Events.idEventSeries=".$_SESSION['email']['idEventSeries']." AND Events.idVenue=".$_SESSION['email']['idVenue'];
	$venuedata = fetch_database_query($q,"Getting Event Information");

	// Lets pull all the dates for this Event
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
	FROM Events 
	JOIN EventDates ON EventDates.idEvent=Events.ID 
	WHERE Events.idEventSeries=".$_SESSION['email']['idEventSeries']." AND Events.idVenue=".$_SESSION['email']['idVenue'] ."
	ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
	
	$dateresults = database_query($q,"Grabbing All Dates for this Event Series");

	$eventDates = array();
	$fullDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	while ($rowdates = mysqli_fetch_assoc($dateresults)) {

		if($prevID != $rowdates['idEvent']) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $rowdates['idEvent'];
			$eventDates['chrDates'.$rowdates['idEvent']] = "";
			$fullDates[$rowdates['idEvent']] = '';
			$prevDate = "";
		}
			if($prevDate != date('F',strtotime($rowdates['dDate']))) {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('F',strtotime($rowdates['dDate']))." ".date('jS',strtotime($rowdates['dDate']));
			} else {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('jS',strtotime($rowdates['dDate']));
			}
			
		$prevDate = date('F',strtotime($rowdates['dDate']));
		$fullDates[$rowdates['idEvent']] .= date('l, F jS, Y',strtotime($rowdates['dDate'])).' from '.date('g:i a',strtotime($rowdates['tBegin'])).' to '.date('g:i a',strtotime($rowdates['tEnd'])).'<br />';
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));
	
	// Get Event Details for Preview
	$q = "SELECT Attendees.ID, Events.ID AS idEvent, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS dDate, EventTitles.chrName, EventTitles.txtShort, EventTitles.txtLong, 
			Signups.idStatus, Attendees.chrFirst, Attendees.chrLast, Attendees.chrEmail, Signups.chrCancel
			FROM Events
			JOIN Signups ON Signups.idEvent=Events.ID
			JOIN EventTitles on Events.idEventTitle=EventTitles.ID
			JOIN Attendees ON Signups.idUser=Attendees.ID
			WHERE Events.idEventSeries=".$_SESSION['email']['idEventSeries']." AND Events.idVenue=".$_SESSION['email']['idVenue']." AND Events.ID LIKE '".$_SESSION['email']['idEvent']."' AND !Events.bDeleted 
			AND Signups.idStatus LIKE '".$_SESSION['email']['idStatus']."' AND !Attendees.bDeleted
			ORDER BY Attendees.ID, dDate, chrName";
			
	$event_info = database_query($q, "All Event Details");

	$prev_userid = 0;
	$count = 0;
	$EventList = "";
	$emailcnt = 0;
		
	while ($row = mysqli_fetch_assoc($event_info)) {

		if ($prev_userid != $row['ID']) {

			if ($count > 0) {
				if (isset($_SESSION['email']['resend']) && $_SESSION['email']['resend'] == 1) {
					if($confirmed > 0) {
						$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=1","Get Cancel E-mail Body");
					} else if($confirmed == 0 && $cancelled == 0) {
						$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=2","Get Cancel E-mail Body");					
					} else if($confirmed == 0 && $waitlist == 0) {
						$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=3","Get Cancel E-mail Body");
					}
					$email['txtBody'] = str_replace('$FIRST_NAME',encode($pre['chrFirst']),$email['txtBody']); 
					$email['txtBody'] = str_replace('$LAST_NAME',encode($pre['chrLast']),$email['txtBody']);
					$email['txtBody'] = str_replace('$SERIES_TITLE',encode($seriesinfo['chrTitle']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_NAME',encode($venuedata['chrVenue']),$email['txtBody']);
					if($venuedata['chrAddress2'] != '') { $venuedata['chrAddress'] .= '<br />'.$venuedata['chrAddress2']; }
					$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($venuedata['chrAddress']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_CITY',encode($venuedata['chrCity']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_STATE',encode($venuedata['chrState']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($venuedata['chrZip']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($venuedata['chrCountry']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_PHONE',encode($venuedata['chrPhone']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_ROOM',encode($venuedata['chrRoom']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$venuedata['chrGoogle'],$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$venuedata['chrTravel'],$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($venuedata['txtDirections'])),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_NOTES',encode($venuedata['txtNotes']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($venuedata['chrContact']),$email['txtBody']);
					$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($venuedata['chrLocation']),$email['txtBody']);
				
					$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
					$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
					$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
					$message = $message.'<div style="border-top:1px solid #666; padding-top:10px;">'.$email['txtBody'].'</div>';
				}			
			
				sendout($pre['chrEmail'],$message);
			}

			$count++;
			$prev_userid = $row['ID'];
			$pre = $row;
			$confirmed=0;
			$waitlist=0;
			$cancelled=0;
			
			$message = "<div>".encode($_SESSION['email']['txtMsg'])."</div>";
			$message = str_replace('$FIRST_NAME',encode($row['chrFirst']),$message); 
			$message = str_replace('$LAST_NAME',encode($row['chrLast']),$message);
			$message = str_replace('$SERIES_TITLE',encode($seriesinfo['chrTitle']),$message);
			$message = str_replace('$VENUE_NAME',encode($venuedata['chrVenue']),$message);
			if($venuedata['chrAddress2'] != '') { $venuedata['chrAddress'] .= '<br />'.$venuedata['chrAddress2']; }
			$message = str_replace('$VENUE_ADDRESS',encode($venuedata['chrAddress']),$message);
			$message = str_replace('$VENUE_CITY',encode($venuedata['chrCity']),$message);
			$message = str_replace('$VENUE_STATE',encode($venuedata['chrState']),$message);
			$message = str_replace('$VENUE_POSTAL',encode($venuedata['chrZip']),$message);
			$message = str_replace('$VENUE_COUNTRY',encode($venuedata['chrCountry']),$message);
			$message = str_replace('$VENUE_PHONE',encode($venuedata['chrPhone']),$message);
			$message = str_replace('$VENUE_ROOM',encode($venuedata['chrRoom']),$message);
			$message = str_replace('$VENUE_ONLINE_MAP',$venuedata['chrGoogle'],$message);
			$message = str_replace('$VENUE_TRAVEL_URL',$venuedata['chrTravel'],$message);
			$message = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($venuedata['txtDirections'])),$message);
			$message = str_replace('$VENUE_NOTES',encode($venuedata['txtNotes']),$message);
			$message = str_replace('$VENUE_CONTACT_PERSON',encode($venuedata['chrContact']),$message);
			$message = str_replace('$VENUE_TIMEZONE',encode($venuedata['chrLocation']),$message);
		if(is_numeric($_SESSION['email']['idEvent'])) {
			$link = $PROJECT_ADDRESS."transfer.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
			$message = str_replace('$TRANSFER_LINK',$link,$message);
		}

			if (isset($_SESSION['email']['resend']) && $_SESSION['email']['resend'] == 1) {
				$allcancel = $row['idEvent'];
				if ($row['idStatus'] == 1 ) { //Confirmed
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
					$confirmed++;
				} else if ($row['idStatus'] == 2) {  //Waitlist
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=6","Get Event List Waitlist E-mail Body");
					$waitlist++;
				} else if ($row['idStatus'] == 3) {  //Cancelled
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=7","Get Event List Cancelled E-mail Body");
					$cancelled++;
				}
						
				$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($row['chrName']),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($row['txtShort'])),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($row['txtLong'])),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
				$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
				$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
				
				$EventList .= $eventemail['txtBody'];
			}			
		} else {
			if (isset($_SESSION['email']['resend']) && $_SESSION['email']['resend'] == 1) {
				$allcancel .= ','.$row['idEvent'];
				if ($row['idStatus'] == 1 ) { //Confirmed
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
					$confirmed++;
				} else if ($row['idStatus'] == 2) {  //Waitlist
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=6","Get Event List Waitlist E-mail Body");
					$waitlist++;
				} else if ($row['idStatus'] == 3) {  //Cancelled
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=7","Get Event List Cancelled E-mail Body");
					$cancelled++;
				}
						
				$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($row['chrName']),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($row['txtShort'])),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($row['txtLong'])),$eventemail['txtBody']);
				$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
				$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
				$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
				
				$EventList .= $eventemail['txtBody'];
			}
		}
	}

	if ($count > 0) { 
		if (isset($_SESSION['email']['resend']) && $_SESSION['email']['resend'] == 1) {
			if($confirmed > 0) {
				$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=1","Get Cancel E-mail Body");
			} else if($confirmed == 0 && $cancelled == 0) {
				$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=2","Get Cancel E-mail Body");					
			} else if($confirmed == 0 && $waitlist == 0) {
				$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$_SESSION['email']['idEventSeries']." AND idType=3","Get Cancel E-mail Body");
			}
			$email['txtBody'] = str_replace('$FIRST_NAME',encode($pre['chrFirst']),$email['txtBody']); 
			$email['txtBody'] = str_replace('$LAST_NAME',encode($pre['chrLast']),$email['txtBody']);
			$email['txtBody'] = str_replace('$SERIES_TITLE',encode($seriesinfo['chrTitle']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_NAME',encode($venuedata['chrVenue']),$email['txtBody']);
			if($venuedata['chrAddress2'] != '') { $venuedata['chrAddress'] .= '<br />'.$venuedata['chrAddress2']; }
			$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($venuedata['chrAddress']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_CITY',encode($venuedata['chrCity']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_STATE',encode($venuedata['chrState']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($venuedata['chrZip']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($venuedata['chrCountry']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_PHONE',encode($venuedata['chrPhone']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_ROOM',encode($venuedata['chrRoom']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$venuedata['chrGoogle'],$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$venuedata['chrTravel'],$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($venuedata['txtDirections'])),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_NOTES',encode($venuedata['txtNotes']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($venuedata['chrContact']),$email['txtBody']);
			$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($venuedata['chrLocation']),$email['txtBody']);
		
			$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
			$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
			$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
			$message = $message.'<div style="border-top:1px solid #666; padding-top:10px;">'.$email['txtBody'].'</div>';
		}			
	
		sendout($pre['chrEmail'],$message);
	} // Send E-mail for last record


function sendout($to,$message) {
	global $seriesinfo,$count;
	
	
	if(sendemail($to,$seriesinfo['chrEmailName'].' <'.$seriesinfo['chrFromEmail'].'>',$_SESSION['email']['chrSubject'],$message)) {
?>
		<script language="javascript" type="text/javascript">
				var cnt='<?=$count?>';
				if (cnt > 25) { window.parent.document.getElementById('line'+(cnt - 25)).style.display = 'none'; }
				window.parent.document.getElementById('emaillog').innerHTML += '<div id="line<?=$count?>">E-mail Sent to <strong><?=$to?></strong> - <?=$count?></div>';
		</script>
		<?
		ob_flush();
		flush();
		
		
		//End Reminder E-mail Section
		
		if (($count%50) == 0) {
			?>
			<script language="javascript" type="text/javascript">
					var cnt='<?=$count?>';
					if (cnt > 25) { window.parent.document.getElementById('line'+(cnt - 25)).style.display = 'none'; }	
					window.parent.document.getElementById('emaillog').innerHTML += '<div id="pause<?=$count?>">PAUSE FOR 1 SECOND</div>';
			</script>
			<?
			ob_flush();
			flush();
			sleep(1);
			?>
			<script language="javascript" type="text/javascript">
					var cnt='<?=$count?>';
					window.parent.document.getElementById('pause'+(<?=$count?>)).style.display = 'none';
			</script>
			<?
			ob_flush();
			flush();
		}
	} else {
?>
			<script language="javascript" type="text/javascript">
				var cnt='<?=$count?>';
				if (cnt > 25) { window.parent.document.getElementById('line'+(cnt - 25)).style.display = 'none'; }
				window.parent.document.getElementById('emaillog').innerHTML += '<div id="line<?=$count?>">Error occured sending e-mail to <strong><?=$to?></strong> - <?=$count?></div>';
		</script>
<?
		ob_flush();
		flush();
		
		
		//End Reminder E-mail Section
		
		if (($count%50) == 0) {
			?>
			<script language="javascript" type="text/javascript">
					var cnt='<?=$count?>';
					if (cnt > 25) { window.parent.document.getElementById('line'+(cnt - 25)).style.display = 'none'; }	
					window.parent.document.getElementById('emaillog').innerHTML += '<div id="pause<?=$count?>">PAUSE FOR 1 SECOND</div>';
			</script>
			<?
			ob_flush();
			flush();
			sleep(1);
			?>
			<script language="javascript" type="text/javascript">
					var cnt='<?=$count?>';
					window.parent.document.getElementById('pause'+(<?=$count?>)).style.display = 'none';
			</script>
			<?
			ob_flush();
			flush();
		}	
	
	}
}

?>
	<script language="javascript" type="text/javascript">
			var cnt='<?=$count?>';
			if (cnt > 25) { window.parent.document.getElementById('line'+(cnt - 25)).style.display = 'none'; }	
			window.parent.document.getElementById('emaillog').innerHTML += '<div>FINISHED, YOU MAY CLOSE OR MOVE ON</div>';
			
	</script>
<?
	ob_flush();
	flush();
?>