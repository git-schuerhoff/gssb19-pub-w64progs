<?php
session_start();
$wishlisthtml = '';
if($this->phpactive() === true)
{
	if($this->get_setting('cbUsePhpWishlist_Checked') == 'True')
	{
		if($_SESSION['login']['ok'])
		{
			$wldbh = $this->db_connect();
			if(isset($_POST['wishlist']))
			{
				if($_POST['wishlist'] != '')
				{
					$name1 = '';
					$name2 = '';
					if(strpos($_POST['wishlist'], ",") !== false)
					{
						$aName = explode(",",$_POST['wishlist']);
						$name1 = $aName[0];
						$name2 = trim($aName[1]);
					}
					else
					{
						if(strpos($_POST['wishlist'], " ") !== false)
						{
							$aName = explode(" ",$_POST['wishlist']);
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
							$name1 = $_POST['wishlist'];
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
							$csql = "SELECT cusIdNo, cusTitle, cusFirstName, cusLastName, cusZipCode, cusCity FROM " . $this->dbtoken . "customer WHERE " .
									  "cusFirstName = '" . $name1 . "' OR cusLastName = '" . $name1 . "' LIMIT 1";
						}
						else
						{
							$csql = "SELECT cusIdNo, cusTitle, cusFirstName, cusLastName, cusZipCode, cusCity FROM " . $this->dbtoken . "customer WHERE " .
									  "(cusFirstName = '" . $name1 . "' AND cusLastName = '" . $name2 . "') OR " .
									  "(cusFirstName = '" . $name2 . "' AND cusLastName = '" . $name1 . "') LIMIT 1";
						}
						$fullname = '';
						$fullcity = '';
						$cid = 0;
						$cerg = mysqli_query($wldbh,$csql);
						if(mysqli_errno($wldbh) == 0)
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
			$this->content = str_replace('{GSSE_INCL_FULLNAME}', $fullname, $this->content);
			$this->content = str_replace('{GSSE_INCL_CITY}', $fullcity, $this->content);
			$wlsql = "SELECT * FROM " . $this->dbtoken . "wishlist WHERE cusIdNo = '" . $cid . "' ORDER BY date DESC";
			$wlerg = mysqli_query($wldbh,$wlsql);
			if(mysqli_errno($wldbh) == 0)
			{
				if(mysqli_num_rows($wlerg) > 0)
				{
					while($wl = mysqli_fetch_assoc($wlerg))
					{
						$itdbh = $this->db_connect();
						$sql = "SELECT itemItemId, itemItemNumber, itemItemDescription, itemSmallImageFile, " .
								 "(SELECT prcPrice FROM " . $this->dbtoken . "price WHERE " . $this->dbtoken . "price.prcItemCount = " . $this->dbtoken . "itemdata.itemItemId AND " . $this->dbtoken . "price.prcQuantityFrom = '0') AS ItemPrice, " .
								 "itemIsNewItem, itemHasDetail, itemItemPage, itemIsCatalogFlg, " .
								 "itemIsVariant, itemAttribute1, itemAttribute2, itemAttribute3, itemIsTextInput, " .
								 "itemInStockQuantity, itemAvailabilityId, itemDetailText1, " .
								 "itemCheckAge, itemMustAge, itemIsAction " .
								 "FROM " . $this->dbtoken . "itemdata WHERE itemIsActive = 'Y' AND itemItemNumber = '" . $wl['itemNumber'] . "' AND itemLanguageId = '" . $this->lngID . "'";
						$erg = mysqli_query($itdbh,$sql);
						if(mysqli_errno($itdbh) == 0)
						{
							if(mysqli_num_rows($erg) > 0)
							{
								if(isset($_POST['wishlist']))
								{
									$delbutton = '';
								}
								else
								{
									$delbutton = file_get_contents('template/wishlist_delete_button.html');
									$delbutton = str_replace('{GSSE_INCL_DELETE}',$this->get_lngtext('LangTagDelete'),$delbutton);
									$delbutton = str_replace('{GSSE_INCL_WLID}',$wl['wlId'],$delbutton);
								}
								$ratingimg = '';
								$ratingsubj = '';
								$ratingbody = '';
								$ratingdate = '';
								include('inc/items_overview.inc.php');
								$wishlisthtml .= str_replace('{GSSE_INCL_ITEMSOVERVIEWLINES}',$this_inner,$outer);
							}
							else
							{
								if(!isset($_POST['wishlist']))
								{
									$this->db_delete('wishlist','wlId',$wl['wlId']);
								}
							}
							mysqli_free_result($erg);
						}
						//mysqli_close($itdbh);
					}
				}
				else
				{
					
					if(isset($_POST['wishlist']))
					{
						$wishlisthtml = $this->get_lngtext('LangTagSearchResultFor') . '&quot;' . $_POST['wishlist'] . '&quot; - 0 ' . $this->get_lngtext('LangTagMatches');
					}
				}
				mysqli_free_result($wlerg);
			}	
			//mysqli_close($npdbh);
		}
		else
		{
			header("Location: index.php?page=createcustomer");
		}
	}
}

$this->content = str_replace($tag, $wishlisthtml, $this->content);
?>