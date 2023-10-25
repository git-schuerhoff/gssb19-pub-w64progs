<?php

$outer = $this->gs_file_get_contents('template/itemsvertical_outer_layout.html');
$line = $this->gs_file_get_contents('template/itemsvertical_line.html');
$box = $this->gs_file_get_contents('template/itemsverticalbox.html');
$this_inner = '';
$this_line = $line;
$this_box = '';
$a = 1;
while($z = mysqli_fetch_assoc($erg))
{
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
	if($action == 1)
	{
		$actionhtml = $this->gs_file_get_contents('template/rabattaktion.html');
		$actionhtml = str_replace('{GSSE_INCL_CURLANG}',$this->lngID,$actionhtml);
		$actionhtml = str_replace('{GSSE_INCL_ACTIONTEXT}','',$actionhtml);
	}
	$this_box = str_replace('{GSSE_INCL_ACTION}',$actionhtml,$this_box);
	$new = ($z['itemIsNewItem'] == 'Y') ? $this->inc_image('', 'template/images/neu_small.gif', 'New', 'New') : '';
	$this_box = str_replace('{GSSE_INCL_NEWITEMIMG}',$new,$this_box);
	$this_box = str_replace('{GSSE_INCL_ITEMDETAILTEXT1}',$z['itemDetailText1'],$this_box);
	$this_box = str_replace('{GSSE_INCL_AVAILBOX}',$this->get_availability($z['itemInStockQuantity'],$z['itemAvailabilityId'],0),$this_box);
	$itemname = ($z['itemHasDetail'] == 'Y') ? $this->inc_link('item_title', $detailurl, '_self', $z['itemItemDescription']) : $z['itemItemDescription'];
	$this_box = str_replace('{GSSE_INCL_ITEMNAME}',$itemname,$this_box);
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
		$itemcompare = str_replace('{GSSE_INCL_ICCHECKED}',($this->is_marked_for_comparison($z['itemItemId'])) ? 'checked="checked"' : '',$itemcompare);
	}
	$this_box = str_replace('{GSSE_INCL_COMPAREITEM}',$itemcompare,$this_box);
	
	//Add boxes
	$this_line = str_replace('{GSSE_INCL_ITEMSVERTICALBOX}',$this_box,$this_line);
	$this_inner .= $this_line;
	$this_line = $line;
	
	$a++;
}
?>