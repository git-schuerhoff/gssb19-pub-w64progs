<?php


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
}

/**
 * Provides functions to generate page statistics
 *
 * (c) 2005 GS Software AG
 * @author Jan Reker / GS Software AG
 * @version 0.1
 *
 *
 *
 *
 */
class pageStatistics{

	var $lang;

	var $dbServer;
	var $dbUser;
	var $dbPass;
	var $dbDatabase;
	var $dbLink;
	var $diagramName;

	/** Start date to display statistics */
	var $statStartDate;
	var $statEndDate;

	/**array with available array sizes*/
	var $diagramSizes;

    var $xsize;
    var $ysize;

    var $layout;
    var $barlayout;


    /**array with available view modes*/
    var $viewmodes;

    /** how many rows will be displayed*/
    var $limit;

    var $gradientToogleStep;
    var $dataTextOffset;

    var $userDetailMode;
    var $userDetailModes;



	/**
	 * Constructor
	 *
	 */
	function __construct(){
 	//initialise the variables with default values

 		$this->lang="deu";
 		$this->diagramName="";

		$this->userDetailMode=0;
		$this->userDetailModes = array();
		if(defined('L_dynsb_statUserBrowserAndVersion')) $this->userDetailModes[] = L_dynsb_statUserBrowserAndVersion;
		if(defined('L_dynsb_statUserBrowserOnly')) $this->userDetailModes[] = L_dynsb_statUserBrowserOnly;

		$dbVars=new dbVars();
		$this->dbServer = $dbVars->strServer;
		$this->dbUser = $dbVars->strUser;
		$this->dbPass = $dbVars->strPass;
		$this->dbDatabase = $dbVars->strDb;

				$this->statStartDate = date("Ymd")."000000";
				$this->statEndDate = date("Ymd")."235959";

  			  $this->xsize = 400;
    		  $this->ysize = 100;

    		  $this->layout=0;
    		  $this->barlayout=0;

			  //initialise the diagram sizes
    		  $this->diagramSizes = array();
    		  $this->diagramSizes[]= "400 x 100";
    		  $this->diagramSizes[]= "500 x 180";
    		  $this->diagramSizes[]= "630 x 240";
    		  $this->diagramSizes[]= "630 x 630";

    		  //initialise the view modes
    		  $this->viewmodes = array();
    		  $this->viewmodes[] = "10";
    		  $this->viewmodes[] = "20";
 			  $this->viewmodes[] = "50";

 			  $this->limit=10;
 			  $this->gradientToogleStep = 2;
       		  $this->dataTextOffset = 1;
		 }



	/**
	 * Create mySQL Connection
	 *
	 */
	function dbConnectionCreate()
	{

		$link = @mysqli_connect($this->dbServer, $this->dbUser, $this->dbPass, $this->dbDatabase)
					or die("<br />aborted:<br /> can´t connect to MySQL at '$this->dbServer' ( user='$this->dbUser' )<br />");
		$link->query("SET NAMES 'utf8'");

		
	    //Set the db link (handler)
	    $this->dbLink=$link;
	    return $link;
	}


	/**
	 * Close DB-Connection
	 *
	 */
	 function dbClose(){
	 	mysqli_close($this->dbLink);
	 }



	/**
	 * Set the variables to create a database connection
	 *
	 * @param string $dbServer Address of the Server
	 * @param string $dbUser Username
	 * @param string $dbPass Passwort
	 * @param string $dbDatabse Name of the database to use
	 */
	function setDbVariables ($dbServer, $dbUser, $dbPass, $dbDatabase)
		{
			$this->dbServer=$dbServer;
			$this->dbUser=$dbUser;
			$this->dbPass=$dbPass;
			$this->dbDatabase=$dbDatabase;

		}

	/**
	 * Set the variable statStartDate and formats it to
	 * yyyymmddhhmmss
	 *
	 * @param statStartDate
	 */
	function setStatStartDate($ssd){

		if (isset($ssd) || trim($ssd)!="" || trim($ssd)!=0)
		{
			$aTmp = explode(".", $ssd);
	        $ssd=$aTmp[2].$aTmp[1].$aTmp[0]."000000";
			$this->statStartDate=$ssd;
		}
	}

	/**
	 * Set the variable statEndDate and formats it to
	 * yyyymmddhhmmss
	 *
	 * @param StatEndDate
	 */
	function setStatEndDate($sed){

		if (isset($sed) || trim($sed)!="" || trim($sed)!=0)
		{
			$aTmp = explode(".", $sed);
	        $sed=$aTmp[2].$aTmp[1].$aTmp[0]."235959";
			$this->statEndDate=$sed;
		}
	}

	/**
	 * Set Layout
	 *
	 * - 0 horizontal
	 * - 1 vertical
	 *
	 * @param int $l
	 */
	function setLayout($l){
		$this->layout=$l;

		if ($l > 1 || $l==null){
			$this->layout=0;
		}
	}

	/**
	 * Set Layout
	 *
	 * - 0 simple shadows
	 * - 1 gradient
	 * default: 0
	 *
	 * @param int $bl
	 */
	function setBarlayout($bl)
     {
      if ($bl > 1 || $bl==null){
			$this->barlayout=0;
		}else
		{
			$this->barlayout=$bl;

		}
     }


     /** Set $lang. If parameter empty language will be set to "deu"
      *
      * @param string language abbreviation ("deu", "eng", ...)
      */
     function setLang($lang){
     $lang=trim($lang);
     if (!isset($lang) || strlen(trim($lang)) == 0)
		{
    	 $this->lang = "deu";
		}
	 else
  		{
		$this->lang = $lang;
		}
     }



	/**
	 * Gets page visits
	 *
	 * @return ResultSet Returns MySQL-Resultset
	 */
	function queryGetPageVisits()
		{
      
			//Create Connection
			$this->dbConnectionCreate();

			//Query to select PageViews, replace null by &nbsp;

			$query= "SELECT p.monPageID, p.monPageName, count(*) AS monPageVisits, " .
					"		IFNULL(f2t.title,'&nbsp;') AS monPageTitle " .

					"  FROM ".DBToken."monitorpageviews p " .
			     "LEFT JOIN ".DBToken."monitorfilenametotitle f2t ".

			        "    ON (f2t.filename = p.monPageName) ".
					" WHERE monPageVisitTimestamp >= $this->statStartDate ".
					"   AND monPageVisitTimestamp <= $this->statEndDate ".

					" GROUP BY monPageName ".
					" ORDER BY monPageVisits DESC ".
					";"
					//" LIMIT 0,$this->limit;"
					;




			//Get resultSet
			$resultSet = @mysqli_query($this->dbLink,$query);

			 //set limit to count of rows in db
			$this->limit= mysqli_num_rows($resultSet);

			return $resultSet;
			}






