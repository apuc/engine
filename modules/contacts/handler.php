<?php
namespace contacts;
use module,db;

#Сторонние модули
require_once(module::$path.'/contacts/class.capcha.php');
#Свои функции
require_once(module::$path.'/../core/mail/send.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function index($send,$capchaSes,$capchaPost,$mail,$txt,$domen){
		$status=array();
		if($send){
			if(empty($capchaPost) or strtolower($capchaPost)!=strtolower($capchaSes))
				$status[]='capcha';
			if(!preg_match("!^[^@]+@[^.@]+\.[^@]+$!",$mail))$status[]='mail';
			if(count($status)==0){
				sendMail("Contact From - FROM: ($domen) $mail",$mail,$txt);
				$status[]='sent';
			}
		}
		return array('status'=>$status,'mail'=>$mail,'txt'=>$txt);
	}
	function capcha(){
		$this->template='';
		capcha();
	}
}
