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

$debug = false;

$aResult = array("errno" => 0,"errmsg" => '',"result" => array());

/*if(!isset($_SESSION['buyerinfo'])) {
	$aResult['errno'] = -2;
	$aResult['errmsg'] = 'No buyer-info send';
	die(json_encode($aResult));
} else {
	if(empty($_SESSION['buyerinfo'])) {
		$aResult['errno'] = -3;
		$aResult['errmsg'] = 'Buyer-info is empty';
		die(json_encode($aResult));
	}
}*/

require __DIR__ . '/paypalapi/vendor/autoload.php';
//use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentCard;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\BankAccount;
use PayPal\Api\Address;
use PayPal\Api\ShippingAddress;

chdir("../");
include_once("inc/class.shopengine.php");
require("inc/class.order.php");
$pp = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);

if($debug) {
	echo '<pre>';
	print_r($order);
}

//TS: Prüfen, ob bereits ein Payment erzeugt wurde
/*if(isset($order->ppplus['paymentid']) && isset($order->ppplus['approvalurl'])) {
	if(strlen(trim($order->ppplus['paymentid'])) > 0 && strlen(trim($order->ppplus['approvalurl'])) > 0) {
		//Es wurde bereits ein Payment erzeugt, Werte zurückgeben und Skript beenden
		$aResult['errno'] = 0;
		$aResult['errmsg'] = '';
		$aResult['result'] = array("paymentid" => $order->ppplus['paymentid'], "approvalurl" => $order->ppplus['approvalurl']);
		die(json_encode($aResult));
	}
}*/

$payment = $order->getPayment();
//switch($_SESSION['buyerinfo']['paymenttype']) {
switch($payment['paymInternalName']) {
	case 'PaymentPayPal':
		$paymentMethod = 'paypal';
		break;
	case 'PaymentInvoice':
		$paymentMethod = 'pay_upon_invoice';
		break;
	case 'PaymentInAdvance':
		$paymentMethod = 'bank';
		break;
	case 'PaymentDirectDebit':
		$paymentMethod = 'bank';
		break;
	case 'PaymentCreditCard':
		$paymentMethod = 'credit_card';
		break;
	default:
		$paymentMethod = 'paypal';
		break;
}

if($debug) {
	/*print_r($_SESSION['basket']);
	print_r($_SESSION['delivery']);*/
}

$currency = strtoupper($pp->get_setting('edCurrencySymbol_Text'));
if($currency == '') {
	$currency = 'EUR';
}

$clientId = $pp->get_setting('edPPPClientId_Text');
$secret = $pp->get_setting('edPPPSecret_Text');

//Wem dieser Code etwas komisch vorkommt:
//Es soll sichim Zweifel grunds�tzlich die Sandbox durchsetzen
//Also wird die Einstellung abgefragt, ob es 'live' ist und dann
//$mode entsprechend gesetzt, ansonsten immer 'sandbox'
//$mode direkt aus der Einstellung zu setzen ist zu heikel
if($pp->get_setting('edPPPMode_Text') == 'live') {
	$mode = 'live';
} else {
	$mode = 'sandbox';
}


if($debug) {
	echo $clientId.'<br>';
	echo $secret.'<br>';
}

//Grunds�tzliche Authentifizierung
$auth = new \PayPal\Auth\OAuthTokenCredential(
	$clientId,// ClientID
	$secret// ClientSecret
);

if($debug) {
	print_r($auth);
}

$apiContext = new \PayPal\Rest\ApiContext(
	$auth
);

$apiContext->setConfig(
	array(
		'mode' => $mode
	)
);

if($debug) {
	print_r($apiContext);
}

//Bezahler anlegen
$payer = new Payer();
$payer->setPaymentMethod($paymentMethod);

//Rechnungsadresse
$customer = $order->getCustomer();
$payment = $order->getPayment();
$delivery = $order->getDelivery();
$billingAddress = new Address();
$billingAddress->setLine1($customer['street'])
				->setLine2($customer['street2'])
				->setCity($customer['city'])
				->setCountryCode($customer['stateISO'])
				->setPostalCode($customer['zip']);
