<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$tabbed = '';
$tabbed = file_get_contents($this->absurl . 'template/tabbedslider_main_outer.html');
$tabitem = file_get_contents($this->absurl . 'template/tabbedslider_main_item.html');
$tabbed = $this->parse_texts($this->get_tags_ret($tabbed),$tabbed);

if(isset($aParam[1]))
{
	$aTabs = explode(',',$aParam[1]);
	$alltabs = count($aTabs);
	if($alltabs > 0)
	{
		$aTabbed = array();
		for($p = 0; $p < $alltabs; $p++)
		{
			if($aTabs[$p] == 'main') {
				$aTabbed[] = array("type" => "main",
							  "settingname" => "cbProductsOnMainPage_Checked", 
							  "targettag" => "{GSSE_INCL_FEATUREDITEMS}",
							  "itemdatafield" => "itemIsOnIndexPage",
							  "itemdatavalue" => "Y");
			}
			if($aTabs[$p] == 'new') {
				$aTabbed[] = array("type" => "new",
							  "settingname" => "", 
							  "targettag" => "{GSSE_INCL_NEWITEMS}",
							  "itemdatafield" => "itemIsNewItem",
							  "itemdatavalue" => "Y");
			}
			if($aTabs[$p] == 'best') {
				$aTabbed[] = array("type" => "best",
							  "settingname" => "cbUsePhpBestseller_Checked", 
							  "targettag" => "{GSSE_INCL_BESTITEMS}",
							  "itemdatafield" => "",
							  "itemdatavalue" => "");
			}
		}
		
		$tabmax = count($aTabbed);
		if($tabmax > 0)
		{
			for($t = 0; $t < $tabmax; $t++)
			{
				$all_items = '';
				if($this->get_setting($aTabbed[$t]['settingname']) == 'True' || $aTabbed[$t]['settingname'] == '')
				{
					$cond = '';
					if($aTabbed[$t]['type'] == 'main' || $aTabbed[$t]['type'] == 'new') {
						$cond = " AND " . $aTabbed[$t]['itemdatafield'] . " = '" . $aTabbed[$t]['itemdatavalue'] . "'";
					} else {
						if($aTabbed[$t]['type'] == 'best') {
							if($this->phpactive()) {
								$aBest = $this->get_bestseller();
								$bestmax = count($aBest);
								if($bestmax > 0) {
									$cond = " AND itemItemNumber IN (" . implode(",",$aBest) . ")";
								} else {
									$tabbed = str_replace($aTabbed[$t]['targettag'],'Keine Bestseller',$tabbed);
									continue;
								}
							} else {
								$tabbed = str_replace($aTabbed[$t]['targettag'],'Kein PHP',$tabbed);
								continue;
							}
						}
					}
					$tdbh = $this->db_connect();
					$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
							 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
							 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
							 "itemInStockQuantity, itemAvailabilityId, itemIsAction " .
							 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y'" . $cond . " LIMIT 10";
					$erg = mysqli_query($tdbh,$sql);
					if(mysqli_error($tdbh) == 0)
					{
						if(isset($erg))
						{
							if(mysqli_num_rows($erg) > 0)
							{
								$item_box = $tabitem;
								include('inc/fill_items_box.inc.php');
							}
							mysqli_free_result($erg);
						}
					}
				}
				$tabbed = str_replace($aTabbed[$t]['targettag'],$all_items,$tabbed);
			}
		}
	}
}
$this->content = str_replace($tag, $tabbed, $this->content);
?>
