<?php
$wishlist = '';
$aerg = array();
$aPrices = array();
if($this->phpactive() === true)
{
	if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
	{
		if($_SESSION['login']['ok'])
		{
			if(isset($_GET['wishlist']))
			{
				if($_GET['wishlist'] != '')
				{
					$name1 = '';
					$name2 = '';
					if(strpos($_GET['wishlist'], ",") !== false)
					{
						$aName = explode(",",$_GET['wishlist']);
						$name1 = $aName[0];
						$name2 = trim($aName[1]);
					}
					else
					{
						if(strpos($_GET['wishlist'], " ") !== false)
						{
							$aName = explode(" ",$_GET['wishlist']);
							switch(count($aName))
							{
								case 1:
									$name1 = $aName[0];
									$name2 = '';
									break;
								case 2:
									$name1 = $aName[0];
									$name2 = $aName[1];
									break;
								case 3:
									$name1 = trim($aName[0] . ' ' . $aName[1]);
									$name2 = $aName[2];
									break;
								default:
									$name1 = '';
									$name2 = '';
									break;
							}
						}
						else
						{
							$name1 = $_GET['wishlist'];
							$name2 = '';
						}
					}
					if($name1 == '' && $name2 == '')
					{
						$fullname = '';
						$fullcity = '';
						$cid = 0;
					}
					else
					{
						if($name2 == '')
						{
							$csql = "SELECT cusIdNo, cusTitle, cusFirstName, cusLastName, cusZipCode, cusCity FROM " . $wlse->dbtoken . "customer WHERE " .
									  "cusFirstName = '" . $name1 . "' OR cusLastName = '" . $name1 . "' LIMIT 1";
						}
						else
						{
							$csql = "SELECT cusIdNo, cusTitle, cusFirstName, cusLastName, cusZipCode, cusCity FROM " . $wlse->dbtoken . "customer WHERE " .
									  "(cusFirstName = '" . $name1 . "' AND cusLastName = '" . $name2 . "') OR " .
									  "(cusFirstName = '" . $name2 . "' AND cusLastName = '" . $name1 . "') LIMIT 1";
						}
						$fullname = '';
						$fullcity = '';
						$cid = 0;
						$wlndbh = $this->db_connect();
						$cerg = mysqli_query($wlndbh,$csql);
						if(mysqli_errno($wlndbh) == 0)
						{
							if(mysqli_num_rows($cerg) == 1)
							{
								$c = mysqli_fetch_assoc($cerg);
								$fullname = $c['cusTitle'] . ' ' . $c['cusLastName'] . ', ' . $c['cusFirstName'];
								$fullcity = $c['cusZipCode'] . ' ' . $c['cusCity'];
								$cid = $c['cusIdNo'];
							}
							mysqli_free_result($cerg);
						}
						mysqli_close($wlndbh);
					}
				}
				else
				{
					$fullname = '';
					$fullcity = '';
					$cid = 0;
				}
			}
			else
			{
				$fullname = $_SESSION['login']['cusTitle'] . " " . $_SESSION['login']['cusLastName'] . ", " . $_SESSION['login']['cusFirstName'];
				$fullcity = $_SESSION['login']['cusZipCode'] . " " . $_SESSION['login']['cusCity'];
				$cid = $_SESSION['login']['cusIdNo'];
			}
			$this->content = str_replace('{GSSE_INCL_WLNAME}', $fullname, $this->content);
			$this->content = str_replace('{GSSE_INCL_WLCITY}', $fullcity, $this->content);
			$wishlist = "<script type='text/javascript'>" . $this->crlf .
									"wishlist(" . $cid . ");" . $this->crlf .
									//"show_pgroup(0);" . $this->crlf .
								"</script>";
		}
		else
		{
			header("Location: index.php?page=createcustomer");
		}
	}
}

$this->content = str_replace($tag, $wishlist, $this->content);
?>