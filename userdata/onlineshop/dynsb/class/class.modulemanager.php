<?php
/*

    GS Module System - class.modulemanager.php
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/

require_once("../class/class.modulevalidator.php");
require_once("../class/class.db.php");

class moduleManager {

    var $mmDir = "";
    var $mmdocRoot = "";
    var $mmArrValidModules = array();
    var $mmStartIndex = null;
    var $mmTreeLevel = null;
    var $mmParentNode = "";
    var $mmWWWHost = "";
    var $mmEntryCounter = 0;
    var $mmMaxModuleLevel = 2;
    var $mmActModuleLevel = 1;
    var $mmLang = "";
    
    function __construct($wwwHost, $docRoot, $modDir, $startIndex, $treeLevel, $parentNode, $lang) {
        $this->mmDir = $modDir;
        $this->mmWWWHost = $wwwHost;
        $this->mmDocRoot = $docRoot;
        $this->mmStartIndex = $startIndex;
        $this->mmTreeLevel = $treeLevel;
        $this->mmParentNode = $parentNode;
        $this->mmLang = $lang;
        $this->readModuleDir();
    }

    function addCounter($val = 0) {
        $this->mmEntryCounter += intval($val);
    }
    
    function readModuleDir() {
        $path = $this->mmDir;
        $dir = opendir($path);

        while(false != ($file = readdir($dir))) 
        {
            if($file != "." && $file != "..") 
            {
                if(is_dir($path.$file)) 
                {
                    $mv = new moduleValidator($this->mmWWWHost, $path.$file, ($this->mmStartIndex + $this->mmEntryCounter), $this->mmTreeLevel, $this->mmParentNode, $this->mmLang);
                    if($mv->mvIsValidModule) 
                    {
                        array_push($this->mmArrValidModules, $mv->mvData);
                        echo $mv->mvData['modTreeStr'];
                        $this->storeModuleinfo($mv->mvData, $file);
                        $this->addCounter($mv->mvData['modEntryCount']);
                        if($mv->mvData['modHasSubModule'] == 1 && ($this->mmActModuleLevel < $this->mmMaxModuleLevel)) 
                        {
                            $submod = new moduleManager($this->mmWWWHost, $this->mmDocRoot, $mv->mvData['modSubModuleFolder'], ($this->mmStartIndex + $this->mmEntryCounter), ($this->mmTreeLevel + 1), $mv->mvData['modId'], $this->mmLang);
                            $this->addCounter($submod->mmEntryCounter);
                        }
                    }
                }
            }
        }
    }
        
    function storeModuleinfo($aModule, $modFolder) {
       
        $dbVars = new dbVars();
        $svrConn = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb) or die("<br />aborted: can´t connect to '".$dbVars->strServer."' mysql-server<br />");
		$svrConn->query("SET NAMES 'utf8'");
        $delqry = mysqli_query($svrConn,"DELETE FROM ".DBToken."moduleinfo WHERE modId = '".$aModule['modId']."'");
        $SQLchk = "SELECT * FROM ".DBToken."moduleinfo WHERE modId = '".$aModule['modId']."'";
        $qrychk = mysqli_query($svrConn,$SQLchk);
        $numchk = mysqli_num_rows($qrychk);
        if($numchk == 0) {
            $modParentId = 'moduleRoot';
            if($aModule['modHasSubModule'] == 0) $modParentId = $aModule['modParentNode'];
            if($modParentId == "n1014") $modParentId = 'moduleRoot';
            $SQLins = "INSERT INTO ".DBToken."moduleinfo (modId, modParentId, modName, modCaption, modVersion, modFolder, modChgUserIdNo, modChgApplicId, modChgHistoryFlg) VALUES ('".$aModule['modId']."', '".$modParentId."', '".$aModule['modName_lang']."', '".$aModule['modCaption_lang']."', '".$aModule['modVersion']."', '".$modFolder."', '1', '".addslashes(substr(strrchr($_SERVER["PHP_SELF"],"/"),1))."', '2')";
            $qryins = mysqli_query($svrConn,$SQLins);
        }
    }
    
}

?>
