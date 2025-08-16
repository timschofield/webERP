<?php

/*
date validation and parsing functions

These functions refer to the session variable defining the date format
The date format is defined in SystemParameters called DefaultDateFormat
this can be a string either:
'd/m/Y' for UK/Australia/New Zealand dates or
'm/d/Y' for US/Canada format dates
or Y/m/d  for Sweden ;) Anders Eriksson anders@weberp.se.
or d.m.Y  for Germany ;) Juergen Ruemmler heinrich@ruemmler.net

Functions in this file (alphabetical order):
- CalcDueDate - Calculates the due date for a transaction based on terms
- CalcEarliestDispatchDate - Calculates the earliest possible dispatch date
- ConvertEDIDate - Converts an EDI format date to the specified format
- ConvertSQLDate - Converts a SQL date to the specified format
- ConvertSQLDateTime - Converts a SQL datetime to the specified format
- ConvertToEDIDate - Converts a date to EDI format
- CreatePeriod - Creates a new accounting period
- Date1GreaterThanDate2 - Checks if one date is greater than another
- DateAdd - Adds a specified interval to a date
- DateDiff - Calculates the difference between two dates
- DayOfMonthFromSQLDate - Gets the day of month from a SQL date
- DayOfWeekFromSQLDate - Gets the day of week from a SQL date
- DisplayDateTime - Formats the current date and time for display
- EndDateSQLFromPeriodNo - Gets the end date for a given period
- EnsureSQLDateFormat - Validates if a date is in SQL format
- Format_Date - Formats a date according to the system settings
- FormatDateForSQL - Converts a date to SQL format
- FormatDateWithTimeForSQL - Converts a datetime to SQL format
- GetMonthText - Returns the month name from a month number
- GetPeriod - Returns the period number for a given date
- GetWeekDayText - Returns the weekday name from a day number
- Is_date - Validates if a string is a valid date
- LastDayOfMonth - Gets the last day of the month for a date
- MonthAndYearFromPeriodNo - Gets the month and year for a period
- MonthAndYearFromSQLDate - Gets the month and year from a SQL date
- PeriodExists - Checks if a period exists for a given date
- SQLDateToEDI - Converts a SQL date to EDI format
- YearAndMonthFromSQLDate - Gets the year and month from a SQL date
- YearEndDate - Gets financial year end date
*/

/**************************************************************************************************************
* Function: Is_date
* Description: Validates if a string is a valid date according to the default date format
* Parameters: $DateEntry - The date string to validate
* Returns: 1 if date is valid, 0 if invalid
**************************************************************************************************************/
function Is_date($DateEntry) {

	$DateEntry = Trim($DateEntry);

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	} elseif (mb_strlen($DateEntry) == 6) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 2);
	} elseif (mb_strlen($DateEntry) == 8) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 4);
	}

	if (!isset($DateArray) or sizeof($DateArray) < 3) {
		return 0;
	}

	if ((int)$DateArray[2] > 9999) {
		return 0;
	}

	if (is_long((int)$DateArray[0]) AND is_long((int)$DateArray[1]) AND is_long((int)$DateArray[2])) {

		if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') OR ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
			if (checkdate((int)$DateArray[1], (int)$DateArray[0], (int)$DateArray[2])) {
				return 1;
			} else {
				return 0;
			}
		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
			if (checkdate((int)$DateArray[0], (int)$DateArray[1], (int)$DateArray[2])) {
				return 1;
			} else {
				return 0;
			}
		} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
			if (checkdate((int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0])) {
				return 1;
			} else {
				return 0;
			}
		} elseif ($_SESSION['DefaultDateFormat'] == 'Y-m-d') {
			if (checkdate((int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0])) {
				return 1;
			} else {
				return 0;
			}
		} else { /*Can't be in an appropriate DefaultDateFormat */
			return 0;
		}
	}
} //end of Is_Date function

/**************************************************************************************************************
* Function: MonthAndYearFromSQLDate
* Description: Extracts and formats the month and year from a SQL date
* Parameters:
*   $DateEntry - The SQL format date to process
*   $UseShortMonthAndYear - Boolean flag for short format (default false)
* Returns: String containing formatted month and year
**************************************************************************************************************/
function MonthAndYearFromSQLDate($DateEntry, $UseShortMonthAndYear = false) {

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	}

	if (mb_strlen($DateArray[2]) > 4) {
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	}

	$MonthAndYear = '';
	$TimeStamp = mktime(0, 0, 0, (int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0]);

	if ($UseShortMonthAndYear) {
		// 2-digit month and year: 04/20.
		// Useful for Graphs with many plot references.
		$MonthAndYear = date('m/y', $TimeStamp);
	} else {
		$MonthName = GetMonthText(date('n', $TimeStamp));
		$MonthAndYear = $MonthName . ' ' . date('Y', $TimeStamp);
	}

	return $MonthAndYear;
}

/**************************************************************************************************************
* Function: YearAndMonthFromSQLDate
* Description: Extracts and formats the year and month from a SQL date
* Parameters:
*   $DateEntry - The SQL format date to process
*   $UseShortYearAndMonth - Boolean flag for short format (default false)
* Returns: String containing formatted year and month
**************************************************************************************************************/
function YearAndMonthFromSQLDate($DateEntry, $UseShortYearAndMonth = false) {

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	}

	if (mb_strlen($DateArray[2]) > 4) {
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	}

	$YearAndMonth = '';
	$TimeStamp = mktime(0, 0, 0, (int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0]);

	if ($UseShortYearAndMonth) {
		// 4-digit year and 2-digit month: 2004-04.
		$YearAndMonth = date('Y-m', $TimeStamp);
	} else {
		$MonthName = GetMonthText(date('n', $TimeStamp));
		$YearAndMonth = date('Y', $TimeStamp) . ' ' . $MonthName;
	}

	return $YearAndMonth;
}

/**************************************************************************************************************
* Function: MonthAndYearFromPeriodNo
* Description: Gets the month and year for a specified period number
* Parameters: $PeriodNo - The period number to get month and year for
* Returns: String containing the month and year for the period
**************************************************************************************************************/
function MonthAndYearFromPeriodNo($PeriodNo) {
	return MonthAndYearFromSQLDate(EndDateSQLFromPeriodNo($PeriodNo));
}

/**************************************************************************************************************
* Function: EndDateSQLFromPeriodNo
* Description: Gets the SQL format end date for a specified period number
* Parameters: $PeriodNo - The period number to get end date for
* Returns: String containing the end date in SQL format (YYYY-MM-DD)
**************************************************************************************************************/
function EndDateSQLFromPeriodNo($PeriodNo) {
	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $PeriodNo . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow['lastdate_in_period'];
}

