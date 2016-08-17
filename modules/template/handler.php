<?php
namespace template;
use module,db;

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function template(){#Делаем все обработки для вывода данных
		$uhandler=module::exec('user',array(),'data')->handler;
		$data=new \stdClass;
		$data->userMenu=module::exec('user',array('act'=>'menu'),1)->str;
		$data->panel=module::exec('user',array('act'=>'panel'),1)->str;
		#Проверяем autoPosting посты
		module::exec('moveContent',array('act'=>'autoPostingCheck'),1);

		$data->status='';
		#проверяем права на запись нужных модулей
		if($uhandler->user->rbac==1){
			$data->status.=module::exec('images/admin',array('act'=>'status'),1)->str;
			$data->status.=module::exec('admin/themes',array('act'=>'status'),1)->str;
		}
		return $data;
	}
}