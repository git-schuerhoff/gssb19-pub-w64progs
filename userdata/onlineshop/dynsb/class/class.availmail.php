<?php
/*
    class availmail - klasse zum eintragen von email-addressen und versenden von verfuegbarkeits-emails
    Autor: SES 
*/

if(file_exists("dynsb/class/class.db.php"))
{
  require_once("dynsb/class/class.db.php");
}
else
{
  if(file_exists("class/class.db.php"))
  {
    require_once("class/class.db.php");
  }
  else
  {
	require_once("class.db.php");
  }	
}

if(file_exists("dynsb/class/class.mailservice.php"))
{
  require_once("dynsb/class/class.mailservice.php");
}
else
{
  if(file_exists("class/class.mailservice.php"))
  {
    require_once("class/class.mailservice.php");
  }
  else
  {
	require_once("class.mailservice.php");
  }
}

class availmail {

    var $avSenderAddress;
    var $avMailSubject;
    var $avMailBody;
    
    function __construct()
    {
        $this->avSenderAddress = '';
        $this->avMailSubject = '';
        $this->avMailBody= ''; 
        
        $this->getsettings();   
    }
    
    function insertAvailEmail($mail, $item)
    {
        if($mail != '' && $item != '' && $this->check_email($mail))
        {
            $dbVars = new dbVars();
            $link = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die();
			$link->query("SET NAMES 'utf8'");
            $strQry = "INSERT INTO ".DBToken."availmail(availmail, availitem) VALUES('".$mail."', '".$item."');";
            mysqli_query($link,$strQry);
            mysqli_close($link);
        }  
    }
    
    function deleteAvailEmail($mail, $item)
    {
        $dbVars = new dbVars();
        $link = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die();
		$link->query("SET NAMES 'utf8'");
        $strQry = "DELETE FROM ".DBToken."availmail WHERE availmail = '".$mail."' AND availitem = '".$item."';";
        mysqli_query($link,$strQry);
        mysqli_close($link);
       
    }
    
    function check_email($email) {
        if(!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
            return false;
        }
        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        for ($i = 0; $i < sizeof($local_array); $i++) {
            if(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
                return false;
            }
        }
        if(!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
            $domain_array = explode(".", $email_array[1]);
            if(sizeof($domain_array) < 2) {
                return false;
            }
            for($i = 0; $i < sizeof($domain_array); $i++) {
                if(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                    return false;
                }
            }
        }
        return true;
    }
    
    function getsettings()
    {
        $dbVars = new dbVars();
        $link = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die();
		$link->query("SET NAMES 'utf8'");
        $qry =  " SELECT * FROM ".DBToken."availmail_settings WHERE avSettingsNo = 1";
        $ret = mysqli_query($link,$qry);
        $obj = mysqli_fetch_object($ret);
        if($obj)
        {
            $this->avSenderAddress = $obj->avSenderAddress;
            $this->avMailSubject   = $obj->avMailSubject;
            $this->avMailBody      = $obj->avMailBody;
        }
        mysqli_close($link);   
    }
    
    function sendavailmail($item, $lang)
    {
        $dbVars = new dbVars();
        $link = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die();
		$link->query("SET NAMES 'utf8'");
        $qry =  " SELECT * FROM ".DBToken."availmail av LEFT JOIN ".DBToken."itemdata id ON id.itemItemNumber = av.availitem";
        $qry .= " WHERE id.itemLanguageId = '".$lang."' AND av.availitem = '".$item."'";
        $ret = mysqli_query($link,$qry);
        while($obj = mysqli_fetch_object($ret))
        {
            $mail = $obj->availmail;
            if($this->check_email($mail)) 
            {
                $pattern = array('{i}', '{d}');
                $replacement = array($obj->itemItemNumber, $obj->itemItemDescription);
                $subject = str_replace($pattern, $replacement, $this->avMailSubject);
                $body = str_replace($pattern, $replacement, $this->avMailBody);
                $ms = new mailservice($this->avSenderAddress, $mail, $subject, $body);
                $ms->createHeader();
				if($ms->sendMail())
                {
                    $this->deleteAvailEmail($mail, $item);   
                }
            }
        }    
    }  

}

?>
