<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$itemhtml = '';
if(isset($_GET['item']) && $_GET['item'] != '')
{
	$this->get_item($_GET['item']);
}
$this->content = str_replace($tag, $itemhtml, $this->content);
?>