<?php

/**************************************************************************************************************
Functions included in this file:
-------------------------------
ArchiveTableGltrans - Archives gltrans records from production DB to archive DB
ArchiveTableLoctransfersObsoletes - Archives loctransfers for obsolete items from production DB to archive DB
ArchiveTableStockmoves - Archives stockmoves records from production DB to archive DB
ArchiveTableStockmovestaxes - Archives stockmovestaxes records with no corresponding stockmoves
display - Displays the archive data form
GetPeriodAlreadyArchived - Gets the period already archived for a specific table
submit - Processes form submission and calls the archive functions
UpdateArchiveTablePeriod - Updates the archived period for a specific table
**************************************************************************************************************/

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/ArchiveConnectDB.php');

$Title = _('KL Archive Data from Production DB into Archive DB');

if (!isset($_POST['ArchiveGltransPeriod'])) {
	$_POST['ArchiveGltransPeriod'] = -13;
}

if (!isset($_POST['ArchiveStockmovesPrd'])) {
	$_POST['ArchiveStockmovesPrd'] = -13;
}

if (!isset($_POST['ArchiveDebtortransPrd'])) {
	$_POST['ArchiveDebtortransPrd'] = -13;
}

if (!isset($_POST['ArchiveLoctransfersObsoletes'])) {
	$_POST['ArchiveLoctransfersObsoletes'] = -13;
}

if (isset($_POST['submit'])) {
	submit($Title, 
		$_POST['ArchiveGltransPeriod'], 
		$_POST['ArchiveStockmovesPrd'], 
		$_POST['ArchiveLoctransfersObsoletes'],
		$_POST['ArchiveDebtortransPrd']);
} else {
	display($Title);
}

/**************************************************************************************************************
 * Submit function
 * 
 * Processes the form submission and calls the appropriate archive functions
 * 
 * @param string $Title The page title
 * @param int $ArchiveGltransPeriod The period to archive gltrans records up to
 * @param int $ArchiveStockmovesPrd The period to archive stockmoves records up to
 * @param int $ArchiveLoctransfers The period to archive loctransfers records up to
 * @return void
 **************************************************************************************************************/
function submit($Title, $ArchiveGltransPeriod, $ArchiveStockmovesPrd, $ArchiveLoctransfers, $ArchiveDebtortransPrd) {

	include('includes/header.php');
	ArchiveTableGltrans($ArchiveGltransPeriod);

	ArchiveTableStockmoves($ArchiveStockmovesPrd);
	ArchiveTableStockmovestaxes();

	ArchiveTableLoctransfersObsoletes($ArchiveLoctransfers);

	ArchiveTableDebtortrans($ArchiveDebtortransPrd);
	ArchiveTableDebtortranstaxes();

	include('includes/footer.php');

} // End of function submit()

/**************************************************************************************************************
 * Display function
 * 
 * Displays the archive data form with options for selecting periods to archive
 *
 * @param string $Title The page title
 * @return void
 **************************************************************************************************************/
