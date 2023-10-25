<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$itemcompare = '';
$aComp = $_SESSION['aitems_compare'];
$iCols = count($aComp);
$itemval = $this->gs_file_get_contents('template/ic_values.html');
$features = '';
$valclass = 'std';

function getFeatureValue($itemid,$featuregroupid,$featureid,$se,$dbh){
	$vsql = 'SELECT fti.featurevalue FROM ' . $se->dbtoken . 'itemdata i '.
			'left join ' . $se->dbtoken . 'items2group ig on ig.ItemID=i.itemItemId '.
			'left join ' . $se->dbtoken . 'featurestoitem fti on fti.itemid=i.itemItemId '.
			'left join ' . $se->dbtoken . 'featuresgrtoitemsgr fgtig on fgtig.itemsgroupid=ig.ProductGroup '.
			'left join ' . $se->dbtoken . 'featuresgroup fg on fg.id=fgtig.featuresgroupid '.
			'right join ' . $se->dbtoken . 'features f on f.id=fti.featureid and f.groupid=fg.id '.
			'where i.itemItemId='.$itemid.' and fg.id='.$featuregroupid.' and f.id='.$featureid;
	$erg = mysqli_query($dbh,$vsql);
	$ret = mysqli_fetch_assoc($erg);
	return $ret['featurevalue'];
}

if($iCols > 0){
	$ids = '(';
	for($pc = 0; $pc < $iCols; $pc++){
		$ids = $ids . $aComp[$pc]['idx'] . ',';
	}
	$ids = substr($ids,0,-1);
	$ids = $ids . ')';
	$dbh = $this->db_connect();
	/* Features Gruppen */
	$fgsql = "SELECT fg.name, fg.id FROM " . $this->dbtoken . "itemdata i ".
			"left join " . $this->dbtoken . "items2group ig on ig.ItemID=i.itemItemId ".
			"left join " . $this->dbtoken . "featurestoitem fti on fti.itemid=i.itemItemId ".
			"left join " . $this->dbtoken . "featuresgrtoitemsgr fgtig on fgtig.itemsgroupid=ig.ProductGroup ".
			"left join " . $this->dbtoken . "featuresgroup fg on fg.id=fgtig.featuresgroupid ".
			"right join " . $this->dbtoken . "features f on f.id=fti.featureid and f.groupid=fg.id ".
			"where i.itemItemId in " . $ids . " and fg.langid='" . $this->lngID . "' group by fg.name";
	$fgerg = mysqli_query($dbh,$fgsql);
	$features = '<tr>';
	while($fgres = mysqli_fetch_assoc($fgerg)){
		/*Features Gruppe Überschrift*/
		$cur_itemdescr = '<th><span class="nobr">{GSSE_INCL_ICATTRVALUE}</span></th>';//$itemval;
		//$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRCLASS}','',$cur_itemdescr);
		$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRVALUE}',$fgres['name'].':',$cur_itemdescr);
		$features .= $cur_itemdescr;
		for($pc = 0; $pc < $iCols; $pc++){
			$cur_itemdescr = $itemval;
			$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRCLASS}','',$cur_itemdescr);
			$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRVALUE}','',$cur_itemdescr);
			$features .= $cur_itemdescr;
		}
		$features .= "</tr><tr>";
		/*Einzelne Features*/
		$fsql ="SELECT f.name, f.id FROM " . $this->dbtoken . "itemdata i ".
			"left join " . $this->dbtoken . "items2group ig on ig.ItemID=i.itemItemId ".
			"left join " . $this->dbtoken . "featurestoitem fti on fti.itemid=i.itemItemId ".
			"left join " . $this->dbtoken . "featuresgrtoitemsgr fgtig on fgtig.itemsgroupid=ig.ProductGroup ".
			"left join " . $this->dbtoken . "featuresgroup fg on fg.id=fgtig.featuresgroupid ".
			"right join " . $this->dbtoken . "features f on f.id=fti.featureid and f.groupid=fg.id ".
			"where i.itemItemId in " . $ids . " and fg.id='" . $fgres['id'] . "' group by f.name";
		$ferg = mysqli_query($dbh,$fsql);
		while($fres = mysqli_fetch_assoc($ferg)){
			$cur_itemdescr = '<th><span class="nobr">{GSSE_INCL_ICATTRVALUE}</span></th>';//$itemval;
			//$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemdescr);
			$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRVALUE}',$fres['name'],$cur_itemdescr);
			$features .= $cur_itemdescr;
			for($pc = 0; $pc < $iCols; $pc++){
				$cur_itemdescr = $itemval;
				$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRCLASS}',$valclass,$cur_itemdescr);
				$cur_itemdescr = str_replace('{GSSE_INCL_ICATTRVALUE}',getFeatureValue($aComp[$pc]['idx'],$fgres['id'],$fres['id'],$this,$dbh),$cur_itemdescr);
				$features .= $cur_itemdescr;
			}
			$features .= "</tr><tr>";
		}
	}
	$features .= "</tr>";
}	

$this->content = str_replace('{GSSE_FUNC_FEATURES}', $features, $this->content);	
?>