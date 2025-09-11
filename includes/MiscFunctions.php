<?php

/*************************************************************
 * ******************** FUNCTION INDEX ********************
 * AddCarriageReturns - Adds carriage returns to a string
 * ChangeFieldInTable - Changes value of specific field across a table
 * checkLanguageChoice - Validates language choice format
 * ContainsIllegalCharacters - Checks if a string contains special characters
 * Convert_CRLF - Replaces text line breaks with specified line break
 * Convert_line_breaks - Replaces HTML and text line breaks with specified line break
 * fShowFieldHelp - Shows field help text based on session settings
 * fShowPageHelp - Shows page help text based on session settings
 * FYStartPeriod - Gets starting period for fiscal year
 * GetCurrencyRate - Calculates currency exchange rate
 * GetECBCurrencyRates - Gets currency rates from European Central Bank
 * GetMailList - Gets email list for a mail group
 * google_currency_rate - Gets currency rate from Google Finance
 * http_file_exists - Checks if a URL exists
 * indian_number_format - Formats numbers in Indian numbering system
 * IsEmailAddress - Validates email address format
 * locale_number_format - Formats numbers according to locale
 * LogBackTrace - Logs debug backtrace information
 * filter_number_format - Converts formatted number to SQL format
 * prnMsg - Displays formatted messages
 * PrintCompanyTo - Prints company info on PDF
 * PrintDetail - Prints text detail on PDF with page break handling
 * PrintDeliverTo - Prints delivery info on PDF
 * PrintOurCompanyInfo - Prints company info in PDF format
 * quote_oanda_currency - Gets currency exchange rate from Oanda
 * ReportPeriod - Determines date period for reports
 * ReportPeriodList - Generates period selection list for reports
 * reverse_escape - Reverses escaped strings
 * SendEmailBySmtp - Sends email using SMTP
 * SendEmailByStandardMailFunction - Sends email using PHP mail function
 * SendEmailFromWebERP - Main email sending function for WebERP
 * ShowDebugBackTrace - Shows the debug backtrace information if debugging is enabled
 * wikiLink - Generates wiki application links
 * XmlElement - Class for XML elements in currency rate parsing
 * ******************** END FUNCTION INDEX ********************
 */

use PHPMailer\PHPMailer\PHPMailer;

/** STANDARD MESSAGE HANDLING & FORMATTING **/
/*  ******************************************  */

function prnMsg($Msg, $Type = 'info', $Prefix = '', $Return = false) {
	global $Messages;
    if($Return){
        $Prefix = $Type == 'info'
            ? __('INFORMATION') . ' ' . __('Message')
            : ($Type == 'warning' || $Type == 'warn'
                ? __('WARNING') . ' ' . __('Report')
                : ($Type == 'error'
                    ? __('ERROR') . ' ' . __('Report')
                    : __('SUCCESS') . ' ' . __('Report')
                )
            );
        return '<div id="MessageContainerFoot">
				<div class="Message '. $Type . ' noPrint">
					<span class="MessageCloseButton">&times;</span>
					<b>'. $Prefix . '</b> : ' .  $Msg . '
				</div>
			</div>';
    }
    else{
        $Messages[] = array($Msg, $Type, $Prefix);
    }
}

function reverse_escape($str) {
	if (is_null($str)) {
		$str = '';
	}

	$Search = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"');
	$Replace = array("\\", "\0", "\n", "\r", "\x1a", "'", '"');
	return str_replace($Search, $Replace, $str);
}

function IsEmailAddress($Email) {
	$AtIndex = strrpos($Email, "@");
	if ($AtIndex == false) {
		return false; // No @ sign is not acceptable.
	}
	if (preg_match('/\\.\\./', $Email)) {
		return false; // > 1 consecutive dot is not allowed.
	}
	//  Check component length limits
	$Domain = mb_substr($Email, $AtIndex + 1);
	$Local = mb_substr($Email, 0, $AtIndex);
	$LocalLen = mb_strlen($Local);
	$DomainLen = mb_strlen($Domain);
	if ($LocalLen < 1 || $LocalLen > 64) {
		// local part length exceeded
		return false;
	}
	if ($DomainLen < 1 || $DomainLen > 255) {
		// domain part length exceeded
		return false;
	}
	if ($Local[0] == '.' or $Local[$LocalLen - 1] == '.') {
		// local part starts or ends with '.'
		return false;
	}
	if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $Domain)) {
		// character not valid in domain part
		return false;
	}
	if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $Local))) {
		// character not valid in local part unless local part is quoted
		if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $Local))) {
			return false;
		}
	}
	//  Check for a DNS 'MX' or 'A' record.
	//  Windows supported from PHP 5.3.0 on - so check.
	$Ret = true;
	/*  Apparently causes some problems on some versions - perhaps bleeding edge just yet
	if (version_compare(PHP_VERSION, '5.3.0') >= 0 or mb_strtoupper(mb_substr(PHP_OS, 0, 3) !== 'WIN')) {
		$Ret = checkdnsrr($Domain, 'MX') or checkdnsrr($Domain, 'A');
	}
	*/
	return $Ret;
}

