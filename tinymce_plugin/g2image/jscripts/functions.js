function insertAtCursor(myField, myValue) {
	//IE support
	if (document.selection && !window.opera) {
		myField.focus();
		sel = window.opener.document.selection.createRange();
		sel.text = myValue;
	}
	//MOZILLA/NETSCAPE/OPERA support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
		+ myValue
		+ myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}

function insertDefaults(){
	imgs = document.getElementsByTagName('img');
	for (var i = 0; i < imgs.length; i++) {
		imgs[i].onclick = function(){insertImage(this.parentNode.getElementsByTagName("form")[0])}
	}
}

function showAdvanced(){
	imgs = document.getElementsByTagName('img');
	for (var i = 0; i < imgs.length; i++) {
		imgs[i].onclick = function(){showHideImageBlock(this.parentNode.getElementsByTagName("form")[0])}
	}
}

function showFileNames(){
	divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		if (divs[i].className == 'bordered_imageblock'){
			forms = divs[i].getElementsByTagName('form');
			for (var j = 0; j < forms.length; j++) {
				if (forms[j].className == 'displayed_form_thumbnail')
					forms[j].className = 'hidden_form';
				else if (forms[j].className == 'displayed_form')
					forms[j].className = 'hidden_form';
			}
		}
		else if (divs[i].className == 'transparent_imageblock')
			divs[i].className = 'bordered_imageblock';
		else if (divs[i].className == 'hidden_title')
			divs[i].className = 'displayed_title';
		else if (divs[i].className == 'inactive_placeholder')
			divs[i].className = 'active_placeholder';
	}
}

function showThumbnails(){
	divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		if (divs[i].className == 'bordered_imageblock'){
			divs[i].className = 'transparent_imageblock';
			forms = divs[i].getElementsByTagName('form');
			for (var j = 0; j < forms.length; j++) {
				if (forms[j].className == 'displayed_form')
					forms[j].className = 'hidden_form';
				else if (forms[j].className == 'displayed_form_thumbnail')
					forms[j].className = 'hidden_form';
			}
		}
		else if (divs[i].className == 'displayed_title')
			divs[i].className = 'hidden_title';
		else if (divs[i].className == 'active_placeholder')
			divs[i].className = 'inactive_placeholder';
	}
}

function showHideImageBlock(obj){
	if(obj.parentNode.className == 'transparent_imageblock' && obj.className == 'hidden_form') {
		obj.className = 'displayed_form_thumbnail';
		obj.parentNode.className = 'bordered_imageblock';
		divs = obj.parentNode.getElementsByTagName("div");
		for (var i = 0; i < divs.length; i++) {
			if (divs[i].className == 'hidden_title')
				divs[i].className = 'displayed_title';
			else if (divs[i].className == 'inactive_placeholder')
				divs[i].className = 'active_placeholder';
		}
		obj.imgdesc.focus();
	}
	else if(obj.parentNode.className == 'bordered_imageblock' && obj.className == 'displayed_form_thumbnail'){
		obj.className = 'hidden_form';
		obj.parentNode.className = 'transparent_imageblock';
		divs = obj.parentNode.getElementsByTagName("div");
		for (var i = 0; i < divs.length; i++) {
			if (divs[i].className == 'displayed_title')
				divs[i].className = 'hidden_title';
			else if (divs[i].className == 'active_placeholder')
				divs[i].className = 'inactive_placeholder';
		}
	}
	else {
			if(obj.className == 'hidden_form'){
			obj.className = 'displayed_form';
			obj.imgdesc.focus();
		}
		else
			obj.className = 'hidden_form';
	}
}

function insertImage(obj) {
	imagehtml=makeHtmlForInsertion(obj);
	g2ic_form=obj.g2ic_form.value;
	g2ic_field=obj.g2ic_field.value;
	if(window.tinyMCE)
		window.opener.tinyMCE.execCommand("mceInsertContent",true,imagehtml);
	else
		insertAtCursor(window.opener.document.forms[g2ic_form].elements[g2ic_field],imagehtml);
	window.close();
}

