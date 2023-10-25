<?php

require("./../conf/db.const.inc.php");

require_once("./include/functions.inc.php");

// connect to database server or die
$link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
$link->query("SET NAMES 'utf8'");

//     echo "<b>Datenbank: ".$dbDatabase."</b><br/>";

//define the path as relative
$path = ".";

//using the opendir function
$dir_handle = @opendir($path) or die("Unable to open $path");

//echo "Directory Listing of $path<br/>";

//running the while loop
while ($file = readdir($dir_handle)) 
{

  if (preg_match('~sslserverdata_from_client_(.*)\.dat~i', $file, $matches)) 
  {    
//     echo "<a href='$file'>$file</a><br/>";
     $lines = file($file);    
    for($i=0;$i<count($lines);$i++)
    {
      $action = getAction($lines[$i]);
      if (preg_match('~gssb_to_dynsb: (.*)~i', $lines[$i], $matches))
      {
//        echo trim($matches[1])."<br/>";
        $CountryCode = getCountryCode($lines[$i]);
        if ("i" == $action && "1" == getCoding($lines[$i]) && "_" == $CountryCode)
        {
          InsertUpdateItemdata(trim($matches[1]));
        }
        else if ("i" == $action && "1" == getCoding($lines[$i]) && "_" != $CountryCode)
        {
          InsertUpdateItemPrice(trim($matches[1]),$CountryCode);
        }
        else if ("d" == $action)
        {
          DeleteItem(trim($matches[1]));
        }
      }
      if (preg_match('~gssbph_to_dynsbph: (.*)~i', $lines[$i], $matches))
      {
        DoPriceHistory(trim($matches[1]));
      }
      if (preg_match('~gssbdl_to_dynsbdl: (.*)~i', $lines[$i], $matches))
      {
        if ("d" == $action)
        {
          DeleteDownloadArticle(trim($matches[1]));
        }
        else
        {
          InsertUpdateDownloadArticle(trim($matches[1]));
        } 
      }
    }
  }
}

//closing the directory
closedir($dir_handle);



function runSQL($sql) {
	global $link;
	$qry = @mysqli_query($link,$sql);
	if ($qry == "1")
	{
  	echo " => ok<br/>"; 
  }
  else
  {
  	echo " => ".$qry."<br/>"; 
  }
  //echo "".$sql."<br/>";
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
//        echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
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
//        echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
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
//        echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
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
//        echo "<b>gefunden key=".$key.", value=".$PriceChangeDate."</b><br/>";
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
//        echo "<b>gefunden key=".$key.", value=".$value."</b><br/>";
        return $value; 
      }
    }
  }
  return null;
}


function DeleteDownloadArticle($linedata)
{
	global $link;
  $arr_datensatz = explode('&',$linedata);
  $ItemNumber = getItemNumber($arr_datensatz);
  $LanguageId = getLanguageId($arr_datensatz);
  $itemdata_tab = DBToken."itemdata";
  $downloadarticle_tab = DBToken."downloadarticle";
  if(isset($ItemNumber) && isset($LanguageId))
  {
    $SQL = "SELECT * FROM ".$itemdata_tab." where itemItemNumber='".$ItemNumber."' AND itemLanguageId='".$LanguageId."';";
    $qry = @mysqli_query($link,$SQL);
    $num = @mysqli_num_rows($qry);
    if($num>0)
    {
      echo "DELETE downloadarticle: ".$ItemNumber;
      $SQL = "UPDATE ".$itemdata_tab." SET itemIsDownloadArticle='F' where itemItemNumber='".$ItemNumber."' AND itemLanguageId = '".$LanguageId."'";
      runSQL($SQL);
  
      $SQL = "DELETE FROM ".$downloadarticle_tab." where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId = '".$LanguageId."'";
      runSQL($SQL);
    }
  }
}

