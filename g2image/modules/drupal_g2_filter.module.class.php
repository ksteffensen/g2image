<?php
/**
 * thumbnail
 *
 */

class drupal_g2_filter{

	/**
	 * See sample module for details
	 */
	function insert($name){
		// caution: \n in javascript strings: \\n
//## JAVASCRIPT #################
		$script = <<<SCRIPTSTUFF
	//module [{$name}]
	insertFunctions["{$name}"] = module_{$name};

	function module_{$name}(stack, form, item, album, options){
		var str = "";

		str += '[' + options.drupal_filter_prefix + ':' + item.id;
		if (form.alignment.value != 'none'){
			str += ' class=' + form.alignment.value;
		}
		if (form.drupal_exactsize.value)
			str += ' exactsize=' + form.drupal_exactsize.value;
		str += ']';

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
			$html = '				<label for="drupal_exactsize">' . T_('Drupal G2 Filter "exactsize" attribute (Leave blank for no exactsize attribute)') . '<br /></label>' . "\n"
			. '				<input type="text" name="drupal_exactsize" size="84" maxlength="150" value="" />' . "\n"
			. '				<br />' . "\n";
		}
		else {
			$html = '				<input type="hidden" name="drupal_exactsize" value="" />' . "\n";
		}

		return $html;
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Drupal Gallery2 Module filter tag');
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