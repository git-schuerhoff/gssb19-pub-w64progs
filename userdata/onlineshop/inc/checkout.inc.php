<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]

/*A TS 23.06.2015: include security-functions to parse users bankinfo*/
//TS 30.11.2015: shoplog wird auch bei nicht eingeloggten Kunden für die Erzeugung eines Codes gebraucht,
//also shoplog immer instantiieren
//if($_SESSION['login']['ok']) {
	if(file_exists("dynsb/class/class.shoplog.php")) {
		if(!in_array("shoplog",get_declared_classes()))
		{
			require_once("dynsb/class/class.shoplog.php");
			$sl = new shoplog();
		}
	} else {
		die("Class shoplog missing!");
	}
//}
/*E TS 23.06.2015: include security-functions to parse users bankinfo*/

if(!isset($_SESSION['basket']))
{
	header('Location: index.php?page=basket');
}
else
{
	if(empty($_SESSION['basket'])) header('Location: index.php?page=basket');
}

/*if(isset($_GET['pp_status'])) {
	echo "<pre>";
	echo $_GET['pp_status'];
	print_r($_SESSION['basket']);
	die("</pre>");
}*/

$buyhtml = $this->gs_file_get_contents('template/checkout.html');
$buyhtml = $this->parse_texts($this->get_tags_ret($buyhtml),$buyhtml);

//A TS 30.12.2014: Mailsystem nach Einstellung wählen
//inc/gsorder.inc.php = bisheriges Mailsystem
//inc/gsme_order.inc.php = Mailsystem der GS MailEngine (GSME)
if ($this->get_setting('cbUseSSLMailScript_Checked') == 'True') 
{
	if ($this->get_setting('edSSLMailScriptURL_Text') <> '')
	{
		$mailsystem = $this->get_setting('edSSLMailScriptURL_Text').'/inc/gsorder.inc.php';
		if($this->get_setting('cbUseMailSystem_Checked') == 'True') {
			$mailsystem = $this->get_setting('edSSLMailScriptURL_Text').'/inc/gsme_order.inc.php';
		}
	}
	else
	{
		$url_https = $this->absurl;
		$url_https = str_replace('http:', 'https:', $url_https);
		$mailsystem = $url_https.'/inc/gsorder.inc.php';
		if($this->get_setting('cbUseMailSystem_Checked') == 'True') {
			$mailsystem = $url_https.'/inc/gsme_order.inc.php';
		}
	}
}
else
{
	$mailsystem = 'inc/gsorder.inc.php';
	if($this->get_setting('cbUseMailSystem_Checked') == 'True') {
		$mailsystem = 'inc/gsme_order.inc.php';
	}
}
$buyhtml = str_replace('{GSSE_INCL_MAILSYSTEM}',$mailsystem,$buyhtml);
//E TS 30.12.2014

// Wird ein Fehler von der Zahlungsart-Anbieterseite ausgegeben, soll der Shopkunde einen anderen Zahlungsart auswählen 
if (isset($_GET['failcode']))
{
	$buyhtml = str_replace('{GSSE_MSG_}',$_GET['failcode'],$buyhtml);
}
else
{
	$buyhtml = str_replace('{GSSE_MSG_}','',$buyhtml);
}


//Skonto-Text
$proccharge = 0;
$cashdiscounttext = '';
if($this->phpactive())
{
	$cashdiscounttext = $this->gs_file_get_contents('template/b_tag.html');
	$cashdiscounttext = str_replace('{GSSE_INCL_BOLDTEXT}',$this->db_text_ret('settingmemo|SettingMemo|SettingName|memoSkontoText'),$cashdiscounttext);
}
$buyhtml = str_replace('{GSSE_INCL_BUYCASHSICOUNTTEXT}',$cashdiscounttext,$buyhtml);

$optionhtml = $this->gs_file_get_contents('template/option.html');
$sel = '';
//{GSSE_OPT_VALUE}
//{GSSE_OPT_SELECTED}
//{GSSE_OPT_TEXT}

/*echo "<pre>";
print_r($_SESSION['basket']);
die("</pre>");*/


$addritems = '';
$aAreas = array();
$cAreas = '';
$buydbh = $this->db_connect();
$adsql = "SELECT AreaId, Text FROM " . $this->dbtoken . "addressarea WHERE Text != '' AND CountryId = '" . $this->cntID . "' AND LanguageId = '" . $this->lngID . "' ORDER BY AreaId ASC";
$aderg = mysqli_query($buydbh,$adsql);
if(mysqli_errno($buydbh) == 0)
{
	if(mysqli_num_rows($aderg) > 0)
	{
		while($ad = mysqli_fetch_assoc($aderg))
		{
			if(isset($_SESSION['delivery']))
			{
				if($_SESSION['delivery']['area']['id'] == $ad['AreaId'])
				{
					$sel = 'selected="selected"';
				}
				else
				{
					$sel = '';
				}
			}
			$cur_opt = $optionhtml;
			//$cur_opt = str_replace('',,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$ad['AreaId'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$ad['Text'],$cur_opt);
			$addritems .= $cur_opt;
			/*array_push($aAreas,$ad['AreaId']);*/
			$aAreas[] = $ad['AreaId'];
		}
	}
	mysqli_free_result($aderg);
}
else
{
	die(mysqli_error($buydbh) . "<br />" . $adsql);
}
$buyhtml = str_replace('{GSSE_INCL_BUYADDRESSAREAITEMS}',$addritems,$buyhtml);

