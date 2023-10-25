<?php

$questonprod = '';
if($this->get_setting('cbQuestOnProduct_Checked') == 'True')
{
	$questonprod = file_get_contents($this->absurl . 'template/questonprod.html');
	$questonprod = str_replace('{GSSE_SURL_}',$this->absurl,$questonprod);
	$questonprod = str_replace('{GSSE_LANG_LangTagQuestToProduct}',$this->get_lngtext('LangTagQuestToProduct'),$questonprod);
}
$this->content = str_replace($tag,$questonprod,$this->content);
?>