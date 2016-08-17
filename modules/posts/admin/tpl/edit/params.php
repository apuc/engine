<div <?if(!@$data->access->editCode){?>style="display:none"<?}?>>
	<label for="post-params"><span>Params for template</span></label>
	<small>(JSON format or short equivalent, <a style="cursor:pointer" id="post-params-example">example</a>)
		<pre style="display:none;">
a: 1
b: {
        b1: value
        b2: value
    }
	</pre>
	</small>
	<script type="text/javascript">
		$('#post-params-example').click(function(){
			var parent=$(this).parent('small');
			parent.children('pre').toggle(200);
		});
	</script>
	<textarea name="post-params" placeholder="example: {&quot;a&quot;: 1}"><?=$post->data?></textarea><br />
</div>
