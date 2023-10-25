<?php
if(isset($_GET['debug'])) {
	$debug = true;
} else {
	$debug = false;
}
if($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors','on');
}
session_start();

require __DIR__ . '/paypalapi/vendor/autoload.php';
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalInvalidCredentialException;
use PayPal\Exception\PayPalConnectionException;

chdir("../");
include_once("inc/class.shopengine.php");
require("inc/class.order.php");
$pp = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);

$clientId = $pp->get_setting('edPPPClientId_Text');
$secret = $pp->get_setting('edPPPSecret_Text');

//Wem dieser Code etwas komisch vorkommt:
//Es soll sichim Zweifel grundsätzlich die Sandbox durchsetzen
//Also wird die Einstellung abgefragt, ob es 'live' ist und dann
//$mode entsprechend gesetzt, ansonsten immer 'sandbox'
//$mode direkt aus der Einstellung zu setzen ist zu heikel
if($pp->get_setting('edPPPMode_Text') == 'live') {
	$mode = 'live';
} else {
	$mode = 'sandbox';
}


$auth = new \PayPal\Auth\OAuthTokenCredential($clientId, $secret);

$apiContext = new \PayPal\Rest\ApiContext($auth);

$apiContext->setConfig(
	array(
		'mode' => $mode
	)
);

//$apiContext = new \PayPal\Rest\ApiContext($clientId,$secret);

if($debug) {
	echo '<pre>__'.$clientId.'__<br>__'.$secret.'__<br>';
}

try {
	$payment = Payment::get($order->ppplus['paymentid'], $apiContext);

	if($debug) {
		print_r($payment);
	}
	$payer = $payment->getPayer();
	$payerInfo = $payer->getPayerInfo();
	$payerID = $payerInfo->getPayerId();
	
	$paymentExecution = new PaymentExecution();
	$paymentExecution->setPayerId($payerID);
	
	$paymentResult = $payment->execute($paymentExecution,$apiContext);
	
	if($debug) {
		print_r($paymentResult);
	}
	
	$order->ppplus['paymentid']= '';
	$_SESSION['order'] = serialize($order);
	$aResult['errno'] = 0;
	$aResult['errmsg'] = 'OK';
	$aResult['paymentid'] = $paymentResult->getId();
	die(json_encode($aResult));
} catch (PayPalInvalidCredentialException $ex) {
	//$aRes = json_decode($ex->getData(),true);
	if($debug) {
		//print_r($aRes);
		print_r($ex);
	}
	/*$aResult['errno'] = -1;
	$aResult['errmsg'] = $ex['message'];*/
	die(json_encode($aResult));
} catch (PayPalConnectionException $ex2) {
	if($debug) {
		//print_r($aRes);
		print_r($ex2);
	}
}



?>