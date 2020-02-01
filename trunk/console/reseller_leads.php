<?	
	$BF = '../';
	$title = 'Reseller Report';
	require($BF. '_lib.php');
	$active = 'report';
	$subactive = 'reseller';

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast"; }
	
	if(!isset($_REQUEST['id']) || $_REQUEST['id'] == "") { $_REQUEST['id'] = 1; } 
	if(!isset($_REQUEST['bApple']) || $_REQUEST['bApple'] == "") { $_REQUEST['bApple'] = "%"; } 
	if(!isset($_REQUEST['idVenue'])) { $_REQUEST['idVenue'] = ""; } 
	unset($_SESSION['chrVenue']);
	
	// Lets Grab all the Attendee's Information
	$q = "SELECT DISTINCT Attendees.ID, Attendees.*, Signups.bCheckin, EventTitles.chrName
			FROM Attendees 
			JOIN Signups ON Signups.idUser=Attendees.ID
			JOIN Events ON Signups.idEvent=Events.ID
			JOIN EventTitles ON Events.idEventTitle=EventTitles.ID
			WHERE !Attendees.bDeleted AND Events.idEventSeries=".$_REQUEST['id']." AND Attendees.bApple LIKE '".$_REQUEST['bApple']."' AND Events.idVenue='".$_REQUEST['idVenue']."' 
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	
	$_SESSION['excel'] = $q;
	
	$attendees = database_query($q, "Getting all Attendees");
	
	include($BF. 'includes/meta_admin.php');	
	include($BF. 'includes/top_admin.php');
	
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:150px; text-align:right; padding-right:10px;">Reseller Lead Report for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;">
											<select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="reseller_leads.php?idVenue=<?=$_REQUEST['idVenue']?>&bApple=<?=$_REQUEST['bApple']?>&id="+this.value' style="width:150px;">
												<option value=''>-Select Event Series-</option>
											
<?
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");
											while ($row = mysqli_fetch_assoc($eventseries)) {
?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
<?
											}
?>

								</select></td>
		<td class="title" style="width:50px; text-align:right; padding-right:10px;">Venue </td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;">
											<select class='FormField' id="idVenue" name='idVenue' onchange='location.href="reseller_leads.php?id=<?=$_REQUEST['id']?>&bApple=<?=$_REQUEST['bApple']?>&idVenue="+this.value' style="width:150px;">
												<option value=''>Select a Venue</option>

<?
	$venues = database_query("SELECT DISTINCT Venues.ID, chrVenue, chrCity, chrState FROM Venues JOIN Events ON Events.idVenue=Venues.ID WHERE !Venues.bDeleted AND !Events.bDeleted AND Events.idEventSeries=".$_REQUEST['id']." ORDER BY chrVenue","getting Venues info");
	
											while ($row = mysqli_fetch_assoc($venues)) {
											
												if ($row['ID'] == $_REQUEST['idVenue']) { $_SESSION['chrVenue'] = $row['chrVenue']; }
?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['idVenue'] ? ' selected="selected"' : "" )?>><?=$row['chrVenue']?> (<?=$row['chrCity']?>, <?=$row['chrState']?>)</option>
<?	
											}
?>
											
											</select></td>
		<td class="title" style="width:100px; text-align:right; padding-right:10px;">Stay In Touch </td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;">
											<select class='FormField' id="bApple" name='bApple' onchange='location.href="reseller_leads.php?id=<?=$_REQUEST['id']?>&idVenue=<?=$_REQUEST['idVenue']?>&bApple="+this.value'>
												<option value='%'<?=($_REQUEST['bApple'] == "%" ? ' selected="selected"' : "" )?>>Either</option>
												<option value='1'<?=($_REQUEST['bApple'] == "1" ? ' selected="selected"' : "" )?>>Yes</option>
												<option value='0'<?=($_REQUEST['bApple'] == "0" ? ' selected="selected"' : "" )?>>No</option>
											
											</select></td>
		<td class="title_right"><input type="button" id="excel" name="excel" <?=($_REQUEST['idVenue'] == "" ? "disabled='disabled'" : "" )?> onclick="window.open('<?=$BF?>console/_report_reseller.php?id=<?=$_REQUEST['id']?>')" value="Export to Excel" /></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Select Series from top to show report data. More fields are in the Excel output. Click on Attendee to View or Edit their Information.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<div><strong>Current Events or Series</strong></div>
				<? sortList('First Name', 'chrFirst', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('Last Name', 'chrLast', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('Company', 'chrCompany', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('E-mail', 'chrEmail', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('Telephone', 'chrPhone', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('Stay in touch?', 'bApple', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
				<? sortList('Checked In', 'bCheckin', '', 'id='.$_REQUEST['id'].'&idVenue='.$_REQUEST['idVenue'].'&bApple='.$_REQUEST['bApple']); ?>
			</tr>
	<? $count=0;
		
	while ($row = mysqli_fetch_assoc($attendees)) {
	$link = "location.href='".$BF."console/editattendee.php?id=".$row['ID']."&idEventSeries=".$_REQUEST['id']."'";
	
	
	 ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrFirst']?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrLast']?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrCompany']?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrEmail']?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrPhone']?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=($row['bApple'] == 1 ? "Yes" : "No")?></td>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=($row['bCheckin'] == 1 ? "Yes" : "No")?></td>
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='7'>No Events to display</td>
				</tr>
	<?	} ?>
		</table>
		<div><strong>Total Attendees:</strong> <?=number_format($count)?></div>
	</div>
</form>

<?
	include($BF. 'includes/bottom_admin.php');
?>
