<?	
	$BF = '../';
	$title = 'Process Mass Move';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	

	$active = 'admin';
	$subactive = 'massmove';	
	
include($BF. 'includes/top_admin.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" nowrap="nowrap">Processing Mass Move</td>
		<td class="title_right" style="text-align:right;">
            
		</td>                
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Please see log Below.</div>
<?
	$attendees = database_query("SELECT S.ID as idSignup, A.ID AS idUser, A.chrFirst, A.chrLast, A.chrEmail, S.chrRegLead
									FROM Signups AS S
									JOIN Attendees AS A ON S.idUser=A.ID
									WHERE S.idEvent='".$_REQUEST['idFrom']."' AND S.idStatus='2' AND !A.bDeleted","Getting User List");
?>

<div class='innerbody'>
	<div>Starting Mass Move.... <?=mysqli_num_rows($attendees)?> Record(s) to Process.</div>
	<div id="emaillog"></div>
</div>
<iframe 
<?
/*
src="<?=$BF?>console/_mass_move.php?idFrom=<?=$_REQUEST['idFrom']?>&idFromStatus=<?=$_REQUEST['idFromStatus']?>&idTo=<?=$_REQUEST['idTo']?>&idToStatus=<?=$_REQUEST['idToStatus']?>"
*/
?>
src="<?=$BF?>console/_mass_move.php?idFrom=<?=$_REQUEST['idFrom']?>&idTo=<?=$_REQUEST['idTo']?>"
width="1" height="1" style="border:none;" frameborder="0">
</iframe>
<?
	include($BF. 'includes/bottom_admin.php');