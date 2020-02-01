<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Venue';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "venue";		 // This is needed to highlight the show section
	require($BF. '_lib.php');

	if(isset($_POST['chrVenue'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Venues SET 
			 chrVenue='".	 encode($_POST['chrVenue']) ."',
			 chrAddress='".	 encode($_POST['chrAddress']) ."',
			 chrAddress2='".	 encode($_POST['chrAddress2']) ."',			 
			 chrCity='".	 encode($_POST['chrCity']) ."',
			 chrState='".	 $_POST['chrState'] ."',
			 chrCountry='".	 $_POST['chrCountry'] ."',
			 chrZip='".		 strip_quotes($_POST['chrZip']) ."',
			 chrPhone='".	 strip_quotes($_POST['chrPhone']) ."',
			 chrRoom='".	 encode($_POST['chrRoom']) ."',
			 intCapacity='". $_POST['intCapacity'] ."',
			 chrContact='".	 encode($_POST['chrContact']) ."',
			 chrDims='".	 encode($_POST['chrDims']) ."',
			 chrGoogle='".	 $_POST['chrGoogle'] ."',
			 chrTravel='".	 encode($_POST['chrTravel']) ."',			 
			 intDropOff='".	 $_POST['intDropOff'] ."',
			 txtNotes='".	 encode($_POST['txtNotes']) ."',	
			 txtDirections='".encode($_POST['txtDirections']) ."',				 
			 idTimeZone='".  $_POST['idTimeZone'] ."',		 
			 idUser='".	 	 $_SESSION['idUser'] ."'
		";
		database_query($q,"Insert Venue");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=1, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['chrVenue']) ."',
			dtDateTime=now(),
			chrTableName='Venues',
			idUser='". $_SESSION['idUser'] ."'
		";
		database_query($q,"Insert audit");
		//End the code for History Insert
		
		header("Location: ". $_POST['moveTo']);
		die();
	}
	include($BF. 'components/states.php');
	include($BF. 'components/countries.php');	
	
	$TimeZone = database_query("SELECT * FROM TimeZone ORDER BY intOffset","getting TimeZone info");


	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrVenue').focus()";
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
		<td class="title">Add Venue</td>
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
					<div class='FormField'><input type='text' name='chrVenue' id='chrVenue' size="50" /></div>

					<div class='FormName'>Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrAddress' id='chrAddress' size="50" /></div>
					<div class='FormField'><input type='text' name='chrAddress2' id='chrAddress2' size="50" /></div>					
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>				
								<div class='FormName'>City <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrCity' id='chrCity' /></div>
							</td>
							<td style="width:5px;"></td>
							<td>
								<div class='FormName'>State <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrState" name='chrState'>
										<?	foreach($states as $st => $name) { ?>
											<option value='<?=@$st?>'><?=$name?></option>
										<?	} ?>
									</select>
								</div>
							</td>
							<td style="width:5px;"></td>							
							<td>
								<div class='FormName'>Zip <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrZip' id='chrZip' size="10" /></div>									
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Country <span class='Required'>(Required)</span></div>
									 <select class='FormField' id="chrCountry" name='chrCountry'>
										<?	foreach($countries as $cy => $name) { ?>
											<option value='<?=@$cy?>'><?=$name?></option>
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
								<div class='FormField'><input type='text' name='chrPhone' id='chrPhone' /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Contact Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrContact' id='chrContact' /></div>
							</td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Room Name <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrRoom' id='chrRoom' /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Room Dimensions <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='chrDims' id='chrDims' /></div>	
							</td>							
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Capacity <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='intCapacity' id='intCapacity' maxlength="6" onchange="calc()" /></div>
							</td>
							<td style="width:25px;"></td>
							<td>
								<div class='FormName'>Drop Off Rate <span class='Required'>(Required)</span></div>
								<div class='FormField'><input type='text' name='intDropOff' id='intDropOff' onchange="calc()" />%</div>
							</td>							

						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td>
								<div class='FormName'>Time Zone <span class='Required'>(Required)</span></div>
								<select class='FormField' id="idTimeZone" name='idTimeZone'  style="width:px;">
								<? while ($row = mysqli_fetch_assoc($TimeZone)) { ?>
									<option value='<?=$row['ID']?>'><?=$row['chrLocation']?></option>
								<?	} ?>
								</select>
							</td>
							<td style="width:13px;"></td>
							<td>
								<div class='FormName'>Total Capacity</div>
								<div class='FormField'><input type='text' size="13" id='totalcap' disabled='disabled'/></div>	
							</td>
						</tr>
					</table>											
				</td>
				<td class="right">
		
				
					<div class='FormName'>Online Map URL <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrGoogle' id='chrGoogle' size="52" /></div>
					
					<div class='FormName'>Travel URL</div>
					<div class='FormField'><input type='text' name='chrTravel' id='chrTravel' size="52" /></div>					

					<div class='FormName'>Manual Dreictions</div>
					<div class='FormField'><textarea id="txtDirections" name="txtDirections" cols="50" rows="13"></textarea></div>
						
					<div class='FormName'>Notes</div>
					<div class='FormField'><textarea id="txtNotes" name="txtNotes" cols="50" rows="13"></textarea></div>
				
				</td>			
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Add Venue And Return' onclick="document.getElementById('moveTo').value='addvenue.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='venues.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
