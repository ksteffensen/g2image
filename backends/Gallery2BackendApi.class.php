<?php
// gallery2_backend.class.php    2007-12-01 14:38 rev. aob
/*
 * Copyright (C) 2005 Andres Obrero
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
	var $root = false;
	var $tree = array();
	var $album = array();        //* normalized 
	var $items = array();

	/**	*************************
	  * for PHP4 compatibility
	  * there should be a function called like the class
	  * *************************
	  */
	function aBackendApi($dsn){
		__construct($dsn);
	}

	/**
	 *
	 * $this->root = rootid
	 * 
	 * $this->tree = [a1=>
	 *                    [b1=>
	 *                         [c1=>[],
	 *                          c2=>[],
	 *                          c3=>[]
	 *                         ]
	 *                     b2=>
	 *                         [d1=>[],
	 *                          d2=>[],
	 *                          d3=>[]
	 *                         ]
	 *                     ]
	 *		            a2=>
	 *                     [b3=>
	 *                         [e1=>[],
	 *                          e2=>[]
	 *                         ]
	 *                      b4=>[]
	 *                      ],
	 *                 a3=>[]
	 *                ];
	 * 
	 * @param $dsn with all needed information
	 * 	$dsn['embedded_mode']
	 * 	$dsn['gallery2_uri']
	 * 	$dsn['gallery2_path']
	 * 	$dsn['base_path']
	 * 	$dsn['use_full_path']
	 * 	$dsn['embed_uri']
	 *  $dsn['album_sortby'] (optional) 
	 *  $dsn['sortby] (optional) 
	 *  $dsn['current_page'] (optional) 
	 *  $dsn['images_per_page'] (optional) 
	 * 	$dsn['root_album'] (optional) 
	 * 	$dsn['images_per_page'] (optional) 
	 * 	$dsn['userid'] ??? what if real user
	 *  $dsn urlCreateStuff
	 * @param array $album_tree (optional) 
	 * @param array $items (optional)
	 * @param array $filters (optional)
	 * 
	 * @return an object
	 * *************************
	 */
	 /*public*/ function __construct($dsn){
	 	
	 	$this->_init($dsn);
		$this->root = self::_getRootAlbumId();
		if(!$dsn['current_album']){
			$dsn['current_album'] = $this->root;
		}
	 	$this->error = array();
		$this->tree = $this->_fetchAlbumTree($this->root, 'title_asc');
		$this->items = $this->getItems($dsn['current_album'], $dsn['sortby'], $dsn['current_page'], $dsn['images_per_page']);
//		$this->album = $this->getItem($dsn['current_album']);
		
		return;

	}

	/**
	 * in PHP4 this should be called at end of code
	 */
	 /*public*/ function __destruct(){
		global $gallery;
		$ret = GalleryEmbed::done();
		self::check($ret);
	}

	/**
	 * Initialize Gallery2
	 *
	 * @param array $dsn see __construct for description
	 */
	/*private*/ function _init($dsn){
	 	
		if(!$dsn['embedded_mode'])
			$dsn['gallery2_uri'] = '/' . $dsn['gallery2_path'] . 'main.php';
		if(!$dsn['use_full_path'])
			$dsn['gallery2_path'] = $dsn['base_path'] . $dsn['gallery2_path'];

		if(file_exists($dsn['gallery2_path'].'embed.php')) {
			require_once($dsn['gallery2_path'].'embed.php');
			if ($dsn['embedded_mode']){
				$error = GalleryEmbed::init( array(
					'g2Uri' => $dsn['gallery2_uri'],
					'embedUri' => $dsn['embed_uri'],
					'fullInit' => true)
				);
			}
			else {
				$error = GalleryEmbed::init( array(
					'g2Uri' => $dsn['gallery2_uri'],
					'embedUri' => $dsn['gallery2_uri'],
					'fullInit' => true)
				);
			}

			if($error){
				self::_fatalError('<h3>Fatal Gallery2 error:</h3><br />Here\'s the error from G2:') . ' ' . $error->getAsHtml();
			}
		}
		// Else die on a fatal error
		else {
			self::_fatalError('<h3>Fatal Gallery2 Error: Cannot activate the Gallery2 Embedded functions.</h3><br />For WordPress users, Validate WPG2 in the Options Admin panel.<br /><br />For other platforms, please verify your Gallery2 path in config.php.');
		}
	 }

	/**
	 * Generate a URL given an ID
	 *
	 * @param int $id
	 * @param string $type either 'image' or 'pagelink'
	 * @return string $url
	 */
	/*private*/ function _generateUrl($id, $type) {
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
	  * in get a random thumb , there is sure a better way to get it, its just a hack
	  *
	  * *************************
	  */
	function random(){
		list ($ret, $bodyHtml, $headHtml) = GalleryEmbed::getImageBlock(array('blocks' => 'randomImage', 'show' => 'title|date'));
		preg_match("/<img.*>/i",$bodyHtml, $res);
		preg_match("/g2_itemId=([0-9]*)/i",$bodyHtml, $idres);
		$id = $idres[1];
		$img = $res[0];
		return $this->getItem($id);
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
	 /*public*/ function getItems($albumID, $sortby, $current_page, $images_per_page){

		list($child_items, $thumbnail_items, $fullsize_items, $resize_items, $id) = $this->_getChildren($albumID, $sortby, $current_page, $images_per_page);
		if (!empty($child_items)) {
			$items = $this->_normalize($child_items, $thumbnail_items, $fullsize_items, $resize_items, false);
		}
		else{
			$items = array();
		}
	 	return $items;
	 }

	/**
	 * @param $itemID
	 * @return normalized $itemObj
	 */
	/* public */ function getItem($id){
		global $gallery;
		list ($ret, $sid) = GalleryCoreApi::loadEntitiesById($id);
		self::check($ret);
		if($sid->getEntityType() =="GalleryDerivativeImage"){ // it is a derivative id
			$id = $sid->getParentId();
			list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
			//$sid = array($sid);
		}
		list ($nodes, $siblings) = $this->getDerivatives( array($id) );
		$items = $this->_normalize($nodes, null, null, null, false);
		return $items[0];
	}

	/**	*************************
	  * looks for the closest matching size
	  * @param $itemObj
	  * @param $size=72...800,
	  * @param $fit="exact/min/max",
	  * @param $direction="x/y/q" (q=quadratic tbd)
	  * @return $derivativeIndex pos in derivatives of itemObj
	  * @return $derivativePtr direct ptr to derivatives
	  * *************************
	  */
	 /*public*/ function fitInSize($itemObj, $osize=320, $fit="exact", $direction="x"){
		$picId = null;
		$hash = $itemObj["hash"];
		//special case alway max! to fit i square
		if($direction == "q"){ // not very elegant...
			krsort($hash["x"]); //reverse
			krsort($hash["y"]);
			foreach($hash["x"] as $sizX=>$id){
				if($sizX<$osize){
					$picIdX = $id;
						break;
				}
			}
			foreach($hash["y"] as $sizY=>$id){
				if($sizY<$osize){
					$picIdY = $id;
						break;
				}
			}
			if( !( isset($picIdY) || isset($picIdX) )  ){
				ksort($hash["x"]); //reverse
				ksort($hash["y"]);

				$keyval = each($hash["x"]);
				$picIdX = $keyval["value"];
				$sizX = $keyval["key"];
				$keyval = each($hash["y"]);
				$picIdY = $keyval["value"];
				$sizY = $keyval["key"];
			}
			if(isset($picIdX) && $sizY < $sizX){
				$orientation = "x" ;
				$picId = $picIdX;
				$siz = $sizX;
			}else{
				$orientation = "y" ;
				$picId = $picIdY;
				$siz = $sizY;
			}

		}else{
			$sizes = $hash[$direction];
			switch($fit){
				case "max":
					$sorty = $sizes;
					krsort($sorty); //reverse
					foreach($sorty as $siz => $pic){
						if($siz <= $osize){
							$picId = $pic;
							break;
						}
					}
				break;
				case "min":
				case "exact":
					foreach($sizes as $siz => $pic){
						if($siz >= $osize){
							$picId = $pic;
							break;
						}
					}
					if(!isset($picId)){
						$picId = $pic; // take the largest
					}
				break;
			}
			$orientation = $direction ;
		}
	 	return array($picId, $siz, $orientation, $hash);
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
	/*private */ function _getChildren($id, $sortby=null, $current_page=null, $images_per_page=null) {
		global $gallery;
		list ($ret, $sid) = GalleryCoreApi::loadEntitiesById($id);
		self::check($ret);

		// first check for thumb, then for image
		if($sid->getEntityType() =="GalleryDerivativeImage"){ // it is a derivative id
			$id = $sid->getParentId();
			list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
			//$sid = array($sid);
		}
		if($sid->getEntityType()=="GalleryPhotoItem"){ // it is an image, so get the album parent and later all sibling
			$id = $sid->getParentId();
			list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
		}

		list ($ret, $child_ids) = GalleryCoreApi::fetchChildDataItemIds($sid);
		self::check($ret);
		
		if (!empty($child_ids)) {
			list ($ret, $child_items) = GalleryCoreApi::loadEntitiesById($child_ids);
			self::check($ret);
			if ($sortby) {
				$child_ids = $this->_sortItems($child_items, $sortby);
				$reload_items = true;
			}
			if ($current_page && $images_per_page) {
				$child_ids = $this->_cutItems($child_ids, $current_page, $images_per_page);
				$reload_items = true;
			}
			if ($reload_items) {
				list ($ret, $child_items) = GalleryCoreApi::loadEntitiesById($child_ids);
			}
			list ($ret, $thumbnail_items, $fullsize_items, $resize_items) = $this->fetchAllVersionsByItemIds($child_ids);
			self::check($ret);
			return array($child_items, $thumbnail_items, $fullsize_items, $resize_items, $id); // id may differ if it is a derivative $id given as param
		}
		else {
			return array(null, null, null, null);			
		}
	}

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
	
	function _byTitleAsc($a, $b) {
		$a_title = strtolower($a->title);
		$b_title = strtolower($b->title);
		if ($a_title == $b_title) return 0;
		return ($a_title < $b_title ) ? -1 : 1;
	}
	
	function _byTitleDesc($a, $b) {
		$a_title = strtolower($a->title);
		$b_title = strtolower($b->title);
		if ($a_title == $b_title) return 0;
		return ($a_title > $b_title ) ? -1 : 1;
	}
	
	function _byOrigTimeAsc($a, $b) {
		$a_orig_time = $a->originationTimestamp;
		$b_orig_time = $b->originationTimestamp;
		if ($a_orig_time == $b_orig_time) return 0;
		return ($a_orig_time < $b_orig_time ) ? -1 : 1;
	}
	
	function _byOrigTimeDesc($a, $b) {
		$a_orig_time = $a->originationTimestamp;
		$b_orig_time = $b->originationTimestamp;
		if ($a_orig_time == $b_orig_time) return 0;
		return ($a_orig_time > $b_orig_time ) ? -1 : 1;
	}
		
	function _byModTimeAsc($a, $b) {
		$a_mod_time = $a->modificationTimestamp;
		$b_mod_time = $b->modificationTimestamp;
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time < $b_mod_time ) ? -1 : 1;
	}
	
	function _byModTimeDesc($a, $b) {
		$a_mod_time = $a->modificationTimestamp;
		$b_mod_time = $b->modificationTimestamp;
		if ($a_mod_time == $b_mod_time) return 0;
		return ($a_mod_time > $b_mod_time ) ? -1 : 1;
	}
		
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
	  * fetch all $items derivatives of an array of ids
	  * *************************
	  */
	/*private */ function getDerivatives($ids){
		list ($ret, $derivatives) = GalleryCoreApi::fetchDerivativesByItemIds($ids);
		self::check($ret);
		list ($ret, $items) = GalleryCoreApi::loadEntitiesById($ids);
		self::check($ret);

		// create reusable array with items separated by type (albums, images, mp3 ...)
		$all = array();
		$siblings = array();
		$cnt =0;
		foreach($items as $id=>$item){
				// this hack may lead in some future to probles, if gallery2 add an same_name xxxderivatives object!!
					$item->xxxderivatives = $derivatives[$item->getId()]; // merge derivatives to each item

				$all[$item->getEntityType()][] = $item;

				$iid = $item->getId();
				$siblings[$iid] = array( "pos"=>$cnt++, "id"=>$iid, "entityType"=>$item->getEntityType());
		}
		return array($all, $siblings);
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
			$data["id"] = $id;
			$data["title"] = $item->getTitle();
			$data["parentId"] = $item->getParentId();
			$data["ownerId"] = $item->getOwnerId();
			if (!empty($thumbnails[$id])) {
				$data["thumbnail_id"] = $thumbnails[$id]->getId();
				$data["thumbnail_img"] = $this->_generateUrl($data["thumbnail_id"], 'image');;
				$data["thumbnail_width"] = $thumbnails[$id]->getWidth();
				$data["thumbnail_height"] = $thumbnails[$id]->getHeight();
			}
			if (!empty($fullsizes[$id])) {
				$urlId = $fullsizes[$id]->getid();
				$data['fullsize_id'] = $urlId;
				$data['fullsize_img'] = $this->_generateUrl($urlId, 'image');
				$data['fullsize_width'] = $fullsizes[$id]->getWidth();
				$data['fullsize_height'] = $fullsizes[$id]->getheight();
			}
			else {
				$urlId = $data['id'];
			}
			$data['image_url'] = $this->_generateUrl($urlId, 'pagelink');

			$data["name"] = $item->getPathComponent();

			// just copy the data from gallery2
			$data["keywords"] = $item->getKeywords();
			$data["summary"] = $item->getSummary();
			$data["description"] = $item->getDescription();

			$data["creationTimestamp"] = $item->getCreationTimestamp();
			$data["originationTimestamp"] = $item->getOriginationTimestamp();
			$data["modificationTimestamp"] = $item->getModificationTimestamp();
			$data["viewedSinceTimestamp"] = $item->getViewedSinceTimestamp();
			$data["serialNumber"] = $item->getSerialNumber();
			$data["entityType"] = $item->getEntityType();
			if ($data["entityType"] != "GalleryAlbumItem") {
				$data["mimeType"] = $item->getMimeType();
			} else {
				$data["mimeType"] = "Album";
			}
			$data["isAlbum"] = $item->getCanContainChildren();
							
			list ($ret, $data["realpath"]) = $item->fetchPath();
			self::check($ret);
			
			$xhash = array();
			$yhash = array();
			$derivatives = array();
			$versions = array();
			if (!empty($thumbnails[$id])) {
				$version = $thumbnails[$id];
				$normalized_version = $this->_normalizeVersion($version);
				$xhash[$normalized_version['width']] = $id;
				$yhash[$normalized_version['height']] = $id;			
				$versions[$normalized_version['id']] = $normalized_version;
			}
			if (!empty($resizes[$id])) {
				foreach($resizes[$id] as $version){
					$normalized_version = $this->_normalizeVersion($version);
					$xhash[$normalized_version['width']] = $id;
					$yhash[$normalized_version['height']] = $id;			
					$versions[$normalized_version['id']] = $normalized_version;
				}
			}
			if (!empty($fullsizes[$id])) {
				$version = $fullsizes[$id];
				$normalized_version = $this->_normalizeVersion($version);
				$xhash[$normalized_version['width']] = $id;
				$yhash[$normalized_version['height']] = $id;			
				$versions[$normalized_version['id']] = $normalized_version;
			}
			
			ksort($xhash);
			ksort($yhash);
			$data["hash"]["x"] = $xhash;
			$data["hash"]["y"] = $yhash;
			$data["versions"] = $versions;
			
			$norm[] = $data;
		}

		return $norm;
	}
	
	function _normalizeVersion($version) {
		$id = $version->getId();
		$w = $version->getWidth();
		$h = $version->getHeight();
		$sized = array();
		$url['image'] = $this->_generateUrl($id, 'image');
		$url['pagelink'] = $this->_generateUrl($id, 'pagelink');
		// php_path is needed for modifying images by external applications
		list ($ret, $php_path) = $version->fetchPath();
		$entityType =  $version->getEntityType();
		if ($entityType != "GalleryAlbumItem") {
			$mimeType = $version->getMimeType();
		} else {
			$mimeType = "Album";
		}
		$normalized_version = array( "id"=>$id, "url"=>$url, "width"=>$w, "height"=>$h, 'entity_type'=>$entityType, 'mime_type'=>$mimeType, "php_path"=>$php_path );
		return $normalized_version;
	}

	/**	*************************
	  *	gallery2 does not provied an correct original image for an album
	  * therefore i grab the first derivative , which always exists (i hope so)
	  * and get the parentId and its derivatives
	  *
	  * @param: $node of an album
	  * @return:
	  *		$realpath points to the original image
	  *		$derrr array of derivatives
	  * *************************
	  */
	function getDerivativesForAlbum($node){
		global $gallery;
		if(isset($node->xxxderivatives[0])){
			$derivativeSourceId = $node->xxxderivatives[0]->getDerivativeSourceId();
			list ($ret, $albumSourceImage) = GalleryCoreApi::loadEntitiesById(array($derivativeSourceId));
			self::check($ret);
			$originalImage = $albumSourceImage[0]->getParentId();
			list ($ret, $derivatives2) = GalleryCoreApi::fetchDerivativesByItemIds(array( $originalImage ));
			self::check($ret);
			$derrr = $derivatives2[$originalImage];
			list ($ret, $albumSourceItem) = GalleryCoreApi::loadEntitiesById(array($originalImage));
			list ($ret, $realpath) = $albumSourceItem[0]->fetchPath();
			self::check($ret);
		}
		return array( $realpath, $derrr );
	}

	//=================================================
	// private helper functions
	//=================================================

	/**
	  * tree
	  */
	function _fetchAlbumTree($base, $sortby) {

		list($error, $album_tree) = GalleryCoreApi::fetchAlbumTree($base);
		self::check($error, 'Error in GalleryCoreApi::fetchAlbumTree - ');
		list($error, $album_tree_ids) = GalleryCoreApi::fetchAllItemIds('GalleryAlbumItem');
		self::check($error,  'Error in GalleryCoreApi::fetchAllItemIds - ');
		list ($error, $tree_items) = GalleryCoreApi::loadEntitiesById($album_tree_ids);
		self::check($error, 'Error in GalleryCoreApi::loadEntitiesById - ');
		$tree = $this->_normalizeTree($base, $album_tree, $tree_items, $sortby);

		return $tree;
	}

	/**
	  * normalize tree
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
		$normalized_album_tree['sorted_by'] = $sortby;
		$normalized_album_tree[$base]['title'] = $tree[$base]['title'];
		if(count($album_tree)>0){
			$normalized_album_tree[$base]['children'] = $this->_normalizeTreeBranches($album_tree, $tree);
		}
		return $normalized_album_tree;
	}
	
	/*
	 * normalize tree branches
	 */
	function _normalizeTreeBranches($album_tree, $tree){
		foreach($album_tree as $album => $branch) {
			$normalized_album_tree[$album]['title'] = $tree[$album]['title'];
			$normalized_album_tree[$album]['creationTimestamp'] = $tree[$album]['creationTimestamp'];
			$normalized_album_tree[$album]['modificationTimestamp'] = $tree[$album]['modificationTimestamp'];
			if(count($branch)>0){
				$normalized_album_tree[$album]['children'] = $this->_normalizeTreeBranches($branch, $tree);
			}
		}
		return $normalized_album_tree;
	}
	
	/**
	  * tree
	  */
	function _getRootAlbumId() {
		// Check for G2 Core API >= 7.5.  getDefaultAlbumId only available at 7.5 or above
		if (GalleryUtilities::isCompatibleWithApi(array(7,5), GalleryCoreApi::getApiVersion())) {
			list($error, $root_album_id) = GalleryCoreApi::getDefaultAlbumId();
		}
		// Otherwise use a Gallery2 2.1 method to get the root album
		else {
			list($error, $root_album_id) = GalleryCoreApi::getPluginParameter('module', 'core', 'id.rootAlbum');
		}
		if ($error) {
			print T_('Error getting root album ID:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $root_album_id;
		}
	}
	//=================================================
	//=================================================
	//=================================================


	//=================================================
	//=================================================
	//=================================================
	/**
	  * output error message of gallery2
	  * @param
	  * @return just die
	  */
	function check($ret, $str="Error: ") {
		global $gallery;
	    if ($ret){
	    	$this->error[] = $str . " ". $ret->getAsHtml();
	    	self::_fatalError($str . " ". $ret->getAsHtml());
		}
	}
	
	/**
	  *
	  */
	function _fatalError($str){
		require_once('header.class.php');
		$header = new g2ic_header($g2ic_options);
		echo $header->html;
		if(function_exists("T_")){ 
			echo T_($str) . "\n";
		}
		else { 
			echo $str . "\n"; 
		}
		echo '</body>
		</html>';
		flush();
		die;
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
	function fetchAllVersionsByItemIds($ids) {
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
		
	/**
	 * Fetch all image versions (thumbnails, fullsizes, and resizes) for an album.  
	 * 
	 * @param int $albumId
	 * @return array($ret, $childItemIds, $thumbnailImageItems, $fullsizeImageItems, $resizeImageItems);
	 *     $ret = Gallery2 Error Object
	 *     $childItemIds = array of child item IDs for use as keys to the image items arrays
	 *     $thumbnailImageItems = array of thumbnail items with $childItemIds as keys
	 *     $fullsizeImageItems = array of preferred items (original items if preferred 
	 *                           not present for a given ID) with $childItemIds as keys
	 *     $resizeImageItems = array of resize items with $childItemIds as keys.  There may be multiple
	 *                         resizes for a given ID
	 */
	 function fetchAllChildImageItemsForAlbum($albumId) {
		list ($ret, $albumItem) = GalleryCoreApi::loadEntitiesById($albumId);
		if($ret) {
			return array ($ret, null, null, null, null);  // Exit, returning the error
		}  
		list ($ret, $childItemIds) = GalleryCoreApi::fetchChildDataItemIds($albumItem);
		if($ret) {
			return array ($ret, null, null, null, null);
		}  
		list ($ret, $thumbnailImageItems, $fullsizeImageItems, $resizeImageItems) = self::fetchAllVersionsByItemIds($childItemIds);
		return array(null, $childItemIds, $thumbnailImageItems, $fullsizeImageItems, $resizeImageItems);
	}	
}
?>