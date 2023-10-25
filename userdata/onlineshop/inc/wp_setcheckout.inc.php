<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$wp = new gs_shopengine();

function spec_sign($str)
{
	$str = str_replace('Ä','Ae',$str);
	$str = str_replace('Ö','Oe',$str);
	$str = str_replace('Ü','Ue',$str);
	$str = str_replace('ä','ae',$str);
	$str = str_replace('ö','oe',$str);
	$str = str_replace('Ü','ue',$str);
	$str = str_replace('ß','ss',$str);
	return $str;
}

if(!isset($_SESSION['basket']))
{
	die($wp->get_lngtext('LangTagTextBasketEmpty'));
}

if(count($_SESSION['basket']) == 0)
{
	die($wp->get_lngtext('LangTagTextBasketEmpty'));
}
else
{
	$aLocal = $_GET;
	if($aLocal['payment_type'] == 'PaymentWorldPay')
	{
		//Schritt 1: Set Checkout
		if($wp->get_setting('cbWorldPayTestMode_Checked') != 'False')
		{
			$wptest = '100';
		}
		else
		{
			$wptest = '0';
		}
		
		$total = 0;
		$tax = 0;
		$taxtotal = 0;
		/*echo "<pre>";
		print_r($_SESSION);
		print_r($_GET);
		die("</pre>");*/
		$allItems ='';
		foreach($_SESSION['basket'] as $val)
		{
			$allItems .= trim($val['art_count'].' x '.$val['art_num'].' '. $val['art_title'] .' '.$val['art_attr0'].' '.$val['art_attr1'].' '.$val['art_attr2']).', ';
		}
		$allItems = htmlentities(substr($allItems, 0, strlen($allItems)-2),ENT_QUOTES);
		
		// erzeuge einen neuen cURL-Handle
		$ch = curl_init();
		// setze die URL und andere Optionen
		$wpurl = 'https://select.worldpay.com/wcc/purchase';
		$params = 'testMode='.$wptest.'&instId='.$wp->get_setting('edWorldPayID_Text');
		$params .= '&currency='.$wp->get_setting('edCurrencySymbol_Text');
		$params .= '&name='.spec_sign($aLocal['forename']). ' '.spec_sign($aLocal['surname']);
		$params .= '&address='.spec_sign($aLocal['address']) . ' ' . spec_sign($aLocal['city']);
		$params .= '&postcode='.$aLocal['zip'];
		$params .= '&tel='.$aLocal['phone'];
		$params .= '&fax='.$aLocal['fax'];
		$params .= '&email='.$aLocal['email'];
		$params .= '&cartId='.$_SESSION['pid'];
		$params .= '&amount='.$_SESSION['invoicetotal'];
		$params .= '&desc='.$allItems;
		$params .= '&lang='.strtolower(spec_sign($aLocal['state']));
		//$params .= '&MC_callback='.urlencode($wp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&pp_status=ok"); 
		$params .= '&MC_callback='.urlencode($wp->shopurl . "index.php?page=buy&pp_status=ok"); 
		
		curl_setopt($ch, CURLOPT_URL, $wpurl);
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
			die('cURL-Fehler: ' . curl_error($ch) . "<br />");
		}
		// schließe den cURL-Handle und gebe die Systemresourcen frei
		curl_close($ch);
		//$aResponse = $wp->get_url_array($erg);
		
		echo $erg;
		
	}
	else
	{
		die($wp->get_lngtext('LangTagTextNoPaymentsAvailable'));
	}
}
?>