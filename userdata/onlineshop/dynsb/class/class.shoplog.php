<?php
/*

	GS-ShopLog v1.1 - class.shoplog.php
	Author: Raimund Kulikowski / GS Software Solutions GmbH
			Sabine Salzsiedler / GS Software Solutions GmbH

	(c) 2007 GS Software AG

	this code is NOT open-source or freeware
	you are not allowed to use, copy or redistribute it in any form

*/


if(file_exists("dynsb/class/class.db.php"))
{
	require_once("dynsb/class/class.db.php");
	require_once("dynsb/include/secure.functions.inc.php");
}
else
{
	if(file_exists("class/class.db.php"))
	{
	require_once("class/class.db.php");
	require_once("include/secure.functions.inc.php");
	}
}

class shoplog
{
	var $actid = 0;
	var $strlog = "";
	var $slc = "";
	var $ordCurrency = "EUR";
	var $cusIdNo = 0;
	var $bNewCus = true;
	var $CustomerExists = 0;
	var $UseBonusPoints = false;
	var $PerGoodsValue = 0;
	var $BonusPointsPerGoodsValue = 0;
	//A TS 06.01.2015
	var $cusPass;
	var $cusUser;
	
	function __construct($id = 9999, $itemNumber='' ,$obj = '', $slc = '')
	{
		$this->actid = intval($id);
		$this->strlog = $obj;
		$this->slc = trim($slc);
		switch($id)
		{
			case 1:
				$this->logShoppage();
			break;
			case 2:
				$this->logDetailpage($itemNumber);
			break;
			case 3:
				$this->logSearchword();
			break;
			default:
				// do nothing
			break;
		}
	}

	function dbconnect()
	{
		$dbVars = new dbVars();
		$con = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$con->query("SET NAMES 'utf8'");
		if($con)
		{
			return $con;
		}
		return false;
	}

	function dbclose($con)
	{
		@mysqli_close($con);
	}

	function getDefaultSQLHeader()
	{
		
		$SQL = "INSERT INTO ".DBToken."monitorlog ";
		$SQL .= "( monActionIdNo, monItemNumber, monValue, monSLC, monChgUserIdNo, monChgApplicId, monChgHistoryFlg )";
		$SQL .= " VALUES ";
		return $SQL;
	}

	function getHistoryInsertSQLHeader()
	{
		
		$SQL = "INSERT INTO ".DBToken."history ";
		$SQL .= "( hisSessionId, hisData1 )";
		$SQL .= " VALUES ";
		return $SQL;
	}

	function getDefaultSQL($itemNumber)
	{
		$SQL = $this->getDefaultSQLHeader();
		$SQL .= "( ".$this->actid.", '".$itemNumber."', '".$this->strlog."', '".$this->slc."', 1, 'class.shoplog.php', 1)";
		return $SQL;
	}

