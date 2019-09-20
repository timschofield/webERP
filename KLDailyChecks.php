<?php


function KL_DailyChecks($Group, $RootPath, $db, $EmailText= ''){
	include('includes/KLDefines.php');
	include('includes/KLPrices.php');
	include('includes/KLBoards.php');
	include('includes/KLReorderLevel.php');
	include('includes/KLEmails.php');
	include('includes/KLGeneralFunctions.php');
	include('includes/GetPrice.inc');
	
	include ('includes/WeberpOpenCartDefines.php');
	include ('includes/OpenCartGeneralFunctions.php');
	include ('includes/WeberpToOpenCartSync.php');
	include ('includes/OpenCartToWeberpSync.php');
	include ('includes/OpenCartConnectDB.php');
	

	if ($Group == "01"){
		$EmailText = KL_DailyMaintenanceDatabase01(FALSE, $db, $EmailText);
	}elseif ($Group == "02"){
		$EmailText = DailyReorderLevelAdjustments(FALSE, TRUE, $RootPath, $db, $EmailText); // Updates RL 
	}elseif ($Group == "03"){
		$EmailText = KL_DailyEmailsToStaff($db, $EmailText);
	}elseif ($Group == "04"){
		$EmailText = WeberpToOpenCartDailySync(FALSE, $db, $db_oc, $oc_tableprefix, $EmailText);
		$EmailText = OpenCartToWeberpSync(FALSE, $db, $db_oc, $oc_tableprefix, $EmailText);
	}elseif ($Group == "05"){
		$EmailText = KL_DailyMaintenanceDatabase05(FALSE, $db, $EmailText);
	}elseif ($Group == "06"){
		$EmailText = KL_DailyMaintenanceDatabase06(FALSE, $db, $EmailText);
	}

	$Result = DB_query("UPDATE config SET confvalue='" . Date('Y-m-d') . "'	WHERE confname='KL_DailyChecks_LastRun'");
	if ($EmailText ==''){
		prnMsg(_('The system has just run the daily Kapal-Laut checks.'),'info');
		KLSendEmail("UserLoggingIn", "Silent", $_SESSION['UserID'], date('d/M/Y H:i'), $_SERVER["REMOTE_ADDR"]);
	}

	return $EmailText;
	
}

function KL_HourlyChecks($RootPath, $db, $EmailText=''){
	include('includes/KLDefines.php');
	include('includes/KLPrices.php');
	include('includes/KLBoards.php');
	include('includes/KLReorderLevel.php');
	include('includes/KLEmails.php');
	include('includes/KLGeneralFunctions.php');
	include('includes/GetPrice.inc');
	
	include ('includes/WeberpOpenCartDefines.php');
	include ('includes/OpenCartGeneralFunctions.php');
	include ('includes/WeberpToOpenCartSync.php');
	include ('includes/OpenCartToWeberpSync.php');
	include ('includes/OpenCartConnectDB.php');
	
	$EmailText = WeberpToOpenCartHourlySync(FALSE, $db, $db_oc, $oc_tableprefix,TRUE, $EmailText);
	$EmailText = OpenCartToWeberpSync(FALSE, $db, $db_oc, $oc_tableprefix, $EmailText);
	
	return $EmailText;
}


function KL_DailyMaintenanceDatabase06($ShowMessages, $db, $EmailText = ''){
	SetRLZeroForObsolete($ShowMessages, $db);
	SetRLZeroForLocations($ShowMessages, $db);
	SetEndDatePriceToObsolete($ShowMessages, $db);
	CleanDiscountForObsoleteItems($ShowMessages, $db);
	CleanObsoleteFromWebsite($ShowMessages, $db);
	CleanInternalRequestsWithoutItems($ShowMessages, $db);
	SetStatusCompleteToFinishedOldPurchaseOrders(150, $ShowMessages, $db);
	CleanWrongPrices($ShowMessages, $db);
	AuthorizeAllInternalStockRequest($ShowMessages, $db);
//	PurgeOldPrices($ShowMessages, $db);
	CleanOldDoubleReceivedGoods(15, $ShowMessages, $db);
	BlockInactiveUsers(17,  7, $ShowMessages, $db); // 17 = SPG
	BlockInactiveUsers(22, 30, $ShowMessages, $db); // 22 = SPG-Support
	PurgeKLTable("kladjustrl","adjustdate", $ShowMessages, $db);
	PurgeKLTable("klchangeprice","endprocessdate", $ShowMessages, $db);
	PurgeKLTable("klmovetodiscount20","endprocessdate", $ShowMessages, $db);
	PurgeKLTable("klmovetodiscount50","endprocessdate", $ShowMessages, $db);
	PurgeKLTable("klmovetodiscount80","endprocessdate", $ShowMessages, $db);
	PurgeAuditTrailTable($ShowMessages, $db);
	return $EmailText;
}

