<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Registration Lead';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "leads";		 // This is needed to highlight the show section
	require($BF. '_lib.php');

    function toAlphaNumber($num) {
        $anum = '';
        while( $num >= 1 ) {
            $num = $num - 1;
            $anum = chr(($num % 26)+65).$anum;
            $num = $num / 26;
        }
        return $anum;
    }
	
	if(isset($_POST['chrLead'])) { // When doing isset, use a required field.  Faster than the php count funtion.

		$clear = false;
		while (!$clear) {
			$code = toAlphaNumber(rand(1, 9999999999));
			$test = fetch_database_query("SELECT COUNT(ID) AS intCount FROM RegLeads WHERE chrCode='".$code."'","Checking Code");
			if($test['intCount'] == 0) { $clear = true; }
		}
		
		
		$q = "INSERT INTO RegLeads SET 
			 chrLead='". encode($_POST['chrLead']) ."',
			 chrCode='". encode($code) ."'
		";
		database_query($q,"Insert Registration Lead");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=1, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['chrLead']) ."',
			dtDateTime=now(),
			chrTableName='RegLeads',
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
		<td class="title">Add Reg Lead</td>
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
					<div class='FormName'>Lead Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLead' id='chrLead' size="126" maxlength="200" /></div>

				</td>			
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Add Lead And Return' onclick="document.getElementById('moveTo').value='addlead.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='leads.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