$shipmentitems = '';
if(count($aAreas) > 0)
{
	$aShipm = $this->get_shipment($aAreas[0]);
	$smpmax2 = count($aShipm);
	if($smpmax2 > 0)
	{
		for($s = 0; $s < $smpmax2; $s++)
		{
			$cur_opt = $optionhtml;
			//$cur_opt = str_replace('',,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aShipm[$s]['sortid'],$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
			$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aShipm[$s]['name'],$cur_opt);
			$shipmentitems .= $cur_opt;
		}
	}
}
$buyhtml = str_replace('{GSSE_INCL_BUYSHIPMENTITEMS}',$shipmentitems,$buyhtml);

$paymentitems = '';
$download = false;
$rentals = false;
$buypayment = '';
//TS 11.12.2015: Auch auf Mietpreise checken
if(count($aAreas) > 0)
{
	// Wenn Downloadartikel im Warenkorb sind -> Rechnung, Nachnahme und Vorkasse ausblenden
	if(isset($_SESSION['basket']))
	{
		$bmax = count($_SESSION['basket']);
		if($bmax > 0)
		{
			for($b = 0; $b < $bmax; $b++)
			{
				if($_SESSION['basket'][$b]['art_isdownload'] == 'Y') {
					$download = true;
				}
				if($_SESSION['basket'][$b]['art_prices']['isrental'] == 'Y') {
					$rentals = true;
				}
			}
		}
	}
	$aPaym = $this->get_payment($aAreas[0], $download);
	//print_r($aPaym);
	//die($this->get_setting('rbUsePPPlus_Checked'));
	$pmmax2 = count($aPaym);
	
	if($pmmax2 > 0)
	{
		//if($this->get_setting('rbUsePPPlus_Checked') == 'False') {
			//Klassische Zahlmethoden
			$buypayment = $this->gs_file_get_contents('template/buypayment.html');
			for($p = 0; $p < $pmmax2; $p++)
			{
				$cur_opt = $optionhtml;
				//$cur_opt = str_replace('',,$cur_opt);
				if (isset($_GET['failcode']))
				{
					if ($_SESSION['delivery']['internalname'] <> $_GET['internalPaymentName'])
					{
						$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aPaym[$p]['sortid'],$cur_opt);
						$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
						$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aPaym[$p]['name'],$cur_opt);
						$paymentitems .= $cur_opt;
					}
				}
				else
				{
					$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aPaym[$p]['sortid'],$cur_opt);
					$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
					$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aPaym[$p]['name'],$cur_opt);
					$paymentitems .= $cur_opt;
				}
			}
			$buypayment = str_replace('{GSSE_INCL_BUYPAYMENTITEMS}',$paymentitems,$buypayment);
		/*} else {
			//Zahlen mit PayPal Plus
			require_once('inc/pp-plus_createpayment.inc.php');
			$buypayment = $this->gs_file_get_contents('template/buypaymentpaypalplus.html');
			$buypayment = str_replace('{GSSE_INCL_PPPAPPRURL}',$_SESSION['pp-plus']['approvalurl'],$buypayment);
			$buypayment = str_replace('{GSSE_INCL_PPPMODE}','sandbox',$buypayment);
		}*/
	}
}
$buyhtml = str_replace('{GSSE_INCL_BUYPAYMENT}',$buypayment,$buyhtml);



// Begin: GSSE_FUNC_BUY2BASKET
if(isset($_SESSION['desktop']))
{
	if($_SESSION['desktop']['is_phone'] == 1)
	{
		$buybasket = $this->gs_file_get_contents($this->absurl . 'template/buy2basket_mobile.html');
	}
	else
	{
		$buybasket = $this->gs_file_get_contents($this->absurl . 'template/buy2basket.html');
	}
}
else
{
	$buybasket = $this->gs_file_get_contents($this->absurl . 'template/buy2basket.html');
}
/*$buybasket = $this->gs_file_get_contents('template/buy2basket.html');*/

$aB2Tags = $this->get_tags_ret($buybasket);
$buybasket = $this->parse_texts($aB2Tags,$buybasket);
$vatincl = ($this->get_setting('cbNetPrice_Checked') == 'False') ? 1 : 0;
$showvat = ($this->get_setting('cbShowVAT_Checked') == 'True') ? 1 : 0;

/*echo "Init: " . $vatincl . "<br>";*/

