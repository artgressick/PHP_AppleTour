<?php // This file is used to send hold all the e-mail sections as functions

function email_from() {

	$tmp = "Pro Applications Marketing Events <proapps_events@apple.com>";
	
	return $tmp;
}

function email_logo($first, $last, $image) { // This is for the tops of the e-mails
	$tmp = "<style type='text/css'>
				<!--
				.email  {
					font-family: Arial, Helvetica, sans-serif;
					font-size: 12px;
					font-style: normal;
					color: #0000FF;
				}
				.section_header {
					font-family: Arial, Helvetica, sans-serif;
					font-size: 24px;
					font-weight: bold;
					text-transform: capitalize;
					color: #666666;
					font-style: normal;
				}
				table {
				}
				.left_col {
					font-weight: bold;
					text-align: left;
					vertical-align: top;
					padding: 2px;
				}
				.fine_print {
					font-size: 10px;
					color: #999999;
				}
				.left_col_cancel {
					font-weight: bold;
					font-style: normal;
					text-align: left;
					vertical-align: top;
					padding-top: 10px;
					padding-bottom: 10px;
				}
				-->
				</style>
				<div id='email'>
				<p><img src='http://protour.techitweb.com/images/".(isset($image) ? $image : "_default_emailattendee_top.gif" )."' alt='Pro Applications' /></p>
				<p>Dear ". $first ." ". $last .":</p>";

	return $tmp;

}

function email_header($msg, $location) { // This is typically the begining paragraph

	$tmp = "<p>".$msg."</p>
			<hr>
			<p><span class='section_header'>EVENT DETAILS</span><br />
			<span class='fine_print'>All times are ". $location ."</span></p>";
	return $tmp;

}

function email_event_confirmed($name, $tBegin, $tEnd, $dDate, $description, $cancel) {

	$tmp = "<p><table width='100%' border='0' cellpadding='0' cellspacing='0'>
						  <tr>
							<td width='130' class='left_col'>Seminar Name: </td>
							<td width='5'>&nbsp;</td>
							<td>". $name ."</td>
						  </tr>
						  <tr>
							<td class='left_col' style='vertical-align:top;'>Registration Status: </td>
							<td width='5'>&nbsp;</td>
							<td><span style='color:red;'>Seat Reserved</span><br />
								<span class='fine_print'><i>(If you are unable to attend this Seminar please cancel your registration so another person may fill your seat. If you have need of any special accommodations (such as ASL interpretation), send us this email with your request at least two weeks prior to the Seminar.)</i></span>
							</td>
						  </tr>
						  <tr>
							<td class='left_col'>Time:</td>
							<td width='5'>&nbsp;</td>
							<td>". date('g:i a',strtotime($tBegin)) ." to ". date('g:i a',strtotime($tEnd)) ."</td>
						  </tr>
						  <tr>
							<td class='left_col'>Date:</td>
							<td width='5'>&nbsp;</td>
							<td>". date('F j, Y',strtotime($dDate)) ."</td>
						  </tr>					  
						  <tr>
							<td class='left_col'>Description:</td>
							<td width='5'>&nbsp;</td>
							<td><br />". nl2br($description) ."</td>
						  </tr>
						  <tr>
							<td class='left_col_cancel'>Cancel Link: </td>
							<td width='5'>&nbsp;</td>
							<td><a href='". $cancel ."'>Cancel This Reservation Registration ONLY</a></td>
						  </tr>
						</table></p>
						<hr>";

	return $tmp;

}

function email_waitlisted($name, $dDate, $description, $cancel) {

	$tmp = "<p><table width='100%' border='0' cellpadding='0' cellspacing='0'>
						  <tr>
							<td width='130' class='left_col'>Seminar Name: </td>
							<td width='5'>&nbsp;</td>
							<td>". $name ."</td>
						  </tr>
						  <tr>
							<td class='left_col' style='vertical-align:top;'>Registration Status: </td>
							<td width='5'>&nbsp;</td>
							<td><span style='color:red;'>Wait-listed</span><br />
								<span class='fine_print'><i>(This is not a reservation, you have been placed you on Waitlist status for this seminar.  The seminar has reached full seating capacity due to demand.  If space becomes available we will notify you immediately and change your status to 'Confirmed'.  If you choose to Cancel your waitlist status at anytime, please click on the Cancel Link below to update your status on this Seminar.)</i></span>
							</td>
						  </tr>
						  <tr>
							<td class='left_col'>Date:</td>
							<td width='5'>&nbsp;</td>
							<td>". date('F j, Y',strtotime($dDate)) ."</td>
						  </tr>					  
						  <tr>
							<td class='left_col'>Description:</td>
							<td width='5'>&nbsp;</td>
							<td><br />". nl2br($description) ."</td>
						  </tr>
						  <tr>
							<td class='left_col_cancel'>Cancel Link: </td>
							<td width='5'>&nbsp;</td>
							<td><a href='". $cancel ."'>Cancel This Seminars Waitlist Registration ONLY</a></td>
						  </tr>
						</table></p>
						<hr>";

	return $tmp;

}

