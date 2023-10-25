<?php
$detailsslider = $this->gs_file_get_contents($this->absurl . 'template/detailsslider.html');
$detailsslider = $this->parse_texts($this->get_tags_ret($detailsslider),$detailsslider);
$tb_item = $this->gs_file_get_contents($this->absurl . 'template/datatable_item.html');

/*$detailsslider = str_replace('',,$detailsslider);*/
/*Ausführliche Beschreibung*/
$detailtext = trim($_SESSION['aitem']['itemDetailText2']);
/*Kurzbeschreibung ggf. hinzufügen (vor der ausführlichen Beschreibung*/
if(trim($_SESSION['aitem']['itemDetailText1']) != '') {
	$detailtext = trim($_SESSION['aitem']['itemDetailText1']).'<br />'.$detailtext;
}
$detailsslider = str_replace('{GSSE_INCL_EXDESC}',$detailtext,$detailsslider);
if($detailtext == ''){
    $detailsslider = str_replace('{GSSE_CLASS_EXDESC}','display:none',$detailsslider);
} else {
    $detailsslider = str_replace('{GSSE_CLASS_EXDESC}','moretexts',$detailsslider);
}

/*Bewertungen*/
$reviews = '';
if($this->phpactive())
{
	if($this->get_setting('cbUsePhpUsercomments_Checked') == 'True')
	{
		require_once './dynsb/module/comments/class.comment.php';
		/*A TS 15.06.2015*/
		/*Aufruf von Methoden aus Comment nicht statisch*/
		$oComm = new Comment();
		/*E TS 15.06.2015*/
		$reviews = $this->gs_file_get_contents($this->absurl . 'template/detailsslider_reviews.html');
		$reviews = str_replace('{GSSE_LANG_LangTagTextMyComments1}',$this->get_lngtext('LangTagTextMyComments1'),$reviews);
		$avgRating = $oComm->getAvgRatingByItemNumberVisible($_SESSION['aitem']['itemItemNumber']);
		$aComments = $oComm->getAllCommentsByItemNumberVisible($_SESSION['aitem']['itemItemNumber']);
		$commcount = count($aComments);
		$review_items = '';
		$commimg = '';
		$commalt = '';
		if($commcount > 0)
		{
			$avgrating_html = $this->gs_file_get_contents($this->absurl . 'template/detailsslider_reviews_avg.html');
			$commimg = './dynsb/module/comments/rating' . substr(str_replace(',', '', $avgRating), 0, 1) . '.gif';
			$commalt = 'rating' . substr(str_replace(',', '', $avgRating), 0, 2) . '.gif';
			$avgrating_html = str_replace('{GSSE_LANG_LangTagTextUserCommentsAvg}',$this->get_lngtext('LangTagTextUserCommentsAvg'),$avgrating_html);
			$avgrating_html = str_replace('{GSSE_INCL_USERCOMMIMG}',$commimg,$avgrating_html);
			$avgrating_html = str_replace('{GSSE_INCL_USERCOMMALT}',$commalt,$avgrating_html);
			$review_items = '';
			$reviewitem = $this->gs_file_get_contents('template/usercommentsitem.html');
			foreach ($aComments as $comment)
			{
				$rating = $comment->getRating();
				$comimg = './dynsb/module/comments/rating' . $rating . '.gif';
				$comalt = 'rating' . $rating;
				$comsub = $comment->getSubject();
				$comdat = $comment->getDate(1);
				$comtxt = $comment->getBody(true);
				$cur_comm = $reviewitem;
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSIMG}',$comimg,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSALT}',$comalt,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSSUBJECT}',$comsub,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSDATE}',$comdat,$cur_comm);
				$cur_comm = str_replace('{GSSE_INCL_USERCOMMENTSBODY}',$comtxt,$cur_comm);
				$review_items .= $cur_comm;
			}
            $reviews = str_replace('{GSSE_CLASS_DSREVIEWS}','moretexts',$reviews);
		}
		else
		{
			$avgrating_html = '';
			$review_items = $this->get_lngtext('LangTagNoRatings');
            $reviews = str_replace('{GSSE_CLASS_DSREVIEWS}','no-display',$reviews);
		}
		$reviews = str_replace('{GSSE_INCL_AVGRATING}',$avgrating_html,$reviews);
		$reviews = str_replace('{GSSE_INCL_REVIEWS}',$review_items,$reviews);
	}
}
$detailsslider = str_replace('{GSSE_INCL_DSREVIEWS}',$reviews,$detailsslider);

