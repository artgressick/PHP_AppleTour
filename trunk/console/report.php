<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Event Report';      // Title to display at the top of the browser window.
	$active = "report";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "global";		 // This is needed to highlight the show section
	require($BF. '_lib.php'); //Grab the Lib File
	
			
	if (!isset($_REQUEST['id'])) { 
		$tmp = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","get first series");			
		$_REQUEST['id'] = $tmp['ID'];
		$_SESSION['chrTitle'] = $tmp['chrTitle'];
	}
			
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dDate, tBegin"; }
	
	include($BF. 'includes/meta_admin.php');
	
	// Lets pull all the dates for this Event
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
	FROM Events 
	JOIN EventDates ON EventDates.idEvent=Events.ID 
	WHERE Events.idEventSeries='".$_REQUEST['id']."' 
	ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
	
	$dateresults = database_query($q,"Grabbing All Dates for this Event Series");
	$eventDates = array();
	$fullDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	while ($rowdates = mysqli_fetch_assoc($dateresults)) {

		if($prevID != $rowdates['idEvent']) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $rowdates['idEvent'];
			$eventDates['chrDates'.$rowdates['idEvent']] = "";
			$fullDates[$rowdates['idEvent']] = '';
			$prevDate = "";
		}
			if($prevDate != date('F',strtotime($rowdates['dDate']))) {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('F',strtotime($rowdates['dDate']))." ".date('jS',strtotime($rowdates['dDate']));
			} else {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('jS',strtotime($rowdates['dDate']));
			}
			
		$prevDate = date('F',strtotime($rowdates['dDate']));
		$fullDates[$rowdates['idEvent']] .= date('l, F jS, Y',strtotime($rowdates['dDate'])).' from '.date('g:i a',strtotime($rowdates['tBegin'])).' to '.date('g:i a',strtotime($rowdates['tEnd'])).'<br />';
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));
	
	

	$q = "SELECT Events.ID, (SELECT tBegin FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS tBegin, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS dDate, (SELECT tEnd FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) AS tEnd, EventTitles.chrName, Venues.chrVenue, Venues.intCapacity, Venues.intDropOff, round(Venues.intCapacity + (Venues.intCapacity * (Venues.intDropOff / 100))) AS intTotalCap,
			(SELECT COUNT(ID) FROM Signups WHERE idEvent=Events.ID AND idStatus=1) AS intSignups, 
			(SELECT COUNT(ID) FROM Signups WHERE idEvent=Events.ID AND idStatus=2) AS intWaitlisters
		  FROM Events
		  JOIN EventTitles ON EventTitles.ID=Events.idEventTitle
		  JOIN Venues ON Venues.ID=Events.idVenue
		  WHERE Events.idEventSeries='".$_REQUEST['id']."' AND !Events.bDeleted
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		  
	$_SESSION['excel'] = $q;
	
		
	$Events = database_query($q, "Grabing all Events");


	
	
	//Load Drop Down Menus
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");


	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:100px;">Event Report for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;"><select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="report.php?id="+this.value'>
											<? while ($row = mysqli_fetch_assoc($eventseries)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
											<?	} ?>
								</select></td>
		<td class="title_right"><input type="button" id="excel" name="excel" onclick="window.open('<?=$BF?>console/_report.php?id=<?=$_REQUEST['id']?>')" value="Export to Excel" /></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Select Series from top to show report data. Sign-up Percent Based off of Capacity not Total Capacity. Click on Event Row to view Signup User List.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<div><strong>Current Events or Series</strong></div>
				<? sortList('Name', 'chrName', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Date(s)', 'dDate', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Time Begin', 'tBegin', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Time End', 'tEnd', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Venue', 'chrVenue', '', 'id='.$_REQUEST['id']); ?>
				<? sortList('Capacity', 'intCapacity', '', 'id='.$_REQUEST['id']); ?>	
				<? sortList('Signups', 'intSignups', '', 'id='.$_REQUEST['id']); ?>	
				<? sortList('Waitlisters', 'intWaitlisters', '', 'id='.$_REQUEST['id']); ?>																		
			</tr>
	<? $count=0;
	$totalsignups=0;
	$totalcapacity=0;
	$totalwaitlisters=0;
	
	while ($row = mysqli_fetch_assoc($Events)) {
	$link = "location.href='".$BF."console/eventsummary.php?id=".$row['ID']."'";
	$totalsignups = $totalsignups + $row['intSignups'];
	$totalwaitlisters = $totalwaitlisters + $row['intWaitlisters'];
	$totalcapacity = $totalcapacity + $row['intCapacity'];
	
	 ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrName']?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=$eventDates['chrDates'.$row['ID']]?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=date('g:i a',strtotime($row['tBegin']))?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=date('g:i a',strtotime($row['tEnd']))?></td>								
					<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrVenue']?></td>								
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intCapacity'])?></td>
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intSignups'])?><br />( <?=round($row['intSignups'] / $row['intCapacity'] * 100)?>% )</td>					
					<td style='cursor: pointer; text-align:center;' onclick="<?=$link?>"><?=number_format($row['intWaitlisters'])?></td>								
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Events to display</td>
				</tr>
	<?	} ?>
		</table>
		<div><strong>Total Capacity for All Venus:</strong> <?=number_format($totalcapacity)?> <strong>Total Signups:</strong> <?=number_format($totalsignups)?> <strong>Total Waitlisters:</strong> <?=number_format($totalwaitlisters)?></div>
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
