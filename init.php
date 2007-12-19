<?php
/*
    Gallery 2 Image Chooser
    Version 3.1 alpha - updated 07 OCT 2007
    Documentation: http://g2image.steffensenfamily.com/

    Author: Kirk Steffensen with inspiration, code snipets,
        and assistance as listed in CREDITS.HTML

    Released under the GPL version 2.
    A copy of the license is in the root folder of this plugin.

    See README.HTML for installation info.
    See CHANGELOG.HTML for a history of changes.
*/

require_once('config.php');

// ====( Initialize Variables )=================================
$g2ic_options['current_page'] = 1;
$g2ic_options['wpg2_valid'] = FALSE;
$g2ic_options['wp_rel_path'] = '';
echo $_SERVER['PHP_SELF'];
$g2ic_options['base_path'] = str_repeat("../", substr_count(dirname($_SERVER['PHP_SELF']), "/"));

// Convert the variables from config.php to $g2ic_options array items.
// Kept the original variable names in config.php for backwards compatibility with
// some integrations that overwrite config.php to set these variables.
$g2ic_options['images_per_page'] = $g2ic_images_per_page;
$g2ic_options['display_filenames'] = $g2ic_display_filenames;
$g2ic_options['alignment'] = $g2ic_default_alignment;
$g2ic_options['album_alignment'] = $g2ic_default_alignment;
$g2ic_options['custom_class_1'] = $g2ic_custom_class_1;
$g2ic_options['custom_class_2'] = $g2ic_custom_class_2;
$g2ic_options['custom_class_3'] = $g2ic_custom_class_3;
$g2ic_options['custom_class_4'] = $g2ic_custom_class_4;
$g2ic_options['custom_url'] = $g2ic_custom_url;
$g2ic_options['class_mode'] = $g2ic_class_mode;
$g2ic_options['imginsert'] = $g2ic_default_image_action;
$g2ic_options['albuminsert'] = $g2ic_default_album_action;
$g2ic_options['sortby'] = $g2ic_sortby;
$g2ic_options['album_sortby'] = $g2ic_album_sortby;
$g2ic_options['max_width'] = $g2ic_max_width;
$g2ic_options['max_height'] = $g2ic_max_height;
$g2ic_options['html_target'] = $g2ic_html_target;
$g2ic_options['custom_target'] = $g2ic_custom_target;
$g2ic_options['html_onclick'] = $g2ic_html_onclick;
$g2ic_options['drupal_g2_filter'] = $g2ic_drupal_g2_filter;
$g2ic_options['drupal_g2_filter_prefix'] = $g2ic_drupal_g2_filter_prefix;
$g2ic_options['bbcode_enabled'] = $g2ic_bbcode_enabled;
$g2ic_options['bbcode_only'] = $g2ic_bbcode_only;
$g2ic_options['embedded_mode'] = $g2ic_embedded_mode;
$g2ic_options['gallery2_path'] = $g2ic_gallery2_path;
$g2ic_options['use_full_path'] = $g2ic_use_full_path;
$g2ic_options['gallery2_uri'] = $g2ic_gallery2_uri;
$g2ic_options['embed_uri'] = $g2ic_embed_uri;
$g2ic_options['language'] = $g2ic_language;
$g2ic_options['tinymce'] = 0;
$g2ic_options['form'] = '';
$g2ic_options['field'] = '';
$g2ic_options['current_album'] = null;
$g2ic_options['current_page'] = 1;
$g2ic_tree = null;
$g2ic_items = null;
$g2ic_totalAvailableDataItems = null;
$g2ic_session_variables = null;

// ==============================================================
// WPG2 validation
// ==============================================================