/*Hersteller*/
$aManu = array();
if($_SESSION['aitem']['itemManufacturer'] != '')
{
	/*array_push($aManu,array($this->get_lngtext('LangTagManufacturer'),$_SESSION['aitem']['itemManufacturer']));*/
	$aManu[] = array($this->get_lngtext('LangTagManufacturer'),$_SESSION['aitem']['itemManufacturer']);
}
if($_SESSION['aitem']['itemManufacturerProductCode'] != '')
{
	/*array_push($aManu,array($this->get_lngtext('LangTagManufacturerNo'),$_SESSION['aitem']['itemManufacturerProductCode']));*/
	$aManu[] = array($this->get_lngtext('LangTagManufacturerNo'),$_SESSION['aitem']['itemManufacturerProductCode']);
}
if($_SESSION['aitem']['itemBrand'] != '')
{
	/*array_push($aManu,array($this->get_lngtext('LangTagBrand'),$_SESSION['aitem']['itemBrand']));*/
	$aManu[] = array($this->get_lngtext('LangTagBrand'),$_SESSION['aitem']['itemBrand']);
}
if($_SESSION['aitem']['itemEAN_ISBN'] != '')
{
	/*array_push($aManu,array($this->get_lngtext('LangTagEANISBN'),$_SESSION['aitem']['itemEAN_ISBN']));*/
	$aManu[] = array($this->get_lngtext('LangTagEANISBN'),$_SESSION['aitem']['itemEAN_ISBN']);
}
$all_items = '';
$manumax = count($aManu);
if($manumax > 0)
{
	for($m = 0; $m < $manumax; $m++)
	{
		if($m == 0)
		{
			$fol = 'first ';
		}
		else
		{
			if($m == ($manumax - 1))
			{
				$fol = 'last ';
			}
			else
			{
				$fol = '';
			}
		}
		if(($m % 2) == 0)
		{
			$eoo = 'even';
		}
		else
		{
			$eoo = 'odd';
		}
		$cur_item = $tb_item;
		$cur_item = str_replace('{GSSE_INCL_FOL}',$fol,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_EOO}',$eoo,$cur_item);
		$cur_item = str_replace('{GSSE_INCL_DATATITLE}',$aManu[$m][0],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_DATAVALUE}',$aManu[$m][1],$cur_item);
		$all_items .= $cur_item;
	}
}
$detailsslider = str_replace('{GSSE_INCL_MANUINFOS}',$all_items,$detailsslider);
if($all_items == ''){
    $detailsslider = str_replace('{GSSE_CLASS_MANUINFOS}','display:none;',$detailsslider);
} else {
    $detailsslider = str_replace('{GSSE_CLASS_MANUINFOS}','moretexts',$detailsslider);
}

/*Downloads*/
$all_items = '';
$dl_item = $this->gs_file_get_contents($this->absurl . 'template/downloads-item.html');
$aDownloads = $this->get_itemdownloads($_SESSION['aitem']['itemItemNumber']);
$dlmax = count($aDownloads);
if($dlmax > 0)
{
	for($ad = 0; $ad < $dlmax; $ad++)
	{
		$icon = $this->get_file_icon($aDownloads[$ad]['filename']);
		$cur_item = $dl_item;
		$cur_item = str_replace('{GSSE_INCL_LINKCLASS}','',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_LINKURL}','javascript:freedownload(\'' . $aDownloads[$ad]['filename'] . '\');',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_TITLE}',$aDownloads[$ad]['title'],$cur_item);
		$cur_item = str_replace('{GSSE_INCL_IMGCLASS}','gs-downloadicon',$cur_item);
		$cur_item = str_replace('{GSSE_INCL_IMGSRC}','template/images/' . $icon,$cur_item);
		$all_items .= $cur_item;
	}
}
$detailsslider = str_replace('{GSSE_INCL_DOWNLOADS}',$all_items,$detailsslider);
if($all_items == ''){
    $detailsslider = str_replace('{GSSE_CLASS_DOWNLOADS}','display:none;',$detailsslider);
} else {
    $detailsslider = str_replace('{GSSE_CLASS_DOWNLOADS}','moretexts',$detailsslider);
}
/*HTML-text 1*/
if ($_SESSION['aitem']['itemHtml1Caption'] <> '')
{
	$detailsslider = str_replace('{GSSE_INCL_HTML1CAPTION}',$_SESSION['aitem']['itemHtml1Caption'],$detailsslider);
}
else
{
	$detailsslider = str_replace('{GSSE_INCL_HTML1CAPTION}','',$detailsslider);
}
$detailsslider = str_replace('{GSSE_INCL_HTMLTEXT1}',$_SESSION['aitem']['itemHtmlText1'],$detailsslider);
if($_SESSION['aitem']['itemHtmlText1'] == ''){
    $detailsslider = str_replace('{GSSE_CLASS_HTMLTEXT1}','display:none;',$detailsslider);
} else {
    $detailsslider = str_replace('{GSSE_CLASS_HTMLTEXT1}','moretexts',$detailsslider);
}
/*HTML-Text 2*/
if ($_SESSION['aitem']['itemHtml2Caption'] <> '')
{
	$detailsslider = str_replace('{GSSE_INCL_HTML2CAPTION}',$_SESSION['aitem']['itemHtml2Caption'],$detailsslider);
}
else
{
	$detailsslider = str_replace('{GSSE_INCL_HTML2CAPTION}','',$detailsslider);
}
$detailsslider = str_replace('{GSSE_INCL_HTMLTEXT2}',$_SESSION['aitem']['itemHtmlText2'],$detailsslider);
if($_SESSION['aitem']['itemHtmlText2'] == ''){
    $detailsslider = str_replace('{GSSE_CLASS_HTMLTEXT2}','display:none;',$detailsslider);
} else {
    $detailsslider = str_replace('{GSSE_CLASS_HTMLTEXT2}','moretexts',$detailsslider);
}


$this->content = str_replace($tag,$detailsslider,$this->content);
?>