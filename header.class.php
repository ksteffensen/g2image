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
			$this->html .= "    <script language='javascript' type='text/javascript' src='../../../../wp-includes/js/tinymce/tiny_mce_popup.js'></script>\n";
		}
		elseif($options['tinymce'] && !$options['wpg2_valid']) {
			$this->html .= "    <script language='javascript' type='text/javascript' src='../../tiny_mce_popup.js'></script>\n";
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
		var obj = document.forms[0];
		var htmlCode = \'\';

		if(typeof(insertFunctions[obj.albuminsert.value])=="function"){
			id = 0;
			var imageObj = {}; // new Object()

			// Fixed core variables
			imageObj.current_album = album.id;
			imageObj.album_name = album.title;
			imageObj.album_url = album.image_url;
			imageObj.album_summary = album.summary;
			if(album.thumbnail_id){
				imageObj.album_thumbnail = album.imageVersions[album.thumbnail_id]["url"]["image"];
				imageObj.album_thumbw = album.imageVersions[album.thumbnail_id]["width"];
				imageObj.album_thumbh = album.imageVersions[album.thumbnail_id]["height"];
			}
			imageObj.album_alignment = obj.album_alignment.value;
			imageObj.class_mode = options.class_mode;

			// Module inserted variables
			// Album modules
';
		if ($options['album_modules']) {
			foreach($options['album_modules'] as $moduleName){
				$this->html .= all_modules::call( $moduleName, "javaScriptVariables");
			}
		}
		$this->html .= 
'
			htmlCode += insertFunctions[obj.albuminsert.value]( [obj.albuminsert.value], imageObj );
		}else{
			alert(obj.albuminsert.value);
			htmlCode += \'Error\';
		}

		insertHtml(htmlCode, options.form, options.field);
	}

	function insertItems(items, album, options){
		var obj = document.forms[0];
		var htmlCode = \'\';
		var imgtitle = \'\';
		var imgalt = \'\';
		var loop = \'\';
		var item_summary = new Array();
		var item_title = new Array();
		var item_description = new Array();
		var image_url = new Array();
		var thumbnail_img = new Array();
		var fullsize_img = new Array();
		var thumbw = new Array();
		var thumbh = new Array();
		var image_id = new Array();

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

				thumbw[i] = \'width="\' + thumbw[i] + \'" \';
				thumbh[i] = \'height="\' + thumbh[i] + \'" \';

				if(typeof(insertFunctions[obj.imginsert.value])=="function"){
					id = 0;
					var imageObj = {}; // new Object()

					// Fixed core variables
					imageObj.pos = i;
					imageObj.image_id = image_id[i];
					id = image_id[i];
					imageObj.image_url = items[id].image_url;
					imageObj.album_url = album.image_url;
					imageObj.fullsize_img = fullsize_img[id];
					imageObj.thumbnail_img = items[id].imageVersions[items[id].thumbnail_id]["url"]["image"];
					imageObj.thumbw = items[id].imageVersions[items[id].thumbnail_id]["width"];
					imageObj.thumbh = items[id].imageVersions[items[id].thumbnail_id]["height"];
					imageObj.w = false; 			// to be done
					imageObj.h =  false; 			// to be done
					imageObj.item_title = items[id].title;
					imageObj.item_summary = items[id].summary;
					imageObj.item_description = items[id].description;
					imageObj.album_id =  album.id;
					imageObj.keywords =  items[id].keywords;
					imageObj.alignment = obj.alignment.value;
					imageObj.class_mode = options.class_mode;

					// Module inserted variables
					// Image modules
';
		if ($options['image_modules']) {
			foreach($options['image_modules'] as $moduleName){
				$this->html .= all_modules::call( $moduleName, "javaScriptVariables");
			}
		}
		$this->html .= 
'
					htmlCode += insertFunctions[obj.imginsert.value]( [obj.imginsert.value], imageObj );
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