<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
require("../../../conf/db.const.inc.php");
// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
$aTbls = array(DBToken . "itemdata",
			   DBToken . "downloadarticle",
			   DBToken . "itemdownloads",
			   DBToken . "item_to_variant",
			   DBToken . "upselling",
			   DBToken . "crossselling",
			   DBToken . "bundles",
			   DBToken . "itemcentraltext",
			   DBToken . "action",
			   DBToken . "price",
			   DBToken . "pricehistory",
			   DBToken . "productgrouplanguage",
			   DBToken . "productgroups",
			   DBToken . "specshipprices",
			   DBToken . "gallery",
			   DBToken . "items2group",
			   DBToken . "addressarea",
			   DBToken . "contentpool",
			   DBToken . "deliveryarea",
			   DBToken . "deliverycountry",
			   DBToken . "deliverylanguage",
			   DBToken . "languagename",
			   DBToken . "paymentcountry",
			   DBToken . "paymentlanguage",
			   DBToken . "salestax",
			   DBToken . "setting",
			   DBToken . "settingmemo",
			   DBToken . "shippingweightcost",
			   DBToken . "attributes",
			   DBToken . "countriesareas",
			   DBToken . "paymentinternalnames",
			   DBToken . "generalinfo",
			   DBToken . "kattags",
			   DBToken . "slideshow");

$aWhere = array(" WHERE itemLanguageId = '" . $_GET['slc'] . "'",//itemdata
					 " WHERE 1",//downloadarticle
					 " WHERE languageId = '" . $_GET['slc'] . "'",//itemdownloads
					 " WHERE 1",//item_to_variant
					 " WHERE 1",//upselling
					 " WHERE 1",//crossselling
					 " WHERE languageId = '" . $_GET['slc'] . "' AND bundleLanguageId = '" . $_GET['slc'] . "'",//bundles
					 " WHERE languageId = '" . $_GET['slc'] . "' AND countryId = '" . $_GET['cnt'] . "'",//itemcentraltext
					 " WHERE 1",//action
					 " WHERE prcCountryId = '" . $_GET['cnt'] . "'",//price
					 " WHERE prchlanguageId = '" . $_GET['slc'] . "'",//pricehistory
					 " WHERE 1",//productgrouplanguage
					 " WHERE 1",//productgroups
					 " WHERE 1",//specshipprices
					 " WHERE 1",//gallery
					 " WHERE 1",//items2group
					 " WHERE LanguageId = '" . $_GET['slc'] . "' AND CountryId = '" . $_GET['cnt'] . "'",//addressarea
					 " WHERE LanguageId = '" . $_GET['slc'] . "'",//contentpool
					 " WHERE CountryId = '" . $_GET['cnt'] . "'",//deliveryarea
					 " WHERE CountryId = '" . $_GET['cnt'] . "'",//deliverycountry
					 " WHERE LanguageId = '" . $_GET['slc'] . "'",//deliverylanguage
					 " WHERE 1",//languagename
					 " WHERE CountryId = '" . $_GET['cnt'] . "'",//paymentcountry
					 " WHERE LanguageId = '" . $_GET['slc'] . "'",//paymentlanguage
					 " WHERE CountryId = '" . $_GET['cnt'] . "'",//salestax
					 " WHERE LanguageId = '" . $_GET['slc'] . "' AND CountryId = '" . $_GET['cnt'] . "'",//setting
					 " WHERE LanguageId = '" . $_GET['slc'] . "' AND CountryId = '" . $_GET['cnt'] . "'",//settingmemo
					 " WHERE CountryId = '" . $_GET['cnt'] . "'",//shippingweightcost
					 " WHERE 1",//attributes
					 " WHERE 1",//countriesareas
					 " WHERE 1",//paymentinternalnames
					 " WHERE 1",//generalinfo
					 " WHERE LanguageId = '" . $_GET['slc'] . "'",//kattags
					 " WHERE 1");//slideshow

//TS 18.03.2016: Wenn del = 0 ist, d. h. es findet KEINE Erst- oder Komplettveröffentlichung statt, dann
//Artikeldaten NICHT löschen. Artikeltabellenname und zugehörige Klausel werden aus den Arrays entfernt
if($_GET['del'] == 0) {
	$aTbls = array_splice($aTbls, 1);
	$aWhere = array_splice($aWhere, 1);
}

$err = 0;
for($t = 0; $t < count($aTbls); $t++)
{
	if($_GET['slc'] != '' AND $_GET['cnt'] != '')
	{
		$where = $aWhere[$t];
	}
	else
	{
		$where = " WHERE 1";
	}
	$trunc = "DELETE FROM " . $aTbls[$t] . $where;
	mysqli_query($link,$trunc);
	if(mysqli_errno($link) != 0)
	{
		$err = 1;
		writeLogFile(mysqli_error($link) . ":\n" . $trunc);
	}
	else
	{
		writeLogFile($trunc . " => OK");
	}
}

if($err == 0)
{
	echo "Dateien wurden erfolgreich ges&auml;ubert.<br />";
}
else
{
	echo "Beim S&auml;ubern der Dateien ist ein Fehler unterlaufen.<br />";
}

mysqli_close($link);

function writeLogFile($str)
{
  if(file_exists("../../gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("../../gssb_to_dynsb_log")),sizeof(decoct(fileperms("../../gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "../../gssb_to_dynsb_log/ED".date('Ymd').".log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str. chr(13) . chr(10);
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}
	
?>