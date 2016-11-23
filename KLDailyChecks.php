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
		$EmailText = KL_DailyMaintenanceDatabase(FALSE, $db, $EmailText);
	}elseif ($Group == "02"){
		$EmailText = DailyReorderLevelAdjustments(FALSE, TRUE, $RootPath, $db, $EmailText); // Updates RL 
	}elseif ($Group == "03"){
		$EmailText = KL_DailyEmailsToStaff($db, $EmailText);
	}elseif ($Group == "04"){
		$EmailText = WeberpToOpenCartDailySync(FALSE, $db, $db_oc, $oc_tableprefix, $EmailText);
		$EmailText = OpenCartToWeberpSync(FALSE, $db, $db_oc, $oc_tableprefix, $EmailText);
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


function KL_DailyMaintenanceDatabase($ShowMessages, $db, $EmailText = ''){
	SetObsoleteForCategoryWithoutStock("DISC20", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC50", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("DISC80", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOKL", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOBL", $ShowMessages, $db);
	SetObsoleteForCategoryWithoutStock("NOPOGE", $ShowMessages, $db);
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
	PurgeKLTable("klmovetodiscount50","endprocessdate", $ShowMessages, $db);
	PurgeKLTable("klmovetodiscount80","endprocessdate", $ShowMessages, $db);
	PurgeAuditTrailTable($ShowMessages, $db);
	$EmailText = KL_DailyOptimizationDatabase($ShowMessages, $db, $EmailText);

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
	$NumberDay = substr(Date('Y-m-d'),-1); // Get the last digit of the date, so we split optimization over 10 days
	
	$ErrMsg ='Could not OPTIMIZE tables because';
	$result = DB_query($sql,$ErrMsg);
	if ($NumberDay == 1){
		$sql = "OPTIMIZE TABLE  `accountgroups` ,
					`accountsection` ,
					`areas` ,
					`audittrail` ,
					`bankaccounts` ,
					`bankaccountusers` ,
					`banktrans` ,
					`bom` ,
					`buckets` ,
					`chartdetails` ,
					`chartmaster` ,
					`chartmasterPMA` ,
					`chartmasterM` ,
					`chartmasterPT` ,
					`cogsglpostings` ,
					`companies`";
	}elseif ($NumberDay == 2){
		$sql = "OPTIMIZE TABLE  `config` ,
					`contractbom` ,
					`contractcharges` ,
					`contractreqts` ,
					`contracts` ,
					`currencies` ,
					`custallocns` ,
					`custbranch` ,
					`custcontacts` ,
					`custnotes` ,
					`debtorsmaster` ,
					`debtortrans` ,
					`debtortranstaxes` ,
					`debtortype` ,
					`debtortypenotes`";
	}elseif ($NumberDay == 3){
		$sql = "OPTIMIZE TABLE  `deliverynotes` ,
					`departments` ,
					`discountmatrix` ,
					`ediitemmapping` ,
					`edimessageformat` ,
					`edi_orders_segs` ,
					`edi_orders_seg_groups` ,
					`emailsettings` ,
					`factorcompanies` ,
					`fixedassetcategories` ,
					`fixedassetlocations` ,
					`fixedassets` ,
					`fixedassettasks` ,
					`fixedassettrans` ,
					`freightcosts` ,
					`geocode_param`";
	}elseif ($NumberDay == 4){
		$sql = "OPTIMIZE TABLE  `gltrans` ,
					`grns` ,
					`holdreasons` ,
					`internalstockcatrole` ,
					`kladjustrl` ,
					`klchangeprice` ,
					`klfreeexchanges` ,
					`klmovetodiscount50` ,
					`klmovetodiscount80` ,
					`labelfields` ,
					`labels` ,
					`lastcostrollup` ,
					`levels` ,
					`locations` ,
					`locstock`";
	}elseif ($NumberDay == 5){
		$sql = "OPTIMIZE TABLE  `loctransfers` ,
					`mailgroupdetails` ,
					`mailgroups` ,
					`manufacturers` ,
					`mrpcalendar` ,
					`mrpdemands` ,
					`mrpdemandtypes` ,
					`mrpparameters` ,
					`mrpplannedorders` ,
					`mrprequirements` ,
					`mrpsupplies` ,
					`offers` ,
					`orderdeliverydifferenceslog` ,
					`packagingused` ,
					`paymentmethods` ,
					`paymentterms`";
	}elseif ($NumberDay == 6){
		$sql = "OPTIMIZE TABLE  `pcashdetails` ,
					`pcexpenses` ,
					`pctabexpenses` ,
					`pctabs` ,
					`pctypetabs` ,
					`periods` ,
					`pickinglistdetails` ,
					`pickinglists` ,
					`pricematrix` ,
					`prices` ,
					`purchdata` ,
					`purchorderauth` ,
					`purchorderdetails` ,
					`purchorders` ,
					`recurringsalesorders` ,
					`recurrsalesorderdetails`";
	}elseif ($NumberDay == 7){
		$sql = "OPTIMIZE TABLE  `relateditems` ,
					`reportcolumns` ,
					`reportfields` ,
					`reportheaders` ,
					`reportlets` ,
					`reportlinks` ,
					`reports` ,
					`salesanalysis` ,
					`salescat` ,
					`salescatprod` ,
					`salescattranslations` ,
					`salesglpostings` ,
					`salesman` ,
					`salesorderdetails` ,
					`salesorders` ,
					`salestypes`";
	}elseif ($NumberDay == 8){
		$sql = "OPTIMIZE TABLE  `scripts` ,
					`securitygroups` ,
					`securityroles` ,
					`securitytokens` ,
					`sellthroughsupport` ,
					`shipmentcharges` ,
					`shipments` ,
					`shippers` ,
					`stockcategory` ,
					`stockcatproperties` ,
					`stockcheckfreeze` ,
					`stockcounts` ,
					`stockdescriptiontranslations` ,
					`stockitemproperties` ,
					`stockmaster` ,
					`stockmoves`";
	}elseif ($NumberDay == 9){
		$sql = "OPTIMIZE TABLE  `stockmovestaxes` ,
					`stockrequest` ,
					`stockrequestitems` ,
					`stockserialitems` ,
					`stockserialmoves` ,
					`suppallocs` ,
					`suppliercontacts` ,
					`supplierdiscounts` ,
					`suppliers` ,
					`suppliertype` ,
					`supptrans` ,
					`supptranstaxes` ,
					`systypes` ,
					`tags` ,
					`taxauthorities` ,
					`taxauthrates`";
	}else{
		$sql = "OPTIMIZE TABLE  `taxcategories` ,
					`taxgroups` ,
					`taxgrouptaxes` ,
					`taxprovinces` ,
					`tenderitems` ,
					`tenders` ,
					`tendersuppliers` ,
					`unitsofdimension` ,
					`unitsofmeasure` ,
					`woitems` ,
					`worequirements` ,
					`workcentres` ,
					`workorders` ,
					`woserialnos` ,
					`www_users` ,
					`www_users_webshop`";
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



?>