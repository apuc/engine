tinyMCE.init({
	mode : "specific_textareas",
	theme : "modern",
	menubar: false,
	statusbar: true,
	relative_urls : false,
	toolbar1: 'undo redo | bold italic forecolor backcolor | outdent indent | bullist numlist | image media link | code | preview | pagebreak',
	toolbar2: 'styleselect formatselect fontselect fontsizeselect',
	//plugins : ["advlist media image code link preview pagebreak wordcount imagetools"],
    plugins: [
        "advlist autolink lists link image charmap print preview anchor pagebreak wordcount",
        "searchreplace visualblocks code fullscreen textcolor colorpicker",
        "insertdatetime media table contextmenu paste"
    ],

	image_advtab: true,
	editor_selector : "post-text", 
	skin : "lightgray",
	plugin_preview_width : "800",
	plugin_preview_height : "600",
	template_replace_values : {
		username : "Some User"
	}
});
