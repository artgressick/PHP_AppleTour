<?	
	$BF = '../';
	$title = 'links';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName, chrTitle"; }
	
	$q = "SELECT Links.ID, chrName, chrSpecial, chrTitle
			FROM Links
			JOIN EventSeries ON EventSeries.ID=Links.idSeries
			WHERE !Links.bDeleted			
		    ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all sessions");

	$active = 'admin';
	$subactive = 'links';

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "Links";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Links</td>
		<td class="title_right"><a href="addlink.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'><table style="width:100%;"><tr><td>Select a Link from the list below to view/edit.</td><td style="text-align:right;">Main Link: <strong>http://protour.techitweb.com/index.php</strong></td></tr></table></div>

<div class='innerbody'>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Name', 'chrName'); ?>
			<? sortList('Series Name', 'chrTitle'); ?>
			<th>Invite Code</th>		
			<th>Link</th>
			<th>Test Link</th>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) {
$code = base64_encode('L='.$row['ID'].'&K='.$row['chrSpecial']);

?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editlink.php?id=<?=$row['ID']?>";'><?=decode($row['chrName'])?></td>
				<td style='cursor: pointer;' onclick='location.href="editlink.php?id=<?=$row['ID']?>";'><?=decode($row['chrTitle'])?></td>
				<td><input type="text" size="15" value="<?=$code?>" /></td>
				<td><input type="text" size="50" value="http://protour.techitweb.com/index.php?ic=<?=$code?>" /></td>
				<td><a href="http://protour.techitweb.com/index.php?ic=<?=$code?>" target="_blank">Link</a></td>				
				<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
				<a href="javascript:warning(<?=$row['ID']?>,'<?=jsencode($row['chrName'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
				</div></td>		
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Links to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>
