<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
chdir("../../../");
include_once("inc/class.shopengine.php");
include_once("inc/class.gsbmconnector.php");
$se = new gs_shopengine();

$start = $_GET['start'];
$numrec = $_GET['numrec'];

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
					 "itemInStockQuantity, itemItemDescription, itemWeight, itemVATRate, itemIsActive " .
					 "FROM " . $se->dbtoken . "itemdata " .
					 "WHERE itemLanguageId = '" . $se->lngID . "' " .
					 "ORDER BY itemItemId ASC " .
					 "LIMIT " . $start . ", " . $numrec;
			//echo $sql . "<br>&nbsp;<br><pre>";
			$result = $mysqli->query($sql);
			if($mysqli->errno == 0) {
				while($r = $result->fetch_object()){
					//print_r($r);
					//echo "<br>--------------<br>";
					$aItemData = array('art_num' => $r->itemItemNumber,
									 'art_defprice' => $r->price,
									 'art_instockqty' => $r->itemInStockQuantity,
									 'art_title' => $r->itemItemDescription,
									 'art_weight' => $r->itemWeight,
									 'art_vatkey' => intval($r->itemVATRate));
					$lActive = ($r->itemIsActive == 'W') ? true : false;
					$gsbmID = $oc->createItem($aItemData,1,'manual_periodic','standard',1,'product',12,$lActive);
					//echo "Item created, id: " . $gsbmID[0] . "<br>";
					/*print_r($aItemData);
					var_dump($lActive);
					echo "<br>--------------<br>";*/
				}
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