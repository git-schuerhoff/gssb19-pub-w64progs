<?php
if(!isset($_SESSION['order'])){
	session_start();
}
//session_start();
$optionhtml = $this->gs_file_get_contents('template/radio.html');
$deldbh = $this->db_connect();
$paymentitems = '';
$checked = '';
$download = false;
$rentals = false;
$buypayment = '';
$order = unserialize($_SESSION['order']);
$basket = $order->getBasket();
$payment = $order->getPayment();
if(isset($order->Customer['stateISO'])){
	$stateISO= $order->Customer['stateISO'];
} else {
	$stateISO = "DE";
}	
$delsql = "SELECT c.Charge, c.ChargePercent, c.UseCashDiscount, c.CashDiscount, c.CashDiscountPercent, l.Text1, n.InternalName " .
           " FROM " . $this->dbtoken . "paymentcountry c " .
           " left join " . $this->dbtoken . "paymentlanguage l on c.SortId = l.SortId " .
           "left join " . $this->dbtoken . "paymentinternalnames n on c.SortId = n.SortId " .
           " WHERE  c.AddressArea in (SELECT addressareaid from " . $this->dbtoken . "countriesareas where countryid='".$stateISO."') AND c.CountryId = 'deu' and l.Text1 <> ''";
           
$delerg = mysqli_query($deldbh,$delsql);
$areasql = "SELECT addressareaid from " . $this->dbtoken . "countriesareas where countryid='".$stateISO."'";
$areaerg = mysqli_query($deldbh,$areasql);
$areaID = mysqli_fetch_assoc($areaerg);
$areaID = $areaID['addressareaid'];
$order->setAreaID($areaID);
$_SESSION['AreaID']=$areaID;
if(mysqli_errno($deldbh) == 0) {
	if(mysqli_num_rows($delerg) > 0) {
		$dl = mysqli_fetch_assoc($delerg);
		$charge = $dl['Charge'];
		$chargepercent = $dl['ChargePercent'];
		$usecashdiscount = $dl['UseCashDiscount'];
		$cashdiscount = $dl['CashDiscount'];
		$cashdiscountpercent = $dl['CashDiscountPercent'];
		$pinternalname = $dl['InternalName'];
	}
	//mysqli_free_result($delerg);
}
//mysqli_free_result($areaerg);
//mysqli_close($deldbh);
//auf Mietpreise checken

// Wenn Downloadartikel im Warenkorb sind -> Rechnung, Nachnahme und Vorkasse ausblenden
$bmax = count($basket);
if($bmax > 0) {
	foreach($basket as $basketitem) {
		if($basketitem['art_price']>0){
			if($basketitem['art_isdownload'] == 'Y') {
				$download = true;
			}
			if($basketitem['art_prices']['isrental']== 'Y') {
				$rentals = true;
			}
		}
	}
}

$aPaym = $this->get_payment($areaID, $download, $rentals);
$pmmax2 = count($aPaym);
if($pmmax2 > 0)
{
    //Klassische Zahlmethoden
    $buypayment = $this->gs_file_get_contents('template/payment.html');
    $buypayment = str_replace('{GSSE_CLASS_PAYMENT}','list-paymenttypes list-unstyled',$buypayment);
	if($rentals){
		$buypayment = str_replace('{GSSE_MSG_DIRECTDEBITONLY}',$this->get_lngtext('LangTagDirectDebitOnly'),$buypayment);
	} else {
		$buypayment = str_replace('{GSSE_MSG_DIRECTDEBITONLY}','',$buypayment);
	}
    // <i class="{GSSE_OPT_ICONCLASS}" title="{GSSE_OPT_ICONTITLE}"></i>
    $onclick = 0;
    for($p = 0; $p < $pmmax2; $p++)
    {
        if($p == 0){
            $checked=" checked='checked'";
        } else {
            $checked = "";
        }
        $cur_opt = $optionhtml;
        
        switch($aPaym[$p]['internalname'])
        {
            case "PaymentPayPal":
                $iconclass = "sprite sprite-paypal-color-big margr10 pull-right";
                $icontitle = $aPaym[$p]['name'];
                break;
            case "PaymentCreditCard":
                $iconclass = "sprite sprite-cc-color-big margr10 pull-right";
                $icontitle = $aPaym[$p]['name'];
                break;
            default:
                $iconclass = "";
                $icontitle = "";
                break;    
        }
        if (isset($_GET['failcode']))
        {
        	if ($payment['paymInternalName'] <> $_GET['internalPaymentName'])
            {
                $cur_opt = str_replace('{GSSE_OPT_VALUE}',$aPaym[$p]['sortid'].'|'.$aPaym[$p]['name'].'|'.$aPaym[$p]['internalname'],$cur_opt);
                //{GSSE_OPT_CHECKED}
                $cur_opt = str_replace('{GSSE_OPT_CHECKED}',$checked,$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(paymentfields,'.$onclick.')"',$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_TEXT}',$aPaym[$p]['name'],$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_ICONCLASS}',$iconclass,$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_ICONTITLE}',$icontitle,$cur_opt);
                $paymentitems .= $cur_opt;
				$onclick = $onclick+1;
            }
        }
        else
        {
        	if($order->guest){
        		if($aPaym[$p]['internalname']<>'PaymentCreditCard' && $aPaym[$p]['internalname']<>'PaymentDirectDebit'){       	
		            $cur_opt = str_replace('{GSSE_OPT_VALUE}',$aPaym[$p]['internalname'].'|'.$aPaym[$p]['name'].'|'.$aPaym[$p]['sortid'],$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_CHECKED}',$checked,$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(paymentfields,'.$onclick.')"',$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_TEXT}',$aPaym[$p]['name'],$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_ICONCLASS}',$iconclass,$cur_opt);
		            $cur_opt = str_replace('{GSSE_OPT_ICONTITLE}',$icontitle,$cur_opt);
		            $paymentitems .= $cur_opt;
					$onclick = $onclick+1;
        		}
        		
        	} else {
        		$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aPaym[$p]['internalname'].'|'.$aPaym[$p]['name'].'|'.$aPaym[$p]['sortid'],$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_CHECKED}',$checked,$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_ONCLICK}','onclick="radioToggle(paymentfields,'.$onclick.')"',$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_CLASS}','kor-label w100p js-radio-trigger',$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_CLASSDIV}','type paymentServiceCC',$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_CLASSINPUT}','js-radio-target',$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aPaym[$p]['name'],$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_ICONCLASS}',$iconclass,$cur_opt);
        		$cur_opt = str_replace('{GSSE_OPT_ICONTITLE}',$icontitle,$cur_opt);
        		$paymentitems .= $cur_opt;
				$onclick = $onclick+1;
        	}
        }
		
    }
    $buypayment = str_replace('{GSSE_INCL_PAYMENT}',$paymentitems,$buypayment);
}

$_SESSION['order'] = serialize($order);
$this->content = str_replace($tag, $buypayment , $this->content);
?>