<?php
include_once('class.order.php');
$order = New Order();
session_start();
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$baskethtml = '';
$baskettotal = 0;
$coupon = 0;
if(isset($basket))
{
	/*echo '<pre>';
	print_r($basket);
	die('</pre>');*/
	if(count($basket) > 0)
	{
		$basketitem = $this->gs_file_get_contents('template/basketitem.html');
		$for_max = count($basket);
		for($b = 0; $b < $for_max; $b++)
		{
			$cur_item = $basketitem;
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
			
			$itemtitle = $basket[$b]['art_num'] . ' ' . $basket[$b]['art_title'];
			$itemtitle .= ($basket[$b]['art_vartitle'] != '') ? ', ' . $basket[$b]['art_vartitle'] : '';
			$itemtitle .= ($basket[$b]['art_attr0'] != '') ? ', ' . $basket[$b]['art_attr0'] : '';
			$itemtitle .= ($basket[$b]['art_attr1'] != '') ? ', ' . $basket[$b]['art_attr1'] : '';
			$itemtitle .= ($basket[$b]['art_attr2'] != '') ? ', ' . $basket[$b]['art_attr2'] : '';
			$itemtitle .= ($basket[$b]['textfeld'] != '') ? ', ' . $basket[$b]['textfeld'] : '';
			
			$cAge = '';
			if($basket[$b]['art_checkage'] == 'Y' && $basket[$b]['art_mustage'] > 0)
			{
				$cAge = '<br />' . $this->get_lngtext('LangTagMinimumAge'). ': ' . $basket[$b]['art_mustage'] . ' ' . $this->get_lngtext('LangTagYears');
			}
			
			$itemtitle .= $cAge;
			
			
			if($basket[$b]['art_dpn'] == '')
			{
				if($basket[$b]['art_img'] != '')
				{
					$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','icon',$cur_itempic);
					/* Bild online oder lokal?*/
					if(strpos($basket[$b]['art_img'],"http") === false && strpos($basket[$b]['art_img'],"://") === false) {
						//TS 30.12.2016: Datei auf Existenz prüfen und ggf. andere Dateien laden
						if(file_exists('images/medium/' . $basket[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basket[$b]['art_img'],$cur_itempic);
						} elseif(file_exists('images/small/' . $basket[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/small/' . $basket[$b]['art_img'],$cur_itempic);
						} elseif(file_exists('images/big/' . $basket[$b]['art_img'])) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/big/' . $basket[$b]['art_img'],$cur_itempic);
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','template/images/no_pic_mid.png',$cur_itempic);
						}
					} else {
						$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basket[$b]['art_img'],$cur_itempic);
					}
					$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basket[$b]['art_title'],$cur_itempic);
					$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basket[$b]['art_title'],$cur_itempic);
				}
				$cur_itemtitle = $itemtitle;
				
			}
			else
			{
				if($basket[$b]['art_hasdetail'] != 'N')
				{
					if($basket[$b]['art_img'] != '')
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKURL}',$basket[$b]['art_dpn'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','icon',$cur_itempic);
						/* Bild online oder lokal?*/
						if(strpos($basket[$b]['art_img'],"http") === false && strpos($basket[$b]['art_img'],"://") === false) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basket[$b]['art_img'],$cur_itempic);
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basket[$b]['art_img'],$cur_itempic);
						}
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basket[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basket[$b]['art_title'],$cur_itempic);
					}
					$cur_itemtitle = $this->gs_file_get_contents('template/link.html');
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKURL}',$basket[$b]['art_dpn'],$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKTARGET}','_self',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKNAME}',$itemtitle,$cur_itemtitle);
				}
				else
				{
					if($basket[$b]['art_img'] != '')
					{
						$cur_itempic = $this->gs_file_get_contents('template/imagelink.html');
						$cur_itempic = str_replace('<a class="{GSSE_INCL_LINKCLASS}" href="{GSSE_INCL_LINKURL}" target="{GSSE_INCL_LINKTARGET}">','',$cur_itempic);
						$cur_itempic = str_replace('</a>','',$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGCLASS}','icon',$cur_itempic);
						/* Bild online oder lokal?*/
						if(strpos($basket[$b]['art_img'],"http") === false && strpos($basket[$b]['art_img'],"://") === false) {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}','images/medium/' . $basket[$b]['art_img'],$cur_itempic);
						} else {
							$cur_itempic = str_replace('{GSSE_INCL_IMGSRC}', $basket[$b]['art_img'],$cur_itempic);
						}
						$cur_itempic = str_replace('{GSSE_INCL_IMGALT}',$basket[$b]['art_title'],$cur_itempic);
						$cur_itempic = str_replace('{GSSE_INCL_IMGTITLE}',$basket[$b]['art_title'],$cur_itempic);
					}
					$cur_itemtitle = $this->gs_file_get_contents('template/link.html');
					$cur_itemtitle = str_replace('<a class="{GSSE_INCL_LINKCLASS}" href="{GSSE_INCL_LINKURL}" target="{GSSE_INCL_LINKTARGET}">','',$cur_itemtitle);
					$cur_itemtitle = str_replace('</a>','',$cur_itemtitle);
					$cur_itemtitle = str_replace('{GSSE_INCL_LINKNAME}',$itemtitle,$cur_itemtitle);
				} 
			}
			
			if($basket[$b]['art_isdownload'] == 'N' && !preg_match('/'.$this->get_lngtext('LangTagCoupon').'/', $basket[$b]['art_title']))
			{
				$cur_artcount = $this->gs_file_get_contents('template/basketitemcount.html');
				$cur_artcount = str_replace('{GSSE_INCL_BASKETITEMCOUNT}',$basket[$b]['art_count'],$cur_artcount);
				$cur_artcount = str_replace('{GSSE_INCL_BASKETITEMKEY}',$b,$cur_artcount);
				$cur_artcount = str_replace('{GSSE_INCL_ISDECIMAL}',$basket[$b]['art_isdecimal'],$cur_artcount);
			}
			else
			{
				$cur_artcount = $basket[$b]['art_count'];
			}
			
			$cur_delete = $this->gs_file_get_contents('template/basketitemdelete.html');
			$cur_delete = str_replace('{GSSE_LANG_LangTagDelete}',$this->get_lngtext('LangTagDelete'),$cur_delete);
			$cur_delete = str_replace('{GSSE_INCL_BASKETITEMKEY}',$b,$cur_delete);
			
			$cur_item = str_replace('{GSSE_INCL_BASKETROWCLASS}',$class,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMPIC}',$cur_itempic,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMTITLE}',$cur_itemtitle,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMCOUNT}',$cur_artcount,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMPRICE}',$this->get_currency($basket[$b]['art_price'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMTOTAL}',$this->get_currency($basket[$b]['art_price'] * $basket[$b]['art_count'],0,'.'),$cur_item);
			$cur_item = str_replace('{GSSE_INCL_BASKETITEMDELETE}',$cur_delete,$cur_item);
			$baskethtml .= $cur_item;
			$baskettotal += $basket[$b]['art_price'] * $basket[$b]['art_count'];
			
			/*Feststellen, ob einer der Artikel im Warenkorb bereits ein Gutschein ist*/
			if(preg_match('/'.$this->get_lngtext('LangTagCoupon').'/', $basket[$b]['art_title']))
			{
				$coupon = 1;
			}
		}
	}
}
$this->content = str_replace($tag, $baskethtml, $this->content);
$this->content = str_replace('{GSSE_INCL_BASKETTOTAL}', $this->get_currency($baskettotal,0,'.'), $this->content);

