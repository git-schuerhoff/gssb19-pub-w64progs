<?php
session_start();
if(isset($_SESSION['sb_settings'])) {
	unset($_SESSION['sb_settings']);
}
if(isset($_SESSION['template'])) {
	unset($_SESSION['template']);
}
?>