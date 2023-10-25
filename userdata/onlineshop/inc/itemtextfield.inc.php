<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$itfhtml = '';

if($_SESSION['aitem']['itemIsTextInput'] == 'Y')
{
	$itfhtml = $this->gs_file_get_contents('template/itemtextfield.html');
	$itfhtml = str_replace('{GSSE_LANG_LangTagYourTextField}',$this->get_lngtext('LangTagYourTextField'),$itfhtml);
}

$this->content = str_replace($tag, $itfhtml, $this->content);
?>
