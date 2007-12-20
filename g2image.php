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

// ====( Version Info )
$g2ic_version_text = '3.1 Alpha';
$g2ic_version_array = array(3,1);

// ====( Initialization Code )
g2ic_magic_quotes_remove($_REQUEST);
require_once('init.php');
require_once('modules/module.inc.php');
require_once('activemodules.php');
require_once('backends/' . 'Gallery2' . 'BackendApi.class.php');
require_once('debug.inc.php');
require_once('header.class.php');
require_once('message_handling.class.php');

$BackendApiClass = 'Gallery2' . 'BackendApi';

$g2obj = new $BackendApiClass($g2ic_options, $g2ic_tree, $g2ic_items, $g2ic_totalAvailableDataItems);
if ($g2obj->error) {
	echo debug::show($g2obj, 'Backend Object');
	list($head, $body) = message_handling::renderMessages($g2obj->messages);
	g2ic_fatal_error($head.$body, $g2ic_options, $g2obj);
}

$_SESSION['g2ic_tree'] =  serialize($g2obj->tree);
$_SESSION['g2ic_items'] =  serialize($g2obj->dataItems);
$_SESSION['g2ic_totalAvailableDataItems'] =  $g2obj->totalAvailableDataItems;

// ====( Main HTML Generation Code )
header('content-type: text/html; charset=utf-8');
$header = new g2ic_header($g2ic_options, $g2obj);
$html = $header->html
. '<body id="g2image">
<form method="post">
<table>
<tr>
<td width="200px" valign="top">
'
. g2ic_make_html_backend_selector($g2ic_options)
. g2ic_make_html_album_tree($g2obj->tree, $g2ic_options, $g2obj)
. '</td>
<td valign="top">
	<div class="main">
'
. g2ic_make_html_album_insert_controls($g2ic_options, $g2obj);

if (empty($g2obj->dataItems)) {
	$html .= g2ic_make_html_empty_page();
}
else {
	$g2ic_page_navigation = g2ic_make_html_page_navigation($g2obj, $g2ic_options);
	$html .= g2ic_make_html_display_options($g2ic_options, $g2obj);
	$html .= g2ic_make_html_image_insert_controls($g2ic_options);
	$html .= $g2ic_page_navigation;
	$html .= g2ic_make_html_image_navigation($g2obj, $g2ic_options);
	$html .= $g2ic_page_navigation;
	
}

$html .= g2ic_make_html_about($g2obj, $g2ic_version_text);

$html .= '	</div>
</td>
</tr>
</table>
</form>
'
. debug::show($g2obj, 'Backend Object')
. '</body>
</html>';

echo $html;

//$BackendApiClass::finished();  TODO fix for PHP4

// ====( Functions - Alphabetical by Function Name)

/**
 * Make the array of selection options for the "How to Insert?" select element
 *
 * @return array $albuminsert_selectoptions The array of selection options for the "How to Insert?" select element
 */
function g2ic_get_albuminsert_selectoptions($g2ic_options){

	$albuminsert_selectoptions = array();

	foreach($g2ic_options['album_modules'] as $moduleName){
		 $albuminsert_selectoptions[$moduleName] = array( "text" => all_modules::call($moduleName, "select") ) ;
	}

	if ($albuminsert_selectoptions[$g2ic_options['albuminsert']]) {
		$albuminsert_selectoptions[$g2ic_options['albuminsert']]['selected'] = TRUE;
	}
	return $albuminsert_selectoptions;
}

/**
 * Make the array of selection options for the "How to Insert?" select element
 *
 * @return array $imginsert_selectoptions The array of selection options for the "How to Insert?" select element
 */
function g2ic_get_imginsert_selectoptions($g2ic_options){

	$imginsert_selectoptions = array();

	foreach($g2ic_options['image_modules'] as $moduleName){
		 $imginsert_selectoptions[$moduleName] = array('text' => all_modules::call($moduleName, 'select')) ;
	}

	if ($imginsert_selectoptions[$g2ic_options['imginsert']]) {
		$imginsert_selectoptions[$g2ic_options['imginsert']]['selected'] = TRUE;
	}
	return $imginsert_selectoptions;
}

