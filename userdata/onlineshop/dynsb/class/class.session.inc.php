<?php

// Datei:       class.Session.inc.php
// Benötigt:    mind. 4.0.1pl2

/*
*   "Manueller" Session-Fallback mit PHP4
*	@version	1.9.2
*/
class Session {
    // Client nimmt Cookies an
    var $usesCookies = false;
    var $sessionHashBits = 4;
    
    // Konstruktor - nimmt, wenn gewünscht einen neuen Session-Namen entgegen
    function __construct($sessionName="dsbID", $disableTransSID=true, $debug=false) {
        global $_POST, $_GET, $_COOKIE;

      //Prüfe die PHP-Einstellung, wie viele Bits für den Session Hash verwendet
      //werden. Ab PHP 5 ist oftmals "5" eine Standardeinstellung und hat eine Session
      //mit 26 Zeichen (statt der üblichen 32 Zeichen) zufolge...
      $sessionHashBits = ini_get("session.hash_bits_per_character");
      $sessionHashFunction = ini_get("session.hash_function");
      
			
			if ($sessionHashFunction == "0" || $sessionHashFunction == false)
			{
				switch ($sessionHashBits)
	        {
	         case 4: $sessionHashLength = 32;
	          break; 
	         case 5: $sessionHashLength = 26;
	          break;
	         case 6: $sessionHashLength = 22;
	          break;
	         
	         default: $sessionHashLength = 32;
	          break;      
	        }
			}
			else if($sessionHashFunction == "1")
			{
				switch ($sessionHashBits)
	        {	         
					 case 4: $sessionHashLength = 40;
	         //case 4: $sessionHashLength = 32;
	          break; 
	         case 5: $sessionHashLength = 32;
	         //case 5: $sessionHashLength = 32;
	          break;
	         case 6: $sessionHashLength = 27;
	         //case 6: $sessionHashLength = 32;
	          break;
	         
	         default: $sessionHashLength = 40;
	         //default: $sessionHashLength = 32;
	          break;      
	        }
			}  

        //if ($disableTransSID) ini_set("session.use_trans_sid","0");

        $this->sendNoCacheHeader();

        // Session-Namen setzen, Session initialisieren
        session_name(isset($sessionName) ? $sessionName : session_name());
        @session_start();

        // Prüfen ob die Session-ID die Standardlänge von 32 bzw. 26 oder 22 Zeichen hat,
        // ansonsten Session-ID neu setzen
        if (strlen(session_id()) != $sessionHashLength) {
            mt_srand ((double)microtime()*1000000);
            session_id(md5(uniqid(mt_rand())));
        }

        // Prüfen, ob eine Session-ID übergeben wurde (über Cookie, POST oder GET)
        $IDpassed = false;
        if (isset($_COOKIE[session_name()]) && @strlen($_COOKIE[session_name()]) == $sessionHashLength) $IDpassed = true;
        if (isset($_POST  [session_name()]) && @strlen($_POST  [session_name()]) == $sessionHashLength) $IDpassed = true;
        if (isset($_GET   [session_name()]) && @strlen($_GET   [session_name()]) == $sessionHashLength) $IDpassed = true;

        if  (!$IDpassed) {
            // Es wurde keine (gültige) Session-ID übergeben.
            // Script-Parameter der URL zufügen
            // Debug-Log
            if ($debug) error_log(date ("[d.m.Y H:i:s]")." - '".getenv("SERVER_NAME").getenv("SCRIPT_NAME")."' created a new Session-ID.\n",3,"/tmp/session.log");
            $query	=	getenv("QUERY_STRING") != ""
						? "?".getenv("QUERY_STRING")
						: "";

            header("Status: 302 Found");
            // auskommentiert TJ 14.07.2003
            //$this->redirectTo(getenv("SCRIPT_NAME").$query); // Script terminiert
        }

        // Wenn die Session-ID übergeben wurde, muß sie
        // nicht unbedingt gültig sein!
        // Für weiteren Gebrauch merken
        $this->usesCookies = ( isset($_COOKIE[session_name()]) && @strlen($_COOKIE[session_name()]) == $sessionHashLength);
    }



    /*
    *   Cacheing unterbinden
    *
    *   Ergänze/Override "session.cache_limiter = nocache"
    *
    *   @param  void
    *   @return void
    */
    function sendNoCacheHeader()    {
        header("Expires: Sat, 05 Aug 2000 22:27:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Cache-Control: post-check=0, pre-check=0");
    }


    /*
    *   HTTP-Redirect ausführen (header("Location: ...")
    *
    *   Diese Methode berücksichtigt auch nicht-standard Ports
    *   und SSL. Ein GET-Parameter beim  wird bei Bedarf
    *   (Session-ID-Fallback) an die URI drangehängt. Nach
    *   dem Aufruf dieser Methode wird das aktive Script
    *   beendet und die Kontrolle wird an das Ziel-Script
    *   übergeben.
    *
    *   @param  string  Ziel-Datei (z.B. "index.html")
    *   @return void
    */
    function redirectTo($pathInfo) {
		global $_SERVER;
        // Relativer Pfad?
        if ($pathInfo[0] != "/") {
            $pathInfo = substr(getenv("SCRIPT_NAME"), 0, strrpos(getenv("SCRIPT_NAME"),"/")+1).$pathInfo;
        }

        // Läuft dieses Script auf einem non-standard Port?
        $port    = !preg_match("/^(80|443)$/",getenv("SERVER_PORT"),$portMatch)
                   ? ":".getenv("SERVER_PORT")
                   : "";

		/*
        // Redirect
        header("Location: "
               .(($portMatch[1] == 443) ? "https://" : "http://")
               .getenv("SERVER_NAME").$port.$this->url($pathInfo));

        */

		/*
        echo "Location: "
               .(($portMatch[1] == 443) ? "https://" : "http://")
               .$_SERVER['SERVER_ADDR'].$port.$this->url($pathInfo);
		*/

        header("Location: ".(($portMatch[1] == 443) ? "https://" : "http://").$_SERVER['SERVER_ADDR'].$port.$this->url($pathInfo));
        exit;
    }