function makeHtmlForInsertion(obj){
	var radio = obj.imginsert;//array of radiobuttons
	var selectedRadio = '';
	var htmlCode = '';
	var imgtitle = '';
	var imgalt = '';
	if(window.tinyMCE)
		var empty_image_path = tinyMCE.baseURL + '/plugins/g2image/images/wpg2_placeholder.jpg';
	else
		var empty_image_path = '';

	//which code is selected
	if (radio.value == 'radio_fake') {
		selectedRadio = obj.radio_selected.value;
	}
	else{
		for(i=0;i<radio.length;i++){
			if(radio[i].checked){
				selectedRadio = radio[i].value ;}
		}
	}

	imgtitle = ' title="' + obj.imgdesc.value + '"';
	imgalt = ' alt="' + obj.imgdesc.value + '"';

	//let's generate HTML code according to selected radiobutton

	switch(selectedRadio){
		case 'thumbnail_image':
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			htmlCode += '<a href="' + obj.image_url.value
			+ '"><img src="'+obj.thumbnail_src.value + '" ' + obj.thumbw.value
			+ ' ' + obj.thumbh.value + imgalt + imgtitle;
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'img')){
				htmlCode += ' class="' + obj.alignment.value + '"';
			}
			htmlCode += ' /></a>';
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '</div>';
			}
		break;
		case 'thumbnail_album':
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			htmlCode += '<a href="' + obj.album_url.value
			+ '"><img src="'+obj.thumbnail_src.value + '" ' + obj.thumbw.value
			+ ' ' + obj.thumbh.value + imgalt + imgtitle;
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'img')){
				htmlCode += ' class="' + obj.alignment.value + '"';
			}
			htmlCode += ' /></a>';
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '</div>';
			}
		break;
		case 'thumbnail_custom_url':
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			htmlCode += '<a href="' + obj.custom_url.value
			+ '"><img src="'+obj.thumbnail_src.value + '" ' + obj.thumbw.value
			+ ' ' + obj.thumbh.value + imgalt + imgtitle;
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'img')){
				htmlCode += ' class="' + obj.alignment.value + '"';
			}
			htmlCode += ' /></a>';
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '</div>';
			}
		break;
		case 'thumbnail_only':
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			htmlCode += '<img src="'+obj.thumbnail_src.value + '" ' + obj.thumbw.value
			+ ' ' + obj.thumbh.value + imgalt + imgtitle;
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'img')){
				htmlCode += ' class="' + obj.alignment.value + '"';
			}
			htmlCode += ' />';
			if ((obj.alignment.value != 'none') && (obj.class_mode.value == 'div')){
				htmlCode += '</div>';
			}
		break;
		case 'wpg2_image':
			if (obj.alignment.value != 'none'){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			if(window.tinyMCE)
				htmlCode += '<img src="' + obj.thumbnail_src.value
				+ '" alt="' + obj.album_name.value + obj.image_name.value
				+ '" title="' + obj.album_name.value + obj.image_name.value
				+ '" ' + obj.thumbw.value + ' ' + obj.thumbh.value
				+ 'id="mce_plugin_g2image_wpg2" />';
			else
				htmlCode += '<wpg2>' + obj.album_name.value + obj.image_name.value + '</wpg2>';
			if (obj.alignment.value != 'none'){
				htmlCode += '</div>';
			}
		break;
		case 'wpg2id_image':
			if (obj.alignment.value != 'none'){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			if(window.tinyMCE)
				htmlCode += '<img src="' + obj.thumbnail_src.value
				+ '" alt="' + obj.image_id.value
				+ '" title="' + obj.image_id.value
				+ '" ' + obj.thumbw.value + ' ' + obj.thumbh.value
				+ 'id="mce_plugin_g2image_wpg2id" />';
			else
				htmlCode += '<wpg2id>' + obj.image_id.value + '</wpg2id>';
			if (obj.alignment.value != 'none'){
				htmlCode += '</div>';
			}
		break;
		case 'wpg2_album':
			if (obj.alignment.value != 'none'){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			if(window.tinyMCE)
				htmlCode += '<img src="' + obj.thumbnail_src.value
				+ '" alt="' + obj.imgdesc.value
				+ '" title="' + obj.imgdesc.value
				+ '" ' + obj.thumbw.value + ' ' + obj.thumbh.value
				+ 'id="mce_plugin_g2image_wpg2" />';
			else
				htmlCode += '<wpg2>' + obj.imgdesc.value + '</wpg2>';
			if (obj.alignment.value != 'none'){
				htmlCode += '</div>';
			}
		break;
		case 'wpg2id_album':
			if (obj.alignment.value != 'none'){
				htmlCode += '<div class="' + obj.alignment.value + '">';
			}
			if(window.tinyMCE)
				htmlCode += '<img src="' + obj.thumbnail_src.value
				+ '" alt="' + obj.imgdesc.value
				+ '" title="' + obj.imgdesc.value
				+ '" ' + obj.thumbw.value + ' ' + obj.thumbh.value
				+ 'id="mce_plugin_g2image_wpg2id" />';
			else
				htmlCode += '<wpg2id>' + obj.imgdesc.value + '</wpg2id>';
			if (obj.alignment.value != 'none'){
				htmlCode += '</div>';
			}
		break;
		case 'drupal_g2_filter':
			htmlCode += '[' + obj.drupal_filter_prefix.value + ':' + obj.image_id.value;
			if (obj.alignment.value != 'none'){
				htmlCode += ' class=' + obj.alignment.value;
			}
			htmlCode += ']';
		break;
		case 'link_image':
			htmlCode = '<a href="' + obj.image_url.value + '">' + obj.imgdesc.value + '</a>';
		break;
		case 'link_album':
			htmlCode = '<a href="' + obj.album_url.value + '">' + obj.imgdesc.value + '</a>';
		break;
		default:
			htmlCode = 'Error';
		break;
	}
	return htmlCode;
}
