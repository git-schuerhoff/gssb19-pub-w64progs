<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if($this->phpactive() === true)
{
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$cus = new gs_shopengine('cusmenu.html');
			$this->content = str_replace($tag, $cus->parse_inc(), $this->content);
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
}
else
{
	$this->content = str_replace($tag, '', $this->content);
}
?>