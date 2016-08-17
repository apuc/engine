$(function(){
	function addCategoryInput(event){
		var input = $(".category-input").first().clone();
		input.keyup(ajaxAutocomplete).focus(focusAutocomplete).blur(blurAutocomplete);
		input.appendTo("#category-inputs");
		addCategoryAddInput();
	}

	function addCategoryAddInput(){
		var el=$("#category-inputs");
		if(el.attr('data-add')=='false')
			return;
		var input = $('<input type="button" value="+" />').addClass("add-category-input");
		input.click(addCategoryInput);
		input.css({
			'width':'40px',
			'height':'40px',
			'margin-left':'2px'
		});
		el.append(input);
	}

	addCategoryAddInput();

	function createAutocompleteBlock(){
		var autocomplete = $("<ul></ul>").addClass("cat-autocomplete");
		autocomplete.css({
			'background': 'white',
			'border': '1px solid black',
			'min-width': '400px',
			'list-style': 'none',
			'padding': 0,
			'margin': 0,
			'position': 'absolute'
		});
		autocomplete.hide();
		autocomplete.appendTo(document.body);
		return autocomplete;
	}
	var autocomplete = createAutocompleteBlock();

	function positionAutocompleteBlock(elemPos){
		var elemOffset = elemPos.offset();
		var elemHeight = elemPos.height();
		autocomplete.css({
			'top': elemOffset.top + elemHeight + 2,
			'left': elemOffset.left
		});
	}

	function setAutocompleteValue(){
		if (currentInput) currentInput.val($(this).children("small").html());
	}

	function fillAutocompleteBlock(elems){
		autocomplete.empty();

		var elem, url, title, listElem;
		for (var i = 0, l = elems.length; i < l; i++){
			elem = elems[i].split(",");
			url = elem[0];
			title = elem[1];
			listElem = $("<li></li>").html(title + " <small>" + url + "</small>").css({
				'padding': '5px',
				'cursor': 'pointer'
			}).mousedown(setAutocompleteValue).appendTo(autocomplete);
		}
	}

	function blurAutocomplete(){
		currentInput = null;
		autocomplete.hide();
	}

	function focusAutocomplete(){
		currentInput = $(this);
		if ($(this).val().length >= 2) ajaxAutocomplete.apply(this);
	}

	function ajaxAutocomplete(){
		var self=$(this);
		var query=self.val();
		var color=self.css('border-color');
		var prfxtbl=$('input[name=prfxtbl]').val();
		if(prfxtbl=='undefined') prfxtbl='';
		if (query.length >= 2){
			$.get(window.location.basepath, {
				module: 'category',
				act: 'ajaxCategories',
				query: query,
				prfxtbl: prfxtbl
			}, function(data){
				if (data == ' '){
					self.css('border-color','red');
					autocomplete.hide();
					return false;
				}else{
					self.css('border-color',color);
				}

				fillAutocompleteBlock(data.split("|"));
				positionAutocompleteBlock(self);
				autocomplete.show();
			});
		}
	}

	var currentInput = null;

	$(".category-input").keyup(ajaxAutocomplete).focus(focusAutocomplete).blur(blurAutocomplete);
});