function DeleteItem($linedata)
{
  $arr_datensatz = explode('&',$linedata);
  $ItemNumber = getItemNumber($arr_datensatz);
  $LanguageId = getLanguageId($arr_datensatz);
  $itemdata_tab = DBToken."itemdata";
  $price_tab = DBToken."price";
  $downloadarticle_tab = DBToken."downloadarticle";

  echo "DELETE item: ".$ItemNumber;

  $SQL = "DELETE FROM ".$itemdata_tab." where itemItemNumber='".$ItemNumber."' AND itemLanguageId = '".$LanguageId."'";
  runSQL($SQL);
  if($qry)
  {
    $SQL = "DELETE FROM ".$price_tab." where prcItemNumber='".$ItemNumber."' AND prcCountryId = '".$LanguageId."'";
    runSQL($SQL);
    
    // hinzugefügt !!!
    $SQL = "DELETE FROM ".$downloadarticle_tab." where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId = '".$LanguageId."'";
    runSQL($SQL);
  }
}

function DoPriceHistory($linedata)
{
	global $link;
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
    
    $SQL = "SELECT * FROM ".DBToken."pricehistory where prchItemNumber='".$ItemNumber."' AND prchLanguageId='".$LanguageId."'
          AND prchDateTime = '".$PriceChangeDate."'";
    $qry = @mysqli_query($link,$SQL);
    $num = @mysqli_num_rows($qry);
    if($num == 0)
    {
        $insertSQL1 = substr($insertSQL1, 0, sizeof($insertSQL1)-3);
        $insertSQL2 = substr($insertSQL2, 0, sizeof($insertSQL2)-3);
  
        echo "INSERT PriceHistory for item: ".$ItemNumber;
        $SQL = "INSERT INTO ".DBToken."pricehistory (".$insertSQL1.") values(".$insertSQL2.");";
        runSQL($SQL);
    }
  }

}

function InsertUpdateDownloadArticle($linedata)
{
	global $link;
  $arr_datensatz = explode('&',$linedata);
  $ItemNumber = getItemNumber($arr_datensatz);
  $LanguageId = getLanguageId($arr_datensatz);
  $LanguageId = base64_decode($LanguageId);
  if(isset($ItemNumber) && isset($LanguageId))
  {
    $downloadarticle_tab = DBToken."downloadarticle";

    $insertSQL1 = "downloadItemNumber, downloadLanguageId, ";
    $insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
    $updateSQL = "";

    for($j=0;$j<count($arr_datensatz);$j++)
    {
      $pos = strpos($arr_datensatz[$j], '=');
      if ($pos !== false)
      {
        $key=substr($arr_datensatz[$j], 0, $pos);      
        $value= substr($arr_datensatz[$j], $pos+1);
        $value=base64_decode($value);     
        
        if ($value!="")
        {

          if ($key == "Filename" || $key == "NumberDownloads"  || $key == "Watermarktext"
              || $key == "$WatermarkPosition" || $key == "$WatermarkIntensity"
              )
          {
            if ($key == "NumberDownloads")
            {
              $key = "AllowedDownloads";
            }
            $key = "download".$key;
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".$value."', ";
            $updateSQL .= $key." = '".$value."', ";
          }
        }
      }
    }
    $SQL = "SELECT * FROM ".$downloadarticle_tab." where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId='".$LanguageId."';";
    $qry = @mysqli_query($link,$SQL);

    $num = @mysqli_num_rows($qry);
    if($num==0)
    {
      $insertSQL1 = substr($insertSQL1,0,sizeof($insertSQL1)-3);
      $insertSQL2 = substr($insertSQL2,0,sizeof($insertSQL2)-3);

      echo "INSERT Downloadarticle for item: ".$ItemNumber;
      $SQL = "INSERT INTO ".$downloadarticle_tab." (".$insertSQL1.", downloadCreateTime, downloadUpdateTime) values(".$insertSQL2.", '".date("YmdHis")."', '".date("YmdHis")."');";
      runSQL($SQL);
    }
    else
    {
      $updateSQL = substr($updateSQL,0,sizeof($updateSQL)-3);

      echo "UPDATE Downloadarticle for item: ".$ItemNumber;
      $SQL = "UPDATE ".$downloadarticle_tab." set ".$updateSQL.", downloadUpdateTime = '".date("YmdHis")."' where downloadItemNumber='".$ItemNumber."' AND downloadLanguageId='".$LanguageId."';";
      runSQL($SQL);
    }  
  }
}

