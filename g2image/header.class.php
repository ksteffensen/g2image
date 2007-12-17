<?php

class g2ic_header {

	var $html = '';

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $options
	 * @return g2ic_header
	 */
	function g2ic_header($options, $g2obj){
		__construct($options, $g2obj);
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $options
	 */
	function __construct($options, $g2obj){
		$this->html =
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>' . T_('Gallery2 Image Chooser') . '</title>
	<link rel="stylesheet" href="css/g2image.css" type="text/css" />
	<link rel="stylesheet" href="css/dtree.css" type="text/css" />
	<link rel="stylesheet" href="css/slimbox.css" type="text/css" media="screen" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		if($options['tinymce'] && $options['wpg2_valid']) {
			$this->html .= "	<script language='javascript' type='text/javascript' src='../../../../wp-includes/js/tinymce/tiny_mce_popup.js'></script>\n";
		}
		elseif($options['tinymce'] && !$options['wpg2_valid']) {
			$this->html .= "	<script language='javascript' type='text/javascript' src='../../tiny_mce_popup.js'></script>\n";
		}
		$this->html .=
'	<script language="javascript" type="text/javascript" src="jscripts/functions.js"></script>
	<script language="javascript" type="text/javascript" src="jscripts/dtree.js"></script>
	<script language="javascript" type="text/javascript" src="jscripts/mootools.js"></script>
	<script language="javascript" type="text/javascript" src="jscripts/slimbox.js"></script>
	<script type="text/javascript">
';
		if ($options['album_modules']) {
			foreach($options['album_modules'] as $moduleName){
				 $this->html .= all_modules::call( $moduleName, "insert");
			}
		}
		if ($options['image_modules']) {
			foreach($options['image_modules'] as $moduleName){
				 $this->html .= all_modules::call( $moduleName, "insert");
			}
		}
		$this->html .=
'	</script>
	<script language="javascript" type="text/javascript">
	<!--
	function g2icInsert(insertType){
';
		$this->html .= PhpArrayToJsObject($options, 'options');
		$this->html .= PhpArrayToJsObject($g2obj->album, 'album');
		$this->html .= PhpArrayToJsObject($g2obj->dataItems, 'dataItems');
		$this->html .=
'		if(insertType == "album") {
			insertAlbum(album, options);
		}
		else {
			insertItems(dataItems, album, options);
		}
	}
	function insertAlbum(album, options){
		var form = document.forms[0];
		var htmlCode = \'\';

		if(typeof(insertFunctions[form.albuminsert.value])=="function"){
			id = 0;
			var imageObj = {}; // new Object()

			// Convert thumbnail info to imageObj literals for convenience
			// (Since they\'re long otherwise...)
			if(album.thumbnail_id){
				imageObj.album_thumbnail = album.imageVersions[album.thumbnail_id]["url"]["image"];
				imageObj.album_thumbw = album.imageVersions[album.thumbnail_id]["width"];
				imageObj.album_thumbh = album.imageVersions[album.thumbnail_id]["height"];
			}

			htmlCode += insertFunctions[form.albuminsert.value]( [form.albuminsert.value], imageObj, form, album, options );
		}else{
			alert(form.albuminsert.value);
			htmlCode += \'Error\';
		}

		insertHtml(htmlCode, options.form, options.field);
	}

	function insertItems(items, album, options){
		var obj = document.forms[0];
		var htmlCode = \'\';
		var id = 0;
		var loop = 0;
		var image_id = new Array();
		var imageObj = new Object();
		var fullsize = new Object();

		//hack required for when there is only one image

		if (obj.images.length) {
			loop = obj.images.length;
			for (var i=0;i<loop;i++) {
				image_id[i] = obj.image_id[i].value;
			}
		}
		else {
			loop = 1;
			image_id[0] = obj.image_id.value;
		}

		//let\'s generate HTML code according to selected insert option

		for (var i=0;i<loop;i++) {
			if ((loop == 1) || obj.images[i].checked) {

				if(typeof(insertFunctions[obj.imginsert.value])=="function"){
					imageObj.pos = i;
					id = image_id[i];
					fullsize = g2icBestFit(items[id], 640, 640, true);
					if (fullsize) {
						imageObj.fullsize_id = fullsize.id;
						imageObj.fullsize_img = fullsize.image;
						imageObj.fullsize_width = fullsize.width;
						imageObj.fullsize_height = fullsize.height;
					}
					if (items[id].thumbnail_id) {
						imageObj.thumbnail_id = items[id].thumbnail_id;
						imageObj.thumbnail_img = items[id].imageVersions[items[id].thumbnail_id]["url"]["image"];
						imageObj.thumbw = items[id].imageVersions[items[id].thumbnail_id]["width"];
						imageObj.thumbh = items[id].imageVersions[items[id].thumbnail_id]["height"];
					}

					htmlCode += insertFunctions[obj.imginsert.value]( [obj.imginsert.value], imageObj, obj, items[id], album, options );
				}else{
					alert(obj.imginsert.value);
					htmlCode += \'Error\';
				}
			}
		}
		insertHtml(htmlCode, options.form, options.field);
	}
    // -->
    </script>
</head>
';
		return;
	}

	/*public*/ function __destruct(){
	}

}
?>