<?php
//error_reporting(E_ALL);
//ini_set("display_errors","on");
session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
$datei = $_GET['file'];
$bmi = $_GET['bmi'];
require("../../../conf/db.const.inc.php");
require_once("../../include/functions.inc.php");
// connect to database server or die
$dbh = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$dbh->query("SET NAMES 'utf8'");
//define the path as relative
$path = "../../";
//using the opendir function
//running the while loop

//Get GSSB-Edition
$editon = base64_decode($gssbEdition);

//Fetchting charsets
$outenc = 'UTF-8';
$chsql = "SELECT @@global.character_set_database AS dbcharset, @@global.character_set_client AS clientcharset, @@global.character_set_connection AS connectioncharset";
$cherg = mysqli_query($dbh,$chsql);
if(mysqli_errno($dbh) == 0)
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

$g = 0;
$fh = fopen($path . $datei,"r") or die("Konnte " . $path . $datei . " nicht öffnen!");

writeLog("Processing file:" . $path . $datei);

//TS 21.03.2016: Einzufügende und zu aktualisierende Artikel für GSBM sammeln
while(!feof($fh))
{
	//echo "Zeile " . ($i + 1) . ": ";
	$zeile = fgets($fh);
	//echo "_" . $zeile . "_<br />";
	$action = getAction($zeile);
	if (preg_match('~gssb_to_dynsb: (.*)~i', $zeile, $matches))
	{
		//echo trim($matches[1])."<br />";
		$CountryCode = getCountryCode($zeile);
		if("i" == $action && "1" == getCoding($zeile) && "_" == $CountryCode)
		{
			InsertUpdateItemdata(trim($matches[1]),$action, $bmi);
		}
		else if("u" == $action && "1" == getCoding($zeile) && "_" == $CountryCode)
		{
			InsertUpdateItemdata(trim($matches[1]),$action, $bmi);
		}
		/*
		else if ("i" == $action && "1" == getCoding($zeile) && "_" != $CountryCode)
		{
			InsertUpdateItemPrice(trim($matches[1]),$CountryCode);
		}
		else if ("d" == $action)
		{
			DeleteItem(trim($matches[1]));
		}*/
	}
	if (preg_match('~gssbph_to_dynsbph: (.*)~i', $zeile, $matches))
	{
		DoPriceHistory(trim($matches[1]));
	}
	if (preg_match('~gssbdl_to_dynsbdl: (.*)~i', $zeile, $matches))
	{
		if ("d" == $action)
		{
			//DeleteDownloadArticle(trim($matches[1]));
		}
		else
		{
			InsertUpdateDownloadArticle(trim($matches[1]));
		} 
	}
	$g++;
}
fclose($fh);
//echo "0" . $datei . ": " . $g . " Zeilen verarbeitet<br />";
unlink($path . $datei) or die("Konnte " . $path . $datei . " nicht entfernen!");
mysqli_close($dbh);

function runSQL($sql) {
	global $dbh;
	//echo $sql."<br/>";
	$qry = @mysqli_query($dbh,$sql);
	if(mysqli_errno($dbh) == 0) {
		@mysqli_free_result($qry);
		return true;
	} else {
		writeLog(mysqli_error($dbh) . ':' .chr(13) . chr(10) . $sql);
	}
	return false;
}

function getAction($str)
{
	$pos = strpos($str, "action=");
	if ($pos !== false)
	{
	return substr($str,$pos+7,1);
	}
	return "_";
}

function getCoding($str)
{
	$pos = strpos($str, "coding=");
	if ($pos !== false)
	{
	return substr($str,$pos+7,1);
	}
	return "_";
}

function getCountryCode($str)
{
	$pos = strpos($str, "CountryCode=");
	if ($pos !== false)
	{
	//Dieser Datensatz enthält einen Preis
	return substr($str,$pos+12,3);
	}
	
	return "_";
}

function getItemNumber($arr_datensatz)
{

	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "ItemNumber")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
		$value=base64_decode($value);			 
//		echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
		return $value; 
		}
	}
	}
	return null;
}

function getItemId($arr_datensatz)
{

	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "ItemId")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
		$value=base64_decode($value);			 
//		echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
		return $value; 
		}
	}
	}
	return null;
}

function getLanguageId($arr_datensatz)
{

	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "LanguageId")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
//		echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
		return $value; 
		}
	}
	}
	return null;
}


function getQuantityFrom($arr_datensatz)
{

	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "QuantityFrom")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
		$value=base64_decode($value);			 
//		echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
		return $value; 
		}
	}
	}
	return null;
}

function getPriceChangeDate($arr_datensatz)
{
	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "PriceChangeDate")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
		$value=base64_decode($value);			 
			$dt_array = explode(" ", $value);
			$date_arr = explode(".", $dt_array[0]);
			$time_arr = explode(":", $dt_array[1]);
			$PriceChangeDate = $date_arr[2]."-".$date_arr[1]."-".$date_arr[0]." ".$time_arr[0].":".$time_arr[1].":".$time_arr[2];