	function logShoppage()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = $this->getDefaultSQL("");
			$qry = mysqli_query($con,$SQL);
			$this->dbclose($con);
		}
	}

	function logDetailpage($itemNumber)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = $this->getDefaultSQL($itemNumber);
			$qry = @mysqli_query($con,$SQL);
			$this->dbclose($con);
		}
	}

	function logSearchword()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$aSearch = explode(" ", $this->strlog);
			foreach($aSearch as $word)
			{
				$SQL = $this->getDefaultSQLHeader();
				$SQL .= "( ".$this->actid.", '','".$word."', '".$this->slc."', 1, 'class.shoplog.php', 1)";
				$qry = @mysqli_query($con,$SQL);
			}
			$this->dbclose($con);
		}
	}

	function logItemHistory($sid, $itemId, $itemShortDesc, $itemPageLink, $itemSmallPicLink, $itemPrice, $itemWeight, $itemIsCatalogFlg)
	{
		
		$con = $this->dbconnect();
		$logcount = 3;
		if($con)
		{
			// start: clean up history-table from sessiondata older than 30 minutes
			$SQLlim = "SELECT DATE_SUB(current_timestamp() , INTERVAL 30 MINUTE) + 0 AS timelimit";
			$qrylim = @mysqli_query($con,$SQLlim);
			$objlim = @mysqli_fetch_object($qrylim);
			$timelimit = $objlim->timelimit;
			$SQLdel = "DELETE FROM ".DBToken."history WHERE hisChgTimestamp < '".$timelimit."'";
			$qrydel = @mysqli_query($con,$SQLdel);
			// end: of clean up history table
			$SQLchk = "SELECT * FROM ".DBToken."history WHERE hisSessionId = '".$sid."'";
			$qrychk = @mysqli_query($con,$SQLchk);
			$numchk = @mysqli_num_rows($qrychk);
			$aNew = array($itemId, $itemShortDesc, $itemPageLink, $itemSmallPicLink, $itemPrice, $itemWeight, $itemIsCatalogFlg);
			if($numchk == 0)
			{
				$aData = array( 0 => $aNew );
				$SQL = $this->getHistoryInsertSQLHeader();
				$SQL .= "( '".$sid."', '".serialize($aData)."')";
				$qry = @mysqli_query($con,$SQL);
			}
			else
			{
				$hit = 0;
				$objchk = @mysqli_fetch_object($qrychk);
				$aData = unserialize($objchk->hisData1);
				for($x = 0; $x < count($aData); $x++)
				{
					if($aNew[0] == $aData[$x][0]) $hit = 1;
				}

				if($hit == 0)
				{
					if(count($aData) < $logcount)
					{
						array_push($aData, $aNew);
					}
					else
					{
						array_push($aData, $aNew);
						array_shift($aData);
					}
				}
				$SQL = "UPDATE ".DBToken."history SET hisData1 = '".serialize($aData)."' WHERE hisSessionId = '".$sid."'";
				$qry = @mysqli_query($con,$SQL);
			}
			$this->dbclose($con);
		}
	}

	function logShoporder($ap, $postdef, $mailFlg, $orderURL, $slc = '', &$pass)
	{
		$this->slc = trim($slc);
		$aData = array();
		$aPosData = array();
		$aCusData = array();
		$ordCountry = $postdef['ordCountry'];
		$ordCurrency = $postdef['ordCurrency'];
		$this->ordCurrency = $ordCurrency;
		$ordpItemId = $postdef['ordpItemId'];
		$ordpItemDesc = $postdef['ordpItemDesc'];
		$ordpQty = $postdef['ordpQty'];
		$ordpPrice = $postdef['ordpPrice'];
		$ordpPriceTotal = $postdef['ordpPriceTotal'];
		$ordpVATPrct = $postdef['ordpVATPrct'];
		$ordpVATValue = $postdef['ordpVATValue'];	
		$postdef = array_flip($postdef);
		//echo "postdef in logShopOrder:<pre>";
		//print_r($postdef);
		//die("</pre>");
        $this->checkCustomerByEmail($ap['email']);
        $aData['cusId'] = $this->cusId;
        //die(var_dump($ap));
		foreach($ap as $key => $value)
		{
			if(substr($value, 0, 3) == $ordCurrency) $value = trim(str_replace($ordCurrency, "", $value));
			//A TS 27.08.2014: Bugsafe
			if(isset($postdef[$key]))
			{
				if($postdef[$key] != "")
				{
					if(substr($postdef[$key], 0, 3) == "cus")
					{
						$aCusData[$postdef[$key]] = $value;
					}
					else
					{
						$aData[$postdef[$key]] = $value;
					}
				}
			}
		}
		for($x = 1; $x <= intval($ap['qtyofpos']); $x++)
		{
			$aPosData[$x]['ordpItemId'] = $ap[$ordpItemId.$x];
			$aPosData[$x]['ordpItemDesc'] = $ap[$ordpItemDesc.$x];
			$aPosData[$x]['ordpQty'] = $ap[$ordpQty.$x];
			$aPosData[$x]['ordpPrice'] = trim(str_replace($ordCurrency, "", $ap[$ordpPrice.$x]));
			$aPosData[$x]['ordpPriceTotal'] = trim(str_replace($ordCurrency, "", $ap[$ordpPriceTotal.$x]));
			$aPosData[$x]['ordpVATPrct'] = $ap[$ordpVATPrct.$x];
			$aPosData[$x]['ordpVATValue'] = $ap[$ordpVATValue.$x];
		}
		$this->writeCustomerData($aData, $aCusData, trim($ap['_LANGTAGFNFIELDEMAILFORMAT_']),$ap['email'], $pass);
		$this->writeOrderData($aData, $aPosData, $ordIdNo);
		$this->writeDownloadData($ordIdNo,$ap);
		if($mailFlg == 0) $this->useOtherMailScript($ap, $orderURL);
	}



	function writeOrderData($aOrd, $aPos, &$ordIdNo)
	{
		$ordIdNo = 0;
		$con = $this->dbconnect();
        $ordCustomerId = '';
		if($con)
		{
			$sOrdKeys = "";
			$sOrdValues = "";
			foreach($aOrd as $key => $value)
			{
				 	if($key !== 'ordAktKey') {
                    if($key == 'cusId'){ 
                        $ordCustomerId = $this->cusId;
                        //die(var_dump($this->cusId));
                        continue;
                    }
                    if($key == 'ordCustomerId') $value = $ordCustomerId;         
					$sOrdKeys .= $key.",";
					if($key == "ordTotalValue" OR $key == "ordTotalValueAfterDsc1" 
						OR $key == "ordTotalValueAfterDsc2" OR $key == "ordVAT1Value"
						OR $key == "ordVAT2Value" OR $key == "ordVAT3Value" OR $key == "ordShippingCost"
                        OR $key == "ordPaymentCost")
					{
						$value = str_replace('.','',$value);
						$value = str_replace(',','.',$value);
						$sOrdValues .= "'".$value."',";
					}
					else
					{
						$sOrdValues .= "'".$value."',";
					}
				 	} else {
				 		$aktKey = $value;
				 	}
                    
			}
			//IP-Adresse speichern
			$cusIP = '';
			$sql = "SELECT * FROM ".DBToken."settings";
			$rs = @mysqli_query($con,$sql);
			$obj = @mysqli_fetch_object($rs);
			if($obj->setSaveIP !== '0') {
				$cusIP = $_SERVER['REMOTE_ADDR'];
			}
			//--------------------
			$sOrdKeys .= "ordCountry,";
			$sOrdValues .= "'".$aOrd['ordDeliverCountry']."',";
			$SQLord = "INSERT INTO ".DBToken."order (ordDate,".$this->removeLastChr($sOrdKeys).",ordSLC,ordChgHistoryFlg, ordCusIdNo, ordIP ) VALUES (NOW()+0,".$this->removeLastChr($sOrdValues).",'".$this->slc."','1','".$this->cusIdNo."', '".$cusIP."')";
            //die(var_dump($SQLord));
			$qryord = @mysqli_query($con,$SQLord);
			$ordIdNo = @mysqli_insert_id($con);
			/* insert the order aktivity */
			$SQLakt = "INSERT INTO ".DBToken."aktivities(custId, mkKey, aktText, aktDate, ordIdNo, aktKey)
						VALUES('".$this->cusIdNo."', 'ord001', 'Bestellung', NOW()+0, '".$ordIdNo."', '".$aktKey."');";
			$qryakt = @mysqli_query($con,$SQLakt);			
			/* insert the order aktivity */
			for($x = 1; $x <= count($aPos); $x++)
			{
				$sPosKeys = "";
				$sPosValues = "";
				foreach($aPos[$x] as $key => $value)
				{
					$sPosKeys .= $key.",";
					if ($key == "ordpPriceTotal" OR $key == "ordpPrice" OR $key == "ordpVATValue")
					{
						$value = str_replace('.','',$value);
						$value = str_replace(',','.',$value);
						$sPosValues .= "'".trim($value)."',";
					}
					else
					{
						$sPosValues .= "'".trim($value)."',";
					}
					if($this->UseBonusPoints && $this->PerGoodsValue != 0)
					{
						//SES 20100204 Artikel ist kein Gutschein
						if($aPos[$x]['ordpItemId'] != '000000')
						{
							if($key == "ordpPriceTotal")
							{
								$bPoints = ($value / $this->PerGoodsValue) * $this->BonusPointsPerGoodsValue;
								$bPoints = ceil($bPoints);
								$this->addCustomerBonusPoints($bPoints);
							}
						}
					}
				}
				if($aPos[$x]['ordpItemId'] == '000000')
				{
					$start = strpos($aPos[$x]['ordpItemDesc'],"(");
					$end = strpos($aPos[$x]['ordpItemDesc'],")");
					$length = $end - $start - 1;
					$code = substr ($aPos[$x]['ordpItemDesc'], $start+1, $length);

					$sqlC = "SELECT * FROM ".DBToken."coupon where coupCode='".$code."'";
					$rsC = @mysqli_query($con,$sqlC);
					$objC = @mysqli_fetch_object($rsC);
					if($objC->coupValid=="once")
					{
					$sqlCoupon = "UPDATE ".DBToken."coupon set coupUsed = '1', coupUseddate='".date('YmdHis')."' where coupCode = '".$code."'";
					$qryCoupon = @mysqli_query($con,$sqlCoupon);
					}
				}
					$SQLitem = "SELECT itemInStockQuantity, itemShipmentStatus, itemItemNumber FROM ".DBToken."itemdata where itemItemNumber = '".$aPos[$x]['ordpItemId']."';";
				$qryitem = @mysqli_query($con,$SQLitem);
				$objitem = @mysqli_fetch_object($qryitem);

				$stock = $objitem->itemInStockQuantity-$aPos[$x]['ordpQty'];
				$status = $objitem->itemShipmentStatus-$aPos[$x]['ordpQty'];

				$SQLava = "Update ".DBToken."itemdata set itemInStockQuantity = '".$stock."' where itemItemNumber='".$objitem->itemItemNumber."'";
				$qryava = @mysqli_query($con,$SQLava);

				$SQLpos = "INSERT INTO ".DBToken."orderpos (ordpOrdIdNo,ordpPosNo,".$this->removeLastChr($sPosKeys).",ordpChgHistoryFlg) VALUES ('".$ordIdNo."','".$x."',".$this->removeLastChr($sPosValues).",'1')";
				$qrypos = @mysqli_query($con,$SQLpos);
			}
            
            // create curl resource 
            $ch = curl_init(); 
            $shopurl = base64_decode($_SESSION['sb_settings']['edAbsoluteShopPath_Text']);
            // set url 
            curl_setopt($ch, CURLOPT_URL, $shopurl."dynsb/shoporder/invoice.php?pk=".$ordIdNo."&lang=deu&d=3"); 

            //return the transfer as a string 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

            // $output contains the output string 
            $output = curl_exec($ch); 

            // close curl resource to free up system resources 
            curl_close($ch); 
            
			$this->dbclose($con);
		}
	}

	function writeCustomerData($aOrd, $aCus, $cusEMailFormat = 'text',$cusEMail, &$pass)
	{
		
		$blob = $this->getRandom4KBlob();
		$pass = $this->getRandomCustomerPassword();
        //die(var_dump($this->bNewCus));
		if($aOrd['ordCustomerId'] != "" && $this->bNewCus)
			$this->bNewCus = $this->checkCustomer($aOrd['ordCustomerId']);
		if($this->bNewCus)
			$this->bNewCus = $this->checkCustomerByEmail($cusEMail);
        //die(var_dump($cusEMail));
		$blob = $this->createSecureCustomerData($aCus, $blob);
		$aIgnoreKeys = array('AnmerkungenBestellung', 'ordAktKey', 'ordId','ordShippingCond', 'ordShippingCost', 'ordDiscount1Value', 'ordDiscount1Prct', 'ordDiscount2Value', 'ordDiscount2Prct', 'ordPaymentCond', 'ordPaymentCost', 'ordVAT1Value', 'ordVAT1Prct', 'ordVAT2Value', 'ordVAT2Prct', 'ordVAT3Value', 'ordVAT3Prct', 'ordTotalValue', 'ordTotalValueAfterDsc1', 'ordTotalValueAfterDsc2', 'ordCurrency');
		$con = $this->dbconnect();

		if($con)
		{
			if($this->bNewCus)
			{
				$sOrdKeys = "";
				$sOrdValues = "";
				foreach($aOrd as $key => $value)
				{
					if(!in_array($key, $aIgnoreKeys))
					{
						if($key != 'ordBirthdate' || $value != "")
						{
						if($key == 'ordCustomerId') continue;//$key = "cusId";
                        if($key == 'cusId') {
                            //A SM 02.05.2017 - Kundennummer automatisch generieren
                            // Prüfen, ob einen Kundennummer schon in Bestellungen verarbeitet wurde
                            $sqlStartNo = "SELECT MAX(CONVERT(ordCustomerId,UNSIGNED INTEGER))+1 AS SettingMemo FROM ".DBToken."order";
                            $qry = @mysqli_query($con,$sqlStartNo);
                            $startNo = mysqli_fetch_assoc($qry);
                            if($startNo['SettingMemo'] == '1'){// Startnummer aus Memo nehmen, wenn ein Nummer hinterlegt ist.
                                $sqlStartNo = "SELECT SettingMemo FROM ".DBToken."settingmemo WHERE SettingName = 'memoCustomerStartNo'";
                                $qry = @mysqli_query($con,$sqlStartNo);
                                $startNo = @mysqli_fetch_assoc($qry); 
                            }
                            if($startNo['SettingMemo'] <> '1'){// Nächste Kundennummer aus Ordertable ermitteln.
                                $sqlStartNo = "SELECT MAX(CONVERT(ordCustomerId,UNSIGNED INTEGER))+1 AS SettingMemo FROM ".DBToken."order";
                                $qry = @mysqli_query($con,$sqlStartNo);
                                $startNo = mysqli_fetch_assoc($qry);
                            }
                            
                            $value = $startNo['SettingMemo'];
                        }    
//A UR 4.2.2011						
						if($key == 'ordBirthdate')
						{
							$teile = explode('.',$value);
							$value = $teile[2]."-".$teile[1]."-".$teile[0];	
						}
//E UR						
						$sOrdKeys .= str_replace("ord", "cus", $key).",";
						$sOrdValues .= "'".$value."',";
						}
					}
				}
				$sOrdKeys .= "cusCountry,";
				$sOrdValues .= "'".$aOrd['ordDeliverCountry']."',";
				$SQL = "INSERT INTO ".DBToken."customer (".$this->removeLastChr($sOrdKeys).",cusData,cusPassword,cusEMailFormat,cusChgHistoryFlg) VALUES (".$this->removeLastChr($sOrdValues).",'".$blob."','".$pass."','".$cusEMailFormat."','1')";
				$qry = @mysqli_query($con,$SQL);
                //die(var_dump($SQL));
				$sql = "SELECT cusIdNo, cusId FROM ".DBToken."customer where cusEMail='".$cusEMail."';";
				$qry = @mysqli_query($con,$sql);
				$obj = @mysqli_fetch_object($qry);
				$this->cusIdNo = $obj->cusIdNo;
                $this->cusId = $obj->cusId;
				//A TS 06.01.2015
				$this->cusPass = $pass;
				$this->cusUser = $cusEMail;
			}
			else
			{
				$sOrdUpdate = "";
				$sOrdKeys = "";
				foreach($aOrd as $key => $value)
				{
					if(!in_array($key, $aIgnoreKeys))
					{
//A UR 24.2.2011						
						if($key != 'ordBirthdate' || $value != "")
						{
						if($key == 'ordBirthdate')
						{
							$teile = explode('.',$value);
							$value = $teile[2]."-".$teile[1]."-".$teile[0];	
						}
//E UR						
						if($key == 'ordCustomerId') $key = "cusId";
                            //$sOrdUpdate .= str_replace("ord", "cus", $key)." = '".$value."',";
//A UR 24.2.2011						
						}
//E UR						
					}
				}
				$sOrdUpdate = str_replace("cusDeliverCountry", "cusCountry", $sOrdUpdate);
				$sOrdKeys .= "cusCountry,";
				$SQL = "UPDATE ".DBToken."customer SET ".$this->removeLastChr($sOrdUpdate).", cusData = '".$blob."', cusEMailFormat = '".$cusEMailFormat."' WHERE cusIdNo = '".$this->cusIdNo."'";
				$qry = @mysqli_query($con,$SQL);
			}
			$this->dbclose($con);
		}
	}

	function checkCustomer($str)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT cusIdNo FROM ".DBToken."customer WHERE cusId = '".$str."' AND cusChgHistoryFlg <> '0'";
			$qry = @mysqli_query($con,$SQL);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		if($num == 0) return true;
		$obj = @mysqli_fetch_object($qry);
		$this->cusIdNo = intval($obj->cusIdNo);
		return false;
	}

	function getCustomerData($cid, $noDeliveryData = 0, $aIgnore = array('cusIdNo','cusPassword','cusDeliverCountry','cusChgTimestamp','cusChgUserIdNo','cusChgApplicId','cusChgHistoryFlg'))
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."customer WHERE cusIdNo = '".$cid."' AND cusChgHistoryFlg <> '0'";
			$qry = @mysqli_query($con,$SQL);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		if($num == 0) return false;
		$obj = @mysqli_fetch_object($qry);
		if($noDeliveryData == 1) array_push($aIgnore, 'cusDeliverFirmname','cusDeliverTitle','cusDeliverFirstName','cusDeliverLastName','cusDeliverStreet','cusDeliverStreet2','cusDeliverZipCode','cusDeliverCity');
		foreach($aIgnore as $key)
		{
			unset($obj->$key);
		}
		return $obj;
	}
	
