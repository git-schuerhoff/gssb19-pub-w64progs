<?php
/******************************************************************************/
/* File: dhl_url.php                                                            */
/******************************************************************************/
$url = "https://www.dhl.de/popweb/ProductOrder.do?checkOnInit=false&insert=true&";
$url .= "formModel.sender.name=".$_POST['ADDR_SEND_FIRST_NAME']."%20".$_POST['ADDR_SEND_LAST_NAME']."&";
$street = $_POST['ADDR_SEND_STREET'];
preg_match("/\d+/",$street,$result);
$housnumber = $result[0];
$street = str_replace ($housnumber, '', $street);
$url .= "formModel.sender.street=".$street."%20".$_POST['ADDR_SEND_STREET_ADD']."&";
$url .= "formModel.sender.houseNumber=".$housnumber."&";
$url .= "formModel.sender.zip=".$_POST['ADDR_SEND_ZIP']."&";
$url .= "formModel.sender.city=".$_POST['ADDR_SEND_CITY']."&";

$url .= "formModel.receiver.name=".$_POST['ADDR_RECV_FIRST_NAME']."%20".$_POST['ADDR_RECV_LAST_NAME']."&";
$street = $_POST['ADDR_RECV_STREET'];
preg_match("/\d+/",$street,$result);
$housnumber = $result[0];
$street = str_replace ($housnumber, '', $street);
$url .= "formModel.receiver.street=".$street."%20".$_POST['ADDR_RECV_STREET_ADD']."&";
$url .= "formModel.receiver.houseNumber=".$housnumber."&";
$url .= "formModel.receiver.zip=".$_POST['ADDR_RECV_ZIP']."&";
$url .= "formModel.receiver.city=".$_POST['ADDR_RECV_CITY'];
//echo "http://www.dhl.de/onlinefrankierung";
header ("Location: ".$url);
?>