/**************************************************************************************************************
* Function: GetMonthText
* Description: Gets the localized text representation of a month number
* Parameters: $MonthNumber - The month number (1-12)
* Returns: String containing the month name
**************************************************************************************************************/
function GetMonthText($MonthNumber) {
	switch ($MonthNumber) {
		case 1:
			$Month = __('January');
			break;
		case 2:
			$Month = __('February');
			break;
		case 3:
			$Month = __('March');
			break;
		case 4:
			$Month = __('April');
			break;
		case 5:
			$Month = __('May');
			break;
		case 6:
			$Month = __('June');
			break;
		case 7:
			$Month = __('July');
			break;
		case 8:
			$Month = __('August');
			break;
		case 9:
			$Month = __('September');
			break;
		case 10:
			$Month = __('October');
			break;
		case 11:
			$Month = __('November');
			break;
		case 12:
			$Month = __('December');
			break;
		default:
			$Month = __('error');
			break;
	}
	return $Month;
}

/**************************************************************************************************************
* Function: GetWeekDayText
* Description: Gets the localized text representation of a week day number
* Parameters: $WeekDayNumber - The week day number (0-6, where 0=Sunday)
* Returns: String containing the day name
**************************************************************************************************************/
function GetWeekDayText($WeekDayNumber) {
	$Day = '';
	switch ($WeekDayNumber) {
		case 0:
			$Day = __('Sunday');
			break;
		case 1:
			$Day = __('Monday');
			break;
		case 2:
			$Day = __('Tuesday');
			break;
		case 3:
			$Day = __('Wednesday');
			break;
		case 4:
			$Day = __('Thursday');
			break;
		case 5:
			$Day = __('Friday');
			break;
		case 6:
			$Day = __('Saturday');
			break;
	}
	return $Day;
}

/**************************************************************************************************************
* Function: DisplayDateTime
* Description: Formats the current date and time according to locale settings
* Parameters: None
* Returns: String containing formatted date and time
**************************************************************************************************************/
function DisplayDateTime() {
	// Long date and time in locale format.
	// Could be replaced by IntlDateFormatter (available on PHP 5.3.0 or later). See http://php.net/manual/en/class.intldateformatter.php
	switch ($_SESSION['Language']) {
		case 'en_GB.utf8':
			$LongDateTime = GetWeekDayText(date('w')) . ' ' . date('j') . ' ' . GetMonthText(date('n')) . ' ' . date('Y') . ' ' . date('G:i');
			break;
		case 'en_US.utf8':
			$LongDateTime = GetWeekDayText(date('w')) . ', ' . GetMonthText(date('n')) . ' ' . date('j') . ', ' . date('Y') . ' ' . date('G:i');
			break;
		case 'es_ES.utf8':
			$LongDateTime = GetWeekDayText(date('w')) . ' ' . date('j') . ' de ' . GetMonthText(date('n')) . ' de ' . date('Y') . ' ' . date('G:i');
			break;
		case 'fr_FR.utf8':
			$LongDateTime = GetWeekDayText(date('w')) . ' ' . date('j') . ' ' . GetMonthText(date('n')) . ' ' . date('Y') . ' ' . date('G:i');
			break;
		default:
			$LongDateTime = GetWeekDayText(date('w')) . ' ' . date('j') . ' ' . GetMonthText(date('n')) . ' ' . date('Y') . ' ' . date('G:i');
			break;
	}
	return $LongDateTime;
}

/**************************************************************************************************************
* Function: DayOfWeekFromSQLDate
* Description: Gets the day of week from a SQL format date
* Parameters: $DateEntry - The SQL format date (YYYY-MM-DD)
* Returns: Integer representing day of week (0-6, where 0=Sunday)
**************************************************************************************************************/
function DayOfWeekFromSQLDate($DateEntry) {

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	}

	if (mb_strlen($DateArray[2]) > 4) {
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	}

	return date('w', mktime(0, 0, 0, (int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0]));
}

/**************************************************************************************************************
* Function: DayOfMonthFromSQLDate
* Description: Gets the day of month from a SQL format date
* Parameters: $DateEntry - The SQL format date (YYYY-MM-DD)
* Returns: Integer representing day of month (1-31)
**************************************************************************************************************/
function DayOfMonthFromSQLDate($DateEntry) {

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	}

	if (mb_strlen($DateArray[2]) > 4) {
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	}

	return date('j', mktime(0, 0, 0, (int)$DateArray[1], (int)$DateArray[2], (int)$DateArray[0]));
}

/**************************************************************************************************************
* Function: YearEndDate
* Description: Calculates the financial year end date
* Parameters:
*   $MonthNo - The month number of the financial year end
*   $YearIncrement - Number of years to add/subtract from current year
* Returns: Unix timestamp for the financial year end date
**************************************************************************************************************/
function YearEndDate($MonthNo, $YearIncrement) {
	if (Date('m') > $MonthNo) {
		$Year = Date('Y') + 1 + $YearIncrement;
	} else {
		$Year = Date('Y') + $YearIncrement;
	}
	return mktime(0, 0, 0, $MonthNo + 1, 0, $Year);
}

/**************************************************************************************************************
* Function: ConvertSQLDate
* Description: Converts a date from SQL format to the format specified in DefaultDateFormat
* Parameters: $DateEntry - The SQL format date (YYYY-MM-DD)
* Returns: String containing formatted date according to DefaultDateFormat
**************************************************************************************************************/
function ConvertSQLDate($DateEntry) {

	/* takes a date in a the format yyyy-mm-dd and converts to a format specified in $_SESSION['DefaultDateFormat']*/
	$ErrorInFormat = false;

	if (!EnsureSQLDateFormat($DateEntry)){
		// if is not in SQL format, there's nothing to do
		$ErrorInFormat = true;
	} else {
		if (mb_strpos($DateEntry, '/')) {
			$DateArray = explode('/', $DateEntry);
		} elseif (mb_strpos($DateEntry, '-')) {
			$DateArray = explode('-', $DateEntry);
		} elseif (mb_strpos($DateEntry, '.')) {
			$DateArray = explode('.', $DateEntry);
		} else {
			$ErrorInFormat = true;
		}
	}
	if ($ErrorInFormat){
		prnMsg(__('The date does not appear to be in a valid format. The date being converted from SQL format was:') . ' ' . $DateEntry,'error');
		switch ($_SESSION['DefaultDateFormat']) {
			case 'd/m/Y':
				return '01/01/1000';
				break;
			case 'd.m.Y':
				return '01.01.1000';
				break;
			case 'm/d/Y':
				return '01/01/1000';
				break;
			case 'Y/m/d':
				return '1000/01/01';
				break;
			case 'Y-m-d':
				return '1000-01-01';
				break;
		}
	} else {
		if (mb_strlen($DateArray[2]) > 4) {  /*chop off the time stuff */
			$DateArray[2] = mb_substr($DateArray[2], 0, 2);
		}
		if ($_SESSION['DefaultDateFormat'] == 'd/m/Y'){
			return $DateArray[2] . '/' . $DateArray[1] . '/' . $DateArray[0];
		} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y'){
			return $DateArray[2] . '.' . $DateArray[1] . '.' . $DateArray[0];
		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y'){
			return $DateArray[1] . '/' . $DateArray[2] . '/' . $DateArray[0];
		} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d'){
			return $DateArray[0] . '/' . $DateArray[1] . '/' . $DateArray[2];
		} elseif ($_SESSION['DefaultDateFormat'] == 'Y-m-d'){
			return $DateArray[0] . '-' . $DateArray[1] . '-' . $DateArray[2];
		}
	}
} // end function ConvertSQLDate

