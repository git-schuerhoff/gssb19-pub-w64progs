<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
$file = "items_to_delete.dat";
require("../../../conf/db.const.inc.php");
require_once("../../include/functions.inc.php");
// connect to database server or die
$dbh = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$dbh->query("SET NAMES 'utf8'");
//define the path as relative
chdir("../../");

$aToDelete = array();
$atoDeleteGSBM = array();
$cDelete = '';
$itemdata_tab = DBToken."itemdata";

if(file_exists($file)) {
	$fh = fopen($file,"r") or die("Konnte " . $file . " nicht öffnen!");
	while(!feof($fh)) {
		$line = fgets($fh);
		if(Trim($line) != '') {
			$aItem = explode(';',$line);
			$cItemId = $aItem[0];
			$cItemNo = base64_decode($aItem[1]);
			$aToDelete[] = $cItemId;
			$atoDeleteGSBM[] = $cItemNo;
		}
	}
	fclose($fh);
	
	$iLimit = count($aToDelete);
	$cDelete = implode(',',$aToDelete);
	$sql = "DELETE FROM " . $itemdata_tab . " WHERE itemItemId IN (" . $cDelete . ") AND itemLanguageId = '" . $_GET['slc'] . "' LIMIT " . $iLimit;
	$dbh->query($sql);
	unlink($file) or die("Konnte " . $file . " nicht entfernen!");
	
}
$dbh->close();
?>
