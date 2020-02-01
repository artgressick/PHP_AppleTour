<?php
function sendemail($to, $from, $subject, $msg) {
	//dtn: THis is the mail Mime includes
	$er = error_reporting(0); 		//dtn: This is added in so that we don't get spammed with PEAR::isError() messages in our tail -f ..
	include_once('Mail.php');		//dtn: This is the main mail addon so that we can use the mime emailer
	include_once('Mail/mime.php');	//dtn: This is the actual mime part of the emailer

	$crlf = "\n";
//	mb_language('en');
//	mb_internal_encoding('UTF-8');
	
	$mime = new Mail_mime($crlf);	

	$subject = decode($subject);
//	$subject = mb_convert_encoding($subject, 'UTF-8',"AUTO");
//	$subject = mb_encode_mimeheader($subject);
//	$from = 'proapps_events@apple.com';
	$hdrs = array('From'    => $from,
				  'Subject' => $subject
			  );
	
	$mime->_build_params['text_encoding'] ='quoted-printable';
	$mime->_build_params['text_charset'] = "UTF-8";
	$mime->_build_params['html_charset'] = "UTF-8";

	$Message = decode($msg);
		
	$mime->setHTMLBody($Message);
		
	$body = $mime->get();
	$hdrs = $mime->headers($hdrs);
//	$body = mb_convert_encoding($body, "UTF-8", "UTF-8"); 
	
	$mail =& Mail::factory('mail');
//	if($mail->send('jsummers@techitsolutions.com', $hdrs, $body)) {
	if($mail->send(decode($to), $hdrs, $body)) {
		return true;
	} else {
		return false;
	}
}
?>
