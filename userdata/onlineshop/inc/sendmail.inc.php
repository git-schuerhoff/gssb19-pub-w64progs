<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();

//$_POST = $_GET;

if($se->get_setting('cbUseMailSystem_Checked') == 'True') {
	require_once("inc/class.smtp.php");
	require_once("inc/class.phpmailer.php");
	include_once("inc/class.gsmailengine.php");
	$me = new gs_mailengine('',$_POST);
	$me->smtphost = $se->get_setting('edSMTPServer_Text');
	$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
	if($secure == 'keine') {
		$secure = '';
	}
	$me->smtpsecure = $secure;//$se->get_setting('cbbSMTPSecure_Text')
	$me->smtpauth = true;//$se->get_setting('cbbSMTPAuth_Text')
	$me->smtpport = $se->get_setting('edSMTPPort_Text');
	$me->smtpusername = $se->get_setting('edEMUser_Text');
	$me->smtppassword = $se->get_setting('edEMPassword_Text');
	$me->from = $se->get_setting('edEMAddress_Text');
	$me->fromname = $se->get_setting('edEMName_Text');
	$me->shoppath = $se->get_setting('edFTPShopDir_Text');
	
	$me->msg = $_POST['message'];
	
	$ok = $me->sendmail($_POST['mail_to'], $_POST['subject'], '', '', false);
	if($ok == 1) {
		echo true;
	} else {
		echo false;
	}
} else {
	//TS 27.12.2016: PHP_EOL statt newlines verwenden
	/*
	$header = "From: " . $_POST['mail_from'] . "\n" .
				 "Content-type: text/plain; charset=\"utf-8\"\n" .
				 "Content-Transfer-Encoding: base64\n" .
				 "Reply-To: " . $_POST['mail_from'] . "\n" .
				 "X-Mailer: PHP/" . phpversion() . "\r\n";
	*/
	$eol = PHP_EOL;
	$header = 'From: ' . $_POST['mail_from'].$eol.
				 'Content-type: text/plain; charset="utf-8"'.$eol.
				 'Content-Transfer-Encoding: base64'.$eol.
				 'Reply-To: ' . $_POST['mail_from'].$eol.
				 'X-Mailer: PHP/'.phpversion().$eol;
	$to = $_POST['mail_to'];
	$sub = $_POST['subject'];
	$msg = $_POST['message'];
	$msg = chunk_split(base64_encode(str_replace('\n',chr(13),$msg)));

	$ok = false;
	$ok = mail($to, $sub, $msg, $header);
	echo $ok;
}

?>