//A UR 24.1.2010
	function getCustomerData2($cusEMail)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."customer WHERE cusEMail = '".$cusEMail."' AND cusChgHistoryFlg <> '0'";
			$qry = @mysqli_query($con,$SQL);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		if($num == 0) return false;
		$obj = @mysqli_fetch_object($qry);
		return $obj;
	}
//E UR

	function setCustomerData($ap)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."customer where cusEMail = '".$ap['cusEMail']."'";
			$qry = mysqli_query($con,$SQL);
			$num = mysqli_num_rows($qry);
			$this->CustomerExists = $num;

			$cid = intval(trim($ap['cid']));
			if(($cid != "" && $cid != 0)&& $this->CustomerExists)
			{
				$blob = $this->getRandom4KBlob();
				$aCus = array("cusBank" => $ap['cusBank'],
							"cusBLZ" => $ap['cusBLZ'],
							"cusAccountNo" => $ap['cusAccountNo'],
							"cusAccountOwner" => $ap['cusAccountOwner'],
							"cusCreditCard" => $ap['cusCreditCard'],
							"cusCreditValidMonth" => $ap['cusCreditValidMonth'],
							"cusCreditValidYear" => $ap['cusCreditValidYear'],
							"cusCreditNo" => $ap['cusCreditNo'],
							"cusCreditChk1" => $ap['cusCreditChk1'],
							"cusCreditChk2" => $ap['cusCreditChk2'],
							"cusCreditOwner" => $ap['cusCreditOwner']
							 );
				$blob = $this->createSecureCustomerData($aCus, $blob);
				$aIgnoreKeys = array("_LANGTAGFNFIELDCOMPANYORPRIVATE_", "send_save", "cusBank", "cusBLZ", "cusAccountNo", "cusAccountOwner", "cusCreditCard", "cusCreditValidMonth", "cusCreditValidYear",	"cusCreditNo", "cusCreditChk1", "cusCreditChk2", "cusCreditOwner", "userdata", "shopname", "dear", "logindata_email_text1", "logindata_email_text2", "user", "password");
				$sCusKeys = "";
				$sCusValues = "";
				$upstr = "";
				unset($ap['cid']);
				foreach($ap as $key => $val)
				{
					//A UR 8.2.2011						
					if($key == 'cusBirthdate')
					{
						$teile = explode('.',$val);
						$val = $teile[2]."-".$teile[1]."-".$teile[0];	
					}
					//E UR						
					if(!in_array($key, $aIgnoreKeys))
					{
						$upstr .= $key." = '".$val."',";
					}
				}
				$SQL = "UPDATE ".DBToken."customer SET ".$this->removeLastChr($upstr).", cusData = '".$blob."' WHERE cusIdNo = '".$cid."'";
				$qry = mysqli_query($con,$SQL);
				return mysqli_affected_rows($con);
			}
			else
			{
				if($this->CustomerExists==0)
				{
					$blob = $this->getRandom4KBlob();
					$aCus = array("cusBank" => $ap['cusBank'],
								"cusBLZ" => $ap['cusBLZ'],
								"cusAccountNo" => $ap['cusAccountNo'],
								"cusAccountOwner" => $ap['cusAccountOwner']
							 );
					$blob = $this->createSecureCustomerData($aCus, $blob);
					$aIgnoreKeys = array("_LANGTAGFNFIELDCOMPANYORPRIVATE_", "send_save",	"cusBank", "cusBLZ", "cusAccountNo", "cusAccountOwner",
									 "shopname", "recipient", "dear", "logindata_email_text1", "button2",
									 "logindata_email_text2", "user", "password", "email", "userdata", 
									 "shopname", "dear", "logindata_email_text1", "logindata_email_text2", "user", "password"
									);
					$colStr = "";
					$valStr = "";
					foreach($ap as $key => $val)
					{
						//A UR 8.2.2011						
						if($key == 'cusBirthdate')
						{
							$teile = explode('.',$val);
							$val = $teile[2]."-".$teile[1]."-".$teile[0];	
						}
						//E UR						
						if(!in_array($key, $aIgnoreKeys))
						{
							$colStr .= $key.", ";
							$valStr .= "'".$val."', ";
						}
					}
					$SQL = "INSERT INTO ".DBToken."customer (".$colStr."cusData) VALUES(".$valStr."'".$blob."')";
					$qry = mysqli_query($con,$SQL);
					return mysqli_affected_rows($con);
				}
			}
		}
		$this->dbclose($con);
	}

	function checkCustomerByEmail($cusEMail)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT cusIdNo, cusId FROM ".DBToken."customer WHERE cusEMail = '".$cusEMail."' AND cusChgHistoryFlg <> '0'";
			$qry = @mysqli_query($con,$SQL);
			$num = @mysqli_num_rows($qry);
			//$this->dbclose($con);
		}
		if($num == 0 ) return true;
		$obj = @mysqli_fetch_object($qry);
		$this->cusIdNo = intval($obj->cusIdNo);
        //A SM 02.05.2017 - Kundennummer automatisch generieren
        // Prüfen, ob einen Kundennummer schon in Bestellungen verarbeitet wurde
        $sqlStartNo = "SELECT MAX(CONVERT(ordCustomerId,UNSIGNED INTEGER))+1 AS SettingMemo FROM ".DBToken."order";
        $qry = @mysqli_query($con,$sqlStartNo);
        $startNo = mysqli_fetch_assoc($qry);
        if($startNo['SettingMemo'] == '1'){// Startnummer aus Memo nehmen, wenn ein Nummer hinterlegt ist.
            $sqlStartNo = "SELECT SettingMemo FROM ".DBToken."settingmemo WHERE SettingName = 'memoCustomerStartNo'";
            $qry = @mysqli_query($con,$sqlStartNo);
            $startNo = @mysqli_fetch_assoc($qry); 
        }
        if($startNo['SettingMemo'] <> '1'){// Nächste Kundennummer aus Ordertable ermitteln.
            $sqlStartNo = "SELECT MAX(CONVERT(ordCustomerId,UNSIGNED INTEGER))+1 AS SettingMemo FROM ".DBToken."order";
            $qry = @mysqli_query($con,$sqlStartNo);
            $startNo = mysqli_fetch_assoc($qry);
        }
        
        if($obj->cusId == ''){
            $obj->cusId = $startNo['SettingMemo'];
            $sql = "UPDATE ".DBToken."customer SET cusId='".$startNo['SettingMemo']."' WHERE cusIdNo=".$obj->cusIdNo;
            $qry = @mysqli_query($con,$sql);
        }
        $this->cusId = intval($obj->cusId);
		return false;
	}

		function checkBlacklistBlocked($cusEMail, &$emailBlockedMessage)		
		{
		$tmp_message = "";
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."black_email_list WHERE blackType=2";
				$res = @mysqli_query($con,$SQL);
				if($row = @mysqli_fetch_assoc($res)) 
			{
			//$emailBlockedMessage = "Schwarze Liste vorhanden.";
			$qrySQL1 = "SELECT * FROM ".DBToken."black_email_list WHERE blackType IN(1,2) ORDER BY blackType";
			$qry1 = @mysqli_query($con,$qrySQL1);
			while($obj1 = mysqli_fetch_object($qry1))
			{
				if ($obj1->blackType == 1)
				{
					$tmp_message = $obj1->blackValues;
				}
				else if ($obj1->blackType == 2)
				{
					if (strlen($obj1->blackValues) > 0)
					{
					if ( eregi ( trim($obj1->blackValues), $cusEMail ) )
					{
						$emailBlockedMessage = $tmp_message;
						return 1;
					}
					}
				}
			}
			@mysqli_free_result($qry1);

			}
			else
			{
			// Die schwarze Liste ist leer.
			return 0;
			}
		}		
		return 0;
		}

//A UR 12.1.2011	
		function checkCustomerBlocked($cusEMail, &$cusBlockedMessage)
		{
		$cusBlockedMessage = "";
		$con = $this->dbconnect();
		if($con)
		{
			if ($this->checkBlacklistBlocked($cusEMail, $cusBlockedMessage) == 1)
			{
			return 1;
			}
			$SQL2 = "SELECT cusBlocked,cusBlockedMessage FROM ".DBToken."customer WHERE
				cusEMail = '".$cusEMail."'";
	
	
			$qry2 = @mysqli_query($con,$SQL2);
			$obj2 = @mysqli_fetch_object($qry2);
			if ($obj2->cusBlocked=='1')
			{
			$cusBlockedMessage = $obj2->cusBlockedMessage;
			$this->dbclose($con);
			return 1;
			}
			$this->dbclose($con);
		}
		return 0;
	}
		

