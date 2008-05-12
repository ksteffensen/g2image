<?php
/*
//  Gallery 2 Image Chooser for TinyMCE
//  Version 1.3.5
//  By Kirk Steffensen - http://g2image.steffensenfamily.com/
//  Released under the GPL version 2.
//  A copy of the license is in the root folder of this plugin.
//  See README.HTML for installation info.
//  See CHANGELOG.HTML for a history of changes.
//  See CREDITS.HTML for inspiration, code, and assistance credits.
*/

// Options Saved?
if ( isset($_POST['action']) ) {
	check_admin_referer();

	if ($_POST['action'] == 'update') {
		$g2_option = get_settings('g2_options');
		foreach ($_POST['g2_options'] as $key=>$value){
			$g2_option[$key] = $value;
		}
		update_option('g2_options', $g2_option);
		$referred = remove_query_arg('updated' , $_SERVER['HTTP_REFERER']);
		$goback = add_query_arg('updated', 'true', $_SERVER['HTTP_REFERER']);
		$goback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $goback);
		header('Location: ' . $goback);
	}
}

$g2_option = get_settings('g2_options');

// Determine if WP-Gallery2 Plugin is present, active, and validated
$current_plugins = get_option('active_plugins');
if (in_array('wp-gallery2/g2embed.php', $current_plugins)) {
	if ($g2_option['g2_validated'] == "Yes" ){
		$g2ic_wpg2_valid = TRUE;
	}
}
?>

<div class="wrap">
<h2><?php _e('G2Image Popup Options', 'g2image') ?></h2>
<?php
// g2image installed?
echo '	<table width="700" bordercolor="#242424" border=1 cellpadding=1 cellspacing=0 >' . "\n";
echo '		<tr>' . "\n";
echo '			<td>' . __('G2Image API located in TinyMCE plugins folder?', 'g2image') . '</td>' . "\n";
echo '			<td>';
if (file_exists('../wp-includes/js/tinymce/plugins/g2image/g2image.php' ) )
	echo '<font color="green">' . __('Success', 'g2image');
else {
	echo '<font color="red">' . __('Failed.  Please install G2Image files in TinyMCE plugins directory.', 'g2image');
			$validate_err=1;
}
echo '</font></td>' . "\n";
echo '		</tr>' . "\n";
echo '	</table>' . "\n";

