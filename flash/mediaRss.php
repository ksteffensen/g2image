<?php
// +---------------------------------------------------------------------------+
// |  XML Feed for Flash Slideshow and/or Audio/Video Player for Gallery2      |
// +---------------------------------------------------------------------------+
// | mediaRss.php     [v.2.0.3]                                                |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007Wayne Patterson [suprsidr@gmail.com]                    |
// | Modified by Kirk Steffensen for use with G2Image                          |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

// Get the g2image config variables
require_once('../config.php');
// ====( Initialize Variables )=================================
$g2ic_options = array();
$g2ic_wp_rel_path = '';
$g2ic_base_path = str_repeat("../", substr_count(dirname($_SERVER['PHP_SELF']), "/"));
// Determine if in a WordPress installation by checking for wpg2.php or $g2ic_in_wordpress being set
if (@file_exists('../../wpg2.php') || $g2ic_in_wordpress) {
	// G2Image is being called from WPG2 directory
	if (@file_exists('../../wpg2.php')) {
		require_once('../../../../../wp-config.php');
	}
	// Otherwise user has set $g2ic_in_wordpress == TRUE because G2Image is being called by another editor.  E.g, FCKEditor
	else {
		for ($count = 1; $count <= 10; $count++) {
			$g2ic_wp_rel_path = $g2ic_wp_rel_path . '../';
			if (@file_exists($g2ic_wp_rel_path . 'wp-config.php')) {
				require_once($g2ic_wp_rel_path . 'wp-config.php');
				break;
			}
			elseif ($count == 10) {
				// Die on fatal error of not finding wp-config.php
				print ('<h3>Fatal Error: Cannot locate wp-config.php.</h3><br />You have set $g2ic_in_wordpress to TRUE, but G2Image cannot locate wp-config.php in any parent directory.');
				print '</body>' . "\n\n";
				print '</html>';
				die;
			}
		}
	}
		$wpg2_g2paths = get_option('wpg2_g2paths');
		$g2ic_embedded_mode = TRUE;
		$g2ic_use_full_path = TRUE;
		$g2ic_embed_uri = $wpg2_g2paths['g2_embeduri'];
		$g2ic_gallery2_uri = $wpg2_g2paths['g2_url'];
		$g2ic_gallery2_path = $wpg2_g2paths['g2_filepath'];
}

if(!$g2ic_embedded_mode) {
	$g2ic_embed_uri = '/' . $g2ic_gallery2_path . 'main.php';
	$g2ic_gallery2_uri = '/' . $g2ic_gallery2_path . '/';
}

if(!$g2ic_use_full_path)
	$g2ic_gallery2_path = $g2ic_base_path.$g2ic_gallery2_path;

function init () { // connect to gallery
	global $g2ic_gallery2_path, $g2ic_embed_uri, $g2ic_gallery2_uri;
	require_once( $g2ic_gallery2_path . 'embed.php');
	$ret = GalleryEmbed::init(array('fullInit' => true, 'embedUri' => $g2ic_embed_uri, 'g2Uri' => $g2ic_gallery2_uri));
	if ($ret) {
		print 'GalleryEmbed::init failed, here is the error message: ' . $ret->getAsHtml();
		exit;
	}
	GalleryEmbed::done();
}

/**
 * Dynamic query for tag items
 * @param int $userId
 * @param string $keyword (optional) keyword for query; get from request if not specified
 * @return array object GalleryStatus a status code
 *         array of item ids
 * @static
 */
