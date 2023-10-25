<?php

$findcheaper = '';
if($this->get_setting('cbFindCheaper_Checked') == 'True')
{
	$findcheaper = $this->gs_file_get_contents($this->absurl . 'template/findcheaper.html');
	//$tellafriend = str_replace('',,$tellafriend);
	$findcheaper = str_replace('{GSSE_SURL_}',$this->absurl,$findcheaper);
	$findcheaper = str_replace('{GSSE_LANG_LangTagFindCheaper}',$this->get_lngtext('LangTagFindCheaper'),$findcheaper);
}
$this->content = str_replace($tag,$findcheaper,$this->content);
?>