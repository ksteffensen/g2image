<?php
$g2ic_options['album_modules'] = array(
	'wpg2_album',
	'drupal_g2_filter_album',
	'flash_mini_slideshow_album',
);

$g2ic_options['image_modules'] = array(
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

$g2ic_options['bbcode_modules'] = array(
	'bbcode_thumbnail_image',
	'bbcode_thumbnail_album',
	'bbcode_thumbnail_custom_url',
	'bbcode_thumbnail_only',
	'bbcode_fullsize_image',
	'bbcode_fullsize_album',
	'bbcode_fullsize_custom_url',
	'bbcode_fullsize_only',
);

if ($g2ic_options['bbcode_enabled']) {
	$g2ic_options['image_modules'] = array_merge($g2ic_options['image_modules'], $g2ic_options['bbcode_modules']);
}

if ($g2ic_options['wpg2_valid']) {
	$g2ic_options['image_modules'] = array_merge(array('wpg2_image'), $g2ic_options['image_modules']);
}

if($g2ic_options['drupal_g2_filter'])	{
	$g2ic_options['image_modules'] = array_merge(array('drupal_g2_filter'), $g2ic_options['image_modules']);
}

if ($g2ic_options['bbcode_only']) {
	$g2ic_options['image_modules'] = $g2ic_options['bbcode_modules'];
}

foreach($g2ic_options['image_modules'] as $module ){
	require_once('./modules/' . $module . '.module.class.php');
}
?>
