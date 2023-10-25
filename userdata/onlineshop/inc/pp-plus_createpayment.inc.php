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

$aResult = array("errno" => 0,"errmsg" => '',"result" => array());

if(!isset($_SESSION['buyerinfo'])) {
	$aResult['errno'] = -2;
	$aResult['errmsg'] = 'No buyer-info send';
	die(json_encode($aResult));
} else {
	if(empty($_SESSION['buyerinfo'])) {
		$aResult['errno'] = -3;
		$aResult['errmsg'] = 'Buyer-info is empty';
		die(json_encode($aResult));
	}
}

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
$pp = new gs_shopengine();

switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$aLocal = &$_GET;
		break;
	case 'POST':
		$aLocal = &$_POST;
		break;
	default:
		$aLocal = &$_POST;
		break;
}

if($debug) {
	echo '<pre>';
	print_r($_SESSION['buyerinfo']);
}

switch($_SESSION['buyerinfo']['paymenttype']) {
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
	print_r($_SESSION['basket']);
	print_r($_SESSION['delivery']);
}

$currency = strtoupper($pp->get_setting('edCurrencySymbol_Text'));
if($currency == '') {
	$curreny = 'EUR';
}

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


if($debug) {
	echo $clientId.'<br>';
	echo $secret.'<br>';
}

//Grundsätzliche Authentifizierung
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
$billingAddress = new Address();
$billingAddress->setLine1($_SESSION['buyerinfo']['billingaddress'])
					->setLine2($_SESSION['buyerinfo']['billingaddress2'])
					->setCity($_SESSION['buyerinfo']['billingcity'])
					->setCountryCode($_SESSION['buyerinfo']['billingstate'])
					->setPostalCode($_SESSION['buyerinfo']['billingzip']);
if($_SESSION['buyerinfo']['phone'] != '') {
	$billingAddress->setPhone($_SESSION['buyerinfo']['phone']);
}

//Lieferadresse
$shippingAddress = new ShippingAddress();
if($_SESSION['buyerinfo']['useshippingaddress'] == 1) {
	$shippingAddress->setLine1($_SESSION['buyerinfo']['shippingaddress'])
					->setLine2($_SESSION['buyerinfo']['shippingaddress2'])
					->setCity($_SESSION['buyerinfo']['shippingcity'])
					->setCountryCode($_SESSION['buyerinfo']['shippingstate'])
					->setPostalCode($_SESSION['buyerinfo']['shippingzip']);
} else {
	$shippingAddress->setLine1($billingAddress->getLine1())
					->setLine2($billingAddress->getLine2())
					->setCity($billingAddress->getCity())
					->setCountryCode($billingAddress->getCountryCode())
					->setPostalCode($billingAddress->getPostalCode());
}

//Kreditkarteninformationen zum Bezahler anlegen
$cardtype = '';
if($paymentMethod == 'credit_card') {
	$cardtype = $pp->getPPCreditCardType($_SESSION['buyerinfo']['creditcardname']);
	if($cardtype == '') {
		die($pp->get_language('LangTagCreditCardNotAccept'));
	}
	$card = new PaymentCard();
	$card->setType($cardtype)
			->setNumber($_SESSION['buyerinfo']['creditcardnumber'])
			->setExpireMonth(str_pad($_SESSION['buyerinfo']['creditcardexpmonth'],2,'0',STR_PAD_LEFT))
			->setExpireYear(str_pad($_SESSION['buyerinfo']['creditcardexpyear'],4,'20',STR_PAD_LEFT))
			->setCvv2($_SESSION['buyerinfo']['creditcardissuenumber'])
			->setFirstName($_SESSION['buyerinfo']['creditcardholderfirstname'])
			->setBillingCountry($_SESSION['buyerinfo']['billingstate'])
			->setBillingAddress($billingAddress)
			->setLastName($_SESSION['buyerinfo']['creditcardholderlastname']);
	$fi = new FundingInstrument();
	$fi->setPaymentCard($card);
	$payer->setFundingInstruments(array($fi));
}

//Bankinformationen zum Bezahler
$bank = '';
if($paymentMethod == 'bank') {
	$bank = new BankAccount();
	$bank->setAccountNumberType('IBAN')
			->setAccountNumber($_SESSION['buyerinfo']['directdebitiban'])
			->setBankName($_SESSION['buyerinfo']['directdebitbank'])
			->setFirstName($_SESSION['buyerinfo']['directdebitholderfirstname'])
			->setLastName($_SESSION['buyerinfo']['directdebitholderlastname'])
			->setBirthDate($pp->date2mysql($_SESSION['buyerinfo']['birthdate']))
			->setBillingAddress($billingAddress);
	$fi = new FundingInstrument();
	$fi->setBankAccount($bank);
	$payer->setFundingInstruments(array($fi));
}

//Weitere Informationen zum Bezahler
$payerInfo = new PayerInfo();
$payerInfo->setBillingAddress($billingAddress)
			 ->setShippingAddress($shippingAddress)
			 ->setFirstName($_SESSION['buyerinfo']['billingfirstname'])
			 ->setLastName($_SESSION['buyerinfo']['billinglastname']);
			
$payer->setPayerInfo($payerInfo);