$vattext = '';
if($showvat == 1)
{
	if($this->get_setting('cbNetPrice_Checked') == 'False') {
		$vattext = $this->get_lngtext('LangTagTextEncludedVAT') . "&nbsp;" . $this->get_lngtext('LangTagTextVAT');
	} else {
		$vattext = $this->get_lngtext('LangTagTextShortExclVAT');
	}
}
$buybasket = str_replace('{GSSE_INCL_VATTITLE}',$vattext,$buybasket);

//TS 11.12.2015: Hinweise bei Mietpreisen anzeigen
$hasrentalclass = 'no-display';
if($rentals) {
	$hasrentalclass = 'gs-float-left';
}
$buyhtml = str_replace('{GSSE_INCL_BASKETHASRENTALITEMS}', $hasrentalclass, $buyhtml);

//include_once('inc/basket2.inc.php');
//$buybasket = str_replace('{GSSE_FUNC_BASKET2}',$baskethtml,$buybasket);
// End GSSE_FUNC_BUY2BASKET

// Begin {GSSE_FUNC_BUY2}
$buy2html = $this->gs_file_get_contents('template/buy2form.html');
$addfield = $this->gs_file_get_contents('template/buy2formadditional.html');

/*Begin general texts*/
$aBuy2Tags = $this->get_tags_ret($buy2html);
$buy2html = $this->parse_texts($aBuy2Tags,$buy2html);
/*End general texts*/

/*A TS 18.06.2015: Bei angemeldeten Benutzern Felder für die Rechnungsadresse auf readonly setzen*/
$buy2readonly = '';
if(isset($_SESSION['login'])) {
	if($_SESSION['login']['ok']) {
		$buy2readonly = " readonly";
	}
}
$buy2html = str_replace('{GSSE_INCL_READONLY}',$buy2readonly,$buy2html);
/*E TS 18.06.2015: Bei angemeldeten Benutzern Felder für die Rechnungsadresse auf readonly setzen*/

$opthtml = $this->gs_file_get_contents('template/option.html');

/*Begin privorbusiness*/
$opts = '';
$cur_opt = $opthtml;
$cur_opt = str_replace('{GSSE_OPT_VALUE}',$this->get_lngtext('LangTagBuy2InputLabelPrivat'),$cur_opt);
$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagBuy2InputLabelPrivat'),$cur_opt);
if(isset($_SESSION['login']))
{
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
if(isset($_SESSION['login']))
{
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
if(isset($_SESSION['login']))
{
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusTitle'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldFormToAddress|LangTagMr}';
	}
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
if(isset($_SESSION['login']))
{
	if($_SESSION['login']['ok'])
	{
		$sel = ($_SESSION['login']['cusTitle'] == $cur_val) ? 'selected' : '';
	}
	else
	{
		$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldFormToAddress|LangTagMrs}';
	}
}
else
{
	$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldFormToAddress|LangTagMrs}';
}
$cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
$opts .= $cur_opt;
$buy2html = str_replace('{GSSE_INCL_MRORMRS}',$opts,$buy2html);
/*End Mr or Mrs*/

/*A TS 27.11.2015: Bei Neukunden E-Mail-Wiederholung anzeigen*/
$emailrepeathtml = '';
$emailconfirmhtml = '';
$emailval = 'style="display: none;"';

if($this->get_setting('cbUseEmailConfirmation_Checked') == 'True') {
	$emailval = '';
	if(!isset($_SESSION['login'])) {
		$emailrepeathtml = $this->gs_file_get_contents('template/emailrepeat.html');
		$emailrepeathtml = str_replace('{GSSE_LANG_LangTagEmailRepeat}',$this->get_lngtext('LangTagEmailRepeat'),$emailrepeathtml);
	} else {
		if(!$_SESSION['login']['ok']) {
			$emailrepeathtml = $this->gs_file_get_contents('template/emailrepeat.html');
			$emailrepeathtml = str_replace('{GSSE_LANG_LangTagEmailRepeat}',$this->get_lngtext('LangTagEmailRepeat'),$emailrepeathtml);
		} else {
			$emailval = 'style="display: none;"';
		}
	}
}
$buy2html = str_replace('{GSSE_INCL_EMAILRPT}',$emailrepeathtml,$buy2html);
$buyhtml = str_replace('{GSSE_INCL_SHOWVALIDATION}',$emailval,$buyhtml);
/*E TS 27.11.2015: Bei Neukunden E-Mail-Wiederholung anzeigen*/

