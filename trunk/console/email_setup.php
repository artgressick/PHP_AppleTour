<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) { header("Location: event_series.php"); die(); }
	$info = fetch_database_query("SELECT ID, chrTitle, bPrivate FROM EventSeries WHERE ID=". $_REQUEST['id'],"getting Event Series info");

	if(isset($_POST['bPrivate'])) { 

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
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bPrivate',$info['bPrivate'],$audit,$table,$info['ID']);		
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_REQUEST['id']); }
				
		header("Location: email_setup.php?id=". $info['ID']);
		die();
	}
	
	
	include($BF. 'components/list/sortList.php'); 
	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "";
	
	//This is the include file for the overlay
	$TableName = "";
	include($BF. 'includes/overlay.php');
	
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		return (total == 0 ? true : false);
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title"><?=$info['chrTitle']?> E-mail Setup.</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">
	<div class='instructions' style='color:red; font-weight:bold;'>PLEASE NOTE: Preview and Test E-mails will use a combination of real and false data, Cancel Links will not work in Preview and Test Modes. Test E-mails will have "TEST:" added to the beginning of the subject.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id="twoCol" class="twoCol" style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td class="left" colspan='3'>

					<div class='FormName'>Does this Event Series require the use of a alternative E-mail at Registration instead of the normal Confirmed / Waitlist E-mails? <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='radio' id='bPrivate0' name='bPrivate' value='0' <?=(!$info['bPrivate'] ? "checked='checked'" : '')?> /> No (Use Normal E-mails) &nbsp;&nbsp; <input type='radio' id='bPrivate1' name='bPrivate' value='1' <?=($info['bPrivate'] ? "checked='checked'" : '')?> /> Yes (Send Overide E-mail Instead)</div>

				</td>
			</tr>
		</table>
		
		<div style='font-weight:bold;'>Add/Edit E-mail Templates</div>
<?
	$types = database_query("SELECT * FROM EmailTypes WHERE ID IN (1,2,9,3,4,8,10) ORDER BY intOrder","Getting E-mail Types");
?>

		<table cellpadding='5' cellspacing='0' style='width:100%;'>
			<tr>
<?
			while($row = mysqli_fetch_assoc($types)) {
?>
				<td style='border:1px solid #666; width:14%; vertical-align:top;'>
					<div style='font-weight:bold;text-align:center; white-space:nowrap;'><?=$row['chrType']?></div>
					<div style='font-size:9px;height:100px;'><?=$row['chrDescription']?></div>
					<div style='border-top:1px solid #666;padding: 5px 0; text-align:center;'>
<?
					$test = fetch_database_query("SELECT ID FROM Emails WHERE idEventSeries=".$info['ID']." AND idType=".$row['ID'],"Checking for email");
					if($test['ID'] == '' || $test['ID'] == 0) {
?>
						<a href='editemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>'>Add E-mail</a>
<?
					} else {
?>
						<a href='editemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>&id=<?=$test['ID']?>'>Edit</a>
						 | <a href='previewemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>&id=<?=$test['ID']?>'>Preview</a>
						 | <a href='testemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>&id=<?=$test['ID']?>'>Test</a>
<?
					}
?>
					</div>
				</td>
<?
			}
?>
			</tr>
		</table>
		<div style='font-weight:bold; padding-top:10px;'>Add/Edit E-mail Template Lists</div>
<?
	$types = database_query("SELECT * FROM EmailTypes WHERE ID IN (5,6,7) ORDER BY intOrder","Getting E-mail Types");
?>

		<table cellpadding='5' cellspacing='0' style='width:100%;'>
			<tr>
<?
			while($row = mysqli_fetch_assoc($types)) {
?>
				<td style='border:1px solid #666; width:33%; vertical-align:top;'>
					<div style='font-weight:bold;text-align:center;'><?=$row['chrType']?></div>
					<div style='font-size:9px;height:40px;'><?=$row['chrDescription']?></div>
					<div style='border-top:1px solid #666;padding: 5px 0; text-align:center;'>
<?
					$test = fetch_database_query("SELECT ID FROM Emails WHERE idEventSeries=".$info['ID']." AND idType=".$row['ID'],"Checking for email");
					if($test['ID'] == '' || $test['ID'] == 0) {
?>
						<a href='editemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>'>Add Template</a>
<?
					} else {
?>
						<a href='editemail.php?idSeries=<?=$info['ID']?>&idType=<?=$row['ID']?>&id=<?=$test['ID']?>'>Edit Template</a>
<?
					}
?>
					</div>
				</td>
<?
			}
?>
			</tr>
		</table>

		<input class='FormButtons' type='submit' value='Save' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
