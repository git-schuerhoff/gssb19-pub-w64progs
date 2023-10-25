<?php

/*
 file: mod.carrier.save.php
*/

require("../include/login.check.inc.php");
require_once("../include/functions.inc.php");
require("../../conf/db.const.inc.php");

if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br>aborted: can´t connect to '$dbServer' <br>");
$link->query("SET NAMES 'utf8'");
$chgApplicId = addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1)); // script name

//------------------------------------------------------------------------------
//
// input validation
//
// needed parameters

if (!isset($_SESSION['SESS_userIdNo']) || strlen(trim($_SESSION['SESS_userIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userIdNo = $_SESSION['SESS_userIdNo'];
}
if (!isset($_SESSION['SESS_userLogin']) || strlen(trim($_SESSION['SESS_userLogin'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_userId = $_SESSION['SESS_userId'];
}
if (!isset($_SESSION['SESS_languageIdNo']) || strlen(trim($_SESSION['SESS_languageIdNo'])) == 0) {
  die ("<br>error: missing session parameter!<br>");
} else {
	$SESS_languageIdNo = $_SESSION['SESS_languageIdNo'];
}

/* query all tables */
//TS 01.03.2017: Nur GSSB-Tabellen
//$sql = "SHOW TABLES FROM $dbDatabase"; 
$sql = "SHOW TABLES LIKE '".DBToken."%' FROM $dbDatabase"; 
if($result = mysqli_query($link,$sql))
{   
  /* add table name to array */  
  while($row = mysqli_fetch_row($result))
  {     
    $found_tables[]=$row[0];   
  } 
} 
else
{   
  die("Error, could not list tables. MySQL Error: " . mysqli_error($link)); 
} 

/* loop through and drop each table */
foreach($found_tables as $table_name)
{
   $sql = "DROP TABLE $database_name.$table_name";
   if($result = mysqli_query($link,$sql))
   {
//        echo "Success - table $table_name deleted.";   
   }   
   else
   {
        echo "Error deleting $table_name. MySQL Error: " . mysqli_error($link) . "";   
   } 
} 

$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
//header( "refresh:5;url=".$path."dynsb/index.php" ); 
header("Location: ".$path."dynsb/index.php");
die();
