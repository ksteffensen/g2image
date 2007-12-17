<?php
/**
 * thumbnail
 *
 */

class thumbnail_lightbox{

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
		var str = "";
		if ((form.alignment.value != 'none') && (options.class_mode == 'div')){
			str += '<div class="' + form.alignment.value + '">';
		}
		str += '<a href="' + imageObj.fullsize_img + '" rel="lightbox';
		if (imageObj['lightbox_group'])
			str += '[' + form.lightbox_group + ']';
		str += '" title="' + item.description
		+ '"><img src="' + imageObj.thumbnail_img
		+ '" width ="' + imageObj.thumbw
		+ '" height="' + imageObj.thumbh
		+ '" alt="' + item.title
		+ '" title="' + item.summary
		+ '"';
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
		$html = '                <label for="lightbox_group">' . T_('LightBox Group (Leave blank to not group with other images)') . '<br /></label>' . "\n"
		. '                <input type="text" name="lightbox_group" size="84" maxlength="150" value="g2image" />' . "\n"
		. '                <br />' . "\n";
		return $html;
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Thumbnail with LightBox link to Fullsized Image') . ' ' . T_('(HTML)');
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