/*Begin countries*/
$countryhtml = '';
$aCountries = array();
if(isset($_SESSION['delivery']))
{
	$aCountries = $this->get_countries($_SESSION['delivery']['area']['id']);
}
$cntmax3 = count($aCountries);
if($cntmax3 > 0)
{
	$cur_opt = $opthtml;
	$cur_opt = str_replace('{GSSE_OPT_VALUE}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_SELECTED}','',$cur_opt);
	$cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext('LangTagPleaseSelect'),$cur_opt);
	$countryhtml .= $cur_opt;
	for($c = 0; $c < $cntmax3; $c++)
	{
		$cur_opt = $opthtml;
		if(isset($_SESSION['login']))
		{
			if($_SESSION['login']['ok'])
			{
				$sel = ($_SESSION['login']['cusCountry'] == $aCountries[$c]['oval']) ? 'selected' : '';
			}
			else
			{
				$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldState|' . $aCountries[$c]['oval'] . '}';
			}
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
$actionkeyfield = '';
if($this->get_setting('cb_marketingKey_Checked') == 'True')
{
	$actionkeyfield = $this->gs_file_get_contents('template/actionkeyfield.html');
	$actionkeyTags = $this->get_tags_ret($actionkeyfield);
	$actionkeyfield = $this->parse_texts($actionkeyTags,$actionkeyfield);
	$actionkeyreqstar = '';
	$actionkeyreqclass = '';
	$actionkeyreqinpclass = '';
	if($this->get_setting('cb_mandatoryfieldMarketingKey_Checked') == 'True')
	{
		$actionkeyreqstar = '*';
		$actionkeyreqclass = 'required';
		$actionkeyreqinpclass = ' required-entry';
	}
	$actionkeyfield = str_replace('{GSSE_INCL_ACTKEYREQSTAR}',$actionkeyreqstar,$actionkeyfield);
	$actionkeyfield = str_replace('{GSSE_INCL_ACTKEYREQCLASS}',$actionkeyreqclass,$actionkeyfield);
	$actionkeyfield = str_replace('{GSSE_INCL_ACTKEYREQINPCLASS}',$actionkeyreqinpclass,$actionkeyfield);
}
$buy2html = str_replace('{GSSE_INCL_FIELDACTIONKEY}',$actionkeyfield,$buy2html);
/*End Actionkey*/

/*Begin Paymentfields*/
$paymenthtml = '';
//if($_SESSION['delivery']['paym']['internalname'] == 'PaymentDirectDebit')
//{
	//Lastschrift
	$paymenthtml = $this->gs_file_get_contents('template/directdebit.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
	/*A TS 23.06.2015: Display bankinfo, if loggedin*/
	$iban = "";
	$bic = "";
	$bank = "";
	$holder = "";
	if(isset($_SESSION['login'])) {
		if($_SESSION['login']['ok']) {
			$cd = $sl->getCustomerData($_SESSION ['login']['cusIdNo']);
			foreach($cd as $key => $val) if($key=="cusData") 
			{
				$iban = gsshow1("cusAccountNo", $val);
				$bic = gsshow1("cusBLZ", $val);
				$bank = gsshow1("cusBank", $val);
				$holder = gsshow1("cusAccountOwner", $val);
			}
		}
	}
	$paymenthtml = str_replace('{GSSE_INCL_BANK}',$bank,$paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_IBAN}',$iban,$paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_BIC}',$bic,$paymenthtml);
	$paymenthtml = str_replace('{GSSE_INCL_HOLDER}',$holder,$paymenthtml);
	/*E TS 23.06.2015: Display bankinfo, if loggedin*/
	$buy2html = str_replace('{GSSE_INCL_DIRECTDEBIT}',$paymenthtml,$buy2html);
//}
//if($_SESSION['delivery']['paym']['internalname'] == 'PaymentCreditCard')
//{
	//Lastschrift
	$paymenthtml = $this->gs_file_get_contents('template/creditcard.html');
	$aPMTags = $this->get_tags_ret($paymenthtml);
	$paymenthtml = $this->parse_texts($aPMTags,$paymenthtml);
	$buy2html = str_replace('{GSSE_INCL_CREDITCARD}',$paymenthtml,$buy2html);
//}

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
		$reqclass = ($this->get_setting('cb_mandatoryfield' . $f . '_Checked') == 'True') ? 'input-text required-entry' : 'input-text';
		$cur_field = str_replace('{GSSE_INCL_REQCLASS}', $reqclass, $cur_field);
		$cur_field = str_replace('{GSSE_INCL_LEGEND}',$fieldtitle,$cur_field);
		$cur_field = str_replace('{GSSE_INCL_REQUESTED}',$req,$cur_field);
		$cur_field = str_replace('{GSSE_INCL_FIELDNAME}',$fieldname,$cur_field);
		$cur_field = str_replace('{GSSE_INCL_FIELDID}','additional' . $f,$cur_field);
		$addfields .= $cur_field;
	}
}
$buy2html = str_replace('{GSSE_INCL_ADDITIONALFIELDS}',$addfields,$buy2html);
/*End additional fields*/

