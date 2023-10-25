<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
include_once("inc/class.order.php");
$pp = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
if(count($basket) == 0)
{
	die(json_encode(array("error_code" => -1, "error_message" => "Basket empty")));
}
else
{
	//TS: Im Zweifel soll sich stehts die Sandbox durchsetzen
	if($pp->get_setting('rbPPUseSandbox_Checked') != 'False')
	{
		//Sandbox
		$ppurl = 'https://api-3t.sandbox.paypal.com/nvp';
		$pprdr = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		$ppusr = $pp->get_setting('edPPEMailSand_Text');
		$pppwd = $pp->get_setting('edPPPWSand_Text');
		$ppsig = $pp->get_setting('edPPSigSand_Text');
	}
	else
	{
		if($pp->get_setting('rbPPUseLivesystem_Checked') == 'True' && $pp->get_setting('rbPPUseSandbox_Checked') == 'False')
		{
			//Livesystem
			$ppurl = 'https://api-3t.paypal.com/nvp';
			$pprdr = 'https://www.paypal.com/cgi-bin/webscr';
			$ppusr = $pp->get_setting('edPayPalID_Text');
			$pppwd = $pp->get_setting('edPPPWLive_Text');
			$ppsig = $pp->get_setting('edPPSigLive_Text');
		}
		else
		{
			//Sandbox
			$ppurl = 'https://api-3t.sandbox.paypal.com/nvp';
			$pprdr = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			$ppusr = $pp->get_setting('edPPEMailSand_Text');
			$pppwd = $pp->get_setting('edPPPWSand_Text');
			$ppsig = $pp->get_setting('edPPSigSand_Text');
		}
	}
	$params = "USER=" . $ppusr .
				 "&PWD=" . $pppwd .
				 "&SIGNATURE=" . $ppsig .
				 "&VERSION=109.0" .
				 "&METHOD=GetExpressCheckoutDetails" .
				 "&token=" . urlencode($_GET['token']);
	$ch = curl_init();
	// setze die URL und andere Optionen
	curl_setopt($ch, CURLOPT_URL, $ppurl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// führe die Aktion aus und gebe die Daten an den Browser weiter
	$erg = curl_exec($ch);
	if(curl_errno($ch))
	{
		die(json_encode(array("error_code" => -1, "error_message" => curl_error($ch))));
	}
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	curl_close($ch);
	$aResponse = $pp->get_url_array($erg);
	//die(print_r($aResponse));
	if($aResponse['ACK'] != 'Success')
	{
		die(json_encode(array("error_code" => -1, "error_message" => "ACK=" . $aResponse['ACK'] . 
				 "&Schritt=GetExpressCheckoutDetails" .
				 "&Fehlernr.=" . $aResponse['L_ERRORCODE0'] .
				 "&Fehler kurz=" . $aResponse['L_SHORTMESSAGE0'] .
				 "&Fehler lang=" . $aResponse['L_LONGMESSAGE0'])));
	}
	
	//Schritt 4:  DoExpressCheckoutPayment
	$params = "USER=" . $ppusr .
				 "&PWD=" . $pppwd .
				 "&SIGNATURE=" . $ppsig .
				 "&VERSION=109.0" .
				 "&METHOD=DoExpressCheckoutPayment" .
				 "&PAYMENTACTION=Sale" .
				 "&CURRENCYCODE=EUR" .
				 "&AMT=" . $aResponse['AMT'] .
				 "&TOKEN=" . $aResponse['TOKEN'] .
				 "&PAYERID=" . $aResponse['PAYERID'];
				 
	$ch = curl_init();
	// setze die URL und andere Optionen
	curl_setopt($ch, CURLOPT_URL, $ppurl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// führe die Aktion aus und gebe die Daten an den Browser weiter
	$erg = curl_exec($ch);
	if(curl_errno($ch))
	{
		die(json_encode(array("error_code" => -1, "error_message" => curl_error($ch))));
	}
	// schließe den cURL-Handle und gebe die Systemresourcen frei
	curl_close($ch);
	$aResponse = $pp->get_url_array($erg);
	//die(print_r($aResponse));
	if($aResponse['ACK'] != 'Success')
	{
		die(json_encode(array("error_code" => -1, "error_message" => "ACK=" . $aResponse['ACK'] . 
				 "&Schritt=DoExpressCheckoutPayment" .
				 "&Fehlernr.=" . $aResponse['L_ERRORCODE0'] .
				 "&Fehler kurz=" . $aResponse['L_SHORTMESSAGE0'] .
				 "&Fehler lang=" . $aResponse['L_LONGMESSAGE0'])));
	}
		
	//Checken, ob mit GiroPay bezahlt wurde
	if($aResponse['PAYMENTSTATUS'] == 'Created')
	{
		//Redirect zu GiroPay
		header("Location: " . $pprdr . "?cmd=_complete-express-checkout&token=" . $aResponse['PAYMENTSTATUS']);
	}
	
	$note = 'ok';
	if(isset($aResponse['NOTE']))
	{
		$note = $aResponse['NOTE'];
	}
	die(json_encode(array("error_code" => 0, "error_message" => $note)));
}

?>