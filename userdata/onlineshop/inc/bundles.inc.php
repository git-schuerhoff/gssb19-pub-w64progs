<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$bundles = '';
$gpreis = 0;
$aBundle = $this->get_bundles($_SESSION['aitem']['itemItemNumber']);
$bndmax = count($aBundle);
if($bndmax > 0)
{
	$bundles = $this->gs_file_get_contents($this->absurl . 'template/bundle_outer.html');
	$bundle_item = $this->gs_file_get_contents($this->absurl . 'template/bundle_item.html');
	$bundles = $this->parse_texts($this->get_tags_ret($bundles),$bundles);
	$all_items = '';
	for($b = 0; $b < $bndmax; $b++)
	{
		if($b == 0)
		{
			$fol = 'first ';
		}
		else
		{
			$fol = '';
		}
		$gpreis += ($aBundle[$b]['itemPrice'] * $aBundle[$b]['itemAmount']);
		$cur_item = $bundle_item;
		$detailurl = $this->absurl . 'index.php?page=detail&amp;item=' . $aBundle[$b]['itemID'] . '&amp;d=' . $aBundle[$b]['itemPage'];
		//$cur_item = str_replace('',,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_FOL}',$fol,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_EOO}',(($b % 2) == 0) ? 'odd' : 'even',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMURL}',$detailurl,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMNAME}',$aBundle[$b]['itemDescription'],$cur_item);
		/* SM 20.10.2014 - Bild online oder lokal?*/
		if(strpos($aBundle[$b]['itemPic'],"http") === false && strpos($aBundle[$b]['itemPic'],"://") === false) {
			$cur_item = str_replace('{GSSE_INCL_ITEMIMG}',$this->absurl . 'images/small/' . $aBundle[$b]['itemPic'],$cur_item);
		} else {
			$cur_item = str_replace('{GSSE_INCL_ITEMIMG}', $aBundle[$b]['itemPic'],$cur_item);
		}
		$cur_item = str_replace('{GSSE_INCL_ITEMPRICE}',$this->get_currency($aBundle[$b]['itemPrice'],0,'.'),$cur_item);
		$cur_item = str_replace('{GSSE_INCL_ITEMQTY}',$aBundle[$b]['itemAmount'],$cur_item);
		$all_items .= $cur_item;
	}
	$bundles = str_replace('{GSSE_INCL_BUNDLEITEMS}',$all_items,$bundles);
	$bundles = str_replace('{GSSE_INCL_EOO}',((($b + 1) % 2) == 0) ? 'odd' : 'even',$bundles);
	$bundles = str_replace('{GSSE_INCL_TOTALPRICE}',$this->get_currency($gpreis,0,'.'),$bundles);
}
$this->content = str_replace($tag, $bundles, $this->content);
?>
