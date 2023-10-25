<?php 
$additionalfield= $this->gs_file_get_contents('template/additionalfield.html');
/*Begin additional fields*/
$addfields = '';
for($a = 1; $a <= 5; $a++)
{
	if($this->get_setting('cb_activ' . $a . '_Checked') == 'True'){
		$curitem = '';
		$curitem = $additionalfield;
		if($this->get_setting('cb_mandatoryfield' . $a . '_Checked') == 'True'){
			$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
			$requiredclass = ' required-entry';
		} else {
			$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
			$requiredclass = '';
		}
		
		$fieldtitle = $this->get_setting('ed_name' . $a . '_Text');
		$field = $this->formfriendly($fieldtitle);			
		$fname = $fieldtitle;
		$curitem = str_replace('{GSSE_INCL_FIELDNAME}', $fname, $curitem);
		$curitem = str_replace('{GSSE_INCL_DIVID}', 'ed_name' . $a . '_Text_div', $curitem);
		$curitem = str_replace('{GSSE_INCL_INPUTID}', $fname, $curitem);
		$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
		$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
		$addfields = $addfields . $curitem;
	}
}

// Birthdayfield
if($this->get_setting('cb_birthField_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldBirthdate_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}', $this->get_lngtext('LangTag__FieldBirthday'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusBirthday_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusBirthday', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

// Phonefield
if($this->get_setting('cb_Phone_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldPhone_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}',  $this->get_lngtext('LangTagFNFieldPhone'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusPhone_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusPhone', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

// Faxfield
if($this->get_setting('cb_fax_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldFax_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}',  $this->get_lngtext('LangTagFNFieldFax'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusFax_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusFax', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

// Mobilefield
if($this->get_setting('cb_mobil_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldMobil_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}',  $this->get_lngtext('LangTagFNFieldMobil'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusMobil_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusMobil', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

// MarketingKeyfield
if($this->get_setting('cb_marketingKey_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldMarketingKey_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}',  $this->get_lngtext('LangTagFieldAktKey'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusAktKey_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusAktKey', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

// NextMessagefield
if($this->get_setting('cb_nextMessage_Checked') == 'True'){
	$curitem = '';
	$curitem = $additionalfield;
	if($this->get_setting('cb_mandatoryfieldNextMessage_Checked') == 'True'){
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','*', $curitem);
		$requiredclass = ' required-entry';
	} else {
		$curitem= str_replace('{GSSE_INCL_REQUIRED}','', $curitem);
		$requiredclass = '';
	}
	
	$curitem = str_replace('{GSSE_INCL_FIELDNAME}',  $this->get_lngtext('LangTagFieldTellMessage'), $curitem);
	$curitem = str_replace('{GSSE_INCL_DIVID}', 'cusNextMessage_div', $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTID}', 'cusNextMessage', $curitem);
	$curitem = str_replace('{GSSE_INCL_CLASS}', 'input-text' . $requiredclass, $curitem);
	$curitem = str_replace('{GSSE_INCL_INPUTTYPE}', 'text', $curitem);
	$addfields = $addfields . $curitem;
}

$this->content = str_replace('{GSSE_FUNC_ADDITIONALFIELDS}', $addfields, $this->content);
/*End additional fields {GSSE_FUNC_ADDITIONALFIELDS} */
?>