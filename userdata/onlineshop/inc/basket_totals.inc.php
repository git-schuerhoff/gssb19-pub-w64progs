<?php
$baskettotalhtml = $this->gs_file_get_contents($this->absurl . 'template/basket_totals.html');
$baskettotalhtml = $this->parse_texts($this->get_tags_ret($baskettotalhtml),$baskettotalhtml);
$bt_item = $this->gs_file_get_contents($this->absurl . 'template/baskettotal_item.html');

$baskettotalhtml = str_replace('{GSSE_INCL_SUBTOTAL}',$this->get_currency($sub_total,0,'.'),$baskettotalhtml);

$chdis = 0;
$shipcost = 0;
$rabat = 0;
$custrabat = 0;

$baskettotal = $sub_total;

$on = 1;

if($_GET['page'] == 'buy')
{
	$checkouttxt = $this->get_lngtext('LangTagButtonSubmit');
}
else
{
	$checkouttxt = $this->get_setting('edOrderButton_Text');
}

$minvalue = floatval(str_replace(',','.',$this->get_setting('edMinOrderValue_Text')));
if($minvalue > $baskettotal)
{
	$notreached = $this->get_lngtext('LangTagTextMinOrderNewValue1') . ' ' . $this->get_currency($baskettotal,0,'.') . ' ' .
					  $this->get_lngtext('LangTagTextMinOrderNewValue2') . ' ' . $this->get_currency($minvalue,0,'.') . ' ' .
					  $this->get_lngtext('LangTagTextMinOrderNewValue3');
	$baskettotalhtml = str_replace('{GSSE_INCL_SHOWCOBUTTON}','no-display',$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_MSG_ERRORNEWCLASS}','notice-msg',$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_MSG_ERRORNEW}',$notreached,$baskettotalhtml);
}
else
{
	$baskettotalhtml = str_replace('{GSSE_INCL_SHOWCOBUTTON}','',$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_INCL_CHECKOUTTXT}',$checkouttxt,$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_MSG_ERRORNEWCLASS}','no-display',$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_MSG_ERRORNEW}','',$baskettotalhtml);
	$baskettotalhtml = str_replace('{GSSE_INCL_PATHTOBUY}',$this->absurl . 'index.php?page=buy',$baskettotalhtml);
}

