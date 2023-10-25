<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$pricebox = '';
//if($_SESSION['aitem']['itemIsTextHasNoPrice'] == 'N')
//{
	$pricebox = file_get_contents($this->absurl . 'template/pricebox.html');
	$itemID = $_SESSION['aitem']['itemItemId'];
	$aPrices = $this->get_prices($itemID);
	
	/*Prices*/
	$action = 'N';
	if($_SESSION['aitem']['itemIsAction'] == 'Y')
	{
		$action = $this->chk_action($itemID,$aPrices);
	}
	
	$refclass = 'display: none;';
	$refqty = '';
	$refunit = '';
	$refprice = '';
	$saleperiod = '';
	
	$itemPrice = $this->get_currency($aPrices['price'],0,'.');
	
	$oldprice = '';
	$oldpriceclass = 'no-old-price';
	$priceclass = 'regular-price';
	if($aPrices['oldprice'] > 0 && $action == 'N')
	{
		$priceclass = 'special-price';
		$oldpriceclass = 'old-price-l';
		$oldprice = file_get_contents($this->absurl . 'template/oldpricenew.html');
		$oldprice = str_replace('{GSSE_INCL_OLDPRICECLASS}',$oldpriceclass,$oldprice);
		$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($aPrices['oldprice'],0,'.'),$oldprice);
	}
	
	if($action == 'Y')
	{
		$itemPrice = $this->get_currency(str_replace(',','.',$aPrices['actprice']),0,'.');
		if($aPrices['actshowperiod'] == 'Y')
		{
			$saleperiod = $this->conv_date($aPrices['actbegindate'],'D') . " - " . $this->conv_date($aPrices['actenddate'],'D');
		}
	}
	
	if($action == 'Y' && $aPrices['actshownormal'] == 'Y')
	{
		if($aPrices['actnormprice'] != '' && $aPrices['actnormprice'] != 0)
		{
			$oldprice_val = str_replace(',','.',$aPrices['actnormprice']);
		}
		else
		{
			$oldprice_val = $aPrices['price'];
		}
		$oldpriceclass = 'old-price-l';
		$priceclass = 'special-price';
		$oldprice = file_get_contents($this->absurl . 'template/oldpricenew.html');
		$oldprice = str_replace('{GSSE_INCL_OLDPRICECLASS}',$oldpriceclass,$oldprice);
		$oldprice = str_replace('{GSSE_INCL_ITEMOLDPRICENEW}',$this->get_currency($oldprice_val,0,'.'),$oldprice);
	}
	
	$pricefrom = '';
	
	/*
	if(count($aPrices['abulk']) > 0)
	{
		$itemPrice = $this->get_currency($aPrices['abulk'][0][1],0,'.');
		$pricefrom = $this->get_lngtext('LangTagFromNew') . '&nbsp;';
	}
	*/
	$trialperiod = '&nbsp;';
    $trialperiodclass = 'display: none;';
	$aftertrial = '&nbsp;';
	$billingperiod = '&nbsp;';
    $billingperiodclass = 'display: none;';
	$aftertrialprice = '&nbsp;';
	$aftertrialperiod = '&nbsp;';
    $aftertrialperiodclass = 'display: none;';
	$runtime = '&nbsp;';
	$runtimelng = '&nbsp;';
	$initpricetxt = '';
	$initprice = '';
	$rentalstyle = 'display: none;';
	//A TS 09.12.2015: rental price
	if(isset($aPrices['isrental'])) {
		if($aPrices['isrental'] == 'Y') {
			$rentalstyle = '';
            $trialperiodclass = 'display: block;';
            $aftertrialperiodclass = 'display: block;';
			if($aPrices['istrial'] == 'Y') {
				if($aPrices['trialfrequency'] > 1) {
					$lPlural = true;
				} else {
					$lPlural = false;
				}
				
				$aftertrialprice = $itemPrice;
				if($aPrices['trialprice'] > 0) {
					$trialperiod = $aPrices['trialfrequency'] . " " . $this->get_billingperiodfromid($aPrices['trialperiod'],false,$lPlural,false) . " " . $this->get_lngtext('LangTagForSomething');
					$itemPrice = $this->get_currency($aPrices['trialprice'],0,'.');
					$billingperiod = $this->get_billingperiodfromid($aPrices['trialperiod'],true,false,true);
                    $billingperiodclass = '';
				} else {
					$trialperiod = $aPrices['trialfrequency'] . " " . $this->get_billingperiodfromid($aPrices['trialperiod'],false,$lPlural,false);
					$itemPrice = $this->get_lngtext('LangTagForFree');
					$billingperiod = '&nbsp;';
				}
				
				$aftertrial = $this->get_lngtext('LangTagAfterSomething');
				$aftertrialperiod = $this->get_billingperiodfromid($aPrices['billingperiod'],true,false,true);
                $aftertrialperiodclass = 'display: block;';
			} else {
				$billingperiod = $this->get_billingperiodfromid($aPrices['billingperiod'],true,false,true);
                $billingperiodclass = '';
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
			if($aPrices['initialprice'] > 0) {
				$initprice = $this->get_currency($aPrices['initialprice'],0,'.');
				$initpricetxt = $this->get_lngtext('LangTagInitialPrice');
			}
		}
	}
	$pricebox = str_replace('{GSSE_INCL_RENTALSTYLE}',$rentalstyle,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_RUNTIMELNG}',$runtimelng,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_RUNTIME}',$runtime,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_AFTERTRIALPRICE}',$aftertrialprice,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_AFTERTRIALPERIOD}',$aftertrialperiod,$pricebox);
    $pricebox = str_replace('{GSSE_INCL_AFTERTRIALPERIODCLASS}',$aftertrialperiodclass,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_TRIALPERIOD}',$trialperiod,$pricebox);
    $pricebox = str_replace('{GSSE_INCL_TRIALPERIODCLASS}',$trialperiodclass,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_AFTERTRIAL}',$aftertrial,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_BIILINGPERIOD}',$billingperiod,$pricebox);
    $pricebox = str_replace('{GSSE_INCL_BILLINGPERIODCLASS}',$billingperiodclass,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_INITPRICETXT}',$initpricetxt,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_INITPRICE}',$initprice,$pricebox);
	//E TS 09.12.2015: rental price
	
	$pricebox = str_replace('{GSSE_INCL_SALEPERIOD}',$saleperiod,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_OLDPRICENEW}',$oldprice,$pricebox);
	//Itemprice
	$pricebox = str_replace('{GSSE_INCL_PRICEFROM}',$pricefrom,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_ITEMPRICE}',$itemPrice,$pricebox);
	//New template
	$pricebox = str_replace('{GSSE_INCL_PRICECLASS}',$priceclass,$pricebox);
	
	/*Begin Exalyser specific*/
	$pricebox = str_replace('{GSSE_LANG_LangTagExaPricePerMonth}',$this->get_lngtext('LangTagExaPricePerMonth'),$pricebox);
	/*Begin Exalyser specific*/
	
	/*Reference price*/
	/*TS 03.09.2015: Auch referencequantity muss ungleich 0 sein*/
	if($aPrices['referenceprice'] != 0 && $aPrices['referencequantity'] != 0)
	{
		$refclass = 'display: block;';
		$refqty = $aPrices['referencequantity'];
		$refunit = $aPrices['referenceunit'];
		$refprice = $this->get_currency($aPrices['referenceprice'],0,'.');
	}
	
	$pricebox = str_replace('{GSSE_INCL_REFPRICECLASS}',$refclass,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_REFCOUNT}',$refqty,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_REFUNIT}',$refunit,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_REFPRICE}',$refprice,$pricebox);
	
	$bulk = '';
	$bulkclass = 'display: none;';
	if($action == 'N')
	{
		if(count($aPrices['abulk']) > 0)
		{
			$pricefrom = $this->get_lngtext('LangTagFromNew') . '&nbsp;';
			$bulkclass = 'display: block;';
			$bulk = file_get_contents($this->absurl . 'template/bulkprices_outer.html');
			$bulk_item = file_get_contents($this->absurl . 'template/bulkprices_item.html');
			$bulk_item = str_replace('{GSSE_INCLPRICEFROM}',$pricefrom,$bulk_item);
			$all_items = '';
			$bu_max3 = count($aPrices['abulk']);
			for($p = 0; $p < $bu_max3; $p++)
			{
				$cur_item = $bulk_item;
				$cur_item = str_replace('{GSSE_INCL_BULKQTY}',$aPrices['abulk'][$p][0],$cur_item);
				$cur_item = str_replace('{GSSE_INCL_BULKPRICE}',$this->get_currency($aPrices['abulk'][$p][1],0,'.'),$cur_item);
				$all_items .= $cur_item;
			}
			$bulk = str_replace('{GSSE_INCL_BULKITEMS}',$all_items,$bulk);
		}
	}
	$pricebox = str_replace('{GSSE_INCL_BULKCLASS}',$bulkclass,$pricebox);
	$pricebox = str_replace('{GSSE_INCL_BULKPRICES}',$bulk,$pricebox);
//}

$this->content = str_replace($tag, $pricebox, $this->content);
?>
