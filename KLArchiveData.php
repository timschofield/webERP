<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/ArchiveConnectDB.php');

$Title = _('KL Archive Data from Production DB into Archive DB');

if (!isset($_POST['ArchiveGltransPeriod'])){
	$_POST['ArchiveGltransPeriod'] = -99;
}

if (!isset($_POST['ArchiveStockmovesPrd'])){
	$_POST['ArchiveStockmovesPrd'] = -99;
}

if (!isset($_POST['ArchiveLoctransfersObsoletes'])){
	$_POST['ArchiveLoctransfersObsoletes'] = 'N';
}

if (isset($_POST['submit'])) {
	submit($Title, 
			$_POST['ArchiveGltransPeriod'], 
			$_POST['ArchiveStockmovesPrd'], 
			$_POST['ArchiveLoctransfersObsoletes']);
} else {
	display($Title);
}



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $ArchiveGltransPeriod, $ArchiveStockmovesPrd, $ArchiveLoctransfersObsoletes) {

	include('includes/header.php');
	ArchivetableGltrans($ArchiveGltransPeriod);
	ArchivetableStockmoves($ArchiveStockmovesPrd);
	ArchivetableStockmovestaxes($ArchiveStockmovesPrd);
//	ArchivetableLoctransfersObsoletes($ArchiveLoctransfersObsoletes);
	include('includes/footer.php');

} // End of function submit()


