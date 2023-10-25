<?php
/*
    Script - gssbph_to_dynsbph.php
    Author: Sergej Schaschkov / GS Software Solutions GmbH

    File: gssbph_to_dynsbph.php
    
    Übertragung der Preishistorie
*/


if(file_exists("../conf/db.const.inc.php"))
{
  require("../conf/db.const.inc.php");
  require_once("include/functions.inc.php");
}

foreach($_REQUEST as $key => $value)
{
  $value = str_replace(" ","+",$value);
  $$key = trim($value);

}

if(isset($session) && base64_decode($session)==$gssbTimeId.$shopURL)
{
    $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
    $link->query("SET NAMES 'utf8'");

    if(isset($ItemNumber) && $ItemNumber != "") 
    {                  
        $ItemNumber = base64_decode($ItemNumber);
        $insertSQL1 .= "prchItemNumber, ";                  
        $insertSQL2 .= "'".$ItemNumber."', ";
    }
    if(isset($LanguageId) && $LanguageId != "") 
    {                  
        $LanguageId = base64_decode($LanguageId); 
        $insertSQL1 .= "prchLanguageId, ";                  
        $insertSQL2 .= "'".$LanguageId."', ";
    }                                     
    if(isset($PriceChangeDate) && $PriceChangeDate != "") 
    {      
        $PriceChangeDate = base64_decode($PriceChangeDate);
		$dt_array = explode(" ", $PriceChangeDate);
		$date_arr = explode(".", $dt_array[0]);
		$time_arr = explode(":", $dt_array[1]);
		$PriceChangeDate = $date_arr[2]."-".$date_arr[1]."-".$date_arr[0]." ".$time_arr[0].":".$time_arr[1].":".$time_arr[2];
        $insertSQL1 .= "prchDateTime, ";           
        $insertSQL2 .= "'".$PriceChangeDate."', ";
    }
    if(isset($ItemPrice) && $ItemPrice != "") 
    {           
        $ItemPrice = base64_decode($ItemPrice);
        $insertSQL1 .= "prchPrice, ";           
        $insertSQL2 .= "'".$ItemPrice."', "; 
    }

    $SQL = "SELECT * FROM ".DBToken."pricehistory where prchItemNumber='".$ItemNumber."' AND prchLanguageId='".$LanguageId."'
            AND prchPrice = '".$ItemPrice."' ORDER BY prchDateTime DESC";
			
    $qry = @mysqli_query($link,$SQL);
	$obj = @mysqli_fetch_object($qry);
    $num = @mysqli_num_rows($qry);
    if($num == 0 || $obj->prchPrice != $ItemPrice)
    {
        $insertSQL1 = substr($insertSQL1, 0, sizeof($insertSQL1)-3);
        $insertSQL2 = substr($insertSQL2, 0, sizeof($insertSQL2)-3);
  
        $SQL = "INSERT INTO ".DBToken."pricehistory (".$insertSQL1.") values(".$insertSQL2.");";
        writeLogFile($SQL);
        $qry = @mysqli_query($link,$SQL);
    }
}
else
{
    writeLogFile("Error 101 - permission denied");
}

function writeLogFile($str)
{

  if(file_exists("gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("gssb_to_dynsb_log")),sizeof(decoct(fileperms("gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "gssb_to_dynsb_log/".date('Ymd')."PH.log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str."\n";
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}
?>
