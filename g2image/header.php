<?php
global $g2ic_options;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php T_('Gallery2 Image Chooser') ?></title>
    <link rel="stylesheet" href="css/g2image.css" type="text/css" />
    <link rel="stylesheet" href="css/dtree.css" type="text/css" />
    <link rel="stylesheet" href="css/slimbox.css" type="text/css" media="screen" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
if($g2ic_options['tinymce'] && $g2ic_options['wpg2_valid']) {
	echo "    <script language='javascript' type='text/javascript' src='../../../../wp-includes/js/tinymce/tiny_mce_popup.js'></script>\n";
}
elseif($g2ic_options['tinymce'] && !$g2ic_options['wpg2_valid']) {
	echo "    <script language='javascript' type='text/javascript' src='../../tiny_mce_popup.js'></script>\n";
}
?>
    <script language="javascript" type="text/javascript" src="jscripts/functions.js"></script>
    <script language="javascript" type="text/javascript" src="jscripts/dtree.js"></script>
    <script language="javascript" type="text/javascript" src="jscripts/mootools.js"></script>
    <script language="javascript" type="text/javascript" src="jscripts/slimbox.js"></script>
    <script type="text/javascript">
<?php
foreach($g2ic_options['image_modules'] as $moduleName){
	 echo all_modules::call( $moduleName, "insert");
}
?>
    </script>
    <script language="javascript" type="text/javascript">
    <!--
	function insertItems(){
		var obj = document.forms[0];
		var htmlCode = '';
		var imgtitle = '';
		var imgalt = '';
		var loop = '';
		var item_summary = new Array();
		var item_title = new Array();
		var item_description = new Array();
		var image_url = new Array();
		var thumbnail_img = new Array();
		var fullsize_img = new Array();
		var thumbw = new Array();
		var thumbh = new Array();
		var image_id = new Array();

		//hack required for when there is only one image

		if (obj.images.length) {
			loop = obj.images.length;
			for (var i=0;i<loop;i++) {
				image_id[i] = obj.image_id[i].value;
				item_title[i] = obj.item_title[i].value;
				item_summary[i] = obj.item_summary[i].value;
				item_description[i] = obj.item_description[i].value
				image_url[i] = obj.image_url[i].value;
				fullsize_img[i] = obj.fullsize_img[i].value;
				thumbnail_img[i] = obj.thumbnail_img[i].value;
				thumbw[i] = obj.thumbw[i].value;
				thumbh[i] = obj.thumbh[i].value;
			}
		}
		else {
			loop = 1;
			image_id[0] = obj.image_id.value;
			item_title[0] = obj.item_title.value;
			item_summary[0] = obj.item_summary.value;
			item_description[0] = obj.item_description.value
			image_url[0] = obj.image_url.value;
			thumbnail_img[0] = obj.thumbnail_img.value;
			fullsize_img[0] = obj.fullsize_img.value;
			thumbw[0] = obj.thumbw.value;
			thumbh[0] = obj.thumbh.value;
		}

		//let's generate HTML code according to selected insert option

		for (var i=0;i<loop;i++) {
			if ((loop == 1) || obj.images[i].checked) {

				imgtitle = ' title="' + item_summary[i] + '"';
				imgalt = ' alt="' + item_title[i] + '"';
				thumbw[i] = 'width="' + thumbw[i] + '" ';
				thumbh[i] = 'height="' + thumbh[i] + '" ';

				if(typeof(insertFunctions[obj.imginsert.value])=="function"){
					id = 0;
					var imageObj = {}; // new Object()

					// Fixed core variables
					imageObj.pos = i;
					imageObj.image_id = image_id[i];
					imageObj.image_url = image_url[i];
					imageObj.album_url = obj.album_url.value;
					imageObj.fullsize_img = fullsize_img[i];
					imageObj.thumbnail_img = thumbnail_img[i];
					imageObj.thumbw = thumbw[i];
					imageObj.thumbh = thumbh[i];
					imageObj.w = false; 			// to be done
					imageObj.h =  false; 			// to be done
					imageObj.item_title = item_title[i];
					imageObj.item_summary = item_summary[i];
					imageObj.item_description = item_description[i];
					imageObj.album_id =  false; 		// to be done
					imageObj.keywords =  false; 		// to be done
					imageObj.derivatives =  false; 		// to be done
					imageObj.siblings =  false; 		// to be done
					imageObj.alignment = obj.alignment.value;
					imageObj.class_mode = obj.class_mode.value;

					// Module inserted variables
					// Album modules

					// Image modules
<?php
foreach($g2ic_options['image_modules'] as $moduleName){
echo all_modules::call( $moduleName, "javaScriptVariables");
}
?>

					htmlCode += insertFunctions[obj.imginsert.value]( [obj.imginsert.value], imageObj );
				}else{
					alert(obj.imginsert.value);
					htmlCode += 'Error';
				}
			}
		}
		insertHtml(htmlCode,obj);
	}
    // -->
    </script>
</head>
<body id="g2image">
    <form method="post">
