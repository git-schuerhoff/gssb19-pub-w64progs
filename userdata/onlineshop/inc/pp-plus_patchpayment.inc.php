<?php
//error_reporting(E_ALL);
//ini_set('display_errors','on');
session_start();

require __DIR__ . '/paypalapi/vendor/autoload.php';
//use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
//use PayPal\Api\Details;
//use PayPal\Api\FundingInstrument;
//use PayPal\Api\Item;
//use PayPal\Api\ItemList;
//use PayPal\Api\Payer;
//use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
//use PayPal\Api\Transaction;
//use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;
//use PayPal\Api\ShippingAddress;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;

chdir("../");
include_once("inc/class.shopengine.php");
require("inc/class.order.php");
$pp = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$delivery = $order->getDelivery();
$ordpayment = $order->getPayment();
$shipping = 0;
$shipping = $delivery['delivTotal'];

$currency = strtoupper($pp->get_setting('edCurrencySymbol_Text'));
if($currency == '') {
	$currency = 'EUR';
}

if(count($basket) == 0)
{
	die($pp->get_lngtext('LangTagTextBasketEmpty'));
}
else
{
	if($ordpayment['paymInternalName'] == 'PaymentPayPal')
	{
		$clientId = $pp->get_setting('edPPPClientId_Text');
		$secret = $pp->get_setting('edPPPSecret_Text');
		
		$auth = new \PayPal\Auth\OAuthTokenCredential(
			$clientId,// ClientID
			$secret// ClientSecret
		);
		
		$apiContext = new \PayPal\Rest\ApiContext(
			$auth
		);
		
		try {
			$payment = Payment::get($order->ppplus['paymentid'], $apiContext);

			$maxbasket = count($basket);
			$usenetto = ($pp->get_setting('cbNetPrice_Checked') == 'True') ? 1 : 0;
			$totalnetto = 0;
			$totalbrutto = 0;
			$taxtotal = 0;
			$handlingamt = $ordpayment['paymInfo']['handlingamt'];
			$discount = $ordpayment['paymInfo']['discount'];
			for($r = 0; $r < $maxbasket; $r++) {
				$totalnetto += ($basket[$r]['art_netprice'] * $basket[$r]['art_count']);
				$totalbrutto += ($basket[$r]['art_brutprice'] * $basket[$r]['art_count']);
				$taxtotal += ($basket[$r]['art_vat'] * $basket[$r]['art_count']);
			}
			$totalnetto = round($totalnetto,2);
			$taxtotal = round($taxtotal,2);
			
			$patchReplace = new \PayPal\Api\Patch();
			$patchReplace->setOp('replace')
				->setPath('/transactions/0/amount')
				->setValue(json_decode('{
								"total": "'.$totalbrutto.'",
								"currency": "'.$currency.'",
								"details": {
									"subtotal": "'.$totalnetto.'",
									"shipping": "'.$shipping.'",
									"tax":"'.$taxtotal.'"
								}
							}'));

			$patchRequest = new \PayPal\Api\PatchRequest();
			$patchRequest->setPatches(array($patchReplace));
			
			$result = $payment->update($patchRequest, $apiContext);
			$_SESSION['order'] = serialize($order);
			$paymentId = $payment->getId();
			die($result);
			
		} catch (Exception $ex) {
			echo $ex->getData();
			exit(1);
		}
	}
	else
	{
		die($pp->get_lngtext('LangTagTextNoPaymentsAvailable'));
	}
}
?>