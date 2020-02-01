<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Daily Statistics Report';      // Title to display at the top of the browser window.
	$active = "report";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "day";		 // This is needed to highlight the show section
	require($BF. '_lib.php'); //Grab the Lib File

	if (!isset($_REQUEST['id'])) { 
		$tmp = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","get first series");			
		$_REQUEST['id'] = $tmp['ID'];
		$_SESSION['chrTitle'] = $tmp['chrTitle'];
	}
			
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "ID"; }

	// Do Query for Report
	$q = "SELECT DATE(Signups.dtStamp) AS ID, idStatus, DATE(Signups.dtCancel) AS dCancel
			FROM Signups
			JOIN Events ON Signups.idEvent=Events.ID
			WHERE Events.idEventSeries='".$_REQUEST['id']."' AND !Events.bDeleted
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];

	$Results = database_query($q, "Grabing all Data");
	$dates = array();
	while ($row = mysqli_fetch_assoc($Results)) {
		if (!isset($dates[$row['ID']])) {
			$dates[$row['ID']] = array();
			$dates[$row['ID']][1] = 0;
			$dates[$row['ID']][2] = 0;
			$dates[$row['ID']][3] = 0;
		}
	$dates[$row['ID']][$row['idStatus']] += 1;
	}

	$_SESSION['excel'] = $dates;

	
	include($BF. 'includes/meta_admin.php');
	  
	//Load Drop Down Menus
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");


	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:120px;">By Venue Report for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;"><select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="report_day.php?id="+this.value'>
											<? while ($row = mysqli_fetch_assoc($eventseries)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
											<?	} ?>
								</select></td>
		<td class="title_right"><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>console/_report_day.php')" value="Export to Excel" /></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Select Series from top to show report data.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<? sortList('Date', 'ID', '', 'id='.$_REQUEST['id']); ?>
				<th>Signups</th>	
				<th>Waitlisters</th>
				<th>Cancellations</th>
			</tr>
	<? $count=0;
	$totalsignups=0;
	$totalwaitlisters=0;
	$totalcancel=0;

	foreach ($dates as $k => $v) {
	$link = "";
	$totalsignups += $dates[$k][1];
	$totalwaitlisters += $dates[$k][2];
	$totalcancel += $dates[$k][3];
	
	 ?>
				<tr id='tr<?=$k?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$k?>");' onmouseout='UnRowHighlight("tr<?=$k?>");'>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=date('M j, Y (l)',strtotime($k))?></td>								
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($dates[$k][1])?></td>					
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($dates[$k][2])?></td>								
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=number_format($dates[$k][3])?></td>
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Dates to display</td>
				</tr>
	<?	} ?>
		</table>
		<div><strong>Total Signups:</strong> <?=number_format($totalsignups)?> <strong>Total Waitlisters:</strong> <?=number_format($totalwaitlisters)?> <strong>Total Cancels:</strong> <?=number_format($totalcancel)?> </div>
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
