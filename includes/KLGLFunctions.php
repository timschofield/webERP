<?php

/*************************************************************************************************************
Functions in this file:

GetCashSalesValueStillFloating() - Retrieves the total cash sales value still floating for a company within a date range
GetGLAccountValueBetweenTwoDates() - Retrieves the total value for a GL account between two dates, with an optional filter

*************************************************************************************************************/

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