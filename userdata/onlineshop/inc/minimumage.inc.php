<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$minimumage = '';
if($_SESSION['aitem']['itemCheckAge'] == 'Y' && $_SESSION['aitem']['itemMustAge'] > 0)
{
	$minimumage = $this->gs_file_get_contents($this->absurl . 'template/textbox.html');
	$minimumage = str_replace('{GSSE_INCL_HEADLINE}',$this->get_lngtext('LangTagAgeRequested'),$minimumage);
	$minimumage = str_replace('{GSSE_INCL_BODYTEXT}',$this->get_lngtext('LangTagMinimumAge') . ' ' . $_SESSION['aitem']['itemMustAge'] . ' ' . $this->get_lngtext('LangTagYears'),$minimumage);
}

$this->content = str_replace($tag, $minimumage, $this->content);
?>
