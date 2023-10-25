<?php
/*

	GS BusinessManager Setup System - class.setup.php
	Author: Raimund Kulikowski / GS Software Solutions GmbH
	
	(c) 2004-2005 GS Software Solutions GmbH
	
	this code is NOT open-source or freeware
	you are not allowed to use, copy or redistribute it in any form
 
*/


class setupEngine {

	var $errorCode = 0;
	var $seEntryCounter = 0;
	var $sePath = "";
	var $seSQL = "";
	var $seSQLdir = "sql";
	var $seTABdir = "tab";
	var $seDATdir = "dat";
	var $seSep = "\\";
	var $seCreateDBFile = "";
	var $seaTAB = array();
	var $dbTAB = array();
	//A TS 03.12.2013 nicht nötig, passende .dat-Datei wird gesucht
	//Wenn nicht gefunden, keine vorbelegten Daten verfügbar 
	//var $seaDAT = array();
	//A TS 03.12.2013 Datenbankzugang klassenweit
	var $dbhost;
	var $dbusr;
	var $dbname;
	var $dbpwd;
	var $dbtoken;
	
	function __construct($modDir, $sep) {
		if(file_exists("../conf/db.const.inc.php"))
		{
			//A TS 03.12.2013 Zugangsdaten klassenweit verfügbar machen
			require("../conf/db.const.inc.php");
			$this->dbhost = $dbServer; 
			$this->dbusr = $dbUser;
			$this->dbname = $dbDatabase;
			$this->dbpwd = $dbPass;
			$this->dbtoken = DBToken;
		}
		else
		{
			die("Unable to load database-settings");
		}
		
		
		$this->sePath = $modDir;
		$this->seSep = $sep;
		//A TS 03.12.2013 nur noch .tab-Dateien einlesen
		/*
		$this->readDir($this->seSQLdir);
		if($this->errorCode == 0) $this->readDir($this->seSQLdir.$this->seSep.$this->seTABdir);
		if($this->errorCode == 0) $this->readDir($this->seSQLdir.$this->seSep.$this->seDATdir);
		if(count($this->seaTAB) == count($this->seaDAT)) {
			session_start();
			$_SESSION['aTAB'] = $this->seaTAB;
			$_SESSION['aDAT'] = $this->seaDAT;
			$_SESSION['createDB'] = $this->seCreateDBFile;
		} else {
			$this->errorCode = 101;
		}
		*/
		$this->readDir($this->seSQLdir.$this->seSep.$this->seTABdir);
		//$this->debug();
	}
	
	function installDB(){
		$dbh = mysqli_connect($this->dbhost, $this->dbusr, $this->dbpwd, $this->dbname) or die("<br />aborted: can´t connect to '$this->dbhost' <br />");
		$dbh->query("SET NAMES 'utf8'");
		for($d = 0; $d < count($this->seaTAB); $d++)
		{
			//A TS 02.12.2013 Datei $this->seaTAB[$d] laden und im Inhalt
			//{dsbxx_} durch den DBToken aus db.const.inc.php ersetzen
			$cre = str_replace('{dsbxx_}',$this->dbtoken,file_get_contents($this->seaTAB[$d]));
			//A TS 22.02.2016: {gssb_charset} durch 'CHARACTER SET utf8 COLLATE utf8_unicode_ci' ersetzen
			$cre = str_replace('{gssb_charset}','CHARACTER SET utf8 COLLATE utf8_unicode_ci',$cre);
			@mysqli_query($dbh,$cre);
			if(mysqli_errno($dbh) != 0)
			{
				die("<h1>Error while creating the database!</h1>" . mysqli_error($dbh) . ":<br>" . $cre);
			}
			//Pfad ".../tab/..." durch ".../dat...", Dateiendung ".tab" durch ".dat" ersetzen
			$datfile = str_replace("/tab/","/dat/",$this->seaTAB[$d]);
			$datfile = str_replace(".tab",".dat",$datfile);
			if(file_exists($datfile))
			{
				$ins = str_replace('{dsbxx_}',$this->dbtoken,file_get_contents($datfile));
				@mysqli_query($dbh,$ins);
			}
		}
		
		//A TS 03.12.2013 mit LOAD DATA INFILE gehts schneller
		if(file_exists("setup/citys.csv"))
		{
			$insert = "LOAD DATA LOCAL INFILE 'setup/citys.csv' INTO TABLE " . $this->dbtoken . "citys FIELDS TERMINATED BY ';'";
			@mysqli_query($dbh,$insert);
		}
		else
		{
			die("Unable to open cities-file.");
		}
		/*
		$handle = @fopen("setup/citys.csv","r");
		$insert = "";
		if($handle !== false)
		{
			echo "Importiere Städte.<br>";
			while ( ($data = fgetcsv ($handle, 1000, ";")) !== FALSE )
			{
				$insert = "INSERT INTO ". $this->dbtoken . "citys (bund_land, ort, plz) VALUES ('" . $data[0] . "','" . $data[1] . "','". $data[2] . "');\n";
				@mysqli_query($dbh,$insert);
				if(mysqli_errno($dbh) != 0)
				{
					die(mysqli_error($dbh) . "<br>&nbsp;<br>" . $insert);
				}
			}
			//echo $insert . "<br>&nbsp;<br>";
			
		}
		else
		{
			die("Unable to open cities-file.");
		}
		fclose ($handle);
		*/
		echo "Fertig.";
		mysqli_close($dbh);
	}