/**************************************************************************************************************
* Function: ConvertSQLDateTime
* Description: Converts a datetime from SQL format to DefaultDateFormat with time
* Parameters: $DateEntry - The SQL format datetime (YYYY-MM-DD HH:MM:SS)
* Returns: String containing formatted datetime according to DefaultDateFormat
**************************************************************************************************************/
function ConvertSQLDateTime($DateEntry) {

	//for MySQL dates are in the format YYYY-mm-dd H:i:s

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	} else {
		prnMsg(__('The date does not appear to be in a valid format. The date being converted from SQL format was:') . ' ' . $DateEntry,'error');
		switch ($_SESSION['DefaultDateFormat']) {
			case 'd/m/Y':
				return '01/01/1000';
				break;
			case 'd.m.Y':
				return '01.01.1000';
				break;
			case 'm/d/Y':
				return '01/01/1000';
				break;
			case 'Y/m/d':
				return '1000/01/01';
				break;
		}
	}

	if (mb_strlen($DateArray[2]) > 4) {
		$Time = mb_substr($DateArray[2], 3, 8);
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	} else {
		$Time = '00:00:00';
	}

	if ($_SESSION['DefaultDateFormat'] == 'd/m/Y'){
		return $DateArray[2] . '/' . $DateArray[1] . '/' . $DateArray[0] . ' ' . $Time;
	} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y'){
		return $DateArray[2] . '.' . $DateArray[1] . '.' . $DateArray[0] . ' ' . $Time;
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y'){
		return $DateArray[1] . '/' . $DateArray[2] . '/' . $DateArray[0] . ' ' . $Time;
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d'){
		return $DateArray[0] . '/' . $DateArray[1] . '/' . $DateArray[2] . ' ' . $Time;
	}

} // end function ConvertSQLDate

/**************************************************************************************************************
* Function: SQLDateToEDI
* Description: Converts a SQL format date to EDI format by removing separators
* Parameters: $DateEntry - The SQL format date (YYYY-MM-DD)
* Returns: String containing the date in EDI format (YYYYMMDD)
**************************************************************************************************************/
function SQLDateToEDI($DateEntry) {

	//for MySQL dates are in the format YYYY-mm-dd
	//EDI format 102 dates are in the format CCYYMMDD - just need to lose the seperator

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	}

	if (mb_strlen($DateArray[2]) > 4) {  /*chop off the time stuff */
		$DateArray[2] = mb_substr($DateArray[2], 0, 2);
	}

	return $DateArray[0] . $DateArray[1] . $DateArray[2];

} // end function SQLDateToEDI

/**************************************************************************************************************
* Function: ConvertToEDIDate
* Description: Converts a date in DefaultDateFormat to EDI format
* Parameters: $DateEntry - The date in DefaultDateFormat
* Returns: String containing the date in EDI format (YYYYMMDD)
**************************************************************************************************************/
function ConvertToEDIDate($DateEntry) {

/* takes a date in a the format specified in $_SESSION['DefaultDateFormat']
and converts to a yyyymmdd - EANCOM format 102*/


	$DateEntry = trim($DateEntry);

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	} elseif (mb_strlen($DateEntry) == 6) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 2);
	} elseif (mb_strlen($DateEntry) == 8) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 4);
	}

//to modify assumption in 2030

	if ((int)$DateArray[2] < 60) {
		$DateArray[2] = '20' . $DateArray[2];
	} elseif ((int)$DateArray[2] > 59 AND (int)$DateArray[2] < 100) {
		$DateArray[2] = '19' . $DateArray[2];
	} elseif ((int)$DateArray[2] > 9999) {
		return 0;
	}

	if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') || ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
		return $DateArray[2] . $DateArray[1] . $DateArray[0];

	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		return $DateArray[2] . $DateArray[0] . $DateArray[1];

	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
		return $DateArray[1] . $DateArray[2] . $DateArray[0];

	}

} // end function to convert DefaultDateFormat Date to EDI format 102

/**************************************************************************************************************
* Function: ConvertEDIDate
* Description: Converts an EDI format date to DefaultDateFormat
* Parameters:
*   $DateEntry - The EDI format date
*   $EDIFormatCode - Format code (102, 203, 616, 718)
* Returns: String containing formatted date according to DefaultDateFormat
**************************************************************************************************************/
function ConvertEDIDate($DateEntry, $EDIFormatCode) {

	/*EDI Format codes:
		102  -  CCYYMMDD
		203  -  CCYYMMDDHHMM
		616  -  CCYYWW  - cant handle the week number
		718  -  CCYYMMDD-CCYYMMDD  can't handle this either a date range
	*/

	switch ($EDIFormatCode) {
	case 102:
		if ($_SESSION['DefaultDateFormat'] == 'd/m/Y'){
			return mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 0, 4);

		} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y') {
			return mb_substr($DateEntry, 6, 2) . '.' . mb_substr($DateEntry, 4, 2) . '.' . mb_substr($DateEntry, 0, 4);

		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
			return mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 0, 4);

		} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
			return mb_substr($DateEntry, 0, 4) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2);
		}
		break;
	case 203:
		if ($_SESSION['DefaultDateFormat'] == 'd/m/Y') {
			return mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 0, 4) . ' ' . mb_substr($DateEntry, 6, 2) . ':' . mb_substr($DateEntry, 8, 2);

		} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y') {
			return mb_substr($DateEntry, 6, 2) . '.' . mb_substr($DateEntry, 4, 2) . '.' . mb_substr($DateEntry, 0, 4) . ' ' . mb_substr($DateEntry, 6, 2) . ':' . mb_substr($DateEntry, 8, 2);

		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
			return mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 0, 4) . ' ' . mb_substr($DateEntry, 6, 2) . ':' . mb_substr($DateEntry, 8, 2);

		} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
			return mb_substr($DateEntry, 0, 4) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2) . ' ' . mb_substr($DateEntry, 6, 2) . ':' . mb_substr($DateEntry, 8, 2);
		}
		break;
	case 616:
		/*multiply the week number by 7 and add to the 1/1/CCYY */
		return date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, 1, 1 + (7 * (int)mb_substr($DateEntry, 4, 2)), mb_substr($DateEntry, 0, 4)));
		break;
	case 718:
		if ($_SESSION['DefaultDateFormat'] == 'd/m/Y'){
			return mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 0, 4) . ' - '. mb_substr($DateEntry, 15, 2) . '/' . mb_substr($DateEntry, 13, 2) . '/' . mb_substr($DateEntry, 9, 4);
		} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y') {
			return mb_substr($DateEntry, 6, 2) . '.' . mb_substr($DateEntry, 4, 2) . '.' . mb_substr($DateEntry, 0, 4) . ' - '. mb_substr($DateEntry, 15, 2) . '.' . mb_substr($DateEntry, 13, 2) . '.' . mb_substr($DateEntry, 9, 4);
		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
			return mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2) . '/' . mb_substr($DateEntry, 0, 4) . ' - '. mb_substr($DateEntry, 13, 2) . '/' . mb_substr($DateEntry, 15, 2) . '/' . mb_substr($DateEntry, 9, 4);
		} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
			return mb_substr($DateEntry, 0, 4) . '/' . mb_substr($DateEntry, 4, 2) . '/' . mb_substr($DateEntry, 6, 2) . ' - '. mb_substr($DateEntry, 13, 2) . '/' . mb_substr($DateEntry, 15, 2) . '/' . mb_substr($DateEntry, 9, 4);
		}

		break;
	}


}