function ContainsIllegalCharacters($CheckVariable) {
	if (mb_strstr($CheckVariable, "'") or mb_strstr($CheckVariable, '+') or mb_strstr($CheckVariable, '?') or
		mb_strstr($CheckVariable, '.') or mb_strstr($CheckVariable, "\"") or mb_strstr($CheckVariable, '&') or
		mb_strstr($CheckVariable, "\\") or mb_strstr($CheckVariable, '"') or mb_strstr($CheckVariable, '>') or
		mb_strstr($CheckVariable, '<')) {
		return true;
	} else {
		return false;
	}
}

class XmlElement {
	var $name;
	var $attributes;
	var $Content;
	var $children;
}

function GetECBCurrencyRates() {
	/* See http://www.ecb.int/stats/exchange/eurofxref/html/index.en.html
	for detail of the European Central Bank rates - published daily */
	/// @todo file_get_contents might be disabled for remote files. Use a better api: curl or sockets
	if (http_file_exists('https://www.ecb.int/stats/eurofxref/eurofxref-daily.xml')) {
		$xml = file_get_contents('https://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $Tags);
		xml_parser_free($parser);

		$elements = array(); // the currently filling [child] XmlElement array
		$stack = array();
		foreach ($Tags as $Tag) {
			$index = count($elements);
			if ($Tag['type'] == 'complete' or $Tag['type'] == 'open') {
				$elements[$index] = new XmlElement;
				$elements[$index]->name = $Tag['tag'];
				if (isset($Tag['attributes'])) {
					$elements[$index]->attributes = $Tag['attributes'];
				}
				if ($Tag['type'] == 'open') { // push
					$elements[$index]->children = array();
					$stack[count($stack)] = & $elements;
					$elements = & $elements[$index]->children;
				}
			}
			if ($Tag['type'] == 'close') { // pop
				$elements = & $stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}
		$Currencies = array();
		foreach ($elements[0]->children[2]->children[0]->children as $CurrencyDetails) {
			$Currencies[$CurrencyDetails->attributes['currency']] = $CurrencyDetails->attributes['rate'];
		}
		$Currencies['EUR'] = 1; //ECB delivers no rate for Euro
		//return an array of the currencies and rates
		return $Currencies;
	} else {
		return array();
	}
}

function GetCurrencyRate($CurrCode, $CurrenciesArray) {
	if ((!isset($CurrenciesArray[$CurrCode]) or !isset($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']])) and $_SESSION['UpdateCurrencyRatesDaily'] != '0') {
		return quote_oanda_currency($CurrCode);
	} elseif ($CurrCode == 'EUR') {
		if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']] == 0) {
			return 0;
		} else {
			return 1 / $CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
		}
	} else {
		if ($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']] == 0) {
			return 0;
		} else {
			return $CurrenciesArray[$CurrCode] / $CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']];
		}
	}
}

function quote_oanda_currency($CurrCode) {
	if (http_file_exists('//www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode . '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault'])) {
		/// @todo file_get_contents and co. might be disabled for remote files. Use a better api: curl or sockets
		$page = file('//www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode . '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault']);
		$match = array();
		preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);
		if (sizeof($match) > 0) {
			return $match[3];
		} else {
			return false;
		}
	}
}

function google_currency_rate($CurrCode) {
	$Rate = 0;
	$PageLines = file('//www.google.com/finance/converter?a=1&from=' . $_SESSION['CompanyRecord']['currencydefault'] . '&to=' . $CurrCode);
	foreach ($PageLines as $Line) {
		if (mb_strpos($Line, 'currency_converter_result')) {
			$Length = mb_strpos($Line, '</span>') - 58;
			$Rate = floatval(mb_substr($Line, 58, $Length));
		}
	}
	return $Rate;
}

function AddCarriageReturns($str) {
	return str_replace('\r\n', chr(10), $str);
}

/// Replace all text/html line breaks with PHP_EOL(default) or given line break.
function Convert_line_breaks($string, $Line_break=PHP_EOL)
{
    $patterns = array(  "/(<br>|<br \/>|<br\/>)\s*/i",
                        "/(\r\n|\r|\n)/" );
    $Replacements = array(  $Line_break,
                            $Line_break );
    $string = preg_replace($patterns, $Replacements, $string);
    return $string;
}
/// Replace all text line breaks with PHP_EOL(default) or given line break.
function Convert_CRLF($string, $Line_break=PHP_EOL)
{
    $patterns = array(  "/(\r\n|\r|\n)/" );
    $Replacements = array(  $Line_break );
    $string = preg_replace($patterns, $Replacements, $string);
    return $string;
}

//NPFunc - New Page Function, can be a direct function call or an anonymous function for more complex behavior
//         Null if not used
//NPINC  - New Page Include, where a PHP script is included again to facilitate a new page
//         Null if not used
//&$YPos - return the updated value
//         Coming in, YPos=prior line, so update it before we print anything, and don't update it if we don't print anything
//Defaults come from addTextWrap
function PrintDetail($PDF,$Text,$YLim,$XPos,&$YPos,$Width,$FontSize,$NPFunc=null,$NPInc=null,$Align='J',$border=0,$fill=0)
{
	$InitialExtraSpace=2;		//shift down slightly from above text

	$Text=Convert_line_breaks(htmlspecialchars_decode($Text));
	$Split = explode(PHP_EOL, $Text);
	foreach ($Split as $LeftOvers) {
		$LeftOvers = stripslashes($LeftOvers);
		while(mb_strlen($LeftOvers)>1) {
			if ($YPos < $YLim) {// If the description line reaches the bottom margin, do PageHeader(), PageInclude(), etc.
				if($NPFunc!=null) {
					$NPFunc();
				}
				if($NPInc!=null) {
					include($NPInc);
				}
			}
			$YPos=$YPos-$FontSize-$InitialExtraSpace;
			$InitialExtraSpace=0;
			$LeftOvers = $pdf->addTextWrap($XPos, $YPos, $Width, $FontSize, $LeftOvers, $Align, $border, $fill);
		}
	}
}

function PrintOurCompanyInfo($PDF,$CompanyRecord,$XPos,$YPos)
{
	$CompanyRecord = array_map('html_entity_decode', $CompanyRecord);

	$FontSize = 14;
	$pdf->addText($XPos, $YPos, $FontSize, $CompanyRecord['coyname']);
	$YPos -= $FontSize;
	$FontSize = 10;

	//webERP default:
	$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
	$pdf->addText($XPos, $YPos-$FontSize*1, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
	$pdf->addText($XPos, $YPos-$FontSize*2, $FontSize, $_SESSION['CompanyRecord']['regoffice3']);
	$pdf->addText($XPos, $YPos-$FontSize*3, $FontSize, $_SESSION['CompanyRecord']['regoffice4']);
	$pdf->addText($XPos, $YPos-$FontSize*4, $FontSize, $_SESSION['CompanyRecord']['regoffice5'] .
		' ' . $_SESSION['CompanyRecord']['regoffice6']);
	$pdf->addText($XPos, $YPos-$FontSize*5, $FontSize,  __('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] .
		' ' . __('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
	$pdf->addText($XPos, $YPos-$FontSize*6, $FontSize, $_SESSION['CompanyRecord']['email']);
}

// Generically move down 82 units after printing this
function PrintDeliverTo($PDF,$CompanyRecord,$Title,$XPos,$YPos)
{
	$CompanyRecord = array_map('html_entity_decode', $CompanyRecord);

	$FontSize = 14;
	$LineHeight=15;
	$pdf->addText($XPos, $YPos,$FontSize, $Title . ':' );

	//webERP default:
	$pdf->addText($XPos, $YPos-15,$FontSize, $CompanyRecord['deliverto']);
	$pdf->addText($XPos, $YPos-30,$FontSize, $CompanyRecord['deladd1']);
	$pdf->addText($XPos, $YPos-45,$FontSize, $CompanyRecord['deladd2']);
	$pdf->addText($XPos, $YPos-60,$FontSize, ltrim($CompanyRecord['deladd3'] . ' ' . $CompanyRecord['deladd4'] . ' ' . $CompanyRecord['deladd5'] . ' ' . $CompanyRecord['deladd6']));

	// Draws a box with round corners around 'Delivery To' info:
	$pdf->RoundRectangle(
		$XPos-6,// RoundRectangle $XPos.
		$YPos+2,// RoundRectangle $YPos.
		245,// RoundRectangle $Width.
		80,// RoundRectangle $Height.
		10,// RoundRectangle $RadiusX.
		10);// RoundRectangle $RadiusY.
}

// Generically move down 82 units after printing this
function PrintCompanyTo($PDF,$CompanyRecord,$Title,$XPos,$YPos)
{
	$CompanyRecord = array_map('html_entity_decode', $CompanyRecord);

	$FontSize = 14;
	$LineHeight=15;
	$pdf->addText($XPos, $YPos,$FontSize, $Title . ':' );

	//webERP default:
	$pdf->addText($XPos, $YPos-15,$FontSize, $CompanyRecord['name']);
	$pdf->addText($XPos, $YPos-30,$FontSize, $CompanyRecord['address1']);
	$pdf->addText($XPos, $YPos-45,$FontSize, $CompanyRecord['address2']);
	$pdf->addText($XPos, $YPos-60,$FontSize, $CompanyRecord['address3'] . ' ' . $CompanyRecord['address4'] . ' ' . $CompanyRecord['address5']. ' ' . $CompanyRecord['address6']);

	// Draws a box with round corners around 'Delivery To' info:
	$pdf->RoundRectangle(
		$XPos-6,// RoundRectangle $XPos.
		$YPos+2,// RoundRectangle $YPos.
		245,// RoundRectangle $Width.
		80,// RoundRectangle $Height.
		10,// RoundRectangle $RadiusX.
		10);// RoundRectangle $RadiusY.
}

/// Assemble URL for configured Wiki Application
function wikiLink($WikiType, $WikiPageID) {
	if (strstr($_SESSION['WikiPath'], 'http:')) {
		$WikiPath = $_SESSION['WikiPath'];
	} elseif (strstr($_SESSION['WikiPath'], 'https:')) {
		$WikiPath = $_SESSION['WikiPath'];
	} else {
		$WikiPath = '../' . $_SESSION['WikiPath'] . '/';
	}

	if ($_SESSION['WikiApp'] == __('WackoWiki')) {
		echo '<a target="_blank" href="' . $WikiPath . $WikiType . $WikiPageID . '">' . __('Wiki ' . $WikiType . ' Knowledge Base') . ' </a>  <br />';
	} elseif ($_SESSION['WikiApp'] == __('MediaWiki')) {
		echo '<a target="_blank" href="' . $WikiPath . 'index.php?title=' . $WikiType . '/' . $WikiPageID . '">' . __('Wiki ' . $WikiType . ' Knowledge Base') . '</a><br />';
	} elseif ($_SESSION['WikiApp'] == __('DokuWiki')) {
		echo '<a target="_blank" href="' . $WikiPath . '/doku.php?id=' . $WikiType . ':' . $WikiPageID . '">' . __('Wiki ' . $WikiType . ' Knowledge Base') . '</a><br />';
	}
}

// Lindsay debug stuff
function LogBackTrace($dest = 0) {

	$stack = debug_backtrace();
	error_log("***BEGIN STACK BACKTRACE***", $dest);
	//  Leave out our frame and the topmost - huge for xmlrpc!
	for ($ii = 1;$ii < count($stack) - 3;$ii++) {
		$frame = $stack[$ii];
		$Msg = "FRAME " . $ii . ": ";
		if (isset($frame['file'])) {
			$Msg.= "; file=" . $frame['file'];
		}
		if (isset($frame['line'])) {
			$Msg.= "; line=" . $frame['line'];
		}
		if (isset($frame['function'])) {
			$Msg.= "; function=" . $frame['function'];
		}
		if (isset($frame['args'])) {
			// Either function args, or included file name(s)
			$Msg.= ' (';
			foreach ($frame['args'] as $val) {
				$typ = gettype($val);
				switch ($typ) {
					case 'array':
						$Msg.= '[ ';
						foreach ($val as $v2) {
							if (gettype($v2) == 'array') {
								$Msg.= '[ ';
								foreach ($v2 as $v3) $Msg.= $v3;
								$Msg.= ' ]';
							} else {
								$Msg.= $v2 . ', ';
							}
							$Msg.= ' ]';
							break;
						}
					case 'string':
						$Msg.= $val . ', ';
						break;

					case 'integer':
						$Msg.= sprintf("%d, ", $val);
						break;

					default:
						$Msg.= '<' . gettype($val) . '>, ';
						break;

					}
					$Msg.= ' )';
			}
		}
		error_log($Msg, $dest);
	}

	error_log('++++END STACK BACKTRACE++++', $dest);
}

function http_file_exists($url) {
	/// @todo send a proper HEAD request
	$f = @fopen($url, 'r');
	if ($f) {
		fclose($f);
		return true;
	}
	return false;
}

/*Functions to display numbers in locale of the user */

function locale_number_format($Number, $DecimalPlaces = 0) {
	global $DecimalPoint;
	global $ThousandsSeparator;
	if ($DecimalPlaces == null) $DecimalPlaces = 0;
	if (substr($_SESSION['Language'], 3, 2) == 'IN') { // If country is India (??_IN.utf8). See Indian Numbering System in Manual, Multilanguage, Technical Overview.
		return indian_number_format(floatval($Number), $DecimalPlaces);
	} else {
		if (!is_numeric($DecimalPlaces) and $DecimalPlaces == 'Variable') {
			$DecimalPlaces = mb_strlen($Number) - mb_strlen(intval($Number));
			if ($DecimalPlaces > 0) {
				$DecimalPlaces--;
			}
		}
		return number_format(floatval($Number), $DecimalPlaces, $DecimalPoint, $ThousandsSeparator);
	}
}

/* and to parse the input of the user into useable number */

function filter_number_format($Number) {
	global $DecimalPoint;
	global $ThousandsSeparator;
	$SQLFormatNumber = str_replace($DecimalPoint, '.', str_replace($ThousandsSeparator, '', trim($Number)));
	/*It is possible if the user entered the $DecimalPoint as a thousands separator and the $DecimalPoint is a comma that the result of this could contain several periods "." so need to ditch all but the last "." */
	if (mb_substr_count($SQLFormatNumber, '.') > 1) {
		return str_replace('.', '', mb_substr($SQLFormatNumber, 0, mb_strrpos($SQLFormatNumber, '.'))) . mb_substr($SQLFormatNumber, mb_strrpos($SQLFormatNumber, '.'));

		echo '<br /> Number of periods: ' . $NumberOfPeriods . ' $SQLFormatNumber = ' . $SQLFormatNumber;

	} else {
		return $SQLFormatNumber;
	}
}

function indian_number_format($Number, $DecimalPlaces) {
	$IntegerNumber = intval($Number);
	$DecimalValue = $Number - $IntegerNumber;
	if ($DecimalPlaces != 'Variable') {
		$DecimalValue = round($DecimalValue, $DecimalPlaces);
	}
	if ($DecimalPlaces != 'Variable' and strlen(substr($DecimalValue, 2)) > 0) {
		/*If the DecimalValue is longer than '0.' then chop off the leading 0*/
		$DecimalValue = substr($DecimalValue, 1);
		if ($DecimalPlaces > 0) {
			$DecimalValue = str_pad($DecimalValue, $DecimalPlaces, '0');
		} else {
			$DecimalValue = '';
		}
	} else {
		if ($DecimalPlaces != 'Variable' and $DecimalPlaces > 0) {
			$DecimalValue = '.' . str_pad($DecimalValue, $DecimalPlaces, '0');
		} elseif ($DecimalPlaces == 0) {
			$DecimalValue = '';
		}
	}
	if (strlen($IntegerNumber) > 3) {
		$LastThreeNumbers = substr($IntegerNumber, strlen($IntegerNumber) - 3, strlen($IntegerNumber));
		$RestUnits = substr($IntegerNumber, 0, strlen($IntegerNumber) - 3); // extracts the last three digits
		$RestUnits = ((strlen($RestUnits) % 2) == 1) ? '0' . $RestUnits : $RestUnits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
		$FirstPart = '';
		$ExplodedUnits = str_split($RestUnits, 2);
		for ($i = 0;$i < sizeof($ExplodedUnits);$i++) {
			if ($i == 0) {
				$FirstPart.= intval($ExplodedUnits[$i]) . ','; // creates each of the 2's group and adds a comma to the end

			} else {
				$FirstPart.= $ExplodedUnits[$i] . ',';

			}
		}
		return $FirstPart . $LastThreeNumbers . $DecimalValue;
	} else {
		return $IntegerNumber . $DecimalValue;
	}
}

function GetMailList($MailGroup) {
	$ToList = array();
	$SQL = "SELECT email,realname
			FROM mailgroupdetails INNER JOIN www_users
			ON www_users.userid=mailgroupdetails.userid
			WHERE mailgroupdetails.groupname='" . $MailGroup . "'";
	$ErrMsg = __('Failed to retrieve mail lists');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) != 0) {
		//Create the string which meets the Recipients requirements
		while ($MyRow = DB_fetch_array($Result)) {
			$ToList[$MyRow['email']] = $MyRow['realname'];
		}
	}
	return $ToList;
}

function ChangeFieldInTable($TableName, $FieldName, $OldValue, $NewValue) {
	/* Used in Z_ scripts to change one field across the table.
	*/
	echo '<br />' . __('Changing') . ' ' . $TableName . ' ' . __('records');
	$SQL = "UPDATE " . $TableName . " SET " . $FieldName . " ='" . $NewValue . "' WHERE " . $FieldName . "='" . $OldValue . "'";
	$ErrMsg = __('The SQL to update' . ' ' . $TableName . ' ' . __('records failed'));
	$Result = DB_query($SQL, $ErrMsg, '', true);
	echo ' ... ' . __('completed');
}

/* Used in report scripts for standard periods.
 * Parameter $Choice is from the 'Period' combobox value.
*/
function ReportPeriodList($Choice, $Options = array('t', 'l', 'n')) {
	$Periods = array();

	if (in_array('t', $Options)) {
		$Periods[] = __('This Month');
		$Periods[] = __('This Year');
		$Periods[] = __('This Financial Year');
	}

	if (in_array('l', $Options)) {
		$Periods[] = __('Last Month');
		$Periods[] = __('Last Year');
		$Periods[] = __('Last Financial Year');
	}

	if (in_array('n', $Options)) {
		$Periods[] = __('Next Month');
		$Periods[] = __('Next Year');
		$Periods[] = __('Next Financial Year');
	}

	$Count = count($Periods);

	$HTML = '<select name="Period">
				<option value=""></option>';

	for ($x = 0;$x < $Count;++$x) {
		if (!empty($Choice) && $Choice == $Periods[$x]) {
			$HTML.= '<option value="' . $Periods[$x] . '" selected>' . $Periods[$x] . '</option>';
		} else {
			$HTML.= '<option value="' . $Periods[$x] . '">' . $Periods[$x] . '</option>';
		}
	}

	$HTML.= '</select>';

	return $HTML;
}

function ReportPeriod($PeriodName, $FromOrTo) {
	/* Used in report scripts to determine period.
	*/
	$ThisMonth = date('m');
	$ThisYear = date('Y');
	$LastMonth = $ThisMonth - 1;
	if ($LastMonth == 0) {
		$LastMonth = 12;
	}
	$LastYear = $ThisYear - 1;
	$NextMonth = $ThisMonth + 1;
	if ($NextMonth == 13) {
		$NextMonth = 1;
	}
	$NextYear = $ThisYear + 1;
	// Find total number of days in this month:
	$TotalDays = cal_days_in_month(CAL_GREGORIAN, $ThisMonth, $ThisYear);
	// Find total number of days in last month:
	$TotalDaysLast = cal_days_in_month(CAL_GREGORIAN, $LastMonth, $ThisYear);
	// Find total number of days in next month:
	$TotalDaysNext = cal_days_in_month(CAL_GREGORIAN, $NextMonth, $ThisYear);
	switch ($PeriodName) {
		case __('This Month'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $ThisMonth, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $ThisMonth, $TotalDays, $ThisYear));
		break;
		case __('This Quarter'):
			$QtrStrt = intval(($ThisMonth - 1) / 3) * 3 + 1;
			$QtrEnd = intval(($ThisMonth - 1) / 3) * 3 + 3;
			if ($QtrEnd == 4 or $QtrEnd == 6 or $QtrEnd == 9 or $QtrEnd == 11) {
				$TotalDays = 30;
			}
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrStrt, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrEnd, $TotalDays, $ThisYear));
		break;
		case __('This Year'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 1, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 12, 31, $ThisYear));
		break;
		case __('This Financial Year'):
			if (Date('m') > $_SESSION['YearEnd']) {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y')));
			} else {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y') - 1));
			}
			$DateEnd = date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], 0));
		break;
		case __('Last Month'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $LastMonth, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $LastMonth, $TotalDaysLast, $ThisYear));
		break;
		case __('Last Quarter'):
			$QtrStrt = intval(($ThisMonth - 1) / 3) * 3 - 2;
			$QtrEnd = intval(($ThisMonth - 1) / 3) * 3 + 0;
			if ($QtrEnd == 4 or $QtrEnd == 6 or $QtrEnd == 9 or $QtrEnd == 11) {
				$TotalDays = 30;
			}
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrStrt, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrEnd, $TotalDays, $ThisYear));
		break;
		case __('Last Year'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 1, 1, $LastYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 12, 31, $LastYear));
		break;
		case __('Last Financial Year'):
			if (Date('m') > $_SESSION['YearEnd']) {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y') - 1));
			} else {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y') - 2));
			}
			$DateEnd = date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], -1));
		break;
		case __('Next Month'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $NextMonth, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $NextMonth, $TotalDaysNext, $ThisYear));
		break;
		case __('Next Quarter'):
			$QtrStrt = intval(($ThisMonth - 1) / 3) * 3 + 4;
			$QtrEnd = intval(($ThisMonth - 1) / 3) * 3 + 6;
			if ($QtrEnd == 4 or $QtrEnd == 6 or $QtrEnd == 9 or $QtrEnd == 11) {
				$TotalDays = 30;
			}
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrStrt, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $QtrEnd, $TotalDays, $ThisYear));
		break;
		case __('Next Year'):
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 1, 1, $NextYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 12, 31, $NextYear));
		break;
		case __('Next Financial Year'):
			if (Date('m') > $_SESSION['YearEnd']) {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y') + 1));
			} else {
				$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, Date('Y')));
			}
			$DateEnd = date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], 1));
		break;
		default:
			$DateStart = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $LastMonth, 1, $ThisYear));
			$DateEnd = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $LastMonth, $TotalDaysLast, $ThisYear));
		break;
	}
	if ($FromOrTo == 'From') {
		$Period = GetPeriod($DateStart);
	} else {
		$Period = GetPeriod($DateEnd);
	}
	return $Period;
}

