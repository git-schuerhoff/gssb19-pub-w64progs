<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se = new gs_shopengine();

$token = $se->get_setting('edToken_Text');
//echo $token;
function get_new_order($se){
    $dbh = $se->db_connect();
    $sql = "SELECT * FROM " . $se->dbtoken . "order WHERE verarb_status=1";
    $erg = mysqli_query($dbh,$sql);
    $aerg = array();
    while($z = mysqli_fetch_assoc($erg)){
        foreach($z as $key => $val)
		{
			$aHelper[$key] = $val;
		}
        $aerg[] = $aHelper;
    }
    return $aerg;
}

function get_orderpos($se,$ordId){
    $dbh = $se->db_connect();
    $sql = "SELECT * FROM " . $se->dbtoken . "orderpos WHERE ordpOrdIdNo=" . $ordId;
    $erg = mysqli_query($dbh,$sql);
    $aerg = array();
    while($z = mysqli_fetch_assoc($erg)){
        foreach($z as $key => $val)
		{
			$aHelper[$key] = $val;
		}
        $aerg[] = $aHelper;
    }
    return $aerg; 
}

function get_customer_by_mail($se,$mail){
    $dbh = $se->db_connect();
    $sql = "SELECT * FROM " . $se->dbtoken . "customer WHERE cusEMail='" . $mail . "'";
    $erg = mysqli_query($dbh,$sql);
    $aerg = array();
    while($z = mysqli_fetch_assoc($erg)){
        foreach($z as $key => $val)
		{
			$aHelper[$key] = $val;
		}
        $aerg[] = $aHelper;
    } 
    return $aerg; 
}

if($_GET['token'] <> $token) {
    echo "Die Token unterscheiden sich! <br>Überprüfen Sie bitte den Token im GS Shopbuilder unter Einstellungen->GS BusinessManager->Token.<br>Im GS BusinessManager wird das gleiche Token unter Einstellungen->Onlineshops gespeichert.";
    die();
}

if(isset($_GET['orderpos'])){
    $test=get_orderpos($se,$_GET['orderpos']);
    echo json_encode($test);
} else {
    $dbh = $se->db_connect();
    $test=get_new_order($se);
    echo json_encode($test);
    // Update Status
    $sql="UPDATE ". $se->dbtoken . "order SET verarb_status=0 WHERE verarb_status=1";
    $erg = mysqli_query($dbh,$sql);
}


/*
if(isset($_GET['customer']) && ($_GET['token']==$token)){
    $test=get_customer_by_mail($se,$_GET['customer']);
    echo json_encode($test);
}
*/
?>