if(isset($customer['phone'])) {
	if($customer['phone'] != '') {
		$billingAddress->setPhone($customer['phone']);
	}
}


//Lieferadresse
$shippingAddress = new ShippingAddress();
if(isset($customer['UseShippingAddress'])) {
	if($customer['UseShippingAddress'] == 'Y') {
		$shippingAddress->setLine1($customer['deliverstreet'])
		->setLine2($customer['deliverstreet2'])
		->setCity($customer['delivercity'])
		->setCountryCode($customer['stateISO'])
		->setPostalCode($customer['deliverzip']);
	} else {
		$shippingAddress->setLine1($billingAddress->getLine1())
						->setLine2($billingAddress->getLine2())
						->setCity($billingAddress->getCity())
						->setCountryCode($billingAddress->getCountryCode())
						->setPostalCode($billingAddress->getPostalCode());
	}
} else {
	$shippingAddress->setLine1($billingAddress->getLine1())
					->setLine2($billingAddress->getLine2())
					->setCity($billingAddress->getCity())
					->setCountryCode($billingAddress->getCountryCode())
					->setPostalCode($billingAddress->getPostalCode());
}

//Weitere Informationen zum Bezahler
$payerInfo = new PayerInfo();
$payerInfo->setBillingAddress($billingAddress)
			 ->setShippingAddress($shippingAddress)
			 ->setFirstName($customer['firstname'])
			 ->setLastName($customer['lastname']);
			
$payer->setPayerInfo($payerInfo);

//Artikelpositionen zusammenstellen
$basket = $order->getBasket();
$maxbasket = count($basket);
$usenetto = ($pp->get_setting('cbNetPrice_Checked') == 'True') ? 1 : 0;
$aItems = array();
$totalnetto = 0;
$totalbrutto = 0;
$taxtotal = 0;
for($r = 0; $r < $maxbasket; $r++) {
	$item = new Item();
	
	// Warenwertrabbatt ber�cksichtigen
	$art_price = $basket[$r]['art_price'];
	
	$item_descr = $basket[$r]['art_title'];
	$item_descr .= ($basket[$r]['art_attr0'] != "") ? ", " . $basket[$r]['art_attr0'] : "";
	$item_descr .= ($basket[$r]['art_attr1'] != "") ? ", " . $basket[$r]['art_attr1'] : "";
	$item_descr .= ($basket[$r]['art_attr2'] != "") ? ", " . $basket[$r]['art_attr2'] : "";
	$item_descr .= ($basket[$r]['art_textfeld'] != "") ? ", " . $basket[$r]['art_textfeld'] : "";
	$item->setName($basket[$r]['art_title'])
			->setDescription($item_descr)
			->setCurrency($currency)
			->setQuantity($basket[$r]['art_count'])
			->setTax($basket[$r]['art_vat'])
			->setPrice($basket[$r]['art_netprice']);
	$aItems[] = $item;
	$totalnetto += ($basket[$r]['art_netprice'] * $basket[$r]['art_count']);
	$totalbrutto += ($basket[$r]['art_brutprice'] * $basket[$r]['art_count']);
	$taxtotal += ($basket[$r]['art_vat'] * $basket[$r]['art_count']);
}

if($debug) {
	echo '<br>Netto:'.$totalnetto;
	echo '<br>Brutto'.$totalbrutto;
	echo '<br>Tax'.$taxtotal;
}

$handlingamt = $payment['paymInfo']['handlingamt'];
$discount = $payment['paymInfo']['discount'];
/*if($payment['paymInfo']['paymUseCashDiscount'] != 'Y'){	
	if($payment['paymInfo']['paymCharge']!= 0) {
		$handlingamt = $payment['paymInfo']['paymCharge'];
	}
	if($payment['paymInfo']['paymChargePercent'] != 0) {
		$handlingamt += round((($totalbrutto) / 100) * $payment['paymInfo']['paymChargePercent'],2);//Auf Bruttowarenwert
	}
} else {
	if($payment['paymInfo']['paymCashDiscount'] != 0){
		$discount = $payment['paymInfo']['paymCashDiscount']* -1;
	}
	if($payment['paymInfo']['paymCashDiscountPercent'] != 0){
		$discount -= round((($totalbrutto) / 100) * $payment['paymInfo']['paymCashDiscountPercent'],2);//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
	}
}*/

