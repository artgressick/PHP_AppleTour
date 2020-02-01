<?	
	$BF = '../';
	$title = 'Send E-mail to Attendees';
	require($BF. '_lib.php');

	$active = 'emails';
	$subactive = 'emailattendees';	
	
	if (isset($_POST['txtMsg'])) {
	
		if ($_POST['idEvent'] == 0) { $_POST['idEvent'] = '%'; };
		if ($_POST['idStatus'] == 0) { $_POST['idStatus'] = '%'; };
	
	
		$_SESSION['email'] = $_POST;  // Moving all submitted data to a Session Variable so we can recall it later
		
		header("Location: ".$BF."console/preview.php");
		die(); 		
	}
	include($BF. 'includes/meta_admin.php');		
	// Load Drop Downs for Page 	
	$EventSeries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle", "Grabbing all Event Series");
	if (!isset($_REQUEST['idEventSeries']) || !is_numeric($_REQUEST['idEventSeries'])) { $_REQUEST = fetch_database_query("Select ID AS idEventSeries FROM EventSeries WHERE !bDeleted ORDER BY chrTitle LIMIT 1", "Grabbing First Event Series"); }
	$Venues = database_query("SELECT DISTINCT Venues.ID, chrVenue, chrCity, chrState FROM Venues JOIN Events ON Events.idVenue=Venues.ID WHERE !Venues.bDeleted AND !Events.bDeleted AND Events.idEventSeries='".$_REQUEST['idEventSeries']."' ORDER BY chrVenue", "Grabbing all Venues");
	$Status = database_query("SELECT * FROM Status ORDER BY ID", "Getting all Status");
		
	if (isset($_REQUEST['idEventSeries']) && isset($_REQUEST['idVenue']) && is_numeric($_REQUEST['idEventSeries']) && is_numeric($_REQUEST['idVenue'])) {
		$Events = database_query("SELECT Events.ID, chrName FROM Events JOIN EventTitles ON Events.idEventTitle=EventTitles.ID WHERE !Events.bDeleted AND !EventTitles.bDeleted AND Events.idEventSeries=".$_REQUEST['idEventSeries']." AND
								 Events.idVenue=".$_REQUEST['idVenue']." ORDER BY chrName"
		, "Grabbing all Events For Series and Venue");	
	}	
	
	?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventSeries', "Please Select a Event Series.");		
		total += ErrorCheck('idVenue', "Please Select a Venue.");	
		total += ErrorCheck('idEvent', "Please Select a Event.");
		total += ErrorCheck('idStatus', "Please Select a Status.");
		total += ErrorCheck('chrSubject', "You Must Enter Subject.");	
		total += CustomError('You Must Enter a Message.','txtMsg','tinyMCE');
		

		if(total == 0) { document.getElementById('idForm').submit(); }
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
<form name='idForm' id='idForm' action='' method="post">	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" nowrap="nowrap">Email Attendees</td>
		<td class="title" nowrap="nowrap">Series:</td>        
		<td class="title_right" style="vertical-align:bottom; text-align:left;" nowrap="nowrap"><select class='FormField' id="idEventSeries" name='idEventSeries' style="width:100px;" onchange='location.href="emailattendees.php?idEvent=<?=$_REQUEST['idEvent']?>&idVenue=<?=$_REQUEST['idVenue']?>&idStatus=<?=$_REQUEST['idStatus']?>&idEventSeries="+this.value'>
			<? while ($row = mysqli_fetch_assoc($EventSeries)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idEventSeries']) && $row['ID'] == $_REQUEST['idEventSeries'] ? ' selected="selected"' : "" )?>><?=decode($row['chrTitle'])?></option>
			<?	} ?>
			</select></td>
       	<td class="title" nowrap="nowrap">Venue:</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;" nowrap="nowrap"><select class='FormField' id="idVenue" name='idVenue' style="width:100px;" onchange='location.href="emailattendees.php?idEventSeries=<?=$_REQUEST['idEventSeries']?>&idEvent=<?=$_REQUEST['idEvent']?>&idStatus=<?=$_REQUEST['idStatus']?>&idVenue="+this.value' >
				<option value=''>Select Venue Location</option>            
			<? while ($row = mysqli_fetch_assoc($Venues)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idVenue']) && $row['ID'] == $_REQUEST['idVenue'] ? ' selected="selected"' : "" )?>><?=decode($row['chrVenue'])?> (<?=decode($row['chrCity'])?>, <?=decode($row['chrState'])?>)</option>
			<?	} ?>
			</select></td>
       	<td class="title" nowrap="nowrap">Event:</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;" nowrap="nowrap" ><select class='FormField' id="idEvent" name='idEvent' style="width:100px;" onchange='location.href="emailattendees.php?idEventSeries=<?=$_REQUEST['idEventSeries']?>&idVenue=<?=$_REQUEST['idVenue']?>&idStatus=<?=$_REQUEST['idStatus']?>&idEvent="+this.value'>
	            <option value=''>Select Event</option>
				<option value='0'<?=(isset($_REQUEST['idEvent']) && 0 == $_REQUEST['idEvent'] ? ' selected="selected"' : "" )?>>All Events</option>
			<? while ($row = mysqli_fetch_assoc($Events)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idEvent']) && $row['ID'] == $_REQUEST['idEvent'] ? ' selected="selected"' : "" )?>><?=decode($row['chrName'])?> (<?=$row['ID']?>)</option>
			<?	} ?>
			</select></td>
       	<td class="title" nowrap="nowrap">Status:</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;" nowrap="nowrap" ><select class='FormField' id="idStatus" name='idStatus' onchange='location.href="emailattendees.php?idEventSeries=<?=$_REQUEST['idEventSeries']?>&idVenue=<?=$_REQUEST['idVenue']?>&idEvent=<?=$_REQUEST['idEvent']?>&idStatus="+this.value' style="width:100px;">
	            <option value=''>Select Status</option>
				<option value='0'<?=(isset($_REQUEST['idStatus']) && 0 == $_REQUEST['idStatus'] ? ' selected="selected"' : "" )?>>All Status's</option>
			<? while ($row = mysqli_fetch_assoc($Status)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idStatus']) && $row['ID'] == $_REQUEST['idStatus'] ? ' selected="selected"' : "" )?>><?=decode($row['chrName'])?></option>
			<?	} ?>
			</select></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>
	<div style='color:red;padding-top:5px;padding-bottom:5px;'>WARNING!!  Due to compatibility issues, please use FireFox when using this page. This will not affect recipients of these e-mails.  To use thr TRANSFER_LINK, Please select an Event.</div> 
	<div>To send a group of Attendees an e-mail, select fields above, and <strong>THEN</strong> enter information below.<br /><strong>NOTE!</strong> Not Selecting fields ABOVE first may result in data in fields below to clear.</div>
		<div style='padding-top:10px; font-weight:bold;'>Legend: <span class='Required'>(These are only for the E-mail Body)</span></div>
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
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$SERIES_TITLE <a href='#' style='font-size:9px;' onclick='insertHTML("$SERIES_TITLE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Title of Series (ie. 3 Day Editors Training)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_NAME <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_NAME")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Name (ie. Apple Inc.)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ADDRESS <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ADDRESS")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Address (ie. 1 Infinite Loop)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_CITY <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_CITY")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's City (ie. Cupertino)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_STATE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_STATE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's State (ie. CA)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_POSTAL <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_POSTAL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Postal (ie. 95014)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_COUNTRY <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_COUNTRY")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Country (ie. US)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ROOM <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ROOM")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's Room (ie. Room 123)</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_PHONE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_PHONE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Venue's  (ie. 800-555-1234)</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_BASIC_DIRECTIONS <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_BASIC_DIRECTIONS")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replace by Manual Directions from the Venue info.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_TRAVEL_URL <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_TRAVEL_URL")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Travel URL from the Venue info.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_ONLINE_MAP <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_ONLINE_MAP")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Online Map URL from the Venue Info.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_CONTACT_PERSON <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_CONTACT_PERSON")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by Contact Name from the Venue Info.</td>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_NOTES <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_NOTES")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Notes from the Venue Info.</td>
			</tr>
			<tr>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$VENUE_TIMEZONE <a href='#' style='font-size:9px;' onclick='insertHTML("$VENUE_TIMEZONE")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by the Time Zone Selected for the Venue.</td>
				
