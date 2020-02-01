<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) { header("Location: event_series.php"); die(); }
	$info = fetch_database_query("SELECT ID, chrTitle, txtThankYou FROM EventSeries WHERE ID=". $_REQUEST['id'],"getting Event Series info");
	
	if(isset($_POST['txtThankYou'])) { 

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
		list($mysqlStr,$audit) = set_strs($mysqlStr,'txtThankYou',$email['txtThankYou'],$audit,$table,$info['ID']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $info['ID']); }
		header("Location: event_series.php?id=". $info['ID']);
		die();
	}
	
	include($BF. 'components/list/sortList.php'); 
	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "tinyMCE.getInstanceById('txtThankYou').getWin().document.body.style.backgroundColor='#ebebeb';";
	
	//This is the include file for the overlay
	$TableName = "";
	include($BF. 'includes/overlay.php');
	
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;
		total += CustomError('You must enter something for the Thank You Page','txtThankYou','tinyMCE');
		return (total == 0 ? true : false);
	}
</script>
<script type="text/javascript" src="<?=$BF?>components/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'simple,advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
});
</script>
<!-- Needs to be seperate script tags! -->
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,filemanager",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		theme_advanced_buttons2_add : "separator,forecolor,backcolor",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator",
		theme_advanced_buttons3_add : "emotions,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_path_location : "bottom",
		content_css : "/example_data/example_full.css",
	    plugin_insertdate_dateFormat : "%Y-%m-%d",
	    plugin_insertdate_timeFormat : "%H:%M:%S",
		extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		external_link_list_url : "example_data/example_link_list.js",
		external_image_list_url : "example_data/example_image_list.js",
		flash_external_list_url : "example_data/example_flash_list.js",
		file_browser_callback : "mcFileManager.filebrowserCallBack",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : true,
		apply_source_formatting : true,
		
		filemanager_rootpath : "<?=realpath($BF . 'uploads')?>",
		filemanager_path : "<?=realpath($BF . 'uploads')?>",
		relative_urls : true,
		document_base_url : "<?=$PROJECT_ADDRESS?>"
	});
	function insertHTML(html) {
	    tinyMCE.execInstanceCommand("mce_editor_0","mceInsertContent",false,html);
	}
</script>
<!-- /tinyMCE -->
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title"><?=$info['chrTitle']?> Thank You Page.</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">
	<div class='instructions'>
		<div style='color:red;padding-top:5px;padding-bottom:5px;'>WARNING!!  Due to compatibility issues, please use FireFox to edit the Thank You page. This will not affect people seeing the Thank You page.</div>
		<div>Fill out the field below with the text and layout you would like the Thank You page to look like. Background is a dark grey, use Light Font Colors.</div>
		<div style='padding-top:10px; font-weight:bold;'>Legend:</div>
		<table cellpadding='3' cellspacing='0' style='width:100%;'>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$FIRST_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$FIRST_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Attendee's First Name (ie. <?=$_SESSION['chrFirst']?>)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$LAST_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$LAST_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Attendee's Last Name (ie. <?=$_SESSION['chrLast']?>)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$EMAIL <a href='#' style='font-size:9px;' onclick='insertHTML("$EMAIL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Attendee's E-mail Address (ie. <?=$_SESSION['chrEmail']?>)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$SERIES_TITLE <a href='#' style='font-size:9px;' onclick='insertHTML("$SERIES_TITLE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Title of Series (ie. <?=$info['chrTitle']?>)</td>
			</tr>
		</table>
	</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id="twoCol" class="twoCol" style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td class="left" colspan='3'>
					<div class='FormName'>Thank You Page <span class='Required'>(Required)</span></div>
					<div class='FormField'><textarea id="txtThankYou" name="txtThankYou" style="width:100%;" wrap="virtual" rows="40"><?=decode($info['txtThankYou'])?></textarea></div>


				</td>
			</tr>
		</table>
		<input type='hidden' name='id' value='<?=$info['ID']?>' />
		<input class='FormButtons' type='submit' value='Save' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
