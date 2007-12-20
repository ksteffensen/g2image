<?php
// message_handling.class.php    2007-12-12 12:12 rev. aob
/** ***********************************************
  * Copyright (C) 2007 Andres Obrero
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  * General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
  * ***********************************************
  */

/** ***********************************************
  * ABSTRACT
  * ========
  * this class handles an array of messages and errors occuring in php classes and renders them to the webpage or in a file
  * each error is an array($type, $message, $information)
  * $message can be translated and type can be styled
  * ***********************************************
  */

/**
  * error_reporting(E_ALL);
  * //************************************************
  * //  by defining EXTERNAL_TRANSLATE_FUNCTION_NAME a external function will be called to translate
  * define("EXTERNAL_TRANSLATE_FUNCTION_NAME", "T_");
  *
  * //************************************************
  * function T_($str){
  * 	return "<i>".$str."</i>";
  * }
  *
  *
  * echo "<html><head></head><body>";
  *
  *  //** create some errors and messages randomly
  * $errors = array();
  * for($i=0; $i<10; $i++){ list($errors[] , $data ) = message_handling::doSample(); }
  * list($head, $body) = message_handling::renderMessages($errors, "./error.html", array(".alert"=>"background-color:black;color:white;"));
  *
  *  //** now we test to append new errors to the file
  * $errors = array();
  * for($i=0; $i<10; $i++){ list($errors[] , $data ) = message_handling::doSample(); }
  * list($head, $body) = message_handling::renderMessages($errors, "./error.html", array(".alert"=>"background-color:#DDF;color:red;font-style:normal"));
  * echo $head.$body;
  *
  *  //** out direct to the file
  * $errors = array();
  * for($i=0; $i<10; $i++){ list($errors[] , $data ) = message_handling::doSample(); }
  * list($head, $body) = message_handling::renderMessages($errors);
  * echo $head.$body;
  * echo "
  * </body>
  * </html>";
  * //* ***********************************************
  * // end example
  */

if(!defined("TARGET_WINDOW_NAME")){
	define("TARGET_WINDOW_NAME", "_messages" );
}

class message_handling{

	/** ***********************************************
	  * reports message in a file and returns a linkstring to the file
	  * or the str of rendered messages
	  *
	  * @param array $messages[] each message: array(string $type, string $message, string $info)
	  * @param string $toFile can be "./name.html" or "name.html" for relative path or absolute like dirname(__FILE__)."/name.html" no "../" allowed
	  * @param array $moreStyles -- see function getStyles()
	  * @param $prefix will be prepend to each $str to be translated
	  *
	  * @return $headStr with style+script $bodyStr
	  * or if isset $toFile
	  * @return $emptyHeadStr , $linkStr to open a separete Windows called messages
	  * ***********************************************
	  */
  function renderMessages($msgs, $toFile=false, $moreStyles=array(), $prefix=""){
	static $runOnce = false;
	static $fileRunOnce = false;
	if(!(is_array($msgs)) ||count($msgs)==0){
		return ""; //** --
	}

	$headStr = ($runOnce) ? "" : message_handling::_getStyle($moreStyles) . message_handling::_getScript();
	$bodyStr ="";

	$cnt = array();
	$loop = "";
	foreach($msgs as $msg){
		if(!isset($cnt[$msg[0]])){ $cnt[$msg[0]]=0; }
		$cnt[$msg[0]]++;
		$more = (trim($msg[2])!="")? "..." : "" ;
		$hand = (trim($msg[2])!="")? "hand" : "" ;
		$loop .= "<div  rel='{$msg[0]}' class='info normal'>";
		$loop .= "<div class='{$msg[0]} cap $hand' onclick='resiz(this)'>".message_handling::T_($prefix.$msg[0]."_".$msg[1])."&nbsp;$more</div><div class='trace'>{$msg[2]}</div>";
		$loop .= "</div>\n";
	}
	$tabs = "&nbsp;<span class='all hand' onclick='ishow(this, \"all\");'><b>".message_handling::T_($prefix."show_all_messages")."</b></span>&nbsp;&nbsp;&nbsp;";
	$title = "";
	foreach($cnt as $type=>$count){
		$title = message_handling::T_($prefix.$type)."=$count ";
		$tabs .= "<span class='$type hand' onclick='ishow(this, \"$type\");'>&nbsp;".message_handling::T_($prefix.$type)." <b>$count</b>&nbsp;</span>&nbsp;";
	}
	$bodyStr .= "<div ><div rel='top' class='info'>&nbsp;$tabs&nbsp;</div><div class='showmessages' >";
	$bodyStr .= $loop;
	$bodyStr .= "</div>\n</div>\n";
	$runOnce = false;

	if($toFile && trim($toFile)!=""){
		$toFile = str_replace("../", "", $toFile);
		$toFile = ( dirname($toFile)==".") ? dirname($_SERVER["REQUEST_URI"])."/".str_replace("./", "", $toFile) : $toFile ; //if relative
		$url = str_replace(realpath($_SERVER["DOCUMENT_ROOT"]), "", $toFile); //if absolute
		$file = str_replace("//", "/", realpath($_SERVER["DOCUMENT_ROOT"])."/". $url  );
		if(is_writable(dirname($file))){
			$append = ($fileRunOnce) ? FILE_APPEND : 0 ;
			file_put_contents($file, "<html>\n<head>\n$headStr\n</head>\n<body style='background-color:#CCC;'><h1>".date("h:i:s Y-m-d")."</h1>\n$bodyStr\n</body>\n</html>\n", $append );
			$fileRunOnce = true;
			return array("", "<a href='$url' target='".TARGET_WINDOW_NAME."' title='$title'>" . message_handling::T_($prefix."Open_Messages") . "</a>" ); //** --
		}
	}
	$runOnce = true;
	return array($headStr, $bodyStr);


  }

