<?php
/**
 * Get all of the Gallery2 items
 *
 * @return array $album_info Album Title and URL for the current album
 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
 */

class bbcode_fullsize_only{

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
		str += '[img]' + item.fullsize_image + '[/img]';
		return str;
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
		return '';
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Fullsized image only - no link') . ' ' . T_('(BBCode)');
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