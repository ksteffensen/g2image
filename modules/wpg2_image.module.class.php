<?php
/**
 * thumbnail
 *
 */

class wpg2_image{

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

		if (imageObj['alignment'] != 'none'){
			str += '<div class="' + imageObj['alignment'] + '">';
		}
		if(window.tinyMCE) {
			str += '<img src="' + imageObj['thumbnail_img']
			+ '" alt="' + imageObj['image_id'];
			if (imageObj['wpg2_tag_size'])
				str += '|' + imageObj['wpg2_tag_size'];
			str += '" title="' + imageObj['image_id'];
			if (imageObj['wpg2_tag_size'])
				str += '|' + imageObj['wpg2_tag_size'];
			str += '" ' + imageObj['thumbw'] + imageObj['thumbh']
			+ 'id="mce_plugin_g2image_wpg2" />';
		}
		else {
			str += '<wpg2>' + imageObj['image_id'];
			if (imageObj['wpg2_tag_size'])
				str += '|' + imageObj['wpg2_tag_size'];
			str += '</wpg2>';
		}
		if (imageObj['alignment'] != 'none'){
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
		global $g2ic_options;
echo "here";
		// Check that the ImageBlock module supports the exactsize attribure (requires module API 1.0.9 or later)
		GalleryCoreApi::requireOnce('modules/core/classes/GalleryRepositoryUtilities.class');
		list($error, $plugin) = GalleryCoreApi::loadPlugin('module', 'ImageBlock');
		$version = $plugin->getVersion();
		$version_comparison = GalleryRepositoryUtilities::compareRevisions($version,'1.0.9');
		if ($version_comparison != 'older') {
			$html = '                <label for="wpg2_tag_size">' . T_('WPG2 tag "size" attribute (Leave blank for the default size of: ') . $g2ic_options['wpg2_tag_size']. 'px)<br /></label>' . "\n"
			. '                <input type="text" name="wpg2_tag_size" size="84" maxlength="150" value="" />' . "\n"
			. '                <br />' . "\n"
			. '                <br />' . "\n";
		}
		else {
			$html = '                <input type="hidden" name="wpg2_tag_size" value="" />' . "\n";
		}
echo "here2";
		return $html;
	}

	/**
	 * Set the javascript variables that this module requires.  Must be unique names among modules.
	 *
	 */
	function javaScriptVariables(){
		return "					imageObj.wpg2_tag_size = obj.wpg2_tag_size.value;\n";
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('WPG2 tag of image');
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