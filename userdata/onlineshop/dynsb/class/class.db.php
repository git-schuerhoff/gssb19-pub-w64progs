<?php

/*
 file: class.db.php
*/

if(file_exists("dynsb/../conf/db.const.inc.php"))
{
	include("dynsb/../conf/db.const.inc.php");
}
else
{
	if(file_exists("../conf/db.const.inc.php"))
	{
		include("../conf/db.const.inc.php");
	}
	else
	{
		if(file_exists("../../conf/db.const.inc.php"))
		{
			include("../../conf/db.const.inc.php");
		}
		else
		{
			if(file_exists("../../../conf/db.const.inc.php"))
			{
				include("../../../conf/db.const.inc.php");
			}
			else
			{ 
				include("../../../../../conf/db.const.inc.php");
			}
		}
	}
}


class dbVars {
	var $strUser;
	var $strPass;
	var $strServer;
	var $strDb;

	// Constructor
	function __construct() {
		global $dbUser, $dbPass, $dbServer, $dbDatabase;
		if(file_exists("dynsb/../conf/db.const.inc.php"))
		{
			include("dynsb/../conf/db.const.inc.php");
		}
		else
		{
			if(file_exists("../conf/db.const.inc.php"))
			{
				include("../conf/db.const.inc.php");
			}
			else
			{
				if(file_exists("../../conf/db.const.inc.php"))
				{
					include("../../conf/db.const.inc.php");
				}
				else
				{
					if(file_exists("../../../conf/db.const.inc.php"))
					{
						include("../../../conf/db.const.inc.php");
					}
					else
					{ 
						include("../../../../../conf/db.const.inc.php");
					}
				}
			}
		}
		$this->strUser = $dbUser;
		$this->strPass = $dbPass;
		$this->strServer = $dbServer;
		$this->strDb = $dbDatabase;
	}
}
?>
