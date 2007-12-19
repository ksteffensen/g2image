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

// Get the best fit image 
function g2icBestFit(item, maxImageWidth, maxImageHeight) {
	// Get the height and width of the largest available imageVersion.  This is
	// because the thumbnail can be square.  If there is only a thumbnail, then it is
	// the largest available image.  But if there are multiple imageVersions, the 
	// largest image is most likely not the thumbnail.
	var hash_x = item.imageHash.x;
	var hash_y = item.imageHash.y;
	var largest_id = null;
	for (var id in hash_x) {
		largest_id = hash_x[id];
	}
	
	// largest_id will remain null if there were no enumerable members in hash_x
	if (largest_id) {
		var imageWidth = item.imageVersions[largest_id].width;
		var imageHeight = item.imageVersions[largest_id].height;
	
		// true if maxDimensions are taller/narrower than image, in which case width is the constraint:
		var widthbound = 0;
		if ( !maxImageHeight || imageHeight * maxImageWidth < imageWidth * maxImageHeight ) {
			widthbound = 1;
		}
		
		var bestFit = new Object();
		if ( maxImageWidth &&  widthbound ) {
			for (var width in hash_x) {
				if (width >= maxImageWidth) {
					bestFit.id = hash_x[width];
					bestFit.image = item.imageVersions[bestFit.id].url.image;
					bestFit.width = maxImageWidth;
					bestFit.height = Math.round(item.imageVersions[bestFit.id].height*maxImageWidth/width);
					return bestFit;	//return the first one equal to or wider than $maxImageWidth
				}
			}
		}
		else if ( maxImageHeight ) {
			for (var height in hash_y) {
				if (height >= maxImageHeight) {
					bestFit.id = hash_y[height];
					bestFit.image = item.imageVersions[bestFit.id].url.image;
					bestFit.height = maxImageHeight;
					bestFit.width = Math.round(item.imageVersions[bestFit.id].width*maxImageHeight/height);
					return bestFit;	//return the first one equal to or wider than $maxImageWidth
				}
			}
		}
		else {
			// If no other image ID has already been returned, return the largest image.
			bestFit.id = largest_id;
			bestFit.image = item.imageVersions[largest_id].url.image;
			bestFit.height = item.imageVersions[largest_id].height;
			bestFit.width = item.imageVersions[largest_id].width;;
			return bestFit;	//return the first one equal to or wider than $maxImageWidth
		}
	}
	
	// If there were no images available, return null.
	return null;	
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

function insertHtml(html, g2ic_form, g2ic_field, keep_open) {
	if(window.opener){
		if(window.tinyMCE)
			window.opener.tinyMCE.execCommand("mceInsertContent",true,html);
		else if (window.opener.FCK)
			window.opener.FCK.InsertHtml(html);
		else
			insertAtCursor(window.opener.document.forms[g2ic_form].elements[g2ic_field],html);
		if (!keep_open)
			window.close();
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
}

// this object will be filled with an array
// of functions to be called in insertItems
var insertFunctions = new Object();