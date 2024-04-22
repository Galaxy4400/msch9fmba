<?php

require("phpmailer5/PHPMailerAutoload.php");

$mailer = new phpmailer();

function doMail($mailTo, $mailSubject, $mailBody, $mailFrom, $mailFromName, $attachments=NULL, $contentType="text/html"){
	global $mailer;

	$mailTo = trim(str_replace('<br />', ',', str_replace(";", ",", trim($mailTo))));
	// $mailFrom = explode("<br />", trim(str_replace(",", "<br />", str_replace(";", "<br />", $mailFrom))));
	// $mailFrom = $mailFrom[0];

	// print_r($mailTo);
	// die('.');

	if($mailTo=="" || count($mailTo)==0)
		return false;

	$mailReplyTo = $mailFrom;
	
	$mailFrom 				= "mailing@webisgroup.ru";
	$mailer->isSMTP();  
	$mailer->Host 			= 'smtp.gmail.com';
	$mailer->SMTPAuth		= true;
	$mailer->Username		= $mailFrom;
	$mailer->Password		= "nIght011";
	$mailer->SMTPSecure 	= 'ssl'; 
	$mailer->Port 			= 465;     
	
	global $debug;
	if($debug) {
		$mailer->SMTPDebug = true;
		$mailer->Debugoutput = "html";
	}

	$mailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
	
	$mailer->From			= $mailFrom;
	$mailer->FromName		= $mailFromName;
	$mailer->Subject		= $mailSubject;
	$mailer->ContentType	= $contentType;
	$mailer->CharSet		= "utf-8";


	if($contentType=="text/plain") $mailer->Body = strip_tags(str_replace("&nbsp;", "", $mailBody));
	else $mailer->Body = $mailBody;


	$mailToArray = explode(",", $mailTo);

	foreach ($mailToArray as $mailAddress) 
		$mailer->AddAddress($mailAddress);
	
	$mailer->AddReplyTo($mailReplyTo);
	

	for ($i=0; $i<count($attachments); $i++){
		$mailer->AddAttachment($attachments[$i]['path'], $attachments[$i]['name'], $attachments[$i]['cid'], $attachments[$i]['inline']);
	}

	if(!$mailer->Send()){
		$res = false;
		echo("error senging mail: ".$mailer->ErrorInfo."<hr>");
	}
	else $res = true;

	$mailer->ClearAddresses();
	$mailer->ClearAttachments();

	return $res;
}
?>