	 /**
	  * Inserts a row into the table "dsb7_monitorpageviews"
	  *
	  * @param string $name filename of the page (path will be cut)
	  * @param string $title Title of the page
	  * @param string $applicID ?id of the script, that inserted the data
	  *
	  * @return int 0:failure, 1:submission ok
	  *
	  */
	 function querySetPageVisits($name,$applicID="class.pagestatistics.php"){

			//cut the path
			$fn=explode("/",$name);
			$name=$fn[(count($fn)-1)];

			//cut spaces
			$name=trim($name);


	 	    //Create Connection
			$this->dbConnectionCreate();

			//Query to select PageViews
			$query="INSERT INTO ".DBToken."monitorpageviews" .
					"           (monPageName, monPageApplicID)" .
					"    VALUES ('$name','$applicID');";

			$resultSet = @mysqli_query($this->dbLink,$query);
			return $resultSet;
	        }






	/**
	 * Inserts the user data extracted from HTTP_USER_AGENT and HTTP_REFERER into
	 * the database
	 *
	 * @param $sid Session ID
	 * @param $http_user_agent optional
	 * @param $http_referer optional
	 *
	 */
	function querySetUserDetails($sid,$http_user_agent="", $http_referer="")
			{
			
			//Create Connection
			$this->dbConnectionCreate();

			//Delete SESSIONS from MonitorSessionTable that are older than 180min
			$del_query= "DELETE FROM ".DBToken."monitorsessions " .
					    " 	   WHERE monSessionTimestamp <  " .
					    "            (SELECT DATE_SUB(current_timestamp() , INTERVAL 180 MINUTE));"
						;

		    mysqli_query($this->dbLink,$del_query);



			//Check if session already exists
			$query="SELECT monSessionShop " .
					" FROM ".DBToken."monitorsessions " .
					"WHERE monSessionShop='$sid';"
					;
			//query
			$resultSet = @mysqli_query($this->dbLink,$query);


			if($resultSet=="")
			{
				//TS 21.06.2017: die Variable $_SERVER['HTTP_REFERER'] existiert nur dann, wenn es auch einen
				//Referer (d. h. eine andere Webseite von der aus auf den Shop verlinkt wurde (z. B. google) gibt.
				//Bei einem direkten Aufruf des Shops über den Browser gibt es keinen Referer, dann schmeißt PHP eine
				//Notice und das wollen wir nicht.
				if(isset($_SERVER['HTTP_REFERER'])) {
					$url = str_replace('index.html', "", $_SERVER['HTTP_REFERER']);
				}
				$url = str_replace('start.php', "", $url);
				$url = str_replace('index.php', "", $url);

        die("<br /><br />Database don't exists. Please click on <a target='_top' href='".$url."dynsb/index.php'>".$url."dynsb/index.php</a>");
				
			}


			//if session doesn't exist, INSERT an new row of user details
			if (mysqli_num_rows($resultSet)==0)
			   {

			   		$ins_query = "INSERT INTO ".DBToken."monitorsessions" .
			   				"				  (monSessionShop)".
			   				"          VALUES ('$sid');";
			   		//execute_query
					mysqli_query($this->dbLink,$ins_query);


					 //if no USER_AGENT parameter set it
					 if (trim($http_user_agent)=="")
					 {
					    $http_user_agent=$_SERVER['HTTP_USER_AGENT'];
					 }

					  //if no REFERER parameter set it
					 if (trim($http_referer)=="")
					 {
					 	 //TS 21.06.2017: die Variable $_SERVER['HTTP_REFERER'] existiert nur dann, wenn es auch einen
						//Referer (d. h. eine andere Webseite von der aus auf den Shop verlinkt wurde (z. B. google) gibt.
						//Bei einem direkten Aufruf des Shops über den Browser gibt es keinen Referer, dann schmeißt PHP eine
						//Notice und das wollen wir nicht.
						if(isset($_SERVER['HTTP_REFERER'])) {
					    $http_referer=$_SERVER['HTTP_REFERER'];
					 	} else {
					 		$http_referer='Direct';
					 	}
					 }

					 //Get user info
					 //$browser=get_browser($http_user_agent);
				     //$browser=$this->php_get_browser($http_user_agent,$pathBrowseCap);
                    $browser = $this->parse_user_agent($http_user_agent);
						//var_dump($browser);

					 //Insert user info
					 $ins_query=
						"INSERT INTO ".DBToken."monitoruserdetails" .
						"(monUserReferer, " .
						" monUserBrowser," .
						" monUserVersion," .
						" monUserPlatform)" .
						"" .
						"VALUES" .
						"" .
						"('$http_referer', " .
						" '$browser[browser]'," .
						" '$browser[version]'," .
						" '$browser[platform]');"
						;

					//execute_query
					mysqli_query($this->dbLink,$ins_query);
					}


			}


