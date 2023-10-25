<?php
session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$buy2html = $this->gs_file_get_contents('template/buy2form.html');
//print_r($_SESSION['delivery']);
//echo "<br />------------------------------<br />Cookies:<br />";

/*if(isset($_COOKIE))
{
	print_r($_COOKIE);
}*/


$addfield = $this->gs_file_get_contents('template/buy2formadditional.html');

/*Begin general texts*/
$aBuy2Tags = $this->get_tags_ret($buy2html);
$buy2html = $this->parse_texts($aBuy2Tags,$buy2html);
/*End general texts*/

$opthtml = $this->gs_file_get_contents('template/option.html');

/*Begin privorbusiness*/
$opts = '';
$cur_opt = $opthtml;
$cur_opt = str_replace('{GSSE_OPT_VALUE}',$this->get_lngtext('LangTagBuy2InputLabelPrivat'),$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagBuy2InputLabelPrivat'),$cur_opt);
if($_SESSION['login']['ok'])
{
	$sel = ($_SESSION['login']['cusFirmname'] == '') ? 'selected' : '';
	if($sel != '')
	{
		$buy2html = str_replace('{GSSE_INCL_CLASSCOMPANY}','displaynone',$buy2html);
		$buy2html = str_replace('{GSSE_INCL_CLASSCUSNR}','displaynone',$buy2html);
		$buy2html = str_replace('{GSSE_INCL_CLASSVATID}','displaynone',$buy2html);
	}
}
else
{
	$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldCompanyOrPrivate|LangTagBuy2InputLabelPrivat}';
	$buy2html = str_replace('{GSSE_INCL_CLASSCOMPANY}','displaynone',$buy2html);
	$buy2html = str_replace('{GSSE_INCL_CLASSCUSNR}','displaynone',$buy2html);
	$buy2html = str_replace('{GSSE_INCL_CLASSVATID}','displaynone',$buy2html);
}
$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
$opts .= $cur_opt;

$cur_opt = $opthtml;
$cur_opt = str_replace('{GSSE_OPT_VALUE}',$this->get_lngtext('LangTagBuy2InputLabelBusiness'),$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagBuy2InputLabelBusiness'),$cur_opt);
if($_SESSION['login']['ok'])
{
	$sel = ($_SESSION['login']['cusFirmname'] != '') ? 'selected' : '';
	if($sel != '')
	{
		$buy2html = str_replace('{GSSE_INCL_CLASSCOMPANY}','',$buy2html);
		$buy2html = str_replace('{GSSE_INCL_CLASSCUSNR}','',$buy2html);
		$buy2html = str_replace('{GSSE_INCL_CLASSVATID}','',$buy2html);
	}
}
else
{
	$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldCompanyOrPrivate|LangTagBuy2InputLabelBusiness}';
}
$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
$opts .= $cur_opt;
$buy2html = str_replace('{GSSE_INCL_PRIVORBUSI}',$opts,$buy2html);
/*End privorbusiness*/

/*Begin Mr or Mrs*/
$opts = '';
$cur_opt = $opthtml;
$cur_opt = str_replace('{GSSE_OPT_VALUE}','',$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_SELECTED}','',$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagPleaseSelect'),$cur_opt);
$opts .= $cur_opt;
$cur_opt = $opthtml;
$cur_val = $this->get_lngtext('LangTagMr');
$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
if($_SESSION['login']['ok'])
{
	$sel = ($_SESSION['login']['cusTitle'] == $cur_val) ? 'selected' : '';
}
else
{
	$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldFormToAddress|LangTagMr}';
}
$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
$opts .= $cur_opt;

$cur_opt = $opthtml;
$cur_val = $this->get_lngtext('LangTagMrs');
$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
if($_SESSION['login']['ok'])
{
	$sel = ($_SESSION['login']['cusTitle'] == $cur_val) ? 'selected' : '';
}
else
{
	$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldFormToAddress|LangTagMrs}';
}
$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
$opts .= $cur_opt;
$buy2html = str_replace('{GSSE_INCL_MRORMRS}',$opts,$buy2html);
/*End Mr or Mrs*/