/**************************************************************************************************************
* Function: Format_Date
* Description: Formats a date according to DefaultDateFormat
* Parameters: $DateEntry - The date to format
* Returns: String containing formatted date or 0 if invalid
**************************************************************************************************************/
function Format_Date($DateEntry) {

	$DateEntry = trim($DateEntry);

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	} elseif (mb_strlen($DateEntry) == 6) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 2);
	} elseif (mb_strlen($DateEntry) == 8) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 4);
	}

//to modify assumption in 2030

	if ((int)$DateArray[2] < 60) {
		$DateArray[2] = '20' . $DateArray[2];
	} elseif ((int)$DateArray[2] > 59 AND (int)$DateArray[2] < 100) {
		$DateArray[2] = '19' . $DateArray[2];
	} elseif ((int)$DateArray[2] > 9999) {
		return 0;
	}

	if (is_long((int)$DateArray[0]) AND is_long((int)$DateArray[1]) AND is_long((int)$DateArray[2])) {
		if ($_SESSION['DefaultDateFormat'] == 'd/m/Y'){
			if (checkdate((int)$DateArray[1], (int)$DateArray[0], (int)$DateArray[2])){
				return $DateArray[0] . '/' . $DateArray[1] . '/' . $DateArray[2];
			}
		} elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y'){
			if (checkdate((int)$DateArray[1], (int)$DateArray[0], (int)$DateArray[2])){
				return $DateArray[0] . '.' . $DateArray[1] . '.' . $DateArray[2];
			}
		} elseif ($_SESSION['DefaultDateFormat'] = 'm/d/Y'){
			if (checkdate((int)$DateArray[0], (int)$DateArray[1], (int)$DateArray[2])){
				return $DateArray[0] . '/' . $DateArray[1] . '/' . $DateArray[2];
			}
		} elseif ($_SESSION['DefaultDateFormat'] = 'Y/m/d'){
			if (checkdate((int)$DateArray[2], (int)$DateArray[0], (int)$DateArray[1])){
				return $DateArray[0] . '/' . $DateArray[1] . '/' . $DateArray[2];
			}
		} elseif ($_SESSION['DefaultDateFormat'] = 'Y-m-d'){
			if (checkdate((int)$DateArray[2], (int)$DateArray[0], (int)$DateArray[1])){
				return $DateArray[0] . '-' . $DateArray[1] . '-' . $DateArray[2];
			}
		} // end if check date
	} else { // end if all numeric inputs
		return 0;
	}
}// end of function

/**************************************************************************************************************
* Function: EnsureSQLDateFormat
* Description: Validates if a date is in SQL format (YYYY-MM-DD)
* Parameters: $Date - The date string to validate
* Returns: Boolean - true if date is in SQL format, false otherwise
**************************************************************************************************************/
function EnsureSQLDateFormat($Date) {
	// All credit to GitHub Copilot
	$Date = mb_substr($Date, 0, 10); // chop off the time stuff
	if ($Date == '1000-01-01'){
		return true; // The date is "no date", but in the correct SQL format
	}else{
		$DateTime = DateTime::createFromFormat('Y-m-d', $Date);
		if ($DateTime && $DateTime->format('Y-m-d') === $Date) {
			return true; // The date is in the correct SQL format
		} else {
			return false; // The date is not in the correct SQL format
		}
	}
}

/**************************************************************************************************************
* Function: FormatDateForSQL
* Description: Converts a date from DefaultDateFormat to SQL format
* Parameters: $DateEntry - The date in DefaultDateFormat
* Returns: String containing the date in SQL format (YYYY-MM-DD)
**************************************************************************************************************/
function FormatDateForSQL($DateEntry) {

/* takes a date in a the format specified in $_SESSION['DefaultDateFormat']
and converts to a yyyy-mm-dd format */
	if (EnsureSQLDateFormat($DateEntry)){
		// if is already SQL format, there's nothing to do
		return $DateEntry;
	} else {
		// if is not SQL format, let's convert it into SQL format
		$Date_Array = array();
		$DateEntry = trim($DateEntry);

		if (mb_strpos($DateEntry, '/')) {
			$Date_Array = explode('/', $DateEntry);
		} elseif (mb_strpos($DateEntry, '-')) {
			$Date_Array = explode('-', $DateEntry);
		} elseif (mb_strpos($DateEntry, '.')) {
			$Date_Array = explode('.', $DateEntry);
		} elseif (mb_strlen($DateEntry) == 6) {
			$Date_Array[0] = mb_substr($DateEntry, 0, 2);
			$Date_Array[1] = mb_substr($DateEntry, 2, 2);
			$Date_Array[2] = mb_substr($DateEntry, 4, 2);
		} elseif (mb_strlen($DateEntry) == 8) {
			$Date_Array[0] = mb_substr($DateEntry, 0, 4);
			$Date_Array[1] = mb_substr($DateEntry, 4, 2);
			$Date_Array[2] = mb_substr($DateEntry, 6, 2);
		}

		if ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
			if (mb_strlen($Date_Array[0]) == 2) {
				if ((int)$Date_Array[0] <= 60) {
					$Date_Array[0] = '20' . $Date_Array[2];
				} elseif ((int)$Date_Array[0] > 60 AND (int)$Date_Array[2] < 100) {
					$Date_Array[0] = '19' . $Date_Array[2];
				}
			}
			return $Date_Array[0] . '-' . $Date_Array[1] . '-' . $Date_Array[2];

		} elseif (($_SESSION['DefaultDateFormat'] == 'd/m/Y')
					OR $_SESSION['DefaultDateFormat'] == 'd.m.Y'){
			if (mb_strlen($Date_Array[2]) == 2) {
				if ((int)$Date_Array[2] <= 60) {
					$Date_Array[2] = '20' . $Date_Array[2];
				} elseif ((int)$Date_Array[2] > 60 AND (int)$Date_Array[2] < 100) {
					$Date_Array[2] = '19'. $Date_Array[2];
				}
			}
			return $Date_Array[2] . '-' . $Date_Array[1] . '-' . $Date_Array[0];

		} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
			if (mb_strlen($Date_Array[2]) == 2) {
				if ((int)$Date_Array[2] <= 60) {
					$Date_Array[2] = '20' . $Date_Array[2];
				} elseif ((int)$Date_Array[2] > 60 AND (int)$Date_Array[2] < 100) {
					$Date_Array[2] = '19' . $Date_Array[2];
				}
			}
			return $Date_Array[2] . '-' . $Date_Array[0] . '-' . $Date_Array[1];
		}
	}

}// end of function

