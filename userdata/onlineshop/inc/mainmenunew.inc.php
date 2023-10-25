<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$mmhtml = '';
if(isset($_GET['page'])) {
	if($_GET['page'] != 'basket' && $_GET['page'] != 'buy') {
		$mmhtml = $this->gs_file_get_contents('mainmenu_' . $this->lngID . '.html');
	}
} else {
	$mmhtml = $this->gs_file_get_contents('mainmenu_' . $this->lngID . '.html');
}
$this->content = str_replace($tag, $mmhtml, $this->content);
?>