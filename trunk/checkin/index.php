<?php
	$BF = "../";
	$auth_not_required = 1;
	require($BF. '_lib.php');
	// Get Branding Information
	$bodyParams = "ESChanged(); ETChanged()";
	if(!isset($_SESSION['chrLogo']) || $_SESSION['chrLogo'] == '') {
		if (isset($_POST['idEventSeries']) && $_POST['idEventSeries'] != "" ) {
			$temp = fetch_database_query("SELECT chrImageName FROM EventSeries WHERE ID='". $_POST['idEventSeries']."'", "Getting Branding Logo");
			$_SESSION['chrLogo'] = $temp['chrImageName'];
		} else {
			$_SESSION['chrLogo'] = '';
		}
	}

	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }	
	include($BF. 'components/list/sortList.php'); 

	$q="SELECT ID, chrTitle
		FROM EventSeries
		WHERE !bDeleted";
	$ES = database_query($q, 'Fetching all Events');
	
	$q = "SELECT DISTINCT EventTitles.ID, EventTitles.chrName, Events.idEventSeries
			FROM EventTitles
			JOIN Events ON EventTitles.ID = Events.idEventTitle
			WHERE !Events.bDeleted AND !EventTitles.bDeleted";
			$results = database_query($q, 'Fetching Titles');
			
	$q= "SELECT DISTINCT Venues.ID, Venues.chrVenue, Events.idEventTitle
		FROM Venues
		JOIN Events ON Events.idVenue = Venues.ID
		WHERE !Events.bDeleted AND !Venues.bDeleted";
		$res = database_query($q, 'Fetching Venues');

		(isset($_POST['idEventSeries'])?$_SESSION['idEventSeries']= $_POST['idEventSeries']:'');
		(isset($_POST['idEventTitles'])?$_SESSION['idEventTitles']= $_POST['idEventTitles']:'');
		(isset($_POST['idVenues'])?$_SESSION['idVenues']= $_POST['idVenues']:'');
		if (count($_POST) && isset($_POST['hiddenval']) && $_POST['hiddenval']==1)
		{
			
			$query = "SELECT ID
					FROM Events
					WHERE idEventSeries = ".$_POST['idEventSeries']." AND idEventTitle = ".$_POST['idEventTitles']." AND idVenue = ".$_POST['idVenues'];
					$event = fetch_database_query($query, 'Getting Event Number');
			foreach($_POST['checkin'] as $attendee)
				{
					$query = "UPDATE Signups SET 
					bCheckin=1, dtCheckin = NOW()
					WHERE idUser= ".$attendee." AND idEvent = ".$event['ID'];
					database_query($query, 'Checking In');
					
				}
		}
		
?>
<script language="javascript" src = "<?=$BF?>includes/forms.js" type = "text/javascript"></script>
<script language = "javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventSeries', "You must select an Event Series.");
		total += ErrorCheck('idEventTitles', "You must select an Event Title.");
		total += ErrorCheck('idVenues', "You must select a Venue.");


		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>

	<script languages= "JavaScript">
		var ETArray = new Array();
		<? $count = 0;
		
		while ($rows = mysqli_fetch_assoc($results))
		{
		?>
		ETArray[<?=$count++?>] = new Array(<?=$rows['ID']?>, "<?=$rows['chrName']?>", <?=$rows['idEventSeries']?>);
		<?
		}
		?>
			function ESChanged() {
			var event = document.getElementById('idEventSeries').value;
			theform = document.getElementById('idForm');
			totallist=<?=$count?>;
			var idthing = '<?=$_SESSION['idEventTitles']?>'
			
			if(event != '') {
				theform.idVenues.options.length=0;
				theform.idEventTitles.options.length=0;
								theform.idEventTitles.options[theform.idEventTitles.options.length] = new Option('Please Choose An Event', '', true, true);
				theform.idVenues.options[theform.idVenues.options.length] = new Option('Please Select An Event' , '');
				
				for (i=0; i< totallist; ++i) {
					if(event == ETArray[i][2]) {
						theform.idEventTitles.options[theform.idEventTitles.options.length] = new Option(ETArray[i][1], ETArray[i][0],'', (idthing==ETArray[i][0] ? "selected" : ""));
					}
				}
			}
		}
	</script>
	<script languages= "JavaScript">
		var VArray = new Array();
		<? $count = 0;
		
		while ($rows = mysqli_fetch_assoc($res))
		{
		?>
		VArray[<?=$count++?>] = new Array(<?=$rows['ID']?>, "<?=$rows['chrVenue']?>", <?=$rows['idEventTitle']?>);
		<?
		}
	?>
			function ETChanged() {
			var event = document.getElementById('idEventTitles').value;
			theform = document.getElementById('idForm');
			totallist=<?=$count?>;
			var idthing = '<?=$_SESSION['idVenues']?>';
			
			if(event != '') {
				theform.idVenues.options.length=0;
				theform.idVenues.options[theform.idVenues.options.length] = new Option('Please Select a Venue', '', true, true);
		
				for (i=0; i< totallist; ++i) {
					if(event == VArray[i][2]) {
						theform.idVenues.options[theform.idVenues.options.length] = new Option(VArray[i][1], VArray[i][0],'', (idthing==VArray[i][0] ? "selected" : ""));
					}
				}
			}
		}
	</script>	
