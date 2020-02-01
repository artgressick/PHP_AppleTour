<?php
	$BF = "../";
	$auth_not_required = 1;
	require($BF. '_lib.php');

	include($BF.'includes/top.php');
	
	//Decode L
	parse_str(base64_decode($_REQUEST['L']),$info); //Grabs the $Special code as it will be unique per signup

?>
<!-- This is the main body of the page.-->
<div class="main">
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="50" colspan="3"><?=(isset($_SESSION['chrLogo']) && $_SESSION['chrLogo'] != "" ? "<img src='".$BF."images/".$_SESSION['chrLogo']."' />" : "" )?></td>
  </tr>
  <tr>
    <td width="7" height="7"><img src="<?=$BF?>images/corner_top_left.gif" width="7" height="7" /></td>
    <td width="786" height="7" background="<?=$BF?>images/line_top.gif"><img src="<?=$BF?>images/line_top.gif" width="7" height="7" /></td>
    <td width="7" height="7"><img src="<?=$BF?>images/corner_top_right.gif" width="7" height="7" /></td>
  </tr>
  <tr>
    <td width="7" background="<?=$BF?>images/line_left.gif"><img src="<?=$BF?>images/line_left.gif" width="7" height="7" /></td>
    <td width="786" bgcolor="#3F3F3F"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><form id="form1" name="form1" method="post" action="thankyou.php">
			<div class="maintitle">Check-in Confirmation.</div>

			<div class="maintitletext">Thank you <?=$info['chrFirst']?> <?=$info['chrLast']?>, you have successfully checked-in for this event. You will receive an e-mail confirmation shortly. If you cannot attend any of the events you Checked-in for, please refer to the email to cancel your reservation. Instructions are contained in the email to update your status.  Thank you and looking forward to a great event. 
			Please <a href = "index.php?R=1">Click Here</a> to return to the main checkin page.</div>
        </form>
</td>

    </tr>
    </table></td>
    <td width="7" background="<?=$BF?>images/line_right.gif"><img src="<?=$BF?>images/line_right.gif" width="7" height="7" /></td>
  </tr>
  <tr>
    <td width="7" height="7"><img src="<?=$BF?>images/corner_bottom_left.gif" width="7" height="7" /></td>
    <td width="786" height="7" background="<?=$BF?>images/line_bottom.gif"><img src="<?=$BF?>images/line_bottom.gif" width="7" height="7" /></td>
    <td width="7" height="7"><img src="<?=$BF?>images/corner_bottom_right.gif" width="7" height="7" /></td>
  </tr>
</table>
</div>
<!-- This is the bottom of the body -->
<?php
	include($BF.'includes/bottom.php');
?>