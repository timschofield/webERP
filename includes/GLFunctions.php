<?php

/*************************************************************************************************************
Functions in this file:

GetCashSalesValueStillFloating() - Retrieves the total cash sales value still floating for a company within a date range
GetDescriptionsFromTagArray() - Retrieves descriptions for an array of tag references
GetGLAccountBalance() - Retrieves the balance for a GL account up to a specific period
GetGLAccountValueBetweenTwoDates() - Retrieves the total value for a GL account between two dates, with an optional filter
InsertGLTags() - Inserts tags into the GL tags table for a journal line
*************************************************************************************************************/

/*************************************************************************************************************
Brief Description: Inserts tags into the GL tags table for a journal line
Parameters:
    $TagArray - Array of tag references to be inserted
Returns:
    boolean - Always returns true
*************************************************************************************************************/
function InsertGLTags($TagArray) {
	if (!empty($TagArray)) {
		$ErrMsg = _('Cannot insert a GL tag for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL tag record was');
		foreach ($TagArray as $Tag) {
			$SQL = "INSERT INTO gltags 
					VALUES ( LAST_INSERT_ID(),
							'" . $Tag . "')";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
        }
	}
    return true;
}

/*************************************************************************************************************
Brief Description: Retrieves descriptions for an array of tag references
Parameters:
    $TagArray - Array of tag references to look up
Returns:
    string - HTML formatted string containing tag references and descriptions
*************************************************************************************************************/
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
				$TagRow['tagdescription'] = _('None');
			}
			$TagDescriptions .= $Tag . ' - ' . $TagRow['tagdescription'] . '<br />';
		}
	}
	return $TagDescriptions;
}

/*************************************************************************************************************
Brief Description: Retrieves the balance for a GL account up to a specific period
Parameters:
    $AccountCode - The GL account code
    $PeriodNo - The period number up to which the balance is calculated
Returns:
    float - The calculated balance for the account up to the specified period, or 0 if no records found
*************************************************************************************************************/
function GetGLAccountBalance($AccountCode, $PeriodNo){
	$SQL = "SELECT SUM(amount) AS total
			FROM gltotals
			WHERE account = '" . $AccountCode . "'
				AND period <= ". $PeriodNo . "";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['total'] ?? 0);
}

/*************************************************************************************************************
Brief Description: Retrieves the total value for a GL account between two dates, with an optional filter
Parameters:
    $AccountCode - The GL account code
    $Filter - Optional filter string ('TO_CASH_KANTOR' or empty)
    $DateFrom - The start date for the period
    $DateTo - The end date for the period
Returns:
    float - The total value for the account within the specified date range and filter, or 0 if no records found
*************************************************************************************************************/
function GetGLAccountValueBetweenTwoDates($AccountCode, $Filter, $DateFrom, $DateTo){
	if ($Filter == 'TO_CASH_KANTOR') {
		$SQLFilter = " AND (gltrans.narrative LIKE '%CASH TO CASH%'
						OR gltrans.narrative LIKE '%CASH TO SUPP%'
						OR gltrans.narrative LIKE '%BANK TO CASH%'
						OR gltrans.narrative LIKE '%CASH TO BANK%'
						OR gltrans.narrative LIKE '%UANG KECIL%')";
	} else {
		$SQLFilter = "";
	}

	$SQL = "SELECT SUM(gltrans.amount) AS total
			FROM gltrans
			WHERE gltrans.trandate >= '" . $DateFrom . "'
				AND gltrans.trandate <= '" . $DateTo . "'
				AND gltrans.account = '" . $AccountCode . "'" .
				$SQLFilter;

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['total'] ?? 0);
}

/*************************************************************************************************************
Brief Description: Retrieves the total cash sales value still floating for a company within a date range
Parameters:
    $Company - The partner code of the company
    $DateFrom - The start date for the period
    $DateTo - The end date for the period
Returns:
    float - The total cash sales value still floating, or 0 if no records found
*************************************************************************************************************/
function GetCashSalesValueStillFloating($Company, $DateFrom, $DateTo){
	$SQL = "SELECT SUM(gltrans.amount) AS total
			FROM gltrans
			WHERE gltrans.trandate >= '" . $DateFrom . "'
				AND gltrans.trandate <= '" . $DateTo . "'
				AND gltrans.account IN (SELECT klposcashaccount
										FROM locations
										WHERE partnercode = '" . $Company . "'
											AND typeloc LIKE 'SHOP%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return ($MyRow['total'] ?? 0);
}


?>