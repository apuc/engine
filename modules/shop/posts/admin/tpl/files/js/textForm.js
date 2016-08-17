tinyMCE.init({
	mode : "specific_textareas",
	theme : "modern",
	menubar: false,
	statusbar: true,
	relative_urls : false,
	toolbar: 'undo redo | bold italic | bullist numlist | image media | code | preview | pagebreak',
	plugins : "advlist,media,image,code,preview,pagebreak,wordcount",
	image_advtab: true,
	editor_selector : "post-text", 
	skin : "lightgray",
	plugin_preview_width : "800",
	plugin_preview_height : "600",
	template_replace_values : {
		username : "Some User"
	}
});
