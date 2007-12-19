<?php
/**
 * Get all of the Gallery2 items
 *
 * @return array $album_info Album Title and URL for the current album
 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
 */

class ultimate_slideshow_images{

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
		var html = '';
		if (item.item_number == 0) {
			html += '<script type="text/javascript"> \\n'
			+ 'var fadeimages=new Array(); \\n';
		}
		html += 'fadeimages[' + item.item_number + ']=["' + item.fullsize_image + '", "' + item.base_item_url + '", ""] \\n';
		var lastItem = item.total_items - 1;
		if (item.item_number == lastItem) {
			html += 'new fadeshow(fadeimages, ' + form.max_width.value + ', ' + form.max_height.value + ', 0, 3000, 1); \\n';
			html += '\</script\>';
		}
		return html;
	}
	//end module [{$name}]

SCRIPTSTUFF;
//## END JAVASCRIPT #############

		return $script;

	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * here we can add extra vars or settings for the rendering
	 *
	 */
	function dialog(){
//		$html = '				<label for="text_link_image">' . T_('Text for text link') . '<br /></label>' . "\n"
//		. '				<input type="text" name="text_link_image" size="84" maxlength="150" value="" />' . "\n"
//		. '				<br />' . "\n";
//		return $html;
		return;  // TODO Add controls
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Ultimate Fade-In Slideshow') . ' ' . T_('(HTML)');
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * instead of a selectionbox a icon
	 *
	 */
	function icon(){
		return '';
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * check for needed javascripts
	 *
	 */
	function preeq(){
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 *
	 */
	function help(){
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * maybe a setup function
	 *
	 */
	function setup(){
		return '';
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * for later,avoid compatibility problem
	 * and any information can be added in the array, like help etc.
	 * @return mixed string or array
	 *
	 */
	function extra($key=false){
		$data = array();
		$data["version"] = "sample V.0.1";
		$data["description"] = "this is the prototype";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
	//------------------------------------------------------------
	//------------------------------------------------------------
}
//------------------------------------------------------------
?>