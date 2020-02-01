<?php
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');
	
	if(isset($_REQUEST['L'])) { $encodedURL = $_REQUEST['L']; } else { ErrorPage(); }
	if($encodedURL == "")  { ErrorPage(); }
	parse_str(base64_decode($encodedURL),$info);
	if(!isset($info['idEventSeries']) || !isset($info['idCheck']) || !isset($info['ID']))  { ErrorPage(); }
	if($info['idEventSeries'] == "" || $info['ID'] == "") { ErrorPage(); }

	
	$error_message = array();
	//Grab EventSeries Information
	$temp = fetch_database_query("SELECT ID, chrTitle, chrImageName, chrLandingText, chrGroupBy, bPrivate, chrEmailName, chrFromEmail
									FROM EventSeries
									WHERE !EventSeries.bDeleted AND EventSeries.ID=".$info['idEventSeries'], "Getting EventSeries and Referral Information");
									
									
	$_SESSION['chrTitle'] = $temp['chrTitle'];

// checks to see if user submitted data from page, if not display registration page
if (isset($_POST['Submit'])) {
	
	$error_message = array();
	// Did the User enter all required information?
	if (@$_POST['signup']=="") { field_blank("selectevent"); $_POST['signup'] = ""; }
	if ($_POST['chrFirst'] == "" ) { field_blank("chrFirst"); }
	if ($_POST['chrLast'] == "") { field_blank("chrLast"); }
if ( $info['idEventSeries'] != 3 ) {	
	if ($_POST['chrAddress'] == "") { field_blank("chrAddress"); }
	if ($_POST['chrCity'] == "") { field_blank("chrCity"); }
	if ($_POST['chrCountry'] == 'US' || $_POST['chrCountry'] == 'CA') {
		if ($_POST['chrState'] == "") { field_blank("chrState"); }
	}
	if ($_POST['chrZip'] == "") { field_blank("chrZip"); }
}
	if ($_POST['chrEmail'] == "") { field_blank("chrEmail"); } 
	else if(!preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",$_POST['chrEmail'],$matches)) { field_blank("chrEmailnotvalid"); }

	if (count($error_message) == 0) {

		// Checks to see if Attendee is already signed up for these events
		$q = "SELECT Attendees.ID
				FROM Signups
				JOIN Attendees ON Attendees.ID=Signups.idUser
				WHERE !Attendees.bDeleted AND Signups.idEvent IN (". implode(",", $_POST['signup']).") AND Attendees.chrEmail='".$_POST['chrEmail']."' AND Signups.idStatus != 3";
		$emailcheck = database_query($q,"Checking Email Address for Use on these Events");

		$test = mysqli_num_rows($emailcheck);
			if ($test != 0) {
				field_blank("emailused");
				$userinfo = $_POST;
			} else {
			
				//Checks for exsiting useraccount to use information else insert user then sets signup.
				$q = "SELECT Attendees.ID, Attendees.chrFirst, Attendees.chrLast
						FROM Attendees
						JOIN Signups ON Attendees.ID=Signups.idUser
						JOIN Events ON Signups.idEvent=Events.ID
						WHERE !Attendees.bDeleted AND Attendees.chrEmail='".$_POST['chrEmail']."' AND Events.bShow AND Events.idEventSeries=".$temp['ID'];
				$userinfo = fetch_database_query($q,"Checking for Existing Account");	

				if($userinfo['ID'] == "") {
					if($userinfo['ID'] == 0) {
						$q = "INSERT INTO Attendees SET 
								 chrFirst='".	 encode($_POST['chrFirst']) ."',
								 chrLast='".	 encode($_POST['chrLast']) ."',
								 chrAddress='".  encode($_POST['chrAddress']) ."',			 
								 chrAddress1='". encode($_POST['chrAddress1']) ."',
								 chrCity='".	 encode($_POST['chrCity']) ."',
								 chrState='".	 $_POST['chrState'] ."',
								 chrZip='".	 	 strip_quotes($_POST['chrZip']) ."',
								 chrCountry='".  $_POST['chrCountry'] ."',
								 chrCompany='".  encode($_POST['chrCompany']) ."',
								 chrPhone='".	 strip_quotes($_POST['chrPhone']) ."',
								 chrEmail='". 	 $_POST['chrEmail'] ."',
								 bApple='".	 	 $_POST['bApple'] ."',
								 intFindout='".	 $_POST['findout'] ."',
								 intCompanyMatches='".	 	 $_POST['companymatches'] ."',
								 intEditingSystem='".	 	 $_POST['editingsystem'] ."',
								 intQ1='". 		 $_POST['intQ1'] ."',
								 chrQ1other='".  encode($_POST['chrQ1other']) ."',
								 intQ2='". 		 $_POST['intQ2'] ."',
								 chrQ2other='".  encode($_POST['chrQ2other']) ."',
								 intQ3='". 		 $_POST['intQ3'] ."',
								 chrQ3other='".  encode($_POST['chrQ3other']) ."',
								 chrAgency='".   encode($_POST['chrAgency']) ."'";
								 
			
						database_query($q,"Insert Attendee");
						
						global $mysqli_connection;
						$userinfo['ID'] = mysqli_insert_id($mysqli_connection);
						$userinfo['chrFirst'] = encode($_POST['chrFirst']);
						$userinfo['chrLast'] = encode($_POST['chrLast']);

					}
				}		
		
				
				// Check above query and use existing information else create new entry
				
				// Check to see if class filled up while the attendee was filling out their information

				$q = "SELECT Events.ID, Venues.intCapacity, Venues.intDropOff,
						(SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus != 3) AS intCount
					  FROM Events
					  JOIN Venues ON Events.idVenue=Venues.ID
					  WHERE Events.ID IN (". implode(",", $_POST['signup']).") AND Events.bShow";
				
				$eventscap = database_query($q,"Getting Event Signup Count");
				
				while ($row = mysqli_fetch_assoc($eventscap)) {
					if ( $row['intCount'] < $row['intCapacity'] + round($row['intCapacity'] * ($row['intDropOff'] / 100))) {
						$_POST['status'.$row['ID']] = 1;
					} else {
						$_POST['status'.$row['ID']] = 2;
					}
				}
							
				$str="";
				
				$special = mt_rand(100000000, 9999999999);
				$status = array();
				foreach ($_POST['signup'] as $row) {

					// Check to see if class filled up or a spot became avaliable while the attendee was filling out their information
					$q = "SELECT Events.ID, Venues.intCapacity, Venues.intDropOff, 
							(SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus != 3) AS intCount
						  FROM Events
						  JOIN Venues ON Events.idVenue=Venues.ID
						  WHERE Events.bShow AND Events.ID = ".$row;
					
					$eventcap = fetch_database_query($q,"Getting Event Signup Count");
					
					if($str != "") { $str .= ","; }
					
					if( $eventcap['intCount'] < $eventcap['intCapacity'] + round($eventcap['intCapacity'] * ($eventcap['intDropOff'] / 100))) {
						$str .= "('".$userinfo['ID']."','".$row."','1','".$special."',now(), '".$_POST['chrEmail']."','".$info['chrRegCode']."')";
						$status[$row] = 1;
					} else {
						$str .= "('".$userinfo['ID']."','".$row."','2','".$special."',now(), '".$_POST['chrEmail']."','".$info['chrRegCode']."')";
						$status[$row] = 2;
					}
				}
				
				database_query("INSERT Signups (idUser, idEvent, idStatus, chrCancel, dtStamp, chrEmailBack, chrRegLead) VALUES ".$str,"Insert Signup Values");

				
				//Setup and Send E-mail to Member
				// Querys to grab information for e-mail

				// Get Venue Information
				$q = "SELECT chrVenue, chrAddress, chrAddress2, chrCity, chrState, chrZip, chrCountry, chrPhone, chrRoom, intCapacity, chrContact, chrGoogle, intDropOff, txtDirections, chrTravel, chrLocation, txtNotes
					FROM Venues
					JOIN Events ON Events.idVenue=Venues.ID
					JOIN Signups on Signups.idEvent=Events.ID
					LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
					WHERE Signups.chrCancel=".$special." AND Events.bShow AND Signups.idUser=".$userinfo['ID'];
				$venuedata = fetch_database_query($q,"Getting Event Information");
				
				//Grab Events Series Title
				$q = "SELECT chrTitle
					FROM Events
					JOIN Signups ON Signups.idEvent=Events.ID
					JOIN EventSeries on Events.idEventSeries=EventSeries.ID
					WHERE Signups.chrCancel=".$special." AND Events.bShow AND Signups.idUser=".$userinfo['ID'];
				$seriesdata = fetch_database_query($q,"Getting Event Information");
			
				// Clear out variables
				$EventList = "";
				$allcancel = "";
				$events_list = '';
				$confirmed = 0;
				$waitlisted = 0;
				foreach ($_POST['signup'] as $row) {

					$q = "SELECT Events.ID, chrName, txtShort, txtLong
						FROM Events
						JOIN EventTitles on Events.idEventTitle=EventTitles.ID AND Events.bShow
						WHERE Events.ID=".$row;
						
					$eventdata = fetch_database_query($q,"Getting Event Info");
					
					$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $userinfo['ID'] . "&idE=" . $row . "&special=" . $special);
		
					if ($allcancel != "") { $allcancel .= ","; } // Comma Seperates for Cancel all Link
			
					$allcancel .= $row;
					
					// Lets pull all the dates for this Event
					$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
					FROM Events 
					JOIN EventDates ON EventDates.idEvent=Events.ID 
					WHERE Events.ID=".$row."  AND Events.bShow
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
		
					//Is this a Waitlist or Confirmed?
						
					if ($status[$row] == 1 ) { //Confirmed
						$confirmed++;
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$info['idEventSeries']." AND idType=5","Get Event List E-mail Body");
					} else {  //Waitlist
						$waitlisted++;
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$info['idEventSeries']." AND idType=6","Get Event List E-mail Body");
					}

					$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventdata['chrName']),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventdata['txtShort'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventdata['txtLong'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row]),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
					
					$EventList .= $eventemail['txtBody']; 
				}		
				if(!$temp['bPrivate']) {
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$info['idEventSeries']." AND idType=".($confirmed > 0 ? '1' : '2'),"Get Normal E-mail Body");
				} else {
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$info['idEventSeries']." AND idType=8","Get Override E-mail Body");
				}
				// Setup e-mail depending on the events the attendee signed up for
				$email['txtBody'] = str_replace('$FIRST_NAME',encode($userinfo['chrFirst']),$email['txtBody']); 
				$email['txtBody'] = str_replace('$LAST_NAME',encode($userinfo['chrLast']),$email['txtBody']);
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
				$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $userinfo['ID'] . "&idE=" . $allcancel . "&special=" . $special);
				$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
				$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
				
				require($BF. 'includes/_emailer.php');
				sendemail($_POST['chrEmail'],$temp['chrEmailName'].' <'.$temp['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);

//				echo $email['txtBody'];
				//Redirects to Thank You Page where E-mail is sent, also pass the UserID so we can grab the information
				$_SESSION['idEventSeries'] = $info['idEventSeries'];
				$_SESSION['User'] = $userinfo['ID'];
		
				header("Location: ".$BF."thankyou.php");
				die();
			}
	} else { $userinfo = $_POST; }
} else { 
	$userinfo = 0;
	database_query("UPDATE EventSeries SET intHit = (intHit + 1) WHERE ID = ".$info['idEventSeries'], "Update Signup Hit Count");
	if($info['chrRegCode'] != '') {
		$hits = fetch_database_query("SELECT ID, intHits FROM RegLeads WHERE chrCode='".$info['chrRegCode']."'","Getting Hit Count");
		if($hits['ID'] != '') {
			database_query("UPDATE RegLeads SET intHits=".($hits['intHits'] + 1)." WHERE ID=".$hits['ID'],"Update Hit Count");
		} else {
			database_query("INSERT INTO RegLeads SET chrLead='UNKNOWN',chrCode='".$info['chrRegCode']."', intHits=1","Insert Unknown Lead");
		}
	}
}

	include($BF. 'components/states.php');	
	include($BF. 'components/countries.php');

