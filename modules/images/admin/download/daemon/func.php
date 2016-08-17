<?php
function sizeFilter($params,$img){
	if(empty($params)) return 1;
	#size check
	if(!empty($params['imsize'])){
		foreach ($params['imsize'] as $val) {
			list($w,$h)=explode("x", $val);
			if($img->ow==$w&&$img->oh==$h) return true;
		}
		return false;
	}
	#ratio check
	if(!empty($params['ratio'])){
		foreach ($params['ratio'] as $val) {
			list($rw,$rh)=explode(":", $val);
			if(($img->ow*$rh)!=($img->oh*$rw)) return false;
		}
	}
	#limit dimensions
	if(!empty($params['lim'])){
		$isw=!empty($params['lim']['w']);
		$ish=!empty($params['lim']['h']);
		if(@$params['lim']['type']=='m'){
			if($img->ow<$params['lim']['w']&&$isw) return false;
			if($img->oh<$params['lim']['h']&&$ish) return false;
		}elseif(@$params['lim']['type']=='lth'){
			if($img->ow>$params['lim']['w']&&$isw) return false;
			if($img->oh>$params['lim']['h']&&$ish) return false;
		}elseif(@$params['lim']['type']=='e'){
			if($img->ow!=$params['lim']['w']&&$isw) return false;
			if($img->oh!=$params['lim']['h']&&$ish) return false;
		}

	}
	return true;
}
?>