	/**
	 * Gets the User Details: Browser, Hits and Percentage as ResultSet or Array
	 *
	 *
	 * @param bool ret_array optional, will return array instead of sql resultset
	 * @param int modus
	 *
	 * @return sqlObjekt monUserBrowser, monUserHits, monUserPercentage
	 */
	function queryGetUserDetails($modus="",$ret_array=FALSE)
  {
    
		$link = $this->dbConnectionCreate();

		//if no modus passed, use the one set in the class variable
		if ($modus!="")$this->userDetailMode=$modus;


		switch($this->userDetailMode)
		{
		case 0:
		//Percentage will directly calculated in the SQL Query.
		//This Query will show the browserversions
		$query ="  SELECT CONCAT(monUserBrowser, ' ' " .
				"	    , monUserVersion) AS monUserBrowser  " .
				"	    , COUNT(monUserID) AS monUserHits " .
				"		, ROUND((COUNT(monUserID) / ".
				"		   (SELECT COUNT(monUserID) " .
				" 				  FROM ".DBToken."monitoruserdetails f " .
				"				 WHERE f.monUserTimestamp >= '$this->statStartDate' " .
				"				   AND f.monUserTimestamp <= '$this->statEndDate' " .
				"				 ) * 100),2)" .
				"		            AS monUserPercentage ".

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= '$this->statStartDate' ".
				"     AND monUserTimestamp <= '$this->statEndDate' ".

				"GROUP BY monUserBrowser " .
				"		, monUserVersion " .

				"ORDER BY monUserHits DESC ".
				";";
		break;

		case 1:
		//only browsernames (without versions) are shown
		$query ="  SELECT monUserBrowser " .
				"	    , COUNT(*) AS monUserHits " .
				"		, ROUND((COUNT(monUserID) / ".
				"		       (SELECT COUNT(monUserID) " .
				" 				  FROM ".DBToken."monitoruserdetails f " .
				"				 WHERE f.monUserTimestamp >= '$this->statStartDate' " .
				"				   AND f.monUserTimestamp <= '$this->statEndDate' " .
				"				 ) * 100),2) " .
				"          AS monUserPercentage " .
				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= '$this->statStartDate' ".
				"     AND monUserTimestamp <= '$this->statEndDate' ".

				"GROUP BY monUserBrowser " .

				"ORDER BY monUserHits DESC " .
				";";
				//"   LIMIT 0,$this->limit;";
		break;
		  }
//echo $query;
 		//execute_query
		$res = mysqli_query($link,$query);


		$this->dbclose();



		if ($ret_array==TRUE)
			{
			   $data=array();
				while ($a=mysqli_fetch_array($res,MYSQLI_NUM))
				{
				 $data[]=$a;
				}

			return $data;

			}
		return $res;
	}


/**
 * Generate the statistics for the visitors of the page
 *
 * int $this->userDetailMode should be set to:
 *  - 0: by hours accumulated
 *  - 1: by day accumulated
 * 	- 2: by month accumulated
 * @return array(mixed) Array with values of the statistic
 */
	function queryGetVisitors()
	{
	 
	$this->dbConnectionCreate();

	//change available viewmodes

	$this->userDetailModes = array();
    $this->userDetailModes[] = L_dynsb_statByHour;
    $this->userDetailModes[] = L_dynsb_statByDay;
 	$this->userDetailModes[] = L_dynsb_statByMonth;


    $this->diagramName = L_dynsb_statView." ". $this->userDetailModes[$this->userDetailMode];

		$statStartDate = $this->statStartDate;
		$statEndDate   = $this->statEndDate;


		switch($this->userDetailMode)
		{
		case 0: //by hour accumulated
		$statStartDateLoop = "00";
		$statEndDateLoop   = "23";
		//$this->diagramName = L_dynsb_Result.": ". $this->userDetailModes[$this->userDetailMode];
		$cut="2";


		$query ="  SELECT DATE_FORMAT(d.monUserTimestamp,'%H') monUserId " .
				"		, DATE_FORMAT(d.monUserTimestamp,'%H') monUserTimestamp " .
				"		, count(*) AS monUserVisits " .
				"		, ROUND(count(*)/(SELECT count(monUserId) " .
				"					        FROM ".DBToken."monitoruserdetails x " .
				"						   WHERE monUserTimestamp >= $statStartDate" .
				"						     AND monUserTimestamp <= $statEndDate)*100,2) AS monUserVisitsPercentage ".

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUserTimestamp " .
#				"ORDER BY monUserTimestamp ";

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert
				"GROUP BY DATE_FORMAT(d.monUserTimestamp,'%H') " .
				"ORDER BY DATE_FORMAT(d.monUserTimestamp,'%H') ";
		break;


	    case 1: //by weekday. 0==Sunday, 1==Monday, etc.
		$statStartDateLoop = "0";
		$statEndDateLoop   = "6";
	//	$this->diagramName =  $this->userDetailModes[$this->userDetailMode];
		$cut=2;


		// "[...]+6 % 9)" is used to make Monday the first day shown
		$query ="  SELECT DATE_FORMAT(d.monUserTimestamp,'%w') monUserId " .
				"		, (DATE_FORMAT(d.monUserTimestamp,'%w')+6)%7 monUserTimestamp " .
				"		, count(*) AS monUserVisits " .
				"		, ROUND(count(*)/(SELECT count(monUserId) " .
				"						    FROM ".DBToken."monitoruserdetails x" .
				"						   WHERE monUserTimestamp >= $statStartDate" .
				"						    AND monUserTimestamp <= $statEndDate)*100,2) AS monUserVisitsPercentage " .

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUserTimestamp " .
#				"ORDER BY monUserTimestamp ";

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert				
				"GROUP BY (DATE_FORMAT(d.monUserTimestamp,'%w')+6)%7 " .
				"ORDER BY (DATE_FORMAT(d.monUserTimestamp,'%w')+6)%7 ";

		break;


		case 2: //by Month

		//to cut the day out of the range, because the whole month should
		//be analysed
		$statStartDate = substr($this->statStartDate,0,6)."00000000";
		$statEndDate   = substr($this->statEndDate  ,0,6)."31245959";
		$cut="3";
	//	$this->diagramName =  $this->userDetailModes[$this->userDetailMode];
 		$statStartDateLoop = "1";
		$statEndDateLoop   = "12";


		$query ="  SELECT DATE_FORMAT(monUserTimestamp,'%M') monUserId" .
				"		, DATE_FORMAT(monUserTimestamp,'%m') monUserTimestamp" .
				"		, count(*) AS monUserVisits" .
				"		, ROUND(count(*)/(SELECT count(monUserId) " .
				"					        FROM ".DBToken."monitoruserdetails x " .
				"						   WHERE monUserTimestamp >= $statStartDate" .
				"						     AND monUserTimestamp <= $statEndDate)*100,2) AS monUserVisitsPercentage ".


				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUserTimestamp " .
#				"ORDER BY monUserTimestamp "

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert	
				"GROUP BY DATE_FORMAT(monUserTimestamp,'%m') " .
				"ORDER BY DATE_FORMAT(monUserTimestamp,'%m') "
				;

		break;


		case 5: //by day
		$statStartDate = substr($statStartDate,0,8);
		$statEndDate   = substr($statEndDate  ,0,8);
		$statStartDateLoop = substr($statStartDate,0,8);
		$statEndDateLoop   = substr($statEndDate,0,8);
		$cut="4";

		$query ="  SELECT DATE_FORMAT(monUserTimestamp,'%e.%m') monUserId" .
				"		, DATE_FORMAT(monUserTimestamp,'%Y%m%d') monUserTimestamp" .
				"		, count(*) AS monUserVisits".

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUserTimestamp " .
#				"ORDER BY monUserTimestamp "

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert	
				"GROUP BY DATE_FORMAT(monUserTimestamp,'%Y%m%d') " .
				"ORDER BY DATE_FORMAT(monUserTimestamp,'%Y%m%d') "
				;
		break;





		case 3: //by hour for each day seperate
		$statStartDate = $this->statStartDate;
		$statEndDate   = $this->statEndDate  ;

		$query ="  SELECT DATE_FORMAT(d.monUserTimestamp,'%H') monUserId" .
				"		, DATE_FORMAT(d.monUserTimestamp,'%Y%m%e%H') monUserTimestamp" .
				"		, count(*) AS monUserVisits".

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUserTimestamp " .
#				"ORDER BY monUserTimestamp ";

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert					
				"GROUP BY DATE_FORMAT(d.monUserTimestamp,'%Y%m%e%H') " .
				"ORDER BY DATE_FORMAT(d.monUserTimestamp,'%Y%m%e%H') ";
		break;


		case 4: //by hour for each day seperate
		$query ="  SELECT DATE_FORMAT(d.monUserTimestamp,'%w') monUserId" .
				"		, DATE_FORMAT(d.monUserTimestamp,'%w') monUserTimestamp" .
				"		, count(*) AS monUserVisits".

				"	 FROM ".DBToken."monitoruserdetails d " .

				"   WHERE monUserTimestamp >= $statStartDate ".
				"     AND monUserTimestamp <= $statEndDate ".

#				"GROUP BY monUsertimeStamp " .
#				"ORDER BY monUsertimeStamp ";

# 15.05.2007: Änderungen, da sonst GROUP BY in MySQL 5 nicht richtig funktioniert					
				"GROUP BY DATE_FORMAT(d.monUserTimestamp,'%w') " .
				"ORDER BY DATE_FORMAT(d.monUserTimestamp,'%w') ";
		break;


		  }

 		//execute_query
#echo $query;
		$res = mysqli_query($this->dbLink,$query);


		//fill array also with dates, where no visitors looked at the shop
		$resData=array();

/*DEBUG*///	echo "[$statStartDateLoop] [$statEndDate]<br /><br />";

		//fetch first row
		$obj=mysqli_fetch_object($res);
	    $resTimestamp=$obj->monUserTimestamp;


		//add leading values without hits to array
		$i=0;
		while ($statStartDateLoop<$resTimestamp )
			   {
		       $resRowData=array();
		 	   $resRowData[]=substr($this->getRealName($statStartDateLoop),0,$cut);	 //)"]$statStartDateLoop";
		 	   $resRowData[]=$this->getRealName($statStartDateLoop);  			 //$obj->monUserVisitsName;
		 	   $resRowData[]="0";
		 	   $resRowData[]="0.00";

		 	   $resData[]=$resRowData;
		 	   $i++;

			switch($this->userDetailMode)
			{

			case 5:
	   	 	  $statStartDateLoop = $this->switchTempTime($this->userDetailMode,$statStartDateLoop,1);
			break;

			default:
		 	  $statStartDateLoop++;
			break;
			}
    	   }

		$resRowData=array();
		$resRowData[]=substr($this->getRealName($obj->monUserTimestamp),0,$cut); 			//$obj->monUserTimestamp;
		$resRowData[]=$this->getRealName($obj->monUserTimestamp);   //$obj->monUserVisitsName;
		$resRowData[]=$obj->monUserVisits;
		$resRowData[]=$obj->monUserVisitsPercentage;
		$resData[]=$resRowData;




		//fill array with values on times between the times
		//in the resultset which don't follow each other
		// [23]->100hits [25]->22hits, than generate [24] with 0hits

		$resTimestampOld=$resTimestamp;
		$tempTimeOld = $this->switchTempTime($this->userDetailMode,$resTimestampOld,1);

		while ($obj=mysqli_fetch_object($res))
		{

  		 $resTimestamp=$obj->monUserTimestamp;
 		 $tempTime = $this->switchTempTime($this->userDetailMode,$resTimestamp);
		 $i=1;

///*DEBUG*/echo "[resTimestampOld] $resTimestampOld  <br />" .
//		 	    "[tempTimeOld]     $tempTimeOld      <br /> " .
//
//		 	    "[resTimestamp]    $resTimestamp     <br />" .
//		 	    "[tempTime]        $tempTime         <br />------<br /> ";


		 while ($tempTime != $tempTimeOld)
		    {

///*DEBUG*/  echo " -[tempTime]      $tempTime    <br />" .
//		 	      " -[tempTimeOld]   $tempTimeOld <br /><br /> ";

		     $resRowData=array();
		 	 $resRowData[]= substr($this->getRealName($tempTimeOld),0,$cut); //"[$tempTimeOld]";
		 	 $resRowData[]= $this->getRealName($tempTimeOld);   //$tempTimeOld;
		 	 $resRowData[]="0";
		 	 $resRowData[]="0.00";

			 $i++;
			 $tempTimeOld = $this->switchTempTime($this->userDetailMode,$resTimestampOld,$i);
		 	 $resData[]=$resRowData;


			}

 		$resRowData=array();
		$resRowData[]=substr($this->getRealName($obj->monUserTimestamp),0,$cut); //$obj->monUserTimestamp;
		$resRowData[]=$this->getRealName($obj->monUserTimestamp);   //$obj->monUserVisitsName;
		$resRowData[]=$obj->monUserVisits;
		$resRowData[]=$obj->monUserVisitsPercentage;
		$resData[]=$resRowData;

		$resTimestampOld=$resTimestamp;
		$tempTimeOld = $this->switchTempTime($this->userDetailMode,$resTimestamp,1);
		}



	//=========================================
		//add leading values without hits to array
		$tempTimeEnd = $this->switchTempTime($this->userDetailMode,$statEndDateLoop,1);
		$i=1;

        //if date is empty, statStartDate is used
        if ($resTimestamp=="")
        {$resTimestamp=$this->switchTempTime($this->userDetailMode,$statStartDate,0);}
        $tempTime    = $this->switchTempTime($this->userDetailMode,$resTimestamp,$i);


//		echo  "[tempTime]		 $tempTime <br />" .
//		 	  "[tempTimeEnd]     $tempTimeEnd <br /> " .
//
//		 	  "[statEndDateLoop] $resTimestamp<br />" .
//		 	  "[resTimestamp]  	 $resTimestamp <br />------<br /> ";


		while ($tempTime<$tempTimeEnd)
			   {
			   $i++;
		       $resRowData=array();
		 	   $resRowData[]=substr($this->getRealName($tempTime),0,$cut);   //$tempTime."[";
		 	   $resRowData[]=$this->getRealName($tempTime);   //$tempTimeOld;
		 	   $resRowData[]="0";
		 	   $resRowData[]="0.00";

		 	   $tempTime =$this->switchTempTime($this->userDetailMode,$resTimestamp,$i);

		 	   $resData[]=$resRowData;

			   }


 //var_dump($resData );

/*DEBUG*/// echo "<br /><br />".str_replace("		","<br />",($query));
		$this->dbclose();

return $resData;
		return $res;
	}

	
	
	
function queryGetUserclicks() {
	$link = $this->dbConnectionCreate();
	$aRet = array();
		
    $this->userDetailModes = array();
    $this->userDetailModes[] = L_dynsb_monuserclicksduration;
    $this->userDetailModes[] = L_dynsb_monuserclickspageimpressions;
 
    $this->diagramName = L_dynsb_statView." ". $this->userDetailModes[$this->userDetailMode] . " ";
		
    $statStartDate = substr($this->statStartDate, 0, 8)."000000";
		$statEndDate   = substr($this->statEndDate  , 0, 8)."235959";
		
		
	switch($this->userDetailMode) {
		
    //Verweildauer der Besucher in Minuten. Zusätzlich wird die durchschnittliche Verweildauer
    //angegeben
    case 0:
    
      //Durchschnitt
  		$qryAvg = "SELECT ROUND(AVG(ROUND((UNIX_TIMESTAMP(moucDatetimeLast) - UNIX_TIMESTAMP(moucDatetimeFirst))/60)))
      , SQRT(VARIANCE(ROUND((UNIX_TIMESTAMP(moucDatetimeLast) - UNIX_TIMESTAMP(moucDatetimeFirst))/60))), COUNT(*)
      						FROM ".DBToken."monitoruserclicks d
      					 WHERE moucDatetimeFirst >= '$statStartDate'  
  				         AND moucDatetimeFirst <= '$statEndDate'";
      //var_dump($qryAvg);
      $resAvg = mysqli_query($link,$qryAvg); 
      $rowAvg = @mysqli_fetch_array($resAvg);
      //var_dump($link);
      $aRet[] = array('#', '<b>&Oslash; ' . L_dynsb_monuserclicksduration . ': ' . $rowAvg[0] . ' min</b>', ''.$rowAvg[2].'', ''.$rowAvg[1].'');
  		
  		
  		//Verweildauer nach Minuten
  		$qry = "SELECT ROUND((UNIX_TIMESTAMP(moucDatetimeLast) - UNIX_TIMESTAMP(moucDatetimeFirst))/60) AS minuten, count(*)
  									, ROUND(count(*)/(SELECT count(moucIdNo)
  																        FROM ".DBToken."monitoruserclicks x 
  																       WHERE moucDatetimeFirst >= '$statStartDate'  
  				                                 AND moucDatetimeFirst <= '$statEndDate') * 100, 2) AS monUserclicksPercentage
  			                      
  						FROM ".DBToken."monitoruserclicks d 
             WHERE moucDatetimeFirst >= '$statStartDate'  
  				     AND moucDatetimeFirst <= '$statEndDate' 
  						
  						GROUP BY ROUND((UNIX_TIMESTAMP(moucDatetimeLast) - UNIX_TIMESTAMP(moucDatetimeFirst))/60)
              ORDER BY minuten";
  		//var_dump($qry);
  		$res = mysqli_query($link,$qry);  
    break;
    
    
    case 1:
    
      $qryAvg = "SELECT ROUND(AVG(moucCountClicks)), count(*), SUM(moucOrdSubmitted), (SUM(moucOrdSubmitted) / count(*) * 100) 
      						FROM ".DBToken."monitoruserclicks d
      					 WHERE moucDatetimeFirst >= '$statStartDate'  
  				         AND moucDatetimeFirst <= '$statEndDate'";
      $resAvg = mysqli_query($link,$qryAvg); 
      $rowAvg = mysqli_fetch_array($resAvg);
      
      $aRet[] = array('#', "<b>&Oslash; " . L_dynsb_monuserclickspageimpressions . ": $rowAvg[0]<br />" . L_dynsb_monuserclicksnumvisitors . ": $rowAvg[1]<br />" . L_dynsb_monuserclicksnumorders . ": $rowAvg[2] ($rowAvg[3]%)</b>", '--', '--');
  	
  		//Verweildauer nach Minuten
  		$qry = "SELECT moucCountClicks, count(*), ROUND(count(*)/(SELECT count(moucIdNo)
  																        FROM ".DBToken."monitoruserclicks x 
  																       WHERE moucDatetimeFirst >= '$statStartDate'  
  				                                 AND moucDatetimeFirst <= '$statEndDate' ) * 100, 2)
  							                      AS monUserclicksPercentage