/*Begin countries*/
$countryhtml = '';
$aCountries = $this->get_countries($_SESSION['delivery']['area']['id']);
$cntmax = count($aCountries);
if($cntmax > 0)
{
	$cur_opt = $opthtml;
	$cur_opt = str_replace('{GSSE_OPT_VALUE}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagPleaseSelect'),$cur_opt);
	$countryhtml .= $cur_opt;
	for($c = 0; $c < $cntmax; $c++)
	{
		$cur_opt = $opthtml;
		if($_SESSION['login']['ok'])
		{
			$sel = ($_SESSION['login']['cusCountry'] == $aCountries[$c]['oval']) ? 'selected' : '';
		}
		else
		{
			$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldState|' . $aCountries[$c]['oval'] . '}';
		}
		$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aCountries[$c]['oval'],$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
		$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aCountries[$c]['otext'],$cur_opt);
		$countryhtml .= $cur_opt;
	}
}
$buy2html = str_replace('{GSSE_INCL_COUNTRYITEMS}',$countryhtml,$buy2html);
/*End countries*/

/*Begin Actionkey*/
$actkey = '';
if($this->phpactive())
{
	$actkey = $addfield;
	$actkey = str_replace('{GSSE_INCL_LEGEND}',$this->get_lngtext('LangTagFieldAktKey'),$actkey);
	$actkey = str_replace('{GSSE_INCL_REQUESTED}','',$actkey);
	$actkey = str_replace('{GSSE_INCL_FIELDNAME}',$this->get_lngtext('LangTagFNFieldAktKey'),$actkey);
}
$buy2html = str_replace('{GSSE_INCL_FIELDACTIONKEY}',$actkey,$buy2html);
/*End Actionkey*/

/*Begin Paymentfields*/
$paymenthtml = '';
if($_SESSION['delivery']['paym']['internalname'] == 'PaymentDirectDebit')
{
	//Lastschrift
	$paymenthtml = $this->gs_file_get_contents('template/directdebit.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
}
if($_SESSION['delivery']['paym']['internalname'] == 'PaymentCreditCard')
{
	//Lastschrift
	$paymenthtml = $this->gs_file_get_contents('template/creditcard.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
}
$buy2html = str_replace('{GSSE_INCL_PAYMENTFIELDS}',$paymenthtml,$buy2html);
/*End Paymentfields*/

/*Begin additional fields*/
$addfields = '';
for($f = 1; $f <= 5; $f++)
{
	//We have 5 additional fields
	if($this->get_setting('cb_activ' . $f . '_Checked') == 'True')
	{
		$cur_field = $addfield;
		$fieldtitle = $this->get_setting('ed_name' . $f . '_Text');
		$fieldname = $this->formfriendly($fieldtitle);
		$req = ($this->get_setting('cb_mandatoryfield' . $f . '_Checked') == 'True') ? '*' : '';
		$cur_field = str_replace('{GSSE_INCL_LEGEND}',$fieldtitle,$cur_field);
		$cur_field = str_replace('{GSSE_INCL_REQUESTED}',$req,$cur_field);
		$cur_field = str_replace('{GSSE_INCL_FIELDNAME}',$fieldname,$cur_field);
		$addfields .= $cur_field;
	}
}
$buy2html = str_replace('{GSSE_INCL_ADDITIONALFIELDS}',$addfields,$buy2html);
/*End additional fields*/


$phonereq = '';
if($this->get_setting('cb_Phone_Checked') == 'True')
{
	$phonereq = '*';
}
$buy2html = str_replace('{GSSE_INCL_PHONEREQ}',$phonereq,$buy2html);
	
$birthreq = '';
if($this->get_setting('cb_birthField_Checked') == 'True')
{
	$birthreq = '*';
}
$buy2html = str_replace('{GSSE_INCL_BIRTHREQ}',$birthreq,$buy2html);

/*Begin deliveryaddress*/
$delhtml = '';
if($this->get_setting('cbAllowShippingAddress_Checked') == 'True')
{
	$delhtml = $this->gs_file_get_contents('template/deliveryaddress.html');
	$aDelTags = $this->get_tags_ret($delhtml);
	$delhtml = $this->parse_texts($aDelTags,$delhtml);
	
	$opts = '';
	$cur_opt = $opthtml;
	$cur_opt = str_replace('{GSSE_OPT_VALUE}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagPleaseSelect'),$cur_opt);
	$opts .= $cur_opt;
	$cur_opt = $opthtml;
	$cur_val = $this->get_lngtext('LangTagMr');
	$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusDeliverTitle'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldShippingFormToAddress|LangTagMr}';
	}
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
	$opts .= $cur_opt;
	
	$cur_opt = $opthtml;
	$cur_val = $this->get_lngtext('LangTagMrs');
	$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusDeliverTitle'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldShippingFormToAddress|LangTagMrs}';
	}
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
	$opts .= $cur_opt;
	$delhtml = str_replace('{GSSE_INCL_DELMRORMRS}',$opts,$delhtml);
}
$buy2html = str_replace('{GSSE_INCL_DELIVERYADDRESS}',$delhtml,$buy2html);
/*End deliveryaddress*/

