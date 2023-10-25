<?php
session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$buy3spmhtml = '';



switch($_SESSION['delivery']['paym']['internalname'])
{
	case 'PaymentPayPal':
		$buy3spmhtml = $this->gs_file_get_contents('template/paypal_form.html');
		$buy3spmhtml = str_replace('{GSSE_INCL_PPEMAIL}',$this->get_setting('edPayPalID_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCURRENCY}',$this->get_setting('edCurrencySymbol_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_ITEMNAME}',spec_sign($this->get_setting('e_emailsubject_Text')),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPAMOUNT}',$_SESSION['invoicetotal'] - $_SESSION['shipcost'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPSHIPPING}',$_SESSION['shipcost'],$buy3spmhtml);
		//$buy3spmhtml = str_replace('{GSSE_INCL_PPIMAGE}',$this->get_setting('edAbsoluteShopPath_Text') . 'template/images/' . $this->get_setting('edPayPalLogo_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPIMAGE}',$this->shopurl . 'template/images/' . $this->get_setting('edPayPalLogo_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSEMAIL}',$_POST['email'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSFIRST}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldFirstName')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSLAST}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldLastName')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSSTREET}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldAddress')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSSTREET2}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldAddress2')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSCITY}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldCity')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSZIP}',$_POST[$this->get_lngtext('LangTagFNFieldZipCode')],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPCUSCOUNTRY}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldState')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPITEMNUMBER}',$_SESSION['pid'],$buy3spmhtml);
		//$buy3spmhtml = str_replace('{GSSE_INCL_PPURLRETURN}',$this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=thankyou',$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPURLRETURN}',$this->shopurl . 'index.php?page=thankyou',$buy3spmhtml);
		//$buy3spmhtml = str_replace('{GSSE_INCL_PPURLCANCEL}',$this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=main',$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_PPURLCANCEL}',$this->shopurl . 'index.php?page=main',$buy3spmhtml);
		break;
	case 'PaymentWorldPay':
		if($this->get_setting('cbWorldPayTestMode_Checked') == 'True')
		{
			$wptest = '100';
		}
		else
		{
			$wptest = '0';
		}
		$allItems ='';
		foreach($_SESSION['basket'] as $val)
		{
			//$allItems .= trim($val['art_count'].' x '.$val['art_num'].' '. iconv('utf-8','iso-8859-2',$val['art_title']) .' '.iconv('utf-8','iso-8859-2',$val['art_attr0']).' '.iconv('utf-8','iso-8859-2',$val['art_attr1']).' '.iconv('utf-8','iso-8859-2',$val['art_attr2'])).', ';
			$allItems .= trim($val['art_count'].' x '.$val['art_num'].' '. $val['art_title'] .' '.$val['art_attr0'].' '.$val['art_attr1'].' '.$val['art_attr2']).', ';
		}
		//A TS 26.12.2012 Hochkommata
		//orig: $allItems = substr($allItems, 0, strlen($allItems)-2);
		//$allItems = str_replace('"','\"',substr($allItems, 0, strlen($allItems)-2));
		$allItems = htmlentities(substr($allItems, 0, strlen($allItems)-2),ENT_QUOTES);
		$buy3spmhtml = $this->gs_file_get_contents('template/worldpay_form.html');
		$buy3spmhtml = str_replace('{GSSE_INCL_WPTEST}',$wptest,$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPINSTID}',$this->get_setting('edWorldPayID_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCURRENCY}',$this->get_setting('edCurrencySymbol_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSFULLNAME}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldFirstName')]) . ' ' . spec_sign($_POST[$this->get_lngtext('LangTagFNFieldLastName')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSADDRESS}',spec_sign($_POST[$this->get_lngtext('LangTagFNFieldAddress')]) . ' ' . spec_sign($_POST[$this->get_lngtext('LangTagFNFieldCity')]),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSPOSTCODE}',$_POST[$this->get_lngtext('LangTagFNFieldZipCode')],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSPHONE}',$_POST[$this->get_lngtext('LangTagFNFieldPhone')],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSFAX}',$_POST[$this->get_lngtext('LangTagFNFieldFax')],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCUSEMAIL}',$_POST['email'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCARTID}',$_SESSION['pid'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPAMOUNT}',$_SESSION['invoicetotal'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPDESC}',$allItems,$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WPCOUNTRY}',strtolower(spec_sign($_POST[$this->get_lngtext('LangTagFNFieldState')])),$buy3spmhtml);
		break;
	case 'PaymentWebMoney':
		if($this->get_setting('cbwebmoneyTestMode_Checked') == 'True')
		{
			$wmtest = '1';
		}
		else
		{
			$wmtest = '0';
		}
		$buy3spmhtml = $this->gs_file_get_contents('template/webmoney_form.html');
		$buy3spmhtml = str_replace('{GSSE_INCL_WMAMOUNT}',$_SESSION['invoicetotal'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMDESC}',iconv('utf-8','iso-8859-2',$_SESSION['basket'][0]['art_num'].' '.$_SESSION['basket'][0]['art_title']),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMPAYMENTNO}',$_SESSION['pid'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMPAYEEPURSE}',$this->get_setting('edwebmoneyID_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMMODE}',$wmtest,$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMSIMMODE}','0',$buy3spmhtml);
		//$buy3spmhtml = str_replace('{GSSE_INCL_WMURLRETURN}',$this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=thankyou',$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMURLRETURN}',$this->shopurl . 'index.php?page=thankyou',$buy3spmhtml);
		//$buy3spmhtml = str_replace('{GSSE_INCL_WMURLCANCEL}',$this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=main',$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_WMURLCANCEL}',$this->shopurl . 'index.php?page=main',$buy3spmhtml);
		break;
	case 'PaymentGiropay':
		if($this->get_setting('cbgiropayTestMode_Checked') == 'True')
		{
			$gptest = '1';
		}
		else
		{
			$gptest = '0';
		}
		$buy3spmhtml = $this->gs_file_get_contents('template/giropay_form.html');
		$buy3spmhtml = str_replace('{GSSE_INCL_GPAMOUNT}',number_format($_SESSION['invoicetotal'] * 100,0,'',''),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPACCOUNTID}',$this->get_setting('edgiropayID_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPCURRENCY}',$this->get_setting('edCurrencySymbol_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPDESCRIPTION}',$this->get_lngtext('LangTagFNFieldTotalAmount'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPNOTIFY}',$_POST['email'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPORDERID}',$_SESSION['pid'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPTESTMODE}',$gptest,$buy3spmhtml);
		break;
	case 'PaymentSaferpay':
		if($this->get_setting('cbSaferpayTestMode_Checked') == 'True')
		{
			$gptest = '1';
		}
		else
		{
			$gptest = '0';
		}
		$buy3spmhtml = $this->gs_file_get_contents('template/giropay_form.html');
		$buy3spmhtml = str_replace('{GSSE_INCL_GPAMOUNT}',number_format($_SESSION['invoicetotal'] * 100,0,'',''),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPACCOUNTID}',$this->get_setting('edgiropayID_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPCURRENCY}',$this->get_setting('edCurrencySymbol_Text'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPDESCRIPTION}',$this->get_lngtext('LangTagFNFieldTotalAmount'),$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPNOTIFY}',$_POST['email'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPORDERID}',$_SESSION['pid'],$buy3spmhtml);
		$buy3spmhtml = str_replace('{GSSE_INCL_GPTESTMODE}',$gptest,$buy3spmhtml);
		break;
	default:
		break;
}

$this->content = str_replace($tag, $buy3spmhtml, $this->content);


function spec_sign($str)
{
	$str = str_replace('Ä','Ae',$str);
	$str = str_replace('Ö','Oe',$str);
	$str = str_replace('Ü','Ue',$str);
	$str = str_replace('ä','ae',$str);
	$str = str_replace('ö','oe',$str);
	$str = str_replace('Ü','ue',$str);
	$str = str_replace('ß','ss',$str);
	return $str;
}

