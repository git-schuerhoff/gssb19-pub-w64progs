<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$pgroupinfo = '';
if(isset($_GET['idx'])) {
	$aPGInfo = $this->get_pglanguageinfo($_GET['idx'],$this->lngID);
	$numPGInfo = count($aPGInfo);
	if($numPGInfo > 0) {
		$pgroupinfo = file_get_contents($this->absurl . 'template/pgroupinfo.html');
		$pgroupinfo = str_replace('{GSSE_INCL_PGROUPNAME}',$aPGInfo[0]['productgroup'],$pgroupinfo);
		$pgroupinfo = str_replace('{GSSE_INCL_PGROUPDESC}',$aPGInfo[0]['grouphint'],$pgroupinfo);
	}
}
$this->content = str_replace($tag, $pgroupinfo, $this->content);
?>