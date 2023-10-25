<?php
/*
    (c) 2004-2005 GS Software Solutions GmbH

    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form

*/



// declare static paths for this module
//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));

define("URL_Availability","../module/"."availability/");

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
$modName_lang = L_dynsb_Availability;
$modName_lang_availability = L_dynsb_SetStatus;
$modName_lang_articles = L_dynsb_SetArticleAmount;
$modName_lang_emaillist = L_dynsb_AvailmailList;
$modName_lang_emailsettings = L_dynsb_AvailmailSettings;
// caption info for the about-screen
$modCaption_lang = L_dynsb_GSmodulAvailability;

// is this module bound to a domain? (0 = No / 1 = Yes)
$modIsBoundFlg = 0;

// domain to which this module is bound if $modIsBoundFlg = 1
$modDomain = "localhost";

// is setup script necessary (0 = No / 1 = Yes)
$modSetup = 0;

// moduleid - has to be unique !!!
$modId = "ava";

// version information
$modVersion = "1.0";

// is this a leaf in the tree menu? (0 = No / 1 = Yes)
$modIsLeaf = 0;

// parent -> 'n1014' for the Modules node
$modParentNode = "n1014";

// link
$modLink = "mod.availability.run.php";

// has subModule (0 = No / 1 = Yes)
$modHasSubModule = 0;

// if subModule = 0 then ""
$modSubModuleFolder = "";

// number of menu-entrys for this module
$modEntryCount = 5;

// for the menustruct
$modTreeStr = "gsm[".$this->mvStartIndex."] = new Array(".$this->mvTreeLevel.", ".$modIsLeaf.", '".$modId."', '".$modParentNode."', '".$modName_lang."', '".$modLink."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+1)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_availability."', '".URL_Availability."mod.availability.categories.php?lang=".$lang."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+2)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_articles."', '".URL_Availability."mod.availability.articles.php?lang=".$lang."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+3)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_emaillist."', '".URL_Availability."mod.availability.availmail.php?lang=".$lang."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+4)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."2', '".$modId."', '".$modName_lang_emailsettings."', '".URL_Availability."mod.availability.settings.php?lang=".$lang."');\n";
?>
