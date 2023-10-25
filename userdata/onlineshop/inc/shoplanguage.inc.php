<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$shoplng = '';
$aLangs = $this->get_registered_langs();
$flaghtml = file_get_contents("template/language_flag.html");
$lngmax = count($aLangs);
if($lngmax > 1)
{
	for($f = 0; $f < $lngmax; $f++)
	{
		$cur_flag = $flaghtml;
		$cur_flag = str_replace('{GSSE_INCL_SLC}',$aLangs[$f]['slc'],$cur_flag);
		$cur_flag = str_replace('{GSSE_INCL_CNT}',$aLangs[$f]['cnt'],$cur_flag);
		$shoplng .= $cur_flag;
	}
}
$this->content = str_replace($tag, $shoplng, $this->content);
?>