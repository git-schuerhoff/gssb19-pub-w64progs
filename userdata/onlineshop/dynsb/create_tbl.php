<?php
/******************************************************************************/
/* File: create_tbl.php																														*/
/******************************************************************************/

require("../conf/db.const.inc.php");
require("class/class.setup.php");
require("include/checkversion.inc.php");
require_once("include/functions.inc.php");

$userLang = getentity(DBToken."settings","setDefaultLanguageIdNo","setIdNo=1");
if(isset($_REQUEST['lang']))
{
	$userLang = $_REQUEST['lang'];
}
/***************** Sprachdatei ************************************************/
if (!isset($userLang) || strlen(trim($userLang)) == 0) 
{
		$lang = "deu";
} 
else 
{
	$lang = $userLang;
	if(!file_exists("lang/lang_".$lang.".php"))
	{
		$lang = "deu";
	}
}

include("lang/lang_".$lang.".php");
/******************************************************************************/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>GS Software - Dynamic GS ShopBuilder Extensions</title>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
		<meta content="de" http-equiv="Language">
		<meta name="author" content="GS Software AG" >
		<link rel="stylesheet" type="text/css" href="css/link.css">
		<link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
	</head>
	<body bgcolor="#FFFFF">
		<div align=center>
			<table width=500 >
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tbody>
					<tr>
						<td>
							<div align=center>
								<p><img src="image/logo_small.png" width="64" height="64" border="0" alt="GS Sofware Dynamic GS ShopBuilder Extensions"><h2>dynamic GS ShopBuilder Extensions</h2></p><br />
								<?php
									$se = new setupEngine("setup/","/");
									if($se->isMySQLConnectable() == 0) die("ERROR: no connection to the mysql server");
									echo L_dynsb_CreateDataBase . "<br />";
									$se->installDB();
								?>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
 </html>
