<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Venue';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "venue";		 // This is needed to highlight the show section
	require($BF. '_lib.php');

	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT * FROM Venues WHERE ID=". $_REQUEST['id'],"getting Venue info");

	if(isset($_POST['chrVenue'])) {

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Venues';
		$mysqlStr = '';
		$audit = '';
		
		$_POST['txtNotes'] = encode($_POST['txtNotes']);
		$_POST['txtDirections'] = encode($_POST['txtDirections']);
		$_POST['chrContact'] = encode($_POST['chrContact']);
		$_POST['chrDims'] = encode($_POST['chrDims']);
		$_POST['chrGoogle'] = encode($_POST['chrGoogle']);
		$_POST['chrTravel'] = encode($_POST['chrTravel']);
		$_POST['chrZip'] = encode($_POST['chrZip']);
		$_POST['chrPhone'] = encode($_POST['chrPhone']);
		$_POST['chrRoom']= encode($_POST['chrRoom']);
		$_POST['chrVenue'] = encode($_POST['chrVenue']);
		$_POST['chrAddress']= encode($_POST['chrAddress']);
		$_POST['chrAddress2']= encode($_POST['chrAddress2']);
		$_POST['chrCity'] = encode($_POST['chrCity']);					  
		

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrVenue',$info['chrVenue'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress',$info['chrAddress'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress2',$info['chrAddress2'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$info['chrCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$info['chrState'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$info['chrCountry'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idTimeZone',$info['idTimeZone'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrZip',$info['chrZip'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$info['chrPhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrRoom',$info['chrRoom'],$audit,$table,$_POST['id']);
		
		$waitcheck=0;
		
		if ($_POST['intCapacity'] >= $info['intCapacity']) {
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intCapacity',$info['intCapacity'],$audit,$table,$_POST['id']);
			$waitcheck=1;
		}
		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrContact',$info['chrContact'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrDims',$info['chrDims'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrGoogle',$info['chrGoogle'],$audit,$table,$_POST['id']);
		
		if ($_POST['intDropOff'] >= $info['intDropOff']) {
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intDropOff',$info['intDropOff'],$audit,$table,$_POST['id']);
			$waitcheck=1;
		}
		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'txtNotes',$info['txtNotes'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'txtDirections',$info['txtDirections'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrTravel',$info['chrTravel'],$audit,$table,$_POST['id']);
				
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		if ($waitcheck == 1) {
			require($BF. 'includes/_emailer.php');
			// Get All Events with this Venue that
			//	$other['intCount'] < ($other['intCapacity'] + round($other['intCapacity'] * ($other['intDropOff'] / 100)))

			// Lets pull all the dates for this Event Series
			$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
					FROM Events 
					JOIN EventDates ON EventDates.idEvent=Events.ID 
					WHERE !Events.bDeleted AND Events.idVenue=".$_POST['id']."
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
			
			$q = "SELECT Events.ID, intCapacity + round(intCapacity * (intDropOff / 100), 0) - (SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus=1) AS intDifference, EventSeries.chrTitle, chrName, txtShort, txtLong,
				  (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS dDate, chrVenue, Venues.chrAddress, Venues.chrAddress2, Venues.chrCity, Venues.chrState, Venues.chrZip, Venues.chrCountry, 
                     Venues.chrPhone, Venues.chrRoom, Venues.intCapacity, Venues.chrContact, Venues.chrGoogle, 
				  Venues.txtDirections, Venues.chrTravel,chrLocation, Venues.txtNotes, Events.ID as idEvent, EventSeries.ID as idEventSeries,EventSeries.chrEmailName, EventSeries.chrFromEmail
				  FROM Events
				  JOIN Venues ON Events.idVenue=Venues.ID
				  JOIN EventTitles on Events.idEventTitle=EventTitles.ID
				  JOIN EventSeries on Events.idEventSeries=EventSeries.ID
				  LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
				  WHERE !Events.bDeleted AND Venues.ID=".$_POST['id']." AND (SELECT COUNT(Signups.ID) FROM Signups WHERE Signups.idEvent=Events.ID AND idStatus=1) < (intCapacity + round(intCapacity * (intDropOff / 100), 0)) AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) > now()
				  ORDER BY Events.ID";
			
			$events = database_query($q, "Getting Events for this Venue that have a difference");
								
			$sendto = array();
			$event_list = array();
			
			while ($row = mysqli_fetch_assoc($events)) {

				$q = "SELECT Attendees.ID, Attendees.chrFirst, Attendees.chrLast, Signups.chrCancel, Attendees.chrEmail, Signups.ID AS idSignup
				FROM Signups
				JOIN Attendees ON Attendees.ID=Signups.idUser
				WHERE Signups.idEvent=".$row['ID']." AND Signups.idStatus=2
				ORDER BY Signups.dtStamp
				LIMIT ".$row['intDifference'];
				
				$emaillist = database_query($q, "Get Users");
				
				while ($user = mysqli_fetch_assoc($emaillist)) {
					$sendto[$user['ID']][$row['ID']]['chrFirst'] = $user['chrFirst'];
					$sendto[$user['ID']][$row['ID']]['chrLast'] = $user['chrLast'];
					$sendto[$user['ID']][$row['ID']]['chrEmail'] = $user['chrEmail'];
					$sendto[$user['ID']][$row['ID']]['chrCancel'] = $user['chrCancel'];
					$sendto[$user['ID']][$row['ID']]['idSignup'] = $user['idSignup'];
				}


				$eventlist[$row['ID']]['ID'] = $row['ID'];
				$eventlist[$row['ID']]['chrTitle'] = $row['chrTitle'];
				$eventlist[$row['ID']]['chrName'] = $row['chrName'];
				$eventlist[$row['ID']]['txtShort'] = $row['txtShort'];
				$eventlist[$row['ID']]['txtLong'] = $row['txtLong'];
				$eventlist[$row['ID']]['dDate'] = $row['dDate'];
				$eventlist[$row['ID']]['chrVenue'] = $row['chrVenue'];
				$eventlist[$row['ID']]['chrAddress'] = $row['chrAddress'];
				$eventlist[$row['ID']]['chrAddress2'] = $row['chrAddress2'];
				$eventlist[$row['ID']]['chrCity'] = $row['chrCity'];
				$eventlist[$row['ID']]['chrState'] = $row['chrState'];
				$eventlist[$row['ID']]['chrZip'] = $row['chrZip'];
				$eventlist[$row['ID']]['chrCountry'] = $row['chrCountry'];
				$eventlist[$row['ID']]['chrPhone'] = $row['chrPhone'];
				$eventlist[$row['ID']]['chrRoom'] = $row['chrRoom'];
				$eventlist[$row['ID']]['intCapacity'] = $row['intCapacity'];
				$eventlist[$row['ID']]['chrContact'] = $row['chrContact'];
				$eventlist[$row['ID']]['chrGoogle'] = $row['chrGoogle'];
				$eventlist[$row['ID']]['txtDirections'] = $row['txtDirections'];
				$eventlist[$row['ID']]['chrTravel'] = $row['chrTravel'];
				$eventlist[$row['ID']]['chrLocation'] = $row['chrLocation'];
				$eventlist[$row['ID']]['txtNotes'] = $row['txtNotes'];
				$eventlist[$row['ID']]['idEventSeries'] = $row['idEventSeries'];
				$eventlist[$row['ID']]['chrEmailName'] = $row['chrEmailName'];
				$eventlist[$row['ID']]['chrFromEmail'] = $row['chrFromEmail'];

			}

			
			$prev_userid = 0;
			$count = 0;
			$pre_toemail = '';
			$EventList = "";
			foreach ($sendto as $idUser => $v) {
				foreach ($v as $Event => $user) {
					if($prev_userid != $idUser) {
						if($count > 0) {
							$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
							$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $prev_userid . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
							$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
							database_query("UPDATE Signups SET idStatus=1 WHERE Signups.idEvent=".$pre_event_id." AND Signups.idUser=".$prev_userid." AND Signups.ID=".$sendto[$prev_userid][$eventlist[$pre_event_id]['ID']]['idSignup']." AND Signups.idStatus = 2","Updating Status");
//							sendemail('sobsmb@gmail.com',$eventlist[$pre_event_id]['chrEmailName'].' <'.$eventlist[$pre_event_id]['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
							sendemail($pre_toemail,$eventlist[$pre_event_id]['chrEmailName'].' <'.$eventlist[$pre_event_id]['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
						}
						$allcancel = $eventlist[$Event]['ID'];
						$pre_toemail = $user['chrEmail'];
						$prev_userid = $idUser;
						$pre = $user;
						$pre_event_id = $eventlist[$Event]['ID'];
						$EventList = '';
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$eventlist[$Event]['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
			
						$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventlist[$Event]['chrName']),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventlist[$Event]['txtShort'])),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventlist[$Event]['txtLong'])),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$eventlist[$Event]['ID']]),$eventemail['txtBody']);
						$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $idUser . "&idE=" . $eventlist[$Event]['ID'] . "&special=" . $user['chrCancel']);
						$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
						
						$EventList .= $eventemail['txtBody'];
						
						$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$eventlist[$Event]['idEventSeries']." AND idType=9","Get waitlist to confirmed E-mail Body");
						$pre['chrSubject'] = $email['chrSubject'];
						$email['txtBody'] = str_replace('$FIRST_NAME',encode($user['chrFirst']),$email['txtBody']); 
						$email['txtBody'] = str_replace('$LAST_NAME',encode($user['chrLast']),$email['txtBody']);
						$email['txtBody'] = str_replace('$SERIES_TITLE',encode($eventlist[$Event]['chrTitle']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_NAME',encode($eventlist[$Event]['chrVenue']),$email['txtBody']);
						$address = $eventlist[$Event]['chrAddress'];
						if($eventlist[$Event]['chrAddress2'] != '') { $address .= '<br />'.$eventlist[$Event]['chrAddress2']; }
						$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($address),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_CITY',encode($eventlist[$Event]['chrCity']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_STATE',encode($eventlist[$Event]['chrState']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($eventlist[$Event]['chrZip']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($eventlist[$Event]['chrCountry']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_PHONE',encode($eventlist[$Event]['chrPhone']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_ROOM',encode($eventlist[$Event]['chrRoom']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$eventlist[$Event]['chrGoogle'],$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$eventlist[$Event]['chrTravel'],$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($eventlist[$Event]['txtDirections'])),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_NOTES',encode($eventlist[$Event]['txtNotes']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($eventlist[$Event]['chrContact']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($eventlist[$Event]['chrLocation']),$email['txtBody']);
															
					} else {
						if ($allcancel != "") { $allcancel .= ","; } // Comma Seperates for Cancel all Link
						$prev_userid = $idUser;
						$allcancel .= $eventlist[$Event]['ID'];
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$eventlist[$Event]['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
				
						$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($eventlist[$Event]['chrName']),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($eventlist[$Event]['txtShort'])),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($eventlist[$Event]['txtLong'])),$eventemail['txtBody']);
						$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$eventlist[$Event]['ID']]),$eventemail['txtBody']);
						$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $idUser . "&idE=" . $eventlist[$Event]['ID'] . "&special=" . $user['chrCancel']);
						$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
						
						$EventList .= $eventemail['txtBody'];
					}
					$count++;
				}
			}
			if($count > 0) {
				$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
				$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $prev_userid . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
				$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
				database_query("UPDATE Signups SET idStatus=1 WHERE Signups.idEvent=".$pre_event_id." AND Signups.idUser=".$prev_userid." AND Signups.ID=".$sendto[$prev_userid][$eventlist[$pre_event_id]['ID']]['idSignup']." AND Signups.idStatus = 2","Updating Status");
//				sendemail('sobsmb@gmail.com',$eventlist[$pre_event_id]['chrEmailName'].' <'.$eventlist[$pre_event_id]['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
				sendemail($pre_toemail,$eventlist[$pre_event_id]['chrEmailName'].' <'.$eventlist[$pre_event_id]['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
				$emailcnt++;			
			}
		
		}
		
		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: venues.php");
		die();
	}
	include($BF. 'components/states.php');
	include($BF. 'components/countries.php');		
	

	$TimeZone = database_query("SELECT * FROM TimeZone ORDER BY intOffset","getting TimeZone info");

	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrVenue').focus(); calc();";
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrVenue', "You must enter a Name.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
	
	function calc() {
	
		if(document.getElementById('intCapacity').value != "" && document.getElementById('intDropOff').value != "") {
		
		var cap = parseInt(document.getElementById('intCapacity').value);
		var dropoff = Math.round(parseInt(document.getElementById('intCapacity').value) * (parseInt(document.getElementById('intDropOff').value) / 100));
		document.getElementById('totalcap').value = cap + dropoff;
	
		}
	}
	
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Edit Venue</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Fill out items and click submit.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='twoCol' class='twoCol' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<td class="left">
					<div class='FormName'>Venue Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrVenue' id='chrVenue' size="50" value="<?=decode($info['chrVenue'])?>" /></div>

					<div class='FormName'>Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrAddress' id='chrAddress' size="50" value="<?=decode($info['chrAddress'])?>" /></div>
					<div class='FormField'><input type='text' name='chrAddress2' id='chrAddress2' size="50" value="<?=decode($info['chrAddress2'])?>" /></div>					
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>				
								<div class='FormName'>City <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrCity' id='chrCity' value="<?=decode($info['chrCity'])?>" /></div>
							</td>
							<td style="width:5px;"></td>
							<td>
								<div class='FormName'>State <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrState" name='chrState'>
										<?	foreach($states as $st => $name) { ?>
											<option value='<?=@$st?>' <?=($info['chrState'] == $st ? 'selected="selected"' : '')?>><?=$name?></option>
										<?	} ?>
									</select>
								</div>
							</td>
							<td style="width:5px;"></td>							
							<td>
								<div class='FormName'>Zip <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrZip' id='chrZip' size="10" value="<?=decode($info['chrZip'])?>" /></div>									
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Country <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrCountry" name='chrCountry'>
										<?	foreach($countries as $cy => $name) { ?>
											<option value='<?=@$cy?>' <?=($info['chrCountry'] == $cy ? 'selected="selected"' : '')?>><?=$name?></option>
										<?	} ?>
									</select>
								</div>
							</td>
						</tr>
					</table>					
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Phone Number <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrPhone' id='chrPhone' value="<?=decode($info['chrPhone'])?>" /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Contact Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrContact' id='chrContact' value="<?=decode($info['chrContact'])?>" /></div>
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Room Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrRoom' id='chrRoom' value="<?=decode($info['chrRoom'])?>" /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Room Dimensions <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrDims' id='chrDims' value="<?=decode($info['chrDims'])?>" /></div>	
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Capacity <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='intCapacity' id='intCapacity' maxlength="6" value="<?=$info['intCapacity']?>" onchange="calc()" /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Drop Off Rate <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='intDropOff' id='intDropOff' value="<?=$info['intDropOff']?>" onchange="calc()" />%</div>
							</td>
						</tr>
					</table>	
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Time Zone <span class='Required'>(Required)</span></div>
								<select class='FormField' id="idTimeZone" name='idTimeZone'  style="width:px;">
								<? while ($row = mysqli_fetch_assoc($TimeZone)) { ?>
									<option value='<?=$row['ID']?>' <?=($info['idTimeZone'] == $row['ID'] ? 'selected="selected"' : '')?>><?=$row['chrLocation']?></option>
								<?	} ?>
								</select>
							</td>
							<td style="width:13px;"></td>
							<td>
								<div class='FormName'>Total Capacity</div>
								<div class='FormField'><input type='text' size="13" id='totalcap' disabled='disabled' /></div>	
							</td>
						</tr>
					</table>										
				</td>
				<td class="right">
		
				
					<div class='FormName'>Online Map URL <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrGoogle' id='chrGoogle' size="52" value="<?=decode($info['chrGoogle'])?>" /></div>
					
					<div class='FormName'>Travel URL</div>
					<div class='FormField'><input type='text' name='chrTravel' id='chrTravel' size="52" value="<?=decode($info['chrTravel'])?>"/></div>					

					<div class='FormName'>Manual Directions</div>
					<div class='FormField'><textarea id="txtDirections" name="txtDirections" cols="50" rows="13"><?=decode($info['txtDirections'])?></textarea></div>
						
					<div class='FormName'>Notes</div>
					<div class='FormField'><textarea id="txtNotes" name="txtNotes" cols="50" rows="13"><?=decode($info['txtNotes'])?></textarea></div>
				
				</td>			
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Update Information' onclick="error_check()" />
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' >
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>