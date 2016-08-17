<?php
 /*
  * Должен возвращать
  * $this->data - объект обработанных входных переменных
  * $this->act - какую функцию обработки используем
  */
class images extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$this->act='index';
		if(in_array(@$input->act,array('save'))){
			$this->act=$input->act;
		}elseif($input->act=='genImg'&&!empty($input->img)){
			$this->act=$input->act;
			$this->data=(object)array(
				'img'=>$input->img,
				'dir'=>(empty($input->dir)?'modules/images/files/images/':$input->dir.'/'),
			);
		}elseif($input->act=='hotlink'&&!empty($input->img)){
			$this->act=$input->act;
			$this->data=(object)array(
				'img'=>$input->img,
			);
		}elseif($input->act=='mkThumb'){
			$this->act=$input->act;
			if(isset($_GET['size'])){
				list($input->sizeW,$input->sizeH)=explode('_',$_GET['size']);
			}
			$this->data=(object)array(
				'img'=>$input->image,
				'sizeW'=>@(int)$input->sizeW,
				'sizeH'=>@(int)$input->sizeH,
				'easy'=>$input->easy,
			);
		}elseif($input->act=='download'){
			$this->act=$input->act;
			$this->data=(object)array(
				'file'=>$input->file,
			);
		}elseif($input->act=='downloadResize'){
			$this->act=$input->act;
			$this->data=(object)array(
				'file'=>!empty($input->file)?$input->file:false,
				'x'=>!empty($input->x)?$input->x:false,
				'y'=>!empty($input->y)?$input->y:false,
			);
		}
	}
}
