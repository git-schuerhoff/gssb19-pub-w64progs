<?php
$p = 0;
$all_items = '';
$addtocartsmall = $this->gs_file_get_contents($this->absurl . 'template/addtocartsmall.html');
$gotodetailhtml = $this->gs_file_get_contents($this->absurl . 'template/gotodetail.html');
$itemlink = $this->gs_file_get_contents($this->absurl . 'template/link.html');
$itemimglink = $this->gs_file_get_contents($this->absurl . 'template/imagelink.html');
$itemimg = $this->gs_file_get_contents($this->absurl . 'template/image.html');
while($z = mysqli_fetch_assoc($erg))
{
	$cur_item = $item_box;
	if($p == 0)
	{
		$fol = ' first';
	}
	else
	{
		if($p == (mysqli_num_rows($erg) - 1))
		{
			$fol = ' last';
		}
		else
		{
			$fol = '';
		}
	}
	if(($p % 2) == 0)
	{
		$eoo = ' even';
	}
	else
	{
		$eoo = ' odd';
	}
	
	$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $z['itemItemId'] . '&amp;d=' . $z['itemItemPage'];
	/*A TS 09.12.2014: Permalink verwenden, wenn verfügbar*/
	if($this->edition == 13) {
		if($this->get_setting('cbUsePermalinks_Checked') == 'True') {
			if($z['itemItemPage'] != '') {
				$detailurl = $z['itemItemPage'];
			}
		}
	}
	
	
	/*$cur_item = str_replace('',,$cur_item);*/
	$cur_item = str_replace('{GSSE_INCL_FOL}',$fol,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_EOO}',$eoo,$cur_item);
	$this_link = '';
	$this_img = $itemimglink;
	if($z['itemHasDetail'] == 'Y')
	{
		$this_link = $itemlink;
		$this_link = str_replace('{GSSE_INCL_LINKCLASS}','',$this_link);
		$this_link = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$this_link);
		$this_link = str_replace('{GSSE_INCL_LINKTARGET}','_self',$this_link);
		$this_link = str_replace('{GSSE_INCL_LINKNAME}',$z['itemItemDescription'],$this_link);
				
		$this_img = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$this_img);
	}
	else
	{
		$this_link = $z['itemItemDescription'];
		$this_img = str_replace('{GSSE_INCL_LINKURL}','',$this_img);
	}
	$this_img = str_replace('{GSSE_INCL_LINKCLASS}','product-image',$this_img);
	$this_img = str_replace('{GSSE_INCL_LINKTARGET}','_self',$this_img);
	
	/*Images*/
	$imgclass = '';
	$aImgs = $this->get_itempics($z['itemItemId']);
	/* SM 20.10.2014 - Bild online oder lokal?*/
	/* TS 30.03.2015: Frage ob Array Elemente hat, damit es keine Notice gibt, wenn der Artikel kein Bild hat*/
	if(count($aImgs) > 0) {
		if(strpos($aImgs[0]['ImageName'],"http") === false && strpos($aImgs[0]['ImageName'],"://") === false) {
			if($aImgs[0]['ImageName'] != '' && file_exists('images/medium/' . $aImgs[0]['ImageName'])) {
				$this_img = str_replace('{GSSE_INCL_IMGSRC}',$this->absurl . 'images/medium/' . $aImgs[0]['ImageName'],$this_img);
			} else {
				$this_img = str_replace('{GSSE_INCL_IMGSRC}',$this->absurl . 'template/images/no_pic_mid.png',$this_img);
			}
		} else {
			$this_img = str_replace('{GSSE_INCL_IMGSRC}', $aImgs[0]['ImageName'],$this_img);
		}
	} else {
		$this_img = str_replace('{GSSE_INCL_IMGSRC}',$this->absurl . 'template/images/no_pic_mid.png',$this_img);
	}
	$this_img = str_replace('{GSSE_INCL_IMGCLASS}',$imgclass,$this_img);
	$this_img = str_replace('{GSSE_INCL_IMGALT}',$z['itemItemDescription'],$this_img);
	$this_img = str_replace('{GSSE_INCL_IMGTITLE}',$z['itemItemDescription'],$this_img);
	$cur_item = str_replace('{GSSE_INCL_ITEMIMG}',$this_img,$cur_item);
	
	$cur_item = str_replace('{GSSE_INCL_ITEMTITLE}',$this_link,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_ITEMNUMBER}',$z['itemItemNumber'],$cur_item);
	
	/*Prices*/
	$aPrices = $this->get_prices($z['itemItemId']);
	/*print_r($aPrices);
	die();*/
	$action = 'N';
	$actionhtmlnew = '';
	if((isset($z['itemIsAction'])) && ($z['itemIsAction'] == 'Y'))
	{
		$action = $this->chk_action($z['itemItemId'],$aPrices);
	}
	/*echo "Aktion: " . $action . "<br><pre>";
	print_r($aPrices);
	echo("</pre><br>");*/
	
	/*Labels & actions*/
	$saleperiod = '&nbsp;';//New Template
	if($action == 'Y')
	{
		$actionhtmlnew = $this->gs_file_get_contents('template/labelsale.html');
		$actionhtmlnew = str_replace('{GSSE_LANG_LangTagLabelSale}',$this->get_lngtext('LangTagLabelSale'),$actionhtmlnew);
		if($aPrices['actshowperiod'] == 'Y')
		{
			$saleperiod = $aPrices['actbegindate'] . " - " . $aPrices['actenddate'];
		}
	}
	$cur_item = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_LABELSALE}',$actionhtmlnew,$cur_item);
	if($z['itemIsNewItem'] == 'Y')
	{
		$new = $this->gs_file_get_contents('template/labelnew.html');
		$new = str_replace('{GSSE_LANG_LangTagLabelNew}',$this->get_lngtext('LangTagLabelNew'),$new);
	}
	else
	{
		$new = '';
	}
	$cur_item = str_replace('{GSSE_INCL_LABELNEW}',$new,$cur_item);
	
	$bests = '';
	if($this->phpactive())
	{
		if($this->get_setting('cbUsePhpBestseller_Checked') == 'True')
		{
			if($this->item_is_bestseller($z['itemItemNumber']))
			{
				$bests = $this->gs_file_get_contents('template/labelbest.html');
			}
		}
	}
	$cur_item = str_replace('{GSSE_INCL_LABELBEST}',$bests,$cur_item);
	
	
	/*Price(s)*/
	$price = 0;
	$oldprice = '&nbsp;';
	$priceclass = 'price';
	$oldpriceclass = '';
	$trialperiod = '&nbsp;';
	$aftertrial = '&nbsp;';
	$billingperiod = '&nbsp;';
	$aftertrialprice = '&nbsp;';
	$aftertrialperiod = '&nbsp;';
	$runtime = '&nbsp;';
	$runtimelng = '&nbsp;';
	if(isset($aPrices['price'])) {
		$price = $this->get_currency($aPrices['price'],0,'.');
		if($aPrices['oldprice'] > 0 && $action == 'N')
		{
			$priceclass = 'special-price';
			$oldpriceclass = 'price';
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
			$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($aPrices['oldprice'],0,'.'),$oldprice);
		}
		if($action == 'Y' && $aPrices['actshownormal'] == 'Y')
		{
			if($aPrices['actnormprice'] != '' && $aPrices['actnormprice'] != '0')
			{
				$normprice = $aPrices['actnormprice'];
			}
			else
			{
				$normprice = $aPrices['oldprice'];
			}
			$priceclass = 'special-price';
			$oldpriceclass = 'price';
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
			$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency(str_replace(',','.',$normprice),0,'.'),$oldprice);
		}
		if($action == 'N')
		{
			if(isset($aPrices['abulk']))
			{
				if(count($aPrices['abulk']) > 0)
				{
					$price = $this->get_lngtext('LangTagFromNew') . ' ' . $this->get_currency($aPrices['abulk'][0][1],0,'.');
				}
			}
		}
		
		$tpnodisp = ' no-display';
		$bpnodisp = ' no-display';
		$trnodisp = ' no-display';
		if(isset($aPrices['isrental'])) {
			if($aPrices['isrental'] == 'Y') {
				$bpnodisp = '';
				if($aPrices['istrial'] == 'Y') {
					$tpnodisp = '';
					$trnodisp = '';
					if($aPrices['trialfrequency'] > 1) {
						$lPlural = true;
					} else {
						$lPlural = false;
					}
					
					$aftertrialprice = $price;
					if($aPrices['trialprice'] > 0) {
						$trialperiod = $aPrices['trialfrequency'] . " " . $this->get_billingperiodfromid($aPrices['trialperiod'],false,$lPlural,false) . " " . $this->get_lngtext('LangTagForSomething');
						$price = $this->get_currency($aPrices['trialprice'],0,'.');
						$billingperiod = $this->get_billingperiodfromid($aPrices['trialperiod'],true,false,true);
					} else {
						$trialperiod = $aPrices['trialfrequency'] . " " . $this->get_billingperiodfromid($aPrices['trialperiod'],false,$lPlural,false);
						$price = $this->get_lngtext('LangTagForFree');
						$billingperiod = '&nbsp;';
					}
					
					$aftertrial = $this->get_lngtext('LangTagAfterSomething');
					$aftertrialperiod = $this->get_billingperiodfromid($aPrices['billingperiod'],true,false,true);
				} else {
					$billingperiod = $this->get_billingperiodfromid($aPrices['billingperiod'],true,false,true);
				}
				if($aPrices['rentalruntime'] > 0) {
					$runtimelng = $this->get_lngtext('LangTagRentalRunTime') . ": ";
					if($aPrices['rentalruntime'] > 1) {
						$lPlural = true;
					} else {
						$lPlural = false;
					}
					$runtime = $aPrices['rentalruntime'] . " " . $this->get_billingperiodfromid($aPrices['billingperiod'],false,$lPlural,false);
				}
			}
		}
	}
	$cur_item = str_replace('{GSSE_INCL_TPNODISPLAY}',$tpnodisp,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_BPNODISPLAY}',$bpnodisp,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_TRNODISPLAY}',$trnodisp,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_RUNTIMELNG}',$runtimelng,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_RUNTIME}',$runtime,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_AFTERTRIALPRICE}',$aftertrialprice,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_AFTERTRIALPERIOD}',$aftertrialperiod,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_TRIALPERIOD}',$trialperiod,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_AFTERTRIAL}',$aftertrial,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_BIILINGPERIOD}',$billingperiod,$cur_item);
	
	$cur_item = str_replace('{GSSE_INCL_OLDPRICENEW}',$oldprice,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_OLDPRICECLASS}',$oldpriceclass,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$price,$cur_item);
	
	/*Begin Exalyser specific*/
	$cur_item = str_replace('{GSSE_LANG_LangTagExaPricePerMonth}',$this->get_lngtext('LangTagExaPricePerMonth'),$cur_item);
	$cur_item = str_replace('{GSSE_LANG_LangTagExaPricePerMonthShort}',$this->get_lngtext('LangTagExaPricePerMonthShort'),$cur_item);
	/*Begin Exalyser specific*/
	
	//New template
	$cur_item = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$cur_item);
	
	/*Ratings*/
	$rating = '';
	if($this->phpactive())
	{
		if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
		{
			if(isset($_SESSION['login']))
			{
				if($_SESSION['login']['ok'])
				{
					$aRat = $this->get_av_rating($z['itemItemNumber']);
					/*$aRat[0]['schnitt'];
					$aRat[0]['menge'];*/
					$rating = $itemimg;
					$rating = str_replace('{GSSE_INCL_IMGCLASS}','',$rating);
					$rating = str_replace('{GSSE_INCL_IMGSRC}','template/images/rating' . $aRat[0]['schnitt'] . '0.gif',$rating);
					$rating = str_replace('{GSSE_INCL_IMGALT}','Rating',$rating);
					$rating = str_replace('{GSSE_INCL_IMGTITLE}','Rating',$rating);
				}
			}
		}
	}
	$cur_item = str_replace('{GSSE_INCL_RATINGIMG}',$rating,$cur_item);
	
	/*Add-To-Cart-Button*/
	/*Add-to-cart*/
	$addtocart = '';
	if($z['itemIsCatalogFlg'] == 'N' && $z['itemIsTextInput'] == 'N' && $z['itemAttribute1'] == '' && $z['itemAttribute2'] == '' && $z['itemAttribute3'] == '' && $aPrices['isrental'] != 'Y')
	{
		$addtocart = $addtocartsmall;
		$addtocart = str_replace('{GSSE_LANG_LangTagAddToBasket}',$this->get_lngtext('LangTagAddToBasket'),$addtocart);
		$addtocart = str_replace('{GSSE_INCL_ITEMID}',$z['itemItemId'],$addtocart);
	}
	else
	{
		if($z['itemHasDetail'] == 'Y')
		{
			$addtocart = $gotodetailhtml;
			$addtocart = str_replace('{GSSE_LANG_LangTagViewDetails}',$this->get_lngtext('LangTagViewDetails'),$addtocart);
			$addtocart = str_replace('{GSSE_INCL_LINKURL}',$detailurl,$addtocart);
		}
		
	}
	$cur_item = str_replace('{GSSE_INCL_ADDTOCARTSMALL}',$addtocart,$cur_item);
	
	/*Compare*/
	$itemcompare = '';
	if($this->get_setting('cbArticleCompare_Checked') == 'True')
	{
		$itemcompare = $this->gs_file_get_contents('template/item_comparison.html');
		$itemcompare = str_replace('{GSSE_LANG_LangTagArticleCompare}','',$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICID}',$z['itemItemId'],$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICNAME}',$z['itemItemDescription'],$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICPAGE}',$detailurl,$itemcompare);
	}
	$cur_item = str_replace('{GSSE_INCL_COMPAREITEM}',$itemcompare,$cur_item);
	
	/*Wishlist & Notepad*/
	$itemwishlist = '';
	if($this->phpactive())
	{
		if(isset($_SESSION['login']))
		{
			if($_SESSION['login']['ok'])
			{
				if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
				{
					/*Wishlist*/
					$itemwishlist = $this->gs_file_get_contents('template/item_towishlist.html');
					$itemwishlist = str_replace('{GSSE_INCL_ITEMNO}',$z['itemItemNumber'],$itemwishlist);
					$itemwishlist = str_replace('{GSSE_INCL_CUSID}',$_SESSION['login']['cusIdNo'],$itemwishlist);
					$itemwishlist = str_replace('{GSSE_INCL_DATE}',date("Ymd"),$itemwishlist);
					$itemwishlist = str_replace('{GSSE_LANG_LangTagMoveToWishList}',$this->get_lngtext('LangTagMoveToWishList'),$itemwishlist);
				}
			}
		}
	}
	$cur_item = str_replace('{GSSE_INCL_WISHLIST}',$itemwishlist,$cur_item);
	
	$all_items .= $cur_item;
	$p++;
}

?>