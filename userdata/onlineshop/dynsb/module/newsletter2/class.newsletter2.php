<?php
/**
 * Created on 20.11.2007
 *
 *	@author Jan Reker
 *
 */

 #TODO
 # shopbuilder lang tags in newsletter2.php und newlsletter.php
 #
 #
 #

if(file_exists("dynsb/class/class.db.php")) {
  require_once("dynsb/class/class.db.php");
}
else
{
  if(file_exists("class/class.db.php")) {
    require_once("class/class.db.php");
  }
}

class newsletter2 
{

	var $addressId;
	var $address;
	var $format;
	/** Array with mailgroup ids */
	var $aMG;
	var $bDoubleOptIn;
	var $timestamp;
	var $activationCode;
	var $bMultipleMg;
	var $shopURL;
	var $senderAddress;

	var $actMailSubj;
	var $actMailBody;
	var $link;

	function __construct() 
  {
		global $shopURL;
		$this->shopURL = $shopURL;

		$this->addressId			= "";
		$this->address 				= "";
		$this->format					= "T";
		$this->aMG 						= "";
		$this->timestamp			= date("Y-m-d H:i:s");
		$this->activationCode = "";
		$this->bMultipleMg 		= "";
		$this->bDoubleOptIn 	= "";
		$this->senderAddress	= "";

		$this->actMailSubj		= "";
		$this->actMailBody		= "";

		$this->link = $this->dbConnect();
		$this->initSettings();

	}

	/**
	 * if user wants to sign in
	 *
	 * @param string $address Email-Address
	 * @param char $format H -> HTML, T-> Text
	 * @param array $aMG Array with mailgroupIDs
	 * @return int 1 if successful else negative values (see Errorcodes)
	 *
	 * 	Errorcodes:
	 *	-1 invalid email address
	 *	-2 no mailgroups selected or invalid mailgroup ids in array
	 *
	 */
	function signIn($address, $format, $aMG) 
  {

		//check/set address
		$this->address = strip_tags(stripslashes(trim($address)));
		if(!$this->checkAddress())
			return -1;

		//check/set format
		$format = strtoupper(strip_tags(stripslashes(trim($format))));
		if ($format == "H" || $format == "T")
			$this->format = $format;


		//check/set mailgroups
		if (is_array($aMG))
    {
			foreach ($aMG as $key => $val) 
      {
				//only check numeric values
				if (is_numeric($val))

					//get mailgroup
					$sql = "SELECT * FROM ".DBToken."nl_mailgroups WHERE nlmgIdNo = '$val'";
					$res	= @mysqli_query($this->link,$sql);
					$row	= @mysqli_fetch_array($res);

					//only if mailgroup id exists in database, put id into array
					if (is_array($row))
						$this->aMG[] = $val;
			}
		}
		if (empty($this->aMG))
			return -2;

		//INSERT
		//if address exists, only insert into mailgroup
		if ($this->getAddress()) 
    {
			$this->insAddr2Mg();
			return 1; //TODO: wirklich prüfen ob return stimmt
		}
		//else insert a new address
		else 
    {
			//insert adress
			if ($this->insAddress()) 
      {

	 			//if successfull, insert entries into addr2mg
	 			if ($this->getAddress()) 
         {
					$this->insAddr2Mg();

					if ($this->bDoubleOptIn)
						$this->sendActivationMail();
					return 1; //TODO: wirklich prüfen ob return stimmt
				}
			}
		}
	}

//A UR 1.2.2011
  // Hiermit wird der Shopbesteller direkt in die erste Mailgroup eintragen (, sofern Eintrag nicht schon vorhanden)
  function signIn2($address, $format) 
  {
 
      $this->address = $address;
  		if (!$this->getAddress()) 
      {
        $this->timestamp			= date("Y-m-d H:i:s");

        $this->format = "T";
		    $format = strtoupper(strip_tags(stripslashes(trim($format))));
    		if ($format == "HTML")
    			$this->format = "H";

  			$sql = "INSERT INTO ".DBToken."nl_addresses (nladAddress, nladFormat, nladActiveFlg, nladCreateDate) " .
  						 "     VALUES ('$address', '$this->format', '1', '$this->timestamp')";
    		$res = @mysqli_query($this->link,$sql);
        
      }      
 			return 1; 
  }
//E UR

