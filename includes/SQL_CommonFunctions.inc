<?php
/* $Revision: 1.7 $ */
/* Common SQL Functions */


Function GetNextTransNo ($TransType, &$db){

/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
Also updates the transaction number

10 sales invoice
11 sales credit note
12 sales receipt

*/

	$SQL = "SELECT TypeNo FROM SysTypes WHERE TypeID = " . $TransType;

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');

	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($GetTransNoResult);
	$SQL = 'UPDATE SysTypes SET TypeNo = ' . ($myrow[0] + 1) . ' WHERE TypeID = ' . $TransType;
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	return $myrow[0] + 1;
}


Function ReadInCompanyRecord (&$db) {

/* Reads all the company data set up in the company record and returns an array */

	$Query=	"SELECT
		GSTNo,
		PostalAddress,
		RegOffice1,
		RegOffice2,
		RegOffice3,
		Telephone,
		Fax,
		Email,
		CurrencyDefault,
		DebtorsAct,
		PytDiscountAct,
		CreditorsAct,
		PayrollAct,
		GRNAct,
		ExchangeDiffAct,
		PurchasesExchangeDiffAct,
		RetainedEarnings,
		FreightAct,
		GLLink_Debtors,
		GLLink_Creditors,
		GLLink_Stock
	FROM Companies
	WHERE CoyCode=1";

	$ErrMsg = _('An error occurred accessing the database to retrieve the company information');
	$ReadCoyResult = DB_query($Query,$db,$ErrMsg);

	if (DB_num_rows($ReadCoyResult)==0) {
      		echo '<BR><B>';
		prnMsg( _('The company record has not yet been set up') . '</B><BR>' . _('From the system setup tab select company maintenance to enter the company infomation and system preferences'),'error','CRITICAL PROBLEM');
		return 0;
	} else {
		$myrow = DB_fetch_array($ReadCoyResult);
		return $myrow;
	}
}


Function GetStockGLCode ($StockID, &$db){

/*Gets the GL Codes relevant to the stock item account from the stock category record */
	$QuerySQL = "SELECT StockAct,
				AdjGLAct,
				PurchPriceVarAct,
				MaterialUseageVarAc,
				WIPAct
			FROM StockMaster,
				StockCategory
			WHERE StockMaster.CategoryID=StockCategory.CategoryID
			AND StockMaster.StockID = '" . $StockID . "'";

	$ErrMsg =  _('The stock GL codes could not be retreived because');
	$GetStkGLResult = DB_query($QuerySQL, $db, $ErrMsg);

	$myrow = DB_fetch_array($GetStkGLResult);
	return $myrow;
}

Function GetTaxRate ($TaxAuthority, $DispatchTaxAuthority, $TaxLevel, &$db){

/*Gets the Tax rate applicable to an item from the TaxAuthority of the branch and TaxLevel of the item */

	$QuerySQL = "SELECT TaxRate FROM TaxAuthLevels WHERE TaxAuthority=" . $TaxAuthority . " AND DispatchTaxAuthority=" . $DispatchTaxAuthority . " AND Level = " . $TaxLevel;

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

?>