function getTagChildIds($userId, $tagName=null) {
	global $gallery;
	$storage =& $gallery->getStorage();

	if (!isset($tagName)) {
		$tagName = GalleryUtilities::getRequestVariables('tagName');
	}
	if (empty($tagName)) {
		return array(GalleryCoreApi::error(ERROR_BAD_PARAMETER), null);
	}

	/* Force case-sensitive look-up to make the query use an column index */
	list ($ret, $tagId) = TagsHelper::getTagIdFromName($tagName, true);
	if ($ret) {
		return array($ret, null);
	}

	if (empty($tagId)) {
		return array(null, array());
	}

	list ($ret, $query, $data) = GalleryCoreApi::buildItemQuery('TagItemMap', 'itemId',
		'[TagItemMap::tagId] = ?', null, null, null, 'core.view', false, $userId);
	if ($ret) {
		return array($ret, null);
	}

	list ($ret, $searchResults) = $gallery->search($query, array_merge(array($tagId), $data));
	if ($ret) {
		return array($ret, null);
	}
	$itemIds = array();
	while ($result = $searchResults->nextResult()) {
		$itemIds[] = $result[0];
	}
	// start item display loop
	if (!empty($itemIds)) {
		foreach( $itemIds as $value ) {
			list ($ret, $childItem) = GalleryCoreApi::loadEntitiesById($value, 'GalleryItem');
			if ($ret) {
				print "Error loading childItems:" . $ret->getAsHtml();
			}
			// we need to check the disabledFlag for each in dynamic mode
			$disabled = getDisabledFlag($childItem->getId());
			if (!$disabled) {
				if (!($childItem->entityType == "GalleryAlbumItem")) {
					$display .= getDisplay($childItem);
				}
			}
		}
		return $display;
	}
// end item display loop
}

/**
 * Dynamic query for keyword items
 * @param int $userId
 * @param string $keyword (optional) keyword for query; get from request if not specified
 * @return array GalleryStatus a status code
 *         array of item ids
 * @static
 */
function getKeywordChildIds($userId, $keyword) {
	global $gallery;
	$storage =& $gallery->getStorage();

	if (!isset($keyword)) {
		$keyword = GalleryUtilities::getRequestVariables('keyword');
	}
	if (empty($keyword)) {
		return array(GalleryCoreApi::error(ERROR_BAD_PARAMETER), null);
	}

	list ($ret, $module) = GalleryCoreApi::loadPlugin('module', 'keyalbum');
	if ($ret) {
		return array($ret, null);
	}
	list ($ret, $params) = GalleryCoreApi::fetchAllPluginParameters('module', 'keyalbum');
	if ($ret) {
		return array($ret, null);
	}

	$keywords = $where = array();
	$keywords[] = '%' . $keyword . '%';
	$where[] = '[GalleryItem::keywords] LIKE ?';


	list ($ret, $query, $data) = GalleryCoreApi::buildItemQuery(
		'GalleryItem', 'id', implode(' AND ', $where),
		$params['orderBy'], $params['orderDirection'], null, 'core.view', false, $userId);
	if ($ret) {
		return array($ret, null);
	}
	if (empty($query)) {
		return array(null, array());
	}

	list ($ret, $searchResults) = $gallery->search($query, array_merge($keywords, $data));
	if ($ret) {
		return array($ret, null);
	}
	$itemIds = array();
	while ($result = $searchResults->nextResult()) {
		$itemIds[] = $result[0];
	}
	// start item display loop
	if (!empty($itemIds)) {
		foreach( $itemIds as $value ) {
			list ($ret, $childItem) = GalleryCoreApi::loadEntitiesById($value, 'GalleryItem');
			if ($ret) {
				print "Error loading childItems:" . $ret->getAsHtml();
			}
			// we need to check the disabledFlag for each in dynamic mode
			$disabled = getDisabledFlag($childItem->getId());
			if(!$disabled) {
				if(!($childItem->entityType == "GalleryAlbumItem")) {
					$display .= getDisplay($childItem);
				}
			}
		}
		return $display;
	}
// end item display loop
}

/**
 * Dynamic query for dynamic items
     * @param int $userId
 * @return array object GalleryStatus a status code
 *         array of item ids
 * @static
 */
