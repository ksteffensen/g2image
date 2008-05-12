<?php
//  Configuration File for Gallery 2 Image Chooser for TinyMCE
//  Version 1.3.5
//  By Kirk Steffensen - http://g2image.steffensenfamily.com/
//  Released under the GPL version 2.
//  A copy of the license is in the root folder of this plugin.

// Change the extensions inside the parentheses to define "valid" image
// extensions.

$g2ic_image_ext_regex = '@(jpg|jpeg|png|gif|bmp|svg)$@i';

// In rare cases (when using a server alias where $_SERVER['PHP_SELF'] gives
// the wrong URL for g2image.php), the Gallery2 path must include the full
// directory path to Gallery2.  If this is the case, set $g2ic_use_full_path
// to TRUE and enter the full directory path in $g2ic_gallery2_full_path.
// Example: /usr/home/username/public_html/gallery2/

$g2ic_use_full_path = FALSE;

$g2ic_gallery2_full_path = '/usr/home/username/public_html/gallery2/';

// WORDPRESS USERS STOP HERE!!!
// WORDPRESS USERS STOP HERE!!!
// WORDPRESS USERS STOP HERE!!!
// NOTE: WordPress users should change all of the remaining settings using the
// "G2Image Popup Options" admin panel in WordPress.

// The path from your web root directory to your Gallery2 directory.
// Example: If your Gallery2 homepage is www.domain.com/gallery2/main.php,
// then g2ic_gallery2_path is "gallery2/".
// Make sure you include the trailing forward slash.

$g2ic_gallery2_path = "gallery2/";

// Set the language for the main g2image popup window.
// There must be a corresponding xx.php file in the g2image/langs/ directory.
// If there is not a corresponding xx.php file, en.php will be loaded.

$g2ic_language = 'en';

// Change this for more/fewer images per page

$g2ic_images_per_page = 15;

// This sets the default view.  If set to TRUE, titles and filenames will be
// displayed.  If set to FALSE, the thumbnails will be displayed in a table
// style.

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

// Change this to change the default settings for the "Results of clicking on an
// image:" option.  Valid settings are 'one_click_insert' and
// 'show_advanced_options'.

$g2ic_click_mode = 'one_click_insert';

// Change this to determine if the user will have the option to determine
// the settings for the the "Results of clicking on an image:" option.  Set this
// to FALSE to prevent users from being able to change the settings.

$g2ic_click_mode_variable = TRUE;

// Change this to change the default "How to Insert" option.  Valid options are
// 'thumbnail_image', 'thumbnail_album', 'thumbnail_custom_url', 'thumbnail_only',
// 'link_image', 'link_parent', and 'drupal_g2_filter'.

$g2ic_default_action = 'thumbnail_image';

// Change this to change the default sort order.  Valid options are 'title_asc',
// 'title_desc', 'name_asc', 'name_desc', 'mtime_desc' (modification time,
// newest first), and 'mtime_asc' (modification time, oldest first).

$g2ic_sortby = 'title_asc';

// EMBEDDED MODE OPERATIONS
// This section applies to embedded mode operations, other than WPG2.
// If you have embedded your Gallery2 in another application (Drupal, Joomla
// etc.), then you'll need to configure the following info to get G2Image to
// create correctly fomatted links for your application.
// WPG2 users are already covered by communications between G2Image and
// WPG2, so you do not need to set these parameters.
//
// The key for users of other platforms is to make sure that your settings here
// match the embedded settings in your platform.  This will result in img URLs
// that will work well in your embedded application.

// Set $g2ic_embedded_mode to TRUE to enable embedded mode operations.

$g2ic_embedded_mode = FALSE;

// $g2ic_embed_path is the path from your root web page to your embedded page.
// $g2ic_embed_uri is the name of your embedded page.
// For example, if your embedded page is
// http://www.domain.com/wordpress/wp-gallery2.php
// then
// $g2ic_embed_path = '/wordpress/';
// and
// $g2ic_embed_uri = 'wp-gallery2.php';

$g2ic_embed_path = '/wordpress/';

$g2ic_embed_uri = 'wp-gallery2.php';

// $g2ic_relative_gallery2_path is the RELATIVE path from your embedded path to
// your Gallery2 root page.
// The two dots and the slash at the front '../' indicate to move "back down the
// directory tree" one directory.  So if your embed page is
// http://www.domain.com/wordpress/wp-gallery2.php
// and your Gallery2 main page is
// http://www.domain.com/gallery2/main.php
// then the relative path is
// $g2ic_relative_gallery2_path = '../gallery2/';
// because you have to "walk back down the tree" one directory from /wordpress/
// to get to the root, and then back up the tree to "gallery2/" to get to main.php

$g2ic_relative_gallery2_path = '../gallery2/';

// DRUPAL GALLERY2 FILTER OPERATIONS
// If you are using Drupal and have the Gallery2 Filter module activated, you
// can insert a G2 Filter in the simplest format: [G2:itemid].  If you want to
// make this the default action, set $g2ic_default_action to 'drupal_g2_filter'.
// Set this to TRUE to enable.

$g2ic_drupal_g2_filter = FALSE;

// Set the Drupal G2 Filter prefix here

$g2ic_drupal_g2_filter_prefix = G2;

?>