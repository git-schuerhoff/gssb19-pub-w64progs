<?php
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$minibasket = $this->gs_file_get_contents($this->absurl . 'template/mini_basket_outer.html');
$minibasket = $this->parse_texts($this->get_tags_ret($minibasket),$minibasket);
$itemlink = $this->gs_file_get_contents($this->absurl . 'template/link.html');
$itemimagelink = $this->gs_file_get_contents($this->absurl . 'template/imagelink.html');
$itemimage = $this->gs_file_get_contents($this->absurl . 'template/image.html');
$pcontent = $this->gs_file_get_contents($this->absurl . 'template/pcontent.html');
$minibasketitems = $this->gs_file_get_contents($this->absurl . 'template/mini_basketitems_outer.html');
$minibasketitems = $this->parse_texts($this->get_tags_ret($minibasketitems),$minibasketitems);
$mbitem = $this->gs_file_get_contents($this->absurl . 'template/mini_basketitems_item.html');
$mbitem = $this->parse_texts($this->get_tags_ret($mbitem),$mbitem);
$cnt = 0;
$total = 0.0;
$all_items = '';
/*Mit oder ohne MwSt.*/
if($this->get_setting('cbNetPrice_Checked') == 'True') {
	//Nettopreise
	$inclvat = false;
} else {
	//Bruttopreise
	$inclvat = true;
}