function getDynamicChildIds($userId, $param='date', $orderBy='creationTimestamp',
	$orderDirection=ORDER_DESCENDING, $table='GalleryEntity', $id='id') {
	global $gallery;
	$storage =& $gallery->getStorage();
	list ($ret, $params) = GalleryCoreApi::fetchAllPluginParameters('module', 'dynamicalbum');
	if ($ret) {
		return array($ret, null);
	}
	$size = $params['size.' . $param];
	$type = $params['type.' . $param];
	if (!$size) {
		return array(GalleryCoreApi::error(ERROR_PERMISSION_DENIED), null);
	}

	list ($show, $albumId) = GalleryUtilities::getRequestVariables('show', 'albumId');
	if (!empty($show)) {
		$type = $show;
	}
	switch ($type) {
	case 'data':
		$class = 'GalleryDataItem';
	break;
	case 'all':
		$class = 'GalleryItem';
	break;
	case 'album':
		$class = 'GalleryAlbumItem';
	break;
	default:
		return array(GalleryCoreApi::error(ERROR_BAD_PARAMETER), null);
	}
	if (!isset($table)) {
		$table = $class;
	}

	$query = '[' . $table . '::' . $id . '] IS NOT NULL';
	if (!empty($albumId)) {
		list ($ret, $sequence) = GalleryCoreApi::fetchParentSequence($albumId);
		if ($ret) {
			return array($ret, null);
		}
		if (!empty($sequence)) {
			$sequence = implode('/', $sequence) . '/' . (int)$albumId . '/%';
			$query = '[GalleryItemAttributesMap::parentSequence] LIKE ?';
			$table = 'GalleryItemAttributesMap';
			$id = 'itemId';
		} else {
			$query = '[' . $table . '::' . $id . '] <> ' . (int)$albumId;
		}
	}
	if ($table == $class) {
		$class = null;
	}
	list ($ret, $query, $data) = GalleryCoreApi::buildItemQuery(
		$table, $id, $query, $orderBy, $orderDirection,
		$class, 'core.view', false, $userId);
	if ($ret) {
		return array($ret, null);
	}
	if (empty($query)) {
		return array(null, array());
	}
	if (!empty($sequence)) {
		array_unshift($data, $sequence);
	}

	list ($ret, $searchResults) = $gallery->search($query, $data,
		array('limit' => array('count' => $size)));
	if ($ret) {
		return array($ret, null);
	}
	$itemIds = array();
	while ($result = $searchResults->nextResult()) {
		$itemIds[] = $result[0];
	}
// start item display loop
	if (!empty($itemIds)) {
		foreach( $itemIds as $value ) {
			list ($ret, $childItem) = GalleryCoreApi::loadEntitiesById($value, 'GalleryItem');
			if ($ret) {
				print "Error loading childItems:" . $ret->getAsHtml();
			}
			// we need to check the disabledFlag for each in dynamic mode
			$disabled = getDisabledFlag($childItem->getId());
			if(!$disabled) {
				if(!($childItem->entityType == "GalleryAlbumItem")) {
					$display .= getDisplay($childItem);
				}
			}
		}
		return $display;
	}
// end item display loop
}

function getRoot() {
	global $gallery;
	if (GalleryUtilities::isCompatibleWithApi(array(7,5), GalleryCoreApi::getApiVersion())) {
		list($ret, $defaultId) = GalleryCoreApi::getDefaultAlbumId();
		if ($ret) {
			return array($ret, null);
		} else {
			return $defaultId;
		}
	} else {
		list ($ret, $defaultId) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.rootAlbum');
		if ($ret) {
			return array($ret, null);
		} else {
			return $defaultId;
		}
	}
}

function getAlbumList ($id) {
	global $gallery;
	$display = "";
	if(!isset($defaultId)) {
		$defaultId = getRoot();
	}
	list ($ret, $Albums) = GalleryCoreApi::fetchAlbumTree();
	list ($ret, $Albums) = GalleryCoreApi::loadEntitiesById(GalleryUtilities::arrayKeysRecursive($Albums), 'GalleryAlbumItem');
	if(isset ($defaultId)) {
		list ($ret, $rootAlbum) = GalleryCoreApi::loadEntitiesById( $defaultId, 'GalleryAlbumItem' );
		if ($ret) {
			print "Error loading rootAlbum:" . $ret->getAsHtml();
		}
		array_unshift($Albums, $rootAlbum);
	}
	foreach ($Albums as $Albums) {
		// we can check for disabledFlag for the whole album
		$disabled = getDisabledFlag($Albums->getId());
		if (($Albums->canContainChildren == 1 && $Albums->parentId == $id) || ($Albums->canContainChildren == 1 && $Albums->getId() == $id) || ($Albums->canContainChildren == 1 && $Albums->parentId == 418) && !$disabled || empty($id)) {
			$display .= "        <album>\n";
			$display .= "            <title>" . cdata($Albums->getTitle()) . "</title>\n";
			$display .= "            <parentId>" . cdata($Albums->parentId) . "</parentId>\n";
			$display .= "            <owner>" . cdata(getOwner($Albums->ownerId, 'GalleryUser')) . "</owner>\n";
			$display .= "            <id>" . cdata($Albums->getId()) . "</id>\n";
			$display .= "        </album>\n";
		}
	}

	return $display;
}

