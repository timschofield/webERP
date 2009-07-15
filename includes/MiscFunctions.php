<?php


/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($Msg,$Type='info', $Prefix=''){

	echo getMsg($Msg, $Type, $Prefix);

}//prnMsg

function getMsg($Msg,$Type='info',$Prefix=''){
	$Colour='';
	switch($Type){
		case 'error':
			$Class = 'error';
			$Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' ._('Message Report');
			break;
		case 'warn':
			$Class = 'warn';
			$Prefix = $Prefix ? $Prefix : _('WARNING') . ' ' . _('Message Report');
			break;
		case 'success':
			$Class = 'success';
			$Prefix = $Prefix ? $Prefix : _('SUCCESS') . ' ' . _('Report');
			break;
		case 'info':
		default:
			$Prefix = $Prefix ? $Prefix : _('INFORMATION') . ' ' ._('Message');
			$Class = 'info';
	}
	return '<DIV class="'.$Class.'"><B>' . $Prefix . '</B> : ' .$Msg . '</DIV>';
}//getMsg

function IsEmailAddress($TestEmailAddress){

/*thanks to Gavin Sharp for this regular expression to test validity of email addresses */

	if (function_exists('preg_match')){
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


Function ContainsIllegalCharacters ($CheckVariable) {

	if (strstr($CheckVariable,"'")
		OR strstr($CheckVariable,'+')
		OR strstr($CheckVariable,"\"")
		OR strstr($CheckVariable,'&')
		OR strstr($CheckVariable,"\\")
		OR strstr($CheckVariable,'"')){

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
	$page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode .  '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault']);
	$match = array();
	preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

	if ( sizeof($match) > 0 ){
      	  	return $match[3];
	} else {
      	   	return false;
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

?>
