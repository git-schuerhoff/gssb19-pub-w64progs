<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tmplFile = "itembox.html";
$res = $this->get_setting('cbProductsOnMainPage_Checked');
$html = '';
if($res == 'True')
{
	$idbh = $this->db_connect();
	$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
			 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS ItemPrice, " .
			 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
			 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
			 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
			 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal " .
			 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemIsOnIndexPage = 'Y' AND itemLanguageId = '" . $this->lngID . "'";
	$erg = mysqli_query($idbh,$sql);
	if(mysqli_errno($idbh) == 0)
	{
		if(mysqli_num_rows($erg) > 0)
		{
			include('inc/items_boxed.inc.php');
			$html = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
		}		
	}
	else
	{
		$html = mysqli_error($idbh) . ":<br />" . $sql;
	}
	mysqli_free_result($erg);
	//mysqli_close($idbh);
}

$this->content = str_replace($tag, $html, $this->content);

