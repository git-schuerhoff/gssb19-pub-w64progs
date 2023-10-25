<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$delse = new gs_shopengine();
$deldbh = $delse->db_connect();
//A SM 05.04.2017 Spec shipping cost
$specShipCost = 0;
$withoutSpecShip = False;
if(isset($_SESSION['basket'])){
    $basket_count = count($_SESSION['basket']);
    for($b = 0; $b < $basket_count; $b++)
    {
       $sql = "SELECT ShippingPrice FROM " . $delse->dbtoken . "specshipprices WHERE ItemNumber = '" . $_SESSION['basket'][$b]['art_num'] . "' AND AreaId = ".$_GET['areaID'];
        $erg = mysqli_query($deldbh,$sql);
        if(mysqli_errno($deldbh) == 0)
        {
            if(mysqli_num_rows($erg) > 0)
            {
               $aSpecShipC = mysqli_fetch_assoc($erg);
            } else {
               $withoutSpecShip = True; 
            }
        }
        if($delse->get_setting('chkSpecShipOnce_checked') == 'True'){
            if($specShipCost < $aSpecShipC['ShippingPrice']){
                $specShipCost = $aSpecShipC['ShippingPrice'];
            }
        } else {
            $specShipCost = $specShipCost + $aSpecShipC['ShippingPrice'];
        }
        mysqli_free_result($erg);
    }    
}
if(($basket_count > 1) and ($withoutSpecShip === True)){
    echo $delse->get_shipcost($_GET['shipID'],$_GET['areaID'],$_GET['sumtotal'], $_SESSION['basketweight']) + $specShipCost;
} else if($withoutSpecShip === False) {
    echo $specShipCost;
} else {
    echo $delse->get_shipcost($_GET['shipID'],$_GET['areaID'],$_GET['sumtotal'], $_SESSION['basketweight']);
}
?>