<?php
	include('includes/top.php');
?>
<!-- This is the main body of the page.-->
<div class="main">
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="50" colspan="3"><img src="images/header2007.gif" width="390" height="40" /></td>
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
        <td width="100%"><form id="form1" name="form1" method="post" action="thankyou.php">
			<div class="maintitle">Choose a road show.</div>
			<div class="maintitletext">Here are a list of road shows that are currently available. Please remember to check back as new tours will be avilable and some dates may change.</div>
            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="roadshow_listing">
				<tr>
                    <th>&nbsp;</th>
                    <th>Pro Application </th>
                    <th>City</th>
                    <th>Date</th>
                    <th>Status</th>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Shake</td>
                    <td nowrap="nowrap">Reston, Virgina </td>
                    <td nowrap="nowrap">April 17th, 2007 </td>
                    <td nowrap="nowrap">Waitlist</td>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Final Cut Studio </td>
                    <td nowrap="nowrap">Washington, DC </td>
                    <td nowrap="nowrap">April 27th, 2007 </td>
                    <td nowrap="nowrap">Open</td>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Logic</td>
                    <td nowrap="nowrap">Cincinnati, Ohio </td>
                    <td nowrap="nowrap">May 7th, 2007 </td>
                    <td nowrap="nowrap">Open</td>
				</tr>
				<tr>
                    <td align="center">&nbsp;</td>
                    <td nowrap="nowrap">Aperture</td>
                    <td nowrap="nowrap">Santa Monica , California </td>
                    <td nowrap="nowrap">June 17th, 2007 </td>
                    <td nowrap="nowrap">Open</td>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Aperture</td>
                    <td nowrap="nowrap">Columbus, Ohio </td>
                    <td nowrap="nowrap">June 17th, 2007 </td>
                    <td nowrap="nowrap">Waitlist</td>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Final Cut Pro </td>
                    <td nowrap="nowrap">Cupertino, California </td>
                    <td nowrap="nowrap">June 19th, 2007 </td>
                    <td nowrap="nowrap">Waitlist</td>
				</tr>
				<tr>
                    <td align="center">&nbsp;</td>
                    <td nowrap="nowrap">Aperture</td>
                    <td nowrap="nowrap">Santa Monica , California </td>
                    <td nowrap="nowrap">June 17th, 2007 </td>
                    <td nowrap="nowrap">Open</td>
				</tr>
				<tr>
                    <td align="center"><input type="checkbox" name="checkbox" value="checkbox" /></td>
                    <td nowrap="nowrap">Aperture</td>
                    <td nowrap="nowrap">Columbus, Ohio </td>
                    <td nowrap="nowrap">June 17th, 2007 </td>
                    <td nowrap="nowrap">Waitlist</td>
				</tr>
            </table>
			<div><input name="Submit" type="submit" class="button" value="Signup for these classes and send me an email" />
			</div>
            
        </form>
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
<?php
	include('includes/bottom.php');
?>
</body>
</html>
