<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Attendee Report';      // Title to display at the top of the browser window.
	$active = "report";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "";		 // This is needed to highlight the show section
	require($BF. '_lib.php'); //Grab the Lib File
	
	if (!isset($_REQUEST['idStatus'])) { $_REQUEST['idStatus'] = '%'; }
	
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }
	
	include($BF. 'includes/meta_admin.php');

	
	// Lets pull all the dates for this Event
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
	FROM Events 
	JOIN EventDates ON EventDates.idEvent=Events.ID 
	WHERE Events.ID='".$_REQUEST['id']."' 
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
	
	

	$q = "SELECT Attendees.ID, chrFirst,chrLast,chrEmail, Status.chrName
		  FROM Attendees
		  JOIN Signups ON Signups.idUser=Attendees.ID
		  JOIN Status ON Signups.idStatus=Status.ID
		  WHERE Signups.idEvent=".$_REQUEST['id']." AND Signups.idStatus LIKE '".$_REQUEST['idStatus']."'
		  ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
			
	$Attendees = database_query($q, "Grabing all Attendees");

	//Get Event Title
	$q = "SELECT Events.ID, Events.dDate, EventTitles.chrName, Venues.chrVenue, Events.idEventSeries
		  FROM Events
		  JOIN EventTitles ON EventTitles.ID=Events.idEventTitle
  		  JOIN Venues ON Venues.ID=Events.idVenue
		  WHERE Events.ID=".$_REQUEST['id'];

	$event = fetch_database_query($q, "getting Event Information");
	
	
	//Load Drop Down Menus
	$status = database_query("SELECT ID, chrName FROM Status ORDER BY ID","getting Status info");


	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:200px;">Attendee Listing where Status is </td>
		<td class="title_right" style="vertical-align:middle; padding-top:3px; text-align:left;"><select class='FormField' id="idStatus" name='idStatus' onchange='location.href="eventsummary.php?id=<?=$_REQUEST['id']?>&idStatus="+this.value'>
												<option value='%'<?=($_REQUEST['idStatus'] == '%' ? ' selected="selected"' : "" )?>>All</option>
											<? while ($row = mysqli_fetch_assoc($status)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['idStatus'] ? ' selected="selected"' : "" )?>><?=$row['chrName']?></option>
											<?	} ?>
								</select></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>List of Attendees for Event listed by Status set above.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<div><strong>Event:</strong> <?=$event['chrName']?><br />
				<strong>Date:</strong> <?=$eventDates['chrDates'.$_REQUEST['id']]?><br />
				<strong>Location:</strong> <?=$event['chrVenue']?></div>
				<? sortList('First Name', 'chrFirst', '', 'id='.$_REQUEST['id'].'&status='.$_REQUEST['idStatus']); ?>
				<? sortList('Last Name', 'chrLast', '', 'id='.$_REQUEST['id'].'&status='.$_REQUEST['idStatus']); ?>
				<? sortList('E-mail', 'chrEmail', '', 'id='.$_REQUEST['id'].'&status='.$_REQUEST['idStatus']); ?>
				<? sortList('Status', 'chrName', '', 'id='.$_REQUEST['id'].'&status='.$_REQUEST['idStatus']); ?>													
			</tr>
	<? $count=0;	
	while ($row = mysqli_fetch_assoc($Attendees)) {
	$link='location.href="editattendee.php?id='.$row['ID'].'&idEventSeries='.$event['idEventSeries'].'"';
	 ?>
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
					<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrFirst']?></td>
					<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLast']?></td>
					<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
					<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>															
				</tr>
	<?	} 
	if($count == 0) { ?>
				<tr>
					<td align="center" colspan='6'>No Attendees to display</td>
				</tr>
	<?	} ?>
		</table>					
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
