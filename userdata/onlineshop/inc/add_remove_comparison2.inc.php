<?php
header("Content-type: application/json; charset=utf-8");
session_start();
if($_GET['itemid'] != -9999 && $_GET['name'] != '*#*#*')
{
	$item_name = htmlspecialchars($_GET['name'],ENT_QUOTES);
	if(!isset($_SESSION['aitems_compare']) || count($_SESSION['aitems_compare']) == 0)
	{
		$_SESSION['aitems_compare'] = array();
		/*array_push($_SESSION['aitems_compare'], array("idx" => $_GET['itemid'], "name" => $item_name));*/
		$_SESSION['aitems_compare'][] = array("idx" => $_GET['itemid'], "name" => $item_name, "page" => $_GET['page']);
	}
	else
	{
		if(array_search_multi($_GET['itemid'], $_SESSION['aitems_compare']) === false)
		{
			/*array_push($_SESSION['aitems_compare'],array("idx" => $_GET['itemid'], "name" => $item_name));*/
			$_SESSION['aitems_compare'][] = array("idx" => $_GET['itemid'], "name" => $item_name, "page" => $_GET['page']);
		}
		else
		{
			$iIDX = array_search_multi($_GET['itemid'], $_SESSION['aitems_compare']);
			if($iIDX !== false)
			{
				array_splice($_SESSION['aitems_compare'], $iIDX, 1);
			}
		}
	}
}
else
{
	$_SESSION['aitems_compare'] = array();
}

echo json_encode($_SESSION['aitems_compare']);

function array_search_multi($search, $array)
{
	foreach($array as $key => $values)
	{
		if(in_array($search, $values))
		{
			return $key;
		}
	}
	return false;
}

?>