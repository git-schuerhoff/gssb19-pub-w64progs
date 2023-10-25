<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if($this->phpactive() === true)
{
	$tmplFile = "fulltextsearch.html";
	$res = $this->get_setting('cbUseXMLSearch_Checked');
	if($res == 'True')
	{
		$xmls = new gs_shopengine($tmplFile);
		$this->content = str_replace($tag, $xmls->parse_inc(), $this->content);
	}
	else
	{
		$this->content = str_replace($tag, '', $this->content);
	}
}
else
{
	$this->content = str_replace($tag, '', $this->content);
}

?>