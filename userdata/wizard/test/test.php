<?php
	/*
	**********************************************************
	* ATTENTION!!!!                                          *
	* PLEASE USE THIS SCRIPT WITH GSSB16 AND HIGHER ONLY !!! *
	* IT REQUIRES PHP 5.3 AND HIGHER !!!                     *
	**********************************************************
	*/
	header('Content-type: text/html; charset=ISO-8859-1');
	function ic_system_info() {
		$thread_safe = false;
		$debug_build = false;
		$cgi_cli = false;
		$php_ini_path = '';
		$compiler = '';
		
		ob_start();
		phpinfo(INFO_GENERAL);
		$php_info = ob_get_contents();
		ob_end_clean();

		/*foreach (split("\n",$php_info) as $line) {*/
		foreach (explode("\n",$php_info) as $line) {
			/*if (eregi('command',$line)) {*/
			if (preg_match("/command/i",$line)) {
				continue;
			}

			if (preg_match('/thread safety.*(enabled|yes)/Ui',$line)) {
				$thread_safe = true;
			}

			if (preg_match('/debug.*(enabled|yes)/Ui',$line)) {
				$debug_build = true;
			}
			
			//TS Compiler-Version für Windows-Versionen
			if (preg_match('/Compiler.*(MSVC9)/Ui',$line)) {
				$compiler = 'VC9';
			}
			if (preg_match('/Compiler.*(MSVC11)/Ui',$line)) {
				$compiler = 'VC11';
			}
			if (preg_match('/Compiler.*(MSVC14)/Ui',$line)) {
				$compiler = 'VC14';
			}
			
			
			/*
			if (eregi("configuration file.*(</B></td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*</td.*)?",$line,$match)) {
				$php_ini_path = $match[2];
				if (!@file_exists($php_ini_path)) {
					$php_ini_path = '';
				}
			}*/

			$cgi_cli = ((strpos(php_sapi_name(),'cgi') !== false) || (strpos(php_sapi_name(),'cli') !== false));
		}
		//echo $thread_safe;
		//echo $debug_build;
		/*return array('THREAD_SAFE' => (int)$thread_safe,
			   'DEBUG_BUILD' => (int)$debug_build,
			   'PHP_INI'     => $php_ini_path,
			   'CGI_CLI'     => (int)$cgi_cli);*/
		return array('THREAD_SAFE' => (int)$thread_safe,
			   'DEBUG_BUILD' => (int)$debug_build,
			   'CGI_CLI'     => (int)$cgi_cli,
			   'COMPILER' => $compiler);
	}
	
	$sys_info = ic_system_info();
	//get the php-version
	$php_version = phpversion();
	$php_flavour = substr($php_version,0,3);
	//get the os name
	$os_name = substr(php_uname(),0,strpos(php_uname(),' '));
	$os_code = strtolower(substr($os_name,0,3));
	//check if any extension already loaded
	//$zend_loaded = zend_loader_enabled();
	$ioncube_loaded = extension_loaded('ionCube Loader');
	
	$extensions = get_loaded_extensions();
	if (in_array('ionCube Loader', $extensions)) {
		$isCorrectVersion = true;
		$ioncubeVersion = '';
		if (function_exists('ioncube_loader_version')) {
			$ioncubeVersion = ioncube_loader_version();
			$ioncubeMajorVersion = (int)substr($ioncubeVersion, 0, strpos($ioncubeVersion, '.'));
			$ioncubeMinorVersion = (int)substr($ioncubeVersion, strpos($ioncubeVersion, '.')+1);
			if ($ioncubeMajorVersion < 4 || ($ioncubeMajorVersion == 4 && $ioncubeMinorVersion < 7)) {
				$isCorrectVersion = false;
				//echo 'ionCube Loader '.$ioncubeVersion.' - old, required is a minimum of ionCube Loader version 4.7<br>';
				echo 'IONCUBE_VERSION=OLD;';
			}
			else
			{
				echo 'IONCUBE_VERSION=OK;';
			}
			echo 'IONCUBE_VERSIONNR=' . $ioncubeVersion . ';';
		}
	}
	
	//output variables
	echo 'OS_CODE='.$os_code.';';
	echo 'PHP_VERSION='.$php_flavour.';';
	//echo 'ZEND_LOADED='.(int)$zend_loaded.';';
	echo 'IONCUBE_LOADED='.(int)$ioncube_loaded.';';
	echo 'SERVER_PATH='.getcwd().';';
	echo 'DOCUMENT_ROOT='.$_SERVER['DOCUMENT_ROOT'].';';      // enthält ggf. die Zeichenkette "strato"
	echo 'OSYSTEM='.str_replace(' ','',php_uname()).';';                          // entält bei Strato die Kennung 
                                                            // "SunOS localhost 5.10 Generic_139556-08 i86pc" bzw.
                                                            // "SunOS localhost 5.10 Generic_142900-13 sun4v" (alt)
	echo 'PHP_INI=' . php_ini_loaded_file() . ';';
	$php64bit = ((PHP_INT_SIZE == 8) ? '1' : '0');
	//empty(strstr(php_uname("m"), '64')) ?  $php64bit = '0' : $php64bit = '1';
	echo 'X64='.$php64bit.';';
  
	foreach($sys_info as $name=>$wert) {
		echo $name.'='.$wert.';';
	}
?>