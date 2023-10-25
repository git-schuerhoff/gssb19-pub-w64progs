<?php


$currentDir = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/dynsb/"));
require_once($_SERVER["DOCUMENT_ROOT"].$currentDir."/dynsb.path.inc.php");

// checks authorization of the user
require(INC_PATH."login.check.inc.php");



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
      <title>session vars</title>
      <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
      <meta content="de" http-equiv="Language">
      <meta name="author" content="GS Software Solutions GmbH">
      <link rel="stylesheet" type="text/css" href="<?php echo URL_CSS ?>link.css">
      <link rel="copyright" href="http://www.gs-software.de" title="(c) 2016 GS Software AG">

</head>
<body>
<div class="text">
<?php
// display all Session variables

	if (!empty($_SESSION)) {
        reset($_SESSION);
        foreach($_SESSION as $key=>$value) {
            echo $key." = ".$value."<br />";
            if (is_object($_SESSION[$key])) { // if object show all object vars
			$obj_name =  $_SESSION[$key];
			$array = get_object_vars($obj_name);
			echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array)." Fields * <br />";
			while (list($key1, $val1) = each($array)) {
                echo "&nbsp;&nbsp;&nbsp; $key1 => $val1";
                echo "<br />";
			}
	  	}
		if (is_array($_SESSION[$key])) { // if array show all array vars
			$array = $_SESSION[$key];
			echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array)." Fields * <br />";
			while (list($key1, $val1) = each($array)) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp; $key1 => $val1";
                echo "<br />";
				if (is_object($_SESSION[$key][$key1])) { // if object show all object vars
					$obj_name1 =  $_SESSION[$key][$key1];
					$array2 = get_object_vars($obj_name1);
					echo "&nbsp;&nbsp;&nbsp; * ".sizeof($array2)." Fields * <br />";
					while (list($key3, $val3) = each($array2)) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $key3 => $val3";
                        echo "<br />";
					}
				}
			}
		}
    }
}

?>
</div>
</body>
</html>
