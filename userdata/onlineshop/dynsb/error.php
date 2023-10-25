<?php 
/******************************************************************************/
/* File: error.php                                                            */
/******************************************************************************/

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
  <title><?echo L_dynsb_Failure;?></title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH">
  <link rel="stylesheet" type="text/css" href="css/link.css">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script language="JavaScript" type="text/javascript">
  var defaultStatus = 'GS Software dynamic ShopBuilder Extensions';
  window.status = window.defaultStatus;
  //----------------------------------------------------------------------------
  function hidestatus()
  {
    window.status = defaultStatus;
    return true;
  }
  </script>
</head>
<BODY BGCOLOR="#FFFFFF" TEXT="#000000" LINK="#0000ff" VLINK="#800080" ALINK="#ff0000">
  <div align=center>
   <table width="550">
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tbody>
    <tr>
      <td>
        <div align="center">
          <p><img src="image/logo_small.png" width="64" height="64" border="0" alt="GS Sofware Dynamic ShopBuilder Extensions"><h2>dynamic ShopBuilder Extensions</h2></p>
          <p><br />
          </p>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="frame">
            <tr>
              <td class="lightorange_bgr">
                <div align="center" class="textbold2"><br />
                  <?php echo L_dynsb_ErrorLogin;?><br />
                </div>
                <form name="form1">
                  <table align="center">
                    <tr>
                      <td align="center"><?php echo L_dynsb_LogindataIncorrect;?></td>
                    </tr>
                    <tr>
                      <td align="center"><?php echo L_dynsb_PleaseTryAgain;?></td>
                    </tr>
                  </table>
                  <br />
                  <center>
                    <a class="button100" href="javascript:history.go(-1);" onMouseover="return hidestatus();"><?php echo L_dynsb_Back;?></a>
                    <br />
                  </center>
                </form>
                <div align="center">&nbsp;</div>
              </td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
 </div>
</body>
</html>
