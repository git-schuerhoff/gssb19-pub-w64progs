<?php
$addcommhtml = '';
if(isset($_SESSION['login']))
{
	if($_SESSION['login']['ok'])
	{
		$this->content = str_replace('{GSSE_INCL_COMMIMG}',$_SESSION['aitem']['itemSmallImageFile'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMNAME}',$_SESSION['aitem']['itemItemDescription'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMNO}',$_SESSION['aitem']['itemItemNumber'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMRET}',$_SESSION['aitem']['itemItemId'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMSHOW}',$_GET['show_comm'], $this->content);
		$this->content = str_replace('{GSSE_INCL_CID}',$_SESSION['login']['cusIdNo'], $this->content);
	}
}
else
{
	if(isset($_GET['cusId']) && isset($_GET['itemNo']))
	{
		$itemId = $this->get_itemId($_GET['itemNo']);
		$item = $this->get_item($itemId);
		$this->content = str_replace('{GSSE_INCL_COMMIMG}',$_SESSION['aitem']['itemSmallImageFile'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMNAME}',$_SESSION['aitem']['itemItemDescription'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMNO}',$_GET['itemNo'], $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMRET}',$itemId, $this->content);
		$this->content = str_replace('{GSSE_INCL_COMMSHOW}','', $this->content);
		$this->content = str_replace('{GSSE_INCL_CID}',$_GET['cusId'], $this->content);
	}
}
$this->content = str_replace('{GSSE_INCL_COMMDATE}',date("Y-m-d") . " " . date("H:i:s"), $this->content);

$this->content = str_replace($tag, $addcommhtml, $this->content);
?>