/**
 * Make the array of selection options for the "HTML Target" select element
 *
 * @return array $imginsert_selectoptions The array of selection options for the "HTML Target" select element
 */
function g2ic_make_html_target_select($name, $g2ic_options) {

	$html_target_options = array('' => array('text' => T_('None')),
		'_blank' => array('text' => T_('_blank - New Window')),
		'_parent' => array('text' => T_('_parent - Parent Frame')),
		'_self' => array('text' => T_('_self - Same Window/Frame')),
		'_top' => array('text' => T_('_top - Top Frame')));

	if ($g2ic_options['custom_target']){
		$html_target_options = array_merge($html_target_options, array($g2ic_options['custom_target'] => array('text' => $g2ic_options['custom_target'])));
	}
	
	if ($html_target_options[$g2ic_options['html_target']]) {
		$html_target_options[$g2ic_options['html_target']]['selected'] = TRUE;
	}
	
	$html = g2ic_make_html_select($name, $html_target_options);

	return $html;
}

/**
 * Remove "Magic Quotes"
 *
 * @param array &$array POST or GET with magic quotes
 */
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

/**
 * Creates the "About" alert HTML
 *
 * @return string $html The "About" alert HTML
 */
function g2ic_make_html_about($g2obj, $version){

	$album_info = $g2obj->album;

	$html = '		<div class="about_button">' . "\n"
	. '			<input type="button" onclick="alert(\'' . T_('Gallery2 Image Chooser') . '\n' . T_('Version') . ' ' . $version
	. '\n' . T_('Documentation:') .  ' http://g2image.steffensenfamily.com/\')" '
	. 'value="' . T_('About G2Image') . '"/>' . "\n"
	. '		</div>' . "\n";

	return $html;
}

function g2ic_make_html_backend_selector($g2ic_options){

	$sortoptions = array();
	
	foreach ($g2ic_options['active_backends'] as $backend) {
		$sortoptions = array_merge($sortoptions, array($backend => array('text' => $backend)));
	}

	$sortoptions[$g2ic_options['current_backend']]['selected'] = TRUE;

	$html = '	' . T_('Gallery Platform:') . "\n"
	. g2ic_make_html_select('current_backend',$sortoptions,'document.forms[0].submit();');
	
	return $html;
}	

/**
 * Creates the album tree HTML
 *
 * @return string $html The album tree HTML
 */
function g2ic_make_html_album_tree($tree, $g2ic_options, $g2obj){

	$sortoptions = $g2obj->albumSortMethod;

	$sortoptions[$g2ic_options['album_sortby']]['selected'] = TRUE;

	$html = '	' . T_('Sorted by:') . "\n"
	. g2ic_make_html_select('album_sortby',$sortoptions,'document.forms[0].submit();')
	
	// Album navigation
	.'	<div class="dtree">' . "\n"
	. '		<p><a href="javascript: d.openAll();">' . T_('Expand all') . '</a> | <a href="javascript: d.closeAll();">' . T_('Collapse all') . '</a> | <a href="?refresh_album_tree=1">' . T_('Refresh') . '</a></p>' . "\n"
	. '		<script type="text/javascript">' . "\n"
	. '			<!--' . "\n"
	. '			d = new dTree("d");' . "\n";
	$parent = -1;
	$node = 0;
	foreach ($tree as $root => $trunk) {
		$html .= g2ic_make_html_album_tree_branches($trunk, $root, $parent, $node, $g2ic_options);
	}
	$html .= '			document.write(d);' . "\n"
	. '			//-->' . "\n"
	. '		</script>' . "\n"
	. '	</div>' . "\n";

	return $html;
}

/**
 * Generates album hierarchy as d.add entites of dtree
 *
 * @param int $current_album id of current album
 * @param int $parent node of the parent album
 */
function g2ic_make_html_album_tree_branches($branch, $current_album, $parent, &$node, $g2ic_options) {

	$album_title = $branch['title'];
	$html = '			d.add(' . $node . ',' . $parent . ',"' . $album_title . '","'
	. '?current_album=' . $current_album . '");' . "\n";
	if (isset($branch['children'])) {
		$parent = $node;
		foreach ($branch['children'] as $album => $twig) {
			$node++;
			$html .= g2ic_make_html_album_tree_branches($twig, $album, $parent, $node, $g2ic_options);
		}
	}
	return $html;
}

