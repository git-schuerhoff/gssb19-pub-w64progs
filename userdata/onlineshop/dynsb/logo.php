<?php
/******************************************************************************/
/* File: logo.php                                                             */
/******************************************************************************/

require("include/login.check.inc.php");

/***************** Sprachdatei ************************************************/
if (!isset($_REQUEST['lang']) || strlen(trim($_REQUEST['lang'])) == 0)
{
    $lang = "deu";
}
else
{
	$lang = $_REQUEST['lang'];
	if(!file_exists("lang/lang_".$lang.".php"))
  {
    $lang = "deu";
  }
}

include("lang/lang_".$lang.".php");
/******************************************************************************/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
      <title>logo</title>
      <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
      <meta content="de" http-equiv="Language" />
      <meta name="author" content="GS Software Solutions GmbH" />
      <link rel="stylesheet" type="text/css" href="css/link.css" />
      <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG" />
</head>

<body style="margin:0px; padding: 0px;">
<div id="PGlogo">
	<img src="image/logo_small.png">
</div>
</body>
</html>
