<?php

/* Functions related to the cron job tasks for Kapal-Laut */

/*
Alphabetical list of functions in this file:
-------------------------------------------
AuthorizeAllInternalStockRequest - Automatically authorizes all pending internal stock requests
BlockInactiveUsers - Blocks inactive users based on their access level and days since last login
CleanDiscountForObsoleteItems - Removes discount categories from obsolete items
CleanInternalRequestsWithoutItems - Deletes internal stock requests that have no items
CleanListToPrint - Removes special characters from a string for clean printing
CleanObsoleteFromWebsite - Removes obsolete items from the website sales categories
CleanOldDoubleReceivedGoods - Fixes double-received goods in stock transfers
CleanPurchOrderDetails - Removes purchase order details with order number 0
CleanWorkOrdersWithoutItems - Removes work orders that have no items associated
CleanWrongPrices - Deletes prices where start date is greater than end date
KLCronJobChecks - Main function that runs scheduled tasks based on group parameter
KL_DailyCleanDB - Runs all database cleaning operations daily
KL_DailyEmailsToStaff - Sends various emails to staff members
KL_DailyOptimizationDatabase - Optimizes database tables in small batches
KL_DailySetObsoleteNoStock - Sets items with zero stock in specific categories to obsolete
PurgeAuditScriptsTable - Removes old entries from the audit scripts table
PurgeAuditTrailTable - Removes old entries from the audit trail table
PurgeKLTable - Generic function to purge old records from Kapal-Laut tables
PurgePackagingUsedTable - Removes old packaging usage records
PurgeRelatedItemsFromObsolete - Removes related item links for obsolete items
PurgeSessionsTable - Removes sessions older than 2 days
SetEndDatePriceToObsolete - Sets end date of prices to today for obsolete items
SetObsoleteForCategoryWithoutStock - Marks items with zero stock in specific category as obsolete
SetRLZeroForLocations - Sets reorder level to zero for specific locations
SetRLZeroForObsolete - Sets reorder level to zero for obsolete items
SetStatusCompleteToFinishedOldPurchaseOrders - Marks old purchase orders as complete
SetTopSalesByGroup - Calculates top sales ranking for items in a specific group
SetTopSalesRanking - Sets up and calculates sales performance rankings
ShowOrEmail - Helper function to display or append to email text based on settings
YesterdayServerUsage - Collects and reports server usage statistics for the previous day
*/