function email_cancelled($name, $dDate, $description) {

	$tmp = "<p><table width='100%' border='0' cellpadding='0' cellspacing='0'>
						  <tr>
							<td width='130' class='left_col'>Seminar Name: </td>
							<td width='5'>&nbsp;</td>
							<td>". $name ."</td>
						  </tr>
						  <tr>
							<td class='left_col' style='vertical-align:top;'>Registration Status: </td>
							<td width='5'>&nbsp;</td>
							<td><span style='color:red;'>Cancelled</span><br />
								<span class='fine_print'><i>(You have already Cancelled this Event, if you would like to attend please submit a new Registration.)</i></span>
							</td>
						  </tr>
						  <tr>
							<td class='left_col'>Date:</td>
							<td width='5'>&nbsp;</td>
							<td>". date('F j, Y',strtotime($dDate)) ."</td>
						  </tr>					  
						  <tr>
							<td class='left_col'>Description:</td>
							<td width='5'>&nbsp;</td>
							<td><br />". nl2br($description) ."</td>
						  </tr>
						</table></p>
						<hr>";

	return $tmp;

}

function email_venueinfo($name, $address1, $address2, $city, $state, $zip, $room, $phone, $directions, $travel, $map, $notes) {

	$tmp = "<p class='section_header'>LOCATION</p>
				<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				  <tr>
					<td width='130' class='left_col'>Name: </td>
					<td width='5'>&nbsp;</td>
					<td>". $name ."</td>
				  </tr>
				  <tr>
					<td class='left_col'>Address: </td>
					<td width='5'>&nbsp;</td>
					<td><p>". $address1 .($address2 != "" ? "<br />". $address2 : "") ."<br />
						". $city .", ". $state ." ". $zip ."</p></td>
				  </tr>
				  <tr>
					<td class='left_col'>Room:</td>
					<td width='5'>&nbsp;</td>
					<td>". $room ."</td>
				  </tr>
				  <tr>
					<td class='left_col'>Phone:</td>
					<td width='5'>&nbsp;</td>
					<td>". $phone ."</td>
				  </tr>
				</table>
				<p class='section_header'>DIRECTIONS</p>
				<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				  <tr>
					<td width='130' class='left_col'>Basic Directions: </td>
					<td width='5'>&nbsp;</td>
					<td>". nl2br($directions) ."</td>
				  </tr>
				  <tr>
					<td class='left_col'>Travel Information: </td>
					<td width='5'>&nbsp;</td>
					<td><p><a href='". $travel ."'>Click for Travel Directions</a></p></td>
				  </tr>
				  <tr>
					<td class='left_col'>Online Map: </td>
					<td width='5'>&nbsp;</td>
					<td><a href='". $map ."'>Click for a Online Map</a></td>
				  </tr>
				  <tr>
					<td class='left_col'>Notes: </td>
					<td width='5'>&nbsp;</td>
					<td>". nl2br($notes) ."</td>
				  </tr>				  
				</table>
				<hr>";

	return $tmp;

}

function email_footer($cancelall) {

	$tmp = "<p class='fine_print'>Dates, times, locations, speakers, and content may change without notice. <br />
				Apple and the event sponsors accept no responsibility in connection with such changes.</p>
				<p>Please arrive in advance to allow time for parking and check-in.<br />
				If you will not be able to attend any of the above Events, please cancel your subscription below to allow another person to fill your spot.</p>
				<p><a href='". $cancelall ."'>Cancel ALL Seminar Registrations</a></p>";

	return $tmp;

}

function email_signature() {

	$tmp = "<p>Thank you for your interest.<br />
				Best Regards,<br />
				Apple Pro Applications Marketing
				</p></div>";

	return $tmp;

}

function email_cancelevent($events) {

	$tmp = "You have successfully cancelled your registration for ". $events;

	return $tmp;

}


?>