              FROM ".DBToken."monitoruserclicks d
             WHERE moucDatetimeFirst >= '$statStartDate'  
  				     AND moucDatetimeFirst <= '$statEndDate' 
              
              GROUP BY moucCountClicks
              ORDER BY moucCountClicks";
 
  		$res = mysqli_query($link,$qry);  
    break;
    }
    
		$i = 0;
        
		while($row = @mysqli_fetch_array($res)) {
		 
            //fülle zeilen ohne wert mit dummywerten
            while($i < $row[0]) {		    
                //für das Diagramm nur alle 5 Minuten eine Zahl darstellen
                if (($i % 5) == 0)
                  $aRet[] = array($i, $i, 0, 0);
                else
                  $aRet[] = array('', $i, 0, 0);
                $i++;
            }
      
            //für das Diagramm nur alle 5 Minuten eine Zahl darstellen
            if (($i % 5) == 0)
                $aRet[] = array($row[0], $row[0], $row[1], $row[2]);
            else
                $aRet[] = array('', $row[0], $row[1], $row[2]);

            $i++;
        } 
    return $aRet; 
	}
	
	/**
	 * 
	 * @param $sessionId
	 * @param $bOrdSubmitted
	 * @return unknown_type
	 */
	function querySetUserclicks($sessionId, $bOrdSubmitted=false) {
		
		$myLink = $this->dbConnectionCreate();
		
		//Gucken ob schon ein Eintrag für diese SessionID vorhanden
		$qry = "SELECT moucIdNo, moucDatetimeFirst FROM " . DBToken . "monitoruserclicks WHERE moucSessionId = '$sessionId'";
		$res = mysqli_query($myLink,$qry);
		
		if ($row = mysqli_fetch_assoc($res)) {
 
      $delDate = date("Y-m-d H:i:s", time() - 86400); //86400 = Anzahl der Sekunden eines Tags
 
      // Lösche SessionId aus alten Daten, wenn gleiche SessionID schon existiert
			if ($row['moucDatetimeFirst'] < $delDate) {
 
				$upd = "UPDATE " . DBToken . "monitoruserclicks SET moucSessionId = '' WHERE moucDatetimeFirst < '$delDate'"; 
				mysqli_query($myLink,$upd);
				
				//da die alte session ID entfernt wurde, muss ein insert statt eines Updates erfolgen
				$insClick = "INSERT INTO " . DBToken . "monitoruserclicks (moucSessionid, moucCountClicks, moucDatetimeFirst, moucDatetimeLast)
	                  			VALUES ('$sessionId', moucCountClicks+1, now(), now());";
				mysqli_query($myLink,$insClick);
			} 
			else {

				//Wenn Bestellung abgeschickt
				if($bOrdSubmitted == false)
					$updOrd = '';
				else
					$updOrd = ", moucOrdSubmitted = '1'";
				
				//Update
				$updClick = "UPDATE " . DBToken . "monitoruserclicks 
												SET moucCountClicks = moucCountClicks + 1, moucDatetimeLast=now() $updOrd
											WHERE moucSessionId = '$sessionId'";
				mysqli_query($myLink,$updClick);
			}
		}
		else {
			$insClick = "INSERT INTO " . DBToken . "monitoruserclicks (moucSessionid, moucCountClicks, moucDatetimeFirst, moucDatetimeLast)
                  			VALUES ('$sessionId', moucCountClicks+1, now(), now());";
			mysqli_query($myLink,$insClick);
		}
	}		
	

	/**
	 * Switch the time used in some queries
	 *
	 * @param $mode same mode as in queryGetVisitors()
	 * @param $timestamp
	 * @param $i incrementor
	 *
	 */
	function switchTempTime($mode=0,$timestamp,$i=0)
	{
		switch($mode)
			{

			case 5:
			 //increment MONTH
		 	 $year =substr($timestamp,0,4);
		 	 $month=substr($timestamp,4,2);
		 	 $day  =substr($timestamp,6,2);

		 	 $tempTime = date("Ymd",mktime(0,0,0,$month,$day+$i,$year));
		 	break;

			case 2:
			 //increment MONTH
		 	 $tempTime = $timestamp+$i;
		 	break;

			case 0:
			 //increment HOUR
		 	 $tempTime = $timestamp+$i;
		 	 break;

		 	 case 1:
			 //increment Weekday
		 	 $tempTime = $timestamp+$i;
		 	 break;
 			}

 	return $tempTime;
	}


