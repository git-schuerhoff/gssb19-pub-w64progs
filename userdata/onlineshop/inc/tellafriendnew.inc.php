<?php
/*Tell-a-friend*/
$tellafriend = '';
if($this->get_setting('cbTellAFriend_Checked') == 'True')
{
	$tellafriend = file_get_contents($this->absurl . 'template/tellafriend.html');
	//$tellafriend = str_replace('',,$tellafriend);
	$tellafriend = str_replace('{GSSE_SURL_}',$this->absurl,$tellafriend);
	$tellafriend = str_replace('{GSSE_LANG_LangTagTellAFriend}',$this->get_lngtext('LangTagTellAFriend'),$tellafriend);
}
$this->content = str_replace($tag,$tellafriend,$this->content);
?>