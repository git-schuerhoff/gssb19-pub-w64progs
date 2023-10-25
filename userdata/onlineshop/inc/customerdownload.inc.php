<?php
$downloadhtml = $this->gs_file_get_contents('template/downloads.html');
$aDLTags = $this->get_tags_ret($downloadhtml);
$downloadhtml = $this->parse_texts($aDLTags,$downloadhtml);
$downloaditemhtml = $this->gs_file_get_contents('template/downloaditem.html');
$nodownloaditemhtml = $this->gs_file_get_contents('template/no-downloaditem.html');
$aDLITags = $this->get_tags_ret($downloaditemhtml);
$downloaditemhtml = $this->parse_texts($aDLITags,$downloaditemhtml);
$items = '';
if(!$_SESSION['login']['ok'])
{
	header("Location: index.php?page=createcustomer");
}
$cid = $_SESSION['login']['cusIdNo'];
if(file_exists("dynsb/class/class.shoplog.php"))
{
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	require_once("dynsb/include/functions.inc.php");
}
else
{
	die($this->get_lngtext('LangTagErrorMissingRootPathFile'));
}

$co = new shoplog();
$gefunden = 0;
$oldItem="";
/*$downloadVerzeichnis = "customer_" . $cid;*/
$dirname = "customerdownloads/".$downloadVerzeichnis."/";

$dldbh = $this->db_connect();
$qrySQL = "SELECT dlcuItemNumber,dlcuFilename,dlcuAllowedDownloads,dlcuCreateTime,itemItemDescription FROM ".$this->dbtoken."downloadarticle_customer c, ".$this->dbtoken."itemdata d".
" WHERE c.dlcuItemNumber=d.itemItemNumber AND c.dlcuSLC=d.itemLanguageId AND c.dlcuCusId=$cid ORDER BY dlcuCreateTime DESC, dlcuItemNumber ASC";
$qry = mysqli_query($dldbh,$qrySQL);
$gefunden = mysqli_num_rows($qry);
$orderdate = '';
$corderdate = '';
while($obj = @mysqli_fetch_object($qry))
{
	if($obj->dlcuAllowedDownloads > 0) {
		$cur_item = $downloaditemhtml;
	} else {
		$cur_item = $nodownloaditemhtml;
	}
	$orderdate = $obj->dlcuCreateTime;
	$cur_item = str_replace('{GSSE_INCL_ITEMNUMBER}',$obj->dlcuItemNumber,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_ITEMDESC}',$obj->itemItemDescription,$cur_item);
	/*A TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
	/*$AnzDownloads = $co->getCountDownloadsAvailible($dirname,$orderdate . ';' . $obj->dlcuFilename);*/
	$cur_item = str_replace('{GSSE_INCL_DOWNLOADCOUNT}',$obj->dlcuAllowedDownloads,$cur_item);
	$cur_item = str_replace('{GSSE_INCL_FILENAME}',$obj->dlcuFilename,$cur_item);
	$params = $cid . "," . $obj->dlcuAllowedDownloads . "," . "'" . $obj->dlcuFilename . "'" .
						  ",'" . $this->get_lngtext('LangTagTextDownloadAreb') . "','" . $orderdate . "','" . $obj->dlcuItemNumber . "'";
	/*E TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
	$cur_item = str_replace('{GSSE_INCL_DOWNLOADPARAMS}',$params,$cur_item);
	/*A TS 22.10.2015: Bestelldatum anzeigen*/
	//20151022101813
	if($orderdate != '') {
		$year = substr($orderdate,0,4);
		$mon = substr($orderdate,4,2);
		$day = substr($orderdate,6,2);
		$hour = substr($orderdate,8,2);
		$min = substr($orderdate,10,2);
		$sec = substr($orderdate,12,2);
		if($this->lngID == 'deu') {
			$corderdate = $day . "." . $mon . "." . $year . " " . $hour . ":" . $min . ":" . $sec;
		} else {
			$corderdate = $year . "-" . $mon . "-"  . $day . " " . $hour . ":" . $min . ":" . $sec;
		}
	}
	$cur_item = str_replace('{GSSE_INCL_ORDERDATE}',$corderdate,$cur_item);
	/*E TS 22.10.2015: Bestelldatum anzeigen*/
	$items .= $cur_item;
}
$noorder = '';
if($gefunden == 0)
{
	$noorder = $this->get_lngtext('LangTagNoOrder');
}
$downloadhtml = str_replace('{GSSE_INCL_NOORDER}', $noorder, $downloadhtml);
$downloadhtml = str_replace('{GSSE_INCL_DOWNLOADITEMS}', $items, $downloadhtml);
mysqli_free_result($qry);

$this->content = str_replace($tag, $downloadhtml, $this->content);