//Artikelpositionen zusammenstellen
$maxbasket = count($_SESSION['basket']);
$usenetto = ($pp->get_setting('cbNetPrice_Checked') == 'True') ? 1 : 0;
$aItems = array();
$totalnetto = 0;
$totalbrutto = 0;
$taxtotal = 0;
for($r = 0; $r < $maxbasket; $r++) {
	$item = new Item();
	//Trialartikel nicht übermitteln
	/*if($_SESSION['basket'][$r]['art_isttrialitem'] == 1) {
		continue;
	}
	if($usenetto == 1) {
		$netto = round($_SESSION['basket'][$r]['art_price'],2);
		$brutto = round($_SESSION['basket'][$r]['art_price'] * (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
		$tax = $brutto - $netto;
	} else {
		$netto = round($_SESSION['basket'][$r]['art_price'] / (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
		$brutto = round($_SESSION['basket'][$r]['art_price'],2);
		$tax = round($_SESSION['basket'][$r]['art_price'],2) - $netto;
	}*/
	// Warenwertrabbatt berücksichtigen
	$art_price = $_SESSION['basket'][$r]['art_price'] - ($_SESSION['basket'][$r]['art_price'] / 100 * $_SESSION['basket'][$r]['art_discount']);
	
	if($usenetto == 1) {
		$netto = round($art_price,2);
		$brutto = round($art_price * (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
		$tax = $brutto - $netto;
	} else {
		$netto = round($art_price / (1 + ($_SESSION['basket'][$r]['art_vatrate'] / 100)),2);
		$brutto = round($art_price,2);
		$tax = $brutto - $netto;
	}
    
	$item_descr = $_SESSION['basket'][$r]['art_title'];
	$item_descr .= ($_SESSION['basket'][$r]['art_attr0'] != "") ? ", " . $_SESSION['basket'][$r]['art_attr0'] : "";
	$item_descr .= ($_SESSION['basket'][$r]['art_attr1'] != "") ? ", " . $_SESSION['basket'][$r]['art_attr1'] : "";
	$item_descr .= ($_SESSION['basket'][$r]['art_attr2'] != "") ? ", " . $_SESSION['basket'][$r]['art_attr2'] : "";
	$item_descr .= ($_SESSION['basket'][$r]['art_textfeld'] != "") ? ", " . $_SESSION['basket'][$r]['art_textfeld'] : "";
	$item->setName($_SESSION['basket'][$r]['art_title'])
			->setDescription($item_descr)
			->setSku($_SESSION['basket'][$r]['art_num'])
			->setCurrency($currency)
			->setQuantity($_SESSION['basket'][$r]['art_count'])
			->setTax($tax)
			->setPrice($netto);
	$aItems[] = $item;
	$totalnetto += ($netto * $_SESSION['basket'][$r]['art_count']);
	$totalbrutto += ($brutto * $_SESSION['basket'][$r]['art_count']);
	$taxtotal += ($tax * $_SESSION['basket'][$r]['art_count']);
}

//Warenkorbsummen aus beziehen (Shopengine)
/*$totalnetto = 0;
$totalbrutto = 0;
$taxtotal = 0;
$pp->getBasketTotals($totalnetto,$totalbrutto,$taxtotal);*/

if($debug) {
	echo '<br>Netto:'.$totalnetto;
	echo '<br>Brutto'.$totalbrutto;
	echo '<br>Tax'.$taxtotal;
}

$handlingamt = 0;
$discount = 0;
if($_SESSION['delivery']['paym']['usecashdiscount'] != 'Y') {
	if($_SESSION['delivery']['paym']['charge'] != 0) {
		$handlingamt = $_SESSION['delivery']['paym']['charge'];
	}
	if($_SESSION['delivery']['paym']['chargepercent'] != 0) {
		//$handlingamt += (($totalbrutto+$shippingamt) / 100) * $_SESSION['delivery']['paym']['chargepercent'];//Auf Brutto-Rechnungssumme
		$handlingamt += round((($totalbrutto) / 100) * $_SESSION['delivery']['paym']['chargepercent'],2);//Auf Bruttowarenwert
	}
} else {
	if($_SESSION['delivery']['paym']['cashdiscount'] != 0) {
		$discount = $_SESSION['delivery']['paym']['cashdiscount'] * -1;
	}
	if($_SESSION['delivery']['paym']['cashdiscountpercent'] != 0) {
		//$discount -= (($totalbrutto+$shippingamt) / 100) * $_SESSION['delivery']['paym']['cashdiscountpercent'];//Auf Brutto-Rechnungssumme (Skonto)
		$discount -= round((($totalbrutto) / 100) * $_SESSION['delivery']['paym']['cashdiscountpercent'],2);//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
	}
}

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
$shipping = $_SESSION['delivery']['ship']['charge'];

if($debug) {
	echo '<br>Handling:'.$handlingamt;
	echo '<br>Discount'.$discount;
	echo '<br>Shipping'.$shipping;
	echo '<br>--------------------------';
	echo '<br>Subtotal'.($totalnetto+$discount);
	echo '<br>Total'.($totalnetto+$taxtotal+$shipping+$handlingamt+$discount);
}

//Artikeldaten zu payment hinzufügen
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
$_SESSION['pp-plus']['paymentid'] = $paymentId;
$_SESSION['pp-plus']['approvalurl'] = $approvalUrl;

$aResult['errno'] = 0;
$aResult['errmsg'] = '';
$aResult['result'] = array("paymentid" => $paymentId, "approvalurl" => $approvalUrl);

die(json_encode($aResult));

?>