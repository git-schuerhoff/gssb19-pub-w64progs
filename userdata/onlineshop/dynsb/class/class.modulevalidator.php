<?php
/*

    GS Module System - class.modulevalidator.php
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/

class moduleValidator {

    var $mvDir = "";
    var $mvName = "";
    var $mvIsValidModule = false;
    var $mvErrorLevel = 0;
    var $mvStartIndex = null;
    var $mvTreeLevel = null;
    var $mvParentNode = "";
    var $mvWWWHost = "";
    var $mvLang = "";
    var $mvData = array();
    
    function __construct($mmWWWHost, $mvDir, $mmStartIndex, $mmTreeLevel, $mmParentNode, $mmLang) {
        $this->mvWWWHost = $mmWWWHost;
        $this->mvDir = $mvDir;
        $this->mvStartIndex = $mmStartIndex;
        $this->mvTreeLevel = $mmTreeLevel;
        $this->mvParentNode = $mmParentNode;
        $this->mvLang = $mmLang;
        $this->checkModuleFiles();
        if($this->mvErrorLevel == 0) $this->readConfig();
        if($this->mvErrorLevel == 0) $this->mvIsValidModule = true;
    }

    function readConfig() {
        require_once($this->mvDir."/mod.".$this->mvName.".config.php");
        if(intval(isset($modIsBoundFlg)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modDomain)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modSetup)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modId)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modParentNode)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modVersion)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modName_lang)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modCaption_lang)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modIsLeaf)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modHasSubModule)) == 0) {
            $this->mvErrorLevel++;
        } else {
            if(intval(isset($modSubModuleFolder)) == 0) $this->mvErrorLevel++;
        }
        if(intval(isset($modEntryCount)) == 0) $this->mvErrorLevel++;
        if(intval(isset($modTreeStr)) == 0) $this->mvErrorLevel++;
        // domain limitation for this module?
        if($this->mvErrorLevel == 0 && $modIsBoundFlg == 1) $this->checkBoundDomain($modDomain);
        if($this->mvErrorLevel == 0 && $modSetup == 1) $this->startSetup();
        if($this->mvErrorLevel == 0) {
            $this->mvData = array(  'modIsBoundFlg' => $modIsBoundFlg,
                                    'modDomain' => $modDomain,
                                    'modSetup' => $modSetup,
                                    'modId' => $modId,
                                    'modParentNode' => $modParentNode,
                                    'modVersion' => $modVersion,
                                    'modName_lang' => $modName_lang,
                                    'modCaption_lang' => $modCaption_lang,
                                    'modIsLeaf' => $modIsLeaf,
                                    'modHasSubModule' => $modHasSubModule,
                                    'modSubModuleFolder' => $modSubModuleFolder,
                                    'modEntryCount' => $modEntryCount,
                                    'modTreeStr' => $modTreeStr );
        }
    }

    function startSetup() {
        require_once($this->mvDir."/mod.".$this->mvName.".setup.php");
        if($setupError > 0) $this->mvErrorLevel++;
    }
    
    function checkModuleFiles() {
        $path = $this->mvDir."/";
        $complete = 0;
        $dir = opendir($path);
        
        $tmppath = substr($path, 0, strlen($path)-1);
        $aModName = explode("/", $tmppath);
        $modname = $aModName[count($aModName)-1];
                
        while(false !== ($file = readdir($dir))) {
            if($file != "." && $file != "..") {
                if(is_file($path.$file)) {
                    $afile = explode(".", $file);
                    if($modname == $afile[1]) $this->mvName = $afile[1];
                    if($file == "mod.".$afile[1].".config.php") $complete++;
                    if($file == "mod.".$afile[1].".setup.php") $complete++;
                    if($file == "mod.".$afile[1].".run.php") $complete++;
                }
            }
        }
        if($complete != 3) $this->mvErrorLevel++;
    }

    function checkBoundDomain($domain) {
        if(trim(strtolower($domain)) != trim(strtolower($this->mvWWWHost))) $this->mvErrorLevel++;
    }
}

?>
