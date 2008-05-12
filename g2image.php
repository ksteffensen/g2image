<?php
/*
    Gallery 2 Image Chooser
    Version 2.2.3 - Updated 27 AUG 2007
    Documentation: http://g2image.steffensenfamily.com/

    Author: Kirk Steffensen with inspiration, code snipets,
        and assistance as listed in CREDITS.HTML

    Released under the GPL version 2.
    A copy of the license is in the root folder of this plugin.

    See README.HTML for installation info.
    See CHANGELOG.HTML for a history of changes.
*/

$g2ic_version_text = '2.2.3';
$g2ic_version_array = array(2,2,3);

// ====( Require Configuration and Initialization Files )=

require_once('config.php');
require_once('init.php');
session_start();

// Get the root album

// Check for G2 Core API >= 7.5.  getDefaultAlbumId only available at 7.5 or above
if (GalleryUtilities::isCompatibleWithApi(array(7,5), GalleryCoreApi::getApiVersion())) {
	list($error, $g2ic_root_album) = GalleryCoreApi::getDefaultAlbumId();
}
// Otherwise use a Gallery2 2.1 method to get the root album
else {
	list($error, $g2ic_root_album) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.rootAlbum');
}

if(isset($_REQUEST['g2ic_tinymce'])){
	$g2ic_tinymce = $_REQUEST['g2ic_tinymce'];
	$_SESSION['g2ic_tinymce'] = $_REQUEST['g2ic_tinymce'];
}
else if (isset($_SESSION['g2ic_tinymce']))
	$g2ic_tinymce = $_SESSION['g2ic_tinymce'];

if(isset($_REQUEST['g2ic_form'])){
	$g2ic_form = $_REQUEST['g2ic_form'];
	$_SESSION['g2ic_form'] = $_REQUEST['g2ic_form'];
}
else if (isset($_SESSION['g2ic_form']))
	$g2ic_form = $_SESSION['g2ic_form'];

if(isset($_REQUEST['g2ic_field'])){
	$g2ic_field = $_REQUEST['g2ic_field'];
	$_SESSION['g2ic_field'] = $_REQUEST['g2ic_field'];
}
else if (isset($_SESSION['g2ic_field']))
	$g2ic_field = $_SESSION['g2ic_field'];

if(isset($_SESSION['g2ic_last_album_visited'])) {
	$g2ic_last_album = $_SESSION['g2ic_last_album_visited'];
}
else {
	$g2ic_last_album = $g2ic_root_album;
}

// ====( Main Code )======================================
g2ic_magic_quotes_remove($_REQUEST);

print g2ic_make_html_header();

$g2ic_current_album = g2ic_get_current_album();
$g2ic_current_page = g2ic_get_current_page();
list($g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page)
	= g2ic_get_display_options($g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page);
$g2ic_gallery_items = g2ic_get_gallery_items();
$g2ic_imginsert_radiobuttons = g2ic_get_imginsert_radiobuttons();

$g2ic_page_navigation = g2ic_make_html_page_navigation();

print g2ic_make_html_dir_menu();

list($g2ic_album_url, $g2ic_album_title) = g2ic_get_album_info($g2ic_current_album);

if ($g2ic_page_navigation['empty']) {
	if ($g2ic_wpg2_valid) print g2ic_make_html_wpg2_album_insert_button();
	if ($g2ic_drupal_g2_filter) print g2ic_make_html_drupal_album_insert_button();
	print_r($g2ic_page_navigation['empty']);
}
elseif ($g2ic_page_navigation['error']) print_r($g2ic_page_navigation['error']);
else {
	print g2ic_make_html_display_options();
	if ($g2ic_wpg2_valid) print g2ic_make_html_wpg2_album_insert_button();
	if ($g2ic_drupal_g2_filter) print g2ic_make_html_drupal_album_insert_button();
	print_r($g2ic_page_navigation['html']);
	if ($g2ic_click_mode_variable)
		print g2ic_make_html_click_options($g2ic_click_mode);
	print g2ic_make_html_image_navigation();
	print_r($g2ic_page_navigation['html']);
}

print g2ic_make_html_about($g2ic_version_text);

print "</body>\n\n";
print "</html>";

$_SESSION['g2ic_last_album_visited'] = $g2ic_current_album;

GalleryEmbed::done();

// ====( Functions )=======================================

//---------------------------------------------------------------------
//	Function:	g2ic_get_album_info
//	Parameters:	int $album_id
//	Returns:	array($album_url, $album_title)
//	Purpose:	Get the URL and title for a given album ID
//---------------------------------------------------------------------