/* Phone field*/
if($this->get_setting('cb_Phone_Checked') == 'True'){
$phonefield = $this->gs_file_get_contents('template/phonefield.html');
$PhoneTags = $this->get_tags_ret($phonefield);
$phonefield = $this->parse_texts($PhoneTags,$phonefield);
$phonereqstar = '';
$phonereqclass = '';
$phonereqinpclass = '';
if($this->get_setting('cb_mandatoryfieldPhone_Checked') == 'True')
{
	$phonereqstar = '*';
	$phonereqclass = 'required';
	$phonereqinpclass = ' required-entry';
}
$phonefield = str_replace('{GSSE_INCL_PHONEREQSTAR}',$phonereqstar,$phonefield);
$phonefield = str_replace('{GSSE_INCL_PHONEREQCLASS}',$phonereqclass,$phonefield);
$phonefield = str_replace('{GSSE_INCL_PHONEREQINPCLASS}',$phonereqinpclass,$phonefield);
} else {
	$phonefield = '';
}
$buy2html = str_replace('{GSSE_INKL_PHONEFIELD}', $phonefield, $buy2html);
/* End Phone Field*/	

/* Birthdate field*/
if($this->get_setting('cb_birthField_Checked') == 'True'){
$birthfield = $this->gs_file_get_contents('template/birthfield.html');
$BirthTags = $this->get_tags_ret($birthfield);
$birthfield = $this->parse_texts($BirthTags,$birthfield);
$birthreqstar = '';
$birthreqclass = '';
$birthreqinpclass = '';
if($this->get_setting('cb_mandatoryfieldBirthdate_Checked') == 'True')
{
	$birthreqstar = '*';
	$birthreqclass = 'required';
	$birthreqinpclass = ' required-entry';
}
$birthfield = str_replace('{GSSE_INCL_BIRTHREQSTAR}',$birthreqstar,$birthfield);
$birthfield = str_replace('{GSSE_INCL_BIRTHREQCLASS}',$birthreqclass,$birthfield);
$birthfield = str_replace('{GSSE_INCL_BIRTHREQINPCLASS}',$birthreqinpclass,$birthfield);
} else {
	$birthfield = '';
}
$buy2html = str_replace('{GSSE_INKL_BIRTHFIELD}', $birthfield, $buy2html);
/*End Birthdate field*/

/* Fax field*/
if($this->get_setting('cb_fax_Checked') == 'True'){
$faxfield = $this->gs_file_get_contents('template/faxfield.html');
$faxTags = $this->get_tags_ret($faxfield);
$faxfield = $this->parse_texts($faxTags,$faxfield);
$faxreqstar = '';
$faxreqclass = '';
$faxreqinpclass = '';
if($this->get_setting('cb_mandatoryfieldFax_Checked') == 'True')
{
	$faxreqstar = '*';
	$faxreqclass = 'required';
	$faxreqinpclass = ' required-entry';
}
$faxfield = str_replace('{GSSE_INCL_FAXREQSTAR}',$faxreqstar,$faxfield);
$faxfield = str_replace('{GSSE_INCL_FAXREQCLASS}',$faxreqclass,$faxfield);
$faxfield = str_replace('{GSSE_INCL_FAXREQINPCLASS}',$faxreqinpclass,$faxfield);
} else {
	$faxfield = '';
}
$buy2html = str_replace('{GSSE_INKL_FAXFIELD}', $faxfield, $buy2html);
/*End Fax field*/

/* Mobil field*/
if($this->get_setting('cb_mobil_Checked') == 'True'){
$mobilfield = $this->gs_file_get_contents('template/mobilfield.html');
$mobilTags = $this->get_tags_ret($mobilfield);
$mobilfield = $this->parse_texts($mobilTags,$mobilfield);
$mobilreqstar = '';
$mobilreqclass = '';
$mobilreqinpclass = '';
if($this->get_setting('cb_mandatoryfieldMobil_Checked') == 'True')
{
	$mobilreqstar = '*';
	$mobilreqclass = 'required';
	$mobilreqinpclass = ' required-entry';
}
$mobilfield = str_replace('{GSSE_INCL_MOBILREQSTAR}',$mobilreqstar,$mobilfield);
$mobilfield = str_replace('{GSSE_INCL_MOBILREQCLASS}',$mobilreqclass,$mobilfield);
$mobilfield = str_replace('{GSSE_INCL_MOBILREQINPCLASS}',$mobilreqinpclass,$mobilfield);
} else {
	$mobilfield = '';
}
$buy2html = str_replace('{GSSE_INKL_MOBILFIELD}', $mobilfield, $buy2html);
/*End Mobil field*/

