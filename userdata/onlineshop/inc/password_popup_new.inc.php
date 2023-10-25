<?php
	session_start();
	if($_SESSION['login']['ok'] === true)
	{
		$cusemail = $_SESSION['login']['cusEMail'];
		$this->content = str_replace('{GSSE_SESS_CUSEMAIL}', $cusemail, $this->content);
		$this->content = str_replace('{GSSE_FUNC_PASSWORD_POPUP_NEW}', '', $this->content);
		if(isset($_POST['passwordnew']) and isset($_POST['passwordrepetition']) and isset($_POST['usermail']) and isset($_POST['passwordold']))
		{
			// Wenn neue Passwörter identisch sind und nicht gleich mit dem alten Password
			if(($_POST['passwordnew'] == $_POST['passwordrepetition']) and ($_POST['passwordnew'] <> $_POST['passwordold']))
			{
				//Das Passwort sollte mindestens 6 Zeichen enthalten.
				if(strlen($_POST['passwordnew']) > 5 and $_POST['passwordold'] <> '')
				{
					if($this->set_new_password($cusemail, $_POST['passwordnew']))
					{
						$tmplFile = "okbox.html";
						$msg = file_get_contents('template/' . $tmplFile);
						$msg = str_replace('{GSSE_LANG_LangTagMsgChangePasswordSuccess}', $this->get_lngtext('LangTagMsgChangePasswordSuccess'), $msg);
						$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
					}
					else
					{
						$tmplFile = "errorbox.html";
						$msg = file_get_contents('template/' . $tmplFile);
						$msg = str_replace('{GSSE_MSG_ERRORNEW}', $this->get_lngtext('LangTagMsgChangePasswordUpdateError'), $msg);
						$msg = str_replace('{GSSE_MSG_ERRORNEWCLASS}', 'error', $msg);
						$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
					}
				}
				elseif($_POST['passwordold'] <> '')
				{
					$tmplFile = "errorbox.html";
					$msg = file_get_contents('template/' . $tmplFile);
					$msg = str_replace('{GSSE_MSG_ERRORNEW}', $this->get_lngtext('LangTagMsgChangePasswordEmptyPasswordError'), $msg);
					$msg = str_replace('{GSSE_MSG_ERRORNEWCLASS}', 'error', $msg);
					$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
				}
				else
				{
					$tmplFile = "errorbox.html";
					$msg = file_get_contents('template/' . $tmplFile);
					$msg = str_replace('{GSSE_MSG_ERRORNEW}', $this->get_lngtext('LangTagMsgChangePasswordRepetitionError'), $msg);
					$msg = str_replace('{GSSE_MSG_ERRORNEWCLASS}', 'error', $msg);
					$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
				}
			} 
			// Wenn das neue Password falsch wiederholt ist
			elseif($_POST['passwordnew'] <> $_POST['passwordrepetition'])
			{
				$tmplFile = "errorbox.html";
				$msg = file_get_contents('template/' . $tmplFile);
				$msg = str_replace('{GSSE_MSG_ERRORNEW}', $this->get_lngtext('LangTagMsgChangePasswordRepetitionError'), $msg);
				$msg = str_replace('{GSSE_MSG_ERRORNEWCLASS}', 'error', $msg);
				$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
			}
			// Wenn das alte Password nicht korrekt ist
			elseif($_POST['passwordold'] == '' || $_POST['passwordold'] <> $_SESSION['login']['cusPassword'])
			{
				$tmplFile = "errorbox.html";
				$msg = file_get_contents('template/' . $tmplFile);
				$msg = str_replace('{GSSE_MSG_ERRORNEW}', $this->get_lngtext('LangTagMsgChangePasswordRepetitionError'), $msg);
				$msg = str_replace('{GSSE_MSG_ERRORNEWCLASS}', 'error', $msg);
				$this->content = str_replace('{GSSE_MSG_PASS}', $msg, $this->content);
			}
		}
		else
		{
			$this->content = str_replace('{GSSE_MSG_PASS}', '', $this->content);
		}
	}
?>