function g2ic_get_album_info($album_id) {
	global $gallery;

	$urlGenerator =& $gallery->getUrlGenerator();

	list ($error,$items) = GalleryCoreApi::loadEntitiesById(array($album_id));
	if(!$error) {
		foreach ($items as $item) {
			$album_url = $urlGenerator->generateUrl(array('view' => 'core.ShowItem', 'itemId' => $item->getid()), array('forceServerRelativeUrl' => true));
			$album_title = $item->getTitle();
		}
	}
	else {
		print 'Error loading album entity';
	}

	return array($album_url, $album_title);
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_current_album
//	Parameters:	None
//	Returns:	string $album - relative path to current folder
//	Purpose:	If 'current_album' is set by GET, return a cleaned-up string.
//---------------------------------------------------------------------

function g2ic_get_current_album(){
	global $g2ic_last_album;

	// If GET or POST have 'rel_path' set, use it
	if(IsSet($_REQUEST['current_album'])){
		$album = $_REQUEST['current_album'];
	}

	// Else use $g2ic_last_album
	else {
		$album = $g2ic_last_album;
	}

	return $album;
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_current_page
//	Parameters:	None
//	Returns:	integer $page
//	Purpose:	If 'g2ic_page' is set by GET, return current page.
//---------------------------------------------------------------------

function g2ic_get_current_page(){
	if (isset($_REQUEST['g2ic_page']) and is_numeric($_REQUEST['g2ic_page'])) {
		$page = floor($_REQUEST['g2ic_page']);
	}
	else {
		$page = 1;
	}
	return $page;
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_display_options
//	Parameters:	$sortby, $display_filenames, $images_per_page
//	Returns:	array($sortby, $display_filenames, $images_per_page)
//	Purpose:	If the display options are set by GET, substitute them for the defaults.
//---------------------------------------------------------------------

function g2ic_get_display_options($sortby, $display_filenames, $images_per_page){

	if(IsSet($_REQUEST['sortby']))
		$sortby = $_REQUEST['sortby'];

	if(IsSet($_REQUEST['display']))
		if ($_REQUEST['display'] == 'filenames')
			$display_filenames = TRUE;

	if(IsSet($_REQUEST['images_per_page']))
		$images_per_page = $_REQUEST['images_per_page'];

	return array($sortby, $display_filenames, $images_per_page);
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_gallery_items
//	Parameters:	None
//	Returns:	$dirs, $files
//	Purpose: 	Return files with allowed extension from upload directory.
//			The filenames are matched against the allowed file extensions.
//---------------------------------------------------------------------

function g2ic_get_gallery_items() {
	GLOBAL $g2ic_current_album,$g2ic_sortby,$g2ic_lang;

	$gallery_items = array();
	$item_mod_times = array();
	$item_orig_times = array();
	$item_create_times = array();
	$item_titles = array();
	$item_ids = array();

	list($ret,$albums) = GalleryCoreApi::loadEntitiesById(array($g2ic_current_album));
	if(!$ret){
		foreach ($albums as $album) {
			list($ret, $data_item_ids) = GalleryCoreApi::fetchChildDataItemIds($album);
			foreach ($data_item_ids as $data_item_id) {
				$item_ids[] = $data_item_id;
				list($ret, $items) = GalleryCoreApi::loadEntitiesById(array($data_item_id));
				foreach ($items as $item) {
					$item_titles[] = $item->getTitle();
					$item_mod_times[] = $item->getModificationTimestamp( );
					$item_orig_times[] = $item->getOriginationTimestamp( );
					$item_create_times[] = $item->getOriginationTimestamp( );
				}
			}
/* For debug
			for($i=0; $i<count($item_ids); $i++) {
				print '$item_ids[' . $i . '] = ' . $item_ids[$i] . '<br />';
				print ' $item_titles[' . $i . '] = ' . $item_titles[$i] . '<br />';
				print ' $item_mod_times[' . $i . '] = ' . $item_mod_times[$i] . '<br />';
				print ' $item_orig_times[' . $i . '] = ' . $item_orig_times[$i] . '<br />';
				print ' $item_create_times[' . $i . '] = ' . $item_create_times[$i] . '<br />';
			}
*/
		}
	}

	// Sort directories and files
	$count_files = count($item_ids);

	if($count_files>0){
		switch ($g2ic_sortby) {
			case 'title_asc' :
				array_multisort($item_titles, $item_ids);
				break;
			case 'title_desc' :
				array_multisort($item_titles, SORT_DESC, $item_ids);
				break;
			case 'orig_time_asc' :
				array_multisort($item_orig_times, $item_titles, $item_ids);
				break;
			case 'orig_time_desc' :
				array_multisort($item_orig_times, SORT_DESC, $item_titles, $item_ids);
				break;
			case 'mtime_asc' :
				array_multisort($item_mod_times, $item_titles, $item_ids);
				break;
			case 'mtime_desc' :
				array_multisort($item_mod_times, SORT_DESC, $item_titles, $item_ids);
		}
		for($i=0; $i<$count_files; $i++) {
			$gallery_items[$i] = array('title'=>$item_titles[$i],'id'=>$item_ids[$i]);
		}
	}

	return $gallery_items;

}

//---------------------------------------------------------------------
//	Function:	g2ic_get_img_info
//	Parameters:	string $image_path_name
//	Returns:	array ($thumbnail_src,$thumbnail_width,$thumbnail_height,
//			$item_title,$image_url,$gallery_url)
//	Purpose:	Get info about an image from Gallery2 and parse out the
//			results into the infomation required to generate the HTML
//---------------------------------------------------------------------

function g2ic_get_img_info($img_id) {
	global $gallery, $g2ic_wpg2_valid,$g2ic_current_album,$g2ic_wpg2_embedpageurl;

	$urlGenerator =& $gallery->getUrlGenerator();

	list ($error,$items) = GalleryCoreApi::loadEntitiesById(array($img_id));
	if(!$error) {
		foreach ($items as $item) {
			$item_title = $item->getTitle();
			$item_description = $item->getDescription();
			$item_summary = $item->getSummary();
			$fullsize_img = $urlGenerator->generateUrl(array('view' => 'core.DownloadItem', 'itemId' => $item->getid()));
			list($error, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array($item->getid()));
			if(!$error) {
				foreach($thumbnails as $thumbnail) {
					$thumbnail_src = $urlGenerator->generateUrl(array('view' => 'core.DownloadItem', 'itemId' => $thumbnail->getid()));
					$image_url = $urlGenerator->generateUrl(array('view' => 'core.ShowItem', 'itemId' => $item->getid()), array('forceServerRelativeUrl' => true));
					$thumbnail_width = '';
					$thumbnail_height = '';
				}
			}
			else {
				print 'Error 2';
			}
		}
	}
	else {
		print 'Error 1';
	}

	if(empty($item_summary))
		$item_summary = $item_title;
	if(empty($item_description))
		$item_description = $item_summary;

	return array ($thumbnail_src,$thumbnail_width,$thumbnail_height,$item_title,$image_url,$fullsize_img,$item_description,$item_summary);
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_imginsert_radiobuttons
//	Parameters:	None
//	Returns:	array $imginsert_radiobuttons
//	Purpose:	Define the radio buttons for the "How to insert?" dialog
//---------------------------------------------------------------------

function g2ic_get_imginsert_radiobuttons(){
	GLOBAL $g2ic_wpg2_valid, $g2ic_default_action, $g2ic_lang, $g2ic_drupal_g2_filter;

	if ($g2ic_wpg2_valid) {
		$imginsert_radiobuttons = array(
			'wpg2_image' => array(
				'text'  => $g2ic_lang['wpg2_tag_image'] ),
			'thumbnail_image' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_image'] ),
			'thumbnail_album' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_album'] ),
			'thumbnail_lightbox' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_lightbox'] ),
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_only'] ),
			'fullsize_image' => array(
				'text'  => $g2ic_lang['wpg2_fullsize_image'] ),
			'fullsize_only' => array(
				'text'  => $g2ic_lang['wpg2_fullsize_only'] ),
			'link_image' => array(
				'text'  => $g2ic_lang['wpg2_link_image'] ),
			'link_album'  => array(
				'text'  => $g2ic_lang['wpg2_link_album'] ),
		);
	}
	else if($g2ic_drupal_g2_filter)	{
		$imginsert_radiobuttons = array(
			'drupal_g2_filter' => array(
				'text' => $g2ic_lang['drupal_g2_filter'] ),
			'thumbnail_image' => array(
				'text'  => $g2ic_lang['thumbnail_image'] ),
			'thumbnail_album' => array(
				'text'  => $g2ic_lang['thumbnail_album'] ),
			'thumbnail_lightbox' => array(
				'text'  => $g2ic_lang['thumbnail_lightbox'] ),
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['thumbnail_only'] ),
			'fullsize_image' => array(
				'text'  => $g2ic_lang['fullsize_image'] ),
			'fullsize_only' => array(
				'text'  => $g2ic_lang['fullsize_only'] ),
			'link_image' => array(
				'text'  => $g2ic_lang['link_image'] ),
			'link_album'  => array(
				'text'  => $g2ic_lang['link_album'] ),
		);
	}
	else {
		$imginsert_radiobuttons = array(
			'thumbnail_image' => array(
				'text'  => $g2ic_lang['thumbnail_image'] ),
			'thumbnail_album' => array(
				'text'  => $g2ic_lang['thumbnail_album'] ),
			'thumbnail_lightbox' => array(
				'text'  => $g2ic_lang['thumbnail_lightbox'] ),
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['thumbnail_only'] ),
			'fullsize_image' => array(
				'text'  => $g2ic_lang['fullsize_image'] ),
			'fullsize_only' => array(
				'text'  => $g2ic_lang['fullsize_only'] ),
			'link_image' => array(
				'text'  => $g2ic_lang['link_image'] ),
			'link_album'  => array(
				'text'  => $g2ic_lang['link_album'] ),
		);
	}

	$imginsert_radiobuttons[$g2ic_default_action]['selected'] = TRUE;
	return $imginsert_radiobuttons;
}

//---------------------------------------------------------------------
//	Function:	g2ic_magic_quotes_remove
//	Parameters:	array &$array POST or GET with magic quotes
//	Returns:	None
//	Purpose:	Remove magic Quotes
//---------------------------------------------------------------------

function g2ic_magic_quotes_remove(&$array) {
	if(!get_magic_quotes_gpc())
		return;
	foreach($array as $key => $elem) {
		if(is_array($elem))
			g2ic_magic_quotes_remove($elem);
		else
			$array[$key] = stripslashes($elem);
	}
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_about
//	Parameters:	$version
//	Returns:	string $html
//	Purpose:	Creates the "About" alert HTML
//---------------------------------------------------------------------

function g2ic_make_html_about($version){
	global $g2ic_lang;

	$html = '<div class="about_button">' . "\n"
	. '    <input type="button" onclick="alert(\'Gallery2 Image Chooser\nVersion ' . $version
	. '\nDocumentation: http://g2image.steffensenfamily.com/\')" '
	. 'value="' . $g2ic_lang['about'] . '"/>' . "\n"
	. '</div>' . "\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_alignment_select
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Creates the alignment selection HTML
//---------------------------------------------------------------------

function g2ic_make_html_alignment_select(){
	GLOBAL $g2ic_default_alignment, $g2ic_custom_class_1, $g2ic_custom_class_2, $g2ic_custom_class_3, $g2ic_custom_class_4, $g2ic_lang;

	// array for output
	$align_options = array('none' => array('text' => $g2ic_lang['img_none']),
		'g2image_normal' => array('text' => $g2ic_lang['img_normal']),
		'g2image_float_left' => array('text' => $g2ic_lang['img_float_left']),
		'g2image_float_right' => array('text' => $g2ic_lang['img_float_right']),
		'g2image_centered' => array('text' => $g2ic_lang['img_centered']));

	if ($g2ic_custom_class_1 != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_custom_class_1 => array('text' => $g2ic_custom_class_1)));
	}

	if ($g2ic_custom_class_2 != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_custom_class_2 => array('text' => $g2ic_custom_class_2)));
	}

	if ($g2ic_custom_class_3 != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_custom_class_3 => array('text' => $g2ic_custom_class_3)));
	}

	if ($g2ic_custom_class_4 != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_custom_class_4 => array('text' => $g2ic_custom_class_4)));
	}

	$align_options[$g2ic_default_alignment]['selected'] = TRUE;

	$html = g2ic_make_html_select('alignment',$align_options);

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_click_options
//	Parameters:	$click_mode
//	Returns:	string $html
//	Purpose:	Creates the click options HTML
//---------------------------------------------------------------------

