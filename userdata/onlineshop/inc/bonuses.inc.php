<?php
	session_start();
	
	if($_SESSION['login']['ok'] == true)
	{
		/*PhpLogVisitPage*/
		if(file_exists("dynsb/class/class.pagestatistics.php"))
		{
			require_once("dynsb/class/class.pagestatistics.php");
			$insert = new pagestatistics();
			$insert->querySetUserclicks( session_id() );
		}
		
		/*phpLogPageHit*/
		if(file_exists("dynsb/class/class.pagestatistics.php"))
		{
			require_once("dynsb/class/class.pagestatistics.php");
			$insert = new pageStatistics();
			$insert->querySetPageVisits($_SERVER["SCRIPT_NAME"]);
		} 
		else 
		{
			echo "Web-Server-Fehler - Statistics!"; 
		}
		
		/*phpSessionLog*/
		if(file_exists("dynsb/class/class.pagestatistics.php"))
		{
			require_once("dynsb/class/class.pagestatistics.php");
			$insert = new pagestatistics();
			$insert->querySetUserDetails(session_id(), null,null,"./dynsb/class/php_browscap.ini");
		} 
		else 
		{
			echo "Web-Server-Fehler - Statistics!"; 
		}
		
		// Begin Email senden
		if(isset($_POST['action']))
		{
			if( isset($_POST['items']))
			{
				$notsend = false;
				require_once("dynsb/class/class.shoplog.php");
				$sl = new shoplog();
				$cid = $_POST['cid'];
				$lang = $_POST['lang'];
				$cust = $sl->getCustData($cid);
				$mailtype = $cust->cusEMailFormat;
				if($cust->cusTitle == $this->get_lngtext('LangTagMr')) 
				{
					$anrede = $this->get_lngtext('LangTagMr').' '.$cust->cusTitle;
				} 
				else 
				{
					$anrede = $this->get_lngtext('LangTagMrs').' '.$cust->cusTitle;
				}

				$sender = $_POST['shopname'];
				$ordermail = $_POST['ordermail'];

				$subject = $_POST['subject'];

				$bp = 0;
				$acount = sizeof($_POST['items']);
				if($acount!=0)
				{
					for($i=0; $i < $acount;$i++)
					{  
						$item = $sl->getItem($_POST['items'][$i], $lang);
						if($item)
						{
							$bp += $item->itemBonusPointsPrice;
							if($bp > $cust->cusBonusPoints) 
							{
								// Für diese Artikel sind nicht genug Bonuspunkte vorhanden!
								$tmplFile = "errorbox.html";
								$msg = $this->gs_file_get_contents('template/' . $tmplFile);
								$msg = str_replace ('{GSSE_MSG_ERROR}', $this->get_lngtext('LangTagTextBonusNotEnough'), $msg);
								$this->content = str_replace ('{GSSE_MSG_}', $msg, $this->content);
								$notsend = false;
							}
							else
							{
								// Artikel wurde(n) erfolgreich angefordert
								$tmplFile = "okbox.html";
								$msg = $this->gs_file_get_contents('template/' . $tmplFile);
								$msg = str_replace ('{GSSE_LANG_LangTagMsgChangePasswordSuccess}', $acount.' '.$this->get_lngtext('LangTagTextBonusArtikel'), $msg);
								$this->content = str_replace ('{GSSE_MSG_}', $msg, $this->content);
								$notsend = true;
							}		
							$items .= "<b> ".$this->get_lngtext('LangTagItemNumberLong').":</b> ".$item->itemItemNumber." ".$item->itemItemDescription."<br />";
						}
					}
					// anbei die Bestätigung der angefordeten Bonusartikel + Folgende Artikel haben Sie angefordert
					$message =  $anrede." ".$cust->cusLastName.",<br /><br /> ".$this->get_lngtext('LangTagTextBonusArtikelConfirm');
					$message .= $items;
					// Sie werden die angeforderten Artikel in Kürze erhalten. + Mit freundlichen Grüßen
					$message .= "<br />".$this->get_lngtext('LangTagTextBonusArtikelReceive')."<br /><br />".$this->get_lngtext('LangTagGreetingsInquiry')."<br />$sender";
					
					// Sehr geehrter Shopbetreiber, <br /><br /> der Kunde mit der Kundennummer
					$message_shop = $this->get_lngtext('LangTagTextBonusShop')." ".$cust->cusId." <br /> ".$this->get_lngtext('LangTagTextBonusShopArtikel')."<br /><br />";
					$message_shop .= $items."<br />";
					// Die Kundendaten
					$message_shop .= "<b>".$this->get_lngtext('LangTagAdressData').":</b> <br />";
					if($cust->cusFirmname) $message_shop .= $cust->cusFirmname." <br />";
					$message_shop .= $cust->cusTitle.' '.$cust->cusFirstName.' '.$cust->cusLastName." <br />";
					$message_shop .= $cust->cusStreet.' '.$cust->cusStreet2." <br />";
					$message_shop .= $cust->cusZipCode.' '.$cust->cusCity." <br />";
					$message_shop .= $cust->cusCountry." <br />";
					$message_shop .= "Tel: ".$cust->cusPhone." <br />";
					$message_shop .= "E-Mail: ".$cust->cusEMail." <br /><br />";
					$message_shop .= $this->get_lngtext('LangTagGreetingsInquiry');

					$header = "MIME-Version: 1.0 \n";
					$header .="From: ".$sender." <noreply@".$_SERVER['SERVER_NAME'].">\n";
					$header .= "X-Mailer: PHP\n";
					$header .= "X-Sender-IP: ".$_SERVER['REMOTE_ADDR']."\n";
					$header .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
					$header .= "Content-type: text/html; charset=\"UTF-8\"\n";
					
					if($notsend === true)
					{
						@mail($ordermail, $subject, $message_shop, $header);
						@mail($cust->cusEMail, $subject, $message, $header);
						$sl->updateCustomerBonusPoints($cid, ($cust->cusBonusPoints - $bp));
					}
					
					
				}
			}
			else
			{	
				// Sie haben noch keine Artikel ausgewählt.
				$tmplFile = "errorbox.html";
				$msg = $this->gs_file_get_contents('template/' . $tmplFile);
				$msg = str_replace ('{GSSE_MSG_ERROR}', $this->get_lngtext('LangTagTextBonusEmpty'), $msg);
				$this->content = str_replace ('{GSSE_MSG_}', $msg, $this->content);
			}
		}
		else
		{
			$this->content = str_replace ('{GSSE_MSG_}', '', $this->content);
		}
		// End Email senden
		
		$sid = session_id();
		if(file_exists("dynsb/class/class.shoplog.php"))
		{
			if(!in_array("shoplog",get_declared_classes()))
			{
				require_once("dynsb/class/class.shoplog.php");
			}
			$sl = new shoplog();
			$cust = $sl->getCustData($_SESSION['login']['cusIdNo']['cusIdNo']);
			$b_data = $sl->getBonusItems();
			$ok = $sl->isLoggedIn($sid);
			
			// Begin Kundendaten Felder
			$this->content = str_replace ('{GSSE_VAL_CID}', $cust->cusIdNo, $this->content);
			$this->content = str_replace ('{GSSE_VAL_cusLastName}', $cust->cusLastName, $this->content);
			$this->content = str_replace ('{GSSE_VAL_cusFirstName}', $cust->cusFirstName, $this->content);
			$this->content = str_replace ('{GSSE_VAL_cusZipCode}', $cust->cusZipCode, $this->content);
			$this->content = str_replace ('{GSSE_VAL_cusCity}', $cust->cusCity, $this->content);
			$this->content = str_replace ('{GSSE_VAL_cusBonusPoints}', $cust->cusBonusPoints, $this->content);
			$this->content = str_replace ('{GSSE_VAL_LANG}', $this->lngID, $this->content);
			// End  Kundendaten Felder
			
			// Begin Bonus Artikel
			$num = @mysqli_num_rows($b_data);
			if($num==0)
			{
				// Derzeit sind keine Bonusartikel vorhanden
				$this->content = str_replace ('{GSSE_VAL_ATT}', 'disabled', $this->content);
				$this->content = str_replace ('{GSSE_FUNC_BONUSES}', $this->get_lngtext('LangTagNoBonusArticles'), $this->content);
			}
			else
			{
				
				$npcount = 1;
				$msg ="";
				while($npobj = mysqli_fetch_object($b_data))
				{
					$item = $sl->getItem($npobj->itemItemNumber, "deu");
					if(!empty($item))
					{ 
						$tmplFile = "bonusitems.html";
						$bonusitems = $this->gs_file_get_contents('template/' . $tmplFile);
						$bonusitems = str_replace('{GSSE_LANG_LangTagItemNumber}', $this->get_lngtext('LangTagItemNumber'), $bonusitems);
						$bonusitems = str_replace('{GSSE_LANG_LangTagBonusPoints}', $this->get_lngtext('LangTagBonusPoints'), $bonusitems);
						$bonusitems = str_replace ('{GSSE_VAL_itemItemNumber}', $npobj->itemItemNumber, $bonusitems);
						$bonusitems = str_replace ('{GSSE_VAL_npcount}', $npcount, $bonusitems);
						
						if($item->itemSmallImageFile == "") 
						{
							$bonusitems = str_replace ('{GSSE_VAL_itemSmallImageFile}', "<img src='images/blind.gif' alt='small' />", $bonusitems);
						}
						else 
						{
							$bonusitems = str_replace ('{GSSE_VAL_itemSmallImageFile}', "<img src='images/small/".$item->itemSmallImageFile."' border='1' alt='small' />", $bonusitems);
						}
						$bonusitems = str_replace ('{GSSE_VAL_itemItemNumber}', $item->itemItemNumber, $bonusitems);
						$bonusitems = str_replace ('{GSSE_VAL_itemItemDescription}', $item->itemItemDescription, $bonusitems);
						$bonusitems = str_replace ('{GSSE_VAL_itemBonusPointsPrice}', $item->itemBonusPointsPrice, $bonusitems);
					}
					$npcount++;
					$msg .= $bonusitems;
				}
				$this->content = str_replace ('{GSSE_FUNC_BONUSES}', $msg, $this->content);
			}
			// End Bonus Artikel
		}
		else 
		{
			echo("Error Wish List");
		}
	}
	else
	{
		header ('Location: index.php?page=main');
	}
?>