/**************************************************************************************************************
* Main function to run scheduled cron job tasks based on the group parameter
*
* @param string $Group - The task group identifier to run
* @param string $RootPath - Path to webERP root directory
* @param string $EmailText - Email text to append results to (optional)
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KLCronJobChecks($Group, $RootPath, $EmailText= ''){
	include('includes/GetPrice.php');
	include('includes/SQL_CommonFunctions.php');
	include('includes/StockFunctions.php');

	include('includes/KLDefines.php');
	include('includes/KLPrices.php');
	include('includes/KLBoards.php');
	include('includes/KLReorderLevel.php');
	include('includes/KLEmails.php');
	include('includes/KLGeneralFunctions.php');
	include('includes/KLMarketplaceFunctions.php');
	include('includes/KLSmartStockTransfers.php');

	include('includes/OCOpenCartGeneralFunctions.php');
	include('includes/OCWeberpToOpenCartSync.php');
	include('includes/OCOpenCartToWeberpSync.php');
	include('includes/OCOpenCartConnectDB.php');

	include('includes/ArchiveConnectDB.php');
	
	if ($Group == "0010-HourlySyncOpenCart"){
		$EmailText = WeberpToOpenCartHourlySync(false , true, $EmailText);
		$EmailText = OpenCartToWeberpSync(false , $EmailText);
	}elseif ($Group == "0100-CleanDB"){
		$EmailText = KL_DailyCleanDB(false, $EmailText);
	}elseif ($Group == "0200-Obsolete"){
		$EmailText = KL_DailySetObsoleteNoStock(false, $EmailText);
	}elseif ($Group == "0250-TopSales"){
		$EmailText = SetTopSalesRanking(false, $EmailText);
	}elseif ($Group == "0300-EmailsToStaff"){
		$EmailText = KL_DailyEmailsToStaff($EmailText);
	}elseif ($Group == "0400-OnlineRLAdjustments"){
		$EmailText = KL_DailyRLAdjustmentsForOnline(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0500-RLForTopSalesKL"){
		$EmailText = KL_DailyRLAdjustmentsForKL(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0600-RLForTopSalesBL"){
		$EmailText = KL_DailyRLAdjustmentsForBlink(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0700-RLForTopSalesOU"){
		$EmailText = KL_DailyRLAdjustmentsForOutlet(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0800-RLRebalancing"){
		$EmailText = KL_DailyRLRebalancing(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0900-RLZeroNotAvailable"){
		$EmailText = KL_DailyRLZeroNotAvailable(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "1000-RLAdjustPackaging"){
		$EmailText = KL_DailyRLAdjustmentsForPackaging(false, true, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "1050-SmartStockTransfersKL"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $EmailText); // prepares the Smart Stock Transfers for KL
	}elseif ($Group == "1060-SmartStockTransfersBL"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $EmailText); // prepares the Smart Stock Transfers for BL 
	}elseif ($Group == "1070-SmartStockTransfersOU"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $EmailText); // prepares the Smart Stock Transfers for OU
	}elseif ($Group == "1100-OptimizeDB"){
		$EmailText = KL_DailyOptimizationDatabase(5, false, $EmailText);
	}elseif ($Group == "1200-DailySyncOpenCart"){
		$EmailText = KL_DailyCleanOpenCartDB(false , $EmailText);
		$EmailText = WeberpToOpenCartDailySync(false , $EmailText);
		$EmailText = OpenCartToWeberpSync(false , $EmailText);
	}else{
		$EmailText = $EmailText . "Group " . $Group . " not found." . "\n";
	}

	$Result = DB_query("UPDATE config SET confvalue = CURRENT_DATE WHERE confname='KLCronJobChecks_LastRun'");
	if ($EmailText ==''){
		prnMsg(__('The system has just run the daily Kapal-Laut checks.'),'info');
		KLSendEmail("UserLoggingIn", "Silent", $_SESSION['UserID'], date('d/M/Y H:i'), $_SERVER["REMOTE_ADDR"]);
	}

	return $EmailText;
	
}

/**************************************************************************************************************
* Runs all database cleaning and maintenance operations
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyCleanDB($ShowMessages, $EmailText){
	$EmailText = YesterdayServerUsage($ShowMessages, $EmailText);
	$EmailText = SetRLZeroForObsolete($ShowMessages, $EmailText);
	$EmailText = SetRLZeroForLocations($ShowMessages, $EmailText);
	$EmailText = SetEndDatePriceToObsolete($ShowMessages, $EmailText);
	$EmailText = CleanDiscountForObsoleteItems($ShowMessages, $EmailText);
	$EmailText = CleanObsoleteFromWebsite($ShowMessages, $EmailText);
	$EmailText = CleanPurchOrderDetails($ShowMessages, $EmailText);
	$EmailText = CleanWorkOrdersWithoutItems($ShowMessages, $EmailText);
	$EmailText = CleanInternalRequestsWithoutItems($ShowMessages, $EmailText);
	$EmailText = SetStatusCompleteToFinishedOldPurchaseOrders(150, $ShowMessages, $EmailText);
	$EmailText = CleanWrongPrices($ShowMessages, $EmailText);
	$EmailText = AuthorizeAllInternalStockRequest($ShowMessages, $EmailText);
	$EmailText = CleanOldDoubleReceivedGoods(15, $ShowMessages, $EmailText);
	$EmailText = BlockInactiveUsers(17,  7, $ShowMessages, $EmailText); // 17 = SPG
	$EmailText = BlockInactiveUsers(22, 30, $ShowMessages, $EmailText); // 22 = SPG-Support
	$EmailText = PurgeKLTable("kladjustrl","adjustdate", $ShowMessages, $EmailText);
	$EmailText = PurgeKLTable("klchangeprice","endprocessdate", $ShowMessages, $EmailText);
	$EmailText = PurgeKLTable("klmovetodiscount20","endprocessdate", $ShowMessages, $EmailText);
	$EmailText = PurgeKLTable("klmovetodiscount50","endprocessdate", $ShowMessages, $EmailText);
	$EmailText = PurgeKLTable("klmovetodiscount80","endprocessdate", $ShowMessages, $EmailText);
	$EmailText = PurgeAuditTrailTable($ShowMessages, $EmailText);
	$EmailText = PurgeAuditScriptsTable($ShowMessages, $EmailText);
	$EmailText = PurgeSessionsTable($ShowMessages, $EmailText);
	$EmailText = PurgePackagingUsedTable(2*365, $ShowMessages, $EmailText); //we keep 2 years of packaging used for analysis. Older usage is not relevant
	return $EmailText;
}

/**************************************************************************************************************
* Collects and reports server usage statistics for the previous day
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing usage statistics
**************************************************************************************************************/
function YesterdayServerUsage($ShowMessages, $EmailText){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -1));
	$ToDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', 0));
	
	$SQL = "SELECT SUM(`secondsrunning`) AS SecsCPU,
				COUNT(`secondsrunning`) AS ScriptsRun
			FROM `auditscripts` 
			WHERE executiondate >= '" . $FromDate . "'
				AND executiondate < '" . $ToDate . "'";
	$ErrMsg ='Could not check auditscripts table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$Text = "Yesterday CPU Usage (Seconds) = ". $MyRow['SecsCPU'];
	InsertKPI("SERVER-CPU-USE-SEC", $MyRow['SecsCPU']);
	InsertKPI("SERVER-USE-SCRIPT", $MyRow['ScriptsRun']);
	InsertKPI("SERVER-CPU-USE-SEC-SCRIPT", round(($MyRow['SecsCPU']/$MyRow['ScriptsRun']),2));

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'INSERT%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumInsert = $MyRow['QueryString'];
	InsertKPI("SERVER-TX-INS-USE-TX", $NumInsert);

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'UPDATE%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumUpdate = $MyRow['QueryString'];
	InsertKPI("SERVER-TX-UPD-USE-TX", $NumUpdate);

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'DELETE%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumDelete = $MyRow['QueryString'];
	InsertKPI("SERVER-TX-DEL-USE-TX", $NumDelete);

	InsertKPI("SERVER-TX-USE-TX",$NumInsert + $NumUpdate + $NumDelete);
	
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}