/* Message field*/
if($this->get_setting('cb_nextMessage_Checked') == 'True'){
$messagefield = $this->gs_file_get_contents('template/messagefield.html');
$messageTags = $this->get_tags_ret($messagefield);
$messagefield = $this->parse_texts($messageTags,$messagefield);
$messagereqstar = '';
$messagereqclass = '';
$messagereqinpclass = '';
if($this->get_setting('cb_mandatoryfieldNextMessage_Checked') == 'True')
{
	$messagereqstar = '*';
	$messagereqclass = 'required';
	$messagereqinpclass = ' required-entry';
}
$messagefield = str_replace('{GSSE_INCL_MESSAGEREQSTAR}',$messagereqstar,$messagefield);
$messagefield = str_replace('{GSSE_INCL_MESSAGEREQCLASS}',$messagereqclass,$messagefield);
$messagefield = str_replace('{GSSE_INCL_MESSAGEREQINPCLASS}',$messagereqinpclass,$messagefield);
} else {
	$messagefield = '';
}
$buy2html = str_replace('{GSSE_INKL_MESSAGEFIELD}', $messagefield, $buy2html);
/*End Message field*/

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
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$sel = ($_SESSION['login']['cusDeliverTitle'] == $cur_val) ? 'selected' : '';
		}
		else
		{
			$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldShippingFormToAddress|LangTagMr}';
		}
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
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$sel = ($_SESSION['login']['cusDeliverTitle'] == $cur_val) ? 'selected' : '';
		}
		else
		{
			$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldShippingFormToAddress|LangTagMrs}';
		}
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
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
		}
		else
		{
			$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|text}';
		}
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
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$sel = ($_SESSION['login']['cusEMailFormat'] == $cur_val) ? 'selected' : '';
		}
		else
		{
			$sel = '{GSSE_COOKIE_SEL|LangTagFNFieldEmailFormat|html}';
		}
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
if(isset($_SESSION['login']))
{
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
}
else
{
	$buy2html = $this->parse_cookies($buy2html);
}
/*End Cookies*/
$buyhtml = str_replace('{GSSE_FUNC_BUY2}', $buy2html, $buyhtml);
//$this->content = str_replace('{GSSE_FUNC_BUY2}', $buy2html, $this->content);
// End {GSSE_FUNC_BUY2}

// Begin {GSSE_FUNC_BUY3HIDDENFIELDS}
$buy3hidden = $this->gs_file_get_contents('template/buy3hiddenfields.html');
$aHITags = $this->get_tags_ret($buy3hidden);
$buy3hidden = $this->parse_texts($aHITags,$buy3hidden,1);
$buy3hidden = $this->set_values($aHITags,$buy3hidden);

$useAttach = ($this->get_setting('cbUseMailClientAttachment_Checked') == 'True') ? '1' : '0';
$buy3hidden = str_replace('{GSSE_INCL_USEATTACH}', $useAttach, $buy3hidden);

if(!isset($_SESSION['pid']))
{
	$pid = date('dmYH').mt_rand(1000,9999);
	$_SESSION['pid'] = $pid;
}
$buy3hidden = str_replace('{GSSE_INCL_PID}', $_SESSION['pid'], $buy3hidden);

/*$absurl = $this->get_setting('edAbsoluteShopPath_Text');
//A TS 13.08.2015: Letztes Zeichen muss ein slash sein!
if(substr($absurl, -1, 1) != '/') { $absurl = $absurl . "/"; }
//E TS 13.08.2015: Letztes Zeichen muss ein slash sein!
if(stripos(__FILE__, 'testshop') !== false)
{
	$absurl = $absurl . 'testshop/';
}*/
$absurl = $this->shopurl;

$buy3hidden = str_replace('{GSSE_INCL_REDIRECT}', $absurl, $buy3hidden);

$downloaddir = '';
$downloadtxt = '';
if(isset($_SESSION['login']))
{
	if($_SESSION['login']['ok'])
	{
		$downloaddir = 'customer_' . $_SESSION['login']['cusIdNo'];
		$downloadtxt = $this->get_lngtext('LangTagLongTextDownload');
	}
}
$buy3hidden = str_replace('{GSSE_INCL_DOWNLOADDIR}', $downloaddir, $buy3hidden);
$buy3hidden = str_replace('{GSSE_INCL_DOWNLOADTXT}', $downloadtxt, $buy3hidden);

//A TS 12.09.2014: Payment-Service Infos
//PayPal
$pp_state = '';
if(isset($_GET['pp_status'])) { $pp_state = $_GET['pp_status']; }
$buy3hidden = str_replace('{GSSE_INCL_PPSTATE}', $pp_state, $buy3hidden);
$pp_token = '';
if(isset($_GET['token'])) { $pp_token = $_GET['token']; }
$buy3hidden = str_replace('{GSSE_INCL_PPTOKEN}', $pp_token, $buy3hidden);
//E TS

//A SM 14.10.2015: Payment-Service Infos
//Saferpay
$sp_state = '';
if(isset($_GET['sp_status'])) { $sp_state = $_GET['sp_status']; }
$buy3hidden = str_replace('{GSSE_INCL_SPSTATE}', $sp_state, $buy3hidden);
//E SM

// End {GSSE_FUNC_BUY3HIDDENFIELDS}

// Begin basket2.inc.php
$basket2_is_loc = 1;
require_once('inc/basket2.inc.php');
$buybasket = str_replace('{GSSE_FUNC_BASKET2}',$baskethtml,$buybasket);
// End basket2.inc.php