function KL_DailyMaintenanceDatabase05($ShowMessages, $db, $EmailText = ''){
	$EmailText = KL_DailyOptimizationDatabase($ShowMessages, $db, $EmailText);
	return $EmailText;
}

function KL_DailyMaintenanceDatabase01($ShowMessages, $db, $EmailText = ''){
	SetObsoleteForCategoryWithoutStock("DISC20", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC50", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC80", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC2A", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC5A", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC8A", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOKA", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOBA", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOGA", $ShowMessages, $db);
	SetTopSalesRanking($ShowMessages, $db);
	return $EmailText;
}


function KL_DailyEmailsToStaff($db, $EmailText){
	$EmailText = SendEmailChangePriceReadyForStep02($db, $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("20", $db, $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("50", $db, $EmailText);
	$EmailText = SendEmailMoveToDiscountReadyForStep02("80", $db, $EmailText);
	return $EmailText;
}

function KL_DailyOptimizationDatabase($ShowMessages, $db, $EmailText = ''){
	$NumberDay = substr(Date('Y-m-d'),-2); // Get the date, 
	
	$ErrMsg ='Could not OPTIMIZE tables because';
	$result = DB_query($sql,$ErrMsg);
	if (($NumberDay == 1) OR ($NumberDay == 21)){
		$sql = "OPTIMIZE TABLE  `gltrans`";
	}elseif (($NumberDay == 2) OR ($NumberDay == 22)){
		$sql = "OPTIMIZE TABLE  `audittrail`";
	}elseif (($NumberDay == 3) OR ($NumberDay == 23)){
		$sql = "OPTIMIZE TABLE  `stockmoves`";
	}elseif (($NumberDay == 4) OR ($NumberDay == 24)){
		$sql = "OPTIMIZE TABLE  `stockmoves`";
	}elseif (($NumberDay == 5) OR ($NumberDay == 25)){
		$sql = "OPTIMIZE TABLE  `salesorderdetails`";
	}elseif (($NumberDay == 6) OR ($NumberDay == 26)){
		$sql = "OPTIMIZE TABLE  `debtortrans`";
	}elseif (($NumberDay == 7) OR ($NumberDay == 27)){
		$sql = "OPTIMIZE TABLE  `packagingused`";
	}elseif (($NumberDay == 8) OR ($NumberDay == 28)){
		$sql = "OPTIMIZE TABLE  `loctransfers` ,
					`salesanalysis`";
	}elseif (($NumberDay == 9) OR ($NumberDay == 29)){
		$sql = "OPTIMIZE TABLE  `banktrans` ,
					`locstock`";
	}elseif (($NumberDay == 10) OR ($NumberDay == 30)){
		$sql = "OPTIMIZE TABLE  `debtortranstaxes` ,
					`salesorders`";
	}elseif (($NumberDay == 11) OR ($NumberDay == 31)){
		$sql = "OPTIMIZE TABLE  `custallocns` ,
					`stockmovestaxes`";
	}elseif ($NumberDay == 12){
		$sql = "OPTIMIZE TABLE  `klretailcustomers` ,
					`pcashdetails`";
	}elseif ($NumberDay == 13){
		$sql = "OPTIMIZE TABLE  `chartdetails` ,
					`stockrequestitems` ,
					`fixedassettrans`";
	}elseif ($NumberDay == 14){
		$sql = "OPTIMIZE TABLE  `grns` ,
					`purchorderdetails` ,
					`purchdata`";
	}elseif ($NumberDay == 15){
		$sql = "OPTIMIZE TABLE  `relateditems` ,
					`klconsignment` ,
					`worequirements` ,
					`prices` ,
					`kladjustrl` ,
					`stockrequest` ,
					`suppinvstogrn`";
	}elseif ($NumberDay == 16){
		$sql = "OPTIMIZE TABLE  `stockmaster` ,
					`levels` ,
					`supptrans` ,
					`stockdescriptiontranslations` ,
					`woitems` ,
					`loctransfercancellations` ,
					`workorders`";
	}elseif ($NumberDay == 17){
		$sql = "OPTIMIZE TABLE  `bom` ,
					`mrpsupplies` ,
					`purchorders` ,
					`supptranstaxes` ,
					`glaccountusers` ,
					`salariescalculated` ,
					`klsalesperformance`";
	}elseif ($NumberDay == 18){
		$sql = "OPTIMIZE TABLE  `salescatprod` ,
					`freightcosts` ,
					`fixedassets` ,
					`returneditems` ,
					`mrprequirements` ,
					`locationusers` ,
					`mrpcalendar`";
	}elseif ($NumberDay == 19){
		$sql = "OPTIMIZE TABLE  `mrpdemands` ,
					`scripts` ,
					`klfreeexchanges` ,
					`securitygroups` ,
					`pctabexpenses` ,
					`chartmaster` ,
					`pcexpenses`";
	}elseif ($NumberDay == 20){
		$sql = "OPTIMIZE TABLE  `custbranch` ,
					`debtorsmaster` ,
					`suppliers` ,
					`salesman` ,
					`bankaccountusers` ,
					`klrevisedemaildomains` ,
					`config` ,
					`periods` ,
					`www_users`";
	}
	
	$result = DB_query($sql,$ErrMsg);
	if ($EmailText !=''){
		$EmailText = $EmailText . _('The system has just run the daily Kapal-Laut optimization. Day = '). $NumberDay . "\n\n" . $sql . "\n\n"; 
	}else{
		prnMsg(_('The system has just run the daily Kapal-Laut optimization. Day = '). $NumberDay,'info');
	}
	return $EmailText;
}

function PurgeKLTable($TableName,$DateField, $ShowMessages, $db){
	if ($_SESSION['MonthsAuditTrail'] > 0){
		 $sql = "DELETE FROM " . $TableName . "
				WHERE  " . $DateField . " <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'
					AND " . $DateField .  " != '0000-00-00'";
		$ErrMsg ='Could not purge table ' . $TableName . ' because';
		$result = DB_query($sql,$ErrMsg);
		if ($ShowMessages) prnMsg("Table " . $TableName . " purged.","info");
	}
}

function CleanDiscountForObsoleteItems($ShowMessages, $db){
	$sql = "UPDATE stockmaster
			SET discountcategory = ''
			WHERE discontinued = 1
				AND discountcategory != ''";
	$ErrMsg =_('Could not clean discount category for obsolete items  because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Discount Category cleaned for obsolete items.","info");
}

function SetObsoleteForCategoryWithoutStock($category, $ShowMessages, $db){
	$sql = "UPDATE stockmaster
			SET discontinued = 1
			WHERE categoryid = '" . $category . "'
				AND discontinued = 0
				AND (SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) = 0";
	$ErrMsg =_('Could not update items without stock because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Items " . $category . " with QOH = 0 flagged as obsolete.","info");
}

function SetRLZeroForObsolete($ShowMessages, $db){
	$sql = "UPDATE locstock
			SET reorderlevel = 0
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = locstock.stockid
						AND stockmaster.discontinued = 1)";
	$ErrMsg =_('Could not set RL = 0 for obsolete items because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("RL updated to zero for obsolete items.","info");
}

function SetRLZeroForLocations($ShowMessages, $db){
	$sql = "UPDATE locstock
			SET reorderlevel = 0
			WHERE loccode IN " . LIST_LOCATIONS_WITH_RL_ALWAYS_ZERO;
	$ErrMsg =_('Could not set RL = 0 for location list because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("RL updated to zero for location list.","info");
}

function SetEndDatePriceToObsolete($ShowMessages, $db){
	$sql = "UPDATE prices
			SET enddate = '" . date('Y-m-d') ."'
			WHERE EXISTS (SELECT *
						FROM stockmaster
						WHERE stockmaster.stockid = prices.stockid
						AND stockmaster.discontinued = 1)
				AND (enddate > '"  . date('Y-m-d') ."'
				  OR enddate = '0000-00-00')";
	$ErrMsg =_('Could not set end date to today for obsolete items because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Prices End Date updated to today for obsolete items.","info");
}
			
function CleanInternalRequestsWithoutItems($ShowMessages, $db){
	$sql = "DELETE FROM stockrequest 
			WHERE NOT EXISTS (SELECT *
								FROM stockrequestitems
								WHERE stockrequest.dispatchid = stockrequestitems.dispatchid )";
	$ErrMsg =_('Could not delete empty internal requests because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Empty Internal Requests removed from DB.","info");
}			

function CleanObsoleteFromWebsite($ShowMessages, $db){
	$sql = "DELETE FROM salescatprod
			WHERE EXISTS (SELECT * FROM stockmaster
							WHERE discontinued = 1
							AND stockmaster.stockid = salescatprod.stockid)";
	$ErrMsg =_('Could not delete obsolete items from sales category for website because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Obsolete items removed from website list.","info");
}
	
function CleanWrongPrices($ShowMessages, $db){
	$sql = "DELETE FROM prices
			WHERE startdate > enddate
			AND enddate != '0000-00-00'";
	$ErrMsg =_('Could not delete wrong prices because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Wrong prices removed from DB","info");

	$sql = "UPDATE prices
			SET enddate = '0000-00-00'
			WHERE enddate = '2050-12-31'";
	$ErrMsg =_('Could not delete wrong prices because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Set end date 0000-00-00 to prices from DB","info");
}

function CleanOldDoubleReceivedGoods($NumDays, $ShowMessages, $db){
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	$sql = "UPDATE loctransfers
			SET recqty = shipqty
			WHERE recdate <= '" . $StartDate. "'
				AND recqty = 2 * shipqty";
	$ErrMsg =_('Could not fix double received goods in shops because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Clean old double received goods in transfers","info");
}

	
function SetStatusCompleteToFinishedOldPurchaseOrders($maxdays, $ShowMessages, $db){
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
	if ($ShowMessages) prnMsg("Set status to Completed to Finished Purchase Orders older than " . $maxdays . " days.","info");
}		

function AuthorizeAllInternalStockRequest($ShowMessages, $db){
	$sql = "UPDATE stockrequest
					SET authorised='1'
					WHERE authorised !='1'";
	$ErrMsg =_('Could not authorize all internal stock requests because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("All pending Internal Stock Requests authorised automatically","info");
}


function BlockInactiveUsers($access, $maxdays, $ShowMessages, $db){
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
	if ($ShowMessages) prnMsg("Blocked inactive users","info");
}

function CleanListToPrint($List){
	$ToClean = array("'", "(", ")");
	$List = str_replace($ToClean, "", $List);
	return $List;
}

function PurgeAuditTrailTable($ShowMessages, $db){
	 $sql = "DELETE FROM audittrail
			WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
	$result = DB_query($sql);
	if ($ShowMessages) prnMsg("Purge old Audit Trail table","info");
}

function SetTopSalesRanking($ShowMessages, $db){

	$sql = "TRUNCATE klsalesperformance";
	$ErrMsg =_('Could not set TRUNCATE klsalesperformance because');
	$result = DB_query($sql,$ErrMsg);
	if ($ShowMessages) prnMsg("Truncated klsaleseprformace table","info");
	
	SetTopSalesByGroup(TRUE,  "KAPAL-LAUT", 60, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "KAPAL-LAUT", 30, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "KAPAL-LAUT", 90, $ShowMessages, $db);
	SetTopSalesByGroup(TRUE,  "BLINK", 60, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "BLINK", 30, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "BLINK", 90, $ShowMessages, $db);
	SetTopSalesByGroup(TRUE,  "OUTLET", 60, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "OUTLET", 30, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "OUTLET", 90, $ShowMessages, $db);
	SetTopSalesByGroup(TRUE,  "GENERAL", 60, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "GENERAL", 30, $ShowMessages, $db);
	SetTopSalesByGroup(FALSE, "GENERAL", 90, $ShowMessages, $db);
	
}

function SetTopSalesByGroup($InsertNeeded, $Group, $NumDays, $ShowMessages, $db){

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
	if (DB_num_rows($result) != 0){
		while ($myrow = DB_fetch_array($result)) {
			if ($InsertNeeded){
				$SQLOp="INSERT INTO klsalesperformance
										(
										stockid,
										topsales". $NumDays.",
										valuesales". $NumDays."
										)
									VALUES (
										'" . $myrow['stkcode'] . "',
										'" . $position . "',
										'" . $myrow['valuesales'] . "'
										)";
				$ErrMsg =_('Could not insert items by top sales because');
			}else{
				$SQLOp="UPDATE klsalesperformance
						SET topsales". $NumDays." = '" . $position . "',
							valuesales". $NumDays." = '" . $myrow['valuesales'] . "'
						WHERE stockid = '" . $myrow['stkcode'] . "'";
				$ErrMsg =_('Could not update items by top sales because');
				
			}
			$resultOp = DB_query($SQLOp,$ErrMsg);
			
			$position++;
		}
	}

	if ($ShowMessages) prnMsg("Top Sales Ranking for " . $Group . " items for last " . $NumDays . " days","info");
}


?>