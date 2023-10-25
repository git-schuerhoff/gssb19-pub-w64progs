<?php
header("Content-type: application/json; charset=utf-8");
$commhtml = '';
session_start();
chdir("../");
if(!isset($_SESSION['login']) || $_SESSION['login']['ok'] !== true)
{
	header("Location: index.php?page=createcustomer");
}
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$cid = $_SESSION['login']['cusIdNo'];
$npdbh = $pgse->db_connect();
$aItems = array();
$npsql = "SELECT * FROM " . $pgse->dbtoken . "itemcomments WHERE itcoCusId = '" . $cid . "' ORDER BY itcoDate DESC";
$nperg = mysqli_query($npdbh,$npsql);
if(mysqli_errno($npdbh) == 0)
{
	if(mysqli_num_rows($nperg) > 0)
	{
		while($np = mysqli_fetch_assoc($nperg))
		{
			$itdbh = $pgse->db_connect();
			$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
					 "(SELECT prcPrice FROM " . $pgse->dbtoken . "price WHERE " . $pgse->dbtoken . "price.prcItemCount = " . $pgse->dbtoken . "itemdata.itemItemId AND " . $pgse->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
					 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, itemIsTextHasNoPrice, " .
					 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
					 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, itemItemText,  " .
					 "itemCheckAge, itemMustAge, itemIsAction, itemisDecimal, itemShipmentStatus " .
					 "FROM " . $pgse->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber = '" . $np['itcoItemNumber'] . "' AND itemLanguageId = '" . $pgse->lngID . "'";
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
					$aPrices = $pgse->get_prices($z['itemItemId']);
					$aHelper['hasaction'] = $pgse->chk_action($z['itemItemId'],$aPrices);
					$aHelper['aprices'] = $aPrices;
					$aHelper['aimgs'] = $pgse->get_itempics($z['itemItemId']);
					$aHelper['avail'] = rawurlencode($pgse->get_availability($z['itemInStockQuantity'],$z['itemShipmentStatus'],0));
					$aRat = $pgse->get_av_rating($z['itemItemNumber']);
					$aHelper['rating_avg'] = $aRat[0]['schnitt'];
					$aHelper['rating_cnt'] = $aRat[0]['menge'];
					$aHelper['bestseller'] = $pgse->item_is_bestseller($z['itemItemNumber']);
					/*$aHelper['avail'] = ' ';*/
					/*array_push($aerg,$aHelper);*/
					$aerg[] = $aHelper;
				}
				else
				{
					$pgse->db_delete('itemcomments','itcoIdNo',$np['itcoIdNo']);
				}
				mysqli_free_result($erg);
			}
			//mysqli_close($itdbh);
		}
	}
	mysqli_free_result($nperg);
}	
//mysqli_close($npdbh);
echo json_encode($aerg);
?>