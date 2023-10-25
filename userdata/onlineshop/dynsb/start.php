<?php
/******************************************************************************/
/* File: logout.php                                                             */
/******************************************************************************/

require("include/login.check.inc.php");
require_once("include/functions.inc.php");
require("include/set.inc.php");
//include_once("../inc/class.shopengine.php");
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html>
<head>
    <title>GS Software - Dynamic ShopBuilder Extensions</title>
    <meta content="text/html" charset="UTF-8" http-equiv="Content-Type">
    <meta content="de" http-equiv="Language">
    <meta name="author" content="GS Software Solutions GmbH">
    <link rel="stylesheet" type="text/css" href="
	<?php 
		//echo $path;
	?>dynsb/css/link.css">
    <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">
    <script language="JavaScript" type="text/javascript">
    var defaultStatus = 'GS Software dynamic ShopBuilder Extensions';
    window.status = window.defaultStatus;
    //--------------------------------------------------------------------------
    function hidestatus()
    {
        window.status = defaultStatus;
        return true;
    }
    //--------------------------------------------------------------------------
    function closeIt()
    {
    	 window.event.returnValue = "<?php echo L_dynsb_QuestionEndProgramm;?>";
    	 //onBeforeUnload();
    }
    //--------------------------------------------------------------------------
    function loadContentFrameURL(url)
    {
        var aURL = new Array();
        var aTHIS = new Array();
        aURL = url.split('/');
        aTHIS = this.contentFrame.window.location.href.split('/');
        // check if we have to load something
        if((aURL[aURL.length-1] != aTHIS[aTHIS.length-1]) && (aURL[aURL.length-2] != aTHIS[aTHIS.length-2])) this.contentFrame.window.location.href = url;
    }
    </script>
</head>

<frameset cols="210,*" onBeforeUnload="javascript:closeIt()" frameborder="0">
 <frameset rows="80,*,90" frameborder="0" framespacing="0">
      <frame name="topFrame" src="logo.php?lang=<?echo $lang;?>" marginwidth="10" marginheight="10" scrolling="no" frameborder="0" framespacing="0">
      <frame name="naviFrame" src="navi/index.php?lang=<?echo $lang;?>" NORESIZE SCROLLING="auto" frameborder="0" marginwidth="10" marginheight="0" framespacing="0">
      <frame name="buttonFrame" src="button.php?lang=<?echo $lang;?>" marginwidth="10" marginheight="10" scrolling="no" frameborder="0" framespacing="0">
 </frameset>
<frame name="contentFrame" src="help/about.php?lang=<?echo $lang;?>" NORESIZE SCROLLING="auto" frameborder="0" marginwidth="10" marginheight="0" framespacing="0">

  <noframes>
    <p>Diese Seite verwendet Frames. Bei Ihnen werden keine Frames angezeigt. <br />
       Bitte benutzen Sie einen aktuellen Browser.</p>
  </noframes>
 </frameset>

</html>
