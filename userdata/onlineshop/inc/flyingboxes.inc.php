<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "flyingboxes.html";
$fly = new gs_shopengine($tmplFile);
$this->content = str_replace($tag, $fly->parse_inc(), $this->content);
?>