<?
			if(isset($_REQUEST['idEvent']) && $_REQUEST['idEvent'] != 0) {
?>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'>$TRANSFER_LINK <a href='#' style='font-size:9px;' onclick='insertHTML("$TRANSFER_LINK")' >(Insert)</a></td>
				<td style=''>=</td>
				<td style='width:50%; font-size:9px;'>Is replaced by a link for the Attendee to Transfer their registration to another event.</td>
<?
			} else {
?>
				<td style='width:100px; white-space:nowrap; text-align:right; font-weight:bold;'></td>
				<td style=''></td>
				<td style='width:50%; font-size:9px;'></td>
<?				
			}
?>
			</tr>
		</table>
	</div>
	<div id='errors'></div>
	<div class='innerbody'>
				
		<div class='FormName'>Enter Subject.  The Default Header and Footer will be applied to the e-mail. <span class='Required'>(Required)</span></div>
		<div class='FormField'><input type="text" id="chrSubject" name="chrSubject" style="width:100%;" value="<?=(isset($_POST['chrSubject']) ? $_POST['chrSubject'] : "" )?>" /></div>
		<div class='FormName'>Enter Message Here. <span class='Required'>(Required)</span></div>
		<div class='FormField'><textarea id="txtMsg" name="txtMsg" style="width:100%;" wrap="virtual" rows="30"><?=(isset($_POST['txtMsg']) ? nl2br($_POST['txtMsg']) : "" )?></textarea></div>
		<div class='FormName'><input type='checkbox' value='1' name='resend' id='resend' <?=(isset($_POST['bResend']) && $_POST['bResend'] == 1 ? " checked='checked'" : "" )?> /> Attach Attendees Registration E-mail to this message.</div>
		<div class='FormField'><input class='FormButtons' type='button' value='Preview' onclick="error_check();" /></div>

	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
