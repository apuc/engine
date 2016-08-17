<?
function userErr($v){#Все ошибки пользователя
	static $err;
	if(!isset($err)){
		$err=array(
			'badName'=>"You have enter bad user name! You can use only alpha and numbers!<br>User name length must be grater then 4 and less then 30!",
			'badMail'=>"You have entered bad email!",
			'badPhone'=>"You have entered bad phone!",
			'badPas'=>"You have enter bad password! You can use only alpha and numbers!<br>Password length must be grater then 4 and less then 30!",
			'mailExists'=>"Another user already use this email or name!",
			'dbError'=>"Database error. Try to register later.",
			'badActivate'=>"Your activation code or login is wrong.",
			'badLogin'=>"Wrong login or password!",
			'badSignup'=>"Error ocured while registering.",
			'badPasSet'=>"Wrong password!"
		);
	}
	return "<div class='error'>{$err[$v]}</div>";
}