/**
 * Creates the alignment selection HTML
 *
 * @return string $html The alignment selection HTML
 */
function g2ic_make_html_alignment_select($name, $g2ic_options){

	// array for output
	$align_options = array('none' => array('text' => T_('None')),
		'g2image_normal' => array('text' => T_('Normal')),
		'g2image_float_left' => array('text' => T_('Float Left')),
		'g2image_float_right' => array('text' => T_('Float Right')),
		'g2image_centered' => array('text' => T_('Centered')));

	if ($g2ic_options['custom_class_1'] != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_options['custom_class_1'] => array('text' => $g2ic_options['custom_class_1'])));
	}

	if ($g2ic_options['custom_class_2'] != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_options['custom_class_2'] => array('text' => $g2ic_options['custom_class_2'])));
	}

	if ($g2ic_options['custom_class_3'] != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_options['custom_class_3'] => array('text' => $g2ic_options['custom_class_3'])));
	}

	if ($g2ic_options['custom_class_4'] != 'not_used'){
		$align_options = array_merge($align_options, array($g2ic_options['custom_class_4'] => array('text' => $g2ic_options['custom_class_4'])));
	}

	$align_options[$g2ic_options[$name]]['selected'] = TRUE;

	$html = g2ic_make_html_select($name, $align_options);

	return $html;
}

/**
 * Create the HTML for the album insert controls
 *
 * @return string $html The HTML for the album insert controls
 */
function g2ic_make_html_album_insert_controls($g2ic_options, $g2obj){
	
	$g2ic_albuminsert_options = g2ic_get_albuminsert_selectoptions($g2ic_options);
	
	// "How to insert:" selector
	$html = '		<fieldset id="album_additional_dialog">' . "\n"
	. '			<legend>' . T_('Album Insertion Options for the entire current album: ') . $g2obj->album['title'] . '</legend>' . "\n"
	. '			<label for="alignment">' . T_('How to Insert Album') . '</label>' . "\n"
	. g2ic_make_html_select('albuminsert', $g2ic_albuminsert_options, 'toggleAlbumTextboxes();')
	. '			<br />' . "\n";

	foreach($g2ic_options['album_modules'] as $moduleName){
		$html .= all_modules::renderOptions($g2ic_options['albuminsert'], $moduleName);
	}

	// Alignment selection
	$html .= '			<label for="album_alignment">' . T_('G2Image Alignment Class') . '</label>' . "\n"
	. g2ic_make_html_alignment_select('album_alignment', $g2ic_options)
	. '			<br />' . "\n"

	// "Insert" button
	. '			<label for="album_insert_button">' . T_('Press button to insert the current album') . '</label>' . "\n"
	. '			<input type="button"' . "\n"
	. '			name="album_insert_button"' . "\n"
	. '			onclick="g2icInsert(\'album\');"' . "\n"
	. '			value="' . T_('Insert') . '"' . "\n"
	. '			/>' . "\n"
	. '		</fieldset>' . "\n";

	return $html;
}

/**
 * Create the HTML for the image controls
 *
 * @return string $html The HTML for the image controls
 */
