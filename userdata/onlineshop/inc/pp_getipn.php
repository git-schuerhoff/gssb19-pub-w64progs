<?php
//session_start();
//error_reporting(E_ALL);
//ini_set('display_errors','on');
//$_POST = $_GET;
//require('class.paypalipn.php');
/*$data_text = "";
foreach ($_POST as $key => $value) {
	$data_text .= $key . " = " . $value . "\r\n";
}
mail('demokunde@gs-shopbuilder.de', 'IPN', $data_text, "From: " . 'schuerhoff@gs-software.de');*/
require __DIR__ . '/paypalclassic/class.paypalipn.php';
use PaypalIPN;
$ipn = new PaypalIPN();
$ipn->usePHPCerts();
// Use the sandbox endpoint during testing.
if($_POST['test_ipn'] == 1) {
	$ipn->useSandbox();
}
$verified = $ipn->verifyIPN();
if ($verified) {
	/*
	 * Process IPN
	 * A list of variables is available here:
	 * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
	 */
}
// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");
?>