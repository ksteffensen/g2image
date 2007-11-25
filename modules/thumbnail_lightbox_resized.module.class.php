<?php
/**
 * A module to allow inserting a resized image from gallery2
 * :tabSize=2:indentSize=2:noTabs=false:
 * :folding=explicit:collapseFolds=1:
 *
 * @return array $album_info Album Title and URL for the current album
 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
 */


//------------------------------------------------------------
//------------------------------------------------------------
// the skeleton of a module
//------------------------------------------------------------
// each new insertModule should be an extension of this basich module
//
class resized_image {

	//------------------------------------------------------------
	/**
	 * this is the main part of the class rendering the desired output
	 *	var stack = [];
	 *	stack[0] = module_name
	 *	stack[1] = function within module
	 *	stack[n] = optional extra subfunction or switch
	 *
	 *	var args = {};
	 *
	 *	var imageObj = {};
	 *	imageObj.id
	 *	imageObj.url
	 *	imageObj.original
	 *	imageObj.thumbnail
	 *	imageObj.thumbw
	 *	imageObj.thumbh
	 *	imageObj.w
	 *	imageObj.h
	 *	imageObj.title
	 *	imageObj.summary
	 *	imageObj.description
	 *	imageObj.album_id
	 *	imageObj.keywords
	 *	imageObj.derivatives	//	all resized versions of the image or a function that deliver this
	 *	imageObj.siblings		// all images in the same album or a function that deliver this
	 *
	 *	@return $string
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
		str += '<a href="' + imageObj['resized_img'] + '" rel="lightbox';
		if (imageObj['lightbox_group_resize'])
			str += '[' + imageObj['lightbox_group_resize'] + ']';
		str += '" title="' + imageObj['item_description'] + '"><img src="'
		+ imageObj['thumbnail_img'] + '" ' + imageObj['thumbw']
		+ ' ' + imageObj['thumbh'] + ' alt="' + imageObj['item_title'] + '" title="' + imageObj['item_summary'] + '"';
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
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * here we can add extra vars or settings for the rendering
	 *
	 */
	function dialog(){
		$html = '                <label for="lightbox_group_resize">' . T_('LightBox Group (Leave blank to not group with other images)') . '<br /></label>' . "\n"
		. '                <input type="text" name="lightbox_group_resize" size="84" maxlength="150" value="g2image" />' . "\n"
		. '                <br />' . "\n";
		return $html;
	}
	//------------------------------------------------------------
	//------------------------------------------------------------
	
	/**
	 * Set the javascript variables that this module requires.  Must be unique names among modules.
	 *
	 */
	function javaScriptVariables(){
		return "					imageObj.lightbox_group_resize = obj.lightbox_group_resize.value;\n";
	}
	
	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Thumbnail with LightBox link to Resized Image');
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * instead of a selectionbox a icon
	 *
	 */
	function icon(){
		return "";
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
		return "";
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
		$data["version"] = 1.0;
		$data["description"] = "Lightbox to the first resized image. If no resized image exists the fullsized image will be used.";
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