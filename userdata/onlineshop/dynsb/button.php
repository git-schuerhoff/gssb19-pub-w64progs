<?php
/******************************************************************************/
/* File: button.php                                                           */
/******************************************************************************/

require("include/login.check.inc.php");
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>button</title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software AG">
    <link rel="stylesheet" type="text/css" href="css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script language="javascript" src="js/gshide.php"></script>
    <script language="JavaScript" type="text/javascript">
    function showHelp()
    {
      var x = 0;
	    var y = 0;
	    var winBreite = 800;
	    var winHoehe = 600;
	    x = Math.round((screen.width-winBreite)/2);
	    y = Math.round((screen.height-winHoehe)/2);
	    var url = '<?php echo L_dynsb_HelpURL;?>';
      var helpwin=window.open(url,'help');
	    helpwin.focus();
    }
    //--------------------------------------------------------------------------
    function logout()
    {
      var close = confirm('<?php echo L_dynsb_QuestionEndProgramm;?>');
      if(close) parent.document.location.href='login.php?strMethod=logout&lang=<?php echo $lang;?>';
    }
    //--------------------------------------------------------------------------
    function loadAbout()
    {
      parent.frames['contentFrame'].location = 'help/about.php?lang=<?php echo $lang;?>';
    }
    </script>
</head>
<body style="margin:0px; padding:0px;">
<form name="frmButton">

<div id="PGbutton">

<table>
<tr>
  <td align="center">
    <input type="button" class="button" onclick="javascript:parent.frames['naviFrame'].expand();" onMouseover="return hidestatus()" value="<?php echo L_dynsb_OpenAll;?>" />
		<input type="button" class="button" onclick="javascript:parent.frames['naviFrame'].collapse();" onMouseover="return hidestatus();" value="<?php echo L_dynsb_CloseAll;?>" />
  </td>
</tr>

<tr>
  <td align="center">
    <input type="button" class="button" onclick="javascript:showHelp();" value="<?php echo L_dynsb_Help;?>" />
    <input type="button" class="button" onclick="javascript:loadAbout();" name="about" value="<?php echo L_dynsb_Info;?>" />
  </td>
</tr>

  <tr>
    <td align="center">
      <input type="button" class="button" onclick="javascript:logout()" value="<?php echo L_dynsb_Logout;?>" />
    </td>
  </tr>
</table>
</form>
</div>

</body>
</html>
