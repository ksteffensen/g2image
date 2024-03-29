<?php
/**
 * Get all of the Gallery2 items
 *
 * @return array $album_info Album Title and URL for the current album
 * @return array $gallery_items Sorted array of IDs and Titles for all Gallery2 Data Items in the current album
 */


//------------------------------------------------------------
//------------------------------------------------------------
// the skeleton of a module
//------------------------------------------------------------
// each new insertModule should be an extension of this basich module
//
class sample_prototype{

	/**
	 * See sample module for details
	 */
	function insert($name){
		// caution: \n in javascript strings: \\n
//## JAVASCRIPT #################
		$script = <<<SCRIPTSTUFF
//module [{$name}]
	insertFunctions["{$name}"] = module_{$name};

	function module_{$name}(stack, form, item, album, options){
		var str = "";
		for(var a in imageObj){
			if(typeof(imageObj[a] == "string")){
				str += "<div class='part'>" + a +" : " + imageObj[a] +"</div>\\n";
			}else{
				str += "<div class='part'>" + a + "<hr/>\\n";
				for(var e in imageObj[a]){
					if(typeof(imageObj[a][e] == "string")){
						str += "<div class='part'>" + e +" : " + imageObj[a][e] +"</div>\\n";
					}
				}
				str += "</div>";
			}
		}
		return str;
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
		return "sample dialog";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * fill the select box to choose this renderer
	 *
	 */
	function select(){
		return "sample mySelection";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * instead of a selectionbox a icon
	 *
	 */
	function icon(){
		return "sample";
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * check for needed javascripts
	 *
	 */
	function preeq(){
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 *
	 */
	function help(){
	}
	//------------------------------------------------------------
	//------------------------------------------------------------

	/**
	 * maybe a setup function
	 *
	 */
	function setup(){
		return "sample Setup";
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
		$data["version"] = "sample V.0.1";
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