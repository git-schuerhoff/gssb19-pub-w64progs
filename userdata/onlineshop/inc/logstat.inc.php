<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
//A TS 26.11.2014: Seitenaufrufeprotokollieren
if(file_exists("dynsb/class/class.shoplog.php")) {
	include_once("dynsb/class/class.shoplog.php");
}
if(isset($aParam[1]))
{
	switch($aParam[1]) {
		case "detail":
			$sl = new shoplog(2, $_SESSION['aitem']['itemItemNumber'], $_SESSION['aitem']['itemItemDescription'], "deu");
			$sl->logItemHistory(session_id(), $_SESSION['aitem']['itemItemNumber'], base64_encode($_SESSION['aitem']['itemItemDescription']), $self, $_SESSION['aitem']['itemItemDescription']['itemSmallImageFile'], $gssbItems[0]["variants"][0]["prices"][0]["price"], $gssbItems[0]["variants"][0]["Weight"], "F");
			break;
		case "shopsearch":
			//
			break;
		default:
			$sl = new shoplog(1,"", $page, $this->lngID);
			break;
	}
}
else
{
	$sl = new shoplog(1,"", $page, $this->lngID);
}
//E TS 26.11.2014
$this->content = str_replace($tag, $detailboxnew, $this->content);
?>