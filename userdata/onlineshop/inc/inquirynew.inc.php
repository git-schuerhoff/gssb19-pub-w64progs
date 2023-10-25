<?php

$inquiry = '';
if($_SESSION['aitem']['itemHasInquiry'] == 'Y')
{
	$inquiry = $this->gs_file_get_contents($this->absurl . 'template/inquirynew.html');
	$inquiry = str_replace('{GSSE_SURL_}',$this->absurl,$inquiry);
	$inquiry = str_replace('{GSSE_LANG_LangTagInquiry}',$this->get_lngtext('LangTagInquiry'),$inquiry);
}

$this->content = str_replace($tag,$inquiry,$this->content);
?>