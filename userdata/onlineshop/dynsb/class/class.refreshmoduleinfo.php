<?php
/*

    GS Module System - class.modulemanager.php
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/

require_once("../class/class.db.php");

class refreshModuleinfo {

    var $rmiDir = "";
    var $aModuleDirs = array();
    var $link;
    
    function __construct($path) {
        $dbc = new dbVars();
        $this->link = mysqli_connect($dbc->strServer,$dbc->strUser,$dbc->strPass,$dbc->strDb);
		$this->link->query("SET NAMES 'utf8'");
        $this->rmiDir = trim($path);
        $this->readAllDirs();
        $this->refresh();
    }

    function readAllDirs($addPath = "") {
        $dir = opendir($this->rmiDir.$addPath);
        while(false !== ($file = readdir($dir))) {
            if($file != "." && $file != "..") {
                if(is_dir($this->rmiDir.$addPath.$file)) {
                    $this->readAllDirs($addPath.$file."/");
                    array_push($this->aModuleDirs, $file);
                }
            }
        }
    }
    
    function refresh() 
    {
        
        $where = "";
        $and = " AND ";
        for($i = 0; $i < count($this->aModuleDirs); $i++) {
            if($i == (count($this->aModuleDirs)-1)) $and = "";
            $where .= "modFolder <> '".$this->aModuleDirs[$i]."'".$and;
        }
        $SQL = "DELETE FROM ".DBToken."moduleinfo WHERE ".$where;
        mysqli_query($this->link,$SQL);
    }
    
}

?>
