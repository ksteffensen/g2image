<?php
/*
Plugin Name: Gallery2 Image Chooser
Version: 1.3.5
Plugin URI: http://g2image.steffensenfamily.com/
Description: This plugin adds the Gallery2 Image Chooser to both quicktags and the rich-text editor (TinyMCE), allowing you to visually select a thumbnail from Gallery2 and insert it with multiple options into the post.  It is designed to work with and complement <a href="http://wpg2.ozgreg.com/">WPG2</a> (however it can be used as a stand-alone program with fewer capabilities than when used with WPG2).
Author: Kirk Steffensen
Author URI: http://www.steffensenfamily.com/
Update: http://g2image.steffensenfamily.com/Download/
*/

/*
Gallery 2 Image Chooser for WordPress
Version 1.3.5
By Kirk Steffensen - http://g2image.steffensenfamily.com/
Released under the GPL version 2.
A copy of the license is in the root folder of this plugin.
See README.HTML for installation info.
See CHANGELOG.HTML for a history of changes.
See CREDITS.HTML for inspiration, code, and assistance credits.
*/

// Load the G2Image translation files
load_plugin_textdomain('g2image','wp-content/plugins/g2image/locale');

$g2ic_wp_url = get_settings('siteurl');

add_action('admin_menu', 'g2ic_processwpadminhooks');
add_filter('admin_footer', 'g2ic_callback');
add_filter('mce_plugins', 'g2ic_extended_editor_mce_plugins', 0);
add_filter('mce_buttons', 'g2ic_extended_editor_mce_buttons', 0);
add_filter('mce_valid_elements', 'g2ic_extended_editor_mce_valid_elements', 0);

function g2ic_processwpadminhooks(){
	add_options_page(__('G2Image Popup Options', 'g2image'), __('G2Image', 'g2image'), 8, 'g2image/g2image-admin.php');
}

function g2ic_extended_editor_mce_plugins($plugins) {
array_push($plugins, 'g2image');
return $plugins;
}

function g2ic_extended_editor_mce_buttons($buttons) {
array_push($buttons, 'separator', 'g2image');
return $buttons;
}

function g2ic_extended_editor_mce_valid_elements($valid_elements) {
$valid_elements .= 'wpg2,wpg2id';
return $valid_elements;
}

function g2ic_callback()
{
	global $g2ic_wp_url;

	if(strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'bookmarklet.php'))
	{
?>
<script language="JavaScript" type="text/javascript"><!--
	var g2ic_toolbar = document.getElementById("ed_toolbar");
<?php
		g2ic_edit_insert_button(__('G2Image', 'g2image'), 'g2ic_open', __('Gallery2 Image Chooser', 'g2image'));
?>

	function g2ic_open() {

		var form = 'post';
		var field = 'content';
		var url = '<?php echo $g2ic_wp_url; ?>/wp-includes/js/tinymce/plugins/g2image/g2image.php?g2ic_form='+form+'&g2ic_field='+field+'&g2ic_tinymce=0';
		var name = 'g2image';
		var w = 600;
		var h = 600;
		var valLeft = (screen.width) ? (screen.width-w)/2 : 0;
		var valTop = (screen.height) ? (screen.height-h)/2 : 0;
		var features = 'width='+w+',height='+h+',left='+valLeft+',top='+valTop+',resizable=1,scrollbars=1';
		var g2imageWindow = window.open(url, name, features);
		g2imageWindow.focus();
	}
//--></script>
<?php
	}
}

if(!function_exists('g2ic_edit_insert_button')) {

	//edit_insert_button: Inserts a button into the editor
	function g2ic_edit_insert_button($caption, $js_onclick, $title = '') {
	?>

	if(g2ic_toolbar){
		var theButton = document.createElement('input');
		theButton.type = 'button';
		theButton.value = '<?php echo $caption; ?>';
		theButton.onclick = <?php echo $js_onclick; ?>;
		theButton.className = 'ed_button';
		theButton.title = "<?php echo $title; ?>";
		theButton.id = "<?php echo "ed_{$caption}"; ?>";
		g2ic_toolbar.appendChild(theButton);
	}
	<?php

	}
}
?>