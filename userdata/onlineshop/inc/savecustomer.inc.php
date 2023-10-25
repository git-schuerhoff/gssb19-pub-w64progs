<?php
	
	if(file_exists("dynsb/class/class.shoplog.php"))
	{
		if(!in_array("shoplog",get_declared_classes()))
		{
			require_once("dynsb/class/class.shoplog.php");
		}
		if(!in_array("shopmail",get_declared_classes()))
		{
			require_once("dynsb/class/class.shopmail.php");
		}
		require_once("inc/postdefinition.inc.php");
		$nc = new shoplog();
		$_POST["cusPassword"] = $nc->getRandomCustomerPassword();
		$_POST["email"] = $_POST["cusEMail"];
		$ret = $nc->setCustomerData($_POST);
		if ($ret < 0)
		{
			echo("Web-Server-Fehler: - ret<0 in setCustomerData!<br />");
			echo "savecustomer<br />";
			echo $ret;
			die();
		}
		$num = $nc->CustomerExists;
		
		if($num==0)
		{
			$_SESSION['cus_string'] = base64_encode($_POST["cusPassword"]);
			$_POST["cusPassword"] = '*****';
			$cm = new shopmail($_POST["cusPassword"], true, $_POST, $_SERVER["PATH_TRANSLATED"]);
			$cm->userpass = $_POST["cusPassword"];
			$cm->FirstName = "cusFirstName";
			$cm->LastName = "cusLastName";
			$cm->FormToAdress = "cusTitle";
			$cm->createPwMail();
		}
		header("Location: index.php?page=savedcustomer");
	}
	else
	{
		echo("Web-Server-Fehler - missing root path file! Here");
		echo "savecustomer";
	}
	/*
	$dbh = $this->db_connect();
		$sql = "INSERT INTO `dsb15_customer` (`cusId`, `cusFirmname`, `cusFirmVATId`, `cusTitle`, `cusFirstName`, `cusLastName`, `cusStreet`, `cusStreet2`, `cusZipCode`, `cusCity`, `cusCountry`, `cusPhone`, `cusFax`, `cusEMail`, `cusEMailFormat`, `cusDeliverFirmname`, `cusDeliverTitle`, `cusDeliverFirstName`, `cusDeliverLastName`, `cusDeliverStreet`, `cusDeliverStreet2`, `cusDeliverZipCode`, `cusDeliverCity`, `cusDeliverCountry`, `cusChgUserIdNo`, `cusChgApplicId`, `cusChgHistoryFlg`, `cusDiscount`, `cusCustomerNews`, `cusBonusPoints`, `cusBlocked`, `cusBlockedMessage`, `cusBirthdate`, `cusMobil`, `cusPassword`) VALUES (NULL, '".$_POST["cusFirmname"]."', '".$_POST["cusFirmVATId"]."', '".$_POST["cusTitle"]."', '".$_POST["cusFirstName"]."', '".$_POST["cusLastName"]."', '".$_POST["cusStreet"]."', '".$_POST["cusStreet"]."', '".$_POST["cusZipCode"]."', '".$_POST["cusCity"]."', '".$_POST["cusCountry"]."', '".$_POST["cusPhone"]."', '".$_POST["cusFax"]."', '".$_POST["cusEMail"]."', '".$_POST["cusEMailFormat"]."', '".$_POST["cusDeliverFirmname"]."', '".$_POST["cusDeliverTitle"]."', '".$_POST["cusDeliverFirstName"]."', '".$_POST["cusDeliverLastName"]."', '".$_POST["cusDeliverStreet"]."', '".$_POST["cusDeliverStreet2"]."', '".$_POST["cusDeliverCity"]."', '".$_POST["cusDeliverCountry"]."', 0, '', '', 0, 0, NULL, 0, '', '', '".$_POST["cusBirthdate"]."', '".$_POST["cusMobil"]."', '".$_POST["cusPassword"]."')";
		$oerg = mysqli_query($dbh,$sql);
	*/
	$tmplFile = "savecustomer.html";
	$this->content = str_replace($tag, $tmplFile, $this->content);
?>