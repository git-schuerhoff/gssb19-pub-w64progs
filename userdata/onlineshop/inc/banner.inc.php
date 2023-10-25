<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if($this->edition == 13) {
	$settingName = "cbUseBanner" . $aParam[1] . "_Checked";
	$tmplFile = "banner" . $aParam[1] . ".html";
	include('parse_func.inc.php');
} else {
	$this->content = str_replace($tag, '', $this->content);
}
?>