function display($Title) {
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	$AlreadyArchivedGltransPeriod = GetPeriodAlreadyArchived('gltrans');
	$AlreadyArchivedStockmovesPrd = GetPeriodAlreadyArchived('stockmoves');
//	$AlreadyArchivedLocTransfersRecdate = GetPeriodAlreadyArchived('loctransfers');

	echo '<fieldset><legend>' . _('Archive Options') . '</legend>';
	echo FieldToSelectOnePeriod('ArchiveGltransPeriod', $AlreadyArchivedGltransPeriod, _('Archive gltrans table records older or equal than'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);
	echo FieldToSelectOnePeriod('ArchiveStockmovesPrd', $AlreadyArchivedStockmovesPrd, _('Archive stockmoves table records older or equal than'), '', 'NEWER_OR_EQUAL_THAN_SELECTED', '', true, false);

/*	echo FieldToSelectFromTwoOptions('N', _('No'), 
									'Y', _('Yes'), 
									'ArchiveLoctransfersObsoletes', 'N', _('Archive loctransfers of obsolete items') . ':', '', '', '', true, false);
			
	echo FieldToSelectOneDate('ArchiveLocTransfersRecdate', ConvertSQLDate($AlreadyArchivedLocTransfersRecdate), _('Archive loctransfers table older or equal than'), '', '', '', true, false);
*/
	echo '</fieldset>';
	
	echo OneButtonCenteredForm('submit', $Title);
	
	echo '</div>
    	</form>';

	include('includes/footer.php');

} // End of function display()

function ArchivetableGltrans($ArchiveToPeriod){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	$StartRecords = GetNumberOfRecordsInTable('gltrans');
	
	// search for the newest date already archived in Archive database table
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT MAX(periodno) AS archivedperiod
			FROM gltrans";

	$Result = DB_query_archive($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = $MyRow['archivedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('gltrans table currently contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("gltrans table already archived until period " . MonthAndYearFromPeriodNo($PeriodAlreadyArchived));
	prnMsg('Archiving gltrans records older or equal than period '. MonthAndYearFromPeriodNo($ArchiveToPeriod));

	if ( $PeriodAlreadyArchived < $ArchiveToPeriod){
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
		if (DB_num_rows($Result) != 0){
			$RecordCounter = 0;
			while ($MyRow = DB_fetch_array($Result)) {
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
				DB_query_archive($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into Archive DB ". locale_number_format($RecordCounter) . " records of gltrans table");
			
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
			if (DB_num_rows($Result) != 0){
				while ($MyConsolidatedRow = DB_fetch_array($Result)) {
					
					$SQLDelete = "DELETE FROM gltrans 
									WHERE periodno = ".$MyConsolidatedRow['periodno']."
										AND account = '".$MyConsolidatedRow['account']."'";
					DB_query($SQLDelete,$ErrMsg,$DbgMsg);
					
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
					DB_query($SQLInsert,$ErrMsg,$DbgMsg);
				}
				prnMsg("Inserted consolidated accounting records in production DB gltrans table");
				$SQLUpdate = "UPDATE klarchivedtables 
							SET period = ".$ArchiveToPeriod."
							WHERE name = 'gltrans'";
				DB_query($SQLUpdate,$ErrMsg,$DbgMsg);
				prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));
			}
		}

		// count how many records are on gltrans in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('gltrans');
		prnMsg('Production gltrans table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');
	}else{
		prnMsg("gltrans table: Nothing to archive", "warn");
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();
	}
}

function ArchivetableStockmoves($ArchiveToPeriod){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('stockmoves');

	// search for the newest date already archived in Archive database table
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT MAX(prd) AS archivedperiod
			FROM stockmoves";

	$Result = DB_query_archive($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = $MyRow['archivedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('stockmoves table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("stockmoves table already archived until period: " . MonthAndYearFromPeriodNo($PeriodAlreadyArchived));
	prnMsg('Archive stockmoves older or equal than period '. MonthAndYearFromPeriodNo($ArchiveToPeriod));

	if ($PeriodAlreadyArchived < $ArchiveToPeriod){
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
		if (DB_num_rows($Result) != 0){
			$RecordCounter = 0;
			while ($MyRow = DB_fetch_array($Result)) {
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
				DB_query_archive($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into Archive DB ". locale_number_format($RecordCounter) . " records of stockmoves table");
			
			$SQLDelete = "DELETE FROM stockmoves 
							WHERE prd <= " . $ArchiveToPeriod . "";
			DB_query($SQLDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted stockmoves records in Production DB");
			
			$SQLUpdate = "UPDATE klarchivedtables 
						SET period = ".$ArchiveToPeriod."
						WHERE name = 'stockmoves'";
			DB_query($SQLUpdate,$ErrMsg,$DbgMsg);
			prnMsg("Updated klarchivedtables records to reflect the new archive period: " . MonthAndYearFromPeriodNo($ArchiveToPeriod));
		}

		// count how many records are on stockmoves in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('stockmoves');
		prnMsg('stockmoves table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');

	}else{
		prnMsg("stockmoves table: Nothing to archive", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();
	}
}

function ArchivetableStockmovestaxes(){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('stockmovestaxes');

	prnMsg('stockmovestaxes table contains '. locale_number_format($StartRecords) . ' records');

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
	
	if (DB_num_rows($Result) != 0){
		// select the webERP stockmoves table to be copied into Archive DB
		$RecordCounter = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$SQLInsert = "INSERT INTO stockmovestaxes 
								( stkmoveno,
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
			DB_query_archive($SQLInsert,$ErrMsg,$DbgMsg);
			$RecordCounter++;
		}
		prnMsg("Copied into Archive DB ". locale_number_format($RecordCounter) . " records of stockmovestaxes table");
		
		$SQLDelete = "DELETE st 
					FROM stockmovestaxes st
					LEFT JOIN stockmoves sm 
						ON st.stkmoveno = sm.stkmoveno
					WHERE sm.stkmoveno IS NULL";
		DB_query($SQLDelete,$ErrMsg,$DbgMsg);
		prnMsg("Deleted stockmovestaxes records in webERP production DB");

		// count how many records are on stockmovestaxes in webERP production DB
		$EndRecords = GetNumberOfRecordsInTable('stockmovestaxes');
		prnMsg('stockmovestaxes table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records archived', 'success');
	}else{
		prnMsg("stockmovestaxes table: Nothing to archive", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();
	}
}

function ArchivetableLoctransfersObsoletes($ArchiveLoctransfersObsoletes){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on loctransfers in webERP production DB
	$StartRecords = GetNumberOfRecordsInTable('loctransfers');

	// search for the newest date already archived in Archive database table
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT MAX(recdate) AS archivedperiod
			FROM loctransfers";

	$Result = DB_query_archive($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = $MyRow['archivedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('loctransfers table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("loctransfers table already archived until : " . $PeriodAlreadyArchived);

	if ($ArchiveLoctransfersObsoletes == "Y"){
		// select the webERP loctransfers table to be copied into Archive DB
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
					AND loctransfers.recdate <= '" . $PeriodAlreadyArchived . "'";
		$Result = DB_query($SQL);	
		$ErrMsg = _('An error occurred in inserting the stockmoves record');
		$DbgMsg = _('The SQL that was used to insert the stockmoves record was');		
		if (DB_num_rows($Result) != 0){
			$RecordCounter = 0;
			while ($MyRow = DB_fetch_array($Result)) {
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
				DB_query_archive($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into Archive DB ". locale_number_format($RecordCounter) . " records of loctransfers table");
			
			$SQLDelete = "DELETE FROM loctransfers 
							WHERE stockid IN (SELECT stockid FROM stockmaster WHERE discontinued = 1)";
			DB_query($SQLDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted loctransfers records in webERP production DB");

		}
	}else{
		prnMsg("locatransfers: Nothing to archive", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on loctransfers in webERP production DB
	$EndRecords = GetNumberOfRecordsInTable('loctransfers');
	prnMsg('loctransfers table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');
}


function GetPeriodAlreadyArchived($TableName){
	$PeriodAlreadyArchived = -99;
	$SQL = "SELECT period
			FROM klarchivedtables
			WHERE name = '".$TableName."'";

	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyArchived = $MyRow['period'];
	}else{
		prnMsg("Error: Cannot get period already archived from table ".$TableName, 'error');
	}
	return $PeriodAlreadyArchived;
} // End of function GetPeriodAlreadyArchived()


?>