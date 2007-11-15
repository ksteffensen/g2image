<?php
function init () // connect to gallery
{
    require_once( 'embed.php');
    $ret = GalleryEmbed::init(array('fullInit' => true, 'embedUri' => '/gallery2/main.php', 'g2Uri' => '/gallery2/'));
    if ($ret) {
        print 'GalleryEmbed::init failed, here is the error message: ' . $ret->getAsHtml();
        exit;
    }
    GalleryEmbed::done(); 
}

function getAlbumList () 
{
	global $gallery;
	$id = $_REQUEST['g2_itemId'];
	$display = "";
	$urlGenerator =& $gallery->getUrlGenerator();
    list ($ret, $Albums) = GalleryCoreApi::fetchAlbumTree();       
    list ($ret, $Albums) = GalleryCoreApi::loadEntitiesById(GalleryUtilities::arrayKeysRecursive($Albums));
    foreach ($Albums as $Albums){
		if (($Albums->canContainChildren == 1 && $Albums->parentId == $id) || ($Albums->canContainChildren == 1 && $Albums->getId() == $id) || empty($id)) 
		{
		    $display .="    <album>\n";
		    $display .= "        <title><![CDATA[" . $Albums->getTitle() . "]]></title>\n";
			$display .= "        <parentId><![CDATA[" . $Albums->parentId . "]]></parentId>\n";
			$display .= "        <owner><![CDATA[" . getOwner($Albums->ownerId) . "]]></owner>\n";
			$display .= "        <id><![CDATA[" . $Albums->getId() . "]]></id>\n";
			$display .="    </album>\n";			
		
		// adding each albums contents
		list ($ret, $entity) = GalleryCoreApi::loadEntitiesById( $Albums->getId() );
    if ($ret) 
	{
        print "Error loading Entity:" . $ret->getAsHtml();
    }

    list ($ret, $childIds) = GalleryCoreApi::fetchChildItemIds($entity);
    if ($ret) 
	{
       print "Error finding child item ids:" . $ret->getAsHtml();
    }

    if (!empty($childIds)) 
    { 
        foreach( $childIds as $value ) 
        {
            list ($ret, $childItem) = GalleryCoreApi::loadEntitiesById($value);
            if ($ret) 
		    {
                print "Error loading childItems:" . $ret->getAsHtml();
            }
			if(!($childItem->entityType == "GalleryAlbumItem")){

			    $currentID = $childItem->getId();
    		    list ($ret, $thumbnailList) = GalleryCoreApi::fetchThumbnailsByItemIds(array($currentID));
    		    if ($ret)
   			    {
        		    return array($ret->wrap(__FILE__, __LINE__), null);
    		    }
				$display .= "        <item>\n";
			    $display .= "            <title>" . getTitle($childItem) . "</title>\n";;
				$display .= "            <id>" . $childItem->getId() . "</id>\n";
				$display .= "            <link>" . getLink($childItem) . "</link>\n";
			    $display .= "            <view>" . getView($childItem) . "</view>\n";
			    $display .= "            <thumbUrl>" . getThumbUrl($childItem) . "</thumbUrl>\n";
			    $display .= "            <width>" . getWidth($childItem) . "</width>\n";
			    $display .= "            <height>" . getHeight($childItem) . "</height>\n";
				$display .= "            <mime>" . getMime($childItem) . "</mime>\n";
				if (!$ret && !empty($thumbnailList)) {
				$display .= "            <description><![CDATA[<a href=\"" . getLink($childItem) . "\"><img border=\"0\" src=\"" . getThumbUrl($childItem) . "\" width=\"" . getWidth($thumbnailList[$currentID]) . "\" height=\"" . getHeight($thumbnailList[$currentID]) . "\"/></a><br/>" . getTitle($childItem) . "]]></description>\n";
				}
				$display .= "            <guid isPermaLink=\"false\">" . getLink($childItem) . "</guid>\n";
				$display .= "            <pubDate>" . date('r', $childItem->getModificationTimestamp()) . "</pubDate>\n";
				$display .= "        </item>\n"; 
            }				
        }
    }
	// end adding each albums contents 
	    }
	}

	return $display;
}

