<?php
//Achtung!!! Parameter werden als Array $aParam übergeben!
//Dabei ist beim "FUNC"-Tag der Parameter ($aParam[0])
//Die Parameter für die Funktion fangen mit $aParam[1]
$jscript = '';
if($this->phpactive() === true)
{
	$res = $this->get_setting('cbUsePhpNewsTicker_Checked');
	if($res == 'True')
	{
		$dbh = $this->db_connect();
		$sql = "SELECT ntContent AS str, ntScrollSpeed AS speed FROM ". $this->dbtoken ."newsticker WHERE ntIdNo = '1' AND ntShowFlg = 'Y' LIMIT 1";
		$erg = mysqli_query($dbh,$sql);
		if(mysqli_errno($dbh) == 0)
		{
			if(mysqli_num_rows($erg) == 1)
			{
				$z = mysqli_fetch_assoc($erg);
				if(trim($z['str']) != "")
				{
					$jscript = '<div class="newsticker"><p id="newsticker"><nobr>'. $z['str'] . '></nobr></p></div>' .
								  '<script language="JavaScript" type="text/javascript">' .
								  'jQuery(document).ready(function ($){' .
								  '$("#newsticker").bxSlider({' .
								  'ticker:true,' .
								  'tickerSpeed: ' .($z['speed']*1000) .
								  '});' .
								  '});' .
								  '</script>';
				}
				else
				{
					$jscript = 'Kein String';
				}
			}
			else
			{
				$jscript = '';
			}
		}
		else
		{
			$jscript = mysqli_error($dbh);
		}
		mysqli_free_result($erg);
		mysqli_close($dbh);
	}
}
$this->content = str_replace($tag, $jscript, $this->content);
?>