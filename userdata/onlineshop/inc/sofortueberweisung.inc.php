<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$su = new gs_shopengine();

require_once('inc/sofortlib/payment/sofortLibSofortueberweisung.inc.php');

// enter your configuration key – you only can create a new configuration key by creating
// a new Gateway project in your account at sofort.com
$configkey = $su->get_setting('edSofortUserId_Text');//'12345:12345:5dbdad2bc861d907eedfd9528127d002';

$Sofortueberweisung = new Sofortueberweisung($configkey);

$Sofortueberweisung->setAmount($_SESSION['invoicetotal']);
$Sofortueberweisung->setCurrencyCode('EUR');
$Sofortueberweisung->setSenderSepaAccount('88888888', '12345678', 'Max Mustermann');
$Sofortueberweisung->setSenderCountryCode('DE');
$Sofortueberweisung->setReason('Testueberweisung', 'Verwendungszweck');
//$Sofortueberweisung->setSuccessUrl(urlencode($su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=thankyou"), true);
$Sofortueberweisung->setSuccessUrl(urlencode($su->shopurl . "index.php?page=thankyou"), true);
//$Sofortueberweisung->setAbortUrl(urlencode($su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&su_status=cancel"));
$Sofortueberweisung->setAbortUrl(urlencode($su->shopurl . "index.php?page=buy&su_status=cancel"));

//$Sofortueberweisung->setNotificationUrl(urlencode($su->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&su_status=ok"));
$Sofortueberweisung->setNotificationUrl(urlencode($su->shopurl . "index.php?page=buy&su_status=ok"));
$Sofortueberweisung->setCustomerprotection(true);


$Sofortueberweisung->sendRequest();

if($Sofortueberweisung->isError()) {
	//SOFORT-API didn't accept the data
	echo $Sofortueberweisung->getError();
} else {
	//buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
	$paymentUrl = $Sofortueberweisung->getPaymentUrl();
	header('Location: '.$paymentUrl);
}