if($discount != 0) {
	$item = new Item();
	$item->setName('Rabatt')
			->setDescription('Rabatt')
			->setCurrency($currency)
			->setQuantity(1)
			->setTax(0)
			->setPrice($discount);
	$aItems[] = $item;
}

$itemList = new ItemList();
$itemList->setItems($aItems);

$shipping = 0;
//$shipping = $_SESSION['delivery']['ship']['charge'];
$shipping = $delivery['delivTotal'];
if($debug) {
	echo '<br>Handling:'.$handlingamt;
	echo '<br>Discount'.$discount;
	echo '<br>Shipping'.$shipping;
	echo '<br>--------------------------';
	echo '<br>Subtotal'.($totalnetto+$discount);
	echo '<br>Total'.($totalnetto+$taxtotal+$shipping+$handlingamt+$discount);
}

//Artikeldaten zu payment hinzuf�gen
$details = new Details();
$details->setSubtotal($totalnetto+$discount)
			->setShipping($shipping)
			->setTax($taxtotal)
			->setHandlingFee($handlingamt);

$amount = new Amount();
$amount->setCurrency($currency)
		 ->setTotal($totalnetto+$taxtotal+$shipping+$handlingamt+$discount)
		 ->setDetails($details);

$transaction = new Transaction();
$transaction->setAmount($amount)
				->setItemList($itemList)
				->setDescription("Payment Beschreibung")
				->setInvoiceNumber(uniqid());

//Payment erzeugen
$payment = new Payment();
$payment->setIntent("sale")
			->setPayer($payer)
			->setTransactions(array($transaction));

//Bezahlung mit PayPal
if($paymentMethod == 'paypal') {
	//$return_url = $pp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&pp_status=ok";
	$return_url = $pp->shopurl . "index.php?page=buy&pp_status=ok";
	//$cancel_url = $pp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&pp_status=cancel";
	$cancel_url = $pp->shopurl . "index.php?page=buy&pp_status=cancel";
	$redirect_urls = new RedirectUrls();
	$redirect_urls->setReturnUrl($return_url);
	$redirect_urls->setCancelUrl($cancel_url);
	$payment->setRedirectUrls($redirect_urls);
	
}

try {
	$payment->create($apiContext);
} catch (PayPalConnectionException $ex) {
	$aRes = json_decode($ex->getData(),true);
	if($debug) {
		print_r($aRes);
	}
	$aResult['errno'] = -1;
	if(isset($aRes['error']) && isset($aRes['error_description'])) {
		$aResult['errmsg'] = $aRes['error'].': '.$aRes['error_description'];
	} else {
		$aResult['errmsg'] = $aRes['name'].': '.$aRes['message'];
	}
	die(json_encode($aResult));
}

if($debug) {
	print_r($payment);
}

$paymentId = $payment->getId();
$approvalUrl = $payment->getApprovalLink();
//$approvalUrl = 'https://api.sandbox.paypal.com/v1/payments/payment/'.$paymentId;
/*
$_SESSION['pp-plus']['paymentid'] = $paymentId;
$_SESSION['pp-plus']['approvalurl'] = $approvalUrl;
*/
$order->ppplus['paymentid'] = $paymentId;
$order->ppplus['approvalurl'] = $approvalUrl;

$_SESSION['order'] = serialize($order);

$aResult['errno'] = 0;
$aResult['errmsg'] = '';
$aResult['result'] = array("paymentid" => $paymentId, "approvalurl" => $approvalUrl);

die(json_encode($aResult));

?>