<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/OldDataConnectDB.php');

$Title = _('Purge Old Data');

include('includes/header.php');

if (!isset($_POST['PurgeLoctransfersObsoletes'])){
	$_POST['PurgeLoctransfersObsoletes'] = 'N';
}

if (isset($_POST['submit'])) {
	submit($Title, 
			$_POST['PurgeGltransPeriod'], 
			$_POST['PurgeStockmovesPrd'], 
			$_POST['PurgeLoctransfersObsoletes']);
} else {
	display($Title);
}

include('includes/footer.php');


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $PurgeGltransPeriod, $PurgeStockmovesPrd, $PurgeLoctransfersObsoletes) {
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	PurgetableGltrans($PurgeGltransPeriod);
	PurgetableStockmoves($PurgeStockmovesPrd);
	PurgetableStockmovestaxes($PurgeStockmovesPrd);
	PurgetableLoctransfersObsoletes($PurgeLoctransfersObsoletes);

} // End of function submit()


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	$SQL = "SELECT gltransperiod,
					stockmovesprd,
					loctransfersrecdate
			FROM klolddatapurged";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$AlreadyPurgedGltransPeriod = $MyRow['gltransperiod'];
	$AlreadyPurgedStockmovesPrd = $MyRow['stockmovesprd'];
	$AlreadyPurgedLocTransfersRecdate = $MyRow['loctransfersrecdate'];

	echo '<table class="selection">';
	
	echo '<tr>
			<td>' . _('Purge gltrans table older or equal than:').'</td>
			<td><select required="required" name="PurgeGltransPeriod">';
	$SQL = "SELECT periodno, 
				   lastdate_in_period 
			FROM periods
			WHERE periodno >= " . $AlreadyPurgedGltransPeriod . "
			ORDER BY periodno DESC";
	$Periods = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Periods)){
		if( $MyRow['periodno']== $AlreadyPurgedGltransPeriod){
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period'])  . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Purge stockmoves table older or equal than:').'</td>
			<td><select required="required" name="PurgeStockmovesPrd">';
	$SQL = "SELECT periodno, 
				   lastdate_in_period 
			FROM periods
			WHERE periodno >= " . $AlreadyPurgedStockmovesPrd . "
			ORDER BY periodno DESC";
	$Periods = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Periods)){
		if( $MyRow['periodno']== $AlreadyPurgedStockmovesPrd){
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period'])  . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Purge loctransfers of obsolete items') . ':</td>
			<td><select name="PurgeLoctransfersObsoletes">
				<option selected="selected" value="N">' . _('No') . '</option>
				<option value="Y">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>';
			
/*	echo '<tr>
			<td>' . _('Purge loctransfers table older or equal than') . '</td>
			<td><input type="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="PurgeLocTransfersRecdate" size="10" maxlength="10" value="' . ConvertSQLDate($AlreadyPurgedLocTransfersRecdate) . '" /></td>
		</tr>';
*/
	echo '<tr>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';

} // End of function display()

