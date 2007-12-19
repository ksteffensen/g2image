<?php
/**
 * thumbnail
 *
 */

class thumbnail_album{

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
		if ((form.alignment.value != 'none') && (options.class_mode == 'div')){
			str += '<div class="' + form.alignment.value + '">';
		}
		str += '<a href="' + album.base_item_url + '"';
		if (form.html_target.value){
			str += ' target="' + form.html_target.value + '"';
		}
		if (form.html_onclick.value){
			str += ' onclick="' + form.html_onclick.value + '"';
		}
		str += '><img src="' + item.thumbnail_image + '"'
		+ ' width ="' + item.thumbnail_width + '"'
		+ ' height="' + item.thumbnail_height + '"'
		+ ' alt="' + item.title + '"'
		+ ' title="' + item.summary + '"';
		if ((form.alignment.value != 'none') && (options.class_mode == 'img')){
			str += ' class="' + form.alignment.value + '"';
		}
		str += ' /></a>';
		if ((form.alignment.value != 'none') && (options.class_mode == 'div')){
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
		return T_('Thumbnail with link to parent album') . ' ' . T_('(HTML)');
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
		$data["description"] = "HTML for thumbnail image with a link to the image's parent album in Gallery2";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
}
?>