/*Begin Newsletter*/
$nlhtml = '';
if($this->get_setting('cbTermsAndConditionsNewsletter_Checked') == 'True')
{
	$nlhtml = $this->gs_file_get_contents('template/activate_newsletter.html');
	$aNLTags = $this->get_tags_ret($nlhtml);
	$nlhtml = $this->parse_texts($aNLTags,$nlhtml);
}
$buy2html = str_replace('{GSSE_INCL_NEWSLETTER}',$nlhtml,$buy2html);
/*End Newsletter*/

/*Begin accept terms and conds*/
$tachtml = '';
if($this->get_setting('cbTermsAndConditions_Checked') == 'True' && $this->get_setting('cbTermsAndConditionsExtra_Checked') == 'False')
{
	$tachtml = $this->gs_file_get_contents('template/accepttermsandcond.html');
	$aTACTags = $this->get_tags_ret($tachtml);
	$tachtml = $this->parse_texts($aTACTags,$tachtml);
}
$buy2html = str_replace('{GSSE_INCL_ACCEPTTAC}',$tachtml,$buy2html);
/*End accept terms and conds*/

/*Begin accept right of revocation*/
$rorhtml = '';
if($this->get_setting('cbTermsAndConditionsExtra_Checked') == 'True' && $this->get_setting('cbTermsAndConditions_Checked') == 'False')
{
	$rorhtml = $this->gs_file_get_contents('template/acceptrightofrevocation.html');
	$aRORTags = $this->get_tags_ret($rorhtml);
	$rorhtml = $this->parse_texts($aRORTags,$rorhtml);
	
}
$buy2html = str_replace('{GSSE_INCL_ACCEPTROR}',$rorhtml,$buy2html);
/*End accept right of revocation*/

//A TS 18.06.2015: Accept all conditions
/*Begin all conditions*/
$allcondhtml = '';
if($this->get_setting('cbTermsAndConditionsExtra_Checked') == 'True' && $this->get_setting('cbTermsAndConditions_Checked') == 'True')
{
	$allcondhtml = $this->gs_file_get_contents('template/acceptallcond.html');
	$aACOTags = $this->get_tags_ret($allcondhtml);
	$allcondhtml = $this->parse_texts($aACOTags,$allcondhtml);
	
}
$buy2html = str_replace('{GSSE_INCL_ACCEPTALL}',$allcondhtml,$buy2html);
/*End all conditions*/

/*Begin E-Mailformat*/
$emfhtml = '';
$opts = '';
if($this->get_setting('cbUsePhpEmailExtension_Checked') == 'True')
{
	$emfhtml = $this->gs_file_get_contents('template/emailformat.html');
	$aEMFTags = $this->get_tags_ret($emfhtml);
	$emfhtml = $this->parse_texts($aEMFTags,$emfhtml);
	$cur_opt = $opthtml;
	$cur_val = 'text';
	$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|text}';
	}
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
	$opts .= $cur_opt;
	
	$cur_opt = $opthtml;
	$cur_val = 'html';
	$cur_opt = str_replace('{GSSE_OPT_VALUE}',$cur_val,$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$cur_val,$cur_opt);
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|html}';
	}
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
	$opts .= $cur_opt;
	$emfhtml = str_replace('{GSSE_INCL_OPTSEMAILFORMAT}',$opts,$emfhtml);
}
$buy2html = str_replace('{GSSE_INCL_EMAILFORMAT}',$emfhtml,$buy2html);
/*End E-Mailformat*/

/*Begin Cookies*/
if(!$_SESSION['login']['ok'])
{
	$buy2html = $this->parse_cookies($buy2html);
}
else
{
	$buy2html = $this->set_userdata($buy2html);
	//Restliche Felder ggf. mit Daten aus dem Cookie bestücken 
	$buy2html = $this->parse_cookies($buy2html);
}
/*End Cookies*/

/*A TS 27.11.2015: Bei Neukunden E-Mail-Wiederholung anzeigen*/
$emailrepeathtml = '';
if(!$_SESSION['login']['ok']) {
	$emailrepeathtml = $this->gs_file_get_contents('emailrepeat.html');
	$emailrepeathtml = str_replace('{GSSE_LANG_LangTagEmailRepeat}',$this->get_lngtext('LangTagBuy2InputLabelPrivat'),$emailrepeathtml);
}
$buy2html = str_replace('{GSSE_INCL_EMAILRPT}',$emailrepeathtml,$buy2html);
/*E TS 27.11.2015: Bei Neukunden E-Mail-Wiederholung anzeigen*/

$this->content = str_replace('{GSSE_FUNC_BUY2}', $buy2html, $this->content);