function FYStartPeriod($PeriodNumber) {
	// Get the end date of the period using EndDateSQLFromPeriodNo
	$LastDateInPeriod = EndDateSQLFromPeriodNo($PeriodNumber);

	// Parse the date components from the SQL date
	$DateArray = explode('-', $LastDateInPeriod);

	// Determine the financial year start date based on YearEnd setting
	if ((int)$DateArray[1] > $_SESSION['YearEnd']) {
		$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, $DateArray[0]));
	} else {
		$DateStart = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 1, 1, $DateArray[0] - 1));
	}
	$StartPeriod = GetPeriod($DateStart);
	return $StartPeriod;
}

function fShowFieldHelp($HelpText) {
	// Shows field help text if $_SESSION['ShowFieldHelp'] is true or is not set.
	if ($_SESSION['ShowFieldHelp'] || !isset($_SESSION['ShowFieldHelp'])) {
		echo '<span class="field_help_text">', $HelpText, '</span>';
	}
}

function fShowPageHelp($HelpText) {
	// Shows page help text if $_SESSION['ShowFieldHelp'] is true or is not set.
	if ($_SESSION['ShowPageHelp'] || !isset($_SESSION['ShowPageHelp'])) {
		echo '<div class="page_help_text">', $HelpText, '</div><br />';
	}
}

