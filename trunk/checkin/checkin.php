<?php
	$BF = "../";
	$auth_not_required = 1;
	require($BF. '_lib.php');

//	parse_str(base64_decode($_REQUEST['L']),$info);
	$error_message = array();
	
	include($BF. 'components/list/sortList.php'); 
	include($BF. 'components/states.php');	
	include($BF. 'components/countries.php');	
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?	
			

// checks to see if user submitted data from page, if not display registration page
if (isset($_POST['submit']) == "Check In") {
	
	$error_message = array();
	// Did the User enter all required information?
	if ($_POST['chrFirst'] == "" ) { field_blank("chrFirst"); }
	if ($_POST['chrLast'] == "") { field_blank("chrLast"); }
if($_SESSION['idEventSeries'] != '3') {
	if ($_POST['chrAddress'] == "") { field_blank("chrAddress"); }
	if ($_POST['chrCity'] == "") { field_blank("chrCity"); }
	if ($_POST['chrState'] == "") { field_blank("chrState"); }
	if ($_POST['chrZip'] == "") { field_blank("chrZip"); }
}
	if ($_POST['chrEmail'] == "") { field_blank("chrEmail"); }

	$query = "SELECT ID FROM Events WHERE idEventSeries=".$_SESSION['idEventSeries']." AND idEventTitle=".$_SESSION['idEventTitles']." AND idVenue=".$_SESSION['idVenues']." AND !bDeleted";
	$event = fetch_database_query($query, 'Getting Event ID');
	$q = "UPDATE Signups 
	SET bCheckin=1 
	WHERE iduser = ".$_POST['id']." AND idEvent = ".$event['ID'];
		database_query($q, 'updating the database');
	
	$query = "SELECT *
				FROM Attendees
				WHERE ID=".$_REQUEST['id'];
				$UserInfo = fetch_database_query($query, 'Getting Attendee information');
				// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Attendees';
		$mysqlStr = '';
		$audit = '';
		

		$_POST['chrZip'] = strip_quotes($_POST['chrZip']);
		$_POST['chrPhone'] = strip_quotes($_POST['chrPhone']);

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirst',$UserInfo['chrFirst'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLast',$UserInfo['chrLast'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress',$UserInfo['chrAddress'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress1',$UserInfo['chrAddress1'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$UserInfo['chrCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$UserInfo['chrState'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrZip',$UserInfo['chrZip'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$UserInfo['chrCountry'],$audit,$table,$_POST['id']);		
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$UserInfo['chrPhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$UserInfo['chrEmail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCompany',$UserInfo['chrCompany'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bApple',$UserInfo['bApple'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intFindout',$UserInfo['intFindout'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intCompanyMatches',$UserInfo['intCompanyMatches'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intEditingSystem',$UserInfo['intEditingSystem'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAgency',$UserInfo['chrAgency'],$audit,$table,$_POST['id']);
			
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }	
	
				//Redirects to Thank You Page where E-mail is sent, also pass the UserID so we can grab the information
				header("Location: checkinthankyou.php?L=".base64_encode("chrFirst=".$_POST['chrFirst']."&chrLast=".$_POST['chrLast']));
				die();
			}
	
						
	
	$q = "SELECT chrName
		FROM EventTitles
		WHERE ID = ".$_SESSION['idEventTitles'];
	$eventTitle = fetch_database_query($q, "Getting Title");
	$q = "SELECT chrVenue
		FROM Venues
		WHERE ID = ".$_SESSION['idVenues'];
	$eventVenue = fetch_database_query($q, "Getting Venue");


include($BF. 'includes/top.php');
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
	<td width="786" bgcolor="#3F3F3F"><form id="form1" name="form1" method="post" action="">
	<?
	if (count($error_message) > 0) {
		foreach ($error_message as $error) {
			echo $error;
		}
	}?>
		<div class="maintitle"><strong><?=$eventTitle['chrName']?></strong> at <strong><?=$eventVenue['chrVenue']?></strong></div>
	<div style="padding-left:7px; padding-right:7px;">
		
	
	<?
	$count=0;
	$checked=""; 
	
	$query = "SELECT *
				FROM Attendees
				WHERE ID=".$_REQUEST['id'];
				$row = fetch_database_query($query, 'Getting Attendee information');
	
	?>
	
	</div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="50%">
							<div class="formfield">
							<span class="textboxname">First Name</span> <span class="textboxrequired">(Required)</span> <br />
							<input name="chrFirst" type="text" id="chrFirst" size="35" maxlength="50" value="<?=$row['chrFirst']?>" />
							</div></td>
						<td width="50%"><div class="formfield"> <span class="textboxname">Last Name</span> <span class="textboxrequired">(Required)</span> <br />
									<input name="chrLast" type="text" id="chrLast" size="35" maxlength="50" value="<?=$row['chrLast']?>" />
						</div></td>
					</tr>
<?
	if($_SESSION['idEventSeries'] != 3) {
?>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Company Name</span><br />
									<input name="chrCompany" type="text" id="chrCompany" size="35" maxlength="75" value="<?=$row['chrCompany']?>" />
						</div></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Address</span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrAddress" type="text" id="chrAddress" size="35" maxlength="75" value="<?=$row['chrAddress']?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Address 2</span><br />
									<input name="chrAddress1" type="text" id="chrAddress1" size="35" maxlength="75" value="<?=$row['chrAddress1']?>" />
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">City</span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrCity" type="text" id="chrCity" size="35" maxlength="45" value="<?=$row['chrCity']?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">State</span> <span class="textboxrequired">(Required)</span><br />
										<select class='FormField' id="chrState" name='chrState'>
												<option value="">Select from list</option>
											<?	foreach($states as $st => $name) { ?>
												<option value='<?=@$st?>' <?=($row['chrState'] == $st ? "selected='selected'": "" )?>><?=$name?></option>
											<?	} ?>
										</select>
						</div></td>
					</tr>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Zip/Postal Code </span> <span class="textboxrequired">(Required)</span><br />
									<input name="chrZip" type="text" id="chrZip" size="35" maxlength="25" value="<?=$row['chrZip']?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Country </span><span class='textboxrequired'>(Required)</span></div>
										 <select class='FormField' id="chrCountry" name='chrCountry'>
											<?	foreach($countries as $cy => $name) { ?>
												<option value='<?=@$cy?>' <?=($row['chrCountry'] == $cy ? 'selected="selected"' : '')?>><?=$name?></option>
											<?	} ?>
										</select>
									</div></td>
					</tr>
<?
	}
?>
					<tr>
						<td><div class="formfield"> <span class="textboxname">Telephone</span><br />
									<input name="chrPhone" type="text" id="chrPhone" size="35" maxlength="25" value="<?=$row['chrPhone']?>" />
						</div></td>
						<td><div class="formfield"> <span class="textboxname">Email</span> <span class="textboxrequired">(Required to send confirmation email.)</span><br />
									<input name="chrEmail" type="text" id="chrEmail" size="35" maxlength="75" value="<?=$row['chrEmail']?>" />
						</div></td>
					</tr>
					<tr>
						<td colspan="2"><hr class="hr" width="97%" size="1" noshade="noshade" /></td>
					</tr>
					
					<tr>
						<td>
<?
						if($_SESSION['idEventSeries'] == 1) {
?>
							<div class="FormName"><span class="textboxname">How did you find out about this seminar?</span></div>
							<div class='FormField'>
								<select class='FormField' id="intFindout" name="intFindout">
									<option value=""<?=($row['intFindout'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
									<option value="1"<?=($row['intFindout'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour Website</option>
									<option value="2"<?=($row['intFindout'] == "2" ? " selected='selected'" : "")?>>Third-party website</option>
									<option value="3"<?=($row['intFindout'] == "3" ? " selected='selected'" : "")?>>Apple Hot News Website</option>
									<option value="4"<?=($row['intFindout'] == "4" ? " selected='selected'" : "")?>>Apple eNews email</option>
									<option value="5"<?=($row['intFindout'] == "5" ? " selected='selected'" : "")?>>Apple Final Cut Studio Tour email</option>
									<option value="6"<?=($row['intFindout'] == "6" ? " selected='selected'" : "")?>>Other source</option>
								</select>
							</div></td><td>
							<div class="FormName"><span class="textboxname">What type of company or institution most closely matches your work?</span></div>
							<div class='FormField'>
								<select class='FormField' id="intCompanyMatches" name="intCompanyMatches">
									<option name =""<?=($row['intCompanyMatches'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
									<option value="1"<?=($row['intCompanyMatches'] == "1" ? " selected='selected'" : "")?>>Production Company
									<option value="2"<?=($row['intCompanyMatches'] == "2" ? " selected='selected'" : "")?>>Broadcast/Cable Studio</option>
									<option value="3"<?=($row['intCompanyMatches'] == "3" ? " selected='selected'" : "")?>>Corporate Video</option>
									<option value="4"<?=($row['intCompanyMatches'] == "4" ? " selected='selected'" : "")?>>Visual Effects Studio</option>
									<option value="5"<?=($row['intCompanyMatches'] == "5" ? " selected='selected'" : "")?>>Animation Studio</option>
									<option value="6"<?=($row['intCompanyMatches'] == "6" ? " selected='selected'" : "")?>>Web/Interactive media</option>
									<option value="7"<?=($row['intCompanyMatches'] == "7" ? " selected='selected'" : "")?>>Post Production Facility</option>
									<option value="8"<?=($row['intCompanyMatches'] == "8" ? " selected='selected'" : "")?>>Independent Filmmaker or Videographer</option>
									<option value="9"<?=($row['intCompanyMatches'] == "9" ? " selected='selected'" : "")?>>Audio Recording Studio</option>
									<option value="10"<?=($row['intCompanyMatches'] == "10" ? " selected='selected'" : "")?>>Education Institution</option>
									<option value="11"<?=($row['intCompanyMatches'] == "11" ? " selected='selected'" : "")?>>Other</option>
								</select>
							</div></td></tr><tr><td>
							<div class="FormName"><span class="textboxname">Which Non-liner Editing System do you primarily use?</span></div>
							<div class='FormField'>
								<select class='FormField' id="editingsystem" name="editingsystem">
									<option value=""<?=($row['intEditingSystem'] == "" ? " selected='selected'" : "")?>>(Choose One)</option>
									<option value="1"<?=($row['intEditingSystem'] == "1" ? " selected='selected'" : "")?>>Apple Final Cut Pro or Final Cut Express</option>
									<option value="2"<?=($row['intEditingSystem'] == "2" ? " selected='selected'" : "")?>>Avid Xpres Pro or DV</option>
									<option value="3"<?=($row['intEditingSystem'] == "3" ? " selected='selected'" : "")?>>Other Avid Product</option>
									<option value="4"<?=($row['intEditingSystem'] == "4" ? " selected='selected'" : "")?>>Adobe Premier or Premiere Pro</option>
									<option value="5"<?=($row['intEditingSystem'] == "5" ? " selected='selected'" : "")?>>Sony Vegas Video</option>
									<option value="6"<?=($row['intEditingSystem'] == "6" ? " selected='selected'" : "")?>>Media 100</option>
									<option value="7"<?=($row['intEditingSystem'] == "7" ? " selected='selected'" : "")?>>Discreet Edit</option>
									<option value="8"<?=($row['intEditingSystem'] == "8" ? " selected='selected'" : "")?>>Pinnacle Liquid, Studio or Pro</option>
									<option value="9"<?=($row['intEditingSystem'] == "9" ? " selected='selected'" : "")?>>ULead MediaStudio</option>
									<option value="10"<?=($row['intEditingSystem'] == "10" ? " selected='selected'" : "")?>>Quantel</option>
									<option value="11"<?=($row['intEditingSystem'] == "11" ? " selected='selected'" : "")?>>Other</option>
									<option value="12"<?=($row['intEditingSystem'] == "12" ? " selected='selected'" : "")?>>None</option>
								</select>
							</div>
<?
						} else if($_SESSION['idEventSeries'] == 2) {
?>

<?
					} else if ($_SESSION['idEventSeries'] == 3) {
?>				
					<div class="FormName"><span class="textboxname">Agency Name.</span></div>
					<div class='FormField'><input type="text" style="width:250px;" maxlength="200" id="chrAgency" name=""chrAgency"" value="<?=$UserInfo['"chrAgency"']?>" /></div>

<?
						}
?>
						</td>
						<td>&nbsp;</td>
					</tr>				
					<tr>
						<td colspan="2"><hr class="hr" width="97%" size="1" noshade="noshade" /></td>
					</tr>
					
					<tr>
						<td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><input id="bApple" name="bApple" type="checkbox" class="checkbox" value="1" checked="checked" /></td>
								<td class="optin"><strong>Stay in touch!</strong> Keep me up to date with Apple news, software updates, and the latest information on products and services to help me make the most of my Apple products.</td>
							</tr>
	
						</table></td>
						</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
	
					<tr>
						<td><input type = "hidden" name = "id" id = "id" value = "<?=$_REQUEST['id']?>">
						<input name="submit" type="submit" class="button" value="Check In" /></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><div class="disclaimer"><a href="http://www.apple.com/legal/privacy/">Apple Privacy Policy</a> <br />
	You're in control. You always have access to your personal information and contact preferences, so you can change them at any time. To learn how Apple safeguards your personal information, please review the Apple Customer Privacy Policy.  If you would rather not receive this information, please uncheck the box.</div></td>
						</tr>
				</table>
<?

function field_blank($code) {
	global $error_message;
	switch ($code) {
		case "chrFirst":
			$Message = "Please Enter your First Name.";
			break;
		case "chrLast":
			$Message = "Please Enter your Last Name.";
			break;
		case "chrAddress":
			$Message = "Please Enter your Address.";
			break;
		case "chrCity":
			$Message = "Please Enter your City.";
			break;
		case "chrState":
			$Message = "Please Select your State.";
			break;	
		case "chrZip":
			$Message = "Please Enter your Zip Code.";
			break;
		case "chrEmail":
			$Message = "Please Enter your E-mail Address.";
			break;
		case "selectevent":
			$Message = "You must select at least 1 Event.";
			break;	
		case "emailused":
			$Message = "Sorry, You are already signed up for one or more of these events.";
			break;									
		default:
			$Message = "";
			break;			
	}
	if ($Message != "") {
	$error_message[] = '<div style="padding-top:10px; text-align:center;"><div class="error">'.$Message.'</div></div>';
	}
	
}

// Table Close with Borders This part will not change throughout page.
?>
				</form></td>
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
<?
	include($BF.'includes/bottom.php');