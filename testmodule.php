<style>
.hidden_textbox{display:none;}
.displayed_textbox{display:block;}
</style>
<script>
var insertFunctions = new Object();

	// just a demo
	var imageObj = {}; // new Object()
	imageObj.pos = 0;
	imageObj.id = 111;
	imageObj.url = "/images/pic.jpg";
	imageObj.album_url = "/album/";
	imageObj.original = "/images/original.jpg";
	imageObj.thumbnail = "/images/thumb.jpg";
	imageObj.thumbw = 45;
	imageObj.thumbh = 45;
	imageObj.w = 1280; 			// to be done
	imageObj.h =  1024; 			// to be done
	imageObj.title = "the title";
	imageObj.summary = "the summary";
	imageObj.description = "big text";
	imageObj.album_id =  22; 		// to be done
	imageObj.keywords =  "boss dia sun"; 		// to be done
	imageObj.derivatives =  [ [334,"/thumb2", 145,145] ,[335,"/thumb3", 245,245] ]; 		// to be done ** needed in mediawiki !!!
	imageObj.siblings =  [333,444,555]; 		// to be done
	imageObj.link_text_album = "Test album link text";

function doInsert(){
	var val = document.getElementById("demo_select").value;
	var htmlCode = insertFunctions[val]( [val], imageObj );
	var output = document.getElementById("show");
	output.value = htmlCode;
}
</script>
<?php
require_once('./gettext.inc');
T_setlocale(LC_ALL, 'en');

// Set the text domain as 'default'
T_bindtextdomain('default', 'langs');
T_bind_textdomain_codeset('default', 'UTF-8');
T_textdomain('default');

$g2ic_options = array();
$g2ic_options['default_image_action'] = 'text_link_album';

require_once("./modules/module.inc.php");

$g2ic_options = array();
$g2ic_options['default_image_action'] = 'text_link_album';
//** test part in init.php
	test_modules::init();
	$str  = "";
	$g2ic_imginsert_options = test_modules::g2ic_get_imginsert_selectoptions();

	$str .=  "<select id='demo_select' onchange='show(this.value)'>\n";
	foreach($g2ic_imginsert_options as $key=>$opt){
		$str .= "<option value='$key'>{$opt["text"]}</option>\n";
	}
	$str .= "</select>\n";

	// [A4] just hide or show
	$str .= "<script>function show(id){var m=document.getElementById('a_'+id);var all=m.parentNode.getElementsByTagName('div');for(var i=0;i<all.length;i++){all[i].style.display='none'}m.style.display='block'}</script>";

	$str .= "<div style='border:solid 1px red' id='additional_dialog'>";

	foreach($g2ic_options['image_modules'] as $moduleName => $version){
		$str .= all_modules::renderOptions($g2ic_options['default_image_action'], $moduleName);
	}
	$str .= "</div>";
	$str .= "<button onclick='doInsert()'>insert</button><br/><textarea id='show' style='width:100%; height:400px;color:white;background-color:black'></textarea>";
	$str .= "\n\n\n";

	// now insert all the functions
	$str .= test_modules::render_javascript();

echo $str;

//**aob [B1]  init.php
/*
	openbase restriction
	walking from root / down the whole path to search some file is a hack.
	having openbase restriction enabled or symlinked the webspace you get a lot of warnings.
	there should be a better way.
	never go above dirname($_SERVER["DOCUMENT_ROOT"])
	$_SERVER["DOCUMENT_ROOT"] for the openbase_restriction
	dirname() for symlinked folders
	i just put a @ to avoid the errors, it is still a hack
*/


//**aob [B2]  g2image.php
/*
	all javascript should only be included when needed.
*/


//**aob [B3]
/**
	i had problem with utf-8, maybe there is a better place
	when i see your webpage i see a lot of strage characters. May be you have seen theese chars visiting a japanese page.
	header('content-type: text/html; charset=utf-8');
	but inserting a header must be done at the proper position.
	gallery2 has its own header handling.
*/

//**aob [B4]
/**
	it is a lot easier to use an id for the way i display/hide the additional dialog for each select
*/




//------------------------------------------------------------
class test_modules{

	function init(){
		global $g2ic_options;
		//**aob mod [A1] test in init.php
		list( $g2ic_options['image_modules'], $hasErrModules)  = all_modules::getModules(dirname(__FILE__)."/modules/");
		if(count($hasErrModules)>0){
			echo "following Modules are not correct:";
			print_r ($hasErrModules);
		}

	}

	function g2ic_get_imginsert_selectoptions(){
		global $g2ic_options;
			//**aob mod [A2] test
			foreach($g2ic_options['image_modules'] as $moduleName => $version){
				 $imginsert_selectoptions[$moduleName] = array( "text" => all_modules::call($moduleName, "select") ) ;
			}

		return $imginsert_selectoptions;
	}

	function render_javascript(){
		global $g2ic_options;
//**aob [A3] add all needed functions
		$html = '    <script type="text/javascript">' . "\n";
		foreach($g2ic_options['image_modules'] as $moduleName => $void){
			 $html .= all_modules::call( $moduleName, "insert" );
		}
		$html .= '    </script>' . "\n";
		return $html;
	}


}

?>