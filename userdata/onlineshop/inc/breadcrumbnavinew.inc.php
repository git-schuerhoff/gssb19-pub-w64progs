<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$bcn = '';
$bcn = $this->gs_file_get_contents("template/breadcrumbnavi.html");
$bcnitem = $this->gs_file_get_contents("template/breadcrumbnaviitem.html");
$bcnlastitem = $this->gs_file_get_contents("template/breadcrumbnavilastitem.html");
$bcnsep = $this->gs_file_get_contents("template/breadcrumbnavi_seperator.html");
$aBCNTags = $this->get_tags_ret($bcn);
$bcn = $this->parse_texts($aBCNTags,$bcn);
$allitems = '';

if($_GET['page'] == 'productgroup') {
	$_SESSION['anavi'] = array();
	get_pgpath($this, $_GET['idx']);
	$_SESSION['anavi'] = array_reverse($_SESSION['anavi']);
}

$firstsep = '';
//http://127.0.0.1/sb15/index.php?page=productgroup&idx=166&childs=0&start=0&name=Komplettsysteme
if(isset($_SESSION['anavi'])){
	$bcnmax2 = count($_SESSION['anavi']);
} else {
	$bcnmax2 = 0;
}	
if($bcnmax2 > 0)
{
	$firstsep = $bcnsep;
	for($p = 0; $p < $bcnmax2; $p++) {
		if($p == ($bcnmax2 - 1) && $_GET['page'] == 'productgroup') {
			$cur_item = $bcnlastitem;
		} else {
			$cur_item = $bcnitem;
		}
		
		$bcnlink = 'index.php?page=productgroup&idx=' . $_SESSION['anavi'][$p]['idx'] . '&childs=0&start=0&name=' . $_SESSION['anavi'][$p]['name'];
		/*A TS 09.12.2014: Permalink verwenden, wenn verfügbar*/
		if($this->edition == 13) {
			if($this->get_setting('cbUsePermalinks_Checked') == 'True') {
				if($_SESSION['anavi'][$p]['perma'] != '') {
					$bcnlink = $_SESSION['anavi'][$p]['perma'];
				}
			}
		}
		
		$cur_item = str_replace('{GSSE_BCN_LINK}',$bcnlink,$cur_item);
		$cur_item = str_replace('{GSSE_BCN_NAMENEW}',$_SESSION['anavi'][$p]['name'],$cur_item);
		$cur_item = str_replace('{GSSE_BCN_SEPNEW}',$bcnsep,$cur_item);
		$cur_item = str_replace('{GSSE_BCN_CLASSNEW}','',$cur_item);
		$allitems .= $cur_item;
	}
	
	if($_GET['page'] == 'detail') {
		$cur_item = $bcnlastitem;
		$cur_item = str_replace('{GSSE_BCN_NAMENEW}',$_SESSION['aitem']['itemItemDescription'],$cur_item);
		$cur_item = str_replace('{GSSE_BCN_SEPNEW}',$bcnsep,$cur_item);
		$cur_item = str_replace('{GSSE_BCN_CLASSNEW}','',$cur_item);
		$allitems .= $cur_item;
	}
	
}

$bcn = str_replace('{GSSE_BCN_SEPNEW}',$firstsep,$bcn);
$bcn = str_replace('{GSSE_BCN_NAVINEW}',$allitems,$bcn);

$this->content = str_replace($tag, $bcn, $this->content);

function get_pgpath($se, $idx) {
	$sedbh = $se->db_connect();
	$sql = "SELECT pl.ProductGroup, pg.Parent, pl.Permalink ".
			"FROM " . $se->dbtoken . "productgrouplanguage pl ".
			"JOIN " . $se->dbtoken . "productgroups pg ON pg.ObjectCount=pl.PgCount ".
			"WHERE pl.PgCount='" . $idx . "' LIMIT 1";
	$erg = mysqli_query($sedbh,$sql);
	if(mysqli_errno($sedbh) == 0) {
		if(mysqli_num_rows($erg) > 0) {
			$z = mysqli_fetch_assoc($erg);
			$parent = $z['Parent'];
			/*array_push($_SESSION['anavi'],array("idx" => $idx, "name" => $z['ProductGroup']));*/
			$_SESSION['anavi'][] = array("idx" => $idx, "name" => $z['ProductGroup'], "perma" => $z['Permalink']);
			//@mysqli_free_result($erg);
			//@mysqli_close($sedbh);
			if($parent != 0) {
				get_pgpath($se,$parent);
			}
		}
		//@mysqli_free_result($erg);
	}
	//@mysqli_close($sedbh);
	return;
}
?>