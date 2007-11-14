<?php
/**
 * thumbnail
 *
 */

class thumbnail_image{

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
		if ((imageObj['alignment'] != 'none') && (imageObj['class_mode'] == 'div')){
			str += '<div class="' + imageObj['alignment'] + '">';
		}
		str += '<a href="' + imageObj['image_url'] + '"><img src="' + imageObj['thumbnail_img'] + '" alt="' + imageObj['item_title'] + '" title="' + imageObj['item_summary'] + '"';
		if ((imageObj['alignment'] != 'none') && (imageObj['class_mode'] == 'img')){
			str += ' class="' + imageObj['alignment'] + '"';
		}
		str += ' /></a>';
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
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Thumbnail with link to image') . ' ' . T_('(HTML)');
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