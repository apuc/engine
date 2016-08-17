<?php
/*
 * Role Based Access Control
 * */
namespace user;
use module,db,url,StdClass;

#Используем собственные функции
require_once(module::$path.'/../core/mail/send.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	public $ut;# User Types for Access Control
	function __construct(){
		static $user;
		if(!isset($user)){
			#инициализируем пустого пользователя
			$user=new StdClass;
			#авторизируем
			module::exec('user',array(),'data');
		}
		$this->user=&$user;
		$this->template='template';#Определяем в какой шаблон будем вписывать
		/*
		 * role Based Access Control
		 * 
		Контент
			editNews - редактировать любую новoсть
			editNewsMy - редактировать свою новость, если она не опубликована, видеть свои не опубликованные
			editNewsAuthor - редактировать свою новость, с возможностями "автор"
			editNewsSearch - редактировать свою новость, с возможностями "searcher"
			delNews - удалять новости
			publishPost - опубликовать пост
			userSetPost - установить автора для поста
			saveNoTextPost - сохранить незаконченый пост
			statPost - посмотреть статистику постов
			delCat - удалить категорию
			editCat - добавить/редактировать категорию
			showAllCat - видеть пустые категории и no-category
			editorList - видеть фильтры типов контента для редактора
			authorList - видеть фильтры типов контента для автора
			searcherList - видеть фильтры типов контента для серчера
			viewHistory - видеть историю изменения постов
			autoPosting - изменять настройки автопостинга
            voteAccess - голосовать, видеть посты за которые не голосовал
			myVoteList - видеть посты, за которые голосовал
			FBpublish - отмечать добавленные на FB посты
			downloadImages - скачивать новые картинки к постам
			parseKeywords - парсить новые кейворды
			pinpost - прикреплять пост к определённой категории
			editKeyword - редактировать кейворды для поста
		Images
			delImage - удалить любую картинку кроме своей
			delImageMy - удалить свою картинку
		Comments
			commentWithoutModer - добавление коментариев без модерации
		Other func
			specPanel - показывать панель администратора
			privilegeSet - назначение привилегий пользователям
			update - возможность применять update системы
			themesSet - возможность выбирать и редактировать тему

			Если данные в $this->ut - то пересмотреть описание и содержание $this->ut
		*/ 
		$this->ut=array(
			0=>array(
				'specPanel',
				'addNews','editNewsMy','editNewsAuthor',
				'delImageMy',
				'showAllCat',
				'authorList',
			),
			/*
				Админ сайта
			*/
			1=>array(
				'specPanel',
				'editNews','addNews','delNews',
				'delImage',
				'moderateComments',
				'moderImage',
				'commentWithoutModer',
				'publishPost','userSetPost','saveNoTextPost','statPost','setSite',
				'delCat','editCat','showAllCat',
				'privilegeSet',
				'editorList',
				'viewHistory',
				'update',
				'moveContent',
				'autoPosting',
				'voteAccess', 
				'myVoteList', 
				'FBpublish',
				'loadStruct',
				'downloadImages','parseKeywords','parseRepl',
				'pinpost',
				'multiPost',
				'listOfArticles',
				'editKeyword',
				'themesSet','themesSetHandler',
				'editCode', //Добавлять параметры в текст для формирование таблиц и других структур по шаблону
				'editorSaveLinks', //сохранять ссылки в тексте
			),
			/*
				Автор контента
			*/
			2=>array(
				'specPanel',
				'addNews','editNewsMy','editNewsAuthor',
				'chooseSite',
				'delImageMy',
				'showAllCat',
				'authorList',
			),
			/**/
			3=>array(
				'commentWithoutModer'
			),
			/*
				Редактор сайта
			*/
			4=>array(
				'specPanel',
				'addNews','editNews','delNews',
				'delImage',
				'moderImageMy',
				'publishPost','userSetPost','saveNoTextPost','statPost','setSite',
				'delCat','editCat','showAllCat',
				'privilegeSet',
				'editorList',
				'viewHistory',
				'voteAccess',
				'pinpost',
				'editKeyword',
				'commentWithoutModer',
				'themesSet',
				'editorSaveLinks',
			),
			/*
				Серчер
			*/
		   5=>array(
				'specPanel',
				'addNews','editNewsMy','editNewsSearch','delMyUnpublishedNews',
				'delImageMy',
				'showAllCat',
				'searcherList',
			),
		);
	}
	/*
		проверяет наличие пользователя в базе
	*/
	function index($mail,$pas){
		#если не авторизованы, то авторизируемся
		if(!isset($this->user->id)){
			#начальные значения для незарегистрированных пользователей
			$this->user=new StdClass;
			$this->user->id=0;
			$this->user->rbac=0;
			#Проверка пользователя на логин
			if(!empty($mail) && !empty($pas)){
				$d=db::qfetch("SELECT * FROM ".PREFIX_SPEC."users WHERE hash='$pas' && mail='$mail' LIMIT 1");
				if(isset($d->id)){
					db::query("UPDATE ".PREFIX_SPEC."users SET visit=NOW() WHERE id='{$d->id}' LIMIT 1");
					$this->user=$d;
				}
			}
			@$this->headers->cookie->visit=array(time(),"+1 year");
		}
	}
	//Возвращает список доступных действий пользователя
	function access(){
		return (object)array_fill_keys($this->ut[$this->user->rbac],1);
	}
	function rbac($user,$type=''){#Типы действий и разрешенные пользователя к ним
		if($this->user->id===0) return false;
		if($type==''){
			$type=$user;
			$user=@$this->user->rbac;
		}
		foreach($this->ut as $rbac=>$ar){
			foreach($ar as $access){
				$types[$access][$rbac]=1;
			}
		}
		# проверка списка прав
		if(is_array($type))
			foreach ($type as $t) {
				if($res=isset($types[$t][$user])) break;
			}
		else
			$res=isset($types[$type][$user]);
		return $res;
	}
	function rbacByUT($type){#список ролей для которых доступен тип действий: $type
		$result=array();
		foreach($this->ut as $rbac=>$arrTypes){
			if(is_array($type)){
				foreach ($type as $t) {
					if(in_array($t,$arrTypes)){
						$result[$rbac]=$rbac; break;
					}
				}
			}elseif(in_array($type,$arrTypes)) 
				$result[$rbac]=$rbac;
		}
		return $result;
	}
	function user(){#Просто вернуть данные о пользователе
		$this->template='';
		return $this->user;
	}
	function menu(){
		return array('user'=>$this->user);
	}
	function panel(){
		$access=new \stdClass;
		foreach($this->ut[1] as $v){
			$access->$v=$this->rbac($v);
		}
		require_once module::$path.'/posts/admin/func.php';
		$tbls=\posts_admin\getPrefixList();
		array_unshift($tbls, '');
		return array(
			'user'=>$this->user,
			'access'=>$access,
			'tbls'=>$tbls,
		);
	}
	function login($mail,$pas,$remember,$submit,$easy){
		$hashLogin=hash('md5',$pas);
		$er=array();
		if($submit){
			if($mail && $pas){
				$hashLoginE=db::escape($hashLogin);
				$this->user=db::qfetch("SELECT * FROM ".PREFIX_SPEC."users WHERE hash='$hashLoginE' && mail='$mail'");
				if(!@$this->user->id){$er[]='badLogin';}
				else{
					#print "$mail,$pas,$remember,$submit";
					$time=$remember?"+5 year":"";
					@$this->headers->cookie->mail=array($mail,$time);
					$this->headers->cookie->pas=array($hashLogin,$time);
					if(!$easy) $this->headers->location=url::userSettings();
					@db::query("UPDATE ".PREFIX_SPEC."users SET visit=NOW() WHERE id={$this->user->id} limit 1");
				}
			}else
				$er[]='badLogin';
		}
		return (object)array('err'=>$er,'socauth'=>getAuthButtonsUrls('/login.html'));
	}
	function register($umail,$pas,$submit,$from,$easy,$phone,$address,$comment){
		if($pas=='')$pas=mt_rand(50000,10000000);
		$hashLogin=hash('md5',$pas);
		$hashLoginE=db::escape($hashLogin);
		$pas=db::escape($pas);
		$mail=db::escape($umail);
		$phone=preg_replace("![^0-9]!",'',trim($phone));
		$address=db::escape($address);
		$comment=db::escape($comment);
		$er=array();
		if($submit){
			if(!preg_match("!^[^@]+@[^@.]+\.[^@]+$!",$mail)) $er[]='badMail';
			if(!preg_match('!^[\w\d ]{5,30}$!',$pas)) $er[]='badPas';
			if(strlen($phone)>0 && strlen($phone)<10){$er[]='badPhone';}
			list($sameMail)=db::qrow("SELECT mail FROM ".PREFIX_SPEC."users WHERE mail='$mail'");
			if($sameMail){$er[]='mailExists';}
			elseif(count($er)==0){
				$code=mt_rand();
				db::query("INSERT INTO ".PREFIX_SPEC."users SET hash='$hashLoginE', mail='$mail',phone='$phone',address='$address',comment='$comment',code='$code', pas='$pas',regdate=NOW(),visit=NOW()");
				list($id)=db::qrow("SELECT id FROM ".PREFIX_SPEC."users WHERE hash='$hashLoginE' && mail='$mail'");
				if(!$id)$er[]='badSignup';
				else{
					@$this->headers->cookie->mail=array($mail,"+5 year");
					$this->headers->cookie->pas=array($hashLogin,"+5 year");
					if(!$easy)$this->headers->location=url::userSettings();
					$txt=strtr(
						module::exec('user',array('act'=>'tplMail','key'=>'activateTxt'),1)->str,
						array('%mail%'=>$umail,'%pas%'=>$pas,'%code%'=>$code)
					);
					set_time_limit(10);
					$subj=module::exec('user',array('act'=>'tplMail','key'=>'activateSubj'),1)->str;
					$sendStatus=sendMail($subj,'',$txt,$umail);
					set_time_limit(1);
				}
			}
			if(!count($er))
				db::query("UPDATE ".PREFIX_SPEC."users SET visit=NOW() WHERE id=$id");
		}
		return (object)array(
			'err'=>$er,
			'from'=>$from,
			'mail'=>$mail,
			'phone'=>$phone,
			'address'=>$address,
			'comment'=>$comment,
			'socauth'=>getAuthButtonsUrls('/register.html')
		);
	}
	function logout(){
		$this->headers->cookie->mail=$this->headers->cookie->pas=array('','now');
		$this->headers->location=HREF;
		return array();
	}
	function activate($mail,$code){ # Активация
		$er=array();
		list($id)=db::qrow("SELECT id FROM ".PREFIX_SPEC."users WHERE mail='$mail' && code='$code'");
		if($id){
			db::query("UPDATE ".PREFIX_SPEC."users SET code='' WHERE id=$id");
			$this->headers->location=url::userSettings();
		}else
			$er[]='badActivate';
		return (object)array('err'=>$er);
	}
	function settings($pas,$newPas,$name,$oldHash,$submit){
		$setGlobals=$set=$er=$msg=array();
		if($submit){
			if($name){
				if(preg_match('!^[\w\d ]{3,30}$!',$name)){
					$set[]="`name`='$name'";
					$this->user->name=$name;
				}else
					$er[]='badName';
			}
			if($pas){
				$hash=db::escape(hash('md5',$pas));
				list($id)=db::qrow("SELECT id FROM ".PREFIX_SPEC."users WHERE hash='$hash' && mail='{$this->user->mail}'");
				if(!$id){$er[]='badPas';}
				else{
					if(preg_match('!^[\w\d ]{4,30}$!',$newPas)){
						$this->headers->cookie->pas=array($hash=hash('md5',$newPas),"+5 years");
						$newHash=db::escape($hash);
						$set[]="`pas`='".db::escape($newPas)."',`hash`='$newHash'";
					}else  $er[]='badPas';
				}
			}
			if($set){
				if(empty($id)){
					list($id)=db::qrow("SELECT id FROM ".PREFIX_SPEC."users WHERE hash='$oldHash' && mail='{$this->user->mail}'");
				}
				if(!$id){$er[]='badPas';}
				else{
					$set=implode(',',$set);
					db::query("UPDATE ".PREFIX_SPEC."users SET $set WHERE id=$id LIMIT 1");
					$msg[]='settings';
				}
			}
		}
		if(empty($setGlobals['cpas']))$setGlobals['cpas']=$oldHash;
		return (object)array('user'=>$this->user,'err'=>$er,'msg'=>$msg);
	}
	function restore($mailEsc){
		$err=array();
		if($mailEsc){
			list($pass,$mail)=db::qrow("SELECT `pas`,`mail` FROM `".PREFIX_SPEC."users` WHERE `mail`='$mailEsc' LIMIT 1");
			if($pass){
				$txt=strtr(
					module::exec('user',array('act'=>'tplMail','key'=>'restoreTxt'),1)->str,
					array('$pas'=>$pass,'$mail'=>$mail)
				);
				set_time_limit(10);
				$subj=module::exec('user',array('act'=>'tplMail','key'=>'restoreSubj'),1)->str;
				if(!sendMail($subj,'',$txt,$mail)){
					$err[]='badMail';
				}
				set_time_limit(1);
			}else
				$err[]='badMail';
		}
		return (object)array('err'=>$err,'mail'=>$mailEsc);
	}
	/**
	 * Авторизация через соц сети
	 */
	function auth($service,$redirect){
		if($service==='facebook'){
			if(isset($_GET['error'])){
				$this->headers->location=$redirect;
				return;
			}
			$ch=curl_init();
			curl_setopt($ch,CURLOPT_URL,"https://graph.facebook.com/oauth/access_token?client_id=".AUTH_FACEBOOK_CLIENT_ID."&client_secret=".AUTH_FACEBOOK_CLIENT_SECRET."&redirect_uri=".HREF."/auth/facebook/&code=".$_GET['code']);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			$result=curl_exec($ch);
			if(!$result){
				exit(curl_error($ch));
			}
			curl_close($ch);
			if($json=json_decode($result)){
				if($json->error){
					exit($json->error->message);
				}
			}else{
				parse_str($result,$token);
				if($token['access_token']){
					$ch=curl_init();
					curl_setopt($ch,CURLOPT_URL,"https://graph.facebook.com/me?access_token=".$token['access_token']);
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
					$result=curl_exec($ch);
					if(!$result) {
						exit(curl_error($ch));
					}
					$data=json_decode($result);
					if($data){
						authSave($this->headers->cookie,'facebook',$token['access_token'],$data->email,$data->name);

						if($redirect=='/login.html' || $redirect=='/register.html'){
							$redirect='/';
						}
						$this->headers->location=$redirect;
						return;
					}
				}
			}
		}elseif($service==='google'){
			if(isset($_GET['error'])){
				$this->headers->location=$redirect;
				return;
			}
			$ch=curl_init();
			curl_setopt($ch,CURLOPT_URL,"https://accounts.google.com/o/oauth2/token");
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,'client_id='.AUTH_GOOGLE_CLIENT_ID.'&client_secret='.AUTH_GOOGLE_CLIENT_SECRET.'&redirect_uri='.HREF.'/auth/google/&grant_type=authorization_code&code='.$_GET['code']);
			$result=curl_exec($ch);
			if(!$result){
				exit(curl_error($ch));
			}
			curl_close($ch);
			$json=json_decode($result);
			if(isset($json->error)){
				exit($json->error->message);
			}
			if($json->access_token){
				$data=json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo'.'?access_token='.$json->access_token));
				if($data && $data->id){
					authSave($this->headers->cookie,'google',$json->access_token,$data->email,$data->name);
					
					if($redirect=='/login.html' || $redirect=='/register.html'){
						$redirect='/';
					}
					$this->headers->location=$redirect;
					return;
				}
			}
		}elseif($service==='twitter'){
			session_start();
			if(isset($_GET['denied']) || !isset($_SESSION['twitter_tokens'][$_GET['oauth_token']])){
				$this->headers->location='/';
				return;
			}
			include __DIR__.'/files/twitteroauth/autoload.php';
			$twitteroauth=new \Abraham\TwitterOAuth\TwitterOAuth(AUTH_TWITTER_CONSUMER_KEY,AUTH_TWITTER_CONSUMER_SECRET, $_GET['oauth_token'], $_SESSION['twitter_tokens'][$_GET['oauth_token']]);
			$access_token = $twitteroauth->oauth("oauth/access_token", array("oauth_verifier" => $_GET['oauth_verifier']));
			if($access_token['oauth_token']){
				$twitteroauth=new \Abraham\TwitterOAuth\TwitterOAuth(AUTH_TWITTER_CONSUMER_KEY,AUTH_TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
				$data = $twitteroauth->get('account/verify_credentials');
				if($data && $data->id){
					authSave($this->headers->cookie,'twitter',$access_token['oauth_token'],$data->screen_name.'@twitter.com',$data->name);
					$this->headers->location='/';
					unset($_SESSION['twitter_tokens'][$_GET['oauth_token']]);
					return;
				}
			}
		}
		exit;
	}
	
	/**
	 * Переход на twitter при авторизации
	 */
	function twitterAuth(){
		include __DIR__.'/files/twitteroauth/autoload.php';
		$twitteroauth=new \Abraham\TwitterOAuth\TwitterOAuth(AUTH_TWITTER_CONSUMER_KEY,AUTH_TWITTER_CONSUMER_SECRET);
		$request_token=$twitteroauth->oauth('oauth/request_token',array('oauth_callback'=>HREF.'/auth/twitter/'));
		if(!$request_token){
			$this->headers->location='/';
		}
		session_start();
		if(!isset($_SESSION['twitter_tokens'])){
			$_SESSION['twitter_tokens']=array();
		}
		$_SESSION['twitter_tokens'][$request_token['oauth_token']] = $request_token['oauth_token_secret'];
		$url=$twitteroauth->url('oauth/authorize',array('oauth_token'=>$request_token['oauth_token']));
		if($url){
			$this->headers->location=$url;
		}else{
			$this->headers->location='/';
		}
		return;
	}
	/*
		обработчик шаблонов писем
	*/
	function tplMail($key){
		$data=new stdClass;
		$data->key=$key;
		return $data;
	}
}

