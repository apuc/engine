<?
$mail=array(
	'from'=>NAME,
	'activateSubj'=>"Activate account on ".NAME,
	'activateTxt'=>"Hello.Thank you for registration!\nYour login information:\nMail: %mail%\npassword: %pas%\n\nTo activate your account, please click here:\n".url::userActivate()."\n\nBest regards,\nRobot.",
	'restoreSubj'=>"Restore password from ".HREF,
	'restoreTxt'=>"Hello!\n\n".HREF."\n\nlogin: \$mail\npassword: \$pas"
);

if(!empty($data->key))
	echo $mail[$data->key];