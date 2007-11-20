/*
    Gallery 2 Image Chooser
    Version 3.1 alpha - updated 07 OCT 2007
    Documentation: http://g2image.steffensenfamily.com/

    Author: Kirk Steffensen with inspiration, code snipets,
        and assistance as listed in CREDITS.HTML

    Released under the GPL version 2.
    A copy of the license is in the root folder of this plugin.

    See README.HTML for installation info.
    See CHANGELOG.HTML for a history of changes.
*/

function activateInsertButton() {
	var obj = document.forms[0];
	var checked = 0;

	if (obj.images.length) {
		loop = obj.images.length;
		for (var i=0;i<loop;i++) {
			if (obj.images[i].checked) {
				checked++;
			}
		}
	}
	else {
		if (obj.images.checked) {
			checked = 1;
		}
	}
	if (checked) {
		document.forms[0].insert_button.disabled = false;
	}
	else {
		document.forms[0].insert_button.disabled = true;
	}
}

function checkAllImages() {
	var obj = document.forms[0];

	if (obj.images.length) {
		loop = obj.images.length;
		for (var i=0;i<loop;i++) {
			obj.images[i].checked = true;
		}
	}
	else {
		obj.images.checked = true;
	}
	document.forms[0].insert_button.disabled = false;
}

function uncheckAllImages() {
	var obj = document.forms[0];

	if (obj.images.length) {
		loop = obj.images.length;
		for (var i=0;i<loop;i++) {
			obj.images[i].checked = false;
		}
	}
	else {
		obj.images.checked = false;
	}
	document.forms[0].insert_button.disabled = true;
}

function toggleAlbumTextboxes() {
	var obj = document.forms[0];

	var pp = document.getElementById("a_"+obj.albuminsert.value); // is there a new module selected?
	var allFields = document.getElementById("album_additional_dialog").getElementsByTagName("DIV");
	for(var i=0; i<allFields.length; i++){
		if(allFields[i].getAttribute("module") != ""){
			allFields[i].className = 'hidden_textbox';
		}
	}
	if(pp && pp.getAttribute("module") ){
		pp.className = 'displayed_textbox';
	}
}

function toggleTextboxes() {
	var obj = document.forms[0];

	var pp = document.getElementById("a_"+obj.imginsert.value); // is there a new module selected?
	var allFields = document.getElementById("additional_dialog").getElementsByTagName("DIV");
	for(var i=0; i<allFields.length; i++){
		if(allFields[i].getAttribute("module") != ""){
			allFields[i].className = 'hidden_textbox';
		}
	}
	if(pp && pp.getAttribute("module") ){
		pp.className = 'displayed_textbox';
	}
}

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
	}
	else {
		myField.value += myValue;
	}
}

function showFileNames(){
	divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		if (divs[i].className == 'hidden_title')
			divs[i].className = 'displayed_title';
		else if (divs[i].className == 'thumbnail_imageblock')
			divs[i].className = 'title_imageblock';
		else if (divs[i].className == 'inactive_placeholder')
			divs[i].className = 'active_placeholder';
	}
}

function showThumbnails(){
	divs = document.getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		if (divs[i].className == 'displayed_title')
			divs[i].className = 'hidden_title';
		else if (divs[i].className == 'title_imageblock')
			divs[i].className = 'thumbnail_imageblock';
		else if (divs[i].className == 'active_placeholder')
			divs[i].className = 'inactive_placeholder';
	}
}

function insertHtml(html,form) {
	g2ic_form=form.g2ic_form.value;
	g2ic_field=form.g2ic_field.value;

	//**aob [A7] added if(window.opener)
	if(window.opener){
		if(window.tinyMCE)
			window.opener.tinyMCE.execCommand("mceInsertContent",true,html);
		else if (window.opener.FCK)
			window.opener.FCK.InsertHtml(html);
		else
			insertAtCursor(window.opener.document.forms[g2ic_form].elements[g2ic_field],html);
		window.close();

	//**aob [A7] added if(window.opener)
	}else{
		var textA = document.getElementById("outputArea");
		if(!textA){
			var p = document.createElement("TEXTAREA");
			p.style.width = "50%";
			p.style.height = "100px";
			p.id = "outputArea";
			textA = document.body.appendChild(p);
			p = document.createElement("DIV");
			p.id = "outputDiv";
			document.body.appendChild(p);
		}
		textA.value = html;
		document.getElementById("outputDiv").innerHTML = html;
	}
	//**aob
}

//**aob [A5]
//**
var insertFunctions = new Object();
// this object will be filled with an array
// of functions to be called in insertItems
//**
//**aob



function insertWpg2Tag(){

	var obj = document.forms[0];
	var htmlCode = '';

	if (obj.alignment.value != 'none'){
		htmlCode += '<div class="' + obj.alignment.value + '">';
	}
	if(window.tinyMCE) {
		htmlCode += '<img src="' + obj.wpg2_thumbnail.value
		+ '" alt="' + obj.wpg2_id.value
		+ '" title="' + obj.wpg2_id.value
		+ '" width="' + obj.wpg2_thumbw.value + '" height="' + obj.wpg2_thumbh.value
		+ '" id="mce_plugin_g2image_wpg2" />';
	}
	else {
		htmlCode += '<wpg2>' + obj.wpg2_id.value + '</wpg2>';
	}
	if (obj.alignment.value != 'none'){
		htmlCode += '</div>';
	}
	insertHtml(htmlCode,obj);
}

function insertDrupalFilter(){

	var obj = document.forms[0];
	var htmlCode = '';

	htmlCode += '[' + obj.drupal_filter_prefix.value + ':' + obj.drupal_image_id.value;
	if (obj.alignment.value != 'none'){
		htmlCode += ' class=' + obj.alignment.value;
	}
	htmlCode += ']';

	insertHtml(htmlCode,obj);
}