// Begin buy3userdata
$buy3userdatahtml = $this->gs_file_get_contents('template/buy3userdata.html');
$aBuy3UTags = $this->get_tags_ret($buy3userdatahtml);
$buy3userdatahtml = $this->parse_texts($aBuy3UTags,$buy3userdatahtml);

if(isset($_SESSION['login']))
{
	if($_SESSION['login']['ok'])
	{
		if($_SESSION['login']['cusCustomerNews'] == 0)
		{
			// Sie bekommen keine Newsletter
			$newsletter = $this->get_lngtext('LangTagNoNewsletter');
		}
		else
		{
			// Sie sind registriert für den Newsletter Empfang
			$newsletter = $this->get_lngtext('LangTagYesNewsletter');
		}
		$kontaktlink = $this->gs_file_get_contents('template/link.html');
		$kontaktlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=addressdata_login', $kontaktlink);
		$kontaktlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $kontaktlink);
		$kontaktlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $kontaktlink);
		$kontaktlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagTextChgCData'), $kontaktlink);
		$buy3userdatahtml = str_replace('{GSSE_INCL_KONTAKTLINK}', $kontaktlink, $buy3userdatahtml);
	
		$passlink = $this->gs_file_get_contents('template/link.html');
		$passlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=password_popup', $passlink);
		$passlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $passlink);
		$passlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $passlink);
		$passlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagTextChangePassword'), $passlink);
		$buy3userdatahtml = str_replace('{GSSE_INCL_PASSLINK}', $passlink, $buy3userdatahtml);	

		$newsletterlink = $this->gs_file_get_contents('template/link.html');
		$newsletterlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=emailform', $newsletterlink);
		$newsletterlink = str_replace('{GSSE_INCL_LINKCLASS}', '', $newsletterlink);
		$newsletterlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $newsletterlink);
		$newsletterlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagEdit'), $newsletterlink);
		$buy3userdatahtml = str_replace('{GSSE_INCL_NEWSLETTERLINK}', $newsletterlink, $buy3userdatahtml);
		
		$billaddrlink = $this->gs_file_get_contents('template/link.html');
		$billaddrlink = str_replace('{GSSE_INCL_LINKURL}', 'index.php?page=addressdata_login', $billaddrlink);
		$billaddrlink = str_replace('{GSSE_INCL_LINKCLASS}', 'edit', $billaddrlink);
		$billaddrlink = str_replace('{GSSE_INCL_LINKTARGET}', '', $billaddrlink);
		$billaddrlink = str_replace('{GSSE_INCL_LINKNAME}', $this->get_lngtext('LangTagEdit'), $billaddrlink);
		$buy3userdatahtml = str_replace('{GSSE_INCL_BILLADDRLINK}', $billaddrlink, $buy3userdatahtml);
		//TS 22.07.2016: Wenn grundsätzlich keine Lieferanschrift erlaubt ist, dann auch den Link nicht anbieten
		if($this->get_setting('cbAllowShippingAddress_Checked') == 'True') {
			$buy3userdatahtml = str_replace('{GSSE_INCL_SHIPADDRLINK}', $billaddrlink, $buy3userdatahtml);
		} else {
			$buy3userdatahtml = str_replace('{GSSE_INCL_SHIPADDRLINK}', '', $buy3userdatahtml);
		}
	}
	else
	{
		// Nur registrierte Kunden können die Newsletter empfangen
		$newsletter = $this->get_lngtext('LangTagNewsletterForRegCustomer');
		$buy3userdatahtml = str_replace('{GSSE_INCL_KONTAKTLINK}', '', $buy3userdatahtml);
		$buy3userdatahtml = str_replace('{GSSE_INCL_PASSLINK}', '', $buy3userdatahtml);
		$buy3userdatahtml = str_replace('{GSSE_INCL_NEWSLETTERLINK}', '', $buy3userdatahtml);
		$buy3userdatahtml = str_replace('{GSSE_INCL_BILLADDRLINK}', '', $buy3userdatahtml);
		$buy3userdatahtml = str_replace('{GSSE_INCL_SHIPADDRLINK}', '', $buy3userdatahtml);
	}
}
else
{
	// Nur registrierte Kunden können die Newsletter empfangen
	$newsletter = $this->get_lngtext('LangTagNewsletterForRegCustomer');
	$buy3userdatahtml = str_replace('{GSSE_INCL_KONTAKTLINK}', '', $buy3userdatahtml);
	$buy3userdatahtml = str_replace('{GSSE_INCL_PASSLINK}', '', $buy3userdatahtml);
	$buy3userdatahtml = str_replace('{GSSE_INCL_NEWSLETTERLINK}', '', $buy3userdatahtml);
	$buy3userdatahtml = str_replace('{GSSE_INCL_BILLADDRLINK}', '', $buy3userdatahtml);
	$buy3userdatahtml = str_replace('{GSSE_INCL_SHIPADDRLINK}', '', $buy3userdatahtml);
}
$buy3userdatahtml = str_replace('{GSSE_INCL_NEWSLETTER}', $newsletter, $buy3userdatahtml);

