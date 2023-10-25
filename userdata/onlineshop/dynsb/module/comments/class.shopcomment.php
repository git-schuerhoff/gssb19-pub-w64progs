<?php
//-> comment im/export?
//-> Hilfreich / ja / nein

if(file_exists("dynsb/class/class.db.php")) {
  require_once("dynsb/class/class.db.php");
}
else
{
  if(file_exists("class/class.db.php")) {
    require_once("class/class.db.php");
  }
}

require_once 'class.shopcommentsettings.php';

class Shopcomment {

	/** ID of this comment*/
	var $idNo;

	/** Rating 0 to 5. 0 means "no rating"*/
	var $rating;

	/** Subject of comment */
	var $subject;

	/** the comment itself */
	var $body;

	/** date of comment. Format 2008-11-27 */
	var $date;

	/** ID of customer who made this comment */
	var $cusId;

	/** Only visible comments should be displayed in customer area*/
	var $bVisible;
	
	var $link;
	/** */
	#var subComment

	function __construct($id=null) {
		$this->link = $this->__dbConnect();

		if (isset($id))
			$this->__loadComment($id);
		else {
			//if new comment

			//set default visibility
			$oCS = new ShopcommentSettings();
			$this->bVisible = $oCS->getVisibilityDefault();
			
			//set date to now
			$this->date = date("Y-m-d H:i:s");
		}
	}
	
	function getIdNo() {
		return $this->idNo;
	}

	function getRating() {
		return $this->rating;
	}
	function setRating($rating) {
		$this->rating = $rating;
	}

	function getSubject() {
		return base64_decode($this->subject);
	}
	function setSubject($subject) {
		$this->subject = base64_encode(strip_tags($subject));
	}

 	/**
 	 * Get Body
 	 * @param $bWithBreaks Format new lines to <br />
 	 *
 	 */
	function getBody($bWithBreaks = false) {
		if ($bWithBreaks)
			return nl2br(base64_decode($this->body));
		return base64_decode($this->body);
	}
	function setBody($body) {
		$this->body = base64_encode(strip_tags($body));
	}

	function getCusId() {
		return $this->cusId;
	}
	function setCusId($cusId) {
		$this->cusId = $cusId;
	}

	function getVisible() {
		return $this->bVisible;
	}
	
	/**
	 * Set visible
	 * 
	 * @param $bVisible 0|1
	 * @return 
	 */
	function setVisible($bVisible) {
		$this->bVisible = $bVisible;
	}
	
	/**
	 * Get Date
	 * 
	 * @param int $format: 1=mm.dd.yyyy, 2=yyyy-mm-dd
	 * @return string date
	 */
	function getDate($format=NULL) {
		switch($format) {
			
			//deutsches Fomat
			case 1: return 	substr($this->date, 8, 2) . '.' 
										. substr($this->date, 5, 2) . '.' 
										. substr($this->date, 0, 4);
			break;

			//englisches Format
			case 2: return 	substr($this->date, 0, 10); break;
		}
		
		return $this->date;
	}

#################################################

	# __function() should mean "private"

	/**
	 * Load a comment from database
	 * @param int/array $id ID of comment OR array with database fields
	 */
	function __loadComment($id) {

		if (!is_array($id)) {
			$qry = "SELECT * FROM " . DBToken . "shopcomments WHERE itcoIdNo = '$id'";
			$res	= @mysqli_query($this->link,$qry);
			$row	= @mysqli_fetch_array($res);
		}
		else
			$row = $id;

		if (is_array($row)) {
			$this->idNo				= $row['itcoIdNo'];
			$this->rating 		= $row['itcoRating'];
			$this->subject		= $row['itcoSubject'];
			$this->body				= $row['itcoBody'];
			$this->date				= $row['itcoDate'];
			$this->cusId			= $row['itcoCusId'];
			$this->bVisible		= $row['itcoVisible'];
			//displayUserName...
		}
	}

	/**
	 * Inserts a new comment into the database oder updates an old one, if
	 * idNo is > 0
	 */
	function save() {
		//TODO: prüfen, ob autoInc in mySQL bei 0 anfängt
		if (isset($this->idNo) && $this->idNo > 0) {
			
			$qry =
				"UPDATE " . DBToken . "shopcomments " .
				"   SET itcoRating = '" . mysqli_real_escape_string($this->link,$this->rating)  . "'".
				"     , itcoSubject = '" . mysqli_real_escape_string($this->link,$this->subject)	 . "'".
				"     , itcoBody = '" . mysqli_real_escape_string($this->link,$this->body)	 . "'".
				"     , itcoDate = '" . mysqli_real_escape_string($this->link,$this->date)	 . "'".
				"     , itcoCusId = '" . mysqli_real_escape_string($this->link,$this->cusId) . "'".
				"     , itcoVisible = '" . mysqli_real_escape_string($this->link,$this->bVisible) . "'".
				" WHERE itcoIdNo = '" . mysqli_real_escape_string($this->link,$this->idNo) . "'"
			;
			if(@mysqli_query($this->link,$qry))
				return $this->idNo;
			else
				return false;
		}
		else {						
			/*$qry =
				"INSERT INTO " . DBToken . "shopcomments" .
				"   SET itcoRating = '" . mysqli_real_escape_string($this->link,$this->rating)  . "'".
				"     , itcoSubject = '" . mysqli_real_escape_string($this->link,$this->subject)	 . "'".
				"     , itcoBody = '" . mysqli_real_escape_string($this->link,strip_tags($this->body))	 . "'".
				"     , itcoDate = '" . mysqli_real_escape_string($this->link,$this->date)	 . "'".
				"     , itcoCusId = '" . mysqli_real_escape_string($this->link,$this->cusId) . "'".
				"     , itcoVisible = '" . mysqli_real_escape_string($this->link,$this->bVisible) . "'"
				;*/
			$qry =
				"INSERT INTO " . DBToken . "shopcomments" .
				"   SET itcoRating = '" . mysqli_real_escape_string($this->link,$this->rating)  . "'".
				"     , itcoSubject = '" . $this->subject	 . "'".
				"     , itcoBody = '" . $this->body	 . "'".
				"     , itcoDate = '" . mysqli_real_escape_string($this->link,$this->date)	 . "'".
				"     , itcoCusId = '" . mysqli_real_escape_string($this->link,$this->cusId) . "'".
				"     , itcoVisible = '" . mysqli_real_escape_string($this->link,$this->bVisible) . "'"
				;
			//die($qry);
			if(@mysqli_query($this->link,$qry))
				return @mysqli_insert_id($this->link);
			else
				return false;
		}
	}
	
	
	/**
	 * //TODO: 	sollte man vielleicht in seperate Klasse auslagern
	 * 				, da auch in shoplog und newsletter2 genutzt.
	 */
	function __dbConnect()  {
		$dbVars = new dbVars();

		$con = @mysqli_connect($dbVars->strServer, $dbVars->strUser, $dbVars->strPass, $dbVars->strDb);
		$con->query("SET NAMES 'utf8'");
		if($con) {
			return $con;
		}
		return false;
  }


