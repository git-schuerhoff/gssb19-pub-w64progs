<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	$itemgroup = '';
	$itemgroup .= '<script language="javascript">'.PHP_EOL.
		'var g_outer_grid=decodeBase64("'.base64_encode($_SESSION['template']['itemsboxed_outer_layout.html']).'");'.PHP_EOL.
		'var g_outer_gridbox=decodeBase64("'.base64_encode($_SESSION['template']['itemsbox.html']).'");'.PHP_EOL.
		'var g_outer_list=decodeBase64("'.base64_encode($_SESSION['template']['itemsoverview_outer_layout.html']).'");'.PHP_EOL.
		'var g_outer_listbox=decodeBase64("'.base64_encode($_SESSION['template']['itemslistbox.html']).'");'.PHP_EOL.
		'var g_lbl_sale=decodeBase64("'.base64_encode($_SESSION['template']['labelsale.html']).'");'.PHP_EOL.
		'var g_lbl_new=decodeBase64("'.base64_encode($_SESSION['template']['labelnew.html']).'");'.PHP_EOL.
		'var g_lbl_best=decodeBase64("'.base64_encode($_SESSION['template']['labelbest.html']).'");'.PHP_EOL.
		'var g_itemcompare=decodeBase64("'.base64_encode($_SESSION['template']['item_comparison.html']).'");'.PHP_EOL.
		'var g_oldprice=decodeBase64("'.base64_encode($_SESSION['template']['oldpricenew.html']).'");'.PHP_EOL.
		'var g_availbox=decodeBase64("'.base64_encode($_SESSION['template']['availbox.html']).'");'.PHP_EOL.
		'var g_addtocartsmall=decodeBase64("'.base64_encode($_SESSION['template']['addtocartsmall.html']).'");'.PHP_EOL.
		'var g_addtocart=decodeBase64("'.base64_encode($_SESSION['template']['addtocart.html']).'");'.PHP_EOL.
		'var g_itemimg_qs=decodeBase64("'.base64_encode($_SESSION['template']['itemimg_qs.html']).'");'.PHP_EOL.
		'var g_itemimg=decodeBase64("'.base64_encode($_SESSION['template']['itemimg.html']).'");'.PHP_EOL.
		'var g_itemnamedetail=decodeBase64("'.base64_encode($_SESSION['template']['itemdetail.html']).'");'.PHP_EOL.
		'var g_itemname=decodeBase64("'.base64_encode($_SESSION['template']['itemtitle.html']).'");'.PHP_EOL.
		'var g_pagerouter=decodeBase64("'.base64_encode($_SESSION['template']['pager_outer.html']).'");'.PHP_EOL.
		'var g_pageritem=decodeBase64("'.base64_encode($_SESSION['template']['pager_item.html']).'");'.PHP_EOL.
		'var g_pageraitem=decodeBase64("'.base64_encode($_SESSION['template']['pager_a_item.html']).'");'.PHP_EOL.
		'var g_pagerprev=decodeBase64("'.base64_encode($_SESSION['template']['pager_previous.html']).'");'.PHP_EOL.
		'var g_pagernext=decodeBase64("'.base64_encode($_SESSION['template']['pager_next.html']).'");'.PHP_EOL.
		'var g_itemwishlist=decodeBase64("'.base64_encode($_SESSION['template']['item_towishlist.html']).'");'.PHP_EOL.
		'var g_rm_itemwishlist=decodeBase64("'.base64_encode($_SESSION['template']['item_fromwishlist.html']).'");'.PHP_EOL.
		'var g_itemnotepad=decodeBase64("'.base64_encode($_SESSION['template']['item_tonotepad.html']).'");'.PHP_EOL.
		'var g_rm_itemnotepad=decodeBase64("'.base64_encode($_SESSION['template']['item_fromnotepad.html']).'");'.PHP_EOL.
		'var g_image=decodeBase64("'.base64_encode($_SESSION['template']['image.html']).'");'.PHP_EOL.
		'var g_gotodetail=decodeBase64("'.base64_encode($_SESSION['template']['gotodetail.html']).'");'.PHP_EOL.
		'var g_attributeOuter=decodeBase64("'.base64_encode($_SESSION['template']['attributes.html']).'");'.PHP_EOL.
		'var g_select=decodeBase64("'.base64_encode($_SESSION['template']['select.html']).'");'.PHP_EOL.
		'</script>'.PHP_EOL;
	
	$this->content = str_replace($tag,$itemgroup,$this->content);
?>