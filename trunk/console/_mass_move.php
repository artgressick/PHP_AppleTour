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
	// Lets first get the list of Users
	$attendees = database_query("SELECT S.ID as idSignup, A.ID AS idUser, A.chrFirst, A.chrLast, A.chrEmail, S.chrRegLead
									FROM Signups AS S
									JOIN Attendees AS A ON S.idUser=A.ID
									WHERE S.idEvent='".$_REQUEST['idFrom']."' AND S.idStatus='2' AND !A.bDeleted","Getting User List");
/*	
	$attendees = database_query("SELECT S.ID as idSignup, A.ID AS idUser, A.chrFirst, A.chrLast, A.chrEmail, S.chrRegLead
									FROM Signups AS S
									JOIN Attendees AS A ON S.idUser=A.ID
									WHERE S.idEvent='".$_REQUEST['idFrom']."' AND S.idStatus='".$_REQUEST['idFromStatus']."' AND !A.bDeleted","Getting User List");
*/	

	$q = "SELECT E.ID, ES.chrTitle, ES.chrEmailName, ES.chrFromEmail, ET.chrName, V.chrVenue, V.chrAddress, V.chrAddress2, V.chrCity, V.chrState, V.chrZip, V.chrCountry, V.chrPhone, 
			V.chrRoom, V.intCapacity, V.chrContact, V.chrGoogle, V.intDropOff, V.txtDirections, V.chrTravel, TZ.chrLocation, V.txtNotes, EM.chrSubject, EM.txtBody
		FROM Events AS E
		JOIN EventSeries AS ES ON E.idEventSeries=ES.ID
		JOIN EventTitles AS ET ON E.idEventTitle=ET.ID
		JOIN Venues AS V ON E.idVenue=V.ID 
		LEFT JOIN TimeZone TZ ON V.idTimeZone=TZ.ID
		LEFT JOIN Emails AS EM ON ES.ID=EM.idEventSeries AND EM.idType=10
		WHERE E.ID='".$_REQUEST['idTo']."' AND !E.bDeleted AND !V.bDeleted AND !ES.bDeleted";
	$eventdata = fetch_database_query($q,"Getting Event Information");
	
	// Lets pull all the dates for this Event
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
	FROM Events 
	JOIN EventDates ON EventDates.idEvent=Events.ID 
	WHERE Events.ID='".$_REQUEST['idTo']."'
	ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
	
	$dateresults = database_query($q,"Grabbing All Dates for this Event");

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
	
	// Lets get the E-mails
	$q = "SELECT EM.txtBody 
			FROM Events AS E
			JOIN EventSeries AS ES ON E.idEventSeries=ES.ID
			JOIN Emails AS EM ON ES.ID=EM.idEventSeries
			WHERE E.ID='".$_REQUEST['idTo']."' AND EM.idType=5
		";
	$eventinfo = fetch_database_query($q,"Getting Event Info Template");

	$emailbody = $eventdata['txtBody'];
	$emailbody = str_replace('$EVENT_INFO',$eventinfo['txtBody'],$emailbody);
	$emailbody = str_replace('$SERIES_TITLE',encode($eventdata['chrTitle']),$emailbody);
	
	$emailbody = str_replace('$SERIES_TITLE',encode($eventdata['chrTitle']),$emailbody);
	$emailbody = str_replace('$VENUE_NAME',encode($eventdata['chrVenue']),$emailbody);
	if($eventdata['chrAddress2'] != '') { $eventdata['chrAddress'] .= '<br />'.$eventdata['chrAddress2']; }
	$emailbody = str_replace('$VENUE_ADDRESS',encode($eventdata['chrAddress']),$emailbody);
	$emailbody = str_replace('$VENUE_CITY',encode($eventdata['chrCity']),$emailbody);
	$emailbody = str_replace('$VENUE_STATE',encode($eventdata['chrState']),$emailbody);
	$emailbody = str_replace('$VENUE_POSTAL',encode($eventdata['chrZip']),$emailbody);
	$emailbody = str_replace('$VENUE_COUNTRY',encode($eventdata['chrCountry']),$emailbody);
	$emailbody = str_replace('$VENUE_PHONE',encode($eventdata['chrPhone']),$emailbody);
	$emailbody = str_replace('$VENUE_ROOM',encode($eventdata['chrRoom']),$emailbody);
	$emailbody = str_replace('$VENUE_ONLINE_MAP',encode($eventdata['chrGoogle']),$emailbody);
	$emailbody = str_replace('$VENUE_TRAVEL_URL',encode($eventdata['chrTravel']),$emailbody);
	$emailbody = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($eventdata['txtDirections'])),$emailbody);
	$emailbody = str_replace('$VENUE_NOTES',encode(nl2br($eventdata['txtNotes'])),$emailbody);
	$emailbody = str_replace('$VENUE_CONTACT_PERSON',encode($eventdata['chrContact']),$emailbody);
	$emailbody = str_replace('$VENUE_TIMEZONE',encode($eventdata['chrLocation']),$emailbody);
	
	$emailbody = str_replace('$EVENT_NAME',encode($eventdata['chrName']),$emailbody);
	$emailbody = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventdata['txtShort'])),$emailbody);
	$emailbody = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventdata['txtLong'])),$emailbody);
	$emailbody = str_replace('$DATES_TIMES',encode($fullDates[$_REQUEST['idTo']]),$emailbody);
	
	$count = 0;
	$emailcnt = 0;
		
	while ($row = mysqli_fetch_assoc($attendees)) {
		$tempmail = $emailbody;
		// ok Lets Update the User to cancelled for the old event
		$tmp = database_query("UPDATE Signups SET idStatus=3, dtCancel=now() WHERE ID='".$row['idSignup']."'","Update Status to Cancel");
		$newspecial = mt_rand(100000000, 9999999999);
		//Insert into New Signup
		
		//SELECT S.ID as idSignup, A.ID AS idUser, A.chrFirst, A.chrLast, A.chrEmail, S.chrRegLead
		$q = "INSERT INTO Signups SET
								idUser='".$row['idUser']."', 
								idEvent='".$eventdata['ID']."', 
								idStatus='1', 
								chrCancel='".$newspecial."', 
								dtStamp=now(), 
								chrEmailBack='".$row['chrEmail']."', 
								chrRegLead='".$row['chrRegLead']."'";
		$tmp = database_query($q,"Insert Record");
			
		$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['idUser'] . "&idE=" . $eventdata['ID'] . "&special=" . $newspecial);
		$tempmail = str_replace('$CANCEL_ALL',$Cancel,$tempmail);
		$tempmail = str_replace('$CANCEL_EVENT',$Cancel,$tempmail);
		$tempmail = str_replace('$FIRST_NAME',encode($row['chrFirst']),$tempmail); 
		$tempmail = str_replace('$LAST_NAME',encode($row['chrLast']),$tempmail);
		$count++;
		sendout($row['chrEmail'],$tempmail);
	}

function sendout($to,$message) {
	global $eventdata,$count;
	
	
	if(sendemail($to,$eventdata['chrEmailName'].' <'.$eventdata['chrFromEmail'].'>',$eventdata['chrSubject'],$message)) {
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
					window.parent.document.getElementById('emaillog').innerHTML += '<div id="pause<?=$count?>">PAUSE FOR 5 SECOND</div>';
			</script>
			<?
			ob_flush();
			flush();
			sleep(5);
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
					window.parent.document.getElementById('emaillog').innerHTML += '<div id="pause<?=$count?>">PAUSE FOR 5 SECOND</div>';
			</script>
			<?
			ob_flush();
			flush();
			sleep(5);
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