function display($Title) {
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	// Add a table showing record counts for both databases with consistent styling
	echo '<table class="selection">
		<thead>
			<tr>
				<th class="ascending">' . _('Table Name') . '</th>
				<th class="ascending">' . _('Records in Production DB') . '</th>
				<th class="ascending">' . _('Records in Archive DB') . '</th>
			</tr>
		</thead>
		<tbody>';
	
	echo '<tr class="striped_row">
			<td>' . _('gltrans') . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('gltrans', 'Production'), 0) . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('gltrans', 'Archive'), 0) . '</td>
		</tr>';
	
	echo '<tr class="striped_row">
			<td>' . _('stockmoves') . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('stockmoves', 'Production'), 0) . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('stockmoves', 'Archive'), 0) . '</td>
		</tr>';
	
	echo '<tr class="striped_row">
			<td>' . _('stockmovestaxes') . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('stockmovestaxes', 'Production'), 0) . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('stockmovestaxes', 'Archive'), 0) . '</td>
		</tr>';
	
	echo '<tr class="striped_row">
			<td>' . _('loctransfers') . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('loctransfers', 'Production'), 0) . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('loctransfers', 'Archive'), 0) . '</td>
		</tr>';

	echo '<tr class="striped_row">
			<td>' . _('debtortrans') . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('debtortrans', 'Production'), 0) . '</td>
			<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('debtortrans', 'Archive'), 0) . '</td>
		</tr>';

	echo '<tr class="striped_row">
		<td>' . _('debtortranstaxes') . '</td>
		<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('debtortranstaxes', 'Production'), 0) . '</td>
		<td class="number">' . locale_number_format(GetNumberOfRecordsInTable('debtortranstaxes', 'Archive'), 0) . '</td>
	</tr>';

	echo '</tbody></table><br/>';

	$AlreadyArchivedGltransPeriod = GetPeriodAlreadyArchived('gltrans');
	$AlreadyArchivedStockmovesPrd = GetPeriodAlreadyArchived('stockmoves');
	$AlreadyArchivedLoctransfersPrd = GetPeriodAlreadyArchived('loctransfers');
	$AlreadyArchivedDebtorTransPeriod = GetPeriodAlreadyArchived('debtortrans');

	echo '<fieldset><legend>' . _('Archive Options') . '</legend>';
	echo FieldToSelectOnePeriod('ArchiveGltransPeriod', $AlreadyArchivedGltransPeriod, _('Archive gltrans records older or equal than'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);
	echo FieldToSelectOnePeriod('ArchiveStockmovesPrd', $AlreadyArchivedStockmovesPrd, _('Archive stockmoves records older or equal than'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);
	echo FieldToSelectOnePeriod('ArchiveLoctransfersObsoletes', $AlreadyArchivedLoctransfersPrd, _('Archive loctransfers records for items maked as obsolete before or equal than'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);
	echo FieldToSelectOnePeriod('ArchiveDebtortransPrd', $AlreadyArchivedDebtorTransPeriod, _('Archive debtortrans records for customers marked as obsolete'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);
	echo '</fieldset>';
	
	echo OneButtonCenteredForm('submit', $Title);
	
	echo '</div>
		</form>';

	include('includes/footer.php');

} // End of function display()

/**************************************************************************************************************
 * ArchiveTableGltrans function
 * 
 * Archives gltrans records from production DB to archive DB up to the specified period
 * Creates consolidated accounting entries for archived records
 *
 * @param int $ArchiveToPeriod The period up to which records should be archived
 * @return void
 **************************************************************************************************************/
function ArchiveTableGltrans($ArchiveToPeriod) {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	$StartRecords = GetNumberOfRecordsInTable('gltrans', 'Production');
	// count how many records are on gltrans in webERP production DB
	
	// search for the newest date already archived in Archive database table
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT MAX(periodno) AS archivedperiod
			FROM gltrans";

	$Result = DB_query_archive($SQL);	
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = ($MyRow['archivedperiod'] === null) ? $ArchiveToPeriod : $MyRow['archivedperiod'];
	} else {
		$ErrorsFound = true;
	}

	prnMsg('gltrans table currently contains ' . locale_number_format($StartRecords) . ' records');
	prnMsg("gltrans table already archived until period " . MonthAndYearFromPeriodNo($PeriodAlreadyArchived));
	prnMsg('Archiving gltrans records older or equal than period ' . MonthAndYearFromPeriodNo($ArchiveToPeriod));

	if ($PeriodAlreadyArchived < $ArchiveToPeriod) {
		// select the webERP gltrans table to be copied into Archive DB
		$SQL = "SELECT counterindex,
						type,
						typeno,
						chequeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						jobref
				FROM gltrans
				WHERE periodno > " . $PeriodAlreadyArchived . "
					AND periodno <= " . $ArchiveToPeriod . "
				ORDER BY periodno,
						trandate,
						account";
		$Result = DB_query($SQL);	
		$ErrMsg = _('An error occurred in inserting the gltrans record');
		$DbgMsg = _('The SQL that was used to insert the gltrans record was');		
		if (DB_num_rows($Result) != 0) {
			$RecordCounter = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				if (!DataExistsInArchive('gltrans', 'counterindex', $MyRow['counterindex'])){
					$SQLInsert = "INSERT INTO gltrans 
									( counterindex,
									type,
									typeno,
									chequeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									jobref
								) VALUES (
								'" . $MyRow['counterindex'] . "',
								'" . $MyRow['type'] . "',
								'" . $MyRow['typeno'] . "',
								'" . DB_escape_string($MyRow['chequeno'] ?? '') . "',
								'" . $MyRow['trandate'] . "',
								'" . $MyRow['periodno'] . "',
								'" . DB_escape_string($MyRow['account'] ?? '') . "',
								'" . DB_escape_string($MyRow['narrative'] ?? '') . "',
								'" . $MyRow['amount'] . "',
								'" . DB_escape_string($MyRow['jobref'] ?? '') . "')";
					DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
					$RecordCounter++;
				}
			}
			prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of gltrans table");
			
			// Now calculate consolidated values for each period and account, delete the details and write the consolidated value on webERP database
			$SQL = "SELECT periodno,
							account,
							MAX(trandate) AS maxdate,
							SUM(amount) AS consolidated
					FROM gltrans
					WHERE periodno > " . $PeriodAlreadyArchived . "
						AND periodno <= " . $ArchiveToPeriod . "
					GROUP BY periodno,
							account
					ORDER BY periodno,
							account";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0) {
				while ($MyConsolidatedRow = DB_fetch_array($Result)) {
					
					$SQLDelete = "DELETE FROM gltrans 
									WHERE periodno = " . $MyConsolidatedRow['periodno'] . "
										AND account = '" . $MyConsolidatedRow['account'] . "'";
					DB_query($SQLDelete, $ErrMsg, $DbgMsg);
					
					$Typeno = GetNextTransNo(1000);
					$SQLInsert = "INSERT INTO gltrans 
										(type,
										typeno,
										chequeno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										jobref
									) VALUES (
									'1000',
									'" . $Typeno . "',
									'0',
									'" . $MyConsolidatedRow['maxdate'] . "',
									'" . $MyConsolidatedRow['periodno'] . "',
									'" . $MyConsolidatedRow['account'] . "',
									'CONSOLIDATED ACCOUNTING',
									'" . $MyConsolidatedRow['consolidated'] . "',
									'')";
					DB_query($SQLInsert, $ErrMsg, $DbgMsg);
				}
				prnMsg("Inserted consolidated accounting records in production DB gltrans table");

				UpdateArchiveTablePeriod('gltrans', $ArchiveToPeriod);
				prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));
			}
		}

		// count how many records are on gltrans in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('gltrans', 'Production');
		prnMsg('Production gltrans table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');
	} else {
		prnMsg("gltrans table: Nothing to archive", "warn");
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}

