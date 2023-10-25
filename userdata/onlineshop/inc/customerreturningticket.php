<?php /*session*/
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$retse = new gs_shopengine();
$rtdbh = $retse->db_connect();
$qrySQL = "SELECT COUNT(*) FROM ".$retse->dbtoken."settings WHERE setIdNo = 1 AND (customerreturningticket_adress<>'' AND customerreturningticket_adress IS NOT NULL)";
$qry = mysqli_query($rtdbh,$qrySQL);
$row = mysqli_fetch_row($qry);  
$ruecksendeadresse_count=$row[0];
if(!$ruecksendeadresse_count)
	die('<p style="font-weight:bold;">Fehler: Ung&uuml;tiger Direktaufruf oder Anbieter hat keine Rücksendeadresse hinterlegt.</p>				
		  <p style="font-weight:bold;">Error: Invalid direct call or offerer deposited no back sending address.</p>
		');
$cid = $_GET['cid'];
$cusSQL = "SELECT customerreturningticket_adress FROM ". $retse->dbtoken ."settings WHERE setIdNo = 1";
$cusqry = mysqli_query($rtdbh,$cusSQL);
$cra = mysqli_fetch_object($cusqry);
$retaddr = nl2br($cra->customerreturningticket_adress);

$rethtml = $retse->gs_file_get_contents('template/customerreturningticket.html');
$aRETTags = $retse->get_tags_ret($rethtml);
$rethtml = $retse->parse_texts($aRETTags,$rethtml);

$rethtml = str_replace('{GSSE_INCL_FULLNAME}',$_GET['ordertitle'] . ' ' . $_GET['orderfirstname'] . ' ' . $_GET['orderlastname'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_COMPANY}',$_GET['orderfirmname'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_STREET}',$_GET['orderstreet'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ZIP}',$_GET['orderzipcode'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_CITY}',$_GET['ordercity'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_RETURNADDR}',$retaddr,$rethtml);
$rethtml = str_replace('{GSSE_INCL_CUSNO}',$_GET['ordercustomerid'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_PHONE}',$_GET['orderphone'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_EMAIL}',$_GET['ordermail'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERID}',$_GET['orderidno'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERDATE}',$_GET['orderdate'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERPOS}',$_GET['orderpposno'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERITEMNO}',$_GET['orderpitemid'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERITEMNAME}',base64_decode($_GET['ordpitemdesc']),$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERQTY}',$_GET['orderpqty'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_ORDERPRC}',$_GET['orderpprice'],$rethtml);
$rethtml = str_replace('{GSSE_INCL_INCL_ORDERCURRENCY}',$_GET['ordercurrency'],$rethtml);

echo $rethtml;

?>