// Determine if in a WordPress installation by checking for wpg2.php or $g2ic_in_wordpress being set
if (@file_exists('../wpg2.php') || $g2ic_in_wordpress) {
	// G2Image is being called from WPG2 directory
	if (@file_exists('../wpg2.php')) {
		require_once('../../../../wp-config.php');
	}
	// Otherwise user has set $g2ic_in_wordpress == TRUE because G2Image is being called by another editor.  E.g, FCKEditor
	else {
		for ($count = 1; $count <= 10; $count++) {
			$g2ic_options['wp_rel_path'] = $g2ic_options['wp_rel_path'] . '../';
			if (@file_exists($g2ic_options['wp_rel_path'] . 'wp-config.php')) {
				require_once($g2ic_options['wp_rel_path'] . 'wp-config.php');
				break;
			}
			elseif ($count == 10) {
				// Die on fatal error of not finding wp-config.php
				g2ic_fatal_error('<h3>Fatal Error: Cannot locate wp-config.php.</h3><br />You have set $g2ic_in_wordpress to TRUE, but G2Image cannot locate wp-config.php in any parent directory.');
			}
		}
	}
	$wpg2_g2ic = get_option('wpg2_g2ic');
	$wpg2_g2paths = get_option('wpg2_g2paths');
	$wpg2_options = get_option('wpg2_options');
	$g2ic_options['language'] = get_locale();

	// Assume WPG2 is active, or we wouldn't be here.
	$g2ic_options['wpg2_valid'] = TRUE;
	$g2ic_options['embedded_mode'] = TRUE;
	$g2ic_options['use_full_path'] = TRUE;

	// Call g2ic_init with WPG2 URI info
	$g2ic_options['embed_uri'] = $wpg2_g2paths['g2_embeduri'];
	$g2ic_options['gallery2_uri'] = $wpg2_g2paths['g2_url'];
	$g2ic_options['gallery2_path'] = $wpg2_g2paths['g2_filepath'];

	// Get the configurations options from the WPG2 admin panel
	if(isset($wpg2_options['g2_tagimgsize']))
		$g2ic_options['wpg2_tag_size'] = $wpg2_options['g2_tagimgsize'];
	if(isset($wpg2_g2ic['g2ic_images_per_page']))
		$g2ic_options['images_per_page'] = $wpg2_g2ic['g2ic_images_per_page'];
	if(isset($wpg2_g2ic['g2ic_display_filenames'])){
		if($wpg2_g2ic['g2ic_display_filenames']=='yes')
			$g2ic_options['display_filenames'] = TRUE;
		else
			$g2ic_options['display_filenames'] = FALSE;
	}
	if(isset($wpg2_g2ic['g2ic_default_alignment'])) {
		$g2ic_options['alignment'] = $wpg2_g2ic['g2ic_default_alignment'];
		$g2ic_options['album_alignment'] = $wpg2_g2ic['g2ic_default_alignment'];
	}
	if(isset($wpg2_g2ic['g2ic_custom_class_1']))
		$g2ic_options['custom_class_1'] = $wpg2_g2ic['g2ic_custom_class_1'];
	if(isset($wpg2_g2ic['g2ic_custom_class_2']))
		$g2ic_options['custom_class_2'] = $wpg2_g2ic['g2ic_custom_class_2'];
	if(isset($wpg2_g2ic['g2ic_custom_class_3']))
		$g2ic_options['custom_class_3'] = $wpg2_g2ic['g2ic_custom_class_3'];
	if(isset($wpg2_g2ic['g2ic_custom_class_4']))
		$g2ic_options['custom_class_4'] = $wpg2_g2ic['g2ic_custom_class_4'];
	if(isset($wpg2_g2ic['g2ic_custom_url']))
		$g2ic_options['custom_url'] = $wpg2_g2ic['g2ic_custom_url'];
	if(isset($wpg2_g2ic['g2ic_class_mode']))
		$g2ic_options['class_mode'] = $wpg2_g2ic['g2ic_class_mode'];
	if(isset($wpg2_g2ic['g2ic_sortby']))
		$g2ic_options['sortby'] = $wpg2_g2ic['g2ic_sortby'];
	if(isset($wpg2_g2ic['g2ic_default_album_action']))
		$g2ic_options['albuminsert'] = $wpg2_g2ic['g2ic_default_album_action'];
	else
		$g2ic_options['albuminsert'] = 'wpg2_album';
	if(isset($wpg2_g2ic['g2ic_default_image_action'])) {
		// For backwards compatibility with old option value in WPG2 G2Image Options tab
		if ($wpg2_g2ic['g2ic_default_image_action'] == 'wpg2')
			$g2ic_options['imginsert'] = 'wpg2_image';
		else
			$g2ic_options['imginsert'] = $wpg2_g2ic['g2ic_default_image_action'];
	}
	else
		$g2ic_options['imginsert'] = 'wpg2_image';
}

