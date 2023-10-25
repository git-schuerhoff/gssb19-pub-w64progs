<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();

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

if(isset($_POST['search'])) {
	$search = $_POST['search'];
} else {
	$search = $_GET['search'];
}
$aSearch = json_decode ($search, true);
$aerg = array();
$aPrices = array();
$SQLText = "";
if (!isset($aSearch['sText']) || strlen(trim($aSearch['sText'])) == 0)
{
	$SQLText = "";
}
else
{
	/*Add to Statistik*/
	$sl->actid = 3;
	$sl->strlog = $aSearch['sText'];
	$sl->logShoppage();
	$sl->actid = '';
	$sl->strlog = '';
	
	$tmpText = str_replace(" ", "%", trim(addslashes(strip_tags($aSearch['sText']))));
	$SQLText  = " AND (lower(i.itemItemDescription) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemVariantDescription) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemItemText) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemManufacturer) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemManufacturerProductCode) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemEAN_ISBN) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemBrand) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemSoonHereText) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemHtmlText1) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemHtmlText2) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemDetailText1) LIKE '%".strtolower($tmpText)."%'";
	$SQLText .= " OR lower(i.itemDetailText2) LIKE '%".strtolower($tmpText)."%')";
}

//-------------------------------------------------------------------ItemNo---------------
$SQLItemNo = "";
if (!isset($aSearch['sItemNo']) || strlen(trim($aSearch['sItemNo'])) == 0)
{
	$SQLItemNo = "";
}
else
{
	$tmpItemNo = trim(addslashes(strip_tags($aSearch['sItemNo'])));
	$SQLItemNo  = " AND i.itemItemNumber LIKE '".$tmpItemNo."%'";
}

//-------------------------------------------------------------------Price---------------
$SQLPrice = "";
if (!isset($aSearch['sStartPrice']) || strlen(trim($aSearch['sStartPrice'])) == 0
&& !isset($aSearch['sEndPrice']) || strlen(trim($aSearch['sEndPrice'])) == 0)
{
	$SQLPrice = "";
}
else
{
	$sStartPrice = str_replace(",",".",$aSearch['sStartPrice']);
	$sEndPrice = str_replace(",",".",$aSearch['sEndPrice']);

	$tmpStartPrice = addslashes(strip_tags($sStartPrice));
	$tmpEndPrice = addslashes(strip_tags($sEndPrice));
	$SQLPrice  = " AND p.prcPrice BETWEEN '".$tmpStartPrice."' AND '".$tmpEndPrice."'";
}

//-------------------------------------------------------------------SpecialItem----------
if(!isset($aSearch['sSpecialItem']) || strlen(trim($aSearch['sSpecialItem'])) == 0
||trim($aSearch['sSpecialItem'])!='on')
{
	$spiTab = "";
	$SQLSpecialItem = "";
}
else
{
	$spiTab = ", ".$pgse->dbtoken."specialitem s ";
	$tmpSpecialItem = addslashes(strip_tags($aSearch['sSpecialItem']));
	$SQLSpecialItem = " AND s.spitemItemNumber = i.itemItemNumber";
}

if($aSearch['sort']==1)
{
	$ordertext="i.itemItemNumber";
}
else if($aSearch['sort']==3)
{
	$ordertext="p.prcPrice";
}
else if($aSearch['sort']==2)
{
	$ordertext="i.itemProductGroupName ";
}

$pgidbh = $pgse->db_connect();
$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, prcPrice as ItemPrice " .
	 "FROM " . $pgse->dbtoken . "itemdata i, " . $pgse->dbtoken . "price p " . $spiTab . 
	 " WHERE p.prcItemNumber = i.itemItemNumber AND i.itemIsActive = 'Y' AND i.itemLanguageId = '" . 
	 $pgse->lngID . "' " . $SQLText . $SQLPrice . $SQLItemNo . $SQLSpecialItem . 
	 " group by i.itemItemId order by ".$ordertext;
$erg = mysqli_query($pgidbh,$sql);

if(mysqli_errno($pgidbh) == 0)
{
	if(mysqli_num_rows($erg) > 0)
	{
		while($z = mysqli_fetch_assoc($erg))
		{
			$aRat = $pgse->get_av_rating($z['itemItemNumber']);
			$aerg[] = array('ID' => $z['itemItemId'],'itemItemDescription' => $z['itemItemDescription'],'ItemPrice' => $z['ItemPrice'],'rating_avg' => $aRat[0]['schnitt'],'OrderID' => 0);
		}
	}
}
@mysqli_close($pgidbh);
echo json_encode($aerg);
?>