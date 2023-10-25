<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$bcnhtml = $this->gs_file_get_contents('template/breadcrumbnavi.html');
$bcnpgroup = $this->gs_file_get_contents('template/breadcrumbnavipgroup.html');
$bcnline = '';
if(isset($_SESSION['anavi']))
{
	$bcnmax = count($_SESSION['anavi']);
	if($bcnmax > 0)
	{
		for($n = 0; $n < $bcnmax; $n++)
		{
			if($n <= 5)
			{
				$level = $n;
			}
			else
			{
				$level = 5;
			}
			$showsub = ' onclick="gsse_showsub(' . $_SESSION['anavi'][$n]['group'] . ',\'pgid_' . $_SESSION['anavi'][$n]['group'] . '\',' . $level . ',' . $_SESSION['anavi'][$n]['parent'] . ',\'' . $_SESSION['anavi'][$n]['title'] . '\',' . $_SESSION['anavi'][$n]['childs'] . ')"';
			$cur_item = $bcnpgroup;
			$cur_item = str_replace('{GSSE_BGN_PGID}', $_SESSION['anavi'][$n]['group'], $cur_item);
			$cur_item = str_replace('{GSSE_BGN_SHOWSUB}', $showsub, $cur_item);
			$cur_item = str_replace('{GSSE_BGN_PGTITLE}', $_SESSION['anavi'][$n]['title'], $cur_item);
			$bcnline .= $cur_item;
		}
	}
}
//Bei Detail-Seite auch Artikelnamen zeigen
if(isset($_SESSION['aitem']) && $_GET['page'] == 'detail')
{
	$bcnitem = $this->gs_file_get_contents('template/breadcrumbnaviitem.html');
	$bcnitem = str_replace('{GSSE_BGN_ITEMTITLE}', $_SESSION['aitem']['itemItemDescription'], $bcnitem);
	$bcnline .= $bcnitem;
}

//Im Warenkorb "Warenkorb" anzeigen
if($_GET['page'] == 'basket')
{
	$bcnitem = $this->gs_file_get_contents('template/breadcrumbnaviitem.html');
	$bcnitem = str_replace('{GSSE_BGN_ITEMTITLE}', $this->get_lngtext('LangTagBasket'), $bcnitem);
	$bcnline .= $bcnitem;
}

$bcnhtml = str_replace('{GSSE_BCN_NAVI}', $bcnline, $bcnhtml);
$this->content = str_replace($tag, $bcnhtml, $this->content);
?>