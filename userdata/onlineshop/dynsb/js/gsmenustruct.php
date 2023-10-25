/*

    GS-Tree Navigation System - menustruct.js
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/
<?php
//$currentDir = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/dynsb/"));
//require_once($_SERVER["DOCUMENT_ROOT"].$currentDir."/dynsb.path.inc.php");

//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));

require_once("../include/functions.inc.php");
require_once("../class/class.modulemanager.php");
require_once("../class/class.refreshmoduleinfo.php");

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

?>
gsm = new Array();

gsm[0] = new Array(1, 0, 'n1001', 'root', '<?php echo L_dynsb_Customerinfo;?>', '../customer/blank.php?lang=<?echo $lang;?>');
gsm[1] = new Array(2, 1, 'n1003', 'n1001', '<?php echo L_dynsb_Add;?>', '../customer/customer.detail.php?act=a&amp;lang=<?echo $lang;?>');
gsm[2] = new Array(2, 1, 'n1003', 'n1001', '<?php echo L_dynsb_Edit;?>', '../customer/customer.search.php?lang=<?echo $lang;?>');
gsm[3] = new Array(2, 1, 'n1003', 'n1001', '<?php echo L_dynsb_Import;?>', '../customer/customer.import.php?lang=<?echo $lang;?>');
gsm[4] = new Array(2, 1, 'n1003', 'n1001', '<?php echo L_dynsb_Export;?>', '../customer/customer.export.php?lang=<?echo $lang;?>');

gsm[5] = new Array(1, 0, 'n1005', 'root', '<?php echo L_dynsb_Shoporder;?>', '../shoporder/blank.php?lang=<?echo $lang;?>');
gsm[6] = new Array(2, 1, 'n1006', 'n1005', '<?php echo L_dynsb_Show;?>', '../shoporder/shoporder.search.php?lang=<?echo $lang;?>');

gsm[7] = new Array(1, 0, 'n1012', 'root', '<?php echo L_dynsb_Settings;?>', '../blank.php?lang=<?echo $lang;?>');
gsm[8] = new Array(2, 1, 'n1013', 'n1012', '<?php echo L_dynsb_Edit;?>', '../settings/settings.detail.php?act=e&amp;lang=<?echo $lang;?>');

gsm[9] = new Array(1, 0, 'n1016', 'root', '<?php echo L_dynsb_Module;?>', '../blank.php?lang=<?echo $lang;?>');
<?php
$mod = new moduleManager($_SERVER['HTTP_HOST'], "", "../module/", 10, 2, 'n1016', $lang);
$rmi = new refreshModuleinfo("../module/");

?>
gsm[68] = new Array(1, 0, 'n1014', 'root', '<?php echo L_dynsb_DHLshippingCenter;?>', '../blank.php?lang=<?echo $lang;?>');

gsm[69] = new Array(2, 1, 'n1015', 'n1014', '<?php echo L_dynsb_Set;?>', '../dhl_carrier/mod.carrier.detail.php?lang=<?echo $lang;?>');

gsm[70] = new Array(2, 1, 'n1015', 'n1014', '<?php echo L_dynsb_DHLonline;?>', '../dhl_carrier/mod.carrier.print.php?lang=<?echo $lang;?>');

gsm[71] = new Array(2, 1, 'n1015', 'n1014', '<?php echo L_dynsb_DHLtracking;?>', '../dhl_carrier/mod.carrier.tracking.php?lang=<?echo $lang;?>');
gsm[72] = new Array(1, 0, 'n1073', 'root', '<?php echo L_dynsb_ImportExportDB;?>', '../blank.php?lang=<?echo $lang;?>');
gsm[73] = new Array(2, 1, 'n1074', 'n1073', '<?php echo L_dynsb_SaveDatabase;?>', '../exportdatabase/exportDB.inc.php');
gsm[74] = new Array(2, 1, 'n1075', 'n1073', '<?php echo L_dynsb_InitDatabase;?>', '../initdatabase/initDatabase.php?lang=<?echo $lang;?>');
gsm[75] = new Array(2, 1, 'n1076', 'n1073', '<?php echo L_dynsb_ImportDatabase;?>', '../importdatabase/importDB.inc.php');

parent.contentFrame.location.href = '../help/about.php?lang=<?echo $lang;?>';
