<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Attendee';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "attendees";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	// This is for the sorting of the rows and columns.  We must set the default order and name

	$q = "SELECT *
			FROM Attendees
			WHERE !bDeleted AND ID=".$_REQUEST['id'];
			
	$UserInfo = fetch_database_query($q, "Getting Attendee Data");

	if(isset($_POST['chrEmail'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Attendees';
		$mysqlStr = '';
		$audit = '';
		
		if(isset($_POST['chrZip'])) { $_POST['chrZip'] = strip_quotes($_POST['chrZip']); }
		$_POST['chrPhone'] = strip_quotes($_POST['chrPhone']);

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirst',$UserInfo['chrFirst'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLast',$UserInfo['chrLast'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$UserInfo['chrEmail'],$audit,$table,$_POST['id']);
	if ($_REQUEST['idEventSeries'] != 3) {
			
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress',$UserInfo['chrAddress'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress1',$UserInfo['chrAddress1'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$UserInfo['chrCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$UserInfo['chrState'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrZip',$UserInfo['chrZip'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$UserInfo['chrCountry'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$UserInfo['chrPhone'],$audit,$table,$_POST['id']);
		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCompany',$UserInfo['chrCompany'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bApple',$UserInfo['bApple'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intFindout',$UserInfo['intFindout'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intCompanyMatches',$UserInfo['intCompanyMatches'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intEditingSystem',$UserInfo['intEditingSystem'],$audit,$table,$_POST['id']);
} else {
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAgency',$UserInfo['chrAgency'],$audit,$table,$_POST['id']);
}			
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }


		// Now to see if status has been changed and Update if Needed
		
		$q = "SELECT ID, idStatus 
				FROM Signups 
				WHERE idUser=".$_POST['id'];
		
		$signupinfo = database_query($q,"Pulling Signup Information for Attendee");

		while ($row = mysqli_fetch_assoc($signupinfo)) { 
			if ( $_POST['idStatusChange'.$row['ID']] != "" && $row['idStatus'] != $_POST['idStatusChange'.$row['ID']] ) {  //if changed set update status
				$qu = "UPDATE Signups 
						SET idStatus=". $_POST['idStatusChange'.$row['ID']]."
						WHERE ID=".$row['ID'];
		
				database_query($qu,"Updating Attendee Status");	
			}		
		}
		
		// Checks to see if we need to resend a confirmation e-mail
		if (isset($_POST['resendID'])) { 
			require($BF. 'includes/_emailer.php');
			
			// Lets pull all the dates for this Event Series
			$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
					FROM Events 
					JOIN EventDates ON EventDates.idEvent=Events.ID 
					JOIN Signups ON Signups.idEvent=Events.ID
					WHERE !Events.bDeleted AND Signups.ID IN (".implode(',', $_POST['resendID']).")
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
		
			$q = "SELECT Attendees.ID, Events.ID AS idEvent, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS dDate, EventTitles.chrName, EventTitles.txtShort,EventTitles.txtLong, Signups.idStatus, Attendees.chrFirst, Attendees.chrLast, 
				Attendees.chrEmail, Signups.chrCancel, 	EventSeries.chrTitle, Venues.chrVenue, Venues.chrAddress, Venues.chrAddress2, Venues.chrCity, Venues.chrState, Venues.chrZip, Venues.chrCountry, 
				Venues.chrPhone, Venues.chrRoom, Venues.intCapacity, Venues.chrContact, Venues.chrGoogle, Venues.intDropOff, Venues.txtDirections, Venues.chrTravel, TimeZone.chrLocation, Venues.txtNotes,
				EventSeries.ID as idEventSeries, EventSeries.chrEmailName, EventSeries.chrFromEmail
				FROM Events
				JOIN Venues ON Events.idVenue=Venues.ID
				JOIN Signups ON Signups.idEvent=Events.ID
				JOIN EventTitles on Events.idEventTitle=EventTitles.ID
				JOIN Attendees ON Signups.idUser=Attendees.ID
				JOIN EventSeries on Events.idEventSeries=EventSeries.ID
				JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
				WHERE Signups.ID IN (".implode(',', $_POST['resendID']).") 
				ORDER BY Attendees.ID, EventSeries.ID, dDate";
			
			$Emails = database_query($q,"Getting Email Data Info");
			
			$count = 0;
			$EventList = "";
			$cancel = '';
		
			while ($row = mysqli_fetch_assoc($Emails)) {
				if ($cancel != $row['chrCancel']) {
					if ($count > 0) { 
					
						if($confirmed > 0) {
							$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=1","Get Cancel E-mail Body");
						} else if($confirmed == 0 && $cancelled == 0) {
							$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=2","Get Cancel E-mail Body");					
						} else if($confirmed == 0 && $waitlist == 0) {
							$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=3","Get Cancel E-mail Body");
						}
						$email['txtBody'] = str_replace('$FIRST_NAME',encode($pre['chrFirst']),$email['txtBody']); 
						$email['txtBody'] = str_replace('$LAST_NAME',encode($pre['chrLast']),$email['txtBody']);
						$email['txtBody'] = str_replace('$SERIES_TITLE',encode($pre['chrTitle']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_NAME',encode($pre['chrVenue']),$email['txtBody']);
						if($pre['chrAddress2'] != '') { $pre['chrAddress'] .= '<br />'.$pre['chrAddress2']; }
						$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($pre['chrAddress']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_CITY',encode($pre['chrCity']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_STATE',encode($pre['chrState']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($pre['chrZip']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($pre['chrCountry']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_PHONE',encode($pre['chrPhone']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_ROOM',encode($pre['chrRoom']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$pre['chrGoogle'],$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$pre['chrTravel'],$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($pre['txtDirections'])),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_NOTES',encode($pre['txtNotes']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($pre['chrContact']),$email['txtBody']);
						$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($pre['chrLocation']),$email['txtBody']);
					
						$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
						$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
						$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
						sendemail($pre_toemail,$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$pre['chrSubject'],$email['txtBody']);
					}
					$confirmed=0;
					$waitlist=0;
					$cancelled=0;
					
					$allcancel = $row['idEvent'];
					$pre_toemail = $row['chrEmail'];
					$prev_userid = $row['ID'];
					$pre = $row;
					
					if ($row['idStatus'] == 1 ) { //Confirmed
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
						$confirmed++;
					} else if ($row['idStatus'] == 2) {  //Waitlist
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=6","Get Event List Waitlist E-mail Body");
						$waitlist++;
					} else if ($row['idStatus'] == 3) {  //Cancelled
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=7","Get Event List Cancelled E-mail Body");
						$cancelled++;
					}
							
					$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($row['chrName']),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($row['txtShort'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($row['txtLong'])),$eventemail['txtBody']);
					$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$row['idEvent']]),$eventemail['txtBody']);
					$Cancel = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $row['ID'] . "&idE=" . $row['idEvent'] . "&special=" . $row['chrCancel']);
					$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$Cancel,$eventemail['txtBody']);
					
					$EventList .= $eventemail['txtBody'];
					
				} else {
				
					if ($row['idStatus'] == 1 ) { //Confirmed
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=5","Get Event List Confirmed E-mail Body");
						$confirmed++;
					} else if ($row['idStatus'] == 2) {  //Waitlist
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=6","Get Event List Confirmed E-mail Body");
						$waitlist++;
					} else if ($row['idStatus'] == 3) {  //Cancelled
						$eventemail = fetch_database_query("SELECT txtBody FROM Emails WHERE idEventSeries=".$row['idEventSeries']." AND idType=7","Get Event List Confirmed E-mail Body");
						$cancelled++;
					}
						
					if ($allcancel != "") { $allcancel .= ","; } // Comma Seperates for Cancel all Link
					$allcancel .= $row['idEvent'];
										
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
						
				if($confirmed > 0) {
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=1","Get Cancel E-mail Body");
				} else if($confirmed == 0 && $cancelled == 0) {
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=2","Get Cancel E-mail Body");					
				} else if($confirmed == 0 && $waitlist == 0) {
					$email = fetch_database_query("SELECT chrSubject, txtBody FROM Emails WHERE idEventSeries=".$pre['idEventSeries']." AND idType=3","Get Cancel E-mail Body");
				}
				$email['txtBody'] = str_replace('$FIRST_NAME',encode($pre['chrFirst']),$email['txtBody']); 
				$email['txtBody'] = str_replace('$LAST_NAME',encode($pre['chrLast']),$email['txtBody']);
				$email['txtBody'] = str_replace('$SERIES_TITLE',encode($pre['chrTitle']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_NAME',encode($pre['chrVenue']),$email['txtBody']);
				if($pre['chrAddress2'] != '') { $pre['chrAddress'] .= '<br />'.$pre['chrAddress2']; }
				$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($pre['chrAddress']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_CITY',encode($pre['chrCity']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_STATE',encode($pre['chrState']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($pre['chrZip']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($pre['chrCountry']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_PHONE',encode($pre['chrPhone']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_ROOM',encode($pre['chrRoom']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$pre['chrGoogle'],$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$pre['chrTravel'],$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($pre['txtDirections'])),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_NOTES',encode($pre['txtNotes']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($pre['chrContact']),$email['txtBody']);
				$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($pre['chrLocation']),$email['txtBody']);
			
				$email['txtBody'] = str_replace('$EVENT_INFO',encode($EventList),$email['txtBody']);
				$Cancelall = $PROJECT_ADDRESS."cancel.php?d=". base64_encode("ID=" . $pre['ID'] . "&idE=" . $allcancel . "&special=" . $pre['chrCancel']);
				$email['txtBody'] = str_replace('$CANCEL_ALL',$Cancelall,$email['txtBody']);
				sendemail($pre['chrEmail'],$pre['chrEmailName'].' <'.$pre['chrFromEmail'].'>',$email['chrSubject'],$email['txtBody']);
			}

		}
		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: attendees.php?id=".$_REQUEST['idEventSeries']);
		die();
	}
	
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrVenue, chrEventTitle"; $_REQUEST['ordCol']="ASC"; }	
	
	include($BF. 'components/states.php');
	include($BF. 'components/countries.php');	
	
	// Grab Attendee Data
	$q = "SELECT *
			FROM Attendees
			WHERE ID=".$_REQUEST['id'];
	
	$UserInfo = fetch_database_query($q, "Getting Attendee Data");
	
	
	$q = "SELECT Signups.ID, DATE_FORMAT(Signups.dtStamp, '%b %e, %Y - %l:%m %p') as dtFormated, Status.ID as idStatus, Status.chrName AS chrStatus, EventTitles.chrName AS chrEventTitle, chrVenue, Signups.chrCancel
			FROM Signups
			JOIN Events ON Events.ID=Signups.idEvent
			JOIN EventTitles ON Events.idEventTitle=EventTitles.ID
			JOIN Venues ON Venues.ID=Events.idVenue
			JOIN Status ON Signups.idStatus=Status.ID
			WHERE !Events.bDeleted AND Signups.idUser=".$_REQUEST['id']." AND Events.idEventSeries=".$_REQUEST['idEventSeries']." 
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	
	$result = database_query($q, "Getting Events User has Signed up for");


	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrFirst', "You must enter a First Name.");
		total += ErrorCheck('chrLast', "You must enter a Last Name.");
<?
	if ($_REQUEST['idEventSeries'] != 3) {
?>																
		total += ErrorCheck('chrAddress', "You must enter a Address.");		
		total += ErrorCheck('chrCity', "You must enter a City.");
		total += ErrorCheck('chrState', "You must select a State.");	
		total += ErrorCheck('chrZip', "You must enter a Zip.");
		total += ErrorCheck('chrCountry', "You must select a Country.");
<?
	}
?>																

		total += ErrorCheck('chrEmail', "You must enter a E-mail Address.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}

</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Edit Attendee</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Edit fields if needed and click Update.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='twoCol' class='twoCol' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<td class="left">
					<table cellpadding="0" cellspacing="0" style="width:100%;">
						<tr>
							<td>
								<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' size="20" value="<?=$UserInfo['chrFirst']?>" /></div>
							</td>
							<td>
								<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrLast' id='chrLast' size="20" value="<?=$UserInfo['chrLast']?>" /></div>
							</td>
						</tr>
					</table>
<?
				if ($_REQUEST['idEventSeries'] != 3) {
?>																

					<div class='FormName'>Company</div>
					<div class='FormField'><input type='text' name='chrCompany' id='chrCompany' value="<?=$UserInfo['chrCompany']?>" size="50" /></div>					
					<div class='FormName'>Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrAddress' id='chrAddress' size="50" value="<?=$UserInfo['chrAddress']?>" /></div>
					<div class='FormField'><input type='text' name='chrAddress1' id='chrAddress2' size="50" value="<?=$UserInfo['chrAddress1']?>" /></div>					
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>				
								<div class='FormName'>City <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrCity' id='chrCity' value="<?=$UserInfo['chrCity']?>" /></div>
							</td>
							<td style="width:5px;"></td>
							<td>
								<div class='FormName'>State <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrState" name='chrState'>
										<?	foreach($states as $st => $name) { ?>
											<option value='<?=@$st?>' <?=($UserInfo['chrState'] == $st ? 'selected="selected"' : '')?>><?=$name?></option>
										<?	} ?>
									</select>
								</div>
							</td>
							<td style="width:5px;"></td>							
							<td>
								<div class='FormName'>Zip <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrZip' id='chrZip' size="10" value="<?=$UserInfo['chrZip']?>" /></div>									
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Country <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrCountry" name='chrCountry'>
										<?	foreach($countries as $cy => $name) { ?>
											<option value='<?=@$cy?>' <?=($UserInfo['chrCountry'] == $cy ? 'selected="selected"' : '')?>><?=$name?></option>
										<?	} ?>
									</select>
								</div>
							</td>
						</tr>
					</table>		
<?
	}
?>			
				</td>
				<td class="right">
					<div class='FormName'>E-mail Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' value="<?=$UserInfo['chrEmail']?>" size="30" /></div>


					<div class='FormName'>Phone Number</div>
					<div class='FormField'><input type='text' name='chrPhone' id='chrPhone' value="<?=$UserInfo['chrPhone']?>" size="30" /></div>
					
					<div class='FormName'>Stay in touch!</div>
					<div class='FormField'><input type="radio" id="bApple" name="bApple" value="1" <?=($UserInfo['bApple'] == 1 ? "checked='checked'" : "")?> /> Yes <input type="radio" id="bApple" name="bApple" value="0" <?=($UserInfo['bApple'] != 1 ? "checked='checked'" : "")?> /> No</div>
<?
				if ($_REQUEST['idEventSeries'] == 1) {
?>																
					<div class="FormName">How did you find out about this seminar?</div>
					<div class='FormField'>
						<select class='FormField' id="intFindout" name="intFindout">
							<option value=""<?=($UserInfo['intFindout'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intFindout'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour Website</option>
							<option value="2"<?=($UserInfo['intFindout'] == "2" ? " selected='selected'" : "")?>>Third-party website</option>
							<option value="3"<?=($UserInfo['intFindout'] == "3" ? " selected='selected'" : "")?>>Apple Hot News Website</option>
							<option value="4"<?=($UserInfo['intFindout'] == "4" ? " selected='selected'" : "")?>>Apple eNews email</option>
							<option value="5"<?=($UserInfo['intFindout'] == "5" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour email</option>
							<option value="6"<?=($UserInfo['intFindout'] == "6" ? " selected='selected'" : "")?>>Other source</option>
						</select>
					</div>
					<div class="FormName">What type of company or institution most closely matches your work?</div>
					<div class='FormField'>
						<select class='FormField' id="intCompanyMatches" name="intCompanyMatches">
							<option name =""<?=($UserInfo['intCompanyMatches'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intCompanyMatches'] == "1" ? " selected='selected'" : "")?>>Production Company</option>
							<option value="2"<?=($UserInfo['intCompanyMatches'] == "2" ? " selected='selected'" : "")?>>Broadcast/Cable Studio</option>
							<option value="3"<?=($UserInfo['intCompanyMatches'] == "3" ? " selected='selected'" : "")?>>Corporate Video</option>
							<option value="4"<?=($UserInfo['intCompanyMatches'] == "4" ? " selected='selected'" : "")?>>Visual Effects Studio</option>
							<option value="5"<?=($UserInfo['intCompanyMatches'] == "5" ? " selected='selected'" : "")?>>Animation Studio</option>
							<option value="6"<?=($UserInfo['intCompanyMatches'] == "6" ? " selected='selected'" : "")?>>Web/Interactive media</option>
							<option value="7"<?=($UserInfo['intCompanyMatches'] == "7" ? " selected='selected'" : "")?>>Post Production Facility</option>
							<option value="8"<?=($UserInfo['intCompanyMatches'] == "8" ? " selected='selected'" : "")?>>Independent Filmmaker or Videographer</option>
							<option value="9"<?=($UserInfo['intCompanyMatches'] == "9" ? " selected='selected'" : "")?>>Audio Recording Studio</option>
							<option value="10"<?=($UserInfo['intCompanyMatches'] == "10" ? " selected='selected'" : "")?>>Education Institution</option>
							<option value="11"<?=($UserInfo['intCompanyMatches'] == "11" ? " selected='selected'" : "")?>>Other</option>
						</select>
					</div>

					<div class="FormName">Which Non-liner Editing System do you primarily use?</div>
					<div class='FormField'>
						<select class='FormField' id="intEditingSystem" name="intEditingSystem">
							<option value=""<?=($UserInfo['intEditingSystem'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intEditingSystem'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Pro or Final Cut Express</option>
							<option value="2"<?=($UserInfo['intEditingSystem'] == "2" ? " selected='selected'" : "")?>>Avid Xpres Pro or DV</option>
							<option value="3"<?=($UserInfo['intEditingSystem'] == "3" ? " selected='selected'" : "")?>>Other Avid Product</option>
							<option value="4"<?=($UserInfo['intEditingSystem'] == "4" ? " selected='selected'" : "")?>>Adobe Premier or Premiere Pro</option>
							<option value="5"<?=($UserInfo['intEditingSystem'] == "5" ? " selected='selected'" : "")?>>Sony Vegas Video</option>
							<option value="6"<?=($UserInfo['intEditingSystem'] == "6" ? " selected='selected'" : "")?>>Media 100</option>
							<option value="7"<?=($UserInfo['intEditingSystem'] == "7" ? " selected='selected'" : "")?>>Discreet Edit</option>
							<option value="8"<?=($UserInfo['intEditingSystem'] == "8" ? " selected='selected'" : "")?>>Pinnacle Liquid, Studio or Pro</option>
							<option value="9"<?=($UserInfo['intEditingSystem'] == "9" ? " selected='selected'" : "")?>>ULead MediaStudio</option>
							<option value="10"<?=($UserInfo['intEditingSystem'] == "10" ? " selected='selected'" : "")?>>Quantel</option>
							<option value="11"<?=($UserInfo['intEditingSystem'] == "11" ? " selected='selected'" : "")?>>Other</option>
							<option value="12"<?=($UserInfo['intEditingSystem'] == "12" ? " selected='selected'" : "")?>>None</option>
						</select>
					</div>
<?
				} else if ($_REQUEST['idEventSeries'] == 2) {
?>				
					<div class="FormName">How did you hear about the Tour?</div>
					<div class='FormField'>
						<select class='FormField' id="intQ1" name="intQ1" style="width:250px;">
							<option value=""<?=($UserInfo['intQ1'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intQ1'] == "1" ? " selected='selected'" : "")?>>Email invitation from Apple.</option>
							<option value="2"<?=($UserInfo['intQ1'] == "2" ? " selected='selected'" : "")?>>Apple eNews email article</option>
							<option value="3"<?=($UserInfo['intQ1'] == "3" ? " selected='selected'" : "")?>>Apple Hot News article</option>
							<option value="4"<?=($UserInfo['intQ1'] == "4" ? " selected='selected'" : "")?>>Third-party website (please list)</option>
							<option value="5"<?=($UserInfo['intQ1'] == "5" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour email</option>
							<option value="6"<?=($UserInfo['intQ1'] == "6" ? " selected='selected'" : "")?>>Other (please list)</option>
						</select>
					</div>
					<div class="FormName">How did you hear about the Tour other answer.</div>
					<div class='FormField'><input type="text" style="width:250px;" maxlength="200" id="chrQ1other" name="chrQ1other" value="<?=$UserInfo['chrQ1other']?>" /></div>

					<div class="FormName">What interest you about attending this event?</div>
					<div class='FormField'>
						<select class='FormField' id="intQ2" name="intQ2" style="width:250px;">
							<option name =""<?=($UserInfo['intQ2'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intQ2'] == "1" ? " selected='selected'" : "")?>>I want to learn more about Aperture before I make a purchase decision.</option>
							<option value="2"<?=($UserInfo['intQ2'] == "2" ? " selected='selected'" : "")?>>I use Aperture already and want to pick up some new tips or have questions answered.</option>
							<option value="3"<?=($UserInfo['intQ2'] == "3" ? " selected='selected'" : "")?>>I am interested in seeing the professional photographer's work.</option>
							<option value="4"<?=($UserInfo['intQ2'] == "4" ? " selected='selected'" : "")?>>Other (please list)</option>
						</select>
					</div>
					<div class="FormName">What interest you about attending this event other answer.</div>
					<div class='FormField'><input type="text" style="width:250px;" maxlength="200" id="chrQ2other" name="chrQ2other" value="<?=$UserInfo['chrQ2other']?>" /></div>
					
					<div class="FormName">What type of photography are you <strong>primarily</strong> doing?</div>
					<div class='FormField'>
						<select class='FormField' id="intQ3" name="intQ3" style="width:250px;">
							<option value=""<?=($UserInfo['intQ3'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
							<option value="1"<?=($UserInfo['intQ3'] == "1" ? " selected='selected'" : "")?>>Sports photography</option>
							<option value="2"<?=($UserInfo['intQ3'] == "2" ? " selected='selected'" : "")?>>Wedding or Portrait</option>
							<option value="3"<?=($UserInfo['intQ3'] == "3" ? " selected='selected'" : "")?>>Commercial - product or fashion photography</option>
							<option value="4"<?=($UserInfo['intQ3'] == "4" ? " selected='selected'" : "")?>>Nature or landscape photography</option>
							<option value="5"<?=($UserInfo['intQ3'] == "5" ? " selected='selected'" : "")?>>Corporate or event photography</option>
							<option value="6"<?=($UserInfo['intQ3'] == "6" ? " selected='selected'" : "")?>>Photo journalism or editorial</option>
							<option value="7"<?=($UserInfo['intQ3'] == "7" ? " selected='selected'" : "")?>>Fine Art</option>
							<option value="8"<?=($UserInfo['intQ3'] == "8" ? " selected='selected'" : "")?>>Architecture</option>
							<option value="9"<?=($UserInfo['intQ3'] == "9" ? " selected='selected'" : "")?>>Amateur or Enthusiast only</option>
							<option value="10"<?=($UserInfo['intQ3'] == "10" ? " selected='selected'" : "")?>>Other (please list)</option>
						</select>
					</div>
					<div class="FormName">What type of photography are you <strong>primarily</strong> doing other answer.</div>
					<div class='FormField'><input type="text" style="width:250px;" maxlength="200" id="chrQ1other" name="chrQ3other" value="<?=$UserInfo['chrQ3other']?>" /></div>
<?
				} else if ($_REQUEST['idEventSeries'] == 3) {
?>				
					<div class="FormName">Agency Name.</div>
					<div class='FormField'><input type="text" style="width:250px;" maxlength="200" id="chrAgency" name="chrAgency" value="<?=$UserInfo['chrAgency']?>" /></div>
<?
				}
?>
				</div>
				</td>			
			</tr>
		</table>
		<div class="FormName">Events Attendee Has Signed up For</div>
		<div class="FormField">
			<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
				<tr>
					<th>Re-Send</th>
					<? sortList('Event Name', 'chrEventTitle', '', 'id='.$_REQUEST['id'].'&idEventSeries='.$_REQUEST['idEventSeries'] ); ?>
					<? sortList('Venue', 'chrVenue', '', 'id='.$_REQUEST['id'].'&idEventSeries='.$_REQUEST['idEventSeries']); ?>
					<? sortList('Signup Date/Time', 'dtFormated', '', 'id='.$_REQUEST['id'].'&idEventSeries='.$_REQUEST['idEventSeries']); ?>
					<? sortList('Status', 'chrStatus', '', 'id='.$_REQUEST['id'].'&idEventSeries='.$_REQUEST['idEventSeries']); ?>	
					<th>Change Status To</th>
				</tr>
		<? $count=0;	
		while ($row = mysqli_fetch_assoc($result)) { 
		?>
		<input type='hidden' name='chrCancel' value='<?=$row['chrCancel']?>' >	
					<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
					onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
						<td style="text-align:center; vertical-align:middle;"><input type="checkbox" name="resendID[]" id="resendID" value="<?=$row['ID']?>" /></td>
						<td><?=$row['chrEventTitle']?></td>
						<td><?=$row['chrVenue']?></td>
						<td><?=$row['dtFormated']?></td>
						<td><?=$row['chrStatus']?></td>
						<td><select class='FormField' id="idStatusChange" name="idStatusChange<?=$row['ID']?>">
								<option value="">No Change</option>
								<option value="1">Confirmed</option>
								<option value="2">WaitList</option>
								<option value="3">Cancelled</option>
							</select>
						</td>
						
					</tr>
		<?	} 
		if($count == 0) { ?>
					<tr>
						<td align="center" colspan='6'>No Events to display</td>
					</tr>
		<?	} ?>
			</table>		
		
		<input class='FormButtons' type='button' value='Update Information and/or Resend Confirmation E-mail' onclick="error_check()" />
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' >	
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
