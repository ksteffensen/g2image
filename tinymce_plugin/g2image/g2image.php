<?php
//  Gallery 2 Image Chooser for TinyMCE
//  Version 1.3.5
//  By Kirk Steffensen - http://g2image.steffensenfamily.com/
//  Released under the GPL version 2.
//  A copy of the license is in the root folder of this plugin.
//  See README.HTML for installation info.
//  See CHANGELOG.HTML for a history of changes.
//  See CREDITS.HTML for inspiration, code, and assistance credits.

$g2ic_version_text = '1.3.5';
$g2ic_version_array = array(1,35);

// ====( Require Configuration and Initialization Files )=

session_start();
error_reporting(E_ALL^ E_NOTICE);
require_once('config.php');
require_once('init.php');

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

// ====( Main Code )======================================

$g2ic_root_album_path = $gallery->getConfig('data.gallery.albums');
g2ic_magic_quotes_remove($_REQUEST);

print g2ic_make_html_header();

$g2ic_rel_path = g2ic_get_rel_path();
$g2ic_current_page = g2ic_get_current_page();
list($g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page)
	= g2ic_get_display_options($g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page);
list($g2ic_dirs_titles,$g2ic_image_files) = g2ic_get_gallery_dirs_and_files();
$g2ic_imginsert_radiobuttons = g2ic_get_imginsert_radiobuttons();

$g2ic_page_navigation = g2ic_make_html_page_navigation();

print g2ic_make_html_dir_menu();

$g2ic_album_url = g2ic_get_album_info($g2ic_rel_path);
if ($g2ic_wpg2_valid) print g2ic_make_html_wpg2_album_insert_button();
if ($g2ic_drupal_g2_filter) print g2ic_make_html_drupal_album_insert_button();

if ($g2ic_page_navigation['empty']) print_r($g2ic_page_navigation['empty']);
elseif ($g2ic_page_navigation['error']) print_r($g2ic_page_navigation['error']);
else {
	print g2ic_make_html_display_options();
	print_r($g2ic_page_navigation['html']);
	if ($g2ic_click_mode_variable)
		print g2ic_make_html_click_options($g2ic_click_mode);
	print g2ic_make_html_image_navigation();
	print_r($g2ic_page_navigation['html']);
}

print g2ic_make_html_about($g2ic_version_text);

print "</body>\n\n";
print "</html>";

$_SESSION['g2ic_last_album_visited'] = $g2ic_rel_path;

GalleryEmbed::done();

// ====( Functions )=======================================

//---------------------------------------------------------------------
//	Function:	g2ic_get_album_info
//	Parameters:	string $album_path_name
//	Returns:	string $album_url
//	Purpose:	Get info about an album from Gallery2 and parse out the
//			album URL
//---------------------------------------------------------------------

