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

$BackendApiClass = 'Gallery2' . 'BackendApi';

$g2obj = new $BackendApiClass( $g2ic_options );
if ($g2obj->error) {
	g2ic_fatal_error($g2obj->error);
}

$g2ic_albuminsert_options = g2ic_get_albuminsert_selectoptions();
$g2ic_imginsert_options = g2ic_get_imginsert_selectoptions();

// ====( Main HTML Generation Code )
header('content-type: text/html; charset=utf-8');
$header = new g2ic_header($g2ic_options);
$html = $header->html;
$html .= '<body id="g2image">
    <form method="post">
        <table>
            <tr>
                <td width="200px" valign="top">
';
$html .= g2ic_make_html_album_tree($g2obj->tree, $g2obj->root);
$html .= '                </td>
                <td valign="top">
                    <div class="main">
';

$html .= g2ic_make_html_album_insert_controls();

if (empty($g2obj->dataItems)) {
	$html .= g2ic_make_html_empty_page();
}
else {
	$g2ic_page_navigation = g2ic_make_html_page_navigation($g2obj);
	$html .= g2ic_make_html_display_options();
	$html .= g2ic_make_html_image_insert_controls();
	$html .= $g2ic_page_navigation;
	$html .= g2ic_make_html_image_navigation($g2obj);
	$html .= $g2ic_page_navigation;
	
}

$html .= g2ic_make_html_about($g2obj, $g2ic_version_text);

$html .= '                    </div>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>';

echo $html;

//$BackendApiClass::finished();

// ====( Functions - Alphabetical by Function Name)

/**
 * Make the array of selection options for the "How to Insert?" select element
 *
 * @return array $albuminsert_selectoptions The array of selection options for the "How to Insert?" select element
 */
function g2ic_get_albuminsert_selectoptions(){
	GLOBAL $g2ic_options;

	$albuminsert_selectoptions = array();

	foreach($g2ic_options['album_modules'] as $moduleName){
		 $albuminsert_selectoptions[$moduleName] = array( "text" => all_modules::call($moduleName, "select") ) ;
	}

	if ($albuminsert_selectoptions[$g2ic_options['default_album_action']]) {
		$albuminsert_selectoptions[$g2ic_options['default_album_action']]['selected'] = TRUE;
	}
	return $albuminsert_selectoptions;
}

/**
 * Make the array of selection options for the "How to Insert?" select element
 *
 * @return array $imginsert_selectoptions The array of selection options for the "How to Insert?" select element
 */
function g2ic_get_imginsert_selectoptions(){
	GLOBAL $g2ic_options;

	$imginsert_selectoptions = array();

	foreach($g2ic_options['image_modules'] as $moduleName){
		 $imginsert_selectoptions[$moduleName] = array( "text" => all_modules::call($moduleName, "select") ) ;
	}

	if ($imginsert_selectoptions[$g2ic_options['default_image_action']]) {
		$imginsert_selectoptions[$g2ic_options['default_image_action']]['selected'] = TRUE;
	}
	return $imginsert_selectoptions;
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
	global $g2ic_options;

	$album_info = $g2obj->album;

	$html = '<div class="about_button">' . "\n"
	. '    <input type="button" onclick="alert(\'' . T_('Gallery2 Image Chooser') . '\n' . T_('Version') . ' ' . $version
	. '\n' . T_('Documentation:') .  ' http://g2image.steffensenfamily.com/\')" '
	. 'value="' . T_('About G2Image') . '"/>' . "\n"
	. '    <input type="hidden" name="current_album" value="' . $album_info['id'] . '">' . "\n"
	. '    <input type="hidden" name="album_name" value="' . $album_info['title'] . '" />' . "\n"
	. '    <input type="hidden" name="album_url" value="' . $album_info['image_url'] . '" />' . "\n"
	. '    <input type="hidden" name="album_summary" value="' . $album_info['summary'] . '" />' . "\n"
	. '    <input type="hidden" name="album_thumbnail" value="' . $album_info['thumbnail_img'] . '" />' . "\n"
	. '    <input type="hidden" name="album_thumbw" value="' . $album_info['thumbnail_width'] . '" />' . "\n"
	. '    <input type="hidden" name="album_thumbh" value="' . $album_info['thumbnail_height'] . '" />' . "\n"
	. '    <input type="hidden" name="g2ic_page" value="' . $g2ic_options['current_page'] . '" />' . "\n"
	. '    <input type="hidden" name="class_mode" value="' . $g2ic_options['class_mode'] . '" />' . "\n"
	. '    <input type="hidden" name="g2ic_form" value="' . $g2ic_options['form'] . '" />' . "\n"
	. '    <input type="hidden" name="g2ic_field" value="' . $g2ic_options['field'] . '" />' . "\n"
	. '    <input type="hidden" name="drupal_filter_prefix" value="' . $g2ic_options['drupal_g2_filter_prefix'] . '" />' . "\n"
	. '</div>' . "\n";

	return $html;
}