function PurgetableGltrans($PurgeToPeriod){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on gltrans in webERP production DB
	$SQL = "SELECT COUNT(*) AS startrecords
			FROM gltrans";

	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$StartRecords = $MyRow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	// search for the newest date already purged in olddata database table
	$PeriodAlreadyPurged = -99;
	$SQL = "SELECT MAX(periodno) AS purgedperiod
			FROM gltrans";

	$Result = DB_query_od($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyPurged = $MyRow['purgedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('gltrans table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("gltrans table already purged until period: " . $PeriodAlreadyPurged);
	prnMsg('Purge gltrans older or equal than period '. $PurgeToPeriod);

	if ( $PeriodAlreadyPurged < $PurgeToPeriod){
		// select the webERP gltrans table to be copied into olddata DB
		$SQL = "SELECT counterindex,
						type,
						typeno,
						chequeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						posted,
						jobref,
						tag
				FROM gltrans
				WHERE periodno > " . $PeriodAlreadyPurged . "
					AND periodno <= " . $PurgeToPeriod . "";
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
									posted,
									jobref
								) VALUES (
								'" . $MyRow['counterindex'] . "',
								'" . $MyRow['type'] . "',
								'" . $MyRow['typeno'] . "',
								'" . $MyRow['chequeno'] . "',
								'" . $MyRow['trandate'] . "',
								'" . $MyRow['periodno'] . "',
								'" . $MyRow['account'] . "',
								'" . $MyRow['narrative'] . "',
								'" . $MyRow['amount'] . "',
								'" . $MyRow['posted'] . "',
								'" . $MyRow['jobref'] . "')";
				$ResultInsert = DB_query_od($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of gltrans table");
			
			// Now calculate consolidated values for each period and account, delete the details and write the consolidated value on webERP database
			$SQL = "SELECT periodno,
							account,
							MAX(trandate) AS maxdate,
							SUM(amount) AS consolidated
					FROM gltrans
					WHERE periodno > " . $PeriodAlreadyPurged . "
						AND periodno <= " . $PurgeToPeriod . "
					GROUP BY periodno,
							account";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0){
				while ($MyConsolidatedRow = DB_fetch_array($Result)) {
					
					$SQLDelete = "DELETE FROM gltrans 
									WHERE periodno = ".$MyConsolidatedRow['periodno']."
										AND account = '".$MyConsolidatedRow['account']."'";
					$ResultDelete = DB_query($SQLDelete,$ErrMsg,$DbgMsg);
					
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
										posted,
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
									'1',
									'')";
					$ResultInsert = DB_query($SQLInsert,$ErrMsg,$DbgMsg);
				}
				prnMsg("Inserted consolidated accounting records");
				$SQLUpdate = "UPDATE klolddatapurged SET gltransperiod = ".$PurgeToPeriod."";
				$ResultUpdate = DB_query($SQLUpdate,$ErrMsg,$DbgMsg);
				prnMsg("Updated klolsdatapurged records");
			}
		}
	}else{
		prnMsg("gltrans: Nothing to purge", "warn");
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on gltrans in webERP production DB
	$SQL = "SELECT COUNT(*) AS endrecords
			FROM gltrans";
	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$EndRecords = $MyRow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('gltrans table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');
}