include($BF. 'components/list/sortList.php'); 

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?	

$q = "SELECT Events.ID, (SELECT tBegin FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tBegin, (SELECT tEnd FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tEnd, chrName, txtShort, txtLong,
	(SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus != 3) AS intCount
	FROM Events
	JOIN EventSeries ON Events.idEventSeries=EventSeries.ID
	JOIN Venues ON Events.idVenue=Venues.ID
	JOIN EventTitles on Events.idEventTitle=EventTitles.ID
	WHERE Events.".$temp['chrGroupBy']."=".$info['ID']." AND Events.bShow AND EventSeries.ID='".$info['idEventSeries']."' AND !EventSeries.bDeleted AND !Events.bDeleted AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) >= DATE_FORMAT(NOW(),'%Y-%m-%d')
	ORDER BY tBegin";
$events = database_query($q,"Getting Events Information");

	// Lets pull all the dates for this Event Series
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
			FROM Events 
			JOIN EventDates ON EventDates.idEvent=Events.ID AND Events.".$temp['chrGroupBy']."=".$info['ID']."
			WHERE Events.idEventSeries='".$info['idEventSeries']."'  AND Events.bShow
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



$q = "SELECT chrVenue, chrAddress, chrAddress2, chrCity, chrState, chrZip, chrCountry, chrPhone, chrRoom, intCapacity, chrContact, chrGoogle, intDropOff, txtDirections, chrTravel, chrLocation
	FROM Venues
	JOIN Events on Events.idVenue=Venues.ID
	LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
	WHERE Events.".$temp['chrGroupBy']."=".$info['ID']." AND !Events.bDeleted AND Events.idEventSeries='".$info['idEventSeries']."' AND Events.bShow";
