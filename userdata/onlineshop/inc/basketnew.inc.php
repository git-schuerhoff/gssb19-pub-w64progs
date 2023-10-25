<?php
include_once('class.order.php');
$order = New Order();
//Achtung!!! Parameter werden als Array $aParam �bergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter f�r die Funktion fangen mit $aParam[1]
$basket = '';
$order = unserialize($_SESSION['order']);
$basketObject = $order->getBasket();
/*
{GSSE_INCL_CLASSVATCOL} = no-display | ''
{GSSE_INCL_CLASSVATHEAD} = no-display | a-center
{GSSE_INCL_CLASSVATDATA} = no-display | a-right
*/

/*echo "<pre>";
print_r($basketObject);
die("</pre>");*/
//echo "</pre>";

$pretotal = 0;//Vorl�ufiger Gesamtwarenwert
$discountpercent = 0;//Gesamtrabatt aus Kundenrabatt + Warenwertrabatt

$lBasketHasRentals = false;
$hasrentalclass = 'no-display';
if(isset($basketObject) && count($basketObject) > 0) {
	/*TS 11.12.2015: Warenkorb nach Mietpreisen durchsuchen*/
	/*TS 07.03.2016: und gleichzeitig vorl�ufige Gesamtsumme berechnen*/
	foreach($basketObject as $val) {
		if($val['art_prices']['isrental'] == 'Y') {
			$lBasketHasRentals = true;
			//break;
		}
		$pretotal += $val['art_price'] * $val['art_count'];
	}
	
	/*TS 11.12.2015: Wenn welche gefunden wurden, entsprechende Text anzeigen*/
	if($lBasketHasRentals === true) {
		$hasrentalclass = 'gs-float-left';
	}
	$this->content = str_replace('{GSSE_INCL_BASKETHASRENTALITEMS}', $hasrentalclass, $this->content);
	
	if(isset($_SESSION['desktop'])) {
		if($_SESSION['desktop']['is_phone'] == 1) {
			$basket = $this->gs_file_get_contents($this->absurl . 'template/basket_outer_mobile.html');
			$b_item = $this->gs_file_get_contents($this->absurl . 'template/basket_item_mobile.html');
			$b_item_small = $this->gs_file_get_contents($this->absurl . 'template/basket_item_mobile_small.html');
		} else {
			$basket = $this->gs_file_get_contents($this->absurl . 'template/basket_outer.html');
			$b_item = $this->gs_file_get_contents($this->absurl . 'template/basket_item.html');
			$b_item_small = $this->gs_file_get_contents($this->absurl . 'template/basket_item_small.html');
		}
	} else {
		$basket = $this->gs_file_get_contents($this->absurl . 'template/basket_outer.html');
		$b_item = $this->gs_file_get_contents($this->absurl . 'template/basket_item.html');
		$b_item_small = $this->gs_file_get_contents($this->absurl . 'template/basket_item_small.html');
	}
	
	$basket = $this->parse_texts($this->get_tags_ret($basket),$basket);
	$b_item = $this->parse_texts($this->get_tags_ret($b_item),$b_item);
	$pcontent = $this->gs_file_get_contents($this->absurl . 'template/pcontent.html');
	
	if($this->get_setting('cbShowVAT_Checked') == 'True') {
		$classvatcol = '';
		$classvathead = 'a-center';
		$classvatdata = 'a-right';
		$showitemvat = true;
		$showtotalvat = true;
	} else {
		$classvatcol = 'no-display';
		$classvathead = 'no-display';
		$classvatdata = 'no-display';
		$showitemvat = false;
		$showtotalvat = false;
	}
	
	/*Mit oder ohne MwSt.*/
	if($this->get_setting('cbNetPrice_Checked') == 'True') {
		//Nettopreise
		$inclvat = false;
		if($this->get_setting('cbShowVAT_Checked') == 'True') {
			$itemvattitle = $this->get_lngtext('LangTagTextShortExclVAT');
		} else {
			$itemvattitle = '';
		}
	} else {
		//Bruttopreise
		$inclvat = true;
		if($this->get_setting('cbShowVAT_Checked') == 'True') {
			$itemvattitle = $this->get_lngtext('LangTag__FieldLongVat');
		} else {
			$itemvattitle = '';
		}
	}
	/*$basket = str_replace('',,$basket);*/
	$basket = str_replace('{GSSE_INCL_CLASSVATCOL}',$classvatcol,$basket);
	$basket = str_replace('{GSSE_INCL_CLASSVATHEAD}',$classvathead,$basket);
	$basket = str_replace('{GSSE_INCL_CLASSVATDATA}',$classvatdata,$basket);
	$basket = str_replace('{GSSE_INCL_VATTITLE}',$itemvattitle,$basket);
	$itemimg = $this->gs_file_get_contents($this->absurl . 'template/image.html');
	$itemimglink = $this->gs_file_get_contents($this->absurl . 'template/imagelink.html');
	$itemnamelink = $this->gs_file_get_contents($this->absurl . 'template/link.html');
	
	$aVats = $this->get_vats();
	$p = 0;
	$coupon = 0;
	$sub_total = 0;
	$basketweight = $order->BasketWeight;//0;
	$all_items = '';
	$cnt = count($basketObject);
	//�ber alle Artikel im Warenkorb
	foreach($basketObject as $val) {
		$loop_coupon = 0;
		if($p == 0) {
			$fol = 'first ';
		} else {
			if($p == ($cnt - 1)) {
				$fol = 'last ';
			} else {
				$fol = '';
			}
		}
		if(($p % 2) == 0) {
			$eoo = 'even';
		} else {
			$eoo = 'odd';
		}
		
		/*Feststellen, ob einer der Artikel im Warenkorb bereits ein Gutschein ist*/
		if(preg_match('/'.$this->get_lngtext('LangTagCoupon').'/', $val['art_title'])) {
			$coupon = 1;
			$loop_coupon = 1;
		}
		
		//TS 11.12.2015: F�r Ersteinrichtungsgeb�hr oder Trialzeiten kleinere Zeilen verwenden
		if($val['art_isinitprice'] == 1 || $val['art_isttrialitem'] == 'Y') {
			$cur_item = $b_item_small;
		} else {
			$cur_item = $b_item;
		}
		
		
		$cur_itemtotal = 0;
		//$basketweight += $val['art_weight'] * $val['art_count'];
		/*$cur_item = str_replace('',,$cur_item);*/
		$cur_item = str_replace('{GSSE_INCL_FOL}',$fol,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_EOO}',$eoo,$cur_item);
				
		/*Image and Name*/
		//TS 11.12.2015: Bei Mietpreisen kein Bild bei den Ersteinrichtungsgeb�hren und der Trialzeit anzeigen
		if($val['art_isinitprice'] == 0 && $val['art_isttrialitem'] == 'N') {
			if($val['art_img'] != '') {
				/* SM 20.10.2014 - Bild online oder lokal?*/
				if(strpos($val['art_img'],"http") === false && strpos($val['art_img'],"://") === false) {
					if(file_exists('images/medium/' . $val['art_img'])) {
						$imgfile = $this->absurl . 'images/medium/' . $val['art_img'];
					} else {
						$imgfile = $this->absurl . 'template/images/no_pic_sma.png';
					}
				} else {
					$imgfile = $val['art_img'];
				}
			} else {
				if($loop_coupon == 0) {
					$imgfile = $this->absurl . 'template/images/no_pic_sma.png';
				} else {
					$imgfile = $this->absurl . 'template/images/coupon.png';
				}
			}
			if($val['art_hasdetail'] == 'Y') {
				$cur_img = $itemimglink;
				$cur_img = str_replace('{GSSE_INCL_LINKCLASS}','product-image',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_LINKURL}',$val['art_dpn'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGSRC}',$imgfile,$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGALT}',$val['art_title'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$val['art_title'],$cur_img);
				
				$cur_name = $itemnamelink;
				$cur_name = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_name);
				$cur_name = str_replace('{GSSE_INCL_LINKURL}',$val['art_dpn'],$cur_name);
				$cur_name = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_name);
				$cur_name = str_replace('{GSSE_INCL_LINKNAME}',$val['art_title'],$cur_name);
			} else {
				$cur_img = $itemimg;
				$cur_img = str_replace('{GSSE_INCL_IMGCLASS}','gs_basket_img',$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGSRC}',$imgfile,$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGALT}',$val['art_title'],$cur_img);
				$cur_img = str_replace('{GSSE_INCL_IMGTITLE}',$val['art_title'],$cur_img);
				$cur_p = $pcontent;
				$cur_p = str_replace('{GSSE_INCL_PCLASS}','product-image',$cur_p);
				$cur_img = str_replace('{GSSE_INCL_PCONTENT}',$cur_img,$cur_p);
				$cur_name = $val['art_title'];
			}
		} else {
			$cur_name = $val['art_title'];
			$cur_img = '';
		}
		
		//Variantenname
		if($val['art_vartitle'] != '') $cur_name .= ' '.$val['art_vartitle'];
		
		$cur_item = str_replace('{GSSE_INCL_ITEMIMG}',$cur_img,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMNAME}',$cur_name,$cur_item);
		/*Attributes, Text & Item-Number*/
		$aAttr = array();
		$cAttr = '';
		if($val['art_attr0'] != '') $aAttr[] = $val['art_attr0'];
		if($val['art_attr1'] != '') $aAttr[] = $val['art_attr1'];
		if($val['art_attr2'] != '') $aAttr[] = $val['art_attr2'];
		if(count($aAttr) > 0) {
			$cAttr = implode(', ', $aAttr);
		}
		$cur_item = str_replace('{GSSE_INCL_ATTRIBUTES}',$cAttr,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_TEXT}',$val['art_textfeld'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMNUMBER}',$val['art_num'],$cur_item);
		/*$cur_item = str_replace('',,$cur_item);*/
		/*Item-Price*/
		//USt-Faktor:
		$vat_rate = $val['art_vatrate'];
		$vat_factor = (100 + $vat_rate)/100;
		
		if($coupon==1){
			$item_price = $val['art_defprice'];
		} else {
			$item_price = $val['art_price'];
		}	
		$item_defprice = $val['art_defprice'];//$item_price;
		$discounttext = '';
		if(($val['art_discount'] > 0) && ($coupon==0)) {
			$discounttext = "(- " . $this->get_number_format($val['art_discount'],'') . "% " . $this->get_lngtext('LangTagFNFieldLocDiscount') . ")";
		}
		$cur_item = str_replace('{GSSE_INCL_DISCOUNT}',$discounttext,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($item_price,0,'.'),$cur_item);
		/*Begin VAT*/
		
		//die("Vatfactor: " . $vat_factor);
		if($showitemvat) {
			if($inclvat) {
				$vat = $item_price - ($item_price / $vat_factor);
			} else {
				$vat = ($item_price * $vat_factor) - $item_price;
			}
			$cur_item = str_replace('{GSSE_INCL_ITEMVAT}',$this->get_currency($vat,0,'.'),$cur_item);
		} else {
			$cur_item = str_replace('{GSSE_INCL_ITEMVAT}','',$cur_item);
			$cur_item = str_replace('{GSSE_INCL_CLASSVATDATA}',$classvatdata,$cur_item);
		}
		$lFound = false;
		$vatsmax4 = count($aVats);
		for($v = 0; $v < $vatsmax4; $v++) {
			if($aVats[$v]['vatrate'] == $vat_rate) {
				$lFound = true;
				break;
			}
		}
		if($lFound === true) {
			$aVats[$v]['vattotal'] = $aVats[$v]['vattotal'] + ($vat * $val['art_count']);
		} else {
			/*array_push($aVats,array("vatrate" => $vat_rate,"vattotal" => $vat));*/
			$aVats[] = array("vatrate" => $vat_rate,"vattotal" => ($vat * $val['art_count']));
		}
		/*End VAT*/
		$cur_item = str_replace('{GSSE_INCL_ITEMID}',$val['art_id'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_BASKETIDX}',$p,$cur_item);
		
		/*Item-Total*/
		$cur_itemtotal = $val['art_totalprice'];// * $item_price;
		$cur_item = str_replace('{GSSE_INCL_ITEMTOTAL}',$this->get_currency($cur_itemtotal,0,'.'),$cur_item);
		
		/*Item Qty*/
		$basketqtychgclass = 'qty_cart';
		$basketqtydispclass = 'no-display';
		$billingperiod = '';
		$cur_item = str_replace('{GSSE_INCL_ITEMQTY}',$val['art_count'],$cur_item);
		/*Rental price?*/
		if($val['art_prices']['isrental'] == 'Y') {
			$basketqtychgclass = 'no-display';
			$basketqtydispclass = 'gs-basket-qty';
			if($val['art_isinitprice'] != 1) {
				$billingperiod = '&nbsp;' . $this->get_billingperiodfromid($val['art_prices']['billingperiod'],false,true,false);
			}
		}
		$cur_item = str_replace('{GSSE_INCL_BASKETQTYCHGCLASS}',$basketqtychgclass,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_BASKETQTYDISPCLASS}',$basketqtydispclass,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_BILLINGPERIOD}',$billingperiod,$cur_item);
		$sub_total += $cur_itemtotal;
		$all_items .= $cur_item;
		$p++;
	}//foreach
	$basket = str_replace('{GSSE_INCL_BASKETITEMS}',$all_items,$basket);
	
	/*Coupon*/
	$couponhtml = '';
	if($this->phpactive()) {
		if($this->get_setting('cbUsePhpCoupon_Checked') == 'True') {
			if($coupon == 0) {
				$couponhtml = $this->gs_file_get_contents($this->absurl . 'template/couponadd.html');
				$couponhtml = $this->parse_texts($this->get_tags_ret($couponhtml),$couponhtml);
			} else {
				$couponhtml = $this->gs_file_get_contents($this->absurl . 'template/couponcashed.html');
				$couponhtml = $this->parse_texts($this->get_tags_ret($couponhtml),$couponhtml);
			}
		}
	}
	$basket = str_replace('{GSSE_INCL_COUPON}', $couponhtml, $basket);
	
	/*Totals*/
	include('./inc/basket_totals.inc.php');
	$basket = str_replace('{GSSE_INCL_BASKETTOTALS}', $baskettotalhtml, $basket);
} else {
	$this->content = str_replace('{GSSE_INCL_BASKETHASRENTALITEMS}', $hasrentalclass, $this->content);
	$basket = $this->gs_file_get_contents($this->absurl . 'template/errorbox.html');
	$basket = str_replace('{GSSE_MSG_ERRORNEWCLASS}','notice-msg',$basket);
	$basket = str_replace('{GSSE_MSG_ERRORNEW}',$this->get_lngtext('LangTagTextBasketEmpty'),$basket);
}

$this->content = str_replace($tag, $basket, $this->content);
?>
