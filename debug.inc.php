<?php
$rndnum=0;
class debug{

	public function show($arr, $start="", $height=false, $colorindex=2) {
		static $debugcount = 0;
		global $rndnum;
		global $colorscheme;

		$colorscheme = array(
			array("eaf293","c5cc7c", "a0a665"),
			array("f2bb94", "cc9e7c", "a68065"),
			array("d6f2ff", "aec4cf", "44809c "),
			array("faea37", "d4c62f", "ada226"),
			array("4bf8d0", "3fd1af", "33ab8f"),
			array("7a7a7a", "a1a1a1", "c7c7c7"),
			array("0099cc", "009999", "00ff00"),
			array("cfc", "cf6", "cf0"),
			array("ffc", "ff6", "ff0"),
			array("f96", "c66", "966"),
		);

		$hell = $colorscheme[$colorindex][0];
		$mittel = $colorscheme[$colorindex][1];
		$dunkel = $colorscheme[$colorindex][2];
		$rndnum++;
		$str = "";

		static $runonce = 0;
		if(!($start ===false)){
			$runonce = 0;
		}
		if(is_numeric($start) && $height==false){
			$height=$start;
		}
		if(!$height){
			$height="auto";
		}else{
			$height = $height."px";
		}
		if($runonce==0){
			$runonce++;
			$str .= "<style>
			#entry$debugcount .caption{background-color:#$hell; cursor:pointer; cursor:hand; font-family:verdana; padding-left:50px;}
			#entry$debugcount .debugshow{font-family:arial; background-color:#$dunkel; vertical-align:top;}
			#entry$debugcount .key{ background-color:#$mittel; border:solid 1px #$hell;}
			#entry$debugcount .val{ background-color:#$hell; border:solid 1px #$mittel;}
			#entry$debugcount .type{ background-color:#33CCFF; }
			#entry$debugcount .debugshow td, .debugshow th{font-size:8pt;}
			#entry$debugcount .child{}
			#entry$debugcount{ border:solid 1px black; max-height:$height; overflow:auto;
			</style>
			";
			$divaround = "id='entry$debugcount'";
		}else{
			$divaround = "class='child'";
//			$str .= "<style></style>";
		}
		$name = "";
		if (is_string($start) && $start!="") {
			$name = $start;
			$start = true;
		}
		if (is_array($arr) || is_object($arr))  {
			$emptyWhat = "empty-array";
			if (is_object($arr)) {
				$type = "key";
				$emptyWhat = "empty-object";
			}
			if (debug::isXOneDimensional($arr) && !$start) {
				if (count($arr) == 0) {
					$str .= "<span class='type' >$emptyWhat</span><br>\n";
				}
				foreach($arr as $key => $value) {
					$str.= "<span class='key' >".debug::decorateValue($key)."</span>\n";
					$str.= "<span class='val' >".debug::decorateValue($value, $key)."</span>\n";
				}
			}
			else {
				$str .= "<div $divaround><div class='caption' ".debug::click("cnt$rndnum").">$name</div><table class='debugshow' cellpadding='0' style='display:block' id='cnt$rndnum'>\n";
				if (count($arr) == 0) {
					$str.= "   <tr ><td colspan='2' class='type' >$emptyWhat</td></tr>\n";
				}
				foreach($arr as $key => $value) {
					$str.= "   <tr id='cnt$rndnum' >\n";
					$str.= "      <td class='key' ".debug::click("cnt$rndnum")." >".debug::decorateValue($key)."</td>\n";
					$str.= "      <td class='val' >".debug::show($value, false)."</td>\n";
					$str.= "   </tr>\n";
				}
				$str.= "</tbody></table></div>\n";
			}
		}
		else {
			$str .= debug::decorateValue($arr);
			if ($name != "") $str .= "$name = $str<br>\n";
		}
		$debugcount++;
		return $str;

	}


	private function decorateValue($value, $key=false) {
		if (is_string($value)) {
			if (trim($value) == "") $decValue = "\"$value\"";
			else $decValue = htmlentities($value);
		}
		else if (is_bool($value)) {
			if ($value) $decValue = "true";
			else $decValue = "false";
			$decValue = "<b>$decValue</b>";
		}
		else if (is_null($value)) {
			$decValue = "<b>null</b>";
		}
		else {
			$decValue = "<b>$value</b>";
		}
		if($key=="href" || $key=="url" || $key=="src"){
			$decValue = "<a target='_debug' href='$value'>$value</a>";
		}
		return $decValue;
	}

	private function click($id){
		return " onclick='var p=document.getElementById(\"$id\"); p.style.display=(p.style.display!=\"none\")?\"none\":\"block\";' ";
	}
	///////////////////////////////////////////////////
	private function isXOneDimensional($arr) {
		if (! is_array($arr) && ! is_object($arr)) return false;
		foreach ($arr as $val) {
			if (is_array($val) || is_object($val)) return false;
		}
		return true;
	}
}

?>