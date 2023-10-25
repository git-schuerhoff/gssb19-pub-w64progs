<?php
$max = mysqli_num_rows($erg);
$columns = 2;


$outer = $this->gs_file_get_contents('template/itemslist_outer_layout.html');
$line = $this->gs_file_get_contents('template/itemslist_line.html');
$box = $this->gs_file_get_contents('template/itemslistbox.html');
$this_inner = '';
$this_line = $line;
$this_box = '';
$a = 1;
while($z = mysqli_fetch_assoc($erg))
{
	//A TS 17.07.2014
	$aImgs = $this->get_itempics($z['itemItemId']);
	$lHasAttributes = 0;
	if(strlen(trim($z['itemAttribute1']," ")) > 0 || strlen(trim($z['itemAttribute2']," ")) > 0 || strlen(trim($z['itemAttribute3']," ")) > 0)
	{
		$lHasAttributes = 1;
	}
	/*begin get Prices*/
	$aPrices = $this->get_prices($z['itemItemId']);
	$action = 0;
	
	if($z['itemIsAction'] == 'Y')
	{
		$action = $this->chk_action($z['itemItemId'],$aPrices);
	}
	/*end get prices*/
	$detailurl = 'index.php?page=detail&amp;item=' . $z['itemItemId'] . '&amp;d=' . $z['itemItemPage'];
	$this_box = $box;
	$actionhtml = '';
	$actionhtmlnew = '';
	$saleperiod = '';//New Template
	if($action == 1)
	{
		$actionhtml = $this->gs_file_get_contents('template/rabattaktion.html');
		$actionhtml = str_replace('{GSSE_INCL_CURLANG}',$this->lngID,$actionhtml);
		$actionhtml = str_replace('{GSSE_INCL_ACTIONTEXT}','',$actionhtml);
		/*New Templates*/
		$actionhtmlnew = $this->gs_file_get_contents('template/labelsale.html');
		$actionhtmlnew = str_replace('{GSSE_LANG_LangTagLabelSale}',$this->get_lngtext('LangTagLabelSale'),$actionhtmlnew);
		if($aPrices['actshowperiod'] == 'Y')
		{
			$saleperiod = $aPrices['actbegindate'] . " - " . $aPrices['actenddate'];
		}
	}
	$this_box = str_replace('{GSSE_INCL_ACTION}',$actionhtml,$this_box);
	/*New Templates*/
	$this_box = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$this_box);
	$this_box = str_replace('{GSSE_INCL_LABELSALE}',$actionhtmlnew,$this_box);
	
	$new = ($z['itemIsNewItem'] == 'Y') ? $this->inc_image('', 'template/images/neu_small.gif', 'New', 'New') : '';
	$this_box = str_replace('{GSSE_INCL_NEWITEMIMG}',$new,$this_box);
	
	/*New Templates*/
	if($z['itemIsNewItem'] == 'Y')
	{
		if(file_exists('template/labelnew.html'))
		{
			$new = $this->gs_file_get_contents('template/labelnew.html');
		}
		$new = str_replace('{GSSE_LANG_LangTagLabelNew}',$this->get_lngtext('LangTagLabelNew'),$new);
	}
	else
	{
		$new = '';
	}
	$this_box = str_replace('{GSSE_INCL_LABELNEW}',$new,$this_box);
	
	$this_box = str_replace('{GSSE_INCL_ITEMDETAILTEXT1}',$z['itemDetailText1'],$this_box);
	$this_box = str_replace('{GSSE_INCL_AVAILBOX}',$this->get_availability($z['itemInStockQuantity'],$z['itemAvailabilityId'],0),$this_box);
	$itemname = ($z['itemHasDetail'] == 'Y') ? $this->inc_link('item_title', $detailurl, '_self', $z['itemItemDescription']) : $z['itemItemDescription'];
	$this_box = str_replace('{GSSE_INCL_ITEMNAME}',$itemname,$this_box);
	//New Template ItemNumber
	$this_box = str_replace('{GSSE_INCL_ITEMNUMBER}',$z['itemItemNumber'],$this_box);
	$minimumage = '';
	if($z['itemCheckAge'] == 'Y' && $z['itemMustAge'] > 0)
	{
		$minimumage = $this->get_lngtext('LangTagMinimumAge') . ": " . $z['itemMustAge'] . " " . $this->get_lngtext('LangTagYears');
	}
	$this_box = str_replace('{GSSE_INCL_MINIMUMAGE}',$minimumage,$this_box);
	//Itemimage
	//A TS 06.10.2014: Wenn im Filename des Bildes http vorkommt, dann diese URL verwenden,
	//ansonsten den relativen Pfad zum Bild
	if(strpos($aImgs[0]['ImageName'], 'http') === false)
	{
		$imgname = 'images/medium/' . $aImgs[0]['ImageName'];
	}
	else
	{
		$imgname = $aImgs[0]['ImageName'];
	}
	if($z['itemHasDetail'] == 'Y')
	{
		$itemimg = $this->inc_imglink('', $detailurl, '_self', 'item_img', $imgname, $z['itemItemDescription'], $z['itemItemDescription']);
	}
	else
	{
		$itemimg = $this->inc_image('item_img', $imgname, $z['itemItemDescription'], $z['itemItemDescription']);
	}
	$this_box = str_replace('{GSSE_INCL_IMGLNK}',$itemimg,$this_box);
	
	//New Template link to detail
	if($z['itemHasDetail'] == 'Y')
	{
		$this_box = str_replace('{GSSE_INCL_ITEMURL}',$detailurl,$this_box);
	}
	
	//Old price
	$oldprice = '';
	if($aPrices['oldprice'] > 0 && $action == 0)
	{
		$pcontent = $this->get_lngtext('LangTagOldPrice') . ': ' . $this->get_currency($aPrices['oldprice'],0,'.');
		$oldprice = $this->inc_pcontent('oldprice',$pcontent);
	}
	
	if($action == 1 && $aPrices['actshownormal'] == 'Y' && $aPrices['actnormprice'] != 0)
	{
		$pcontent = $this->get_lngtext('LangTagOldPrice') . ': ' . $this->get_currency(str_replace(',','.',$aPrices['actnormprice']),0,'.');
		$oldprice = $this->inc_pcontent('oldprice',$pcontent);
	}
	
	$this_box = str_replace('{GSSE_INCL_ITEMOLDPRICE}',$oldprice,$this_box);
	//New Template*/
	$oldprice = '';
	$priceclass = 'price';
	if($aPrices['oldprice'] > 0 && $action == 0)
	{
		$priceclass = 'special-price';
		if(file_exists('template/oldpricenew.html'))
		{
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
		}
		$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($aPrices['oldprice'],0,'.'),$oldprice);
	}
	if($action == 1 && $aPrices['actshownormal'] == 'Y' && $aPrices['actnormprice'] != 0)
	{
		$priceclass = 'special-price';
		if(file_exists('template/oldpricenew.html'))
		{
			$oldprice = $this->gs_file_get_contents('template/oldpricenew.html');
		}
		$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency(str_replace(',','.',$aPrices['actnormprice']),0,'.'),$oldprice);
	}
	$this_box = str_replace('{GSSE_INCL_OLDPRICENEW}',$oldprice,$this_box);
	//Referenceprice
	if($aPrices['referenceprice'] > 0)
	{
		$pcontent = $this->get_lngtext('LangTagReferencePrice') . ': ' . $aPrices['referencequantity'] . ' ' . $aPrices['referenceunit'] . ' = ' . $this->get_currency($aPrices['referenceprice'],0,'.');
		$this_box = str_replace('{GSSE_INCL_ITEMREFPRICE}',$this->inc_pcontent('referenceprice',$pcontent),$this_box);
	}
	else
	{
		$this_box = str_replace('{GSSE_INCL_ITEMREFPRICE}','',$this_box);
	}
		
	//Itemprice
	$price = $aPrices['price'];
	$this_box = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($price,0,'.'),$this_box);
	//New template
	$this_box = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$this_box);
	
	//Add to cart button
	//Count variants
	$aVariants = $this->get_variants($z['itemItemNumber']);
	$varcount = count($aVariants);
	$tr = false;
	if($lHasAttributes == 1 || $z['itemIsVariant'] == 'Y' || $z['itemIsTextInput'] == 'Y') $tr = true;
	if($varcount == 0 && $tr === false)
	{
		if($z['itemIsCatalogFlg'] == 'N' || $z['itemHasDetail'] == 'N')
		{
			//$output = $this->inc_input('art_count','text','','','1',2,0,false);
			//$output .= $this->inc_input('buttonimage ordersmall','button','','','',0,0,false);
			$output = $this->gs_file_get_contents('template/addtocart.html');
			$output = str_replace('{GSSE_INCL_ITEMINDEX}',$z['itemItemId'],$output);
			$output = str_replace('{GSSE_INCL_COUNTER}',$a,$output);
			$output = str_replace('{GSSE_INCL_ISDECIMAL}',$z['itemisDecimal'],$output);
		}
	}
	else
	{
		//$output = $this->inc_link('aorder_link', $detailurl, '_self', $this->get_setting('edOrderButton_Text'));
		$output = $this->gs_file_get_contents('template/newbutton.html');
		$action = "self.location.href = '" . $detailurl . "';";
		$output = str_replace('{GSSE_INCL_BUTTITLE}', $this->get_setting('edOrderButton_Text'),$output);
		$output = str_replace('{GSSE_INCL_BUTACTION}',$action,$output);
	}
	
	$this_box = str_replace('{GSSE_INCL_ADDTOCARTBUTTON}', $output,$this_box);
	
	//Text priceinformation
	$pinfo = $this->get_setting('edPriceInformation_Text');
	$this_box = str_replace('{GSSE_INCL_ITEMPRICEINFO}',$pinfo,$this_box);
	$this_box = str_replace('{GSSE_INCL_DELETEBUTTON}',$delbutton,$this_box);
	
	$this_box = str_replace('{GSSE_INCL_RATINGIMG}',$ratingimg,$this_box);
	$this_box = str_replace('{GSSE_INCL_RATINGSUBJ}',$ratingsubj,$this_box);
	$this_box = str_replace('{GSSE_INCL_RATINGBODY}',$ratingbody,$this_box);
	$this_box = str_replace('{GSSE_INCL_RATINGDATE}',$ratingdate,$this_box);
	
	//Item comparison
	$itemcompare = '';
	if($this->get_setting('cbArticleCompare_Checked') == 'True')
	{
		$itemcompare = $this->gs_file_get_contents('template/item_comparison.html');
		$itemcompare = str_replace('{GSSE_LANG_LangTagArticleCompare}',$this->get_lngtext('LangTagArticleCompare'),$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICID}',$z['itemItemId'],$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICNAME}',$z['itemItemDescription'],$itemcompare);
		$itemcompare = str_replace('{GSSE_INCL_ICCHECKED}',($this->is_marked_for_comparison($z['itemItemId'])) ? 'checked="checked"' : '',$itemcompare);
	}
	$this_box = str_replace('{GSSE_INCL_COMPAREITEM}',$itemcompare,$this_box);
	
	//New Template
	//Images and link
	$itemimgnew = 'images/medium/' . $aImgs[0]['ImageName'];
	$this_box = str_replace('{GSSE_INCL_ITEMIMG}',$itemimgnew,$this_box);
	//2nd image
	if(count($aImgs) > 1)
	{
		$itemimgnew2 = 'images/medium/' . $aImgs[1]['ImageName'];
	}
	else
	{
		$itemimgnew2 = $itemimgnew;
	}
	$this_box = str_replace('{GSSE_INCL_ITEMIMG2}',$itemimgnew2,$this_box);
	
	if($z['itemHasDetail'] == 'Y')
	{
		$this_box = str_replace('{GSSE_INCL_ITEMURL}',$detailurl,$this_box);
	}
	else
	{
		$this_box = str_replace('{GSSE_INCL_ITEMURL}','',$this_box);
	}
	//New Temnplate
	//Place itemname only without link
	$this_box = str_replace('{GSSE_INCL_ITEMNAMEONLY}',$z['itemItemDescription'],$this_box);
	
	//Add boxes
	if(($a % 2) != 0)
	{
		$this_line = str_replace('{GSSE_INCL_ITEMSLISTBOX1}',$this_box,$this_line);
	}
	else
	{
		$this_line = str_replace('{GSSE_INCL_ITEMSLISTBOX2}',$this_box,$this_line);
	}
	if(($a % $columns) == 0 || $a == $max)
	{
		
		$this_inner .= $this_line;
		$this_line = $line;
	}
	$a++;
}
?>