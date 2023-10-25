<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$pgitems = '';
$pgroup = 0;
$feathtml = '';

if($this->get_setting('cbProductsOnMainPage_Checked') == 'True') {
	$feathtml = $this->gs_file_get_contents($this->absurl . 'template/featureditems.html');
	if(isset($aParam[1])) {
		switch($aParam[1]) {
			case 'feat':
				$pgroup = -1;
				break;
			case 'new':
				$pgroup = -2;
				break;
			default:
				$pgroup = -1;
				break;
		}
	} else {
		$pgroup = -1;
	}
	
	if($pgroup != 0) {
		$pgitems = '<script type="text/javascript">' . $this->crlf .
					  'load_pgitemsnew(' . $pgroup . ',"");' . $this->crlf .
					  '</script>';
	}
	$feathtml = str_replace('{GSSE_INCL_FEATITEMSHEADLINE}',$this->get_setting('edFeaturedItemsHeadLine_Text'), $feathtml);
	$feathtml = str_replace('{GSSE_INCL_FEATSCRIPT}', $pgitems, $feathtml);
}
$this->content = str_replace($tag, $feathtml, $this->content);
?>