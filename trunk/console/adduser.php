<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add User';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "users";		 // This is needed to highlight the show section
	
	require($BF. '_lib.php');

	if(isset($_POST['chrLast'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Users SET 
			 chrFirst='". encode($_POST['chrFirst']) ."',
			 chrLast='". encode($_POST['chrLast']) ."',
			 chrEmail='". $_POST['chrEmail'] ."',			 
			 chrPassword=SHA1('" . $_POST['chrPassword'] . "')
		";
		database_query($q,"Insert user");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=1, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['chrLast']) ."',
			dtDateTime=now(),
			chrTableName='Shows',
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

		total += ErrorCheck('chrLast', "You must enter a Name.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>

<form name='idForm' id='idForm' action='' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Add User</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add a User, fill in all the information and click on one of the options at the bottom of the page.</div>

	<div class='innerbody'>
	
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' maxlength="80" size="40" /></div>
					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' maxlength="80" size="40" /></div>					
								
				</td>
				<td class='gutter'>	</td>
				<td class='right'>

					<div class='FormName'>Email Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' maxlength="50" size="40" /></div>
					<div class='FormName'>Password <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrPassword' id='chrPassword' maxlength="40" size="40" /></div>
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Add User And Return' onclick="document.getElementById('moveTo').value='adduser.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='users.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />

	</div>

</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
