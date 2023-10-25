<?php
$b2bhtml = $this->gs_file_get_contents("template/link.html");
//$b2bhtml = str_replace('',,$b2bhtml);
$b2bhtml = str_replace('{GSSE_INCL_LINKCLASS}','startbutton',$b2bhtml);
$b2bhtml = str_replace('{GSSE_INCL_LINKURL}','index.php?page=main',$b2bhtml);
$b2bhtml = str_replace('{GSSE_INCL_LINKTARGET}','_self',$b2bhtml);
$b2bhtml = str_replace('{GSSE_INCL_LINKNAME}','Start',$b2bhtml);

if($this->get_setting('cbUsePhpB2BLogin_Checked') == 'True')
{
	if(isset($_SESSION['login']))
	{
		if(!$_SESSION['login']['ok'])
		{
			$b2bhtml = $this->gs_file_get_contents("template/b2blogin.html");
			$aB2BTags = $this->get_tags_ret($b2bhtml);
			$b2bhtml = $this->parse_texts($aB2BTags,$b2bhtml);
		}
	}
	else
	{
		$b2bhtml = $this->gs_file_get_contents("template/b2blogin.html");
		$aB2BTags = $this->get_tags_ret($b2bhtml);
		$b2bhtml = $this->parse_texts($aB2BTags,$b2bhtml);
	}
}
$this->content = str_replace($tag, $b2bhtml, $this->content);
?>