$venueinfo = fetch_database_query($q,"Getting Venue Information");

include($BF. 'includes/top.php');
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
	<td width="786" bgcolor="#ebebeb"><form id="form1" name="form1" method="post" action="">
	<?
	if (count($error_message) > 0) {
		foreach ($error_message as $error) {
			echo $error;
		}
	}?>
		<div class="maintitle"><strong><?=decode($temp['chrTitle'])?></strong> at <strong><?=decode($venueinfo['chrVenue'])?></strong></div>
	<div style="padding-left:7px; padding-right:7px;">
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>
				<th style="width:5px;">&nbsp;</th>
				<th>Event Title</th>
				<th>Date(s)</th>
				<th>Begin Time</th>		
				<th>End Time</th>
				<th>Sign Up Status</th>
			</tr>
	
	<?
	$count=0;
	$checked="";
	while ($row = mysqli_fetch_assoc($events)) {
	
		if ( $row['intCount'] < $venueinfo['intCapacity'] + round($venueinfo['intCapacity'] * ($venueinfo['intDropOff'] / 100))) {
			$Status = "<strong>Reserve Seat</strong>";
		} else {
			$Status = "<span style='color:#FF0000;'><strong>Join the Wait List</strong></span>";
		}
	
	?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style="vertical-align:middle; cursor: pointer; width:5px;" id='qhTitle<?=$row['ID']?>a'><input type="checkbox" id="signup<?=$row['ID']?>" name="signup[]" value="<?=$row['ID']?>" <?=( $info['idCheck'] != "" && $info['idCheck'] == 0 ? "checked='checked'" : ( $count == (isset($_POST['signup']) ? in_array($row['ID'],$userinfo['signup']) : $info['idCheck']) ? "checked='checked'" : "" ) )?> />
				</td>
				<td style='cursor: pointer;' onclick='quickHideG("<?=$row['ID']?>a");' id='qhTitle<?=$row['ID']?>a'><?=$row['chrName']?></td>
				<td style='cursor: pointer; white-space:nowrap;' onclick='quickHideG("<?=$row['ID']?>a");' id='qhTitle<?=$row['ID']?>a'><?=$eventDates['chrDates'.$row['ID']]?></td>
				<td style='cursor: pointer;' onclick='quickHideG("<?=$row['ID']?>a");' id='qhTitle<?=$row['ID']?>a'><?=date('g:i a',strtotime($row['tBegin']))?></td>
				<td style='cursor: pointer;' onclick='quickHideG("<?=$row['ID']?>a");' id='qhTitle<?=$row['ID']?>a'><?=date('g:i a',strtotime($row['tEnd']))?></td>
				<td style='cursor: pointer;' onclick='quickHideG("<?=$row['ID']?>a");' id='qhTitle<?=$row['ID']?>a'><?=$Status?></td>
			</tr>
			<tr id='qhBody<?=$row['ID']?>a' class='<?=($count-1%2?'ListLineOdd':'ListLineEven')?>' style='display:none; border-top: 1px;'>
				<td colspan="6" style="padding:5px;">Event Description:
					<div style="border:#999999 solid 1px;padding:3px;"><?=nl2br($row['txtLong'])?><p>Date(s) and Time(s) for this Session:<br /><?=$fullDates[$row['ID']]?></p></div>
				</td>
			</tr>
	
	<?	}  ?>
		</table>
		<span class="textboxrequired">(Click on the title for more details)</span>
	</div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="50%">
							<div class="formfield">
							<span class="textboxname">First Name</span> <span class="textboxrequired">(Required)</span> <br />
							<input name="chrFirst" type="text" id="chrFirst" size="35" maxlength="50" value="<?=encode($userinfo['chrFirst'])?>" />
							</div></td>
						<td width="50%"><div class="formfield"> <span class="textboxname">Last Name</span> <span class="textboxrequired">(Required)</span> <br />
									<input name="chrLast" type="text" id="chrLast" size="35" maxlength="50" value="<?=encode($userinfo['chrLast'])?>" />
						</div></td>
					</tr>
