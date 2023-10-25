<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
if($this->get_setting('cbUseTrustedShops_Checked') == 'True') {
	$settingName = "cbUseTrustedShops_Checked";
	$tmplFile = "trustedshops.html";
	include('parse_func.inc.php');
} else {
	$this->content = str_replace($tag, '', $this->content);
}
?>