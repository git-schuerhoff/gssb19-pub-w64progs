<?php
$shopinfo = file_get_contents('template/shopinfo.html');
$option = file_get_contents('template/mainmenu_item.html');
$all_items = '';

$shopinfo = str_replace('{GSSE_LANG_LangTagShopInfoTitle}',$this->get_lngtext('LangTagShopInfoTitle'),$shopinfo);

$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=cond',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagShipment'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagShipment'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=privacy',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagPrivacy'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagPrivacy'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=conditions',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagTermsAndCond'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagTermsAndCond'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=paymentinfo',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagPaymentInfo'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagPaymentInfo'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=right_of_revocation',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagRightOfRevocation'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagRightOfRevocation'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=modelwithdrawalform',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagModelWithdrawalForm'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagModelWithdrawalForm'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
$cur_item = $option;
$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=imprint',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagImprint'),$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagImprint'),$cur_item);
$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
$all_items .= $cur_item;
if($this->phpactive())
{
	if($this->get_setting('cbUsePhpShopcomments_Checked') == 'True')
	{
		$cur_item = $option;
		$cur_item = str_replace('{GSSE_INCL_MMURL}',$this->absurl . 'index.php?page=gs_shopcomments',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_MMNAVIDX}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_MMTITLE}',$this->get_lngtext('LangTagShopComments'),$cur_item);
		$cur_item = str_replace('{GSSE_INCL_MMFULLTITLE}',$this->get_lngtext('LangTagShopComments'),$cur_item);
		$cur_item = str_replace('{GSSE_SURL_}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_MMIMGFILE}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SUBMENU}','',$cur_item);
		$all_items .= $cur_item;
	}
}

$shopinfo = str_replace('{GSSE_INCL_SHOPINFOITEMS}',$all_items,$shopinfo);

$this->content = str_replace($tag,$shopinfo,$this->content);
?>