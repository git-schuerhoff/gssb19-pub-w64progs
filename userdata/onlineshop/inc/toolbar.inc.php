<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if($aParam[1] == 1)
{
	$toolbar = new gs_shopengine('toolbar.html');
}
else
{
	$toolbar = new gs_shopengine('toolbar_bottom.html');
}
$this->content = str_replace($tag, $toolbar->parse_inc(), $this->content);
?>