	/**
	 * Sign out => Delete from nl_address and nl_addr2mg
	 * @param string $address Email Adress
	 * @return int 1 on success, negative values on failure
	 *
	 * Errorcodes:
	 * 	-1 Address doesn't exist
	 * 	-2 Deletion failed: dsb8_nl_addresses
	 * 	-3 Deletion failed: dsb8_nl_addr2mg
	 */
	function signOut($address) 
  {
		//check address
		$this->address = strip_tags(stripslashes(trim($address)));

		//if address in db, delete entries
		if($this->getAddress()) 
    {
			$sqlAddr = "DELETE FROM ".DBToken."nl_addresses WHERE nladIdNo = '$this->addressId'";
			$sqla2mg = "DELETE FROM ".DBToken."nl_addr2mg WHERE admgNladIdNo = '$this->addressId'";

			if (!@mysqli_query($this->link,$sqla2mg))
 				return -3;

			if (!@mysqli_query($this->link,$sqlAddr))
 				return -2;

			return 1;
		}
		else
			return -1;
	}

	/**
	 * Activate Address with activation code
	 * @param string $address Email-Adress
	 * @param sting $activationCode
	 * @return 1 on success, negative values on failure
	 *
	 * 	Errorcodes:
	 * 		-1 Address doesn't exist
	 * 		-2 Activation failure
	 */
	function activateAddress($address, $activationCode) 
  {
		//check/set address
		$this->address 	= strip_tags(stripslashes(trim($address)));
		$activationCode = strip_tags(stripslashes(trim($activationCode)));

		if(!$this->getAddress())
			return -1;

		$upd = "UPDATE ".DBToken."nl_addresses SET nladActiveFlg = '1', nladActivationDate = '$this->timestamp', nladActivationCode = ''" .
					 " WHERE nladIdNo = '$this->addressId' " .
					 "   AND nladActivationCode = '$activationCode'";
		@mysqli_query($this->link,$upd);
		$res = @mysqli_affected_rows($this->link);

		if ($res > 0)
			return 1;
		else
			return -2;
	}

	/**
	 * Check if valid mail address
	 *
	 */
	function checkAddress()
	{
   	$pattern = "^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$";
   	if (eregi($pattern, $this->address))
   		return true;
   	else
   		return false;
	}

	/**
	 * Transfer settings from DB to class
	 */
	function initSettings() 
  {
		$sql 	= "SELECT * FROM ".DBToken."nl_settings";
		$res	= @mysqli_query($this->link,$sql);
		$row	= @mysqli_fetch_array($res);

		$this->bDoubleOptIn 	= $row["nlseDoubleOptIn"];
		$this->bMultipleMg		= $row["nlseMultipleMg"];
		$this->senderAddress	= $row["nlseSenderAddress"];
		$this->actMailSubj		= $row["nlseActMailSubj"];
		$this->actMailBody		= $row["nlseActMailBody"];
	}

	/**
	 * Returns array with address data and sets $this->addressId on success
	 * else false
	 */
	function getAddress() 
  {
		$sql = "SELECT * FROM ".DBToken."nl_addresses WHERE nladAddress = '$this->address'";
		$res	= @mysqli_query($this->link,$sql);
		$row	= @mysqli_fetch_array($res);

		if (is_array($row)) {
			$this->addressId = $row["nladIdNo"];
			return $row;
		}
		else
			return false;
	}

