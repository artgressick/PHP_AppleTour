<?php
// INSERT PROJECT NAME HERE
	session_name('appleappevents');
	session_start();

	require_once "Spreadsheet/Excel/Writer.php";
	
	include('appleappevents-conf.php');
	
//	$mysqli_connection = mysqli_connect($host, $user, $pass);

//  mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
//	$result = mysqli_query($mysqli_connection, $_SESSION['excel']);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	
	
	$workbook->send('Daily_Statistics_Report('. $time .').xls');	
	
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
	$worksheet =& $workbook->addWorksheet('Daily Report('. $time .')');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;
	
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Date', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Sign-ups to Date', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Waitlisters to Date', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Cancels to Date', $format_column_header);
	$column_num++;	
	
	$row_num++;
	
	$totalsignups=0;
	$totalcancel=0;
	$totalwaitlisters=0;
	
	$dates = $_SESSION['excel'];

		foreach ($dates as $k => $v) {
	
		$totalsignups += $dates[$k][1];
		$totalwaitlisters += $dates[$k][2];
		$totalcancel += $dates[$k][3];
		
		$column_num = 0;
	
		$worksheet->write($row_num, $column_num, date('M j, Y (l)',strtotime($k)), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($dates[$k][1]), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($dates[$k][2]), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, number_format($dates[$k][3]), $format_data);
		$column_num++;		
		$row_num++;
	}
	$column_num = 0;
	$row_num++;
	$worksheet->write($row_num, $column_num, 'Totals:', $format_column_header);
	$column_num++;
	$worksheet->write($row_num, $column_num, number_format($totalsignups), $format_column_header);
	$column_num++;	
	$worksheet->write($row_num, $column_num, number_format($totalwaitlisters), $format_column_header);
	$column_num++;	
	$worksheet->write($row_num, $column_num, number_format($totalcancel), $format_column_header);
	$column_num++;	
	
	$row_num++;	
	

$workbook->close();

$_SESSION['excel'] = array();
	
?>