//E UR 

	
	function addCustomerBonusPoints($bPoints)
	{	 
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "UPDATE ".DBToken."customer SET cusBonusPoints = cusBonusPoints + '".$bPoints."' WHERE cusIdNo = '".$this->cusIdNo."'";
			$qry = @mysqli_query($con,$SQL);
		}
		return true;
	}

	function getRandom4KBlob()
	{
		$blob = "";
		for($i = 0; $i < 4096; $i++)
		{
			srand((double)microtime()*1000000);
			$y = rand(1,2);
			if($y == 1) $cc = rand(48,57);
			if($y == 2) $cc = rand(65,90);
			$blob .= chr($cc);
		}
		return $blob;
	}

	function getRandomCustomerPassword()
	{
		$pwd = "";
		for($i = 0; $i < 8; $i++)
		{
			srand((double)microtime()*1000000);
			$y = rand(1,2);
			if($y == 1) $cc = rand(48,57);
			if($y == 2) $cc = rand(65,90);
			$pwd .= chr($cc);
		}
		return $pwd;
	}

	function createSecureCustomerData($aCus, $blob)
	{
		$aList = array('cusBank', 'cusBLZ', 'cusAccountNo', 'cusAccountOwner', 'cusCreditCard', 'cusCreditNo', 'cusCreditValidMonth', 'cusCreditValidYear', 'cusCreditChk1', 'cusCreditChk2', 'cusCreditOwner');
		if(count($aCus) > 0)
		{
			foreach($aList as $key)
			{
				$value = $aCus[$key];
				if(strlen($value) > 0)
				{
					gshide(strtoupper($value), $key, $blob);
				}
				else
				{
					$blob[getColumnLengthIndex($key)] = "L";
				}
			}
		}
		else
		{
			foreach($aList as $key)
			{
				$blob[getColumnLengthIndex($key)] = "L";
			}
		}
		return $blob;
	}

	function removeLastChr($str)
	{
		return substr($str, 0, strlen($str)-1);
	}

	function removeFirstChr($str)
	{
		return substr($str, 1, strlen($str)-1);
	}

	function useOtherMailScript($ap, $url)
	{
		echo "<html>\n";
		echo "<head><title>dynsb orderform passthrough</title></head>";
		echo "<body>\n";
		echo "<form name=\"orderform\" method=\"POST\" action=\"".$url."\">\n";
		foreach($ap as $key => $value)
		{
			echo "<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\" />\n";
		}
			echo "</form>\n";
		echo "<script language=javascript>\n";
		echo "document.orderform.submit();\n";
		echo "</script>\n";
		echo "</body>\n";
		echo "</html>\n";
	}

	function getItemHistory($sid)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT hisData1 FROM ".DBToken."history WHERE hisSessionId = '".$sid."'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
			$aval = unserialize($obj->hisData1);
			$this->dbclose($con);
		}
		return $aval;
	}

	function getNewsTickerData()
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT ntContent AS str, ntScrollSpeed AS speed FROM ".DBToken."newsticker WHERE ntIdNo = '1' AND ntShowFlg = '1'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
			$this->dbclose($con);
		}
		return $obj;
	}

	function getNewsData()
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."news WHERE newsStartDate < current_timestamp() + 0 AND newsChgHistoryFlg <> '0' ORDER BY newsSortIndex ASC";
			$qry = @mysqli_query($con,$SQL);
			$this->dbclose($con);
		}
		return $qry;
	}

	function checkLoginData($ap,$sid)
	{
		
		$con = $this->dbconnect();
		$pass = trim($ap['password']);
		if($con)
		{
			$this->cleanUpSessionTable();
			$SQL = "SELECT cusIdNo, cusPassword, cusTitle, cusFirstName, cusLastName FROM ".DBToken."customer WHERE cusEMail = '".trim($ap['userid'])."' AND cusChgHistoryFlg <> '0'	AND cusBlocked <> '1'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
			if($obj && ($pass == $obj->cusPassword))
			{
				$SQL = "SELECT * FROM ".DBToken."session WHERE sessId = '".$sid."'";
				$qry = @mysqli_query($con,$SQL);
				$num = @mysqli_num_rows($qry);
				if($num == 0)
			{
					$SQLdel = "DELETE FROM ".DBToken."session WHERE sessCustomerIdNo = '".$obj->cusIdNo."'";
					$qrydel = @mysqli_query($con,$SQLdel);
					$SQL = "INSERT INTO ".DBToken."session (sessId, sessCustomerIdNo) VALUES ('".$sid."','".$obj->cusIdNo."')";
					$qry = @mysqli_query($con,$SQL);
					$this->dbclose($con);
				}
				return 1;
			}
		}
		return 0;
	}

	function addSessionData($cid, $sid)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
		$SQL = "SELECT * FROM ".DBToken."session WHERE sessId = '".$sid."'";
			$qry = @mysqli_query($con,$SQL);
				$num = @mysqli_num_rows($qry);
			if($num == 0)
		{
			$SQL = "INSERT INTO ".DBToken."session (sessId, sessCustomerIdNo) VALUES ('".$sid."','".$cid."')";
			$qry = @mysqli_query($con,$SQL);
			}
			$this->dbclose($con);
		}
	}
	// SES 20100204
	function getCustomerBonusPoints($cid)
	{
		$res = 0;
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT cusBonusPoints FROM ".DBToken."customer WHERE cusIdNo = '".$cid."'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
			$res = $obj->cusBonusPoints;
			$this->dbclose($con);
		}
		return $res;
	}

	function isLoggedIn($sid)
	{
			
		$aCus = array(0 => '0');
		$con = $this->dbconnect();
		if($con)
		{
				$SQL = "SELECT DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR) AS timelimit";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
			$timelimit = $obj->timelimit;
			$SQL = "SELECT sessCustomerIdNo FROM ".DBToken."session WHERE sessId = '".$sid."' AND sessLastAccess > '".$timelimit."'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);

			if($obj)
			{
				$SQL = "SELECT cusIdNo, cusPassword, cusTitle, cusFirstName, cusLastName FROM ".DBToken."customer WHERE cusIdNo = '".$obj->sessCustomerIdNo."' AND cusChgHistoryFlg <> '0'";
				$qry = @mysqli_query($con,$SQL);
				$obj = @mysqli_fetch_object($qry);
				$aCus = array("".$obj->cusIdNo."","".$obj->cusTitle."","".$obj->cusFirstName."","".$obj->cusLastName."");
				$this->dbclose($con);
				return $aCus;
				}
			else
			{
					return $aCus;
				}
		}
		return $aCus;
	}

	function logout($sid)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "DELETE FROM ".DBToken."session WHERE sessId = '".$sid."'";
			$qry = @mysqli_query($con,$SQL);
				$this->dbclose($con);
				if($qry)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		return 0;
	}

	function cleanUpSessionTable()
	{
		$con = $this->dbconnect();
		$cnt = 0;
		$SQL = "SELECT DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY) AS timelimit";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$timelimit = $obj->timelimit;
			$SQL = "SELECT * FROM ".DBToken."session WHERE sessLastAccess < '".$timelimit."'";
		$qry = @mysqli_query($con,$SQL);
		while($obj = @mysqli_fetch_object($qry))
		{
			$SQLdel = "DELETE FROM ".DBToken."session WHERE sessIdNo = '".$obj->sessIdNo."'";
			$qrydel = @mysqli_query($con,$SQLdel);
			if($qrydel) $cnt++;
		}
			return $cnt;
	}


	/*
		return 0 = success
		return 1 = new pass is not equal with the pass repetition
		return 2 = error on updating user data
		return 3 = user data not found in db
		return 4 = no empty or too short passwords allowed
	*/
	function changeLoginData($ap)
	{
		
		$minpwdlength = 6;
		$con = $this->dbconnect();
		$pass = trim($ap['passwordold']);
		$passnew = trim($ap['passwordnew']);
		$passrep = trim($ap['passwordrepetition']);
		if($passnew != $passrep) return 1;
		if($passnew == '') return 4;
		if(strlen($passnew) < $minpwdlength) return 4;
		if($con)
		{
			$SQL = "SELECT cusIdNo, cusPassword, cusTitle, cusFirstName, cusLastName FROM ".DBToken."customer WHERE cusEMail = '".trim($ap['userid'])."'";
			$qry = @mysqli_query($con,$SQL);
			$obj = @mysqli_fetch_object($qry);
		}
		if($obj && $pass == $obj->cusPassword)
		{
			$SQL = "UPDATE ".DBToken."customer SET cusPassword = '".$passnew."' WHERE cusEMail = '".trim($ap['userid'])."' AND cusIdNo = '".trim($ap['cid'])."'";
			$qry = mysqli_query($con,$SQL);
			if($qry && @mysqli_affected_rows($con) > 0)
			{
				$this->dbclose($con);
				return 0;
			}
			else
			{
				$this->dbclose($con);
				return 2;
			}
		}
		else
		{
			$this->dbclose($con);
			return 3;
		}
	}
	

	function getBestsellerList()
	{
		
		$count = $this->getBestsellerCount();
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT op.ordpItemId, op.ordpPrice, op.ordpItemDesc, o.ordCurrency, SUM(op.ordpQty) AS qty
					FROM ".DBToken."orderpos op, ".DBToken."order o, ".DBToken."itemdata itm 
					WHERE o.ordIdNo = op.ordpOrdIdNo
					AND itm.itemItemNumber=op.ordpItemId
					AND op.ordpItemId <> '000000'
					GROUP BY op.ordpItemId
					ORDER BY qty DESC LIMIT 0,".$count;
			$qry = @mysqli_query($con,$SQL);

			$ignore = "";
			while($obj = @mysqli_fetch_object($qry))
			{
				$SQL2 = "SELECT * FROM ".DBToken."itemdata WHERE itemItemNumber = '".$obj->ordpItemId."'";
					$qry2 = @mysqli_query($con,$SQL2);
					$num = @mysqli_num_rows($qry2);

					if($num == 0 || $num == "")
					{
						$ignore = "'".$obj->ordpItemId."',";
					}
			}

			$ignore = substr($ignore,0,strlen($ignore)-1);
			$SQL =	"SELECT op.ordpItemId, op.ordpPrice, op.ordpItemDesc, o.ordCurrency, SUM(op.ordpQty) AS qty
						 FROM ".DBToken."orderpos op, ".DBToken."order o
						 WHERE o.ordIdNo = op.ordpOrdIdNo ";
			if(strlen($ignore)>0)
			{
				$SQL .= "AND NOT(op.ordpItemId IN(".$ignore.")) ";
			}
			$SQL .= "AND op.ordpItemId <> '000000'
						 GROUP BY op.ordpItemId
						 ORDER BY qty DESC LIMIT 0,".$count;
			$qry = @mysqli_query($con,$SQL);
		}
		$this->dbclose($con);
		return $qry;
	}

	function getArticle($ItemNo)
	{
		
		$con = $this->dbconnect();
		if($con)
		{
		//$SQL = "SELECT * from ".DBToken."itemdata, ".DBToken."price where itemItemNumber='".$ItemNo."' AND prcItemNumber='".$ItemNo."' AND itemItemNumber=prcItemNumber";
		// Ich brauche das ORDER BY prcQuantityFrom für die alten Templates ( Uwe Reuschel 13.1.2010) 
		$SQL = "SELECT * from ".DBToken."itemdata, ".DBToken."price where itemItemNumber='".$ItemNo."' AND prcItemNumber='".$ItemNo."' AND itemItemNumber=prcItemNumber ORDER BY prcQuantityFrom";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$this->dbclose($con);
		}
		return $obj;
	}

	function getAutoCrossSellingList($ItemNo, &$num)
	{
		$con = $this->dbconnect();
		$orderIds = '';
		if($con)
		{
		$SQL = "SELECT op.ordpOrdIdNo
				FROM ".DBToken."orderpos op
				WHERE ordpItemId='".trim($ItemNo)."'";

		$qry = @mysqli_query($con,$SQL);

		while($obj = @mysqli_fetch_object($qry))
		{
			$orderIds .= $obj->ordpOrdIdNo.", ";
		}
		$orderIds = substr($orderIds,0,sizeof($orderIds)-3);

		$AutoCrossSellingCount = $this->getAutoCrossSellingCount();
		$con = $this->dbconnect();
		$SQL2 = "SELECT *, count(op.ordpItemId) as num
				 FROM ".DBToken."orderpos op
				 INNER JOIN ".DBToken."itemdata itm ON itm.itemItemNumber=op.ordpItemId
				 WHERE op.ordpOrdIdNo IN(".$orderIds.")
				 AND op.ordpItemId<>'000000'
				 AND op.ordpItemId<>'".trim($ItemNo)."'
				 GROUP BY op.ordpItemId
				 ORDER BY num DESC LIMIT 0, ".$AutoCrossSellingCount.";";

		$qry2 = @mysqli_query($con,$SQL2);
		$ignore = "";
		while($obj2 = @mysqli_fetch_object($qry2))
		{
			$SQL3 = "SELECT * FROM ".DBToken."itemdata WHERE itemItemNumber = '".$obj2->ordpItemId."'";
				$qry3 = @mysqli_query($con,$SQL3);
				$num = @mysqli_num_rows($qry3);

				if($num == 0 || $num == "")
				{
					$ignore = "'".$obj2->ordpItemId."',";
				}
		}

		$ignore = substr($ignore,0,strlen($ignore)-1);
		$SQL4 =	"SELECT *, count(op.ordpItemId) as num
					FROM ".DBToken."orderpos op
					WHERE op.ordpOrdIdNo IN(".$orderIds.")
					AND op.ordpItemId<>'000000' ";
		if(strlen($ignore)>0)
		{
			$SQL4 .= "AND NOT(op.ordpItemId IN(".$ignore.")) ";
		}
		$SQL4 .= "AND op.ordpItemId<>'000000'
					AND op.ordpItemId<>'".trim($ItemNo)."'
					GROUP BY op.ordpItemId
					ORDER BY num DESC LIMIT 0, ".$AutoCrossSellingCount.";";
		$qry4 = @mysqli_query($con,$SQL4);

		$itemlist = array();
		$item = array();

		while($obj = @mysqli_fetch_object($qry4))
		{
			$OrderSQL = "SELECT * FROM ".DBToken."order where ordIdNo = '".$obj->ordpOrdIdNo."'";
			$OrderQRY = @mysqli_query($con,$OrderSQL);
			$OrderOBJ = @mysqli_fetch_object($OrderQRY);

			$ItemSQL = "SELECT * from ".DBToken."itemdata where itemItemNumber='".$obj->ordpItemId."'";
			$ItemQRY = @mysqli_query($con,$ItemSQL);
			$ItemOBJ = @mysqli_fetch_object($ItemQRY);

			$PriceSQL = "SELECT * from ".DBToken."price where prcItemNumber='".$obj->ordpItemId."'";
			$PriceQRY = @mysqli_query($con,$PriceSQL);
			$PriceOBJ = @mysqli_fetch_object($PriceQRY);

			if($ItemOBJ && $PriceOBJ)
			{
			$item = array();
			array_push($item, $ItemOBJ);
			array_push($item, $PriceOBJ);
			array_push($itemlist, $item);
			unset($ItemOBJ);
			unset($PriceOBJ);
			unset($item);
			}
		}
		}
		$this->dbclose($con);
		return $itemlist;
	}

	function getAutoCrossSellingCount()
	{
		$con = $this->dbconnect();
		if($con)
		{
		$SQL = "SELECT setAutoCrossSellingCount FROM ".DBToken."settings";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$AutoCrossSellingCount = $obj->setAutoCrossSellingCount;
		$this->dbclose($con);
		}
		return $AutoCrossSellingCount;
	}

	function getBestsellerCount()
	{
		$con = $this->dbconnect();
		if($con)
		{
		$SQL = "SELECT setBestsellerCount FROM ".DBToken."settings";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$BestsellerCount = $obj->setBestsellerCount;
		$this->dbclose($con);
		}
		return $BestsellerCount;
	}

	function getCustomerNewsData($cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$csql = "SELECT * FROM ".DBToken."customer where cusIdNo = '".$cid."'";
			$cqry = @mysqli_query($con,$csql);
			$cobj = @mysqli_fetch_object($cqry);
			$SQL = "SELECT * FROM ".DBToken."customernews WHERE (cnewsForAll = '1' OR cnewsIdNo ='".$cobj->cusCustomerNews."') AND cnewsStartDate < current_timestamp() + 0";
			$qry = @mysqli_query($con,$SQL);
			$this->dbclose($con);
		}
		return $qry;
	}

	function getLastOrderCount()
	{
		$con = $this->dbconnect();
		if($con)
		{
		$SQL = "SELECT setLastOrderCount FROM ".DBToken."settings";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$LastOrderCount = $obj->setLastOrderCount;
		$this->dbclose($con);
		}
		return $LastOrderCount;
	}


	function getOrderByCustom($cid)
	{
		$count = $this->getLastOrderCount();
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT * FROM ".DBToken."order where ordCusIdNo = '".$cid."' order by ordDate DESC LIMIT 0,".$count;
			$qry = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $qry;
	}

	function getOrderposByOrder($orderID)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT * FROM ".DBToken."orderpos where ordpOrdIdNo = '".$orderID."'";
			$qry = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $qry;

	}

	function getCouponData($couponcode)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT * FROM ".DBToken."coupon where coupCode='".$couponcode."' AND coupUsed='0' AND coupAssigned='1'";
			$qry = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $qry;
	}

	function getPDFCouponData($coupId)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql1 = "SELECT coupCode, coupPrice, coupCurrency FROM ".DBToken."coupon where coupId = '".$coupId."'";
			$qry1 = @mysqli_query($con,$sql1);
			$obj1 = @mysqli_fetch_object($qry1);
			$sql2 = "SELECT * FROM ".DBToken."settings";
			$qry2 = @mysqli_query($con,$sql2);
			$obj2 = @mysqli_fetch_object($qry2);
			$data = array("Text1" => $obj2->couponText1,
						"Text2" => $obj2->couponText2,
						"Image" => $obj2->couponImage,
						"ImageXsize" => $obj2->couponImageXsize,
						"ImageYsize" => $obj2->couponImageYsize,
						"Code" => $obj1->coupCode,
						"Price" => $obj1->coupPrice,
						"Currency" =>$obj1->coupCurrency);
			$this->dbclose($con);
		}
		return $data;
	}

	function isSpecialItem($ItemNo)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."specialitem where spitemItemNumber = '".trim($ItemNo)."' AND spitemStartDate < ".date('YmdHis')." AND spitemEndDate > ".date('YmdHis');
			$QRY = @mysqli_query($con,$SQL);
			$num = @mysqli_num_rows($QRY);
			if($num!=0)
			{
				return true;
			}
			else
			{
				return false;
			}
			$this->dbclose($con);
		}
	}

	function getAllManufacturer()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemManufacturer from ".DBToken."itemdata where itemManufacturer<>'' group by itemManufacturer order by itemManufacturer";
			$qry = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $qry;
	}

	function existsAllManufacturer()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemManufacturer from ".DBToken."itemdata where itemManufacturer<>'' group by itemManufacturer order by itemManufacturer";
			$qry = @mysqli_query($con,$sql);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		return $num;
	}

	function getAllBrands()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemBrand from ".DBToken."itemdata where itemBrand<>'' group by itemBrand order by itemBrand";
			$qry = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $qry;
	}

	function existsAllManufacturerNo()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemManufacturerProductCode from ".DBToken."itemdata where itemManufacturerProductCode<>''group by itemManufacturerProductCode order by itemManufacturerProductCode";
			$qry = @mysqli_query($con,$sql);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		return $num;
	}

	function existsBrands()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemBrand from ".DBToken."itemdata where itemBrand<>'' order by itemBrand";
			$qry = @mysqli_query($con,$sql);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		return $num;
	}

	function existsEanIsbn()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT itemEAN_ISBN from ".DBToken."itemdata where itemEAN_ISBN<>'' order by itemEAN_ISBN";
			$qry = @mysqli_query($con,$sql);
			$num = @mysqli_num_rows($qry);
			$this->dbclose($con);
		}
		return $num;
	}

	function getOrderEmailLayout()
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "SELECT * FROM ".DBToken."settings";
			$qry = @mysqli_query($con,$sql);
			$obj = @mysqli_fetch_object($qry);
			//TS 22.07.2016: E-Mail-Logo aus setting (SB-Einstellungen (edLogo2_Text)) laden
			$sql = "SELECT SettingValue AS SmallShopLogo FROM ".DBToken."setting WHERE SettingName='edLogo2_Text' AND LanguageID='" . $this->slc . "' LIMIT 1";
			$qry = @mysqli_query($con,$sql);
			$obj2 = @mysqli_fetch_object($qry);
			if($obj2->SmallShopLogo != '') {
				$obj->ordEmailImage = $obj2->SmallShopLogo;
			}
			$this->dbclose($con);
		}
		return $obj;
	}

	// Newsletter-Funktionen --------------------------------------------
	function getEmailData($sqlString) {
		$con = $this->dbconnect();
		if($con) {
		$result = @mysqli_query($con,$sqlString);
		$this->dbclose($con);
		}
		return $result;
	}

	function getEmailDataNum($sqlString) {
		$con = $this->dbconnect();
		if($con) {
		$result = @mysqli_query($con,$sqlString);
		$num = @mysqli_num_rows($result);
		$this->dbclose($con);
		}
		return $num;
	}

	function saveEmailData($sqlString) {
		$con = $this->dbconnect();
		if($con) {
		$result = mysqli_query($con,$sqlString);
		$this->dbclose($con);
		}
		return $result;
	}

	function checkEmail($email)
	{
		 $pattern = "^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$";
		 return eregi($pattern, $email);
	}

	function splitEmail($email) {

		$pos = strpos($email, "@");
		$name = substr($email, 0, $pos);
		//$name = "\"".$name."\"";

		$len = strlen($email);
		$domain = substr($email, $pos+1, $len);
		//$domain = "\"".$domain."\"";

		$regs[1] = $name;
		$regs[2] = $domain;

		return $regs;
	}

	function getCustomerDiscount($cusID)
	{
		$con = $this->dbconnect();
		if($con) {

		$SQL = "SELECT cusDiscount FROM ".DBToken."customer where cusIdNo='".$cusID."'";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$this->dbclose($con);
		}
		return $obj->cusDiscount;
	}

	function getitemavail(&$post){
		$con = $this->dbconnect();
		if($con){
			for($a = 0; count($post); $a++){
				$qtyofpos = $post[$a]['art_count'];
	
				for($i=0; $i<$qtyofpos; $i++){
					$sql = "SELECT itemInStockQuantity, itemShipmentStatus FROM ".DBToken."itemdata WHERE itemItemNumber = '".$post['art_num']."'";
					$rs = @mysqli_query($con,$sql);
					$obj = @mysqli_fetch_object($rs);
					$stock = $obj->itemInStockQuantity;
					$status = $obj->itemShipmentStatus;
					if($stock<=0 && $stock!=null){
						$stock = '0';
					}
					//A TS 26.08.2014 status -1 berücksichtigen
					if($status!=null && $status != -1){
						$sql = "SELECT * from ".DBToken."availability WHERE avaId='".$status."'";
						$rs = @mysqli_query($con,$sql);
						$obj = @mysqli_fetch_object($rs);
						$avaDescription = $obj->avaDescription;
					} else {
						$avaDescription = "";
					}
					$post[$a]['art_avail'] = $avaDescription;
				}
			}	
			$this->dbclose($con);
		}
	}

	function getMailScript($mailscript, &$path, &$host)
		{
		if($mailscript=="gsorder.php")
			{
		$host = $_SERVER['HTTP_HOST'];
				$path = $_SERVER['SCRIPT_URL'];
				$path = str_replace("paynova.php","gsorder.php",$path);
			}
			else
			{
				$host_tmp = substr($mailscript,8);
				$pos = strpos($host_tmp,"/");
				$host_tmp2 = substr($host_tmp,0,$pos);
				$host = substr($mailscript,0,8+strlen($host_tmp2));
				$path = substr($mailscript,8+strlen($host_tmp2));
			}
			$host = str_replace("https://","",$host);
			$host = str_replace("www.","",$host);
		}

		function getItem($itemNo, $lang)
		{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT * FROM ".DBToken."itemdata, ".DBToken."price WHERE
				itemItemNumber = '".$itemNo."'
				AND itemLanguageId = '".$lang."'
				AND itemItemNumber = prcItemNumber
				AND prcCountryId = itemLanguageId";
		$rs = @mysqli_query($con,$sql);
		$obj = @mysqli_fetch_object($rs);
		$this->dbclose($con);
		}
		return $obj;
	}

	function getNotpadData($cusIdNo)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT n.*, i.* FROM ".DBToken."notepad n
			left join ".DBToken."itemdata i on i.itemItemNumber =	n.itemNumber
			 where n.cusIdNo = '".$cusIdNo."' group by	n.itemNumber order by n.date desc";
		$rs = @mysqli_query($con,$sql) or die(mysqli_error($con));
		$this->dbclose($con);
		}
		return $rs;
	}

	function getSettings($field)
		{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT ".$field." FROM ".DBToken."settings";
		$rs = @mysqli_query($con,$sql);
		$obj = @mysqli_fetch_object($rs);
		$this->dbclose($con);
		}
		return $obj;
	}

	function findArticle($itemNumber,$detailPageName,$lang)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT * FROM ".DBToken."itemdata, ".DBToken."price 
				 WHERE itemItemNumber = '".$itemNumber."' 
				 AND itemItemNumber = prcItemNumber
				 AND itemLanguageId = '".$lang."' 
				 AND prcCountryId = itemLanguageId"; 
		/*
		$sql = "SELECT * FROM ".DBToken."itemdata, ".DBToken."price
				WHERE itemItemNumber = '".$itemNumber."'
				AND itemItemPage='".$detailPageName."'
				AND itemLanguageId = '".$lang."'
				AND itemItemNumber = prcItemNumber
				AND prcCountryId = itemLanguageId";*/
		$rs = @mysqli_query($con,$sql);
		$obj = @mysqli_fetch_object($rs);
		$this->dbclose($con);
		}
		return $obj;
	}

	function delNotepadEntry($item, $cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "DELETE FROM ".DBToken."notepad WHERE cusIdNo='".$cid."' AND itemNumber='".$item."'";
		@mysqli_query($con,$sql);
		$this->dbclose($con);
		}
	}

	function insNotepadEntry($item, $cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "INSERT INTO ".DBToken."notepad (cusIdNo, itemNumber, date) values('".$cid."','".$item."','".date('Ymd')."')";
		@mysqli_query($con,$sql);
		$this->dbclose($con);
		}
	}

	function getWishlistData($cusIdNo)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT n.*, i.* FROM ".DBToken."wishlist n
			left join ".DBToken."itemdata i on i.itemItemNumber =	n.itemNumber
			 where n.cusIdNo = '".$cusIdNo."' group by	n.itemNumber order by n.date desc";

		 // $sql = "SELECT * FROM ".DBToken."wishlist where cusIdNo = '".$cusIdNo."'";
		$rs = @mysqli_query($con,$sql);
		$this->dbclose($con);
		}
		return $rs;
	}

	function delWishListEntry($item, $cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "DELETE FROM ".DBToken."wishlist WHERE cusIdNo='".$cid."' AND itemNumber='".$item."'";
		@mysqli_query($con,$sql);
		$this->dbclose($con);
		}
	}

	function insWishListEntry($item, $cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "INSERT INTO ".DBToken."wishlist (cusIdNo, itemNumber, date) values('".$cid."','".$item."','".date('Ymd')."')";
		@mysqli_query($con,$sql);
		$this->dbclose($con);
		}
	}

	function getCustData($cid)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT * FROM ".DBToken."customer where cusIdNo = '".$cid."'";
		$rs = @mysqli_query($con,$sql);
		$obj = @mysqli_fetch_object($rs);
		$this->dbclose($con);
		}
		return $obj;
	}

	function searchCustomer($term, $searchtxt)
	{
		$con = $this->dbconnect();
		if($con)
		{
		if($term=="email")
		{
			$sql = "SELECT * FROM ".DBToken."customer where cusEMail = '".$searchtxt."'";
		}
		else if($term=="name")
		{
			if(sizeof($searchtxt)==1)
			{
			$sql = "SELECT * FROM ".DBToken."customer where cusFirstName = '".$searchtxt[0]."' OR cusLastName = '".$searchtxt[0]."'";
			}
			else if(sizeof($searchtxt)==2)
			{
			$sql = "SELECT * FROM ".DBToken."customer where (cusFirstName = '".$searchtxt[0]."' AND cusLastName = '".$searchtxt[1]."') OR (cusFirstName = '".$searchtxt[1]."' AND cusLastName = '".$searchtxt[0]."')";
			}
		}
		$rs = @mysqli_query($con,$sql);
		$this->dbclose($con);
		}
		return $rs;
	}

	function getFAQs()
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT * from ".DBToken."faq where faqActive='1'";
		$rs = @mysqli_query($con,$sql);
		$this->dbclose($con);
		}
		return $rs;
	}

	function startSearch($sql_str)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$rs = mysqli_query($con,$sql_str);
		}
		$this->dbclose($con);
		return $rs;
	}
	
	function getBonusItems()
	{
		$con = $this->dbconnect();
		if($con)
		{
		$sql = "SELECT * FROM ".DBToken."itemdata where itemIsBonusArticle = 'Y'";
		$rs = @mysqli_query($con,$sql);
		$this->dbclose($con);
		}
		return $rs;
	}
	
	function updateCustomerBonusPoints($cid, $points)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$sql = "UPDATE ".DBToken."customer SET cusBonusPoints = '".$points."' WHERE cusIdNo = '".$cid."'";
			$rs = @mysqli_query($con,$sql);
			$this->dbclose($con);
		}
		return $rs;
	}

	function checkBestellbar($ordpItemId1){
		$con = $this->dbconnect();
		if($con) {
			//Länderkennung wird ignoriert, weil sie auch in getBestsellerList ignoriert wurde 
			$SQL2 = "SELECT * FROM ".DBToken."itemdata WHERE itemItemNumber = '".$ordpItemId1."' and itemIsCatalogFlg IN('F','0','')";
			$qry2 = @mysqli_query($con,$SQL2);
			$num = @mysqli_num_rows($qry2);
			if ($num > 0){
				// bestellbar
				return 1;
			} else {
				// nicht bestellbar
				return 0;
			}
		}
	}

		function checkDownLoadArtikel($itemNo, $lang)
		{
		$con = $this->dbconnect();
		if($con)
		{
			$SQL2 = "SELECT itemIsDownloadArticle FROM ".DBToken."itemdata WHERE
				itemItemNumber = '".$itemNo."'
				AND itemLanguageId = '".$lang."'";
	
	
			$qry2 = @mysqli_query($con,$SQL2);
			$obj2 = @mysqli_fetch_object($qry2);
			if ($obj2->itemIsDownloadArticle=='Y' || $obj2->itemIsDownloadArticle=='T')
			{
			$this->dbclose($con);
			return 'true';
			}
			$this->dbclose($con);
		}
		return 'false';
	}
	
 
	function gibDownloadfilename($con,$itemNo,$lang,&$AllowedDownloads)
	{ 
		$Downloadfilename = "";
		$AllowedDownloads = 0;
		if($con)
		{
		$SQL2 = "SELECT downloadFilename,downloadAllowedDownloads FROM ".DBToken."downloadarticle WHERE
				downloadItemNumber = '".$itemNo."'
				AND downloadLanguageId = '".$lang."'";	
		
		$qry2 = @mysqli_query($con,$SQL2);
		$num = @mysqli_num_rows($qry2);
		if ($num > 0)
		{
			$obj2 = @mysqli_fetch_object($qry2);
			$Downloadfilename = $obj2->downloadFilename;
			$AllowedDownloads = $obj2->downloadAllowedDownloads;
		}		
		}
		
		return $Downloadfilename;
	}

	/*A TS 22.10.2015: Downloads werden nur noch über die Datenbank gezählt und gehändelt.
	Das Erzeugen eines Kunden-Download-Ordners ist überflüssig*/
	/*function getCustomerDownloadDir($ap,$lang)
	{
		$dirname = "";
		$downloaddir = $ap['downloaddir'];
		//echo $downloaddir;

		if (strlen($downloaddir) > 0)
		{
			$dirname = "./customerdownloads"; 
			if (!file_exists($dirname)) 
			{
				if (!mkdir($dirname))
				{
					echo "Could not create directory ".$dirname." ."; 
					return;
				}
			}
			chmod($dirname, 0777);
			$dirname = $dirname."/".$downloaddir;
			if (!file_exists($dirname)) 
			{
				if (!mkdir($dirname))
				{
					echo "Could not create directory ".$dirname."/".$downloaddir." ."; 
					return "";
				}
			}
			chmod($dirname, 0777);
		}
		return $dirname; 
	}*/
	/*E TS 22.10.2015: Downloads werden nur noch über die Datenbank gezählt und gehändelt.
	Das Erzeugen eines Kunden-Download-Ordners ist überflüssig*/

	/*A TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
	function writeDownloadData($ordIdNo,$ap)
	{		
		//$dirname =	$this->getCustomerDownloadDir($ap,'');
		/*if (strlen($dirname) > 0)
		{
		$con = $this->dbconnect();
		if($con)
		{
			for($x = 1; $x <= intval($ap['qtyofpos']); $x++)
			{
				$Item = $ap[itemId.$x];
				$ItemDesc = $ap['_LANGTAGFNFIELDTEXTITEM_'.$x];
				$sDownload = $ap['_LANGTAGFNFIELDDOWNLOADITM_'.$x];
				$nQuantity = (int)$ap['_LANGTAGFNFIELDQUANTITY_'.$x];
	
				if ($sDownload == "true")
				{
					
					$dldTime = date("YmdHis", time());
					$strDateiname = $this->gibDownloadfilename($con,$Item,$this->slc,$AllowedDownloads);
					if (strlen($strDateiname) > 0)
					{
						$srcname="./download/".$strDateiname;
						$Anz = $this->getCountDownloadsAvailible($dirname,$dldTime . ';' . $strDateiname);
						$bret = true;
						if ($Anz < 0)
						{
							$destname=$dirname."/".$AllowedDownloads.';' . $dldTime . ';' .$strDateiname;
							touch($destname);
							$bret = copy($srcname, $destname);
							if (!$bret)
							{
								print ("failed to copy $strDateiname...<br>\n");
							}
						}
						else
						{
							$srcname = $dirname."/".$Anz.';' . $dldTime . ';' .$strDateiname;
							$AllowedDownloads = $AllowedDownloads*$nQuantity + $Anz;
							$destname=$dirname."/".$AllowedDownloads.';' . $dldTime . ';' .$strDateiname;
							//echo "<br>srcname=".$srcname;
							//echo "<br>newfilename=".$destname;
							$bret =rename($srcname,$destname);
							if (!$bret)
							{
								print ("failed to rename $srcname...<br>\n");
							}
						}
						if ($bret)
						{
							$SQLord = "INSERT INTO ".DBToken."downloadarticle_customer (dlcuItemNumber,dlcuSLC,dlcuFilename,dlcuAllowedDownloads,dlcuOrdId, dlcuCusId,dlcuCreateTime )".
										" VALUES ('".$Item."','".$this->slc."','".$strDateiname."',".$AllowedDownloads.",".$ordIdNo.",".$this->cusIdNo.",'" . $dldTime . "')";
							$qryord = @mysqli_query($con,$SQLord);
							$ordIdNo = @mysqli_insert_id($con);
						}
					}
				}
			} // for
			$this->dbclose($con);
		}
		}*/
		$con = $this->dbconnect();
		for($x = 1; $x <= intval($ap['qtyofpos']); $x++) {
			$Item = $ap[itemId.$x];
			$ItemDesc = $ap['_LANGTAGFNFIELDTEXTITEM_'.$x];
			$sDownload = $ap['_LANGTAGFNFIELDDOWNLOADITM_'.$x];
			$nQuantity = (int)$ap['_LANGTAGFNFIELDQUANTITY_'.$x];
			if ($sDownload == "true") {
				$dldTime = date("YmdHis", time());
				$strDateiname = $this->gibDownloadfilename($con,$Item,$this->slc,$AllowedDownloads);
				$SQLord = "INSERT INTO ".DBToken."downloadarticle_customer (dlcuItemNumber,dlcuSLC,dlcuFilename,dlcuAllowedDownloads,dlcuOrdId, dlcuCusId,dlcuCreateTime )".
							" VALUES ('".$Item."','".$this->slc."','".$strDateiname."',".$AllowedDownloads.",".$ordIdNo.",".$this->cusIdNo.",'" . $dldTime . "')";
							$qryord = @mysqli_query($con,$SQLord);
							$ordIdNo = @mysqli_insert_id($con);
			}
		}//for
		$this->dbclose($con);
	}
	/*E TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/

	//A UR 16.2.2011	
	// Diese Funktion wurde implementiert, weil unser kostenloser CMailServer (für lokalen Test) 
	// das Gleichheitszeichen '=' nicht ordentlich als Referenz übertragen kann und die 
	// Parameterliste deshalb verschlüsselt übergeben werden muss. 
	function convertParams($arrConverted) 
	{
	$strParameter="";
	foreach($arrConverted as $strParameter => $value)
	{ 
		break;
	}	//echo $strParameter."<br>";
	$strParameter = str_replace("%","=",$strParameter);
	$strParameter = base64_decode($strParameter);
	//echo $strParameter."<br>";

	$pieces = explode("&", $strParameter);
	$arrparams = array();
	foreach($pieces as $key => $value)
	{
		$tmp = explode("=",$value);
		$arrparams[$tmp[0]]= $tmp[1];
	}
	return $arrparams;	
	}
//E UR	

	/*A TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
	/*function getCountDownloadsAvailible($dirname,$filename)
	{
		// $dirname : Absolutpfad des Customer-Downloadverzeichnis
		// $filename : Name der Downloaddatei ohne das Präfix mit der Anzahl freier Downloads
		$dlcount = -1;
		$dir_handle = @opendir($dirname) or die("Unable to open ".$dirname);
		while ($file = readdir($dir_handle))
		{
		if (!is_dir($file))
		{
			$fparts = explode(';',$file);
			if ($fparts[1])
			{
			if ($fparts[1] == $filename)
			{
				$dlcount = intval($fparts[0]);
				break;
			}
			}				
		}

		}
		closedir($dir_handle);
	 
		
		return $dlcount;
	}*/
	/*E TS 22.10.2015: Downloads nur noch über die Datenbank zählen und händeln*/
		