function PurgetableStockmoves($PurgeToPeriod){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$SQL = "SELECT COUNT(*) AS startrecords
			FROM stockmoves";

	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$StartRecords = $MyRow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	// search for the newest date already purged in olddata database table
	$PeriodAlreadyPurged = -99;
	$SQL = "SELECT MAX(prd) AS purgedperiod
			FROM stockmoves";

	$Result = DB_query_od($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyPurged = $MyRow['purgedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('stockmoves table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("stockmoves table already purged until period: " . $PeriodAlreadyPurged);
	prnMsg('Purge stockmoves older or equal than period '. $PurgeToPeriod);

	if ($PeriodAlreadyPurged < $PurgeToPeriod){
		// select the webERP stockmoves table to be copied into olddata DB
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
				WHERE prd <= " . $PurgeToPeriod . "";
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
								'" . $MyRow['stockid'] . "',
								'" . $MyRow['type'] . "',
								'" . $MyRow['transno'] . "',
								'" . $MyRow['loccode'] . "',
								'" . $MyRow['trandate'] . "',
								'" . $MyRow['userid'] . "',
								'" . $MyRow['debtorno'] . "',
								'" . $MyRow['branchcode'] . "',
								'" . $MyRow['price'] . "',
								'" . $MyRow['prd'] . "',
								'" . $MyRow['reference'] . "',
								'" . $MyRow['qty'] . "',
								'" . $MyRow['discountpercent'] . "',
								'" . $MyRow['standardcost'] . "',
								'" . $MyRow['show_on_inv_crds'] . "',
								'" . $MyRow['newqoh'] . "',
								'" . $MyRow['hidemovt'] . "',
								'" . $MyRow['narrative'] . "')";
				$ResultInsert = DB_query_od($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of stockmoves table");
			
			$SQLDelete = "DELETE FROM stockmoves 
							WHERE prd <= " . $PurgeToPeriod . "";
			$ResultDelete = DB_query($SQLDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted stockmoves records in webERP production DB");
			
			$SQLUpdate = "UPDATE klolddatapurged SET stockmovesprd = ".$PurgeToPeriod."";
			$ResultUpdate = DB_query($SQLUpdate,$ErrMsg,$DbgMsg);
			prnMsg("Updated klolsdatapurged records");
		}
	}else{
		prnMsg("stockmoves: Nothing to purge", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on stockmoves in webERP production DB
	$SQL = "SELECT COUNT(*) AS endrecords
			FROM stockmoves";
	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$EndRecords = $MyRow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('stockmoves table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');
}

function PurgetableStockmovestaxes(){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$SQL = "SELECT COUNT(*) AS startrecords
			FROM stockmovestaxes";
	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$StartRecords = $MyRow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('stockmovestaxes table contains '. locale_number_format($StartRecords) . ' records');

	if ( TRUE){
		// select the webERP stockmoves table to be copied into olddata DB
		$SQL = "SELECT stkmoveno,
						taxauthid,
						taxrate,
						taxontax,
						taxcalculationorder
				FROM stockmovestaxes
				WHERE stkmoveno NOT IN (SELECT stkmoveno FROM stockmoves)";
		$Result = DB_query($SQL);	
		$ErrMsg = _('An error occurred in inserting the stockmovestaxes record');
		$DbgMsg = _('The SQL that was used to insert the stockmovestaxes record was');		
		if (DB_num_rows($Result) != 0){
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
				$ResultInsert = DB_query_od($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of stockmovestaxes table");
			
			$SQLDelete = "DELETE FROM stockmovestaxes 
							WHERE stkmoveno NOT IN (SELECT stkmoveno FROM stockmoves)";
			$ResultDelete = DB_query($SQLDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted stockmoves records in webERP production DB");
		}
	}else{
		prnMsg("stockmovestaxes: Nothing to purge", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on stockmoves in webERP production DB
	$SQL = "SELECT COUNT(*) AS endrecords
			FROM stockmovestaxes";
	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$EndRecords = $MyRow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('stockmovestaxes table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');
}

function PurgetableLoctransfersObsoletes($PurgeLoctransfersObsoletes){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	// count how many records are on loctransfers in webERP production DB
	$SQL = "SELECT COUNT(*) AS startrecords
			FROM loctransfers";

	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$StartRecords = $MyRow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	// search for the newest date already purged in olddata database table
	$PeriodAlreadyPurged = -99;
	$SQL = "SELECT MAX(recdate) AS purgedperiod
			FROM loctransfers";

	$Result = DB_query_od($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$PeriodAlreadyPurged = $MyRow['purgedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('loctransfers table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("loctransfers table already purged until : " . $PeriodAlreadyPurged);

	if ($PurgeLoctransfersObsoletes == "Y"){
		// select the webERP stockmoves table to be copied into olddata DB
		$SQL = "SELECT loctransferid,
						reference,
						stockid,
						shipqty,
						recqty,
						shipdate,
						recdate,
						shiploc,
						recloc
				FROM loctransfers
				WHERE stockid IN (SELECT stockid FROM stockmaster WHERE discontinued = 1)";
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
								'" . $MyRow['reference'] . "',
								'" . $MyRow['stockid'] . "',
								'" . $MyRow['shipqty'] . "',
								'" . $MyRow['recqty'] . "',
								'" . $MyRow['shipdate'] . "',
								'" . $MyRow['recdate'] . "',
								'" . $MyRow['shiploc'] . "',
								'" . $MyRow['recloc'] . "')";
				$ResultInsert = DB_query_od($SQLInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of loctransfers table");
			
			$SQLDelete = "DELETE FROM loctransfers 
							WHERE stockid IN (SELECT stockid FROM stockmaster WHERE discontinued = 1)";
			$ResultDelete = DB_query($SQLDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted loctransfers records in webERP production DB");

		}
	}else{
		prnMsg("locatransfers: Nothing to purge", 'warn');
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on loctransfers in webERP production DB
	$SQL = "SELECT COUNT(*) AS endrecords
			FROM loctransfers";
	$Result = DB_query($SQL);	
	if (DB_num_rows($Result) != 0){
		$MyRow = DB_fetch_array($Result);
		$EndRecords = $MyRow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('loctransfers table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved', 'success');
}


?>