/**************************************************************************************************************
* Function: FormatDateWithTimeForSQL
* Description: Converts a datetime from DefaultDateFormat to SQL format
* Parameters: $DateTime - The datetime string in DefaultDateFormat with time
* Returns: String containing the datetime in SQL format (YYYY-MM-DD HH:MM:SS)
**************************************************************************************************************/
function FormatDateWithTimeForSQL($DateTime) {
    //  Split the time off, fix date and add the time to returned value.
    $dt = explode(' ', $DateTime);
    return FormatDateForSQL($dt[0]) . ' ' . $dt[1];
}

/**************************************************************************************************************
* Function: LastDayOfMonth
* Description: Calculates the last day of the month for a given date
* Parameters: $DateEntry - The date in DefaultDateFormat
* Returns: String containing the last day of month in DefaultDateFormat
**************************************************************************************************************/
function LastDayOfMonth($DateEntry) {
	/*Expects a date in DefaultDateFormat and
	 * Returns the last day of the month in the entered date
	 * in the DefaultDateFormat
	 *
	 * mktime (0,0,0 month, day, year)
	 */

	$DateEntry = trim($DateEntry);

	if (mb_strpos($DateEntry, '/')) {
		$DateArray = explode('/', $DateEntry);
	} elseif (mb_strpos($DateEntry, '-')) {
		$DateArray = explode('-', $DateEntry);
	} elseif (mb_strpos($DateEntry, '.')) {
		$DateArray = explode('.', $DateEntry);
	} elseif (mb_strlen($DateEntry) == 6) {
		$DateArray[0] = mb_substr($DateEntry, 0, 2);
		$DateArray[1] = mb_substr($DateEntry, 2, 2);
		$DateArray[2] = mb_substr($DateEntry, 4, 2);
	} elseif (mb_strlen($DateEntry) == 8) {
		$DateArray[0] = mb_substr($DateEntry, 0, 4);
		$DateArray[1] = mb_substr($DateEntry, 4, 2);
		$DateArray[2] = mb_substr($DateEntry, 6, 2);
	}

	if ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
		if (mb_strlen($DateArray[0]) == 2) {
			if ((int)$DateArray[0] <= 60) {
				$DateArray[0] = '20' . $DateArray[2];
			} elseif ((int)$DateArray[0] > 60 AND (int)$DateArray[2] < 100) {
				$DateArray[0] = '19' . $DateArray[2];
			}
		}

		$DateStamp =  mktime(0, 0, 0, $DateArray[1] + 1, 0, $DateArray[0]);

	}elseif (($_SESSION['DefaultDateFormat'] == 'd/m/Y') OR $_SESSION['DefaultDateFormat'] == 'd.m.Y'){
		if (mb_strlen($DateArray[2]) == 2) {
			if ((int)$DateArray[2] <= 60) {
				$DateArray[2] = '20' . $DateArray[2];
			} elseif ((int)$DateArray[2] > 60 AND (int)$DateArray[2] < 100) {
				$DateArray[2] = '19' . $DateArray[2];
			}
		}
		$DateStamp =  mktime(0, 0, 0, $DateArray[1] + 1, 0, $DateArray[2]);


	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		if (mb_strlen($DateArray[2]) == 2) {
			if ((int)$DateArray[2] <= 60) {
				$DateArray[2] = '20' . $DateArray[2];
			} elseif ((int)$DateArray[2] > 60 AND (int)$DateArray[2] < 100) {
				$DateArray[2] = '19' . $DateArray[2];
			}
		}
		return $DateArray[2] . '-' . $DateArray[0] . '-' . $DateArray[1];
		$DateStamp =  mktime(0, 0, 0, $DateArray[0] + 1, 0, $DateArray[2]);
	}
	return Date($_SESSION['DefaultDateFormat'], $DateStamp);
}// end of Last Day in the month function

