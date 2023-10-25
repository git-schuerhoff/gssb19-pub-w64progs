<?php
session_start();

$this->content = str_replace('{GSSE_INCL_ITEMIMG}', $_SESSION['aitem']['itemSmallImageFile'], $this->content);
$this->content = str_replace('{GSSE_INCL_ITEMNAME}', $_SESSION['aitem']['itemItemNumber'] . ' ' . $_SESSION['aitem']['itemItemDescription'], $this->content);
$this->content = str_replace('{GSSE_INCL_ITEMNAMEESC}', urlencode($_SESSION['aitem']['itemItemNumber'] . ' ' . $_SESSION['aitem']['itemItemDescription']), $this->content);
//$this->content = str_replace('{GSSE_INCL_ITEMLNK}', $this->get_setting('edAbsoluteShopPath_Text') . 'index.php?page=detail&idx=' . $_SESSION['aitem']['itemItemId'], $this->content);
$this->content = str_replace('{GSSE_INCL_ITEMLNK}', $this->shopurl . 'index.php?page=detail&idx=' . $_SESSION['aitem']['itemItemId'], $this->content);
$this->content = str_replace('{GSSE_INCL_RECEMAIL}', $this->get_setting('edOrderEmail_Text'), $this->content);

$this->content = str_replace($tag, '', $this->content);
?>