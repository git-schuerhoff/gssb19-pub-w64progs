<?php
	session_start();
	chdir ('../');
	include('inc/class.shopengine.php');
	$nav_se = new gs_shopengine();
	if($_GET['parent'] == 0)
	{
		//Bei Hauptmenuepunkt array leeren
		$_SESSION['anavi'] = array();
	}
	
	$pIndex = $nav_se->array_search_multi($_GET['parent'], $_SESSION['anavi']);
	$max = count($_SESSION['anavi']) - 1;
	if($pIndex !== false)
	{
		//Wenn die Parent-Gruppe der aktuellen Gruppe bereits geöffnet ist,
		//alle Unter-Gruppen schließen
		$aHelper = array();
		for($n = 0; $n <= $pIndex; $n++)
		{
			/*array_push($aHelper,$_SESSION['anavi'][$n]);*/
			$aHelper[] = $_SESSION['anavi'][$n];
		}
		$_SESSION['anavi'] = $aHelper;
	}
	
	if($_GET['group'] != 0)
	{
		if($nav_se->array_search_multi($_GET['group'], $_SESSION['anavi']) === false)
		{
			/*array_push($_SESSION['anavi'],array('group' => $_GET['group'],'level' => $_GET['level'],'parent' => $_GET['parent'],'active' => '_active','title' => $_GET['title'],'childs' => $_GET['childs']));*/
			$_SESSION['anavi'][] = array('group' => $_GET['group'],'level' => $_GET['level'],'parent' => $_GET['parent'],'active' => '_active','title' => $_GET['title'],'childs' => $_GET['childs']);
		}
	}
	
	//_active-Style auf untersten Zweig setzen
	$last = count($_SESSION['anavi']) - 1;
	if($last == 0)
	{
		$_SESSION['anavi'][0]['active'] = '_active';
	}
	else
	{
		for($n = 0; $n <= $last; $n++)
		{
			if($n < $last)
			{
				$act = '';
			}
			else
			{
				$act = '_active';
			}
			$_SESSION['anavi'][$n]['active'] = $act;
		}
	}
	
	$nav_se->get_subnavi($_GET['group'],$_GET['level']);
?>