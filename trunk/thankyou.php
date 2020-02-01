<?php
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');

	$temp = fetch_database_query("SELECT chrImageName, chrTitle, txtThankYou
									FROM EventSeries
									WHERE !bDeleted AND ID='".$_SESSION['idEventSeries']."'", "Getting EventSeries");

	$_SESSION['chrTitle'] = $temp['chrTitle'];
	
	//First Grab User Information
	$q = "SELECT chrFirst, chrLast, chrEmail
			FROM Attendees
			WHERE Attendees.ID='".$_SESSION['User']."'";
	$userinfo = fetch_database_query($q,"Getting User Information");
	
	$temp['txtThankYou'] = str_replace('$FIRST_NAME',encode($userinfo['chrFirst']),$temp['txtThankYou']); 
	$temp['txtThankYou'] = str_replace('$LAST_NAME',encode($userinfo['chrLast']),$temp['txtThankYou']);
	$temp['txtThankYou'] = str_replace('$EMAIL',encode($userinfo['chrEmail']),$temp['txtThankYou']);
	$temp['txtThankYou'] = str_replace('$SERIES_TITLE',encode($temp['chrTitle']),$temp['txtThankYou']);
	
	include('includes/top.php');
?>
<!-- This is the main body of the page.-->
<div class="main">
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="50" colspan="3"><img src="<?=$PROJECT_ADDRESS.'images/'.$temp['chrImageName']?>" /></td>
  </tr>
  <tr>
    <td width="7" height="7"><img src="images/corner_top_left.gif" width="7" height="7" /></td>
    <td width="786" height="7" background="images/line_top.gif"><img src="images/line_top.gif" width="7" height="7" /></td>
    <td width="7" height="7"><img src="images/corner_top_right.gif" width="7" height="7" /></td>
  </tr>
  <tr>
    <td width="7" background="images/line_left.gif"><img src="images/line_left.gif" width="7" height="7" /></td>
    <td width="786" bgcolor="#ebebeb"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%; text-align:top;">
			<div style='color:black;'><?=decode($temp['txtThankYou'])?></div>
		</td>
    </tr>
    </table></td>
    <td width="7" background="images/line_right.gif"><img src="images/line_right.gif" width="7" height="7" /></td>
  </tr>
  <tr>
    <td width="7" height="7"><img src="images/corner_bottom_left.gif" width="7" height="7" /></td>
    <td width="786" height="7" background="images/line_bottom.gif"><img src="images/line_bottom.gif" width="7" height="7" /></td>
    <td width="7" height="7"><img src="images/corner_bottom_right.gif" width="7" height="7" /></td>
  </tr>
</table>
</div>
<!-- This is the bottom of the body -->
<?
	include('includes/bottom.php');
?>