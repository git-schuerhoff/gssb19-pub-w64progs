<?php
/*error_reporting(E_ALL);
ini_set('display_errors','on');*/
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
	$sl = new shoplog();
}
else
{
	die("Class shoplog missing!");
}
$pgroupname = '';
$count = sizeof($_SESSION['anavi']);
for($i = 0; $i < $count; $i++)
{
	$pgroupname .= $_SESSION['anavi'][$i]['name'].' / ';
}
/*Add to Detailviewmonitor*/
$sl->actid = 1;
$sl->strlog = $pgroupname;
$sl->logShoppage();
$sl->actid = '';
$sl->strlog = '';
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$pgitems = '';
$pgroup = 0;
if(isset($aParam[1])) {
	switch($aParam[1]) {
		case 'feat':
			$pgroup = -1;
			break;
		case 'new':
			$pgroup = -2;
			break;
		default:
			if(isset($_GET['idx'])) {
				$pgroup = $_GET['idx'];
			}
			break;
	}
} else {
	$pgroup = $_GET['idx'];
}

//die("pgroup=".$pgroup);
if($pgroup != 0) {
	//Warengruppenanzeige ermitteln
	//chdir("../");
	//include_once("inc/class.shopengine.php");
	//$pgse = new gs_shopengine();
	$pgdbh = $this->db_connect();
	$pgsql = 'SELECT TemplateFile FROM '.$this->dbtoken.'productgroups WHERE ObjectCount='.$pgroup.' LIMIT 1';
	$pgerg = mysqli_query($pgdbh,$pgsql);
	$view = '';
	if(mysqli_num_rows($pgerg) > 0) {
		$z = mysqli_fetch_assoc($pgerg);
		$view = $z['TemplateFile'];
	}
	mysqli_close($pgdbh);

	/*$pgitems = '<script type="text/javascript">' . $this->crlf .
				  'load_pgitems(' . $pgroup . ',"position","ASC");' . $this->crlf .
				  'show_pgroup(0);' . $this->crlf .
				  '</script>';*/
	$pgitems = '<script type="text/javascript">' . $this->crlf .
				  'load_pgitemsnew(' . $pgroup . ',"'.$view.'");' . $this->crlf .
				  '</script>';
	if(isset($_GET['idx'])) {
		$_SESSION['active_group'] = $_GET['idx'];
	}
}
$this->content = str_replace($tag, $pgitems, $this->content);
?>