/**************************************************************************************************************
 * ArchiveTableStockmoves function
 * 
 * Archives stockmoves records from production DB to archive DB up to the specified period
 *
 * @param int $ArchiveToPeriod The period up to which records should be archived
 * @return void
 **************************************************************************************************************/
function ArchiveTableStockmoves($ArchiveToPeriod) {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('stockmoves', 'Production');

	// search for the newest date already archived in Archive database table
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT MAX(prd) AS archivedperiod
			FROM stockmoves";

	$Result = DB_query_archive($SQL);	
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = ($MyRow['archivedperiod'] === null) ? $ArchiveToPeriod : $MyRow['archivedperiod'];
	} else {
		$ErrorsFound = true;
	}

	prnMsg('stockmoves table contains ' . locale_number_format($StartRecords) . ' records');
	prnMsg("stockmoves table already archived until period: " . MonthAndYearFromPeriodNo($PeriodAlreadyArchived));
	prnMsg('Archive stockmoves older or equal than period ' . MonthAndYearFromPeriodNo($ArchiveToPeriod));

	if ($PeriodAlreadyArchived < $ArchiveToPeriod) {
		// select the webERP stockmoves table to be copied into Archive DB
		$SQL = "SELECT stkmoveno,
						stockid,
						type,
						transno,
						loccode,
						trandate,
						userid,
						debtorno,
						branchcode,
						price,
						prd,
						reference,
						qty,
						discountpercent,
						standardcost,
						show_on_inv_crds,
						newqoh,
						hidemovt,
						narrative
				FROM stockmoves
				WHERE prd <= " . $ArchiveToPeriod . "";
		$Result = DB_query($SQL);	
		$ErrMsg = _('An error occurred in inserting the stockmoves record');
		$DbgMsg = _('The SQL that was used to insert the stockmoves record was');		
		if (DB_num_rows($Result) != 0) {
			$RecordCounter = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				if (!DataExistsInArchive('stockmoves', 'stkmoveno', $MyRow['stkmoveno'])){
					$SQLInsert = "INSERT INTO stockmoves 
									( stkmoveno,
									stockid,
									type,
									transno,
									loccode,
									trandate,
									userid,
									debtorno,
									branchcode,
									price,
									prd,
									reference,
									qty,
									discountpercent,
									standardcost,
									show_on_inv_crds,
									newqoh,
									hidemovt,
									narrative
								) VALUES (
								'" . $MyRow['stkmoveno'] . "',
								'" . DB_escape_string($MyRow['stockid'] ?? '') . "',
								'" . $MyRow['type'] . "',
								'" . $MyRow['transno'] . "',
								'" . DB_escape_string($MyRow['loccode'] ?? '') . "',
								'" . $MyRow['trandate'] . "',
								'" . DB_escape_string($MyRow['userid'] ?? '') . "',
								'" . DB_escape_string($MyRow['debtorno'] ?? '') . "',
								'" . DB_escape_string($MyRow['branchcode'] ?? '') . "',
								'" . $MyRow['price'] . "',
								'" . $MyRow['prd'] . "',
								'" . DB_escape_string($MyRow['reference'] ?? '') . "',
								'" . $MyRow['qty'] . "',
								'" . $MyRow['discountpercent'] . "',
								'" . $MyRow['standardcost'] . "',
								'" . $MyRow['show_on_inv_crds'] . "',
								'" . $MyRow['newqoh'] . "',
								'" . $MyRow['hidemovt'] . "',
								'" . DB_escape_string($MyRow['narrative'] ?? '') . "')";
					DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
					$RecordCounter++;
				}
			}
			prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of stockmoves table");
			
			$SQLDelete = "DELETE FROM stockmoves 
							WHERE prd <= " . $ArchiveToPeriod . "";
			DB_query($SQLDelete, $ErrMsg, $DbgMsg);
			prnMsg("Deleted stockmoves records in Production DB");
			
			UpdateArchiveTablePeriod('stockmoves', $ArchiveToPeriod);
			prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));
		}

		// count how many records are on stockmoves in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('stockmoves', 'Production');
		prnMsg('stockmoves table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');

	} else {
		prnMsg("stockmoves table: Nothing to archive", 'warn');
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}