function InsertUpdateItemdata($linedata)
{
	global $link;
  $arr_datensatz = explode('&',$linedata);
  $itemdata_tab = DBToken."itemdata";
  $ItemNumber = getItemNumber($arr_datensatz);
  $LanguageId = getLanguageId($arr_datensatz);

  if(isset($ItemNumber) && isset($LanguageId))
  {
    $insertSQL1 = "itemItemNumber, itemLanguageId, ";
    $insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
    $updateSQL = "";
    
  
    for($j=0;$j<count($arr_datensatz);$j++)
    {
  //    echo $arr_datensatz[$j]."<br/>";
      $pos = strpos($arr_datensatz[$j], '=');
      if ($pos !== false)
      {
        $key=substr($arr_datensatz[$j], 0, $pos);      
        $value= substr($arr_datensatz[$j], $pos+1);
        $value=base64_decode($value);  

		//echo $key." = ".$value." <br />";
        
		//A TS 04.06.2012: CentralTextNr als key hinzugefügt
		//A TS 04.06.2012: UseCentralText als key hinzugefügt
		//A TS 01.06.2012
		//Natürlich müssen auch leere Werte geschrieben werden,
		//falls der Inhalt eines Feldes gelöscht wurde
        //if ($value!="")
        //{
          if ($key == "ItemText" || $key == "ItemDescription"  || $key == "ItemProductGroupName"
              || $key == "ItemProductGroupIdNo" || $key == "VariantDescription"  || $key == "ItemProductGroupName"
              || $key == "DetailText1" || $key == "DetailText2"  || $key == "Attribute1"
              || $key == "Attribute2" || $key == "Attribute3" || $key == "Manufacturer"
              || $key == "ManufacturerProductCode" || $key == "EAN_ISBN" || $key == "Brand"
			  || $key == "CentralTextNr" || $key == "UseCentralText"
              )
          {
			if ($key != "ItemProductGroupName" && $key != "ItemProductGroupIdNo")
            {
              $key = "item".$key;
            }
			
			//A TS 05.06.2012
			//MySQL kennt kein "Wahr" oder "Falsch", also "1" oder "0"
			if ($key == "itemUseCentralText")
			{
				if($value == "Wahr")
				{
					$value = "1";
				}
				else
				{
					$value = "0";
				}
			}
			//E TS
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".addslashes($value)."', ";
            $updateSQL .= $key." = '".addslashes($value)."', ";  
			
          }
		  //A TS 18.05.2012: "ItemId" als key hinzugefügt
		  else if ($key == "ItemPage" || $key == "ItemLink"  || $key == "SmallImageFile" 
              || $key == "SmallImageLink" || $key == "MediumImageFile"  || $key == "MediumImageLink"
              || $key == "BigImageFile" || $key == "BigImageLink"  || $key == "VATRate"
              || $key == "IsActive"  || $key == "IsVariant"
              || $key == "IsTextHasNoPrice" || $key == "HasDetail"  || $key == "LargeImage2File"
              || $key == "LargeImage2Link" || $key == "LargeImage3File"  || $key == "LargeImage3Link"
              || $key == "NewFlag" || $key == "IsCatalogFlag"  || $key == "IsDownloadArticle"
              || $key == "IsBonusArticle" || $key == "PriceBonusPoints"  || $key == "HasInquiry"
			  || $key == "ItemId"
                  )
          {
            if ($key == "BigImageFile")
            {
              $key = BigImage1File;
            }                  
            else if ($key == "BigImageLink")
            {
              $key = "BigImage1Link";
            }                  
            else if ($key == "NewFlag")
            {
              $key = "IsNewItem";
            }                  
            else if ($key == "IsCatalogFlag")
            {
              $key = "IsCatalogFlg";
            }                  
            else if ($key == "PriceBonusPoints")
            {
              $key = "BonusPointsPrice";
            }  
			
            $key = "item".$key;
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".$value."', ";
            $updateSQL .= $key." = '".$value."', ";
          }
          else if ($key == "InStockQuantity" || $key == "Weight")
          {
            $key = "item".$key;
            $value = str_replace(",",".",$value);     // Hier auch für "Weight" eingefügt. 
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".$value."', ";
            $updateSQL .= $key." = '".$value."', ";
          } 
        //}   
  
  //      echo "key=".$key.", value=".$value."<br/>";  
      }
    }
  
    //SS20091105 Die Warenverfügbarkeit auf Standard setzen
    $insertSQL1 .= "itemShipmentStatus, ";                  
    $insertSQL2 .= "'-1', ";
    
    $SQL = "SELECT * FROM ".$itemdata_tab." where itemItemNumber='".$ItemNumber."' AND itemLanguageId='".$LanguageId."';";
    $qry = @mysqli_query($link,$SQL);
    $num = @mysqli_num_rows($qry);
    if($num==0)
    {
      //echo "<b>INSERT INTO ".$itemdata_tab." (".$insertSQL1."</b><br/>";
      $SQL = "INSERT INTO ".$itemdata_tab." (".$insertSQL1." itemCreateTime, itemUpdateTime) values(".$insertSQL2."'".date("YmdHis")."', '".date("YmdHis")."');";
      echo "INSERT item: ".$ItemNumber;
      runSQL($SQL);
    }
    else
    {
      $SQL = "UPDATE ".$itemdata_tab." set ".$updateSQL." itemUpdateTime = '".date("YmdHis")."' where itemItemNumber='".$ItemNumber."' AND itemLanguageId='".$LanguageId."';";
      echo "UPDATE item: ".$ItemNumber;
      runSQL($SQL);
    }
  }  
}


