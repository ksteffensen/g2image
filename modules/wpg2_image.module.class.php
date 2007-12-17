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

	function module_{$name}(stack, imageObj, form, item, album, options){
		var str = '';

		if (form.alignment.value != 'none') {
			str += '<div class="' + form.alignment.value + '">';
		}
		if (window.tinyMCE) {
			str += '<img src="' + imageObj.thumbnail_img
			+ '" alt="' + item.id;
			if (form.wpg2_tag_size.value)
				str += '|' + form.wpg2_tag_size.value;
			str += '" title="' + item.id;
			if (form.wpg2_tag_size.value)
				str += '|' + form.wpg2_tag_size.value;
			str += '" width="' + imageObj.thumbw + '" height="' + imageObj.thumbh
			+ '" id="mce_plugin_g2image_wpg2" />';
		}
		else {
			str += '<wpg2>' + item.id;
			if (form.wpg2_tag_size.value)
				str += '|' + form.wpg2_tag_size.value;
			str += '</wpg2>';
		}
		if (form.alignment.value != 'none'){
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

		// Check that the ImageBlock module supports the exactsize attribure (requires module API 1.0.9 or later)
		GalleryCoreApi::requireOnce('modules/imageblock/module.inc');
		GalleryCoreApi::requireOnce('modules/core/classes/GalleryRepositoryUtilities.class');
		$plugin = new ImageBlockModule;
		$version = $plugin->getVersion();
		$version_comparison = GalleryRepositoryUtilities::compareRevisions($version,'1.0.9');
		if ($version_comparison != 'older') {
			$html = '				<label for="wpg2_tag_size">' . T_('WPG2 tag "size" attribute (Leave blank for the default size of: ') . $g2ic_options['wpg2_tag_size']. 'px)<br /></label>' . "\n"
			. '				<input type="text" name="wpg2_tag_size" size="84" maxlength="150" value="" />' . "\n"
			. '				<br />' . "\n";
		}
		else {
			$html = '				<input type="hidden" name="wpg2_tag_size" value="" />' . "\n";
		}

		return $html;
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