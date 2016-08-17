<div class="themes-import">
	<form name="themeimport" enctype="multipart/form-data" method="post" action="<?=url::admin_themes()?>">
		<div id="themeimport-log"></div>
		<label><span class="themes-import-ico"></span><input type="file" name="importTheme"/></label>
		<input type="submit" value="Import"/>
	</form>
</div>
<script type="text/javascript">
	(function(){
		var form=document.forms.themeimport;
		var log=document.getElementById('themeimport-log');
		form.onsubmit=function(){
			var formData=new FormData(form);
			formData.append('act','importTpl');

			var xhr = new XMLHttpRequest();
			xhr.onload = xhr.onerror = function() {
				if(this.status==200){
					if(xhr.responseText=='done'){
						log.innerHTML='<span class="success">success</span>';
						setTimeout(function(){
							document.location.reload();
						},300);
					}else
						log.innerHTML=log.innerHTML='<span class="success">'+xhr.responseText+'</span>';
				}else
					log.innerHTML="error "+this.status;
			};
			xhr.upload.onprogress = function(event) {
				log.innerHTML=event.loaded+' / '+event.total;
			}
			xhr.open("POST", this.action, true);
			xhr.send(formData);
			return false;
		}
	})();
</script>