/*
 * Improve language check to avoid potential LFI issue.
 * Reported by: https://lyhinslab.org
 * @todo we could just check that $language is an existing key within $LanguagesArray instead, which has the added
 *       value of not allowing unsupported language codes
 */
function checkLanguageChoice($language) {
	return preg_match('/^([a-z]{2}\_[A-Z]{2})(\.utf8)$/', $language);
}

/**
 * Main email sending function for WebERP
 *
 * This function serves as the primary interface for sending emails from WebERP.
 * It determines whether to use standard PHP mail() function or SMTP based on system configuration
 * and handles different input formats for recipients and attachments.
 *
 * @param string $From        Email address of the sender
 * @param mixed  $To          Can be string with single email or array of email addresses (keys) with names (values)
 * @param string $Subject     Subject of the email
 * @param string $Body        Body content of the email
 * @param mixed  $Attachments Can be string with single file path or array of file paths to attach
 * @param bool   $Silent      If true, suppresses success/error messages (default: false)
 *
 * @return mixed Returns true if email was sent successfully, or error message if failed
 */
function SendEmailFromWebERP($From, $To, $Subject, $Body, $Attachments=array(), $Silent = false) {

	// Convert $Attachments to array if it's a string
	if (!is_array($Attachments) && !empty($Attachments)) {
		$Attachments = array($Attachments);
	}

	if ($_SESSION['SmtpSetting'] == 0) {
		// Handle both string and array formats for $To
		if (is_array($To)) {
			$EmailSent = true; // Start with true, will be set to false if any email fails
			// Send individual emails to each recipient
			foreach ($To as $ToAddress => $ToName) {
				// If the key is numeric, the $ToAddress is actually the value
				if (is_numeric($ToAddress)) {
					$ToAddress = $ToName;
				}
				$Result = SendEmailByStandardMailFunction($From,
													$ToAddress,
													$Subject,
													$Body,
													$Attachments);
				// If any email fails, mark the whole operation as failed
				if (!$Result) {
					$EmailSent = false;
				}
			}
		} else {
			$EmailSent = SendEmailByStandardMailFunction($From,
													$To,
													$Subject,
													$Body,
													$Attachments);
		}
	} else {
		// Convert $To to array if it's a string
		if (!is_array($To)) {
			$To = array($To => ''); // Using empty string as recipient name
		}

		$mail = new PHPMailer(true);
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$EmailSent = SendEmailBySmtp($mail,
						$From,
						$To,
						$Subject,
						$Body,
						$Attachments);
	}

	if (!$Silent) {
		// Check if $EmailSent is a boolean true or a string (error message)
		if ($EmailSent === true) {
			prnMsg( __('Email has been sent.'), 'success');
		} else {
			$ErrorMessage = is_string($EmailSent) ? $EmailSent : __('Unknown error');
			prnMsg( __('Email not sent. An error was encountered: ') . $ErrorMessage, 'error');
		}
	}
	return $EmailSent;
}

