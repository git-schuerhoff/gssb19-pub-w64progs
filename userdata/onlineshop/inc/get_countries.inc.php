<?php
session_start();
chdir("../");
include_once("inc/class.shopengine.php");
$se_countrys = new gs_shopengine();

	$aErg = $se_countrys->get_countries($_GET['area']);
	$ergmax = count($aErg);
	if($ergmax > 0)
	{
		$ausg = '';
		for($a = 0; $a < $ergmax; $a++)
		{
			$ausg .= '0|' . $aErg[$a]['oval'] . '|' . $aErg[$a]['otext'] . '~';
		}
		echo $ausg;
	}
	else
	{
		echo '-1| | ~';
	}

?>