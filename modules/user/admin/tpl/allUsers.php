<?
	$roles=array(
		4=>'editor',
		5=>'Searcher',
		2=>'Author',
		0=>'User',
	);
?>
<form action='' method='post' class="form">
<table border=1>
	<tr>
        <td>Name</td>
        <td>Email</td>
       <?foreach($roles as $k=>$v){
		   print "<td>$v</td>";
	   }?>
    </tr>
	<?foreach($data->users as $user){?>
		<tr>
            <td><?= $user->name?></td>
            <td><?= $user->mail?></td>
            <?foreach($roles as $k=>$v){?>
				<td>
					<input type="radio" name='users[<?=$user->id?>]' <?=$user->rbac==$k?"checked":''?> value="<?=$k?>">
				</td>
			<?}?>
        </tr>
	<?}?>
</table>
<input class="button" type="submit" value="save">
</form>
