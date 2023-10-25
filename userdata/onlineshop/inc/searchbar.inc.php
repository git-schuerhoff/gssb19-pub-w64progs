<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "searchbar.html";
$sbar = new gs_shopengine($tmplFile);
$this->content = str_replace($tag, $sbar->parse_inc(), $this->content);
?>