<?	
	$BF = '../';
	$title = 'Send E-mail to Attendees';
	require($BF. '_lib.php');

	$active = 'emails';
	$subactive = 'emailattendees';	
	
	if (isset($_POST['submit']) && $_POST['submit'] == "Start") {
	
		header("Location: ".$BF."console/sendemail.php");
		die(); 		
	}
	include($BF. 'includes/meta_admin.php');	
	// Get the amount of Attendees this e-mail will goto

	$q = "SELECT COUNT(DISTINCT Attendees.ID) AS total
			FROM Attendees
			JOIN Signups ON Signups.idUser=Attendees.ID
			JOIN Events ON Signups.idEvent=Events.ID
			WHERE Events.idEventSeries=".$_SESSION['email']['idEventSeries']." AND Events.idVenue=".$_SESSION['email']['idVenue']." AND Events.ID LIKE '".$_SESSION['email']['idEvent']."' AND Signups.idStatus LIKE '".$_SESSION['email']['idStatus']."' AND !Attendees.bDeleted";

	$total_emails = fetch_database_query($q, "Getting Total Amount of Attendees for E-mail");	

	$seriesinfo = fetch_database_query("SELECT ID, chrTitle, chrEmailName, chrFromEmail
									FROM EventSeries
									WHERE !EventSeries.bDeleted AND EventSeries.ID=".$_SESSION['email']['idEventSeries'], "Getting EventSeries");
	
	$q = "SELECT chrVenue, chrAddress, chrAddress2, chrCity, chrState, chrZip, chrCountry, chrPhone, chrRoom, intCapacity, chrContact, chrGoogle, intDropOff, txtDirections, chrTravel, chrLocation, txtNotes
		FROM Venues
		JOIN Events ON Events.idVenue=Venues.ID
		LEFT JOIN TimeZone ON Venues.idTimeZone=TimeZone.ID
		WHERE Events.idEventSeries=".$_SESSION['email']['idEventSeries']." AND Events.idVenue=".$_SESSION['email']['idVenue'];
	$venuedata = fetch_database_query($q,"Getting Event Information");
	
	$message = $_SESSION['email']['txtMsg'];
	
	$message = str_replace('$FIRST_NAME',encode($_SESSION['chrFirst']),$message); 
	$message = str_replace('$LAST_NAME',encode($_SESSION['chrLast']),$message);
	$message = str_replace('$SERIES_TITLE',encode($seriesinfo['chrTitle']),$message);
	$message = str_replace('$VENUE_NAME',encode($venuedata['chrVenue']),$message);
	if($venuedata['chrAddress2'] != '') { $venuedata['chrAddress'] .= '<br />'.$venuedata['chrAddress2']; }
	$message = str_replace('$VENUE_ADDRESS',encode($venuedata['chrAddress']),$message);
	$message = str_replace('$VENUE_CITY',encode($venuedata['chrCity']),$message);
	$message = str_replace('$VENUE_STATE',encode($venuedata['chrState']),$message);
	$message = str_replace('$VENUE_POSTAL',encode($venuedata['chrZip']),$message);
	$message = str_replace('$VENUE_COUNTRY',encode($venuedata['chrCountry']),$message);
	$message = str_replace('$VENUE_PHONE',encode($venuedata['chrPhone']),$message);
	$message = str_replace('$VENUE_ROOM',encode($venuedata['chrRoom']),$message);
	$message = str_replace('$VENUE_ONLINE_MAP',$venuedata['chrGoogle'],$message);
	$message = str_replace('$VENUE_TRAVEL_URL',$venuedata['chrTravel'],$message);
	$message = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($venuedata['txtDirections'])),$message);
	$message = str_replace('$VENUE_NOTES',encode($venuedata['txtNotes']),$message);
	$message = str_replace('$VENUE_CONTACT_PERSON',encode($venuedata['chrContact']),$message);
	$message = str_replace('$VENUE_TIMEZONE',encode($venuedata['chrLocation']),$message);
	if(is_numeric($_SESSION['email']['idEvent'])) {
		$message = str_replace('$TRANSFER_LINK','http://appleappevents.techitweb.com/transfer.php',$message);
	}
	
	
	include($BF. 'includes/top_admin.php');
?>
<form name='idForm' id='idForm' action='' method="post">	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" nowrap="nowrap">Email Attendees</td>
		<td class="title_right" style="text-align:right;">
            
		</td>                
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>This is a preview of the e-mail series you are about to send, Click Send at the bottom to start.<br /><br /> Once you click Start do not stop the process until it is done. This will be indicated by DONE at the end, The screen will appear White and you will see each attendees name as they are e-mailed. There will be also a 1 second pause after each 50 e-mails sent, You will see these as "Pause" lines. Depending on the number indicated below, and server loads this can take several minuates. Pleaes be Patient.<br /><br />WARNING!!  This will send a total of <strong><?=$total_emails['total']?></strong> E-mails.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<div style="border:1px #999999 solid; margin-bottom:5px; padding:3px;"><strong>Subject:</strong> <?=$_SESSION['email']['chrSubject']?></div>
		<div style="border:1px #999999 solid; padding:3px; background-color:#FFFFFF;">
			<div id='email'>
				<?=$message // Display Message Text from Previous Page?>
<?			if (isset($_SESSION['email']['resend']) && $_SESSION['email']['resend'] == 1) { ?>
					
				<div style='border-top:1px solid #666; padding-bottom:20px; padding-top:20px; text-align:center; font-weight:bold; color:red;'>A copy of the Attendees registration e-mail will go here.</div>
<?			} ?>
					
			</div>
		</div>

		<div class='FormField'><input class='FormButtons' type='submit' name="submit" value='Start' /></div>
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
