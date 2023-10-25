<?php
session_start();
$manuhtml = '';
if($this->phpactive() === true)
{
	if($this->get_setting('cbUsePhpManufacturerList_Checked') == 'True')
	{
		$itdbh = $this->db_connect();
		$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
				 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
				 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
				 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
				 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
				 "itemCheckAge, itemMustAge, itemIsAction " .
				 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemManufacturer = '" . $_POST['manu'] . "' AND itemLanguageId = '" . $this->lngID . "'";
		$erg = mysqli_query($itdbh,$sql);
		$num = mysqli_num_rows($erg);
		$this->content = str_replace('{GSSE_INCL_MANUNAME}', $_POST['manu'], $this->content);
		$this->content = str_replace('{GSSE_INCL_NUMANU}', $num, $this->content);
		if(mysqli_errno($itdbh) == 0)
		{
			if(mysqli_num_rows($erg) > 0)
			{
				$delbutton = '';
				include('inc/items_overview.inc.php');
				$manuhtml .= str_replace('{GSSE_INCL_ITEMSOVERVIEWLINES}',$this_inner,$outer);
			}
			mysqli_free_result($erg);
		}
	}
}


$this->content = str_replace($tag, $manuhtml, $this->content);
?>