<?	include($BF. "includes/top.php"); ?>
<form name = "idForm" id = "idForm" method='POST' action = ''>
<div class="main">
	<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td height="50" colspan="3"><?=(isset($_SESSION['chrLogo']) && $_SESSION['chrLogo'] != "" ? "<img src='".$BF."images/".$_SESSION['chrLogo']."' />" : "" )?></td>
		</tr>
		<tr>
			<td width="7" height="7"><img src="<?=$BF?>images/corner_top_left.gif" width="7" height="7" /></td>
			<td width="786" height="7" background="<?=$BF?>images/line_top.gif"><img src="<?=$BF?>images/line_top.gif" width="7" height="7" /></td>
			<td width="7" height="7"><img src="<?=$BF?>images/corner_top_right.gif" width="7" height="7" /></td>
		</tr>
		<tr>
			<td width="7" background="<?=$BF?>images/line_left.gif"><img src="<?=$BF?>images/line_left.gif" width="7" height="100%" /></td>
			<td width="786" bgcolor="#3F3F3F">
				<div class="maintitle" style="padding-top:10px; text-align:center; font-size: 14px;">Please Choose Between these Events</div>
				<div style="padding-top:10px; padding-bottom:10px;">
					<center>
					
					<table style = "border: 0px;"><tr><td colspan = "3"> <div id = "errors"></div></td></tr><tr><td>
					<select name = "idEventSeries" id = "idEventSeries" onchange = "ESChanged();" style = "width: 200px;">
						<option value = ''>Select a Series</option>
						<?  while($EST = mysqli_fetch_assoc($ES))
						{ ?>
							<option value = "<?=$EST['ID']?>" <?=($EST['ID'] == $_SESSION['idEventSeries']?'selected':'')?>><?=$EST['chrTitle']?></option>
					 <? } ?>
					 </select>
					 </td><td>
					 <select name = "idEventTitles" id = "idEventTitles" onchange = "ETChanged();" style = "width: 200px;">
					 	<option value = ''>Please Select a Series</option>
					 </select>
					 </td><td>
					 <select name = "idVenues" id = "idVenues" style = "width: 200px;">
					 		<option value = ''>Please Select A Series</option>
					 </select>
					 </td>
					 	<td>
					 		<select name = "chrCheckedIn" style = "width: 200px;">
					 			<option value = "All">Show All</option>
					 			<option value = "CheckedIn">Checked In</option>
					 			<option value = "NotCheckedIn" selected>Not Checked In</option>
					 		</select>
					 	</td></tr><tr>
					 	<td colspan = "2"><input type="button" value="Sort By Status then Last/First Name" onclick='location.href="index.php?R=1&chrSearch=<?=$_REQUEST['chrSearch']?>&chrCheckedIn=<?=(isset($_POST['chrCheckedin']) ? $_POST['chrCheckedin'] : "")?>&sortCol=chrStatus,chrLast,chrFirst&ordCol=;"' /></td>
					 	<td colspan = "2" style = "text-align: right;">
					 	<span style = "font-size: 12px; color: #FFFFFF;">Search For User </span><input type="search" name="chrSearch" placeholder="Search Users" value='<?=$_REQUEST['chrSearch']?>' />
					<input type = "hidden"  name = 'id' id = 'id' value = "search" />
					<input type='button' name='Go' value='Go' onclick = "error_check()" />
						</td>
					</tr>		
				</table>
			</td>
			<td width="7" background="<?=$BF?>images/line_right.gif"><img src="<?=$BF?>images/line_right.gif" width="7" height="100%" /></td>
		</tr>
