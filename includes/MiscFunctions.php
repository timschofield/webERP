<?php


/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($msg,$type='info', $prefix=''){

	echo '<P>' . getMsg($msg, $type, $prefix) . '</P>';

}//prnMsg

function getMsg($msg,$type='info',$prefix=''){
	$Colour='';
	switch($type){
		case 'error':
			$Colour='red';
			$prefix = $prefix ? $prefix : _('ERROR Message Report');
			break;
		case 'warn':
			$Colour='maroon';
			$prefix = $prefix ? $prefix : _('WARNING Message Report');
			break;
		case 'success':
			$Colour='darkgreen';
			$prefix = $prefix ? $prefix : _('SUCCESS Report');
			break;
		case 'info':
		default:
			$prefix = $prefix ? $prefix : _('INFORMATION Message');
			$Colour='navy';
	}
	return '<font color="' . $Colour . '"><b>' . $prefix . '</b> : ' .$msg . '</font>';
}//getMsg

?>