/**
 * Format...
 *
 */
	function getRealName ($i,$mode="null")
		{
			if ($mode=="null")
				{$mode=$this->userDetailMode;
				}

		switch($mode)
		{

		 //day
		  case 5:
		   	return $i;
		  break;

		 //month
		  case 2:
			switch ($i){
			case 1: return L_dynsb_month01;
			case 2: return L_dynsb_month02;
			case 3: return L_dynsb_month03;
			case 4: return L_dynsb_month04;
			case 5: return L_dynsb_month05;
			case 6: return L_dynsb_month06;
			case 7: return L_dynsb_month07;
			case 8: return L_dynsb_month08;
			case 9: return L_dynsb_month09;
			case 10: return L_dynsb_month10;
			case 11: return L_dynsb_month11;
			case 12: return L_dynsb_month12;
			}

		//by hour
	 	 case 0:
		   	$i = sprintf("%2d",$i);
			return "$i:00 - $i:59";
		  break;


		  case 1:
			switch ($i){
			case 6: return L_dynsb_sunday;
			case 0: return L_dynsb_monday;
			case 1: return L_dynsb_tuesday;
			case 2: return L_dynsb_wednesday;
			case 3: return L_dynsb_thursday;
			case 4: return L_dynsb_friday;
			case 5: return L_dynsb_saturday;
			}

		 break;
		 }


		}




	 /**
	  * Returns an array of one column of the sql resultset
	  *
	  * @param $res SQL-Resultset
	  * @param $column the column to be processed
	  * @return array
	  */
	 function sqlResultset2array($res, $column){

		$data = array();

		//fill array with data from the sql query
		while($obj = @mysqli_fetch_object($res)) {
    	array_push($data, $obj->$column);
		}

		//if data found smaller than limit, fill array with 0's
		if(count($data) < $this->limit) {
    	   $diff = $this->limit - count($data);

    	   for($i = 0; $i < $diff; $i++) {
        	array_push($data, 0);
    	    }
		}

		if ($data==null){$data[]=0;}

		return $data;

	 }


