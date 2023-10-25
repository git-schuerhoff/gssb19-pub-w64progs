<?php
/******************************************************************************/
/* File: logout.php                                                             */
/******************************************************************************/

require("class/class.session.inc.php"); // fallback session class
require_once("include/functions.inc.php");

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

$userLang = getentity(DBToken."settings","setDefaultLanguageIdNo","setIdNo=1");

$Session = new Session();
$Session->destroy();


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
      <title><?php echo L_dynsb_InvalidSessiondata;?></title>
      <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
      <meta content="de" http-equiv="Language">
      <meta name="author" content="GS Software Solutions GmbH">
      <link rel="stylesheet" type="text/css" href="css/link.css">
      <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">


<script language="JavaScript" type="text/javascript">
<!--

if (window.name != '')
	{
		top.location.href="session_invalid.php?lang=<?echo $lang;?>";

	}

//-->
</script>
</head>
<body bgcolor="#FFFFFF">
  <div align=center>
   <table width=550 >
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tbody>
    <tr>
      <td>
        <div align=center>
          <p><img src="image/v6_64.gif" width="64" height="64" border="0" alt="GS Sofware Dynamic ShopBuilder Extensions"><h2>dynamic ShopBuilder Extensions</h2></p>
          <p><br />
          </p>
          <table width="100%" cellspacing="0" cellpadding="0" class="login">
            <tr>
              <td class="lightorange_bgr">
                <div align="center" class="textbold2"><br />
                  <?php echo L_dynsb_InvalidSessiondata;?><br />
                </div>
                <form name="form1">
                  <table align="center">
                    <tr>
                      <td>
                        <?php echo L_dynsb_InfoTextInvalidSessiondata;?>
                      </td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td><?php echo L_dynsb_InfoTextInvalidSessiondata;?></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table>
                  <br />
                  <center>
                    <a class="button100" href="javascript:document.location.href='index.php';" onMouseover="return hidestatus();"><?php echo L_dynsb_Login;?></a>
                    <br />
                  </center>
                </form>
                <div align="center">&nbsp;</div>
              </td>
            </tr>
          </table>
          <p>&nbsp;</p>
          <p></p>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
 </div>
<noscript>
<h1>Bitte aktivieren Sie JavaScript in Ihrem Browser! Ohne JavaScript k&ouml;nnen Sie dieses System nicht nutzen!</h1>
</noscript>
<noframes>
<h1>Ihr Browser kann keine Frames anzeigen. Dieses System funktioniert aber nur mit Frames!</h1>
</noframes>


</body>
</html>
