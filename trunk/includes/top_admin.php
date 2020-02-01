</head>
<body onLoad="<?=(isset($bodyParams) ? $bodyParams : '')?>">

<!-- The top of the screen -->

<div class='mainBody'>

<div width="900" class='topBanner NoPrint'>
	<img src="<?=$BF?>images/title_banner.jpg" width="900" height="100" />
</div>

<table border="0" cellspacing="0" cellpadding="0" class='navMenu NoPrint'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($active == 'home' ? '_active' : '')?>"><a href="<?=$BF?>index.php">Customer Front</a></td>
		<td class="menu<?=($active == 'report' ? '_active' : '')?>"><a href="<?=$BF?>console/report.php">Reports</a></td>	
		<td class="menu<?=($active == 'checkin' ? '_active' : '')?>"><a href="<?=$BF?>checkin/index.php">Checkin</a></td>	
		<td class="menu<?=($active == 'emails' ? '_active' : '')?>"><a href="<?=$BF?>console/emailattendees.php">Emails</a></td>
		<td class="menu<?=($active == 'admin' ? '_active' : '')?>"><a href="<?=$BF?>console/index.php">Admin</a></td>						

		
		<td class="fill"><a href='<?=$BF?>profile/'><?=$_SESSION['chrFirst']?> <?=$_SESSION['chrLast']?></a> | <a href='?logout=1'>Logout</a> </td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<?
	if($active == 'admin') {	
?>

<table border="0" cellspacing="0" cellpadding="0" class='navSubMenu NoPrint'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($subactive == 'eventseries' ? '_active' : '')?>"><a href="event_series.php">Event Series</a></td>
		<td class="menu<?=($subactive == 'venue' ? '_active' : '')?>"><a href="venues.php">Venues</a></td>
		<td class="menu<?=($subactive == 'eventtitles' ? '_active' : '')?>"><a href="eventtitles.php">Event Titles</a></td>				
		<td class="menu<?=($subactive == 'leads' ? '_active' : '')?>"><a href="leads.php">Registration Leads</a></td>
		<td class="menu<?=($subactive == 'attendees' ? '_active' : '')?>"><a href="attendees.php">Attendees</a></td>
		<td class="menu<?=($subactive == 'users' ? '_active' : '')?>"><a href="users.php">Users</a></td>
		<td class="menu<?=($subactive == 'chkprint' ? '_active' : '')?>"><a href="checkinprint.php">Checkin List Print-out/Manual Checkin</a></td>
		<td class="menu<?=($subactive == 'massmove' ? '_active' : '')?>"><a href="massmove.php">Mass Move</a></td> 
		<td class="fill"></td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<? 
	}
?>

<?
	if($active == 'report') {	
?>

<table border="0" cellspacing="0" cellpadding="0" class='navSubMenu NoPrint'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($subactive == 'global' ? '_active' : '')?>"><a href="<?=$BF?>console/report.php">Global Report</a></td>
		<td class="menu<?=($subactive == 'eventtitle' ? '_active' : '')?>"><a href="<?=$BF?>console/report_eventtitle.php">Event Title Report</a></td>
		<td class="menu<?=($subactive == 'venue' ? '_active' : '')?>"><a href="<?=$BF?>console/report_venue.php">By Venue Report</a></td>
		<td class="menu<?=($subactive == 'day' ? '_active' : '')?>"><a href="<?=$BF?>console/report_day.php">Daily Statistics Report</a></td>
		<td class="menu<?=($subactive == 'reseller' ? '_active' : '')?>"><a href="<?=$BF?>console/reseller_leads.php">Reseller Leads</a></td>
		<td class="fill"></td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<? 
	}
?>

<?
	if($active == 'emails') {	
?>

<table border="0" cellspacing="0" cellpadding="0" class='navSubMenu NoPrint'>
	<tr>
		<td><img src="<?=$BF?>images/cap-left.gif" width="10" height="21" /></td>
		<td class="menu<?=($subactive == 'emailattendees' ? '_active' : '')?>"><a href="emailattendees.php">E-mail Attendees</a></td>
		<td class="fill"></td>
		<td align="right"><img src="<?=$BF?>images/cap-right.gif" width="10" height="21" /></td>
	</tr>
</table>

<? 
	}
?>

<div class='content'>
