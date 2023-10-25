<?php
/******************************************************************************/
/* File: index.php                                                            */
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

$se = new setupEngine("setup/","/");
if($se->isMySQLConnectable() == 0) die("ERROR: no connection to the mysql server");
//if($se->isDatabaseInstalled() == 0) header("Location: setup/setup.php");
//A TS 02.12.2013 Installation �lasse
if($se->isDatabaseInstalled() == 0) $se->installDB();

//Database modification
//SS 20100902 not needed now
//include("setup/modi_database.php");


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title>GS Software - Dynamic GS ShopBuilder Extensions</title>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
  <meta content="de" http-equiv="Language">
  <meta name="author" content="GS Software Solutions GmbH" >
  <link rel="stylesheet" type="text/css" href="css/link.css">
  <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
  <script language="JavaScript" type="text/javascript">
  var defaultStatus = 'GS Software dynamic GS ShopBuilder Extensions';
  window.status = window.defaultStatus;
  //----------------------------------------------------------------------------  
  function hidestatus()
  {
    window.status = defaultStatus;
    return true;
  }
  //----------------------------------------------------------------------------  
  function send() 
  {
    document.frmLogin.submit();
  }
  //----------------------------------------------------------------------------  
  function changeLang()
  {
    location = 'index.php?lang='+document.frmLogin.lang.value;
  }
  </script>
</head>
<body bgcolor="#FFFFF" onLoad="document.frmLogin.struName.focus()">
<div align=center>
  <table width=500 >
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tbody>
    <tr>
      <td>
        <div align=center>
          <p><img src="image/logo_small.png" width="64" height="64" border="0" alt="GS Sofware Dynamic GS ShopBuilder Extensions"><h2>dynamic GS ShopBuilder Extensions</h2></p>
          <p><br />
          </p>
          <table width="100%" cellspacing="0" cellpadding="0" class="login">
            <tr>
              <td class="lightorange_bgr">
                <div align="center"><br />
                  <b><?php echo L_dynsb_EnterLoginData;?></b><br />
              <br />
                </div>
                <form name="frmLogin" method="post" action="login.php">
                  <input type="hidden" name="strMethod" value="check_login">
                  <table align="center" border="0">
                    <tr>
                      <td align="left"><?php echo L_dynsb_EnterUsername;?>:</td>
                      <td width="5">&nbsp;</td>
                      <td align="right">
                        <input type="text" tabindex=1 name="struName" onKeyDown="if(event.keyCode==13) send();" maxlength="20" size="20" class="inputbox150_eingabe" value="">
                      </td>
                    </tr>
                    <tr>
                      <td align="left"><?php echo L_dynsb_EnterPasswort;?>:</td>
                      <td>&nbsp;</td>
                      <td align="right">
                        <input type="password" tabindex=2 name="strPass" onKeyDown="if(event.keyCode==13) send();" maxlength="20" size="20" class="inputbox150_eingabe" value="">
                      </td>
                    </tr>
                    <tr>
                      <td align="left"><?php echo L_dynsb_FrontendLanguage;?>:</td>
                      <td>&nbsp;</td>
                      <td align="right">
                        <select name="lang" onChange="changeLang();" tabindex=3 class="inputbox150_eingabe" onKeyDown="if(event.keyCode==13) send();">                        
                            <?php
                            /*
                            if(L_dynsb_ShopLang!="{L_dynsb_ShopLang}"
                               && L_dynsb_ShopLang!="{L_dynsb_ShopLang_deu}"
                               && L_dynsb_ShopLang!="{L_dynsb_ShopLang_eng}"
                              )
                            {*/
                            ?>
                            <!--<option value="gs_" <?php if($_REQUEST['lang']=="gs_") echo "selected";?>><?php echo L_dynsb_ShopLang;?></option>-->
                            <?//}?>
                            <option value="deu" <?php if($userLang=="deu") echo "selected";?>><?php echo L_dynsb_German;?></option>
                            <option value="eng" <?php if($userLang=="eng") echo "selected";?>><?php echo L_dynsb_English;?></option>
                        </select>
                      </td>
                    </tr>
                  </table>
                  <br />
                  <center>
                    <input type="button" class="button" onClick="javascript:send();" value="<?php echo L_dynsb_Login;?>"><br /><br />
                  </center>
                </form>
              </td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
  </div>
	<noframes>
	<p>Diese Seite verwendet Frames. Bei Ihnen werden keine Frames angezeigt.<br />
	   Bitte benutzen Sie einen aktuellen Browser.</p>
	</noframes>
	<noscript>
	<h3>Bitte aktivieren Sie JavaScript in Ihrem Browser! <br />Ohne JavaScript k&ouml;nnen Sie dieses System nicht nutzen!</h1>
	</noscript>

  </body>
 </html>
