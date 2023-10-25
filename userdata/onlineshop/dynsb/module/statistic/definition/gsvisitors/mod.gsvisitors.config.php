<?php
/**
*
*    page statistic module - mod.gspageviews.config.php
*
*    @author: Raimund Kulikowski / GS Software Solutions GmbH
*    @author: Jan Reker
*
*    (c) 2004-2006 GS Software Solutions GmbH
*
*
*    this code is NOT open-source or freeware
*    you are not allowed to use, copy or redistribute it in any form
*
*/



// declare static paths for this module
define("URL_GSvisitors",URL_STATISTIC_DEF."gsvisitors/");

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
$modName_lang =  L_dynsb_statVisitors;
// caption info for the about-screen
$modCaption_lang =L_dynsb_GSmodulVisitors;

// is this module bound to a domain? (0 = No / 1 = Yes)
$modIsBoundFlg = 0;

// domain to which this module is bound if $modIsBoundFlg = 1
$modDomain = "localhost";

// is setup script necessary (0 = No / 1 = Yes)
$modSetup = 0;

// moduleid - has to be unique !!!
$modId = "svisits";

// version information
$modVersion = "1.0";

// is this a leaf in the tree menu? (0 = No / 1 = Yes)
$modIsLeaf = 1;

// parent -> 'n1014' for the Modules node
$modParentNode = $this->mvParentNode;

// link
$modLink = URL_GSvisitors."mod.gsvisitors.run.php?lang=".$lang;

// has subModule (0 = No / 1 = Yes)
$modHasSubModule = 0;

// if subModule = 0 then ""
$modSubModuleFolder = "";

// number of menu-entrys for this module
$modEntryCount = 1;

// for the menustruct
$modTreeStr = "gsm[".$this->mvStartIndex."] = new Array(".$this->mvTreeLevel.", ".$modIsLeaf.", '".$modId."', '".$modParentNode."', '".$modName_lang."', '".$modLink."');\n";
?>
