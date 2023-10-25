<?php
session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$buy3userdatahtml = $this->gs_file_get_contents('template/buy3userdata.html');
$aBuy3UTags = $this->get_tags_ret($buy3userdatahtml);
$buy3userdatahtml = $this->parse_texts($aBuy3UTags,$buy3userdatahtml);
$buy3uditem = $this->gs_file_get_contents('template/buy3userdataitem.html');

/*
echo "<br />------------------------------------------------<br />&nbsp;<br />";
print_r($_POST);
echo "<br />------------------------------------------------<br />&nbsp;<br />";
*/
/*Begin CusNo*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldCustomerNR');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_CUSTOMERNR}', $curitem, $buy3userdatahtml);
/*End CusNo*/

/*Begin Company*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldCompany');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_COMPANY}', $curitem, $buy3userdatahtml);
/*End Company*/

/*Begin VatId*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldFirmVATId');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_VATID}', $curitem, $buy3userdatahtml);
/*End VatId*/

/*Begin Fullname and Address*/
$buy3userdatahtml = str_replace('{GSSE_INCL_FULLNAME}',$_POST[$this->get_lngtext('LangTagFNFieldFormToAddress')] . " " . $_POST[$this->get_lngtext('LangTagFNFieldFirstName')] . " " . $_POST[$this->get_lngtext('LangTagFNFieldLastName')], $buy3userdatahtml);
$buy3userdatahtml = str_replace('{GSSE_INCL_FULLADDRESS}',$_POST[$this->get_lngtext('LangTagFNFieldAddress')] . " " . $_POST[$this->get_lngtext('LangTagFNFieldZipCode')] . " " . $_POST[$this->get_lngtext('LangTagFNFieldCity')], $buy3userdatahtml);
/*Begin Fullname and Address*/

/*Begin Address2*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldAddress2');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_ADDRESS2}', $curitem, $buy3userdatahtml);
/*End Address2*/

/*Begin Fax*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldFax');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_FAX}', $curitem, $buy3userdatahtml);
/*End Fax*/

/*Begin Mobil*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldMobil');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_MOBIL}', $curitem, $buy3userdatahtml);
/*End Mobil*/

/*Begin Birthday*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldGeburtsdatum');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fdisp = $_POST[$field];
	$fvalue = $fdisp;
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fdisp, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_BIRTHDAY}', $curitem, $buy3userdatahtml);
/*End Birthday*/

/*Begin Actkey*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldAktKey');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_ACTKEY}', $curitem, $buy3userdatahtml);
/*End Actkey*/

/*Begin additional fields*/
for($a = 1; $a <= 5; $a++)
{
	$curitem = '';
	$fieldtitle = $this->get_setting('ed_name' . $a . '_Text');
	$field = $this->formfriendly($fieldtitle);
	if($_POST[$field] != '')
	{
		$curitem = $buy3uditem;
		$fname = $fieldtitle;
		$fvalue = $_POST[$field];
		$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
		$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $field, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
	}
	$buy3userdatahtml = str_replace('{GSSE_INCL_ADDFIELD' . $a . '}', $curitem, $buy3userdatahtml);
}
/*End additional fields*/

