<?
	require('appleappevents-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if($_REQUEST['postType'] == "delete") {
		$total = 0;
		$q = "UPDATE ". $_REQUEST['tbl'] ." SET bDeleted=1 WHERE ID=".$_REQUEST['id'];
		if(mysql_query($q)) { 
			if ($_REQUEST['tbl'] == "Attendees") { 
				mysql_query("DELETE FROM Signups WHERE idUser=".$_REQUEST['id']);		
				}
			$total++;
		}

		$q = "INSERT INTO Audit SET idUser=".$_REQUEST['idUser'].", idRecord=".$_REQUEST['id'].", chrTableName='". $_REQUEST['tbl'] ."', chrColumnName='bDeleted', dtDatetime=now(), txtOldValue='0', txtNewValue='1', idType=3"; 
		if(mysql_query($q)) { $total += 2; }
  		echo $total;
	}
?>
