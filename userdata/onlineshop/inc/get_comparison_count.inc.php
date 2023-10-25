<?php
session_start();
if(isset($_SESSION['aitems_compare']))
{
	echo count($_SESSION['aitems_compare']);
}
else
{
	echo 0;
}

?>