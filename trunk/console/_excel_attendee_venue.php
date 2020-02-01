<?php
// INSERT PROJECT NAME HERE
	session_name('appleappevents');
	session_start();
	
function decode($val) {
	$val = str_replace('&quot;','"',$val);
	$val = str_replace("&apos;","'",$val);
	return $val;
}

	include('appleappevents-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$q = "SELECT Attendees.*, Status.chrName AS chrStatus, DATE_FORMAT(Signups.dtStamp, '%m/%d/%Y - %h:%i %p') AS dtFormated, Signups.bCheckin, DATE_FORMAT(Signups.dtCheckin, '%m/%d/%Y - %h:%i %p') AS dtCheckin, EventTitles.chrName AS chrEventTitle
		  FROM Attendees
		  JOIN Signups ON Signups.idUser=Attendees.ID
		  JOIN Status ON Signups.idStatus=Status.ID
		  JOIN Events ON Signups.idEvent=Events.ID
		  JOIN EventTitles ON Events.idEventTitle=EventTitles.ID
		  WHERE !Attendees.bDeleted AND idEventSeries=".$_REQUEST['series']." AND Events.idVenue=".$_REQUEST['id']."
		  ORDER BY Attendees.chrLast, Attendees.chrFirst";

	$result = mysqli_query($mysqli_connection, $q);

	$q = "SELECT chrVenue
		  FROM Venues
		  WHERE ID=".$_REQUEST['id'];
		  
	$venue = mysqli_fetch_assoc(mysqli_query($mysqli_connection,$q));

	$venue_name = str_replace(" ", "_", decode($venue['chrVenue']));
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=Attendee_Report_(". $venue_name .").xls");
	header("Pragma: no-cache");
	header("Expires: 0");
?>
<style>
.Heading { font-weight:bold; font-size:11px; border-right: 1px solid #000000; margin:2px; vertical-align:middle; height:20px; }

.FirstRow1 { font-size:11px; border-top: 1px solid #000000; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#DDDDDD; }
.FirstRow2 { font-size:11px; border-top: 1px solid #000000; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#FFFFFF; }

.SecondRow1 { font-size:11px; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#DDDDDD; }
.SecondRow2 { font-size:11px; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#FFFFFF; }
</style>


	<table border="0">
		<tr>
			<td class="Heading">First Name</td>
			<td class="Heading">Last Name</td>
<?
	if($_REQUEST['series'] != 3) { 
?>
			<td class="Heading">Company</td>	
			<td class="Heading">Address</td>
			<td class="Heading">Address1</td>
			<td class="Heading">City</td>
			<td class="Heading">State</td>
			<td class="Heading">Zip</td>
			<td class="Heading">Country</td>
<?
	}
?>
			<td class="Heading">Phone</td>
			<td class="Heading">E-mail</td>
			<td class="Heading">Stay in Touch?</td>
			<td class="Heading">Session Name</td>
			<td class="Heading">Status</td>
			<td class="Heading">Signup Date/Time</td>
			<td class="Heading">Attended?</td>
		</tr>
<?
	$lastrow = 0;
	$count=0;

	while($row = mysqli_fetch_array($result)) {
		if ($lastrow != $row['ID']) {
			$count++;
			$lastrow = $row['ID'];

			?>
			<tr>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrFirst'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrLast'])?></td>
<?
	if($_REQUEST['series'] != 3) { 
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCompany'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrAddress'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrAddress1'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCity'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrState'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrZip']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCountry'])?></td>
<?
	}
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrPhone']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrEmail']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=($row['bApple'] ? "Yes" : "No")?></td>	
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrEventTitle'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrStatus'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['dtFormated']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=($row['bCheckin'] ? "Yes ( ".$row['dtCheckin']." )" : "No")?></td>			
			</tr>
			<?
	
		} else {
	
			?>
			<tr style="border-top:0;">
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
<?
	if($_REQUEST['series'] != 3) { 
?>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
<?
	}
?>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>">&nbsp;</td>	
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>" style="border-top: 1px solid #000000;"><?=decode($row['chrEventTitle'])?></td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>" style="border-top: 1px solid #000000;"><?=decode($row['chrStatus'])?></td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>" style="border-top: 1px solid #000000;"><?=$row['dtFormated']?></td>
				<td class="SecondRow<?=($count%2 ? "1" : "2" )?>" style="border-top: 1px solid #000000;"><?=($row['bCheckin'] ? "Yes ( ".$row['dtCheckin']." )" : "No")?></td>			
			</tr>
			
			<?
			
		}			
				
	}
?>
	</table>