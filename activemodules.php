<?php
$g2ic_options['modules'] = array(
	'thumbnail_image',
	'thumbnail_album',
	'thumbnail_lightbox',
	'thumbnail_custom_url',
	'thumbnail_only',
	'fullsize_image',
	'fullsize_only',
	'text_link_image',
	'text_link_album',
);

foreach($g2ic_options['modules'] as $module ){
	require_once('./modules/' . $module . '.module.class.php');
}
?>