/**
 * Creates the album tree HTML
 *
 * @return string $html The album tree HTML
 */
function g2ic_make_html_album_tree($tree, $root){

	// Album navigation
	$html = '<div class="dtree">' . "\n"
	. '    <p><a href="javascript: d.openAll();">' . T_('Expand all') . '</a> | <a href="javascript: d.closeAll();">' . T_('Collapse all') . '</a></p>' . "\n"
	. '    <script type="text/javascript">' . "\n"
	. '        <!--' . "\n"
	. '        d = new dTree("d");' . "\n";
	$parent = -1;
	$node = 0;
	$html .= g2ic_make_html_album_tree_branches($tree[$root], $root, $parent, $node);
	$html .= '        document.write(d);' . "\n"
	. '        //-->' . "\n"
	. '    </script>' . "\n"
	. '</div>' . "\n\n";

	return $html;
}

/**
 * Generates album hierarchy as d.add entites of dtree
 *
 * @param int $current_album id of current album
 * @param int $parent node of the parent album
 */
function g2ic_make_html_album_tree_branches($branch, $current_album, $parent, &$node) {
	global $g2ic_options;
	$album_title = $branch['title'];
	$html = '        d.add(' . $node . ',' . $parent . ',"' . $album_title . '","'
	. '?current_album=' . $current_album . '&sortby=' . $g2ic_options['sortby']
	. '&images_per_page=' . $g2ic_options['images_per_page'] . '");' . "\n";
	if ($branch['children']) {
		$parent = $node;
		foreach ($branch['children'] as $album => $twig) {
			$node++;
			$html .= g2ic_make_html_album_tree_branches($twig, $album, $parent, $node);
		}
	}
	return $html;
}

/**
 * Creates the alignment selection HTML
 *
 * @return string $html The alignment selection HTML
 */
function g2ic_make_html_alignment_select($name){
	GLOBAL $g2ic_options;

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

	$align_options[$g2ic_options['default_alignment']]['selected'] = TRUE;

	$html = g2ic_make_html_select($name,$align_options);

	return $html;
}

/**
 * Create the HTML for the image controls
 *
 * @return string $html The HTML for the image controls
 */
