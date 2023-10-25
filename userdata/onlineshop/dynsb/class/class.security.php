<?php
/*

file: class.security.php
Desc: Allows to create a secure login .

*/
// Set length of session to 60 minutes
define("SESSION_LENGTH", 60);

if(file_exists("class/class.db.php"))
{
  require_once("class/class.db.php");
  require_once("class/class.session.inc.php"); // fallback "php session" class
}
else
{
  if(file_exists("../class/class.db.php"))
  { 
    require_once("../class/class.db.php");
    require_once("../class/class.session.inc.php"); // fallback "php session" class
  }
  else
  {
    if(file_exists("../../class/class.db.php"))
    {
      require_once("../../class/class.db.php");
      require_once("../../class/class.session.inc.php"); // fallback "php session" class
    }
    else
    {
      require_once("../../../../class/class.db.php");
      require_once("../../../../class/class.session.inc.php"); // fallback "php session" class
    }
  }
}

class Security {

    var $__LogTableName;
    var $__StoreSessionTableName;
    var $__ExtraFieldNames;
    var $__FieldNameSess;
    var $__FieldNameUserIdNo;
    var $__FieldNameUserId;

    function __construct() {
        $this->__LogTableName = 'tbl_Logs';
        $this->__StoreSessionTableName = 'tbl_UserSession';
        $this->__ExtraFieldNames = '';
        $this->__FieldNameSess = 'sessId';
        $this->__FieldNameUserIdNo = 'userIdNo';
        $this->__FieldNameUser = 'userId';
    }

    function Log_TableName($strName) {
        $this->__LogTableName = $strName;
    }

    function StoreSession_TableName($strName) {
        $this->__StoreSessionTableName = $strName;
    }

    function FieldNames($strSess, $strUserIdNo, $strUserId) {
        $this->__FieldNameSess = $strSess;
        $this->__FieldNameUserIdNo = $strUserIdNo;
        $this->__FieldNameUserId = $strUserId;
    }

    function ExtraFieldNames($strArray) {
        $this->__ExtraFieldNames = $strArray;
    }

    /*
        Function Name: AddLog()
        Desc: Adds a new record to the log table. This allows tracking of
        who is logging in, and who is trying to 'hack' into certain areas
    */
    function AddLog($strLogData = '', $logFlg) {
        $dbVars = new dbVars;
        @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$svrConn->query("SET NAMES 'utf8'");
        // Connected to the database server OK
        if($svrConn) {
            mysqli_query($svrConn,"INSERT INTO {$this->__LogTableName} VALUES (0, '$strLogData', '$logFlg')");
            //mysqli_close($svrConn);
        } else {
            // Failed
            die("Couldn't write log data to the MySQL database.");
        }
    }


    /*
        Function Name: StoreSession()
        Paramaters: [ strUserId: The user id of the current user trying to login
        strUserName: The username of the person trying to login
        /intSec: The level of the current user trying to login. This is used if you want to
        /make people administrators, and members.
        strArray: This an array file which will allow other fields to be entered into
        the database]
        Desc: Stores the details of a new session with the parsed user credentials.
    */
    function StoreSession($strUserIdNo = 0, $strUserId = "", $strArray = "") {
        // start session
        $Session = new Session();
        
        $dbVars = new dbVars();
        @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
        $svrConn->query("SET NAMES 'utf8'");
        if($svrConn) {
                // clean up session table on another successfull login
                // before the session timeout ---> RK 22.10.2004
                $qryclean = @mysqli_query($svrConn,"DELETE FROM ".$this->__StoreSessionTableName." WHERE ".$this->__FieldNameUserId." = '".$strUserId."'");
                
                $FieldNames = explode(",", $this->__ExtraFieldNames);
                $FieldNameValue = explode(",", $strArray);
                $strQuery = "INSERT INTO {$this->__StoreSessionTableName} ";
                $strQuery .= "({$this->__FieldNameSess}, {$this->__FieldNameUserId}, {$this->__FieldNameUserIdNo} ";
                $i = 0;
                foreach($FieldNames as $name=>$value) {
                    if($value != '') {
                        $strQuery .= ", $value";
                        $i++;
                    }
                }
                $strQuery .= ") VALUES ('" . session_id() . "', '$strUserId', '$strUserIdNo'";
                $e = 0;
                foreach($FieldNameValue as $name=>$value) {
                    if($i > $e) {
                        $strQuery .= ", '$value'";
                        $e++;
                    }
                }
                $strQuery .= ")";
                $result = mysqli_query($svrConn,$strQuery);
                //mysqli_close($svrConn);
    
                if($result) {
                    return true;
                } else {
                    return false;
                }
        } else {
            // Couldnt connect to server
            return false;
        }
    }



