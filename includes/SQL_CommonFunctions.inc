<?php
/* $Revision: 1.2 $ */
/* Common SQL Functions */


Function GetNextTransNo ($TransType, &$db){

/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
Also updates the transaction number

10 sales invoice
11 sales credit note
12 sales receipt

*/

$SQL = "SELECT TypeNo FROM SysTypes WHERE TypeID = " . $TransType;

$GetTransNoResult = DB_query($SQL,$db);
if (DB_error_no($db) !=0){
	  echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The next transaction number could not be retrieved from the database: -<BR>" . DB_error_msg($db);
	  if ($debug==1){
		   echo "<BR>The following SQL to retrieve the transaction number was used:<BR>$SQL<BR>";
	  }
	  return 0;
}

$myrow = DB_fetch_row($GetTransNoResult);

$NextTransNumber = $myrow[0] + 1;

$SQL = "UPDATE SysTypes SET TypeNo = " . $NextTransNumber . " WHERE TypeID = " . $TransType;

$GetTransNoResult = DB_query($SQL,$db);
if (DB_error_no($db) !=0){
	  echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The transaction number could not be incremented: -<BR>" . DB_error_msg($db);
	  if ($debug==1){
		   echo "<BR>The following SQL to increment the transaction number was used:<BR>$SQL<BR>";
	  }
	  return 0;
}


Return $NextTransNumber;

}


Function ReadInCompanyRecord (&$db) {

/* Reads all the company data set up in the company record and returns an array */

$Query="SELECT GSTNo, PostalAddress, RegOffice1, RegOffice2, RegOffice3, Telephone, Fax, Email, CurrencyDefault,DebtorsAct, PytDiscountAct, CreditorsAct, PayrollAct, GRNAct, ExchangeDiffAct, PurchasesExchangeDiffAct, RetainedEarnings, FreightAct, GLLink_Debtors, GLLink_Creditors, GLLink_Stock FROM Companies WHERE CoyCode=1";


$ReadCoyResult = DB_query($Query,$db);
if (DB_error_no($db)!=0){
	echo "<BR>An error occurred accessing the database to retrieve the company information: - " .DB_error_msg($db);
	return 0;
} elseif (DB_num_rows($ReadCoyResult)==0) {
      echo "<BR><B>CRITICAL PROBLEM: The company record has not yet been set up</B><BR>From the system setup tab, select company maintenance to enter the company infomation and system preferences";
      return 0;
} else {
	$myrow = DB_fetch_array($ReadCoyResult);
	return $myrow;
}

}


Function GetStockGLCode ($StockID, &$db){

/*Gets the GL Codes relevant to the stock item account from the stock category record */

	$QuerySQL = "SELECT StockAct, AdjGLAct, PurchPriceVarAct, MaterialUseageVarAc, WIPAct FROM StockMaster, StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID AND StockMaster.StockID = '" . $StockID . "'";


	$GetStkGLResult = DB_query($QuerySQL,$db);

	if (DB_error_no($db) != 0){
		echo "The stock GL codes could not be retreived because : - " . DB_error_msg($db) . " The SQL that was used to try to get this information was :<BR>" . $QuerySQL;
		return 0;
	}
	$myrow = DB_fetch_array($GetStkGLResult);

	return $myrow;

}

Function GetTaxRate ($TaxAuthority, $DispatchTaxAuthority, $TaxLevel, &$db){

/*Gets the Tax rate applicable to an item from the TaxAuthority of the branch and TaxLevel of the item */
	Global $debug;

	$QuerySQL = "SELECT TaxRate FROM TaxAuthLevels WHERE TaxAuthority=" . $TaxAuthority . " AND DispatchTaxAuthority=" . $DispatchTaxAuthority . " AND Level = " . $TaxLevel;

	$GetTaxRateResult = DB_query($QuerySQL,$db);

	if (DB_error_no($db) != 0){
		echo "<BR>The tax rate for this item could not be retreived because : - " . DB_error_msg($db);
		if ($debug==1){
			echo  "<BR>The SQL that was used to try to get this information was :<BR>" . $QuerySQL;
		}
		return 0;
	}
	if (DB_num_rows($GetTaxRateResult)==1){
		$myrow = DB_fetch_row($GetTaxRateResult);
		return $myrow[0];
	} else {
		/*The tax rate is not defined for this Tax Authority and Dispatch Tax Authority */
		return 0;
	}

}



?>