  ####static####
	function __getComments($qry) {
		Shopcomment::__dbConnect();
		$aRet = array();
		$res = @mysqli_query($this->link,$qry);
		while ($row = mysqli_fetch_array($res)) {
			$aRet[] = new Shopcomment($row);
		}
		return $aRet;
	}
	
	/**
	 * Get all comments - also invisible ones
	 * @param $start=null
	 * @param $limit=null
	 * @param $order=null
	 * @return array Array with comment objects
	 */
	function getAllComments($start=null, $limit=null, $aFilter=null, $order=null) {
		
		if (isset($start) && isset($limit))
			$sqlLimit = " LIMIT $start, $limit ";
		else
			$sqlLimit = '';

		
		$sqlFCusId 		= '';
		$sqlFDate 		= '';
		$sqlFVisible 	= '';

		
		if (!empty($aFilter['cusId'])) {
			$sqlFCusId = "AND itcoCusId='" . mysqli_real_escape_string($this->link,trim($aFilter['cusId'])) . "' ";		
		}	
		if (!empty($aFilter['date'])) {
			$date = trim($aFilter['date']);
			$date = substr($date, 6, 4) . '-'. substr($date, 3, 2) . '-'. substr($date, 0, 2) . '%';
			$sqlFDate = "AND itcoDate LIKE '" . mysqli_real_escape_string($this->link,$date) . "' ";				
		}	
		if (is_numeric($aFilter['visible'])) {
			$sqlFVisible= "AND itcoVisible='" . mysqli_real_escape_string($this->link,trim($aFilter['visible'])) . "' ";		
		}	
		
		$qry = "SELECT * FROM " . DBToken . "shopcomments WHERE 1=1 $sqlFCusId $sqlFDate $sqlFVisible ORDER BY itcoDate DESC $sqlLimit";
		return Shopcomment::__getComments($qry);
	}
	 
	
	/**
	 * Get all VISIBLE comments 
	 */
	function getAllCommentsVisible() {
		$qry = "SELECT * FROM " . DBToken . "shopcomments WHERE itcoVisible='1' ORDER BY itcoDate DESC";
		return Shopcomment::__getComments($qry);
	}
	
	/**
	 * Get all Comments for an user with $cusId
	 * 
	 * @param $cusId
	 * @return array Array with Comment objects
	 */
	function getAllCommentsByUser($cusIdNo) {
		$qry = "SELECT * FROM " . DBToken . "shopcomments WHERE itcoCusId = '$cusIdNo' AND itcoVisible='1' ORDER BY itcoDate DESC";
		return Shopcomment::__getComments($qry);
	}

//	function getAvgRatingByItemNumber($itemNumber) {
//		$qry = "SELECT round(avg(itcoRating)*2)  FROM dsb9_itemcomments i WHERE itcoItemNumber = '$itemNumber' AND itcoRating > 0";
//		#return Comment::__getAllComments($qry);
//	}
	
	function getAvgRatingVisible($format='1') {
		// ... *2)/2 -> round to 0.5 (e.g. 2 > 2.5 > 3 > 3.5)
		$qry = "SELECT round(avg(itcoRating)*2)/2  FROM " . DBToken . "shopcomments i WHERE itcoRating > 0 AND itcoVisible='1'";
		
		Shopcomment::__dbConnect();
		$res = @mysqli_query($this->link,$qry);
		if ($row = mysqli_fetch_row($res)) { 
			switch ($format) {
				case 1: return str_replace('.', ',', $row[0]); break;
				case 2:
				default: return $row[0]; break;
			}
		}
	}
	
	/**
	 * Delete a comment
	 * 
	 * @param $idNo ID of comment
	 * @param $cusId=null customer id. if called from user area.
	 * @return boolen true|false
	 */
	function delete($idNo, $cusId=null) {
		if (empty($idNo))
			return false;
			
		$qry_cus = '';
		if (!empty($cusId))
			$qry_cus = " AND itcoCusId = '$cusId'";
		
		$qry = "DELETE FROM " . DBToken . "shopcomments WHERE itcoIdNo = '$idNo' $qry_cus";
		Shopcomment::__dbConnect();
		return @mysqli_query($this->link,$qry);
	}
	
} //CLASS
 
?>
