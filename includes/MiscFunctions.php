<?php

/* $Id$*/

/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($Msg,$Type='info', $Prefix=''){

	echo getMsg($Msg, $Type, $Prefix);

}//prnMsg

function getMsg($Msg,$Type='info',$Prefix=''){
	$Colour='';
	if ($_SESSION['LogSeverity']>0) {
		$LogFile=fopen($_SESSION['LogPath'].'/webERP-test.log', 'a');
	}
	switch($Type){
		case 'error':
			$Class = 'error';
			$Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' ._('Message Report');
			if ($_SESSION['LogSeverity']>0) {
				fwrite($LogFile, date('Y-m-d h-m-s').','.$Type.','.$_SESSION['UserID'].','.trim($Msg,',')."\n");
			}
			break;
		case 'warn':
			$Class = 'warn';
			$Prefix = $Prefix ? $Prefix : _('WARNING') . ' ' . _('Message Report');
			if ($_SESSION['LogSeverity']>1) {
				fwrite($LogFile, date('Y-m-d h-m-s').','.$Type.','.$_SESSION['UserID'].','.trim($Msg,',')."\n");
			}
			break;
		case 'success':
			$Class = 'success';
			$Prefix = $Prefix ? $Prefix : _('SUCCESS') . ' ' . _('Report');
			if ($_SESSION['LogSeverity']>3) {
				fwrite($LogFile, date('Y-m-d h-m-s').','.$Type.','.$_SESSION['UserID'].','.trim($Msg,',')."\n");
			}
			break;
		case 'info':
		default:
			$Prefix = $Prefix ? $Prefix : _('INFORMATION') . ' ' ._('Message');
			$Class = 'info';
			if ($_SESSION['LogSeverity']>2) {
				fwrite($LogFile, date('Y-m-d h-m-s').','.$Type.','.$_SESSION['UserID'].','.trim($Msg,',')."\n");
			}
	}
	return '<DIV class="'.$Class.'"><B>' . $Prefix . '</B> : ' .$Msg . '</DIV>';
}//getMsg

function IsEmailAddress($email){

	$atIndex = strrpos ($email, "@");
	if ($atIndex === false)
	{
	    return  false;	// No @ sign is not acceptable.
	}

	if (preg_match('/\\.\\./', $email))
	    return  false;	// > 1 consecutive dot is not allowed.

	//  Check component length limits
	$domain = substr ($email, $atIndex+1);
	$local = substr ($email, 0, $atIndex);
	$localLen = strlen ($local);
	$domainLen = strlen ($domain);
	if ($localLen < 1 || $localLen > 64)
	{
	    // local part length exceeded
	    return  false;
	}
	if ($domainLen < 1 || $domainLen > 255)
	{
	    // domain part length exceeded
	    return  false;
	}

	if ($local[0] == '.' || $local[$localLen-1] == '.')
	{
	    // local part starts or ends with '.'
	    return  false;
	}
	if (!preg_match ('/^[A-Za-z0-9\\-\\.]+$/', $domain ))
	{
	    // character not valid in domain part
	    return  false;
	}
	if (!preg_match ('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
		     str_replace ("\\\\", "" ,$local ) ))
	{
	    // character not valid in local part unless local part is quoted
	    if (!preg_match ('/^"(\\\\"|[^"])+"$/',
					str_replace("\\\\", "", $local) ))
	    {
		return  false;
	    }
	}

	//  Check for a DNS 'MX' or 'A' record.
	//  Windows supported from PHP 5.3.0 on - so check.
	$ret = true;
	if (version_compare(PHP_VERSION, '5.3.0') >= 0
		    || strtoupper(substr(PHP_OS, 0, 3) !== 'WIN')) {
	    $ret = checkdnsrr( $domain, "MX" ) || checkdnsrr( $domain, "A" );
	}

	return  $ret;
}


function ContainsIllegalCharacters ($CheckVariable) {

	if (strstr($CheckVariable,"'")
		OR strstr($CheckVariable,'+')
		OR strstr($CheckVariable,"\"")
		OR strstr($CheckVariable,'&')
		OR strstr($CheckVariable,"\\")
		OR strstr($CheckVariable,'"')
		OR strstr($CheckVariable,' ')){

		return true;
	} else {
		return false;
	}
}


function pre_var_dump(&$var){
	echo "<div align=left><pre>";
	var_dump($var);
	echo "</pre></div>";
}



class XmlElement {
  var $name;
  var $attributes;
  var $content;
  var $children;
};

