<?php


/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($msg,$type="info", $prefix=""){

	echo getMsg($msg, $type, $prefix);

}//prnMsg

function getMsg($msg,$type="info",$prefix=""){
	$Colour='';
	switch($type){
		case "error":
			$Colour='red';
			break;
		case "warn":
			$Colour='maroon';
			break;
		case "success":
			$Colour='#336600';
			break;
		case "info":
		default:
			$Colour='navy';
	}
	return "<font color=" . $Colour . "><b>" . $prefix . "</b> : " .$msg . "</font>";
}//getMsg

?>