//E UR 4.2.2010

		function getXMLresponse($status, $message, $Merchant_orderid, $PaynovaSecretKey)
		{
			$xml = '<?xml version="1.0" encoding="utf-8"?>'.
				 '<responsemessage>'.
				 '<status>'.$status.'</status>'.
				 '<statusmessage>'.$message.'</statusmessage>'.
				 '<neworderid>'.$Merchant_orderid.'</neworderid>'.
				 '<batchid></batchid>'.
				 '<checksum>'.md5($status.$message.$Merchant_orderid.$PaynovaSecretKey).'</checksum>'.
				 '</responsemessage>';

			return $xml;
		}
		
	function getAvaImg($ItemNo){
		$con = $this->dbconnect();
		if($con) {
			$sql = "SELECT itemInStockQuantity, itemShipmentStatus FROM ".DBToken."itemdata WHERE itemItemNumber = '".$ItemNo."'";
			$qry = @mysqli_query($con,$sql);
			$item = @mysqli_fetch_object($qry);
			
			if($item->itemShipmentStatus == -1) {
				$sql = "SELECT * from ".DBToken."availability WHERE avaMinQty<='".$item->itemInStockQuantity."' AND avaMaxQty>='".$item->itemInStockQuantity."'";
			} else {
				$sql = "SELECT * from ".DBToken."availability WHERE avaId='".$item->itemShipmentStatus."'";
			}
			$qry = @mysqli_query($con,$sql);
			$ava = @mysqli_fetch_object($qry);
			//image mit der ampel laden
			$im = imagecreatefromjpeg("images/ampel.jpg");
			//eine farbe erstellen
			$color = $ava->avaColor;
			$color = substr($color, 1);
			$r = hexdec(substr($color, 0, 2));
			$g = hexdec(substr($color, 2, 2));
			$b = hexdec(substr($color, 4, 2));
			$ypos = $ava->avaPos;
			$image_color = imagecolorallocate($im, $r, $g, $b);
			//neues image erstellen
			imagefilledellipse($im, 13, $ava->avaPos, 18, 18, $image_color);
			return $im;
		}
	}
	
