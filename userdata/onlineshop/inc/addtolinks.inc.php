<?php
$addtolinks = $this->gs_file_get_contents($this->absurl . 'template/addtolinks.html');

$addtolinks = str_replace('{GSSE_LANG_LangTagAddToBasket}',$this->get_lngtext('LangTagAddToBasket'),$addtolinks);
//$addtolinks = str_replace('{GSSE_INCL_ITEMID}',$_SESSION['aitem']['itemItemId'],$addtolinks);

/*Loggedin-User functions*/
$wishlist = '';
$notepad = '';
$rating = '';
if($this->phpactive())
{
	/*Begin loggedin-functions*/
	if(isset($_SESSION['login']))
	{
		if($_SESSION['login']['ok'])
		{
			$cid = $_SESSION['login']['cusIdNo'];
			$itemNo = $_SESSION['aitem']['itemItemNumber'];
			$itemName = $_SESSION['aitem']['itemItemDescription'];
			$date = date("Ymd");
			/*Wishlist*/
			if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
			{
				$wishlist = $this->gs_file_get_contents($this->absurl . 'template/item_towishlist.html');
				$wishlist = str_replace('{GSSE_INCL_ITEMNO}',$itemNo,$wishlist);
				$wishlist = str_replace('{GSSE_INCL_CUSID}',$cid,$wishlist);
				$wishlist = str_replace('{GSSE_INCL_DATE}',$date,$wishlist);
				$wishlist = str_replace('{GSSE_LANG_LangTagMoveToWishList}',$this->get_lngtext('LangTagMoveToWishList'),$wishlist);
			}
		
			/*Notepad*/
			if($this->get_setting('cbUsePhpNotepad_Checked') == 'True')
			{
				$notepad = $this->gs_file_get_contents($this->absurl . 'template/item_tonotepad.html');
				$notepad = str_replace('{GSSE_INCL_ITEMNO}',$itemNo,$notepad);
				$notepad = str_replace('{GSSE_INCL_CUSID}',$cid,$notepad);
				$notepad = str_replace('{GSSE_INCL_DATE}',$date,$notepad);
				$notepad = str_replace('{GSSE_LANG_LangTagNote}',$this->get_lngtext('LangTagNote'),$notepad);
			}
		
			/*Rating*/
			if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
			{
				$rating = $this->gs_file_get_contents($this->absurl . 'template/item_torating.html');
				$rating = str_replace('{GSSE_SURL_}',$this->absurl,$rating);
				$rating = str_replace('{GSSE_INCL_SHOWCOMM}',$this->db_text_ret('itemcomments_settings|itseVisDef|itseIdNo|Y'),$rating);
				$rating = str_replace('{GSSE_LANG_LangTagTextUserCommentsAdd}',$this->get_lngtext('LangTagTextUserCommentsAdd'),$rating);
			}
		}
	}
}

/*End Loggedin-User functions*/

/*Itemcompare*/
$itemcompare = '';
if($this->get_setting('cbArticleCompare_Checked') == 'True')
{
	$itemcompare = $this->gs_file_get_contents($this->absurl . 'template/item_comparison.html');
	$itemcompare = str_replace('{GSSE_LANG_LangTagArticleCompare}',$this->get_lngtext('LangTagArticleCompare'),$itemcompare);
	$itemcompare = str_replace('{GSSE_INCL_ICID}',$_SESSION['aitem']['itemItemId'],$itemcompare);
	$itemcompare = str_replace('{GSSE_INCL_ICNAME}',urlencode($_SESSION['aitem']['itemItemDescription']),$itemcompare);
	$itemcompare = str_replace('{GSSE_INCL_ICPAGE}',$_SESSION['aitem']['itemItemPage'],$itemcompare);
}

$addtolinks = str_replace('{GSSE_INCL_WISHLIST}',$wishlist,$addtolinks);
$addtolinks = str_replace('{GSSE_INCL_COMPAREITEM}',$itemcompare,$addtolinks);
$addtolinks = str_replace('{GSSE_INCL_NOTEPAD}',$notepad,$addtolinks);
$addtolinks = str_replace('{GSSE_INCL_RATING}',$rating,$addtolinks);

$this->content = str_replace($tag,$addtolinks,$this->content);
?>