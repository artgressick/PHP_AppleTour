<?php
// INSERT PROJECT NAME HERE
	session_name('appleappevents');
	session_start();
	
function decode($val) {
	$val = str_replace('&quot;','"',$val);
	$val = str_replace("&apos;","'",$val);
	return $val;
}

	include('appleappevents-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('m-d-y', strtotime('today'));
		
	$result = mysqli_query($mysqli_connection, $_SESSION['excel']);

	$q = "SELECT chrTitle
		  FROM EventSeries
		  WHERE ID=".$_REQUEST['id'];
		  
	$venue = mysqli_fetch_assoc(mysqli_query($mysqli_connection,$q));

	$venue_name = str_replace(" ", "_", decode($venue['chrTitle'])."-".decode($_SESSION['chrVenue']));
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=Reseller_Lead_Report_(". $venue_name .").xls");
	header("Pragma: no-cache");
	header("Expires: 0");
?>
<style>
.Heading { font-weight:bold; font-size:11px; border-right: 1px solid #000000; margin:2px; vertical-align:middle; height:20px; }

.FirstRow1 { font-size:11px; border-top: 1px solid #000000; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#DDDDDD; }
.FirstRow2 { font-size:11px; border-top: 1px solid #000000; border-right: 1px solid #000000; margin:2px; vertical-align:middle; background-color:#FFFFFF; }

</style>

	<table border="0">
		<tr>
			<td class="Heading">Event</td>
			<td class="Heading">First Name</td>
			<td class="Heading">Last Name</td>
<?
		if ($_REQUEST['id'] != 3) {
?>

			<td class="Heading">Company</td>	
			<td class="Heading">Address</td>
			<td class="Heading">Address1</td>
			<td class="Heading">City</td>
			<td class="Heading">State</td>
			<td class="Heading">Zip</td>
			<td class="Heading">Country</td>
<?
	}
?>
			<td class="Heading">Phone</td>
			<td class="Heading">E-mail</td>
			<td class="Heading">Stay in Touch?</td>
			<td class="Heading">Checked In?</td>
<?
		if ($_REQUEST['id'] == 1) {
?>
			<td class="Heading">Found out about Seminar by</td>
			<td class="Heading">Type of company or institution closely matches</td>
			<td class="Heading">Non-liner Editing System primarily use</td>
<?
		} else if ($_REQUEST['id'] == 2) {
?>
			<td class="Heading">Heard about tour from</td>
			<td class="Heading">Heard about tour from other answer</td>
			<td class="Heading">Interest about attending this event</td>
			<td class="Heading">Interest about attending this event other answer</td>
			<td class="Heading">Type of photography primarily doing</td>
			<td class="Heading">Type of photography primarily doing other answer</td>
<?
		} else if ($_REQUEST['id'] == 3) {
?>		
			<td class="Heading">Agency</td>
<?		
		}
?>

		</tr>
<?
	$count=0;

	while($row = mysqli_fetch_array($result)) {
			$count++;
		
		if ($_REQUEST['id'] == 1) {
		
			if ($row['intFindout'] == "1") { $Findout = "Apple Final Cut Studio Tour Website"; }
			else if ($row['intFindout'] == "2") { $Findout = "Third-party website"; }
			else if ($row['intFindout'] == "3") { $Findout = "Apple Hot News Website"; }
			else if ($row['intFindout'] == "4") { $Findout = "Apple eNews email"; }
			else if ($row['intFindout'] == "5") { $Findout = "Apple Final Cut Studio Tour email"; }
			else if ($row['intFindout'] == "6") { $Findout = "Other source"; }
			else { $Findout = "N/A"; }

			if ($row['intCompanyMatches'] == "1") { $CompanyMatches = "Production Company"; }
			else if ($row['intCompanyMatches'] == "2") { $CompanyMatches = "Broadcast/Cable Studio"; }
			else if ($row['intCompanyMatches'] == "3") { $CompanyMatches = "Corporate Video"; }
			else if ($row['intCompanyMatches'] == "4") { $CompanyMatches = "Visual Effects Studio"; }
			else if ($row['intCompanyMatches'] == "5") { $CompanyMatches = "Animation Studio"; }
			else if ($row['intCompanyMatches'] == "6") { $CompanyMatches = "Web/Interactive media"; }
			else if ($row['intCompanyMatches'] == "7") { $CompanyMatches = "Post Production Facility"; }
			else if ($row['intCompanyMatches'] == "8") { $CompanyMatches = "Independent Filmmaker or Videographer"; }
			else if ($row['intCompanyMatches'] == "9") { $CompanyMatches = "Audio Recording Studio"; }
			else if ($row['intCompanyMatches'] == "10") { $CompanyMatches = "Education Institution"; }
			else if ($row['intCompanyMatches'] == "11") { $CompanyMatches = "Other"; }
			else { $CompanyMatches = "N/A"; }			

			if ($row['intEditingSystem'] == "1") { $EditingSystem = "Apple Final Cut Pro or Final Cut Express"; }
			else if ($row['intEditingSystem'] == "2") { $EditingSystem = "Avid Xpres Pro or DV"; }
			else if ($row['intEditingSystem'] == "3") { $EditingSystem = "Other Avid Product"; }
			else if ($row['intEditingSystem'] == "4") { $EditingSystem = "Adobe Premier or Premiere Pro"; }
			else if ($row['intEditingSystem'] == "5") { $EditingSystem = "Sony Vegas Video"; }
			else if ($row['intEditingSystem'] == "6") { $EditingSystem = "Media 100"; }
			else if ($row['intEditingSystem'] == "7") { $EditingSystem = "Discreet Edit"; }
			else if ($row['intEditingSystem'] == "8") { $EditingSystem = "Pinnacle Liquid, Studio or Pro"; }
			else if ($row['intEditingSystem'] == "9") { $EditingSystem = "ULead MediaStudio"; }
			else if ($row['intEditingSystem'] == "10") { $EditingSystem = "Quantel"; }
			else if ($row['intEditingSystem'] == "11") { $EditingSystem = "Other"; }
			else if ($row['intEditingSystem'] == "12") { $EditingSystem = "None"; }
			else { $EditingSystem = "N/A"; }
			
		} else if ($_REQUEST['id'] == 2) {
					
			if ($row['intQ1'] == "1") { $Q1 = "Email invitation from Apple."; }
			else if ($row['intQ1'] == "2") { $Q1 = "Apple eNews email article"; }
			else if ($row['intQ1'] == "3") { $Q1 = "Apple Hot News article"; }
			else if ($row['intQ1'] == "4") { $Q1 = "Third-party website (please list)"; }
			else if ($row['intQ1'] == "5") { $Q1 = "Apple Final Cut Studio Tour email"; }
			else if ($row['intQ1'] == "6") { $Q1 = "Other (please list)"; }
			else { $Q1 = "N/A"; }			


			if ($row['intQ2'] == "1") { $Q2 = ""; }
			else if ($row['intQ2'] == "2") { $Q2 = "I want to learn more about Aperture before I make a purchase decision."; }
			else if ($row['intQ2'] == "3") { $Q2 = "I use Aperture already and want to pick up some new tips or have questions answered."; }
			else if ($row['intQ2'] == "4") { $Q2 = "I am interested in seeing the professional photographer's work."; }
			else if ($row['intQ2'] == "5") { $Q2 = "Other (please list)"; }
			else { $Q2 = "N/A"; }	
			
			if ($row['intQ3'] == "1") { $Q3 = "Sports photography"; }
			else if ($row['intQ3'] == "2") { $Q3 = "Wedding or Portrait"; }
			else if ($row['intQ3'] == "3") { $Q3 = "Commercial - product or fashion photography"; }
			else if ($row['intQ3'] == "4") { $Q3 = "Nature or landscape photography"; }
			else if ($row['intQ3'] == "5") { $Q3 = "Corporate or event photography"; }
			else if ($row['intQ3'] == "6") { $Q3 = "Photo journalism or editorial"; }
			else if ($row['intQ3'] == "7") { $Q3 = "Fine Art"; }
			else if ($row['intQ3'] == "8") { $Q3 = "Architecture"; }
			else if ($row['intQ3'] == "9") { $Q3 = "Amateur or Enthusiast only"; }
			else if ($row['intQ3'] == "10") { $Q3 = "Other (please list)"; }
			else { $Q3 = "N/A"; }	
			
		}

?>
			<tr>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrName'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrFirst'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrLast'])?></td>
<?
		if ($_REQUEST['id'] != 3) {
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCompany'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrAddress'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrAddress1'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCity'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrState'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrZip']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrCountry'])?></td>
<?
	}
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrPhone']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$row['chrEmail']?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=($row['bApple'] ? "Yes" : "No")?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=($row['bCheckin'] ? "Yes" : "No")?></td>	
<?
		if ($_REQUEST['id'] == 1) {
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$Findout?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$CompanyMatches?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$EditingSystem?></td>
<?
		} else if ($_REQUEST['id'] == 2) {
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$Q1?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrQ1other'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$Q2?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrQ2other'])?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=$Q3?></td>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrQ3other'])?></td>
<?
		} else if ($_REQUEST['id'] == 3) {
?>
				<td class="FirstRow<?=($count%2 ? "1" : "2" )?>"><?=decode($row['chrAgency'])?></td>
<?
		}
?>
			</tr>
<?
				
	}
?>
	</table>