// AB 31.12.2010	
	function getPrice($sid)
	{
		$con = $this->dbconnect();
		if($con)
		{
			$SQL = "SELECT * FROM ".DBToken."price WHERE prcItemNumber = '".$sid."' order by prcQuantityFrom";
			$qry = @mysqli_query($con,$SQL);
			$arr = array();
			while($arr[] = @mysqli_fetch_object($qry));
			$this->dbclose($con);
		}
		return $arr;
	}	

// AB 31.08.2011	
	function getVariant($ItemId, $lang)
	{
		$con = $this->dbconnect();
		if($con)
		{
		$var_arr = array();
		 // ArtikelNummer holen
		$SQL = "SELECT * FROM ".DBToken."itemdata	
				WHERE itemItemId='$ItemId' AND itemLanguageId='$lang' GROUP BY itemItemId";
		//echo $SQL."<br>";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$ItemNumber = $obj->itemItemNumber;
		mysqli_free_result($qry);
		//Hauptartikel bzw. angeklickter Artikel zuerst
		$var_arr[] = $obj;
		// VariantGruppe finden
		//Blödsinn bei einem Hauptartikel ist die ItemNumber die VariantGroup
		/*
		$SQL = "SELECT varVariantGroup FROM ".DBToken."item_to_variant 
				WHERE varItemNumber='$ItemNumber' GROUP BY varItemNumber";
		//echo $SQL."<br>";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_object($qry);
		$varGroupNr = ($obj->varVariantGroup)? $obj->varVariantGroup : $ItemNumber;
		mysqli_free_result($qry);
		*/
		//hole alle Varianten 
		//A TS 05.02.2013 nach Reihenfolge geordnet
		/*$SQL = "SELECT varItemNumber FROM ".DBToken."item_to_variant	
				WHERE	varVariantGroup='$varGroupNr' ORDER BY varVariantIdNo ASC";
		*/
		$SQL = "SELECT varItemNumber FROM ".DBToken."item_to_variant	
				WHERE	varVariantGroup='$ItemNumber' ORDER BY varVariantIdNo ASC";
		//echo $SQL."<br>";
		$qry = @mysqli_query($con,$SQL);
		if(mysqli_num_rows($qry) > 0)
		{
			while($row = @mysqli_fetch_object($qry))
			{ 
				//$var_arr[]= "itemItemNumber='".$row->varItemNumber."'";
				//echo $row->varItemNumber . "<br />";
				$SQL = "SELECT * FROM ".DBToken."itemdata	
					WHERE itemItemNumber = '" . $row->varItemNumber . "'";
				$vqry = @mysqli_query($con,$SQL);
				while($vrow = @mysqli_fetch_object($vqry))
				{
					$var_arr[]=$vrow;
				}
				mysqli_free_result($vqry);
			}
		 }
		$this->dbclose($con);
		}
		return $var_arr;
	}
	
	
	
	function getAttributes($ItemId, $lang)
	{
		$con = $this->dbconnect();
		$arr = array();
		if($con)
		{
		$SQL = "SELECT itemAttribute1, itemAttribute2, itemAttribute3 FROM ".DBToken."itemdata	
				WHERE itemItemId='$ItemId' AND itemLanguageId='$lang' GROUP BY itemItemId";
		$qry = @mysqli_query($con,$SQL);
		$obj = @mysqli_fetch_assoc($qry);	

		$attr_array = array();
		 if($obj) foreach($obj as $val){
			$arr = array();
			$SQL = "SELECT value FROM ".DBToken."attributes	
				WHERE name='$val'";
			$qry = @mysqli_query($con,$SQL);
			while($o = @mysqli_fetch_object($qry))
			{
				if($o->value) $arr[] = $o->value;
			}
			 if($arr) $attr_array[] = $arr;
		}
		}	
		return $attr_array;	
	}	

	function getCrossselling($ItemNo)
	 {
	 	$arr = array();
	 	$con = $this->dbconnect();
		 if($con)
		{
		$SQL = "SELECT crsCrossSelingItem FROM ".DBToken."crossselling	
				WHERE crsItemNumber='$ItemNo'";
		$qry = @mysqli_query($con,$SQL);

		while($r = @mysqli_fetch_assoc($qry))
			{
				if($r) $arr[] = $r;
			}
		}
		return	$arr;
	}
	
	function getUpselling($ItemNo)
	 {
	 	$arr = array();
	 	$con = $this->dbconnect();
		 if($con)
		{
		$SQL = "SELECT * FROM ".DBToken."upselling	
				WHERE upsObjectCount='$ItemNo'";
		$qry = @mysqli_query($con,$SQL);

		while($r = @mysqli_fetch_assoc($qry))
			{
				if($r) $arr[] = $r;
			}
		}
		return	$arr;
	}	

	function getArtNo($ItemNo)
	 {
	 $con = $this->dbconnect();
		 if($con)
		{
		$SQL = "SELECT itemItemNumber FROM ".DBToken."itemdata	
				WHERE itemItemId='$ItemNo' GROUP BY itemItemId";
		$qry = @mysqli_query($con,$SQL);
		$res = @mysqli_fetch_object($qry);
		}
		return	$res->itemItemNumber;
	}	
	
	 function getArticleDownloads($ItemNo, $lang)	
	 { 
		$con = $this->dbconnect();
		 if($con)
		{ $res_arr = array();
		$SQL = "SELECT title, fileName FROM ".DBToken."itemdownloads 
				WHERE itemNumber='$ItemNo' AND languageId='$lang'";
		$qry = @mysqli_query($con,$SQL);
		while($res = @mysqli_fetch_object($qry))
		{
		 $res_arr[] = $res;
		}
		}
		return	$res_arr;	
	 }	
	 
	function getBundles($ItemNo, $lang)	
	 { 
		$con = $this->dbconnect();
		 if($con)
		{ 
		 $bundleListe = array();
		$SQL = "SELECT * FROM ".DBToken."bundles as bund
				WHERE bund.bundleGroup='$ItemNo' AND bund.bundleLanguageId='$lang'";
		$qry = @mysqli_query($con,$SQL);
		while($res = @mysqli_fetch_assoc($qry))
		{
		 $bundleListe[] = $res;
		}
		
	foreach($bundleListe as $v){
		$SQL = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile FROM ".DBToken."itemdata 
				WHERE itemItemNumber='".$v['itemNumber']."' AND itemLanguageId='$lang'";
		$qry = @mysqli_query($con,$SQL);
		$res = @mysqli_fetch_assoc($qry);
		$res_arr[] = array_merge($v,$res);
		}	
		}
		return	$res_arr;	
	 } 
	//A TS 30.11.2012 itemID stat itemNo verwenden
	function getProductInfos($ItemID)
	 {
	 $con = $this->dbconnect();
		 if($con)
		{
		$SQL = "SELECT itemManufacturer, itemBrand, itemManufacturerProductCode, itemEAN_ISBN FROM ".DBToken."itemdata	
				WHERE itemItemId='".$ItemID."' GROUP BY itemItemId";
		$qry = @mysqli_query($con,$SQL);
		$res = @mysqli_fetch_object($qry);
		}
		return	$res;
	}	 
	
