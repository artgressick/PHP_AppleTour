<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Registration Lead';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "leads";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT * FROM RegLeads WHERE ID=". $_REQUEST['id'],"getting Lead info");

	if(isset($_POST['chrLead'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'RegLeads';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLead',$info['chrLead'],$audit,$table,$_POST['id']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: leads.php");
		die();
	}

	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrLead').focus()";
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrLead', "You must enter a Lead Name.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Edit Registration Lead</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Fill out items and click update.</div>
	<div id='errors'></div>
	<div class='innerbody'>
			<table id='twoCol' class='twoCol' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<td class="left">
					<div class='FormName'>Lead Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLead' id='chrLead' size="126" maxlength="200" value="<?=$info['chrLead']?>" /></div>
				
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
