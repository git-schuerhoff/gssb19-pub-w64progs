<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
/*echo "<br />BestsellerPG-Tag: " . $tag . "<br />";*/
if($this->phpactive() === true)
{
	$tmplFile = "bestsellerpg.html";
	$besthtml = $this->gs_file_get_contents('template/' . $tmplFile);
	$res = $this->get_setting('cbUsePhpBestsellerProdgroup_Checked');
	if($res == 'True')
	{
		$dbh = $this->db_connect();
		$osql = "SELECT i.itemItemNumber " .
				  "FROM " . $this->dbtoken . "bestsellerpg d " .
				  "JOIN " . $this->dbtoken . "itemdata i ON d.bepgItemIdNo = i.itemItemNumber AND i.itemProductGroupIdNo = d.bepgPgIdNo " .
				  "JOIN " . $this->dbtoken . "price p    ON d.bepgItemIdNo = p.prcItemNumber " .
				  "WHERE d.bepgPgIdNo = '" . $_GET['idx'] . "' " .
				  "AND i.itemLanguageId = '" .$this->lngID . "' " .
				  "GROUP BY d.bepgItemIdNo " .
				  "ORDER BY d.bepgRank";
		$oerg = mysqli_query($dbh,$osql);
		if(mysqli_num_rows($oerg) > 0)
		{
			$besthtml = str_replace('{LangTagBestseller}',$this->get_lngtext('LangTagBestsellerPg'),$besthtml);
			$iO = 0;
			while($o = mysqli_fetch_assoc($oerg))
			{
				if($iO == 0)
				{
						$ItemNumbers = '"' . $o['itemItemNumber'] . '"';
				}
				else
				{
					$ItemNumbers .= ',"' . $o['itemItemNumber'] . '"';
				}
				$iO++;
			}
			$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
					 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0' LIMIT 1) AS ItemPrice, " .
					 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
					 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
					 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
					 "itemCheckAge, itemMustAge, itemIsAction " .
					 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber IN (" . $ItemNumbers . ") AND itemLanguageId = '" . $this->lngID . "'";
			$erg = mysqli_query($dbh,$sql);
			if(mysqli_errno($dbh) == 0)
			{
				if(mysqli_num_rows($erg) > 0)
				{
					include('inc/items_boxed.inc.php');
					$html = str_replace('{GSSE_INCL_ITEMSBOXEDLINES}',$this_inner,$outer);
					$besthtml = str_replace('{GSSE_INCL_BESTSELLERPG}',$html,$besthtml);
				}
			}
			else
			{
				$besthtml = mysqli_error($dbh) . ":<br />" . $sql;
			}
			mysqli_free_result($erg);
			$this->content = str_replace($tag, $besthtml, $this->content);
		}
		else
		{
			$this->content = str_replace($tag, '', $this->content);
		}
		mysqli_free_result($oerg);
	}
	else
	{
		$this->content = str_replace($tag, '', $this->content);
	}
}
else
{
	$this->content = str_replace($tag, '', $this->content);
}
