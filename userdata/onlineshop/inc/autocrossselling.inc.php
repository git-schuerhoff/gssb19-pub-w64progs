<?php
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
	$sl = new shoplog();
}
else
{
	die("Class shoplog missing!");
}

$auto = '';

if($this->get_setting('cbAutoCrossSelling_Checked') == 'True')
{
	/*Begin Upsselling*/
	$au = $sl->getAutoCrossSellingList($_SESSION['aitem']['itemItemNumber'], $num);
	if(count($au) > 0)
	{
		$auto = $this->gs_file_get_contents($this->absurl . 'template/crossup_outer.html');
		$item_box = $this->gs_file_get_contents($this->absurl . 'template/crossup_item.html');
		$auto = str_replace('{GSSE_INCL_CROSSUPHEAD}',$this->get_lngtext('LangTagAutoCrossSelling'),$auto);
		$auto = str_replace('{GSSE_INCL_BLOCKID}','block_autoselling_products',$auto);
		$c = 0;
		foreach($au as $aItems)
		{
			if($c == 0)
			{
				$csItemIds = '"' . $aItems[0]->itemItemId . '"';
			}
			else
			{
				$csItemIds .= ',"' . $aItems[0]->itemItemId . '"';
			}
			$c++;
		}
		$csdbh = $this->db_connect();
		$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
				 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
				 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
				 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
				 "itemInStockQuantity, itemAvailabilityId " .
				 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemId IN (" . $csItemIds . ")";
		$erg = mysqli_query($csdbh,$sql);
		if(mysqli_errno($csdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				include('./inc/fill_items_box.inc.php');
				$auto = str_replace('{GSSE_INCL_CROSSUPITEMS}',$all_items,$auto);
			}
		}
		mysqli_free_result($erg);
		unset($erg);	
	}
}
/*End Autocrossselling*/

$this->content = str_replace($tag,$auto,$this->content);
?>