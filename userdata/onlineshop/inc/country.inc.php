<?php
	$country = "";
	$aState = array();
	$option = $this->gs_file_get_contents('template/option.html');
	$dbh = $this->db_connect();
	$sql = "SELECT SettingName FROM " . $this->dbtoken . "setting WHERE SettingName LIKE 'clbCountry%' AND LanguageId = '" . $this->lngID . "' AND CountryId = '" . $this->cntID . "' AND SettingValue = 'True'";
	$erg = mysqli_query($dbh,$sql);
	
	if(mysqli_errno($dbh) == 0)
	{
		while ($z = mysqli_fetch_assoc($erg)) {
		
			$landCode = substr($z["SettingName"],10,2);
			$sql = "SELECT cntLangTag FROM " . $this->dbtoken . "countries WHERE cntCountryCode = '".$landCode."'";
			$ergLand = mysqli_query($dbh,$sql);
			$land = mysqli_fetch_assoc($ergLand);
			$strLang = $this->get_lngtext($land["cntLangTag"]);
			/*array_push($aState,array("oval" => $landCode, "otext" => $strLang));*/
			$aState[] = array("oval" => $landCode, "otext" => $strLang);
		}
		if(count($aState) > 1)
		{
			foreach ($aState as $key => $row) {
					$CNM[$key] = $row['otext'];
			}
			array_multisort($CNM, SORT_ASC, $aState);
			$smax = count($aState);
			for($c = 0; $c < $smax; $c++)
			{
				$cur_opt = $option;
				$cur_opt = str_replace('{GSSE_OPT_VALUE}',$aState[$c]['oval'],$cur_opt);
				$cur_opt = str_replace('{GSSE_OPT_SELECTED}','',$cur_opt);
				$cur_opt = str_replace('{GSSE_OPT_TEXT}',$aState[$c]['otext'],$cur_opt);
				$country .= $cur_opt;
			}
		}
		
	}

	$this->content = str_replace($tag, $country, $this->content);
?>