function SendEmailBySmtp($MailObj, $From, $To, $Subject, $Body, $Attachments=array()) {
	$SQL = "SELECT host,
					port,
					username,
					password,
					auth
				FROM emailsettings";
	$Result = DB_query($SQL);
	$MyEmailRow = DB_fetch_array($Result);
	$MailObj->isSMTP();
	$MailObj->Host = $MyEmailRow['host'];
	if ($MyEmailRow['auth'] == 1) {
		$MailObj->SMTPAuth = true;
	} else {
		$MailObj->SMTPAuth = false;
	}
	$MailObj->Username = $MyEmailRow['username'];
	$MailObj->Password = $MyEmailRow['password'];
	$MailObj->Port = $MyEmailRow['port'];
	$MailObj->setFrom($From, '');
	//$Recipients = '';
	//$RecipientNames = '';
	foreach ($To as $ToAddress => $ToName) {
		$MailObj->addAddress($ToAddress, $ToName);
	}
	// Ensure Attachments is an array before looping
	if (is_array($Attachments)) {
		foreach ($Attachments as $Attachment) {
			$MailObj->addAttachment($Attachment, basename($Attachment));
		}
	}
	$MailObj->isHTML(false);
	$MailObj->Subject = $Subject;
	$MailObj->Body = $Body;
	if (!$MailObj->send()) {
		$EmailSent = $MailObj->ErrorInfo;
	} else {
		$EmailSent = true;
	}
	$MailObj->smtpClose();
	return $EmailSent;
}

