<?php
/**
 * thumbnail
 *
 */

class bbcode_thumbnail_custom_url{

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
		str += '[url=' + imageObj['custom_url_thumbnail_bbcode'] + '][img]' + imageObj['thumbnail_img'] + '[/img][/url]';
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
		global $g2ic_options;
		$html = '                <label for="custom_url_thumbnail_bbcode">' . T_('Custom URL') . '<br /></label>' . "\n"
		. '                <input type="text" name="custom_url_thumbnail_bbcode" size="84" maxlength="150" value="' . $g2ic_options['custom_url'] . '" />' . "\n"
		. '                <br />' . "\n";
		return $html;
	}

	/**
	 * Set the javascript variables that this module requires.  Must be unique names among modules.
	 *
	 */
	function javaScriptVariables(){
		return "					imageObj.custom_url_thumbnail_bbcode = obj.custom_url_thumbnail_bbcode.value;\n";
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Thumbnail with link to custom URL (from text box below)') . ' ' . T_('(BBCode)');
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
		$data["description"] = "BBCode for thumbnail image with a link to the image's page in Gallery2";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
}
?>