/**************************************************************************************************************
* Sets items with zero stock in specific discontinued categories to obsolete status
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailySetObsoleteNoStock($ShowMessages, $EmailText = ''){
	$EmailText = SetObsoleteForCategoryWithoutStock("NOPOKA", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("NOPOBA", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("NOPOGA", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC2A", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC2B", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC2G", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC5A", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC5B", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC5G", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC8A", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC8B", $ShowMessages, $EmailText);
	$EmailText = SetObsoleteForCategoryWithoutStock("DISC8G", $ShowMessages, $EmailText);
//	$EmailText = PurgeRelatedItemsFromObsolete($ShowMessages, $EmailText);
	return $EmailText;
}


/**************************************************************************************************************
* Sends various email notifications to staff members
*
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyEmailsToStaff($EmailText){
	$EmailText = SendEmailChangePriceReadyForStep02($EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("20", $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("50", $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("80", $EmailText);
	return $EmailText;
}

/**************************************************************************************************************
* Optimizes database tables in small batches to prevent server overload
*
* @param int $TablesPerDay - Number of tables to optimize per day
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function KL_DailyOptimizationDatabase($TablesPerDay, $ShowMessages, $EmailText = ''){
	$ErrMsg ='Could not OPTIMIZE tables because';

	$SQL = "SHOW TABLES";
 	$Result = DB_query($SQL,$ErrMsg);
	$TotalTables = DB_num_rows($Result);
 	if ($TotalTables != 0){
		$Text = 'DB optimization' . "\n";
		$Text .= '# Tables to optimize: ' . $TablesPerDay . "\n";
		$CurrentDay = date('z'); // Day of the year (0-365)
		$startIndex = ($CurrentDay * $TablesPerDay) % $TotalTables;

		// Move the result pointer to the starting index
		$Skip = 0;
		$Count = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($Skip < $startIndex){
				$Skip++;
			}else{
				$TableName = $MyRow[0];
				$OptimizeSQL = "OPTIMIZE TABLE " . $TableName . "";
				$OptimizeResult = DB_query($OptimizeSQL,$ErrMsg);
				if (!$OptimizeResult) {
					$Text .= 'ERROR Optimizing ' . $TableName . "\n";
				} else {
					$Text .= 'Optimized ' . $TableName . "\n";
				}

				$Count++;
				if ($Count >= $TablesPerDay) {
					break; // Stop after optimizing the desired number of tables
				}
			}
		}
	}else{
		$Text = 'DB optimization. DB has no tables' . "\n" . $SQL;
	}

	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Generic function to purge old records from Kapal-Laut tables
*
* @param string $TableName - Name of the table to purge
* @param string $DateField - Name of the date field to compare
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgeKLTable($TableName,$DateField, $ShowMessages, $EmailText){
	if ($_SESSION['MonthsAuditTrail'] > 0){
		 $SQL = "DELETE FROM " . $TableName . "
				WHERE  " . $DateField . " <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'
					AND " . $DateField .  " != '1000-01-01'";
		$ErrMsg ='Could not purge table ' . $TableName . ' because';
		DB_query($SQL,$ErrMsg);
		$Text = "Table " . $TableName . " purged.";
		$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	}
	return $EmailText;
}

/**************************************************************************************************************
* Removes old packaging usage records
*
* @param int $DaysToKeep - Number of days of records to keep
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgePackagingUsedTable($DaysToKeep, $ShowMessages, $EmailText){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysToKeep));
	$SQL = "DELETE FROM packagingused
			WHERE date < '" . $FromDate . "'";
	$ErrMsg ='Could not purge packagingused table because';
	DB_query($SQL,$ErrMsg);
	$Text = "Table packagingused purged.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes purchase order details with order number 0
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanPurchOrderDetails($ShowMessages, $EmailText){
	$SQL = "DELETE FROM purchorderdetails WHERE orderno='0'";
	$ErrMsg ='Could not clean purchorderdetails table because';
	DB_query($SQL,$ErrMsg);
	$Text = "Table purchorderdetails cleaned.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes work orders that have no items associated with them
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanWorkOrdersWithoutItems($ShowMessages, $EmailText){
	$SQL = "DELETE w
			FROM workorders w
			LEFT JOIN woitems wi 
				ON w.wo = wi.wo
			WHERE wi.wo IS NULL;";
	$ErrMsg ='Could not clean workorders table because';
	DB_query($SQL,$ErrMsg);
	$Text = "Table workorders without items cleaned.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes discount categories from obsolete items
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanDiscountForObsoleteItems($ShowMessages, $EmailText){
	$SQL = "UPDATE stockmaster
			SET discountcategory = ''
			WHERE discontinued = 1
				AND discountcategory != ''";
	$ErrMsg =__('Could not clean discount category for obsolete items  because');
	DB_query($SQL,$ErrMsg);
	$Text = "Discount Category cleaned for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Marks items with zero stock in specific category as obsolete
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetObsoleteForCategoryWithoutStock($Category, $ShowMessages, $EmailText){
	$SQL = "UPDATE stockmaster
			SET discontinued = 1
			WHERE categoryid = '" . $Category . "'
				AND discontinued = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) = 0";
	$ErrMsg =__('Could not update items without stock because');
	DB_query($SQL,$ErrMsg);
	$Text = "Items " . $Category . " with QOH = 0 flagged as obsolete.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Sets reorder level to zero for obsolete items
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetRLZeroForObsolete($ShowMessages, $EmailText){
	$SQL = "UPDATE locstock
			SET reorderlevel = 0
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = locstock.stockid
						AND stockmaster.discontinued = 1)";
	$ErrMsg =__('Could not set RL = 0 for obsolete items because');
	DB_query($SQL,$ErrMsg);
	$Text = "RL updated to zero for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Sets reorder level to zero for specific locations
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetRLZeroForLocations($ShowMessages, $EmailText){
	$SQL = "UPDATE locstock
			SET reorderlevel = 0
			WHERE loccode IN " . LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO;
	$ErrMsg =__('Could not set RL = 0 for location list because');
	DB_query($SQL,$ErrMsg);
	$Text = "RL updated to zero for location list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Sets end date of prices to today for obsolete items
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetEndDatePriceToObsolete($ShowMessages, $EmailText){
	$SQL = "SELECT COUNT(*) AS items
			FROM prices
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND enddate > CURRENT_DATE";
	$ErrMsg =__('Error in function SetEndDatePriceToObsolete because');
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	InsertKPI("STOCK-MOVED-OBS-MOD", $MyRow['items']);

	$SQL = "UPDATE prices
			SET enddate = CURRENT_DATE
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND enddate > CURRENT_DATE";
	$ErrMsg =__('Could not set end date to today for obsolete items because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Prices End Date updated to today for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
			
/**************************************************************************************************************
* Deletes internal stock requests that have no items
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanInternalRequestsWithoutItems($ShowMessages, $EmailText){
	$SQL = "DELETE FROM stockrequest 
			WHERE NOT EXISTS (SELECT *
								FROM stockrequestitems
								WHERE stockrequest.dispatchid = stockrequestitems.dispatchid )";
	$ErrMsg =__('Could not delete empty internal requests because');
	DB_query($SQL,$ErrMsg);
	$Text = "Empty Internal Requests removed from DB.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}			

/**************************************************************************************************************
* Removes obsolete items from the website sales categories
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanObsoleteFromWebsite($ShowMessages, $EmailText){
	$SQL = "DELETE FROM salescatprod
			WHERE EXISTS (SELECT * FROM stockmaster
							WHERE discontinued = 1
							AND stockmaster.stockid = salescatprod.stockid)";
	$ErrMsg =__('Could not delete obsolete items from sales category for website because');
	DB_query($SQL,$ErrMsg);
	$Text = "Obsolete items removed from website list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
	
/**************************************************************************************************************
* Deletes prices where start date is greater than end date
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanWrongPrices($ShowMessages, $EmailText){
	$SQL = "DELETE FROM prices
			WHERE startdate > enddate
			AND enddate != '9999-12-31'";
	$ErrMsg =__('Could not delete wrong prices because');
	DB_query($SQL,$ErrMsg);
	$Text = "Wrong prices removed from DB";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	return $EmailText;
}

/**************************************************************************************************************
* Fixes double-received goods in stock transfers
*
* @param int $NumDays - Number of days to look back for transfers
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function CleanOldDoubleReceivedGoods($NumDays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "UPDATE loctransfers
			SET recqty = shipqty
			WHERE recdate <= '" . $StartDate. "'
				AND recqty = 2 * shipqty";
	$ErrMsg =__('Could not fix double received goods in shops because');
	DB_query($SQL,$ErrMsg);
	$Text = "Clean old double received goods in transfers";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Marks old purchase orders as complete
*
* @param int $maxdays - Maximum age of purchase orders to update
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetStatusCompleteToFinishedOldPurchaseOrders($maxdays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$SQL = "UPDATE purchorders 
			SET status = 'Completed' 
			WHERE status NOT IN ('Completed', 'Cancelled', 'Rejected')
				AND orddate <= '" . $StartDate . "'
				AND NOT EXISTS (SELECT *
						FROM purchorderdetails
						WHERE purchorderdetails.orderno = purchorders.orderno
						AND completed = 0)";
	$ErrMsg =__('Could not update old finshed POs to complete because');
	DB_query($SQL,$ErrMsg);
	$Text = "Set status = COMPLETED to Finished Purchase Orders older than " . $maxdays . " days.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;

}		

/**************************************************************************************************************
* Automatically authorizes all pending internal stock requests
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function AuthorizeAllInternalStockRequest($ShowMessages, $EmailText){
	$SQL = "SELECT COUNT(*) AS total
			FROM stockrequest
			WHERE authorised !='1'";
	$ErrMsg =__('Error in function AuthorizeAllInternalStockRequest because');
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	InsertKPI("TRANSFERS-INTREQ", $MyRow['total']);

	$SQL = "UPDATE stockrequest
					SET authorised='1'
					WHERE authorised !='1'";
	$ErrMsg =__('Could not authorize all internal stock requests because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "All pending Internal Stock Requests authorised automatically";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Blocks inactive users based on their access level and days since last login
*
* @param int $access - Access level to filter users
* @param int $maxdays - Maximum days since last login to block user
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function BlockInactiveUsers($access, $maxdays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;

	$SQL = "UPDATE www_users
			SET blocked = '1'
			WHERE lastvisitdate IS NOT NULL
				AND DATE(lastvisitdate) < '" . $StartDate . "'
				AND userid NOT LIKE '999%'
				AND fullaccess = '" . $access . "'
				AND blocked = '0'
				AND userid <> 'TestUser'";
	$ErrMsg =__('Could not block inactive users because');
	DB_query($SQL,$ErrMsg);
	$Text = "Blocked inactive users with access level " . $access . " and not logging in for " . $maxdays . " days";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes special characters from a string for clean printing
*
* @param string $List - The input string to clean
*
* @return string - The cleaned string
**************************************************************************************************************/
function CleanListToPrint($List){
	$ToClean = array("'", "(", ")");
	$List = str_replace($ToClean, "", $List);
	return $List;
}