/**************************************************************************************************************
* Function: Date1GreaterThanDate2
* Description: Compares two dates to determine if first date is greater than second
* Parameters:
*   $Date1 - First date in DefaultDateFormat
*   $Date2 - Second date in DefaultDateFormat
* Returns: Integer - 1 if Date1 > Date2, 0 otherwise
**************************************************************************************************************/
function Date1GreaterThanDate2($Date1, $Date2) {

	/* returns true (1) if Date1 is greater than Date2 */

	$Date1 = trim($Date1);
	$Date2 = trim($Date2);

	/* Get date elements */
	if ($_SESSION['DefaultDateFormat'] == 'd.m.Y' )  {
		list($Day1, $Month1, $Year1) = explode('.', $Date1);
		list($Day2, $Month2, $Year2) = explode('.', $Date2);
	} elseif ($_SESSION['DefaultDateFormat'] =='d/m/Y'){
		list($Day1, $Month1, $Year1) = explode('/', $Date1);
		list($Day2, $Month2, $Year2) = explode('/', $Date2);
	} elseif ($_SESSION['DefaultDateFormat'] =='m/d/Y'){
		list($Month1, $Day1, $Year1) = explode('/', $Date1);
		list($Month2, $Day2, $Year2) = explode('/', $Date2);
	} elseif ($_SESSION['DefaultDateFormat'] =='Y/m/d' ){
		list($Year1, $Month1, $Day1) = explode('/', $Date1);
		list($Year2, $Month2, $Day2) = explode('/', $Date2);
	} elseif ($_SESSION['DefaultDateFormat'] =='Y-m-d' ){
		list($Year1, $Month1, $Day1) = explode('-', $Date1);
		list($Year2, $Month2, $Day2) = explode('-', $Date2);
	}

	/*Try to make the year of each date comparable - if one date is specified as just
	 * 2 characters and the other >2 then then make them both 4 characters long. Assume
	 *  a date >50 to be 1900's and less than to be 2000's
	 */

	if (mb_strlen($Year1) > 2 AND mb_strlen($Year2) == 2){
		if ($Year2 > 50) {
			$Year2 = 1900 + $Year2;
		} else {
			$Year2 = 2000 + $Year2;
		}
	}
	if (mb_strlen($Year2) > 2 AND mb_strlen($Year1) == 2){
		if ($Year1 > 50) {
			$Year1 = 1900 + $Year1;
		} else {
			$Year1 = 2000 + $Year1;
		}
	}

	/* Compare years */
	if ($Year1 > $Year2){
		return 1;
	} elseif ($Year2 > $Year1){
		return 0;
	}

	/* Compare months. Years are equal*/
	if ($Month1 > $Month2){
		return 1;
	} elseif ($Month2 > $Month1){
		return 0;
	}

	/* Compare days. Years and months are equal */
	if ($Day1 > $Day2){
		return 1;
	} elseif ($Day2 > $Day1){
		return 0;
	}
	/* The dates are equal, so return false as date 1 is NOT greater than date 2 */
	return 0;
}

/**************************************************************************************************************
* Function: CalcDueDate
* Description: Calculates the due date based on transaction date and payment terms
* Parameters:
*   $TranDate - The transaction date in DefaultDateFormat
*   $DayInFollowingMonth - Day in the following month when payment is due (0 for days-based)
*   $DaysBeforeDue - Number of days before payment is due (for days-based terms)
* Returns: String containing the due date in DefaultDateFormat
**************************************************************************************************************/
function CalcDueDate($TranDate, $DayInFollowingMonth, $DaysBeforeDue){

	$TranDate = trim($TranDate);

	if (mb_strpos($TranDate, '/')) {
		$DateArray = explode('/', $TranDate);
	} elseif (mb_strpos($TranDate, '-')) {
		$DateArray = explode('-', $TranDate);
	} elseif (mb_strpos($TranDate, '.')) {
		$DateArray = explode('.', $TranDate);
  }

	if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') OR ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
		if ($DayInFollowingMonth == 0){ /*then it must be set up for DaysBeforeDue type */

			$DayDue = $DateArray[0] + $DaysBeforeDue;
			$MonthDue = $DateArray[1];
			$YearDue = $DateArray[2];

		} elseif($DayInFollowingMonth >= 29) { //take the last day of month
			if ($DayInFollowingMonth <= 31) {
				$DayDue = 0;
			} else {
				$DayDue = $DayInFollowingMonth - 31;
			}
			$MonthDue = $DateArray[1] + 2;
			$YearDue = $DateArray[2];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $DateArray[1] + 1;
			$YearDue = $DateArray[2];

		}
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		if ($DayInFollowingMonth == 0){ /*then it must be set up for DaysBeforeDue type */
			$DayDue = $DateArray[1] + $DaysBeforeDue;
			$MonthDue = $DateArray[0];
			$YearDue = $DateArray[2];

		} elseif($DayInFollowingMonth >= 29) { //take the last day of month
			if ($DayInFollowingMonth <= 31) {
				$DayDue = 0;
			} else {
				$DayDue = $DayInFollowingMonth - 31;
			}
			$MonthDue = $DateArray[0] + 2;
			$YearDue = $DateArray[2];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $DateArray[0] + 1;
			$YearDue = $DateArray[2];
		}
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
		if ($DayInFollowingMonth == 0){ /*then it must be set up for DaysBeforeDue type */
			$DayDue = $DateArray[2] + $DaysBeforeDue;
			$MonthDue = $DateArray[1];
			$YearDue = $DateArray[0];

		} elseif($DayInFollowingMonth >= 29) { //take the last day of month

			if ($DayInFollowingMonth <= 31) {
				$DayDue = 0;
			} else {
				$DayDue = $DayInFollowingMonth - 31;
			}
			$MonthDue = $DateArray[1] + 2;
			$YearDue = $DateArray[0];
		} else {
			$DayDue = $DayInFollowingMonth;
			$MonthDue = $DateArray[1] + 1;
			$YearDue = $DateArray[0];
		}
	}
	return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, $MonthDue, $DayDue, $YearDue));

}

/**************************************************************************************************************
* Function: DateAdd
* Description: Adds a specified time interval to a date
* Parameters:
*   $DateToAddTo - The base date in DefaultDateFormat
*   $PeriodString - Type of period to add (d=days, w=weeks, m=months, y=years)
*   $NumberPeriods - Number of periods to add (positive or negative)
* Returns: String containing the resulting date in DefaultDateFormat
**************************************************************************************************************/
function DateAdd($DateToAddTo, $PeriodString, $NumberPeriods){
	/*Takes
	 * DateToAddTo in $_SESSION['DefaultDateFormat'] format
	 * $PeriodString is one of:
	 * d - days
	 * w - weeks
	 * m - months
	 * y - years
	 * $NumberPeriods is an integer positve or negative */
  $DateToAddTo = trim($DateToAddTo);

	if (mb_strpos($DateToAddTo, '/')) {
		$Date_Array = explode('/', $DateToAddTo);
	} elseif (mb_strpos($DateToAddTo, '-')) {
		$Date_Array = explode('-', $DateToAddTo);
	} elseif (mb_strpos($DateToAddTo, '.')) {
		$Date_Array = explode('.', $DateToAddTo);
  }

	if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') OR ($_SESSION['DefaultDateFormat'] == 'd.m.Y')){

		switch ($PeriodString) {
		case 'd': //Days
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[0] + $NumberPeriods , (int)$Date_Array[2]));
			break;
		case 'w': //weeks
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[0] + ($NumberPeriods * 7), (int)$Date_Array[2]));
			break;
		case 'm': //months
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1] + $NumberPeriods, (int)$Date_Array[0], (int)$Date_Array[2]));
			break;
		case 'y': //years
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[0], (int)$Date_Array[2] + $NumberPeriods));
			break;
		default:
			return 0;
		}
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y'){

		switch ($PeriodString) {
		case 'd':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[0], (int)$Date_Array[1] + $NumberPeriods, (int)$Date_Array[2]));
			break;
		case 'w':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[0], (int)$Date_Array[1] + ($NumberPeriods * 7), (int)$Date_Array[2]));
			break;
		case 'm':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[0] + $NumberPeriods, (int)$Date_Array[1], (int)$Date_Array[2]));
			break;
		case 'y':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[0], (int)$Date_Array[1], (int)$Date_Array[2] + $NumberPeriods));
			break;
		default:
			return 0;
		}
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d'){

		switch ($PeriodString) {
		case 'd':
		/* Fix up the Y/m/d calculation */
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[2] + $NumberPeriods, (int)$Date_Array[0]));
			break;
		case 'w':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[2] + ($NumberPeriods * 7), (int)$Date_Array[0]));
			break;
		case 'm':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1] + $NumberPeriods, (int)$Date_Array[2], (int)$Date_Array[0]));
			break;
		case 'y':
			return Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, (int)$Date_Array[1], (int)$Date_Array[2], (int)$Date_Array[0] + $NumberPeriods));
			break;
		default:
			return 0;
		}
	}
}

