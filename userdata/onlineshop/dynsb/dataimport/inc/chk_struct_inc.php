<?php
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
require("../../../conf/db.const.inc.php");
// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");
// select database or die
//define the path as relative
$ausg = "";

// A SM 20.03.2014
//Neues Feld in dsbxx_itemdata
//CheckAge, TINYINT (1), NOTNULL, 0
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemisDecimal'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemisDecimal TINYINT(1) NOT NULL DEFAULT '1' AFTER itemVideoLink";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
// E SM 20.03.2014

// A TS 24.06.2014
//Neues Feld in dsbxx_itemdata
//itemMetaDescription text
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemMetaDescription'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemMetaDescription text AFTER itemisDecimal";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 24.06.2014

// A TS 24.06.2014
//Neues Feld in dsbxx_itemdata
//itemMetaKeywords text
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemMetaKeywords'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemMetaKeywords text AFTER itemMetaDescription";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 24.06.2014
// A SM 19.12.2014
//Neues Feld in dsbxx_itemdata
//itemHtml1Caption text
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemHtml1Caption'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemHtml1Caption text AFTER itemMetaKeywords";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}

$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemHtml2Caption'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemHtml2Caption text AFTER itemHtml1Caption";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E SM 19.12.2014

// A SM 07.09.2015
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemMaxOrderQuantity'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemMaxOrderQuantity text AFTER itemHtml2Caption";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
// E SM 07.009.2015

//A TS 06.06.2014
//Tabelle dsbxx_kattags erzeugen
$kattagssql = "CREATE TABLE IF NOT EXISTS " . DBToken . "kattags " .
				  "(" .
				  "`Count` int(11) unsigned NOT NULL default '0'," .
				  "`KatCount` int(11) unsigned NOT NULL default '0'," .
				  "`LanguageId` char(3) NOT NULL default ''," .
				  "`Tag` varchar(64) NOT NULL," .
				  "`TextName` varchar(64) NOT NULL," .
				  "KEY `LanguageId` (`LanguageId`)," .
				  "KEY `KatCount` (`KatCount`)," .
				  "KEY `Tag` (`Tag`)" .
				  ");";
mysqli_query($link,$kattagssql);
if(mysqli_errno($link) != 0)
{
	die("Tabelle " . DBToken . "kattags konnte nicht erzeugt werden!<br />" . mysqli_error($link));
}
else
{
	$ausg .= "Tabelle " . DBToken . "kattags wurde erfolgreich angelegt.<br />";
}

// A TS 08.12.2014
//Neues Feld in dsbxx_productgroups
//Permalink
$test = "SHOW COLUMNS FROM " . DBToken . "productgroups LIKE 'Permalink'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "productgroups ADD COLUMN Permalink VARCHAR(255) NOT NULL AFTER IsEdited";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle productgroups erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle productgroups<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle productgroups ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 08.12.2014
// A TS 08.12.2014
//Neues Feld in dsbxx_productgroups
//Published
$test = "SHOW COLUMNS FROM " . DBToken . "productgroups LIKE 'Published'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "productgroups ADD COLUMN Published int(11) unsigned NOT NULL default '0' AFTER Permalink";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle productgroups erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle productgroups<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle productgroups ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 08.12.2014

// A TS 08.12.2014
//Neues Feld in dsbxx_productgrouplanguage
//Permalink
$test = "SHOW COLUMNS FROM " . DBToken . "productgrouplanguage LIKE 'Permalink'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "productgrouplanguage ADD COLUMN Permalink VARCHAR(255) NOT NULL AFTER GroupHint";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle productgrouplanguage erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle productgrouplanguage<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle productgrouplanguage ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 08.12.2014
// A TS 08.12.2014
//Neues Feld in dsbxx_productgroups
//Published
$test = "SHOW COLUMNS FROM " . DBToken . "productgrouplanguage LIKE 'Published'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "productgrouplanguage ADD COLUMN Published int(11) unsigned NOT NULL default '0' AFTER Permalink";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle productgrouplanguage erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle productgrouplanguage<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle productgrouplanguage ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E TS 08.12.2014