/**************************************************************************************************************
 * ArchiveTableStockmovestaxes function
 * 
 * Archives stockmovestaxes records from production DB to archive DB for which there is no corresponding
 * stockmoves record
 *
 * @return void
 **************************************************************************************************************/
function ArchiveTableStockmovestaxes() {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('stockmovestaxes', 'Production');

	prnMsg('stockmovestaxes table contains ' . locale_number_format($StartRecords) . ' records');

	$SQL = "SELECT st.stkmoveno,
				st.taxauthid,
				st.taxrate,
				st.taxontax,
				st.taxcalculationorder
			FROM stockmovestaxes st
			LEFT JOIN stockmoves sm ON st.stkmoveno = sm.stkmoveno
			WHERE sm.stkmoveno IS NULL";
	$Result = DB_query($SQL);	
	$ErrMsg = _('An error occurred in inserting the stockmovestaxes record');
	$DbgMsg = _('The SQL that was used to insert the stockmovestaxes record was');		
	
	if (DB_num_rows($Result) != 0) {
		// select the webERP stockmoves table to be copied into Archive DB
		$RecordCounter = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if (!DataExistsInArchive('stockmovestaxes', 'stkmoveno', $MyRow['stkmoveno'])){
				$SQLInsert = "INSERT INTO stockmovestaxes 
								(stkmoveno,
								taxauthid,
								taxrate,
								taxontax,
								taxcalculationorder
							) VALUES (
							'" . $MyRow['stkmoveno'] . "',
							'" . $MyRow['taxauthid'] . "',
							'" . $MyRow['taxrate'] . "',
							'" . $MyRow['taxontax'] . "',
							'" . $MyRow['taxcalculationorder'] . "')";
				DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
				$RecordCounter++;
			}
		}
		prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of stockmovestaxes table");
		
		$SQLDelete = "DELETE st 
					FROM stockmovestaxes st
					LEFT JOIN stockmoves sm 
						ON st.stkmoveno = sm.stkmoveno
					WHERE sm.stkmoveno IS NULL";
		DB_query($SQLDelete, $ErrMsg, $DbgMsg);
		prnMsg("Deleted stockmovestaxes records in webERP production DB");

		// count how many records are on stockmovestaxes in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('stockmovestaxes', 'Production');
		prnMsg('stockmovestaxes table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');
	} else {
		prnMsg("stockmovestaxes table: Nothing to archive", 'warn');
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}

