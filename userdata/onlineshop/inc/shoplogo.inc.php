<?php
//Achtung!!! Parameter werden als Array $aParam �bergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter f�r die Funktion fangen mit $aParam[1]
$shplogo = new gs_shopengine('shoplogo.html');

$this->content = str_replace($tag, $shplogo->parse_inc(), $this->content);
?>