// A SM 19.12.2014
//Neues Feld in dsbxx_itemdata
//itemHtml1Caption text
$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemHtml1Caption'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemHtml1Caption text AFTER itemMetaKeywords";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}

$test = "SHOW COLUMNS FROM " . DBToken . "itemdata LIKE 'itemHtml2Caption'";
$erg = mysqli_query($link,$test);
if(mysqli_errno($link) == 0)
{
	if(mysqli_num_rows($erg) == 0)
	{
		$ins = "ALTER TABLE " . DBToken . "itemdata ADD COLUMN itemHtml2Caption text AFTER itemHtml1Caption";
		mysqli_query($link,$ins);
		if(mysqli_errno($link) == 0)
		{
			$ausg .= "Tabelle itemdata erfolgreich angepasst.<br />";
		}
		else
		{
			$ausg .= "Fehler beim anpassen der Tabelle itemdata<br />";
		}
	}
	else
	{
		$ausg .= "Tabelle itemdata ist auf dem neuesten Stand.<br />";
	}
}
else
{
	$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
}
//E SM 19.12.2014

//A TS 09.12.2015: Fields for Rental price
	/*prcIsRental smallint(3) default '0',
	prcBillingPeriod int(11) default '0',
	prcBillingFrequency varchar(255) default NULL,
	prcInitialPrice double default NULL,
	prcIsTrial smallint(3) default '0',
	prcTrialPeriod int(11) default '0',
	prcTrialFrequency int(11) default '0',
	prcTrialPrice double default NULL,
	prcRentalRuntime int(11) default '0'*/
	chg_struct(DBToken, "price", "prcIsRental", "smallint(3) default '0'", "prcItemCountEng");
	chg_struct(DBToken, "price", "prcBillingPeriod", "int(11) default '0'", "prcIsRental");
	chg_struct(DBToken, "price", "prcBillingFrequency", "varchar(255) default NULL", "prcBillingPeriod");
	chg_struct(DBToken, "price", "prcInitialPrice", "double default NULL", "prcBillingFrequency");
	chg_struct(DBToken, "price", "prcIsTrial", "smallint(3) default '0'", "prcInitialPrice");
	chg_struct(DBToken, "price", "prcTrialPeriod", "int(11) default '0'", "prcIsTrial");
	chg_struct(DBToken, "price", "prcTrialFrequency", "int(11) default '0'", "prcTrialPeriod");
	chg_struct(DBToken, "price", "prcTrialPrice", "double default NULL", "prcTrialFrequency");
	chg_struct(DBToken, "price", "prcRentalRuntime", "int(11) default '0'", "prcTrialPrice");
//E TS 09.12.2015: Fields for Rental price

//A TS function for structure-change
function chg_struct($db_token, $table, $field, $type, $after) {
	global $link,$ausg;
	$test = "SHOW COLUMNS FROM " . $db_token . $table . " LIKE '" . $field . "'";
	$erg = mysqli_query($link,$test);
	if(mysqli_errno($link) == 0) {
		if(mysqli_num_rows($erg) == 0) {
			$ins = "ALTER TABLE " . $db_token . $table . " ADD COLUMN " . $field . " " . $type . " AFTER " . $after;
			mysqli_query($link,$ins);
			if(mysqli_errno($link) == 0) {
				$ausg .= "Tabelle " . $table . " erfolgreich angepasst.<br />";
			} else {
				$ausg .= "Fehler beim anpassen der Tabelle " . $table . "<br />";
			}
		} else {
			$ausg .= "Tabelle " . $table . " ist auf dem neuesten Stand.<br />";
		}
	} else {
		$ausg .= "Es ist ein Fehler aufgetreten<br />"; 
	}
	return;
}
//E TS function for structure-change

mysqli_close($link);
echo $ausg;

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