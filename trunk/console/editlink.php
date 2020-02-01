<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Link';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "links";		 // This is needed to highlight the show section
	require($BF. '_lib.php');

	if(@$_POST['moveTo'] != "" ) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "UPDATE Links SET 
			 chrName='".	encode($_POST['chrName']) ."',
			 idSeries='". 	$_POST['idSeries'] ."',
			 chrEvents='".	implode(",", $_POST['event']) ."'
			 WHERE ID=".$_POST['id'];
		database_query($q,"Insert Link");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=2, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['chrName']) ."',
			dtDateTime=now(),
			chrTableName='Links',
			idUser='". $_SESSION['idUser'] ."'
		";
		database_query($q,"Insert audit");
		//End the code for History Insert
		
		header("Location: ". $_POST['moveTo']);
		die();
	}
	include($BF. 'components/list/sortList.php'); 

	$q = "SELECT *
			FROM Links
			WHERE ID='".$_REQUEST['id']."'";
	$info = fetch_database_query($q,"Getting Link Information");

	$info['chrEvents'] = explode(',',$info['chrEvents']);

	if (@$_POST['idSeries'] != "") {
			$Events = database_query("
			SELECT Events.ID, chrName, tBegin, tEnd, dDate 
			FROM Events 
			JOIN EventTitles ON EventTitles.ID = Events.idEventTitle
			WHERE !Events.bDeleted AND idEventSeries = ".$_POST['idSeries'] ."
			ORDER BY dDate, tBegin","getting Events info");
		} ELSE { 
		$Events = database_query("
			SELECT Events.ID, chrName, tBegin, tEnd, dDate 
			FROM Events 
			JOIN EventTitles ON EventTitles.ID = Events.idEventTitle
			WHERE !Events.bDeleted AND idEventSeries = ". $info['idSeries'] ."
			ORDER BY dDate, tBegin","getting Events info");
		}
		

	$q = "SELECT ID, chrTitle
			FROM EventSeries 
			WHERE !EventSeries.bDeleted			
		    ORDER BY chrTitle";	
	$Series = database_query($q,"Getting all Event Series");


	include($BF. 'includes/meta_admin.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = "document.getElementById('chrName').focus()";
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Name.");
		total += ErrorCheck('idSeries', "You must Select at least 1 Event.");		

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'includes/top_admin.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Edit Link</td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>
<form name='idForm' id='idForm' action='' method="post">
<div class='instructions'>Fill out items and click submit.</div>
	<div id='errors'></div>
	<div class='innerbody'>
		<table id='twoCol' class='twoCol' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>			
				<td class="left" style="width:50%;">
					<div class='FormName'>Link Description <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrName' id='chrName' size="50" maxlength="200" value="<?=$info['chrName']?>" /></div>
					<div class='FormName'>Event Series <span class='Required'>(Required)</span></div>
						<select class='FormField' id="idSeries" name='idSeries' style="width:300px;"  onchange='document.getElementById("idForm").submit();' >
							<option value="">Select Series</option>
						<? while ($row = mysqli_fetch_assoc($Series)) { ?>
							<option value='<?=$row['ID']?>'<?=($info['idSeries'] == $row['ID'] ? 'selected="selected"' : '')?>><?=$row['chrTitle']?></option>
						<?	} ?>
						</select>					
				</td>
				<td class="right" style="width:50%;">
				<div class='FormName'>Select Event(s) for this Link <span class='Required'>(Required)</span></div>
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>			
							<th style="width:5px;">&nbsp;</th>
							<th>Name</th>
							<th>Date</th>
							<th>Time Begin</th>
							<th>Time End</th>							
						</tr>
				<? $count=0;	
				while ($row = mysqli_fetch_assoc($Events)) { ?>
							<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 	onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
								<td style="width:5px; vertical-align:middle;"><input type="checkbox" id="event<?=$row['ID']?>" name="event[]" value="<?=$row['ID']?>" <?=(in_array($row['ID'], $info['chrEvents']) ? 'checked="checked"' : "")?> /></td>
								<td style='cursor: pointer;' onclick='FieldClick("event",<?=$row['ID']?>)'><?=decode($row['chrName'])?></td>
								<td style='cursor: pointer;' onclick='FieldClick("event",<?=$row['ID']?>)'><?=date('M j Y',strtotime($row['dDate']))?></td>
								<td style='cursor: pointer;' onclick='FieldClick("event",<?=$row['ID']?>)'><?=date('g:i a',strtotime($row['tBegin']))?></td>	
								<td style='cursor: pointer;' onclick='FieldClick("event",<?=$row['ID']?>)'><?=date('g:i a',strtotime($row['tEnd']))?></td>											
							</tr>
				<?	} 
				if($count == 0) { ?>
							<tr>
								<td align="center" colspan='6'>No Event Titles to display</td>
							</tr>
				<?	} ?>
					</table>
					<input type='button' value='Select All' onclick='SelectALL("event")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("event")' />				
				</td>
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Edit Link And Add New' onclick="document.getElementById('moveTo').value='addlink.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Edit And Move On' onclick="document.getElementById('moveTo').value='links.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />
		<input type='hidden' name='id' id='id' value="<?=$_REQUEST['id']?>" />		
	</div>
</form>
<?
	include($BF. 'includes/bottom_admin.php');
?>
