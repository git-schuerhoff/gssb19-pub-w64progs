<?php
$max = mysqli_num_rows($erg);
$columns = $this->get_setting('edSetNumberColTable_Text');
if($columns < 1)
{
	$columns = 1;
}

/*
if($columns > 4)
{
	$columns = 4;
}
*/

if($max < $columns)
{
	$columns = $max;
}

$outer = $this->gs_file_get_contents('template/itemsboxed_outer_layout.html');
$line = $this->gs_file_get_contents('template/itemsboxed_line.html');
/*$box = $this->gs_file_get_contents('template/itemsbox_' . $columns . '.html');*/
$box = $this->gs_file_get_contents('template/itemsbox.html');

//A TS 17.06.2014: Zellenbreite nach der Anzahl der Spalten ermitteln
$cellwidth = 100 / $columns;
$box = str_replace('{GSSE_INCL_CELLWIDTH}', $cellwidth, $box);
//E TS

$this_inner = '';
$this_line = '';
$this_box = '';
$this_boxes = '';
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
	//print_r($aPrices);
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
		/*Old Templates*/
		$actionhtml = $this->gs_file_get_contents('template/rabattaktion.html');
		$actionhtml = str_replace('{GSSE_INCL_CURLANG}',$this->lngID,$actionhtml);
		$actionhtml = str_replace('{GSSE_INCL_ACTIONTEXT}','',$actionhtml);
		/*New Templates*/
		if(file_exists('template/labelsale.html'))
		{
			$actionhtmlnew = $this->gs_file_get_contents('template/labelsale.html');
			$actionhtmlnew = str_replace('{GSSE_LANG_LangTagLabelSale}',$this->get_lngtext('LangTagLabelSale'),$actionhtmlnew);
		}
		if($aPrices['actshowperiod'] == 'Y')
		{
			$saleperiod = $aPrices['actbegindate'] . " - " . $aPrices['actenddate'];
		}
		
	}
	/*Old Templates*/
	$this_box = str_replace('{GSSE_INCL_ACTION}',$actionhtml,$this_box);
	/*New Templates*/
	$this_box = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$this_box);
	$this_box = str_replace('{GSSE_INCL_LABELSALE}',$actionhtmlnew,$this_box);
	/*Old Templates*/
	$new = ($z['itemIsNewItem'] == 'Y') ? $this->inc_image('', 'template/images/neu.gif', 'New', 'New') : '';
	$this_box = str_replace('{GSSE_INCL_NEWITEMIMG}',$new,$this_box);
	/*New Templates*/
	if($z['itemIsNewItem'] == 'Y')
	{
		if(file_exists('template/labelnew.html'))
		{
			$new = $this->gs_file_get_contents('template/labelnew.html');
			$new = str_replace('{GSSE_LANG_LangTagLabelNew}',$this->get_lngtext('LangTagLabelNew'),$new);
		}
	}
	else
	{
		$new = '';
	}
	$this_box = str_replace('{GSSE_INCL_LABELNEW}',$new,$this_box);
	
	$this_box = str_replace('{GSSE_INCL_AVAILBOX}',$this->get_availability($z['itemInStockQuantity'],$z['itemAvailabilityId'],0),$this_box);
	$itemname = ($z['itemHasDetail'] == 'Y') ? $this->inc_link('item_title', $detailurl, '_self', $z['itemItemDescription']) : $z['itemItemDescription'];
	$this_box = str_replace('{GSSE_INCL_ITEMNAME}',$itemname,$this_box);
	//New Template ItemNumber
	$this_box = str_replace('{GSSE_INCL_ITEMNUMBER}',$z['itemItemNumber'],$this_box);
	//Itemimage
	//A TS 06.10.2014: Wenn im Filename des Bildes http vorkommt, dann diese URL verwenden,
	//ansonsten den relativen Pfad zum Bild
	if(strpos($aImgs[0]['ImageName'], 'http') === false)
	{
		if(file_exists('images/small/'.$aImgs[0]['ImageName']))
		{
			$imgname = 'images/small/' . $aImgs[0]['ImageName'];
		}
		else
		{
			$imgname = 'images/medium/' . $aImgs[0]['ImageName'];
		}
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
	
	// Item Text nur für Basic und Standard
	if($z['itemHasDetail'] == 'Y')
	{
		$this_box = str_replace('{GSSE_INCL_ITEMTEXT}','',$this_box);
	}
	else
	{
		$this_box = str_replace('{GSSE_INCL_ITEMTEXT}',$z['itemItemText'],$this_box);
	}
	//oldprice
	//Old Template*/
	$oldprice = '&nbsp;';
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
	//Bulkprices
	if(count($aPrices['abulk']) > 0 && $action == 0)
	{
		$bulkprices = '';
		$bumax2 = count($aPrices['abulk']);
		for($s = 0; $s < $bumax2; $s++)
		{
			$pcontent = $this->get_lngtext('LangTagFromNew') . ' ' .
							$aPrices['abulk'][$s][0] . ' ' .
							$this->get_lngtext('LangTagUnits') . ' ' .
							$this->get_currency($aPrices['abulk'][$s][1],0,'.');
			$bulkprices .= $this->inc_pcontent('',$pcontent);
		}
		$this_box = str_replace('{GSSE_INCL_ITEMBULKPRICE}',$bulkprices,$this_box);
	}
	else
	{
		$this_box = str_replace('{GSSE_INCL_ITEMBULKPRICE}','',$this_box);
	}
	
	//Itemprice
	$this_box = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($aPrices['price'],0,'.'),$this_box);
	//New template
	$this_box = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$this_box);
	//Add to cart button
	//Count variants
	$aVariants = $this->get_variants($z['itemItemNumber']);
	$varcount = count($aVariants);
	$tr = false;
	if($lHasAttributes == 1 || $z['itemIsVariant'] == 'Y' || $z['itemIsTextInput'] == 'Y') $tr = true;
	if($varcount == 0 && $tr === false && (count($aPrices['abulk']) == 0))
	{
		if($z['itemIsCatalogFlg'] == 'N' || $z['itemHasDetail'] == 'N')
		{
			//$output = $this->inc_input('art_count','text','','','1',2,0,false);
			//$output .= $this->inc_input('buttonimage ordersmall','button','','','',0,0,false);
			$output = $this->gs_file_get_contents('template/addtocart.html');
			$output = str_replace('{GSSE_INCL_ITEMINDEX}',$z['itemItemId'],$output);
			$output = str_replace('{GSSE_INCL_COUNTER}',$a,$output);
			$output = str_replace('{GSSE_INCL_ISDECIMAL}',$z['itemisDecimal'],$output);
			/*
			$output2 = $this->gs_file_get_contents('template/newbutton.html');
			$action = "self.location.href = '" . $detailurl . "';";
			$output2 = str_replace('{GSSE_INCL_BUTTITLE}', $this->get_setting('edOrderButton_Text'),$output2);
			$output2 = str_replace('{GSSE_INCL_BUTACTION}',$action,$output2);
			$output .= $output2;
			*/
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
	
	//Itemdescription, only in compare mode
	$itemdescription = '';
	if(isset($compare_items))
	{
		$itemdescription = $z['itemItemText'];
	}
	$this_box = str_replace('{GSSE_INCL_ITEMDESCRIPTION}',$itemdescription,$this_box);
	
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
	$this_box = str_replace('{GSSE_INCL_ITEMIMG}',$itemimgnew,$this_box);
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
	
	
	//Add box
	$this_boxes .= $this_box;
	if(($a % $columns) == 0 || $a == $max)
	{
		$this_line = str_replace('{GSSE_INCL_ITEMSBOXED}',$this_boxes,$line);
		$this_inner .= $this_line;
		$this_boxes = '';
		$this_line = '';
	}
	$a++;
}
?>