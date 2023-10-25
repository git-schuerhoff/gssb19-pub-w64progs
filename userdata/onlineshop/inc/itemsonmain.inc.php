<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "itemsonmain.html";
$res = $this->get_setting('cbProductsOnMainPage_Checked');
if($res == 'True')
{
	$ionm = new gs_shopengine($tmplFile);
	$this->content = str_replace($tag, $ionm->parse_inc(), $this->content);
}
else
{
	$this->content = str_replace($tag, '', $this->content);
}
