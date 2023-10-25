<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php"); 
require("inc/class.order.php");
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$invoicetotal = $order->getBasketInvoiceTotal();
$sp = new gs_shopengine();
if(count($basket) == 0)
{
	die(json_encode(array("error_code" => -1, "error_message" => "Basket empty")));
}
else
{
$spurl = 'https://www.saferpay.com/hosting/createpayinit.asp?';//'https://www.saferpay.com/hosting/Redirect.asp?';

	if($sp->get_setting('cbSaferpayTestMode_Checked') == 'True')
	{
		//SandboxDoExpressCheckoutPayment
		$params = "ACCOUNTID=99867-94913159" . 
				 "&spPassword=XAjc3Kna" .
				 //"&SUCCESSLINK=" . urlencode($sp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&sp_status=ok") .
				 "&SUCCESSLINK=" . urlencode($sp->shopurl . "index.php?page=buy&sp_status=ok") .
				 //"&FAILLINK=" . urlencode($sp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&sp_status=cancel") .
				 "&FAILLINK=" . urlencode($sp->shopurl . "index.php?page=buy&sp_status=cancel") .
				 "&AMOUNT=" . str_replace('.','',$invoicetotal) .
				 "&CURRENCY=" . $sp->get_setting('edCurrencySymbol_Text') .
				 "&ORDERID=" . $sp->get_setting('edShopName_Text') .
				 "&DESCRIPTION=Saferpay eCommerce" .
				 "&DELIVERY=no" .
				 "&CCNAME=yes" .
				 "&SHOWLANGUAGES=yes" .
				 "&CCCVC=yes";
	}
	else
	{
		//Livesystem
		$params = "ACCOUNTID=" . $sp->get_setting('edSaferpayID_Text') .
				 "&spPassword=" . $sp->get_setting('edSaferpayPass_Text') .
				 //"&SUCCESSLINK=" . urlencode($sp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&sp_status=ok") .
				 "&SUCCESSLINK=" . urlencode($sp->shopurl . "index.php?page=buy&sp_status=ok") .
				 //"&FAILLINK=" . urlencode($sp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&sp_status=cancel") .
				 "&FAILLINK=" . urlencode($sp->shopurl . "index.php?page=buy&sp_status=cancel") .
				 "&AMOUNT=" . str_replace('.','',$invoicetotal) .
				 "&CURRENCY=" . $sp->get_setting('edCurrencySymbol_Text') .
				 "&ORDERID=" . $sp->get_setting('edShopName_Text') .
				 "&DESCRIPTION=Saferpay eCommerce" .
				 "&DELIVERY=no" .
				 "&CCNAME=yes" .
				 "&CCCVC=yes";
		
	}
	
				 
	$ch = curl_init();
	// setze die URL und andere Optionen
	curl_setopt($ch, CURLOPT_URL, $spurl);
	curl_setopt($ch, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
	curl_setopt($ch, CURLOPT_HEADER, false);			// no header in output
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 	// receive returned characters
	// führe die Aktion aus und gebe die Daten an den Browser weiter
	$erg = curl_exec($ch);
	if(curl_errno($ch))
	{
		die(json_encode(array("error_code" => -2, "error_message" => curl_error($ch))));
	}
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	curl_close($ch);
	header("Location: " . $erg);
}

?>