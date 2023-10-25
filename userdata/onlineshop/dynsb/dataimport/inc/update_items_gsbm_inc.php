<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
chdir("../../../");
include_once("inc/class.shopengine.php");
include_once("inc/class.gsbmconnector.php");
$se = new gs_shopengine();

//A TS 17.03.2016: ProPlus?
if($se->edition == 13) {
	if($se->get_setting('cbUseGSBM_Checked') == 'True') {
		$bmurl = $se->get_setting('edGSBMUrl_Text');
		$bmdbn = $se->get_setting('edGSBMDBName_Text');
		$bmusr = $se->get_setting('edGSBMUserName_Text');
		$bmpwd = convert_uudecode($se->get_setting('edGSBMPassword_Text'));
		if($bmurl != '' && $bmdbn != '' && $bmusr != '' && $bmpwd != '') {
			$mysqli = $se->db_connect();
			$oc = new gsbmConnector($bmurl,$bmdbn,$bmusr,$bmpwd,false);
			$oc->connect();
			$sql = "SELECT itemItemId, itemItemNumber, " . 
					 "(SELECT prcPrice FROM " . $se->dbtoken . "price where " . $se->dbtoken . "price.prcItemCount = " . $se->dbtoken . "itemdata.itemItemId LIMIT 1) AS price, " .
					 "itemInStockQuantity, itemItemDescription, itemWeight, itemVATRate, itemIsActive, itemGSBMStatus " .
					 "FROM " . $se->dbtoken . "itemdata " .
					 "WHERE itemLanguageId = '" . $se->lngID . "' AND itemGSBMStatus != '*' " .
					 "ORDER BY itemItemId ASC";
			//echo $sql . "<br>&nbsp;<br><pre>";
			$result = $mysqli->query($sql);
			if($mysqli->errno == 0) {
				while($r = $result->fetch_object()){
					if($r->itemGSBMStatus == 'i') {
						//Artikel hinzufügen
						$aItemData = array('art_num' => $r->itemItemNumber,
											 'art_defprice' => $r->price,
											 'art_instockqty' => $r->itemInStockQuantity,
											 'art_title' => $r->itemItemDescription,
											 'art_weight' => $r->itemWeight,
											 'art_vatkey' => intval($r->itemVATRate));
						$lActive = ($r->itemIsActive == 'W') ? true : false;
						//print_r($aItemData);
						$gsbmID = $oc->createItem($aItemData,1,'manual_periodic','standard',1,'product',12,$lActive);
					} else {
						$oc->dataModel = 'product.template';
						$aGSBMItem = $oc->getData(array(array(array('default_code', '=', $r->itemItemNumber))),array('fields' => array('id'),'limit'=>1));
						if(empty($aGSBMItem)) {
							$aItemData = array('art_num' => $r->itemItemNumber,
													 'art_defprice' => $r->price,
													 'art_instockqty' => $r->itemInStockQuantity,
													 'art_title' => $r->itemItemDescription,
													 'art_weight' => $r->itemWeight,
													 'art_vatkey' => intval($r->itemVATRate));
							$lActive = ($r->itemIsActive == 'W') ? true : false;
							$gsbmID = $oc->createItem($aItemData,1,'manual_periodic','standard',1,'product',12,$lActive);
						} else {
							$gsbmID = $aGSBMItem[0]['id'];
							$aData = array('list_price' => $r->price,
												'lst_price' => $r->price,
												'qty_available' => $r->itemInStockQuantity,
												'virtual_available' => $r->itemInStockQuantity,
												'display_name' => $r->itemItemDescription,
												'name' => $r->itemItemDescription,
												'weight' => $r->itemWeight,
												'taxes_id' => array(array(6,false,array(intval($r->itemVATRate)))));
							$oc->setManyData($gsbmID,$aData);
						}
					}
				}
				$upd = "UPDATE " . $se->dbtoken . "itemdata SET itemGSBMStatus = '*' WHERE itemGSBMStatus != '*' AND itemLanguageId = '" . $se->lngID . "'";
				$mysqli->query($upd);
			} else {
				echo $mysqli->error;
			}
			//echo "</pre>";
			$mysqli->close();
			//createItem($aItemData,$saleok = 1,$valuation = 'manual_periodic',$costmethod = 'standard',$categid = 1,$type = 'product',$itemUOM = 12)
		}
	}
}
?>