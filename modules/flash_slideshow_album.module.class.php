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

	function module_{$name}(stack, form, album, options){
		var str = "";

		var g2imageUrl = location.href;
		g2imageUrl = g2imageUrl.substring(0, g2imageUrl.lastIndexOf('/g2image.php'));

		if (form.album_alignment.value != 'none'){
			str += '<div class="' + form.album_alignment.value + '">';
		}
		str += '<embed src="' + g2imageUrl + '/flash/minislideshow.swf" '
		+ 'flashvars="xmlUrl=' + g2imageUrl + '/flash/mediaRss.php?g2_itemId='
		+ album.id
		+ '%26g2_maxImageHeight=' + form.flash_slideshow_height.value
		+ '%26g2_maxImageWidth=' + form.flash_slideshow_width.value
		if (form.flash_slideshow_use_full.value == 'true') {
			str += '&useFull=true';
		}
		+ '&delay=' + form.flash_slideshow_delay.value;
		if (form.flash_slideshow_shuffle.value == 'true') {
			str += '&shuffle=true';
		}
		if (form.flash_slideshow_drop_shadow.value == 'true') {
			str += '&showDropShadow=true';
		}
		str += '&transInType=' + form.flash_slideshow_transition_in.value
		+ '&transOutType=' + form.flash_slideshow_transition_out.value;
		if (form.flash_slideshow_no_link.value == 'true') {
			str += '&noLink=true';
		}
		if (form.flash_slideshow_alt_link.value != 'false') {
			str += '&altLink=' + form.flash_slideshow_alt_link.value;
		}
		str += '&linkTarget=' + form.flash_slideshow_link_target.value;
		if (form.flash_slideshow_show_title.value == 'true') {
			str += '&showTitle=true'
			+ '&titleColor=' + form.flash_slideshow_title_color.value
			+ '&titleBgColor=' + form.flash_slideshow_title_bg_color.value;
		}
		if (form.flash_slideshow_masks.value == 'circleMask') {
			str += '&circleMask=true';
		}
		if (form.flash_slideshow_masks.value == 'roundedMask') {
			str += '&roundedMask=true';
		}
		if (form.flash_slideshow_masks.value == 'starMask') {
			str += '&starMask=true';
		}
		if (options.flash_slideshow_site_info_text) {
			str += '&siteInfoText=' + options.flash_slideshow_site_info_text;
		}
		if (options.flash_slideshow_site_info_url) {
			str += '&siteInfoUrl=' + options.flash_slideshow_site_info_url;
		}
		str += '" quality="high"';
		if (form.flash_slideshow_use_full.value == 'true') {
			str += ' allowfullscreen=true';
		}
		else {
			str += ' wmode="transparent"';
		}
		str += ' name="minislide"'
		+ ' type="application/x-shockwave-flash"'
		+ ' pluginspage="http://www.macromedia.com/go/getflashplayer"'
		+ ' align="middle" height="' + form.flash_slideshow_height.value + '"'
		+ ' width="' + form.flash_slideshow_width.value + '"></embed>';
		if (form.album_alignment.value != 'none'){
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

		$html = '				' . T_('Flash Slideshow Configuration') . "\n"
		. '				<input type="button"' . "\n"
		. '				name="show_flash_slideshow_configuration"' . "\n"
		. '				onclick="document.getElementById(\'flash_slideshow_configuration\').style.display=\'inline\';"' . "\n"
		. '				value="' . T_('Show') . '"' . "\n"
		. '				/>' . "\n"
		. '				<input type="button"' . "\n"
		. '				name="hide_flash_slideshow_configuration"' . "\n"
		. '				onclick="document.getElementById(\'flash_slideshow_configuration\').style.display=\'none\';"' . "\n"
		. '				value="' . T_('Hide') . '"' . "\n"
		. '				/>' . "\n"
		. '				<br />' . "\n"
		. '				<div id="flash_slideshow_configuration" style="display:none">' . "\n"
		. '					' . T_('Height') . "\n"
		. '					<input type="text" name="flash_slideshow_height" size="4" maxlength="4" value="' . $g2ic_options['flash_slideshow_height'] . '" />' . "\n"
		. '					' . T_('Width') . "\n"
		. '					<input type="text" name="flash_slideshow_width" size="4" maxlength="4" value="' . $g2ic_options['flash_slideshow_width'] . '" /><br />' . "\n"
		. '					' . T_('Use Fullsized Images') . "\n"
		. '					<select name="flash_slideshow_use_full" size="1">' . "\n"
		. '						<option value="true">' . T_('True (Use Fullsized Image)') . '</option>' . "\n"
		. '						<option value="false"' . flash_slideshow_album::selected('flash_slideshow_use_fullsize', 'false') . '>' . T_('False (Use Thumbnails)') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Delay (in seconds)') . "\n"
		. '					<input type="text" name="flash_slideshow_delay" size="4" maxlength="4" value="' . $g2ic_options['flash_slideshow_delay'] . '" /><br />' . "\n"
		. '					' . T_('Shuffle') . "\n"
		. '					<select name="flash_slideshow_shuffle" size="1">' . "\n"
		. '						<option value="false">' . T_('False') . '</option>' . "\n"
		. '						<option value="true"' . flash_slideshow_album::selected('flash_slideshow_shuffle', 'true') . '>' . T_('True') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Drop Shadow') . "\n"
		. '					<select name="flash_slideshow_drop_shadow" size="1">' . "\n"
		. '						<option value="false">' . T_('False') . '</option>' . "\n"
		. '						<option value="true' . flash_slideshow_album::selected('flash_slideshow_drop_shadow', 'true') . '">' . T_('True') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Transition In') . "\n"
		. '					<select name="flash_slideshow_transition_in" size="1">' . "\n"
		. '						<option value="Fade">' . T_('Fade') . '</option>' . "\n"
		. '						<option value="Blinds"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Blinds') . '>' . T_('Blinds') . '</option>' . "\n"
		. '						<option value="Fly"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Fly') . '>' . T_('Fly') . '</option>' . "\n"
		. '						<option value="Iris"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Iris') . '>' . T_('Iris') . '</option>' . "\n"
		. '						<option value="Photo"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Photo') . '>' . T_('Photo') . '</option>' . "\n"
		. '						<option value="PixelDissolve"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'PixelDissolve') . '>' . T_('Pixel Dissolve') . '</option>' . "\n"
		. '						<option value="Rotate"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Rotate') . '>' . T_('Rotate') . '</option>' . "\n"
		. '						<option value="Squeeze"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Squeeze') . '>' . T_('Squeeze') . '</option>' . "\n"
		. '						<option value="Wipe"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Wipe') . '>' . T_('Wipe') . '</option>' . "\n"
		. '						<option value="Random"' . flash_slideshow_album::selected('flash_slideshow_transition_in', 'Random') . '>' . T_('Random') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Transition Out') . "\n"
		. '					<select name="flash_slideshow_transition_out" size="1">' . "\n"
		. '						<option value="Fade">' . T_('Fade') . '</option>' . "\n"
		. '						<option value="Blinds"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Blinds') . '>' . T_('Blinds') . '</option>' . "\n"
		. '						<option value="Fly"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Fly') . '>' . T_('Fly') . '</option>' . "\n"
		. '						<option value="Iris"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Iris') . '>' . T_('Iris') . '</option>' . "\n"
		. '						<option value="Photo"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Photo') . '>' . T_('Photo') . '</option>' . "\n"
		. '						<option value="PixelDissolve"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'PixelDissolve') . '>' . T_('Pixel Dissolve') . '</option>' . "\n"
		. '						<option value="Rotate"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Rotate') . '>' . T_('Rotate') . '</option>' . "\n"
		. '						<option value="Squeeze"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Squeeze') . '>' . T_('Squeeze') . '</option>' . "\n"
		. '						<option value="Wipe"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Wipe') . '>' . T_('Wipe') . '</option>' . "\n"
		. '						<option value="Random"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'Random') . '>' . T_('Random') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('No Link') . "\n"
		. '					<select name="flash_slideshow_no_link" size="1">' . "\n"
		. '						<option value="false">' . T_('False') . '</option>' . "\n"
		. '						<option value="true"' . flash_slideshow_album::selected('flash_slideshow_no_link', 'true') . '>' . T_('True') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Alternate Link') . "\n"
		. '					<input type="text" name="flash_slideshow_alt_link" size="80" maxlength="256" value="' . $g2ic_options['flash_slideshow_alt_link'] . '" /><br />' . "\n"
		. '					' . T_('Link Target') . "\n"
		. '					<select name="flash_slideshow_link_target" size="1">' . "\n";
		if (($g2ic_options['flash_slideshow_link_target'] != '_parent') && ($g2ic_options['flash_slideshow_link_target'] != '_blank') && ($g2ic_options['flash_slideshow_link_target'] != '_self') && ($g2ic_options['flash_slideshow_link_target'] != '_top')) {
			$html .= '						<option value="' . $g2ic_options['flash_slideshow_link_target'] . '">' . $g2ic_options['flash_slideshow_link_target'] . '</option>' . "\n";
		}
		$html .= '						<option value="_parent">' . T_('_parent') . '</option>' . "\n"
		. '						<option value="_blank"' . flash_slideshow_album::selected('flash_slideshow_link_target', '_blank') . '>' . T_('_blank') . '</option>' . "\n"
		. '						<option value="_self"' . flash_slideshow_album::selected('flash_slideshow_link_target', '_self') . '>' . T_('_self') . '</option>' . "\n"
		. '						<option value="_top"' . flash_slideshow_album::selected('flash_slideshow_link_target', '_top') . '>' . T_('_top') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Show Title') . "\n"
		. '					<select name="flash_slideshow_show_title" size="1">' . "\n"
		. '						<option value="false">' . T_('False') . '</option>' . "\n"
		. '						<option value="top"' . flash_slideshow_album::selected('flash_slideshow_show_title', 'top') . '>' . T_('Top') . '</option>' . "\n"
		. '						<option value="bottom"' . flash_slideshow_album::selected('flash_slideshow_show_title', 'bottom') . '>' . T_('Bottom') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '					' . T_('Title Color (000000 = Black, FFFFFF = White)') . "\n"
		. '					<input type="text" name="flash_slideshow_title_color" size="7" maxlength="6" value="' . $g2ic_options['flash_slideshow_title_color'] . '" /><br />' . "\n"
		. '					' . T_('Title Background Color') . "\n"
		. '					<input type="text" name="flash_slideshow_title_bg_color" size="7" maxlength="6" value="' . $g2ic_options['flash_slideshow_title_bg_color'] . '" /><br />' . "\n"
		. '					' . T_('Mask') . "\n"
		. '					<select name="flash_slideshow_masks" size="1">' . "\n"
		. '						<option value="none">' . T_('None') . '</option>' . "\n"
		. '						<option value="circleMask"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'circleMask') . '>' . T_('Circle Mask') . '</option>' . "\n"
		. '						<option value="roundedMask"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'roundedMask') . '>' . T_('Rounded Mask') . '</option>' . "\n"
		. '						<option value="starMask"' . flash_slideshow_album::selected('flash_slideshow_transition_out', 'starMask') . '>' . T_('Star Mask') . '</option>' . "\n"
		. '					</select><br />' . "\n"
		. '				</div>' . "\n";
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

	function selected($name, $value) {
		global $g2ic_options;

		$html = '';

		if ($g2ic_options[$name] == $value) {
			$html = ' SELECTED';
		}

		return $html;
	}

}
?>