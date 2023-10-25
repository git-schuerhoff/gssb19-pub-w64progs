<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$comesoon = '';
if($_SESSION['aitem']['itemSoonHereFlag'] == 'Y')
{
	$comesoon = $this->gs_file_get_contents($this->absurl . 'template/textbox.html');
	$comesoon = str_replace('{GSSE_INCL_HEADLINE}',$this->get_lngtext('LangTagComingSoon'),$comesoon);
	$comesoon = str_replace('{GSSE_INCL_BODYTEXT}',$_SESSION['aitem']['itemSoonHereText'],$comesoon);
}

$this->content = str_replace($tag, $comesoon, $this->content);
?>
