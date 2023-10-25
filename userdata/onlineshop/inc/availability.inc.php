<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$avail = '';
if($this->phpactive())
{
	if($this->get_setting('cbUsePhpAvailability_Checked') == 'True')
	{
		$avail = $this->get_availability($_SESSION['aitem']['itemInStockQuantity'],$_SESSION['aitem']['itemAvailabilityId'],1);
		if($this->get_setting('cbUsePhpAvailMail_Checked') == 'True')
		{
			//Abfragen, ob der Lagerbestand des Artikels im Bereich des Verfügbarkeitsstatus "Artikel wird bestellt"
			//liegt, wenn ja Mail-Formular anzeigen. Auf die Status-ID ist kein Verlass
			if(($this->item_mustbeordered($_SESSION['aitem']['itemInStockQuantity']) == $_SESSION['aitem']['curAvaId']))
			{
				$avmail = $this->gs_file_get_contents($this->absurl . 'template/availmailbox.html');
				$avmail = $this->parse_texts($this->get_tags_ret($avmail),$avmail);
				//$avmail = str_replace('{GSSE_INCL_ITEMLNK}', $this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=detail&idx=' . $_SESSION['aitem']['itemItemId'], $avmail);
				$avmail = str_replace('{GSSE_INCL_ITEMLNK}', $this->shopurl . 'index.php?page=detail&idx=' . $_SESSION['aitem']['itemItemId'], $avmail);
				$avcont = urlencode($_SESSION['aitem']['itemItemNumber'] . ' ' . $_SESSION['aitem']['itemItemDescription']);
				//$_SESSION['aitem']['itemItemId'];
				$avmail = str_replace('{GSSE_INCL_ITEM}',$avcont,$avmail);
				$avail .= $avmail;
			}
		}
	}
}
$this->content = str_replace($tag, $avail, $this->content);
?>
