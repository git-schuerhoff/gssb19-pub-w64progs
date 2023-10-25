<?php
session_start();
$vats = '';
for($v = 0; $v < count($_SESSION['art_vatsumme']); $v++)
{
	$vats .= '0|'.$_SESSION['art_vatsumme'][$v]['vatrate'].'|'.$_SESSION['art_vatsumme'][$v]['vattotal'].'~';
}
echo $vats;
?>