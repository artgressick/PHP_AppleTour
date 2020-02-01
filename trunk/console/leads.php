<?	
	$BF = '../';
	$title = 'Registration Leads';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLead"; }
	
	$q = "SELECT ID,chrLead, chrCode, intHits, (SELECT COUNT(ID) FROM Signups WHERE chrRegLead=RegLeads.chrCode) AS intSignups,
			(SELECT COUNT(ID) FROM Signups WHERE chrRegLead=RegLeads.chrCode AND idStatus=1) AS intConfirmed,
			(SELECT COUNT(ID) FROM Signups WHERE chrRegLead=RegLeads.chrCode AND idStatus=2) AS intWaitListed,
			(SELECT COUNT(ID) FROM Signups WHERE chrRegLead=RegLeads.chrCode AND idStatus=3) AS intCancelled
			FROM RegLeads
			WHERE !bDeleted			
		    ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	
	$result = database_query($q,"Getting all Leads");

	$active = 'admin';
	$subactive = 'leads';

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "RegLeads";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Registration Leads</td>
		<td class="title_right"><a href="addlead.php"><img src="<?=$BF?>images/plus_add.gif"border="0" /></a></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Select a Registration Lead from the list below to view/edit.<br />To use a lead add the code text to the end of the Event Series URL. ie. (http://appleappevents.techitweb.com/?Series=9283475<b>&C=VXXSPBG</b>)</div>

<div class='innerbody'>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>			
			<? sortList('Lead Name', 'chrLead'); ?>
			<th>Code</th>
			<? sortList('Hits', 'intHits'); ?>
			<? sortList('Total Signups', 'intSignups'); ?>
			<? sortList('Confirmed', 'intConfirmed'); ?>
			<? sortList('Wait-listed', 'intWaitListed'); ?>
			<? sortList('Cancelled', 'intCancelled'); ?>
			
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['chrLead']?></td>
				<td style='cursor: auto;'><input type='text' value='&amp;C=<?=$row['chrCode']?>' style='width:150px;' /></td>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['intHits']?></td>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['intSignups']?></td>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['intConfirmed']?></td>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['intWaitListed']?></td>
				<td style='cursor: pointer;' onclick='location.href="editlead.php?id=<?=$row['ID']?>";'><?=$row['intCancelled']?></td>
				
				<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
				<a href="javascript:warning(<?=$row['ID']?>,'<?=jsencode($row['chrLead'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
				</div></td>		
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6' style='height:20px;'>No Registration Leads to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>