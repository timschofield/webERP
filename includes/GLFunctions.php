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
    $TagArray - Array of tag references to be inserted
Returns:
    boolean - Always returns true
*/
function InsertGLTags($TagArray) {
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
    $TagArray - Array of tag references to look up
Returns:
    string - HTML formatted string containing tag references and descriptions
*/
function GetDescriptionsFromTagArray($TagArray) {
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
    $AccountCode - The GL account code
    $PeriodNo - The period number up to which the balance is calculated
Returns:
    float - The calculated balance for the account up to the specified period, or 0 if no records found
*/
function GetGLAccountBalance($AccountCode, $PeriodNo){
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
    $AccountCode - The GL account code
Returns:
    string - The name of the GL account or '' if no records found
*/
function GetGLAccountName($AccountCode){
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
	$SelectedPeriod - The value for the selected period
	$PreviousPeriod - The value for the previous period
Returns:
	string - The relative change formatted as a percentage, or 'N/A' if the previous period is zero
*/
function RelativeChange($SelectedPeriod, $PreviousPeriod) {
	// Calculates the relative change between selected and previous periods. Uses percent with locale number format.
	if (ABS($PreviousPeriod) >= 0.01) {
		return locale_number_format(($SelectedPeriod - $PreviousPeriod) * 100 / $PreviousPeriod,
			$_SESSION['CompanyRecord']['decimalplaces']) . '%';
	} else {
		return __('N/A');
	}
}
