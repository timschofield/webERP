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
			$prefix = $prefix ? $prefix : _('ERROR') . ' ' ._('Message Report');
			break;
		case 'warn':
			$Colour='maroon';
			$prefix = $prefix ? $prefix : _('WARNING') . ' ' . _('Message Report');
			break;
		case 'success':
			$Colour='darkgreen';
			$prefix = $prefix ? $prefix : _('SUCCESS') . ' ' . _('Report');
			break;
		case 'info':
		default:
			$prefix = $prefix ? $prefix : _('INFORMATION') . ' ' ._('Message');
			$Colour='navy';
	}
	return '<FONT COLOR="' . $Colour . '"><B>' . $prefix . '</B> : ' .$msg . '</FONT>';
}//getMsg

?>