function getItems ($id) {
	global $gallery;
	$display = "";

	list ($ret, $entity) = GalleryCoreApi::loadEntitiesById( $id, 'GalleryItem' );
	if ($ret) {
		print "Error loading Entity:" . $ret->getAsHtml();
	}
	// we can check for disabledFlag for the whole album
	$disabled = getDisabledFlag($id);
	if(!$disabled) {
		list ($ret, $childIds) = GalleryCoreApi::fetchChildItemIds($entity);
		if ($ret) {
			print "Error finding child item ids:" . $ret->getAsHtml();
		}
		if (!empty($childIds)) {
			foreach( $childIds as $value ) {
				list ($ret, $childItem) = GalleryCoreApi::loadEntitiesById($value, 'GalleryItem');
				if ($ret) {
					print "Error loading childItems:" . $ret->getAsHtml();
				}
				if(!($childItem->entityType == "GalleryAlbumItem")) {
					$display .= getDisplay($childItem);
				}
			}
		}
		return $display;
	}
}

//the big display function
function getDisplay($item) {
	$item = getPreferred($item);
	list ($ret, $bestFit) = getBestImageId($item->getId());
	if ($ret) {
		print 'Error getting best-fit image: ' . $ret->getAsHtml();
	}
	$itemId = $item->getId();
	$display = '';
	if(hasPermission($itemId)) {
		list ($ret, $thumbnailList) = GalleryCoreApi::fetchThumbnailsByItemIds(array($itemId));
		if ($ret) {
			return array($ret->wrap(__FILE__, __LINE__), null);
		}
		$display .= "        <item>\n";
		$display .= "            <title>" . cdata(getTitle($item)) . "</title>\n";
		$display .= "            <id>" . $itemId . "</id>\n";
		$display .= "            <link>" . getLink($item) . "</link>\n";
		$display .= "            <view>" . getView($bestFit) . "</view>\n";
		$display .= "            <thumbUrl>" . getThumbUrl($item) . "</thumbUrl>\n";
		$display .= "            <width>" . getWidth($bestFit) . "</width>\n";
		$display .= "            <height>" . getHeight($bestFit) . "</height>\n";
		$display .= "            <mime>" . getMime($bestFit) . "</mime>\n";
		if (!$ret && !empty($thumbnailList)) {
			$display .= "            <description>". cdata("<a href=\"" . getLink($item) . "\"><img border=\"0\" src=\"" . getThumbUrl($item) . "\" width=\"" . getWidth($thumbnailList[$itemId]) . "\" height=\"" . getHeight($thumbnailList[$itemId]) . "\"/></a><br/>" . getTitle($item)) ."</description>\n";
		}
		$display .= "            <guid isPermaLink=\"false\">" . getLink($item) . "</guid>\n";
		$display .= "            <pubDate>" . date('r', $item->getModificationTimestamp()) . "</pubDate>\n";
		// start new media rss
		$display .= "            <media:content url=\"" . getView($bestFit) . "\" type=\"" . getMime($bestFit) . "\" width=\"" . getWidth($bestFit) . "\" height=\"" . getHeight($bestFit) . "\">\n";
		$display .= "                <media:title type=\"plain\">" . cdata(getTitle($item)) . "</media:title>\n";
		$display .= "                <media:thumbnail url=\"" . getThumbUrl($item) . "\" width=\"" . getWidth($thumbnailList[$itemId]) . "\" height=\"" . getHeight($thumbnailList[$itemId]) . "\" time=\"" . date('r', $item->getModificationTimestamp()) . "\"/>\n";
		if (!$ret && !empty($thumbnailList)) {
			$display .= "                <media:description type=\"html\">" . cdata("<a href=\"" . getLink($item) . "\"><img border=\"0\" src=\"" . getThumbUrl($item) . "\" width=\"" . getWidth($thumbnailList[$itemId]) . "\" height=\"" . getHeight($thumbnailList[$itemId]) . "\"/></a><br/>" . getTitle($item)) ."</media:description>\n";
		}
		$display .= "            </media:content>\n";
		$display .= "        </item>\n";
	}
	return $display;
}

