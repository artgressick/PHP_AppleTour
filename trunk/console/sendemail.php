<?	
	$BF = '../';
	$title = 'Send E-mail to Attendees';
	require($BF. '_lib.php');
	include($BF. 'includes/meta_admin.php');	

	$active = 'emails';
	$subactive = 'emailattendees';	
	
include($BF. 'includes/top_admin.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title" nowrap="nowrap">Emailing Attendees</td>
		<td class="title_right" style="text-align:right;">
            
		</td>                
		<td class="right"></td>
	</tr>
</table>

<div class='instructions'>Please see log Below.</div>


<div class='innerbody'>
	<div>Starting E-mails....</div>
	<div id="emaillog"></div>
</div>
<iframe 
src="<?=$BF?>console/_email_attendees.php"
width="1" height="1" style="border:none;" frameborder="0">
</iframe>
<?
	include($BF. 'includes/bottom_admin.php');