function g2ic_make_html_album_insert_controls(){
	global $g2ic_albuminsert_options, $g2ic_options;

	// "How to insert:" selector
	$html = '        <fieldset id="album_additional_dialog">' . "\n"
	. '            <legend>' . T_('Album Insertion Options for the entire current album: ') . $g2obj->album['title'] . '</legend>' . "\n"
	. '            <label for="alignment">' . T_('How to Insert Album') . '</label>' . "\n"
	. g2ic_make_html_select('albuminsert', $g2ic_albuminsert_options, 'toggleAlbumTextboxes();')
	. '            <br />' . "\n";

	$html .= "  \n";
	foreach($g2ic_options['album_modules'] as $moduleName){
		$html .= all_modules::renderOptions($g2ic_options['default_album_action'], $moduleName);
	}

	// Alignment selection
	$html .= '            <label for="album_alignment">' . T_('G2Image Alignment Class') . '</label>' . "\n"
	. g2ic_make_html_alignment_select('album_alignment')
	. '            <br />' . "\n"

	// "Insert" button
	. '            <label for="album_insert_button">' . T_('Press button to insert the current album') . '</label>' . "\n"
	. '            <input type="button"' . "\n"
	. '            name="album_insert_button"' . "\n"
	. '            onclick="insertAlbum();"' . "\n"
	. '            value="' . T_('Insert') . '"' . "\n"
	. '            />' . "\n"
	. '        </fieldset>' . "\n\n";

	return $html;
}

/**
 * Create the HTML for the image controls
 *
 * @return string $html The HTML for the image controls
 */
function g2ic_make_html_image_insert_controls(){
	global $g2ic_imginsert_options, $g2ic_options;

	// "How to insert:" selector
	$html = '        <fieldset id="additional_dialog">' . "\n"
	. '            <legend>' . T_('Individual Image Insertion Options for the images below') . '</legend>' . "\n"
	. '            <label for="alignment">' . T_('How to Insert Image') . '</label>' . "\n"
	. g2ic_make_html_select('imginsert', $g2ic_imginsert_options, 'toggleTextboxes();')
	. '            <br />' . "\n";

	$html .= "  \n";
	foreach($g2ic_options['image_modules'] as $moduleName){
		$html .= all_modules::renderOptions($g2ic_options['default_image_action'], $moduleName);
	}

	// Alignment selection
	$html .= '            <label for="alignment">' . T_('G2Image Alignment Class') . '</label>' . "\n"
	. g2ic_make_html_alignment_select('alignment')
	. "        </fieldset>\n\n";

	// "Insert" button
	$html .=  "        <fieldset>\n"
	. '            <legend>' . T_('Press button to insert checked image(s)') . '</legend>' . "\n"
	. "            <input disabled type='button'\n"
	. "            name='insert_button'\n"
	. '            onclick="insertItems();"' . "\n"
	. '            value="' . T_('Insert') . '"' . "\n"
	. '            />' . "\n"
	. '            <a href="javascript: checkAllImages();">' . T_('Check all') . '</a> | <a href="javascript: uncheckAllImages();">' . T_('Uncheck all') . '</a>' . "\n"
	. "        </fieldset>\n\n";

	return $html;
}

/**
 * Creates the HTML for the "Display Options" box
 *
 * @return string $html The HTML for the "Display Options" box
 */