if(is_array($basket)){
	$cnt = count($basket);
} else {
	$cnt = 0;
}
if($cnt > 0) {

	$p = 0;
	foreach($basket as $val) {
		$loop_coupon = 0;
		/*Feststellen, ob einer der Artikel im Warenkorb bereits ein Gutschein ist*/
		if(preg_match('/'.$this->get_lngtext('LangTagCoupon').'/', $val['art_title'])) {
			$loop_coupon = 1;
		}
		if($p == ($cnt - 1)) {
			$last = ' last';
		} else {
			$last = '';
		}
		if(($p % 2) == 0) {
			$eoo = ' even';
		} else {
			$eoo = ' odd';
		}
		$cur_item = $mbitem;
		//$cur_item = str_replace('',,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_LAST}',$last,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_EOO}',$eoo,$cur_item);
		$aAttr = array();
		$cAttr = '';
		if($val['art_attr0'] != '') $aAttr[] = $val['art_attr0'];
		if($val['art_attr1'] != '') $aAttr[] = $val['art_attr1'];
		if($val['art_attr2'] != '') $aAttr[] = $val['art_attr2'];
		if(count($aAttr) > 0) {
			$cAttr = implode(', ', $aAttr);
		}
		$vat_rate = $val['art_vatrate'];
		$vat_factor = (100 + $vat_rate)/100;
		$item_price = $val['art_price'];
		/*A TS 07.03.2016: Ggf. Rabatt von NETTO!!!!!!-Preis abziehen*/
		//Originalpreis sichern
		$item_defprice = $item_price;
		if($val['art_discount'] > 0) {
			if($inclvat) {
				//Shop hat Bruttopreise, d. h. erst UST herausrechnen
				$netprice = round($item_price / $vat_factor,2);
				//Dann Rabatt rausrechnen
				//$disprice = round($netprice / ((100+$val['art_discount'])/100),2);
				$disprice = $netprice - round(($netprice/100)*$val['art_discount'],2);
				//Und MwSt wieder drauf
				//$item_price = round($disprice * $vat_factor,2);
			} else {
				//Shop hat Nettopreise
				//Hier brauch nur der Rabatt rausgezogen werden
				//$item_price = round($item_price / ((100+$val['art_discount'])/100),2);
			}
		}
		$cur_item = str_replace('{GSSE_INCL_ATTRIBUTES}',$cAttr,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_TEXT}',$val['art_textfeld'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMNUMBER}',$val['art_num'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMQTY}',$val['art_count'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($item_price,0,'.'),$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMIDX}',$p,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMPRICEINFO}',$this->get_setting('edPriceInformation_Text'),$cur_item);
				
		/*Image and Name*/
		if($val['art_img'] != '') {
			/* SM 20.10.2014 - Bild online oder lokal?*/
			if(strpos($val['art_img'],"http") === false && strpos($val['art_img'],"://") === false) {
				$imgfile = $this->absurl . 'images/medium/' . $val['art_img'];
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
			$cur_image = $itemimage;
			$cur_image = str_replace('{GSSE_INCL_IMGCLASS}','',$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGSRC}',$imgfile,$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGALT}',$val['art_title'],$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGTITLE}',$val['art_title'],$cur_image);
			$cur_name = $itemlink;
			$cur_name = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_name);
			$cur_name = str_replace('{GSSE_INCL_LINKURL}',$val['art_dpn'],$cur_name);
			$cur_name = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_name);
			$cur_name = str_replace('{GSSE_INCL_LINKNAME}',$val['art_title'],$cur_name);
		} else {
			$cur_name = $val['art_title'];
			$cur_image = $itemimage;
			$cur_image = str_replace('{GSSE_INCL_IMGCLASS}','',$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGSRC}',$imgfile,$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGALT}',$val['art_title'],$cur_image);
			$cur_image = str_replace('{GSSE_INCL_IMGTITLE}',$val['art_title'],$cur_image);
			$cur_p = $pcontent;
			$cur_p = str_replace('{GSSE_INCL_PCLASS}','product-image',$cur_p);
			$cur_image = str_replace('{GSSE_INCL_PCONTENT}',$cur_image,$cur_p);
		}
		//Variantenname
		if($val['art_vartitle'] != '') $cur_name .= ' '.$val['art_vartitle'];
		$cur_item = str_replace('{GSSE_INCL_ITEMNAME}',$cur_name,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMIMG}',$cur_image,$cur_item);
		$total += $item_price*$val['art_count'];
		$all_items .= $cur_item;
		$p++;
	}//Foreach
	$minibasketitems = str_replace('{GSSE_INCL_MBITEMS}',$all_items,$minibasketitems);
	$minibasketitems = str_replace('{GSSE_INCL_BUYURL}',$this->absurl . 'index.php?page=buy',$minibasketitems);
	$minibasketitems = str_replace('{GSSE_INCL_BASKETURL}',$this->absurl . 'index.php?page=basket',$minibasketitems);
	$minibasketitems = str_replace('{GSSE_INCL_MBSUBTOTAL}',$this->get_currency($total,0,'.'),$minibasketitems);
	$minvalue = floatval(str_replace(',','.',$this->get_setting('edMinOrderValue_Text')));
	if($minvalue > $total) {
		$notreached = $this->get_lngtext('LangTagTextMinOrderNewValue1') . ' ' . $this->get_currency($total,0,'.') . ' ' .
		$this->get_lngtext('LangTagTextMinOrderNewValue2') . ' ' . $this->get_currency($minvalue,0,'.') . ' ' .
		$this->get_lngtext('LangTagTextMinOrderNewValue3');
		$minibasketitems = str_replace('{GSSE_INCL_SHOWCOBUTTON}','no-display',$minibasketitems);
		$minibasketitems = str_replace('{GSSE_MSG_ERRORNEWCLASS}','notice-msg',$minibasketitems);
		$minibasketitems = str_replace('{GSSE_MSG_ERRORNEW}',$notreached,$minibasketitems);
	} else {
		$minibasketitems = str_replace('{GSSE_INCL_SHOWCOBUTTON}','button',$minibasketitems);
		$minibasketitems = str_replace('{GSSE_MSG_ERRORNEWCLASS}','no-display',$minibasketitems);
		$minibasketitems = str_replace('{GSSE_MSG_ERRORNEW}','',$minibasketitems);
	}
} else {//if($cnt > 0)
	$minibasketitems = $this->gs_file_get_contents($this->absurl . 'template/mini_basket_empty.html');
	$minibasketitems = $this->parse_texts($this->get_tags_ret($minibasketitems),$minibasketitems);
}//if($cnt > 0)

$minibasket = str_replace('{GSSE_INCL_BASKETURL}',$this->absurl . 'index.php?page=basket',$minibasket);
$minibasket = str_replace('{GSSE_INCL_MINIBASKETITEMS}',$minibasketitems,$minibasket);
$minibasket = str_replace('{GSSE_INCL_CARTQTY}',$cnt,$minibasket);
$minibasket = str_replace('{GSSE_INCL_CARTVALUE}',$this->get_currency($total,0,'.'),$minibasket);

$this->content = str_replace($tag, $minibasket, $this->content);

?>