/**************************************************************************************************************
 * ArchiveTableLoctransfersObsoletes function
 * 
 * Archives loctransfers records for obsolete items from production DB to archive DB up to the specified period
 *
 * @param int $ArchiveToPeriod The period up to which obsolete item transfers should be archived
 * @return void
 **************************************************************************************************************/
function ArchiveTableLoctransfersObsoletes($ArchiveToPeriod) {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	// count how many records are on loctransfers in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('loctransfers', 'Production');

	prnMsg('loctransfers table contains ' . locale_number_format($StartRecords) . ' records');

	$ArchiveToEndDate = EndDateSQLFromPeriodNo($ArchiveToPeriod);
	// select the webERP loctransfers of the items marked as obsolete before the period selected 
	$SQL = "SELECT loctransfers.loctransferid,
				loctransfers.reference,
				loctransfers.stockid,
				loctransfers.shipqty,
				loctransfers.recqty,
				loctransfers.shipdate,
				loctransfers.recdate,
				loctransfers.shiploc,
				loctransfers.recloc
			FROM loctransfers
			INNER JOIN stockmaster 
				ON loctransfers.stockid = stockmaster.stockid
			WHERE stockmaster.discontinued = 1
				AND stockmaster.date_updated <= '" . $ArchiveToEndDate . "'";
	$Result = DB_query($SQL);
	$ErrMsg = _('An error occurred in inserting the stockmoves record');
	$DbgMsg = _('The SQL that was used to insert the stockmoves record was');
	if (DB_num_rows($Result) != 0) {
		$RecordCounter = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if (!DataExistsInArchive('loctransfers', 'loctransferid', $MyRow['loctransferid'])){
				$SQLInsert = "INSERT INTO loctransfers 
									( loctransferid,
									reference,
									stockid,
									shipqty,
									recqty,
									shipdate,
									recdate,
									shiploc,
									recloc
								) VALUES (
								'" . $MyRow['loctransferid'] . "',
								'" . DB_escape_string($MyRow['reference'] ?? '') . "',
								'" . DB_escape_string($MyRow['stockid'] ?? '') . "',
								'" . $MyRow['shipqty'] . "',
								'" . $MyRow['recqty'] . "',
								'" . $MyRow['shipdate'] . "',
								'" . $MyRow['recdate'] . "',
								'" . DB_escape_string($MyRow['shiploc'] ?? '') . "',
								'" . DB_escape_string($MyRow['recloc'] ?? '') . "')";
				DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
				$RecordCounter++;
			}	
		}
		prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of loctransfers table");
		
		$SQLDelete = "DELETE lt 
					FROM loctransfers lt
					INNER JOIN stockmaster sm
						ON lt.stockid = sm.stockid
					WHERE sm.discontinued = 1
						AND sm.date_updated <= '" . $ArchiveToEndDate . "'";
		DB_query($SQLDelete, $ErrMsg, $DbgMsg);
		prnMsg("Deleted loctransfers records in webERP production DB");

		// count how many records are on loctransfers in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('loctransfers', 'Production');
		prnMsg('loctransfers table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');

		UpdateArchiveTablePeriod('loctransfers', $ArchiveToPeriod);
		prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));

	} else {
		prnMsg("locatransfers: Nothing to archive", 'warn');
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}

/**************************************************************************************************************
 * GetPeriodAlreadyArchived function
 * 
 * Gets the period already archived for a specific table from the klarchivedtables table
 *
 * @param string $TableName The name of the table to get the archived period for
 * @return int The period number already archived for the specified table
 **************************************************************************************************************/
