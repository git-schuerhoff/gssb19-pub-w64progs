<?php
/*
    (c) 2004-2005 GS Software Solutions GmbH

    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form

*/

// declare static paths for this module
//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
define("URL_Comments","../module/"."comments/");

if (!isset($this->mvLang) || strlen(trim($this->mvLang)) == 0) {
    $lang = "deu";
}
else {
	$lang = $this->mvLang;
	if(!file_exists("../lang/lang_".$lang.".php")) {
    $lang = "deu";
  }
}

include("../lang/lang_".$lang.".php");

// tree menu captions
$modName_lang = L_dynsb_ModuleComments;
$modName_lang_archiv 		= L_dynsb_Comments;
$modName_lang_comments_shop = L_dynsb_Comments_Shop;
$modName_lang_settings 	= L_dynsb_Settings;

// caption info for the about-screen
$modCaption_lang = "GS Software " . L_dynsb_ModuleComments . " 1.0";

// is this module bound to a domain? (0 = No / 1 = Yes)
$modIsBoundFlg = 0;

// domain to which this module is bound if $modIsBoundFlg = 1
$modDomain = "localhost";

// is setup script necessary (0 = No / 1 = Yes)
$modSetup = 0;

// moduleid - has to be unique !!!
$modId = "nl3";

// version information
$modVersion = "1.0";

// is this a leaf in the tree menu? (0 = No / 1 = Yes)
$modIsLeaf = 0;

// parent -> 'n1014' for the Modules node
$modParentNode = "n1014";

// link
$modLink = "mod.comments.run.php";

// has subModule (0 = No / 1 = Yes)
$modHasSubModule = 0;

// if subModule = 0 then ""
$modSubModuleFolder = "";

// number of menu-entrys for this module
$modEntryCount = 4;

// for the menustruct
$modTreeStr = "gsm[".$this->mvStartIndex."] = new Array(".$this->mvTreeLevel.", ".$modIsLeaf.", '".$modId."', '".$modParentNode."', '".$modName_lang."', '".$modLink."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+1)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."4', '".$modId."', '".$modName_lang_archiv."', '".URL_Comments."mod.comments.search.php?lang=".$lang."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+2)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."6', '".$modId."', '".$modName_lang_comments_shop."', '".URL_Comments."mod.shopcomments.search.php?lang=".$lang."');\n";
$modTreeStr .= "gsm[".($this->mvStartIndex+3)."] = new Array(".($this->mvTreeLevel+1).", 1, 'x".$modId."6', '".$modId."', '".$modName_lang_settings."', '".URL_Comments."mod.comments.settings.php?lang=".$lang."');\n";

?>
