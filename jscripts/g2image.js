/*
    Gallery 2 Image Chooser
    Version 3.0.3 - updated 13 MAY 2008
    Documentation: http://g2image.steffensenfamily.com/

    Author: Kirk Steffensen with inspiration, code snipets,
        and assistance as listed in CREDITS.HTML

    Released under the GPL version 2.
    A copy of the license is in the root folder of this plugin.

    See README.HTML for installation info.
    See CHANGELOG.HTML for a history of changes.
*/

tinyMCEPopup.onInit.add(function(ed) {
	var formObj = document.forms[0];
	formObj.file_name.value = tinyMCEPopup.getWindowArg('file_name');
});

function insertwpg2() {

	var formObj   = document.forms[0];
	var file_name = formObj.file_name.value;
	var empty_image_path = tinyMCEPopup.getWindowArg('plugin_url') + "/images/wpg2_placeholder.jpg";

	var html = ''
		+ '<img src="' + empty_image_path + '"'
		+ ' alt="'+file_name+'" title="'+file_name+'" class="mceItem" id="mce_plugin_g2image_wpg2" />';

	tinyMCEPopup.execCommand("mceInsertContent", true, html);
	tinyMCEPopup.close();
}

function insertwpg2id() {

	var formObj   = document.forms[0];
	var file_name = formObj.file_name.value;
	var empty_image_path = tinyMCEPopup.getWindowArg('plugin_url') + "/images/wpg2_placeholder.jpg";

	var html = ''
		+ '<img src="' + empty_image_path + '"'
		+ ' alt="'+file_name+'" title="'+file_name+'" class="mceItem" id="mce_plugin_g2image_wpg2id" />';

	tinyMCEPopup.execCommand("mceInsertContent", true, html);
	tinyMCEPopup.close();
}

function cancelAction() {

	tinyMCEPopup.close();
}