//check if current user has view permissions
function hasPermission($itemId) {
	global $gallery;
	if (!isset($userId)) {
		$userId = $gallery->getActiveUserId();
	}
	if (!isset($userId)) {
		$userId = GalleryCoreApi::getAnonymousUserId();
	}
	list ($ret, $ok) = GalleryCoreApi::hasItemPermission($itemId, 'core.view', $userId);
	if ($ret || !$ok) {
		return false;
	} else {
		return true;
	}
}

//check to see if a module is available
function pluginCheck($plugin) {
	list ($ret, $modules) = GalleryCoreApi::fetchPluginStatus('module');
	if ($ret)
	{
		print "checking plugin:". $plugin . " - " . $ret->getAsHtml();
	}
	if($modules[$plugin]['active'] && $modules[$plugin]['available']) {
		return true;
	} else {
		return false;
	}
}
//check to see if the "Prevent this album from being displayed in the Image Block" is checked
function getDisabledFlag($itemId) {
	$isActive = pluginCheck('imageblock');
	if($isActive) {
		list ($ret, $searchResults) = GalleryCoreApi::getMapEntry('ImageBlockDisabledMap',
			array('itemId'), array('itemId' => (int)$itemId));
		if ($ret) {
			return false;
		}
		$result = false;
		if ($rec = $searchResults->nextResult()) {
			$result = (bool)$rec[0];
		}
		return $result;
	} else {
		//we want to return false if the imageBlock module is not active
		return false;
	}
}

function getPreferred($item) {
	list ($ret, $preferred) = GalleryCoreApi::fetchPreferredsByItemIds(array($id));
	if ($ret) {
		return array($ret, null);
	}
	if (isset($preferred[$id])) {
		return $preferred[$id];
	} else {
		return $item;
	}
}

function getOwner($id, $type) {
	list ($ret, $entity) = GalleryCoreApi::loadEntitiesById( $id, $type );
	if ($ret) {
		print "Error loading ownerId:" . $ret->getAsHtml();
	}
	$owner = $entity->userName;
	return $owner;
}

function getTitle($item) {
	return stripTags($item->getTitle());
}

function stripTags($tostrip) {
	GalleryCoreApi::requireOnce('lib/smarty_plugins/modifier.markup.php');
	$stripped = smarty_modifier_markup($tostrip, 'strip');
	return $stripped;
}

function getMime($item) {
	if (!($item->entityType == "GalleryAlbumItem")) {
		return $item->getMimeType();
	} else {
		return "Album";
	}
}

function getWidth($item) {
	if(($item->entityType == "GalleryAnimationItem" || $item->entityType == "GalleryPhotoItem" || $item->entityType == "ThumbnailImage" || $item->entityType == "GalleryMovieItem" || $item->entityType == "GalleryDerivativeImage")) {
		return $item->getWidth();
	} else {
		return 480;
	}
}

function getHeight($item) {
	if(($item->entityType == "GalleryAnimationItem" || $item->entityType == "GalleryPhotoItem" || $item->entityType == "ThumbnailImage" || $item->entityType == "GalleryMovieItem" || $item->entityType == "GalleryDerivativeImage")) {
		return $item->getHeight();
	} else {
		return 160;
	}
}

