<?php
//session_start();
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$buybasket = $this->gs_file_get_contents('template/buy2basket.html');
$aB2Tags = $this->get_tags_ret($buybasket);
$buybasket = $this->parse_texts($aB2Tags,$buybasket);
$vatincl = ($this->get_setting('cbNetPrice_Checked') == 'False') ? 1 : 0;
$showvat = ($this->get_setting('cbShowVAT_Checked') == 'True') ? 1 : 0;

$vattext = '';
if($showvat == 1)
{
	$vattext = $this->get_lngtext('LangTagTextVAT');
	if($this->get_setting('cbNetPrice_Checked') == 'False')
	{
		$vattext = $this->get_lngtext('LangTagTextEncludedVAT') . "&nbsp;" . $vattext;
	}
}
$buybasket = str_replace('{GSSE_INCL_VATTITLE}',$vattext,$buybasket);

$mkhidden = 0;
/*if($_GET['page'] == 'buy3')
{
	$mkhidden = 1;
}*/
include_once('inc/basket2.inc.php');
$buybasket = str_replace('{GSSE_FUNC_BASKET2}',$baskethtml,$buybasket);


$this->content = str_replace('{GSSE_FUNC_BUY2BASKET}', $buybasket, $this->content);
?>