function g2ic_make_html_image_insert_controls($g2ic_options){
	
	$g2ic_imginsert_options = g2ic_get_imginsert_selectoptions($g2ic_options);
	
	// "How to insert:" selector
	$html = '		<fieldset id="additional_dialog">' . "\n"
	. '			<legend>' . T_('Individual Image Insertion Options for the images below') . '</legend>' . "\n"
	. '			<label for="alignment">' . T_('How to Insert Image') . '</label>' . "\n"
	. g2ic_make_html_select('imginsert', $g2ic_imginsert_options, 'toggleTextboxes();')
	. '			<br />' . "\n";

	foreach($g2ic_options['image_modules'] as $moduleName){
		$html .= all_modules::renderOptions($g2ic_options['imginsert'], $moduleName);
	}

	// Alignment selection
	$html .= '			<label for="alignment">' . T_('G2Image Alignment Class') . '</label>' . "\n"
	. g2ic_make_html_alignment_select('alignment', $g2ic_options)
	
	// Advanced HTML Controls
	. '			' . T_('Show Advanced HTML Controls') . "\n"
	. '			<input type="button"' . "\n"
	. '			name="show_flash_slideshow_configuration"' . "\n"
	. '			onclick="document.getElementById(\'advanced_html_controls\').style.display=\'inline\';"' . "\n"
	. '			value="' . T_('Show') . '"' . "\n"
	. '			/>' . "\n"
	. '			<input type="button"' . "\n"
	. '			name="hide_flash_slideshow_configuration"' . "\n"
	. '			onclick="document.getElementById(\'advanced_html_controls\').style.display=\'none\';"' . "\n"
	. '			value="' . T_('Hide') . '"' . "\n"
	. '			/>' . "\n"
	. '			<br />' . "\n"
	. '			<div id="advanced_html_controls" style="display:none">' . "\n"
	. '				' . T_('Fullsize Image Maximum Dimensions - Leave both blank to use the original') . '<br />' . "\n"
	. '				' . T_('Max Width:') . "\n"
	. '				<input type="text" name="max_width" size="4" maxlength="4" value="' . $g2ic_options['max_width'] . '" />' . "\n"
	. '				' . T_('Max Height:') . "\n"
	. '				<input type="text" name="max_height" size="4" maxlength="4" value="' . $g2ic_options['max_height'] . '" /><br />' . "\n"
	. '				' . T_('HTML Target: ') . "\n"
	. g2ic_make_html_target_select('html_target', $g2ic_options)
	. '				<br />' . "\n"
	. '				' . T_('HTML Onclick:') . "\n"
	. '				<input type="text" name="html_onclick" size="80" maxlength="1000" value="' . $g2ic_options['html_onclick'] . '" />' . "\n"
	. '			</div>' . "\n"
	. '		</fieldset>'  . "\n"
	
	// "Insert" button
	. '		<fieldset>' . "\n"
	. '			<legend>' . T_('Press button to insert checked image(s)') . '</legend>' . "\n"
	. '			<input disabled type="button"'
	. ' name="insert_button"'
	. ' onclick="g2icInsert();"'
	. ' value="' . T_('Insert') . '"'
	. ' />' . "\n"
	. '			<a href="javascript: checkAllImages();">' . T_('Check all') . '</a> | <a href="javascript: uncheckAllImages();">' . T_('Uncheck all') . '</a>' . "\n"
	. '			' . T_('Keep G2Image window open after insertion') . "\n"
	. '			<input type="checkbox"'
	. ' name="keep_window_open"';
	if ($g2ic_options['keep_window_open']) {
		$html .= ' CHECKED';
	}
	$html .= ' />' . "\n"
	. '		</fieldset>' . "\n";

	return $html;
}

/**
 * Creates the HTML for the "Display Options" box
 *
 * @return string $html The HTML for the "Display Options" box
 */
function g2ic_make_html_display_options($g2ic_options, $g2obj){

	$images_per_page_options = array(10,20,30,40,50,60,9999);

	if (!in_array($g2ic_options['images_per_page'],$images_per_page_options)){
		array_push($images_per_page_options,$g2ic_options['images_per_page']);
		sort($images_per_page_options);
	}

	$sortoptions = $g2obj->itemSortMethod;

	$sortoptions[$g2ic_options['sortby']]['selected'] = TRUE;

	$html = "		<fieldset>\n"
	. '			<legend>' . T_('Display Options') . '</legend>' . "\n"
	. '			' . T_('Sorted by:') . "\n"
	. g2ic_make_html_select('sortby',$sortoptions,'document.forms[0].submit();')
	. '			' . T_('Per Page:') . "\n"
	. '			<select name="images_per_page" onchange="document.forms[0].submit();">' . "\n";

	for($i=0;$i<count($images_per_page_options);$i++){
		$html .= '				<option value="' . $images_per_page_options[$i] . '"';
		if ($images_per_page_options[$i] == $g2ic_options['images_per_page'])
			$html .= " selected='selected'";
		$html .= '>';
		if ($images_per_page_options[$i] == 9999)
			$html .= T_('All');
		else
			$html .= $images_per_page_options[$i];
		$html .= "</option>\n";
	}

	$html .=	"			</select>\n"
	. '			<br />' . "\n";

	$html .= '			<input type="radio" name="display" value="thumbnails"';
	if (!$g2ic_options['display_filenames'])
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "			onclick='showThumbnails()'"
	.  '>' . T_('Thumbnails') . '</input>' . "\n";

	$html .= '			<input type="radio" name="display" value="filenames"';
	if ($g2ic_options['display_filenames'])
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "			onclick='showFileNames()'"
	.  '>' . T_('Thumbnails with info') . '</input>' . "\n";

	$html .= "		</fieldset>\n";

	return $html;
}


