<?php
/*

    file: path.inc.php
    all necessary application paths
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH

    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/

// file includes
define("INC_PATH",$_SERVER["DOCUMENT_ROOT"].URL_ROOT."include/");
define("CLASS_PATH",$_SERVER["DOCUMENT_ROOT"].URL_ROOT."class/");
define("INC_DB_PATH",INC_PATH."db/");

// login page
define("URL_LOGIN", $_SERVER['HTTP_HOST'].URL_ROOT."index.php");

// invalid session page
define("URL_SESS_INVALID", $_SERVER['HTTP_HOST'].URL_ROOT."session_invalid.php");

// setup module
define("URL_SETUP",URL_ROOT."setup/");
define("URL_SETUP_SQL",URL_SETUP."sql/");
define("URL_SETUP_TAB",URL_SETUP_SQL."tab/");
define("URL_SETUP_DAT",URL_SETUP_SQL."dat/");
define("INC_SETUP", $_SERVER["DOCUMENT_ROOT"].URL_SETUP);

// default application folders
define("URL_SETTINGS",URL_ROOT."settings/");
define("URL_JS",URL_ROOT."js/");
define("URL_CSS",URL_ROOT."css/");
define("URL_SQL",URL_ROOT."sql/");
define("URL_IMAGE",URL_ROOT."image/");
define("URL_HELP",URL_ROOT."help/");

// default modules
define("URL_CUSTOMER",URL_ROOT."customer/");
define("URL_SHOPORDER",URL_ROOT."shoporder/");
define("URL_MONITOR",URL_ROOT."monitor/");

// dynamic modules
define("URL_MODULE",URL_ROOT."module/");

?>
