<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$cusmenu_sm = '';
if($this->phpactive() === true)
{
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$cus = new gs_shopengine('cusmenu_sm.html');
			$cusmenu_sm = $cus->parse_inc();
		}
	}
}
$this->content = str_replace($tag, $cusmenu_sm, $this->content);
?>