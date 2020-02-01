<?php
// INSERT PROJECT NAME HERE
	session_name('appleappevents');
	session_start();

	require_once "Spreadsheet/Excel/Writer.php";
	
	include('appleappevents-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$result = mysqli_query($mysqli_connection, $_SESSION['excel']);

		// Lets pull all the dates for this Event
	$q = "SELECT EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd 
	FROM Events 
	JOIN EventDates ON EventDates.idEvent=Events.ID 
	WHERE Events.idEventSeries=".$_REQUEST['id']." 
	ORDER BY EventDates.idEvent, EventDates.dDate, EventDates.tBegin, EventDates.tEnd";
	
	$dateresults = mysqli_query($mysqli_connection, $q);
	$eventDates = array();
	$fullDates = array();
	$prevID = 0;
	$prevDate = "";
	$day = 0;
	while ($rowdates = mysqli_fetch_assoc($dateresults)) {

		if($prevID != $rowdates['idEvent']) { 
			if($prevID != 0) { $eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate)); }
			$day = 1;
			$prevID = $rowdates['idEvent'];
			$eventDates['chrDates'.$rowdates['idEvent']] = "";
			$fullDates[$rowdates['idEvent']] = '';
			$prevDate = "";
		}
			if($prevDate != date('F',strtotime($rowdates['dDate']))) {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('F',strtotime($rowdates['dDate']))." ".date('jS',strtotime($rowdates['dDate']));
			} else {
				if($day != 1) { $eventDates['chrDates'.$rowdates['idEvent']] .= ", "; }
				$eventDates['chrDates'.$rowdates['idEvent']] .= date('jS',strtotime($rowdates['dDate']));
			}
			
		$prevDate = date('F',strtotime($rowdates['dDate']));
		$fullDates[$rowdates['idEvent']] .= date('l, F jS, Y',strtotime($rowdates['dDate'])).' from '.date('g:i a',strtotime($rowdates['tBegin'])).' to '.date('g:i a',strtotime($rowdates['tEnd'])).'<br />';
		$day++;
		
	}
	$eventDates['chrDates'.$prevID] .= " ".date('Y',strtotime($prevDate));
	
	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	
	$filename  = str_replace(" ", "_", $_SESSION['chrTitle'] );
	
	$workbook->send($filename .'_Report('. $time .').xls');	
	
	// create format for column headers
	$format_column_header =& $workbook->addFormat();
	$format_column_header->setBold();
	$format_column_header->setSize(10);
	$format_column_header->setAlign('left');
	
	// create data format
	$format_data =& $workbook->addFormat();
	$format_data->setSize(10);
	$format_data->setAlign('left');
	
	
function decode($val) {
	$val = str_replace('&quot;','"',$val);
	$val = str_replace("&apos;","'",$val);
	return $val;
}

	
	// Create worksheet
	$worksheet =& $workbook->addWorksheet('Report('. $time .')');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	$worksheet->setColumn($column_num, $column_num, 60);
	$worksheet->write($row_num, $column_num, 'Event Name', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Venue Location', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Date(s)', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Begin Time', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'End Time', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Capacity', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Drop Off Rate', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Total Capacity', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Sign-ups to Date', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Sign-ups to Capacity', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Waitlisters to Date', $format_column_header);
	$column_num++;	
	
	$row_num++;
	
	$totalsignups=0;
	$totalcapacity=0;
	$totalwaitlisters=0;
	$totaltotalcapacity=0;
	
	while($row = mysqli_fetch_assoc($result)) {
	
		$totalsignups = $totalsignups + $row['intSignups'];
		$totalwaitlisters = $totalwaitlisters + $row['intWaitlisters'];
		$totalcapacity = $totalcapacity + $row['intCapacity'];
		$totaltotalcapacity = $totaltotalcapacity + $row['intTotalCap'];
		
		$column_num = 0;
	
		$worksheet->write($row_num, $column_num, decode($row['chrName']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrVenue']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $eventDates['chrDates'.$row['ID']], $format_data);
		$column_num++;			
		$worksheet->write($row_num, $column_num, date('g:i a',strtotime($row['tBegin'])), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, date('g:i a',strtotime($row['tEnd'])), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($row['intCapacity']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['intDropOff']."%", $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, number_format($row['intTotalCap']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($row['intSignups']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, round($row['intSignups'] / $row['intCapacity'] * 100).'%', $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($row['intWaitlisters']), $format_data);
		$column_num++;		
		$row_num++;
	}
	$column_num = 0;
	$row_num++;
	$worksheet->setColumn($column_num, $column_num, 60);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Totals:', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, number_format($totalcapacity), $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, '', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, number_format($totaltotalcapacity).' ('.round($totalsignups/$totaltotalcapacity*100).'%)', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, number_format($totalsignups), $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, round($totalsignups/$totalcapacity*100).'%', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, number_format($totalwaitlisters), $format_column_header);
	$column_num++;	
	
	$row_num++;	
	

$workbook->close();
	
?>
