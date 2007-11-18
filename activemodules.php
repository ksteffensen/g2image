<?php
$g2ic_options['modules'] = array(
	'thumbnail_image',
	'thumbnail_album',
	'thumbnail_lightbox',
	'thumbnail_custom_url',
	'thumbnail_only',
	'fullsize_image',
	'fullsize_album',
	'fullsize_custom_url',
	'fullsize_only',
	'text_link_image',
	'text_link_album',
);

if ($g2ic_options['wpg2_valid']) {
	$g2ic_options['modules'] = array('wpg2_image') + $g2ic_options['modules'];
}

if($g2ic_options['drupal_g2_filter'])	{
	$g2ic_options['modules'] = array('drupal_g2_filter') + $g2ic_options['modules'];
}

foreach($g2ic_options['modules'] as $module ){
	require_once('./modules/' . $module . '.module.class.php');
}
?>
