<?php

/* $Revision: 1.16 $ */

/* Common SQL Functions */


Function GetNextTransNo ($TransType, &$db){

/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
Also updates the transaction number

10 sales invoice
11 sales credit note
12 sales receipt

*/

	$SQL = "SELECT typeno FROM systypes WHERE typeid = " . $TransType;

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');

	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($GetTransNoResult);
	$SQL = 'UPDATE systypes SET typeno = ' . ($myrow[0] + 1) . ' WHERE typeid = ' . $TransType;
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	return $myrow[0] + 1;
}


Function GetStockGLCode ($StockID, &$db){

/*Gets the GL Codes relevant to the stock item account from the stock category record */
	$QuerySQL = "SELECT stockact,
				adjglact,
				purchpricevaract,
				materialuseagevarac,
				wipact
			FROM stockmaster,
				stockcategory
			WHERE stockmaster.categoryid=stockcategory.categoryid
			AND stockmaster.stockid = '" . $StockID . "'";

	$ErrMsg =  _('The stock GL codes could not be retreived because');
	$GetStkGLResult = DB_query($QuerySQL, $db, $ErrMsg);

	$myrow = DB_fetch_array($GetStkGLResult);
	return $myrow;
}

Function GetTaxRate ($TaxAuthority, $DispatchTaxProvince, $TaxCategory, &$db){

/*Gets the Tax rate applicable to an item from the TaxAuthority of the branch and TaxLevel of the item */

	$QuerySQL = "SELECT taxrate 
			FROM taxauthrates 
			WHERE taxauthority=" . $TaxAuthority . " 
			AND dispatchtaxprovince=" . $DispatchTaxProvince . " 
			AND taxcatid = " . $TaxCategory;

	$ErrMsg = _('The tax rate for this item could not be retreived because');
	$GetTaxRateResult = DB_query($QuerySQL,$db,$ErrMsg);

	if (DB_num_rows($GetTaxRateResult)==1){
		$myrow = DB_fetch_row($GetTaxRateResult);
		return $myrow[0];
	} else {
		/*The tax rate is not defined for this Tax Authority and Dispatch Tax Authority */
		return 0;
	}

}

Function GetTaxes ($TaxGroup, $DispatchTaxProvince, $TaxCategory, &$db) {

	$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid=" . $TaxGroup . " 
			AND taxauthrates.dispatchtaxprovince=" . $DispatchTaxProvince . " 
			AND taxauthrates.taxcatid = " . $TaxCategory . "
			ORDER BY taxgrouptaxes.calculationorder";


	$ErrMsg = _('The taxes and rate for this tax group could not be retreived because');
	$GetTaxesResult = DB_query($SQL,$db,$ErrMsg);

	if (DB_num_rows($GetTaxesResult)>=1){
		return $GetTaxesResult;
	} else {
		/*The tax group is not defined with rates */
		return 0;
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

Function GetCreditAvailable($DebtorNo,&$db) {

	$sql = "SELECT debtorsmaster.debtorno, 
			debtorsmaster.creditlimit,
			SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) as balance
		FROM debtorsmaster INNER JOIN debtortrans 
			ON debtorsmaster.debtorno=debtortrans.debtorno
		WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
		GROUP BY debtorsmaster.debtorno,
			debtorsmaster.creditlimit";
		
	$ErrMsg =  _('The current account balance of the customer could not be retrieved because');
	$GetAccountBalanceResult = DB_query($sql, $db, $ErrMsg);
	
	if (DB_num_rows($GetAccountBalanceResult)==1){
		
		$myrow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $myrow['creditlimit'] - $myrow['balance'];
	} else {
		$sql = "SELECT creditlimit
				FROM debtorsmaster 
				WHERE debtorno='" . $DebtorNo . "'";
		$GetAccountBalanceResult = DB_query($sql, $db, $ErrMsg);
		$myrow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $myrow['creditlimit'];
	}
	/*Take into account the value of outstanding sales orders too */
	$sql = "SELECT SUM(salesorderdetails.unitprice *
				(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) *
				(1 - salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.debtorno = '" . $DebtorNo . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation = 0";
	
	$ErrMsg =  _('The value of outstanding orders for the customer could not be retrieved because');
	$GetOSOrdersResult = DB_query($sql, $db, $ErrMsg);
	
	$myrow = DB_fetch_array($GetOSOrdersResult);
	$CreditAvailable -= $myrow['ordervalue'];
	
	Return $CreditAvailable;
	
}

?>