<?php
if(isset($settingName) && $settingName != "")
{
	$res = $this->get_setting($settingName);
	if($res == 'True')
	{
		$bn = new gs_shopengine($tmplFile);
		$this->content = str_replace($tag, $bn->parse_inc(), $this->content);
	}
	else
	{
		$this->content = str_replace($tag, '', $this->content);
	}
}
else
{
	$bn = new gs_shopengine($tmplFile);
	$this->content = str_replace($tag, $bn->parse_inc(), $this->content);
}

?>