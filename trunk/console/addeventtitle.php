<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Event Title';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventtitles";		 // This is needed to highlight the show section
	require($BF. '_lib.php');

	if(isset($_POST['chrName'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO EventTitles SET 
			 chrName='".	 encode($_POST['chrName']) ."',
			 txtShort='". encode($_POST['txtShortDescription']) ."',
			 txtLong='".	 encode($_POST['txtLongDescription']) ."',
			 idUser='".	 	 $_SESSION['idUser'] ."'			 

		";
		database_query($q,"Insert Event Title");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=1, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['chrName']) ."',
			dtDateTime=now(),
			chrTableName='EventTitles',
			idUser='". $_SESSION['idUser'] ."'
		";
		database_query($q,"Insert audit");
		//End the code for History Insert
		
		header("Location: ". $_POST['moveTo']);
		die();
	}


	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrName').focus()";
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Name.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Add Session</td>
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
				<td>
					<div class='FormName'>Event Title <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrName' id='chrName' size="126" maxlength="200" /></div>

					<div class='FormName'>Short Description</div>
					<div class='FormField'><textarea id="txtShortDescription" name="txtShortDescription" cols="124" rows="5" wrap="virtual" /></textarea></div>					
	
						
					<div class='FormName'>Long Description</div>
					<div class='FormField'><textarea id="txtLongDescription" name="txtLongDescription" cols="124" rows="15"  wrap="virtual"></textarea></div>
				
				</td>			
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Add Event Title And Return' onclick="document.getElementById('moveTo').value='addeventtitle.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='eventtitles.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
