<?php
class BackendApi {

	function init($g2ic_options) {
		if(!$g2ic_options['embedded_mode'])
			$g2ic_options['gallery2_uri'] = '/' . $g2ic_options['gallery2_path'] . 'main.php';

		if(!$g2ic_options['use_full_path'])
			$g2ic_options['gallery2_path'] = $g2ic_options['base_path'].$g2ic_options['gallery2_path'];

		if(file_exists($g2ic_options['gallery2_path'].'embed.php')) {
			require_once($g2ic_options['gallery2_path'].'embed.php');

			if ($g2ic_options['embedded_mode']){
				$embed_options['g2Uri'] = $g2ic_options['gallery2_uri'];
				$embed_options['embedUri'] = $g2ic_options['embed_uri'];
				$error = GalleryEmbed::init( array(
					'g2Uri' => $embed_options['g2Uri'],
					'embedUri' => $embed_options['embedUri'],
					'fullInit' => true)
				);
			}
			else{
				$embed_options['g2Uri'] = $g2ic_options['gallery2_uri'];
				$error = GalleryEmbed::init( array(
					'g2Uri' => $option['g2Uri'],
					'embedUri' => $option['g2Uri'],
					'fullInit' => true)
				);
			}

			if ($error) {
				$g2ic_options['tinymce'] = FALSE;
				$g2ic_options['wpg2_valid'] = FALSE;
				require_once('header.php');
				print T_('<h3>Fatal Gallery2 error:</h3><br />Here\'s the error from G2:') . ' ' . $error->getAsHtml() . "\n";
				print "</body>\n\n";
				print "</html>";
				die;
			}

			return;

		}
		// Else die on a fatal error
		else {
			$g2ic_options['tinymce'] = FALSE;
			$g2ic_options['wpg2_valid'] = FALSE;
			require_once('header.php');
			print T_('<h3>Fatal Gallery2 Error: Cannot activate the Gallery2 Embedded functions.</h3><br />For WordPress users, Validate WPG2 in the Options Admin panel.<br /><br />For other platforms, please verify your Gallery2 path in config.php.');
			print '</body>' . "\n\n";
			print '</html>';
			die;
		}
	}

	function loadEntityById($id) {
		list ($error, $entities) = GalleryCoreApi::loadEntitiesById(array($id));
		if ($error) {
			print T_('Error loading entity:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $entities[0];
		}
	}

	function fetchChildDataItemIds($id) {
		list ($error, $childDataItemIds) = GalleryCoreApi::fetchChildDataItemIds($id);
		if ($error) {
			print T_('Error fetching child data item IDs:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $childDataItemIds;
		}
	}

	function fetchPreferredsByItemId($id) {
		list($error, $preferred) = GalleryCoreApi::fetchPreferredsByItemIds(array($id));
		if ($error) {
			print T_('Error fetching preferred item:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $preferred;
		}
	}

	function fetchThumbnailsByItemId($id) {
		list($error, $thumbnails) = GalleryCoreApi::fetchThumbnailsByItemIds(array($id));
		if ($error) {
			print T_('Error fetching thumbnails:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $thumbnails;
		}
	}

	function fetchResizesByItemId($id) {
		list($error, $resizes_array) = GalleryCoreApi::fetchResizesByItemIds(array($id));
		if ($error) {
			print T_('Error fetching resizes:') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $resizes_array;
		}
	}

	function getRootAlbumId() {
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

	function fetchAlbumTree($id, $depth) {
		list($error, $album_tree) = GalleryCoreApi::fetchAlbumTree($id, $depth);
		if ($error) {
			print T_('Error fetching album tree') . ' ' . $error->getAsHtml() . "\n";
		}
		else {
			return $album_tree;
		}
	}

	function finished() {
		GalleryEmbed::done();
		return;
	}

}
?>
