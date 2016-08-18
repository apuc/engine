<?
/*
	using editor "Ace"
		- How to https://ace.c9.io/#nav=howto
		- js plugin dir /modules/admin/themes/tpl/files/ace

*/
$tpl->title="Edit theme: {$data->theme}";
?>
<script src="<?=HREF?>/modules/admin/themes/tpl/files/ace/min/ace.js"></script>
<?$tpl->headlink='<link rel="stylesheet" type="text/css" href="'.HREF.'/modules/admin/themes/tpl/files/jquery-ui/jquery-ui.min.css"/>';?>
<script src="<?=HREF?>/modules/admin/themes/tpl/files/jquery-ui/jquery-ui.min.js"></script>
<style type="text/css">
	.edittheme>div{display: inline-block;vertical-align: top}
	.edittheme .sidebar,.edittheme .right-sidebar{width: 240px;}
	.edittheme .content{width: 690px;margin: 0 8px;}
	.edittheme .sidebar h2{margin: 0 0 5px 0}
	.edittheme .sidebar>ul{padding:0 !important;}
	.edittheme .sidebar ul{padding: 0 0 0 20px;cursor: pointer;}
	.edittheme .sidebar li span{display: inline-block;padding: 0 4px;vertical-align: top;}
	.edittheme .sidebar li span:hover{background-color: #F1F1F1;}
	.edittheme .sidebar li a{display:inline-block;vertical-align:top;}
	.edittheme .sidebar ul>li>ul{display: none;}
	.edittheme .content input[type="button"]{display: block;margin: auto 0 auto auto;}
	#editor{display: block;width: 100%;height: 700px;white-space: pre;margin: 3px 0 0 0;}
	#style-editor{display: block;width: 100%;height: 300px;white-space: pre;margin: 3px 0 0 0;}
	.success{display: block;padding: 2px 5px 4px;background: #90EE90; color:#FFFFFF; font-weight: bold; font-size: 1em}
	.warning{display: block;padding: 2px 5px 4px;background: #FF0000; color:#FFFFFF; font-weight: bold; font-size: 1em}
	.del-icon {
		background: url('<?=HREF?>/files/icons/admin/themes/iset.png') no-repeat scroll 2px -262px / auto auto;
		display: inline-block;
		height: 17px;
		width: 14px;
		padding: 0;
	}
	.del-icon:hover{opacity: 0.4;}
	.new-file-icon {
		background: url('<?=HREF?>/files/icons/admin/themes/iset.png') no-repeat scroll -92px -262px / auto auto;
		display: inline-block;
		height: 17px;
		width: 14px;
		padding: 0;
	}
	.new-file-icon:hover{opacity: 0.4;}
</style>
<script type="text/javascript">
	(function(){
		//init empty editor
		var editor={};
		var styleeditor={};
		var theme='';
		var controlEditor={
			styleEditorControlView:function (){
				var editor=$('#style-editor');
				if(getCookie('style-editor')=='true')
					editor.css({'display':'block'});
				else
					editor.css({'display':'none'});

				if($('a.style-editor-view').length > 0) return;

				editor.before('<a class="style-editor-view">styles <span></span></a>');
				var editorview=$('a.style-editor-view');
				editorview.css({'cursor':'pointer','text-decoration':'none'});
				var detectView=function(){
					var view=editor.css('display')!='none';
					editorview.children('span').html(view?'&#9650;':'&#9660;');
					return view;
				};
				detectView();
				
				editorview.click(function(){
					editor.slideToggle(200,function(){
						setCookie('style-editor',detectView(),'365');
						styleeditor.resize();
					});
				});
			},
			detach:function(){
				var editor=$('#style-editor');
				editor.css({'display':'none'});
				var editorview=$('a.style-editor-view');
				editorview.detach();
			}
		};

		$(document).ready(function(){
			//set current theme
			theme=switchTheme();
			//устанавливаем события
			//события дерева файлов
			eventList();
			eventCloneList();
			//кнопки save
			$('.content input[name="save"]').click(save);
			var isCtrl = false;
			document.onkeyup=function(e){
				if(e.keyCode == 17) isCtrl=false;
			}
			document.onkeydown=function(e){
				if(e.keyCode == 17) isCtrl=true;
				if(e.keyCode == 83 && isCtrl == true) {
					save();
					return false;
				}
			}
			//start editor
			editor=ace.edit("editor");
			editor.setTheme("ace/theme/chrome");
			editor.getSession().setMode("ace/mode/php");
			//start style-editor
			styleeditor=ace.edit("style-editor");
			styleeditor.setTheme("ace/theme/chrome");
			styleeditor.getSession().setMode("ace/mode/css");
			//clone
			$('.right-sidebar li span').click(function(){
				$(this).parent('li').children('ul').slideToggle(200);
			});
			$('.right-sidebar .clone-tpl').click(eventClone);
			//extedit button
			$('#extedit input').change(handlerExt);
		});

		var eventClone=function(){
			theme=switchTheme();
			var path=buildPath($(this));
			var li=$(this).parent('li');
			if(path) path.reverse();
			else path=new Array();
			if(li.children('i').html()){
				path.push(li.children('i').html());	
			}else if(li.children('span').html()){
				path.push(li.children('span').html());	
			}
			cloneTpl(path);
		};
		var eventList=function(){
			$('#themefiles li span').click(function(){
				$(this).parent('li').children('ul').slideToggle(200);
			});
			$('#themefiles ul li a').click(eventOpenToEdit);
			$('#themefiles .del-tpl').click(eventDetachFromTree);
			$('#themefiles .newfile-tpl').click(eventNewFile);
		};
		var eventCloneList=function(){
			$('#clonefiles ul li i').click(eventOpenToEditClone);
		}
		var eventOpenToEdit=function(){
			theme=switchTheme();
			var path=buildPath($(this));
			path.reverse();
			path.push($(this).html());
			clearMessage();
			openToEdit(path);
		}
		var eventOpenToEditClone=function(){
			theme=switchTheme('clone');
			var path=buildPath($(this));
			path.reverse();
			path.push($(this).html());
			clearMessage();
			openToEdit(path);
		}
		var eventDetachFromTree=function(){
			var path=buildPath($(this));
			var li=$(this).parent('li');
			if(path) path.reverse();
			else path=new Array();
			if(li.children('a').html()){
				path.push(li.children('a').html()); 
			}else if(li.children('span').html()){
				path.push(li.children('span').html());  
			}
			detachTpl(path,this);
		};
		var eventNewFile=function(){
			var path=buildPath($(this));
			var li=$(this).parent('li');
			if(path) path.reverse();
			else path=new Array();
			if(li.children('a').html()){
				path.push(li.children('a').html()); 
			}else if(li.children('span').html()){
				path.push(li.children('span').html());
			}
			var fileList=this;
			$('#dialog').dialog();
			//new file
			var buttonNewFile=$('#dialog_file_btn');
			buttonNewFile.unbind('click');
			buttonNewFile.click(function(){
				var name=$('#dialog_file_name').val();
				if(name!=''){
					path.push(name);
					createTpl(path,fileList);
				}else
					return;
				$('#dialog').dialog('close');
			});
			//new dir
			var buttonNewDir=$('#dialog_dir_btn');
			buttonNewDir.unbind('click');
			buttonNewDir.click(function(){
				var nameDir=$('#dialog_dir_name').val();
				if(nameDir!=''){
					path.push(nameDir);
					createDir(path,fileList);
				}else
					return;
				$('#dialog').dialog('close');
			});
			//browse file
			var buttonBrowseFile=$('#dialog input:file');
			buttonBrowseFile.unbind('change');
			buttonBrowseFile.change(function(){
				var file=this.files[0];
				uploadFileTpl(path,file,fileList);
				$('#dialog').dialog('close');
			});
		};

		var eventNewDir=function(){
			var path=buildPath($(this));
			var li=$(this).parent('li');
			if(path) path.reverse();
			else path=new Array();
			if(li.children('a').html()){
				path.push(li.children('a').html());
			}else if(li.children('span').html()){
				path.push(li.children('span').html());
			}
			var fileList=this;
			$('#dialog').dialog();
			//new file
			var buttonNewDir=$('#dialog_dir_btn');
			buttonNewDir.unbind('click');
			buttonNewDir.click(function(){
				var nameDir=$('#dialog_dir_name').val();
				if(nameDir!=''){
					path.push(nameDir);
					createTpl(path,fileList);
				}else
					return;
				$('#dialog').dialog('close');
			});
			//browse file
			var buttonBrowseFile=$('#dialog input:file');
			buttonBrowseFile.unbind('change');
			buttonBrowseFile.change(function(){
				var file=this.files[0];
				uploadFileTpl(path,file,fileList);
				$('#dialog').dialog('close');
			});
		};

		//functions
		function uploadFileTpl(path,file,el){
			var data=new FormData();
			data.append('act','uploadFile');
			data.append('file',file);
			$(path).each(function(indx,itm){
				if(itm)
					data.append('path[]',itm);
			});
			data.append('theme',$('input[name="theme"]').val());
			$.ajax({
				url: document.location.basepath+'?module=admin/themes',
				type: 'POST',
				data: data,
				cache: false,
				processData: false,
				contentType: false,
				success:function(respond){
					var treeTargetEl=$(el).parent('li').children('ul');
					treeTargetEl.append(respond);
					renewEvents();
				},
			});
		}
		function openToEdit(path){
			//сохраняем имя файла в форме
			$('input[name="path"]').val(path);
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'openFile',path:path,theme:theme},
				function (answer){
					var head=$('#edittheme-head');
					head.html('['+theme+'] '+path.join('/'));
					editor.setValue(answer);
					editor.gotoLine(0);
				}
			);
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'openFileCss',path:path,theme:theme},
				function (answer){
					if(answer=='nocss'){
						styleeditor.setValue('');
						controlEditor.detach();
					}else{
						styleeditor.setValue(answer);
						styleeditor.gotoLine(0);
						controlEditor.styleEditorControlView();
					}
				}
			);
		}
		function buildPath(el){
			var p=[];
			var dir=el.parent('li').parent('ul').parent('li').children('span');
			if(dir.html()){
				var name=dir.html();
				p.push(name);
				path=p.concat(buildPath(dir));
				return path;
			}
		}
		function save(){
			var path=$('input[name="path"]').val();
			if(!theme||!path) return;
			if(!$.isArray(path)){
				path=path.split(',');
			}
			var head=$('#edittheme-head');
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'saveFile',path:path,theme:theme,text:editor.getValue(),css:styleeditor.getValue()},
				function (answer){
					clearMessage();
					if(answer=='done'){
						head.after('<span class="success">saved</span>');
					}else{
						head.after('<span class="warning">'+answer+'</span>');
					}
					clearMessage(1);
				}
			);
		}
		function detachTpl(path,el){
			if(!confirm("Are you sure?")) return;
			var head=$('#edittheme-head');
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'delTpl',path:path,theme:$('input[name="theme"]').val()},
				function (answer){
					clearMessage();
					if(answer=='done'){
						head.after('<span class="success">saved</span>');
						$(el).parent('li').remove();
					}else{
						head.after('<span class="warning">'+answer+'</span>');
					}
				}
			);
		}
		//request to create file
		function createTpl(path,el){
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'createTpl',path:path,theme:theme},
				function (answer){
					console.log(answer);
					openToEdit(path,theme);
					var treeTargetEl=$(el).parent('li').children('ul');
					treeTargetEl.append(answer);
					renewEvents();
				}
			);
		}
		function createDir(path,el){
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'createDir',path:path,theme:theme},
				function (data){
					console.log(data);
					//openToEdit(path,theme);
					var treeTargetEl=$(el).parent('li').children('ul');
					treeTargetEl.append(data);
					renewEvents();
				}
			);
		}
		function clearMessage(delay){
			var suc=$('span.success');
			var war=$('span.warning');
			if(!delay){
				suc.detach();
				war.detach();
			}else{
				suc.delay(1200).fadeOut(500,function(){
					suc.detach();
				});
				war.delay(1200).fadeOut(500,function(){
					war.detach();
				});
			}
		}
		function cloneTpl(path){
			var cloneTheme=$('select[name=clone] option:checked').val();
			if(!cloneTheme) {
				alert('clone theme is empty'); return;
			}
			$.post(
				document.location.basepath+'?module=admin/themes',
				{act:'cloneTpl',path:path,theme:theme,clone:cloneTheme},
				function (answer){
					//reload sidebar
					$('#themefiles').html('...');
					$('#themefiles').html(answer);
					eventList();
				}
			);
		}
		function renewEvents(){
			$('#themefiles ul li a').unbind("click");
			$('#themefiles ul li a').click(eventOpenToEdit);
			$('#themefiles .del-tpl').unbind("click");
			$('#themefiles .del-tpl').click(eventDetachFromTree);
		}
		function switchTheme(t){
			var theme;
			if(t=='clone')
				theme=$('select[name=clone] option:checked').val();
			else
				theme=$('input[name="theme"]').val();
			return theme;
		}
		var handlerExt=function(){
			var chk=$(this).prop('checked');
			var val=0;
			if(chk) val=1; 
			setCookie('edit_ext',val,'365');
			document.location.href=document.location.href;
		};
	})();
</script>
<?include $template->inc('edit/dialog.php');?>
<div class="edittheme">
	<div class="sidebar">
		<?if($data->access->themesSetHandler){?>
			<label id="extedit">расширенный <input type="checkbox"<?=$data->ext?' checked=""':''?>/></label>
		<?}?>
		<?include $template->inc('edit/sidebar.php');?>
	</div>
	<div class="content">
		<div>
			<input type="button" value="save" name="save"/>
			<h4 id="edittheme-head"></h4>
			<div id="style-editor"></div><br/>
			<div id="editor"></div>
			<input type="hidden" value="<?=$data->theme?>" name="theme"/>
			<input type="hidden" value="" name="path"/>
			<input type="button" value="save" name="save"/>
		</div>
	</div>
	<div class="right-sidebar"><?include $template->inc('edit/right-sidebar.php');?></div>
</div>