<?php
/* Copyright GS Software AG */
session_start();
	require_once("dynsb/module/newsletter2/class.newsletter2.php");

	$address = htmlentities(strip_tags($_POST["emailaddress"]));

	$tmplFile = "newsletterform.html";
	$form = $this->gs_file_get_contents('template/' . $tmplFile);
	$formTags = $this->get_tags_ret($form);
	$form = $this->parse_texts($formTags,$form);
	$tmplFile = "mailinggroup.html";
	$mailinggroup = $this->gs_file_get_contents('template/' . $tmplFile);
	$mailinggroupTags = $this->get_tags_ret($mailinggroup);
	$mailinggroup = $this->parse_texts($mailinggroupTags,$mailinggroup);
	$tmplFile = "aktivecode.html";
	$aktivecode = $this->gs_file_get_contents('template/' . $tmplFile);
	$aktivecodeTags = $this->get_tags_ret($aktivecode);
	$aktivecode = $this->parse_texts($aktivecodeTags,$aktivecode);
 	
	$showform = true;
	
	$nl2 = new newsletter2();
	$aMG          = $nl2->getMailgroups();
	$bMultiMg     = $nl2->getMultipleMg();
	$bDoubleOptIn = $nl2->getDoubleOptIn();

	if ($bMultiMg == 1)
	{
		$mgInputType = "checkbox";
	}
	else
	{
		$mgInputType = "radio";
	}
	//hide mailgroup chooser, if only one is available
	if (count($aMG) < 2)
	{
		$bHideElement = " style='display:none;' ";
		$mailinggroup = str_replace('{GSSE_STYLE}', $bHideElement, $mailinggroup);
	}
	else
	{
		$mailinggroup = str_replace('{GSSE_STYLE}', '', $mailinggroup);
	}
	
	// Begin Multigroup
	$multiGroupSelect = "";
	foreach ($aMG as $key => $value) 
	{
		if ($bHideElement)
		{
			$checked = " checked ";
		}
		else
		{
			$checked = "";
		}
		
		$multiGroupSelect .= "<input type='".$mgInputType."' name='nl2mg[]' value='".$key."' ".$checked." /> <b>".$value["name"]."</b>";
		if (!empty($value["desc"]))
		{
			$multiGroupSelect .= "<br /><span style='padding-left:25px;'>".$value["desc"]."</span><br />";
		}
	}
	$mailinggroup = str_replace('{GSSE_SEL_GROUP}', $multiGroupSelect, $mailinggroup);
	// End Multigroup
	
	//if sign in button is pressed
	if (isset($_POST["btnnl2signin"])) 
	{
		$insFormat = $_POST["nl2format"];
		$aInsMG    = $_POST["nl2mg"];

		switch ($nl2->signIn($address, $insFormat, $aInsMG)) 
		{
			case 1:
				//"Adresse wurde erfolgreich eingetragen"
				$noticeText = $this->get_lngtext('LangTagTextSignedIn');
				$bOk = true;
				$showform = false;
				break;
			case -1: 
				//"Fehler im Feld Email-Adresse"
				$noticeText = $this->get_lngtext('LangTagTextEmailAddressIncorrect'); 
				$bOk = false;
				$showform = true;
				break;
			case -2: 
				//"Bitte wählen Sie eine Mailgruppe aus"
				$noticeText = $this->get_lngtext('LangTagTextChooseMailgroup'); 
				$bOk = false;
				$showform = true;
				break;
		}
	}//if signout button is pressed
	elseif (isset($_POST["btnnl2signout"])) 
	{
		if ($nl2->signOut($address) == 1) 
		{
			//"Adresse wurde erfolgreich ausgetragen"
			$bOk = true;
			$noticeText = $this->get_lngtext('LangTagTextSignOut');
			$showform = false;
		}
		else
		{	// Fehler
			$noticeText = $this->get_lngtext('LangTagTextFailed');
			$bOk = false;
			$showform = true;
		}
	}
	//if activate button is pressed
	//param a from activation mail
	elseif (isset($_POST["btnnl2activate"]) || isset($_POST["a"])) 
	{
		$mode = 2;

		//if from activation mail
		if (isset($_POST["a"])) 
		{
			$aParam = explode(",", $_POST["a"]);
			$address        = $aParam[0];
			$activationCode = $aParam[1];
		}
		else 
		{
			$activationCode = $_POST["nl2actcode"];
		}

		if ($nl2->activateAddress($address, $activationCode) == 1) 
		{
			//"Ihre Emailadresse wurde erfolgreich für unseren Newsletter aktiviert!"
			$bOk = true;
			$noticeText = $this->get_lngtext('LangTagTextAddressActivated');
			$showform = false;
		}
		else
		{	// Fehler
			$noticeText = $this->get_lngtext('LangTagTextFailed');
			$bOk = false;
			$showform = true;
		}
	}
	
	
	if($showform == true)
	{	
		// Begin Button "Eintragen"
		if(isset($_POST['btnnl2in']) || isset($_POST['btnnl2signin']))
		{
			$tmplFile = "button.html";
			$button = $this->gs_file_get_contents('template/' . $tmplFile);
			$button = str_replace('{GSSE_NAME_BUTTON}', 'btnnl2signin', $button);
			$button = str_replace('{GSSE_VAL_BUTTON}', $this->get_lngtext('LangTagEnter'), $button);
			$form = str_replace('{GSSE_INCL_MAILINGGROUP}', $mailinggroup, $form);
			$form = str_replace('{GSSE_INCL_AKTIVCODE}', '', $form);
		}
		// End Button "Eintragen"

		// Begin Button "Austragen"
		if(isset($_POST['btnnl2out']) || isset($_POST['btnnl2signout']))
		{
			$tmplFile = "button.html";
			$button = $this->gs_file_get_contents('template/' . $tmplFile);
			$button = str_replace('{GSSE_NAME_BUTTON}', 'btnnl2signout', $button);
			$button = str_replace('{GSSE_VAL_BUTTON}', $this->get_lngtext('LangTagSignOut'), $button);
			$form = str_replace('{GSSE_INCL_MAILINGGROUP}', '', $form);
			$form = str_replace('{GSSE_STYLE}', " style='display:none;' ", $form);
			$form = str_replace('{GSSE_INCL_AKTIVCODE}', '', $form);
		}
		// End Button "Austragen"		
		
		// Begin Button "Aktivieren"
		if(isset($_POST['btnnl2act']) || isset($_POST['btnnl2activate']))
		{
			$tmplFile = "button.html";
			$button = $this->gs_file_get_contents('template/' . $tmplFile);
			$button = str_replace('{GSSE_NAME_BUTTON}', 'btnnl2activate', $button);
			$button = str_replace('{GSSE_VAL_BUTTON}', $this->get_lngtext('LangTagActivate'), $button);
			$form = str_replace('{GSSE_INCL_MAILINGGROUP}', '', $form);
			$form = str_replace('{GSSE_STYLE}', " style='display:none;' ", $form);
			$form = str_replace('{GSSE_INCL_AKTIVCODE}', $aktivecode, $form);
		}
		// End Button "Aktivieren"
		
		// Begin Form anzeigen
		if(isset($_POST['emailaddress']))
		{
			$form = str_replace('{GSSE_MSG_EMAIL}', $_POST['emailaddress'], $form);
		}
		else
		{
			$form = str_replace('{GSSE_MSG_EMAIL}', '', $form);
		}
		$form = str_replace('{GSSE_INCL_BUTTON}', $button, $form);
		$this->content = str_replace ('{GSSE_FUNC_NEWSLETTER}', $noticeText."<br />".$form, $this->content);
		// End Form anzeigen
	}
	else
	{
		$this->content = str_replace ('{GSSE_FUNC_NEWSLETTER}', $noticeText, $this->content);
	}
	
?>