//	  function sqlResultset2DateArray($res, $column){
//
//		$data = array();
//
//		//fill array with data from the sql query
//		while($obj = @mysqli_fetch_object($res)) {
//    	array_push($data, $obj->$column);
//		}
//
//		//if data found smaller than limit, fill array with 0's
//		if(count($data) < $this->limit) {
//    	   $diff = $this->limit - count($data);
//
//    	   for($i = 0; $i < $diff; $i++) {
//        	array_push($data, 0);
//    	    }
//		}
//		return $data;
//
//	 }



	 /**
	  * Calculates the scale value to adjust the diagram bar sizes properly
	  * NOTE: if value == 0 then value = 1
	  *
	  * @param array $data A data array, like it is returned by sqlResultset2array()
	  * @return double Scale value
	  */
	 function calculateScaleval($data){

	 // detect data maximum and create scaleval for the bars
	 sort($data);
	 $data = array_reverse($data);
	 $maxval = $data[0];

     $scaleval = doubleval($maxval / $this->ysize);

     //to avoid divison by zero
     if($scaleval == 0) $scaleval = 1;

	 return $scaleval;

	 }

	  /**
	  * Calculates the scale value to adjust the diagram bar sizes properly
	  * NOTE: if value == 0 then value = 1
	  *
	  * @param double $sv value, like it is returned by calculateScaleval()
	  * @return double Scale End value
	  */
	 function calculateScalevalEnd($sv){
	  $scalevalend = $sv + doubleval(($sv / 100) * 10);

	  if($scalevalend == 0) $scalevalend = 1;

	  return $scalevalend;
	 }


     /**
      * Switches the variables which are necassary for displaying the diagram
      *
      * Meaning of the Parameter
      * - 0: 400x100
      * - 1: 500x180
      * - 2: 630x240
      * - 3: 630x630
      * - default: 400x100
      *
      * @param int $mode
      *
      */
     function switchDiagramSize($mode)
     {

      //NOTE:	Changes here also effect the values
      //		set in the constructor

       switch ($mode)
       {
      	case 0:
  			  $this->xsize = 400;
    		  $this->ysize = 100;
    	break;

    	case 1:
  			  $this->xsize = 500;
    		  $this->ysize = 180;
    	break;

    	case 2:
  			  $this->xsize = 630;
    		  $this->ysize = 240;
    	break;

    	case 3:
  			  $this->xsize = 630;
    		  $this->ysize = 630;
    	break;

    	default:
  			  $this->xsize = 400;
    		  $this->ysize = 100;
    	break;
       }

     }


     /**
      * Switches the variables which are necassary for displaying the diagram
      *
      * Meaning of the Parameter
      * - 0: 10 Rows
      * - 1: 20 Rows
      * - 2: 50 Rows
      * - default: 10
      *
      * @param int $mode
      *
      */
     function switchDiagramViewmode($mode)
     {

      //NOTE:	Changes here also effect the values
      //		set in the constructor

       switch ($mode)
       {
      	case 0:
  			  $this->limit = 10;
  			  $this->gradientToogleStep = 2;
       		  $this->dataTextOffset = 1;
    	break;

    	case 1:
  			  $this->limit = 20;
  			  $this->gradientToogleStep = 4;
       		  $this->dataTextOffset = 1;
    	break;

    	case 2:
  			  $this->limit = 50;
  			  $this->gradientToogleStep = 10;
       		  $this->dataTextOffset = 1;
    	break;

    	default:
  			  $this->limit = 10;
  			  $this->gradientToogleStep = 2;
       		  $this->dataTextOffset = 1;
    	break;
       }

     }



      /**
       * Returns array with text for picsize, array index should be
       * used in switchDiagramSize($a_index)
       *
       * @return array
       */
      function getDiagramSizes(){
		return $this->diagramSizes;
      }

      /**
       * Returns array with text for the View Mode, array index should be
       * used in switchDiagramViewmode($a_index)
       *
       * @return array
       */
      function getDiagramViewmodes(){
		return $this->viewmodes;
      }


      function getXsize(){
		if ($this->layout==0){
		  $x = intval(trim($this->xsize));
		}else{
		  $x = intval(trim( $this->ysize));
		}

		return $x;
      }

      function getYsize(){
		if ($this->layout==0){
		 $y = intval(trim($this->ysize));
		}else{
		 $y = intval(trim($this->xsize));
		}
		return $y;
      }


	function getDiagramName(){
		return $this->diagramName;
	}


       function getLimit(){
		return $this->limit;
      }

      function getUserDetailModes(){
      	return $this->userDetailModes;
      }

      function setUserDetailMode($p){
      	if ($p==null || $p==""){$p=0;}
      	$this->userDetailMode=$p;

      }


       function getGradientToogleStep(){
		return $this->gradientToogleStep;
      }

       function getDataTextOffset(){
		return $this->dataTextOffset;
      }

       function getLayout(){
		return $this->layout;
      }

      function getBarlayout()
     {
       return $this->barlayout;
     }

     function getLang()
     {
       return $this->lang;
     }



 	  /**
 	   * Return the statistics start date formatted in yyyymmddhhmmss
 	   *
 	   * @return string statStartDate
 	   */
      function getStatStartDate(){
         return $this->statStartDate;
      }

      /**
 	   * Return the statistics end date formatted in yyyymmddhhmmss
 	   *
 	   * @return string statEndDate
 	   */
      function getStatEndDate(){
         return $this->statEndDate;
      }


