<?php 
@session_start();
if(isset($_GET['cusId']))
{	
	$cusIdNo = $_GET['cusId'];
	$_SESSION['login']['cusIdNo'] = $cusIdNo;
	//$_SESSION['login']['ok'] = true;
}
if(file_exists("dynsb/class/class.shoplog.php")) 
{
	if(!in_array("shoplog", get_declared_classes())) 
	{
		require_once("dynsb/class/class.shoplog.php");
	}

	$sl = new shoplog();
	$sid = session_id();
	
	require_once 'dynsb/module/comments/class.shopcomment.php';
	$oc = new Shopcomment();
	
	//check if logged in
	if($_SESSION['login']['ok'] == true) 
	{
		$cusIdNo = $_SESSION['login']['cusIdNo'];
		
		
		//Kommentar hinzufügen
		if (isset($_POST['commsubmit']) and $_POST['editcomment'] == '') 
		{
			//echo"++++kommentare hinzuf&uuml;gen+++++++++";
			//if fields are empty or no rating
			if(($_POST['commrating'] == '')
			|| (''==(trim($_POST['commsubject'])))
			|| (''==(trim($_POST['commbody'])))) 
			{
				$_SESSION['commrating'] 	= $_POST['commrating'];
				$_SESSION['commsubject'] 	= $_POST['commsubject'];
				$_SESSION['commbody'] 		= $_POST['commbody'];
				$_SESSION['commerr'] 			= 1;
			}

			//if magice quotes is on, stripslashes because mysqli_real_escape_string() will be
			//used later
			if (get_magic_quotes_gpc()) 
			{
				$_POST['commitemnumber'] 	= stripslashes($_POST['commitemnumber']);
				$_POST['commrating'] 			= stripslashes($_POST['commrating']);
				$_POST['commsubject'] 		= stripslashes($_POST['commsubject']);
				$_POST['commbody'] 				= stripslashes($_POST['commbody']);
			}

			//Insert new comment
			$oc->setRating($_POST['commrating']);
			$oc->setSubject($_POST['commsubject']);
			$oc->setBody($_POST['commbody']);
			$oc->setCusId($cusIdNo["cusIdNo"]);
			
			if(!$oc->save()) 
			{
				$_SESSION['commerr'] = 2;
			}
		}
		//++++kommentare löschen+++++++++
		elseif (isset($_POST['commdel'])) 
		{
			//echo"++++Kommentare l&ouml;schen+++++++++ UserId=".$cusIdNo["cusIdNo"];
			foreach((array)$_POST['items'] as $itemNumber) 
			{
				$oc->delete($itemNumber, $cusIdNo["cusIdNo"]);
			}
		}
		//++++kommentare aktualisieren+++++++++
		elseif(isset($_POST['editcomment']) and ($_POST['editcomment'] <> '')) 
		{
			//echo "++++kommentare aktualisieren+++++++++";
			//commitemnumber in diesem Fall die Kommentar ID
			$id = trim($_POST['editcomment']);
			if (empty($id)) 
			{
				header('Location: index.php?page=gs_addshopcomment');
				die();
			}

			//if fields are empty or no rating
			if(($_POST['commrating'] == '')
			|| (''==(trim($_POST['commsubject'])))
			|| (''==(trim($_POST['commbody'])))) 
			{
				$_SESSION['commrating'] 	= $_POST['commrating'];
				$_SESSION['commsubject'] 	= $_POST['commsubject'];
				$_SESSION['commbody'] 		= $_POST['commbody'];
				$_SESSION['commerr'] 			= 1;
				header('Location: index.php?page=gs_addshopcomment');
				die();
			}

			//if magice quotes is on, stripslashes because mysqli_real_escape_string() will be
			//used later
			if (get_magic_quotes_gpc()) 
			{
				$_POST['commrating'] 			= stripslashes($_POST['commrating']);
				$_POST['commsubject'] 		= stripslashes($_POST['commsubject']);
				$_POST['commbody'] 				= stripslashes($_POST['commbody']);
			}

			//Insert new comment
			//$oc = new Shopcomment($id);
			$oc->setRating($_POST['commrating']);
			$oc->setSubject($_POST['commsubject']);
			$oc->setBody($_POST['commbody']);
			//save only when customer Ids match
			if ($cusIdNo["cusIdNo"] == $oc->getCusId()) 
			{
				if(!$oc->save()) 
				{
					$_SESSION['commerr'] = 2;
				}
			}
			// header('Location: index.php?page=gs_addshopcomment');
			// die();
		}
		
		
		// Alle Kommentare anzeigen
		$aMyComments = $this->show_shopcomments($cusIdNo["cusIdNo"]);
		$showcomments = "";
		foreach($aMyComments as $key => $val)
		{
			$tmplFile = "showshopcomments.html";
			$msg = file_get_contents('template/' . $tmplFile);
			$msg = str_replace('{GSSE_SHOW_ID}', $val['itcoIdNo'], $msg);
			$msg = str_replace('{GSSE_SHOW_COMMENTSUBJECT}', base64_decode($val["itcoSubject"]), $msg);
			$msg = str_replace('{GSSE_SHOW_COMMENTDATE}', $val["itcoDate"], $msg);
			$msg = str_replace('{GSSE_SHOW_COMMENTTEXT}', base64_decode($val["itcoBody"]), $msg);
			$msg = str_replace('{GSSE_SHOW_RATING}', $val["itcoRating"], $msg);
			$showcomments = $showcomments . $msg;
		}
		$this->content = str_replace('{GSSE_SHOW_COMMENTS}', $showcomments, $this->content);
		$this->content = str_replace('{GSSE_FUNC_SHOPCOMMENTCONTROL}', '', $this->content);
	}
	else
	{
		if(($_POST['commrating'] !== '--')
			&& ('' !==(trim($_POST['commsubject'])))
			&& ('' !==(trim($_POST['commbody'])))) 
			{
				$oc->setRating($_POST['commrating']);
				$oc->setSubject($_POST['commsubject']);
				$oc->setBody($_POST['commbody']);
				$oc->setCusId($_SESSION['login']['cusIdNo']);
				$oc->save();
			}
		$this->content = str_replace('{GSSE_SHOW_COMMENTS}', '', $this->content);
		$this->content = str_replace('{GSSE_FUNC_SHOPCOMMENTCONTROL}', '', $this->content);
	}
}
else 
{
	die("Error #199: Cannot find dynamic extensions");
}
?>