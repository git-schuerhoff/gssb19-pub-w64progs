<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$shipmpaym = file_get_contents('template/shipmpaym_outer.html');
$shipmpaymitem = file_get_contents('template/shipmpaym_item.html');


$allitems = '';

/*Begin DHL*/
if($this->get_shipmpaym('dhl',1) === true)
{
	$sphtml = $shipmpaymitem;
	$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMURL}','http://www.dhl.com',$sphtml);
	$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMNAME}','dhl',$sphtml);
	$allitems .= $sphtml;
}
/*End DHL*/

/*Begin PayPal*/
if($this->get_setting('cbUsePayPal_Checked') == 'True')
{
	if($this->get_shipmpaym('paypal',0) === true)
	{
		$sphtml = $shipmpaymitem;
		$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMURL}','http://www.paypal.com',$sphtml);
		$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMNAME}','paypal',$sphtml);
		$allitems .= $sphtml;
	}
}
/*End PayPal*/

/*Begin Other*/
$sphtml = $shipmpaymitem;
$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMURL}','#',$sphtml);
$sphtml = str_replace('{GSSE_INCL_SHIPMPAYMNAME}','other',$sphtml);
$allitems .= $sphtml;
/*End Other*/

$shipmpaym = str_replace('{GSSE_INCL_SHIPMPAYMITEMS}',$allitems,$shipmpaym);


$this->content = str_replace($tag, $shipmpaym, $this->content);
?>