function GetECBCurrencyRates () {
/* See http://www.ecb.int/stats/exchange/eurofxref/html/index.en.html
for detail of the European Central Bank rates - published daily */
	if (file_exists('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml')) {
		$xml = file_get_contents('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $tags);
		xml_parser_free($parser);

		$elements = array();  // the currently filling [child] XmlElement array
		$stack = array();
		foreach ($tags as $tag) {
			$index = count($elements);
			if ($tag['type'] == "complete" || $tag['type'] == "open") {
				$elements[$index] = new XmlElement;
				$elements[$index]->name = $tag['tag'];
				$elements[$index]->attributes = $tag['attributes'];
				$elements[$index]->content = $tag['value'];
				if ($tag['type'] == "open") {  // push
					$elements[$index]->children = array();
					$stack[count($stack)] = &$elements;
					$elements = &$elements[$index]->children;
				}
			}
			if ($tag['type'] == "close") {  // pop
				$elements = &$stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}


		$Currencies = array();
		foreach ($elements[0]->children[2]->children[0]->children as $CurrencyDetails){
			$Currencies[$CurrencyDetails->attributes['currency']]= $CurrencyDetails->attributes['rate'] ;
		}
		$Currencies['EUR']=1; //ECB delivers no rate for Euro
		//return an array of the currencies and rates
		return $Currencies;
	} else {
		return false;
	}
}

function GetCurrencyRate($CurrCode,$CurrenciesArray) {
  if ((!isset($CurrenciesArray[$CurrCode]) or !isset($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]))){
  	return quote_oanda_currency($CurrCode);
  } elseif ($CurrCode=='EUR'){
  	if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]==0) {
  		return 0;
  	} else {
  		return 1/$CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
  	}
  }	else {
  	if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']]==0) {
  		return 0;
  	} else {
  		return $CurrenciesArray[$CurrCode]/$CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
  	}
  }
}

function quote_oanda_currency($CurrCode) {
	if (file_exists('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode .  '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault'])) {
		$page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode .  '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault']);
		$match = array();
		preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

			if ( sizeof($match) > 0 ){
				return $match[3];
			} else {
				return false;
		}
	}
}


function AddCarriageReturns($str) {
	return str_replace('\r\n',chr(10),$str);
}


function wikiLink($type, $id) {

	if ($_SESSION['WikiApp']==_('WackoWiki')){
		echo '<a target="_blank" href="../' . $_SESSION['WikiPath'] . '/' . $type .  $id . '">' . _('Wiki ' . $type . ' Knowlege Base') . '</A><BR>';
	} elseif ($_SESSION['WikiApp']==_('MediaWiki')){
		echo '<a target="_blank" href="../' . $_SESSION['WikiPath'] . '/index.php/' . $type . '/' .  $id . '">' . _('Wiki ' . $type . ' Knowlege Base') . '</A><BR>';
	}

}//wikiLink

function DecimalPlaces($currcode, $db) {
	$sql='SELECT decimalplaces FROM currencies WHERE currabrev="'.$currcode.'"';
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	return $myrow['decimalplaces'];
}

function StockDecimalPlaces($StockID, $db) {
	$sql='SELECT decimalplaces FROM stockmaster WHERE stockid="'.$StockID.'"';
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	return $myrow['decimalplaces'];
}


//  Lindsay debug stuff
function LogBackTrace( $dest = 0 ) {
    error_log( "***BEGIN STACK BACKTRACE***", $dest );

    $stack = debug_backtrace();
    //  Leave out our frame and the topmost - huge for xmlrpc!
    for( $ii = 1; $ii < count( $stack ) - 3; $ii++ )
    {
	$frame = $stack[$ii];
	$msg = "FRAME " . $ii . ": ";
	if( isset( $frame['file'] ) ) {
	    $msg .= "; file=" . $frame['file'];
	}
	if( isset( $frame['line'] ) ) {
	    $msg .= "; line=" . $frame['line'];
	}
	if( isset( $frame['function'] ) ) {
	    $msg .= "; function=" . $frame['function'];
	}
	if( isset( $frame['args'] ) ) {
	    // Either function args, or included file name(s)
	    $msg .= ' (';
	    foreach( $frame['args'] as $val ) {

			$typ = gettype( $val );
			switch( $typ ) {
				case 'array':
				    $msg .= '[ ';
				    foreach( $val as $v2 ) {
						if( gettype( $v2 ) == 'array' ) {
						    $msg .= '[ ';
						    foreach( $v2 as $v3 )
							$msg .= $v3;
						    $msg .= ' ]';
						} else {
						    $msg .= $v2 . ', ';
					    }
					    $msg .= ' ]';
					    break;
					}
				case 'string':
				    $msg .= $val . ', ';
				    break;

				case 'integer':
				    $msg .= sprintf( "%d, ", $val );
				    break;

				default:
				    $msg .= '<' . gettype( $val ) . '>, ';
				    break;

		    	}
		    $msg .= ' )';
			}
		}
	error_log( $msg, $dest );
    }

    error_log( '++++END STACK BACKTRACE++++', $dest );

    return;
}

function http_file_exists($url)  {
	return false;
	$f=@fopen($url,"r");
	if($f) {
		fclose($f);
		return true;
	}
	return false;
}

?>
