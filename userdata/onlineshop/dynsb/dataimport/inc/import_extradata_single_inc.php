<?php
//error_reporting(E_ALL);
//ini_set("display_errors","on");
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
$datei = $_GET['file'];
require("../../../conf/db.const.inc.php");
require_once("../../include/functions.inc.php");
// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
//define the path as relative
$path = "../../";

//Fetchting charsets
$outenc = 'UTF-8';
$chsql = "SELECT @@global.character_set_database AS dbcharset, @@global.character_set_client AS clientcharset, @@global.character_set_connection AS connectioncharset";
$cherg = mysqli_query($link,$chsql);
if(mysqli_errno($link) == 0)
{
	$ch = mysqli_fetch_assoc($cherg);
	if($ch['dbcharset'] == 'utf8')
	{
		$outenc = 'UTF-8';
	}
	else
	{
		$outenc = 'ISO-8859-1';
	}
}
$aIntEnc = iconv_get_encoding('all');
$inpenc = $aIntEnc['input_encoding'];

//echo "Input: " . $inpenc . " => " . $outenc . "<br />";

//running the while loop
$fh = fopen($path . $datei,"r") or die("Konnte " . $path . $datei . " nicht öffnen!");
$i = 0;

writeLog("Processing file:" . $path . $datei);

while(!feof($fh))
{
	//echo "Zeile " . ($i + 1) . ": ";
	$zeile = fgets($fh);
	$aCmd = explode("|",$zeile);
	
	//A TS 02.10.2012 Lösch-Kommando wurde entfernt
	/*if($aCmd[0] == "d")
	{
		$cmd = "DELETE FROM " . $aCmd[1];
		$esql = "DELETE FROM " . $aCmd[1] . " WHERE " . $aCmd[2];
		$sql = $esql;
	}
	else
	{
	*/
	
	//Leerzeilen überspringen
	if(isset($aCmd[1]))
	{
		if($aCmd[1] == "")
		{
			continue;
		}
	}
	else
	{
		continue;
	}
	
	$cmd = "INSERT INTO " . $aCmd[1];
	$esql = "INSERT INTO " . $aCmd[1] . " VALUES(";
	$sql = $esql;
	//Werte in Array teilen
	$avalues = explode(",",$aCmd[2]);
	if(count($avalues) == 0)
	{
		continue;
		echo "Leeren Datensatz uebersprungen<br />";
	}
	$adecvals = array();
	$decstr;
	$avals = array();
	$str;
	for($v = 0; $v < count($avalues); $v++)
	{
		if($aCmd[1] == DBToken . 'generalinfo' && $v == 3)
		{
			array_push($adecvals,"'" . $avalues[$v] . "'");
		}
		else
		{
			$decoded = base64_decode($avalues[$v]);
			//array_push($adecvals,"'" . addslashes(iconv($inpenc,$outenc,$decoded)) . "'");
			array_push($adecvals,"'" . addslashes($decoded) . "'");
		}
		//array_push($adecvals,"'" . iconv('ISO-8859-1','UTF-8',base64_decode($avalues[$v])) . "'");
		array_push($avals,"'" . $avalues[$v] . "'");
	}
	$decstr = implode(",",$adecvals);
	$str = implode(",",$avals);
	$esql .= $str . ");";
	$sql .= $decstr . ");";
	//}
	/*
	if($aCmd[1] == 'dsb15_settingmemo')
	{
		echo $sql . "<br />";
	}
	*/
	
	$erg = mysqli_query($link,$sql);
	if(mysqli_errno($link) == 0)
	{
		//writeLog($sql . " => OK");
		$i++;
	}
	else
	{
		writeLog(mysqli_error($link) . ":" . chr(13) . chr(10) .  $sql);
	}
}
fclose($fh);
echo $datei . ": " . $i . " Zeilen verarbeitet<br />";
unlink($path . $datei) or die("Konnte " . $path . $datei . " nicht entfernen!");
mysqli_close($link);

function writeLog($cText) {
	if(file_exists($_SESSION['transferlog'])) {
		$lfh = fopen($_SESSION['transferlog'],'a');
		if($lfh) {
			fwrite($lfh,'Extradata, ' . date("Y-m-d H:i:s") . ": " . $cText . chr(13) . chr(10));
		}
		fclose($lfh);
	}
	return;
}
?>