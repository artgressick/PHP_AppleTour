<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	if(isset($_POST['chrTitle'])) { // When doing isset, use a required field.  Faster than the php count funtion.

		$checked = false;
		while ($checked == false) {
			$randnum = rand(1111, 99999999);
			$test = database_query("SELECT ID FROM EventSeries WHERE intLink=".$randnum,"Seeing if number exists");
			if(mysqli_num_rows($test) == 0) { $checked = true; }
		}
		$q = "INSERT INTO EventSeries SET 
			 chrTitle='".		encode($_POST['chrTitle']) ."',
			 intLink='".		$randnum."',
			 idUser='".	 	 	$_SESSION['idUser'] ."',
			 chrEmailName='".	encode($_POST['chrEmailName']) ."',
			 chrFromEmail='".	$_POST['chrFromEmail'] ."',
			 chrLandingText='". $_POST['chrLandingText']."',
			 chrGroupBy='". $_POST['chrGroupBy']."'
			 
		";
		if(database_query($q,"Insert Event Series")) {

			// This is the code for inserting the Audit Page
			// Type 1 means ADD NEW RECORD, change the TABLE NAME also
			global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
			$newID = mysqli_insert_id($mysqli_connection);

			if(is_uploaded_file($_FILES['chrLogo']['tmp_name'])) {
				$phName = str_replace(" ","_",basename($_FILES['chrLogo']['name']));
				
				database_query("UPDATE EventSeries SET 
					intImageSize=". $_FILES['chrLogo']['size'] .",
					chrImageName='". $newID ."-". $phName ."',
					chrImageType='". $_FILES['chrLogo']['type'] ."'
					WHERE ID=". $newID ."
					","insert image");
					
				$uploaddir = $BF . 'images/';
				$uploadfile = $uploaddir . $newID .'-'. $phName;
	
				move_uploaded_file($_FILES['chrLogo']['tmp_name'], $uploadfile);
			}
			
			if ( $_POST['idEventTitle'] != "" ) {
				$q = "INSERT INTO Events SET 
					 idEventSeries='".	$newID ."',
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
				
			$q = "INSERT INTO Audit SET 
				idType=1, 
				idRecord='". $newID ."',
				txtNewValue='". encode($_POST['chrTitle']) ."',
				dtDateTime=now(),
				chrTableName='EventSeries',
				idUser='". $_SESSION['idUser'] ."'
			";
			database_query($q,"Insert audit");
			//End the code for History Insert
		}

		if($_POST['moveTo'] == "editevent_series.php") { $_POST['moveTo'] .= "?id=".$newID; } 
				
		header("Location: ". $_POST['moveTo']);
		die();
	}
	


	$Venues = database_query("SELECT ID, chrVenue, chrCity, chrState FROM Venues WHERE !bDeleted","getting Venues info");
	
	$TimeZone = database_query("SELECT * FROM TimeZone","getting TimeZone info");

	$EventTitles = database_query("SELECT ID,chrName FROM EventTitles WHERE !bDeleted","getting EventTitles info");	
	
	$Referrals = database_query("SELECT ID,chrName FROM Referral WHERE !bDeleted","getting Referral info");	


	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrTitle').focus()";
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrTitle', "You must enter a Event Series Title.");
		total += ErrorCheck('chrEmailName', "You must enter a From E-mail Name.");
		total += ErrorCheck('chrFromEmail', "You must enter a From E-mail Address.");
		total += ErrorCheck('chrLogo', "You must enter upload a Logo Graphic.");
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
		<td class="title">Add Event Series</td>
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
					<div style="padding-bottom:10px;"><input type='text' name='chrTitle' id='chrTitle' style="width:100%;" value="" /></div>

				</td>
			</tr>
			<tr>
				<td class="left">

					<div class='FormName'>Landing Page Choose By Text <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrLandingText' id='chrLandingText' maxlength='200' style="width:100%;" value="Please Choose Between these Locations" /></div>

				</td>
				<td class="gutter"></td>
				<td class="right">

					<div class='FormName'>Landing Page Group By <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;">
						<select class='FormField' id="chrGroupBy" name='chrGroupBy' style="width:100%;" >
							<option value='idVenue'>Venue</option>
							<option value='idEventTitle'>Event Title (All Event Venus must be the same)</option>
							
						</select>
						
					</div>

				</td>
			</tr>
			<tr>
				<td class="left">
				
					<div class='FormName'>From E-mail Name <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrEmailName' id='chrEmailName' style="width:100%;" maxlength='150' value="Pro Applications Marketing Events" /></div>
				
				</td>
				<td class="gutter"></td>
				<td class="right">

					<div class='FormName'>From E-mail Address <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrFromEmail' id='chrFromEmail' style="width:100%;" maxlength='150' value="proapps_events@apple.com" /></div>

				</td>				
			</tr>
			<tr>
				<td class="left">
				
					<div class='FormName'>Logo Image Upload <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='file' name='chrLogo' id='chrLogo' style="width:100%;" /></div>
				
				</td>
				<td class="gutter"></td>
				<td class="right">

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

		<input class='FormButtons' type='submit' value='Save and Add another Event' onclick="document.getElementById('moveTo').value='editevent_series.php';" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='submit' value='Save and Start New Event Series' onclick="document.getElementById('moveTo').value='addevent_series.php';" /> &nbsp;&nbsp;
		<input class='FormButtons' type='submit' value='Save and Move on' onclick="document.getElementById('moveTo').value='event_series.php';" />
		<input type='hidden' name='moveTo' id='moveTo' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
