<?php
header("Content-type: application/json; charset=utf-8");
chdir("../");
include_once("inc/class.shopengine.php");
$pgse = new gs_shopengine();
$pgdbh = $pgse->db_connect();
$aerg = array();
//$aids = array();
$aPrices = array();

$pgisql = "SELECT G.ItemID, G.OrderIDX, I.itemItemNumber, I.itemItemDescription, P.prcPrice, " .
			 "(SELECT IFNULL(AVG(R.itcoRating),0) FROM " . $pgse->dbtoken . "itemcomments R WHERE R.itcoItemNumber = I.itemItemNumber AND R.itcoVisible ='Y') AS schnitt ".
			 "FROM " . $pgse->dbtoken . "items2group G " .
			 "JOIN " . $pgse->dbtoken . "itemdata I ON G.ItemID=I.itemItemId AND I.itemIsActive='Y' AND I.itemIsVariant='N' AND I.itemLanguageId='" . $pgse->lngID . "' " .
			 "JOIN " . $pgse->dbtoken . "price P ON G.ItemID = P.prcItemCount AND P.prcQuantityFrom = 0 " .
			 "WHERE G.ProductGroup = " . $_GET['idx'] . " " .
			 "ORDER BY G.OrderIDX ASC, G.ItemID ASC";
$pgierg = mysqli_query($pgdbh,$pgisql);
$iO = 0;
if(mysqli_num_rows($pgierg) > 0) {
	while($z = mysqli_fetch_assoc($pgierg)) {
		//$aRat = $pgse->get_av_rating($z['itemItemNumber']);
		//if(!in_array($z['ItemID'],$aids)) {
			//$aids[] = $z['ItemID'];
			$aerg[] = array('ID' => $z['ItemID'],'itemItemDescription' => $z['itemItemDescription'],'ItemPrice' => $z['prcPrice'],'rating_avg' => $z['schnitt'],'OrderID' => $z['OrderIDX']);
		//}
		//$aerg[] = array('ID' => $z['ItemID'],'itemItemDescription' => $z['itemItemDescription'],'ItemPrice' => $z['prcPrice'],'rating_avg' => 0,'OrderID' => $z['OrderIDX']);
		
	}
	echo json_encode($aerg);
} else {
	$limit = '';
	$pgsubcats = '';
	$subdbh = $pgse->db_connect();
	$subsql = "SELECT * FROM " . $pgse->dbtoken . "productgroups WHERE Parent = '" . $_GET['idx'] . "' ORDER BY Sequence ASC";
	$suberg = mysqli_query($subdbh,$subsql);
	$iS = 0;
	/*if($level > 5) {
		$level = 5;
	}*/
	$max = mysqli_num_rows($suberg);
	if($max > 0) {
		//$lastidx = count($_SESSION['anavi']) - 1;
		//$level = $_SESSION['anavi'][$lastidx]['level'];
		$pgsubcats = $pgse->gs_file_get_contents('template/subcatsouter.html');
		$pgsubcats = str_replace('{GSSE_LANG_LangTagTextItems}',$pgse->get_lngtext('LangTagTextItems'),$pgsubcats);
		$pgsubcatslines = '';
		$pgsubcatline = $pgse->gs_file_get_contents('template/subcatsline.html');
		$pgsubcatitem = $pgse->gs_file_get_contents('template/subcatsitem.html');
		$cur_items = '';
		while($sub = mysqli_fetch_assoc($suberg)) {
			$chsql = "SELECT COUNT(ObjectCount) AS childs FROM " . $pgse->dbtoken . "productgroups WHERE Parent = '" . $sub['ObjectCount'] . "'";
			$cherg = mysqli_query($subdbh,$chsql);
			$ch = mysqli_fetch_assoc($cherg);
			$childs = $ch['childs'];
			mysqli_free_result($cherg);
			$cur_item = $pgsubcatitem;
			$permsql = "SELECT Permalink FROM " . $pgse->dbtoken . "productgrouplanguage WHERE PgCount = " . $sub['ObjectCount'] . " AND LanguageId = '" . $pgse->lngID . "'";
			$permerg = mysqli_query($subdbh, $permsql);
			$perm = mysqli_fetch_assoc($permerg);
		
			$url = 'index.php?page=productgroup&amp;idx=' . $sub['ObjectCount'];
			/*A TS 09.12.2014: Permalink verwenden, wenn verfügbar*/
			if($pgse->edition == 13) {
				if($pgse->get_setting('cbUsePermalinks_Checked') == 'True') {
					if($perm['Permalink'] != '') {
						$url = $perm['Permalink'];
					}
				}
			}
			mysqli_free_result($permerg);
			$cur_item = str_replace('{GSSE_INCL_SHOWSUB}',$url,$cur_item);
			$cur_item = str_replace('{GSSE_INCL_SUBCATITEMID}',$sub['ObjectCount'],$cur_item);
			$cur_item = str_replace('{GSSE_INCL_SUBCATITEMTITLE}',$sub['ProductGroup'],$cur_item);
			$imgsql = "SELECT ImageFile FROM " . $pgse->dbtoken . "productgrouplanguage WHERE PgCount = " . $sub['ObjectCount'] . " AND LanguageId = '" . $pgse->lngID . "'";
			$imgerg = mysqli_query($subdbh, $imgsql);
			$img = mysqli_fetch_assoc($imgerg);
			if($img['ImageFile'] == '') {
				$imgfile = 'template/images/pg_no_pic.jpg';
			} else {
				$imgfile = 'images/groups/small/' . $img['ImageFile'];
			}
			$cur_item = str_replace('{GSSE_INCL_SUBCATITEMPIC}',$imgfile,$cur_item);
			$cur_items .= $cur_item;
			$iS++;
		
			mysqli_free_result($imgerg);
			
			if(($iS % 4) == 0 || $iS == $max) {
				$cur_line = $pgsubcatline;
				$cur_line = str_replace('{GSSE_INCL_SUBCATSITEMS}',$cur_items,$cur_line);
				$pgsubcatslines .= $cur_line;
				$cur_items = '';
			}
		}
		$pgsubcats = str_replace('{GSSE_INCL_SUBCATLINES}',$pgsubcatslines,$pgsubcats);
	}
	$pgse->content = str_replace($tag, $pgsubcats, $pgse->content);
	echo $pgsubcats;
}
@mysqli_close($pgdbh);
?>