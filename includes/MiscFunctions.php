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
			$prefix = "ERROR Message Report";
			break;
		case "warn":
			$Colour='maroon';
			$prefix = "WARNING Message Report";
			break;
		case "success":
			$Colour='#336600';
			$prefix = "Success Report";
			break;
		case "info":
		default:
			$prefix = "INFORMATION Message";
			$Colour='navy';
	}
	return "<font color=" . $Colour . "><b>" . $prefix . "</b> : " .$msg . "</font>";
}//getMsg

?>
