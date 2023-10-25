<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$staticp = '';
if($this->phpactive())
{
	$stdbh = $this->db_connect();
	$stsql = "SELECT * FROM " . $this->dbtoken . "staticpages WHERE 1";
	$sterg = mysqli_query($stdbh,$stsql);
	if(mysqli_errno($stdbh) == 0)
	{
		if(mysqli_num_rows($sterg) > 0)
		{
			$staticp = file_get_contents('template/staticpagesmenu.html');
			$sitem = file_get_contents('template/staticpagesmenuitem.html');
			$allitems = '';
			while($st = mysqli_fetch_assoc($sterg))
			{
				$cur_item = $sitem;
				$cur_item = str_replace('{GSSE_INCL_STATICMENULINK}',$st['menuentryURL'],$cur_item);
				$allitems .= $cur_item;
			}
			$staticp = str_replace('{GSSE_INCL_STATICMENUITEMS}',$allitems,$staticp);
		}
		
	}
	
}
$this->content = str_replace($tag, $staticp, $this->content);
?>