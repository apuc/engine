<style type="text/css">
	.edittheme .right-sidebar>ul{padding:0 !important;}
	.edittheme .right-sidebar ul{padding: 0 0 0 20px;cursor: pointer;}
	.edittheme .right-sidebar li{width: 130px;list-style: outside none none;clear: both;}
	.edittheme .right-sidebar li i{display:inline-block;float:right;width: 102px;}
	.edittheme .right-sidebar li span{display: inline-block;padding: 0 4px;vertical-align: top;font-weight: 600;}
	.edittheme .right-sidebar li span:hover{background-color: #F1F1F1;}
	.edittheme .right-sidebar ul>li>ul{display: none;}
	.edittheme .right-sidebar .manage{width: 100%;}
	.edittheme .right-sidebar .manage button{display:block;margin: auto;cursor: pointer;}
	.edittheme .right-sidebar .manage input[type="button"]{display:block;margin: auto;cursor: pointer;}
	.clone-icon {
		background: url('<?=HREF?>/files/icons/admin/themes/iset.png') no-repeat scroll -222px -261px;
		display: inline-block;
		height: 17px;
		width: 20px;
		padding: 0;
		float: left;
	}
	.clone-icon:hover{opacity: 0.4;}
</style>
<div class="manage">
<?if(!empty($data->themesList)){?>
	<h3 style="float:left;margin-right:5px;">Клонировать</h3>
	<form method="GET" action="<?=HREF?>">
		<select name="clone" onchange="if(this.value) this.form.submit();">
		<?foreach ($data->themesList as $val) {?>
			<option value="<?=$val?>"<?=$val==$data->clone?' selected=""':''?>><?=$val?></option>
		<?}?>
		</select>
		<input type="hidden" name="module" value="admin/themes"/>
		<input type="hidden" name="theme" value="<?=$data->theme?>"/>
		<input type="hidden" name="act" value="edit"/>
	</form>
	<div id="clonefiles">
	<?if(!empty($data->cloneThemeDir)){?>
		<?=listRecClone($data->cloneThemeDir)?>
	<?}?>
	</div>
<?}?>
</div>
<?function listRecClone($list){?>
	<ul>
		<?foreach ($list as $key => $v) {
			if(is_array($v)){
				if(count($v)){?><li><span><?=$key?></span><small class="clone-icon clone-tpl" title="clone"></small><?listRecClone($v)?></li><?}
			}else{?><li><i><?=$key?></i><small class="clone-icon clone-tpl" title="clone"></small></li><?}
		}?>
	</ul>
<?}?>