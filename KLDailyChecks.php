<?php


function KL_DailyChecks($Group, $RootPath, $EmailText= ''){
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
	

	if ($Group == "0100-CleanDB"){
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
	}elseif ($Group == "1100-OptimizeDB"){
		$EmailText = KL_DailyOptimizationDatabase(5, FALSE, $EmailText);
	}elseif ($Group == "1200-SyncWebERPOpenCart"){
		$EmailText = KL_DailyCleanOpenCartDB(FALSE , $EmailText);
		$EmailText = WeberpToOpenCartDailySync(FALSE , $EmailText);
		$EmailText = OpenCartToWeberpSync(FALSE , $EmailText);
	}else{
		$EmailText = $EmailText . "Group " . $Group . " not found." . "\n";
	}

	$Result = DB_query("UPDATE config SET confvalue='" . Date('Y-m-d') . "'	WHERE confname='KL_DailyChecks_LastRun'");
	if ($EmailText ==''){
		prnMsg(_('The system has just run the daily Kapal-Laut checks.'),'info');
		KLSendEmail("UserLoggingIn", "Silent", $_SESSION['UserID'], date('d/M/Y H:i'), $_SERVER["REMOTE_ADDR"]);
	}

	return $EmailText;
	
}

function KL_HourlyChecks($RootPath, $EmailText=''){
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
	
	$EmailText = WeberpToOpenCartHourlySync(FALSE , TRUE, $EmailText);
	$EmailText = OpenCartToWeberpSync(FALSE , $EmailText);
	
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
	
	$sql = "SELECT SUM(`secondsrunning`) AS SecsCPU,
				COUNT(`secondsrunning`) AS ScriptsRun
			FROM `auditscripts` 
			WHERE executiondate >= '" . $FromDate . "'
				AND executiondate < '" . $ToDate . "'";
	$ErrMsg ='Could not check auditscripts table because';
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	$Text = "CPU Usage (Seconds) = ". $myrow['SecsCPU'];
	InsertKPI("ServerUsage", "CPU Usage (Seconds)", $myrow['SecsCPU']);
	InsertKPI("ServerUsage", "Scripts Run (Scripts)", $myrow['ScriptsRun']);
	InsertKPI("ServerUsage", "CPU Usage (Seconds/Script)", round(($myrow['SecsCPU']/$myrow['ScriptsRun']),2));

	$sql = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'INSERT%'";
	$ErrMsg ='Could not check audittrail table because';
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	$NumInsert = $myrow['QueryString'];
	InsertKPI("ServerUsage", "DB Usage Tx INSERT (Tx)", $NumInsert);

	$sql = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'UPDATE%'";
	$ErrMsg ='Could not check audittrail table because';
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	$NumUpdate = $myrow['QueryString'];
	InsertKPI("ServerUsage", "DB Usage Tx UPDATE (Tx)", $NumUpdate);

	$sql = "SELECT COUNT(`querystring`) AS QueryString
			FROM `audittrail` 
			WHERE transactiondate >= '" . $FromDate . "'
				AND transactiondate < '" . $ToDate . "'
				AND querystring LIKE 'DELETE%'";
	$ErrMsg ='Could not check audittrail table because';
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	$NumDelete = $myrow['QueryString'];
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

function KL_DailyOptimizationDatabase($tablesPerDay, $ShowMessages, $EmailText = ''){
//	$NumberDay = substr(Date('Y-m-d'),-2); // Get the date number
	$ErrMsg ='Could not OPTIMIZE tables because';

	$sql = "SHOW TABLES";
 	$result = DB_query($sql,$ErrMsg);
	$totalTables = DB_num_rows($result);
 	if ($totalTables != 0){
		$Text = 'DB optimization' . "\n";
		$Text .= '# Tables to optimize: ' . $tablesPerDay . "\n";
		$currentDay = date('z'); // Day of the year (0-365)
		$startIndex = ($currentDay * $tablesPerDay) % $totalTables;

		// Move the result pointer to the starting index
		$skip = 0;
		$count = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($skip < $startIndex){
				$skip++;
			}else{
				$tableName = $myrow[0];
				$optimizeSql = "OPTIMIZE TABLE " . $tableName . "";
				$optimizeResult = DB_query($optimizeSql,$ErrMsg);
				if (!$optimizeResult) {
					$Text .= 'ERROR Optimizing ' . $tableName . "\n";
				} else {
					$Text .= 'Optimized ' . $tableName . "\n";
				}

				$count++;
				if ($count >= $tablesPerDay) {
					break; // Stop after optimizing the desired number of tables
				}
			}
		}
	}else{
		$Text = 'DB optimization. DB has no tables' . "\n" . $sql;
	}

	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function PurgeKLTable($TableName,$DateField, $ShowMessages, $EmailText){
	if ($_SESSION['MonthsAuditTrail'] > 0){
		 $sql = "DELETE FROM " . $TableName . "
				WHERE  " . $DateField . " <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'
					AND " . $DateField .  " != '0000-00-00'";
		$ErrMsg ='Could not purge table ' . $TableName . ' because';
		$result = DB_query($sql,$ErrMsg);
		$Text = "Table " . $TableName . " purged.";
		$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	}
	return $EmailText;
}

function PurgePackagingUsedTable($DaysToKeep, $ShowMessages, $EmailText){
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$DaysToKeep));
	$sql = "DELETE FROM packagingused
			WHERE date < '" . $FromDate . "'";
	$ErrMsg ='Could not purge packagingused table because';
	$result = DB_query($sql,$ErrMsg);
	$Text = "Table packagingused purged.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function CleanPurchOrderDetails($ShowMessages, $EmailText){
	$sql = "DELETE FROM purchorderdetails WHERE orderno='0'";
	$ErrMsg ='Could not clean purchorderdetails table because';
	$result = DB_query($sql,$ErrMsg);
	$Text = "Table purchorderdetails cleaned.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function CleanDiscountForObsoleteItems($ShowMessages, $EmailText){
	$sql = "UPDATE stockmaster
			SET discountcategory = ''
			WHERE discontinued = 1
				AND discountcategory != ''";
	$ErrMsg =_('Could not clean discount category for obsolete items  because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Discount Category cleaned for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetObsoleteForCategoryWithoutStock($category, $ShowMessages, $EmailText){
	$sql = "UPDATE stockmaster
			SET discontinued = 1
			WHERE categoryid = '" . $category . "'
				AND discontinued = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) = 0";
	$ErrMsg =_('Could not update items without stock because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Items " . $category . " with QOH = 0 flagged as obsolete.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetRLZeroForObsolete($ShowMessages, $EmailText){
	$sql = "UPDATE locstock
			SET reorderlevel = 0
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = locstock.stockid
						AND stockmaster.discontinued = 1)";
	$ErrMsg =_('Could not set RL = 0 for obsolete items because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "RL updated to zero for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetRLZeroForLocations($ShowMessages, $EmailText){
	$sql = "UPDATE locstock
			SET reorderlevel = 0
			WHERE loccode IN " . LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO;
	$ErrMsg =_('Could not set RL = 0 for location list because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "RL updated to zero for location list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function SetEndDatePriceToObsolete($ShowMessages, $EmailText){
	$sql = "SELECT COUNT(*) AS items
			FROM prices
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND (enddate > '"  . date('Y-m-d') ."'
				  OR enddate = '9999-12-31')";
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	InsertKPI("Stock", "Models moved to obsolete (MODELS)", $myrow['items']);

	$sql = "UPDATE prices
			SET enddate = '" . date('Y-m-d') ."'
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND (enddate > '"  . date('Y-m-d') ."'
				  OR enddate = '9999-12-31')";
	$ErrMsg =_('Could not set end date to today for obsolete items because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Prices End Date updated to today for obsolete items.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
			
function CleanInternalRequestsWithoutItems($ShowMessages, $EmailText){
	$sql = "DELETE FROM stockrequest 
			WHERE NOT EXISTS (SELECT *
								FROM stockrequestitems
								WHERE stockrequest.dispatchid = stockrequestitems.dispatchid )";
	$ErrMsg =_('Could not delete empty internal requests because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Empty Internal Requests removed from DB.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}			

function CleanObsoleteFromWebsite($ShowMessages, $EmailText){
	$sql = "DELETE FROM salescatprod
			WHERE EXISTS (SELECT * FROM stockmaster
							WHERE discontinued = 1
							AND stockmaster.stockid = salescatprod.stockid)";
	$ErrMsg =_('Could not delete obsolete items from sales category for website because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Obsolete items removed from website list.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}
	
function CleanWrongPrices($ShowMessages, $EmailText){
	$sql = "DELETE FROM prices
			WHERE startdate > enddate
			AND enddate != '9999-12-31'";
	$ErrMsg =_('Could not delete wrong prices because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Wrong prices removed from DB";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	return $EmailText;
}

function CleanOldDoubleReceivedGoods($NumDays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$sql = "UPDATE loctransfers
			SET recqty = shipqty
			WHERE recdate <= '" . $StartDate. "'
				AND recqty = 2 * shipqty";
	$ErrMsg =_('Could not fix double received goods in shops because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Clean old double received goods in transfers";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

	
function SetStatusCompleteToFinishedOldPurchaseOrders($maxdays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays));
	$sql = "UPDATE purchorders 
			SET status = 'Completed' 
			WHERE status NOT IN ('Completed', 'Cancelled', 'Rejected')
				AND orddate <= '" . $StartDate . "'
				AND NOT EXISTS (SELECT *
						FROM purchorderdetails
						WHERE purchorderdetails.orderno = purchorders.orderno
						AND completed = 0)";
	$ErrMsg =_('Could not update old finshed POs to complete because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Set status = COMPLETED to Finished Purchase Orders older than " . $maxdays . " days.";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;

}		

function AuthorizeAllInternalStockRequest($ShowMessages, $EmailText){
	$sql = "SELECT COUNT(*) AS total
			FROM stockrequest
			WHERE authorised !='1'";
	$result = DB_query($sql,$ErrMsg);
	$myrow = DB_fetch_array($result);
	InsertKPI("Shops", "Internal Requests", $myrow['total']);

	$sql = "UPDATE stockrequest
					SET authorised='1'
					WHERE authorised !='1'";
	$ErrMsg =_('Could not authorize all internal stock requests because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "All pending Internal Stock Requests authorised automatically";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


function BlockInactiveUsers($access, $maxdays, $ShowMessages, $EmailText){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$maxdays-1)) ;

	$sql = "UPDATE www_users
			SET blocked = '1'
			WHERE lastvisitdate IS NOT NULL
				AND DATE(lastvisitdate) < '" . $StartDate . "'
				AND userid NOT LIKE '999%'
				AND fullaccess = '" . $access . "'
				AND blocked = '0'
				AND userid <> 'TestUser'";
	$ErrMsg =_('Could not block inactive users because');
	$result = DB_query($sql,$ErrMsg);
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
	 $sql = "DELETE FROM audittrail
			WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
	$result = DB_query($sql);
	$Text = "Purge Audit Trail table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function PurgeAuditScriptsTable($ShowMessages, $EmailText){
	 $sql = "DELETE FROM auditscripts
			WHERE  executiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
	$result = DB_query($sql);
	$Text = "Purge Audit Script table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}

function PurgeRelatedItemsFromObsolete($ShowMessages, $EmailText){
	$sql = "DELETE FROM relateditems
			WHERE relateditems.stockid IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	$result = DB_query($sql);
	$Text = "Purge Related Items table from obsolete items (stockid)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);

	$sql = "DELETE FROM relateditems
			WHERE relateditems.related IN (SELECT stockmaster.stockid
											FROM stockmaster
											WHERE discontinued = 1)";
	$result = DB_query($sql);
	$Text = "Purge Related Items table from obsolete items (related)";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	return $EmailText;
}


function SetTopSalesRanking($ShowMessages, $EmailText){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Set Top Sales Ranking Table" . "\n\n"; 
	}	
	$sql = "TRUNCATE klsalesperformance";
	$ErrMsg =_('Could not set TRUNCATE klsalesperformance because');
	$result = DB_query($sql,$ErrMsg);
	$Text = "Truncated klsaleseprformace table";
	$EmailText = ShowOrEmail($ShowMessages, $EmailText, $Text);
	
	$SQL="SELECT stockmaster.stockid
			FROM stockmaster
			WHERE stockmaster.discontinued = 0 
				AND (stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_KAPAL_LAUT . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . " 
				OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_GENERAL . ") ";
	$result = DB_query($SQL);
	$ErrMsg =_('Could not insert items by top sales because');
	if (DB_num_rows($result) != 0){	
		while ($myrow = DB_fetch_array($result)) {
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
									'" . $myrow['stockid'] . "',
									'9999999',
									'9999999',
									'9999999',
									'0',
									'0',
									'0'
									)";
			$resultOp = DB_query($SQLOp,$ErrMsg);
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
	$result = DB_query($SQL,$ErrMsg);

	$position = 1;
	$ErrMsg =_('Could not update items by top sales because');
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			$SQLOp="UPDATE klsalesperformance
					SET topsales". $NumDays." = '" . $position . "',
						valuesales". $NumDays." = '" . $myrow['valuesales'] . "'
					WHERE stockid = '" . $myrow['stkcode'] . "'";
				
			$resultOp = DB_query($SQLOp,$ErrMsg);
			
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