$basketemptyhtml = '';
if($this->get_setting('edMinOrderValue_Text') <> "")
{
	$basketbuyurl = 'chk_minordervalue('.$baskettotal.', '.$this->get_setting('edMinOrderValue_Text').');';
}
else
{
	$basketbuyurl = 'chk_minordervalue('.$baskettotal.', 0);';
}
if(!isset($basket))
{
	$basketemptyhtml = $this->gs_file_get_contents('template/basketempty.html');
	$basketemptyhtml = str_replace('{LangTagTextBasketEmpty}',$this->get_lngtext('LangTagTextBasketEmpty'),$basketemptyhtml);
	$basketbuyurl = 'alert(\'' . $this->get_lngtext('LangTagTextBasketEmpty') . '\')';
}
else
{
	if(count($basket) == 0)
	{
		$basketemptyhtml = $this->gs_file_get_contents('template/basketempty.html');
		$basketemptyhtml = str_replace('{LangTagTextBasketEmpty}',$this->get_lngtext('LangTagTextBasketEmpty'),$basketemptyhtml);
		$basketbuyurl = 'alert(\'' . $this->get_lngtext('LangTagTextBasketEmpty') . '\')';
	}
}
$this->content = str_replace('{GSSE_INCL_BASKETBUYURL}', $basketbuyurl, $this->content);
$this->content = str_replace('{GSSE_INCL_BASKETEMPTY}', $basketemptyhtml, $this->content);

$couponhtml = '';
if($this->phpactive())
{
	if($this->get_setting('cbUsePhpCoupon_Checked') == 'True')
	{
		if($coupon == 0)
		{
			$couponhtml = $this->gs_file_get_contents('template/couponadd.html');
			$couponhtml = str_replace('{GSSE_LANG_LangTagCouponCode}', $this->get_lngtext('LangTagCouponCode'), $couponhtml);
			$couponhtml = str_replace('{GSSE_LANG_LangTagWrongCouponCode}', '', $couponhtml);
			$couponhtml = str_replace('{GSSE_LANG_LangTagOk}', $this->get_lngtext('LangTagOk'), $couponhtml);
			$couponhtml = str_replace('{GSSE_LANG_LangTagCouponText}', $this->get_lngtext('LangTagCouponText'), $couponhtml);
		}
		else
		{
			$couponhtml = $this->gs_file_get_contents('template/couponcashed.html');
			$couponhtml = str_replace('{GSSE_LANG_LangTagCouponCashed}', $this->get_lngtext('LangTagCouponCashed'), $couponhtml);
		}
	}
}
$this->content = str_replace('{GSSE_INCL_COUPON}', $couponhtml, $this->content);
