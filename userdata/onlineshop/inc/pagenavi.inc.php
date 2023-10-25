<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$num = $this->get_setting('edBreakPageAfter_Text');
$max = 0;
$limit = '';

$pnavhtml = '';
$pnavback = '';
$pnavpages = '';
$pnavfor = '';
$pgtmpl = $this->get_pgtemplate($_GET['idx']);


if($_GET['childs'] == 0 && $pgtmpl != 'shoppage_simplelist.htm')
{
	if($num > 0)
	{
		$pndbh = $this->db_connect();
		$pnsql = "SELECT COUNT(ID) AS summe FROM " . $this->dbtoken . "items2group LEFT JOIN " . $this->dbtoken . "itemdata ON " . $this->dbtoken . "items2group.ItemID = " . $this->dbtoken . "itemdata.itemItemId WHERE " . $this->dbtoken . "items2group.ProductGroup = '" . $_GET['idx'] . "' AND " . $this->dbtoken . "itemdata.itemIsActive = 'Y' AND " . $this->dbtoken . "itemdata.itemIsVariant = 'N'";
		$pnerg = mysqli_query($pndbh,$pnsql);
		if(mysqli_errno($pndbh) == 0)
		{
			if(mysqli_num_rows($pnerg) == 1)
			{
				$pn = mysqli_fetch_assoc($pnerg);
				$max = $pn['summe'];
				if($max > $num)
				{
					$pnavhtml = file_get_contents('template/pagenavi.html');
					if(($max % $num) == 0)
					{
						$pages = $max / $num;
					}
					else
					{
						$pages = floor($max / $num) + 1;
					}
					if(isset($_GET['start']))
					{
						if($_GET['start'] >= $num)
						{
							$newstart = $_GET['start'] - $num;
							$pnavback = file_get_contents('template/pagenavibackward.html');
							$pnavback = str_replace('{GSSE_PGN_IDX}',$_GET['idx'],$pnavback);
							$pnavback = str_replace('{GSSE_PGN_CHILDS}',$_GET['childs'],$pnavback);
							$pnavback = str_replace('{GSSE_PGN_BACKWARD}',$newstart,$pnavback);
						}
					}
					$pnavhtml =  str_replace('{GSSE_PGN_GOLEFT}',$pnavback,$pnavhtml);
					$pnavpage = file_get_contents('template/pagenavipage.html');
					$newstart = 0;
					for($p = 1; $p <= $pages; $p++)
					{
						$hilite = '';
						if($newstart == $_GET['start'])
						{
							$hilite = ' highlight';
						}
						$cur_page = $pnavpage;
						$cur_page = str_replace('{GSSE_PGN_IDX}',$_GET['idx'],$cur_page);
						$cur_page = str_replace('{GSSE_PGN_CHILDS}',$_GET['childs'],$cur_page);
						$cur_page = str_replace('{GSSE_PGN_START}',$newstart,$cur_page);
						$cur_page = str_replace('{GSSE_PGN_HILITE}',$hilite,$cur_page);
						$cur_page = str_replace('{GSSE_PGN_PAGE}',$p,$cur_page);
						$pnavpages .= $cur_page;
						$newstart = $newstart + $num;
					}
					$pnavhtml =  str_replace('{GSSE_PGN_PAGES}',$pnavpages,$pnavhtml);
					if(isset($_GET['start']))
					{
						if($_GET['start'] < $max)
						{
							if(($_GET['start'] + $num) < $max)
							{
								$newstart = $_GET['start'] + $num;
								$pnavfor = file_get_contents('template/pagenaviforward.html');
								$pnavfor = str_replace('{GSSE_PGN_IDX}',$_GET['idx'],$pnavfor);
								$pnavfor = str_replace('{GSSE_PGN_CHILDS}',$_GET['childs'],$pnavfor);
								$pnavfor = str_replace('{GSSE_PGN_FORWARD}',$newstart,$pnavfor);
							}
						}
					}
					$pnavhtml =  str_replace('{GSSE_PGN_GORIGHT}',$pnavfor,$pnavhtml);
				}
			}
		}
		mysqli_free_result($pnerg);
		mysqli_close($pndbh);
	}
}
$this->content = str_replace($tag, $pnavhtml, $this->content);
?>