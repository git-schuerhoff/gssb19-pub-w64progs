<?php
if(file_exists("class.db.php")) {
	require_once("class.db.php");
}

class BestsellerProductgroup {

	var $idProductgroup;
	var $langId;

	//settings
	var $setBestsellerPgCount;
	var $setBestsellerPgUpdate;
	var $useOrdOptBestsellerPg;

	function __construct($idProductgroup, $langId) {

		if (empty($idProductgroup))
			return false;

		$this->idProductgroup = $idProductgroup;
		$this->langId = $langId;

		$this->__dbConnect();
		$this->__update();
	}

	/**
	 * Get bestselling products for this prodgroup
	 *
	 * @return array array with DB Results or empty array
	 */
	function getProducts() {
    $link = $this->__dbConnect();

    $aRet = array();
		//all bestselling products
		$qry = "SELECT i.*, p.*
							FROM " . DBToken . "bestsellerpg d
							JOIN " . DBToken . "itemdata i ON d.bepgItemIdNo = i.itemItemNumber AND i.itemProductGroupIdNo = d.bepgPgIdNo
							JOIN " . DBToken . "price p    ON d.bepgItemIdNo = p.prcItemNumber
							WHERE d.bepgPgIdNo = '" . $this->idProductgroup . "'
							AND i.itemLanguageId = '" . $this->langId . "'
							GROUP BY d.bepgItemIdNo							
							ORDER BY d.bepgRank";
		$res = mysqli_query($link,$qry);

		while($row = mysqli_fetch_object($res)) {
			$aRet[] = $row;
		}

		return $aRet;
	}

	/**
	 * Checks wether an update for product group bestsellers is necessary
	 * and calculates new values
	 *
	 */
	function __update() {
		$link = $this->__dbConnect();
		//get settings
		$qry = "SELECT setBestsellerPgCount, setBestsellerPgUpdate, useOrdOptBestsellerPg
		          FROM " . DBToken . "settings";

		$res = @mysqli_query($link,$qry);
                $row = @mysqli_fetch_assoc($res);
		if($row) {

			//settings
			$this->setBestsellerPgCount 	= $row['setBestsellerPgCount'];
			$this->setBestsellerPgUpdate	= $row['setBestsellerPgUpdate'];
			$this->useOrdOptBestsellerPg	= $row['useOrdOptBestsellerPg'];

			$nowDay = date('Y-m-d 00:00:00');
			$now = date('Y-m-d H:i:s');

			//if last update wasn't today, do it now
			if ($this->setBestsellerPgUpdate < $nowDay) {

				//update settings
				$updSet = "UPDATE " . DBToken . "settings SET setBestsellerPgUpdate='$nowDay'";
				@mysqli_query($link,$updSet);

				//get all product groups, to update the bestseller
				$qryAllPg = "SELECT itemProductGroupIdNo
                       FROM " . DBToken . "itemdata
                      GROUP BY itemProductGroupIdNo";
        $resAllPg = @mysqli_query($link,$qryAllPg);

				while($rowAllPg = mysqli_fetch_row($resAllPg)) {

  				//get bestselling items from order table
  				$qryItems = "SELECT i.itemItemNumber, SUM(op.ordpQty) qty
  											FROM " . DBToken . "itemdata i
  											INNER JOIN ".DBToken."orderpos op ON i.itemItemNumber = op.ordpItemId
  											WHERE i.itemProductGroupIdNo = '" . $rowAllPg[0] . "'
  											GROUP BY op.ordpItemId
  											ORDER BY qty DESC
  											LIMIT " . $this->setBestsellerPgCount;
  				$resItems = @mysqli_query($link,$qryItems);

  				//delete old entries
  				$del = "DELETE FROM " . DBToken . "bestsellerpg WHERE bepgPgIdNo='" . $rowAllPg[0] . "'";
  				@mysqli_query($link,$del);

  				//insert bestselling items in BESTSELLERPG-table
  				$i = 0;
  				while($rowItems = @mysqli_fetch_assoc($resItems)) {
  					$i++;
  					$qryIns = "INSERT INTO " . DBToken . "bestsellerpg (bepgPgIdNo, bepgItemIdNo, bepgRank, bepgTimestamp)
  												VALUES ('" . $rowAllPg[0] . "','" . $rowItems['itemItemNumber'] . "', '$i', '$now')";
  					@mysqli_query($link,$qryIns);
  				}

				}
			}
		}
	}

	/**
	 * //TODO: 	sollte man vielleicht in seperate Klasse auslagern
	 * 				, da auch in shoplog, comments und newsletter2 genutzt.
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
} //END CLASS
?>
