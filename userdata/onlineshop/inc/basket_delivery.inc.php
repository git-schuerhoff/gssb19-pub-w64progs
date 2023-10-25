<?php
/*Begin Shipment*/

//$shipmenttext = $_SESSION['delivery']['ship']['name'] . " " . $this->get_lngtext('LangTagTextShippingAddressArea') . ": " . $_SESSION['delivery']['area']['name'];
if(!isset($order->Customer['areaName'])){
	$defaultArea = $order->getAreaName();
} else {
	$defaultArea = $order->Customer['areaName'];
}
$shipmenttext = $order->Delivery['delivName'] . " " . $this->get_lngtext('LangTagTextShippingAddressArea') . ": " . $defaultArea;
//$shipcost = $this->get_shipcost($_SESSION['delivery']['ship']['id'],$_SESSION['delivery']['area']['id'],$baskettotal,$basketweight);
$shipcost = $this->get_shipcost($order->Delivery['delivID'],$order->Delivery['delivAreaID'],$baskettotal,$basketweight);
$cur_item = $bt_item;
$cur_item = str_replace('{GSSE_INCL_TOTNAME}', $shipmenttext, $cur_item);
$cur_item = str_replace('{GSSE_INCL_TOTVALUE}', $this->get_currency($shipcost,0,'.'), $cur_item);
$all_items .= $cur_item;
/*End Shipment*/

/*Begin Payment*/
$chdis = 0;
$discorcharge = '';
$paybaskettotal = $baskettotal + $shipcost;
if($order->Payment['paymInfo']['paymUseCashDiscount'] == 'Y')
{
	//subtract discount
	$absdiscount = $order->Payment['paymInfo']['paymCashDiscount'];
	$procdiscount = $order->Payment['paymInfo']['paymCashDiscountPercent'];
	if($absdiscount == 0 && $procdiscount > 0)
	{
		//procentual discount without limits
		$discorcharge = $this->get_lngtext('LangTagCashDiscount');
		//$chdis = round((($paybaskettotal / 100) * $procdiscount),2) * -1;//Auf Brutto-Rechnungssumme (Skonto)
		$chdis = round((($baskettotal / 100) * $procdiscount),2) * -1;//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
		
		//$chdis = ($paybaskettotal - ($paybaskettotal / ((100+$procdiscount)/100))) * -1;
		$discorcharge = $discorcharge . " " . $this->get_number_format($procdiscount,".") . "%";
	}
	
	if($absdiscount > 0 && $procdiscount == 0)
	{
		//absolute discount only
		$discorcharge = $this->get_lngtext('LangTagCashDiscount');
		$chdis = $absdiscount * -1;
	}
	
	if($absdiscount > 0 && $procdiscount > 0)
	{
		//procentual discount with absolute discount limit
		$absdis = $absdiscount * -1;
		//$prodis = round((($paybaskettotal / 100) * $procdiscount),2) * -1;//Auf Brutto-Rechnungssumme (Skonto)
		$prodis = round((($baskettotal / 100) * $procdiscount),2) * -1;//Auf Bruttowarenwert (Rabatt anstatt Skonto!)
		//$prodis = ($paybaskettotal - ($paybaskettotal / ((100+$procdiscount)/100))) * -1;
		if($prodis < $absdis)
		{
			$discorcharge = $this->get_lngtext('LangTagCashDiscount');
			$chdis = $absdis;
		}
		else
		{
			$discorcharge = $this->get_lngtext('LangTagCashDiscount');
			$chdis = $prodis;
		}
		$discorcharge = $discorcharge . " " . $this->get_number_format($procdiscount,".") . "%, max. " . $this->get_currency($absdis,0,'.');
	}
}
else
{
	//add charge
	$discorcharge = $this->get_lngtext('LangTagFieldPaymentCharge');
	$abscharge = $order->Payment['paymInfo']['paymCharge'];
	$procharge = $order->Payment['paymInfo']['paymChargePercent'];
	if($abscharge == 0 && $procharge > 0)
	{
		//procentual charge without minimum
		$chdis = round(($baskettotal / 100) * $procharge,2);
		//$chdis = ($baskettotal * ((100+$procharge)/100)) - $baskettotal;
		$discorcharge = $discorcharge . " " . $this->get_number_format($procharge,".") . "%";
	}
	
	if($abscharge > 0 && $procharge == 0)
	{
		//absolute charge only
		$chdis = $abscharge;
	}
	
	if($abscharge > 0 && $procharge > 0)
	{
		//procentual charge with absolute charge minimum
		//$proch = ($baskettotal * ((100+$procharge)/100)) - $baskettotal;
		$proch = round(($baskettotal / 100) * $procharge,2);
		$discorcharge = $discorcharge . " " . $this->get_number_format($procharge,".") . "%, min. " . $this->get_currency($abscharge,0,'.');
		if($proch < $abscharge)
		{
			$chdis = $abscharge;
		}
		else
		{
			$chdis = $proch;
		}
	}
}

//TS 07.03.2016: An dieser Stelle nur dann anzeigen, wenn:
//- es sich um eine Gebühr mit MwSt handelt (chdis > 0)
//- es sich nicht um eine PayPal-Gebühr handelt (keine MwSt)
if($chdis > 0 && $order->Payment['paymInternalName']!= "PaymentPayPal") {
	$discorcharge = $order->Payment['paymName']. " " . $discorcharge;
	$cur_item = $bt_item;
	$cur_item = str_replace('{GSSE_INCL_TOTNAME}', $discorcharge, $cur_item);
	$cur_item = str_replace('{GSSE_INCL_TOTVALUE}', $this->get_currency($chdis,0,'.'), $cur_item);
	$all_items .= $cur_item;
}
/*End Payment*/
?>