function getRating($item) {
	$isActive = pluginCheck('rating');
	if($isActive) {
		$itemId = $item->getId();
		$rating = '';
		GalleryCoreApi::requireOnce('modules/rating/classes/RatingHelper.class');
		list ($ret, $Ratings) = RatingHelper::fetchRatings($itemId, '');
		if(!empty ($Ratings)) {
			$rating = $Ratings[$id]['rating'];
			return "            <rating>" . $rating . "</rating>\n";
		} else {
			return "            <rating>0</rating>\n";
		}
	}
}

function getThumbUrl($item) {
	global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
	$itemId = $item->getId();
	list ($ret, $thumbnail) = GalleryCoreApi::fetchThumbnailsByItemIds(array($itemId));
	if (!$ret && !empty($thumbnail)) {
		$thumbUrl = $urlGenerator->generateUrl(
			array('view' => 'core.DownloadItem', 'itemId' => $thumbnail[$itemId]->getId(),
				'serialNumber' => $thumbnail[$itemId]->getSerialNumber()),
			array('forceFullUrl' => true, 'forceSessionId' => true, 'htmlEntities' => true));
	} else {
		$thumbUrl = "";
	}
	return $thumbUrl;
}

function getLink($item) {
	global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
	$link = $urlGenerator->generateUrl(
		array('view' => 'core.ShowItem', 'itemId' => $item->getId()),
		array('forceFullUrl' => true, 'forceSessionId' => true));
	return $link;
}

function getPreferredLink($item) {
	global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
	$link = $urlGenerator->generateUrl(
		array('view' => 'core.ShowItem', 'itemId' => $item->getId(), 'imageViewsIndex' => 0),
		array('forceFullUrl' => true, 'forceSessionId' => true));
	return $link;
}

function getView($item) {
	global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
	$view = $urlGenerator->generateUrl(
		array('view' => 'core.DownloadItem', 'itemId' => $item->getId(),
			'serialNumber' => $item->getSerialNumber()),
		array('forceFullUrl' => true, 'forceSessionId' => true, 'htmlEntities' => true));
	return $view;
}

function getBestImageId($masterId) {
	global $gallery;

	if (isset ($_REQUEST['g2_maxImageHeight'])) {
		$maxImageHeight = $_REQUEST['g2_maxImageHeight'];
	}
	if (isset ($_REQUEST['g2_maxImageWidth'])) {
		$maxImageWidth = $_REQUEST['g2_maxImageWidth'];
	}

	$potentialImages = array();

	//how about the original?
	$ret = GalleryCoreApi::assertHasItemPermission($masterId,'core.viewSource');
	if (!$ret) {
		//is there a preferred derivative of the original?
		list ($ret, $preferred) = GalleryCoreApi::fetchPreferredsByItemIds(array($masterId));
		if ($ret) {
			return array ($ret,null);
		}
		if (!empty($preferred[$masterId])) {
			$potentialImages[] = $preferred[$masterId];
		} else {
		//if no preferred, use the original	original
			list ($ret, $item) = GalleryCoreApi::loadEntitiesById($masterId);
			if ($ret) {
				return array ($ret,null);
			}
			$potentialImages[] = $item;
		}
	}
	// If the user can see resized versions consider those too
	$ret = GalleryCoreApi::assertHasItemPermission($masterId,'core.viewResizes');
	if (!$ret) {
		list ($ret, $resizes) = GalleryCoreApi::fetchResizesByItemIds(array($masterId));
		if ($ret) {
			return array($ret,null);
		}
		if (!empty($resizes)) {
			foreach ($resizes[$masterId] as $resize) {
				$potentialImages[] = $resize;
			}
 		}
	}
	//can always use the thumbnail
	list($ret,$thumbs) = GalleryCoreApi::fetchThumbnailsByItemIds( array($masterId) );
	if ($ret) {
		return array ($ret,null);
	}
	$potentialImages[] = $thumbs[$masterId];

	//true if maxDimensions are taller/narrower than image, in which case width is the constraint:
	$widthbound = ( !$maxImageHeight || $potentialImages[0]->height * $maxImageWidth < $potentialImages[0]->width * $maxImageHeight ) ? 1 : 0;

	usort($potentialImages, "byWidth");

	if ( $maxImageWidth &&  $widthbound ) {
		foreach ($potentialImages as $potentialImage) {
		if ($potentialImage->width >= $maxImageWidth) {
			return array ( null, $potentialImage);	//return the first one wider than $maxImageWidth
		}
			}
	}
	elseif ( $maxImageHeight ) {
		foreach ($potentialImages as $potentialImage) {
		if ($potentialImage->height >= $maxImageHeight) {
			return array ( null, $potentialImage);	//return the first one taller than $maxImageHeight
			}
		}
	}
	$bestImage=array_pop($potentialImages);
	return array( null,  $bestImage);			//none of them big enough - use the largest
}

