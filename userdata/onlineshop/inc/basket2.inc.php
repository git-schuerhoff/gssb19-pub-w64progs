<?php
include_once('class.order.php');
$order = New Order;
//session_start();
$order = unserialize($_SESSION['order']);
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$baskethtml = '';
$baskettotal = 0;
$basketweight = 0;
$rabat = 0;
$custrabat = 0;
$mkhidden = 1;
$cur_vat = '';
$cur_vathd = '';
/*if(!isset($mkhidden))
{
	$mkhidden = 0;
}*/

if($mkhidden == 1)
{
	$buyitemhidden = $this->gs_file_get_contents('template/buy3itemhiddenfields.html');
	$hiddenitems = '';
}
$aVats = $this->get_vats();
//$order = unserialize($_SESSION['order']);
$basketObject = $order->getBasket();
if(isset($basketObject))
{
	//print_r($basketObject);
	$basket_count = count($basketObject);
	if($basket_count > 0)
	{
		if(isset($_SESSION['desktop']))
		{
			if($_SESSION['desktop']['is_phone'] == 1)
			{
				$basketitem = $this->gs_file_get_contents($this->absurl . 'template/basketitem2_mobile.html');
				$basketitem_small = $this->gs_file_get_contents($this->absurl . 'template/basketitem2_mobile_small.html');
				$basketsum = $this->gs_file_get_contents('template/basketsummaryitem_mobile.html');
			}
			else
			{
				$basketitem = $this->gs_file_get_contents($this->absurl . 'template/basketitem2.html');
				$basketitem_small = $this->gs_file_get_contents($this->absurl . 'template/basketitem2_small.html');
				$basketsum = $this->gs_file_get_contents('template/basketsummaryitem.html');
			}
		}
		else
		{
			$basketitem = $this->gs_file_get_contents($this->absurl . 'template/basketitem2.html');
			$basketitem_small = $this->gs_file_get_contents($this->absurl . 'template/basketitem2_small.html');
			$basketsum = $this->gs_file_get_contents('template/basketsummaryitem.html');
		}
		/*$basketitem = $this->gs_file_get_contents('template/basketitem2.html');*/
		/*$basketsum = $this->gs_file_get_contents('template/basketsummaryitem.html');*/
		for($b = 0; $b < $basket_count; $b++)
		{
			$basketweight += $basketObject[$b]['art_weight'] * $basketObject[$b]['art_count'];
			
			$cur_item = $basketitem;
			//TS 11.12.2015: Für Ersteinrichtungsgebühr oder Trialzeiten kleinere Zeilen verwenden
			if($basketObject[$b]['art_isinitprice'] == 1 || $basketObject[$b]['art_isttrialitem'] == 'Y') {
				$cur_item = $basketitem_small;
			} else {
				$cur_item = $basketitem;
			}
			
			//TS 08.03.2016: Artikelpreis ermitteln und dabei Rabatte berücksichtigen
			$item_price = $basketObject[$b]['art_price'];
			$item_defprice = $item_price;
			$vat_rate = $basketObject[$b]['art_vatrate'];
			$vat_factor = (100+$vat_rate)/100;
			$discount = $basketObject[$b]['art_discount'];
			$discounttext = '';
			if($discount > 0) {
				/*if($vatincl == 1) {
					//Shop hat Bruttopreise, d. h. erst UST herausrechnen
					$netprice = round($item_price / $vat_factor,2);
					//Dann Rabatt rausrechnen
					//$disprice = round($netprice / ((100+$discount)/100),2);
					$disprice = $this->calcItemDiscount($netprice,$discount);
					//Und MwSt wieder drauf
					$item_price = round($disprice * $vat_factor,2);
				} else {
					//Shop hat Nettopreise
					//Hier brauch nur der Rabatt rausgezogen werden
					//$item_price = round($item_price / ((100+$discount)/100),2);
					$item_price = $this->calcItemDiscount($item_price,$discount);
				}*/
				$discounttext = "(- " . $this->get_number_format($discount,'') . "% " . $this->get_lngtext('LangTagFNFieldLocDiscount') . ")";
			}
			$cur_item = str_replace('{GSSE_INCL_DISCOUNT}',$discounttext,$cur_item);
			
			if($vat_rate > 0)
			{
				$arttotalprice = $item_price * $basketObject[$b]['art_count'];
				if($vatincl == 1)
				{
					$vat = round($arttotalprice - ($arttotalprice / $vat_factor),2);
				}
				else
				{
					$vat = round(($arttotalprice * $vat_factor) - $arttotalprice,2);
				}
				$cur_vat = '';
				$cur_vathd = '';
				if($showvat == 1)
				{
					$cur_vat = $this->get_currency($vat,0,'.') . " (" . $this->get_number_format($basketObject[$b]['art_vatrate'],".") . " %)";
					if($mkhidden == 1)
					{
						$cur_vathd = $vat;
					}
				}
				$lfound = false;
				$vatsmax = count($aVats);
				for($v = 0; $v < $vatsmax; $v++)
				{
					if($aVats[$v]['vatrate'] == $basketObject[$b]['art_vatrate'])
					{
						$lFound = true;
						break;
					}
				}
				if($lFound === true)
				{
					$aVats[$v]['vattotal'] = $aVats[$v]['vattotal'] + $vat;
				}
				else
				{
					/*array_push($aVats,array("vatrate" => $basketObject[$b]['art_vatrate'],"vattotal" => $vat));*/
					$aVats[] = array("vatrate" => $basketObject[$b]['art_vatrate'],"vattotal" => $vat);
				}
			}
			else
			{
				$lfound = false;
				$vat = 0;
				$cur_vat = $this->get_currency($vat,0,'.') . " (" . $this->get_number_format(0,".") . " %)";
				$vatsmax2 = count($aVats);
				for($v = 0; $v < $vatsmax2; $v++)
				{
					if($aVats[$v]['vatrate'] == 0)
					{
						$lFound = true;
						break;
					}
				}
				if($lFound === true)
				{
					$aVats[$v]['vattotal'] = $aVats[$v]['vattotal'] + 0;
				}
				else
				{
					/*array_push($aVats,array("vatrate" => 0,"vattotal" => $vat));*/
					$aVats[] = array("vatrate" => 0,"vattotal" => $vat);
				}
			}
			$pos = $b + 1;
			if(($pos % 2) == 0)
			{
				$class = 'row2';
			}
			else
			{
				$class = 'row1';
			}
			
			$cur_itempic = '&nbsp;';
			
			$itemtitle = $basketObject[$b]['art_num'] . ' ' . $basketObject[$b]['art_title'];
			$itemtitle .= ($basketObject[$b]['art_vartitle'] != '') ? ', ' . $basketObject[$b]['art_vartitle'] : '';
			$itemtitle .= ($basketObject[$b]['art_attr0'] != '') ? ', ' . $basketObject[$b]['art_attr0'] : '';
			$itemtitle .= ($basketObject[$b]['art_attr1'] != '') ? ', ' . $basketObject[$b]['art_attr1'] : '';
			$itemtitle .= ($basketObject[$b]['art_attr2'] != '') ? ', ' . $basketObject[$b]['art_attr2'] : '';
			$itemtitle .= ($basketObject[$b]['art_textfeld'] != '') ? ', ' . $basketObject[$b]['art_textfeld'] : '';
			
			if($mkhidden == 1)
			{
				$itemtitlehd = $basketObject[$b]['art_title'];
				$itemtitlehd .= ($basketObject[$b]['art_vartitle'] != '') ? ', ' . $basketObject[$b]['art_vartitle'] : '';
				$itemtitlehd .= ($basketObject[$b]['art_attr0'] != '') ? ', ' . $basketObject[$b]['art_attr0'] : '';
				$itemtitlehd .= ($basketObject[$b]['art_attr1'] != '') ? ', ' . $basketObject[$b]['art_attr1'] : '';
				$itemtitlehd .= ($basketObject[$b]['art_attr2'] != '') ? ', ' . $basketObject[$b]['art_attr2'] : '';
				$itemtitlehd .= ($basketObject[$b]['art_textfeld'] != '') ? ', ' . $basketObject[$b]['art_textfeld'] : '';
			}
			
			$cAge = '';
			if($basketObject[$b]['art_checkage'] == 'Y' && $basketObject[$b]['art_mustage'] > 0)
			{
				$cAge = '<br />' . $this->get_lngtext('LangTagMinimumAge'). ': ' . $basketObject[$b]['art_mustage'] . ' ' . $this->get_lngtext('LangTagYears');
			}
			
			$itemtitle .= $cAge;
			
			
			if($basketObject[$b]['art_dpn'] == '')
			{
				if($basketObject[$b]['art_img'] != '')
				{
					$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
					/* Bild online oder lokal?*/
					if(strpos($basketObject[$b]['art_img'],"http") === false && strpos($basketObject[$b]['art_img'],"://") === false) {
						/*if(file_exists('images/medium/' . $basketObject[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
						}*/
						//TS 30.12.2016: Datei auf Existenz prüfen und ggf. andere Dateien laden
						if(file_exists('images/medium/' . $basketObject[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
						} elseif(file_exists('images/small/' . $basketObject[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/small/' . $basketObject[$b]['art_img'],$cur_itempic);
						} elseif(file_exists('images/big/' . $basketObject[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/big/' . $basketObject[$b]['art_img'],$cur_itempic);
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','template/images/no_pic_sma.png',$cur_itempic);
						}
					} else {
						$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basketObject[$b]['art_img'],$cur_itempic);
					}
					//$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
				}
				else
				{
					$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
				}
				$cur_itemtitle = $itemtitle;
				
			}
			else
			{
				if($basketObject[$b]['art_hasdetail'] != 'N')
				{
					if($basketObject[$b]['art_img'] != '')
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKURL}',$basketObject[$b]['art_dpn'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
						/* Bild online oder lokal?*/
						if(strpos($basketObject[$b]['art_img'],"http") === false && strpos($basketObject[$b]['art_img'],"://") === false) {
							if(file_exists('images/medium/' . $basketObject[$b]['art_img'])) {
								$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
							} else {
								$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
							}
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basketObject[$b]['art_img'],$cur_itempic);
						}
						//$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
					}
					else
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKURL}',$basketObject[$b]['art_dpn'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
					}
					$cur_itemtitle = $this->gs_file_get_contents('template/link.html');
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKURL}',$basketObject[$b]['art_dpn'],$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKNAME}',$itemtitle,$cur_itemtitle);
				}
				else
				{
					if($basketObject[$b]['art_img'] != '')
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('<a class="{GSSE_INCL_LINKCLASS}" href="{GSSE_INCL_LINKURL}" target="{GSSE_INCL_LINKTARGET}">','',$cur_itempic);
						$cur_itempic = str_replace('</a>','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
						/* Bild online oder lokal?*/
						if(strpos($basketObject[$b]['art_img'],"http") === false && strpos($basketObject[$b]['art_img'],"://") === false) {
							if(file_exists('images/medium/' . $basketObject[$b]['art_img'])) {
								$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
							} else {
								$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
							}
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basketObject[$b]['art_img'],$cur_itempic);
						}
						//$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basketObject[$b]['art_img'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
					}
					else
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('<a class="{GSSE_INCL_LINKCLASS}" href="{GSSE_INCL_LINKURL}" target="{GSSE_INCL_LINKTARGET}">','',$cur_itempic);
						$cur_itempic = str_replace('</a>','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', 'template/images/no_pic_sma.png',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basketObject[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basketObject[$b]['art_title'],$cur_itempic);
					}
					$cur_itemtitle = $this->gs_file_get_contents('template/link.html');
					$cur_itemtitle = str_replace('<a class="{GSSE_INCL_LINKCLASS}" href="{GSSE_INCL_LINKURL}" target="{GSSE_INCL_LINKTARGET}">','',$cur_itemtitle);
					$cur_itemtitle = str_replace('</a>','',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKNAME}',$itemtitle,$cur_itemtitle);
				}
			}
			
			$cur_artcount = $basketObject[$b]['art_count'];
			/*Rental price?*/
			$billingperiod = '';
			if($basketObject[$b]['art_prices']['isrental'] == 'Y') {
				$basketqtychgclass = 'no-display';
				$basketqtydispclass = 'gs-basket-qty';
				if($basketObject[$b]['art_isinitprice'] != 1) {
					$billingperiod = '&nbsp;' . $this->get_billingperiodfromid($basketObject[$b]['art_prices']['billingperiod'],false,true,false);
				}
			}
			$cur_item = str_replace('{GSSE_INCL_BILLINGPERIOD}',$billingperiod,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETROWCLASS}',$class,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMPIC}',$cur_itempic,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMTITLE}',$cur_itemtitle,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMCOUNT}',$cur_artcount,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMPRICE}',$this->get_currency($item_price,0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMVAT}',$cur_vat,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMTOTAL}',$this->get_currency($item_price * $basketObject[$b]['art_count'],0,'.'),$cur_item);
			$baskethtml .= $cur_item;
			$baskettotal += $item_price * $basketObject[$b]['art_count'];
			
			if($mkhidden == 1)
			{
				$cur_hidden = $buyitemhidden;
				$dnld = 'false';
				if($basketObject[$b]['art_isdownload'] == 'Y')
				{
					$dnld = 'true';
				}
				$cur_hidden = str_replace('{GSSE_INCL_DOWNLOADITEM}',$dnld,$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMPOS}',$b + 1,$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMNUMBER}',$basketObject[$b]['art_num'],$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMIMAGE}',$basketObject[$b]['art_img'],$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMTEXT}',base64_encode($this->email_friendly($itemtitlehd)),$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMQUANTITY}',$cur_artcount,$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_number_format($item_price,'.'),$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_VATRATE}',$this->get_number_format($basketObject[$b]['art_vatrate'],'.'),$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_VATAMOUNT}',$this->get_number_format($cur_vathd,'.'),$cur_hidden);
				$cur_hidden = str_replace('{GSSE_INCL_ITEMTOTAL}',$this->get_number_format($item_price * $basketObject[$b]['art_count'],'.'),$cur_hidden);
				$hiddenitems .= $cur_hidden;
			}
			
		}//for
		$_SESSION['art_vatsumme'] = $aVats;
		/*Begin place hidden item fields on buy3*/
		if($mkhidden == 1)
		{
			//{GSSE_INCL_ITEMHIDDENFIELDS} is in buy3hiddenfields.html
			if(isset($basket2_is_loc)) {
				$buy3hidden = str_replace('{GSSE_INCL_ITEMHIDDENFIELDS}', $hiddenitems, $buy3hidden);
			} else {
				$this->content = str_replace('{GSSE_INCL_ITEMHIDDENFIELDS}', $hiddenitems, $this->content);
			}
		}
		/*End place hidden item fields on buy3*/
		
		//$baskettotalwithoutrabat = $baskettotal;
		$baskettotalwithoutrabat = $baskettotal;
		
		
		/*End Kundenrabat*/
		
		/*Begin Payment*/
		$buybasket = str_replace('{GSSE_INCL_BASKETTOTAL}',$this->get_currency($baskettotal,0,'.'),$buybasket);
		$chdis = 0;
		$discorcharge = '';
		$payment = $order->getPayment();
		if(isset($payment))
		{
			if($payment['paymInfo']['paymUseCashDiscount'] == 'Y')
			{
				//subtract discount
				$absdiscount = $payment['paymInfo']['paymCashDiscount'];
				$procdiscount = $payment['paymInfo']['paymCashDiscountPercent'];
				if($absdiscount == 0 && $procdiscount > 0)
				{
					//procentual discount without limits
					$discorcharge = $this->get_lngtext('LangTagTextRabat');
					$chdis = (($baskettotal / 100) * $procdiscount) * -1;
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
					$prodis = (($baskettotal / 100) * $procdiscount) * -1;
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
				/*echo "<pre>";
				print_r($_SESSION['delivery']['paym']);*/
				
				$discorcharge = $this->get_lngtext('LangTagFieldPaymentCharge');
				$payment['paymInfo'] = $order->get_paymInfo();
				$abscharge = floatval($payment['paymInfo']['paymCharge']);
				$procharge = floatval($payment['paymInfo']['paymChargePercent']);
				if($abscharge == 0 && $procharge > 0)
				{
					//procentual charge without minimum
					$chdis = ($baskettotal / 100) * $procharge;
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
					$proch = ($baskettotal / 100) * $procdiscount;
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
				//die($chdis);
			}
			$discorcharge = $payment['paymName'] . " " . $discorcharge;
		}
		$buybasket = str_replace('{GSSE_INCL_PAYMENT}', $discorcharge, $buybasket);
		//die($chdis.' -> ' .$this->get_currency($chdis,0,'.'));
		$buybasket = str_replace('{GSSE_INCL_PAYMENTCOST}', $this->get_currency($chdis,0,'.'), $buybasket);
		/*End Payment*/
		
		/*Begin Shipment*/
		$shipcost = 0;
		$delivery = $order->getDelivery();
		if(isset($delivery))
		{
			$shipmenttext = $delivery['delivName'] . " " . $this->get_lngtext('LangTagTextShippingAddressArea') . ": " . $order->getAreaName();
			$shipcost = $this->get_shipcost($delivery['delivID'],$delivery['delivAreaID'],$baskettotal,$basketweight);
			$buybasket = str_replace('{GSSE_INCL_SHIPMENT}', $shipmenttext, $buybasket);
			$buybasket = str_replace('{GSSE_INCL_SHIPMENTCOST}', $this->get_currency($shipcost,0,'.'), $buybasket);
		}
		/*End Shipment*/
		
		
		/*Begin subtotal*/
		$subtotal = $baskettotal + $chdis + $shipcost - $rabat - $custrabat;
		$subtotalhtml = '';
		if($vatincl == 0 && $showvat == 1)
		{
			$subtotalhtml = $basketsum;
			$subtotalhtml = str_replace('{GSSE_INCL_BASKETELEMENTTITLE}', $this->get_lngtext('LangTagSubTotal'), $subtotalhtml);
			$subtotalhtml = str_replace('{GSSE_INCL_BASKETELEMENTCONT}', $this->get_currency($subtotal,0,'.'), $subtotalhtml);
		}
		$buybasket = str_replace('{GSSE_INCL_BASKETSUBTOTAL}', $subtotalhtml, $buybasket);
		/*End subtotal*/
		
		/*Begin vat*/
		$vathtml = '';
		if($showvat == 1)
		{
			if($vatincl == 1)
			{
				$vatname = $this->get_lngtext('LangTagTextEncludedVAT') . " " . $this->get_lngtext('LangTagTextShortVAT') . " ";
			}
			else
			{
				$vatname = $this->get_lngtext('LangTagTextShortVAT') . " ";
			}
			$sumvattotal = 0;
			$vatsmax3 = count($aVats);
			for($v = 0; $v < $vatsmax3; $v++)
			{
				if($aVats[$v]['vattotal'] > 0)
				{
					$cur_vatitem = $basketsum;
					$cur_vatttitle = $vatname . $this->get_number_format($aVats[$v]['vatrate'],".") . " %";
					$cur_vattotal = $this->get_currency($aVats[$v]['vattotal'],0,'.');
					$cur_vatitem = str_replace('{GSSE_INCL_BASKETELEMENTTITLE}', $cur_vatttitle, $cur_vatitem);
					$cur_vatitem = str_replace('{GSSE_INCL_BASKETELEMENTCONT}', $cur_vattotal, $cur_vatitem);
					$vathtml .= $cur_vatitem;
				}
				$sumvattotal += $aVats[$v]['vattotal'];
			}
			//$sumvattotal = $sumvattotal - ($sumvattotal/100*$rabatpercent);
		}
		$buybasket = str_replace('{GSSE_INCL_BASKETVAT}', $vathtml, $buybasket);
		/*End vat*/
		
		/*Begin sumtotal*/
		if($vatincl == 0 && $showvat == 1)
		{
			$invoicetotal = $subtotal + $sumvattotal;
		}
		else
		{
			$invoicetotal = $subtotal;
		}
		$buybasket = str_replace('{GSSE_INCL_SUMTOTAL}', $this->get_currency($invoicetotal,0,'.'), $buybasket);
		/*End sumtotal*/
		
		/*Zusätzliche Infos*/
		$_SESSION['invoicetotal'] = round($invoicetotal,2);
		$_SESSION['shipcost'] = round($shipcost,2);
		
		/**/
		
		/*Begin on buy3 hidden summary fields*/
		if($mkhidden == 1)
		{
			$summhidden = $this->gs_file_get_contents('template/buy3summaryhidden.html');
			$summhidden = str_replace('{GSSE_INCL_QUANTITYOFPOS}', count($basketObject), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_LOCTOTAL}', $this->get_number_format($baskettotal,'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_PAYMENT}', $payment['paymName'], $summhidden);
			$summhidden = str_replace('{GSSE_INCL_PAYMENTCHARGE}', $this->get_number_format(round($chdis,2),'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_SHIPMENT}', $delivery['delivName'], $summhidden);
			$summhidden = str_replace('{GSSE_INCL_SHIPMENTAREA}', $order->getAreaName(), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_SHIPMENTCOST}', $this->get_number_format($shipcost,'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_SUMDISCOUNT2}', $this->get_number_format(round($subtotal,2),'.'), $summhidden);
			if(isset($rabatpercent)){
				$summhidden = str_replace('{GSSE_INCL_DISCOUNTPRCT}', $this->get_number_format($rabatpercent,'.'), $summhidden);
			} else {
				$summhidden = str_replace('{GSSE_INCL_DISCOUNTPRCT}', '', $summhidden);
			}
			
			$summhidden = str_replace('{GSSE_INCL_DISCOUNTVALUE}', $this->get_number_format($rabat,'.'), $summhidden);
			if(isset($custrabatpercent)){
				$summhidden = str_replace('{GSSE_INCL_CUSTDISCOUNTPRCT}', $this->get_number_format($custrabatpercent,'.'), $summhidden);
			} else {
				$summhidden = str_replace('{GSSE_INCL_CUSTDISCOUNTPRCT}', '', $summhidden);
			}
			$summhidden = str_replace('{GSSE_INCL_CUSTDISCOUNTVALUE}', $this->get_number_format(round($custrabat,2),'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_INVOICETOTAL}', $this->get_number_format(round($invoicetotal,2),'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VAT1}', $this->get_number_format($aVats[0]['vatrate'],'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VATTOTAL1}', $this->get_number_format(round($aVats[0]['vattotal'],2),'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VAT2}', $this->get_number_format($aVats[1]['vatrate'],'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VATTOTAL2}', $this->get_number_format(round($aVats[1]['vattotal'],2),'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VAT3}', $this->get_number_format($aVats[2]['vatrate'],'.'), $summhidden);
			$summhidden = str_replace('{GSSE_INCL_VATTOTAL3}', $this->get_number_format(round($aVats[2]['vattotal'],2),'.'), $summhidden);
			
			if(isset($basket2_is_loc)) {
				$buy3hidden = str_replace('{GSSE_INCL_ITEMSUMMARYFIELDS}', $summhidden, $buy3hidden);
			} else {
				$this->content = str_replace('{GSSE_INCL_ITEMSUMMARYFIELDS}', $summhidden, $this->content);
			}
		}
		/*End on buy3 hidden summary fields*/
	}
}
?>