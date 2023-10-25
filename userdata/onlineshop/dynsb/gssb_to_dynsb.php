<?php
/*
    Mail Script - gssb_to_dynsb.php
    Author: Sabine Salzsiedler / GS Software Solutions GmbH

    File: gssb_to_dynsb.php
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

$ItemNumber = base64_decode($ItemNumber);

if(isset($session) && base64_decode($session)==$gssbTimeId.$shopURL)
{
  $link = @mysqli_connect($dbServer, $dbUser, $dbPass, $dbDatabase) or die("<br />aborted: can´t connect to '$dbServer' <br />");
  $link->query("SET NAMES 'utf8'");
  $itemdata_tab = DBToken."itemdata";
  $price_tab = DBToken."price";


  $insertSQL1 = "itemItemNumber, itemLanguageId, ";
  $insertSQL2 = "'".$ItemNumber."', '".$LanguageId."', ";
  $updateSQL = "";

  if($action == "i")
  {
    if(isset($LanguageId) && isset($ItemNumber))
    {
      //itemdata
      if($coding==1)
      {
 //AB
        if(isset($ObjectCount)&&$ObjectCount!="") 
        {                    
          $ObjectCount = base64_decode($ObjectCount);
          $insertSQL1 .= "itemItemId, ";    
          $insertSQL2 .= "'".addslashes($ObjectCount)."', ";
          $updateSQL .= "itemItemId = '".addslashes($ObjectCount)."', ";
        } 
        if(isset($ItemText)) 
        {                    
          $ItemText = base64_decode($ItemText);
          $insertSQL1 .= "itemItemText, ";    
          $insertSQL2 .= "'".addslashes($ItemText)."', ";
          $updateSQL .= "itemItemText = '".addslashes($ItemText)."', ";
        }
        if(isset($ItemPage)) 
        {                  
          $ItemPage = base64_decode($ItemPage); 
          $insertSQL1 .= "itemItemPage, ";                  
          $insertSQL2 .= "'".$ItemPage."', ";
          $updateSQL .= $sqlstr .= "itemItemPage = '".$ItemPage."', ";
        }
        if(isset($ItemDescription)) 
        {           
          $ItemDescription = base64_decode($ItemDescription);
          $insertSQL1 .= "itemItemDescription, ";           
          $insertSQL2 .= "'".addslashes($ItemDescription)."', "; 
          $updateSQL .= "itemItemDescription = '".addslashes($ItemDescription)."', ";
        }
        
        if(isset($ItemProductGroupName)) 
        {      
          $ItemProductGroupName = base64_decode($ItemProductGroupName); 
          $insertSQL1 .= "itemProductGroupName, ";           
          $insertSQL2 .= "'".addslashes($ItemProductGroupName)."', ";
          $updateSQL .= "itemProductGroupName = '".addslashes($ItemProductGroupName)."', ";  
        }
        
        if(isset($ItemProductGroupIdNo)) 
        {      
          $ItemProductGroupIdNo = base64_decode($ItemProductGroupIdNo); 
          $insertSQL1 .= "itemProductGroupIdNo, ";           
          $insertSQL2 .= "'".addslashes($ItemProductGroupIdNo)."', ";
          $updateSQL .= "itemProductGroupIdNo = '".addslashes($ItemProductGroupIdNo)."', ";  
        }
        
        if(isset($VariantDescription)) 
        {        
          $VariantDescription = base64_decode($VariantDescription);
          $insertSQL1 .= "itemVariantDescription, ";        
          $insertSQL2 .= "'".addslashes($VariantDescription)."', ";  
          $updateSQL .= "itemVariantDescription = '".addslashes($VariantDescription)."', "; 
        }

        if(isset($ItemLink)) 
        {                  
          $ItemLink = base64_decode($ItemLink);
          $insertSQL1 .= "itemItemLink, ";                  
          $insertSQL2 .= "'".$ItemLink."', ";  
          $updateSQL .= "itemItemLink = '".$ItemLink."', "; 
        }
        if(isset($DetailText1)) 
        {               
          $DetailText1 = base64_decode($DetailText1);
          $insertSQL1 .= "itemDetailText1, ";               
          $insertSQL2 .= "'".addslashes($DetailText1)."', ";  
          $updateSQL .= "itemDetailText1 = '".addslashes($DetailText1)."', "; 
        }
        if(isset($DetailText2)) 
        {               
          $DetailText2 = base64_decode($DetailText2);
          $insertSQL1 .= "itemDetailText2, ";               
          $insertSQL2 .= "'".addslashes($DetailText2)."', ";  
          $updateSQL .= "itemDetailText2 = '".addslashes($DetailText2)."', "; 
        }
        if(isset($SmallImageFile)) 
        {            
          $SmallImageFile = base64_decode($SmallImageFile); 
          $insertSQL1 .= "itemSmallImageFile, ";            
          $insertSQL2 .= "'".$SmallImageFile."', "; 
          $updateSQL .= "itemSmallImageFile = '".$SmallImageFile."', "; 
        }
        if(isset($SmallImageLink)) 
        {            
          $SmallImageLink = base64_decode($SmallImageLink); 
          $insertSQL1 .= "itemSmallImageLink, ";            
          $insertSQL2 .= "'".$SmallImageLink."', "; 
          $updateSQL .= "itemSmallImageLink = '".$SmallImageLink."', "; 
        }
        if(isset($MediumImageFile)) 
        {           
          $MediumImageFile = base64_decode($MediumImageFile); 
          $insertSQL1 .= "itemMediumImageFile, ";           
          $insertSQL2 .= "'".$MediumImageFile."', ";   
          $updateSQL .= "itemMediumImageFile = '".$MediumImageFile."', ";       
        }
        if(isset($MediumImageLink)) 
        {           
          $MediumImageLink = base64_decode($MediumImageLink);
          $insertSQL1 .= "itemMediumImageLink, ";           
          $insertSQL2 .= "'".$MediumImageLink."', ";  
          $updateSQL .= "itemMediumImageLink = '".$MediumImageLink."', "; 
        }
        if(isset($BigImageFile)) 
        {              
          $BigImageFile = base64_decode($BigImageFile);
          $insertSQL1 .= "itemBigImage1File, ";              
          $insertSQL2 .= "'".$BigImageFile."', "; 
          $updateSQL .= "itemBigImage1File = '".$BigImageFile."', ";  
        }
        if(isset($BigImageLink)) 
        {              
          $BigImageLink = base64_decode($BigImageLink); 
          $insertSQL1 .= "itemBigImage1Link, ";              
          $insertSQL2 .= "'".$BigImageLink."', "; 
          $updateSQL .= "itemBigImage1Link = '".$BigImageLink."', "; 
        }
        if(isset($Attribute1)) 
        {                
          $Attribute1 = base64_decode($Attribute1);
          $insertSQL1 .= "itemAttribute1, ";                
          $insertSQL2 .= "'".addslashes($Attribute1)."', ";  
          $updateSQL .= "itemAttribute1 = '".addslashes($Attribute1)."', "; 
        }
        if(isset($Attribute2)) 
        {                
          $Attribute2 = base64_decode($Attribute2);
          $insertSQL1 .= "itemAttribute2, ";                
          $insertSQL2 .= "'".addslashes($Attribute2)."', ";  
          $updateSQL .= "itemAttribute2 = '".addslashes($Attribute2)."', "; 
        }
        if(isset($Attribute3)) 
        {                
          $Attribute3 = base64_decode($Attribute3); 
          $insertSQL1 .= "itemAttribute3, ";                
          $insertSQL2 .= "'".addslashes($Attribute3)."', "; 
          $updateSQL .= "itemAttribute3 = '".addslashes($Attribute3)."', "; 
        }
        if(isset($VATRate)) 
        {                   
          $VATRate = base64_decode($VATRate); 
          $insertSQL1 .= "itemVATRate, ";                   
          $insertSQL2 .= "'".$VATRate."', "; 
          $updateSQL .= "itemVATRate = '".$VATRate."', "; 
        }
        if(isset($Weight)) 
        {                   
          $Weight = base64_decode($Weight); 
          $insertSQL1 .= "itemWeight, ";                   
          $insertSQL2 .= "'".$Weight."', "; 
          $updateSQL .= "itemWeight = '".$Weight."', "; 
        }        
        if(isset($IsActive)) 
        {                  
          $IsActive = base64_decode($IsActive);
          $insertSQL1 .= "itemIsActive, ";                  
          $insertSQL2 .= "'".$IsActive."', ";  
          $updateSQL .= "itemIsActive = '".$IsActive."', "; 
        }

        if(isset($IsVariant)) 
        {                 
          $IsVariant = base64_decode($IsVariant); 
          $insertSQL1 .= "itemIsVariant, ";                 
          $insertSQL2 .= "'".$IsVariant."', "; 
          $updateSQL .= "itemIsVariant = '".$IsVariant."', "; 
        }
        if(isset($isAction)) 
        {                  
          $insertSQL1 .= "itemIsAction, ";                  
          $insertSQL2 .= "'".$isAction."', ";  
          $updateSQL .= "itemIsAction = '".$isAction."', "; 
        }	
        if(isset($useCText)) 
        {                  
          $insertSQL1 .= "itemUseCentralText, ";                  
          $insertSQL2 .= "'".$useCText."', ";  
          $updateSQL .= "itemUseCentralText = '".$useCText."', "; 
        }	
        if(isset($cTextNr)) 
        {                  
          $insertSQL1 .= "itemCentralTextNr, ";                  
          $insertSQL2 .= "'".$cTextNr."', ";  
          $updateSQL .= "itemCentralTextNr = '".$cTextNr."', "; 
        }		
        if(isset($IsTextHasNoPrice)) 
        {          
          $IsTextHasNoPrice = base64_decode($IsTextHasNoPrice);
          $insertSQL1 .= "itemIsTextHasNoPrice, ";          
          $insertSQL2 .= "'".$IsTextHasNoPrice."', "; 
          $updateSQL .= "itemIsTextHasNoPrice = '".$IsTextHasNoPrice."', ";  
        }
        if(isset($HasDetail)) 
        {                 
          $HasDetail = base64_decode($HasDetail);
          $insertSQL1 .= "itemHasDetail, ";                 
          $insertSQL2 .= "'".$HasDetail."', ";
          $updateSQL .= "itemHasDetail = '".$HasDetail."', ";   
        }
        if(isset($LargeImage2File)) 
        {           
          $LargeImage2File = base64_decode($LargeImage2File);
          $insertSQL1 .= "itemLargeImage2File, ";           
          $insertSQL2 .= "'".$LargeImage2File."', ";  
          $updateSQL .= "itemLargeImage2File = '".$LargeImage2File."', "; 
        }
        if(isset($LargeImage2Link)) 
        {           
          $LargeImage2Link = base64_decode($LargeImage2Link);
          $insertSQL1 .= "itemLargeImage2Link, ";           
          $insertSQL2 .= "'".$LargeImage2Link."', ";  
          $updateSQL .= "itemLargeImage2Link = '".$LargeImage2Link."', "; 
        }
        if(isset($LargeImage3File)) 
        {           
          $LargeImage3File = base64_decode($LargeImage3File);
          $insertSQL1 .= "itemLargeImage3File, ";           
          $insertSQL2 .= "'".$LargeImage3File."', ";  
          $updateSQL .= "itemLargeImage3File = '".$LargeImage3File."', "; 
        }
        if(isset($LargeImage3Link)) 
        {           
          $LargeImage3Link = base64_decode($LargeImage3Link);
          $insertSQL1 .= "itemLargeImage3Link, ";           
          $insertSQL2 .= "'".$LargeImage3Link."', ";
          $updateSQL .= "itemLargeImage3Link = '".$LargeImage3Link."', ";   
        }
        if(isset($Manufacturer)) 
        {              
          $Manufacturer = base64_decode($Manufacturer);
          $insertSQL1 .= "itemManufacturer, ";              
          $insertSQL2 .= "'".addslashes($Manufacturer)."', ";
          $updateSQL .= "itemManufacturer = '".addslashes($Manufacturer)."', ";   
        }
        if(isset($ManufacturerProductCode)) 
        {   
          $ManufacturerProductCode = base64_decode($ManufacturerProductCode);
          $insertSQL1 .= "itemManufacturerProductCode, ";   
          $insertSQL2 .= "'".addslashes($ManufacturerProductCode)."', ";
          $updateSQL .= "itemManufacturerProductCode = '".addslashes($ManufacturerProductCode)."', ";   
        }
        if(isset($EAN_ISBN)) 
        {                  
          $EAN_ISBN = base64_decode($EAN_ISBN);
          $insertSQL1 .= "itemEAN_ISBN, ";                  
          $insertSQL2 .= "'".addslashes($EAN_ISBN)."', ";
          $updateSQL .= "itemEAN_ISBN = '".addslashes($EAN_ISBN)."', ";   
        }
        if(isset($Brand)) 
        {                     
          $Brand = base64_decode($Brand); 
          $insertSQL1 .= "itemBrand, ";                     
          $insertSQL2 .= "'".addslashes($Brand)."', ";
          $updateSQL .= "itemBrand = '".addslashes($Brand)."', ";  
        }
        if(isset($InStockQuantity)&&$InStockQuantity!="") 
        {           
          $InStockQuantity = base64_decode($InStockQuantity);
          $InStockQuantity = str_replace(",",".",$InStockQuantity);
		      $insertSQL1 .= "itemInStockQuantity, ";           
          $insertSQL2 .= "'".$InStockQuantity."', ";
		      $updateSQL .= "itemInStockQuantity = '".$InStockQuantity."', ";  
        }
        if(isset($NewFlag)) 
        {                    
          
          $NewFlag = base64_decode($NewFlag);
          $NewFlag = substr($NewFlag,0,1); 
          $insertSQL1 .= "itemIsNewItem, ";                 
          $insertSQL2 .= "'".$NewFlag."', "; 
          $updateSQL .= "itemIsNewItem = '".$NewFlag."', "; 
        }
//A UR 29.12.2009
        if(isset($IsCatalogFlag)) 
        {                    
          
          $IsCatalogFlag = base64_decode($IsCatalogFlag);
          $IsCatalogFlag = substr($IsCatalogFlag,0,1); 
          $insertSQL1 .= "itemIsCatalogFlg, ";                 
          $insertSQL2 .= "'".$IsCatalogFlag."', "; 
          $updateSQL .= "itemIsCatalogFlg = '".$IsCatalogFlag."', "; 
        }
        if(isset($IsDownloadArticle)) 
        {                    
          
          $IsDownloadArticle = base64_decode($IsDownloadArticle);
          $IsDownloadArticle = substr($IsDownloadArticle,0,1); 
          $insertSQL1 .= "itemIsDownloadArticle, ";                 
          $insertSQL2 .= "'".$IsDownloadArticle."', "; 
          $updateSQL .= "itemIsDownloadArticle = '".$IsDownloadArticle."', "; 
        }
// 23.12.2010        
        if(isset($SpecPostage)) 
        {                              
          $SpecPostage = base64_decode($SpecPostage);
          $insertSQL1 .= "itemSpecPostage, ";                 
          $insertSQL2 .= "'".$SpecPostage."', "; 
          $updateSQL .= "itemSpecPostage = '".$SpecPostage."', ";           
        }
//E UR     		
		if(isset($IsBonusArticle)) 
        {                    
          
          $IsBonusArticle = base64_decode($IsBonusArticle);
		  if(empty($IsBonusArticle)) { $IsBonusArticle = 'FALSE'; }
          $IsBonusArticle = substr($IsBonusArticle,0,1); 
          $insertSQL1 .= "itemIsBonusArticle, ";                 
          $insertSQL2 .= "'".$IsBonusArticle."', "; 
          $updateSQL .= "itemIsBonusArticle = '".$IsBonusArticle."', "; 
        }
		
		if(isset($PriceBonusPoints)) 
        {                    
          
          $PriceBonusPoints = base64_decode($PriceBonusPoints);
          $insertSQL1 .= "itemBonusPointsPrice, ";                 
          $insertSQL2 .= "'".$PriceBonusPoints."', "; 
          $updateSQL .= "itemBonusPointsPrice = '".$PriceBonusPoints."', "; 
        }		
//A UR 23.2.2010
		if(isset($HasInquiry)) 
        {                    
          
          $HasInquiry = base64_decode($HasInquiry);
		  if(empty($HasInquiry)) { $HasInquiry = 'FALSE'; }
          $HasInquiry = substr($HasInquiry,0,1); 
          $insertSQL1 .= "itemHasInquiry, ";                 
          $insertSQL2 .= "'".$HasInquiry."', "; 
          $updateSQL .= "itemHasInquiry = '".$HasInquiry."', "; 
        }
		
		if(isset($IsTextInput)) 
        {                    
          
          $IsTextInput = base64_decode($IsTextInput);
          $IsTextInput = substr($IsTextInput,0,1); 
          $insertSQL1 .= "itemIsTextInput, ";                 
          $insertSQL2 .= "'".$IsTextInput."', "; 
          $updateSQL .= "itemIsTextInput = '".$IsTextInput."', "; 
        }
//E UR     		
    }
      
      //SS20091105 Die Warenverfügbarkeit auf Standard setzen
      $insertSQL1 .= "itemShipmentStatus, ";                  
      $insertSQL2 .= "'-1', ";

      $SQL = "SELECT * FROM ".$itemdata_tab." where itemItemId='".$ObjectCount."' AND itemItemNumber='".$ItemNumber."' AND itemLanguageId='".$LanguageId."';";
      $qry = @mysqli_query($link,$SQL);
      $num = @mysqli_num_rows($qry);
      if($num==0)
      {
    
        $insertSQL1 = substr($insertSQL1,0,sizeof($insertSQL1)-3);
        $insertSQL2 = substr($insertSQL2,0,sizeof($insertSQL2)-3);

        $SQL = "INSERT INTO ".$itemdata_tab." (".$insertSQL1.", itemCreateTime, itemUpdateTime) values(".$insertSQL2.", '".date("YmdHis")."', '".date("YmdHis")."');";
        writeLogFile($SQL);
        $qry = @mysqli_query($link,$SQL);
      }
      else
      {
        //SS20091105
        $obj = @mysqli_fetch_object($qry);
        if($obj->itemShipmentStatus == 0) 
        { 
            $ShipmentStatus = -1;
        } 
        else 
        {
            $ShipmentStatus = $obj->itemShipmentStatus;   
        }
        
        $updateSQL .= "itemShipmentStatus = '".$ShipmentStatus."', ";
        $updateSQL = substr($updateSQL,0,sizeof($updateSQL)-3);

        $SQL = "UPDATE ".$itemdata_tab." set ".$updateSQL.", itemUpdateTime = '".date("YmdHis")."' where itemItemNumber='".$ItemNumber."' AND itemLanguageId='".$LanguageId."';";
        writeLogFile($SQL);
        $qry = @mysqli_query($link,$SQL);
      }
    }
    if(isset($CountryCode) && isset($ItemNumber) && isset($QuantityFrom))
    { //price
      if($coding==1)
      {
                                            
        if(isset($Price)) {             $Price = base64_decode($Price); }
        if(isset($QuantityFrom)) {      $QuantityFrom = base64_decode($QuantityFrom); }
        if(isset($QuantityTo)) {        $QuantityTo = base64_decode($QuantityTo); }
        if(isset($SalesTaxNo)) {        $SalesTaxNo = base64_decode($SalesTaxNo); }
        if(isset($QuantityNo)) {        $QuantityNo = base64_decode($QuantityNo); }
        if(isset($ItemCount)) {         $ItemCount = base64_decode($ItemCount); }
        if(isset($OldPrice)) {          $OldPrice = base64_decode($OldPrice); }
        if(isset($ShippingPrice)) {     $ShippingPrice = base64_decode($ShippingPrice); }
        if(isset($ReferencePrice)){     $ReferencePrice = base64_decode($ReferencePrice); }
	      if(isset($ReferenceUnit)) {     $ReferenceUnit = base64_decode($ReferenceUnit); }
        if(isset($ReferenceQuantity)) { $ReferenceQuantity = base64_decode($ReferenceQuantity); }
      }
      $SQL = "SELECT * FROM ".$price_tab." where prcItemNumber='".$ItemNumber."' AND prcCountryId='".$CountryCode."' AND prcQuantityFrom='".$QuantityFrom."';";
      $qry = @mysqli_query($link,$SQL);
      $num = @mysqli_num_rows($qry);
      if($num==0)
      {
        $colstr .= "prcItemNumber, prcCountryId, ";
        $valstr .= "'".$ItemNumber."', '".$CountryCode."', ";

        if(isset($Price)) 
        {
      		$Price = str_replace(",",".",$Price);
      		$colstr .= "prcPrice, ";               $valstr .= "'".$Price."', ";
      	}
        if(isset($QuantityFrom))
        {
      		$QuantityFrom = str_replace(",",".",$QuantityFrom);
      		$colstr .= "prcQuantityFrom, ";       $valstr .= "'".$QuantityFrom."', ";
      	}
        if(isset($QuantityTo))
        {
      		$QuantityTo = str_replace(",",".",$QuantityTo);
      		$colstr .= "prcQuantityTo, ";         $valstr .= "'".$QuantityTo."', ";
      	}
        if(isset($SalesTaxNo))
        { $colstr .= "prcSalesTaxNo, ";         $valstr .= "'".$SalesTaxNo."', "; }
        if(isset($QuantityNo))
        { $colstr .= "prcQuantityNo, ";         $valstr .= "'".$QuantityNo."', "; }
        if(isset($ItemCount))
        { $colstr .= "prcItemCount, ";          $valstr .= "'".$ItemCount."', "; }
        if(isset($OldPrice))
        {
      		$OldPrice = str_replace(",",".",$OldPrice);
      		$colstr .= "prcOldPrice, ";           $valstr .= "'".$OldPrice."', ";
      	}
        if(isset($ShippingPrice))
        {
      		$ShippingPrice = str_replace(",",".",$ShippingPrice);
      		$colstr .= "prcShippingPrice, ";      $valstr .= "'".$ShippingPrice."', ";
      	}
        if(isset($ReferencePrice))
        {
      		$ReferencePrice = str_replace(",",".",$ReferencePrice);
      		$colstr .= "prcReferencePrice, ";     $valstr .= "'".$ReferencePrice."', ";
      	}
        if(isset($ReferenceQuantity))
        {
      		$ReferenceQuantity = str_replace(",",".",$ReferenceQuantity);
      		$colstr .= "prcReferenceQuantity, ";    $valstr .= "'".$ReferenceQuantity."', ";
      	}
      	if(isset($ReferenceUnit))
        { $colstr .= "prcReferenceUnit, ";          $valstr .= "'".$ReferenceUnit."', "; }

        $colstr = substr($colstr,0,sizeof($colstr)-3);
        $valstr = substr($valstr,0,sizeof($valstr)-3);

        $SQL = "INSERT INTO ".$price_tab." (".$colstr.") values(".$valstr.");";
        writeLogFile($SQL);
        $qry = @mysqli_query($link,$SQL);
      }
      else
      {
        if(isset($Price))
        {
      		$Price = str_replace(",",".",$Price);
      		$sqlstr .= "prcPrice = '".$Price."', ";
      	}
        if(isset($QuantityFrom))
        {
      		$QuantityFrom = str_replace(",",".",$QuantityFrom);
      		$sqlstr .= "prcQuantityFrom = '".$QuantityFrom."', ";
      	}
        if(isset($QuantityTo))
        {
      		$QuantityTo = str_replace(",",".",$QuantityTo);
      		$sqlstr .= "prcQuantityTo = '".$QuantityTo."', ";
      	}
        if(isset($SalesTaxNo))
        { $sqlstr .= "prcSalesTaxNo = '".$SalesTaxNo."', "; }
        if(isset($QuantityNo))
        { $sqlstr .= "prcQuantityNo = '".$QuantityNo."', "; }
        if(isset($ItemCount))
        { $sqlstr .= "prcItemCount = '".$ItemCount."', "; }
        if(isset($OldPrice))
        {
      		$OldPrice = str_replace(",",".",$OldPrice);
      		$sqlstr .= "prcOldPrice = '".$OldPrice."', ";
      	}
        if(isset($ShippingPrice))
        {
      		$ShippingPrice = str_replace(",",".",$ShippingPrice);
      		$sqlstr .= "prcShippingPrice = '".$ShippingPrice."', ";
      	}
        if(isset($ReferencePrice))
        {
      		$ReferencePrice = str_replace(",",".",$ReferencePrice);
      		$sqlstr .= "prcReferencePrice = '".$ReferencePrice."', ";
      	}
        if(isset($ReferenceQuantity))
        {
      		$ReferenceQuantity = str_replace(",",".",$ReferenceQuantity);
      		$sqlstr .= "prcReferenceQuantity = '".$ReferenceQuantity."', ";
      	}
      	if(isset($ReferenceUnit))
        { 
          $sqlstr .= "prcReferenceUnit = '".$ReferenceUnit."', "; 
        }
        $sqlstr = substr($sqlstr,0,sizeof($sqlstr)-3);

        $SQL = "UPDATE ".$price_tab." set ".$sqlstr." where prcItemNumber='".$ItemNumber."' AND prcCountryId='".$CountryCode."';";
	      writeLogFile($SQL);
        $qry = @mysqli_query($link,$SQL);
      }
    }
 }
  else if($action == "d")
  {
    $SQL = "DELETE FROM ".$itemdata_tab." where itemItemNumber='".$ItemNumber."' AND itemLanguageId = '".$LanguageId."'";
    writeLogFile($SQL);
    $qry = @mysqli_query($link,$SQL);
    if($qry)
    {
      $SQL = "DELETE FROM ".$price_tab." where prcItemNumber='".$ItemNumber."' AND prcCountryId = '".$LanguageId."'";
      writeLogFile($SQL);
      $qry = @mysqli_query($link,$SQL);
      if($qry)
      {
        //experemintell. schickt die artikelnummer zurück wenn es erfolgreich gelöscht wurde
        echo base64_decode($ItemNumber."=".$LanguageId);
      }
    }
  }
//A UR 30.12.2010
  else if($action == "x")
  {
    //lösche alle nicht mehr benutzten Preise
    if(isset($CountryCode) && isset($ItemNumber))
    {
      $SQL = "DELETE FROM ".$price_tab." where prcItemNumber='".$ItemNumber."' AND prcCountryId = '".$CountryCode."'";
      writeLogFile($SQL);
      $qry = @mysqli_query($link,$SQL);
    }
  }
//E UR    
  
}
//writeLogFile("Error 101 - permission denied");

function writeLogFile($str)
{

  if(file_exists("gssb_to_dynsb_log/readme.txt"))
  {
    $perms = substr(decoct(fileperms("gssb_to_dynsb_log")),sizeof(decoct(fileperms("gssb_to_dynsb_log")))-4,3);
    if($perms=="777")
    {
      $filename = "gssb_to_dynsb_log/".date('Ymd').".log";
      $handle = fopen($filename, "a");
      $content = date('Y-m-d H:i:s')." -> ".$str."\n";
      fwrite($handle, $content);
      fclose($handle);
    }
  }

}
?>