function g2ic_make_html_display_options(){
	global $g2ic_options;

	$images_per_page_options = array(10,20,30,40,50,60,9999);

	if (!in_array($g2ic_options['images_per_page'],$images_per_page_options)){
		array_push($images_per_page_options,$g2ic_options['images_per_page']);
		sort($images_per_page_options);
	}

	// array for output
	$sortoptions = array('title_asc' => array('text' => T_('Gallery2 Title (A-z)')),
		'title_desc' => array('text' => T_('Gallery2 Title (z-A)')),
		'orig_time_desc' => array('text' => T_('Origination Time (Newest First)')),
		'orig_time_asc' => array('text' => T_('Origination Time (Oldest First)')),
		'mtime_desc' => array('text' => T_('Last Modification (Newest First)')),
		'mtime_asc' => array('text' => T_('Last Modification (Oldest First)')));

	$sortoptions[$g2ic_options['sortby']]['selected'] = TRUE;

	$html = "<div>\n"
	. "    <fieldset>\n"
	. '        <legend>' . T_('Display Options') . '</legend>' . "\n"
	. '            ' . T_('Sorted by:') . "\n"
	. g2ic_make_html_select('sortby',$sortoptions,'document.forms[0].submit();')
	. '            ' . T_('Per Page:') . "\n"
	. '            <select name="images_per_page" onchange="document.forms[0].submit();">' . "\n";

	for($i=0;$i<count($images_per_page_options);$i++){
		$html .= '                <option value="' . $images_per_page_options[$i] . '"';
		if ($images_per_page_options[$i] == $g2ic_options['images_per_page'])
			$html .= " selected='selected'";
		$html .= '>';
		if ($images_per_page_options[$i] == 9999)
			$html .= T_('All');
		else
			$html .= $images_per_page_options[$i];
		$html .= "</option>\n";
	}

	$html .=	"            </select>\n"
	. '            <br />' . "\n";

	$html .= '            <input type="radio" name="display" value="thumbnails"';
	if (!$g2ic_options['display_filenames'])
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "            onclick='showThumbnails()'"
	.  '>' . T_('Thumbnails') . '</input>' . "\n";

	$html .= '            <input type="radio" name="display" value="filenames"';
	if ($g2ic_options['display_filenames'])
		$html .= ' checked="checked"' . "\n";
	else
		$html .= "\n";
	$html .= "            onclick='showFileNames()'"
	.  '>' . T_('Thumbnails with info') . '</input>' . "\n";

	$html .= "    </fieldset>\n"
	. "</div>\n\n";

	return $html;
}

/**
 * Make the HTML for the "No photos in this album" message
 *
 * @return string $html The HTML for the "No photos in this album" message
 */
function g2ic_make_html_empty_page() {

	$html = '<p><strong>' . T_('There are no photos in this album.<br /><br />Please pick another album from the navigation options above.') . '</strong></p>' . "\n\n";

	return $html;
}

/**
 * Make the HTML for the image block
 *
 * @return string $html The HTML for the image block
 */