/**************************************************************************************************************
* Function: DateDiff
* Description: Calculates the difference between two dates in specified units
* Parameters:
*   $Date1 - First date in DefaultDateFormat
*   $Date2 - Second date in DefaultDateFormat
*   $Period - Unit for difference calculation (d=days, w=weeks, m=months, y=years, s=seconds)
* Returns: Integer representing the difference in specified units
**************************************************************************************************************/
function DateDiff($Date1, $Date2, $Period) {

	/* expects dates in the format specified in $_SESSION['DefaultDateFormat'] - period can be one of 'd','w','y','m'
	months are assumed to be 30 days and years 365.25 days This only works
	provided that both dates are after 1970. Also only works for dates up to the year 2035 ish */

	$Date1 = trim($Date1);
	$Date2 = trim($Date2);

	if (mb_strpos($Date1, '/')) {
		$Date1_array = explode('/', $Date1);
	} elseif (mb_strpos($Date1, '-')) {
		$Date1_array = explode('-', $Date1);
	} elseif (mb_strpos($Date1, '.')) {
		$Date1_array = explode('.', $Date1);
  }
	if (mb_strpos($Date2, '/')) {
		$Date2_array = explode('/', $Date2);
	} elseif (mb_strpos($Date2, '-')) {
		$Date2_array = explode('-', $Date2);
	} elseif (mb_strpos($Date2, '.')) {
		$Date2_array = explode('.', $Date2);
  }

	if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') or ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
		$Date1_Stamp = mktime(0, 0, 0, (int)$Date1_array[1], (int)$Date1_array[0], (int)$Date1_array[2]);
		$Date2_Stamp = mktime(0, 0, 0, (int)$Date2_array[1], (int)$Date2_array[0], (int)$Date2_array[2]);
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		$Date1_Stamp = mktime(0, 0, 0, (int)$Date1_array[0], (int)$Date1_array[1], (int)$Date1_array[2]);
		$Date2_Stamp = mktime(0, 0, 0, (int)$Date2_array[0], (int)$Date2_array[1], (int)$Date2_array[2]);
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
		$Date1_Stamp = mktime(0, 0, 0, (int)$Date1_array[1], (int)$Date1_array[2], (int)$Date1_array[0]);//Changeorder of entries to match Y/M/D format
		$Date2_Stamp = mktime(0, 0, 0, (int)$Date2_array[1], (int)$Date2_array[2], (int)$Date2_array[0]); //Changeorder of entries to match Y/M/D format
	}

	$Difference = $Date1_Stamp - $Date2_Stamp;
	/* Difference is the number of seconds between each date negative if Date 2 > Date 1 */

	switch ($Period) {
	case 'd':
		return (int) ($Difference / (24 * 60 * 60));
		break;
	case 'w':
		return (int) ($Difference / (24 * 60 * 60 * 7));
		break;
	case 'm':
		return (int) ($Difference / (24 * 60 * 60 * 30));
		break;
	case 's':
		return $Difference;
		break;
	case 'y':
		return (int) ($Difference / (24 * 60 * 60 * 365.25));
		break;
	default:
		return 0;
	}

}

/**************************************************************************************************************
* Function: CalcEarliestDispatchDate
* Description: Calculates the earliest possible dispatch date based on company settings
* Parameters: None
* Returns: Unix timestamp of earliest possible dispatch date
**************************************************************************************************************/
function CalcEarliestDispatchDate() {

	/* If the hour is after Dispatch Cut Off Time default dispatch date to tomorrow */
	$EarliestDispatch = (Date('H') >= $_SESSION['DispatchCutOffTime']) ? (time() + 24 * 60 * 60) : time();

	if ((Date('w', $EarliestDispatch) == 0) AND ($_SESSION['WorkingDaysWeek'] != '7')) {

	/*if today is a sunday AND the company does NOT work 7 days a week, the dispatch date must be tomorrow (Monday) or after */

		$EarliestDispatch = Mktime(0, 0, 0, Date('m', $EarliestDispatch), Date('d', $EarliestDispatch) + 1, Date('y', $EarliestDispatch));

	} elseif ((Date('w', $EarliestDispatch) == 6) AND ($_SESSION['WorkingDaysWeek'] != '6') AND ($_SESSION['WorkingDaysWeek'] != '7')) {

	/*if today is a saturday AND the company does NOT work at least 6 days a week, the dispatch date must be Monday or after */

		$EarliestDispatch = Mktime(0, 0, 0, Date('m', $EarliestDispatch), Date('d', $EarliestDispatch) + 2, Date('y', $EarliestDispatch));

	}else {

		$EarliestDispatch = Mktime(0, 0, 0, Date('m'), Date('d'), Date('y'));
	}
	return $EarliestDispatch;
}

/**************************************************************************************************************
* Function: CreatePeriod
* Description: Creates a new accounting period
* Parameters:
*   $PeriodNo - The period number to create
*   $PeriodEnd - Unix timestamp of the period end date
* Returns: None
**************************************************************************************************************/
function CreatePeriod($PeriodNo, $PeriodEnd) {
	$GetPrdSQL = "INSERT INTO periods (periodno,
										lastdate_in_period
									) VALUES (
										'" . $PeriodNo . "',
										'" . Date('Y-m-d', $PeriodEnd) . "'
									)";
	$ErrMsg = __('An error occurred in adding a new period number');
	$GetPrdResult = DB_query($GetPrdSQL, $ErrMsg);

	$TotalsSQL = "INSERT INTO gltotals (account, period, amount)
				SELECT accountcode, '" . $PeriodNo . "', 0 FROM chartmaster";
	$ErrMsg = __('An error occurred in adding a new period number to the gltotals table');

}