function SendEmailByStandardMailFunction($From, $To, $Subject, $Body, $Attachments=array()) {
	// If no attachments, use simple mail function
	if(empty($Attachments)) {
		$result = mail($To, $Subject, $Body, "From: $From\r\n");
		return $result;
	} else {
		// Create a boundary for the email
		$boundary = md5(time());

		// Headers for a MIME email with attachments
		$headers = "From: $From\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

		// Email body with attachments
		$message = "--$boundary\r\n";
		$message .= "Content-Type: text/plain; charset=utf-8\r\n";
		$message .= "Content-Transfer-Encoding: base64\r\n\r\n";
		$message .= chunk_split(base64_encode($Body)) . "\r\n";

		// Attach each file
		$allFilesExist = true;
		foreach($Attachments as $Attachment) {
			if(file_exists($Attachment)) {
				$file_content = file_get_contents($Attachment);
				$message .= "--$boundary\r\n";
				$message .= "Content-Type: application/octet-stream; name=\"" . basename($Attachment) . "\"\r\n";
				$message .= "Content-Transfer-Encoding: base64\r\n";
				$message .= "Content-Disposition: attachment; filename=\"" . basename($Attachment) . "\"\r\n\r\n";
				$message .= chunk_split(base64_encode($file_content)) . "\r\n";
			} else {
				$allFilesExist = false;
			}
		}
		$message .= "--$boundary--";

		// Only attempt to send if all files existed
		if($allFilesExist) {
			// Send the email with attachments
			$result = mail($To, $Subject, $message, $headers);
			return $result;
		} else {
			// Return false if any attachment file was missing
			return false;
		}
	}
}


