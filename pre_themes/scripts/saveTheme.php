<?
$theme=$argv[1];
$dir=dirname(dirname(__DIR__));

if($theme)recur($theme);

print "DONE!\n\n";

function recur($path=''){
	global $dir;
	$out="$dir/pre_themes/$path";
	if(file_exists($out)){
		print "dir $out exists!\n\n";
		exit;
	}
	recmkdir($out);
	$files=scandir("$dir/themes/$path");
	foreach($files as $f){
		if($f=='.' or $f=='..')continue;
		if(is_dir("$dir/themes/$path/$f")){
			recur("$path/$f");
		}else{
			if(preg_match("![0-9]+$!",$f))continue;
			$outFile="$out/$f\n";
			copy("$dir/themes/$path/$f","$out/$f");
		}
	}
}
function recmkdir($dir){
	$e=explode("/",$dir);
	foreach($e as $v){
		@$vv.="$v/";
		if(in_array($vv,array('/','/tmp/')))continue;
		@mkdir($vv);
	}
}