/**
 * Function to emulate the fnmatch() function for windows.
 * optimized for Browser User Agents
 *
 * @param string $pattern search pattern
 * @param string $string string in which the pattern is searched
 *
 * @return int
 */
function fnmatch_win($pattern, $string) {
   for ($op = 0, $npattern = '', $n = 0, $l = strlen($pattern); $n < $l; $n++) {
       switch ($c = $pattern[$n]) {
           case '\\':
               $npattern .= '\\' . @$pattern[++$n];
           break;

		   //escape delimiter
		   case '/':
               $npattern .= '\\' . $c;
           break;

           case '.': case '+': case '^': case '$': case '(': case ')': case '{': case '}': case '=': case '!': case '<': case '>': case '|':
               $npattern .= '\\' . $c;
           break;


		   //fnmatch: ? means 1 character, in regex it means 0 or 1 character.
		   //so we have to use {1}...
		   case '?':
		      $npattern .= '.{1}';
           break;

		   case '*':
               $npattern .= '.' . $c;
           break;

           case '[': case ']': default:
               $npattern .= $c;
               if ($c == '[') {
                   $op++;
               } else if ($c == ']') {
                   if ($op == 0) return false;
                   $op--;
               }
           break;
       }
   }

   if ($op != 0) return false;
 //  echo "<br />/^$npattern$/i ------------- $string<br />";

   return preg_match('/^' . $npattern . '$/i', $string);
}



/**
 * Emulates the PHP-Function get_browser(), but
 * you can define the path where the browsecap.ini
 * is stored.
 *
 *
 *
 *
 */