//		echo "<b>gefunden key=".$key.", value=".$PriceChangeDate."</b><br/>";
		return $PriceChangeDate; 
		}
	}
	}
	return null;
}

function getItemPrice($arr_datensatz)
{

	for($k=0;$k<count($arr_datensatz);$k++)
	{
	$pos = strpos($arr_datensatz[$k], '=');
	if ($pos !== false)
	{
		$key=substr($arr_datensatz[$k], 0, $pos);
		if ($key == "ItemPrice")
		{
		$value= substr($arr_datensatz[$k], $pos+1);
		$value=base64_decode($value);			 
//		echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
		return $value; 
		}
	}
	}
	return null;
}

function DoPriceHistory($linedata)
{
	global $dbh;
	$arr_datensatz = explode('&',$linedata);
	$ItemNumber = getItemNumber($arr_datensatz);
	$LanguageId = getLanguageId($arr_datensatz);
	$LanguageId=base64_decode($LanguageId);
	$PriceChangeDate = getPriceChangeDate($arr_datensatz);
	$ItemPrice = getItemPrice($arr_datensatz);			 

	
	if(isset($ItemNumber) && isset($LanguageId))
	{
	
	$insertSQL1 = "prchItemNumber, ";
	$insertSQL2 = "'".$ItemNumber."', ";
	$insertSQL1 .= "prchLanguageId, ";
	$insertSQL2 .= "'".$LanguageId."', ";
	$insertSQL1 .= "prchDateTime, ";
	$insertSQL2 .= "'".$PriceChangeDate."', ";
	$insertSQL1 .= "prchPrice, ";
	$insertSQL2 .= "'".$ItemPrice."', "; 
	
	$SQL = "SELECT prchItemNumber FROM ".DBToken."pricehistory where prchItemNumber='".$ItemNumber."' AND prchLanguageId='".$LanguageId."'
			AND prchDateTime = '".$PriceChangeDate."'";
	$qry = @mysqli_query($dbh,$SQL);
	$num = @mysqli_num_rows($qry);
	if($num == 0)
	{
		$insertSQL1 = substr($insertSQL1, 0, sizeof($insertSQL1)-3);
		$insertSQL2 = substr($insertSQL2, 0, sizeof($insertSQL2)-3);
	
		//echo "INSERT PriceHistory for item: ".$ItemNumber;
		$SQL = "INSERT INTO ".DBToken."pricehistory (".$insertSQL1.") values(".$insertSQL2.");";
		runSQL($SQL);
	}
	mysqli_free_result($qry);
	}

}

function InsertUpdateDownloadArticle($linedata)
{
	global $dbh;
	$arr_datensatz = explode('&',$linedata);
	$ItemNumber = getItemNumber($arr_datensatz);
	$LanguageId = getLanguageId($arr_datensatz);
	$LanguageId = base64_decode($LanguageId);
	if(isset($ItemNumber) && isset($LanguageId)) {
		$downloadarticle_tab = DBToken."downloadarticle";

		$insertSQL1 = "downloadItemNumber, downloadLanguageId, ";
		$insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
		$updateSQL = "";

		for($j=0;$j<count($arr_datensatz);$j++) {
			$pos = strpos($arr_datensatz[$j], '=');
			if ($pos !== false) {
				$key=strtolower(substr($arr_datensatz[$j], 0, $pos));
				$value= substr($arr_datensatz[$j], $pos+1);
				$value=base64_decode($value); 
		
				if ($value!="" && $key != "") {
					if ($key == "filename" || $key == "numberdownloads"	|| $key == "watermarktext"
						|| $key == "watermarkposition" || $key == "watermarkintensity") {
						if ($key == "numberdownloads") {
							$key = "alloweddownloads";
						}
						$key = "download".$key;
						$insertSQL1 .= $key.", ";
						$insertSQL2 .= "'".$value."', ";
						$updateSQL .= $key." = '".$value."', ";
					}
				}
			}
		}
	
	$SQL = "INSERT INTO ".$downloadarticle_tab." (".$insertSQL1."downloadCreateTime, downloadUpdateTime) values(".$insertSQL2."'".date("YmdHis")."', '".date("YmdHis")."');";
	runSQL($SQL);
	//mysqli_free_result($qry);
	}
}

