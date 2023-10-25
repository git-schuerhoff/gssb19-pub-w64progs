<?php
$sitemaphtml = '';
$outer = file_get_contents('template/sm_outer.html');
$item = file_get_contents('template/sm_item.html');
$link = file_get_contents('template/navi_line_sm.html');

$sitemaphtml .= get_menuitems(0, $this, $outer, $item, $link);

$this->content = str_replace($tag, $sitemaphtml, $this->content);

function get_menuitems($parent, $se, $outer, $item, $link)
{
	$smdbh = $se->db_connect();
	$cur_outer = $outer;
	if($parent == 0)
	{
		$cur_class = '';
	}
	else
	{
		$cur_class = 'subc';
	}
	$smsql = "SELECT ObjectCount, ProductGroup, Permalink FROM " . $se->dbtoken . "productgroups WHERE Parent = '" . $parent . "' ORDER BY Sequence ASC";
	$smerg = mysqli_query($smdbh,$smsql);
	$items = '';
	while($p = mysqli_fetch_object($smerg))
	{
		$cur_item = $item;
		$cur_link = $link;
		$url = 'index.php?page=productgroup&amp;idx=' . $p->ObjectCount;
		/*A TS 09.12.2014: Permalink verwenden, wenn verfügbar*/
		if($se->edition == 13) {
			if($se->get_setting('cbUsePermalinks_Checked') == 'True') {
				if($p->Permalink != '') {
					$url = $p->Permalink;
				} else {
					$plsql = "SELECT Permalink FROM " . $se->dbtoken . "productgrouplanguage WHERE ProductGroup='".$p->ProductGroup."' and languageid='".$se->lngID."'";
					$plerg = mysqli_query($smdbh,$plsql);
					while($pl = mysqli_fetch_object($plerg)){
						if($pl->Permalink != '') {
							$url = $pl->Permalink;
						}
					}
				}
			}
		}
		$cur_link = str_replace('{GSSE_PG_LINK}',$url,$cur_link);
		$cur_link = str_replace('{GSSE_PG_TITLE}',$p->ProductGroup,$cur_link);
		$cur_item = str_replace('{GSSE_INCL_SMCLASS}',$cur_class,$cur_item);
		$subs = hasSubItems($p->ObjectCount,$se);
		if($subs > 0)
		{
			$cur_link .= get_menuitems($p->ObjectCount, $se, $outer, $item, $link);
		}
		$cur_item = str_replace('{GSSE_INCL_SMITEM}',$cur_link,$cur_item);
		$items .= $cur_item;
	}
	$cur_outer = str_replace('{GSSE_INCL_SMITEMS}',$items,$cur_outer);
	return $cur_outer;
}

function hasSubItems($pg,$se2)
{
	$subs = 0;
	$hpdbh = $se2->db_connect();
	$hpsql = "SELECT ObjectCount FROM " . $se2->dbtoken . "productgroups WHERE Parent = '" . $pg . "'";
	$hperg = mysqli_query($hpdbh,$hpsql);
	if(mysqli_errno($hpdbh) == 0)
	{
		$subs = mysqli_num_rows($hperg);
	}
	mysqli_free_result($hperg);
	mysqli_close($hpdbh);
	return $subs;
}
?>