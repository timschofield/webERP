<?php

/* Functions related to the cron job tasks for Kapal-Laut */

function KLCronJobChecks($Group, $RootPath, $EmailText= ''){
	include('includes/KLDefines.php');
	include('includes/KLPrices.php');
	include('includes/KLBoards.php');
	include('includes/KLReorderLevel.php');
	include('includes/KLEmails.php');
	include('includes/KLGeneralFunctions.php');
	include('includes/KLMarketplaceFunctions.php');
	include('includes/GetPrice.inc');
	include('includes/SQL_CommonFunctions.inc');
	include ('includes/OpenCartGeneralFunctions.php');
	include ('includes/WeberpToOpenCartSync.php');
	include ('includes/OpenCartToWeberpSync.php');
	include ('includes/OpenCartConnectDB.php');
	include ('includes/KLSmartStockTransfers.php');
	include ('includes/htmlMimeMail.php');

	if ($Group == "0010-SyncOpenCart"){
		$EmailText = WeberpToOpenCartHourlySync(FALSE , TRUE, $EmailText);
		$EmailText = OpenCartToWeberpSync(FALSE , $EmailText);
	}elseif ($Group == "0100-CleanDB"){
		$EmailText = KL_DailyCleanDB(FALSE, $EmailText);
	}elseif ($Group == "0200-Obsolete"){
		$EmailText = KL_DailySetObsoleteNoStock(FALSE, $EmailText);
	}elseif ($Group == "0250-TopSales"){
		$EmailText = SetTopSalesRanking(FALSE, $EmailText);
	}elseif ($Group == "0300-EmailsToStaff"){
		$EmailText = KL_DailyEmailsToStaff($EmailText);
	}elseif ($Group == "0400-OnlineRLAdjustments"){
		$EmailText = KL_DailyRLAdjustmentsForOnline(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0500-RLForTopSalesKL"){
		$EmailText = KL_DailyRLAdjustmentsForKL(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0600-RLForTopSalesBL"){
		$EmailText = KL_DailyRLAdjustmentsForBlink(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0700-RLForTopSalesOU"){
		$EmailText = KL_DailyRLAdjustmentsForOutlet(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0800-RLRebalancing"){
		$EmailText = KL_DailyRLRebalancing(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "0900-RLZeroNotAvailable"){
		$EmailText = KL_DailyRLZeroNotAvailable(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "1000-RLAdjustPackaging"){
		$EmailText = KL_DailyRLAdjustmentsForPackaging(FALSE, TRUE, $RootPath, $EmailText); // Updates RL 
	}elseif ($Group == "1050-SmartStockTransfersKL"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $RootPath, $EmailText); // prepares the Smart Stock Transfers for KL
	}elseif ($Group == "1060-SmartStockTransfersBL"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $RootPath, $EmailText); // prepares the Smart Stock Transfers for BL 
	}elseif ($Group == "1070-SmartStockTransfersOU"){
		$EmailText = KLPrepareGroupSmartStockTransfers($Group, $RootPath, $EmailText); // prepares the Smart Stock Transfers for OU
	}elseif ($Group == "1100-OptimizeDB"){
		$EmailText = KL_DailyOptimizationDatabase(5, FALSE, $EmailText);
	}elseif ($Group == "1200-SyncWebERPOpenCart"){
		$EmailText = KL_DailyCleanOpenCartDB(FALSE , $EmailText);
		$EmailText = WeberpToOpenCartDailySync(FALSE , $EmailText);
		$EmailText = OpenCartToWeberpSync(FALSE , $EmailText);
	}else{
		$EmailText = $EmailText . "Group " . $Group . " not found." . "\n";
	}

	$Result = DB_query("UPDATE config SET confvalue = CURRENT_DATE WHERE confname='KLCronJobChecks_LastRun'");
	if ($EmailText ==''){
		prnMsg(_('The system has just run the daily Kapal-Laut checks.'),'info');
		KLSendEmail("UserLoggingIn", "Silent", $_SESSION['UserID'], date('d/M/Y H:i'), $_SERVER["REMOTE_ADDR"]);
	}

	return $EmailText;
	
}

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
	$EmailText = PurgePackagingUsedTable(2*365, $ShowMessages, $EmailText); //we keep 2 years of packaging used for analysis. Older usage is not relevant
	return $EmailText;
}

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
	InsertKPI("ServerUsage", "CPU Usage (Seconds)", $MyRow['SecsCPU']);
	InsertKPI("ServerUsage", "Scripts Run (Scripts)", $MyRow['ScriptsRun']);
	InsertKPI("ServerUsage", "CPU Usage (Seconds/Script)", round(($MyRow['SecsCPU']/$MyRow['ScriptsRun']),2));

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'INSERT%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumInsert = $MyRow['QueryString'];
	InsertKPI("ServerUsage", "DB Usage Tx INSERT (Tx)", $NumInsert);

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'UPDATE%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumUpdate = $MyRow['QueryString'];
	InsertKPI("ServerUsage", "DB Usage Tx UPDATE (Tx)", $NumUpdate);

	$SQL = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'DELETE%'";
	$ErrMsg ='Could not check audittrail table because';
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$NumDelete = $MyRow['QueryString'];
	InsertKPI("ServerUsage", "DB Usage Tx DELETE (Tx)", $NumDelete);

	InsertKPI("ServerUsage", "DB Usage Tx (Tx)",$NumInsert + $NumUpdate + $NumDelete);
	
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}



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


function KL_DailyEmailsToStaff($EmailText){
	$EmailText = SendEmailChangePriceReadyForStep02($EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("20", $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("50", $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("80", $EmailText);
	return $EmailText;
}

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
		$skip = 0;
		$count = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($skip < $startIndex){
				$skip++;
			}else{
				$TableName = $MyRow[0];
				$optimizeSql = "OPTIMIZE TABLE " . $TableName . "";
				$optimizeResult = DB_query($optimizeSql,$ErrMsg);
				if (!$optimizeResult) {
					$Text .= 'ERROR Optimizing ' . $TableName . "\n";
				} else {
					$Text .= 'Optimized ' . $TableName . "\n";
				}

				$count++;
				if ($count >= $TablesPerDay) {
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

function PurgeKLTable($TableName,$DateField, $ShowMessages, $EmailText){
	if ($_SESSION['MonthsAuditTrail'] > 0){
		 $SQL = "DELETE FROM " . $TableName . "
				WHERE  " . $DateField . " <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'
					AND " . $DateField .  " != '0000-00-00'";
		$ErrMsg ='Could not purge table ' . $TableName . ' because';
		$Result = DB_query($SQL,$ErrMsg);
		$Text = "Table " . $TableName . " purged.";
		$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	}
	return $EmailText;
}

function PurgePackagingUsedTable($DaysToKeep, $ShowMessages, $EmailText){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysToKeep));
	$SQL = "DELETE FROM packagingused
			WHERE date < '" . $FromDate . "'";
	$ErrMsg ='Could not purge packagingused table because';
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Table packagingused purged.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function CleanPurchOrderDetails($ShowMessages, $EmailText){
	$SQL = "DELETE FROM purchorderdetails WHERE orderno='0'";
	$ErrMsg ='Could not clean purchorderdetails table because';
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Table purchorderdetails cleaned.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function CleanWorkOrdersWithoutItems($ShowMessages, $EmailText){
	$SQL = "DELETE w
			FROM workorders w
			LEFT JOIN woitems wi 
				ON w.wo = wi.wo
			WHERE wi.wo IS NULL;";
	$ErrMsg ='Could not clean workorders table because';
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Table workorders without items cleaned.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


function CleanDiscountForObsoleteItems($ShowMessages, $EmailText){
	$SQL = "UPDATE stockmaster
			SET discountcategory = ''
			WHERE discontinued = 1
				AND discountcategory != ''";
	$ErrMsg =_('Could not clean discount category for obsolete items  because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Discount Category cleaned for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetObsoleteForCategoryWithoutStock($Category, $ShowMessages, $EmailText){
	$SQL = "UPDATE stockmaster
			SET discontinued = 1
			WHERE categoryid = '" . $Category . "'
				AND discontinued = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) = 0";
	$ErrMsg =_('Could not update items without stock because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Items " . $Category . " with QOH = 0 flagged as obsolete.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetRLZeroForObsolete($ShowMessages, $EmailText){
	$SQL = "UPDATE locstock
			SET reorderlevel = 0
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = locstock.stockid
						AND stockmaster.discontinued = 1)";
	$ErrMsg =_('Could not set RL = 0 for obsolete items because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "RL updated to zero for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetRLZeroForLocations($ShowMessages, $EmailText){
	$SQL = "UPDATE locstock
			SET reorderlevel = 0
			WHERE loccode IN " . LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO;
	$ErrMsg =_('Could not set RL = 0 for location list because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "RL updated to zero for location list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetEndDatePriceToObsolete($ShowMessages, $EmailText){
	$SQL = "SELECT COUNT(*) AS items
			FROM prices
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND enddate > CURRENT_DATE";
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	InsertKPI("Stock", "Models moved to obsolete (MODELS)", $MyRow['items']);

	$SQL = "UPDATE prices
			SET enddate = CURRENT_DATE
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND enddate > CURRENT_DATE";
	$ErrMsg =_('Could not set end date to today for obsolete items because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Prices End Date updated to today for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
			
function CleanInternalRequestsWithoutItems($ShowMessages, $EmailText){
	$SQL = "DELETE FROM stockrequest 
			WHERE NOT EXISTS (SELECT *
								FROM stockrequestitems
								WHERE stockrequest.dispatchid = stockrequestitems.dispatchid )";
	$ErrMsg =_('Could not delete empty internal requests because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Empty Internal Requests removed from DB.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}			

function CleanObsoleteFromWebsite($ShowMessages, $EmailText){
	$SQL = "DELETE FROM salescatprod
			WHERE EXISTS (SELECT * FROM stockmaster
							WHERE discontinued = 1
							AND stockmaster.stockid = salescatprod.stockid)";
	$ErrMsg =_('Could not delete obsolete items from sales category for website because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Obsolete items removed from website list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
	
function CleanWrongPrices($ShowMessages, $EmailText){
	$SQL = "DELETE FROM prices
			WHERE startdate > enddate
			AND enddate != '9999-12-31'";
	$ErrMsg =_('Could not delete wrong prices because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Wrong prices removed from DB";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	return $EmailText;
}

function CleanOldDoubleReceivedGoods($NumDays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$SQL = "UPDATE loctransfers
			SET recqty = shipqty
			WHERE recdate <= '" . $StartDate. "'
				AND recqty = 2 * shipqty";
	$ErrMsg =_('Could not fix double received goods in shops because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Clean old double received goods in transfers";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

	
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
	$ErrMsg =_('Could not update old finshed POs to complete because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Set status = COMPLETED to Finished Purchase Orders older than " . $maxdays . " days.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;

}		

function AuthorizeAllInternalStockRequest($ShowMessages, $EmailText){
	$SQL = "SELECT COUNT(*) AS total
			FROM stockrequest
			WHERE authorised !='1'";
	$Result = DB_query($SQL,$ErrMsg);
	$MyRow = DB_fetch_array($Result);
	InsertKPI("Shops", "Internal Requests", $MyRow['total']);

	$SQL = "UPDATE stockrequest
					SET authorised='1'
					WHERE authorised !='1'";
	$ErrMsg =_('Could not authorize all internal stock requests because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "All pending Internal Stock Requests authorised automatically";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


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
	$ErrMsg =_('Could not block inactive users because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Blocked inactive users with access level " . $access . " and not logging in for " . $maxdays . " days";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function CleanListToPrint($List){
	$ToClean = array("'", "(", ")");
	$List = str_replace($ToClean, "", $List);
	return $List;
}

function PurgeAuditTrailTable($ShowMessages, $EmailText){
	 $SQL = "DELETE FROM audittrail
			WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
	$Result = DB_query($SQL);
	$Text = "Purge Audit Trail table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function PurgeAuditScriptsTable($ShowMessages, $EmailText){
	 $SQL = "DELETE FROM auditscripts
			WHERE  executiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
	$Result = DB_query($SQL);
	$Text = "Purge Audit Script table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function PurgeRelatedItemsFromObsolete($ShowMessages, $EmailText){
	$SQL = "DELETE FROM relateditems
			WHERE relateditems.stockid IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	$Result = DB_query($SQL);
	$Text = "Purge Related Items table from obsolete items (stockid)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	$SQL = "DELETE FROM relateditems
			WHERE relateditems.related IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	$Result = DB_query($SQL);
	$Text = "Purge Related Items table from obsolete items (related)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


function SetTopSalesRanking($ShowMessages, $EmailText){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Set Top Sales Ranking Table" . "\n\n"; 
	}	
	$SQL = "TRUNCATE klsalesperformance";
	$ErrMsg =_('Could not set TRUNCATE klsalesperformance because');
	$Result = DB_query($SQL,$ErrMsg);
	$Text = "Truncated klsaleseprformace table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	
	$SQL="SELECT stockmaster.stockid
			FROM stockmaster
			WHERE stockmaster.discontinued = 0 
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_GENERAL . ") ";
	$Result = DB_query($SQL);
	$ErrMsg =_('Could not insert items by top sales because');
	if (DB_num_rows($Result) != 0){	
		while ($MyRow = DB_fetch_array($Result)) {
			$SQLOp="INSERT INTO klsalesperformance
									(
									stockid,
									topsales30,
									topsales60,
									topsales90,
									valuesales30,
									valuesales60,
									valuesales90
									)
								VALUES (
									'" . $MyRow['stockid'] . "',
									'9999999',
									'9999999',
									'9999999',
									'0',
									'0',
									'0'
									)";
			$ResultOp = DB_query($SQLOp,$ErrMsg);
		}
	}
	$Text = "Initialized klsaleseprformace table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	

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
	
	$SQL="SELECT salesorderdetails.stkcode,
				SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice) AS valuesales
			FROM salesorderdetails, stockmaster
			WHERE salesorderdetails.stkcode = stockmaster.stockid
				AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
				AND stockmaster.categoryid IN " . $ListCategories . "
			GROUP BY salesorderdetails.stkcode
			ORDER BY SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice) DESC";
	$ErrMsg =_('Could not sort items by top sales because');
	$Result = DB_query($SQL,$ErrMsg);

	$position = 1;
	$ErrMsg =_('Could not update items by top sales because');
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			$SQLOp="UPDATE klsalesperformance
					SET topsales". $NumDays." = '" . $position . "',
						valuesales". $NumDays." = '" . $MyRow['valuesales'] . "'
					WHERE stockid = '" . $MyRow['stkcode'] . "'";
				
			$ResultOp = DB_query($SQLOp,$ErrMsg);
			
			$position++;
		}
	}

	$Text = "Top Sales Ranking for " . $Group . " items for last " . $NumDays . " days";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function ShowOrEmail($ShowMessages, $EmailText, $Text){
	if ($ShowMessages) prnMsg($Text,"info");
	if ($EmailText !=''){
		$EmailText = $EmailText . $Text . "\n"; 
	}	
	return $EmailText;
}

?>