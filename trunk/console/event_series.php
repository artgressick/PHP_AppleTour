<?	
	$BF = '../';
	$title = 'Event Series';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrTitle"; }
	
	$q = "SELECT EventSeries.ID, chrTitle, intLink, intHit
			FROM EventSeries 
			WHERE !EventSeries.bDeleted			
		    ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all sessions");

	$active = 'admin';
	$subactive = 'eventseries';

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "EventSeries";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Event Series</td>
		<td class="title_right"><a href="addevent_series.php"><img src="<?=$BF?>images/plus_add.gif"border="0" alt="Add Event Series" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Select a Session from the list below to view/edit.</div>

<div class='innerbody'>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Event Series Name', 'chrTitle'); ?>
			<th>Link to Series</th>
			<th>Thank you Page</th>
			<th>E-mail Setup</th>
			<th>Hits</th>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editevent_series.php?id=<?=$row['ID']?>";'><?=$row['chrTitle']?></td>
				<td style='cursor: normal;'><input type='text' style='width:450px' value='<?=$PROJECT_ADDRESS?>?Series=<?=$row['intLink']?>' /></td>
				<td style='cursor: pointer;' onclick='location.href="thankyoupage.php?id=<?=$row['ID']?>";'>Thank you Page</td>
				<td style='cursor: pointer;' onclick='location.href="email_setup.php?id=<?=$row['ID']?>";'>Setup E-mail</td>
				<td style='cursor: pointer;' onclick='location.href="editevent_series.php?id=<?=$row['ID']?>";'><?=$row['intHit']?></td>
				<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
				<a href="javascript:warning(<?=$row['ID']?>,'<?=jsencode($row['chrTitle'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
				</div></td>		
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Sessions to display</td>
			</tr>
<?	} ?>
	</table>
	<div style='padding:10px;'><strong>PLEASE NOTE:</strong> If you would like to have have options checked by default add the following to the link above:<br />
	&idCheck=0&nbsp;&nbsp;&nbsp;&nbsp;-- adding this will check all sessions<br />
	&idCheck=1&nbsp;&nbsp;&nbsp;&nbsp;-- adding this will check the first session listed<br />
	&idCheck=2&nbsp;&nbsp;&nbsp;&nbsp;-- adding this will check the second session listed<br /><br />
	Link should look like this:<br />
	<?=$PROJECT_ADDRESS?>?Series=234624<strong>&idCheck=1</strong>
	</div>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>