	/** ***********************************************
	  * call an external translation function if exists.
	  * the function must be declared by define("EXTERNAL_TRANSLATE_FUNCTION_NAME", "funcname");
	  * @param $str
	  * @return translated $str
	  * ***********************************************
	  */
	function T_($str){
		if(function_exists(EXTERNAL_TRANSLATE_FUNCTION_NAME)){
			return call_user_func(EXTERNAL_TRANSLATE_FUNCTION_NAME, $str );
		}else{
			return $str;
		}

	}

	/* ***********************************************
	 * create the needed script to fold and sort items exactly once
	 * @param -
	 * @return $headStr
	 * ***********************************************
	 */
  function _getScript(){
	$headStr = "<script>";
	$headStr .= "
	//==javascript==//
	function resiz(obj){
		switch(obj.parentNode.className){
			case 'info normal':
				obj.parentNode.className = 'info expanded';
			break;
			case 'info expanded':
				obj.parentNode.className = 'info normal';
			break;
		}
	}

	//==javascript==//
	function ishow(obj, type){
		var pp = obj;
		while(pp && typeof(pp.getAttribute('rel'))!='string' && pp.getAttribute('rel')!= 'info'){
			pp = pp.parentNode;
		}
		var p = pp.parentNode.getElementsByTagName('DIV');
		for(var i=0; i<p.length; i++){
			var xp = p[i];
			if(xp.className=='showmessages'){
				xp.style.display='block';
			}
			var u = xp.getAttribute('rel');
			if( ( typeof(u)=='string' && u==type ) || u=='top' || (type=='all' && typeof(u)=='string')){
				xp.style.display = 'block';
			}else if(typeof(u)=='string'){
				xp.style.display = 'none';
			}else{
			}
		}
	}
	";
	$headStr .= "</script>\n";
	return $headStr;
  }

	/** ***********************************************
	  * create the needed style exactly once
	  * @param array of additional styles key is classname with preceeding dot '.alert', value is the part between {} :color:red;
	  * @return $headStr
	  * ***********************************************
	  */
  function _getStyle($moreStyles=array()){
	$headStr ="";
	$headStr .= "  <style>";
	$headStr .= "
	 .info{font-family:verdana;  margin:1px; padding:1px; }
	 .info .cap{ font-size:10pt;}
	 .info .trace{font-family:courier; font-size:8pt; margin-left:10px;
	 	background-color:#EEE; padding:10px; border-top:solid 1px white; border-left:solid 1px white;}
	 .normal .trace{ display:none;}
	 .expanded .trace{display:block;}
	 .info .error{ background-color:#FDC; color:red; border:solid 1px red;}
	 .info .warning{ background-color:#EFD; border:solid 1px orange; }
	 .info .notice{ background-color:#CDF; border:solid 1px blue;}
	 .info .debug{ background-color:#DDD; border:solid 1px black;}
	 .cap{padding-left:10px;}
	 .showmessages{display:block;}
	 .more{}
	 .all{ border:solid 1px black; }
	 .hand{ cursor:hand; cursor:pointer; }
	";
	foreach($moreStyles as $name=>$style){
		$headStr .= "\t$name{".$style."}\n";
	}
	$headStr .= "  </style>\n";
	return $headStr;
  }

	/** ***********************************************
	  * a dummy function to test this class
	  * @param -
	  * @return messageArray($type, $message, $information);
	  * ***********************************************
	  */
  function doSample(){
	$sample = array("notice", "warning", "error", "alert");
	$trace = array(print_r($GLOBALS, true), print_r($_ENV, true), print_r($_SERVER, true), print_r(debug_backtrace(), true) );
	$messages = "HARLEZ-VOUS FRANCAIS? - Can you drive a French motorcycle?
	EX POST FUCTO - Lost in the mail
	IDIOS AMIGOS - We're wild and crazy guys
	VENI, VIPI, VICI - I came, I'm a very important person, I conquered
	COGITO, EGGO SUM - I think, therefore I waffle
	RIGOR MORRIS - The cat is dead
	RESPONDEZ S'IL VOUS PLAID - Honk if you're Scottish
	QUE SERA SERF - Life is feudal
	LE ROI EST MORT, JIVE LE ROI - The king is dead. No kidding
	POSH MORTEM - Death styles of the rich and famous
	PRO BOZO PUBLICO - Support your local clown
	MONAGE A TROIS - I am three years old
	FELIX NAVIDAD - Our cat has a boat
	HASTE CUISINE - Fast French food
	VENI, VIDI, VICE - I came, I saw, I partied
	QUIP PRO QUO - A fast retort
	ALOHA OY - Love; greetings, farewell; from such a pain you should never know
	MAZEL TON - Tons of good luck
	APRES MOE LE DELUGE - Curly and Larry got wet
	PORT-KOCHERE - Sacramental wine
	ICH LIEBE RICH - I'm really crazy about having dough
	FUI GENERIS - What's mine is mine
	VISA LA FRANCE - Don't leave your chateau without it
	CA VA SANS DIRT - And that's not gossip
	MERCI RIEN - Thanks for nothin'
	AMICUS PURIAE - Platonic friend
	";
	$msg = explode("\n", $messages);
	$normalData ="";
	return( array(
		array(
			$sample[ rand(0,3) ],
			$msg[ rand(0,count($msg)-1) ],
			$trace[ rand(0, count($trace)-1) ]
		) ,
		$normalData )
	);
  }
	//** end class
}
?>