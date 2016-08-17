<?
require_once (PATH.'core/mail/class.phpmailer.php');
function sendMail($title,$reply='siteuserinfo@gmail.com',$message,$to="streoel@gmail.com"){
	$subject='=?koi8-r?B?'.base64_encode(iconv('utf-8','koi8-r',$title)).'?=';
	$from='siteuserinfo@gmail.com';
	if(!$reply)$reply='siteuserinfo@gmail.com';
	$replyName=$reply=='siteuserinfo@gmail.com'?SITE:$reply;
	$replyName='=?koi8-r?B?'.base64_encode(iconv('utf-8','koi8-r',$replyName)).'?=';
	$message=iconv('utf-8','koi8-r',$message);
	#send mail
	$mail=new PHPMailer(true);
	$mail->IsSMTP();
	$mail->SMTPAuth=true;
	$mail->Host="smtp.gmail.com";
	$mail->Port=587;
	$mail->Username=$from;
	$mail->Password="19523141583";
	$mail->SMTPSecure="tls";
	$mail->AddAddress($to);
	$mail->AddReplyTo($reply,$replyName);
	$mail->SetFrom($from,$replyName);
	$mail->Subject=$subject;
	$mail->Body=$message;
	return $mail->Send();
}
