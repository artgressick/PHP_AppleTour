<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT ID, chrTitle, chrEmailName, chrFromEmail, chrImageName, chrLandingText, chrGroupBy FROM EventSeries WHERE ID=". $_REQUEST['id'],"getting Event Series info");

	if(isset($_POST['chrTitle'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'EventSeries';
		$mysqlStr = '';
		$audit = '';
		
		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrTitle',$info['chrTitle'],$audit,$table,$info['ID']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmailName',$info['chrEmailName'],$audit,$table,$info['ID']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFromEmail',$info['chrFromEmail'],$audit,$table,$info['ID']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLandingText',$info['chrLandingText'],$audit,$table,$info['ID']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrGroupBy',$info['chrGroupBy'],$audit,$table,$info['ID']);
		
		
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_REQUEST['id']); }

		if(is_uploaded_file($_FILES['chrLogo']['tmp_name'])) {
			$phName = str_replace(" ","_",basename($_FILES['chrLogo']['name']));
			
			database_query("UPDATE EventSeries SET 
				intImageSize=". $_FILES['chrLogo']['size'] .",
				chrImageName='". $info['ID'] ."-". $phName ."',
				chrImageType='". $_FILES['chrLogo']['type'] ."'
				WHERE ID=". $info['ID'] ."
				","insert image");
				
			$uploaddir = $BF . 'images/';
			$uploadfile = $uploaddir . $info['ID'] .'-'. $phName;

			move_uploaded_file($_FILES['chrLogo']['tmp_name'], $uploadfile);
		}

		if ( $_POST['idEventTitle'] != "" ) {
			$q = "INSERT INTO Events SET 
				 idEventSeries='".	$info['ID'] ."',
				 idEventTitle='".	$_POST['idEventTitle'] ."',
				 idVenue='".	 	$_POST['idVenue'] ."',		
				 idUser='".	 	 	$_SESSION['idUser'] ."',
				 bShow='".			$_POST['bShow'] ."'
				";
			if(database_query($q,"Insert Event into Series")) {

				global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
				$newID2 = mysqli_insert_id($mysqli_connection);
					
				$class_dates = array();		
				$i = 1;
				$j = 0;
				while ($i <= $TOTAL_DATE_FIELDS) {		
					if ($_POST['dDate'.$i] != "") {
				 		$class_dates[$j] = array();
				 		$class_dates[$j]['dDate'] = date('Y-m-d',strtotime($_POST['dDate'.$i])); 
				 		$class_dates[$j]['tBegin'] = date('G:i:s',strtotime($_POST['tBegin'.$i]));
				 		$class_dates[$j]['tEnd'] = date('G:i:s',strtotime($_POST['tEnd'.$i]));
						$j++;
				 	}
				 	$i++;
				}
		
				sort($class_dates);
		
				if(count($class_dates) > 0) {
					$q2 = "";
					foreach ($class_dates as $k => $entry) {
						$q2 .= "('". $newID2 ."','". $entry['dDate'] ."','". $entry['tBegin'] ."','". $entry['tEnd'] ."'),";
					}
					$q2 = substr($q2, 0, -1);
					database_query("INSERT INTO EventDates (idEvent,dDate,tBegin,tEnd) VALUES ".$q2,"Insert Dates into DB");
				}
			}
		}	

		if($_POST['moveTo'] == "editevent_series.php") { $_POST['moveTo'] .= "?id=".$newID; }
				
		header("Location: ". $_POST['moveTo']);
		die();
	}
	
	
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dDate, tBegin"; }
	
	$Venues = database_query("SELECT ID, chrVenue, chrCity, chrState FROM Venues WHERE !bDeleted","getting Venues info");
	
	$Events = database_query("
	SELECT Events.ID, Events.bShow, chrName, (SELECT tBegin FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tBegin, (SELECT tEnd FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as tEnd, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) dDate, chrVenue, chrCity, chrState
	FROM Events 
	JOIN EventTitles ON EventTitles.ID = Events.idEventTitle
	LEFT JOIN Venues ON Events.idVenue=Venues.ID
	WHERE !Events.bDeleted AND idEventSeries = ".$_REQUEST['id'] ."
	ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol']
	,"getting Events info");
	
	$TimeZone = database_query("SELECT * FROM TimeZone","getting TimeZone info");
	
	$EventTitles = database_query("SELECT ID,chrName FROM EventTitles WHERE !bDeleted","getting EventTitles info");
	
	$Referrals = database_query("SELECT ID,chrName FROM Referral WHERE !bDeleted","getting Referral info");	

	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrTitle').focus()";
	
	//This is the include file for the overlay
	$TableName = "Events";
	include($BF. 'includes/overlay.php');
	
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrTitle', "You must enter a Event Series Title.");
		total += ErrorCheck('chrEmailName', "You must enter a From E-mail Name.");
		total += ErrorCheck('chrFromEmail', "You must enter a From E-mail Address.");
		total += ErrorCheck('chrLandingText', "You must enter the Landing Page Choose By Text.");
		total += ErrorCheck('chrGroupBy', "You must select a Landing Page Group By option.");

		return (total == 0 ? true : false);
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Edit Session</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">
<div class='instructions'>Fill out items and click a Save Button.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id="twoCol" class="twoCol" style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td class="left" colspan='3'>

					<div class='FormName'>Event Series Main Title <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrTitle' id='chrTitle' style="width:100%;" value="<?=$info['chrTitle']?>" /></div>

				</td>
			</tr>
			<tr>
				<td class="left">

					<div class='FormName'>Landing Page Choose Text <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrLandingText' id='chrLandingText' maxlength='200' style="width:100%;" value="<?=$info['chrLandingText']?>" /></div>

				</td>
				<td class="gutter"></td>
				<td class="right">

					<div class='FormName'>Landing Page Group By <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;">
						<select class='FormField' id="chrGroupBy" name='chrGroupBy' style="width:100%;" >
							<option value='idVenue' <?=($info['chrGroupBy'] == 'idVenue' ? "selected='selected'" : '')?>>Venue</option>
							<option value='idEventTitle' <?=($info['chrGroupBy'] == 'idEventTitle' ? "selected='selected'" : '')?>>Event Title (All Event Venus must be the same)</option>
							
						</select>
					</div>

				</td>
			</tr>
			<tr>
				<td class="left">
				
					<div class='FormName'>From E-mail Name <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrEmailName' id='chrEmailName' style="width:100%;" maxlength='150' value="<?=$info['chrEmailName']?>" /></div>
				
				</td>
				<td class="gutter"></td>
				<td class="right">

					<div class='FormName'>From E-mail Address <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrFromEmail' id='chrFromEmail' style="width:100%;" maxlength='150' value="<?=$info['chrFromEmail']?>" /></div>

				</td>				
			</tr>
			<tr>
				<td class="left">
				
					<div class='FormName'>Upload New Logo Image</div>
					<div style="padding-bottom:10px;"><input type='file' name='chrLogo' id='chrLogo' style="width:100%;" /></div>
				
				</td>
				<td class="gutter"></td>
				<td class="right">
					<?=($info['chrImageName'] != '' ? "<div class='FormName'>Current Logo Image <span class='Required'>(May be shrunk on this preview)</span></div><img width='100%' src='".$BF."images/".$info['chrImageName']."' border='0' />" : '')?>
				</td>				
			</tr>
			<tr>
				<td colspan='3'>
					<div style="border:1px #AAA solid; padding:5px;">
						<div style="padding-bottom:5px;"><strong>Add Event to Series</strong></div>
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
							<tr>
								<td style="width:50%;">
									<div class='FormName'>Show this event.</div>
									<div class='FormField'><input type="radio" id="bShow" name="bShow" value="0" "checked='checked' /> No <input type="radio" id="bShow" name="bShow" value="1" /> Yes</div>
								
									<div class='FormName'>Event Title</div>
									<select class='FormField' id="idEventTitle" name='idEventTitle' style="width:98%;" >
											<option value=''>Select Event Title</option>					
										<? while ($row = mysqli_fetch_assoc($EventTitles)) { ?>
											<option value='<?=$row['ID']?>'><?=$row['chrName']?></option>
										<?	} ?>
									</select>
								</td>
								<td style="width:50%;">
									<div class='FormName'>Venue</div>
									<select class='FormField' id="idVenue" name='idVenue' style="width:100%;" >
										<option value=''>Select Venue</option>
									<? while ($row = mysqli_fetch_assoc($Venues)) { ?>
										<option value='<?=$row['ID']?>'><?=$row['chrVenue']?> (<?=$row['chrCity']?>, <?=$row['chrState']?>)</option>
									<?	} ?>
									</select>
								</td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<?
						$i = 1;
						while ($i <= $TOTAL_DATE_FIELDS) {
?>
							<tr>
								<td style="width:33%;">
									<div class='FormName'>Date <?=$i?> <span class='Required'>(5/24/2008)</span></div>					
									<div class='FormField'><input type='text' name='dDate<?=$i?>' id='dDate<?=$i?>' style="width:98%" /></div>
								</td>
								<td style="width:34%;">
									<div class='FormName'>Begin Time <?=$i?> <span class='Required'>(8:00 am)</span></div>
									<div class='FormField'><input type='text' name='tBegin<?=$i?>' id='tBegin<?=$i?>' style="width:98%" /></div>
								</td>
								<td style="width:33%;">
									<div class='FormName'>End Time <?=$i?> <span class='Required'>(3:00 pm)</span></div>					
									<div class='FormField'><input type='text' name='tEnd<?=$i?>' id='tEnd<?=$i?>' style="width:100%" /></div>
								</td>
							</tr>
<?
							$i++;
						}
?>
						</table>
					</div>
				</td>
			</tr>
		</table>				
<?
	// Lets pull all the dates for this Event Series
	$q = "SELECT EventDates.idEvent, EventDates.dDate 
			FROM Events 
			JOIN EventDates ON EventDates.idEvent=Events.ID AND Events.idEventSeries=".$info['ID']." 
			ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
			
	$results = database_query($q,"Grabbing All Dates for this Event Series");

	$eventDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	while ($row = mysqli_fetch_assoc($results)) {

		if($prevID != $row['idEvent']) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $row['idEvent'];
			$eventDates['chrDates'.$row['idEvent']] = "";
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
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));
?>

		<div style='padding:10px 0 5px 0;'><strong>Current Events for Series</strong></div>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>
				<th>Status</th>
				<? sortList('Name', 'chrName', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Date(s)', 'dDate', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Time Begin', 'tBegin', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Time End', 'tEnd', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Venue', 'chrVenue', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('City', 'chrCity', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('State', 'chrState', '', 'id='.$_REQUEST['id']); ?>														
				
				<th><img src="<?=$BF?>images/options.gif"></th>
			</tr>
	<? $count=0;	
	while ($row = mysqli_fetch_assoc($Events)) { ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=($row['bShow']==0?'Hidden':'Shown')?></td>
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=$row['chrName']?> (<?=$row['ID']?>)</td>
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=$eventDates['chrDates'.$row['ID']]?></td>
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=date('g:i a',strtotime($row['tBegin']))?></td>
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=date('g:i a',strtotime($row['tEnd']))?></td>								
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=$row['chrVenue']?></td>								
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=$row['chrCity']?></td>								
					<td style='cursor: pointer;' onclick='newwin = window.open("popup-editevent.php?idEvent=<?=$row['ID']?>","new","width=600,height=500,resizable=1,scrollbars=1"); newwin.focus();'><?=$row['chrState']?></td>								
					<td class='options' style="vertical-align:middle;"><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
					<a href="javascript:warning(<?=$row['ID']?>,'<?=$row['chrName']?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
					</div></td>		
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Event Titles to display</td>
				</tr>
	<?	} ?>
		</table>					

		<input class='FormButtons' type='submit' value='Save and Add another Event' onclick="document.getElementById('moveTo').value='editevent_series.php?id=<?=$_REQUEST['id']?>';" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='submit' value='Save and Start New Event Series' onclick="document.getElementById('moveTo').value='addevent_series.php';" /> &nbsp;&nbsp;
		<input class='FormButtons' type='submit' value='Save and Move on' onclick="document.getElementById('moveTo').value='event_series.php';" />
		<input type='hidden' name='moveTo' id='moveTo' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
