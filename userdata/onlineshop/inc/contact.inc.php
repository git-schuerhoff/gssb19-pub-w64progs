<?php
$this->content = str_replace('{GSSE_INCL_RECEMAIL}', $this->get_setting('edOrderEmail_Text'), $this->content);

$this->content = str_replace($tag, '', $this->content);
?>