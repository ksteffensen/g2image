<?php
//------------------------------------------------------------

define("_MODULE_PATTERN_", ".module.class.php");
define("_MODULE_REGEXP_", "/.*".preg_quote(_MODULE_PATTERN_)."/i");

class all_modules{
	function has_error(){
	}
	//------------------------------------------------------------
	// looks for all modules available and includes them if valid
	//------------------------------------------------------------
	function getModules( $dir ){
		$modulesAvailable = array();
		$modulesErrors = array();
		$modules = all_modules::findMatchFiles($dir, _MODULE_REGEXP_);
		foreach( $modules as $module ){  //maybe a caching method would increase speed - write all in one file!
			require_once($dir.$module);
			$className = preg_replace("/"._MODULE_PATTERN_."/i", "", $module );  // get the classname out of the filename
			if(class_exists($className) ){  // erst ab PHP5.0.3 && is_subclass_of("module_basic")){
				$valid = call_user_func(array($className, "extra"), "version" );

				if($valid != module_prototype::extra("version")) {
					$modulesAvailable[$className] = $valid;
				}else{
					$modulesErrors[$className] = "$className: " .$dir.$module . " : ". $valid;
				}
			}else{
					$modulesErrors[$className] = "$className: " .$dir.$module;
			}
		}
		return array($modulesAvailable, $modulesErrors);
	}

	//------------------------------------------------------------
	//------------------------------------------------------------
	function call($module, $func){
		return call_user_func( array($module, $func), $module );
	}

	//-------------------------------
	//$regexp = "/[0-9]{1,}_[0-9]{1,}_[0-9]{4,}/";
	//$regexp = "/.jpg/";

	function findMatchFiles($dir,$regexp){
		$result_array = array();
			$dir = dir($dir);
		if ($regexp != null) {
			while ($file = $dir->read()) {
				if (preg_match($regexp, $file)) {
					$result_array[] = $file;
				}
			}
		}
		$dir->close();
		return $result_array;
	}


	//------------------------------------------------------------

	function renderOptions($defaultAction, $module){
			$class = ($defaultAction == $module ) ? "displayed" : "hidden" ;
			$html = "			<div id=\"a_{$module}\" module=\"{$module}\" name=\"{$module}_textbox\"  class=\"{$class}_textbox\" >" . "\n";
			$html .= call_user_func(array($module, "dialog"));
			$html .= '			</div>' . "\n\n";
			return $html;

	}

}





//------------------------------------------------------------
//------------------------------------------------------------
// the skeleton of a module
//------------------------------------------------------------
// each new insertModule should be an extension of this basich module
//
class module_prototype{

	//------------------------------------------------------------
	/**
	 * this is the main part of the class rendering the desired output
	 *	var stack = [];
	 *	stack[0] = module_name
	 *	stack[1] = function within module
	 *	stack[n] = optional extra subfunction or switch
	 *
	 *	var imageObj = {};
	 *	imageObj.id
	 *	imageObj.url
	 *	imageObj.original
	 *	imageObj.thumbnail
	 *	item.thumbnail_width
	 *	item.thumbnail_height
	 *	imageObj.w
	 *	imageObj.h
	 *	imageObj.title
	 *	imageObj.summary
	 *	imageObj.description
	 *	imageObj.album_id
	 *	imageObj.keywords
	 *	imageObj.derivatives	//	all resized versions of the image or a function that deliver this
	 *	imageObj.siblings		// all images in the same album or a function that deliver this
	 *
	 *	@return $string
	 */
	function insert($name){
		// caution: \n in javascript strings: \\n
//## JAVASCRIPT #################
		$script = <<<SCRIPTSTUFF
	//module [{$name}]
	insertFunctions["{$name}"] = module_{$name};

	function module_{$name}(stack, form, item, album, options){
		return " ---";
	}
	//end module [{$name}]

SCRIPTSTUFF;
//## END JAVASCRIPT #############

		return $script;

	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * here we can add extra vars or settings for the rendering
	 *
	 */
	function dialog(){
		return "my dialog";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return "mySelection";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * instead of a selection-box an icon
	 *
	 */
	function icon(){
		return "";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * check for needed javascripts
	 *
	 */
	function preeq(){
		return "";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 *
	 */
	function help(){
		return "";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * maybe a setup function
	 *
	 */
	function setup(){
		return "mySetup";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * for later,avoid compatibility problem
	 * and any information can be added in the array, like help etc.
	 * @return mixed string or array
	 *
	 */
	function extra($key=false){
		$data = array();
		$data["version"] = "prototype V.0.1";
		$data["description"] = "this is the prototype";
		if($key and isset($data[$key])){
			return $data[$key];
		}else{
			return $data;
		}
	}
	//------------------------------------------------------------
	//------------------------------------------------------------
}
//------------------------------------------------------------
?>