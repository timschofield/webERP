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
			$prefix = _('ERROR Message Report');
			break;
		case "warn":
			$Colour='maroon';
			$prefix = _('WARNING Message Report');
			break;
		case "success":
			$Colour='#336600';
			$prefix = _('Success Report');
			break;
		case "info":
		default:
			$prefix = _('INFORMATION Message');
			$Colour='navy';
	}
	return "<P><TABLE><TR><TD><font color=" . $Colour . "><b>" . $prefix . "</b> : " .$msg . "</font></TD></TR></TABLE>";
}//getMsg

?>