function InsertUpdateItemPrice($linedata,$CountryCode)
{
	global $link;
  $arr_datensatz = explode('&',$linedata);
  $price_tab = DBToken."price";
  $ItemNumber = getItemNumber($arr_datensatz);
  $QuantityFrom = getQuantityFrom($arr_datensatz);

  if(isset($ItemNumber) && isset($QuantityFrom))
  {
    $insertSQL1 = "prcItemNumber, prcCountryId, ";
    $insertSQL2 = "'".$ItemNumber."', '".$CountryCode."', ";
    $updateSQL = "";

    for($j=0;$j<count($arr_datensatz);$j++)
    {
//      echo $arr_datensatz[$j]."<br/>";
      $pos = strpos($arr_datensatz[$j], '=');
      if ($pos !== false)
      {
        $key=substr($arr_datensatz[$j], 0, $pos);      
        $value= substr($arr_datensatz[$j], $pos+1);
        $value=base64_decode($value);     
        
        if ($value!="")
        {
          if ($key == "Price" || $key == "QuantityFrom" || $key == "QuantityTo" || $key == "OldPrice" 
              || $key == "ShippingPrice" || $key == "ReferencePrice" || $key == "ReferenceQuantity" 
              )
          {
            $key = "prc".$key;
            $value = str_replace(",",".",$value);            
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".$value."', ";
            $updateSQL .= $key." = '".$value."', ";
          } 
          else if ($key == "SalesTaxNo" || $key == "QuantityNo" || $key == "ItemCount"
              || $key == "ReferenceUnit"
            )
          {
            $key = "prc".$key;
            $insertSQL1 .= $key.", ";           
            $insertSQL2 .= "'".$value."', ";
            $updateSQL .= $key." = '".$value."', ";

          } 
        }
      }
    }
    $insertSQL1 = substr($insertSQL1, 0, sizeof($insertSQL1)-3);
    $insertSQL2 = substr($insertSQL2, 0, sizeof($insertSQL2)-3);
    $updateSQL = substr($updateSQL,0,sizeof($updateSQL)-3);
        
    $SQL = "SELECT * FROM ".$price_tab." where prcItemNumber='".$ItemNumber."' AND prcCountryId='".$CountryCode."';";
    $qry = @mysqli_query($link,$SQL);
    $num = @mysqli_num_rows($qry);
    if($num==0)
    {
      $SQL = "INSERT INTO ".$price_tab." (".$insertSQL1.") values(".$insertSQL2.");";
      echo "INSERT price for item: ".$ItemNumber;
      runSQL($SQL);
    }
    else
    {
      echo "UPDATE price for item: ".$ItemNumber;
      $SQL = "UPDATE ".$price_tab." set ".$updateSQL." where prcItemNumber='".$ItemNumber."' AND prcCountryId='".$CountryCode."';";
      runSQL($SQL);
    }
    
  }
}

?> 