$buyhtml = str_replace('{GSSE_FUNC_BUY3USERDATA}', $buy3userdatahtml, $buyhtml);
// End buy3userdata

$buyhtml = str_replace('{GSSE_FUNC_BUY3HIDDENFIELDS}', $buy3hidden, $buyhtml);
$buyhtml = str_replace('{GSSE_FUNC_BUY2BASKET}', $buybasket, $buyhtml);

// Begin Sepa
    $sepamandat = $this->gs_file_get_contents($this->absurl . 'template/sepamandat.html');
    $sepaTags = $this->get_tags_ret($sepamandat);
    $sepamandat = $this->parse_texts($sepaTags,$sepamandat);
    if($rentals) {
        $sepamandat = str_replace('{GSSE_INCL_LangTagCreditorAccept}',$this->get_lngtext('LangTagCreditorAcceptMultiple'), $sepamandat);
        $sepamandat = str_replace('{GSSE_INCL_SEPAACCEPT}','<br/><br/><h2>'.$this->get_lngtext('LangTagSepaMandat').'</h2><br/><br/>'.$this->get_lngtext('LangTagCreditorAcceptMultiple').'<br/><br/>'.$this->get_lngtext('LangTagLetterAutomatic').'<br/><br/>'.$this->get_lngtext('LangTagSepaMandatIssue'), $sepamandat);
    } else {    
        $sepamandat = str_replace('{GSSE_INCL_LangTagCreditorAccept}',$this->get_lngtext('LangTagCreditorAccept'), $sepamandat);
        $sepamandat = str_replace('{GSSE_INCL_SEPAACCEPT}','<br/><br/><h2>'.$this->get_lngtext('LangTagSepaMandat').'</h2><br/><br/>'.$this->get_lngtext('LangTagCreditorAccept').'<br/><br/>'.$this->get_lngtext('LangTagLetterAutomatic').'<br/><br/>'.$this->get_lngtext('LangTagSepaMandatIssue'), $sepamandat);
    }    
    $sepamandat = str_replace('{GSSE_INCL_CREDITOR}', $this->get_setting('edShopCompany_Text'), $sepamandat);
    $sepamandat = str_replace('{GSSE_INCL_CREDITORNUMBER}', $this->get_setting('edCreditorIdentifier_Text'), $sepamandat);
    $sepamandat = str_replace('{GSSE_INCL_CITYDATEFIRM}', $this->get_setting('edShopCity_Text').', '.date('d.m.Y').' {GSSE_INCL_ACCOUNTHOLDER1}', $sepamandat); 
    $buyhtml = str_replace('{GSSE_FUNC_SEPAMANDAT}', $sepamandat, $buyhtml);
// End Sepa
//A TS 30.11.2015: Validierungssektion
$valhtml = $this->gs_file_get_contents($this->absurl . 'template/emailvalidate.html');
$aValTags = $this->get_tags_ret($valhtml);
$valhtml = $this->parse_texts($aValTags,$valhtml);
if(!isset($_SESSION['valcode'])) {
	$_SESSION['valcode'] = $sl->getRandomCustomerPassword();
	$_COOKIE['valcode'] = $_SESSION['valcode'];
}
$_SESSION['validated'] = false;
if(isset($_SESSION['login'])) {
	if($_SESSION['login']['ok']) {
		$valhtml = '';
		$_SESSION['valcode'] = '';
		$_COOKIE['valcode'] = '';
		$_SESSION['validated'] = true;
	}
}
$buyhtml = str_replace('{GSSE_FUNC_EMAILVALIDATE}', $valhtml, $buyhtml);
//E TS 30.11.2015: Validierungssektion

//A TS 11.11.2016: PayPal plus Integration
$buypaymentpaypalplus = '';
if($this->get_setting('rbUsePPPlus_Checked') == 'True') {
	$buypaymentpaypalplus = $this->gs_file_get_contents('template/buypaymentpaypalplus.html');
}
$buyhtml = str_replace('{GSSE_INCL_BUYPAYMENTPAYPALPLUS}', $buypaymentpaypalplus, $buyhtml);
//E TS 11.11.2016: PayPal plus Integration

$this->content = str_replace('{GSSE_FUNC_CHECKOUT}', $buyhtml, $this->content);

if(isset($_GET['su_status']))
{
	if($_GET['su_status'] == 'ok') {
		$this->content = str_replace('{GSSE_START}', '<script type="text/javascript">window.onload = function(){
		document.getElementById("gs_order_cont_button").click();
		};</script>', $this->content);
	} else {
		$this->content = str_replace('{GSSE_START}', '', $this->content);
	}
}
else
{
	$this->content = str_replace('{GSSE_START}', '', $this->content);
}
?>