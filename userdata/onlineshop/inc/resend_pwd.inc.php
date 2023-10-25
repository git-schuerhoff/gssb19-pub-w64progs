<?php
	chdir("../");
	include_once("inc/class.shopengine.php");
	$rpse = new gs_shopengine();
	if(file_exists("dynsb/class/class.shoplog.php")) {
		if(!in_array("shoplog",get_declared_classes())) {
			require_once("dynsb/class/class.shoplog.php");
			require_once("dynsb/class/class.shopmail.php");
		}
		$sl = new shoplog();
		$cd = $sl->getCustomerData2($_GET['user_mail']);

		if($cd) {
			$_POST["cusPassword"] = $cd->cusPassword;
			$_POST["email"] = trim($_GET['user_mail']);

			//aus createcustomer.php:
			$_POST["shopname"] = $rpse->get_setting("edShopName_Text");
			$_POST["userdata"] = $rpse->get_lngtext("LangTagUserdata");
			$_POST["recipient"] = $rpse->get_setting("edOrderEmail_Text");
			$_POST["dear"] = $rpse->get_lngtext("LangTagDear");
			$_POST["logindata_email_text1"] = $rpse->get_lngtext("LangTagResendPasswMailText1");
			$_POST["logindata_email_text2"] = $rpse->get_lngtext("LangTagResendPasswMailText2");
			$_POST["user"] = $rpse->get_lngtext("LangTagTextUser");
			$_POST["password"] = $rpse->get_lngtext("LangTagTextPassword");

			$_POST["cusTitle"] = trim($cd->cusTitle);
			$_POST["cusFirstName"] = trim($cd->cusFirstName);
			$_POST["cusLastName"] = trim($cd->cusLastName);

			$cm = new shopmail($_POST["cusPassword"], true, $_POST, $_SERVER['SCRIPT_FILENAME']);
			$cm->userpass = $_POST["cusPassword"];

			$cm->FormToAddress = "cusTitle";
			$cm->FirstName = "cusFirstName";
			$cm->LastName = "cusLastName";

			//print_r($_POST);
			$cm->createPwMail();
			echo '1';
		} else {
			echo '2';
		}
	} else {
		echo '3';
	}
?>