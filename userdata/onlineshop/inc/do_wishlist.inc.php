<?php
header("Content-type: application/json; charset=utf-8");
session_start();
chdir("../");
if(!isset($_SESSION['login']) || $_SESSION['login']['ok'] !== true)
{
	header("Location: index.php?page=createcustomer");
}
$cid = $_GET['cid'];
$aerg = array();
$aPrices = array();
include_once("inc/class.shopengine.php");
$wlse = new gs_shopengine();
$wldbh = $wlse->db_connect();
$wlsql = "SELECT * FROM " . $wlse->dbtoken . "wishlist WHERE cusIdNo = '" . $cid . "' ORDER BY date DESC";
$wlerg = mysqli_query($wldbh,$wlsql);
if(mysqli_errno($wldbh) == 0)
{
	if(mysqli_num_rows($wlerg) > 0)
	{
		while($wl = mysqli_fetch_assoc($wlerg))
		{
			$itdbh = $wlse->db_connect();
			$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
					 "(SELECT prcPrice FROM " . $wlse->dbtoken . "price WHERE " . $wlse->dbtoken . "price.prcItemCount = " . $wlse->dbtoken . "itemdata.itemItemId AND " . $wlse->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
					 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
					 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
					 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
					 "itemCheckAge, itemMustAge, itemIsAction, itemIsTextHasNoPrice, itemItemText, itemShipmentStatus " .
					 "FROM " . $wlse->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber = '" . $wl['itemNumber'] . "' AND itemLanguageId = '" . $wlse->lngID . "'";
			$erg = mysqli_query($itdbh,$sql);
			if(mysqli_errno($itdbh) == 0)
			{
				if(mysqli_num_rows($erg) > 0)
				{
					$aHelper = array();
					$z = mysqli_fetch_assoc($erg);
					foreach($z as $key => $val)
					{
						$aHelper[$key] = $val;
					}
					$aPrices = $wlse->get_prices($z['itemItemId']);
					$aHelper['hasaction'] = $wlse->chk_action($z['itemItemId'],$aPrices);
					$aHelper['aprices'] = $aPrices;
					$aHelper['aimgs'] = $wlse->get_itempics($z['itemItemId']);
					$aHelper['avail'] = rawurlencode($wlse->get_availability($z['itemInStockQuantity'],$z['itemShipmentStatus'],0));
					$aHelper['availtext'] = $wlse->get_availability_text($z['itemInStockQuantity'],$z['itemShipmentStatus'],0);
					$aRat = $wlse->get_av_rating($z['itemItemNumber']);
					$aHelper['rating_avg'] = $aRat[0]['schnitt'];
					$aHelper['rating_cnt'] = $aRat[0]['menge'];
					$aHelper['wl_act'] = '1';
					$aHelper['wl_cid'] = $cid;
					$aHelper['wl_wlid'] = $wl['wlId'];
					$aHelper['bestseller'] = $wlse->item_is_bestseller($z['itemItemNumber']);
					/*array_push($aerg,$aHelper);*/
					$aerg[] = $aHelper;
				}
				mysqli_free_result($erg);
			}
			//mysqli_close($itdbh);
		}
	}
	mysqli_free_result($wlerg);
}

echo json_encode($aerg);
?>