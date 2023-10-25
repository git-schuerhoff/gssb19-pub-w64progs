<?php
//session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
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
if(stripos(__FILE__, 'testshop') !== false)
{
	$absurl = $absurl . '/testshop';
}*/
$absurl = $this->shopurl;

$buy3hidden = str_replace('{GSSE_INCL_REDIRECT}', $absurl, $buy3hidden);

$downloaddir = '';
$downloadtxt = '';
if(isset($_SESSION['login']['ok']))
{
	$downloaddir = 'customer_' . $_SESSION['login']['cusIdNo'];
	$downloadtxt = $this->get_lngtext('LangTagLongTextDownload');
}
$buy3hidden = str_replace('{GSSE_INCL_DOWNLOADDIR}', $downloaddir, $buy3hidden);
$buy3hidden = str_replace('{GSSE_INCL_DOWNLOADTXT}', $downloadtxt, $buy3hidden);

$this->content = str_replace('{GSSE_FUNC_BUY3HIDDENFIELDS}', $buy3hidden, $this->content);

?>