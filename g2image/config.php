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
$g2ic_options = array();

$g2ic_in_wordpress = FALSE;
// WORDPRESS USERS STOP HERE!!!
// WORDPRESS USERS STOP HERE!!!
// WORDPRESS USERS STOP HERE!!!
// NOTE: WordPress users should change all of the remaining settings using the
// "G2Image Popup Options" admin panel in WordPress.

// If $g2ic_use_full_path is set to FALSE (default setting - in EMBEDDED MODE
// OPERATIONS at the bottom of this file), then $g2ic_gallery2_path is
// the path from your web root directory to your Gallery2 directory.
// Example: If your Gallery2 homepage is www.domain.com/gallery2/main.php,
// then g2ic_gallery2_path is "gallery2/".
// Make sure you include the trailing forward slash.
//
// If $g2ic_use_full_path is set to TRUE, then $g2ic_gallery2_path is
// the full directory path to your Gallery2 directory.
// Example: /usr/username/public_html/gallery2/
// Make sure you include the trailing forward slash.

$g2ic_gallery2_path = "gallery2/";

// Set the language for the main g2image popup window.
// There must be a corresponding xx.mo file in the g2image/langs/ directory.
// If there is not a corresponding xx.mo file, English will be used.

$g2ic_language = 'en';

// Change this for more/fewer images per page.

$g2ic_images_per_page = 15;

// This sets the default view.  If set to TRUE, titles, summaries, and
// descriptions will be displayed.  If set to FALSE, only the thumbnails will
// be displayed.

$g2ic_display_filenames = FALSE;

// This sets the default alignment option.  Valid options are  'none',
// 'g2image_normal', 'g2image_float_left', 'g2image_float_right',
// 'g2image_centered', and one of the class names entered in the custom classes
// below.  Using 'none' will result in inserting an img tag with no class
// attribute.  See README.TXT for implementing CSS to support the g2image
// classes necessary for this option.

$g2ic_default_alignment = 'none';

// You can define custom class names for your img tag.  If these are set to
// anything other than 'not_used', they will be available under the
// alignment/class selector.  You can make it the default class by entering it
// in $g2ic_default_alignment above.

$g2ic_custom_class_1 = 'not_used';
$g2ic_custom_class_2 = 'not_used';
$g2ic_custom_class_3 = 'not_used';
$g2ic_custom_class_4 = 'not_used';

// This sets the default URL for the Custom URL option.

$g2ic_custom_url = 'http://';

// Change this to determine where the alignment class will be inserted.
// Valid options are 'img' to have it inserted as <img class=...> or
// 'div' to have it inserted as <div class=...><img ...>.
// If you choose 'div', you will have to manually delete any <div> tags manually
// after deleting images from the TinyMCE window.
// This setting will not affect <wpg2> tags, which are always wrapped with a
// <div> tag, if using g2image alignment classes for the <wpg2> tags.

$g2ic_class_mode = 'img';

// Change this to change the default "How to Insert" option.  Valid options are
// 'thumbnail_image', 'thumbnail_album', 'thumbnail_custom_url', 'thumbnail_only',
// 'link_image', 'link_album', 'drupal_g2_filter', 'thumbnail_lightbox',
// 'fullsize_image', and 'fullsize_only'.

$g2ic_default_album_action = 'flash_slideshow_album';
$g2ic_default_image_action = 'thumbnail_image';

// Change this to change the default sort order.  Valid options are 'title_asc',
// 'title_desc', 'orig_time_desc' (origination time, newest first),
// 'orig_time_asc' (origination time, oldest first), 'mtime_desc' (modification
// time, newest first), and 'mtime_asc' (modification time, oldest first).

$g2ic_sortby = 'title_asc';

// EMBEDDED MODE OPERATIONS
// This section applies to embedded mode operations, other than WPG2.
// If you have embedded your Gallery2 in another application (Drupal, Joomla
// etc.), then you'll need to configure the following info to get G2Image to
// create correctly fomatted links for your application.
//
// WPG2 users are already covered by communications between G2Image and
// WPG2, so you do not need to set these parameters.
//
// The key for users of other platforms is to make sure that your settings here
// match the embedded settings in your platform.  This will result in img URLs
// that will work well in your embedded application.

// Set $g2ic_embedded_mode to TRUE to enable embedded mode operations.

$g2ic_embedded_mode = FALSE;

