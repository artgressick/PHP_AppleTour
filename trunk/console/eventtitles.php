<?	
	$BF = '../';
	$title = 'Event Titles';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	$q = "SELECT ID,chrName
			FROM EventTitles
			WHERE !bDeleted			
		    ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all sessions");

	$active = 'admin';
	$subactive = 'eventtitles';

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "EventTitles";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Event Titles</td>
		<td class="title_right"><a href="addeventtitle.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Select a event title from the list below to view/edit.</div>

<div class='innerbody'>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Name', 'chrName'); ?>
	
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editeventtitle.php?id=<?=$row['ID']?>";'><?=$row['chrName']?></td>
				<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
				<a href="javascript:warning(<?=$row['ID']?>,'<?=jsencode($row['chrName'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
				</div></td>		
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Event Titles to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>