<?
	if ( $info['idEventSeries'] != 3 ) {
?>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Company Name</span><br />
									<input name="chrCompany" type="text" id="chrCompany" size="35" maxlength="75" value="<?=encode($userinfo['chrCompany'])?>" />
						</div></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Address</span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrAddress" type="text" id="chrAddress" size="35" maxlength="75" value="<?=encode($userinfo['chrAddress'])?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Address 2</span><br />
									<input name="chrAddress1" type="text" id="chrAddress1" size="35" maxlength="75" value="<?=encode($userinfo['chrAddress1'])?>" />
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">City</span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrCity" type="text" id="chrCity" size="35" maxlength="45" value="<?=encode($userinfo['chrCity'])?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">State/Province</span> <span class="textboxrequired">(Required for US & CA)</span><br />
										<select class='FormField' id="chrState" name='chrState'>
												<option value="">Select from list</option>
											<?	foreach($states as $st => $name) { ?>
												<option value='<?=@$st?>' <?=($userinfo['chrState'] == $st ? "selected='selected'": "" )?>><?=$name?></option>
											<?	} ?>
										</select>
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Postal Code </span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrZip" type="text" id="chrZip" size="35" maxlength="25" value="<?=strip_quotes($userinfo['chrZip'])?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Country </span><span class='textboxrequired'>(Required)</span></div>
										 <select class='FormField' id="chrCountry" name='chrCountry'>
											<?	foreach($countries as $cy => $name) { ?>
												<option value='<?=@$cy?>' <?=($userinfo['chrCountry'] == $cy ? 'selected="selected"' : '')?>><?=$name?></option>
											<?	} ?>
										</select>
									</div></td>
					</tr>
<?
	}
