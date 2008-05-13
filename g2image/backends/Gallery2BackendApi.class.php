<?php
// Gallery2BackendApi.class.php    2007-12-11
/*
 * Copyright (C) 2007 Andres Obrero and Kirk Steffensen
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

class Gallery2BackendApi{
	var $tree = array();
	var $album = array();
	var $totalAvailableDataItems = 0;
	var $dataItems = array();
	var $albumItems = array();
	var $itemSortMethod = array();
	var $albumSortMethod = array();
	var $error = false;
	var $messages = array();

	//=================================================
	// Public functions
	//=================================================

	/**
	 * PHP4 compatibility class constructor
	 *
	 * @param array $dsn see __construct for details
	 */
	function Gallery2BackendApi($dsn, $album_tree=null, $data_items=null, $totalAvailableDataItems=null, $album_items=null, $filters=null){
		Gallery2BackendApi::__construct($dsn, $album_tree=null, $data_items=null, $totalAvailableDataItems=null, $album_items=null, $filters=null);
		if ($this->error) {return;}
	}

	/**
	 * Construct the Gallery2BackendApi object
	 * 
	 * @param $dsn with all needed information
	 * 	$dsn['embedded_mode']
	 * 	$dsn['gallery2_uri']
	 * 	$dsn['gallery2_path']
	 * 	$dsn['base_path']
	 * 	$dsn['use_full_path']
	 * 	$dsn['embed_uri']
	 *  $dsn['album_sortby'] (optional) // How to sort the album tree and albumItems
	 *  $dsn['build_all_data_items'] (optional)
	 *  $dsn['build_all_album_items'] (optional)  // Overrides build_child_album_items
	 *  $dsn['build_child_album_items'] (optional)
	 *  $dsn['sortby] (optional)   // How to sort dataItems
	 *  $dsn['current_page'] (optional) 
	 *  $dsn['images_per_page'] (optional) 
	 * 	$dsn['images_per_page'] (optional) 
	 * 	$dsn['gallery2_root_album'] (optional) 
	 * @param array $album_tree (optional) 
	 * @param array $data_items (optional)
	 * @param array $album_items (optional)
	 * @param array $filters (optional)      // Not implemented.  Included for future expansion
	 * 
	 * @return an object
	 * $this->tree = album tree array
	 *    tree = [root ID 
	 *               [title,
	 *                source_image_id,
	 *                sorted_by,
	 *                children 
	 *                   [id
	 *                       [title,
	 *                        creationTimestamp,
	 *                        modificationTimestamp,
	 *                        source_image_id,
	 *                        children
	 *                           [id
	 *                               [title,
	 *                                creationTimestamp,
	 *                                modificationTimestamp,
	 *                                source_image_id
	 *                               ]
	 *                           ]
	 *                       ]
	 *                   ],
	 *                   [id
	 *                       [title,
	 *                        creationTimestamp,
	 *                        modificationTimestamp,
	 *                        source_image_id,
	 *                        children
	 *                           [id
	 *                               [title,
	 *                                creationTimestamp,
	 *                                modificationTimestamp,
	 *                                source_image_id
	 *                               ]
	 *                           ]
	 *                       ]
	 *                   ]
	 *               ],
	 *           ]
	 * $this->album = normalized item for current album
	 * $this->dataItems = array of normalized child data items for current album (or ALL data items
	 *                    in the entire gallery if build_all_data_items is true)
	 * $this->albumItems = array of normalized child album items for current album (only built if 
	 *                     build_child_album_items is true.  Will have ALL album items
	 *                     in the entire gallery if build_all_album_items is true)
	 * $this->error = error returned during object construction
	 * *************************
	 */
	function __construct($dsn, $album_tree=null, $data_items=null, $totalAvailableDataItems=null, $album_items=null, $filters=null){

		$this->_init($dsn);
		if ($this->error) {return;}
		if (!isset($dsn['gallery2_root_album'])) {
		 	list($ret, $root) = $this->getRootAlbumId();
			$this->_check($ret);
			if ($this->error) {return;}
		}
		else {
			$root = $dsn['gallery2_root_album'];
		}
		if(!isset($dsn['current_album'])) {
			$dsn['current_album'] = $root;
		}
		if (!$album_tree) {
			list($ret, $this->tree) = $this->getAlbumTree($root, $dsn['album_sortby']);
			$this->_check($ret);
			if ($this->error) {return;}
		}
		else {
			$this->tree = $album_tree;
		}
		if (!$data_items) {
			list($ret, $this->dataItems, $this->totalAvailableDataItems) = $this->getItems($dsn['current_album'], $dsn['sortby'], $dsn['current_page'], $dsn['images_per_page'], 'data', $dsn['build_all_data_items']);
			$this->_check($ret);
			if ($this->error) {return;}
		}
		else {
			$this->dataItems = $data_items;
			$this->totalAvailableDataItems = $totalAvailableDataItems;
		}
		if (!$album_items) {
			if ($dsn['build_all_album_items']) {
				list($ret, $this->albumItems) = $this->getItems(null, $dsn['album_sortby'], null, null, 'album', true);
				$this->_check($ret);
				if ($this->error) {return;}
			}
			elseif ($dsn['build_child_album_items']) {
				list($ret, $this->albumItems) = $this->getItems($dsn['current_album'], $dsn['album_sortby'], null, null, 'album', false);
				$this->_check($ret);
				if ($this->error) {return;}
			}
		}
		else {
			$this->albumItems = $album_items;
		}
		list($ret, $album) = $this->getItemsByIds(array($dsn['current_album']));
		$this->_check($ret);
		$this->album = $album[$dsn['current_album']];
		$this->itemSortMethod = $this->getItemSortMethod();
		$this->albumSortMethod = $this->getAlbumSortMethod();
		
		return;

	}

	/**
	 * in PHP4 this should be called at end of code
	 */
	function __destruct(){
		global $gallery;
		$ret = GalleryEmbed::done();
		$this->_check($ret);
	}

	/**	*************************
	  * @param $albumID
	  *
	  * item[
	  *		  id, parentId, ownerId,
	  *		  "name", "title", "summary", "description", "keywords",
	  *		  "entityType" (item or album), canContainChildren
	  *		  creationTimestamp, modificationTimestamp,viewedSinceTimestamp,
	  *		  serialNumber, fullsize_img (/url), width, height
	  *		  hash["x"=>[size1=>id1, size2=>id2, size3=>id3],"y"=>[size1=>id1, size2=>id2, size3=>id3] ]
	  *		  derivatives[id1=>[id,width,height,urls["key"=>url, "key"=>url] ], id2=>[...]
	  *		  		urls key are 'pagelink', 'image', "php"
	  *     ]
	  *
	  * @param $ambumID
	  * @return array $items
	  * *************************
	  * *************************
	  * *************************
	  * *************************
	  */
	function getItems($albumID, $sortby=null, $current_page=null, $images_per_page=null, $child_type='data', $get_all=false){

		list($ret, $child_items, $thumbnails, $fullsizes, $resizes, $id, $total_number_child_items) = Gallery2BackendApi::_getChildren($albumID, $sortby, $current_page, $images_per_page, $child_type, $get_all);
		if ($ret) {return array($ret, null);}
		if (!empty($child_items)) {
			list ($ret, $items) = Gallery2BackendApi::_normalize($child_items, $thumbnails, $fullsizes, $resizes);
			if ($ret) {return array($ret, null);}
		}
		else{
			$items = array();
		}
		return array(null, $items, $total_number_child_items);
	}

	/**
	 * @param array $ids
	 * @return array normalized $itemObj
	 */
	function getItemsByIds($ids){
		global $gallery;
		list ($ret, $items) = GalleryCoreApi::loadEntitiesById($ids);
		if ($ret) {return array($ret, null);}
		foreach ($items as $key=>$item)
			if ($item->getEntityType() == "GalleryDerivativeImage") { // it is a derivative id
				$ids['$key'] = $item->getParentId();
				list ($ret, $items['$key']) =GalleryCoreApi::loadEntitiesById($ids['$key']);
				if ($ret) {return array($ret, null);}
			}
			if ($item->getEntityType() == "GalleryAlbumItem") { // it is an album
				list ($ret, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array($ids[$key]));
				if ($ret) {return array($ret, null);}
				if (!empty($thumbnails)) {			
					$derivativeSourceId = $thumbnails[$ids[$key]]->getDerivativeSourceId();
					list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
					if ($ret) {return array($ret, null);}
					$ids[$key] = $albumSourceImage[0]->getParentId();
				}
			}
		list ($ret, $thumbnails, $fullsizes, $resizes) = Gallery2BackendApi::_fetchAllVersionsByItemIds($ids);
		if ($ret) {return array($ret, null);}
		if (!empty($items)) {
			list ($ret, $normalized_items) = Gallery2BackendApi::_normalize($items, $thumbnails, $fullsizes, $resizes);
			if ($ret) {return array($ret, null);}
		}
		else{
			$normalized_items = array();
		}
	 	return array(null, $normalized_items);
	}

	/**
	 * Generate album tree array
	 *
	 * @param integer $base
	 * @param string $sortby
	 * @return object
	 */
	function getAlbumTree($base, $sortby) {

		list ($ret, $album_tree) = GalleryCoreApi::fetchAlbumTree($base);
		if ($ret) {return array($ret, null);}
		list ($ret, $album_tree_ids) = GalleryCoreApi::fetchAllItemIds('GalleryAlbumItem');
		if ($ret) {return array($ret, null);}
		list ($ret, $tree_items) = GalleryCoreApi::loadEntitiesById($album_tree_ids);
		if ($ret) {return array($ret, null);}
		list ($ret, $tree) = Gallery2BackendApi::_normalizeTree($base, $album_tree, $tree_items, $sortby);
		if ($ret) {return array($ret, null);}
		return array(null, $tree);
	}

	/**
	 * Get the best fit image ID from an normalized item's imageVersions array
	 * Best fit is equal to or larger than the dimensions given so that it can 
	 * be shrunk in the browser, unless $getEqualOrLarger is false.  In that case
	 * the best fit is equal to or smaller than the dimensions.
	 *
	 * @param array $item
	 * @param int $maxImageWidth
	 * @param int $maxImageHeight
	 * @param int $getEqualOrLarger
	 * @return int $id
	 */
	function getBestFit($item, $maxImageWidth, $maxImageHeight, $getEqualOrLarger=true) {
		// Get the height and width of the largest available imageVersion.  This is
		// because the thumbnail can be square.  If there is only a thumbnail, then it is
		// the largest available image.  But if there are multiple imageVersions, the 
		// largest image is most likely not the thumbnail.
		$hash_x = $item['imageHash']['x'];
		$largest_id = array_pop($hash_x);
		$imageWidth = $item['imageVersions'][$largest_id]['width'];
		$imageHeight = $item['imageVersions'][$largest_id]['height'];
		$hash_x = $item['imageHash']['x'];
		$hash_y = $item['imageHash']['y'];
		if (!$getEqualOrLarger) {
			$hash_x = array_reverse($hash_x, true);
			$hash_y = array_reverse($hash_y, true);
		}
				
		// true if maxDimensions are taller/narrower than image, in which case width is the constraint:
		$widthbound = ( !$maxImageHeight || $imageHeight * $maxImageWidth < $imageWidth * $maxImageHeight ) ? 1 : 0;
		
		if ( $maxImageWidth &&  $widthbound ) {
			foreach ($hash_x as $width=>$id) {
				if ($getEqualOrLarger) {
					if ($width >= $maxImageWidth) {
						return $id;	//return the first one equal to or wider than $maxImageWidth
					}
				}
				else {
					if ($width <= $maxImageWidth) {
						return $id;	//return the first one equal to or narrower than $maxImageWidth
					}
				}
			}
		}
		elseif ( $maxImageHeight ) {
			foreach ($hash_y as $height=>$id) {
				if ($getEqualOrLarger) {
					if ($height >= $maxImageHeight) {
						return $id;	//return the first one equal to or taller than $maxImageHeight
					}
				}
				else {
					if ($height <= $maxImageHeight) {
						return $id;	//return the first one equal to or shorter than $maxImageHeight
					}
				}
			}
		}
		// If no other image ID has already been returned, return the ID of the largest/smallest image.
		return array_pop($hash_x);	
	}

	/**
	 * Get the root album ID
	 *
	 * @return array(GalleryErrorObject $ret, int $root_album_id)
	 */
	function getRootAlbumId() {
		// Check for G2 Core API >= 7.5.  getDefaultAlbumId only available at 7.5 or above
		if (GalleryUtilities::isCompatibleWithApi(array(7,5), GalleryCoreApi::getApiVersion())) {
			list($ret, $root_album_id) = GalleryCoreApi::getDefaultAlbumId();
		}
		// Otherwise use a Gallery2 2.1 method to get the root album
		else {
			list($ret, $root_album_id) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.rootAlbum');
		}
		if ($ret) {
			return array($ret, null);
		}
		else {
			return array(null, $root_album_id);
		}
	}
	
	function getItemSortMethod() {
		$itemSortMethod = array('title_asc' => array('text' => T_('Gallery2 Title (A-z)')),
			'title_desc' => array('text' => T_('Gallery2 Title (z-A)')),
			'orig_time_desc' => array('text' => T_('Origination Time (Newest First)')),
			'orig_time_asc' => array('text' => T_('Origination Time (Oldest First)')),
			'mtime_desc' => array('text' => T_('Last Modification (Newest First)')),
			'mtime_asc' => array('text' => T_('Last Modification (Oldest First)')));
		return $itemSortMethod;
	}

	function getAlbumSortMethod() {
		$itemSortMethod = array('title_asc' => array('text' => T_('Gallery2 Title (A-z)')),
			'title_desc' => array('text' => T_('Gallery2 Title (z-A)')),
			'create_time_desc' => array('text' => T_('Creation Time (Newest First)')),
			'create_time_asc' => array('text' => T_('Creation Time (Oldest First)')),
			'mtime_desc' => array('text' => T_('Last Modification (Newest First)')),
			'mtime_asc' => array('text' => T_('Last Modification (Oldest First)')),
			'' => array('text' => T_('None (G2 manual sort order)')));
		return $itemSortMethod;
	}

	//=================================================
	// Private functions
	//=================================================

	/**
	 * Initialize Gallery2
	 *
	 * @param array $dsn see __construct for description
	 */
	function _init($dsn){
		
		if(!$dsn['embedded_mode'])
			$dsn['gallery2_uri'] = '/' . $dsn['gallery2_path'] . 'main.php';
		if(!$dsn['use_full_path'])
			$dsn['gallery2_path'] = $dsn['base_path'] . $dsn['gallery2_path'];
			
		if(file_exists($dsn['gallery2_path'].'embed.php')) {
			require_once($dsn['gallery2_path'].'embed.php');
			if ($dsn['embedded_mode']){
				$ret = GalleryEmbed::init( array(
					'g2Uri' => $dsn['gallery2_uri'],
					'embedUri' => $dsn['embed_uri'],
					'fullInit' => true)
				);
			}
			else {
				$ret = GalleryEmbed::init( array(
					'g2Uri' => $dsn['gallery2_uri'],
					'embedUri' => $dsn['gallery2_uri'],
					'fullInit' => true)
				);
			}
			
			$this->_check($ret, '<h3>Fatal Gallery2 error:  Failed to initialize Gallery2.</h3><br />Here\'s the error from G2:');
			if ($this->error) {return;}
		}
		// Else die on a fatal error
		else {
			$this->_fatalError('<h3>Fatal Gallery2 Error: Cannot activate the Gallery2 Embedded functions because cannot locate embed.php.</h3><br />For WordPress users, Validate WPG2 in the Options Admin panel.<br /><br />For other platforms, please verify your Gallery2 path in config.php.');
			if ($this->error) {return;}
		}
	}

	/**
	 * Generate a URL given an ID
	 *
	 * @param int $id
	 * @param string $type either 'image' or 'pagelink'
	 * @return string $url
	 */
	function _generateUrl($id, $type) {
		global $gallery;
		$urlGenerator =& $gallery->getUrlGenerator();
		if ($type == 'image'){
			$view = 'core.DownloadItem';
		}
		else {
			$view = 'core.ShowItem';
		}
		$url = $urlGenerator->generateUrl(array('view' => $view, 'itemId' => $id), array('forceFullUrl' => true));
		return $url;
	}
	
	/**	*************************
	  * workhorse to get all items of an given album
	  *
	  * @param: $id is the id in gallery, the rest is just passed through to gallery2
	  * @return
	  * 	$all: an array of poiters of gallery2 items - both: images and albums, just to speed up
	  *			as special feature i add all derivatives to each item
	  * 	$siblings: all ids of the childs of $id parent
	  * *************************
	  */
	function _getChildren($id, $sortby=null, $current_page=null, $images_per_page=null, $child_type='data', $get_all=false) {
		global $gallery;
		
		if ($get_all) {
			if ($child_type == 'album') {
				list ($ret, $child_ids) = GalleryCoreApi::fetchAllItemIds('GalleryAlbumItem');
			}
			else {
				list ($ret, $photo_ids) = GalleryCoreApi::fetchAllItemIds('GalleryPhotoItem');
				if ($ret) {return array($ret, null, null, null, null, null);}
				list ($ret, $movie_ids) = GalleryCoreApi::fetchAllItemIds('GalleryMovieItem');
				if ($ret) {return array($ret, null, null, null, null, null);}
				list ($ret, $animation_ids) = GalleryCoreApi::fetchAllItemIds('GalleryAnimationItem');
				if ($ret) {return array($ret, null, null, null, null, null);}
				list ($ret, $unknown_ids) = GalleryCoreApi::fetchAllItemIds('GalleryUnknownItem');
				if ($ret) {return array($ret, null, null, null, null, null);}
				$child_ids = array_merge($photo_ids, $movie_ids, $animation_ids, $unknown_ids);
			}
		}
		else {
			list ($ret, $item) = GalleryCoreApi::loadEntitiesById($id);
			if ($ret) {return array($ret, null, null, null, null, null);}
			
			if($item->getEntityType() != "GalleryAlbumItem"){
				$id = $item->getParentId();
				list ($ret, $item) =GalleryCoreApi::loadEntitiesById($id);
				if ($ret) {return array($ret, null, null, null, null, null);}
			}
	
			if ($child_type == 'album') {
				list ($ret, $child_ids) = GalleryCoreApi::fetchChildAlbumItemIds($item);
			}
			else {
				list ($ret, $child_ids) = GalleryCoreApi::fetchChildDataItemIds($item);
			}
		}
			
		if ($ret) {return array($ret, null, null, null, null, null);}
		
		$total_number_child_items = count($child_ids);
		
		if (!empty($child_ids)) {
			list ($ret, $child_items) = GalleryCoreApi::loadEntitiesById($child_ids);
			if ($ret) {return array($ret, null, null, null, null, null);}
			if ($sortby && count($child_ids)>1) {
				$child_ids = Gallery2BackendApi::_sortItems($child_items, $sortby);
				$reload_items = true;
			}
			if ($child_type == 'album') {
				$source_image_ids = array();
				list ($ret, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds($child_ids);
				if ($ret) {return array($ret, null);}
				foreach ($child_ids as $child_id) {
					if (!empty($thumbnails[$child_id])) {			
						$derivativeSourceId = $thumbnails[$child_id]->getDerivativeSourceId();
						list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
						if ($ret) {return array($ret, null);}
						$source_image_ids[] = $albumSourceImage[0]->getParentId();
					}
					else {
						$source_image_ids[] = $child_id;
					}
				}
				$reload_items = true;
			}
			elseif ($current_page && $images_per_page) {
				$child_ids = Gallery2BackendApi::_cutItems($child_ids, $current_page, $images_per_page);
				$reload_items = true;
			}
			if ($reload_items) {
				list ($ret, $child_items) = GalleryCoreApi::loadEntitiesById($child_ids);
				if ($ret) {return array($ret, null, null, null, null, null);}
			}
			if ($child_type == 'album') {
				list ($ret, $thumbnails, $fullsizes, $resizes) = Gallery2BackendApi::_fetchAllVersionsByItemIds($source_image_ids);
			}
			else {
				list ($ret, $thumbnails, $fullsizes, $resizes) = Gallery2BackendApi::_fetchAllVersionsByItemIds($child_ids);
			}
			if ($ret) {return array($ret, null, null, null, null, null);}
			return array(null, $child_items, $thumbnails, $fullsizes, $resizes, $id, $total_number_child_items); // id may differ if it is a derivative $id given as param
		}
		else {
			return array(null, null, null, null, null, 0);			
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $items
	 * @param unknown_type $sortby
	 * @return unknown
	 */
	function _sortItems($items, $sortby) {
		switch ($sortby) {
			case 'title_asc' :
				usort($items, array ("Gallery2BackendApi","_byTitleAsc"));
				break;
			case 'title_desc' :
				usort($items, array ("Gallery2BackendApi","_byTitleDesc"));
				break;
			case 'orig_time_asc' :
				usort($items, array ("Gallery2BackendApi","_byOrigTimeAsc"));
				break;
			case 'orig_time_desc' :
				usort($items, array ("Gallery2BackendApi","_byOrigTimeDesc"));
				break;
			case 'mtime_asc' :
				usort($items, array ("Gallery2BackendApi","_byModTimeAsc"));
				break;
			case 'mtime_desc' :
				usort($items, array ("Gallery2BackendApi","_byModTimeDesc"));
		}
		$ids = array();
		foreach ($items as $item) {
			$ids[] = $item->getId();
		}
		return $ids;
		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byTitleAsc($a, $b) {
		$a_title = strtolower($a->title);
		if (empty($a_title)) {
			$a_title = strtolower($a->getPathComponent());
		}
		$b_title = strtolower($b->title);
		if (empty($b_title)) {
			$b_title = strtolower($b->getPathComponent());
		}
			if ($a_title == $b_title) return 0;
		return ($a_title < $b_title ) ? -1 : 1;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byTitleDesc($a, $b) {
		$a_title = strtolower($a->title);
		if (empty($a_title)) {
			$a_title = strtolower($a->getPathComponent());
		}
		$b_title = strtolower($b->title);
		if (empty($b_title)) {
			$b_title = strtolower($b->getPathComponent());
		}
		if ($a_title == $b_title) return 0;
		return ($a_title > $b_title ) ? -1 : 1;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byOrigTimeAsc($a, $b) {
		$a_orig_time = $a->originationTimestamp;
		$b_orig_time = $b->originationTimestamp;
		if ($a_orig_time == $b_orig_time) return 0;
		return ($a_orig_time < $b_orig_time ) ? -1 : 1;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byOrigTimeDesc($a, $b) {
		$a_orig_time = $a->originationTimestamp;
		$b_orig_time = $b->originationTimestamp;
		if ($a_orig_time == $b_orig_time) return 0;
		return ($a_orig_time > $b_orig_time ) ? -1 : 1;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byModTimeAsc($a, $b) {
		$a_mod_time = $a->modificationTimestamp;
		$b_mod_time = $b->modificationTimestamp;
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time < $b_mod_time ) ? -1 : 1;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	function _byModTimeDesc($a, $b) {
		$a_mod_time = $a->modificationTimestamp;
		$b_mod_time = $b->modificationTimestamp;
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time > $b_mod_time ) ? -1 : 1;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $ids
	 * @param unknown_type $current_page
	 * @param unknown_type $images_per_page
	 * @return unknown
	 */
	function _cutItems($ids, $current_page, $images_per_page) {
		$sub_ids = array();
		foreach($ids as $key => $id) {
			if (!(($current_page-1)*$images_per_page <= $key)) { // Haven't gotten there yet
				continue;
			}
			elseif (!($key < $current_page*$images_per_page)) {
				break; // Have gone past the range for this page
			}
			$sub_ids[] = $id;
		}
		return $sub_ids;
	}
	
	/**	*************************
	  *	convert all the objects into an array and add some handy hash tables
	  *	this function can be enhanced with all the needed stuff adding to the array
	  *
	  * @param:
	  *		$nodes: array of nodes to normalize
	  *		$type: $type="GalleryAlbumItem" , "GalleryPhotoItem" or mp3 etc.
	  *		$filter: tbd for example to get only $nodes with a specified keyword or title - not yet implemented
	  * *************************
	  */
	function _normalize($items, $thumbnails, $fullsizes, $resizes, $filter=false){
		$norm = array();
		foreach($items as $item){
			$data = array();
			$id = $item->getId();
			if ($item->getEntityType() == "GalleryAlbumItem") { // it is an album
				list ($ret, $album_thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array( $id ));
				if ($ret) {return array($ret, null);}
				if (!empty($album_thumbnails)) {			
					$derivativeSourceId = $album_thumbnails[$id]->getDerivativeSourceId();
					list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
					if ($ret) {return array($ret, null);}
					$id = $albumSourceImage[0]->getParentId();
				}
			}
			$data["id"] = $item->getId();
			$data["title"] = $item->getTitle();
			$data["name"] = $item->getPathComponent();
			if (empty($data['title'])) {
				$data['title'] = $data["name"];
			}
			$data['base_item_url'] = Gallery2BackendApi::_generateUrl($data["id"], 'pagelink');
			$data["entityType"] = $item->getEntityType();
			if ($data["entityType"] != "GalleryAlbumItem") {
				$data["mimeType"] = $item->getMimeType();
			} else {
				$data["mimeType"] = "Album";
			}
			$data["parentId"] = $item->getParentId();
			$data["ownerId"] = $item->getOwnerId();
			if (!empty($thumbnails[$id])) {
				$data["thumbnail_id"] = $thumbnails[$id]->getId();
			}
			else {
				$data["thumbnail_id"] = null;
			}
			$data["keywords"] = $item->getKeywords();
			$data["summary"] = $item->getSummary();
			$data["description"] = $item->getDescription();

			$data["creationTimestamp"] = $item->getCreationTimestamp();
			$data["originationTimestamp"] = $item->getOriginationTimestamp();
			$data["modificationTimestamp"] = $item->getModificationTimestamp();
			$data["viewedSinceTimestamp"] = $item->getViewedSinceTimestamp();
			$data["serialNumber"] = $item->getSerialNumber();
			$data["isAlbum"] = $item->getCanContainChildren();
			
			if ($data["entityType"] == "GalleryAlbumItem") {
				list ($ret, $albums) = GalleryCoreApi::loadEntitiesById(array($id));
				list ($ret, $data["realpath"]) = $albums[0]->fetchPath();
				if ($ret) {return array($ret, null);}
			}
			else {
				list ($ret, $data["realpath"]) = $item->fetchPath();
				if ($ret) {return array($ret, null);}
			}
			
			$xhash = array();
			$yhash = array();
			$image_versions = array();
			if (!empty($thumbnails[$id])) {
				$image_version = $thumbnails[$id];
				$normalized_image_version = Gallery2BackendApi::_normalizeVersion($image_version);
				$xhash[$normalized_image_version['width']] = $normalized_image_version['id'];
				$yhash[$normalized_image_version['height']] = $normalized_image_version['id'];			
				$image_versions[$normalized_image_version['id']] = $normalized_image_version;
			}
			// In the future if Gallery2 adds resizes for movies or other data item
			// types, will need to do entity type test on resizes
			if (!empty($resizes[$id])) {
				foreach($resizes[$id] as $image_version){
					$normalized_image_version = Gallery2BackendApi::_normalizeVersion($image_version);
					$xhash[$normalized_image_version['width']] = $normalized_image_version['id'];
					$yhash[$normalized_image_version['height']] = $normalized_image_version['id'];			
					$image_versions[$normalized_image_version['id']] = $normalized_image_version;
				}
			}
			print $id;
			print_r($fullsizes);
			$fullsize_entity_type = $fullsizes[$id]->getEntityType();
			if (!empty($fullsizes[$id]) && (($fullsize_entity_type == 'GalleryPhotoItem') || $fullsize_entity_type == 'GalleryDerivativeImage')) {
				$image_version = $fullsizes[$id];
				$normalized_image_version = Gallery2BackendApi::_normalizeVersion($image_version);
				$xhash[$normalized_image_version['width']] = $normalized_image_version['id'];
				$yhash[$normalized_image_version['height']] = $normalized_image_version['id'];			
				$image_versions[$normalized_image_version['id']] = $normalized_image_version;
			}
			
			ksort($xhash);
			ksort($yhash);
			$data["imageHash"]["x"] = $xhash;
			$data["imageHash"]["y"] = $yhash;
			// In the future if Gallery2 adds resizes for movies or other data item
			// types, will need to do entity type test on resizes and fullsizes to build
			// other version arrays like movieVersions or animationVersions.
			$data["imageVersions"] = $image_versions;
			
			$norm[$data["id"]] = $data;
		}

		return array(null, $norm);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $version
	 * @return unknown
	 */
	function _normalizeVersion($version) {
		$id = $version->getId();
		$entityType =  $version->getEntityType();
		if ($entityType != 'GalleryUnknownItem') {
			$w = $version->getWidth();
			$h = $version->getHeight();
		}
		else {
			$w = null;
			$h = null;
		}
		$sized = array();
		$url['image'] = Gallery2BackendApi::_generateUrl($id, 'image');
		$url['pagelink'] = Gallery2BackendApi::_generateUrl($id, 'pagelink');
		// php_path is needed for modifying images by external applications
		list ($ret, $php_path) = $version->fetchPath();
		if ($entityType != "GalleryAlbumItem") {
			$mimeType = $version->getMimeType();
		} else {
			$mimeType = "Album";
		}
		$normalized_version = array( "id"=>$id, "url"=>$url, "width"=>$w, "height"=>$h, 'entity_type'=>$entityType, 'mime_type'=>$mimeType, "php_path"=>$php_path );
		return $normalized_version;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $base
	 * @param unknown_type $album_tree
	 * @param unknown_type $tree_items
	 * @param unknown_type $sortby
	 * @return unknown
	 */
	function _normalizeTree($base, $album_tree, $tree_items, $sortby){
		$tree = array();
		$normalized_album_tree[$base] = array();
		foreach($tree_items as $item){
			$id = $item->getId();
			$tree[ $id ] = array();
			$tree[ $id ]["title"] = $item->getTitle();
			if(empty($tree[ $id ]["title"])) {
				$tree[ $id ]["title"] = $item->getPathComponent();
			}
			$tree[ $id ]["creationTimestamp"] = $item->getCreationTimestamp();
			$tree[ $id ]["modificationTimestamp"] = $item->getModificationTimestamp();
			//etc... whatever needed but it should be exact as in normalize, as it can be overwritten
			// with the real normalize
		}
		$normalized_album_tree[$base]['title'] = $tree[$base]['title'];
		list ($ret, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array( $base ));
		if ($ret) {return array($ret, null);}
		if (!empty($thumbnails)) {			
			$derivativeSourceId = $thumbnails[$base]->getDerivativeSourceId();
			list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
			if ($ret) {return array($ret, null);}
			$normalized_album_tree[$base]['source_image_id'] = $albumSourceImage[0]->getParentId();
		}
		else {
			$normalized_album_tree[$base]['source_image_id'] = null;
		}
		$normalized_album_tree[$base]['sorted_by'] = $sortby;
		if(count($album_tree)>0){
			list ($ret, $normalized_album_tree[$base]['children']) = Gallery2BackendApi::_normalizeTreeBranches($album_tree, $tree, $sortby);
			if ($ret) {return array($ret, null);}
			if (count($normalized_album_tree[$base]['children'])>1) { 
				$normalized_album_tree[$base]['children'] = Gallery2BackendApi::_sortNormalizedTreeBranches($normalized_album_tree[$base]['children'], $sortby);
			}
		}
		return array (null, $normalized_album_tree);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $album_tree
	 * @param unknown_type $tree
	 * @param unknown_type $sortby
	 * @return unknown
	 */
	function _normalizeTreeBranches($album_tree, $tree, $sortby){
		foreach($album_tree as $album => $branch) {
			$normalized_album_tree[$album]['title'] = $tree[$album]['title'];
			$normalized_album_tree[$album]['creationTimestamp'] = $tree[$album]['creationTimestamp'];
			$normalized_album_tree[$album]['modificationTimestamp'] = $tree[$album]['modificationTimestamp'];
			list ($ret, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array( $album ));
			if ($ret) {return array($ret, null);}
			if (!empty($thumbnails)) {			
				$derivativeSourceId = $thumbnails[$album]->getDerivativeSourceId();
				list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
				if ($ret) {return array($ret, null);}
				$normalized_album_tree[$album]['source_image_id'] = $albumSourceImage[0]->getParentId();
			}
			else {
				$normalized_album_tree[$album]['source_image_id'] = null;
			}
			if(count($branch)>0){
				list ($ret, $normalized_album_tree[$album]['children']) = Gallery2BackendApi::_normalizeTreeBranches($branch, $tree, $sortby);
				if ($ret) {return array($ret, null);}
				if (count($normalized_album_tree[$album]['children'])>1) { 
					$normalized_album_tree[$album]['children'] = Gallery2BackendApi::_sortNormalizedTreeBranches($normalized_album_tree[$album]['children'], $sortby);
				}
			}
		}
		return array (null, $normalized_album_tree);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $items
	 * @param unknown_type $sortby
	 * @return unknown
	 */
	function _sortNormalizedTreeBranches($items, $sortby) {
		switch ($sortby) {
			case 'title_asc' :
				uasort($items, array ("Gallery2BackendApi","_albumByTitleAsc"));
				break;
			case 'title_desc' :
				uasort($items, array ("Gallery2BackendApi","_albumByTitleDesc"));
				break;
			case 'create_time_asc' :
				uasort($items, array ("Gallery2BackendApi","_albumByCreationTimeAsc"));
				break;
			case 'create_time_desc' :
				uasort($items, array ("Gallery2BackendApi","_albumByCreationTimeDesc"));
				break;
			case 'mtime_asc' :
				uasort($items, array ("Gallery2BackendApi","_albumByModTimeAsc"));
				break;
			case 'mtime_desc' :
				uasort($items, array ("Gallery2BackendApi","_albumByModTimeDesc"));
		}
		return $items;
	}
	
	function _albumByTitleAsc($a, $b) {
		$a_title = strtolower($a['title']);
		$b_title = strtolower($b['title']);
		if ($a_title == $b_title) return 0;
		return ($a_title < $b_title ) ? -1 : 1;
	}
	
	function _albumByTitleDesc($a, $b) {
		$a_title = strtolower($a['title']);
		$b_title = strtolower($b['title']);
		if ($a_title == $b_title) return 0;
		return ($a_title > $b_title ) ? -1 : 1;
	}
	
	function _albumByCreationTimeAsc($a, $b) {
		$a_create_time = $a['creationTimestamp'];
		$b_create_time = $b['creationTimestamp'];
		if ($a_create_time == $b_create_time) return 0;
		return ($a_create_time < $b_create_time ) ? -1 : 1;
	}
	
	function _albumByCreationTimeDesc($a, $b) {
		$a_create_time = $a['creationTimestamp'];
		$b_create_time = $b['creationTimestamp'];
		if ($a_create_time == $b_create_time) return 0;
		return ($a_create_time > $b_create_time ) ? -1 : 1;
	}
	
	function _albumByModTimeAsc($a, $b) {
		$a_mod_time = $a['modificationTimestamp'];
		$b_mod_time = $b['modificationTimestamp'];
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time < $b_mod_time ) ? -1 : 1;
	}
	
	function _albumByModTimeDesc($a, $b) {
		$a_mod_time = $a['modificationTimestamp'];
		$b_mod_time = $b['modificationTimestamp'];
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time > $b_mod_time ) ? -1 : 1;
	}
	
	/**
	 * If there is an error from Gallery2, convert it into HTML and put it into $this->error
	 *
	 * @param galleryErrorObject $ret
	 * @param string $str
	 */
	function _check($ret, $str="Error: ") {
		global $gallery;
		if ($ret){
			$errStr = (function_exists("T_")) ? T_($str) : $str;
			$errStr .= $ret->getAsHtml();
			$this->error = $errStr;
			$this->messages[] = array("error", $errStr, debug_backtrace());
		}
		return;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $str
	 */
	function _fatalError($str){
		$errStr = (function_exists("T_")) ? T_($str) : $str;
		$this->error = $errStr;
		$this->messages[] = array("error", $errStr, debug_backtrace());
		return;
	}
	
	/**
	 * Fetch all image versions (thumbnails, fullsizes, and resizes) for an 
	 * array of item IDs.  
	 * 
	 * The ID array should have been generated by fetchChildDataItemIds, 
	 * fetchChildDataItemIds, or fetchChildAlbumItemIds so that we know that
	 * core.view has already been checked.
	 * 
	 * @param array $ids
	 * @return array($ret, $thumbnailImageItems, $fullsizeImageItems, $resizeImageItems);
	 *     $ret = Gallery2 Error Object
	 *     $thumbnailImageItems = array of thumbnail items with $ids as keys
	 *     $fullsizeImageItems = array of preferred items (original items if preferred 
	 *                           not present for a given ID) with $ids as keys
	 *     $resizeImageItems = array of resize items with $ids as keys.  There may be multiple
	 *                         resizes for a given ID
	 */
	function _fetchAllVersionsByItemIds($ids) {
		// Given that core.view has already been checked, we can just load 
		// the thumbnails for the entire array.
		list ($ret, $thumbnailImageItems) = GalleryCoreApi::fetchThumbnailsByItemIds( $ids );
		if ($ret) {
			return array ($ret, null, null, null);
		}
		// However, we don't know if the current user has permission to see the original/preferred
		// and/or resizes for each childItemId, so we'll have to check.
		//
		// First get all of the permissions for the array of IDs.
		list ($ret, $permissions) = GalleryCoreApi::fetchPermissionsForItems( $ids );
		if ($ret) {
			return array ($ret, null, null, null);
		}
		// Next build arrays of IDs that have the viewSource and viewResizes permissions
		$idsSource = array ();
		$idsResizes = array ();
		foreach ($ids as $id) {
			if (isset($permissions[$id]['core.viewSource'])) {
				$idsSource[] = $id;
			}
			if (isset($permissions[$id]['core.viewResizes'])) {
				$idsResizes[] = $id;
			}
		}
		// Now we can load the preferreds for the viewSource ID array
		list ($ret, $fullsizeImageItems) = GalleryCoreApi::fetchPreferredsByItemIds( $idsSource );
		if ($ret) {
			return array ($ret, null, null, null);
		}
		// If there was no preferred for a given ID, we need to load the original instead
		foreach ($idsSource as $id) {
			if (empty($fullsizeImageItems[$id])) {
				list ($ret, $item) = GalleryCoreApi::loadEntitiesById($id);
				if ($ret) {
					return array ($ret, null, null, null);
				}
				$fullsizeImageItems[$id] = $item;
			}
		}
		// Now we can load the resizes for the viewResizes ID array
		list ($ret, $resizeImageItems) = GalleryCoreApi::fetchResizesByItemIds( $idsResizes );
		if ($ret) {
			return array ($ret, null, null, null);
		}
		return array(null, $thumbnailImageItems, $fullsizeImageItems, $resizeImageItems);
	}
}
?>