<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Event Series';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "eventseries";		 // This is needed to highlight the show section
	require($BF. '_lib.php');
	
	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	if(!isset($_REQUEST['idSeries']) || !is_numeric($_REQUEST['idSeries'])) { header("Location: event_series.php"); die(); }
	if(!isset($_REQUEST['idType']) || !is_numeric($_REQUEST['idType'])) { header("Location: email_setup.php?id=".$_REQUEST['idSeries']); die(); }
	if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) { header("Location: email_setup.php?id=".$_REQUEST['idSeries']); die(); }
	$info = fetch_database_query("SELECT ID, chrTitle, chrEmailName, chrFromEmail FROM EventSeries WHERE ID=". $_REQUEST['idSeries'],"getting Event Series info");
	$emailtype = fetch_database_query("SELECT * FROM EmailTypes WHERE ID=".$_REQUEST['idType'],"Getting Email Type Info");
	$email = fetch_database_query("SELECT ID, chrSubject, txtBody FROM Emails WHERE idType=".$_REQUEST['idType']." AND idEventSeries=".$_REQUEST['idSeries'],"Getting Email Info");
	$eventemail = fetch_database_query("SELECT ID, chrSubject, txtBody FROM Emails WHERE idType=".($_REQUEST['idType'] <= 3 ? $_REQUEST['idType'] + 4 : 5)." AND idEventSeries=".$_REQUEST['idSeries'],"Getting Event List Email Info");
	$venue = fetch_database_query("SELECT V.chrVenue, V.chrAddress, V.chrAddress2, V.chrCity, V.chrState, V.chrZip, V.chrCountry, V.chrPhone, V.chrRoom, V.chrGoogle, V.chrTravel, V.txtDirections, V.txtNotes, V.chrContact, TZ.chrLocation
									FROM Events AS E
									JOIN Venues AS V ON E.idVenue=V.ID
									LEFT JOIN TimeZone AS TZ ON V.idTimeZone=TZ.ID
									WHERE E.idEventSeries=".$info['ID']." AND !E.bDeleted AND !V.bDeleted
									GROUP BY E.ID
									LIMIT 1
								", "Get Venue Info");
	if($venue['chrVenue'] == '') {
		$venue = array('chrVenue' => 'Apple Inc.',
					   'chrAddress' => '1 Infinite Loop',
					   'chrAddress2' => '',
					   'chrCity' => 'Cupertino',
					   'chrState' => 'CA',
					   'chrZip' => '95014',
					   'chrCountry' => 'US',
					   'chrPhone' => '555-555-5555',
					   'chrRoom' => 'Room 101',
					   'chrGoogle' => 'http://maps.google.com/maps?f=q&hl=en&geocode=&time=&date=&ttype=&q=1+Infinite+Loop,+Cupertino,+CA+95014&sll=37.331778,-122.030768&sspn=0.007848,0.015095&ie=UTF8&z=17&iwloc=addr&om=0',
					   'chrTravel' => 'http://maps.google.com/maps?f=q&hl=en&geocode=&time=&date=&ttype=&q=1+Infinite+Loop,+Cupertino,+CA+95014&sll=37.331778,-122.030768&sspn=0.007848,0.015095&ie=UTF8&ll=37.331778,-122.030768&spn=0.031394,0.060382&t=h&z=15&om=0',
					   'txtDirections' => '1.	Head south on Sunnyvale Saratoga Rd toward La Conner Dr<br />2.	Continue on N de Anza Blvd<br />3.	Turn left at Mariani Ave<br />4.	Turn left at Infinite Loop	',
					   'txtNotes' => 'Apple Inc. home office',
					   'chrContact' => 'Jessica',
					   'chrLocation' => 'Pacific Standard Time (GMT-8)'
					   );
	}
	$event = fetch_database_query("SELECT E.ID, ET.chrName, ET.txtShort, ET.txtLong FROM Events AS E JOIN EventTitles AS ET ON E.idEventTitle=ET.ID WHERE E.idEventSeries=".$info['ID']." AND !E.bDeleted LIMIT 1", "Get Event Info");
	if($event['ID'] != '') {
		// Lets pull all the dates for this Event Series
		$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
		FROM Events 
		JOIN EventDates ON EventDates.idEvent=Events.ID 
		WHERE Events.ID=".$event['ID']." 
		ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
		
		$results = database_query($q,"Grabbing All Dates for this Event Series");
	
		$eventDates = array();
		$fullDates = array();
		$prevID = 0;
		$prevDate = "";
		$day = 0;
		while ($row = mysqli_fetch_assoc($results)) {
	
			if($prevID != $row['idEvent']) { 
				if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
				$day = 1;
				$prevID = $row['idEvent'];
				$eventDates['chrDates'.$row['idEvent']] = "";
				$fullDates[$row['idEvent']] = '';
				$prevDate = "";
			}
				if($prevDate != date('F',strtotime($row['dDate']))) {
					if($day != 1) { $eventDates['chrDates'.$row['idEvent']] .= ", "; }
					$eventDates['chrDates'.$row['idEvent']] .= date('F',strtotime($row['dDate']))." ".date('jS',strtotime($row['dDate']));
				} else {
					if($day != 1) { $eventDates['chrDates'.$row['idEvent']] .= ", "; }
					$eventDates['chrDates'.$row['idEvent']] .= date('jS',strtotime($row['dDate']));
				}
				
			$prevDate = date('F',strtotime($row['dDate']));
			$fullDates[$row['idEvent']] .= date('l, F jS, Y',strtotime($row['dDate'])).' from '.date('g:i a',strtotime($row['tBegin'])).' to '.date('g:i a',strtotime($row['tEnd'])).'<br />';
			$day++;
			
		}
		$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));
	
	} else {
		$event['ID'] = 1;
		$event['chrName'] = 'Test Session';
		$event['txtShort'] = 'Short Event Description';
		$event['txtLong'] = 'Long Event Description';
		$fullDates[1] = date('l, F jS, Y').' from '.date('g:i a').' to '.date('g:i a',strtotime('+3 hours')).'<br />';
		$fullDates[1] .= date('l, F jS, Y',strtotime('+1 Day')).' from '.date('g:i a').' to '.date('g:i a',strtotime('+3 hours')).'<br />';
	}
	
	$email['txtBody'] = str_replace('$FIRST_NAME',encode($_SESSION['chrFirst']),$email['txtBody']); 
	$email['txtBody'] = str_replace('$LAST_NAME',encode($_SESSION['chrLast']),$email['txtBody']);
	$email['txtBody'] = str_replace('$SERIES_TITLE',encode($info['chrTitle']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_NAME',encode($venue['chrVenue']),$email['txtBody']);
	if($venue['chrAddress2'] != '') { $venue['chrAddress'] .= '<br />'.$venue['chrAddress2']; }
	$email['txtBody'] = str_replace('$VENUE_ADDRESS',encode($venue['chrAddress']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_CITY',encode($venue['chrCity']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_STATE',encode($venue['chrState']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_POSTAL',encode($venue['chrZip']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_COUNTRY',encode($venue['chrCountry']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_PHONE',encode($venue['chrPhone']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_ROOM',encode($venue['chrRoom']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_ONLINE_MAP',$venue['chrGoogle'],$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_TRAVEL_URL',$venue['chrTravel'],$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_BASIC_DIRECTIONS',encode(nl2br($venue['txtDirections'])),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_NOTES',encode($venue['txtNotes']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_CONTACT_PERSON',encode($venue['chrContact']),$email['txtBody']);
	$email['txtBody'] = str_replace('$VENUE_TIMEZONE',encode($venue['chrLocation']),$email['txtBody']);
	$email['txtBody'] = str_replace('$CANCEL_ALL',$PROJECT_ADDRESS.'cancel.php',$email['txtBody']);
	$eventemail['txtBody'] = str_replace('$EVENT_NAME',encode($event['chrName']),$eventemail['txtBody']);
	$eventemail['txtBody'] = str_replace('$EVENT_SHORT_DESCRIPTION',encode(nl2br($event['txtShort'])),$eventemail['txtBody']);
	$eventemail['txtBody'] = str_replace('$EVENT_LONG_DESCRIPTION',encode(nl2br($event['txtLong'])),$eventemail['txtBody']);
	$eventemail['txtBody'] = str_replace('$DATES_TIMES',encode($fullDates[$event['ID']]),$eventemail['txtBody']);
	$eventemail['txtBody'] = str_replace('$CANCEL_EVENT',$PROJECT_ADDRESS.'cancel.php',$eventemail['txtBody']);
	$email['txtBody'] = str_replace('$EVENT_INFO',encode($eventemail['txtBody']),$email['txtBody']);
	
	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "";
	
	//This is the include file for the overlay
	$TableName = "";
	include($BF. 'includes/overlay.php');
	include($BF. 'includes/top_admin.php');
?>
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title"><?=$info['chrTitle']?> Preview for <?=$emailtype['chrType']?> E-mail.</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>
		<div><span style='font-weight:bold;'>E-mail Description:</span> <?=$emailtype['chrDescription']?></div>
	</div>
	<div id='errors'></div>
	<div class='innerbody' style='background:white;'>
		<table cellpadding='2' cellspacing='0' style='width:100%;'>
			<tr>
				<td style='width:50px; font-weight:bold; color:#AAA; text-align:right;'>From:</td>
				<td><?=$info['chrEmailName']?></td>
			<tr>
			</tr>
				<td style='width:50px; font-weight:bold; color:#AAA; text-align:right;'>Subject:</td>
				<td><?=$email['chrSubject']?></td>
			<tr>
			</tr>
				<td style='width:50px; font-weight:bold; color:#AAA; text-align:right;'>Date:</td>
				<td><?=date('F j, Y g:i a')?></td>
			<tr>
			</tr>
				<td style='width:50px; font-weight:bold; color:#AAA; text-align:right;'>To:</td>
				<td><?=$_SESSION['chrFirst'].' '.$_SESSION['chrLast']?></td>
			</tr>
		</table>
		<hr />
		<div><?=decode($email['txtBody'])?></div>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>
