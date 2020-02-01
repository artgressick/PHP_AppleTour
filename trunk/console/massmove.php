<?	
	$BF = '../';
	$title = 'Mass Move';
	require($BF. '_lib.php');
	if(count($_POST)) {
		//header("Location: processmassmove.php?idFrom=".$_POST['idFrom']."&idFromStatus=".$_POST['idFromStatus']."&idTo=".$_POST['idTo']."&idToStatus=".$_POST['idToStatus']);
		header("Location: processmassmove.php?idFrom=".$_POST['idFrom']."&idTo=".$_POST['idTo']);
		die();
	}
	
	include($BF. 'includes/meta_admin.php');	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	
	$q = "SELECT E.ID, E.bShow, ES.chrTitle, ET.chrName, V.chrVenue,
			(SELECT COUNT(ID) FROM Signups AS S WHERE S.idStatus=1 AND S.idEvent=E.ID) AS intConfirmed,
			(SELECT COUNT(ID) FROM Signups AS S WHERE S.idStatus=2 AND S.idEvent=E.ID) AS intWaitlisted
			FROM Events AS E
			JOIN EventSeries AS ES ON E.idEventSeries=ES.ID
			JOIN EventTitles AS ET ON E.idEventTitle=ET.ID
			JOIN Venues AS V ON E.idVenue=V.ID
			WHERE !E.bDeleted AND !ES.bDeleted AND !V.bDeleted AND !ET.bDeleted
			ORDER BY ES.chrTitle, ET.chrName, V.chrVenue";	
	$results = database_query($q,"Getting all events");

	$active = 'admin';
	$subactive = 'massmove';
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;
		var fromid='';
		var toid='';
<?
	while($row = mysqli_fetch_assoc($results)) {
?>
		if(document.getElementById('idFrom<?=$row['ID']?>').checked == true) { fromid = '<?=$row['ID']?>'; }
		if(document.getElementById('idTo<?=$row['ID']?>').checked == true) { toid = '<?=$row['ID']?>'; }
<?
	}
?>
		if(fromid == '') { total++; CustomError("You must select a From Event.", 'idFrom'); }
		total += ErrorCheck('idFromStatus', "You must select a From Status.");
		if(toid == '') { total++; CustomError("You must select a To Event.", 'idTo'); }
		total += ErrorCheck('idToStatus', "You must select a To Status.");
		if(total == 0 && fromid == toid && document.getElementById('idFromStatus').value == document.getElementById('idToStatus').value) { total++; CustomError("The To and From Events and Status can not be the same.", 'id'); } 

		return (total == 0 ? true : false);
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Mass Move</td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>Select an Event to move Attendees From and To below. Attendees will be moved from Wait-list from the From Event to Confirmed to the To Event.</div>
	<div class='innerbody'>
		<div id='errors'></div>
		<form id='idForm' name='idForm' method='post' action='' onsubmit="return error_check()">
		<div style='font-size:14px; font-weight:bold; padding:5px 0;'>Choose From Event</div>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<th>From</th>
				<th>Status</th>
				<th>Event Series</th>
				<th>Event Title</th>
				<th>Venue</th>
				<th>Confirmed</th>
				<th>WaitListed</th>
			</tr>
<? 
mysqli_data_seek($results,0);
$count=0;	
while ($row = mysqli_fetch_assoc($results)) { ?>
			<tr id='trf<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("trf<?=$row['ID']?>");' onmouseout='UnRowHighlight("trf<?=$row['ID']?>");'>
				<td><input type='radio' name='idFrom' value='<?=$row['ID']?>' id='idFrom<?=$row['ID']?>' /></td>
				<td><?=($row['bShow']==0?'Hidden':'Shown')?></td>
				<td><?=$row['chrTitle']?></td>
				<td><?=$row['chrName']?> (<?=$row['ID']?>)</td>
				<td><?=$row['chrVenue']?></td>
				<td><?=$row['intConfirmed']?></td>
				<td><?=$row['intWaitlisted']?></td>
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='7' style='height:20px;'>No Events to display</td>
			</tr>
<?	} ?>
		</table>
<? /*
		<div style='padding:10px 0;'>

<? $status = database_query("SELECT * FROM Status ORDER BY ID","Getting Status"); ?>
			<div class='FormName'>Select From Status</div>
			<select class='FormField' id="idFromStatus" name='idFromStatus' style="" >
					<option value=''>Select From Status</option>					
				<? while ($row = mysqli_fetch_assoc($status)) { ?>
					<option value='<?=$row['ID']?>'><?=$row['chrName']?></option>
				<?	} ?>
			</select>
		</div>
*/
?>
		<input type='hidden' id='idFromStatus' name='idFromStatus' value='2' />
		<div style='font-size:14px; font-weight:bold; padding:5px 0;'>Choose To Event</div>
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<th>To</th>
				<th>Status</th>
				<th>Event Series</th>
				<th>Event Title</th>
				<th>Venue</th>
				<th>Confirmed</th>
				<th>WaitListed</th>
			</tr>
<?
mysqli_data_seek($results,0);
$count=0;	
while ($row = mysqli_fetch_assoc($results)) { ?>
			<tr id='trt<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("trt<?=$row['ID']?>");' onmouseout='UnRowHighlight("trt<?=$row['ID']?>");'>
				<td><input type='radio' name='idTo' value='<?=$row['ID']?>' id='idTo<?=$row['ID']?>' /></td>
				<td><?=($row['bShow']==0?'Hidden':'Shown')?></td>
				<td><?=$row['chrTitle']?></td>
				<td><?=$row['chrName']?> (<?=$row['ID']?>)</td>
				<td><?=$row['chrVenue']?></td>
				<td><?=$row['intConfirmed']?></td>
				<td><?=$row['intWaitlisted']?></td>
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='7' style='height:20px;'>No Events to display</td>
			</tr>
<?	} ?>
		</table>
<?
/*

		<div style='padding:10px 0;'>
<? mysqli_data_seek($status,0); ?>
			<div class='FormName'>Select To Status</div>
			<select class='FormField' id="idToStatus" name='idToStatus' style="" >
					<option value=''>Select To Status</option>					
				<? while ($row = mysqli_fetch_assoc($status)) { ?>
					<option value='<?=$row['ID']?>'><?=$row['chrName']?></option>
				<?	} ?>
			</select>
		</div>
*/
?>
		<input type='hidden' id='idToStatus' name='idToStatus' value='1' />
		<input class='FormButtons' type='submit' value='Process Mass Move' onclick="" style='margin-top:10px;' />
	</form>
	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>