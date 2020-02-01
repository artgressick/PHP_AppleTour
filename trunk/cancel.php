<?php
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');

	(isset($_REQUEST['d']) ? $cancelURL = $_REQUEST['d'] : ErrorPage());	

	parse_str(base64_decode($cancelURL),$info);

	(!isset($info['idE']) || !isset($info['ID']) || !isset($info['special']) ? ErrorPage() : "");
	($info['idE']=="" || $info['ID']=="" || $info['special']=="" ? ErrorPage() : "");

	//Grab EventSeries Information
	$temp = fetch_database_query("SELECT EventSeries.ID, EventSeries.chrTitle, EventSeries.chrImageName, chrEmailName, chrFromEmail
									FROM EventSeries
									JOIN Events ON EventSeries.ID=Events.idEventSeries
									JOIN Signups ON Signups.idEvent=Events.ID
									WHERE !EventSeries.bDeleted AND Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."'"
									, "Getting EventSeries and Referral Information");
	($temp['ID'] == "" ? ErrorPage() : "");
	
	$_SESSION['chrTitle'] = $temp['chrTitle'];

	if (isset($_POST['submit']) && $_POST['submit'] == "Cancel Event(s)") {
		require($BF. 'includes/_emailer.php');
		$q = "SELECT Attendees.ID, chrFirst, chrLast, chrEmail
				FROM Signups
				JOIN Attendees ON Attendees.ID=Signups.idUser
				WHERE Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.idStatus != 3 AND Signups.chrCancel='".$info['special']."'";
		$canceluser = fetch_database_query($q,"Getting Cancel User Info");	
		$Events = explode(',', $info['idE']);
		$events_cancelled = "";
		
		//Gets old status for each event
		$q = "SELECT idEvent
				FROM Signups
				WHERE Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."' AND Signups.idStatus = 1";
		$oldconfirmed = database_query($q, "Getting Old Status");

		$q = "SELECT chrVenue, chrAddress, chrAddress2, chrCity, chrState, chrZip, chrCountry, chrPhone, chrRoom, intCapacity, chrContact, chrGoogle, intDropOff, txtDirections, chrTravel, chrLocation, txtNotes
			FROM Venues
			JOIN Events ON Events.idVenue=Venues.ID
			JOIN Signups on Signups.idEvent=Events.ID
			LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
			WHERE Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."' AND Signups.idStatus != 3
			LIMIT 1";
		$venuedata = fetch_database_query($q,"Getting Event Information");
		
		$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
		FROM Events 
		JOIN EventDates ON EventDates.idEvent=Events.ID 
		WHERE Events.ID IN (".$info['idE'].")
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
		
		$EventList = "";
		foreach($Events as $row) {

			$q = "SELECT Events.ID, chrName, txtShort, txtLong
				FROM Events
				JOIN EventTitles on Events.idEventTitle=EventTitles.ID
				JOIN Signups ON Signups.idEvent=Events.ID
				WHERE Events.ID=".$row." AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."' AND Signups.idStatus != 3";
				
			$eventdata = fetch_database_query($q,"Getting Event Info");

			$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$temp['ID']." AND idType=7","Get Event List cancel E-mail Body");
			
			$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventdata['chrName']),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventdata['txtShort'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventdata['txtLong'])),$eventemail['txtBody']);
			$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row]),$eventemail['txtBody']);
				
			$EventList .= $eventemail['txtBody'];
		}

		//Cancels Registration
		$q = "UPDATE Signups
			SET idStatus=3, dtCancel=now()
			WHERE Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."' AND Signups.idStatus != 3";
		$cancelquery = database_query($q,"Cancel Registration");

		$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$temp['ID']." AND idType=3","Get Cancel E-mail Body");

		$email['txtBody'] = str_replace('$FIRST_NAME',encode($canceluser['chrFirst']),$email['txtBody']); 
		$email['txtBody'] = str_replace('$LAST_NAME',encode($canceluser['chrLast']),$email['txtBody']);
		$email['txtBody'] = str_replace('$SERIES_TITLE',encode($temp['chrTitle']),$email['txtBody']);
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
		
		sendemail($canceluser['chrEmail'],$temp['chrEmailName'].' <'.$temp['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);
		
		$numconfirmedevents = mysqli_num_rows($oldconfirmed);
		if($numconfirmedevents > 0) {
			$eventIDs = '';
			
			while ($ids = mysqli_fetch_assoc($oldconfirmed)) {
				$eventIDs .= $ids['idEvent'].',';
			}
			$eventIDs = substr($eventIDs,0,-1);
			$q = "SELECT Attendees.ID, Attendees.chrFirst, Attendees.chrLast, Signups.chrCancel, Attendees.chrEmail, chrName, txtShort, txtLong,  
					Events.ID as idEvent, Signups.ID AS idSignup
					,(SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus = 1) AS intCount
					FROM Signups
					JOIN Attendees ON Attendees.ID=Signups.idUser
					JOIN Events ON Signups.idEvent=Events.ID
					JOIN EventTitles on Events.idEventTitle=EventTitles.ID
					WHERE Signups.idEvent IN (".$eventIDs.") AND Signups.idStatus=2 AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) > now()
					ORDER BY Signups.dtStamp
					LIMIT ".$numconfirmedevents;
					
			$userinfo = database_query($q,"Checking for Waitlisters");
			$prev_userid = 0;
			$count = 0;
			$pre_toemail = '';
			$EventList = "";
			while ($row = mysqli_fetch_assoc($userinfo)) {
				if($prev_userid != $row['ID']) {
					if($count > 0) {
						$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
						$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
						$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
						sendemail($pre_toemail,$temp['chrEmailName'].' <'.$temp['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);			
					}
					$allcancel = $row['idEvent'];
					$pre_toemail = $row['chrEmail'];
					$prev_userid = $row['ID'];
					$pre = $row;
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$temp['ID']." AND idType=5","Get Event List Confirmed E-mail Body");

					$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventdata['chrName']),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventdata['txtShort'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventdata['txtLong'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
					$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
					$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
					
					$EventList .= $eventemail['txtBody'];
					
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$temp['ID']." AND idType=9","Get Cancel E-mail Body");
					
					$email['txtBody'] = str_replace('$FIRST_NAME',encode($row['chrFirst']),$email['txtBody']); 
					$email['txtBody'] = str_replace('$LAST_NAME',encode($row['chrLast']),$email['txtBody']);
					$email['txtBody'] = str_replace('$SERIES_TITLE',encode($temp['chrTitle']),$email['txtBody']);
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
				} else {
					if ($allcancel != "") { $allcancel .= ","; } // Comma Seperates for Cancel all Link
					$allcancel .= $row['idEvent'];
					$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$temp['ID']." AND idType=5","Get Event List Confirmed E-mail Body");

					$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventdata['chrName']),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventdata['txtShort'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventdata['txtLong'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
					$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
					$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
					
					$EventList .= $eventemail['txtBody'];
				}
				$q = "UPDATE Signups
						SET idStatus=1
						WHERE Signups.idEvent=".$row['idEvent']." AND Signups.idUser=".$row['ID']." AND Signups.chrCancel='".$row['chrCancel']."' AND Signups.idStatus = 2";
				database_query($q,"Upgrade to confirmed Registration");
				
				$count++;
			}
			
			if($count > 0) { 
				$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
				$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
				$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
				sendemail($pre['chrEmail'],$temp['chrEmailName'].' <'.$temp['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);
			}
		}
		
		
		header("Location: ".$BF."cancelsuccess.php?L=".base64_encode("idEventSeries=".$temp['ID']));
		die();
	}
		include($BF. 'components/list/sortList.php'); 
	
	
		
	$q = "SELECT Events.ID, chrName, Signups.idStatus, (SELECT tBegin FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tBegin, (SELECT tEnd FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tEnd
		FROM Signups
		JOIN Events ON Signups.idEvent=Events.ID
		JOIN EventTitles on Events.idEventTitle=EventTitles.ID
		WHERE Signups.idEvent IN (".$info['idE'].") AND Signups.idUser=".$info['ID']." AND Signups.chrCancel='".$info['special']."' AND Signups.idStatus != 3";
		
	$events = database_query($q,"Getting Event Info");
	
	// Lets pull all the dates for this Event Series
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
			FROM Events 
			JOIN EventDates ON EventDates.idEvent=Events.ID 
			WHERE Events.ID IN (".$info['idE'].")
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
	
	
	include('includes/top.php');
	$showbutton = true;
?>
<!-- This is the main body of the page.-->
<div class="main">
	<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
	  <tr>
		<td height="50" colspan="3"><img src="<?=$PROJECT_ADDRESS.'images/'.$temp['chrImageName']?>" /></td>
	  </tr>
	  <tr>
		<td width="7" height="7"><img src="images/corner_top_left.gif" width="7" height="7" /></td>
		<td width="786" height="7" background="images/line_top.gif"><img src="images/line_top.gif" width="7" height="7" /></td>
		<td width="7" height="7"><img src="images/corner_top_right.gif" width="7" height="7" /></td>
	  </tr>
	  <tr>
		<td width="7" background="images/line_left.gif"><img src="images/line_left.gif" width="7" height="7" /></td>
		<td width="786" bgcolor="#ebebeb"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="100%">
				<form id="form1" name="form1" method="post">
				<div class="maintitle">Cancel Registration.</div>
				<div class="maintitletext">Please Confirm that you would like to cancel the following Event(s), and click the Cancel Button at the bottom.</div>
	
				<div style="padding-left:7px; padding-right:7px;">
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<th>Event Title</th>
							<th>Date(s)</th>
							<th>Begin Time</th>		
							<th>End Time</th>
							<th>Signed Up Status</th>
						</tr>
				
				<?
				$count=0;
				$checked="";
				while ($row = mysqli_fetch_assoc($events)) {
								
					if ( $row['idStatus'] == 1) {
						$Status = "Seat Reserved";
					} else {
						$Status = "Wait-Listed";
					}
				
				?>
						<tr class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'>
							<td><?=$row['chrName']?></td>
							<td><?=$eventDates['chrDates'.$row['ID']]?></td>
							<td><?=date('g:i a',strtotime($row['tBegin']))?></td>
							<td><?=date('g:i a',strtotime($row['tEnd']))?></td>
							<td><?=$Status?></td>
						</tr>
				
<?
					}
					if ($count == 0) {
						$showbutton = false;
?>
						<tr class='ListLineOdd'>
							<td colspan='5' align="center" height="20">You have already cancelled.</td>
						</tr>
<?				
					}

?>
				
					</table>
				</div>
<?
					if ($showbutton == true) {
?>
				<div style="padding-left:7px; padding-right:7px; padding-top:10px;">
					<input type="submit" id="submit" name="submit" value="Cancel Event(s)" />
				</div>
<?
					}
?>
				</form>
			</td>
		</tr>
		</table></td>
		<td width="7" background="images/line_right.gif"><img src="images/line_right.gif" width="7" height="7" /></td>
	  </tr>
	  <tr>
		<td width="7" height="7"><img src="images/corner_bottom_left.gif" width="7" height="7" /></td>
		<td width="786" height="7" background="images/line_bottom.gif"><img src="images/line_bottom.gif" width="7" height="7" /></td>
		<td width="7" height="7"><img src="images/corner_bottom_right.gif" width="7" height="7" /></td>
	  </tr>
	</table>
</div>
<!-- This is the bottom of the body -->
<?
	include('includes/bottom.php');
?>



