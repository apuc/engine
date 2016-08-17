<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class images_admin_download extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		$this->act=!empty($input->act)?$input->act:'';

		if($input->act=='downloadImages'){
			$sort='datePublish desc';
			if(!empty($input->sort)){
				if(in_array($input->sort,array('datePublish asc','datePublish desc','countPhoto asc','countPhoto desc'))){
					$sort=$input->sort;
				}
			}
			$this->data=(object)array(
				'page'=>@$input->page>1?(int)$input->page:1,
				'num'=>empty($input->num)?10:(int)$input->num,
				'sort'=>$sort,
				'mainkey'=>empty($input->mainkey)?false:$input->mainkey,
			);
		}elseif($input->act=='showLog'){
			$this->data=(object)array(
				'log'=>empty($input->log)?false:$input->log,
				'tail'=>empty($input->tail)?0:(int)$input->tail,
				'start'=>empty($input->start)?0:(int)$input->start,
			);
		}elseif($input->act=='status'){
			if(!empty($input->upload_new_submit)) {
				$isnew=true;
				$count=empty($input->upload_new)?0:(int)$input->upload_new;
			}elseif(!empty($input->upload_to_submit)) {
				$isnew=false;
				$count=empty($input->upload_to)?0:(int)$input->upload_to;
			}
			$this->data=(object)array(
				'is_new'=>empty($isnew)?false:$isnew,
				'count'=>empty($count)?0:$count,
				'kidpids'=>empty($input->kidpids)?null:$input->kidpids,
				'allowgallery'=>(int)(@$input->allowgallery=='on'),
				'word'=>empty($input->addword)?'':$input->addword,
				'imsize'=>empty($input->imsize)?false:$input->imsize,
				'manimsize'=>empty($input->man)?false:$input->man,
				'skipExists'=>empty($input->skipExists)?false:$input->skipExists,
				'mainkey'=>empty($input->mainkey)?false:$input->mainkey,
			);
		}elseif($input->act=='stopDaemons'){
			$this->data=(object)array();
		}elseif($input->act=='clearCache'){
			$this->data=(object)array();
		}elseif($input->act=='anotherRunning'){
			$this->data=(object)array();
		}
	}
}
