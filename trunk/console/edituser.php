<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit User';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "users";		// Used to highlight the shows section
	
	require($BF. '_lib.php');

	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT * FROM Users WHERE ID=". $_REQUEST['id'],"getting user info");

	if(isset($_POST['chrFirst'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Users';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirst',$info['chrFirst'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLast',$info['chrLast'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);


		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		//Saving New Password if one is supplied
		if ($_POST['chrPassword'] != "" ) {
			$q = "UPDATE Users SET chrPassword=SHA1('" . $_POST['chrPassword'] . "') WHERE ID=".$_POST['id'];
		database_query($q,"Saving New Password");
		}

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: users.php");
		die();
	}
	include($BF. 'includes/meta_admin.php');	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrName').focus()";
		
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrFirst', "You must enter a User Name.");
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
	
</script>

<?
	include($BF. 'includes/top_admin.php');
?>

<form name='idForm' id='idForm' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Edit User</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To edit a User, fill in all the information and click on the "Update Information" button.</div>

	<div class='innerbody'>
	
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>User Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' value="<?=decode($info['chrFirst'])?>" /></div>
					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' maxlength="80" size="40" value="<?=decode($info['chrLast'])?>" /></div>						
			  </td>
			  
			  	<td class='gutter'>
				
			  </td>
				<td>

					<div class='FormName'>Email Address <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' size='40' value="<?=decode($info['chrEmail'])?>" /></div>
					<div class='FormName'>Password <span class='Required'>(Type in new password to reset users password)</span></div>
					<div class='FormField'><input type='text' name='chrPassword' id='chrPassword' maxlength="40" size="40" /></div>					

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
