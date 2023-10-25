<?php
$max = mysqli_num_rows($erg);

$outer = $this->gs_file_get_contents('template/itemssimple_outer_layout.html');
$line = $this->gs_file_get_contents('template/itemssimple_line.html');
$this_inner = '';
$this_line = '';
while($z = mysqli_fetch_assoc($erg))
{
	$this_line = $line;
	$detailurl = 'index.php?page=detail&amp;item=' . $z['itemItemId'] . '&amp;d=' . $z['itemItemPage'];
	$itemname = ($z['itemHasDetail'] == 'Y') ? $this->inc_link('item_title', $detailurl, '_self', $z['itemItemDescription']) : $z['itemItemDescription'];
	$this_line = str_replace('{GSSE_INCL_ITEMNAME}',$itemname,$this_line);
	$aPrices = $this->get_prices($z['itemItemId']);
	$action = 0;
	if($z['itemIsAction'] == 'Y')
	{
		$action = $this->chk_action($z['itemItemId'],$aPrices);
	}
	
	$actionhtml = '';
	if($action == 1)
	{
		$actionhtml = $this->gs_file_get_contents('template/rabattaktion.html');
		$actionhtml = str_replace('{GSSE_INCL_CURLANG}',$this->lngID,$actionhtml);
		$actionhtml = str_replace('{GSSE_INCL_ACTIONTEXT}','',$actionhtml);
	}
	$this_line = str_replace('{GSSE_INCL_ACTION}',$actionhtml,$this_line);
	
	//Itemprice
	$this_line = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($aPrices['price'],0,'.'),$this_line);
	
	$this_inner .= $this_line;
}

?>