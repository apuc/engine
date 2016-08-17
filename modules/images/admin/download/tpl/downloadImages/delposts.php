<?if(isset($data->access->delNews)){?>
	<script>
	function deletePosts(){
		var ids=[];
		jQuery("input.download_checkbox_post").each(function(indx,el) {
			if(el.checked){
				ids[ids.length]=jQuery(el).attr('data-id');
			}
		});
		if(ids){
			$.post(window.location.basepath+"?module=posts/admin&act=delPosts", {pids: ids}).done(
				function(data){
					$( "#result" ).html( data );
				}
			);
		}
	}
	function setChecked(set){
		var el=jQuery("input.download_checkbox_post");
		var chk=jQuery(set).is(':checked');
		if(!chk){
			el.prop("checked",false);
		}else{
			el.prop("checked",true);
		}
	}
	</script>
	<div style="text-align:center;margin:5px 0;">
		<div id="result"></div>
		<input type="button" value="delete checked" onclick="deletePosts()"/>
		<input type="checkbox" name="set" onclick="setChecked(this)" />
	</div>
<?}?>