function GetPeriodAlreadyArchived($TableName) {
	$PeriodAlreadyArchived = -13;
	$SQL = "SELECT period
			FROM klarchivedtables
			WHERE name = '" . $TableName . "'";

	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0) {
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = $MyRow['period'];
	} else {
		prnMsg("Error: Cannot get period already archived from table " . $TableName, 'error');
	}
	return $PeriodAlreadyArchived;
} // End of function GetPeriodAlreadyArchived()

/**************************************************************************************************************
 * UpdateArchiveTablePeriod function
 * 
 * Updates the archived period for a specific table in the klarchivedtables table
 *
 * @param string $TableName The name of the table to update the archived period for
 * @param int $Period The new period to set as archived for the table
 * @return void
 **************************************************************************************************************/
function UpdateArchiveTablePeriod($TableName, $Period) {
	$SQLUpdate = "UPDATE klarchivedtables 
				SET period = " . $Period . "
				WHERE name = '" . $TableName . "'";
	DB_query($SQLUpdate);
} // End of function UpdateArchiveTablePeriod()

/**************************************************************************************************************
 * ArchiveTableDebtortrans function
 * 
 * Archives debtortrans records from production DB to archive DB up to the specified period
 *
 * @param int $ArchiveToPeriod The period up to which records should be archived
 * @return void
 **************************************************************************************************************/
function ArchiveTableDebtortrans($ArchiveToPeriod) {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	// count how many records are on debtortrans in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('debtortrans', 'Production');

	prnMsg('debtortrans table contains ' . locale_number_format($StartRecords) . ' records');
	prnMsg('Archive debtortrans records for customers marked as obsolete ' . MonthAndYearFromPeriodNo($ArchiveToPeriod));

	// select the webERP debtortrans table to be copied into Archive DB
	$ArchiveToEndDate = EndDateSQLFromPeriodNo($ArchiveToPeriod);
	$SQL = "SELECT id,
					transno,
					type,
					debtortrans.debtorno,
					branchcode,
					trandate,
					inputdate,
					prd,
					settled,
					reference,
					tpe,
					order_,
					rate,
					ovamount,
					ovgst,
					ovfreight,
					ovdiscount,
					diffonexch,
					alloc,
					invtext,
					shipvia,
					edisent,
					consignment,
					packages,
					salesperson,
					balance
				FROM debtortrans
			INNER JOIN debtorsmaster
				ON debtortrans.debtorno = debtorsmaster.debtorno
			WHERE debtorsmaster.lastpaiddate <=  '" . $ArchiveToEndDate . "'
				AND (debtorsmaster.typeid = 7
				OR debtorsmaster.typeid = 11)";
	$Result = DB_query($SQL);	
	$ErrMsg = _('An error occurred in inserting the debtortrans record');
	$DbgMsg = _('The SQL that was used to insert the debtortrans record was');		
	if (DB_num_rows($Result) != 0) {
		$RecordCounter = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if (!DataExistsInArchive('debtortrans', 'id', $MyRow['id'])){
				$SQLInsert = "INSERT INTO debtortrans 
								(id,
								transno,
								type,
								debtorno,
								branchcode,
								trandate,
								inputdate,
								prd,
								settled,
								reference,
								tpe,
								order_,
								rate,
								ovamount,
								ovgst,
								ovfreight,
								ovdiscount,
								diffonexch,
								alloc,
								invtext,
								shipvia,
								edisent,
								consignment,
								packages,
								salesperson,
								balance
							) VALUES (
							'" . $MyRow['id'] . "',
							'" . $MyRow['transno'] . "',
							'" . $MyRow['type'] . "',
							'" . $MyRow['debtorno'] . "',
							'" . $MyRow['branchcode'] . "',
							'" . $MyRow['trandate'] . "',
							'" . $MyRow['inputdate'] . "',
							'" . $MyRow['prd'] . "',
							'" . $MyRow['settled'] . "',
							'" . DB_escape_string($MyRow['reference'] ?? '') . "',
							'" . $MyRow['tpe'] . "',
							'" . $MyRow['order_'] . "',
							'" . $MyRow['rate'] . "',
							'" . $MyRow['ovamount'] . "',
							'" . $MyRow['ovgst'] . "',
							'" . $MyRow['ovfreight'] . "',
							'" . $MyRow['ovdiscount'] . "',
							'" . $MyRow['diffonexch'] . "',
							'" . $MyRow['alloc'] . "',
							'" . DB_escape_string($MyRow['invtext'] ?? '') . "',
							'" . $MyRow['shipvia'] . "',
							'" . $MyRow['edisent'] . "',
							'" . $MyRow['consignment'] . "',
							'" . $MyRow['packages'] . "',
							'" . $MyRow['salesperson'] . "',
							'" . $MyRow['balance'] . "')";
				DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
				$RecordCounter++;
			}
		}
		prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of debtortrans table");
		
		$SQLDelete = "DELETE debtortrans 
					FROM debtortrans 
					INNER JOIN debtorsmaster
						ON debtortrans.debtorno = debtorsmaster.debtorno
					WHERE debtorsmaster.lastpaiddate <= '" . $ArchiveToEndDate . "'
						AND (debtorsmaster.typeid = 7
						OR debtorsmaster.typeid = 11)";
		DB_query($SQLDelete, $ErrMsg, $DbgMsg);
		prnMsg("Deleted debtortrans records in Production DB");
		
		UpdateArchiveTablePeriod('debtortrans', $ArchiveToPeriod);
		prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));

		// count how many records are on debtortrans in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('debtortrans', 'Production');
		prnMsg('debtortrans table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');

	} else {
		prnMsg("debtortrans table: Nothing to archive", 'warn');
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}