function php_get_browser($agent = NULL, $path="../dynsb/class/php_browscap.ini"){
$agent=$agent?$agent:$_SERVER['HTTP_USER_AGENT'];
$yu=array();
$q_s=array("#\.#","#\*#","#\?#");
$q_r=array("\.",".*",".?");

//$brows=parse_ini_file($path,true);
$brows=parse_ini_file('dynsb/class/php_browscap.ini',true);
var_dump($brows);
$ii=0;
foreach($brows as $k=>$t){

 // echo strpos($agent,$k).")<br/>";
 // echo fnmatch($k,$agent)."]<br/>";
 //echo "<br/>".$ii++.$k."  ---> ".$agent;

  if($this->fnmatch_win($k,$agent)){


  $yu['browser_name_pattern']=$k;
  /*$pat=preg_replace($q_s,$q_r,$k);*/
  $pat=preg_replace_callback($q_s,function ($m) { return array("\.",".*",".?"); },$k);
  $yu['browser_name_regex']=strtolower("^$pat$");
   foreach($brows as $g=>$r){
     if($t['Parent']==$g){
       foreach($brows as $a=>$b){
         if($r['Parent']==$a){
           $yu=array_merge($yu,$b,$r,$t);
           foreach($yu as $d=>$z){
             $l=strtolower($d);
             $hu[$l]=$z;
           }
         }
       }
     }
   }
   break;
  }
}
return $hu;
}



/**
 * Parses a user agent string into its important parts
 *
 * @author Jesse G. Donat <donatj@gmail.com>
 * @link https://github.com/donatj/PhpUserAgent
 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
 * @throws \InvalidArgumentException on not having a proper user agent to parse.
 * @return string[] an array with browser, version and platform keys
 */
function parse_user_agent( $u_agent = null ) {
	if( is_null($u_agent) ) {
		if( isset($_SERVER['HTTP_USER_AGENT']) ) {
			$u_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			throw new \InvalidArgumentException('parse_user_agent requires a user agent');
		}
	}

	$platform = null;
	$browser  = null;
	$version  = null;

	$empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

	if( !$u_agent ) return $empty;

	if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {
		preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

		$priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'CrOS', 'X11' );

		$result['platform'] = array_unique($result['platform']);
		if( count($result['platform']) > 1 ) {
			if( $keys = array_intersect($priority, $result['platform']) ) {
				$platform = reset($keys);
			} else {
				$platform = $result['platform'][0];
			}
		} elseif( isset($result['platform'][0]) ) {
			$platform = $result['platform'][0];
		}
	}

	if( $platform == 'linux-gnu' || $platform == 'X11' ) {
		$platform = 'Linux';
	} elseif( $platform == 'CrOS' ) {
		$platform = 'Chrome OS';
	}

	preg_match_all('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
				TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
		$u_agent, $result, PREG_PATTERN_ORDER);

	// If nothing matched, return null (to avoid undefined index errors)
	if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
		if( preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result) ) {
			return array( 'platform' => $platform ?: null, 'browser' => $result['browser'], 'version' => isset($result['version']) ? $result['version'] ?: null : null );
		}

		return $empty;
	}

	if( preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result) ) {
		$rv_result = $rv_result['version'];
	}

	$browser = $result['browser'][0];
	$version = $result['version'][0];

	$lowerBrowser = array_map('strtolower', $result['browser']);

	$find = function ( $search, &$key, &$value = null ) use ( $lowerBrowser ) {
		$search = (array)$search;

		foreach( $search as $val ) {
			$xkey = array_search(strtolower($val), $lowerBrowser);
			if( $xkey !== false ) {
				$value = $val;
				$key   = $xkey;

				return true;
			}
		}

		return false;
	};

	$key = 0;
	$val = '';
	if( $browser == 'Iceweasel' || strtolower($browser) == 'icecat' ) {
		$browser = 'Firefox';
	} elseif( $find('Playstation Vita', $key) ) {
		$platform = 'PlayStation Vita';
		$browser  = 'Browser';
	} elseif( $find(array( 'Kindle Fire', 'Silk' ), $key, $val) ) {
		$browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
		$platform = 'Kindle Fire';
		if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
			$version = $result['version'][array_search('Version', $result['browser'])];
		}
	} elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
		$browser = 'NintendoBrowser';
		$version = $result['version'][$key];
	} elseif( $find('Kindle', $key, $platform) ) {
		$browser = $result['browser'][$key];
		$version = $result['version'][$key];
	} elseif( $find('OPR', $key) ) {
		$browser = 'Opera Next';
		$version = $result['version'][$key];
	} elseif( $find('Opera', $key, $browser) ) {
		$find('Version', $key);
		$version = $result['version'][$key];
	} elseif( $find('Puffin', $key, $browser) ) {
		$version = $result['version'][$key];
		if( strlen($version) > 3 ) {
			$part = substr($version, -2);
			if( ctype_upper($part) ) {
				$version = substr($version, 0, -2);

				$flags = array( 'IP' => 'iPhone', 'IT' => 'iPad', 'AP' => 'Android', 'AT' => 'Android', 'WP' => 'Windows Phone', 'WT' => 'Windows' );
				if( isset($flags[$part]) ) {
					$platform = $flags[$part];
				}
			}
		}
	} elseif( $find(array( 'IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome' ), $key, $browser) ) {
		$version = $result['version'][$key];
	} elseif( $rv_result && $find('Trident', $key) ) {
		$browser = 'MSIE';
		$version = $rv_result;
	} elseif( $find('UCBrowser', $key) ) {
		$browser = 'UC Browser';
		$version = $result['version'][$key];
	} elseif( $find('CriOS', $key) ) {
		$browser = 'Chrome';
		$version = $result['version'][$key];
	} elseif( $browser == 'AppleWebKit' ) {
		if( $platform == 'Android' && !($key = 0) ) {
			$browser = 'Android Browser';
		} elseif( strpos($platform, 'BB') === 0 ) {
			$browser  = 'BlackBerry Browser';
			$platform = 'BlackBerry';
		} elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
			$browser = 'BlackBerry Browser';
		} else {
			$find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
		}

		$find('Version', $key);
		$version = $result['version'][$key];
	} elseif( $pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser'])) ) {
		$pKey = reset($pKey);

		$platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
		$browser  = 'NetFront';
	}

	return array( 'platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null );
}
}
?>
