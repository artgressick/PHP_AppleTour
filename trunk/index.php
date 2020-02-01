<?php
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');
	// Checking request variables
	if(!isset($_REQUEST['Series']) || !is_numeric($_REQUEST['Series'])) { ErrorPage(); }
	if(isset($_REQUEST['idCheck']) && is_numeric($_REQUEST['idCheck'])) { $idCheck = $_REQUEST['idCheck']; } else { $idCheck = ""; }
	if(isset($_REQUEST['C']) && $_REQUEST['C'] != '' && !is_numeric($_REQUEST['C'])) { $chrLeadCode = $_REQUEST['C']; } else { $chrLeadCode = ''; }   
	$check = fetch_database_query("SELECT ID FROM EventSeries WHERE intLink=".$_REQUEST['Series']." AND !bDeleted","Getting Series ID");
	if($check['ID'] != '') { $idEventSeries = $check['ID']; } else { ErrorPage(); }
	
	if($idEventSeries == "") { ErrorPage(); }
	include($BF. 'components/list/sortList.php'); 
	
	//Grab EventSeries Information
	$temp = fetch_database_query("SELECT ID, chrTitle, chrImageName, chrLandingText, chrGroupBy
									FROM EventSeries
									WHERE !EventSeries.bDeleted AND EventSeries.ID=".$idEventSeries, "Getting EventSeries and Referral Information");
		
	if($temp['ID'] == "") {	
		ErrorPage();
	}
	
	$_SESSION['chrTitle'] = $temp['chrTitle'];

	$q = "SELECT Events.ID, chrVenue, chrName, chrCountry, (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) as dDate, chrCity, chrState, Events.idVenue, Events.idEventTitle
		FROM EventSeries
		JOIN Events ON EventSeries.ID=Events.idEventSeries
		JOIN Venues ON Events.idVenue=Venues.ID
		JOIN EventTitles ON Events.idEventTitle=EventTitles.ID
		WHERE !EventSeries.bDeleted AND !Events.bDeleted AND Events.bShow AND (SELECT dDate FROM EventDates WHERE idEvent=Events.ID ORDER BY dDate,tBegin LIMIT 1) >= DATE_FORMAT(NOW(),'%Y-%m-%d') AND EventSeries.ID=".$idEventSeries."
		GROUP BY ".$temp['chrGroupBy']."
		ORDER BY dDate";
	$locations = database_query($q,"Getting location information");	

	// Lets pull all the dates for this Event Series
	$q = "SELECT EventDates.idEvent, EventDates.dDate, Events.idVenue, Events.idEventTitle
			FROM Events 
			JOIN EventDates ON EventDates.idEvent=Events.ID AND Events.idEventSeries=".$idEventSeries." AND Events.bShow
			ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
			
	$results = database_query($q,"Grabbing All Dates for this Event Series");

	$eventDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	$preDay = "";
	while ($row = mysqli_fetch_assoc($results)) {

		if($prevID != $row[$temp['chrGroupBy']]) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $row[$temp['chrGroupBy']];
			$eventDates['chrDates'.$row[$temp['chrGroupBy']]] = "";
			$prevDate = "";
			$preDay = "";
		}
			if($prevDate != date('F',strtotime($row['dDate']))) {
				if($day != 1) { $eventDates['chrDates'.$row[$temp['chrGroupBy']]] .= ", "; }
				$eventDates['chrDates'.$row[$temp['chrGroupBy']]] .= date('F',strtotime($row['dDate']))." ".date('jS',strtotime($row['dDate']));
				$preDay = date('jS',strtotime($row['dDate']));
			} else {
				if($preDay != date('jS',strtotime($row['dDate']))) {
					if($day != 1) { $eventDates['chrDates'.$row[$temp['chrGroupBy']]] .= ", "; }
					$eventDates['chrDates'.$row[$temp['chrGroupBy']]] .= date('jS',strtotime($row['dDate']));
					$preDay = date('jS',strtotime($row['dDate']));
				}
			}
			
		$prevDate = date('F',strtotime($row['dDate']));
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));

include($BF. 'includes/top.php');
?>
<!-- This is the main body of the page.-->
<div class="main">
	<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td height="50" colspan="3"><img src="<?=$PROJECT_ADDRESS.'images/'.$temp['chrImageName']?>" /></td>
		</tr>
		<tr>
			<td width="7" height="7"><img src="images/corner_top_left.gif" width="7" height="7" /></td>
			<td width="786" height="7" background="images/line_top.gif"><img src="images/line_top.gif" width="7" height="7" /></td>
			<td width="7" height="7"><img src="images/corner_top_right.gif" width="7" height="7" /></td>
		</tr>
		<tr>
			<td width="7" background="images/line_left.gif"><img src="images/line_left.gif" width="7" height="7" /></td>
			<td width="786" bgcolor="#ebebeb">
				<div class="maintitle" style="padding-top:10px; text-align:center;"><?=$temp['chrLandingText']?></div>
				<div style="padding-top:10px; padding-bottom:10px;">
					<center>
						<table id='List' class='List' style='width: 500px;' cellpadding="0" cellspacing="0">
							<tr>
								<th>Location</th>

								<th>City, State (US or CA)</th>
								<th>Date(s)</th>
							</tr>

							<?
							$count=0;
							while ($row = mysqli_fetch_assoc($locations)) {
						
								$link = 'location.href="register.php?L='. base64_encode("idEventSeries=". $idEventSeries ."&ID=". $row[$temp['chrGroupBy']] ."&idCheck=". $idCheck ."&chrRegCode=".$chrLeadCode).'"';
	
								?>
								<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
									<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrVenue']?></td>
<?
	if ($row['chrCountry'] == "US" || $row['chrCountry'] == "CA") {
?>
									<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCity']?>, <?=$row['chrState']?></td>
<?
	} else {
?>
									<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCity']?></td>
<?
	}
?>
									<td style='cursor: pointer;' onclick='<?=$link?>'><?=$eventDates['chrDates'.$row[$temp['chrGroupBy']]]?></td>
								</tr>
								<?
							}  ?>

						</table>
					</center>
				</div>
				</td>
			<td width="7" background="images/line_right.gif"><img src="images/line_right.gif" width="7" height="7" /></td>
		</tr>
		<tr>
			<td width="7" height="7"><img src="images/corner_bottom_left.gif" width="7" height="7" /></td>
			<td width="786" height="7" background="images/line_bottom.gif"><img src="images/line_bottom.gif" width="7" height="7" /></td>
			<td width="7" height="7"><img src="images/corner_bottom_right.gif" width="7" height="7" /></td>
		</tr>
	</table>
</div>
<!-- This is the bottom of the body -->
<?
	include('includes/bottom.php');