    /*
        Function Name: IsLoggedIn()
        Paramaters: N/A
        Desc: Checks if the current user has a session id. If the session id
        is valid, then true is returned. If not false is returned.
    */
    public function IsLoggedIn() {
        // start session
        $Session = new Session();
        // Check that the session id is valid
        $dbVars = new dbVars();
        @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$svrConn->query("SET NAMES 'utf8'");
        if($svrConn) {
                $strQuery = "SELECT {$this->__FieldNameSess} FROM {$this->__StoreSessionTableName} ";
                $strQuery .= "WHERE {$this->__FieldNameSess} = '" . session_id() . "'";
                $results = @mysqli_query($svrConn,$strQuery);
                if($result = @mysqli_fetch_array($results)) {
                 	// update of table 'session' column 'lastAccess'
                 	if ($result) {
                        $strQuery = "UPDATE {$this->__StoreSessionTableName} SET sessLastAccess = (NOW() + 0) ";
                        $strQuery .= "WHERE {$this->__FieldNameSess} = '" . session_id() . "'";
                        $res = @mysqli_query($svrConn,$strQuery);
                    }
                    mysqli_close($svrConn);
                    return $result;
                } else {
                    return 0;
                }
            
        } else {
            return false;
        }
    }



    function deleteInvalidSessionLogs() {
    	$timeMax = time() - (60 * SESSION_LENGTH);
    	// info: nummer of online users : $mResult = @mysqli_query("select count(*) FROM {$this->__StoreSessionTableName} where unix_timestamp(lastAccess) >= '$timeMax' ");
    	// delete invalid session logs in table 'session'
    	$mResult = @mysqli_query($svrConn,"DELETE FROM {$this->__StoreSessionTableName} WHERE unix_timestamp(lastAccess) < '$timeMax' ");
    	return $mResult;
    }



    function GetData() {
        // start session
        $Session = new Session();
        $arrRet = array();
        $FieldNames = explode(",", $this->__ExtraFieldNames);
        // Check that the session id is valid
        $dbVars = new dbVars();
        @$svrConn = mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$svrConn->query("SET NAMES 'utf8'");
        if($svrConn) {
                $strQuery = "SELECT * FROM {$this->__StoreSessionTableName} ";
                $strQuery .= "WHERE {$this->__FieldNameSess} = '".session_id()."'";
                $results = @mysqli_query($svrConn,$strQuery);
                if($result = @mysqli_fetch_array($results)) {
                    $arrRet[] = $result[$this->__FieldNameUserIdNo];
                    $arrRet[] = $result[$this->__FieldNameUserId];
                    foreach($FieldNames as $name=>$value) {
                        if($value != '') $arrRet[] = $result[$value];
                    }
                }
                //mysqli_close($svrConn);
        
        }
        if(!is_array($arrRet)) {
            if($FieldNames[0] != '') {
                $s = sizeof($FieldNames);
            } else {
                $s = 0;
                $s = 3 + $s;
                for($i = 0; $i <= $s; $i++) {
                    $arrRet[] = '';
                }
            }
        }
        return $arrRet;
    }
}
?>