function getAction($ItemId, $lang)	
	{
		$con = $this->dbconnect();
		if($con)
		{
			//A TS 07.11.2012 Beginn- und Endzeit mit einrechnen!!!
			$SQL = "SELECT a.* FROM ".DBToken."itemdata as i
				RIGHT JOIN ".DBToken."action as a
				ON i.itemItemId = a.itemId
				WHERE i.itemIsAction=1 and i.itemItemId=$ItemId and i.itemLanguageId='$lang'";
			$qry = @mysqli_query($con,$SQL);
			$res = @mysqli_fetch_object($qry);
					$bh = substr($res->action_begintime,0,2);
					$bn = substr($res->action_begintime,3,2);
					$bs = substr($res->action_begintime,6,2);
					$bd = substr($res->action_begindate,0,2);
					$bm = substr($res->action_begindate,3,2);
					$by = substr($res->action_begindate,6,4);
					$begin =	mktime($bh, $bn, $bs, $bm, $bd, $by).'<br>';
					$eh = substr($res->action_endtime,0,2);
					$en = substr($res->action_endtime,3,2);
					$es = substr($res->action_endtime,6,2);
					$ed = substr($res->action_enddate,0,2);
					$em = substr($res->action_enddate,3,2);
					$ey = substr($res->action_enddate,6,4);
					$end =	mktime($eh, $en, $es, $em, $ed, $ey).'<br>';
					$actuel = mktime();
			if ($res)
			{
				if($actuel>=$end)
				{
					$SQL = "UPDATE ".DBToken."itemdata SET itemIsAction=0 WHERE itemItemId=$ItemId and itemLanguageId='$lang' LIMIT 1";
					$qry = @mysqli_query($con,$SQL);
					return '';
				}
				elseif ($actuel>=$begin)
				{
					return $res;
				}
			}
			else
			{
				return '';
			}
		}
	}
	 
	function getCentralText($ItemId, $lang)
	 {
	 $con = $this->dbconnect();
		 if($con)
		{
		$SQL = "SELECT itemCentralTextNr FROM ".DBToken."itemdata
			 		WHERE itemUseCentralText=1 and itemItemId=$ItemId and itemLanguageId='$lang'";
		$qry = @mysqli_query($con,$SQL) or die (mysqli_error($con));
		$res = @mysqli_fetch_object($qry);	
		if($res) 
		{
		 $nr = 'memoArticleText'.($res->itemCentralTextNr+1);
			$SQL = "SELECT memoArticleText FROM ".DBToken."itemcentraltext
					WHERE SettingName='$nr' and languageId='$lang'";
			$qry = @mysqli_query($con,$SQL) or die (mysqli_error($con));
			$res = @mysqli_fetch_object($qry);			 
		}			 
		}
		return	$res->memoArticleText;
	}	

	//A TS 21.06.2012
	function getMainItemId($ItemId)
	{
		//Diese Funktion ermittelt aus einer ItemId die ItemId des Haupt-Artikels,
		//wenn es sich bei dem Artikel um eine Variante handelt, ansonsten wird die
		//übergebene ItemId zurückgegeben
		$con = $this->dbconnect();
		if($con)
		{
			//Zunächst die ItemNumber aus dsbxx_itemdata ermitteln
			$sql = "SELECT itemItemNumber FROM " . DBToken . "itemdata WHERE itemItemId = '" . $ItemId . "'";
			$erg = @mysqli_query($con,$sql) or die (mysqli_error($con));
			$z = mysqli_fetch_assoc($erg);
			
			//Jetzt die varVariantGroup anhand von varItemNumber (= itemItemNumber) ermitteln
			//wird nichts gefunden, bedeutet das das der Artikel keine Varianten hat oder wir 
			//schon den Hauptartikel haben, $ItemId wieder zurückgeben und Ende
			$sql2 = "SELECT varVariantGroup FROM " . DBToken . "item_to_variant WHERE varItemNumber = '" . $z['itemItemNumber']. "' LIMIT 1";
			$erg2 = @mysqli_query($con,$sql2) or die (mysqli_error($con));
			if(mysqli_num_rows($erg2) == 1)
			{
				$v = mysqli_fetch_assoc($erg2);
				
				//Anhand der varVariantGroup die itemItemId aus dsbxx_itemdata ermitteln
				$sql3 = "SELECT itemItemId FROM " . DBToken . "itemdata WHERE itemItemNumber = '" . $v['varVariantGroup'] . "' LIMIT 1";
				$erg3 = @mysqli_query($con,$sql3) or die (mysqli_error($con));
				$i = mysqli_fetch_assoc($erg3);
				return $i['itemItemId'];
			}
			else
			{
				//Nichts gefunden, d. h. keine Varianten vorhanden
				return $ItemId;
			}
		
		}
		else
		{
			return $ItemId;
		}
	}
	
	function email_friendly($str)
	{
		//Wandelt Sonderzeichen in E-Mail-freundliches Quoted Printable um
		$erg = "";
		$asc = "";
		for($i = 0; $i < strlen($str); $i++)
		{
			$char = substr($str,$i,1);
			$code = ord($char);
			$asc .= $code . "|";
			if(($code >= 48 && $code <= 57) || ($code >= 65 && $code <= 90) || ($code >= 97 && $code <= 122) || $code == 32)
			{
				$erg .= $char;
			}
			else
			{
				$erg .= strtoupper("=" . dechex($code));
			}
		}
		return $erg;
	}
	
	//A TS 13.02.2013 Variantendarstellung anhand der itemID feststellen
	function get_varmode($itemId)
	{
		//Bei Fehlern immer 0, d. h. Darstellung als Icon zurückgeben
		$res = 0;
		$con = $this->dbconnect();
		if($con)
		{
			//1. Artikelnummer anhand der itemId ermitteln
			$sql = "SELECT itemItemNumber FROM " . DBToken . "itemdata WHERE itemItemId = '" . $itemId . "' LIMIT 1";
			$erg = mysqli_query($con,$sql);
			if(mysqli_errno($con) == 0)
			{
				if(mysqli_num_rows($erg) > 0)
				{
					$z = mysqli_fetch_assoc($erg);
					//2. Variantenmodus ermitteln
					$sql2 = "SELECT ShowAsDropDown FROM " . DBToken . "item_to_variant WHERE varVariantGroup = '" . $z['itemItemNumber'] . "' LIMIT 1";
					$erg2 = mysqli_query($con,$sql2);
					if(mysqli_errno($con) == 0)
					{
						if(mysqli_num_rows($erg2) > 0)
						{
							$v = mysqli_fetch_assoc($erg2);
							$res = $v['ShowAsDropDown'];
						}
					}
				}
			}
			mysqli_free_result($erg);
			mysqli_free_result($erg2);
		}
		return $res;
	}
}
?>