/**
 * Make the HTML for the "No photos in this album" message
 *
 * @return string $html The HTML for the "No photos in this album" message
 */
function g2ic_make_html_empty_page() {

	$html = '<p><strong>' . T_('There are no photos in this album.<br /><br />Please pick another album from the navigation options above.') . '</strong></p>' . "\n";

	return $html;
}

/**
 * Make the HTML for the image block
 *
 * @return string $html The HTML for the image block
 */
function g2ic_make_html_image_navigation($g2obj, $g2ic_options){

	$items = $g2obj->dataItems;

	$html = '';
	foreach($items as $item) {

		$image_id = $item['id'];

		if ($g2ic_options['display_filenames']){
			$html .=  "		<div class='title_imageblock'>\n";
		}
		else {
			$html .=  "		<div class='thumbnail_imageblock'>\n";
		}
		$html .= g2ic_make_html_img($g2obj, $item);

		if ($g2ic_options['display_filenames'])
			$html .= '			<div class="displayed_title">' . "\n";
		else
			$html .= '			<div class="hidden_title">' . "\n";

		$html .= '				' . T_('Title: (used for alt in HTML)') . ' <input type="text" name="item_title" size="60" maxlength="200" value="' . htmlspecialchars($item['title']) . '" /><br />' . "\n"
		. '				' . T_('Summary: (used for title in HTML)') . ' <input type="text" name="item_summary" size="60" maxlength="200" value="' . htmlspecialchars($item['summary']) . '" /><br />' . "\n"
		. '				' . T_('Description: (used for caption in Lightbox)') . '<input type="text" name="item_description" size="60" maxlength="200" value="' . htmlspecialchars($item['description']) . '" /><br />' . "\n";

		$html .=  "			</div>\n";

		if ($g2ic_options['display_filenames']){
			$html .=  "			<div class='active_placeholder'>\n"
			. "			</div>\n";
		}
		else {
			$html .=  "			<div class='inactive_placeholder'>\n"
			. "			</div>\n";
		}

		// hidden fields

		$html .= '			<input type="hidden" name="image_id" value="' . $item['id'] . '" />' . "\n"
		. '		</div>' . "\n";
	}
	return $html;
}


/**
 * Make the HTML for an individual image
 *
 * @param array $item_info Information on the image
 * @return string $html The HTML for an individual image
 */
function g2ic_make_html_img($g2obj, $item) {

	$html = '';

	if ($item['thumbnail_id']) {
		$thumbnail_img = $item['imageVersions'][$item['thumbnail_id']]['url']['image'];
		$thumbnail_width = $item['imageVersions'][$item['thumbnail_id']]['width'];
		$thumbnail_height = $item['imageVersions'][$item['thumbnail_id']]['height'];
	}
	else {
		$thumbnail_img = '';
		$thumbnail_width = 100;
		$thumbnail_height = 100;
	}
	$html .= '			<div style="background:#F0F0EE';
	if ($thumbnail_img) {
		$html .= ' url(' . $thumbnail_img . ')'; 
	}
	$html .= '; width:' 
	. $thumbnail_width . 'px; height:' . $thumbnail_height . 'px; float: left;">' . "\n"
	. '				<input type="checkbox" name="images" onclick="activateInsertButton();"/>' . "\n";

	if ($item['imageVersions']) {
		$magnifier_img_id = $g2obj->getBestFit($item, 640, 640, false);
		$magnifier_img = $item['imageVersions'][$magnifier_img_id]['url']['image'];
		$html .= '				<a title="' . $item['title'] .  '" rel="lightbox[g2image]" href="'
		. $magnifier_img . '">' . "\n"
		. '				<img src="images/magnifier.gif" border="0"></a>' . "\n";
	}
	if (!$item['thumbnail_id']) {
		$html .= '				<br />' . T_('No Thumbnail:<br />') . $item['title'];
	}
	$html .= '			</div>' . "\n";

	return $html;

}


