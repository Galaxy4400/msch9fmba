<?

unset($mail_attachments);
unset($error);

$maxFileSize = 20000; // Mb

//===============================================================
function displayForm($form){
  global $ent, $id;

	$form = is_array($form) ? $form[0] : $form; // учет обработки смарттегов

  $ent['id'] = $id;
  $ent['form'] = $form;
  $ent['key'] = getSystemVariable($db, 'site_key');
  $ent = my_array_merge($ent, populateFromPost());

  return parseTemplate(tplFromFile(dir_prefix."engine/templates/forms/form".$form.".htm"), $ent);
}

//===============================================================
function doProcessForm($formid){
  global $db, $ent, $mailSubject, $actionSub, $Email, $Name, $formProcessSuccess, $ajax, $globalMailTo, $formid;

	$mailTo = $formid == '_attach' ? getSystemVariable($db, 'attachmentMailTo') : $globalMailTo;

	if(!reCaptchaCheck()) {
		$ent['message'] = '<p><b>Запрос выглядит похожим на автоматический.</b></p>';
		$res = parseTemplate(tplFromFile(dir_prefix."engine/templates/forms/error.htm"), $ent);
		if ($ajax) die(json_encode(['message' => $res]));
		return $res;
	}

  switch($actionSub) {
    case "doRegisterUser":	return doRegisterUser(); break;
    case "doRecallPassword":	return doRecallPassword(); break;
		default:
			$ent = my_array_merge($ent, populateFromPost());

			$mail_attachments = doMailAttachFiles();
			$error = $mail_attachments['errors'];

			if (count($error) > 0) {
				$ent['message'] = implode('<br><br>', $error);
				$res = parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
				$formProcessSuccess = false;
			} else {
				doMail($mailTo, $mailSubject, "<html><body>" . nl2br(parseTemplate(tplFromFile(dir_prefix . "engine/templates/forms/email" . $formid . ".htm"), $ent)) . "</body></html>", $Email, $Name, $mail_attachments);
				if ($ajax) {
					$res = parseTemplate(tplFromFile(dir_prefix . "engine/templates/forms/ajax-ok" . language_suffix . ".htm"), $ent);
					die(json_encode(['message' => $res]));
				} else {
					$res = parseTemplate(tplFromFile(dir_prefix . "engine/templates/forms/ok" . language_suffix . ".htm"), $ent);
				} 
				$formProcessSuccess = true;
			}
			break;
  }

  return $res;
}

//===============================================================
function doMailAttachFiles(){
  global $_FILES, $error, $maxFileSize, $formid;

  unset($res);
  $fileURL = $_FILES['file'];
  $tempfile = $fileURL['tmp_name'];
	$ext = utf8_pathinfo($fileURL['name'])['extension'];
  if(trim($tempfile)!=""){
    unset($r);
    $fs = filesize($tempfile) / 1024;
    if ($fs > $maxFileSize) $error[] = "Размер файла 1 (".number_format($fs, 2)." кб) превышает допустимый максимум (".$maxFileSize." кб)";
		if ($formid == "_submission") {
			if ($ext != "jpg" && $ext != "png") {
				$error[] = "Файл должен быть с расширением jpg или png";
			} 
		}
    $r['path'] = $tempfile;
    $r['name'] = $fileURL['name'];
    $res[] = $r;
  }
	
  $res['errors'] = $error;
  return $res;
}

//===============================================================
function reCaptchaCheck() {
	global $db;
	if (isset($_REQUEST['g-recaptcha-response'])) {
		if ($_REQUEST['g-recaptcha-response'] == '') return false;
		$reCaptcha = 'https://www.google.com/recaptcha/api/siteverify?secret='.getSystemVariable($db, 'secret_key').'&response='.$_REQUEST['g-recaptcha-response'];
		$reCaptcha = json_decode(file_get_contents($reCaptcha), true);
		if (!$reCaptcha['success']) {  // || $reCaptcha['score'] < 0.5
			return false;
		}
	}
	return true;
}
