<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "loginbar.html";
$lbar = new gs_shopengine($tmplFile);
$this->content = str_replace($tag, $lbar->parse_inc(), $this->content);
?>