function getOwner($id)
{
    list ($ret, $entity) = GalleryCoreApi::loadEntitiesById( $id );
	$owner = $entity->userName;
	return $owner;
}

function getTitle($item) {
    $title = $item->getTitle();
    GalleryCoreApi::requireOnce('lib/smarty_plugins/modifier.markup.php');
    $title = smarty_modifier_markup($title, 'strip');
    return $title;
}

function stripTags($tostrip) {
    GalleryCoreApi::requireOnce('lib/smarty_plugins/modifier.markup.php');
    $stripped = smarty_modifier_markup($tostrip, 'strip');
    return $stripped;
}

function getMime($item) {
	if(!($item->entityType == "GalleryAlbumItem")){
		return $item->getMimeType();
	} else {
		return "Album";
	}
}

function getWidth($item) {
	if(($item->entityType == "GalleryAnimationItem" || $item->entityType == "GalleryPhotoItem" || $item->entityType == "ThumbnailImage" || $item->entityType == "GalleryMovieItem" || $item->entityType == "GalleryDerivativeImage")){
		return $item->getWidth();
	} else {
		return 480;
	}
}

function getHeight($item) {
	if(($item->entityType == "GalleryAnimationItem" || $item->entityType == "GalleryPhotoItem" || $item->entityType == "ThumbnailImage" || $item->entityType == "GalleryMovieItem" || $item->entityType == "GalleryDerivativeImage")){
		return $item->getHeight();
	} else {
		return 160;
	}
}
	
function getRating($item)
{
    $itemId = $item->getId();
    $rating = '';
    GalleryCoreApi::requireOnce('modules/rating/classes/RatingHelper.class');
	list ($ret, $Ratings) = RatingHelper::fetchRatings($itemId, '');
	if(!empty ($Ratings)){
	$rating = $Ratings[$id]['rating'];
	return "            <rating>" . $rating . "</rating>\n";
	} else {
	return "            <rating>0</rating>\n";
	}
}

function getThumbUrl($item)
{
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

function getLink($item)
{
    global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
	$link = $urlGenerator->generateUrl(
	    array('itemId' => $item->getId()),
		array('forceFullUrl' => true, 'forceSessionId' => true));
	return $link;
}

function getView($item)
{
    global $gallery;
	$urlGenerator =& $gallery->getUrlGenerator();
    $view = $urlGenerator->generateUrl(
	    array('view' => 'core.DownloadItem', 'itemId' => $item->getId(),
		    'serialNumber' => $item->getSerialNumber()),
		array('forceFullUrl' => true, 'forceSessionId' => true, 'htmlEntities' => true));
	return $view;
}

function xml() {
	init();
	global $gallery;
	$xml = '';
	$urlGenerator =& $gallery->getUrlGenerator();
    $link = $urlGenerator->generateUrl(array(), array('forceFullUrl' => true));
	$vm = $gallery->getPhpVm();
	list ($ret, $language) = GalleryTranslator::getDefaultLanguageCode( );
	if ($ret) 
	{
        $language = "en-us";
    }
	$gallery->locale = '';
	if ($gallery->locale == 0) {
	    $gallery->locale = 'ISO-8859-1';
	}
	if (!$vm->headers_sent()) {
	    $vm->header('Content-type: text/xml; charset=UTF-8');
	}
	echo "<?xml version=\"1.0\" encoding=\"" . $gallery->locale . "\"?>\n";	
	$xml .= "<rss version=\"2.0\">\n";
	$xml .= "    <channel>\n";
	$xml .= "        <title><![CDATA[ XML Mini SlideShow for Gallery2 ]]></title>\n";
	$xml .= "        <link>" . $link . "</link>\n";
	$xml .= "        <description>XML Mini SlideShow for Gallery2</description>\n";
	$xml .= "        <language>" .$language. "</language>\n";
	$xml .= "        <generator>4WiseGuys RSS Generator version 1.5.3</generator>\n";
	$xml .= "        <lastBuildDate>" . date('r', $vm->time()) . "</lastBuildDate>\n";
	$xml .= "        <ttl>120</ttl>\n";
	$xml .= getAlbumList ();              
	$xml .= "    </channel>\n";
	$xml .= "</rss>\n";
	echo $xml;
}

xml();
?>