function g2ic_make_html_click_options($click_mode){
	global $g2ic_lang;

	$html = "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['click_options'] . '</legend>' . "\n";

	$html .= '        <input type="radio" name="display" value="thumbnails"';
	if ($click_mode == 'one_click_insert')
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "        onclick='insertDefaults()'"
	.  '>' . $g2ic_lang['click_one_click'] . '</input>' . "\n";

	$html .= '        <input type="radio" name="display" value="filenames"';
	if ($click_mode == 'show_advanced_options')
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "        onclick='showAdvanced()'"
	.  '>' . $g2ic_lang['click_advanced'] . '</input>' . "\n";

	$html .= "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_dir_menu
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Creates the directory navigation HTML
//---------------------------------------------------------------------

function g2ic_make_html_dir_menu(){
	global $gallery, $g2ic_current_album, $g2ic_root_album, $g2ic_dirs_titles, $g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page,$g2ic_lang;

	// Album navigation
	$html = '<div>' . "\n"
	. '    <fieldset>' . "\n"
	. '        <legend>' . $g2ic_lang['current_album'] . '</legend>' . "\n"
	. '        <form name="album_navigation">' . "\n"
	. '            <select name="current_album" id="current_album" size="1" style="width: 500px;" onchange="document.forms[0].submit()">' . "\n";
	(!isset($_REQUEST['current_album'])) ? $selected = $g2ic_current_album : $selected = $_REQUEST['current_album'];
	$level = 0;
	$html .= get_album_hierarchy($g2ic_root_album, $level, $selected);
	$html .= '            </select>'
	. '        </form>' . "\n"
	. '</div>' . "\n\n";

	return $html;
}

/**
 * wp_gallery_remote_build_album_hierarchy
 * generates album hierarchy as <option> entites of selection box
 *
 * @param String/int $current_album id of current album
 * @param int $level hierarchy level; used to indent the entries of the album hierarchy
 * @param Array $album_list array of album information
 */
function get_album_hierarchy($current_album, $level, $selected) {
	global $gallery;

	list ($error,$items) = GalleryCoreApi::loadEntitiesById(array($current_album));
	if(!$error){
		foreach ($items as $item) {
			$album_title = $item->getTitle();
			if(empty($album_title)) {
				$album_title = $item->getPathComponent();
			}
		}
		($selected == $current_album) ? $is_selected = ' selected' : $is_selected = '';
		$html .= '                <option value="' . $current_album . '"' . $is_selected . '>' . get_album_indent($level) . $album_title . '</option>' . "\n";
	}

	list($ret, $sub_albums) = GalleryCoreApi::fetchAlbumTree($current_album,1);

	$albums = array_keys($sub_albums);

	if (count($albums) > 0) {
		$level++;
		foreach ($albums as $album) {
			      $html .= get_album_hierarchy($album, $level, $selected);
		}
	}

	return $html;
}

function get_album_indent($level) {
  $s = '';
  for ($i=1; $i<=$level; $i++) {
    $s .= '&nbsp;&nbsp;&nbsp;&nbsp;';
  }
  return $s;
}


//---------------------------------------------------------------------
//	Function:	g2ic_make_html_display_options
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Make the HTML for the Sort Selector
//---------------------------------------------------------------------

function g2ic_make_html_display_options(){
	global $g2ic_sortby,$g2ic_current_album,$g2ic_images_per_page,$g2ic_display_filenames,$g2ic_lang;

	$images_per_page_options = array(10,20,30,40,50,60);

	if (!in_array($g2ic_images_per_page,$images_per_page_options)){
		array_push($images_per_page_options,$g2ic_images_per_page);
		sort($images_per_page_options);
	}

	// array for output
	$sortoptions = array('title_asc' => array('text' => $g2ic_lang['title_a_to_z']),
		'title_desc' => array('text' => $g2ic_lang['title_z_to_a']),
		'orig_time_desc' => array('text' => $g2ic_lang['orignination_time_new']),
		'orig_time_asc' => array('text' => $g2ic_lang['orignination_time_old']),
		'mtime_desc' => array('text' => $g2ic_lang['last_modification_new']),
		'mtime_asc' => array('text' => $g2ic_lang['last_modification_old']));

	$sortoptions[$g2ic_sortby]['selected'] = TRUE;

	$html = "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['display_legend'] . '</legend>' . "\n"
	. '        <form action="' . $_SERVER['PHP_SELF'] . '" method="get">' . "\n"
	. '            <input type="hidden" name="current_album" value="' . $g2ic_current_album . '">' . "\n"
	. '            ' . $g2ic_lang['sorted_by'] . "\n"
	. g2ic_make_html_select('sortby',$sortoptions)
	. '            ' . $g2ic_lang['thumbnails_per_page'] . "\n"
	. '            <select name="images_per_page" onchange="document.forms[1].submit();">' . "\n";

	for($i=0;$i<count($images_per_page_options);$i++){
		$html .= '                <option value="' . $images_per_page_options[$i] . '"';
		if ($images_per_page_options[$i] == $g2ic_images_per_page)
			$html .= " selected='selected'";
		$html .= '>' . $images_per_page_options[$i] . "</option>\n";
	}

	$html .= '            </select><br />' . "\n";

	$html .= '            <input type="radio" name="display" value="thumbnails"';
	if (!$g2ic_display_filenames)
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "            onclick='showThumbnails()'"
	.  '>' . $g2ic_lang['thumbnails'] . '</input>' . "\n";

	$html .= '            <input type="radio" name="display" value="filenames"';
	if ($g2ic_display_filenames)
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "            onclick='showFileNames()'"
	.  '>' . $g2ic_lang['titles'] . '</input>' . "\n";

	$html .= "        </form>\n"
	. "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_drupal_album_insert_button
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Create the HTML for Drupal G2 Filter album insertion
//---------------------------------------------------------------------

function g2ic_make_html_drupal_album_insert_button(){

	GLOBAL $g2ic_current_album, $g2ic_drupal_g2_filter_prefix, $g2ic_lang, $g2ic_form, $g2ic_field, $g2ic_album_title;
	$html = '';

	// Create the form
	$album_id = $g2ic_current_album;
	$album_title = $g2ic_album_title;

	$html .= "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['drupal_album_legend'] . ' ' . $album_title . '</legend>' . "\n"
	. "        <form action='{$_SERVER['PHP_SELF']}?current_album={$g2ic_current_album}'\n"
	. "        method='post'\n"
	. "        id='drupal_album_form' >\n"

	// Alignment class select box
	. '            ' . $g2ic_lang['alignment_class'] . "\n"
	. g2ic_make_html_alignment_select()

	// "Insert" button
	. "            <input type='button'\n"
	. "            onclick='insertImage(this.parentNode)'\n"
	. '            value="' . $g2ic_lang['insert'] . '"' . "\n"
	. "            />\n"
	. "            <input type='hidden' name='imginsert' value='radio_fake' />\n"
	. "            <input type='hidden' name='radio_selected' value='drupal_g2_filter' />\n"
	. "            <input type='hidden' name='image_id' value='{$album_id}' />\n"
	. "            <input type='hidden' name='g2ic_wpg2_valid' value='0' />\n"
	. "            <input type='hidden' name='drupal_filter_prefix' value='{$g2ic_drupal_g2_filter_prefix}' />\n"
	. "            <input type='hidden' name='g2ic_form' value='{$g2ic_form}' />\n"
	. "            <input type='hidden' name='g2ic_field' value='{$g2ic_field}' />\n"
	. "            <input type='hidden' name='imgdesc' value='{$album_title}' />\n"
	. "        </form>\n"
	. "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_header
//	Parameters:	None
//	Returns:	string $html - HTML header
//	Purpose:	Make the text for the HTML header
//---------------------------------------------------------------------

function g2ic_make_html_header(){
	global $g2ic_lang, $g2ic_tinymce, $g2ic_wpg2_valid;
	$html = "<html xmlns='http://www.w3.org/1999/xhtml'>\n"
	. "<head>\n"
	. '    <title>' . $g2ic_lang['title'] . '</title>' . "\n"
	. "    <link rel='stylesheet' href='css/g2image.css' type='text/css' />\n"
	. "    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n";
	if($g2ic_tinymce && $g2ic_wpg2_valid) {
		$html .= "    <script language='javascript' type='text/javascript' src='../../../../wp-includes/js/tinymce/tiny_mce_popup.js'>\n"
		. "    </script>\n";
	}
	elseif($g2ic_tinymce && !$g2ic_wpg2_valid) {
		$html .= "    <script language='javascript' type='text/javascript' src='../../tiny_mce_popup.js'>\n"
		. "    </script>\n";
	}
	$html .= "    <script language='javascript' type='text/javascript' src='jscripts/functions.js'>\n"
	. "    </script>\n"
	. "</head>\n\n"
	. "<body id=\"g2image\">\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_image_navigation
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Create the HTML for image navigation/selection
//---------------------------------------------------------------------

function g2ic_make_html_image_navigation(){
	global $g2ic_gallery_items,$g2ic_current_page,$g2ic_images_per_page,
		$g2ic_imginsert_radiobuttons,$g2ic_current_album,$g2ic_sortby,
		$g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_item_title,$g2ic_image_url,$g2ic_album_url,$g2ic_custom_url,
		$g2ic_display_filenames, $g2ic_class_mode,$g2ic_lang,$g2ic_form,$g2ic_field,
		$g2ic_drupal_g2_filter_prefix,$g2ic_wpg2_valid,$g2ic_album_title,$g2ic_fullsize_img,
		$g2ic_item_description,$g2ic_item_summary;

	reset($g2ic_gallery_items);

	$html = '';

	foreach($g2ic_gallery_items as $key => $row) {
		$image_title = $row['title'];
		$image_id = $row['id'];

		if (!(($g2ic_current_page-1)*$g2ic_images_per_page <= $key)) // Haven't gotten there yet
			continue;
		else if (!($key < $g2ic_current_page*$g2ic_images_per_page))
			break; // Have gone past the range for this page

		$album_name = $g2ic_album_title;

		if ($g2ic_display_filenames)
			$html .= '<div class="bordered_imageblock">' . "\n";
		else
			$html .= '<div class="transparent_imageblock">' . "\n";

		$html .= g2ic_make_html_img($image_id) . "\n";

		if ($g2ic_display_filenames)
			$html .= '    <div class="displayed_title">' . "\n";
		else
			$html .= '    <div class="hidden_title">' . "\n";

		$html .= '        ' . $g2ic_lang['image_title'] . htmlspecialchars($image_title) . "<br />\n";

		$html .=  "    </div>\n\n";
		if ($g2ic_display_filenames)
			$html .=  "    <div class='active_placeholder'>\n";
		else
			$html .=  "    <div class='inactive_placeholder'>\n";
		$html .=  "    </div>\n\n";

		// Create the hidden input area (appears when image is clicked)
		$html .= "    <form action='{$_SERVER['PHP_SELF']}?current_album={$g2ic_current_album}'\n"
		. "        method='post'\n"
		. "        id='{$image_id}'\n"
		. "        class='hidden_form'>\n\n"

		// "How to insert:" radio buttons
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['how_insert'] . '</legend>' . "\n"
		. g2ic_make_html_radiobuttons('imginsert', $g2ic_imginsert_radiobuttons)

		// hidden fields
		. "            <input type='hidden' name='thumbnail_src' value='{$g2ic_g2thumbnail_src}' />\n"
		. "            <input type='hidden' name='fullsize_img' value='{$g2ic_fullsize_img}' />\n"
		. "            <input type='hidden' name='item_title' value='{$g2ic_item_title}' />\n"
		. "            <input type='hidden' name='item_summary' value='{$g2ic_item_summary}' />\n"
		. "            <input type='hidden' name='item_description' value='{$g2ic_item_description}' />\n"
		. "            <input type='hidden' name='image_url' value='{$g2ic_image_url}' />\n"
		. "            <input type='hidden' name='album_url' value='{$g2ic_album_url}' />\n"
		. "            <input type='hidden' name='image_id' value='{$image_id}' />\n"
		. "            <input type='hidden' name='album_name' value='{$album_name}' />\n"
		. "            <input type='hidden' name='thumbw' value='{$g2ic_thumbnail_width}' />\n"
		. "            <input type='hidden' name='thumbh' value='{$g2ic_thumbnail_height}' />\n"
		. "            <input type='hidden' name='sortby' value='{$g2ic_sortby}' />\n"
		. "            <input type='hidden' name='g2ic_page' value='{$g2ic_current_page}' />\n"
		. "            <input type='hidden' name='relpath' value='{$g2ic_current_album}' />\n"
		. "            <input type='hidden' name='class_mode' value='{$g2ic_class_mode}' />\n"
		. "            <input type='hidden' name='g2ic_form' value='{$g2ic_form}' />\n"
		. "            <input type='hidden' name='g2ic_field' value='{$g2ic_field}' />\n"
		. "            <input type='hidden' name='g2ic_wpg2_valid' value='{$g2ic_wpg2_valid}' />\n"
		. "            <input type='hidden' name='drupal_filter_prefix' value='{$g2ic_drupal_g2_filter_prefix}' />\n"
		. "        </fieldset>\n\n"

		// "Insert" button
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['insert_legend'] . '</legend>' . "\n"
		. "            <input type='button'\n"
		. "            onclick='insertImage(this.parentNode.parentNode)'\n"
		. '            value="' . $g2ic_lang['insert'] . '"' . "\n"
		. '            />' . "\n"
		. "        </fieldset>\n\n"

/* Pulled due to bug in imageblock
		// Image Size
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['image_size'] . '</legend>' . "\n"
		. g2ic_make_html_image_size()
		. "        </fieldset>\n\n"
*/

		// Alignment selection
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['alignment_legend'] . '</legend>' . "\n"
		. g2ic_make_html_alignment_select()
		. "        </fieldset>\n\n"

		// "Custom URL" text box
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['custom_url'] . '</legend>' . "\n"
		. '            <label for="custom_url">' . $g2ic_lang['url'] . '</label>' . "\n"
		. "            <input type='text' name='custom_url' size='84' maxlength='150' value='{$g2ic_custom_url}' />\n"
		. "        </fieldset>\n\n"

		// "Description" text box
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['description_legend'] . '</legend>' . "\n"
		. '            <label for="imgdesc">' . $g2ic_lang['description'] . '</label>' . "\n"
		. "            <input type='text' name='imgdesc' size='84' maxlength='150' value='{$g2ic_item_title}' />\n"
		. "        </fieldset>\n\n"
		. "    </form>\n"
		. "</div>\n\n";
	}
	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_img
//	Parameters:	$image - Filename
//	Returns:	string $html
//	Purpose:	Make <img ... /> html snippet for an image
//---------------------------------------------------------------------

function g2ic_make_html_img($data_item_id) {
	global $g2ic_current_album,$g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_item_title,$g2ic_image_url, $g2ic_click_mode,$g2ic_fullsize_img,$g2ic_item_description,$g2ic_item_summary;
	$html = '';

	// Determine $g2ic_img_html and $g2ic_link_html
	list ($g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_item_title,$g2ic_image_url,$g2ic_fullsize_img,$g2ic_item_description,$g2ic_item_summary) = g2ic_get_img_info($data_item_id);

	// ---- image code
	$html .= "    <img src='" . $g2ic_g2thumbnail_src. "' \n"
	. "    " . $g2ic_thumbnail_width." \n"
	. "    " . $g2ic_thumbnail_height." \n"
	. "    alt='".$g2ic_item_summary."' \n";

	if ($g2ic_click_mode == 'one_click_insert')
		$html .= "    onclick='insertImage(this.parentNode.getElementsByTagName(\"form\")[0])'\n";
	else
		$html .= "    onClick='showHideImageBlock(this.parentNode.getElementsByTagName(\"form\")[0])'\n";

	$html .= "    />\n";

	return $html;

}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_image_size
//	Parameters:	None
//	Returns:	string $html - HTML for image size selection
//	Purpose:	Generate the HTML for selecting the image size, if other than default
//---------------------------------------------------------------------

function g2ic_make_html_image_size() {
	global $g2ic_lang, $g2ic_image_size;

	$html = '            <label for="image_size">' . $g2ic_lang['image_size'] . '</label>' . "\n"
		. '            <input type="text" name="image_size" size="6" maxlength="4" value="" />' . "\n"
		. '            ' . $g2ic_lang['image_size_instructions'] . $g2ic_image_size . "\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_page_navigation
//	Parameters:	None
//	Returns:	string $html - HTML for page navigation
//	Purpose:	Generate the HTML for navigating over multiple pages
//---------------------------------------------------------------------

function g2ic_make_html_page_navigation() {
	global $g2ic_gallery_items,$g2ic_display_filenames,$g2ic_images_per_page,
	$g2ic_current_page,$g2ic_current_album,$g2ic_sortby,$g2ic_lang;

	//Check if the current directory is empty - print empty message and return if empty
	if (empty($g2ic_gallery_items)) {
		$html = '<p><strong>' . $g2ic_lang['empty_album'] . '</strong></p>' . "\n\n";
		$page_navigation['empty'] = $html;
		return $page_navigation;
	}

	// ---- navigation for pages of images
	$pages = ceil(count($g2ic_gallery_items)/$g2ic_images_per_page);
	if ($g2ic_current_page > $pages)
		$g2ic_current_page = $pages;

	$pagelinks = array();
	for ($count = 1; $count <= $pages; $count++) {
		if ($g2ic_current_page == $count) {
			$pagelinks[] = "        <strong>$count</strong>";
		}
		else {
			$html = "        <a href='{$_SERVER['PHP_SELF']}?g2ic_page={$count}"
			. "&sortby={$g2ic_sortby}&current_album={$g2ic_current_album}";
			if ($g2ic_display_filenames)
				$html .= "&display=filenames";
			else
				$html .= "&display=thumbnails";
			$html .= "&images_per_page={$g2ic_images_per_page}'>$count</a>";
			$pagelinks[] = $html;
		}
	}

	if (count($pagelinks) > 1) {
		$html = "<div>\n"
		. "    <fieldset>\n"
		. '        <legend>' . $g2ic_lang['page_navigation'] . '</legend>' . "\n"
		. '        ' . $g2ic_lang['page'] . ' '. "\n"
		. implode("     - \n", $pagelinks)
		. "\n"
		. "    </fieldset>\n"
		. "</div>\n\n";
	}
	else {
		$html = "";
	}

	$page_navigation['html'] = $html;
	return $page_navigation;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_radiobuttons
//	Parameters:	$name,$options
//	Returns:	string $html
//	Purpose:	Make html for set of radio buttons
//---------------------------------------------------------------------

function g2ic_make_html_radiobuttons($name,$options) {
	$elements = array();
	foreach ($options as $value => $option) {
		$html = "            <input type='radio' name='{$name}' value='{$value}'";
		if (isset($option['selected']) and $option['selected'])
			$html .= " checked='checked'";
		$html .= ">{$option['text']}</input>";
		$elements[] = $html;
	}

	$html = implode("<br />\n",$elements) . "\n";

	return $html;

}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_select
//	Parameters:	$name,$options
//	Returns:	string $html
//	Purpose:	Make html for select element
//	Notes:		The hash $options should contain values and Description:
//			array(
//				'value' => array(
//					text     => 'Description',
//					selected => (TRUE|FALSE),
//				),
//				...
//			)
//---------------------------------------------------------------------

function g2ic_make_html_select($name,$options) {
	$html = "            <select name='$name' id='$name' size='1'";
	if($name == 'sortby') {
		$html .= ' onchange="document.forms[1].submit();"';
	}
	$html .= '>' ."\n";
	foreach ($options as $value => $option) {
		$html .= "                <option value='{$value}'";
		if (isset($option['selected']) and $option['selected'])
			$html .= " selected='selected'";
		$html .= ">{$option['text']}</option>\n";
	}
	$html .= "            </select>\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_wpg2_album_insert_button
//	Parameters:	$album_path
//	Returns:	string $html
//	Purpose:	Create the HTML for image navigation/selection
//---------------------------------------------------------------------

function g2ic_make_html_wpg2_album_insert_button(){

	GLOBAL $g2ic_current_album, $g2ic_album_title, $g2ic_wpg2_valid, $g2ic_lang, $g2ic_form, $g2ic_field, $g2ic_item_summary;
	$html = '';

	// Determine $g2ic_img_html and $g2ic_link_html
	list ($thumbnail_src,$thumbnail_width,$thumbnail_height,
		$item_title,$image_url,$fullsize_img,$item_description,$item_summary) = g2ic_get_img_info($g2ic_current_album);

	// Create the form
	$html .= "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['wpg2_album_legend'] . ' ' . $g2ic_album_title . '</legend>' . "\n"
	. "        <form action='{$_SERVER['PHP_SELF']}?current_album={$g2ic_current_album}'\n"
	. "        method='post'\n"
	. "        id='wpg2_album_form' >\n"

// Image Size box - Pulled Due to Imageblock Bug
    . '<input type="hidden" name="image_size" size="6" maxlength="4" value="" />'
//	. g2ic_make_html_image_size()
//	. '        <br />'

	// Alignment class select box
	. '            ' . $g2ic_lang['alignment_class'] . "\n"
	. g2ic_make_html_alignment_select()

	// "Insert" button
	. "            <input type='button'\n"
	. "            onclick='insertImage(this.parentNode)'\n"
	. '            value="' . $g2ic_lang['insert'] . '"' . "\n"
	. "            />\n"
	. "            <input type='hidden' name='imginsert' value='radio_fake' />\n"
	. "            <input type='hidden' name='radio_selected' value='wpg2_album' />\n"
	. "            <input type='hidden' name='imgdesc' value='{$g2ic_current_album}' />\n"
	. "            <input type='hidden' name='item_summary' value='{$g2ic_item_summary}' />\n"
	. "            <input type='hidden' name='thumbnail_src' value='{$thumbnail_src}' />\n"
	. "            <input type='hidden' name='thumbw' value='{$thumbnail_width}' />\n"
	. "            <input type='hidden' name='thumbh' value='{$thumbnail_height}' />\n"
	. "            <input type='hidden' name='g2ic_wpg2_valid' value='{$g2ic_wpg2_valid}' />\n"
	. "            <input type='hidden' name='g2ic_form' value='{$g2ic_form}' />\n"
	. "            <input type='hidden' name='g2ic_field' value='{$g2ic_field}' />\n"
	. "        </form>\n"
	. "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}
?>