/**************************************************************************************************************
* Function: PeriodExists
* Description: Checks if an accounting period exists for a given date
* Parameters: $TransDate - Unix timestamp of the date to check
* Returns: Boolean - true if period exists, false otherwise
**************************************************************************************************************/
function PeriodExists($TransDate) {

	/* Find the date a month on */
	$MonthAfterTransDate = Mktime(0, 0, 0, Date('m', $TransDate) + 1, Date('d', $TransDate), Date('Y', $TransDate));

	$GetPrdSQL = "SELECT periodno FROM periods WHERE lastdate_in_period < '" . Date('Y/m/d', $MonthAfterTransDate) . "' AND lastdate_in_period >= '" . Date('Y/m/d', $TransDate) . "'";

	$ErrMsg = __('An error occurred in retrieving the period number');
	$GetPrdResult = DB_query($GetPrdSQL, $ErrMsg);

	if (DB_num_rows($GetPrdResult) == 0) {
		return false;
	} else {
		return true;
	}

}

/**************************************************************************************************************
* Function: GetPeriod
* Description: Determines the period number for a given date
* Parameters:
*   $TransDate - The date to check in DefaultDateFormat
*   $UseProhibit - Boolean to check against prohibited posting dates (default true)
* Returns: Integer representing the period number
**************************************************************************************************************/
function GetPeriod($TransDate, $UseProhibit = true) {

	/* Convert the transaction date into a unix time stamp.*/

	if (mb_strpos($TransDate, '/')) {
		$DateArray = explode('/', $TransDate);
	} elseif (mb_strpos($TransDate, '-')) {
		$DateArray = explode('-', $TransDate);
	} elseif (mb_strpos($TransDate, '.')) {
		$DateArray = explode('.', $TransDate);
	}

	if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') or ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
		$TransDate = mktime(0, 0, 0, $DateArray[1], $DateArray[0], $DateArray[2]);
	} elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
		$TransDate = mktime(0, 0, 0, $DateArray[0], $DateArray[1], $DateArray[2]);
	} elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d' OR $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
		$TransDate = mktime(0, 0, 0, $DateArray[1], $DateArray[2], $DateArray[0]);
	}

	if (Is_Date(ConvertSQLDate($_SESSION['ProhibitPostingsBefore'])) AND $UseProhibit){ //then the ProhibitPostingsBefore configuration is set
		$Date_Array = explode('-', $_SESSION['ProhibitPostingsBefore']); //its in ANSI SQL format
		$ProhibitPostingsBefore = mktime(0, 0, 0, $Date_Array[1], $Date_Array[2], $Date_Array[0]);

		/* If transaction date is in a closed period use the month end of that period */
		if ($TransDate < $ProhibitPostingsBefore) {
			$TransDate = $ProhibitPostingsBefore;
		}
	}
	/* Find the unix timestamp of the last period end date in periods table */
	$SQL = "SELECT MAX(lastdate_in_period), MAX(periodno) from periods";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if (is_null($MyRow[0])){ //then no periods are currently defined - so set a couple up starting at 0
		$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",__('Could not insert first period'));
		$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",__('Could not insert second period'));
		$LastPeriod = 1;
		$LastPeriodEnd = mktime(0, 0, 0, Date('m') + 2, 0, Date('Y'));
	} else {
		$Date_Array = explode('-', $MyRow[0]);
		$LastPeriodEnd = mktime(0, 0, 0, $Date_Array[1] + 1, 0, (int)$Date_Array[0]);
		$LastPeriod = $MyRow[1];
	}
	/* Find the unix timestamp of the first period end date in periods table */
	$SQL = "SELECT MIN(lastdate_in_period), MIN(periodno) from periods";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$Date_Array = explode('-', $MyRow[0]);
	$FirstPeriodEnd = mktime(0, 0, 0, $Date_Array[1], 0, (int)$Date_Array[0]);
	$FirstPeriod = $MyRow[1];

	/* If the period number doesn't exist */
	if (!PeriodExists($TransDate)) {
		/* if the transaction is after the last period */

		if ($TransDate > $LastPeriodEnd) {

			$PeriodEnd = mktime(0, 0, 0, Date('m', $TransDate) + 1, 0, Date('Y', $TransDate));

			while ($PeriodEnd >= $LastPeriodEnd) {
				if (Date('m', $LastPeriodEnd) <= 13) {
					$LastPeriodEnd = mktime(0, 0, 0, Date('m', $LastPeriodEnd) + 2, 0, Date('Y', $LastPeriodEnd));
				} else {
					$LastPeriodEnd = mktime(0, 0, 0, 2, 0, Date('Y', $LastPeriodEnd) + 1);
				}
				$LastPeriod++;
				CreatePeriod($LastPeriod, $LastPeriodEnd);
			}
		} else {
		/* The transaction is before the first period */
			$PeriodEnd = mktime(0, 0, 0, Date('m', $TransDate), 0, Date('Y', $TransDate));
			$Period = $FirstPeriod - 1;
			while ($FirstPeriodEnd > $PeriodEnd) {
				CreatePeriod($Period, $FirstPeriodEnd);
				$Period--;
				if (Date('m', $FirstPeriodEnd) > 0) {
					$FirstPeriodEnd = mktime(0, 0, 0, Date('m', $FirstPeriodEnd), 0, Date('Y', $FirstPeriodEnd));
				} else {
					$FirstPeriodEnd = mktime(0, 0, 0, 13, 0, Date('Y', $FirstPeriodEnd));
				}
			}
		}
	} else if (!PeriodExists(mktime(0, 0, 0, Date('m',$TransDate) + 1, Date('d',$TransDate), Date('Y',$TransDate)))) {
		/* Make sure the following months period exists */
		$SQL = "SELECT MAX(lastdate_in_period), MAX(periodno) from periods";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$Date_Array = explode('-', $MyRow[0]);
		$LastPeriodEnd = mktime(0, 0, 0, $Date_Array[1] + 2, 0, (int)$Date_Array[0]);
		$LastPeriod = $MyRow[1];
		CreatePeriod($LastPeriod + 1, $LastPeriodEnd);
	}

	/* Now return the period number of the transaction */

	$MonthAfterTransDate = Mktime(0, 0, 0, Date('m', $TransDate) + 1, Date('d', $TransDate), Date('Y', $TransDate));
	$GetPrdSQL = "SELECT periodno
					FROM periods
					WHERE lastdate_in_period < '" . Date('Y-m-d', $MonthAfterTransDate) . "'
					AND lastdate_in_period >= '" . Date('Y-m-d', $TransDate) . "'";

	$ErrMsg = __('An error occurred in retrieving the period number');
	$GetPrdResult = DB_query($GetPrdSQL, $ErrMsg);
	$MyRow = DB_fetch_row($GetPrdResult);

	return $MyRow[0];
}