// If your Gallery2 main.php is in a different subdomain than G2Image,
// you must set $g2ic_use_full_path to TRUE, and enter the full directory path
// to Gallery2 in $g2ic_gallery2_path.  However, G2Image by itself
// does not support Gallery2 being in a different subdomain.  You must use
// another program with Gallery2 in embedded mode (like Drupal or Joomla)
// with Gallery2 URL rewrite rules properly configured to redirect the resulting
// link to Gallery2 in the other subdomain.  That is why this setting is located in
// the EMBEDDED MODE OPERATIONS section.
//
// If $g2ic_embedded_mode is set to FALSE, $g2ic_use_full_path must also be
// set to false and the proper path must be entered into $g2ic_gallery2_path.

$g2ic_use_full_path = FALSE;

// $g2ic_gallery2_uri is the URL of your Gallery2 main page.
// For example, if your Gallery2 main page is
// http://www.domain.com/gallery2/main.php
// then
// $g2ic_gallery2_uri = 'http://www.domain.com/gallery2/main.php';

$g2ic_gallery2_uri = 'http://www.domain.com/gallery2/main.php';

// $g2ic_embed_uri is the URL of your embedded page.
// For example, if your embedded page is
// http://www.domain.com/wordpress/wp-gallery2.php
// then
// $g2ic_embed_uri = 'http://www.domain.com/wordpress/wp-gallery2.php';

$g2ic_embed_uri = 'http://www.domain.com/wordpress/wp-gallery2.php';

// DRUPAL GALLERY2 FILTER OPERATIONS
// If you are using Drupal and have the Gallery2 Filter module activated, you
// can insert a G2 Filter in the simplest format: [G2:itemid].  If you want to
// make this the default action, set $g2ic_default_image_action to 'drupal_g2_filter'.
// Set this to TRUE to enable.

$g2ic_drupal_g2_filter = FALSE;

// Set the Drupal G2 Filter prefix here

$g2ic_drupal_g2_filter_prefix = 'G2';

// BBCODE OPERATIONS
// Add documentation...
$g2ic_bbcode_enabled = FALSE;

$g2ic_bbcode_only = FALSE;

// Default Flash Slideshow configuration values
// See http://www.flashyourweb.com/dokukiki/doku.php/xmlmini:2.0 for details on options

// Height and width of flash player window in pixels
$g2ic_options['flash_slideshow_height'] = 150;
$g2ic_options['flash_slideshow_width'] = 150;

// Use thumbnails (false) or fullsize images (true)
// The fullsize image will be the next larger of available resizes or the original
$g2ic_options['flash_slideshow_use_full'] = 'true';

// Delay in seconds
$g2ic_options['flash_slideshow_delay'] = 3;

// true or false
$g2ic_options['flash_slideshow_shuffle'] = 'false';

// true or false
$g2ic_options['flash_slideshow_drop_shadow'] = 'false';

// Case sensitive: Blinds Fade Fly Iris Photo PixelDissolve Rotate Squeeze Wipe Zoom Random
$g2ic_options['flash_slideshow_transition_in'] = 'Fade';
$g2ic_options['flash_slideshow_transition_out'] = 'Fade';

// Whether to have a link over the photos: true or false
$g2ic_options['flash_slideshow_no_link'] = 'false';

// false or a URL.  If set, replaces ALL of the links with this one URL
$g2ic_options['flash_slideshow_alt_link'] = 'false';

// Target for links: _parent _blank _self _top or your own custom target for a named frame
$g2ic_options['flash_slideshow_link_target'] = '_parent';

// false, top, or bottom
$g2ic_options['flash_slideshow_show_title'] = 'false';
$g2ic_options['flash_slideshow_title_color'] = 'FFFFFF';
$g2ic_options['flash_slideshow_title_bg_color'] = '333333';

// none starMask roundedMask circleMask
$g2ic_options['flash_slideshow_masks'] = 'none';

// Site information text for right-click menu.  If empty, nothing shows on that menu.
// Example 'Copyright 2007 4WiseGuys.com'
$g2ic_options['flash_slideshow_site_info_text'] = '';

// Site information URL for right-click menu.  If not empty, this will be the link behind
// the site info text in the right-click menu.
// Example 'http://www.4wiseguys.com'
$g2ic_options['flash_slideshow_site_info_url'] = '';
?>