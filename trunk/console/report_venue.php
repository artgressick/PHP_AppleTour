<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Venue Report';      // Title to display at the top of the browser window.
	$active = "report";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "venue";		 // This is needed to highlight the show section
	require($BF. '_lib.php'); //Grab the Lib File
			
	if (!isset($_REQUEST['id'])) { 
		$tmp = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","get first series");			
		$_REQUEST['id'] = $tmp['ID'];
		$_SESSION['chrTitle'] = $tmp['chrTitle'];
	}
			
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dDate, tBegin"; }
	
			// Do Query for Report
		$q = "SELECT Venues.ID, Venues.chrVenue,
			(SELECT COUNT(Signups.ID) FROM Signups JOIN Events ON Events.ID=Signups.idEvent WHERE Events.idVenue=Venues.ID AND idStatus=1) AS intSignups, 
			(SELECT COUNT(Signups.ID) FROM Signups JOIN Events ON Events.ID=Signups.idEvent WHERE Events.idVenue=Venues.ID AND idStatus=2) AS intWaitlisters,
			(SELECT COUNT(Signups.ID) FROM Signups JOIN Events ON Events.ID=Signups.idEvent WHERE Events.idVenue=Venues.ID AND idStatus=3) AS intCancel
		  FROM Venues
		  JOIN Events ON Venues.ID=Events.idVenue
		  WHERE Events.idEventSeries='".$_REQUEST['id']."' AND !Events.bDeleted AND !Venues.bDeleted
          GROUP BY Venues.ID
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
	$_SESSION['excel'] = $q;
	
		
	$Events = database_query($q, "Grabing all Events");
		
	include($BF. 'includes/meta_admin.php');
	
	//Load Drop Down Menus
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");


	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:120px;">By Venue Report for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;"><select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="report_venue.php?id="+this.value'>
											<? while ($row = mysqli_fetch_assoc($eventseries)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
											<?	} ?>
								</select></td>
		<td class="title_right"><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>console/_report_venue.php')" value="Export to Excel" />&nbsp;&nbsp;<input type="button" id="excel1" name="excel1" onclick="window.open('<?=$BF?>console/_excel_attendee_all.php?id=<?=$_REQUEST['id']?>')" value="Export All Attendees to Excel" /></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Select Series from top to show report data.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<? sortList('Venue Name', 'chrVenue', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Signups', 'intSignups', '', 'id='.$_REQUEST['id']); ?>	
				<? sortList('Waitlisters', 'intWaitlisters', '', 'id='.$_REQUEST['id']); ?>	
				<? sortList('Cancellations', 'intCancel', '', 'id='.$_REQUEST['id']); ?>
				<th>Attendee Export</th>
			</tr>
	<? $count=0;
	$totalsignups=0;
	$totalwaitlisters=0;
	$totalcancel=0;
	
	while ($row = mysqli_fetch_assoc($Events)) {
	$link = "";
	$totalsignups = $totalsignups + $row['intSignups'];
	$totalwaitlisters = $totalwaitlisters + $row['intWaitlisters'];
	$totalcancel = $totalcancel + $row['intCancel'];
	
	 ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrVenue']?></td>								
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($row['intSignups'])?></td>					
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($row['intWaitlisters'])?></td>								
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($row['intCancel'])?></td>
					<td style='cursor: pointer;'><a href="_excel_attendee_venue.php?id=<?=$row['ID']?>&series=<?=$_REQUEST['id']?>">Export Attendees</a></td>
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Venues to display</td>
				</tr>
	<?	} ?>
		</table>
		<div><strong>Total Signups:</strong> <?=number_format($totalsignups)?> <strong>Total Waitlisters:</strong> <?=number_format($totalwaitlisters)?> <strong>Total Cancels:</strong> <?=number_format($totalcancel)?> </div>
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>