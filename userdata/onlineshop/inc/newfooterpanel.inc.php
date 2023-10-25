<?php
//Achtung!!! Parameter werden als Array $aParam �bergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter f�r die Funktion fangen mit $aParam[1]
$footerpanel = '';
if($this->phpactive())
{
	$tmplFile = 'footerpanel.html';
	$fp = new gs_shopengine($tmplFile);
	$footerpanel = $fp->parse_inc();
	
	$phone = trim($this->get_setting('edShopTelephone_Text'));
	$phonecl = 'no-display';
	if($phone != '')
	{
		$phonecl = '';
	}
	$footerpanel = str_replace('{GSSE_INCL_CLFPPHONE}', $phonecl, $footerpanel);
	$footerpanel = str_replace('{GSSE_INCL_FPPHONE}', $phone, $footerpanel);
	
	$mobil = trim($this->get_setting('edShopMobile_Text'));
	$mobilcl = 'no-display';
	if($mobil != '')
	{
		$mobilcl = '';
	}
	$footerpanel = str_replace('{GSSE_INCL_CLFPMOBIL}', $mobilcl, $footerpanel);
	$footerpanel = str_replace('{GSSE_INCL_FPMOBIL}', $mobil, $footerpanel);
	
	$faxno = trim($this->get_setting('edShopFax_Text'));
	$faxnocl = 'no-display';
	if($faxno != '')
	{
		$faxnocl = '';
	}
	$footerpanel = str_replace('{GSSE_INCL_CLFPFAXNO}', $faxnocl, $footerpanel);
	$footerpanel = str_replace('{GSSE_INCL_FPFAXNO}', $faxno, $footerpanel);
}
$this->content = str_replace($tag, $footerpanel, $this->content);
?>