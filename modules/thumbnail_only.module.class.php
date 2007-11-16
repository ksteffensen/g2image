<?php
/**
 * Get all of the Gallery2 items
 *
 * @return array $album_info Album Title and URL for the current album
 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
 */

class thumbnail_only{

	/**
	 * See the sample prototype for details
	 */
	function insert($name){
		// caution: \n in javascript strings: \\n
//## JAVASCRIPT #################
		$script = <<<SCRIPTSTUFF
//module [{$name}]
	insertFunctions["{$name}"] = module_{$name};

	function module_{$name}(stack, imageObj){
		var str = "";
		if ((imageObj['alignment'] != 'none') && (imageObj['class_mode'] == 'div')){
			str += '<div class="' + imageObj['alignment'] + '">';
		}
		str += '<img src="' + imageObj['thumbnail_img'] + '" alt="' + imageObj['item_title'] + '" title="' + imageObj['item_summary'] + '"';
		if ((imageObj['alignment'] != 'none') && (imageObj['class_mode'] == 'img')){
			str += ' class="' + imageObj['alignment'] + '"';
		}
		str += ' />';
		if ((imageObj['alignment'] != 'none') && (imageObj['class_mode'] == 'div')){
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
		return T_('Thumbnail only - no link') . ' ' . T_('(HTML)');
	}

	/**
	 * instead of a selectionbox a icon
	 *
	 */
	function icon(){
		return "sample";
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
		return "sample Setup";
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
		$data["description"] = "HTML for thumbnail only - no link";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
}
?>