<?	
	$BF = '../';
	$title = 'Attendees';
	require($BF. '_lib.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast"; }
	
	if (!isset($_REQUEST['id'])) { 
		$tmp = fetch_database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","get first series");			
		$_REQUEST['id'] = $tmp['ID'];
		$_SESSION['chrTitle'] = $tmp['chrTitle'];
	}
	
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ''; }
	if(!isset($_REQUEST['chrLimitTo']) && $_REQUEST['chrSearch'] != '') { $_REQUEST['chrLimitTo'] = ''; }
	if(!isset($_REQUEST['chrLimitTo'])) { $_REQUEST['chrLimitTo'] = 'A'; }
	if($_REQUEST['chrLimitTo'] >= 'A' && $_REQUEST['chrLimitTo'] <= 'Z') {
		$where = " WHERE !Attendees.bDeleted AND chrLast LIKE '" . $_REQUEST['chrLimitTo'] . "%' AND !Events.bDeleted  AND Events.idEventSeries=".$_REQUEST['id'];
	} else {
		$where = " WHERE !Attendees.bDeleted AND chrLast NOT BETWEEN 'A' AND 'Z' AND !Events.bDeleted AND Events.idEventSeries='".$_REQUEST['id']."'";
	}
	
	$q = "SELECT DISTINCT Attendees.ID,Attendees.chrFirst,Attendees.chrLast,Attendees.chrPhone,Attendees.chrEmail, CONCAT(A2.chrFirst,' ',A2.chrLast) as chrRefer
			FROM Attendees
			JOIN Signups ON Attendees.ID=Signups.idUser
			JOIN Events ON Events.ID=Signups.idEvent
			LEFT JOIN Attendees as A2 ON Attendees.idRefer=A2.ID
			";
			if($_REQUEST['chrSearch'] != '') {
				$searchs = split(' ',$_REQUEST['chrSearch']);
				if(count($searchs > 1)) {
					$q .= " WHERE !Attendees.bDeleted AND ";
					$cnt = 0;
					foreach($searchs as $k) {
						$q .= ($cnt++ > 0 ? ' AND ' : '')."(Attendees.chrFirst LIKE '%" . encode($k) . "%' OR 
							Attendees.chrLast LIKE '%" . encode($k) . "%' OR
							Attendees.chrPhone LIKE '%" . encode($k) . "%' OR
							Attendees.chrEmail LIKE '%" . encode($k) . "%')";
					}
					$q .=  " AND !Events.bDeleted AND Events.idEventSeries=".$_REQUEST['id'];
				} else {
					$q .= " WHERE (Attendees.chrFirst LIKE '%" . encode($_REQUEST['chrSearch']) . "%' OR 
						Attendees.chrLast LIKE '%" . encode($_REQUEST['chrSearch']) . "%' OR
						Attendees.chrPhone LIKE '%" . encode($_REQUEST['chrSearch']) . "%' OR
						Attendees.chrEmail LIKE '%" . encode($_REQUEST['chrSearch']) . "%') AND !Events.bDeleted AND Events.idEventSeries='".$_REQUEST['id']."'";
				}
			} else {
				$q .= "WHERE !Attendees.bDeleted AND Attendees.chrLast LIKE '" . $_REQUEST['chrLimitTo'] . "%' AND !Events.bDeleted AND Events.idEventSeries='".$_REQUEST['id']."'";
			}
		$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];

		$result = database_query($q,"Getting all Attendees");

	$active = 'admin';
	$subactive = 'attendees';
	include($BF. 'includes/meta_admin.php');	
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?

	//Load Drop Down Menus
	$eventseries = database_query("SELECT ID, chrTitle FROM EventSeries WHERE !bDeleted ORDER BY chrTitle","getting Event Series info");


	include($BF. 'includes/top_admin.php');
	
	//This is the include file for the overlay
	$TableName = "Attendees";
	include($BF. 'includes/overlay.php');
?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
			<td class="title" style="width:80px;">Attendees for</td>
		<td class="title_right" style="vertical-align:bottom; text-align:left;"><select class='FormField' id="idEventSeries" name='idEventSeries' onchange='location.href="attendees.php?id="+this.value'>
											<? while ($row = mysqli_fetch_assoc($eventseries)) { ?>
												<option value='<?=$row['ID']?>'<?=($row['ID'] == $_REQUEST['id'] ? ' selected="selected"' : "" )?>><?=$row['chrTitle']?></option>
											<?	} ?>
								</select></td>
		<td class="title_right"></td>
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Select a Attendee from the list below to view/edit.</div>

<div class='innerbody'>
 	<table class='Tabs TightTabs Serif' cellspacing="0" cellpadding="0">
		<tr>
<?	for($aloop = ord('A'); $aloop <= ord('Z'); $aloop++) { ?>
                <td<?=($_REQUEST['chrLimitTo'] == chr($aloop) ? ' class="Current"' : '')?>><a href="?chrLimitTo=<?=chr($aloop)?>&id=<?=$_REQUEST['id']?>"><?=chr($aloop)?></a></td>
<?	} ?>

            <td<?=($_REQUEST['chrLimitTo'] == 'other' ? ' class="Current"' : '')?>><a href="?chrLimitTo=other&id=<?=$_REQUEST['id']?>">Other</a></td>
			<td class='TheRest' style='text-align: right;'>
				<form id="search" method="get" action="">
					Search <input type="search" name="chrSearch" placeholder="Search Users" value='<?=$_REQUEST['chrSearch']?>' />
					<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />
					<input type='submit' name='Go' value='Go' />
				</form>
			</td>
			</tr>
		</table>

	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>		
			<th></th>	
			<? sortList('First Name', 'chrFirst','','chrSearch='.$_REQUEST['chrSearch'].'&chrLimitTo='.$_REQUEST['chrLimitTo'].'&id='.$_REQUEST['id']); ?>
			<? sortList('Last Name', 'chrLast','','chrSearch='.$_REQUEST['chrSearch'].'&chrLimitTo='.$_REQUEST['chrLimitTo'].'&id='.$_REQUEST['id']); ?>
			<? sortList('Phone Number', 'chrPhone','','chrSearch='.$_REQUEST['chrSearch'].'&chrLimitTo='.$_REQUEST['chrLimitTo'].'&id='.$_REQUEST['id']); ?>
			<? sortList('E-mail Address', 'chrEmail','','chrSearch='.$_REQUEST['chrSearch'].'&chrLimitTo='.$_REQUEST['chrLimitTo'].'&id='.$_REQUEST['id']); ?>
<?
		if($_REQUEST['id'] == 3) {
			sortList('Linked to:', 'chrRefer','','chrSearch='.$_REQUEST['chrSearch'].'&chrLimitTo='.$_REQUEST['chrLimitTo'].'&id='.$_REQUEST['id']);
		}
?>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>
<? $count=0;	
while ($row = mysqli_fetch_assoc($result)) { 
$link='location.href="editattendee.php?id='.$row['ID'].'&idEventSeries='.$_REQUEST['id'].'"';
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td><img src='<?=$BF?>images/profile-gray.gif' width='14' height='12' alt='Events Signed Up For' /></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrFirst']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLast']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrPhone']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
<?
		if($_REQUEST['id'] == 3) {
?>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrRefer']?></td>
<?			
		}
?>

				<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
				<a href="javascript:warning(<?=$row['ID']?>,'<?=jsencode($row['chrFirst'])?> <?=jsencode($row['chrLast'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
				</div></td>	
			</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Attendees to display</td>
			</tr>
<?	} ?>
	</table>

	</div>
<?
	include($BF. 'includes/bottom_admin.php');
?>