$all_items = '';
//$on = 1;
if($_GET['page'] == 'buy' || $on == 1)
{
	
	/*Begin delivery*/
	//if(isset($_SESSION['delivery']))
	//{
		include('./inc/basket_delivery.inc.php');
	//}
	
	//TS 07.03.2016: Gebühren für Zahlungsarten mit 19% MwSt. in Deutschland!!!!
	//Rabatte für Zahlungsarten sind SKONTI und werden vom GESAMTBRUTTO abgezogen
	/*Begin add vat for charge or discount and shipcost*/
	$chdisvat = 0;
	$shipvat = 0;
	$vat_factor = (100 + $aVats[0]['vatrate']) / 100;
	//Nur Mehrwertsteuer berechnen, wenn Zahlungsart NICHT PayPal ist, denn PayPal hat Bankstatus und
	//die Gebühren sind daher Geldtransferkosten und OHNE Umsatzsteuer
	if($order->Payment['paymInternalName']!= "PaymentPayPal") {
		if($chdis != 0) {
			$abschdis = abs($chdis);
			if($chdis > 0) {
				if($inclvat) {
					$chdisvat = round($abschdis - ($abschdis / $vat_factor),2);
				} else {
					$chdisvat = round(($abschdis * $vat_factor) - $abschdis,2);
				}
				$aVats[0]['vattotal'] = $aVats[0]['vattotal'] + $chdisvat;
			}
		}
	}
	
	if($shipcost > 0) {
		if($inclvat) {
			$shipvat = round($shipcost - ($shipcost / $vat_factor),2);
		} else {
			$shipvat = round(($shipcost * $vat_factor) - $shipcost,2);
		}
		$aVats[0]['vattotal'] = $aVats[0]['vattotal'] + $shipvat;
	}
	//$aVats[0]['vattotal'] = $aVats[0]['vattotal']-($aVats[0]['vattotal']/100*$rabatpercent) + $chdisvat + $shipvat;
		
	/*End add vat for charge or discount and shipcost*/
	
	//An dieser Stelle zusammenrechnen, wenn:
	//- es sich um eine Gebühr handelt
	//- und diese Gebühr USt.Pflichtig ist (nicht Bank oder PayPal)
	if($chdis > 0 && $order->Payment['paymInternalName']!= "PaymentPayPal") {
		$totalwdelivery = $baskettotal + $chdis + $shipcost;
	} else {
		$totalwdelivery = $baskettotal + $shipcost;
	}
	
	if($totalwdelivery != $baskettotal)
	{
		$cur_item = $bt_item;
		/*$cur_item = str_replace('',,$cur_item);*/
		$cur_item = str_replace('{GSSE_INCL_TOTNAME}',$this->get_lngtext('LangTagSubTotal'),$cur_item);
		$cur_item = str_replace('{GSSE_INCL_TOTVALUE}',$this->get_currency($totalwdelivery,0,'.'),$cur_item);
		$all_items .= $cur_item;
	}
	
	/*VATs*/
	if($showitemvat) {
		$vatsmax5 = count($aVats);
		for($v = 0; $v < $vatsmax5; $v++)
		{
			if($aVats[$v]['vattotal'] != 0)
			{
				$cur_item = $bt_item;
				/*$cur_item = str_replace('',,$cur_item);*/
				$cur_item = str_replace('{GSSE_INCL_TOTNAME}',$itemvattitle . ' ' . $this->get_number_format($aVats[$v]['vatrate'],'.') . ' %',$cur_item);
				$cur_item = str_replace('{GSSE_INCL_TOTVALUE}',$this->get_currency($aVats[$v]['vattotal'],0,'.'),$cur_item);
				$all_items .= $cur_item;
				if(!$inclvat) {
					$baskettotal += $aVats[$v]['vattotal'];
				}
			}
		}
	}
	
	//Zahlungsgebühr hier anzeigen, wenn:
	//- es sich um eine Grbühr handelt
	//- es sich um eine USt-freie Gebühr (Banken/PayPal) handelt
	if($chdis > 0 && $order->Payment['paymInternalName']== "PaymentPayPal") {
		$discorcharge = $order->Payment['paymName']. " " . $discorcharge;
		$cur_item = $bt_item;
		$cur_item = str_replace('{GSSE_INCL_TOTNAME}', $discorcharge, $cur_item);
		$cur_item = str_replace('{GSSE_INCL_TOTVALUE}', $this->get_currency($chdis,0,'.'), $cur_item);
		$all_items .= $cur_item;
	}
}

if($chdis >= 0) {
	//Zahlart: Gebühr fällig, alles gut
	$grandtotal = $baskettotal + $chdis + $shipcost;
	$invoicetotal = $grandtotal;
} else {
	//Skonto wird von der Gesamtsumme abgezogen
	$grandtotal = $baskettotal + $shipcost;
	
	//Skonto anzeigen
	$discorcharge = $order->Payment['paymName']. " " . $discorcharge;
	$cur_item = $bt_item;
	$cur_item = str_replace('{GSSE_INCL_TOTNAME}', $discorcharge, $cur_item);
	$cur_item = str_replace('{GSSE_INCL_TOTVALUE}', $this->get_currency($chdis,0,'.'), $cur_item);
	$all_items .= $cur_item;
	$invoicetotal = $grandtotal + $chdis;
}
$invoicetotal = $order->BasketInvoiceTotal;
$baskettotalhtml = str_replace('{GSSE_INCL_BASKETTOTALITEMS}',$all_items,$baskettotalhtml);
$baskettotalhtml = str_replace('{GSSE_INCL_GRANDTOTAL}',$this->get_currency($invoicetotal,0,'.'),$baskettotalhtml);
?>