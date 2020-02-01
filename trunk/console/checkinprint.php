<?	
	$BF = '../';
	$title = 'Checkin List Print-out';
	require($BF. '_lib.php');
	


	if (isset($_POST['checkin']) && count($_POST['checkin']) > 0 && isset($_POST['dtCheckin'])) {
		foreach($_POST['checkin'] as $signup)	{
			$query = "UPDATE Signups SET 
			bCheckin=1, dtCheckin = '". date('Y-m-d h:i:s', strtotime($_POST['dtCheckin'])) ."'
			WHERE ID= ".$signup;
			database_query($query, 'Checking In');
		}
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }	
	$result = array();
	if (isset($_REQUEST['idEvent']) && $_REQUEST['idEvent'] != "") {
		$q = "SELECT Signups.ID, Attendees.chrFirst, Attendees.chrLast, Attendees.chrEmail,Attendees.chrCompany, Status.chrName as chrStatus, DATE_FORMAT(Signups.dtCheckin, '%c/%d/%Y - %h:%i%p') AS tCheckin, Signups.bCheckin
				FROM Attendees
				JOIN Signups ON Signups.idUser=Attendees.ID
				JOIN Status ON Signups.idStatus=Status.ID
				WHERE !Attendees.bDeleted AND Signups.idStatus != 3 AND Signups.idEvent=".$_REQUEST['idEvent']."
				ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$result = database_query($q,"Getting all Attendees");
	}	
	
	$active = 'admin';
	$subactive = 'chkprint';

	// Load Drop Downs for Page 	
	$EventSeries = database_query("Select ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle", "Grabbing all Event Series");
	if (!isset($_REQUEST['idEventSeries']) || $_REQUEST['idEventSeries'] == "") { $_REQUEST = fetch_database_query("Select ID AS idEventSeries FROM EventSeries WHERE !bDeleted ORDER BY chrTitle LIMIT 1", "Grabbing First Event Series"); }
	$Venues = database_query("Select Venues.ID, Venues.chrVenue, Venues.chrCity, Venues.chrState FROM Venues
								JOIN Events ON Events.idVenue=Venues.ID
							 WHERE !Venues.bDeleted AND Events.idEventSeries=".$_REQUEST['idEventSeries']." GROUP BY Venues.ID ORDER BY Venues.chrVenue", "Grabbing all Venues");
	
if (isset($_REQUEST['idEventSeries']) && isset($_REQUEST['idVenue']) && $_REQUEST['idVenue'] != "" && $_REQUEST['idEventSeries'] != "") {
		$Events = database_query("Select Events.ID, chrName FROM Events JOIN EventTitles ON Events.idEventTitle=EventTitles.ID WHERE !Events.bDeleted AND !EventTitles.bDeleted AND Events.idEventSeries=".$_REQUEST['idEventSeries']." AND Events.idVenue=".$_REQUEST['idVenue']." ORDER BY chrName"
		, "Grabbing all Events For Series and Venue");	
	}	


include($BF. 'includes/meta_admin.php');
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('dtCheckin', "You must enter a Date and Time.");		

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>

<?
	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "EventTitles";
	include($BF. 'includes/overlay.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade NoPrint'>
	<tr>
		<td class="left"></td>
		<td class="title" style="width:150px;">Checkin List Print-out</td>
		<td class="title" style="width:45px;">Series:</td>        
		<td class="title_right" style="vertical-align:bottom; text-align:left; width:155px;">
        	<select class='FormField' id="idEventSeries" name='idEventSeries' style="width:150px;" onchange='location.href="checkinprint.php?idEvent=<?=$_REQUEST['idEvent']?>&idVenue=<?=$_REQUEST['idVenue']?>&idEventSeries="+this.value'>
			<? while ($row = mysqli_fetch_assoc($EventSeries)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idEventSeries']) && $row['ID'] == $_REQUEST['idEventSeries'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
			<?	} ?>
			</select>
        </td>
       	<td class="title" style="width:50px;">Venue:</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left; width:155px;">
           	<select class='FormField' id="idVenue" name='idVenue' style="width:150px;" onchange='location.href="checkinprint.php?idEventSeries=<?=$_REQUEST['idEventSeries']?>&idEvent=<?=$_REQUEST['idEvent']?>&idVenue="+this.value' >
				<option value=''>Select Venue Location</option>            
			<? while ($row = mysqli_fetch_assoc($Venues)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idVenue']) && $row['ID'] == $_REQUEST['idVenue'] ? ' selected="selected"' : "" )?>><?=$row['chrVenue']?> (<?=$row['chrCity']?>, <?=$row['chrState']?>)</option>
			<?	} ?>
			</select>            
		</td>
       	<td class="title" style="width:50px;">Event:</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left; width:155px;">
           	<select class='FormField' id="idEvent" name='idEvent' style="width:150px;" onchange='location.href="checkinprint.php?idEventSeries=<?=$_REQUEST['idEventSeries']?>&idVenue=<?=$_REQUEST['idVenue']?>&idEvent="+this.value'>
	            <option value=''>Select Event</option>     
			<? while ($row = mysqli_fetch_assoc($Events)) { ?>
				<option value='<?=$row['ID']?>'<?=(isset($_REQUEST['idEvent']) && $row['ID'] == $_REQUEST['idEvent'] ? ' selected="selected"' : "" )?>><?=$row['chrName']?></option>
			<?	} ?>
			</select>            
		</td>    
		<td class="title_right" style="text-align:right;">
             <input type="button" id="Print" name="Print" onclick="window.print();" value="Print" />
		</td>                
		<td class="right"></td>
	</tr>
</table>
<div class='instructions NoPrint'>To get a print out of Attendees for a particular event, please choose Event Series, Venue and Event Title from above, then click Print to print the list.<br />To check in attendees from the print out, enter in a date/time and check the box next to the persons name.</div>
<form name='idForm' id='idForm' action='' method="post">
<div id='errors' class="NoPrint"></div>	
<div class='innerbody' id="PrintArea">
	<table cellpadding="0" cellspacing="0" style="width:100%;" class="NoPrint">
    	<tr>
        	<td class='FormName'>Date/Time of Checkin (ie. '05/25/2008 3:30pm') <span class='Required'>(Required)</span></td>
            <td class='FormField'><input type="text" name="dtCheckin" id="dtCheckin" size="50" /></td>
            <td style="text-align:right; vertical-align:top;"><input type='button' value='Check-in Attendees' onclick="error_check();" /></td>
        </tr>
    </table><br />
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<? sortList('Checked In?', 'bCheckin','','idEventSeries='.$_REQUEST['idEventSeries'].'&idVenue='.$_REQUEST['idVenue'].'&idEvent='.$_REQUEST['idEvent']); ?>
			<? sortList('Name', 'chrLast','','idEventSeries='.$_REQUEST['idEventSeries'].'&idVenue='.$_REQUEST['idVenue'].'&idEvent='.$_REQUEST['idEvent']); ?>
			<? sortList('Company', 'chrCompany','','idEventSeries='.$_REQUEST['idEventSeries'].'&idVenue='.$_REQUEST['idVenue'].'&idEvent='.$_REQUEST['idEvent']); ?>
			<? sortList('Signup ID', 'ID','','idEventSeries='.$_REQUEST['idEventSeries'].'&idVenue='.$_REQUEST['idVenue'].'&idEvent='.$_REQUEST['idEvent']); ?>
			<? sortList('Status', 'chrStatus','','idEventSeries='.$_REQUEST['idEventSeries'].'&idVenue='.$_REQUEST['idVenue'].'&idEvent='.$_REQUEST['idEvent']); ?>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>'>
				<td><input type="checkbox" id="checkin" value="<?=$row['ID']?>" <?=($row['bCheckin'] == 1 ? "checked='checked' disabled='disabled' name='ischeckedin[]'" : "name='checkin[]'")?> /></td>
				<td><?=$row['chrLast']?>, <?=$row['chrFirst']?></td>
				<td><?=$row['chrCompany']?></td>
				<td><?=$row['ID']?></td>
				<td><?=$row['chrStatus']?></td>                
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Event Titles to display</td>
			</tr>
<?	} ?>
	</table>
<div class='FormField NoPrint'><input class='FormButtons' type='button' value='Check-in Attendees' onclick="error_check();" /></div>
	</div>
</form>       
<?
	include($BF. 'includes/bottom_admin.php');
?>