if(!$validate_err){
?>
<form name="g2image" method="post" action="admin.php?page=g2image/g2image-admin.php&noheader=true">
	<input type="hidden" name="action" value="update" />
	<fieldset class="options">
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">

<?php
	if (!$g2ic_wpg2_valid) {
?>

	<tr>
	<th valign="top" scope="row"><?php _e('Gallery2 Path', 'g2image') ?> </th>
	<td>
	<?php _e('WPG2 is not active, so you must enter the path from your web root directory to your Gallery2 directory.', 'g2image') ?>
	<input name="g2_options[g2ic_gallery2_path]" id="g2ic_gallery2_path" value="<?php if(isset($g2_option['g2ic_gallery2_path'])){echo $g2_option['g2ic_gallery2_path'];} else{echo "gallery2/";} ?>" size="50" / ><br />
	<?php _e('Example: If your Gallery2 homepage is www.domain.com/gallery2/main.php, then the Gallery2 path is "gallery2/".  Make sure you include the trailing forward slash.<br /><strong>NOTE:</strong> <a href="http://wpg2.ozgreg.com">WPG2</a> extends the integration of Gallery2 into WordPress and is highly recommended.', 'g2image') ?>
	</td>
	</tr>

<?php
	}
?>

	<tr>
	<th valign="top" scope="row"><?php _e('Thumbnails per Page:', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_images_per_page]" id="g2ic_images_per_page" value="<?php if(isset($g2_option['g2ic_images_per_page'])){echo $g2_option['g2ic_images_per_page'];} else{echo "15";} ?>" size="10" / ><br />
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Image Sort Order:', 'g2image') ?></th>
	<td>
	<select name="g2_options[g2ic_sortby]">
		<option value="title_asc"<?php if ($g2_option['g2ic_sortby']=="title_asc" ){ echo " selected";} ?>><?php _e('Gallery2 Title (A-z)', 'g2image') ?></option>
		<option value="title_desc"<?php if ($g2_option['g2ic_sortby']=="title_desc" ){ echo " selected";} ?>><?php _e('Gallery2 Title (z-A)', 'g2image') ?></option>
		<option value="name_asc"<?php if ($g2_option['g2ic_sortby']=="name_asc" ){ echo " selected";} ?>><?php _e('Filename (A-z)', 'g2image') ?></option>
		<option value="name_desc"<?php if ($g2_option['g2ic_sortby']=="name_desc" ){ echo " selected";} ?>><?php _e('Filename (z-A)', 'g2image') ?></option>
		<option value="mtime_desc"<?php if ($g2_option['g2ic_sortby']=="mtime_desc" ){ echo " selected";} ?>><?php _e('Last Modification (newest first)', 'g2image') ?></option>
		<option value="mtime_asc"<?php if ($g2_option['g2ic_sortby']=="mtime_asc" ){ echo " selected";} ?>><?php _e('Last Modification (oldest first)', 'g2image') ?></option>
	</select> <br />
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Display', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_display_filenames]" type="radio" id="g2ic_display_filenames_false" value="no" <?php if(!isset($g2_option['g2ic_display_filenames'])){echo "checked ";} elseif ($g2_option['g2ic_display_filenames']=="no"){echo "checked ";}?> /><?php _e('Thumbnails Only', 'g2image') ?><br />
	<input name="g2_options[g2ic_display_filenames]" type="radio" id="g2ic_display_filenames_true" value="yes" <?php if ($g2_option['g2ic_display_filenames']=="yes"){echo "checked ";}?> /><?php _e('Thumbnails with Titles and Filenames', 'g2image') ?>
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Click Action', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_click_mode]" type="radio" id="g2ic_click_mode_one_click_insert" value="one_click_insert" <?php if(!isset($g2_option['g2ic_click_mode'])){echo "checked ";} elseif ($g2_option['g2ic_click_mode']=="one_click_insert"){echo "checked ";}?> /><?php _e('Instantly insert using default settings', 'g2image') ?><br />
	<input name="g2_options[g2ic_click_mode]" type="radio" id="g2ic_click_mode_show_advanced_options" value="show_advanced_options" <?php if ($g2_option['g2ic_click_mode']=="show_advanced_options"){echo "checked ";}?> /><?php _e('Show Advanced Options Panel', 'g2image') ?>
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Allow User to Change Click Options?', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_click_mode_variable]" type="radio" id="g2ic_click_mode_variable_true" value="yes" <?php if(!isset($g2_option['g2ic_click_mode_variable'])){echo "checked ";} elseif ($g2_option['g2ic_click_mode_variable']=="yes"){echo "checked ";}?> /><?php _e('Yes', 'g2image') ?><br />
	<input name="g2_options[g2ic_click_mode_variable]" type="radio" id="g2ic_click_mode_variable_false" value="no" <?php if ($g2_option['g2ic_click_mode_variable']=="no"){echo "checked ";}?> /><?php _e('No', 'g2image') ?>
	</td>
	</tr>

<?php
	if ($g2ic_wpg2_valid) {
?>

	<tr>
	<th valign="top" scope="row"><?php _e('WPG2ID or WPG2 Tags?', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_wpg2id_tags]" type="radio" id="g2ic_wpg2id_tags_true" value="yes" <?php if(!isset($g2_option['g2ic_wpg2id_tags'])){echo "checked ";} elseif ($g2_option['g2ic_wpg2id_tags']=="yes"){echo "checked ";}?> /><?php _e('WPG2ID Tags', 'g2image') ?><br />
	<input name="g2_options[g2ic_wpg2id_tags]" type="radio" id="g2ic_wpg2id_tags_false" value="no" <?php if ($g2_option['g2ic_wpg2id_tags']=="no"){echo "checked ";}?> /><?php _e('WPG2 Tags', 'g2image') ?>
	</td>
	</tr>

<?php
	}
?>

	<tr>
	<th valign="top" scope="row"><?php _e('Default Action:', 'g2image') ?></th>
	<td>
	<?php _e('Choose the default "How to Insert" option.', 'g2image') ?><br />
	<select name="g2_options[g2ic_default_action]">

<?php
	if ($g2ic_wpg2_valid) {
?>

		<option value="wpg2"<?php if ($g2_option['g2ic_default_action']=="wpg2" ){ echo " selected";} ?>><?php _e('WPG2/WPG2ID Tag', 'g2image') ?></option>

<?php
	}
?>

		<option value="thumbnail_image"<?php if ($g2_option['g2ic_default_action']=="thumbnail_image" ){ echo " selected";} ?>><?php _e('Thumbnail with link to image', 'g2image') ?></option>
		<option value="thumbnail_album"<?php if ($g2_option['g2ic_default_action']=="thumbnail_album" ){ echo " selected";} ?>><?php _e('Thumbnail with link to parent album', 'g2image') ?></option>
		<option value="thumbnail_custom_url"<?php if ($g2_option['g2ic_default_action']=="thumbnail_custom_url" ){ echo " selected";} ?>><?php _e('Thumbnail with link to custom URL', 'g2image') ?></option>
		<option value="thumbnail_only"<?php if ($g2_option['g2ic_default_action']=="thumbnail_only" ){ echo " selected";} ?>><?php _e('Thumbnail only - no link', 'g2image') ?></option>
		<option value="link_image"<?php if ($g2_option['g2ic_default_action']=="link_image" ){ echo " selected";} ?>><?php _e('Text link to image', 'g2image') ?></option>
		<option value="link_album"<?php if ($g2_option['g2ic_default_action']=="link_album" ){ echo " selected";} ?>><?php _e('Text link to parent album', 'g2image') ?></option>
	</select> <br />
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Image Alignment:', 'g2image') ?></th>
	<td>
	<?php _e('The g2image classes must be implemented in your style.css for the alignment classes to be effective.', 'g2image') ?><br />
	<select name="g2_options[g2ic_default_alignment]">
		<option value="none"<?php if ($g2_option['g2ic_default_alignment']=="none" ){ echo " selected";} ?>><?php _e('None', 'g2image') ?></option>
		<option value="g2image_normal"<?php if ($g2_option['g2ic_default_alignment']=="g2image_normal" ){ echo " selected";} ?>><?php _e('Normal', 'g2image') ?></option>
		<option value="g2image_float_left"<?php if ($g2_option['g2ic_default_alignment']=="g2image_float_left" ){ echo " selected";} ?>><?php _e('Float Left', 'g2image') ?></option>
		<option value="g2image_float_right"<?php if ($g2_option['g2ic_default_alignment']=="g2image_float_right" ){ echo " selected";} ?>><?php _e('Float Right', 'g2image') ?></option>
		<option value="g2image_centered"<?php if ($g2_option['g2ic_default_alignment']=="g2image_centered" ){ echo " selected";} ?>><?php _e('Centered', 'g2image') ?></option>
		<?php if(isset($g2_option['g2ic_custom_class_1'])&&($g2_option['g2ic_custom_class_1']!='not_used')){ ?>
		<option value="<?php echo $g2_option['g2ic_custom_class_1']; ?>"<?php if ($g2_option['g2ic_default_alignment']==$g2_option['g2ic_custom_class_1']){ echo " selected";} ?>><?php echo $g2_option['g2ic_custom_class_1']; ?></option>
		<?php } ?>
		<?php if(isset($g2_option['g2ic_custom_class_2'])&&($g2_option['g2ic_custom_class_2']!='not_used')){ ?>
		<option value="<?php echo $g2_option['g2ic_custom_class_2']; ?>"<?php if ($g2_option['g2ic_default_alignment']==$g2_option['g2ic_custom_class_2']){ echo " selected";} ?>><?php echo $g2_option['g2ic_custom_class_2']; ?></option>
		<?php } ?>
		<?php if(isset($g2_option['g2ic_custom_class_3'])&&($g2_option['g2ic_custom_class_3']!='not_used')){ ?>
		<option value="<?php echo $g2_option['g2ic_custom_class_3']; ?>"<?php if ($g2_option['g2ic_default_alignment']==$g2_option['g2ic_custom_class_3']){ echo " selected";} ?>><?php echo $g2_option['g2ic_custom_class_3']; ?></option>
		<?php } ?>
		<?php if(isset($g2_option['g2ic_custom_class_4'])&&($g2_option['g2ic_custom_class_4']!='not_used')){ ?>
		<option value="<?php echo $g2_option['g2ic_custom_class_4']; ?>"<?php if ($g2_option['g2ic_default_alignment']==$g2_option['g2ic_custom_class_4']){ echo " selected";} ?>><?php echo $g2_option['g2ic_custom_class_4']; ?></option>
		<?php } ?>
	</select> <br />
	<?php _e('Custom classes will be available as options after entering them below and hitting the "Update Options" button.', 'g2image') ?><br />
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Class Mode', 'g2image') ?> </th>
	<td>

<?php
	if ($g2ic_wpg2_valid) {
?>

	<?php _e('This setting only applies to images inserted as img tags.  WPG2 tags will be wrapped with a div tag.', 'g2image') ?><br />

<?php
	}
?>

	<input name="g2_options[g2ic_class_mode]" type="radio" id="g2ic_class_mode_img" value="img" <?php if(!isset($g2_option['g2ic_class_mode'])){echo "checked ";} elseif ($g2_option['g2ic_class_mode']=="img"){echo "checked ";}?> /><?php _e('Class in the img tag - &lt;img class=... /&gt; (Recommended)', 'g2image') ?><br />
	<input name="g2_options[g2ic_class_mode]" type="radio" id="g2ic_class_mode_div" value="div" <?php if ($g2_option['g2ic_class_mode']=="div"){echo "checked ";}?> /><?php _e('Class in a div tag wrapper - &lt;div class=...&gt;&lt;img ... /&gt;&lt;/div&gt;', 'g2image') ?>
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Default Custom URL:', 'g2image') ?> </th>
	<td>
	<input name="g2_options[g2ic_custom_url]" id="g2ic_custom_url" value="<?php if(isset($g2_option['g2ic_custom_url'])){echo $g2_option['g2ic_custom_url'];} else{echo "http://";} ?>" size="50" / ><br />
	</td>
	</tr>
	<tr>
	<th valign="top" scope="row"><?php _e('Custom Classes:', 'g2image') ?> </th>
	<td>
	<?php _e('Custom Class 1 (a valid class in your CSS or "not_used")', 'g2image') ?><br />
	<input name="g2_options[g2ic_custom_class_1]" id="g2ic_custom_class_1" value="<?php if(isset($g2_option['g2ic_custom_class_1'])){echo $g2_option['g2ic_custom_class_1'];} else{echo "not_used";} ?>" size="30" / ><br />
	<?php _e('Custom Class 2 (a valid class in your CSS or "not_used")', 'g2image') ?><br />
	<input name="g2_options[g2ic_custom_class_2]" id="g2ic_custom_class_2" value="<?php if(isset($g2_option['g2ic_custom_class_2'])){echo $g2_option['g2ic_custom_class_2'];} else{echo "not_used";} ?>" size="30" / ><br />
	<?php _e('Custom Class 3 (a valid class in your CSS or "not_used")', 'g2image') ?><br />
	<input name="g2_options[g2ic_custom_class_3]" id="g2ic_custom_class_3" value="<?php if(isset($g2_option['g2ic_custom_class_3'])){echo $g2_option['g2ic_custom_class_3'];} else{echo "not_used";} ?>" size="30" / ><br />
	<?php _e('Custom Class 4 (a valid class in your CSS or "not_used")', 'g2image') ?><br />
	<input name="g2_options[g2ic_custom_class_4]" id="g2ic_custom_class_4" value="<?php if(isset($g2_option['g2ic_custom_class_4'])){echo $g2_option['g2ic_custom_class_4'];} else{echo "not_used";} ?>" size="30" / ><br />
	</td>
	</tr>
	</table>
	</fieldset>
	<p class="submit">
		<input type="submit" name="submit" value="<?php _e('Update Options', 'g2image') ?> &raquo;" />
	</p>
</form>
<?php
}
?>
</div>