/**************************************************************************************************************
 * ArchiveTableDebtortranstaxes function
 * 
 * Archives debtortranstaxes records from production DB to archive DB for which there is no corresponding
 * debtortrans record
 *
 * @return void
 **************************************************************************************************************/
function ArchiveTableDebtortranstaxes() {
	DB_Txn_Begin();
	$ErrorsFound = false; // hope for the best
	
	// count how many records are on debtortrans in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('debtortranstaxes', 'Production');

	prnMsg('debtortranstaxes table contains ' . locale_number_format($StartRecords) . ' records');

	$SQL = "SELECT debtortranstaxes.debtortransid,
				debtortranstaxes.taxauthid,
				debtortranstaxes.taxamount
			FROM debtortranstaxes
			LEFT JOIN debtortrans
				ON debtortranstaxes.debtortransid = debtortrans.id
			WHERE debtortrans.id IS NULL";
	$Result = DB_query($SQL);	
	$ErrMsg = _('An error occurred in inserting the debtortranstaxes record');
	$DbgMsg = _('The SQL that was used to insert the debtortranstaxes record was');		
	
	if (DB_num_rows($Result) != 0) {
		// select the webERP debtortrans table to be copied into Archive DB
		$RecordCounter = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if (!DataExistsInArchive('debtortranstaxes', 'debtortransid', $MyRow['debtortransid'])){
				$SQLInsert = "INSERT INTO debtortranstaxes 
								(debtortransid,
								taxauthid,
								taxamount
							) VALUES (
							'" . $MyRow['debtortransid'] . "',
							'" . $MyRow['taxauthid'] . "',
							'" . $MyRow['taxamount'] . "')";
				DB_query_archive($SQLInsert, $ErrMsg, $DbgMsg);
				$RecordCounter++;
			}
		}
		prnMsg("Copied into Archive DB " . locale_number_format($RecordCounter) . " records of debtortranstaxes table");
		
		$SQLDelete = "DELETE debtortranstaxes 
					FROM debtortranstaxes
					LEFT JOIN debtortrans
						ON debtortranstaxes.debtortransid = debtortrans.id
					WHERE debtortrans.id IS NULL";
		DB_query($SQLDelete, $ErrMsg, $DbgMsg);
		prnMsg("Deleted debtortranstaxes records in webERP production DB");

		// count how many records are on debtortranstaxes in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('debtortranstaxes', 'Production');
		prnMsg('debtortranstaxes table now contains ' . locale_number_format($EndRecords) . ' records. ' . locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');
	} else {
		prnMsg("debtortranstaxes table: Nothing to archive", 'warn');
		$ErrorsFound = true;
	}

	if (!$ErrorsFound) {
		$Result = DB_Txn_Commit();
	} else {
		$Result = DB_Txn_Rollback();
	}
}


?>