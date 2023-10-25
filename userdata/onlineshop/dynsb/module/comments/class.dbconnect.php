<?php
if(@file_exists("dynsb/class/class.db.php")) {
  require_once("dynsb/class/class.db.php");
}
elseif(@file_exists("class/class.db.php")) {
    require_once("class/class.db.php");
}
elseif(@file_exists("../../class/class.db.php")) {
    require_once("../../class/class.db.php");
}


class DbConnect {
	function __construct(){
		
	}
	
	function connect()  {
		$dbVars = new dbVars();

		$con = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$con->query("SET NAMES 'utf8'");
		if($con) {
			return $con;
		}
		return false;
  }
}
?>