function InsertUpdateItemdata($linedata,$action,$bmi)
{
	global $dbh, $edition;
	$arr_datensatz = explode('&',$linedata);
	$itemdata_tab = DBToken."itemdata";
	$ItemNumber = getItemNumber($arr_datensatz);
	$LanguageId = getLanguageId($arr_datensatz);
	$ItemId = getItemId($arr_datensatz);

	//A TS 29.08.2012 ItemId darf nicht 0 sein!
	if(isset($ItemNumber) && isset($LanguageId) && $ItemId != 0)
	//if(isset($ItemId))
	{
		$insertSQL1 = "itemItemNumber, itemLanguageId, ";
		$insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
		$updateSQL = "";
		
		$aStringFields = array("itemtext","itemdescription","itemproductgroupname","variantdescription","itemproductgroupname",
									  "detailtext1","detailtext2","attribute1","attribute2","attribute3","manufacturer","manufacturerproductcode",
									  "ean_isbn","brand","usecentraltext","istextinput","isaction","checkage","soonhereflag","soonheretext","isslideshow",
									  "isonindexpage","htmltext1","htmltext2","urlstotestreports","isdecimal","metadescription","metakeywords","html1caption",
									  "html2caption","centraltextnr","itempage","itemlink","smallimagefile","smallimagelink","mediumimagefile","mediumimagelink",
									  "bigimagefile","bigimagelink","isactive","isvariant","istexthasnoprice","hasdetail","largeimage2file",
									  "largeimage2link","largeimage3file","largeimage3link","newflag","iscatalogflag","isdownloadarticle","isbonusarticle",
									  "hasinquiry","videolink","specpostage"
									);
		$aNumFields = array("itemproductgroupidno","vatrate","pricebonuspoints","itemid","mustage","instockquantity","weight");
	
		for($j=0;$j<count($arr_datensatz);$j++)
		{
			//echo $arr_datensatz[$j]."<br/>";
			$pos = strpos($arr_datensatz[$j], '=');
			if ($pos !== false)
			{
				$key=strtolower(substr($arr_datensatz[$j], 0, $pos));
				$value= substr($arr_datensatz[$j], $pos+1);
				$value=base64_decode($value);
				
				if (in_array($key,$aStringFields)) {
					if ($key == "bigimagefile") {
						$key = "bigimage1file";
					} else if ($key == "bigimagelink") {
						$key = "bigimage1link";
					} else if ($key == "newflag") {
						$key = "isnewitem";
					} else if ($key == "iscatalogflag") {
						$key = "iscatalogflg";
					}
					
					if ($key != "itemproductgroupname") {
						$key = "item".$key;
					}
					
					$insertSQL1 .= $key.", ";
					$insertSQL2 .= "'".addslashes($value)."', ";
					$updateSQL .= $key." = '".addslashes($value)."', ";
				} else if (in_array($key,$aNumFields)){
					if ($key == "pricebonuspoints") {
						$key = "bonuspointsprice";
					}
					if ($key != "itemproductgroupidno") {
						$key = "item".$key;
					}
					
					$value = str_replace(",",".",$value);//Bei Decimals sicherheitshalber Komma durch Punkt
					$insertSQL1 .= $key.", ";
					//$value = iconv($inpenc,$outenc,$value);
					$insertSQL2 .= $value.", ";
					$updateSQL .= $key." = ".$value.", ";
				} else {
					//Nix
				}
			}
		}
		$insertSQL1 .= "itemshipmentstatus, ";
		$insertSQL2 .= "'-1', ";
		$updateSQL .= "itemshipmentstatus = '-1', ";
		
		if($edition == 13) {
			//ProPlus only
			$gsbmStatus = $action;
			//Bei Komplettveröffentlichung im Update-Modus (neue Artikel werden angelegt, wenn die Artikelnummer
			//im GSBM nicht gefunden wird
			if($bmi == 2) { $gsbmStatus = 'u'; }
			//Bei Erstveröffentlichung hier nichts machen, dafür gibt es ein separates Skript
			if($bmi == 3) { $gsbmStatus = '*'; }
			
			if($action == 'i') {
				$SQL = "INSERT INTO ".$itemdata_tab." (".$insertSQL1." itemCreateTime, itemUpdateTime, itemGSBMStatus) values(".$insertSQL2."'".date("YmdHis")."', '".date("YmdHis")."', '" . $gsbmStatus ."');";
			} else {
				$SQL = "UPDATE ".$itemdata_tab." set ".$updateSQL." itemUpdateTime = '".date("YmdHis")."', itemGSBMStatus = '" . $gsbmStatus . "' where itemItemId='".$ItemId."';";
			}
		} else {
			if($action == 'i') {
				$SQL = "INSERT INTO ".$itemdata_tab." (".$insertSQL1." itemCreateTime, itemUpdateTime) values(".$insertSQL2."'".date("YmdHis")."', '".date("YmdHis")."');";
			} else {
				$SQL = "UPDATE ".$itemdata_tab." set ".$updateSQL." itemUpdateTime = '".date("YmdHis")."' where itemItemId='".$ItemId."';";
			}
		}
		//echo $SQL . "<br />";
		runSQL($SQL);
		//E TS
	}
	return $ItemId;
}

function writeLog($cText) {
	if(file_exists($_SESSION['transferlog'])) {
		$lfh = fopen($_SESSION['transferlog'],'a');
		if($lfh) {
			fwrite($lfh,'Itemdata, ' . date("Y-m-d H:i:s") . ": " . $cText . chr(13) . chr(10));
		}
		fclose($lfh);
	}
	return;
}

?>
