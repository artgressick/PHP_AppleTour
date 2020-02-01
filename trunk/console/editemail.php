<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	if(!isset($_REQUEST['idSeries']) || !is_numeric($_REQUEST['idSeries'])) { header("Location: event_series.php"); die(); }
	if(!isset($_REQUEST['idType']) || !is_numeric($_REQUEST['idType'])) { header("Location: email_setup.php?id=".$_REQUEST['idSeries']); die(); }
	$info = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE ID=". $_REQUEST['idSeries'],"getting Event Series info");
	$emailtype = fetch_database_query("SELECT * FROM EmailTypes WHERE ID=".$_REQUEST['idType'],"Getting Email Type Info");
	if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) { 
		$email = fetch_database_query("SELECT ID, chrSubject, txtBody FROM Emails WHERE idType=".$_REQUEST['idType']." AND idEventSeries=".$_REQUEST['idSeries'],"Getting Email Info");
	} else { $email = 0; }
	
	
	if(isset($_POST['txtBody'])) { 

			// Set the basic values to be used.
			//   $table = the table that you will be connecting to to check / make the changes
			//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
			//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
			$table = 'Emails';
			$mysqlStr = '';
			$audit = '';
	
		if($_POST['id'] == '') {
			$q = "INSERT INTO ".$table." SET
					idEventSeries = '".$info['ID']."',
					idType = '".$emailtype['ID']."',
					chrSubject = '".encode($_POST['chrSubject'])."',
					txtBody = '".encode($_POST['txtBody'])."'
				";
			database_query($q,"Insert Email into table");
		} else {
			
			// "List" is a way for php to split up an array that is coming back.  
			// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
			//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
			//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
			//    ...  This also will ONLY add changes to the audit table if the values are different.
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrSubject',$email['chrSubject'],$audit,$table,$email['ID']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'txtBody',$email['txtBody'],$audit,$table,$email['ID']);		
			
			// if nothing has changed, don't do anything.  Otherwise update / audit.
			if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $email['ID']); }
		}
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
<?
	if(!in_array($_REQUEST['idType'],array(5,6,7))) {
?>
		total += ErrorCheck('chrSubject', "You must enter a Subject for this E-mail.");
<?
	}
?>
		total += CustomError('You must enter something for the Body','txtBody','tinyMCE');
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
		relative_urls : false,
		remove_script_host : false,
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
		<td class="title"><?=$info['chrTitle']?> E-mail Setup for <?=$emailtype['chrType']?> E-mail.</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">
	<div class='instructions'>
		<div style='color:red;padding-top:5px;padding-bottom:5px;'>WARNING!!  Due to compatibility issues, please use FireFox to add or edit E-mail templates. This will not affect recipients of these e-mails.</div>
		<div><span style='font-weight:bold;'>E-mail Description:</span> <?=$emailtype['chrDescription']?></div>
		<div style='padding-top:10px; font-weight:bold;'>Legend: <span class='Required'>(These are only for the E-mail Body)</span></div>
		<table cellpadding='3' cellspacing='0' style='width:100%;'>
<?
		if(!in_array($_REQUEST['idType'],array(5,6,7))) {
?>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$FIRST_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$FIRST_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Attendee's First Name (ie. <?=$_SESSION['chrFirst']?>) </td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$LAST_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$LAST_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Attendee's Last Name (ie. <?=$_SESSION['chrLast']?>)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$SERIES_TITLE <a href='#' style='font-size:9px;' onclick='insertHTML("$SERIES_TITLE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Title of Series (ie. <?=$info['chrTitle']?>)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$EVENT_INFO <a href='#' style='font-size:9px;' onclick='insertHTML("$EVENT_INFO")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>List of Selected Events (is replaced by the Event-List template)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Name (ie. Apple Inc.)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ADDRESS <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ADDRESS")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Address (ie. 1 Infinite Loop)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_CITY <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_CITY")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's City (ie. Cupertino)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_STATE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_STATE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's State (ie. CA)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_POSTAL <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_POSTAL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Postal (ie. 95014)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_COUNTRY <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_COUNTRY")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Country (ie. US)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ROOM <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ROOM")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Room (ie. Room 123)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_PHONE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_PHONE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's  (ie. 800-555-1234)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_BASIC_DIRECTIONS <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_BASIC_DIRECTIONS")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replace by Manual Directions from the Venue info.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_TRAVEL_URL <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_TRAVEL_URL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Travel URL from the Venue info.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ONLINE_MAP <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ONLINE_MAP")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Online Map URL from the Venue Info.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_CONTACT_PERSON <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_CONTACT_PERSON")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by Contact Name from the Venue Info.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_NOTES <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_NOTES")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Notes from the Venue Info.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_TIMEZONE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_TIMEZONE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Time Zone Selected for the Venue.</td>
			</tr>
<?
			if($_REQUEST['idType'] != 3) {
?>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$CANCEL_ALL <a href='#' style='font-size:9px;' onclick='insertHTML("$CANCEL_ALL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Link to Cancel ALL events signed up for.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>&nbsp;</td>
				<td style=''>&nbsp;</td>
				<td style='width:50%; font-size:9px;'>&nbsp;</td>
			</tr>
<?
			}
		}
		if(in_array($_REQUEST['idType'],array(5,6,7))) {
?>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$EVENT_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$EVENT_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Name of Event/Session.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$EVENT_SHORT_DESCRIPTION <a href='#' style='font-size:9px;' onclick='insertHTML("$EVENT_SHORT_DESCRIPTION")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Short Description from the Event Title.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$EVENT_LONG_DESCRIPTION <a href='#' style='font-size:9px;' onclick='insertHTML("$EVENT_LONG_DESCRIPTION")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Long Description from the Event Title.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$DATES_TIMES <a href='#' style='font-size:9px;' onclick='insertHTML("$DATES_TIMES")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>List of Dates and Times for this Event (ie:<br /><span style='font-size:8px;'>Tuesday, February 5th, 2008 from 10:00 am to 5:00 pm<br/>Wednesday, February 6th, 2008 from 10:00 am to 5:00 pm</span>)</td>
			</tr>
<?
			if($_REQUEST['idType'] != 7) {
?>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$CANCEL_EVENT <a href='#' style='font-size:9px;' onclick='insertHTML("$CANCEL_EVENT")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Link to Cancel this Event Only.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'></td>
				<td style=''></td>
				<td style='width:50%; font-size:9px;'></td>
			</tr>
<?
			}
		}
?>
		</table>
	</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id="twoCol" class="twoCol" style="width: 100%;" cellpadding="0" cellspacing="0">
			<tr>
				<td class="left" colspan='3'>
<?
	if(!in_array($_REQUEST['idType'],array(5,6,7))) {
?>

					<div class='FormName'>Subject <span class='Required'>(Required)</span></div>
					<div style="padding-bottom:10px;"><input type='text' name='chrSubject' id='chrSubject' size="126" maxlength="200" value="<?=decode($email['chrSubject'])?>" /></div>
<?
	}
?>

					<div class='FormName'>E-mail Body <span class='Required'>(Required)</span></div>
					<div class='FormField'><textarea id="txtBody" name="txtBody" style="width:100%;" wrap="virtual" rows="40"><?=decode($email['txtBody'])?></textarea></div>


				</td>
			</tr>
		</table>
		<input type='hidden' name='idSeries' value='<?=$info['ID']?>' />
		<input type='hidden' name='idType' value='<?=$emailtype['ID']?>' />
		<input type='hidden' name='id' value='<?=$email['ID']?>' />
		<input class='FormButtons' type='submit' value='Save' />
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
