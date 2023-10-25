<?php
require_once 'class.dbconnect.php';

class CommentSettings {
	
	var $visibilityDefault;
	var $link;
	
	function __construct() {
		$oCon = new DbConnect();
		$this->link = $oCon->connect();
		$this->load();
	}
	
	/**
	 * Load comment settings
	 * @return boolean
	 */
	function load() {
		$qry = "SELECT itseVisDef FROM " . DBToken . "itemcomments_settings WHERE itseIdNo = 1";
		$res = @mysqli_query($this->link,$qry);
		if ($row = mysqli_fetch_row($res)) {
			$this->visibilityDefault = $row[0];
			return true;
		}
		return false;
	} 

	/**
	 * Save comment settings
	 * @return boolean
	 */
	function save() {
		$qry = "UPDATE " . DBToken . "itemcomments_settings SET itseVisDef='" . $this->visibilityDefault . "' WHERE itseIdNo=1";
		$b = @mysqli_query($this->link,$qry);
		$this->load();
		return $b;		
	}
	
//	/**
//	 * Switch default visibility. 
//	 * 0 -> 1 
//	 * 1 -> 0
//	 * @return visibility like getVisibilityDefault()
//	 */
//	function switchVisibilityDefault() {
//		if ($this->visibilityDefault > 0)
//			$this->visibilityDefault = 0;
//		else
//			$this->visibilityDefault = 1;
//		
//		$this->save();
//		return $this->getVisibilityDefault();
//	}
	
	function getVisibilityDefault() {
		return $this->visibilityDefault;
	}
	function setVisibilityDefault($visibilityDefault) {
		$this->visibilityDefault = $visibilityDefault;
	}
}
?>