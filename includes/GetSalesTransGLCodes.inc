<?php
/* $Revision: 1.4 $ */
/*Functions to get the GL codes for customer transactions based on
$Area, $StockID to determine the stock category and the SalesType (Price List)

Function returns the relavent GL Code to post COGS entries to*/

Function GetCOGSGLAccount ($Area, $StockID, $SalesType, &$db) {

/*Get the StockCategory for this item */
	$SQL = "SELECT CategoryID FROM StockMaster WHERE StockID='" . $StockID . "'";
	$Result=DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);
	$StockCategory = $myrow[0];

/*Gets the GL Code for the COGS for a specific area and stock category. */

	$SQL = "SELECT GLCode FROM COGSGLPostings WHERE Area = '" . $Area . "' AND StkCat = '" . $StockCategory . "' AND SalesType='" . $SalesType . "'";
	/*Need to determine if COGS GL codes set up for the stk cat, area and sales type of the item/customer branch and 	use the most appropriate GL Code.
	If no match for all fields area, sales type, stock category then the rules for choosing the nearest match
	are

	- goes for gold a match for salestype stock category and area then -
	- matching Area, stock category and AN Sales type
	- see if matching Area, stock category - AN sales type
	- see if matching Area, saletype and ANY StockCategory
	- see if mathcing Area, ANY stock category and AN salestype
	- see if matching stockcategory, AN area and AN salestype
	- if still no record is found then the GL Code for the default area, sales type and default stock category is used

	*/

	$ErrMsg = _('Can not retrieve the cost of sales GL code because');
	$DbgMsg =_('SQL to get the cost of sales GL Codes for sales and discounts');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT GLCode FROM COGSGLPostings
			WHERE Area = '" . $Area . "'
			AND StkCat = '" . $StockCategory . "'
			AND SalesType = 'AN'";

		$ErrMsg = _('Can not retrieve the cost of sales GL code because');
		$DbgMsg = _('SQL to get the cost of sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT GLCode FROM COGSGLPostings
			WHERE Area = '" . $Area . "'
			AND StkCat = 'ANY' AND SalesType = '" . $SalesType . "'";

		$ErrMsg = _('Can not retrieve the cost of sales GL code because');
		$DbgMsg = _('SQL to get the cost of sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT GLCode FROM COGSGLPostings WHERE Area = 'AN' AND SalesType='AN' AND StkCat = '$StockCategory'";
		$ErrMsg = _('Can not retrieve the cost of sales GL code because');
		$DbgMsg = _('SQL to get the cost of sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	}

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT GLCode FROM COGSGLPostings WHERE Area = '$Area' AND StkCat = 'ANY' AND SalesType='AN'";

		$ErrMsg = _('Can not retrieve the cost of sales GL code because');
		$DbgMsg = _('SQL to get the cost of sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	}

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT GLCode FROM COGSGLPostings WHERE Area = 'AN' AND StkCat = 'ANY' AND SalesType='AN'";

		$ErrMsg = _('Can not retrieve the cost of sales GL code because') . '<BR>' . DB_error_msg($db);
		$DbgMsg = _('SQL to get the cost of sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	}
	if (DB_num_rows($Result)==0){ /*STILL!*/
		/*The default if all else fails */
		/*Check GL account 1 exists */
		$SQL = "SELECT AccountCode FROM ChartMaster WHERE AccountCode=1";
		$Result = DB_query($SQL,$db);
		if (DB_num_rows($Result)==0){ /*It doesn't exist so  create it */
			$Result = DB_query("SELECT GroupName FROM AccountGroups WHERE GroupName='Sales'",$db);
			if (DB_num_rows($Result)==0) {
				$Result = DB_query("INSERT INTO AccountGroups (GroupName, SectionInAccounts, PandL, SequenceInTB) VALUES ('Sales',1,1,5)",$db);
			}
			$SQL = "INSERT INTO ChartMaster VALUES (1, 'Default Sales and COGS', 'Sales')";
			$Result = DB_query($SQL,$db);
		}
		return 1;
	}


	$myrow = DB_fetch_row($Result);
	Return $myrow[0];
}

Function GetSalesGLAccount ($Area, $StockID, $SalesType, &$db) {

/*Gets the  Sales GL Code for a specific area, sales type and stock category */

/*Get the StockCategory for this item */
	$SQL = "SELECT CategoryID FROM StockMaster WHERE StockID='" . $StockID . "'";
	$Result=DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);
	$StockCategory = $myrow[0];


	/*Need to determine if Sales GL codes set up for the stk cat, area and sales type of the item/customer branch and 	use the most appropriate GL Code.
	If no match for all fields area, sales type, stock category then the rules for choosing the nearest match
	are

	- goes for gold a match for salestype stock category and area then -
	- matching Area, stock category and AN Sales type
	- see if matching Area, stock category - AN sales type
	- see if matching Area, saletype and ANY StockCategory
	- see if mathcing Area, ANY stock category and AN salestype
	- see if matching stockcategory, AN area and AN salestype
	- if still no record is found then the GL Code for the default area, sales type and default stock category is used

	*/

	$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = '" . $Area . "' AND StkCat = '" . $StockCategory . "' AND SalesType = '". $SalesType . "'";

	$ErrMsg = _('There was a problem retrieving the sales general ledger code because');
	$DbgMsg =  _('SQL to get the sales GL Codes for sales and discounts');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($Result)==0){
		DB_free_result($Result);
		$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = '" . $Area . "' AND StkCat = '" . $StockCategory . "' AND SalesType = 'AN'";

		$ErrMsg = _('Can not retrieve the sales GL code because');
		$DbgMsg = _('SQL to get the sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){
		DB_free_result($Result);
		$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = '" . $Area . "' AND StkCat = 'ANY' AND SalesType = '" . $SalesType . "'";

		$ErrMsg =_('Can not retrieve the sales GL code because');
		$DbgMsg = _('SQL to get the sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){
		DB_free_result($Result);
		$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = 'AN' AND SalesType='AN' AND StkCat = '$StockCategory'";
		$ErrMsg = _('Can not retrieve the sales GL code because');
		$DbgMsg = _('SQL to get the sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){
		DB_free_result($Result);
		$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = '$Area' AND StkCat = 'ANY' AND SalesType='AN'";
		$ErrMsg = _('Can not retrieve the sales GL code because');
		$DbgMsg = _('SQL to get the sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	}

	if (DB_num_rows($Result)==0){

		DB_free_result($Result);
		$SQL = "SELECT SalesGLCode, DiscountGLCode FROM SalesGLPostings WHERE Area = 'AN' AND StkCat = 'ANY' AND SalesType='AN'";
		$ErrMsg = _('Can not retrieve the sales GL code because');
		$DbgMsg = _('SQL to get the sales GL Codes for sales and discounts');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	}
	if (DB_num_rows($Result)==0){ /*STILL!*/
		/*The default if all else fails */
		/*Check GL account 1 exists */
		$SQL = "SELECT AccountCode FROM ChartMaster WHERE AccountCode=1";
		$Result = DB_query($SQL,$db);
		if (DB_num_rows($Result)==0){ /*It doesn't exist so  create it */
			/*First check the account group sales exists */
			$Result = DB_query("SELECT GroupName FROM AccountGroups WHERE GroupName='Sales'",$db);
			if (DB_num_rows($Result)==0) {
				$Result = DB_query("INSERT INTO AccountGroups (GroupName, SectionInAccounts, PandL, SequenceInTB) VALUES ('Sales',1,1,5)",$db);
			}
			$SQL = "INSERT INTO ChartMaster VALUES (1, 'Default Sales and COGS', 'Sales')";
			$Result = DB_query($SQL,$db);
		}
		return 1;
	}

	$myrow = DB_fetch_array($Result);
	Return $myrow;
}

?>
