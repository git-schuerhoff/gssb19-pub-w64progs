<?php
/*
    Script - gssb_inserts.php
    Author: GS Software Solutions GmbH

    File: gssb_inserts.php
*/
writeLogFile("Start");


if(file_exists("../conf/db.const.inc.php"))
{
  require("../conf/db.const.inc.php");
  require_once("include/functions.inc.php");
}

foreach($_REQUEST as $key => $value)
{
  $$key = trim($value);
}

if(isset($session) && base64_decode($session)==$gssbTimeId.$shopURL)
{
  $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
  $link->query("SET NAMES 'utf8'");
  $data = base64_decode($data);
  $tablename = base64_decode($tablename);
  dataInsert($tablename, $data);
}

/*function dataInsert($tablename, $data)
{
     $SQL = "DELETE FROM ".DBToken.$tablename;
     $qry = @mysqli_query($link, $SQL);
	 
	 $data_arr = explode(':=:', $data);
   foreach($data_arr as $wert){	 
      $inserts = implode(',', explode('$=$', $wert));
      $SQL = "INSERT INTO ".DBToken."$tablename values($inserts);";
      $qry = @mysqli_query($link, $SQL);
	} 
}*/

function dataInsert($tablename) {
	global $link;
	$SQL = "DELETE FROM ".DBToken.$tablename;
	/*A TS 21.05.2012
	Löschen ausführen
	*/
	$qry = @mysqli_query($link,$SQL);
	//E TS 21.05.2012
	writeLogFile($SQL);

	$filename = $tablename.'.csv';
	$file = fopen($filename, 'r');
	
	while(($line = fgetcsv($file, 4096, ";", "\"")) !== FALSE) { 
		$inserts = '';
		foreach($line as $wert) {
			$inserts = $inserts."'".$wert."',";
		}
		$inserts = substr($inserts, 0, strlen($inserts) - 1);
		$SQL = "INSERT INTO ".DBToken."$tablename values($inserts);";
		writeLogFile($SQL);
		$qry = @mysqli_query($link,$SQL);
	}
	fclose($file);
	unlink($filename);
}

function writeLogFile($str)
{
  if(file_exists("gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("gssb_to_dynsb_log")),sizeof(decoct(fileperms("gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "gssb_to_dynsb_log/V".date('Ymd').".log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str."\n";
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}

?>