session_start();

if (isset($_SESSION['g2ic_options'])) {
	$g2ic_options = unserialize($_SESSION['g2ic_options']);
	$g2ic_session_variables = $g2ic_options;
}

// Is this a TinyMCE window?
if(isset($_REQUEST['g2ic_tinymce'])){
	$g2ic_options['tinymce'] = $_REQUEST['g2ic_tinymce'];
}

// Get the form name (if set) for insertion (not TinyMCE or FCKEditor)
if(isset($_REQUEST['g2ic_form'])){
	$g2ic_options['form'] = $_REQUEST['g2ic_form'];
}

// Get the field name (if set) for insertion (not TinyMCE or FCKEditor)
if(isset($_REQUEST['g2ic_field'])){
	$g2ic_options['field'] = $_REQUEST['g2ic_field'];
}

foreach ($g2ic_options as $key=>$value) {
	if (isset($_REQUEST[$key])) {
		$g2ic_options[$key] = $_REQUEST[$key];
	}
}

if(isset($_SESSION['g2ic_tree'])) {
	if (!isset($_REQUEST['refresh_album_tree']) && ($g2ic_session_variables['album_sortby'] == $g2ic_options['album_sortby'])) {
		$g2ic_tree = unserialize($_SESSION['g2ic_tree']);
	}
}

if(isset($_SESSION['g2ic_items'])) {
	if (($g2ic_session_variables['current_album'] == $g2ic_options['current_album']) && ($g2ic_session_variables['sortby'] == $g2ic_options['sortby']) && ($g2ic_session_variables['current_page'] == $g2ic_options['current_page']) && ($g2ic_session_variables['images_per_page'] == $g2ic_options['images_per_page'])) {
		$g2ic_items = unserialize($_SESSION['g2ic_items']);
		$g2ic_totalAvailableDataItems = $_SESSION['g2ic_totalAvailableDataItems'];
	}
}

$_SESSION['g2ic_options'] = serialize($g2ic_options);

// ==============================================================
// NOTE for developers:
// If you are developing an embedded application for Gallery2 and want to use
// this plugin for accessing Gallery2, this is where you'll need
// to validate that your ebedded page exists and then get the values from your
// embedded application $g2ic_options['gallery2_path'], $g2ic_options['gallery2_uri'], and
// $g2ic_options['embed_uri'].  Descriptions of these variables are in config.php
//
// You'll also need to set $g2ic_options['embedded_mode'] to TRUE.
//
// If you use the full directory path for $g2ic_gallery_path, you'll need to set
// $g2ic_options['use_full_path'] to TRUE.  If you use a path relative to the root web page,
// you'll need to set $g2ic_options['use_full_path'] to FALSE.
//
// If your embedded application sets its own localization, you'll need to set the
// language in $g2ic_options['language'].  If you need to load the language file for any
// initialization messages (as in the WPG2 code above), you'll need to load it
// before those messages appear.  If you do load it in your initialization
// sequence, set $g2ic_options['language']_loaded to TRUE, so that the language pack won't
// get loaded again.
//
// See http://g2image.steffensenfamily.com for more details.
//
// ==============================================================

// Determine gettext locale
if (file_exists('./langs/' . $g2ic_options['language'] . '.mo')) {
	$locale = $g2ic_options['language'];
}
else {
	$locale = 'en';
}

// gettext setup
require_once('gettext.inc');
T_setlocale(LC_ALL, $locale);

// Set the text domain as 'default'
T_bindtextdomain('default', 'langs');
T_bind_textdomain_codeset('default', 'UTF-8');
T_textdomain('default');
?>
