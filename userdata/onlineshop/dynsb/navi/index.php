<?php

//$currentDir = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/dynsb/"));
//require_once($_SERVER["DOCUMENT_ROOT"].$currentDir."/dynsb.path.inc.php");

//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));

// checks authorization of the user
require("../include/login.check.inc.php");

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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>Tree Menu</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
	<link rel="stylesheet" type="text/css" href="../css/link.css">
	<link rel="stylesheet" type="text/css" href='../css/tree.css'>
	<script type="text/javascript" src="../js/gshide.php"></script>
	<script type="text/javascript" src="../js/gstree.php"></script>
	<script type="text/javascript" src="../js/gsmenustruct.php?lang=<?php echo $lang;?>"></script>
</head>

<body>

<!-- Here starts the the GSTreeMenu -->

<script language="JavaScript" type="text/javascript">
	showall(gsm);
</SCRIPT>

<!-- End of GSTreeMenu -->

</body>
</html>
