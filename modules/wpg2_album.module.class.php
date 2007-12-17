<?php
/**
 * thumbnail
 *
 */

class wpg2_album{

	/**
	 * See sample module for details
	 */
	function insert($name){
		// caution: \n in javascript strings: \\n
//## JAVASCRIPT #################
		$script = <<<SCRIPTSTUFF
	//module [{$name}]
	insertFunctions["{$name}"] = module_{$name};

	function module_{$name}(stack, form, album, options){
		var str = "";

		if (form.album_alignment.value != 'none'){
			str += '<div class="' + form.album_alignment.value + '">';
		}
		if(window.tinyMCE) {
			str += '<img src="' + album.thumbnail_image
			+ '" alt="' + album.id 
			+ '" title="' + album.id
			+'" width="' + album.thumbnail_width
			+ '" height="' + album.thumbnail_height
			+ '" id="mce_plugin_g2image_wpg2" />';
		}
		else {
			str += '<wpg2>' + album.id + '</wpg2>';
		}
		if (form.album_alignment.value != 'none'){
			str += '</div>';
		}

		return str;
	}
	//end module [{$name}]

SCRIPTSTUFF;
//## END JAVASCRIPT #############

		return $script;

	}

	/**
	 * here we can add extra vars or settings for the rendering
	 *
	 */
	function dialog(){
		return '';
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('WPG2 tag of album');
	}

	/**
	 * instead of a selectionbox a icon
	 *
	 */
	function icon(){
		return '';
	}

	/**
	 * check for needed javascripts
	 *
	 */
	function preeq(){
	}

	/**
	 *
	 */
	function help(){
	}

	/**
	 * maybe a setup function
	 *
	 */
	function setup(){
		return '';
	}

	/**
	 * for later,avoid compatibility problem
	 * and any information can be added in the array, like help etc.
	 * @return mixed string or array
	 *
	 */
	function extra($key=false){
		$data = array();
		$data["version"] = 1.0;
		$data["description"] = "HTML for thumbnail image with a link to the image's page in Gallery2";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
}
?>