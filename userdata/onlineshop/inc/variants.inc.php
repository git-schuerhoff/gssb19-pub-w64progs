<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
/*echo "<pre>";
print_r($_SESSION['aitem']);
die("</pre>");*/
$varhtml = '';
$aVarMainItem = $this->get_varmainitem($_SESSION['aitem']['itemItemNumber']);
$detdbh = $this->db_connect();
/*$vsql = "SELECT varItemNumber, ShowAsDropDown, " .
		  "(SELECT itemItemDescription FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS itemName, " .
		  "(SELECT ImageName FROM " .
		  $this->dbtoken . "gallery WHERE " . $this->dbtoken . "gallery.itemId = " . 
		  $this->dbtoken . "item_to_variant.varItemCount " .
		  "ORDER BY ".$this->dbtoken."gallery.ImageOrder ASC LIMIT 1) AS itemPic, " .
		  "(SELECT itemItemId FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS itemId, " .
		  "(SELECT itemVariantDescription FROM " .
		  $this->dbtoken . "itemdata WHERE " . $this->dbtoken . "itemdata.itemItemNumber = " . 
		  $this->dbtoken . "item_to_variant.varItemNumber AND " .
		  $this->dbtoken . "itemdata.itemLanguageId = '" . $this->lngID . "') AS VarName " .
		  "FROM " . $this->dbtoken . "item_to_variant WHERE " .
		  "varVariantGroup = '" . $aVarMainItem['itemNumber'] . "' ORDER BY variantOrder ASC";*/
$vsql = "SELECT v.varItemNumber, v.ShowAsDropDown, i.itemItemId AS itemId, i.itemItemDescription AS itemName, ".
		"i.itemVariantDescription AS VarName, i.itemItemPage AS itemPage, ".
		"(SELECT ImageName FROM ".$this->dbtoken."gallery WHERE ".$this->dbtoken."gallery.itemId=v.varItemCount ORDER BY ".$this->dbtoken."gallery.ImageOrder ASC LIMIT 1) AS itemPic ".
		"FROM ".$this->dbtoken."item_to_variant v ".
		"JOIN ".$this->dbtoken."itemdata i ON i.itemItemId=v.varItemCount ".
		"WHERE varVariantGroup='".$aVarMainItem['itemNumber']."' ORDER BY variantOrder ASC";
$verg = mysqli_query($detdbh,$vsql);
if(mysqli_errno($detdbh) == 0) {
	if(mysqli_num_rows($verg) > 0) {
		$aVars = array();
		$aImages = $this->get_itempics($_SESSION['aitem']['itemItemId']);
		$showDropDown = 0;
		//TS: Zuerst der aktuelle Artikel
		$cvarname = ($_SESSION['aitem']['itemVariantDescription'] != "") ? $_SESSION['aitem']['itemVariantDescription'] : $_SESSION['aitem']['itemItemDescription'];
		if($_SESSION['aitem']['itemItemNumber'] == $aVarMainItem['itemNumber']) {
			$aVars[] = array("ItemNumber" => $_SESSION['aitem']['itemItemNumber'],
							 "ItemName" => $cvarname,
							 "ItemPic" => $aImages[0]['ImageName'],
							 "ItemId" => $_SESSION['aitem']['itemItemId'],
							 "ItemPage" => $_SESSION['aitem']['itemItemPage']
			);
		} else {
			$aVarImages = $this->get_itempics($aVarMainItem['itemId']);
			$aVars[] = array("ItemNumber" => $aVarMainItem['itemNumber'],
							 "ItemName" => $aVarMainItem['itemName'],
							 "ItemPic" => $aVarImages[0]['ImageName'],
							 "ItemId" => $aVarMainItem['itemId'],
							 "ItemPage" => $aVarMainItem['itemItemPage']
			);
		}
		//TS: Dann die zugehörigen Varianten
		while($v = mysqli_fetch_assoc($verg)) {
			$cvarname = ($v['VarName'] != "") ? $v['VarName'] : $v['itemName'];
			
			$aVars[] = array("ItemNumber" => $v['varItemNumber'],
											"ItemName" => $cvarname,
											"ItemPic" => $v['itemPic'],
											"ItemId" => $v['itemId'],
											"ItemPage" => $v['itemPage']);
			if($v['ShowAsDropDown'] == 'Y') {
				$showDropDown = 1;
			}
		}
		mysqli_free_result($verg);
		$varhtml = file_get_contents($this->absurl . 'template/variants_outer.html');
		$varhtml = str_replace('{GSSE_LANG_VARTXT}',$this->get_lngtext('LangTagTextVariantsTitle'),$varhtml);
		if($showDropDown == 0) {
			$varitemouter = file_get_contents($this->absurl . 'template/variants_itemsouter.html');
			$varitem = file_get_contents($this->absurl . 'template/variants_item.html');
		} else {
			$varitemouter = file_get_contents($this->absurl . 'template/variants_itemsouter_dd.html');
			$varitem = file_get_contents($this->absurl . 'template/variants_item_dd.html');
		}
		
		$varallitems = '';
		//TS 23.02.2017: foreach ist schneller
		//$varmax2 = count($aVars);
		//for($cv = 0; $cv < $varmax2; $cv++) {
		$cv = 0;
		foreach($aVars as $curVar) {
			
			if($curVar['ItemId'] == $_SESSION['aitem']['itemItemId']) {
				$sel = " selected";
				$varClass = "currentVariant";
			} else {
				$sel = "";
				$varClass = "";
			}
			$cur_var_item = $varitem;
			//index.php?page=detail&amp;item={GSSE_VAR_VARITEMID}
			//{GSSE_INCL_VARLINK}
			//itemItemPage
			$varLink = 'index.php?page=detail&amp;item='.$curVar['ItemId'];
			if($this->get_setting('cbUsePermalinks_Checked') == 'True' && trim($curVar['ItemPage']) != '') {
				$varLink = $curVar['ItemPage'];
			}
			$cur_var_item = str_replace('{GSSE_INCL_VARLINK}',$varLink,$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARID}',$cv,$cur_var_item);
			$cur_var_item = str_replace('{GSSE_INCL_VARCLASS}',$varClass,$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARNAME}',$curVar['ItemName'],$cur_var_item);
			$itemPic = 'template/images/no_pic_mid.png';
			if(file_exists('images/small/'.$curVar['ItemPic'])) {
				$itemPic = 'images/small/'.$curVar['ItemPic'];
			} elseif(file_exists('images/medium/'.$curVar['ItemPic'])) {
				$itemPic = 'images/medium/'.$curVar['ItemPic'];
			} elseif(file_exists('images/big/'.$curVar['ItemPic'])) {
				$itemPic = 'images/big/'.$curVar['ItemPic'];
			}
			$cur_var_item = str_replace('{GSSE_VAR_VARPIC}',$itemPic,$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_VARITEMID}',$curVar['ItemId'],$cur_var_item);
			$cur_var_item = str_replace('{GSSE_VAR_SELECTED}',$sel,$cur_var_item);
			$varallitems .= $cur_var_item;
			$cv++;
		}//foreach
		$varitemouter = str_replace('{GSSE_DET_VARITEMS}', $varallitems, $varitemouter);
		$varhtml = str_replace('{GSSE_DET_VARITEMSOUTER}',$varitemouter,$varhtml);
	}
} else {
	$varhtml = mysqli_error($detdbh) . ":<br />" . $vsql;
}
$this->content = str_replace($tag, $varhtml, $this->content);
?>
