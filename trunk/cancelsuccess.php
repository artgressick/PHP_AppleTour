<?
	$BF = "";
	$auth_not_required = 1;
	require($BF. '_lib.php');
	include('includes/top.php');
	
	(isset($_REQUEST['L']) ? $L = $_REQUEST['L'] : $L = "");
	($L != "" ? parse_str(base64_decode($L),$info) : "");
	(isset($info['idEventSeries']) ? $idEventSeries = $info['idEventSeries'] : $idEventSeries = "");

	if($idEventSeries != "") {
		$temp = fetch_database_query("SELECT EventSeries.ID, Referral.chrLogo, EventSeries.chrTitle
										FROM EventSeries
										JOIN Referral ON EventSeries.idReferral=Referral.ID
										WHERE !EventSeries.bDeleted AND !Referral.bDeleted AND EventSeries.ID=".$info['idEventSeries'], "Getting EventSeries and Referral Information");

		$chrLogo = $temp['chrLogo'];
		$_SESSION['chrTitle'] = $temp['chrTitle'];
	} else {
		$chrLogo = "";
	}

?>
<!-- This is the main body of the page.-->
<div class="main">
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="50" colspan="3"><?=($chrLogo != "" ? "<img src='".$chrLogo."' />" : "&nbsp;")?></td>
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
        <td width="100%">
			<div class="maintitle">Cancel Registration Success.</div>
			<div class="maintitletext">You have Successfully Cancelled your Registration.<br /><i>(If you did not want to do this you can sign up again, however your status can not be guaranteed)</i></div>
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