	function addCounter($val = 0) {
		$this->seEntryCounter += intval($val);
	}
	
	function getCounter() {
		return intval($this->seEntryCounter);
	}
	
	function readDir($dname) 
	{
		$path = $this->sePath.$this->seSep.$dname.$this->seSep;
		$path = str_replace("//", "/", $path);
		$dir = opendir($path);
		while(false !== ($file = readdir($dir))) 
		{
			if($file != "." && $file != ".." && substr($file,0,6)== 'dsbxx_') 
			{
				if(is_dir($path.$file)) 
				{
					//
				} 
				else 
				{
					if($dname == "sql" && $file == 'createDB.sql') 
				  {
						$this->seCreateDBFile = $path.$file;
					} 
				  else 
				  {
						if($file != 'php.ini') 
					{
							if($dname == $this->seSQLdir.$this->seSep.$this->seTABdir) $this->seaTAB[] = $path.$file;
							//if($dname == $this->seSQLdir.$this->seSep.$this->seDATdir) $this->seaDAT[] = $path.$file;
						}
					}
					if($file != "index.html" && $file != "install_dynsb.sql" && $file != "php.ini") $this->seEntryCounter++;
				}
			}
		}
	}
	
	function isMySQLConnectable() 
	{
		$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
		$err = 1;
		$link = mysqli_connect($this->dbhost, $this->dbusr, $this->dbpwd, $this->dbname) or $err = 0;
		$link->query("SET NAMES 'utf8'");
		return $err;
	}

	function isDatabaseInstalled() 
	{
		
		$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));
		$err = 1;
		$link = mysqli_connect($this->dbhost, $this->dbusr, $this->dbpwd, $this->dbname) or $err = 0;
		$link->query("SET NAMES 'utf8'");
		if($err == 1) 
		{
				//TS 01.03.2017: Nur GS ShopBuilder-Tabellen interessieren hier!!!!
				//$SQL = "SHOW TABLES";
				$SQL = "SHOW TABLES LIKE '".$this->dbtoken."%'";
				$qry = mysqli_query($link,$SQL);
				$num = mysqli_num_rows($qry);
				$dbscounter = 0;
				while($row = @mysqli_fetch_row($qry)) 
		{
			if(substr($row[0], 0, 6) == $this->dbtoken) 
			{
				$dbscounter++; 
				array_push ($this->dbTAB, $row[0]);
			}
			//TS 03.08.2017: Andere DB-Tabellen ignorieren
			/*else
			{
				echo "Table '".$row[0]."' in database ".$this->dbname." is not used by GS ShopBuilder.<br />";
				echo "Please remove this table to make the dynamic extensions work.";
				die();
			}*/
		}
        // SM 02.02.2017 - Erlauben den Kunden neue Tabellen z.B. für eKomi zu erstellen
		//if($dbscounter == count($this->seaTAB)||$dbscounter == count($this->seaTAB)+16) 
        if($dbscounter >= count($this->seaTAB))     
		{
			// do nothing...
		} 
		else 
		{
			if($dbscounter!=0)
			{
				echo "Database is not complete!<br />";
				foreach($this->seaTAB as $key => $value) 
				{
					$tabFile = str_replace(".tab","",substr($value,14));
					$tabFile = str_replace("dsbxx_",$this->dbtoken,$tabFile);
					if(!in_array ($tabFile, $this->dbTAB))
					{
						echo "Table '".$tabFile."' is missing!<br />";
					}
				}
				die();
			}
			else
			{
				$err = 0;
			}
		}
	}
	return $err;
}
	
	function loadAndRunSQL($pf) {
		echo "--> ".$pf."<br />";
	}

	function debug() {
		print_r($this->seaTAB);
		echo "\n\n<br />\n\n";
		//print_r($this->seaDAT);
		//echo "\n\n<br />\n\n";
		//print_r($_SESSION);
		//echo "\n\n<br />\n\n";
		echo "entrys: ".$this->seEntryCounter."<br />";		
	}
}

?>
