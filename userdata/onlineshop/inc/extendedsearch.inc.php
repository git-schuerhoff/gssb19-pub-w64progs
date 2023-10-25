<?php
// Begin Form anzeigen
$tmplFile = "extendedsearchform.html";
$form = $this->gs_file_get_contents('template/' . $tmplFile);
$form = str_replace ('{GSSE_VAL_LANG}', $this->lngID, $form);
$formTags = $this->get_tags_ret($form);
$form = $this->parse_texts($formTags,$form);
$this->content = str_replace ('{GSSE_FUNC_EXTENDEDSEARCH}', $form, $this->content);
// End Form anzeigen
?>