<?php
$shopmenu = file_get_contents('template/shopmenu_outer.html');
$shopmenuitem = file_get_contents('template/shopmenu_item.html');
$all_items = '';

$cur_item = $shopmenuitem;
$cur_item = str_replace('{GSSE_INCL_FOL}','first',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=main',$cur_item);
$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagHome'),$cur_item);
$all_items .= $cur_item;
/*
$cur_item = $shopmenuitem;
$cur_item = str_replace('{GSSE_INCL_FOL}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=basket',$cur_item);
$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagBasket'),$cur_item);
$all_items .= $cur_item;
*/

if(isset($_SESSION['desktop']))
{
	if($_SESSION['desktop']['s_width'] >= 360)
	{
		$cur_item = $shopmenuitem;
		$cur_item = str_replace('{GSSE_INCL_FOL}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=sitemap',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagSitemap'),$cur_item);
		$all_items .= $cur_item;
	}
}

/*
A TS 17.07.2015:
Wenn PHP nicht aktiv, dann ist Kontakt der letzte Menüpunkt*/
$last = '';
if($this->phpactive()) {
	//Wenn alle Optionen False sind, dann ist Kontakt der letze Punkt
	if($this->get_setting('cbUsePhpFAQ_Checked') != 'True' &&
		$this->get_setting('cbUsePhpExtendedSearch_Checked') != 'True' &&
		$this->get_setting('cbUsePhpB2BLogin_Checked') != 'True' &&
		$this->get_setting('cbUsePhpCustomerLogin_Checked') != 'True') {
			$last = 'last';
	}
} else {
	$last = 'last';
}
/*E TS 17.07.2015*/

$cur_item = $shopmenuitem;
$cur_item = str_replace('{GSSE_INCL_FOL}',$last,$cur_item);
$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=contact',$cur_item);
$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagContact'),$cur_item);
$all_items .= $cur_item;

$cur_item = $shopmenuitem;
$cur_item = str_replace('{GSSE_INCL_FOL}',$last,$cur_item);
$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=imprint',$cur_item);
$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagImprint'),$cur_item);
$all_items .= $cur_item;

if($this->phpactive())
{
	if($this->get_setting('cbUsePhpFAQ_Checked') == 'True')
	{
		$cur_item = $shopmenuitem;
		$cur_item = str_replace('{GSSE_INCL_FOL}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=faq',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagFAQs'),$cur_item);
		$all_items .= $cur_item;
	}
	if($this->get_setting('cbUsePhpExtendedSearch_Checked') == 'True')
	{
		$cur_item = $shopmenuitem;
		$cur_item = str_replace('{GSSE_INCL_FOL}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=extendedsearch',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagExtendedSearch'),$cur_item);
		$all_items .= $cur_item;
	}
	if($this->get_setting('cbUsePhpB2BLogin_Checked') == 'True' || $this->get_setting('cbUsePhpCustomerLogin_Checked') == 'True')
	{
		if(isset($_SESSION['login']))
		{
			//TS 14.07.2015: class gs-login-link zur Identifizierung des Login-Menüeintrags hinzugefügt
			//Bei alten Templates dürfte dies keine Auswirkungen haben
			if($_SESSION['login']['ok'])
			{
				$cur_item = $shopmenuitem;
				$cur_item = str_replace('{GSSE_INCL_FOL}','',$cur_item);
				$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=customerlogout',$cur_item);
				$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagTextLogout'),$cur_item);
				$all_items .= $cur_item;
			}
			else
			{
				$cur_item = $shopmenuitem;
				$cur_item = str_replace('{GSSE_INCL_FOL}','gs-login-link',$cur_item);
				$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=createcustomer',$cur_item);
				$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagTextLogin'),$cur_item);
				$all_items .= $cur_item;
			}
		}
		else
		{
			$cur_item = $shopmenuitem;
			$cur_item = str_replace('{GSSE_INCL_FOL}','gs-login-link',$cur_item);
			$cur_item = str_replace('{GSSE_INCL_SMURL}',$this->absurl . 'index.php?page=createcustomer',$cur_item);
			$cur_item = str_replace('{GSSE_INCL_LINKNAME}',$this->get_lngtext('LangTagTextLogin'),$cur_item);
			$all_items .= $cur_item;
		}
	}
}

$shopmenu = str_replace('{GSSE_INCL_SHOPMENUITEMS}',$all_items,$shopmenu);

$this->content = str_replace($tag,$shopmenu,$this->content);
?>