<?
						if(isset($_POST['idVenues']) || isset($_REQUEST['sortCol'])) {
?>
		<tr>
			<td width="7" background="<?=$BF?>images/line_left.gif"><img src="<?=$BF?>images/line_left.gif" width="7" height="100%" /></td><td colspan = "1">
				<div style='background:#3F3F3F; color:white; padding:5px;'>Total Attendees Listed: <span id='totalAttendees'></span></div>
				<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
					<tr>
						<th>&nbsp;</th>
<?
						if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast"; }
						(!isset($_POST['chrCheckedin'])?$_POST['chrCheckedin']=$_REQUEST['chrCheckedIn']:'');
						
						sortList('First Name', 'chrFirst','','R=2&chrSearch='.$_REQUEST['chrSearch']."&chrCheckedIn=".$_POST['chrCheckedin']);
						sortList('Last Name', 'chrLast','', 'R=2&chrSearch='.$_REQUEST['chrSearch']."&chrCheckedIn=".$_POST['chrCheckedin']);
						if($_SESSION['idEventSeries'] != 3) {
						sortList('Company', 'chrCompany','', 'R=2&chrSearch='.$_REQUEST['chrSearch']."&chrCheckedIn=".$_POST['chrCheckedin']);
						}
						sortList('E-Mail', 'chrEmail','', 'R=2&chrSearch='.$_REQUEST['chrSearch']."&chrCheckedIn=".$_POST['chrCheckedin']);
?>
						<th>Checked In</th>
<?
						sortList('Status', 'chrStatus','', 'R=2&chrSearch='.$_REQUEST['chrSearch']."&chrCheckedIn=".$_POST['chrCheckedin']);
?>
					</tr>
						
	<?
		if (isset($_POST['chrCheckedin'])) {
			$_REQUEST['chrCheckedin'] = $_POST['chrCheckedin'];
		}
			
		
			$query = "SELECT Attendees.chrFirst, Attendees.chrLast, Attendees.chrCompany, Attendees.chrEmail, Status.chrName AS chrStatus, Attendees.ID, Signups.bCheckin
						FROM Attendees
						JOIN Signups ON Signups.idUser = Attendees.ID
						JOIN Status ON Signups.idStatus = Status.ID
						JOIN Events ON Events.ID = Signups.idEvent
						JOIN Venues ON Events.idVenue = Venues.ID
						WHERE !Attendees.bDeleted AND !Venues.bDeleted AND Events.idEventSeries = '".$_SESSION['idEventSeries']."' AND Events.idEventTitle = '".$_SESSION['idEventTitles']."' AND Events.idVenue = '".$_SESSION['idVenues']."'";

						if ($_REQUEST['chrCheckedIn'] == "CheckedIn")
							{
								$query .= " AND Signups.bCheckin ";
							}
						else if ($_REQUEST['chrCheckedIn'] == "NotCheckedIn")
						{
							$query .= " AND !Signups.bCheckin ";
						}
						if($_REQUEST['chrSearch']!='')
						{
							$query .= " AND (chrLast LIKE '%".$_REQUEST['chrSearch']."%' OR chrFirst LIKE '%".$_REQUEST['chrSearch']."%' OR chrEmail LIKE '%".$_REQUEST['chrSearch']."%')";
						}
						$query .= " ORDER BY ". $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
						
						$result = database_query($query, 'Getting all Attendees');

?><script languages="JavaScript">document.getElementById('totalAttendees').innerHTML='<?=mysqli_num_rows($result)?>';</script><?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'checkin.php?id='. $row['ID'];
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;'><input type = "checkbox" name = "checkin[]" id = "checkin[]" value = "<?=$row['ID']?>" style = "checkbox"></td>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>";'><?=$row['chrFirst']?></td>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>";'><?=$row['chrLast']?></td>
<?
	if($_SESSION['idEventSeries'] != 3) {
?>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>";'><?=$row['chrCompany']?></td>
<?
	}
?>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>";'><?=$row['chrEmail']?></td>
							<td style='cursor: pointer; white-space:nowrap;' onclick='location.href="<?=$link?>";'><?=($row['bCheckin']?'Checked In':"Not Checked In")?></td>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>";'><?=$row['chrStatus']?></td>
						</tr> 
			<?	} 
			if($count == 0) { ?>
						<tr>
							<td align="center" colspan='8' style='height:20px; color:white;'>No Attendees to display</td>
						</tr>
			<?	} ?>
					</table> 
				</td><td width="7" background="<?=$BF?>images/line_right.gif"><img src="<?=$BF?>images/line_right.gif" width="7" height="100%" /></td></tr><tr>
				<td width="7" background="<?=$BF?>images/line_left.gif"><img src="<?=$BF?>images/line_left.gif" width="7" height="100%" /></td><td width="786" bgcolor="#3F3F3F">
				<br />
				<input type = "button" class= "button" name = "check" id = "check" value = "Check In" onclick = "document.getElementById('hiddenval').value = 1; error_check()" />
				<input type = "hidden" id = "hiddenval" name = "hiddenval" value = 0 />
				<br /><span class="maintitle" style="padding-top:2px; font-size: 10px;">This may take a few moments</span></td>
				<td width="7" background="<?=$BF?>images/line_right.gif"><img src="<?=$BF?>images/line_right.gif" width="7" height="100%" /></td></tr><tr>
				
				<? } ?>
				<td width="7" height="7"><img src="<?=$BF?>images/corner_bottom_left.gif" width="7" height="7" /></td>
			<td width="786" height="7" background="<?=$BF?>images/line_bottom.gif"><img src="<?=$BF?>images/line_bottom.gif" width="7" height="7" /></td>
			<td width="7" height="7"><img src="<?=$BF?>images/corner_bottom_right.gif" width="7" height="7" /></td>
					</tr><tr>

		</tr></table></div>
</form>				
	<?  include($BF."includes/bottom.php");  ?>