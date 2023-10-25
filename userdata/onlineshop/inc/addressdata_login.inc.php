<?php
	if(file_exists("dynsb/class/class.shoplog.php")) {
	if(!in_array("shoplog",get_declared_classes()))
	{
		require_once("dynsb/class/class.shoplog.php");
	}
	
	$sl = new shoplog();
	
	// Beginn Kundendaten speichern
	if(isset($_POST['send_save']))  
	{
		$a = $_POST;
		if($a['_LANGTAGFNFIELDCOMPANYORPRIVATE_']=='privat') $a['cusFirmname']='';
		$this->content = str_replace('{GSSE_INCL_CLASSCOMPANY}','displaynone',$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSCUSNR}','displaynone',$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSVATID}','displaynone',$this->content );
		unset($a['_LANGTAGFNFIELDCOMPANYORPRIVATE_']);
		$res =  $sl->setCustomerData($a);
		if($res>0)
		{
			$tmplFile = "okbox.html";
			$msg = $this->gs_file_get_contents('template/' . $tmplFile);
			$msg = str_replace('{GSSE_LANG_LangTagMsgChangePasswordSuccess}', $this->get_lngtext('LangTagMsgChangedData'), $msg);
			$this->content = str_replace('{GSSE_MSG_BOX}', $msg, $this->content);
			//$msg = '<div id="fd_msg" class="ok_box" style="display:inherit;">Die Daten wurden geändert</div>';
		}
		else
		{
			$tmplFile = "errorbox.html";
			$msg = $this->gs_file_get_contents('template/' . $tmplFile);
			$msg = str_replace('{GSSE_MSG_ERROR}', $this->get_lngtext('LangTagErrorInsertCustomer'), $msg);
			$this->content = str_replace('{GSSE_MSG_BOX}', $msg, $this->content);
		}
	}
	// End Kundendaten speichern
	
	// Beginn Kundendaten aus DB abfragen
	$cd = $sl->getCustomerData($_SESSION ['login']['cusIdNo']);	
	// End Kundendaten aus DB abfragen

	// Beginn selected Privat/Firma
	if($cd->cusFirmname == '')
	{
		$this->content = str_replace('{GSSE_INCL_CLASSCOMPANY}','displaynone',$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSCUSNR}','displaynone',$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSVATID}','displaynone',$this->content );
		/*A TS 31.07.2015: Privat oder Firma wählen*/
		$this->content = str_replace('{GSSE_SEL|LangTagFNFieldCompanyOrPrivate|{GSSE_LANG_LangTagBuy2InputLabelPrivat}}','selected',$this->content );
		$this->content = str_replace('{GSSE_SEL|LangTagFNFieldCompanyOrPrivate|{GSSE_LANG_LangTagBuy2InputLabelBusiness}}','',$this->content );
		$this->content = str_replace('{GSSE_INCL_COMPORPRIVIDX}','0',$this->content );
		/*E TS 31.07.2015: Privat oder Firma wählen*/
	}
	else
	{
		$this->content = str_replace('{GSSE_INCL_CLASSCOMPANY}',$cd->cusFirmname,$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSCUSNR}',$cd->cusId,$this->content );
		$this->content = str_replace('{GSSE_INCL_CLASSVATID}',$cd->cusFirmVATId,$this->content );
		/*A TS 31.07.2015: Privat oder Firma wählen*/
		$this->content = str_replace('{GSSE_SEL|LangTagFNFieldCompanyOrPrivate|{GSSE_LANG_LangTagBuy2InputLabelPrivat}}','',$this->content );
		$this->content = str_replace('{GSSE_SEL|LangTagFNFieldCompanyOrPrivate|{GSSE_LANG_LangTagBuy2InputLabelBusiness}}','selected',$this->content );
		$this->content = str_replace('{GSSE_INCL_COMPORPRIVIDX}','1',$this->content );
		/*E TS 31.07.2015: Privat oder Firma wählen*/
	}
	// End selected Privat/Firma
	
	// Beginn selected Herr/Frau
	if($cd->cusTitle == $this->get_lngtext('LangTagMr'))
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMr}}','selected',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMrs}}','',$this->content );
	}
	elseif($cd->cusTitle == $this->get_lngtext('LangTagMrs'))
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMrs}}','selected',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMr}}','',$this->content );
	}
	else
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMrs}}','',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusTitle|{GSSE_LANG_LangTagMr}}','',$this->content );	
	}
	// End selected Herr/Frau

	// Beginn selected Herr/Frau cusDeliverTitle
	if($cd->cusDeliverTitle == $this->get_lngtext('LangTagMr'))
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMr}}','selected',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMrs}}','',$this->content );
	}
	elseif($cd->cusDeliverTitle == $this->get_lngtext('LangTagMrs'))
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMrs}}','selected',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMr}}','',$this->content );
	}
	else
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMrs}}','',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusDeliverTitle|{GSSE_LANG_LangTagMr}}','',$this->content );	
	}
	// End selected Herr/Frau cusDeliverTitle
	
	// Beginn selected Gewünschtes Format der Bestätigungsemail
	if($cd->cusEMailFormat == 'text')
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusEMailFormat|text}','selected',$this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusEMailFormat|html}','',$this->content );
	}
	else
	{
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusEMailFormat|text}', '', $this->content );
		$this->content = str_replace('{GSSE_COOKIE_SEL|cusEMailFormat|html}','selected',$this->content );
	}
	// End selected Gewünschtes Format der Bestätigungsemail
	
	// Begin Geburtsdatum Format
	$tag = substr($cd->cusBirthdate, 8, 2);
	$monat = substr($cd->cusBirthdate, 5, 2);
	$jahr = substr($cd->cusBirthdate, 0, 4);
	$this->content = str_replace('{GSSE_VAL_cusBirthdate}',$tag.'.'.$monat.'.'.$jahr,$this->content );
	// End Geburtsdatum Format
	
	// Beginn restliche Felder 
	foreach($cd as $key => $val)
	{
		$this->content = str_replace ('{GSSE_VAL_'.$key.'}', $val, $this->content);
	}
	$this->content = str_replace ('{GSSE_FUNC_ADDRESSDATA_LOGIN}', '<br />', $this->content);
	$this->content = str_replace ('{GSSE_MSG_BOX}', '', $this->content);
	$this->content = str_replace ('{GSSE_INCL_CID}', $_SESSION ['login']['cusIdNo'], $this->content);
	// End restliche Felder
	
	// Beginn Konto und Bank Daten
	foreach($cd as $key => $val) if($key=="cusData") 
	{
		$this->content = str_replace ('{GSSE_VAL_cusBank}', gsshow1("cusBank", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusBLZ}', gsshow1("cusBLZ", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusAccountNo}', gsshow1("cusAccountNo", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusAccountOwner}', gsshow1("cusAccountOwner", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditCard}', gsshow1("cusCreditCard", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditValidMonth}', gsshow1("cusCreditValidMonth", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditValidYear}', gsshow1("cusCreditValidYear", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditNo}', gsshow1("cusCreditNo", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditChk1}', gsshow1("cusCreditChk1", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditChk2}', gsshow1("cusCreditChk2", $val), $this->content);
		$this->content = str_replace ('{GSSE_VAL_cusCreditOwner}', gsshow1("cusCreditOwner", $val), $this->content);
	}
	// End Konto und Bank Daten
	
	/*PhpLogVisitPage*/
	if(file_exists("dynsb/class/class.pagestatistics.php"))
	{
		require_once("dynsb/class/class.pagestatistics.php");
		$insert = new pagestatistics();
		$insert->querySetUserclicks( session_id() );
	}
}
?>