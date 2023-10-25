<?php
/*echo "<pre>";
print_r($_SESSION['basket']);
die("</pre>");*/
$customer = $order->getCustomer();
$basket = $order->getBasket();
$delivery = $order->getDelivery();
$payment = $order->getPayment();
$params = "USER=" . $ppusr .
			 "&PWD=" . $pppwd .
			 "&SIGNATURE=" . $ppsig .
			 "&VERSION=109.0" .
			 "&METHOD=SetExpressCheckout" .
			 "&PAYMENTACTION=Authorization" .
			 //"&RETURNURL=" . urlencode($pp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&pp_status=ok") .//Shoppfad aus Klasse
			 "&RETURNURL=" . urlencode($order->se->shopurl . "index.php?page=buy&pp_status=ok") .
			 "&LOCALECODE=DE" .
			 "&CURRENCYCODE=EUR" .
			 //"&HDRIMG=" . $pp->get_setting('edAbsoluteShopPath_Text') . "images/" . $pp->get_setting('edLogo2_Text') .//Shoppfad aus Klasse
			 "&HDRIMG=" . $order->se->shopurl . "images/" . $order->se->get_setting('edLogo2_Text') .
			 //"&CANCELURL=" . urlencode($pp->get_setting('edAbsoluteShopPath_Text') . "index.php?page=buy&pp_status=cancel") .//Shoppfad aus Klasse
			 "&CANCELURL=" . urlencode($order->se->shopurl . "index.php?page=buy&pp_status=cancel") .
			 "&ADDROVERRIDE=1" .
			 "&NOSHIPPING=0" .
			 "&CUSTOM=Dieses Feld ist unsichtbar" .
			 "&SHIPTONAME=" . urlencode($customer['mrormrsText'] . " " . $customer['firstname'] . " " . $customer['lastname']) .
			 "&SHIPTOSTREET=" . urlencode($customer['street']) .
			 "&SHIPTOCITY=" . urlencode($customer['city']) .
			 "&SHIPTOCOUNTRYCODE=" . urlencode($customer['stateISO']) .
			 "&SHIPTOZIP=" . $customer['zip'] .
			 "&PHONENUM=" . urlencode($customer['cusPhone']);
$maxbasket = count($basket);
$usenetto = ($order->se->get_setting('cbNetPrice_Checked') == 'True') ? 1 : 0;
$total = 0;
$totalbrutto = 0;
$b = 0;
$s = 0;
for($r = 0; $r < $maxbasket; $r++)
{
	//Trialartikel nicht übermitteln
	/*if($_SESSION['basket'][$r]['art_isttrialitem'] == 1) {
		continue;
	}*/
	if($usenetto == 1)
	{
		$netto = round($basket[$r]['art_price'],2);
		//Rabatte berechnen
		if($basket[$r]['art_discount'] > 0) {
			$netto = $order->se->calcItemDiscount($netto,$basket[$r]['art_discount']);
		}
		$brutto = round($netto * (1 + ($basket[$r]['art_vatrate'] / 100)),2);
		$tax = $brutto - $netto;
	}
	else
	{
		//echo 'Brutto: '.$_SESSION['basket'][$r]['art_price'].'<br>';
		$netto = round($basket[$r]['art_price'] / (1 + ($basket[$r]['art_vatrate'] / 100)),2);
		//echo 'Netto: '.$netto.'<br>';
		//Rabatte berechnen
		if($basket[$r]['art_discount'] > 0) {
			$netto = $order->se->calcItemDiscount($netto,$basket[$r]['art_discount']);
			//echo 'Netto rabattiert: '.$netto.'<br>';
			$brutto = round($netto * (1 + ($basket[$r]['art_vatrate'] / 100)),2);
			//echo 'Brutto rabattiert: '.$brutto.'<br>';
		} else {
			$brutto = round($basket[$r]['art_price'],2);
		}
		$tax = $brutto - $netto;
		//die('MwSt.: '.$tax.'<br>');
	}
	$total += ($netto * $basket[$r]['art_count']);
	$totalbrutto += ($brutto * $basket[$r]['art_count']);
	$taxtotal += round(($tax * $basket[$r]['art_count']),2);
	$item_name = $basket[$r]['art_title'];
	$item_name .= ($basket[$r]['art_attr0'] != "") ? ", " . $basket[$r]['art_attr0'] : "";
	$item_name .= ($basket[$r]['art_attr1'] != "") ? ", " . $basket[$r]['art_attr1'] : "";
	$item_name .= ($basket[$r]['art_attr2'] != "") ? ", " . $basket[$r]['art_attr2'] : "";
	$item_name .= ($basket[$r]['art_textfeld'] != "") ? ", " . $basket[$r]['art_textfeld'] : "";
	$params .= "&L_NAME" . $s . "=" . urlencode($item_name) .
	"&L_NUMBER" . $s . "=" . urlencode($basket[$r]['art_num']) .
				  "&L_AMT" . $s . "=" . $netto .
				  "&L_QTY" . $s . "=" . $basket[$r]['art_count'] .
				  "&L_TAXAMT" . $s . "=" . $tax;
	$s++;
	
	/*echo "usenetto:".$usenetto."<br>";
	echo "tax:".$tax."<br>";
	echo "netto:".$netto."<br>";
	echo "brutto:".$brutto."<br>";
	echo "total:".$total."<br>";
	echo "totalbrutto:".$totalbrutto."<br>";
	echo "taxtotal:".$taxtotal."<br>";*/
	
	if($basket[$r]['art_prices']['isrental'] == 'Y') {
		if($basket[$r]['art_isinitprice'] == 0 && $basket[$r]['art_isttrialitem'] == 'N') {
			//Errechne Folgerechnungen
			$subsquentInvCount = 1;
			$payedInvoices = 1;//Erstmal statisch
			
			$runtime = $basket[$r]['art_prices']['rentalruntime'];
			$billingfreq = $basket[$r]['art_billingfreq'];
			if($billingfreq != 0 && $billingfreq != '') {
				$subsquentInvCount = ($runtime / $billingfreq) - $payedInvoices;
			}
			
			$recurringDescr = $subsquentInvCount . ' ' . $basket[$r]['art_textfeld'];
			$params .= "&L_BILLINGTYPE" . $b . "=MerchantInitiatedBilling" .
						  "&L_BILLINGAGREEMENTDESCRIPTION" . $b . "=" . urlencode($recurringDescr);
			$b++;
		}
	}
}

