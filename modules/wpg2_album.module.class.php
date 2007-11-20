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

	function module_{$name}(stack, imageObj){
		var str = "";

		if (imageObj['album_alignment'] != 'none'){
			str += '<div class="' + imageObj['album_alignment'] + '">';
		}
		if(window.tinyMCE) {
			str += '<img src="' + imageObj['album_thumbnail']
			+ '" alt="' + imageObj['current_album'];
			str += '" title="' + imageObj['current_album'];
			str += '" width="' + imageObj['album_thumbw'] + '" height="' + imageObj['album_thumbh']
			+ '" id="mce_plugin_g2image_wpg2" />';
		}
		else {
			str += '<wpg2>' + imageObj['current_album'] + '</wpg2>';
		}
		if (imageObj['album_alignment'] != 'none'){
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
	 * Set the javascript variables that this module requires.  Must be unique names among modules.
	 *
	 */
	function javaScriptVariables(){
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