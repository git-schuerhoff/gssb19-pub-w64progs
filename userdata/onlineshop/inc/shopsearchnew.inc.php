<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$search_items = '';

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
if($_POST['search'] !== '')
{
	/*Add to Statistik*/
	$sl->actid = 3;
	$sl->strlog = $_POST['search'];
	$sl->logShoppage();
	$sl->actid = '';
	$sl->strlog = '';
}
$search_items = '<script type="text/javascript">' . $this->crlf .
			  'search_itemsnew("' . $_POST['search'] . '");' . $this->crlf .
			  //'show_pgroupnew(0);' . $this->crlf .
			  '</script>';

$this->content = str_replace($tag, $search_items, $this->content);
?>