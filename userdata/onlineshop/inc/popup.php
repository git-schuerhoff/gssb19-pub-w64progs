<?php
 	@session_start();
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	header("Cache-Control: post-check=0, pre-check=0", FALSE);
 
	chdir("../");
	include_once("inc/class.shopengine.php"); 
	$se = new gs_shopengine();
	
	$tmplFile = "popup.html";
	$popuphtml = $se->gs_file_get_contents('template/' . $tmplFile);
	if (isset($_GET['content'])){
		switch ($_GET['content']){
			// Versandkosten
			case "VInfo":
				$page = str_replace("{GSSE_LANG_popuplang}", $se->get_lngtext('LangTag__FieldPostage'), $popuphtml);
				$page = str_replace("{GSSE_TXDB_popupcontent}", $se->db_text_ret('contentpool|Text|Name|VInfo'), $page);
			break;
			// Impressum
			case "imprint":
				$page = str_replace("{GSSE_LANG_popuplang}", $se->get_lngtext('LangTagImprint'), $popuphtml);
				$page = str_replace("{GSSE_TXDB_popupcontent}", $se->db_text_ret('settingmemo|SettingMemo|SettingName|memoImprint'), $page);
			break;
			// AGB
			case "AGB":
				$page = str_replace("{GSSE_LANG_popuplang}", $se->get_lngtext('LangTagButtonCond'), $popuphtml);
				$page = str_replace("{GSSE_TXDB_popupcontent}", $se->db_text_ret('settingmemo|SettingMemo|SettingName|memoTerminsAndConds'), $page);
			break;
			// Datenschutzerklärung
			case "privacy":
				$page = str_replace("{GSSE_LANG_popuplang}", $se->get_lngtext('LangTagPrivacy'), $popuphtml);
				$page = str_replace("{GSSE_TXDB_popupcontent}", $se->db_text_ret('settingmemo|SettingMemo|SettingName|memoPricacyInfos'), $page);
			break;
			// Widerruf
			case "revocation":
				$page = str_replace("{GSSE_LANG_popuplang}", $se->get_lngtext('LangTagTermsAndCondWithdrawal'), $popuphtml);
				$page = str_replace("{GSSE_TXDB_popupcontent}", $se->db_text_ret('settingmemo|SettingMemo|SettingName|memoRightOfRevocation'), $page);
			break;
		}
		$page = str_replace("{GSSE_LANG_LangTagButtonCloseWindow}", $se->get_lngtext('LangTagButtonCloseWindow'), $page);
		$page = str_replace("{GSSE_LANG_LangTagPrint}", $se->get_lngtext('LangTagPrint'), $page);
	}
	echo $page;
?>