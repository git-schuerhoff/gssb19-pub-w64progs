<?php
	/*PhpLogVisitPage*/
	if(file_exists("dynsb/class/class.pagestatistics.php"))
	{
		require_once("dynsb/class/class.pagestatistics.php");
		$insert = new pagestatistics();
		$insert->querySetUserclicks( session_id() );
	}
	
	/*phpSessionLog*/
	if(file_exists("dynsb/class/class.pagestatistics.php"))
	{
		require_once("dynsb/class/class.pagestatistics.php");
		$insert = new pagestatistics();
		$insert->querySetUserDetails(session_id(), null,null,"dynsb/class/php_browscap.ini");
	} 
	else 
	{
		echo "Web-Server-Fehler - Statistics!"; 
	}
	
	require_once './dynsb/module/comments/class.shopcomment.php';
	
	$sc = new Shopcomment();

	$avgRating = $sc->getAvgRatingVisible();
	$aComments = $sc->getAllCommentsVisible();
	
	if (count($aComments) > 0) 
	{
		// Durchschnittliche Kundenbewertung:
		$avgRating = substr(str_replace(',', '', $avgRating), 0, 2);
		$this->content = str_replace('{GSSE_VAL_AVGRATING}', $this->get_lngtext('LangTagTextUserCommentsAvg').": <img src='template/images/rating".$avgRating.".gif' alt='template/images/rating".$avgRating.".gif' />", $this->content);
		$shopcomments = "";
		foreach ($aComments as $comment) 
		{
			$rating = $comment->getRating();
			$tmplFile = "shopcomments.html";
			$shopcommentshtml = file_get_contents('template/' . $tmplFile);
			$shopcommentshtml = str_replace('{GSSE_VAL_RATING}', $rating, $shopcommentshtml);
			$shopcommentshtml = str_replace('{GSSE_VAL_SUBJECT}', $comment->getSubject(), $shopcommentshtml);
			$shopcommentshtml = str_replace('{GSSE_VAL_DATE}', $comment->getDate(1), $shopcommentshtml);
			$shopcommentshtml = str_replace('{GSSE_VAL_GETBODY}', $comment->getBody(true), $shopcommentshtml);
			$shopcomments .= $shopcommentshtml;
		} 
		//"Rückgabe" $shopcommentshtml
		$this->content = str_replace('{GSSE_FUNC_SHOPCOMMENTS}', $shopcomments, $this->content);
	} 
	else 
	{
		// Keine Shopbewertungen vorhanden
		$this->content = str_replace('{GSSE_VAL_AVGRATING}', '', $this->content);
		$this->content = str_replace('{GSSE_FUNC_SHOPCOMMENTS}', $this->get_lngtext('LangTagNoShopComments'), $this->content);
	}
?>