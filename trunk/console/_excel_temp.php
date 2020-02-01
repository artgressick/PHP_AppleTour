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
		
	$q = "SELECT A.chrFirst, A.chrLast, A.chrEmail
			FROM Attendees AS A
			JOIN Signups AS S ON S.idUser=A.ID
			WHERE S.idEvent=41 AND (lower(A.chrEmail) LIKE '%@aol.com' OR lower(A.chrEmail) LIKE '%@yahoo.com') AND S.idStatus=1";

	$result = mysqli_query($mysqli_connection, $q);

	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=Attendee_Yahoo_Or_AOL.xls");
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
			<td class="Heading">E-mail</td>	
		</tr>
<?
	while($row = mysqli_fetch_array($result)) {
?>
			<tr>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrFirst'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrLast'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrEmail']?></td>
			</tr>
<?
	}
?>
	</table>