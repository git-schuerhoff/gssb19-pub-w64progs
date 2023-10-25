<?php /* Copyright GS Software AG */
header("Content-Type: text/html; charset=utf-8");
session_start();
chdir("../");
require_once("inc/class.smtp.php");
require_once("inc/class.phpmailer.php");
include_once("inc/class.shopengine.php");
include_once("inc/class.gsmailengine.php");
$se = new gs_shopengine();
// gsme_order.inc.php
/* Copyright GS Software AG */
/*
    ****************************************************************************
    * CAUTION: Please !!!DO NOT!!! change this file.                           *
    ****************************************************************************
    * ACHTUNG: Bitte nehmen Sie an dieser Datei !!!KEINE VERÄNDERUNGEN!!! vor. *
    ****************************************************************************
    GS MailShopEngine v1.0 - class.gsmailshopengine.php
    Author: Thilo Schuerhoff / Schuerhoff EDV

    (c) 2014 - 2015 GS Software AG

	this code is NOT open-source or freeware
	you are not allowed to use, copy or redistribute it in any form
*/
/*echo "<b>GSME V1.0</b><br />Zu Beginn von gsorder.inc.php:<br /><pre>";
print_r($_POST);
die("</pre>");*/

if(!isset($_POST['email']))
{
	$_POST['email'] = $_POST['recipient'];
}
if(!empty($_POST) && isset($_POST['email'])) {
	if(file_exists("dynsb/class/class.shoplog.php")) {
		if(!in_array("shoplog",get_declared_classes()))
		{
			require_once("dynsb/class/class.shoplog.php");
		}
		$sl = new shoplog();
		
		$doSendMail = true;
		if($se->get_setting('cbUseGSBMOrderMail_Checked') == 'True') {
			$doSendMail = false;
		}
		
		if(strtolower($_POST['_LANGTAGFNFIELDEMAILFORMAT_']) == 'html') {
			customer_htmlmail($doSendMail);
		} else {
			customer_textmail($doSendMail);
		}
		//shopowner_textmail();
		shopowner_htmlmail();
		header("Location: " . $_POST['redirect']);
		
	} else {
		echo("{LangTagErrorMissingRootPathFile}");
	}
} else {
	die();
}