//echo 'PP<pre>';
//print_r($_SESSION['delivery']);
//die('</pre>');

//$shippingamt = $_SESSION['shipcost'];
$shippingamt = $delivery['delivTotal'];//$_SESSION['delivery']['ship']['charge'];
$invoicetotal = $order->getBasketInvoiceTotal();//$_SESSION['invoicetotal'];
$handlingamt = $payment['paymInfo']['handlingamt'];
$discount = $payment['paymInfo']['discount'];
/*if($_SESSION['delivery']['paym']['usecashdiscount'] != 'Y') {
	if($_SESSION['delivery']['paym']['charge'] != 0) {
		$handlingamt = $_SESSION['delivery']['paym']['charge'];
	}
	if($_SESSION['delivery']['paym']['chargepercent'] != 0) {
		$handlingamt += (($totalbrutto) / 100) * $_SESSION['delivery']['paym']['chargepercent'];//Auf Bruttowarenwert
	}
} else {
	if($_SESSION['delivery']['paym']['cashdiscount'] != 0) {
		$discount = $_SESSION['delivery']['paym']['cashdiscount'] * -1;
	}
	if($_SESSION['delivery']['paym']['cashdiscountpercent'] != 0) {
		$discount -= (($totalbrutto) / 100) * $_SESSION['delivery']['paym']['cashdiscountpercent'];//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
	}
}*/

$handlingamt = round($handlingamt,2);
$discount = round($discount,2);

//die($totalbrutto.'<br>'.$shippingamt.'<br>'.$discount);

//die($totalbrutto.'Handling: '.$handlingamt);


//$discount = ($invoicetotal - ($total + $taxtotal + $shippingamt + $handlingamt));
//$total += $discount;

//TS 14.10.2015: Rabatte als Artikel übergeben
if($discount != 0) {
	$params .= "&L_NAME" . $r . "=" . urlencode('Rabatt') .
				  "&L_NUMBER" . $r . "=" . urlencode('Rabatt') .
				  "&L_AMT" . $r . "=" . $discount .
				  "&L_QTY" . $r . "=1" .
				  "&L_TAXAMT" . $r . "=0.00";
}


//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";

/*echo "<br><br><br>Rechnung total: " . $invoicetotal . "<br>Total: " . $total . "<br>";
echo "- Gebühr: " . $handlingamt . "<br>";
echo "- Versand: " . $shippingamt . "<br>";
echo "____________________________<br>";
echo "= Ohne VS und PP: " . ($invoicetotal - ($handlingamt + $shippingamt)) . "<br>";
echo "- WK Gesamt: " .$totalbrutto;
echo "<br>= Rabatt: " . $discount;*/

$params .= "&ITEMAMT=" . ($total + $discount) .
			 "&TAXAMT=" . $taxtotal .
			 "&SHIPPINGAMT=" . $shippingamt .
			 "&HANDLINGAMT=" . $handlingamt .
			 "&AMT=" . ($total + $taxtotal + $shippingamt + $handlingamt + $discount);
			 
//echo str_replace('&','<br>',$params);
//die();
			
/*$params .= "&ITEMAMT=" . $_SESSION['invoicetotal'] .
			 "&TAXAMT=" . $taxtotal .
			 "&SHIPPINGAMT=" . $shippingamt .
			 "&HANDLINGAMT=" . $handlingamt .
			 "&AMT=" . ($_SESSION['invoicetotal'] + $taxtotal + $shippingamt + $handlingamt);*/
?>