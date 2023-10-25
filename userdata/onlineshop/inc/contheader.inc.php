<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$ilngs = count($this->aslcs);
$nobasket = '';
if(isset($aParam[1])) {
	if($aParam[1] == 'nobasket') {
		$nobasket = '-nobasket';
	}
}

if($ilngs > 1) {
	$cnthead = new gs_shopengine('contheader_lng'.$nobasket.'.html');
} else {
	$cnthead = new gs_shopengine('contheader'.$nobasket.'.html');
}

$this->content = str_replace($tag, $cnthead->parse_inc(), $this->content);
?>