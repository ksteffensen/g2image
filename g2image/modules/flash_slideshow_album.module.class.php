<?php
/**
 * thumbnail
 *
 */

class flash_slideshow_album{

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

		var g2imageUrl = location.href;
		g2imageUrl = g2imageUrl.substring(0, g2imageUrl.lastIndexOf('/g2image.php'));

		if (imageObj['album_alignment'] != 'none'){
			str += '<div class="' + imageObj['album_alignment'] + '">';
		}
		str += '<embed src="' + g2imageUrl + '/minislideshow/minislideshow.swf" '
		+ 'flashvars="xmlUrl=/wordpress/wp-content/plugins/wpg2/g2image/minislideshow/xml.php?g2_itemId='
		+ imageObj['current_album'] + '&delay=' + imageObj['flash_slideshow_delay'] + '&titleColor=' + imageObj['flash_slideshow_title_color']
		+ '&titleBgColor=' + imageObj['flash_slideshow_title_bg_color'] + '" quality="high" wmode="transparent" name="minislide" '
		+ 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" '
		+ 'align="middle" height="' + imageObj['flash_slideshow_height'] + '" width="' + imageObj['flash_slideshow_width'] + '"></embed>';
		if (imageObj['album_alignment'] != 'none'){
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
		$html = '                <label for="flash_slideshow_width">' . T_('Width') . '</label>' . "\n"
		. '                <input type="text" name="flash_slideshow_width" size="4" maxlength="4" value="150" />' . "\n"
		. '                <label for="flash_slideshow_height">' . T_('Height') . '</label>' . "\n"
		. '                <input type="text" name="flash_slideshow_height" size="4" maxlength="4" value="150" />' . "\n"
		. '                <label for="flash_slideshow_delay">' . T_('Delay') . '</label>' . "\n"
		. '                <input type="text" name="flash_slideshow_delay" size="4" maxlength="4" value="3" />' . "\n"
		. '                <label for="flash_slideshow_title_color">' . T_('Title Color') . '</label>' . "\n"
		. '                <input type="text" name="flash_slideshow_title_color" size="7" maxlength="6" value="FFFFFF" />' . "\n"
		. '                <label for="flash_slideshow_title_bg_color">' . T_('Title Background Color') . '</label>' . "\n"
		. '                <input type="text" name="flash_slideshow_title_bg_color" size="7" maxlength="6" value="333333" />' . "\n"
		. '                <br />' . "\n";
		return $html;
	}

	/**
	 * Set the javascript variables that this module requires.  Must be unique names among modules.
	 *
	 */
	function javaScriptVariables(){
		$html = "					imageObj.flash_slideshow_width = obj.flash_slideshow_width.value;\n"
		. "					imageObj.flash_slideshow_height = obj.flash_slideshow_height.value;\n"
		. "					imageObj.flash_slideshow_delay = obj.flash_slideshow_delay.value;\n"
		. "					imageObj.flash_slideshow_title_color = obj.flash_slideshow_title_color.value;\n"
		. "					imageObj.flash_slideshow_title_bg_color = obj.flash_slideshow_title_bg_color.value;\n";
		return $html;
	}

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return T_('Flash slideshow of album');
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