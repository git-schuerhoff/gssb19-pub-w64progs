<?php /* Copyright GS Software AG */
//header("Content-Type: text/html; charset=utf-8");
session_start();
chdir("../");
require_once("inc/class.smtp.php");
require_once("inc/class.phpmailer.php");
include_once("inc/class.shopengine.php");
include_once("inc/class.gsmailengine.php");
include_once("inc/class.order.php");
include_once("dynsb/class/class.shoplog.php");
$se = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$customer = $order->getCustomer();
$payment = $order->getPayment();
$delivery = $order->getDelivery();
$info = $order->getInfo();
$sl = new shoplog();
$sepahtml = '';
$doSendMail = true;
$doSendExalyser = false;
$exaURL = '';
$exaToken = '';
//die(var_dump($order));
function my_strrpos($haystack, $needle, $offset=0) {
    // same as strrpos, except $needle can be a string
    $strrpos = false;
    if (is_string($haystack) && is_string($needle) && is_numeric($offset)) {
        $strlen = strlen($haystack);
        $strpos = strpos(strrev(substr($haystack, $offset)), strrev($needle));
        if (is_numeric($strpos)) {
            $strrpos = $strlen - $strpos - strlen($needle);
        }
    }
    return $strrpos;
}

if($se->get_setting('cbUseGSBMOrderMail_Checked') == 'True') {
	$doSendMail = false;
}
//die(var_dump($se->get_setting('chkUseExalyser_checked')));
if($se->get_setting('chkUseExalyser_checked') == 'True') {
	$doSendExalyser = true;
	$exaURL = $se->get_settingmemo('memoUrlToExalyser');
	$exaToken = $se->get_settingmemo('memoTokenToExalyser');
}

if($order->getCustomerByEmail($customer['cust_email']) == 'NULL'){
	$order->createCustomer($customer);
	$customer = $order->getCustomer();
}

