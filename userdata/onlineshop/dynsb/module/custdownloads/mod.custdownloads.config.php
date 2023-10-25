<?php
/*

    faq module - mod.faq.config.js
    Author: Sabine Salzsiedler / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/



// declare static paths for this module
//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
define("URL_CUSTDOWNLOADS","../module/"."custdownloads/");

if (!isset($this->mvLang) || strlen(trim($this->mvLang)) == 0) 
{
    $lang = "deu";
} 
else 
{
	$lang = $this->mvLang;
	if(!file_exists("../lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");

// tree menu captions

$modName_lang = L_dynsb_CustDownloads;
/*$modName_lang_add = L_dynsb_Add;*/
$modName_lang_edit = L_dynsb_Edit;
// caption info for the about-screen
$modCaption_lang = L_dynsb_GSmodulCustDownloads;

// is this module bound to a domain? (0 = No / 1 = Yes)
$modIsBoundFlg = 0;

// domain to which this module is bound if $modIsBoundFlg = 1
$modDomain = "localhost";

// is setup script necessary (0 = No / 1 = Yes)
$modSetup = 0;

// moduleid - has to be unique !!!
$modId = "ccustdownloads";

// version information
$modVersion = "1.0";

// is this a leaf in the tree menu? (0 = No / 1 = Yes)
$modIsLeaf = 0;

// parent -> 'n1014' for the Modules node
$modParentNode = "n1014";

// link
$modLink = "mod.custdownloads.run.php";

// has subModule (0 = No / 1 = Yes)
$modHasSubModule = 0;

// if subModule = 0 then ""
$modSubModuleFolder = "";

// number of menu-entrys for this module
$modEntryCount = 2;

// for the menustruct
$modTreeStr = "gsm[".$this->mvStartIndex."] = new Array(".$this->mvTreeLevel.", ".$modIsLeaf.", '".$modId."', '".$modParentNode."', '".$modName_lang."', '".$modLink."');\n";
/*$modTreeStr .= "gsm[".($this->mvStartIndex+1)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_add."', '".URL_FAQ."mod.custdownloads.detail.php?act=a&lang=".$lang."');\n";*/
$modTreeStr .= "gsm[".($this->mvStartIndex+1)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_edit."', '".URL_CUSTDOWNLOADS."mod.custdownloads.search.php?lang=".$lang."');\n";
?>