	/**
	 * Get mailgroupid
	 * @return array|false
	 */
	function getMailgroupId($mgName) 
  {
		$sql = "SELECT * FROM ".DBToken."nl_mailgroups WHERE nlmgName = '$mgName'";
		$res	= @mysqli_query($this->link,$sql);

		if ($row	= @mysqli_fetch_array($res))
			return $row["nlmgIdNo"];
		else
			return false;
	}

	/**
	 * Get all mailgroups
	 * @return array|false
	 */
	function getMailgroups() 
  {
		$sql = "SELECT * FROM ".DBToken."nl_mailgroups ORDER BY nlmgName";
		$res	= @mysqli_query($this->link,$sql);

		$ret =  array();
		while ($row	= @mysqli_fetch_array($res)) 
    {
			$ret[$row["nlmgIdNo"]] = array("name" => $row["nlmgName"], "desc" => $row["nlmgDesc"]);
		}

		if (is_array($ret))
			return $ret;
		else
			return false;
	}

	/**
	 * Insert an address in dsb8_nl_address
	 * and an association in dsb8_nl_addr2mg
	 *
	 */
	function insAddress() 
  {
		if (empty($this->address) ||  empty($this->aMG)) return false;

		//Insert address with activation code
		if ($this->bDoubleOptIn) 
    {
			$this->getActivationCode();
			$sql = "INSERT INTO ".DBToken."nl_addresses (nladAddress, nladFormat, nladActiveFlg, nladActivationCode, nladCreateDate) " .
						 "     VALUES ('$this->address', '$this->format' ,'0', '$this->activationCode', '$this->timestamp')";
		}
		//Insert address without activation code
		else 
    {
			$sql = "INSERT INTO ".DBToken."nl_addresses (nladAddress, nladFormat, nladActiveFlg, nladCreateDate) " .
						 "     VALUES ('$this->address', '$this->format', '1', '$this->timestamp')";
		}
		$res = @mysqli_query($this->link,$sql);

		if ($res)
			return true;
		else
			return false;
	}

	/**
	 * insert entries in dsb8_nl_addr2mg
	 */
	function insAddr2Mg() 
  {

		$bRes = true;
		foreach ($this->aMG as $key => $val) 
    {
			$sql = "INSERT INTO ".DBToken."nl_addr2mg (admgNladIdNo, admgNlmgIdNo, admgChgDate) " .
					   "     VALUES ('$this->addressId', '$val', '$this->timestamp')";
			$res = @mysqli_query($this->link,$sql);
			$bRes = $bRes && (bool) $res;
		}

		if ($bRes)
			return true;
		else
			return false;
	}

	function sendActivationMail() 
  {
		if(file_exists("dynsb/class/class.db.php")) 
    {
			require_once("dynsb/class/class.mailservice.php");
		}
		else	
    {
			if(file_exists("class/class.db.php")) 
      {
				require_once("class/class.mailservice.php");
			}
		}

		$ms = new mailservice($this->senderAddress, $this->address, $this->actMailSubj, "");

		if ($this->format == "H") 
    {
			$ms->createHtmlHeader();
			$message = nl2br($this->actMailBody);
			$message .= "<br /><br />Code: $this->activationCode";
		}
		else 
    {
			$ms->createHeader();
			$message = $this->actMailBody;
			$message .= "\n\n"."Code: $this->activationCode";
		}

		$ms->setMessage($message);
		$ms->sendMail();
	}

	function getActivationCode() 
  {
		$this->activationCode = substr(md5($this->address.$this->timestamp), 0, 16);
	}

	function getDoubleOptIn() 
  {
		return $this->bDoubleOptIn;
	}

	function setDoubleOptIn($bDoubleOptIn) 
  {
		$this->bDoubleOptIn = $bDoubleOptIn;
	}

	function getMultipleMg() 
  {
		return $this->bMultipleMg;
	}


	/**
	 * //TODO: 	sollte man vielleicht in seperate Klasse auslagern
	 * 				, da auch in shoplog genutzt.
	 */
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
}
?>
