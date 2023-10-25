<?php
/**
 * @author Jan Reker
 *
 */
class logfilewriter {

  var $directory;
  var $filename;
  var $bTimestamp;
  var $handle;

  /**
   * Opens or creates a logfile
   * @param $filename Name of logfile
   * @param $bTimestamp=true If true, appeds timestamp to filename
   */
  function __construct($filename, $bTimestamp=true) {

    $this->filename = $filename;
    $this->bTimestamp = $bTimestamp;
    $this->handle = false;

    //search logs dir, depth $i
    $dir = "../logs/";
    $i = 0;
    while (!is_dir($dir) && $i < 3) {
      $i++;
      $dir = "../" . $dir;
    }
    $this->directory = $dir;

		//append timestamp
    if ($bTimestamp)
      $this->filename .= "_".date("Y-m-d_His");

    //create or open file
    $file = $this->directory.$this->filename;
		$handle = @fopen($file, "a");
    if ($handle)
      $this->handle = $handle;
  }

  /**
   * Write line to logfile
   *
   * @param $str String to write
   * @param $bTimestamp=true if true, every line will begin with a timestamp
   * @param $linefeed="\r\n"
   *
   */
  function write($str, $bTimestamp=true, $linefeed="\r\n") {

    if ($this->handle) {
      if($bTimestamp)
        $str = date("Y-m-d H:i:s")." ".$str;

      $str .= $linefeed;

      @fwrite($this->handle, $str);
    }
  }
}
?>