<?if($data->accessSetUser&&!empty($data->userList)){?>
<div>
	<label for="foruser"><span>For User</span><br/>
		<select name="foruser">
			<option value="none"<?=empty($post->user)?' selected=""':''?>></option>
		<?foreach ($data->userList as $val) {?>
			<option value="<?=$val->id?>"<?=(@$post->user==$val->id)?' selected=""':''?>><?=empty($val->name)?$val->mail:$val->name." ({$val->mail})"?></option>
		<?}?>
		</select>
	</label>
</div>
<?}?>