    /*
    *   Entfernt mögliche abschließende "&" und "?"
    *
    *   @param  string  String
    *   @return string  String ohne abschließende "&" und "?"
    */
    function removeTrail($pathInfo) {
        $dummy = preg_match("/(.*)(?<!&|\?)/",$pathInfo,$match);
        return $match[0];
    }



    /*
    *   Fallback via GET - wenn Cookies ausgeschaltet sind
    *
    *   @param  string  Ziel-Datei
    *   @return string  Ziel-Datei mit - bei Bedarf - angehängter Session-ID
    */
    function url($pathInfo)  {
        if ($this->usesCookies || ini_get("session.use_trans_sid")) return $pathInfo;

        // Anchor-Fragment extrahieren
        $dummyArray = split("#",$pathInfo);
        $pathInfo = $dummyArray[0];

        // evtl. (kaputte) Session-ID(s) aus dem Querystring entfernen
        //$pathInfo = preg_replace("/[?|&]".session_name()."=[^?|&]*/","",$pathInfo);
        $pathInfo = preg_replace_callback("/[?|&]".session_name()."=[^?|&]*/",function ($m) { return ''; },$pathInfo);

        // evtl. Query-Delimiter korrigieren
        if (preg_match("/&/",$pathInfo) && !preg_match("/\?/",$pathInfo)) {
            // 4ter Parameter für "preg_replace()" erst ab 4.0.1pl2
            /*$pathInfo = preg_replace("/&/","?",$pathInfo,1);*/
            $pathInfo = preg_replace_callback("/&/",function ($m) { return '?'; },$pathInfo,1);
        }

        // Restmüll entsorgen
        $pathInfo = $this->removeTrail($pathInfo);

        // Session-Name und Session-ID frisch hinzufügen
        $pathInfo .= preg_match("/\?/",$pathInfo) ? "&" : "?";
        $pathInfo .= session_name()."=".session_id();

        // Anchor-Fragment wieder anfügen
        $pathInfo .= isset($dummyArray[1]) ? "#".$dummyArray[1] : "";

        return $pathInfo;
    }


    /*
    *   Fallback via HIDDEN FIELD - wenn Cookies ausgeschaltet sind
    *
    *   Ohne Cookies erfolgt Fallback via HTML-Hidden-Field
    *   (für Formulare)
    *
    *   @param  void
    *   @return string  HTML-Hidden-Input-Tag mit der Session-ID
    */
    function hidden() {
        if ($this->usesCookies || ini_get("session.use_trans_sid")) return "";
        return '<INPUT type="hidden" name="'.session_name().'" value="'.session_id().'" />';
    }



    /*
    *    Variable korrekt registrieren
    *
    *    Wenn PHP mit "register_globals=Off" konfiguriert ist,
    *    ist das Session-Management ein wenig broken.
    *    Diese Methode korrigiert das indem sie eine Referenz
    *    zum $HTTP_SESSION_VARS-Hash erzwingt, der dann korrekt
    *    gespeichert wird.
    *
    *    @param    string    Beliebige Anzahl von Variablennamen,
    *                        die "registriert" werden sollen
    *    @return    void
    */
    function register()    {
        foreach(func_get_args() as $arg) {
            //session_register($arg);
            $_SESSION[$arg] = "";
        }
        if (!ini_get("register_globals") || strtolower(ini_get("register_globals")) == 'off') {
                // Keine Überraschungen in zuküftigen
                // PHP-Versionen erwünscht.
                $oldLevel    =    error_reporting(0);
                foreach(func_get_args() as $arg) {
                    $GLOBALS["HTTP_SESSION_VARS"][$arg] = &$GLOBALS[$arg];
                }
                error_reporting($oldLevel);
        }
    }



    /*
    *    Variable aus den Session-Daten löschen
    *
    *    Wenn PHP mit "register_globals=Off" konfiguriert ist,
    *    ist das Session-Management ein wenig broken.
    *    Diese Methode korrigiert das indem sie den entsprechenden
    *    Index im $HTTP_SESSION_VARS-Hash löscht.
    *
    *    @param    string    Beliebige Anzahl von Variablennamen,
    *                        die "unregistriert" werden sollen
    *    @return    void
    */
    function unregister() {
        foreach(func_get_args() as $arg) {
            //session_unregister($arg);
            unset($_SESSION[$arg]);
            $keyIndex = array_keys(array_keys($GLOBALS["HTTP_SESSION_VARS"]), $arg);
            if (sizeof($keyIndex) == 0) continue;
            array_splice($GLOBALS["HTTP_SESSION_VARS"], $keyIndex[0], 1);
        }
    }



    function destroy($sessionName="dsbID") {
         
        //session_name(isset($sessionName) ? $sessionName : session_name());
/*
        $handle=opendir(get_cfg_var('session.save_path'));
        while ($file = readdir ($handle))
        {
          if (substr($file, 0, 4)=="sess")
          {
            @unlink(get_cfg_var('session.save_path').DIRECTORY_SEPARATOR.$file);
          }
        }
        closedir($handle);
        
        
  */      
        //return session_destroy();
        return true;
    }

} // of class

?>
