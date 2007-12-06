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

	/** *************************
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
	 * 	$dsn['userid'] ??? what if real user
	 *  $dsn urlCreateStuff
	 *
	 * @return an object
	 * *************************
	 */
	 /*public*/ function __construct($dsn){
	 	global $gallery;
	 	
	 	$dsn['generateUrl'] = array();
		//** here are the needed stuff to generate an url
		$dsn['generateUrl']['show'] = array('href'=>$g2ic_options["gallery2_uri"], 'view' => 'core.ShowItem', array('forceFullUrl' => true)) ;
		$dsn['generateUrl']['download'] = array('href'=>$g2ic_options["gallery2_uri"], 'view' => 'core.DownloadItem', array('forceFullUrl' => true)) ;
	 	
	 	if(!$dsn['embedded_mode'])
			$dsn['gallery2_uri'] = '/' . $dsn['gallery2_path'] . 'main.php';
		if(!$dsn['use_full_path'])
			$dsn['gallery2_path'] = $dsn['base_path'].$dsn['gallery2_path'];

		if(file_exists($dsn['gallery2_path'].'embed.php')) {
			require_once($dsn['gallery2_path'].'embed.php');
			if ($dsn['embedded_mode']){
				$dsn['g2Uri'] = $dsn['gallery2_uri'];
				$dsn['embedUri'] = $dsn['embed_uri'];
				$error = GalleryEmbed::init( array(
					'g2Uri' => $dsn['gallery2_uri'],
					'embedUri' => $dsn['embed_uri'],
					'fullInit' => true)
				);
			}
			$dsn['g2Uri'] = $dsn['gallery2_uri'];
			$error = GalleryEmbed::init( array(
				'g2Uri' => $dsn['gallery2_uri'],
				'embedUri' => $dsn['embed_uri'],
				'fullInit' => true)
			);

			if($error){
				$dsn['tinymce'] = FALSE;
				$dsn['wpg2_valid'] = FALSE;
				require_once('header.php');
				self::_fatalError('<h3>Fatal Gallery2 error:</h3><br />Here\'s the error from G2:') . ' ' . $error->getAsHtml() . "\n";
			}
			$this->generateUrlArray = $dsn['generateUrl'];
			$this->root = self::_getRootAlbumId();
			if(!$dsn['current_album']){
				$dsn['current_album'] = $this->root;
			}
			$this->tree = $this->_fetchAlbumTree($this->root, 'title');
			$this->items = $this->getItems($dsn['current_album'], $dsn['sort_by'], $dsn['current_page'], $dsn['images_per_page']);
			$this->album = $this->getItem($dsn['current_album']);
		}
		// Else die on a fatal error
		else {
			$dsn['tinymce'] = FALSE; //**hm** what does this in the api???
			$dsn['wpg2_valid'] = FALSE;
			self::_fatalError('<h3>Fatal Gallery2 Error: Cannot activate the Gallery2 Embedded functions.</h3><br />For WordPress users, Validate WPG2 in the Options Admin panel.<br /><br />For other platforms, please verify your Gallery2 path in config.php.');
		}

		return;

	}


	/**	*************************
	  * in PHP4 this should be called at end of code
	  * *************************
	  */
	 /*public*/ function __destruct(){
		global $gallery;
		$ret = GalleryEmbed::done();
		self::check($ret);
	}

	/**
	 * Get all of the Gallery2 items
	 *
	 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
	 */
	function g2ic_get_gallery_items() { //TODO turn this into a sort function
		GLOBAL $gallery, $g2ic_options;
	
		$gallery_items = array();
		$item_mod_times = array();
		$item_orig_times = array();
		$item_create_times = array();
		$item_titles = array();
		$item_ids = array();
		$album_info = array();
	
	//**hm** this actually load all childItems of an album
		$child_ids = $this->getItems( $g2ic_options['current_album'] );
	
	/**hm** this part i don't know what for
		$urlGenerator =& $gallery->getUrlGenerator();
		$album = BackendApi::loadEntityById($g2ic_options['current_album']);
	//**aa
		$album_info['url'] = $urlGenerator->generateUrl(array('view' => 'core.ShowItem', 'itemId' => $album->getid()), array('forceFullUrl' => true));
		//***i really don't know what $album_info is for?
		$album_info['title'] = $album->getTitle();
		if(empty($album_info['title'])) {
			$album_info['title'] = $album->getPathComponent();
		}
	//**hm** end part */
	
	
		foreach ($this->items as $item) {
			$item_ids[] = $item["id"];
			$item_titles[] = $item["title"];
			$item_mod_times[] = $item["modificationTimestamp"] ;
			$item_orig_times[] = $item["modificationTimestamp"] ;
			$item_create_times[] = $item["creationTimestamp"] ;
		}
		// Sort directories and files
		$count_files = count($item_ids);
	
		if($count_files>0){
			switch ($g2ic_options['sortby']) {
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
		return array($album_info, $gallery_items);
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
	  *		  		urls key are "show", "download", "php"
	  *     ]
	  *
	  * @param $ambumID
	  * @return array $items
	  * *************************
	  * *************************
	  * *************************
	  * *************************
	  */
	 /*public*/ function getItems($albumID, $sort_by, $current_page, $images_per_page){

		list($nodes, $albumID) = $this->getChildren($albumID, $sort_by, $current_page, $images_per_page);
		$items = self::normalize($nodes, "GalleryPhotoItem", false);
	 	return $items;
	 }


	/**	*************************
	  * @param $itemID
	  * @return normalized $itemObj
	  * *************************
	  */
	/* public */ function getItem($id){
		global $gallery;
		list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
		self::check($ret);
		if($sid->getEntityType() =="GalleryDerivativeImage"){ // it is a derivative id
			$id = $sid->getParentId();
			list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
			//$sid = array($sid);
		}
		list ($nodes, $siblings) = $this->getDerivatives( array($id) );
		$items = self::normalize($nodes, $sid->getEntityType() , false);
		return $items[0];
	}



	/**	*************************
	  * looks for the closest matching size of thumbs
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
	/*private */ function getChildren($id, $sort_by=null, $current_page=null, $images_per_page=null, $offset=null, $amount=null, $userID=null){
		global $gallery;
		list ($ret, $sid) =GalleryCoreApi::loadEntitiesById($id);
		self::check($ret);
//**mm
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
//**mm
		// fetch all albums and images together
		list ($ret, $child_ids) = GalleryCoreApi::fetchChildDataItemIds($sid, $offset, $amount, $userID ); //TODO fix offset/amount call
		self::check($ret);
		// now all sizes for speed up all together
		list ($typed_child_items, $siblings) = $this->getDerivatives($child_ids);
		return array($typed_child_items, $id); // id may differ if it is a derivative $id given as param
	}


	/**	*************************
	  * fetch all $items derivatives of an array of ids
	  * *************************
	  */
	/*private */ function getDerivatives($child_ids){
		list ($ret, $derivatives) = GalleryCoreApi::fetchDerivativesByItemIds($child_ids);
		self::check($ret);
		list ($ret, $items) =GalleryCoreApi::loadEntitiesById($child_ids);
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
	function normalize($nodes, $type, $filter=false){

		global $gallery;
		$urlGenerator =& $gallery->getUrlGenerator();
		$norm = array();
		if(isset($nodes[$type]) ){
			foreach($nodes[$type] as $node){
				$data = array();
				$data["id"] = $node->getId();
				$data["parentId"] = $node->getParentId();
				$data["ownerId"] = $node->getOwnerId();
				if($type == "GalleryAlbumItem"){
					list($data["realpath"], $derrr )  = self::getDerivativesForAlbum($node);
				}else{
					$derrr = $node->xxxderivatives;
					list ($ret, $data["realpath"]) = $node->fetchPath();
					self::check($ret);
				}
				$xhash = array();
				$yhash = array();
				$derivatives = array();
				foreach($derrr as $deriva){

					$id = $deriva->getId();
					$w = $deriva->getWidth();
					$h = $deriva->getHeight();
					$xhash[$w] = $id;
					$yhash[$h] = $id;
					$sized = array();
					foreach($this->generateUrlArray as $key => $urlArray){
						$urlArray["itemId"] = $deriva->getId();
						$sized[$key] = $urlGenerator->generateUrl($urlArray);
						list ($ret, $php_path) = $deriva->fetchPath();
					}
					// php_path is needed for modifying images by external applications
					$derivatives[$id] = array( "id"=>$id, "url"=>$sized, "width"=>$w, "height"=>$h, "php_path"=>$php_path );

					//extra for thumbnail
					if(!(strpos($deriva->getDerivativeOperations() , "thumbnail")===false)){
						$data["thumbnail_width"] = $w;
						$data["thumbnail_height"] = $h;
						$data["thumbnail_img"] = $sized["download"];
					}

				}

				list($err, $preferred) = GalleryCoreApi::fetchPreferredsByItemIds(array($data["id"]));
				$urlArrayImage = $this->generateUrlArray["download"];
				$urlArrayPageLink = $this->generateUrlArray["show"];
				if (!empty($preferred[$data["id"]])) {
					$urlArrayImage['itemId'] = $preferred[$data["id"]]->getid();
					$urlArrayPageLink['itemId'] = $preferred[$data["id"]]->getid();
				}else {
					$urlArrayImage['itemId'] = $data["id"];
					$urlArrayPageLink['itemId'] = $data["id"];
				}
				$data['fullsize_img'] = $urlGenerator->generateUrl($urlArrayImage);
				$data['image_url'] = $urlGenerator->generateUrl($urlArrayPageLink);
				//**mm
				if($type == "GalleryAlbumItem"){ // better take the largest todo!!!
					$data['fullsize_width'] = $node->xxxderivatives[0]->getWidth();
					$data['fullsize_height'] = $node->xxxderivatives[0]->getheight();
				}else{
					$data['fullsize_width'] = $node->getWidth();
					$data['fullsize_height'] = $node->getheight();
				}
//**mm
				ksort($xhash);
				ksort($yhash);
				$data["hash"]["x"] = $xhash;
				$data["hash"]["y"] = $yhash;
				$data["derivatives"] = $derivatives;

				$data["name"] = $node->getPathComponent();

				// just copy the data from gallery2
				$data["keywords"] = $node->getKeywords();
				$data["summary"] = $node->getSummary();
				$data["description"] = $node->getDescription();

				$data["title"] = $node->getTitle();
				$data["creationTimestamp"] = $node->getCreationTimestamp();
				$data["originationTimestamp"] = $node->getOriginationTimestamp();
				$data["modificationTimestamp"] = $node->getModificationTimestamp();
				$data["viewedSinceTimestamp"] = $node->getViewedSinceTimestamp();
				$data["serialNumber"] = $node->getSerialNumber();
				$data["entityType"] = $node->getEntityType();
				$data["canContainChildren"] = $node->getCanContainChildren();
				//$data[""] = $node->get();
				$norm[] = $data;
			}
		}
		return $norm;
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
	//=================================================
	//=================================================
	//=================================================
	// private helper functions
	//=================================================

	/**
	  * tree
	  */
	function _fetchAlbumTree($base, $sort_by) {

		list($error, $album_tree) = GalleryCoreApi::fetchAlbumTree($base);
		self::check($error, 'Error in GalleryCoreApi::fetchAlbumTree - ');
		list($error, $album_tree_ids) = GalleryCoreApi::fetchAllItemIds('GalleryAlbumItem');
		self::check($error,  'Error in GalleryCoreApi::fetchAllItemIds - ');
		list ($error, $tree_items) = GalleryCoreApi::loadEntitiesById($album_tree_ids);
		self::check($error, 'Error in GalleryCoreApi::loadEntitiesById - ');
		$tree = $this->_normalizeTree($base, $album_tree, $tree_items, $sort_by);

		return $tree;
	}

	/**
	  * normalize tree
	  */
	function _normalizeTree($base, $album_tree, $tree_items, $sort_by){
		$tree = array();
		$normalized_album_tree[$base] = array();
		foreach($tree_items as $item){
			$id = $item->getId();
			$tree[ $id ] = array();
			$tree[ $id ]["title"] = $item->getTitle();
			if(empty($tree[ $id ]["title"])) {
				$tree[ $id ]["title"] = $item->getPathComponent();
			}
			$tree[ $id ]["originationTimestamp"] = $item->getOriginationTimestamp();
			$tree[ $id ]["modificationTimestamp"] = $item->getModificationTimestamp();
			//etc... whatever needed but it should be exact as in normalize, as it can be overwritten
			// with the real normalize
		}
		$normalized_album_tree['sorted_by'] = $sort_by;
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
			$normalized_album_tree[$album]['originationTimestamp'] = $tree[$album]['originationTimestamp'];
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
	    	self::_fatalError($str . " ". $ret->getAsHtml() . "\n");
		}
	}
	/**
	  *
	  */
	function _fatalError($str){
		if(function_exists("T_")){ print T_($str);}else{ echo $str; }
		print '</body>' . "\n\n";
		print '</html>';
		flush();
		die;
	}

}
?>