function customer_htmlmail($doSendMail) {
	global $sl, $se;
	require_once("inc/postdefinition.inc.php");
	//echo "Array aus postdefinition:<br /><pre>";
	//print_r($aPostDef);
	//die("</pre>");
	
	if($se->get_setting('cbUsePhpBonusPoints_Checked') == 'True')
	{
		$sl->UseBonusPoints = true;
		$sl->PerGoodsValue = $se->get_setting('forgoodsvalue');
		$sl->BonusPointsPerGoodsValue = $se->get_setting('pointsforgoods');
	}
	$sl->getitemavail($_POST);
	$sl->logShoporder($_POST, $aPostDef, 1, '', $se->lngID, $pass);
	//$_POST wird durch logShoporder nicht geändert
	
    if($se->get_setting('cbUsePhpCreateNewCustomer_Checked') == 'True'){
        customer_pwmail();
	}
	/*echo "POST-Daten nach sl->getitemavail:<br /><pre>";
	print_r($_POST);
	die("</pre>");*/
	$bNewCus = $sl->bNewCus;
	//A TS 27.11.2012 Dekodierung der Artikelnamen
	foreach($_POST as $key => $value)
	{
		
		if(strpos($key,"_LANGTAGFNFIELDTEXTITEM_") !== false)
		{
			//$_POST[$key] = $sl->email_friendly(iconv('UTF-8', 'ISO-8859-1',base64_decode($value)));
			$_POST[$key] = base64_decode($value);
		}
	}
	if($se->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
	{
		if ($_POST['_LANGTAGFNTERMSANDCONDNEWSLETTER_'] != '' || $_POST['_LANGTAGFNTERMSANDCONDNEWSLETTER2_'] == 1)
		{
		
		  require_once("dynsb/module/newsletter2/class.newsletter2.php");
		  $nl2 = new newsletter2();
		  $nl2->signIn2($_POST['email'], $_POST['_LANGTAGFNFIELDEMAILFORMAT_']);
		}
	}
	
	/*echo "<pre>";
	print_r($_POST);
	die("</pre>");*/
	if($doSendMail) {
		$me = new gs_mailengine('template/gs_ordermail_customer_html.html',$_POST);
		//die("Mailengine initialised");
		$me->smtphost = $se->get_setting('edSMTPServer_Text');
		$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
		if($secure == 'keine') {
			$secure = '';
		}
		//die($secure);
		$me->smtpsecure = $secure;
		$me->smtpauth = true;
		$me->smtpport = $se->get_setting('edSMTPPort_Text');
		$me->smtpusername = $se->get_setting('edEMUser_Text');
		$me->smtppassword = $se->get_setting('edEMPassword_Text');
		$me->from = $se->get_setting('edShopEmail_Text');
		$me->fromname = $se->get_setting('edShopName_Text');
		$me->shoppath = $se->get_setting('edFTPShopSubDir_Text');
		
		$shoplogo = $se->get_setting('edLogo2_Text');
		$aExt = explode('.',$shoplogo);
		$cExt = strtolower($aExt[count($aExt) - 1]);
		if($cExt == 'jpg') { $cExt = 'jpeg'; }
		if($shoplogo != '') {
			$shopsubdir = $se->get_setting('edFTPShopSubDir_Text');
			if($shopsubdir != "") {
				$shopsubdir = $shopsubdir . "/";
			}
			$shoplogopath = $_SERVER['DOCUMENT_ROOT'] . '/' . $shopsubdir . 'images/' . $shoplogo;
			//die($shoplogopath);
			$me->aImages[] = array("cid" => "shoplogo","path" => $shoplogopath,"name" => $shoplogo,"type" => "image/" . $cExt);
		} else {
			$me->aImages[] = array("cid" => "shoplogo","path" => "","name" => "","type" => "image/gif");
		}
	
		$me->get_tags();
		$me->parse_data();
		$me->parse_tags();
		
		$metxt = new gs_mailengine('template/gs_ordermail_customer_text.html',$_POST);
		$metxt->get_tags();
		$metxt->parse_data();
		$metxt->parse_tags();
		
		$me->msg = $metxt->content;
		$me->htmlmsg = $me->content;
		
		$succ = $me->sendmail2($_POST['email'], $_POST['answer_subject'], '', '', true);
	}
	
	//die("Ergebnis: " . $succ);
	return;
}

function customer_textmail($doSendMail) {
	global $sl, $se;
	require_once("inc/postdefinition.inc.php");
	//echo "Array aus postdefinition:<br /><pre>";
	//print_r($aPostDef);
	//die("</pre>");
	
	if($se->get_setting('cbUsePhpBonusPoints_Checked') == 'True')
	{
		$sl->UseBonusPoints = true;
		$sl->PerGoodsValue = $se->get_setting('forgoodsvalue');
		$sl->BonusPointsPerGoodsValue = $se->get_setting('pointsforgoods');
	}
	$sl->getitemavail($_POST);
	$sl->logShoporder($_POST, $aPostDef, 1, '', $se->lngID, $pass);
	//$_POST wird durch logShoporder nicht geändert
	
	if($se->get_setting('cbUsePhpCreateNewCustomer_Checked') == 'True'){
        customer_pwmail();
	}
	
	//echo "POST-Daten nach sl->getitemavail:<br /><pre>";
	//print_r($_POST);
	//die("</pre>");
	$bNewCus = $sl->bNewCus;
	//A TS 27.11.2012 Dekodierung der Artikelnamen
	foreach($_POST as $key => $value)
	{
		
		if(strpos($key,"_LANGTAGFNFIELDTEXTITEM_") !== false)
		{
			//$_POST[$key] = $sl->email_friendly(iconv('UTF-8', 'ISO-8859-1',base64_decode($value)));
			$_POST[$key] = base64_decode($value);
		}
	}
	if($se->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
	{
		if ($_POST['_LANGTAGFNTERMSANDCONDNEWSLETTER_'] != '' || $_POST['_LANGTAGFNTERMSANDCONDNEWSLETTER2_'] == 1)
		{
		
		  require_once("dynsb/module/newsletter2/class.newsletter2.php");
		  $nl2 = new newsletter2();
		  $nl2->signIn2($_POST['email'], $_POST['_LANGTAGFNFIELDEMAILFORMAT_']);
		}
	}
	
	//echo "<pre>";
	//print_r($_POST);
	//die("</pre>");
	if($doSendMail) {
		$me = new gs_mailengine('template/gs_ordermail_customer_text.html',$_POST);
		$me->smtphost = $se->get_setting('edSMTPServer_Text');
		$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
		if($secure == 'keine') {
			$secure = '';
		}
		//die($secure);
		$me->smtpsecure = $secure;
		$me->smtpauth = true;
		$me->smtpport = $se->get_setting('edSMTPPort_Text');
		$me->smtpusername = $se->get_setting('edEMUser_Text');
		$me->smtppassword = $se->get_setting('edEMPassword_Text');
		$me->from = $se->get_setting('edShopEmail_Text');
		$me->fromname = $se->get_setting('edShopName_Text');
		$me->shoppath = $se->get_setting('edFTPShopDir_Text');
		
		$me->get_tags();
		$me->parse_data();
		$me->parse_tags();
		
		$me->msg = $me->content;
			
		//echo "<pre>";
		//print_r($me->aImages);
		//die("</pre>");
		//die($me->content);
		
		$me->sendmail2($_POST['email'], $_POST['answer_subject'], '', '', false);
	}
	return;
}

function shopowner_htmlmail() {
	global $sl, $se;
	
	/*echo "<pre>";
	print_r($_POST);
	die("</pre>");*/
	
		$me = new gs_mailengine('template/gs_ordermail_shopowner_html.html',$_POST);
		//die("Mailengine initialised");
		$me->smtphost = $se->get_setting('edSMTPServer_Text');
		$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
		if($secure == 'keine') {
			$secure = '';
		}
		//die($secure);
		$me->smtpsecure = $secure;
		$me->smtpauth = true;
		$me->smtpport = $se->get_setting('edSMTPPort_Text');
		$me->smtpusername = $se->get_setting('edEMUser_Text');
		$me->smtppassword = $se->get_setting('edEMPassword_Text');
		//$me->from = $se->get_setting('edShopEmail_Text');
		//$me->fromname = $se->get_setting('edShopName_Text');
		$me->from = $_POST['email'];
		$me->shoppath = $se->get_setting('edFTPShopSubDir_Text');
		
		$shoplogo = $se->get_setting('edLogo2_Text');
		$aExt = explode('.',$shoplogo);
		$cExt = strtolower($aExt[count($aExt) - 1]);
		if($cExt == 'jpg') { $cExt = 'jpeg'; }
		if($shoplogo != '') {
			$shopsubdir = $se->get_setting('edFTPShopSubDir_Text');
			if($shopsubdir != "") {
				$shopsubdir = $shopsubdir . "/";
			}
			$shoplogopath = $_SERVER['DOCUMENT_ROOT'] . '/' . $shopsubdir . 'images/' . $shoplogo;
			//die($shoplogopath);
			$me->aImages[] = array("cid" => "shoplogo","path" => $shoplogopath,"name" => $shoplogo,"type" => "image/" . $cExt);
		} else {
			$me->aImages[] = array("cid" => "shoplogo","path" => "","name" => "","type" => "image/gif");
		}
	
		$me->get_tags();
		$me->parse_data();
		$me->parse_tags();
		
		$metxt = new gs_mailengine('template/gs_ordermail_shopowner_text.html',$_POST);
		$metxt->get_tags();
		$metxt->parse_data();
		$metxt->parse_tags();
		
		$attach = $me->createAttachment();
		// Sepa-Platzhalter entfernen
        $metxt->content = str_replace('{GSME|TXT|SepaAccept}','',$metxt->content);
        $me->content = str_replace('{GSME|TXT|SepaAccept}','',$me->content);
		$me->msg = $metxt->content;
		$me->htmlmsg = $me->content;
		
		//$succ = $me->sendmail($_POST['email'], $_POST['answer_subject'], '', '', true);
		$me->sendmail2($_POST['recipient'], $_POST['subject'], $attach, 'gsshopbuilder.txt', true);
	
	
	//die("Ergebnis: " . $succ);
	return;
	//-----------------------------------------------------------------
	//-----------------------------------------------------------------
	//-----------------------------------------------------------------
	/*global $sl, $se;
		
	$me = new gs_mailengine('template/gs_ordermail_shopowner_text.html',$_POST);
	$me->smtphost = $se->get_setting('edSMTPServer_Text');
	$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
	if($secure == 'keine') {
		$secure = '';
	}
	//die($secure);
	$me->smtpsecure = $secure;
	$me->smtpauth = true;
	$me->smtpport = $se->get_setting('edSMTPPort_Text');
	$me->smtpusername = $se->get_setting('edEMUser_Text');
	$me->smtppassword = $se->get_setting('edEMPassword_Text');
	$me->from = $se->get_setting('edEMAddress_Text');
	$me->fromname = $se->get_setting('edEMName_Text');
	$me->shoppath = $se->get_setting('edFTPShopDir_Text');
	
	$me->get_tags();
	$me->parse_data();
	$me->parse_tags();
	
	$attach = $me->createAttachment();
	
	$me->msg = $me->content;
	
	$me->sendmail($_POST['recipient'], $_POST['subject'], $attach, 'gsshopbuilder.txt', false);
	return;*/
}

function shopowner_textmail() {
	global $sl, $se;
		
	//echo "<pre>";
	//print_r($_POST);
	//die("</pre>");
	
	$me = new gs_mailengine('template/gs_ordermail_shopowner_text.html',$_POST);
	$me->smtphost = $se->get_setting('edSMTPServer_Text');
	$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
	if($secure == 'keine') {
		$secure = '';
	}
	//die($secure);
	$me->smtpsecure = $secure;
	$me->smtpauth = true;
	$me->smtpport = $se->get_setting('edSMTPPort_Text');
	$me->smtpusername = $se->get_setting('edEMUser_Text');
	$me->smtppassword = $se->get_setting('edEMPassword_Text');
	//$me->from = $se->get_setting('edShopEmail_Text');
	//$me->fromname = $se->get_setting('edShopName_Text');
	$me->from = $_POST['email'];
	$me->shoppath = $se->get_setting('edFTPShopDir_Text');
	
	$me->get_tags();
	$me->parse_data();
	$me->parse_tags();
	
	$attach = $me->createAttachment();
	
	$me->msg = $me->content;
	
	//echo "<pre>";
	//print_r($me->aImages);
	//die("</pre>");
	//die($me->content);
	
	$me->sendmail2($_POST['recipient'], $_POST['subject'], $attach, 'gsshopbuilder.txt', false);
	return;
}

function customer_pwmail() {
	global $sl, $se;
	$lSendMail = false;
	$cParam = '';
	$cMailTemplate = 'template/gs_pwmail_customer_text.html';
	
	//Spezialfall für den GS-Shop behandeln
	if($_POST['recipient'] != 'order@gs-software.de') {
		//Shop ist nicht der GS-Shop, weiter wie bisher
		if($sl->bNewCus) {
			//Kunde ist neu
			if($sl->cusPass != '' && $sl->cusUser != '') {
				//Benutzername und Passwort wurden erzeugt
				//TS 10.04.2017: Passwort nicht anzeigen
				//$_POST['cus_password'] = $sl->cusPass;
				//Stattdessen in der Sesison merken und auf der thankyou-Seite anzeigen
				$_SESSION['cus_string'] = base64_encode($sl->cusPass);
				$_POST['cus_password'] = '*****';
				$_POST['cus_username'] = $sl->cusUser;
				//Mail senden
				$lSendMail = true;
			}
		}
		//Ansonsten Mail nicht senden
	} else {
		// Es ist der GS-Shop
		//Mail wird auf jeden Fall gesendet
		$lSendMail = true;
		//Benutzername und Passwort erstmal standardmäßig leer
		$_POST['cus_password'] = $se->get_lngtext('LangTagAlreadySent');
		$_POST['cus_username'] = $se->get_lngtext('LangTagAlreadySent');
		//Standard-Mail-Template ohne Zugangsdaten
		if($sl->bNewCus) {
			//Kunde ist neu
			if($sl->cusPass != '' && $sl->cusUser != '') {
				//Benutzername und Passwort wurden erzeugt
				//TS 10.04.2017: Passwort nicht anzeigen
				//$_POST['cus_password'] = $sl->cusPass;
				//Stattdessen in der Sesison merken und auf der thankyou-Seite anzeigen
				$_SESSION['cus_string'] = base64_encode($sl->cusPass);
				$_POST['cus_password'] = '*****';
				$_POST['cus_username'] = $sl->cusUser;
				//Voreintrag in sb_xbl_configs,
				//senden der Daten per CURL
				$cParam = "xmlusr=" . $sl->cusUser .
							 "&xmlcred=" . $sl->cusPass;
				$ch = curl_init();
				// setze die URL und andere Optionen
				curl_setopt($ch, CURLOPT_URL, 'https://www.gs-shopbuilder.de/sbxmlconfiggenerator/preentry.php');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $cParam);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				//curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				// führe die Aktion aus und gebe die Daten an den Browser weiter
				$res = curl_exec($ch);
				//Mail-Template mit Zugangsdaten
				$cMailTemplate = 'template/gs_pwmail_customer_text.html';
			}
		}
	}
	
	
	if($lSendMail) {
		
		$me = new gs_mailengine($cMailTemplate,$_POST);
		$me->smtphost = $se->get_setting('edSMTPServer_Text');
		$secure = strtolower($se->get_setting('cbbSMTPSecure_Text'));
		if($secure == 'keine') {
			$secure = '';
		}
		//die($secure);
		$me->smtpsecure = $secure;
		$me->smtpauth = true;
		$me->smtpport = $se->get_setting('edSMTPPort_Text');
		$me->smtpusername = $se->get_setting('edEMUser_Text');
		$me->smtppassword = $se->get_setting('edEMPassword_Text');
		$me->from = $se->get_setting('edShopEmail_Text');
		$me->fromname = $se->get_setting('edShopName_Text');
		$me->shoppath = $se->get_setting('edFTPShopDir_Text');
		
		$me->get_tags();
		$me->parse_data();
		$me->parse_tags();
		
		$me->msg = $me->content;
		
		$me->sendmail2($_POST['email'], $_POST['userdata'] . ' ' . $_POST['shopname'],'', '', false);
	}
	
	return;
}

?>