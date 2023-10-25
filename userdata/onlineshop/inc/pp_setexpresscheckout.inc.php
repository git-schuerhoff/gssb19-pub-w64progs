<?php
/*session_start();
chdir("../");
include_once("inc/class.shopengine.php");
include_once("inc/class.order.php");
$pp = new gs_shopengine();
$order = new Order();
$order = unserialize($_SESSION['order']);*/
$basket = $order->getBasket();

if(!isset($basket))
{
	die($pp->get_lngtext('LangTagTextBasketEmpty'));
}

if(count($basket) == 0)
{
	die($pp->get_lngtext('LangTagTextBasketEmpty'));
}
else
{
	//$aLocal = $_POST;
	/*$aLocal = $_GET;
	if($aLocal['payment_type'] == 'PaymentPayPal')
	{*/
		//Schritt 1: SetExpressCheckout
		//TS: Im Zweifel soll sich stehts die Sandbox durchsetzen
		if($order->se->get_setting('rbPPUseSandbox_Checked') != 'False')
		{
			//Sandbox
			$ppurl = 'https://api-3t.sandbox.paypal.com/nvp';
			$pprdr = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			$ppusr = $order->se->get_setting('edPPEMailSand_Text');
			$pppwd = $order->se->get_setting('edPPPWSand_Text');
			$ppsig = $order->se->get_setting('edPPSigSand_Text');
		}
		else
		{
			if($order->se->get_setting('rbPPUseLivesystem_Checked') == 'True' && $order->se->get_setting('rbPPUseSandbox_Checked') == 'False')
			{
				//Livesystem
				$ppurl = 'https://api-3t.paypal.com/nvp';
				$pprdr = 'https://www.paypal.com/cgi-bin/webscr';
				$ppusr = $order->se->get_setting('edPayPalID_Text');
				$pppwd = $order->se->get_setting('edPPPWLive_Text');
				$ppsig = $order->se->get_setting('edPPSigLive_Text');
			}
			else
			{
				//Sandbox
				$ppurl = 'https://api-3t.sandbox.paypal.com/nvp';
				$pprdr = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				$ppusr = $order->se->get_setting('edPPEMailSand_Text');
				$pppwd = $order->se->get_setting('edPPPWSand_Text');
				$ppsig = $order->se->get_setting('edPPSigSand_Text');
			}
		}
		
		$total = 0;
		$tax = 0;
		$taxtotal = 0;
		/*echo "<pre>";
		print_r($_SESSION['basket']);
		print_r($_GET);
		die("</pre>");*/
		include_once("inc/pp_params_v109.inc.php");
		
		/*echo "<pre>";
		print_r(explode("&",$params));
		die("</pre>");*/
		
		// erzeuge einen neuen cURL-Handle
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
			echo json_encode('cURL-Fehler: ' . curl_error($ch) . "<br />");
		}
		// schließe den cURL-Handle und gebe die Systemresourcen frei
		curl_close($ch);
		$aResponse = $order->se->get_url_array($erg);
		/*print_r($aResponse);*/
		if($aResponse['ACK'] != 'Success')
		{
			echo json_encode("ACK=" . $aResponse['ACK'] . 
				 "<br>Schritt=SetExpressCheckout" .
				 "<br>Fehlernr.=" . $aResponse['L_ERRORCODE0'] .
				 "<br>Fehler kurz=" . urldecode($aResponse['L_SHORTMESSAGE0']) .
				 "<br>Fehler lang=" . urldecode($aResponse['L_LONGMESSAGE0']));
		}
		else
		{
			//Schritt 2:  REDIRECT zu PayPal
			//header("Location: " . $pprdr . "?cmd=_express-checkout&token=" . $aResponse['TOKEN']);
			echo json_encode("Location: " . $pprdr . "?cmd=_express-checkout&token=" . $aResponse['TOKEN']);
		}
	/*}
	else
	{
		die($pp->get_lngtext('LangTagTextNoPaymentsAvailable'));
	}*/
}
?>