function g2ic_make_html_image_navigation($g2obj){
	global $g2ic_options;

	$items = $g2obj->dataItems;

	$html = '';
	foreach($items as $item) {

		$image_id = $item['id'];

		if ($g2ic_options['display_filenames']){
			$html .=  "<div class='title_imageblock'>\n";
		}
		else {
			$html .=  "<div class='thumbnail_imageblock'>\n";
		}
		$html .= g2ic_make_html_img($g2obj, $item) . "\n";

		if ($g2ic_options['display_filenames'])
			$html .= '    <div class="displayed_title">' . "\n";
		else
			$html .= '    <div class="hidden_title">' . "\n";

		$html .= '        ' . T_('Title: (used for alt in HTML)') . ' <input type="text" name="item_title" size="60" maxlength="200" value="' . htmlspecialchars($item['title']) . '" /><br />' . "\n"
		. '        ' . T_('Summary: (used for title in HTML)') . ' <input type="text" name="item_summary" size="60" maxlength="200" value="' . htmlspecialchars($item['summary']) . '" /><br />' . "\n"
		. '        ' . T_('Description: (used for caption in Lightbox)') . '<input type="text" name="item_description" size="60" maxlength="200" value="' . htmlspecialchars($item['description']) . '" /><br />' . "\n";

		$html .=  "    </div>\n\n";

		if ($g2ic_options['display_filenames']){
			$html .=  "    <div class='active_placeholder'>\n"
			. "    </div>\n\n";
		}
		else {
			$html .=  "    <div class='inactive_placeholder'>\n"
			. "    </div>\n\n";
		}

		// hidden fields

		$html .= '    <input type="hidden" name="thumbnail_img" value="' . $item['thumbnail_img'] . '" />' . "\n"
		. '    <input type="hidden" name="fullsize_img" value="' . $item['fullsize_img'] . '" />' . "\n"
		. '    <input type="hidden" name="image_url" value="' . $item['image_url'] . '" />' . "\n"
		. '    <input type="hidden" name="image_id" value="' . $item['id'] . '" />' . "\n"
		. '    <input type="hidden" name="thumbw" value="' . $item["thumbnail_width"] . '" />' . "\n"
		. '    <input type="hidden" name="thumbh" value="' . $item["thumbnail_height"] . '" />' . "\n"
		. '</div>' . "\n";
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

	// ---- image code
	// TODO Think about making thumbnails bestfit in 100x100 square
	// TODO Fix so that it shows something for no thumbnail
	$html .= '    <div style="background:#F0F0EE url(' . $item['thumbnail_img'] . '); width:' 
	. $item['thumbnail_width'] . 'px; height:' . $item['thumbnail_height'] . 'px; float: left;">' . "\n"
	. '        <input type="checkbox" name="images" onclick="activateInsertButton();"/>' . "\n";

// TODO Fix so that it doesn't show magnifier if non-image.
//	if ($item_info['number_resizes'] === 'non-image') {
//	}
//	else {
		$magnifier_img = $item['fullsize_img']; //TODO fix this so that it is bestfit
		$html .= '        <a title="' . $item['title'] .  '" rel="lightbox[g2image]" href="'
		. $magnifier_img . '">' . "\n"
		. '        <img src="images/magnifier.gif" border="0"></a>' . "\n";
//	}
	$html .= '    </div>' . "\n";

	return $html;

}

/**
 * Make the HTML for navigating multiple pages of images
 *
 * @return string $html The HTML for navigating multiple pages of images
 */
function g2ic_make_html_page_navigation($g2obj) {
	global $g2ic_options;

	// ---- navigation for pages of images
	$pages = ceil(count($g2obj->dataItems)/$g2ic_options['images_per_page']);
	if ($g2ic_options['current_page'] > $pages) {
		$g2ic_options['current_page'] = $pages;
	}

	$pagelinks = array();
	for ($count = 1; $count <= $pages; $count++) {
		if ($g2ic_options['current_page'] == $count) {
			$pagelinks[] = '        <strong>' . $count . '</strong>';
		}
		else {
			$pagelinks[] = '        <a href="?g2ic_page=' . $count
			. '&sortby=' . $g2ic_options['sortby'] . '&current_album=' . $g2obj->album['id']
			. '&images_per_page='  . $g2ic_options['images_per_page'] . '">' . $count . '</a>';
		}
	}

	if (count($pagelinks) > 1) {
		$html = '<div>' . "\n"
		. '    <fieldset>' . "\n"
		. '        <legend>' . T_('Page Navigation:') . '</legend>' . "\n"
		. '        ' . T_('Page:') . ' '. "\n"
		. implode("     - \n", $pagelinks) . "\n"
		. '    </fieldset>' . "\n"
		. '</div>' . "\n\n";
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
 * 	'value' => array(
 * 	'text'     => 'Description',
 * 	'selected' => (TRUE|FALSE),
 * 	),
 * 	...
 * )
 *
 * @param string $name The name for the select element
 * @param array $options The array of options attributes for the select element
 * @param string $onchange (optional) The string that will be exectuted when the user changes options
 * @return string $html The HTML for for select element
 */
function g2ic_make_html_select($name,$options,$onchange=null) {
	$html = '            <select name="' . $name . '" size="1" ';
	if($onchange) {
		$html .= 'onchange="' . $onchange . '" ';
	}
	$html .= '>' . "\n";
	foreach ($options as $value => $option) {
		$html .= "                <option value='{$value}'";
		if (isset($option['selected']) and $option['selected'])
			$html .= " selected='selected'";
		$html .= ">{$option['text']}</option>\n";
	}
	$html .= "            </select>\n";

	return $html;
}
if ($g2obj->error) {
	echo debug::show($g2obj->error, 'Errors');
}

function g2ic_fatal_error($str){
	require_once('header.class.php');
	$header = new g2ic_header($g2ic_options);
	echo $header->html;
	echo $str . "\n";
	echo '</body>
	</html>';
	flush();
	die;
}


echo debug::show($g2obj, 'Backend Object');
?>
