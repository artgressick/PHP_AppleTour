<?php
	$BF = "../";
	require_once($BF. '_lib.php');
	
	$title = "Modify Event Information";	
	include($BF. 'includes/meta_admin.php');	

	$q = "SELECT Events.ID, idEventTitle, chrName, Events.idVenue, chrVenue, chrCity, chrState, Events.bShow
		  FROM Events
		  JOIN EventTitles ON idEventTitle=EventTitles.ID
		  LEFT JOIN Venues ON Events.idVenue=Venues.ID
		  WHERE Events.ID=".$_REQUEST['idEvent'];

	$eventinfo = fetch_database_query($q, 'get event info');
	
	if(isset($_POST['idEventTitle'])) { 

		$q = "UPDATE Events SET 
			 idEventTitle='".	$_POST['idEventTitle'] ."',
			 idVenue='".	 	$_POST['idVenue'] ."',	
			 bShow='".			$_POST['bShow'] ."'					 
			 WHERE ID=".$eventinfo['ID'];
		
		database_query($q,"Update Event");

		database_query("DELETE FROM EventDates WHERE idEvent=".$eventinfo['ID'],"Remove Dates for Event");

		$class_dates = array();		
		$i = 1;
		$j = 0;
		while ($i <= $TOTAL_DATE_FIELDS) {		
			if ($_POST['dDate'.$i] != "") {
		 		$class_dates[$j] = array();
		 		$class_dates[$j]['dDate'] = date('Y-m-d',strtotime($_POST['dDate'.$i])); 
		 		$class_dates[$j]['tBegin'] = date('G:i:s',strtotime($_POST['tBegin'.$i]));
		 		$class_dates[$j]['tEnd'] = date('G:i:s',strtotime($_POST['tEnd'.$i]));
				$j++;
		 	}
		 	$i++;
		}

		sort($class_dates);

		if(count($class_dates) > 0) {
			$q2 = "";
			foreach ($class_dates as $k => $entry) {
				$q2 .= "('". $eventinfo['ID'] ."','". $entry['dDate'] ."','". $entry['tBegin'] ."','". $entry['tEnd'] ."'),";
			}
			$q2 = substr($q2, 0, -1);
			database_query("INSERT INTO EventDates (idEvent,dDate,tBegin,tEnd) VALUES ".$q2,"Insert Dates into DB");
		}
			
		?>

		<script language=JavaScript>
		window.opener.window.location.reload();
		window.close();
		</script>

		<?

		die();
	}



	$Venues = database_query("SELECT ID, chrVenue, chrCity, chrState FROM Venues WHERE !bDeleted","getting Venues info");
	$EventTitles = database_query("SELECT ID,chrName FROM EventTitles WHERE !bDeleted","getting EventTitles info");
	$results = database_query("SELECT dDate, tBegin, tEnd FROM EventDates WHERE idEvent=".$eventinfo['ID'],"Getting Dates");
	
	$j=1;
	$dates = array();
	while($row = mysqli_fetch_assoc($results)) {
		$dates['dDate'.$j] = $row['dDate'];
		$dates['tBegin'.$j] = $row['tBegin'];
		$dates['tEnd'.$j] = $row['tEnd']; 
		$j++;
	}

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script type="text/javascript">
	function associate(id, name)
	{
		dad = window.opener.document;
<?		if(isset($data['functioncall'])) { ?>
			window.opener.<?=$data['functioncall']?>(id, name);
<?		} ?>

		//window.close();
	}
</script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventTitle', "You must select a Title.");
		total += ErrorCheck('dDate1', "You must enter at least 1 Date.");
		total += ErrorCheck('tBegin1', "You must enter at least 1 Begin Time.");		
		total += ErrorCheck('tEnd1', "You must enter at least 1 End Time.");

		return (total == 0 ? true : false);
	}
</script>
<?
	include($BF. 'includes/top_popup.php');
?>
<form id='idForm' name='idForm' method='post' action='' onsubmit="return error_check()">
		<div style="padding-bottom:5px;"><strong>Modify Event Information</strong></div>
		<div class='instructions'>Fill out items and click Save and Close. All Fields Required.</div>
		<div id='errors'></div>
		<div style="border:1px #AAA solid; padding:5px;">
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
					<tr>
						<td style="width:50%;">
							<div class='FormName'>Show this event.</div>
							<div class='FormField'><input type="radio" id="bShow" name="bShow" value="0" <?=($eventinfo['bShow'] == 0 ? "checked='checked'" : "")?> /> No <input type="radio" id="bShow" name="bShow" value="1" <?=($eventinfo['bShow'] == 1 ? "checked='checked'" : "")?> /> Yes</div>
						
							<div class='FormName'>Event Title</div>
							<select class='FormField' id="idEventTitle" name='idEventTitle' style="width:98%;" >
									<option value=''>Select Event Title</option>					
								<? while ($row = mysqli_fetch_assoc($EventTitles)) { ?>
									<option value='<?=$row['ID']?>' <?=($row['ID'] == $eventinfo['idEventTitle'] ? 'selected="selected"' : "")?>><?=$row['chrName']?></option>
								<?	} ?>
							</select>
						</td>
						<td style="width:50%;">
							<div class='FormName'>Venue <span class='Required'>(Required)</span></div>
							<select class='FormField' id="idVenue" name='idVenue' style="width:100%;" >
								<option value=''>Select Venue</option>
							<? while ($row = mysqli_fetch_assoc($Venues)) { ?>
								<option value='<?=$row['ID']?>'<?=($row['ID'] == $eventinfo['idVenue'] ? 'selected="selected"' : "")?>><?=$row['chrVenue']?> (<?=$row['chrCity']?>, <?=$row['chrState']?>)</option>
							<?	} ?>
							</select>
						</td>
					</tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<?
				$i = 1;
				while ($i <= $TOTAL_DATE_FIELDS) {
?>
					<tr>
						<td style="width:33%;">
							<div class='FormName'>Date <?=$i?> <span class='Required'>(5/24/2008)</span></div>					
							<div class='FormField'><input type='text' name='dDate<?=$i?>' id='dDate<?=$i?>' style="width:98%" value="<?=(isset($dates['dDate'.$i]) ? date('n/j/Y',strtotime($dates['dDate'.$i])) : '')?>"/></div>
						</td>
						<td style="width:34%;">
							<div class='FormName'>Begin Time <?=$i?> <span class='Required'>(8:00 am)</span></div>
							<div class='FormField'><input type='text' name='tBegin<?=$i?>' id='tBegin<?=$i?>' style="width:98%"  value="<?=(isset($dates['tBegin'.$i]) ? date('g:i a',strtotime($dates['tBegin'.$i])) : '')?>"/></div>
						</td>
						<td style="width:33%;">
							<div class='FormName'>End Time <?=$i?> <span class='Required'>(3:00 pm)</span></div>					
							<div class='FormField'><input type='text' name='tEnd<?=$i?>' id='tEnd<?=$i?>' style="width:100%" value="<?=(isset($dates['tEnd'.$i]) ? date('g:i a',strtotime($dates['tEnd'.$i])) : '')?>"/></div>
						</td>
					</tr>
<?
					$i++;
				}
?>
				</table>
			</div>
<input class='FormButtons' type='submit' value='Save and Close Window' />
<input type='hidden' name='idEvent' id='idEvent' value="<?=$eventinfo['ID']?>" />
</form>

<? include($BF. "includes/bottom_popup.php"); ?>
