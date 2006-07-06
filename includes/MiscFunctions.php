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

function IsEmailAddress($TestEmailAddress){

/*thanks to Gavin Sharp for this regular expression to test validity of email addresses */

	if (function_exists("preg_match")){
		if(preg_match("/^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$/", $TestEmailAddress)){
			return true;
		} else {
			return false;
		}
	} else {
		if (strlen($TestEmailAddress)>5 AND strstr($TestEmailAddress,'@')>2 AND (strstr($TestEmailAddress,'.co')>3 OR strstr($TestEmailAddress,'.org')>3 OR strstr($TestEmailAddress,'.net')>3 OR strstr($TestEmailAddress,'.edu')>3 OR strstr($TestEmailAddress,'.biz')>3)){
			return true;
		} else {
			return false;
		}
	}
}

function pre_var_dump(&$var){
	echo "<div align=left><pre>"; 
	var_dump($var); 
	echo "</pre></div>";
}


function ConvertNumberToWords( $num ){

// convert long integer into American English words.
// e.g. -12345 -> "minus twelve thousand forty-five"
// Handles negative and positive integers
// on range -Long.MAX_VALUE .. Long.MAX_VALUE;
// It cannot handle Long.MIN_VALUE;

   $ZERO = _('zero');
   $MINUS = _('minus');

   // zero is shown as "" since it is never used in combined forms
   // 0 .. 19
   $lowName = array(_(''), 
			_('one'), 
			_('two'), 
			_('three'), 
			_('four'), 
			_('five'),
         		_('six'), 
			_('seven'), 
			_('eight'), 
			_('nine'),
			_('ten'),
			_('eleven'),
			_('twelve'), 
			_('thirteen'),
			_('fourteen'),
			_('fifteen'),
			_('sixteen'), 
			_('seventeen'), 
			_('eighteen'),
			_('nineteen')
		);

   // 0, 10, 20, 30 ... 90
   $tys = array('',
		'', 
		_('twenty'),
		_('thirty'),
		_('forty'),
		_('fifty'),
		_('sixty'),
		_('seventy'),
		_('eighty'),
		_('ninety')
		);

   // We only need up to a quintillion, since a long is about 9 * 10 ^ 18
   // American: unit, hundred, thousand, million, billion, trillion, quadrillion, quintillion
   $groupName = array('',
			_('hundred'),
			_('thousand'),
			_('million'), 
			_('billion'),
         		_('trillion'),
			_('quadrillion'),
			_('quintillion')
			);

   // How many of this group is needed to form one of the succeeding group.
   // American: unit, hundred, thousand, million, billion, trillion, quadrillion, quintillion
   $divisor = array(100, 
			10, 
			1000, 
			1000, 
			1000, 
			1000, 
			1000, 
			1000) ;

   $num = str_replace(",","",$num);
   $num = number_format($num,2,'.','');
   $cents = substr($num,strlen($num)-2,strlen($num)-1);
   $num = (int)$num;

   $s = "";

   if ( $num == 0 ) $s = $ZERO;
   $negative = ($num < 0 );
   if ( $negative ) $num = -$num;

   // Work least significant digit to most, right to left.
   // until high order part is all 0s.
   for ( $i=0; $num>0; $i++ ) {
       $remdr = (int)($num % $divisor[$i]);
       $num = $num / $divisor[$i];
       // check for 1100 .. 1999, 2100..2999, ... 5200..5999
       // but not 1000..1099,  2000..2099, ...
       // Special case written as fifty-nine hundred.
       // e.g. thousands digit is 1..5 and hundreds digit is 1..9
       // Only when no further higher order.
       
       // doing hundreds
			 if ( $i == 1 && 1 <= $num && $num <= 5 ){
           if ( $remdr > 0 ){
               $remdr += $num * 10;
               $num = 0;
           } // end if
       } // end if
       if ( $remdr == 0 ){
           continue;
       }
       $t = "";
       if ( $remdr < 20 ){
           $t = $lowName[$remdr];
       }
       else if ( $remdr < 100 ){
           $units = (int)$remdr % 10;
           $tens = (int)$remdr / 10;
           $t = $tys [$tens];
           if ( $units != 0 ){
               $t .= "-" . $lowName[$units];
           }
       }else {
           $t = $inWords($remdr);
       }
       $s = $t . " " . $groupName[$i] . " "  . $s;
       $num = (int)$num;
   } // end for
   $s = trim($s);
   if ( $negative ){
       $s = $MINUS . " " . $s;
   }

   $s .= " and $cents/100";

   return $s;

} // end ConvertNumberToWords
?>