/*Begin Payment*/
$paymenthtml = '';
if($_SESSION['delivery']['paym']['internalname'] == 'PaymentDirectDebit')
{
	//Lastschrift
	$paymenthtml = $this->gs_file_get_contents('template/directdebit_buy3.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_BANKNAME}',$_POST[$this->get_lngtext('LangTagFNFinancialInstitution')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_BANKCODE}',$_POST[$this->get_lngtext('LangTagFNBankCodeNumber')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_BANKACCOUNT}',$_POST[$this->get_lngtext('LangTagFNAccountNumber')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_BANKHOLDER}',$_POST[$this->get_lngtext('LangTagFNAccountHolder')], $paymenthtml);
}
if($_SESSION['delivery']['paym']['internalname'] == 'PaymentCreditCard')
{
	$paymenthtml = $this->gs_file_get_contents('template/creditcard_buy3.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_CARDNAME}',$_POST[$this->get_lngtext('LangTagFNCreditCard')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_EXPMONTH}',$_POST[$this->get_lngtext('LangTagFNMonthExpirationDate')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_EXPYEAR}',$_POST[$this->get_lngtext('LangTagFNYearExpirationDate')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_CARDNUMBER}',$_POST[$this->get_lngtext('LangTagFNCreditCardNumber')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_ISSUENO1}',$_POST[$this->get_lngtext('LangTagFNIssueNumber')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_SECURITYCODE}',$_POST[$this->get_lngtext('LangTagFNSecurityCode')], $paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_CARDHOLDER}',$_POST[$this->get_lngtext('LangTagFNCreditCardHolder')], $paymenthtml);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_PAYMENT}', $paymenthtml, $buy3userdatahtml);
/*End Payment*/

/*Begin delivery*/
$deladdr = '';
if($this->get_setting('cbAllowShippingAddress_Checked') == 'True')
{
	$deladdr = $this->gs_file_get_contents('template/shippingaddress_head.html');
	$aDATags = $this->get_tags_ret($deladdr);
	$deladdr = $this->parse_texts($aDATags,$deladdr);
	$aFields = array('LangTagFNFieldShippingCompany','LangTagFNFieldShippingFormToAddress','LangTagFNFieldShippingFirstName','LangTagFNFieldShippingLastName','LangTagFNFieldShippingStreet','LangTagFNFieldShippingZipCode','LangTagFNFieldShippingCity','LangTagFNFieldShippingAddress2');
	$aDispFi = array('LangTagFNFieldCompany','LangTagFNFieldFormToAddress','LangTagFNFieldFirstName','LangTagFNFieldLastName','LangTagFNFieldAddress','LangTagFNFieldZipCode','LangTagFNFieldCity','LangTagFNFieldAddress2');
	$fldmax = count($aFields);
	for($f = 0; $f < $fldmax; $f++)
	{
		$field = $this->get_lngtext($aFields[$f]);
		if($_POST[$field] != '')
		{
			$curitem = $buy3uditem;
			$fname = $this->get_lngtext($field);
			$fdisp = $this->get_lngtext($aDispFi[$f]);
			$fvalue = $_POST[$field];
			$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fdisp, $curitem);
			$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
			//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
			//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
			$deladdr .= $curitem;
		}
	}
	$field = $this->get_lngtext('LangTagFNFieldShippingFormToAddress');
	
	
}
$buy3userdatahtml = str_replace('{GSSE_INCL_DELIVERYADDRESS}', $deladdr, $buy3userdatahtml);
/*End delivery*/

/*Begin want newsletter*/
$curitem = '';
if($this->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
{
	$field = $this->get_lngtext('LangTagFNTermsAndCondNewsletter');
	if($_POST[$field] != '')
	{
		$curitem = $buy3uditem;
		$fname = $this->get_lngtext($field);
		$fdisp = $this->get_lngtext('LangTagNewsletter');
		$fvalue = $_POST[$field];
		$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fname, $curitem);
		$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
	}
}
$buy3userdatahtml = str_replace('{GSSE_INCL_WANTNEWSLETTER}', $curitem, $buy3userdatahtml);
/*Begin want newsletter*/

/*Begin termsandcond*/
$curitem = '';
if($this->get_setting('cbTermsAndConditions_Checked') == 'True')
{
	$field = 'accepttermsancond';
	if($_POST[$field] != '')
	{
		$curitem = $this->gs_file_get_contents('template/termsandcond_buy3.html');
		$fname = $field;
		$fdisp = $this->get_lngtext('LangTagFNTermsAndCond');
		$fvalue = $_POST[$field];
		$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fdisp, $curitem);
		$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
	}
}
$buy3userdatahtml = str_replace('{GSSE_INCL_TERMSANDCOND}', $curitem, $buy3userdatahtml);
/*Begin termsandcond*/

/*Begin termsandcondwithdrawal*/
$curitem = '';
if($this->get_setting('cbTermsAndConditionsExtra_Checked') == 'True')
{
	$field = 'acceptror';
	if($_POST[$field] != '')
	{
		$curitem = $buy3uditem;
		$fname = $field;
		$fdisp = $this->get_lngtext('LangTagFNTermsAndCondWithdrawal');
		$fvalue = $_POST[$field];
		$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fdisp, $curitem);
		$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
		//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
	}
}
$buy3userdatahtml = str_replace('{GSSE_INCL_TACWITHDRAWAL}', $curitem, $buy3userdatahtml);
/*Begin termsandcondwithdrawal*/

/*Begin E-Mail-Format*/
$curitem = '';
$field = $this->get_lngtext('LangTagFNFieldEmailFormat');
if($_POST[$field] != '')
{
	$curitem = $buy3uditem;
	$fname = $this->get_lngtext($field);
	$fdisp = $this->get_lngtext('LangTagFieldEmailFormat');
	$fvalue = $_POST[$field];
	$curitem = str_replace('{GSSE_INCL_DISPFIELDNAME}', $fdisp, $curitem);
	$curitem = str_replace('{GSSE_INCL_DISPFIELDVALUE}', $fvalue, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDNAME}', $fname, $curitem);
	//$curitem = str_replace('{GSSE_INCL_INPFIELDVALUE}', $fvalue, $curitem);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_EMAILFORMAT}', $curitem, $buy3userdatahtml);
/*End E-Mail-Format*/

/*Begin set values*/
$aValTags = $this->get_tags_ret($buy3userdatahtml);
$buy3userdatahtml = $this->set_values($aValTags,$buy3userdatahtml);
/*End set values*/

$this->content = str_replace('{GSSE_FUNC_BUY3USERDATA}', $buy3userdatahtml, $this->content);