function g2ic_get_album_info($album_path_name) {
	global $g2ic_wpg2_valid;

	$href = '';
	// Strip off leading slash
	//$album_path_name = substr_replace($album_path_name,'',0,1);

	// Strip out %20 and replace with space
	$album_path_name = str_replace ("%20", " ", $album_path_name);

	$gallery_block_html = g2ic_imagebypathblock($album_path_name);

	// Parse out the results
	preg_match('/href="[^"]*"/',$gallery_block_html,$href);
	$album_url = preg_replace('/href="/','',$href[0]);
	$album_url = preg_replace('/"/','',$album_url);
	return $album_url;
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
//	Function:	g2ic_get_g2_id_title_by_path
//	Parameters:	string $path
//	Returns:	array ($g2itemid, $g2item_title)
//	Purpose:	Get an item's Gallery2 Title by path.
//			Die on errors.
//---------------------------------------------------------------------

function g2ic_get_g2_id_title_by_path($path){

	global $g2ic_lang;

	list ($ret, $g2itemid) = GalleryCoreAPI::fetchItemIdByPath($path);

	if (!$ret->isError){
		list ($ret, $item) = GalleryCoreApi::loadEntitiesById($g2itemid);
		if (!$ret->isError) {
			$g2item_title = $item->getTitle() . "\n";
		}
		else {
			print $path . $g2ic_lang['g2_entities_by_id_error'];
			print $ret->getAsHtml() . "\n";
			print "</body>\n\n";
			print "</html>";
			die;
		}
	}
	else {
		$g2itemid = '***INVALID***';
		$g2item_title = '***INVALID***';
	}

	return array($g2itemid, $g2item_title);
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_gallery_dirs_and_files
//	Parameters:	None
//	Returns:	$dirs, $files
//	Purpose: 	Return files with allowed extension from upload directory.
//			The filenames are matched against the allowed file extensions.
//---------------------------------------------------------------------

function g2ic_get_gallery_dirs_and_files() {
	GLOBAL $g2ic_rel_path,$g2ic_root_album_path,$g2ic_image_ext_regex,$g2ic_sortby,$g2ic_lang;

	$image_files = array();
	$dirs = array();
	$titles = array();
	$ids = array();
	$current_dir = '';
	$file = '';
	$title = '';
	$name = '';
	$file_on_disk = '';
	$filenames = array();
	$file_times = array();
	$file_titles = array();
	$file_ids = array();
	$dirs_titles = array();

	// open current directory
	if (FALSE === ($current_dir = opendir($g2ic_root_album_path.$g2ic_rel_path))) {
		print '<p>' . $g2ic_lang['g2_directory_error'] . '</p>';
		return FALSE;
	}

	// Get info on valid directories and files
	while (FALSE != ($file = readdir($current_dir))) {
		$file_on_disk = $g2ic_root_album_path.$g2ic_rel_path."$file";

		//Reads the DirNames for navigation
		if(is_dir($file_on_disk)){
			if($file != '.' && $file != '..') {
				list($id, $title) = g2ic_get_g2_id_title_by_path($g2ic_rel_path . $file);
				if ($title != '***INVALID***') {
					$dirs[] = $file;
					$titles[] = $title;
				}
			}
		}

		if (is_file($file_on_disk) and preg_match($g2ic_image_ext_regex , $file)) {
			$name = basename($file_on_disk);
			list($id, $title) = g2ic_get_g2_id_title_by_path($g2ic_rel_path . $name);
			if ($title != '***INVALID***') {
				$filenames[] = $name;
				$file_titles[] = $title;
				$file_ids[] = $id;
				if (FALSE !== ($temp = @filemtime($file_on_disk)))
					$file_times[] = $temp;
			}
		}
	}

	closedir($current_dir);

	// Sort directories and files
	$count_dirs = count($dirs);
	$count_files = count($filenames);

	if($count_dirs>0){
		array_multisort($titles,$dirs);

		for($i=0; $i<$count_dirs; $i++) {
			$dirs_titles[$i] = array('directory'=>$dirs[$i],'title'=>$titles[$i]);
		}
	}

	if($count_files>0){
		switch ($g2ic_sortby) {
			case 'title_asc' :
				array_multisort($file_titles, $filenames, $file_times, $file_ids);
				break;
			case 'title_desc' :
				array_multisort($file_titles, SORT_DESC, $filenames, SORT_DESC, $file_times, $file_ids);
				break;
			case 'name_asc' :
				array_multisort($filenames, $file_titles, $file_times, $file_ids);
				break;
			case 'name_desc' :
				array_multisort($filenames, SORT_DESC, $file_titles, SORT_DESC, $file_times, $file_ids);
				break;
			case 'mtime_asc' :
				array_multisort($file_times, $file_titles, $filenames, $file_ids);
				break;
			case 'mtime_desc' :
				array_multisort($file_times, SORT_DESC, $file_titles, $filenames, $file_ids);
		}
		for($i=0; $i<$count_files; $i++) {
			$image_files[$i] = array('filename'=>$filenames[$i],'title'=>$file_titles[$i],'id'=>$file_ids[$i]);
		}
	}

	return array($dirs_titles,$image_files);

}

//---------------------------------------------------------------------
//	Function:	g2ic_get_img_info
//	Parameters:	string $image_path_name
//	Returns:	array ($thumbnail_src,$thumbnail_width,$thumbnail_height,
//			$thumbnail_alt_text,$image_url,$gallery_url)
//	Purpose:	Get info about an image from Gallery2 and parse out the
//			results into the infomation required to generate the HTML
//---------------------------------------------------------------------

function g2ic_get_img_info($img_path_name) {
	global $g2ic_wpg2_valid,$g2ic_rel_path;

	$src = '';
	$href = '';
	$width = '';
	$height = '';
	$alt = '';

	// Strip leading slash unless this is the root directory
	if (strlen($img_path_name) > 1)
		$img_path_name = substr_replace($img_path_name,'',0,1);

	// Strip out %20 and replace with space
	$img_path_name = str_replace ("%20", " ", $img_path_name);

	$gallery_block_html = g2ic_imagebypathblock($img_path_name);

	// Parse out the results
	preg_match('/src="[^"]*"/',$gallery_block_html,$src);
	preg_match('/href="[^"]*"/',$gallery_block_html,$href);
	preg_match('/width="[^"]*"/',$gallery_block_html,$width);
	preg_match('/height="[^"]*"/',$gallery_block_html,$height);
	preg_match('/alt="[^"]*"/',$gallery_block_html,$alt);
	$thumbnail_src = preg_replace('/src="/','',$src[0]);
	$thumbnail_src = preg_replace('/"/','',$thumbnail_src);
	$thumbnail_width = $width[0];
	$thumbnail_height = $height[0];
	$thumbnail_alt_text = preg_replace('/alt="/','',$alt[0]);
	$thumbnail_alt_text = preg_replace('/"/','',$thumbnail_alt_text );
	$image_url = preg_replace('/href="/','',$href[0]);
	$image_url = preg_replace('/"/','',$image_url);
	return array ($thumbnail_src,$thumbnail_width,$thumbnail_height,$thumbnail_alt_text,$image_url);
}

//---------------------------------------------------------------------
//	Function:	g2ic_get_imginsert_radiobuttons
//	Parameters:	None
//	Returns:	array $imginsert_radiobuttons
//	Purpose:	Define the radio buttons for the "How to insert?" dialog
//---------------------------------------------------------------------

function g2ic_get_imginsert_radiobuttons(){
	GLOBAL $g2ic_wpg2_valid, $g2ic_wpg2id_tags, $g2ic_default_action,$g2ic_lang,$g2ic_drupal_g2_filter;

	if ($g2ic_wpg2_valid) {
		if($g2ic_wpg2id_tags)
			$wpg2_option = 'wpg2id_image';
		else
			$wpg2_option = 'wpg2_image';
		$imginsert_radiobuttons = array(
			$wpg2_option => array(
				'text'  => $g2ic_lang['wpg2_tag_image'] ),
			'thumbnail_image' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_image'] ),
			'thumbnail_album' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_album'] ),
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['wpg2_thumbnail_only'] ),
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
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['thumbnail_only'] ),
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
			'thumbnail_custom_url' => array(
				'text'  => $g2ic_lang['thumbnail_custom_url'] ),
			'thumbnail_only' => array(
				'text'  => $g2ic_lang['thumbnail_only'] ),
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
//	Function:	g2ic_get_rel_path
//	Parameters:	None
//	Returns:	string $path - relative path to current folder
//	Purpose:	If 'rel_path' is set by GET, return a cleaned-up string.
//---------------------------------------------------------------------

function g2ic_get_rel_path(){
	global $g2ic_last_album, $g2ic_root_album_path;

	// If GET or POST have 'rel_path' set, use it
	if(IsSet($_REQUEST['rel_path'])){
		$path = str_replace ('..', '',$_REQUEST['rel_path']);
	}

	// Else if the $g2ic_last_album_visited is invalid, use the root '/'
	elseif (FALSE === (is_dir($g2ic_root_album_path.$g2ic_last_album))) {
		$path = '/';
	}

	// Else use $g2ic_last_album
	else {
		$path = $g2ic_last_album;
	}

	return $path;
}

//---------------------------------------------------------------------
//	Function:	g2ic_imageblock
//	Parameters:	$g2inputid, $g2itemsize=null
//	Returns:	string $img
// 	Purpose:	Imageblock Function for Blog Images.
//			Called by g2ic_imagebypathblock.
//			This is a native embedded function for use without
//			WordPress and WPG2.  (If WPG2 is present, the plugin
//			uses the WPG2 g2_imageblock function instead.)
//---------------------------------------------------------------------

function g2ic_imageblock( $g2itemid) {

	global $g2ic_lang;

	// Build the Image Block
	$blockoptions['blocks'] = 'specificItem';
	$blockoptions['show'] = 'none';
	$blockoptions['itemId'] = $g2itemid;

	list ($ret, $itemimg, $headimg) = GalleryEmbed::getImageBlock($blockoptions);

	if (!$ret->isError){
		$img = $itemimg;

		// Compact the output
		$img = preg_replace("/(\s+)?(\<.+\>)(\s+)?/", "$2", $img);

		GalleryEmbed::done();
	}
	else {
		print $g2itemid . '  ' . $g2ic_lang['g2_id_not_found_error'];
		print $ret->getAsHtml() . "\n";
		print "</body>\n\n";
		print "</html>";
		die; // Die if file not found.  Should not be able to get to here.
	}

	return $img;
}

//---------------------------------------------------------------------
//	Function:	g2ic_imagebypathblock
//	Parameters:	$g2inputpath
//	Returns:	$img
// 	Purpose:	Include image from gallery based on path
//			This is a native embedded function for use without
//			WordPress and WPG2.  (If WPG2 is present, the plugin
//			uses the WPG2 g2_imagebypathblock function instead.)
//---------------------------------------------------------------------

function g2ic_imagebypathblock( $g2itempath ) {
	global $g2ic_embedded_mode, $g2ic_lang;

	// Make Sure Item Path does not contain a + as it should instead be a space
	$g2itempath = str_replace ("+", " ", $g2itempath);

	// Get the Image
	list ($ret, $g2itemid) = GalleryCoreAPI::fetchItemIdByPath($g2itempath);

	if (!$ret->isError){
		$img = g2ic_imageblock($g2itemid);
	}
	else {
		print $g2ic_lang['g2_id_by_path_error'] . $g2itempath . '<br />';
		print "</body>\n\n";
		print "</html>";
		$img = $g2ic_lang['invalid_image'];
	}
	return $img;
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
	. '\nAuthor: Kirk Steffensen\nDocumentation: http://g2image.steffensenfamily.com/\')" '
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
	global $g2ic_rel_path, $g2ic_dirs_titles, $g2ic_sortby, $g2ic_display_filenames, $g2ic_images_per_page,$g2ic_lang;

	$html = "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['current_album'] . '</legend>' . "\n"
	. '        <a href="?rel_path=/&sortby=' . $g2ic_sortby;
	if ($g2ic_display_filenames)
		$html .= "&display=filenames";
	else
		$html .= "&display=thumbnails";
	list($root_id, $root_title) = g2ic_get_g2_id_title_by_path('/');
	$html .= '&images_per_page=' . $g2ic_images_per_page . '">' . $root_title . '</a>/' . "\n";

	$tok = strtok ($g2ic_rel_path,"/");
	$rel_path_tok = "/$tok/";
	while ($tok) {
		$html .= '        <a href="?rel_path=' . $rel_path_tok . '&sortby=' . $g2ic_sortby;
		if ($g2ic_display_filenames)
			$html .= "&display=filenames";
		else
			$html .= "&display=thumbnails";
		list($dir_id, $dir_title) = g2ic_get_g2_id_title_by_path($rel_path_tok);
		$html .= '&images_per_page=' . $g2ic_images_per_page . '">' . $dir_title . '</a>/' . "\n";
		$tok = strtok ("/");
		$rel_path_tok .= "{$tok}/";
	}

	$html.= "    </fieldset>\n";

	// Subdirectory navigation
	if ($g2ic_dirs_titles != null) {
		$html .= '    <fieldset>' . "\n"
		. '        <legend>' . $g2ic_lang['subalbums'] . '</legend>' . "\n"
		. '        <form name="subdirectory_navigation">' . "\n"
		. '            <select name="subalbums">' . "\n";

		foreach($g2ic_dirs_titles as $key => $row){
			$html .= '                <option value="?rel_path='
			. $g2ic_rel_path . $row['directory'] . '/&sortby=' . $g2ic_sortby;
			if ($g2ic_display_filenames)
				$html .= '&display=filenames';
			else
				$html .= '&display=thumbnails';
			$html .= '&images_per_page=' . $g2ic_images_per_page .  '">' . $row['title'] . '</option>' . "\n";
		}

		$html .=	"            </select>\n"
		. '            <input type="button" name="Submit" value=' . $g2ic_lang['go'] . "\n"
		. '            onClick="top.location.href = this.form.subalbums.options[this.form.subalbums.selectedIndex].value;' . "\n"
		. '            return false;">' . "\n"
		. '        </form>' . "\n"
		. '    </fieldset>' . "\n";
	}

	$html .= '</div>' . "\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_display_options
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Make the HTML for the Sort Selector
//---------------------------------------------------------------------

function g2ic_make_html_display_options(){
	global $g2ic_sortby,$g2ic_rel_path,$g2ic_images_per_page,$g2ic_display_filenames,$g2ic_lang;

	$images_per_page_options = array(10,20,30,40,50,60);

	if (!in_array($g2ic_images_per_page,$images_per_page_options)){
		array_push($images_per_page_options,$g2ic_images_per_page);
		sort($images_per_page_options);
	}

	// array for output
	$sortoptions = array('title_asc' => array('text' => $g2ic_lang['title_a_to_z']),
		'title_desc' => array('text' => $g2ic_lang['title_z_to_a']),
		'name_asc' => array('text' => $g2ic_lang['name_a_to_z']),
		'name_desc' => array('text' => $g2ic_lang['name_z_to_a']),
		'mtime_desc' => array('text' => $g2ic_lang['last_modification_new']),
		'mtime_asc' => array('text' => $g2ic_lang['last_modification_old']));

	$sortoptions[$g2ic_sortby]['selected'] = TRUE;

	$html = "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['display_legend'] . '</legend>' . "\n"
	. '        <form action="' . $_SERVER['PHP_SELF'] . '" method="get">' . "\n"
	. '            <input type="hidden" name="rel_path" value="' . $g2ic_rel_path . '">' . "\n"
	. '            ' . $g2ic_lang['sorted_by'] . "\n"
	. g2ic_make_html_select('sortby',$sortoptions)
	. '            ' . $g2ic_lang['thumbnails_per_page'] . "\n"
	. '            <select name="images_per_page">' . "\n";

	for($i=0;$i<count($images_per_page_options);$i++){
		$html .= '                <option value="' . $images_per_page_options[$i] . '"';
		if ($images_per_page_options[$i] == $g2ic_images_per_page)
			$html .= " selected='selected'";
		$html .= '>' . $images_per_page_options[$i] . "</option>\n";
	}

	$html .=	"            </select>\n"
	. '            <input type="submit" value="' . $g2ic_lang['redraw'] . '" /><br />' . "\n";

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
	.  '>' . $g2ic_lang['filenames'] . '</input>' . "\n";

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

	GLOBAL $g2ic_rel_path, $g2ic_drupal_g2_filter_prefix, $g2ic_lang, $g2ic_form, $g2ic_field;
	$html = '';

	// Create the form
	list($album_id, $album_title) = g2ic_get_g2_id_title_by_path($g2ic_rel_path);
	$html .= "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['drupal_album_legend'] . ' ' . $album_title . '</legend>' . "\n"
	. "        <form action='{$_SERVER['PHP_SELF']}?rel_path={$g2ic_rel_path}'\n"
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
	global $g2ic_lang, $g2ic_tinymce;
	$html = "<html xmlns='http://www.w3.org/1999/xhtml'>\n"
	. "<head>\n"
	. '    <title>' . $g2ic_lang['title'] . '</title>' . "\n"
	. "    <link rel='stylesheet' href='css/g2image.css' type='text/css' />\n"
	. "    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n";
	if($g2ic_tinymce) {
		$html .= "    <script language='javascript' type='text/javascript' src='../../tiny_mce_popup.js'>\n"
		. "    </script>\n";
	}
	$html .= "    <script language='javascript' type='text/javascript' src='jscripts/functions.js'>\n"
	. "    </script>\n"
	. "</head>\n\n"
	. "<body>\n\n";

	return $html;
}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_image_navigation
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Create the HTML for image navigation/selection
//---------------------------------------------------------------------

function g2ic_make_html_image_navigation(){
	global $g2ic_image_files,$g2ic_current_page,$g2ic_images_per_page,
		$g2ic_imginsert_radiobuttons,$g2ic_rel_path,$g2ic_sortby,
		$g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_thumbnail_alt_text,$g2ic_image_url,$g2ic_album_url,$g2ic_custom_url,
		$g2ic_display_filenames, $g2ic_class_mode,$g2ic_lang,$g2ic_form,$g2ic_field,$g2ic_drupal_g2_filter_prefix;

	reset($g2ic_image_files);

	$html = '';

	foreach($g2ic_image_files as $key => $row) {
		$image_filename = $row['filename'];
		$image_title = $row['title'];
		$image_id = $row['id'];

		if (!(($g2ic_current_page-1)*$g2ic_images_per_page <= $key)) // Haven't gotten there yet
			continue;
		else if (!($key < $g2ic_current_page*$g2ic_images_per_page))
			break; // Have gone past the range for this page

		$album_name = substr_replace($g2ic_rel_path,'',0,1);

		if ($g2ic_display_filenames)
			$html .= '<div class="bordered_imageblock">' . "\n";
		else
			$html .= '<div class="transparent_imageblock">' . "\n";

		$html .= g2ic_make_html_img($image_filename) . "\n";

		if ($g2ic_display_filenames)
			$html .= '    <div class="displayed_title">' . "\n";
		else
			$html .= '    <div class="hidden_title">' . "\n";

		$html .= '        ' . $g2ic_lang['image_title'] . htmlspecialchars($image_title) . "<br />\n";

		$html .= '        ' . $g2ic_lang['filename'] . htmlspecialchars($image_filename) . "\n";

		$html .=  "    </div>\n\n";
		if ($g2ic_display_filenames)
			$html .=  "    <div class='active_placeholder'>\n";
		else
			$html .=  "    <div class='inactive_placeholder'>\n";
		$html .=  "    </div>\n\n";

		$imgdesc = $g2ic_thumbnail_alt_text;

		// Create the hidden input area (appears when image is clicked)
		$html .= "    <form action='{$_SERVER['PHP_SELF']}?rel_path={$g2ic_rel_path}'\n"
		. "        method='post'\n"
		. "        id='{$image_filename}'\n"
		. "        class='hidden_form'>\n\n"

		// "How to insert:" radio buttons
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['how_insert'] . '</legend>' . "\n"
		. g2ic_make_html_radiobuttons('imginsert', $g2ic_imginsert_radiobuttons)

		// hidden fields
		. "            <input type='hidden' name='thumbnail_src' value='{$g2ic_g2thumbnail_src}' />\n"
		. "            <input type='hidden' name='image_url' value='{$g2ic_image_url}' />\n"
		. "            <input type='hidden' name='album_url' value='{$g2ic_album_url}' />\n"
		. "            <input type='hidden' name='image_name' value='{$image_filename}' />\n"
		. "            <input type='hidden' name='image_id' value='{$image_id}' />\n"
		. "            <input type='hidden' name='album_name' value='{$album_name}' />\n"
		. "            <input type='hidden' name='thumbw' value='{$g2ic_thumbnail_width}' />\n"
		. "            <input type='hidden' name='thumbh' value='{$g2ic_thumbnail_height}' />\n"
		. "            <input type='hidden' name='file' value='". rawurlencode($image_filename) ."' />\n"
		. "            <input type='hidden' name='sortby' value='{$g2ic_sortby}' />\n"
		. "            <input type='hidden' name='g2ic_page' value='{$g2ic_current_page}' />\n"
		. "            <input type='hidden' name='relpath' value='{$g2ic_rel_path}' />\n"
		. "            <input type='hidden' name='class_mode' value='{$g2ic_class_mode}' />\n"
		. "            <input type='hidden' name='g2ic_form' value='{$g2ic_form}' />\n"
		. "            <input type='hidden' name='g2ic_field' value='{$g2ic_field}' />\n"
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

		// Alignment selection
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['alignment_legend'] . '</legend>' . "\n"
		. g2ic_make_html_alignment_select()
		. "        </fieldset>\n\n"

		// "Custom URL" text box
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['custom_url'] . '</legend>' . "\n"
		. '            <label for="custom_url">' . $g2ic_lang['url'] . '</label>' . "\n"
		. "            <input type='text' name='custom_url' size='98' maxlength='150' value='{$g2ic_custom_url}' />\n"
		. "        </fieldset>\n\n"

		// "Description" text box
		. "        <fieldset>\n"
		. '            <legend>' . $g2ic_lang['description_legend'] . '</legend>' . "\n"
		. '            <label for="imgdesc">' . $g2ic_lang['description'] . '</label>' . "\n"
		. "            <input type='text' name='imgdesc' size='90' maxlength='150' value='{$imgdesc}' />\n"
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

function g2ic_make_html_img($image) {
	global $g2ic_rel_path,$g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_thumbnail_alt_text,$g2ic_image_url, $g2ic_click_mode;
	$html = '';

	$filename = $g2ic_rel_path.rawurlencode($image);

	// Determine $g2ic_img_html and $g2ic_link_html
	list ($g2ic_g2thumbnail_src,$g2ic_thumbnail_width,$g2ic_thumbnail_height,
		$g2ic_thumbnail_alt_text,$g2ic_image_url) = g2ic_get_img_info($filename);

	// ---- image code
	$html .= "    <img src='" . $g2ic_g2thumbnail_src. "' \n"
	. "    " . $g2ic_thumbnail_width." \n"
	. "    " . $g2ic_thumbnail_height." \n"
	. "    alt='".$g2ic_thumbnail_alt_text."' \n";

	if ($g2ic_click_mode == 'one_click_insert')
		$html .= "    onclick='insertImage(this.parentNode.getElementsByTagName(\"form\")[0])'\n";
	else
		$html .= "    onClick='showHideImageBlock(this.parentNode.getElementsByTagName(\"form\")[0])'\n";

	$html .= "    />\n";

	return $html;

}

//---------------------------------------------------------------------
//	Function:	g2ic_make_html_page_navigation
//	Parameters:	None
//	Returns:	string $html - HTML for page navigation
//	Purpose:	Generate the HTML for navigating over multiple pages
//---------------------------------------------------------------------

function g2ic_make_html_page_navigation() {
	global $g2ic_image_files,$g2ic_display_filenames,$g2ic_images_per_page,
	$g2ic_current_page,$g2ic_rel_path,$g2ic_sortby,$g2ic_lang;

	//Check if the current directory is empty - print empty message and return if empty
	if (empty($g2ic_image_files)) {
		$html = '<p><strong>' . $g2ic_lang['empty_album'] . '</strong></p>' . "\n\n";
		$page_navigation['empty'] = $html;
		return $page_navigation;
	}

	// ---- navigation for pages of images
	$pages = ceil(count($g2ic_image_files)/$g2ic_images_per_page);
	if ($g2ic_current_page > $pages)
		$g2ic_current_page = $pages;

	$pagelinks = array();
	for ($count = 1; $count <= $pages; $count++) {
		if ($g2ic_current_page == $count) {
			$pagelinks[] = "        <strong>$count</strong>";
		}
		else {
			$html = "        <a href='{$_SERVER['PHP_SELF']}?g2ic_page={$count}"
			. "&sortby={$g2ic_sortby}&rel_path={$g2ic_rel_path}";
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
	$html = "            <select name='$name' id='$name' size='1'>\n";
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
//	Parameters:	None
//	Returns:	string $html
//	Purpose:	Create the HTML for WPG2 album insertion
//---------------------------------------------------------------------

function g2ic_make_html_wpg2_album_insert_button(){

	GLOBAL $g2ic_rel_path, $g2ic_wpg2id_tags, $g2ic_lang, $g2ic_form, $g2ic_field;
	$html = '';

	// Strip leading slash unless this is the root directory
	if (strlen($g2ic_rel_path) > 1)
		$album_name = substr_replace($g2ic_rel_path,'',0,1);
	else $album_name = $g2ic_rel_path;

	// Determine $g2ic_img_html and $g2ic_link_html
	list ($g2thumbnail_src,$thumbnail_width,$thumbnail_height,
		$thumbnail_alt_text,$image_url) = g2ic_get_img_info($g2ic_rel_path);

	// Create the form
	list($album_id, $album_title) = g2ic_get_g2_id_title_by_path($g2ic_rel_path);
	$html .= "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . $g2ic_lang['wpg2_album_legend'] . ' ' . $album_title . '</legend>' . "\n"
	. "        <form action='{$_SERVER['PHP_SELF']}?rel_path={$g2ic_rel_path}'\n"
	. "        method='post'\n"
	. "        id='wpg2_album_form' >\n"

	// Alignment class select box
	. '            ' . $g2ic_lang['alignment_class'] . "\n"
	. g2ic_make_html_alignment_select()

	// "Insert" button
	. "            <input type='button'\n"
	. "            onclick='insertImage(this.parentNode)'\n"
	. '            value="' . $g2ic_lang['insert'] . '"' . "\n"
	. "            />\n"
	. "            <input type='hidden' name='imginsert' value='radio_fake' />\n";
	if($g2ic_wpg2id_tags){
		$html .= "            <input type='hidden' name='radio_selected' value='wpg2id_album' />\n"
		. "            <input type='hidden' name='imgdesc' value='{$album_id}' />\n";
	}
	else {
		$html .= "            <input type='hidden' name='radio_selected' value='wpg2_album' />\n"
		. "            <input type='hidden' name='imgdesc' value='{$album_name}' />\n";
	}
	$html .=  "            <input type='hidden' name='thumbnail_src' value='{$g2thumbnail_src}' />\n"
	. "            <input type='hidden' name='thumbw' value='{$thumbnail_width}' />\n"
	. "            <input type='hidden' name='thumbh' value='{$thumbnail_height}' />\n"
	. "            <input type='hidden' name='g2ic_form' value='{$g2ic_form}' />\n"
	. "            <input type='hidden' name='g2ic_field' value='{$g2ic_field}' />\n"
	. "        </form>\n"
	. "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}
?>