if(isset($_POST['sepamandat'])){
	$tmplFile = 'sepamandat.html';
	$sepase = new gs_shopengine($tmplFile);
	$sepahtml = $sepase->parse_inc();
	$bmax = count($basket);
	$download = false;
	$rentals = false;
	if($bmax > 0)
	{
		for($b = 0; $b < $bmax; $b++)
		{
			if($basket[$b]['art_isdownload'] == 'Y') {
				$download = true;
			}
			if($basket[$b]['art_prices']['isrental'] == 'Y') {
				$rentals = true;
			}
		}
	}
	$sepahtml = str_replace('<input id="SepaEinverstandenCheck" class="checkbox required-entry" name="SepaAccept" value="{GSSE_INCL_SEPAACCEPT}" type="checkbox">','',$sepahtml);
	if($rentals) {
		$sepahtml = str_replace('{GSSE_INCL_LangTagCreditorAccept}',$sepase->get_lngtext('LangTagCreditorAcceptMultiple'), $sepahtml);
	} else {
		$sepahtml= str_replace('{GSSE_INCL_LangTagCreditorAccept}',$sepase->get_lngtext('LangTagCreditorAccept'), $sepahtml);
	}
	$sepahtml= str_replace('{GSSE_INCL_CREDITOR}', $sepase->get_setting('edShopCompany_Text'), $sepahtml);
	$sepahtml= str_replace('{GSSE_INCL_CREDITORNUMBER}', $sepase->get_setting('edCreditorIdentifier_Text'), $sepahtml);
	$sepahtml= str_replace('{GSSE_INCL_CITYDATEFIRM}', $sepase->get_setting('edShopCity_Text').', '.date('d.m.Y').' {GSSE_INCL_ACCOUNTHOLDER1}', $sepahtml);
	
	$sepahtml = str_replace('{GSSE_INCL_ACCOUNTHOLDER}', $customer['firstname'].' '.$customer['lastname'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_STREET}', $customer['street'].' '.$customer['street2'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_ZIP}', $customer['zip'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_CITY}', $customer['city'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_STATE}', $customer['areaName'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_ACCOUNTHOLDER1}', $customer['firstname'].' '.$customer['lastname'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_FinancialInstitution}', $customer['financialinstitution'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_ACCOUNTNUMBER}', $customer['iban'], $sepahtml);
	$sepahtml = str_replace('{GSSE_INCL_BANKNUMBER}', $customer['bic'], $sepahtml);
	
	$customer['SepaAccept'] = $sepahtml;
	
	$order->setCustomer($customer);
}

if($order->writeOrder() == "True"){
	
	if($doSendExalyser){
		// JSON String for Exalyser servlet
		
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_PORT, 80);
		// Mapping
		$cus = $order->getCustomerByEmail($customer['cust_email']);
		$bas = $order->getBasket();
		//TODO: Settings auslesen
		$ord['ORDshopsystem'] = 'GS Shopbuilder Pro';
		$ord['ORDshopversion'] = '19';
		$ord['ORDshopname'] = $se->get_setting('edShopName_Text');
		
		//Customer mapping
		$ordCus['CUSname'] = $customer['mrormrsText'].' '.$customer['firstname'].' '.$customer['lastname'];
		$ordCus['CUSno'] = $cus['cusIdNo'];
		$ordCus['CUSmail'] = $customer['cust_email'];
		$ordCus['CUScompanyname'] = $customer['company'];
		$ordCus['CUSkeyword'] = $ordCus['CUSname'].' '.$customer['company'];
		//Currency mapping
		$ordCus['CDTcode'] = $order->getCurrency();
		// Kontodaten mapping
		if(isset($customer['iban'])){
			$ordCus['CUS2BNKiban'] = $customer['iban'];
			$ordCus['CUS2BNKpayer'] = $customer['AccountHolderFirstName'].' '.$customer['AccountHolderLastName'];
			$ordCus['BNKname'] = $customer['financialinstitution'];
		} else {
			$ordCus['CUS2BNKiban'] = '';
			$ordCus['CUS2BNKpayer'] = '';
			$ordCus['BNKname'] = '';
		}
		
		// Address mapping
		$streetpos = my_strrpos($customer['street'],' ',0);
		$ordAdr['ADRname'] = substr($ordCus['CUSname'].' '.$customer['street2'],0,140);//ADRname max 140
		$ordAdr['ADRtype'] = 'HOUSE';
		$ordAdr['ADRpostcode'] = $customer['zip'];
		$ordAdr['ADRcity'] = $customer['city'];		
		$ordAdr['ADRstreet'] = substr($customer['street'],0,$streetpos);
		$ordAdr['ADRstreetno'] = trim(substr($customer['street'],$streetpos));
		$ordAdr['CNTisocode'] = $customer['stateISO'];
		
		// Delivery address mapping
		if(isset($customer['deliverfirstname'])){
			$streetpos = my_strrpos($customer['deliverstreet'],' ',0);
			$ordAdr['delADRname'] = substr($customer['delivermrormrs'].' '.$customer['deliverfirstname'].' '.$customer['deliverlastname'],0,140);
			$ordAdr['delADRtype'] = 'HOUSE';
			$ordAdr['delADRstreet'] = substr($customer['deliverstreet'],0,$streetpos);
			$ordAdr['delADRstreetno'] = trim(substr($customer['deliverstreet'],$streetpos));
			$ordAdr['delADRpostcode'] = $customer['deliverzip'];
			$ordAdr['delADRcity'] = $customer['delivercity'];
		} else {
			$ordAdr['delADRname'] = '';
			$ordAdr['delADRtype'] = '';
			$ordAdr['delADRstreet'] = '';
			$ordAdr['delADRstreetno'] = '';
			$ordAdr['delADRpostcode'] = '';
			$ordAdr['delADRcity'] = '';
		}

		//Orderhead mapping
		$ordCusord['CUSORDdate'] = date('Y-m-d H:i:s');
		$ordCusord['CUSORDname'] = 'GSSB Bestellung '.$order->getOrderNumber();
		$ordCusord['CUSORDno'] = $order->getOrderNumber();
		$ordCusord['CUSORDsubject'] = 'Bestellung von '.$se->get_setting('edAbsoluteShopPath_Text');
		$ordCusord['CUSORDpayment'] = $payment['paymName'];
		$ordCusord['CUSORDhandling'] = $payment['paymTotal'];
		$ordCusord['CUSORDdelivery'] = $delivery['delivName'];
		$ordCusord['CUSORDshipping'] = $delivery['delivTotal'];
		$ordCusord['CUSORDusenetprice'] = $se->get_setting('cbNetPrice_Checked');
		
		// Orderpositions mapping
		$exaOrdPositions = array();
		for($i=0; $i<count($bas); $i++){
			$exaOrdPos = array();
			$exaOrdPos['PARname'] = $bas[$i]['art_num'];
			$exaOrdPos['PAR2CNTname'] = $bas[$i]['art_title'];
			$exaOrdPos['Validfrom'] = date('Y-m-d');
			$exaOrdPos['CUSORDPOSquantity'] = $bas[$i]['art_count'];
			$exaOrdPos['CUSORDPOSprice'] = $bas[$i]['art_price'];
			$exaOrdPos['CUSORDPOSnetprice'] = $bas[$i]['art_netprice'];
			$exaOrdPos['CUSORDPOSbrutprice'] = $bas[$i]['art_brutprice'];
			$exaOrdPos['CUSORDPOSremark'] = substr($bas[$i]['art_attr0'].' '.$bas[$i]['art_attr1'].' '.$bas[$i]['art_attr2'],0,80);
			$exaOrdPos['TAX2TTBrate'] = $bas[$i]['art_vatrate'];
			$exaOrdPos['CUSORDPOSdiscountrate'] = $bas[$i]['art_discount'];
			$exaOrdPos['CUSORDPOSmustage'] = $bas[$i]['art_mustage'];
			$exaOrdPos['UOMname'] = 'Stk';
			$exaOrdPos['UOMlongname'] = 'Stück';
			array_push($exaOrdPositions,$exaOrdPos);
		}
		$ord['ORDcus'] = json_encode($ordCus);
		$ord['ORDadr'] = json_encode($ordAdr);
		$ord['ORDcusord'] = json_encode($ordCusord);
		$ord['ORDcusordpos'] = json_encode($exaOrdPositions);
		
		$Param['TOKEN'] = $exaToken;
		$Param['ORD'] = base64_encode(json_encode($ord));
		
		$cParam = json_encode($Param);
		
		// setze die URL und andere Optionen
		curl_setopt($ch, CURLOPT_URL, $exaURL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		$pos = strpos($exaURL,'https');
		if($pos === false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $cParam);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '.strlen($cParam)));  
		// führe die Aktion aus und gebe die Daten an den Browser weiter
		$res = curl_exec($ch);
		//die(var_dump($res));
	}
	
	$_SESSION['order'] = serialize($order);
	shopowner_htmlmail();
	customer_htmlmail($doSendMail);
	/*if($customer['EmailFormat'] == 'HTML') {
		customer_htmlmail($doSendMail);
	} else {
		customer_textmail($doSendMail);
	}*/
	// Lieferadresse von PayPal löschen
	$customer = $order->getCustomer();
	$customer['delivermrormrs'] = '';
	$customer['deliverfirstname'] = '';
	$customer['deliverlastname'] = '';
	$customer['deliverstreet'] = '';
	$customer['deliverzip'] = '';
	$customer['delivercity'] = '';
	$order->setCustomer($customer);
	
	$order->delBasket();
	$order->Discount = 0;
	$order->$DiscountPercent = 0;
	$_SESSION['order'] = serialize($order);
	//unset($_SESSION['order']);
	echo json_encode($info['redirect_local']);
	
} else {
	die("Etwas ist falsch gelaufen!");
}
function customer_htmlmail($doSendMail) {
	global $sl, $se, $order, $basket, $customer, $info;
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
	
	//$sl->logShoporder($_POST, $aPostDef, 1, '', $se->lngID, $pass);
	
	
	//customer_pwmail();
	
	/*echo "POST-Daten nach sl->getitemavail:<br /><pre>";
	 print_r($_POST);
	 die("</pre>");*/
	$bNewCus = $sl->bNewCus;

	if($se->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
	{
		if (isset($customer['newsletterinput'])){
			require_once("dynsb/module/newsletter2/class.newsletter2.php");
			$nl2 = new newsletter2();
			$nl2->signIn2($customer['cust_email'], 1);
		}
	}
	
	/*echo "<pre>";
	 print_r($_POST);
	 die("</pre>");*/
	if($doSendMail) {
		$me = new gs_mailengine('template/gs_ordermail_customer_html.html');
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
		
		$metxt = new gs_mailengine('template/gs_ordermail_customer_text.html');
		$metxt->get_tags();
		$metxt->parse_data();
		$metxt->parse_tags();
		
		$me->msg = $metxt->content;
		$me->htmlmsg = $me->content;
		
		$succ = $me->sendmail2($customer['cust_email'], $info['answer_subject'], '', '', true);
	}
	
	//die("Ergebnis: " . $succ);
	return;
}

function customer_pwmail() {
	global $sl, $se, $order, $customer, $info;
	$lSendMail = false;
	$cParam = '';
	$cMailTemplate = 'template/gs_pwmail_customer_text.html';
	
	//Spezialfall für den GS-Shop behandeln
	if($info['recipient'] != 'order@gs-software.de') {
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
		
		$me = new gs_mailengine($cMailTemplate);
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
		
		$me->sendmail2($customer['cust_email'], $info['userdata'] . ' ' . $info['shopname'],'', '', false);
	}
	
	return;
}

function customer_textmail($doSendMail) {
	global $sl, $se, $order, $customer, $info;
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
	
	//$sl->logShoporder($_POST, $aPostDef, 1, '', $se->lngID, $pass);
	//$_POST wird durch logShoporder nicht geÃ¤ndert
	
	customer_pwmail();
	
	//echo "POST-Daten nach sl->getitemavail:<br /><pre>";
	//print_r($_POST);
	//die("</pre>");
	$bNewCus = $sl->bNewCus;
	//A TS 27.11.2012 Dekodierung der Artikelnamen
	/*foreach($_POST as $key => $value)
	{
		
		if(strpos($key,"_LANGTAGFNFIELDTEXTITEM_") !== false)
		{
			//$_POST[$key] = $sl->email_friendly(iconv('UTF-8', 'ISO-8859-1',base64_decode($value)));
			$_POST[$key] = base64_decode($value);
		}
	}*/
	if($se->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
	{
		if (isset($customer['newsletterinput']) && $customer['newsletterinput']== 'Y')
		{
			
			require_once("dynsb/module/newsletter2/class.newsletter2.php");
			$nl2 = new newsletter2();
			$nl2->signIn2($customer['cust_email'], $customer['EmailFormat']);
		}
	}
	
	//echo "<pre>";
	//print_r($_POST);
	//die("</pre>");
	if($doSendMail) {
		$me = new gs_mailengine('template/gs_ordermail_customer_text.html');
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
		
		$me->sendmail2($customer['email'], $info['answer_subject'], '', '', false);
	}
	return;
}

function shopowner_htmlmail() {
	global $sl, $se, $order, $basket, $customer, $info;
	
	/*echo "<pre>";
	 print_r($_POST);
	 die("</pre>");*/
	
	$me = new gs_mailengine('template/gs_ordermail_shopowner_html.html');
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
	$me->from = $customer['cust_email'];
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
	
	$metxt = new gs_mailengine('template/gs_ordermail_shopowner_text.html');
	$metxt->get_tags();
	$metxt->parse_data();
	$metxt->parse_tags();
	
	//$attach = $me->createAttachment();
	// Sepa-Platzhalter entfernen
	if($sepahtml !== ''){
		$metxt->content = str_replace('{GSME|TXT|SepaAccept}','',$metxt->content);
	} 
	$me->content = str_replace('{GSME|TXT|SepaAccept}','',$me->content);
	$me->msg = $metxt->content;
	$me->htmlmsg = $me->content;
	$me->sendmail2($info['recipient'], $info['subject'], '', '', true);
	
	return;
}
?>