?>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Telephone</span><br />
									<input name="chrPhone" type="text" id="chrPhone" size="35" maxlength="25" value="<?=strip_quotes($userinfo['chrPhone'])?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Email</span> <span class="textboxrequired">(Required to send confirmation email.)</span><br />
									<input name="chrEmail" type="text" id="chrEmail" size="35" maxlength="75" value="<?=$userinfo['chrEmail']?>" />
						</div></td>
					</tr>
					<tr>
						<td colspan="2"></td>
					</tr>
					
<?
  if($info['idEventSeries'] == 3 ) {
?>
					<tr>
						<td colspan='2'>
							<div class="formfield">
								<span class="textboxname">Agency Name</span> <span class="textboxrequired">(Required)</span> <br />
								<select class='FormField' id="chrAgency" name="chrAgency">
									<option value=""<?=($userinfo['chrAgency'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
									<option value="The Geller Agency"<?=($userinfo['chrAgency'] == "The Geller Agency" ? " selected='selected'" : "")?>>The Geller Agency</option>
									<option value="Gersh"<?=($userinfo['chrAgency'] == "Gersh" ? " selected='selected'" : "")?>>Gersh</option>
									<option value="ICM"<?=($userinfo['chrAgency'] == "ICM" ? " selected='selected'" : "")?>>ICM</option>
									<option value="Innovative Artists"<?=($userinfo['chrAgency'] == "Innovative Artists" ? " selected='selected'" : "")?>>Innovative Artists</option>
									<option value="iTalent"<?=($userinfo['chrAgency'] == "iTalent" ? " selected='selected'" : "")?>>iTalent</option>
									<option value="Jacob and Kole"<?=($userinfo['chrAgency'] == "Jacob and Kole" ? " selected='selected'" : "")?>>Jacob and Kole</option>
									<option value="Mirisch"<?=($userinfo['chrAgency'] == "Mirisch" ? " selected='selected'" : "")?>>Mirisch</option>
									<option value="Montana"<?=($userinfo['chrAgency'] == "Montana" ? " selected='selected'" : "")?>>Montana</option>
									<option value="Murtha"<?=($userinfo['chrAgency'] == "Murtha" ? " selected='selected'" : "")?>>Murtha</option>
									<option value="Paradigm"<?=($userinfo['chrAgency'] == "Paradigm" ? " selected='selected'" : "")?>>Paradigm</option>
									<option value="Sheldon Prosnit"<?=($userinfo['chrAgency'] == "Sheldon Prosnit" ? " selected='selected'" : "")?>>Sheldon Prosnit</option>
									<option value="Skouras"<?=($userinfo['chrAgency'] == "Skouras" ? " selected='selected'" : "")?>>Skouras</option>
									<option value="UTA"<?=($userinfo['chrAgency'] == "UTA" ? " selected='selected'" : "")?>>UTA</option>
									<option value="Independent"<?=($userinfo['chrAgency'] == "Independent" ? " selected='selected'" : "")?>>Independent</option>
									<option value="Other"<?=($userinfo['chrAgency'] == "Other" ? " selected='selected'" : "")?>>Other (Enter Agency Name in Box Below)</option>
								</select>
							</div>
							<div class="formfield"> <span class="textboxname">Other Agency</span><br />
								<input name="chrAgencyOther" type="text" id="chrAgencyOther" size="35" maxlength="25" value="<?=strip_quotes($userinfo['chrAgencyOther'])?>" />
							</div>
						</td>
					</tr>
<?
	}
?>
<?
	if ( $info['idEventSeries'] == 1 ) {
?>
					<tr>

						<td><div class="formfield"><span class="textboxname">How did you find out about this seminar?</span><br />
							<select class='FormField' id="findout" name="findout">
								<option value=""<?=($userinfo['findout'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['findout'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour Website</option>
								<option value="2"<?=($userinfo['findout'] == "2" ? " selected='selected'" : "")?>>Third-party website</option>
								<option value="3"<?=($userinfo['findout'] == "3" ? " selected='selected'" : "")?>>Apple Hot News Website</option>
								<option value="4"<?=($userinfo['findout'] == "4" ? " selected='selected'" : "")?>>Apple eNews email</option>
								<option value="5"<?=($userinfo['findout'] == "5" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour email</option>
								<option value="6"<?=($userinfo['findout'] == "6" ? " selected='selected'" : "")?>>Other source</option>
							</select>
							
						</div></td>
						<td><div cl
						ass="formfield"><span class="textboxname">What type of company or institution most closely matches your work?</span><br />
							<select class='FormField' id="companymatches" name="companymatches">
								<option name =""<?=($userinfo['companymatches'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['companymatches'] == "1" ? " selected='selected'" : "")?>>Production Company</option>
								<option value="2"<?=($userinfo['companymatches'] == "2" ? " selected='selected'" : "")?>>Broadcast/Cable Studio</option>
								<option value="3"<?=($userinfo['companymatches'] == "3" ? " selected='selected'" : "")?>>Corporate Video</option>
								<option value="4"<?=($userinfo['companymatches'] == "4" ? " selected='selected'" : "")?>>Visual Effects Studio</option>
								<option value="5"<?=($userinfo['companymatches'] == "5" ? " selected='selected'" : "")?>>Animation Studio</option>
								<option value="6"<?=($userinfo['companymatches'] == "6" ? " selected='selected'" : "")?>>Web/Interactive media</option>
								<option value="7"<?=($userinfo['companymatches'] == "7" ? " selected='selected'" : "")?>>Post Production Facility</option>
								<option value="8"<?=($userinfo['companymatches'] == "8" ? " selected='selected'" : "")?>>Independent Filmmaker or Videographer</option>
								<option value="9"<?=($userinfo['companymatches'] == "9" ? " selected='selected'" : "")?>>Audio Recording Studio</option>
								<option value="10"<?=($userinfo['companymatches'] == "10" ? " selected='selected'" : "")?>>Education Institution</option>
								<option value="11"<?=($userinfo['companymatches'] == "11" ? " selected='selected'" : "")?>>Other</option>
							</select>
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"><span class="textboxname">Which Non-liner Editing System do you primarily use?</span><br />
							<select class='FormField' id="editingsystem" name="editingsystem">
								<option value=""<?=($userinfo['editingsystem'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['editingsystem'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Pro or Final Cut Express</option>
								<option value="2"<?=($userinfo['editingsystem'] == "2" ? " selected='selected'" : "")?>>Avid Xpres Pro or DV</option>
								<option value="3"<?=($userinfo['editingsystem'] == "3" ? " selected='selected'" : "")?>>Other Avid Product</option>
								<option value="4"<?=($userinfo['editingsystem'] == "4" ? " selected='selected'" : "")?>>Adobe Premier or Premiere Pro</option>
								<option value="5"<?=($userinfo['editingsystem'] == "5" ? " selected='selected'" : "")?>>Sony Vegas Video</option>
								<option value="6"<?=($userinfo['editingsystem'] == "6" ? " selected='selected'" : "")?>>Media 100</option>
								<option value="7"<?=($userinfo['editingsystem'] == "7" ? " selected='selected'" : "")?>>Discreet Edit</option>
								<option value="8"<?=($userinfo['editingsystem'] == "8" ? " selected='selected'" : "")?>>Pinnacle Liquid, Studio or Pro</option>
								<option value="9"<?=($userinfo['editingsystem'] == "9" ? " selected='selected'" : "")?>>ULead MediaStudio</option>
								<option value="10"<?=($userinfo['editingsystem'] == "10" ? " selected='selected'" : "")?>>Quantel</option>
								<option value="11"<?=($userinfo['editingsystem'] == "11" ? " selected='selected'" : "")?>>Other</option>
								<option value="12"<?=($userinfo['editingsystem'] == "12" ? " selected='selected'" : "")?>>None</option>
							</select>
						</div></td>
						<td>&nbsp;</td>
					</tr>
<?
	} else if ($info['idEventSeries'] == 2) {
?>
					<tr>

						<td width="50%"><div class="formfield"><span class="textboxname">How did you hear about the Tour?</span><br />
							<select class='FormField' id="intQ1" name="intQ1" style="width:250px;">
								<option value=""<?=($userinfo['intQ1'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['intQ1'] == "1" ? " selected='selected'" : "")?>>Email invitation from Apple.</option>
								<option value="2"<?=($userinfo['intQ1'] == "2" ? " selected='selected'" : "")?>>Apple eNews email article</option>
								<option value="3"<?=($userinfo['intQ1'] == "3" ? " selected='selected'" : "")?>>Apple Hot News article</option>
								<option value="4"<?=($userinfo['intQ1'] == "4" ? " selected='selected'" : "")?>>Third-party website (please list)</option>
								<option value="5"<?=($userinfo['intQ1'] == "5" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour email</option>
								<option value="6"<?=($userinfo['intQ1'] == "6" ? " selected='selected'" : "")?>>Other (please list)</option>
							</select><br /><span class="textboxnameother">&nbsp;&nbsp;&nbsp;Other Answer:</span><br />					
							&nbsp;&nbsp;<input type="text" style="width:250px;" maxlength="200" id="chrQ1other" name="chrQ1other" value="<?=encode($userinfo['chrQ1other'])?>" />
						</div></td>
						<td><div class="formfield"><span class="textboxname">What interest you about attending this event?</span><br />
							<select class='FormField' id="intQ2" name="intQ2" style="width:250px;">
								<option name =""<?=($userinfo['intQ2'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['intQ2'] == "1" ? " selected='selected'" : "")?>>I want to learn more about Aperture before I make a purchase decision.</option>
								<option value="2"<?=($userinfo['intQ2'] == "2" ? " selected='selected'" : "")?>>I use Aperture already and want to pick up some new tips or have questions answered.</option>
								<option value="3"<?=($userinfo['intQ2'] == "3" ? " selected='selected'" : "")?>>I am interested in seeing the professional photographer's work.</option>
								<option value="4"<?=($userinfo['intQ2'] == "4" ? " selected='selected'" : "")?>>Other (please list)</option>
							</select><br /><span class="textboxnameother">&nbsp;&nbsp;&nbsp;Other Answer:</span><br />
							&nbsp;&nbsp;<input type="text" style="width:250px;" maxlength="200" id="chrQ2other" name="chrQ2other" value="<?=encode($userinfo['chrQ2other'])?>" />
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"><span class="textboxname">What type of photography are you <strong>primarily</strong> doing?</span><br />
							<select class='FormField' id="intQ3" name="intQ3" style="width:250px;">
								<option value=""<?=($userinfo['intQ3'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
								<option value="1"<?=($userinfo['intQ3'] == "1" ? " selected='selected'" : "")?>>Sports photography</option>
								<option value="2"<?=($userinfo['intQ3'] == "2" ? " selected='selected'" : "")?>>Wedding or Portrait</option>
								<option value="3"<?=($userinfo['intQ3'] == "3" ? " selected='selected'" : "")?>>Commercial - product or fashion photography</option>
								<option value="4"<?=($userinfo['intQ3'] == "4" ? " selected='selected'" : "")?>>Nature or landscape photography</option>
								<option value="5"<?=($userinfo['intQ3'] == "5" ? " selected='selected'" : "")?>>Corporate or event photography</option>
								<option value="6"<?=($userinfo['intQ3'] == "6" ? " selected='selected'" : "")?>>Photo journalism or editorial</option>
								<option value="7"<?=($userinfo['intQ3'] == "7" ? " selected='selected'" : "")?>>Fine Art</option>
								<option value="8"<?=($userinfo['intQ3'] == "8" ? " selected='selected'" : "")?>>Architecture</option>
								<option value="9"<?=($userinfo['intQ3'] == "9" ? " selected='selected'" : "")?>>Amateur or Enthusiast only</option>
								<option value="10"<?=($userinfo['intQ3'] == "10" ? " selected='selected'" : "")?>>Other (please list)</option>
							</select><br /><span class="textboxnameother">&nbsp;&nbsp;&nbsp;Other Answer:</span><br />
							&nbsp;&nbsp;<input type="text" style="width:250px;" maxlength="200" id="chrQ3other" name="chrQ3other" value="<?=encode($userinfo['chrQ3other'])?>" />
						</div></td>
						<td>&nbsp;</td>
					</tr>
<?
	} else if ($info['idEventSeries'] == 3) {
?>
					<tr>
						<td colspan='2' style='border-top:2px #000 solid; color:#CCC; font-weight:bold; padding-top:3px; font-size:12px;'>
							Enter in information below to sign up your first assistant.
						</td>
					</tr>
					<tr>
						<td width="50%"><div class="formfield"><span class="textboxname">First Name</span> <span class="textboxrequired">(Required for Additional Sign-up)</span><br />
								<input name="chrFirst2" type="text" id="chrFirst2" size="35" maxlength="50" value="<?=encode($userinfo['chrFirst2'])?>" />
						</div></td>
						<td width="50%"><div class="formfield"><span class="textboxname">Last Name</span> <span class="textboxrequired">(Required for Additional Sign-up)</span><br />
									<input name="chrLast2" type="text" id="chrLast2" size="35" maxlength="50" value="<?=encode($userinfo['chrLast2'])?>" />
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Telephone</span><br />
									<input name="chrPhone2" type="text" id="chrPhone2" size="35" maxlength="25" value="<?=strip_quotes($userinfo['chrPhone2'])?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Email</span> <span class="textboxrequired">(Required for Additional Sign-up)</span><br />
									<input name="chrEmail2" type="text" id="chrEmail2" size="35" maxlength="75" value="<?=$userinfo['chrEmail2']?>" />
						</div></td>
					</tr>
<?
	}
?>
					
					<tr>
						<td colspan="2"></td>
					</tr>
					
					<tr>
						<td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><input id="bApple" name="bApple" type="checkbox" class="checkbox" value="1" checked="checked" /></td>
								<td class="optin"><strong>Stay in touch!</strong> Keep me up to date with Apple news, software updates, and the latest information on products and services to help me make the most of my Apple products.</td>
							</tr>
	
						</table></td>
						</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td><input name="Submit" type="submit" class="button" value="Submit Registration" /></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><div class="disclaimer"><a href="http://www.apple.com/legal/privacy/">Apple Privacy Policy</a> <br />
	You're in control. You always have access to your personal information and contact preferences, so you can change them at any time. To learn how Apple safeguards your personal information, please review the Apple Customer Privacy Policy.  If you would rather not receive this information, please uncheck the box.</div></td>
						</tr>
				</table>
<?

function field_blank($code) {
	global $error_message;
	switch ($code) {
		case "selectevent":
			$Message = "You must select at least 1 Event.";
			break;	
		case "chrFirst":
			$Message = "Please Enter your First Name.";
			break;
		case "chrLast":
			$Message = "Please Enter your Last Name.";
			break;
		case "chrAddress":
			$Message = "Please Enter your Address.";
			break;
		case "chrCity":
			$Message = "Please Enter your City.";
			break;
		case "chrState":
			$Message = "Please Select your State/Province.";
			break;	
		case "chrZip":
			$Message = "Please Enter your Postal Code.";
			break;
		case "chrEmail":
			$Message = "Please Enter your E-mail Address.";
			break;
		case "chrAgency":
			$Message = "Please Select a Agency from the List, or if not listed, enter Agency Name in the Other Agency box.";
			break;
		case "chrEmail2":
			$Message = "Please Enter the E-mail Address of the Additional Sign-up.";
			break;
		case "emailused":
			$Message = "Sorry, You are already signed up for one or more of these events.";
			break;	
		case "chrEmailnotvalid":
			$Message = "You must enter a Valid Email Address.";
			break;
		case "chrFirst2":
			$Message = "Please Enter the First Name of the Additional Sign-up.";
			break;
		case "chrLast2":
			$Message = "Please Enter the Last Name of the Additional Sign-up.";
			break;
		case "emailused2":
			$Message = "Sorry, Your Additional person is already registered for one or more of these events.";
			break;	
		case "chrEmail2notvalid":
			$Message = "You must enter a Valid Email Address for the Additional Sign-up.";
			break;
		case "chrMatches":
			$Message = "Sorry, The Additions persons e-mail address must not match yours.";
			break;	
		default:
			$Message = "";
			break;			
	}
	if ($Message != "") {
	$error_message[] = '<div style="padding-top:10px; text-align:center;"><div class="error">'.$Message.'</div></div>';
	}
	
}

// Table Close with Borders This part will not change throughout page.
?>
				</form></td>
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