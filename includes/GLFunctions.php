<?php

/*************************************************************************************************************
Functions in this file:

GetDescriptionsFromTagArray() - Retrieves descriptions for an array of tag references
GetGLAccountBalance() - Retrieves the balance for a GL account up to a specific period
GetGLAccountName() - Retrieves the name of a GL account
InsertGLTags() - Inserts tags into the GL tags table for a journal line
RelativeChange() - Calculates the relative change between selected and previous periods

*************************************************************************************************************/

/**
Inserts tags into the GL tags table for a journal line
Parameters:
    array|null $TagArray - Array of tag references to be inserted
Returns:
    boolean - Always returns true
*/
function InsertGLTags(?array $TagArray): bool {
	if (!empty($TagArray)) {
		$ErrMsg = __('Cannot insert a GL tag for the journal line because');
		foreach ($TagArray as $Tag) {
			$SQL = "INSERT INTO gltags
					VALUES ( LAST_INSERT_ID(),
							'" . $Tag . "')";
			$Result = DB_query($SQL, $ErrMsg, '', true);
        }
	}
    return true;
}

/**
Retrieves descriptions for an array of tag references
Parameters:
    array|null $TagArray - Array of tag references to look up
Returns:
    string - HTML formatted string containing tag references and descriptions
*/
function GetDescriptionsFromTagArray(?array $TagArray): string {
	$TagDescriptions = '';
	if (isset($TagArray)){
		foreach ($TagArray as $Tag) {
			$TagSql = "SELECT tagdescription
						FROM tags
						WHERE tagref='" . $Tag . "'";
			$TagResult = DB_query($TagSql);
			$TagRow = DB_fetch_array($TagResult);
			if ($Tag == 0) {
				$TagRow['tagdescription'] = __('None');
			}
			$TagDescriptions .= $Tag . ' - ' . $TagRow['tagdescription'] . '<br />';
		}
	}
	return $TagDescriptions;
}

/**
Retrieves the balance for a GL account up to a specific period
Parameters:
    string|int $AccountCode - The GL account code
    int $PeriodNo - The period number up to which the balance is calculated
Returns:
    float - The calculated balance for the account up to the specified period, or 0 if no records found
*/
function GetGLAccountBalance(string|int $AccountCode, int $PeriodNo): float|int {
	$SQL = "SELECT SUM(amount) AS total
			FROM gltotals
			WHERE account = '" . $AccountCode . "'
				AND period <= ". $PeriodNo . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['total'] ?? 0);
}

/**
Retrieves the name of a GL account
Parameters:
    string|int $AccountCode - The GL account code
Returns:
    string - The name of the GL account or '' if no records found
*/
function GetGLAccountName(string|int $AccountCode): string {
	$SQL = "SELECT accountname
			FROM chartmaster
			WHERE accountcode = '" . $AccountCode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['accountname'] ?? '');
}

/**
Calculates the relative change between selected and previous periods. Uses percent with locale number format.
Parameters:
	float|int $SelectedPeriod - The value for the selected period
	float|int $PreviousPeriod - The value for the previous period
Returns:
	string - The relative change formatted as a percentage, or 'N/A' if the previous period is zero
*/
function RelativeChange(float|int $SelectedPeriod, float|int $PreviousPeriod): string {
	include(__DIR__ . '/SQL_CommonFunctions.php');
	// Calculates the relative change between selected and previous periods. Uses percent with locale number format.
	if (ABS($PreviousPeriod) >= CurrencyTolerance($_SESSION['CompanyRecord']['currencydefault'])) {
		return locale_number_format(($SelectedPeriod - $PreviousPeriod) * 100 / $PreviousPeriod,
			$_SESSION['CompanyRecord']['decimalplaces']) . '%';
	} else {
		return __('N/A');
	}
}


/**
Returns the cash flow activity name for a given activity code.
Parameters:
	string|int $Activity - The cash flow activity code
Returns:
	string - The corresponding activity name
*/
function CashFlowsActivityName(string|int $Activity): string {
	// Converts the cash flow activity number to an activity text.
	return match ((string)$Activity) {
		'-1'      => '<b>' . __('Not set up') . '</b>',
		'0'       => __('No effect on cash flow'),
		'1'       => __('Operating activity'),
		'2'       => __('Investing activity'),
		'3'       => __('Financing activity'),
		'4'       => __('Cash or cash equivalent'),
		default => '<b>' . __('Unknown') . '</b>',
	};
}

/**
Retrieves the currency rate for a given currency code
Parameters:
    string $CurrencyCode - The currency code
Returns:
    float - The currency rate for the given currency code or 0 if no records found
*/
function GetwebERPCurrencyRate(string $CurrencyCode): float|int {
	$SQL = "SELECT rate
			FROM currencies
			WHERE currabrev = '" . $CurrencyCode . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['rate'] ?? 0);
}
