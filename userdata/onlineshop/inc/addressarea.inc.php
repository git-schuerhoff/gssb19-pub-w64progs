<?php
	$optionhtml = $this->gs_file_get_contents('template/option.html');
    $sel = '';
    $addritems = '';
    $aAreas = array();
    $cAreas = '';
    $buydbh = $this->db_connect();
    //$adsql = "SELECT AreaId, Text FROM " . $this->dbtoken . "addressarea WHERE Text != '' AND CountryId = '" . $this->cntID . "' AND LanguageId = '" . $this->lngID . "' ORDER BY AreaId ASC";
	
	$adsql = "select a.countryid as state, c.cntLangTag as name from " . $this->dbtoken . "countriesareas a left join " . $this->dbtoken . "countries c on c.cntCountryCode = a.countryid where a.addressareaid in (SELECT AreaId FROM " . $this->dbtoken . "addressarea WHERE Text != '' AND CountryId = 'deu' AND LanguageId = 'deu' ORDER BY a.countryid ASC) order by a.addressareaid ASC";
	
    $aderg = mysqli_query($buydbh,$adsql);
    if(mysqli_errno($buydbh) == 0)
    {
        if(mysqli_num_rows($aderg) > 0)
        {
            while($ad = mysqli_fetch_assoc($aderg))
            {
                $cur_opt = $optionhtml;
                //$cur_opt = str_replace('',,$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_VALUE}',$ad['state'],$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_SELECTED}',$sel,$cur_opt);
                $cur_opt = str_replace('{GSSE_OPT_TEXT}',$this->get_lngtext($ad['name']),$cur_opt);
                $addritems .= $cur_opt;
                /*array_push($aAreas,$ad['AreaId']);*/
                $aAreas[] = $ad['state'];
            }
        }
        mysqli_free_result($aderg);
    }
    else
    {
        die(mysqli_error($buydbh) . "<br />" . $adsql);
    }

	$this->content = str_replace($tag, $addritems, $this->content);
?>