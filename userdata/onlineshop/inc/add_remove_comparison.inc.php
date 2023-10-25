<?php
session_start();
if(!isset($_SESSION['aitems_compare']) || count($_SESSION['aitems_compare']) == 0)
{
	if($_GET['add'] == 1)
	{
		$_SESSION['aitems_compare'] = array($_GET['itemid']);
	}
}
else
{
	if($_GET['add'] == 1)
	{
		if(!array_search($_GET['itemid'], $_SESSION['aitems_compare'], true))
		{
			/*array_push($_SESSION['aitems_compare'],$_GET['itemid']);*/
			$_SESSION['aitems_compare'][] = $_GET['itemid'];
		}
	}
	else
	{
		$iIDX = array_search($_GET['itemid'], $_SESSION['aitems_compare'], true);
		if($iIDX !== false)
		{
			array_splice( $_SESSION['aitems_compare'], $iIDX, 1);
		}
	}
}

echo implode(',',$_SESSION['aitems_compare']);

?>