/**
 * Make the HTML for navigating multiple pages of images
 *
 * @return string $html The HTML for navigating multiple pages of images
 */
function g2ic_make_html_page_navigation($g2obj, $g2ic_options) {

	$pages = ceil($g2obj->totalAvailableDataItems/$g2ic_options['images_per_page']);
	if ($g2ic_options['current_page'] > $pages) {
		$g2ic_options['current_page'] = $pages;
	}

	$pagelinks = array();
	for ($count = 1; $count <= $pages; $count++) {
		if ($g2ic_options['current_page'] == $count) {
			$pagelinks[] = '			<strong>' . $count . '</strong>';
		}
		else {
			$pagelinks[] = '			<a href="?current_page=' . $count . '">' . $count . '</a>';
		}
	}

	if (count($pagelinks) > 1) {
		$html = '		<fieldset>' . "\n"
		. '			<legend>' . T_('Page Navigation:') . '</legend>' . "\n"
		. '			' . T_('Page:') . ' '. "\n"
		. implode(" - \n", $pagelinks) . "\n"
		. '		</fieldset>' . "\n";
	}
	else {
		$html = "";
	}

	return $html;
}

/**
 * Creates HTML for a select element
 *
 * The array $options should contain values and descriptions:
 * array(
 *     'value' => array(
 *     'text' => 'Description',
 *     'selected' => (TRUE|FALSE),
 *     ),
 *     ...
 * )
 *
 * @param string $name The name for the select element
 * @param array $options The array of options attributes for the select element
 * @param string $onchange (optional) The string that will be exectuted when the user changes options
 * @return string $html The HTML for for select element
 */
function g2ic_make_html_select($name,$options,$onchange=null) {
	$html = '				<select name="' . $name . '" size="1" ';
	if($onchange) {
		$html .= 'onchange="' . $onchange . '" ';
	}
	$html .= '>' . "\n";
	foreach ($options as $value => $option) {
		$html .= "					<option value='{$value}'";
		if (isset($option['selected']) and $option['selected'])
			$html .= " selected='selected'";
		$html .= ">{$option['text']}</option>\n";
	}
	$html .= "				</select>\n";

	return $html;
}

function g2ic_fatal_error($str, $g2ic_options, $g2obj){
	require_once('header.class.php');
	$header = new g2ic_header($g2ic_options, $g2obj);
	echo $header->html;
	echo $str . "\n";
	echo '</body>
	</html>';
	flush();
	die;
}

function PhpArrayToJsObject($array, $objName)
{
	return "\t\t" . 'var ' . $objName . ' = ' . PhpArrayToJsObject_Recurse($array, 3) . ';' . "\n";
}

function PhpArrayToJsObject_Recurse($array, $level)
{
	// Base case of recursion: when the passed value is not a PHP array, just output it (in quotes).
	if(! is_array($array) )
	{
		// Handle null specially: otherwise it becomes "".
		if ($array === null)
		{
			return 'null';
		}
		$array = str_replace('\\', '\\\\', $array);
		return '"' . $array . '"';
	}
   
	// Open this JS object.
	$retVal =  "\n" . g2ic_add_tabs($level) . "{";

	// Output all key/value pairs as "$key" : $value
	// * Output a JS object (using recursion), if $value is a PHP array.
	// * Output the value in quotes, if $value is not an array (see above).
	$first = true;
	foreach($array as $key => $value)
	{
		// Add a comma before all but the first pair.
		if (! $first )
		{
			$retVal .= ', ' . "\n" . g2ic_add_tabs($level);
		}
		$first = false;
	   
		// Quote $key if it's a string.
		if (is_string($key) )
		{
			$key = '"' . $key . '"';
		}
		
		$nextLevel = $level +1;
		$retVal .= $key . ' : ' . PhpArrayToJsObject_Recurse($value, $nextLevel);
	}

	// Close and return the JS object.
	return $retVal . '} ';
}

function g2ic_add_tabs ($level) {
	$retVal = '';
	for ($i=0; $i<$level; $i++) {
		$retVal .= "\t";
	}
	return $retVal;
}
?>
