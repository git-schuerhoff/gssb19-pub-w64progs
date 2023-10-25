<?PHP

/*
    file: path.inc.php
    This file contains basic paths etc.
*/



/*
    ENG:
    uncomment the following line for full error reporting
    
    DEU:
    Entfernen Sie die Kommentar '//'-Slashes um vollst�ndige Fehlerhinweise
    w�hrend der PHP-Ausf�hrung zu erhalten.
*/
//error_reporting(E_ALL);



/*
    ENG:
    the upload and application-start path
    change this folder if you have uploaded the 'dynsb'-application
    to another location.
    
    DEU:
    Festlegung des Upload-Verzeichnisses
    Sollten Sie die 'dynsb'-Anwendung in einen anderen Ordner hochgeladen
    haben so m�ssen Sie diesen URL_ROOT-Eintrag entsprechend �ndern.
*/
define("URL_ROOT","/dynsb/");

require_once($_SERVER["DOCUMENT_ROOT"].URL_ROOT."path.inc.php");
?>