/**************************************************************************************************************
* Removes old entries from the audit trail table
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgeAuditTrailTable($ShowMessages, $EmailText){
	$SQL = "DELETE FROM audittrail
			WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
	DB_query($SQL);
	$Text = "Purge Audit Trail table in webERP";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	return $EmailText;
}

/**************************************************************************************************************
* Removes old entries from the audit scripts table
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgeAuditScriptsTable($ShowMessages, $EmailText){
	$SQL = "DELETE FROM auditscripts
			WHERE  executiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
	DB_query($SQL);
	$Text = "Purge Audit Script table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes sessions older than 2 days
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgeSessionsTable($ShowMessages, $EmailText){
	$Days = 1;
	$SomeDaysAgo = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$Days));
	$SQL = "DELETE FROM sessions
			WHERE logintime <= '" . $SomeDaysAgo . "'";
	DB_query($SQL);
	$Text = "Purge Sessions table for entries older than " . $Days . " days";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Removes related item links for obsolete items
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function PurgeRelatedItemsFromObsolete($ShowMessages, $EmailText){
	$SQL = "DELETE FROM relateditems
			WHERE relateditems.stockid IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	DB_query($SQL);
	$Text = "Purge Related Items table from obsolete items (stockid)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	$SQL = "DELETE FROM relateditems
			WHERE relateditems.related IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	DB_query($SQL);
	$Text = "Purge Related Items table from obsolete items (related)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


/**************************************************************************************************************
* Sets up and calculates sales performance rankings
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetTopSalesRanking($ShowMessages, $EmailText){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Set Top Sales Ranking Table" . "\n\n"; 
	}	
	
	// TRUNCATE operation remains the same - already optimal
	$SQL = "TRUNCATE klsalesperformance";
	$ErrMsg =__('Could not set TRUNCATE klsalesperformance because');
	DB_query($SQL,$ErrMsg);
	$Text = "Truncated klsaleseprformace table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	
	// OPTIMIZED BATCH INSERT OPERATION
	// ORIGINAL ISSUES:
	// - Multiple OR conditions with IN clauses (inefficient query execution)
	// - Row-by-row INSERT operations (high transaction overhead)
	// - No table aliases (reduced readability)
	//
	// OPTIMIZED IMPROVEMENTS:
	// - Single IN clause with combined category lists
	// - Batch INSERT...SELECT operation (eliminates loop and individual INSERTs)
	// - Table alias 'sm' for stockmaster
	// - Direct INSERT from SELECT result (no PHP loop required)
	
	// Combine all category lists into a single comprehensive list
	// This eliminates the need for multiple OR conditions
	$AllCategories = str_replace(')', '', LIST_STOCK_CATEGORIES_KAPAL_LAUT);
	$AllCategories = str_replace('(', '', $AllCategories);
	$AllCategories .= ',' . str_replace(')', '', str_replace('(', '', LIST_STOCK_CATEGORIES_BLINK));
	$AllCategories .= ',' . str_replace(')', '', str_replace('(', '', LIST_STOCK_CATEGORIES_OUTLET));
	$AllCategories .= ',' . str_replace(')', '', str_replace('(', '', LIST_STOCK_CATEGORIES_GENERAL));
	$AllCategories = '(' . $AllCategories . ')';
	
	// Single optimized INSERT...SELECT operation
	$SQL = "INSERT INTO klsalesperformance 
				(stockid, topsales30, topsales60, topsales90, valuesales30, valuesales60, valuesales90)
			SELECT sm.stockid, 9999999, 9999999, 9999999, 0, 0, 0
			FROM stockmaster sm
			WHERE sm.discontinued = 0 
				AND sm.categoryid IN " . $AllCategories;
	
	$ErrMsg =__('Could not insert items by top sales because');
	DB_query($SQL,$ErrMsg);
	
	$Text = "Initialized klsaleseprformace table with batch INSERT";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	
	// The SetTopSalesByGroup calls remain the same - they use the already optimized function
	$EmailText = SetTopSalesByGroup("KAPAL-LAUT", 90, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("KAPAL-LAUT", 60, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("KAPAL-LAUT", 30, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("BLINK", 90, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("BLINK", 60, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("BLINK", 30, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("OUTLET", 90, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("OUTLET", 60, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("OUTLET", 30, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("GENERAL", 90, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("GENERAL", 60, $ShowMessages, $EmailText);
	$EmailText = SetTopSalesByGroup("GENERAL", 30, $ShowMessages, $EmailText);
	
	return $EmailText;
}

/**************************************************************************************************************
* Calculates top sales ranking for items in a specific group
*
* @param string $Group - The group to calculate top sales for
* @param int $NumDays - Number of days to consider for top sales
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Email text to append results to
*
* @return string - Updated email text containing results of operations
**************************************************************************************************************/
function SetTopSalesByGroup($Group, $NumDays, $ShowMessages, $EmailText){

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

	if ($Group == "KAPAL-LAUT"){
		$ListCategories = LIST_STOCK_CATEGORIES_KAPAL_LAUT;
	}elseif ($Group == "BLINK"){
		$ListCategories = LIST_STOCK_CATEGORIES_BLINK;
	}elseif ($Group == "OUTLET"){
		$ListCategories = LIST_STOCK_CATEGORIES_OUTLET;
	}elseif ($Group == "GENERAL"){
		$ListCategories = LIST_STOCK_CATEGORIES_GENERAL;
	}else{
		return;
	}
	
	// OPTIMIZED MAIN QUERY
	// ORIGINAL ISSUES:
	// - Old-style comma JOIN syntax (salesorderdetails, stockmaster)
	// - Missing table aliases
	// - Repeated SUM calculation in SELECT and ORDER BY
	// - Suboptimal filter order
	//
	// OPTIMIZED IMPROVEMENTS:
	// - Modern explicit INNER JOIN syntax
	// - Table aliases (sod, sm) for all tables
	// - ORDER BY references alias 'valuesales' instead of repeating SUM
	// - Date filter applied first for better selectivity
	// - Cleaner, more maintainable SQL structure
	$SQL="SELECT sod.stkcode,
			SUM(sod.qtyinvoiced * sod.unitprice) AS valuesales
		FROM salesorderdetails sod
		INNER JOIN stockmaster sm ON sod.stkcode = sm.stockid
		WHERE sod.actualdispatchdate >= '" . $StartDate . "'
			AND sm.categoryid IN " . $ListCategories . "
		GROUP BY sod.stkcode
		ORDER BY valuesales DESC";
	
	$ErrMsg =__('Could not sort items by top sales because');
	$Result = DB_query($SQL,$ErrMsg);

	$Position = 1;
	$ErrMsg =__('Could not update items by top sales because');
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$SQLOp="UPDATE klsalesperformance
					SET topsales". $NumDays." = '" . $Position . "',
						valuesales". $NumDays." = '" . $MyRow['valuesales'] . "'
					WHERE stockid = '" . $MyRow['stkcode'] . "'";
				
			DB_query($SQLOp,$ErrMsg);
			$Position++;
		}
	}

	$Text = "Top Sales Ranking for " . $Group . " items for last " . $NumDays . " days";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

/**************************************************************************************************************
* Helper function to display or append to email text based on settings
*
* @param bool $ShowMessages - Whether to show messages in the UI
* @param string $EmailText - Existing email text to append to
* @param string $Text - New text to add to email or display
*
* @return string - Updated email text
**************************************************************************************************************/
function ShowOrEmail($ShowMessages, $EmailText, $Text){
	if ($ShowMessages) prnMsg($Text,"info");
	if ($EmailText !=''){
		$EmailText = $EmailText . $Text . "\n"; 
	}	
	return $EmailText;
}
