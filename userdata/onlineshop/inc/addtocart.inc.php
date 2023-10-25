<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$addtocart = '';
$billingfrequencyclass = 'no-display';
$allbillingfreqs = '';
$billingfrequnit = '';
if($_SESSION['aitem']['itemIsCatalogFlg'] == 'N')
{
	$addtocart = $this->gs_file_get_contents($this->absurl . 'template/addtocart.html');
	$addtocart = $this->parse_texts($this->get_tags_ret($addtocart),$addtocart);
	//TS: Bei Miet-Preisen, die Änderungsmöglichkeiten der Anzahl nicht anzeigen
	$aPrices = $this->get_prices($_SESSION['aitem']['itemItemId']);
	$quantity = 'gs-float-left';
	if(isset($aPrices['isrental'])) {
		if($aPrices['isrental'] != "N") {
			$quantity = "no-display";
			$billingfrequencyclass = 'gs-float-left';
			$billingfrequnit = $this->get_billingperiodfromid($aPrices['billingperiod'],false,false,true);
			$aFreqs = explode(',',$aPrices['billingfrequency']);
			$iMax = count($aFreqs);
			if($iMax > 0) {
				for($i = 0; $i < $iMax; $i++) {
					$allbillingfreqs .= '<option value="' . $aFreqs[$i] . '">' . $aFreqs[$i] . ' ' . $billingfrequnit . '</option>\n';
				}
			} else {
				$allbillingfreqs = '<option value="1">1</option>';
			}
			
		}
	}
	$addtocart = str_replace('{GSSE_INCL_OBJ}',$_SESSION['aitem']['itemItemId'].'-qty',$addtocart);
	$addtocart = str_replace('{GSSE_INCL_ERRBOX}',$_SESSION['aitem']['itemItemId'].'-errbox',$addtocart);
	$addtocart = str_replace('{GSSE_INCL_ADDQUANTITY}',$quantity,$addtocart);
	$addtocart = str_replace('{GSSE_INCL_ITEMID}',$_SESSION['aitem']['itemItemId'],$addtocart);
}

$addtocart = str_replace('{GSSE_INCL_BILLINGFREQUENCYCLASS}',$billingfrequencyclass,$addtocart);
$addtocart = str_replace('{GSSE_INCL_BILLINGFREQUENCYOPTIONS}',$allbillingfreqs,$addtocart);

//PayPal-Expresscheckout temporary disabled
$ppexpresscheckout = $this->gs_file_get_contents($this->absurl . 'template/pp_express_checkout.html');
$addtocart = str_replace('{GSSE_INCL_PPEXPRESSCHECKOUT}','',$addtocart);

$this->content = str_replace($tag, $addtocart, $this->content);
?>
