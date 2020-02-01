<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Event Report';      // Title to display at the top of the browser window.
	$active = "report";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventtitle";		 // This is needed to highlight the show section
	require($BF. '_lib.php'); //Grab the Lib File
	
			
	if (!isset($_REQUEST['id'])) { 
		$tmp = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","get first series");			
		$_REQUEST['id'] = $tmp['ID'];
		$_SESSION['chrTitle'] = $tmp['chrTitle'];
	}
			
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	include($BF. 'includes/meta_admin.php');
	

	$q = "SELECT EventTitles.ID, chrName,
				(SELECT COUNT(Signups.ID) 
				FROM Signups
				JOIN Events AS EventsSub ON EventsSub.ID=Signups.idEvent
				WHERE EventsSub.idEventTitle = EventTitles.ID AND Signups.idStatus=1 AND Events.idEventSeries='".$_REQUEST['id']."'
				) AS intSignups,
				(SELECT COUNT(Signups.ID) 
				FROM Signups
				JOIN Events AS EventsSub ON EventsSub.ID=Signups.idEvent
				WHERE EventsSub.idEventTitle = EventTitles.ID AND Signups.idStatus=2 AND Events.idEventSeries='".$_REQUEST['id']."'
				) AS intWaitlisters,
				(SELECT COUNT(Signups.ID) 
				FROM Signups
				JOIN Events AS EventsSub ON EventsSub.ID=Signups.idEvent
				WHERE EventsSub.idEventTitle = EventTitles.ID AND Signups.idStatus=3 AND Events.idEventSeries='".$_REQUEST['id']."'
				) AS intCancel,				
				(SELECT COUNT(Signups.ID) 
				FROM Signups
				JOIN Events AS EventsSub ON EventsSub.ID=Signups.idEvent
				WHERE Signups.idStatus=1 AND Events.idEventSeries='".$_REQUEST['id']."'
				) AS intTotalSignups, 
				(SELECT round(intSignups/intTotalSignups*100)) AS intPercent
			FROM EventTitles
			JOIN Events ON Events.idEventTitle=EventTitles.ID
			WHERE Events.idEventSeries='".$_REQUEST['id']."' AND !EventTitles.bDeleted
			GROUP BY EventTitles.ID
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
					  
	$_SESSION['excel'] = $q;
	
		
	$Events = database_query($q, "Generating Report");


	
	
	//Load Drop Down Menus
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");


	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:150px;">Event Title Report for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;"><select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="report_eventtitle.php?id="+this.value'>
											<? while ($row = mysqli_fetch_assoc($eventseries)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
											<?	} ?>
								</select></td>
		<td class="title_right"><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>console/_report_eventtitle.php')" value="Export to Excel" /></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Select Series from top to show report data.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<? sortList('Event Title', 'chrName', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Sign-ups', 'intSignups', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Percent of Sign-ups', 'intPercent', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Wait-listers', 'intWaitlisters', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Cancellations', 'intCancel', '', 'id='.$_REQUEST['id']); ?>
				
												
			</tr>
	<? $count=0;
	$totalsignups=0;
	$totalcancel=0;
	$totalwaitlisters=0;
	
	while ($row = mysqli_fetch_assoc($Events)) {
	$link = "";
	$totalsignups = $totalsignups + $row['intSignups'];
	$totalwaitlisters = $totalwaitlisters + $row['intWaitlisters'];
	$totalcancel = $totalcancel + $row['intCancel'];
	
	 ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrName']?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intSignups'])?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=$row['intPercent'].'%'?></td>		
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intWaitlisters'])?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intCancel'])?></td>							
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Event Titles to display</td>
				</tr>
	<?	} ?>
		</table>
		<div><strong>Total Signups:</strong> <?=number_format($totalsignups)?> <strong>Total Waitlisters:</strong> <?=number_format($totalwaitlisters)?> <strong>Total Cancellations:</strong> <?=number_format($totalcancel)?></div>
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
