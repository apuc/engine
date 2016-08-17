<?if(isset($data->access->delNews)){?>
	<script>
	function deletePosts(){
		var ids=[];
		var prfxtbl=$('input[name=prfxtbl]').val();
		var tblname='post';
		if(prfxtbl!='')
			tblname=prfxtbl+'_'+tblname;
		if(!confirm("Delete from table ["+tblname+"]?")) return;
		$("[name='post[]']").each(function() {
			if(this.checked){
				ids[ids.length]=this.value;
			}
		});
		$.post(window.location.basepath+"?module=posts/admin&act=delPosts", {pids: ids,prfxtbl: prfxtbl}).done(
			function(data){
				$( "#result" ).html( data );
			}
		);
	}
	function setChecked(){
		var chk=$("[name='post[]']" ).is(':checked');
		if(chk){
			$("[name='post[]']" ).prop( "checked", false );
		}else{
			$("[name='post[]']" ).prop( "checked", true );
		}
	}
	</script>
	<div style="text-align:center;margin:5px 0;">
		<div id="result"></div>
		<input type="button" value="delete checked" onclick="deletePosts()"/>
		<input type="checkbox" name="set" onclick="setChecked()" />
		<input type="hidden" name="prfxtbl" value="<?=!empty($data->prfxtbl)?$data->prfxtbl:''?>" />
	</div>
<?}?>