function ShowDebugBackTrace($DebugMessage, $SQL){
	global $Debug;

	if ($Debug == 1) {
		$Trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}else if ($Debug >= 2) {
		$Trace = debug_backtrace();
	}else {
		// Should not happen. Safety check
		return;
	}

	prnMsg($DebugMessage. '<br />' . $SQL . '<br />' . __('in file') . ' ' . $Trace[0]['file'] . __('on line') . ' ' . $Trace[0]['line'],
				'error', __('Database SQL Failure'));

	echo '<div class="centre">
		<table class="selection">
		<tr><th colspan="6">' . __('Function Call Stack') . '</th></tr>
		<tr>
		<th>' . __('Frame') . '</th>
		<th>' . __('File') . '</th>
		<th>' . __('Line') . '</th>
		<th>' . __('Function') . '</th>
		<th>' . __('Class') . '</th>
		<th>' . __('Arguments') . '</th>
		</tr>';
	foreach ($Trace as $Index => $Frame) {
		if (isset($Frame['args']) && count($Frame['args']) > 0) {
			$Parameters = '<pre>' . htmlspecialchars(print_r($Frame['args'], true)) . '</pre>';
		} else {
			$Parameters = 'N/A';
		}
		echo '<tr class="striped_row">
			<td>' . $Index . '</td>
			<td>' . (isset($Frame['file']) ? $Frame['file'] : 'N/A') . '</td>
			<td>' . (isset($Frame['line']) ? $Frame['line'] : 'N/A') . '</td>
			<td>' . (isset($Frame['function']) ? $Frame['function'] : 'N/A') . '</td>
			<td>' . (isset($Frame['class']) ? $Frame['class'] : 'N/A') . '</td>
			<td>' . $Parameters . '</td>
		</tr>';
	}
	echo '</table>
		</div>';
}