/**
 * Авторизация на сайте после прихода от соц сети
 */
function authSave($cookie,$service,$access_token,$email,$name){
	$hash=md5($service.$email.$access_token);

	$cookie->mail=array($email,"");
	$cookie->pas=array($hash,"");

	$email=db::escape($email);
	$name=db::escape($name);
	$token=db::escape($access_token);
	$date=date('Y-m-d');
	$row=db::qfetch("SELECT id FROM `".PREFIX_SPEC."users` WHERE service='".$service."' AND mail='".$email."'");
	if($row && $row->id){
		db::query(sprintf("UPDATE `".PREFIX_SPEC."users` SET name='%s',visit='%s',hash='%s',token='%s' WHERE id=%d", $name,$date,$hash,$token,$row->id));
	}else{
		db::query(sprintf("INSERT INTO `".PREFIX_SPEC."users` (name, mail, regdate, visit, rbac, hash, service, token) VALUES ('%s','%s','%s','%s',%d,'%s','%s','%s')", $name,$email,$date,$date,0,$hash,$service,$token));
	}
}

/**
 * Ссылки для кнопок авторизации в соцсетях
 */
function getAuthButtonsUrls($redirect_uri=null){
	if(!defined('AUTH_FACEBOOK_CLIENT_ID')||!defined('AUTH_GOOGLE_CLIENT_ID')||!defined('AUTH_TWITTER_CONSUMER_KEY')){
		return;
	}
	if(!$redirect_uri){
		$redirect_uri=$_SERVER['REQUEST_URI'];
	}
	$facebook_url='https://www.facebook.com/dialog/oauth?client_id='.AUTH_FACEBOOK_CLIENT_ID.'&redirect_uri='.HREF.'/auth/facebook/&state='.urlencode($redirect_uri).'&response_type=code&scope=email,public_profile';
	$google_url='https://accounts.google.com/o/oauth2/auth?redirect_uri='.HREF.'/auth/google/&response_type=code&client_id='.AUTH_GOOGLE_CLIENT_ID.'&state='.urlencode($redirect_uri).'&scope='.urlencode('https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
	$twitter_url='/?module=user&act=twitterAuth';

	return (object)array(
		'facebook'=>$facebook_url,
		'google'=>$google_url,
		'twitter'=>$twitter_url
	);
}
