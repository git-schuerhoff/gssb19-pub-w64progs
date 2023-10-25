<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$limit = '';
$pgsubcats = '';
$subdbh = $this->db_connect();
$subsql = "SELECT * FROM " . $this->dbtoken . "productgroups WHERE Parent = '" . $_GET['idx'] . "' ORDER BY Sequence ASC";
$suberg = mysqli_query($subdbh,$subsql);
$iS = 0;
if($level > 5)
{
	$level = 5;
}
$max = mysqli_num_rows($suberg);
if($max > 0)
{
	$lastidx = count($_SESSION['anavi']) - 1;
	$level = $_SESSION['anavi'][$lastidx]['level'];
	$pgsubcats = file_get_contents('template/subcatsouter.html');
	$pgsubcats = str_replace('{GSSE_LANG_LangTagTextItems}',$this->get_lngtext('LangTagTextItems'),$pgsubcats);
	$pgsubcatslines = '';
	$pgsubcatline = file_get_contents('template/subcatsline.html');
	$pgsubcatitem = file_get_contents('template/subcatsitem.html');
	$cur_items = '';
	while($sub = mysqli_fetch_assoc($suberg))
	{
		$chsql = "SELECT COUNT(ObjectCount) AS childs FROM " . $this->dbtoken . "productgroups WHERE Parent = '" . $sub['ObjectCount'] . "'";
		$cherg = mysqli_query($subdbh,$chsql);
		$ch = mysqli_fetch_assoc($cherg);
		$childs = $ch['childs'];
		mysqli_free_result($cherg);
		$cur_item = $pgsubcatitem;
		$showsub = ' onclick="gsse_showsub(' . $sub['ObjectCount'] . ',\'pgid_' . $sub['ObjectCount'] . '\',' . $level . ',' . $_GET['idx'] . ',\'' . $sub['ProductGroup'] . '\',' . $childs . ')"';
		$cur_item = str_replace('{GSSE_INCL_SHOWSUB}',$showsub,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SUBCATITEMID}',$sub['ObjectCount'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_SUBCATITEMTITLE}',$sub['ProductGroup'],$cur_item);
		if($sub['ImageFile'] == '')
		{
			$imgfile = 'template/images/pg_no_pic.jpg';
		}
		else
		{
			$imgfile = 'images/groups/small/' . $sub['ImageFile'];
		}
		$cur_item = str_replace('{GSSE_INCL_SUBCATITEMPIC}',$imgfile,$cur_item);
		$cur_items .= $cur_item;
		$iS++;
		
		if(($iS % 4) == 0 || $iS == $max)
		{
			$cur_line = $pgsubcatline;
			$cur_line = str_replace('{GSSE_INCL_SUBCATSITEMS}',$cur_items,$cur_line);
			$pgsubcatslines .= $cur_line;
			$cur_items = '';
		}
	}
	$pgsubcats = str_replace('{GSSE_INCL_SUBCATLINES}',$pgsubcatslines,$pgsubcats);
}
$this->content = str_replace($tag, $pgsubcats, $this->content);
?>