function byWidth($a, $b) {
	if ($a->width == $b->width) return 0;
		return ($a->width < $b->width ) ? -1 : 1;
}

function cdata($text) {
	return '<![CDATA[' . $text . ']]>';
}

function xml() {
	init();
	global $gallery;
	$title = '';
	$userId = $gallery->getActiveUserId();
	if (isset ($_REQUEST['mode'])) {
		$mode = $_REQUEST['mode'];
	}
	if (isset ($_REQUEST['g2_itemId'])) {
		$g2_itemId = $_REQUEST['g2_itemId'];
		list ($ret, $item) = GalleryCoreApi::loadEntitiesById($g2_itemId, 'GalleryAlbumItem');
		if ($ret) {
			print "Error loading initial item:" . $ret->getAsHtml();
		}
		$title = getTitle($item);
	} else {
		$title = "XML Mini SlideShow for Gallery2";
	}

	if (isset ($_REQUEST['g2_view'])) {
		$g2_view = $_REQUEST['g2_view'];
	}
	$xml = '';
	$urlGenerator =& $gallery->getUrlGenerator();
	$link = $urlGenerator->generateUrl(array(), array('forceFullUrl' => true));
	$vm = $gallery->getPhpVm();
	list ($ret, $language) = GalleryTranslator::getDefaultLanguageCode( );
	if ($ret) {
		$language = "en-us";
	}
	if (!$vm->headers_sent()) {
		$vm->header('Content-Type: application/rss+xml; charset=UTF-8');
	}
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	$xml .= "<rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss/\">\n";
	$xml .= "    <channel>\n";
	$xml .= "        <title>" . cdata($title) . "</title>\n";
	$xml .= "        <link>" . $link . "</link>\n";
	$xml .= "        <description>" . cdata($title) . "</description>\n";
	$xml .= "        <language>" .$language. "</language>\n";
	$xml .= "        <generator>FlashYourWeb MediaRSS Generator v2.0.3</generator>\n";
	$xml .= "        <lastBuildDate>" . date('r', $vm->time()) . "</lastBuildDate>\n";
	$xml .= "        <ttl>120</ttl>\n";
	$xml .= getAlbumList ($g2_itemId);
	switch ($mode) {
		case 'dynamic':
			switch ($g2_view) {
				case 'dynamicalbum.UpdatesAlbum':
					$xml .= getDynamicChildIds($userId);
				break;
				case 'dynamicalbum.PopularAlbum':
					$xml .= getDynamicChildIds($userId, 'views', 'viewCount', ORDER_DESCENDING, 'GalleryItemAttributesMap', 'itemId');
				break;
				case 'dynamicalbum.RandomAlbum':
					$xml .= getDynamicChildIds($userId, 'random', 'random', ORDER_ASCENDING, null, 'id');
				break;
				case 'keyalbum.KeywordAlbum':
					$xml .= getKeywordChildIds($userId, $g2_keyword=null);
				break;
				case 'tags.VirtualAlbum':
					$xml .= getTagChildIds($userId, $g2_tagName=null);
				break;
				default:
					$xml .= getDynamicChildIds($userId);
			}
		break;
		default:
			if(isset($g2_itemId)) {
				$xml .= getItems($g2_itemId);
			} else {
				$xml .= getItems(getRoot());
			}
	}
	$xml .= "    </channel>\n";
	$xml .= "</rss>";
	echo $xml;
}

xml();
?>