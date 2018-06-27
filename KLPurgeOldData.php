<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/OldDataConnectDB.php');

$Title = _('Purge Old Data');

include('includes/header.php');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['PurgeGltransPeriod'], $_POST['PurgeStockmovesPrd'], $_POST['PurgeLocTransfersRecdate'], $db);
} else {
	display($Title, $db);
}

include('includes/footer.php');


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $PurgeGltransPeriod, $PurgeStockmovesPrd, $PurgeLocTransfersRecdate, &$db) {
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	PurgetableGltrans($PurgeGltransPeriod);
	PurgetableStockmoves($PurgeStockmovesPrd);
	PurgetableStockmovestaxes($PurgeStockmovesPrd);

} // End of function submit()


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
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

	$sql = "SELECT gltransperiod,
					stockmovesprd,
					loctransfersrecdate
			FROM klolddatapurged";
	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);
	$AlreadyPurgedGltransPeriod = $myrow['gltransperiod'];
	$AlreadyPurgedStockmovesPrd = $myrow['stockmovesprd'];
	$AlreadyPurgedLocTransfersRecdate = $myrow['loctransfersrecdate'];

	echo '<table class="selection">';
	
	echo '<tr>
			<td>' . _('Purge gltrans table older or equal than:').'</td>
			<td><select required="required" name="PurgeGltransPeriod">';
	$sql = "SELECT periodno, 
				   lastdate_in_period 
			FROM periods
			WHERE periodno >= " . $AlreadyPurgedGltransPeriod . "
			ORDER BY periodno DESC";
	$Periods = DB_query($sql);
	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $myrow['periodno']== $AlreadyPurgedGltransPeriod){
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . ConvertSQLDate($myrow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . ConvertSQLDate($myrow['lastdate_in_period'])  . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Purge stockmoves table older or equal than:').'</td>
			<td><select required="required" name="PurgeStockmovesPrd">';
	$sql = "SELECT periodno, 
				   lastdate_in_period 
			FROM periods
			WHERE periodno >= " . $AlreadyPurgedStockmovesPrd . "
			ORDER BY periodno DESC";
	$Periods = DB_query($sql);
	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $myrow['periodno']== $AlreadyPurgedStockmovesPrd){
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . ConvertSQLDate($myrow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . ConvertSQLDate($myrow['lastdate_in_period'])  . '</option>';
		}
	}
	echo '</select></td></tr>';

/*	echo '<tr>
			<td>' . _('Purge loctransfers table older or equal than') . '</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="PurgeLocTransfersRecdate" size="10" maxlength="10" value="' . ConvertSQLDate($AlreadyPurgedLocTransfersRecdate) . '" /></td>
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
	$sql = "SELECT COUNT(*) AS startrecords
			FROM gltrans";

	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$StartRecords = $myrow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	// search for the newest date already purged in olddata database table
	$PeriodAlreadyPurged = -99;
	$sql = "SELECT MAX(periodno) AS purgedperiod
			FROM gltrans";

	$result = DB_query_od($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$PeriodAlreadyPurged = $myrow['purgedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('gltrans table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("gltrans table already purged until period: " . $PeriodAlreadyPurged);
	prnMsg('Purge gltrans older or equal than period '. $PurgeToPeriod);

	if ( $PeriodAlreadyPurged < $PurgeToPeriod){
		// select the webERP gltrans table to be copied into olddata DB
		$sql = "SELECT counterindex,
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
		$result = DB_query($sql);	
		$ErrMsg = _('An error occurred in inserting the gltrans record');
		$DbgMsg = _('The SQL that was used to insert the gltrans record was');		
		if (DB_num_rows($result) != 0){
			$RecordCounter = 0;
			while ($myrow = DB_fetch_array($result)) {
				$sqlInsert = "INSERT INTO gltrans 
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
									jobref,
									tag
								) VALUES (
								'" . $myrow['counterindex'] . "',
								'" . $myrow['type'] . "',
								'" . $myrow['typeno'] . "',
								'" . $myrow['chequeno'] . "',
								'" . $myrow['trandate'] . "',
								'" . $myrow['periodno'] . "',
								'" . $myrow['account'] . "',
								'" . $myrow['narrative'] . "',
								'" . $myrow['amount'] . "',
								'" . $myrow['posted'] . "',
								'" . $myrow['jobref'] . "',
								'" . $myrow['tag'] . "')";
				$resultInsert = DB_query_od($sqlInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of gltrans table");
			
			// Now calculate consolidated values for each period and account, delete the details and write the consolidated value on webERP database
			$sql = "SELECT periodno,
							account,
							MAX(trandate) AS maxdate,
							SUM(amount) AS consolidated
					FROM gltrans
					WHERE periodno > " . $PeriodAlreadyPurged . "
						AND periodno <= " . $PurgeToPeriod . "
					GROUP BY periodno,
							account";
			$result = DB_query($sql);
			if (DB_num_rows($result) != 0){
				while ($mycosolidatedrow = DB_fetch_array($result)) {
					
					$sqlDelete = "DELETE FROM gltrans 
									WHERE periodno = ".$mycosolidatedrow['periodno']."
										AND account = '".$mycosolidatedrow['account']."'";
					$resultDelete = DB_query($sqlDelete,$ErrMsg,$DbgMsg);
					
					$Typeno = GetNextTransNo(1000, $db);
					$sqlInsert = "INSERT INTO gltrans 
										(type,
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
									) VALUES (
									'1000',
									'" . $Typeno . "',
									'0',
									'" . $mycosolidatedrow['maxdate'] . "',
									'" . $mycosolidatedrow['periodno'] . "',
									'" . $mycosolidatedrow['account'] . "',
									'CONSOLIDATED ACCOUNTING',
									'" . $mycosolidatedrow['consolidated'] . "',
									'1',
									'',
									'0')";
					$resultInsert = DB_query($sqlInsert,$ErrMsg,$DbgMsg);
				}
				prnMsg("Inserted consolidated accounting records");
				$sqlUpdate = "UPDATE klolddatapurged SET gltransperiod = ".$PurgeToPeriod."";
				$resultUpdate = DB_query($sqlUpdate,$ErrMsg,$DbgMsg);
				prnMsg("Updated klolsdatapurged records");
			}
		}
	}else{
		prnMsg("Nothing to purge");
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on gltrans in webERP production DB
	$sql = "SELECT COUNT(*) AS endrecords
			FROM gltrans";
	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$EndRecords = $myrow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('gltrans table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved');
}

function PurgetableStockmoves($PurgeToPeriod){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$sql = "SELECT COUNT(*) AS startrecords
			FROM stockmoves";

	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$StartRecords = $myrow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	// search for the newest date already purged in olddata database table
	$PeriodAlreadyPurged = -99;
	$sql = "SELECT MAX(prd) AS purgedperiod
			FROM stockmoves";

	$result = DB_query_od($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$PeriodAlreadyPurged = $myrow['purgedperiod'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('stockmoves table contains '. locale_number_format($StartRecords) . ' records');
	prnMsg("stockmoves table already purged until period: " . $PeriodAlreadyPurged);
	prnMsg('Purge stockmoves older or equal than period '. $PurgeToPeriod);

//	if ( $PeriodAlreadyPurged < $PurgeToPeriod){
	if ( TRUE){
		// select the webERP stockmoves table to be copied into olddata DB
		$sql = "SELECT stkmoveno,
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
		$result = DB_query($sql);	
		$ErrMsg = _('An error occurred in inserting the stockmoves record');
		$DbgMsg = _('The SQL that was used to insert the stockmoves record was');		
		if (DB_num_rows($result) != 0){
			$RecordCounter = 0;
			while ($myrow = DB_fetch_array($result)) {
				$sqlInsert = "INSERT INTO stockmoves 
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
								'" . $myrow['stkmoveno'] . "',
								'" . $myrow['stockid'] . "',
								'" . $myrow['type'] . "',
								'" . $myrow['transno'] . "',
								'" . $myrow['loccode'] . "',
								'" . $myrow['trandate'] . "',
								'" . $myrow['userid'] . "',
								'" . $myrow['debtorno'] . "',
								'" . $myrow['branchcode'] . "',
								'" . $myrow['price'] . "',
								'" . $myrow['prd'] . "',
								'" . $myrow['reference'] . "',
								'" . $myrow['qty'] . "',
								'" . $myrow['discountpercent'] . "',
								'" . $myrow['standardcost'] . "',
								'" . $myrow['show_on_inv_crds'] . "',
								'" . $myrow['newqoh'] . "',
								'" . $myrow['hidemovt'] . "',
								'" . $myrow['narrative'] . "')";
				$resultInsert = DB_query_od($sqlInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of stockmoves table");
			
			$sqlDelete = "DELETE FROM stockmoves 
							WHERE prd <= " . $PurgeToPeriod . "";
			$resultDelete = DB_query($sqlDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted stockmoves records in webERP production DB");
			
			$sqlUpdate = "UPDATE klolddatapurged SET stockmovesprd = ".$PurgeToPeriod."";
			$resultUpdate = DB_query($sqlUpdate,$ErrMsg,$DbgMsg);
			prnMsg("Updated klolsdatapurged records");
		}
	}else{
		prnMsg("Nothing to purge");
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on stockmoves in webERP production DB
	$sql = "SELECT COUNT(*) AS endrecords
			FROM stockmoves";
	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$EndRecords = $myrow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('stockmoves table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved');
}

function PurgetableStockmovestaxes(){
	DB_Txn_Begin();
	$ErrorsFound = FALSE; // hope for the best
	
	// count how many records are on stockmoves in webERP production DB
	$sql = "SELECT COUNT(*) AS startrecords
			FROM stockmovestaxes";
	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$StartRecords = $myrow['startrecords'];
	}else{
		$ErrorsFound = TRUE;
	}

	prnMsg('stockmovestaxes table contains '. locale_number_format($StartRecords) . ' records');

	if ( TRUE){
		// select the webERP stockmoves table to be copied into olddata DB
		$sql = "SELECT stkmoveno,
						taxauthid,
						taxrate,
						taxontax,
						taxcalculationorder
				FROM stockmovestaxes
				WHERE stkmoveno NOT IN (SELECT stkmoveno FROM stockmoves)";
		$result = DB_query($sql);	
		$ErrMsg = _('An error occurred in inserting the stockmovestaxes record');
		$DbgMsg = _('The SQL that was used to insert the stockmovestaxes record was');		
		if (DB_num_rows($result) != 0){
			$RecordCounter = 0;
			while ($myrow = DB_fetch_array($result)) {
				$sqlInsert = "INSERT INTO stockmovestaxes 
									( stkmoveno,
									taxauthid,
									taxrate,
									taxontax,
									taxcalculationorder
								) VALUES (
								'" . $myrow['stkmoveno'] . "',
								'" . $myrow['taxauthid'] . "',
								'" . $myrow['taxrate'] . "',
								'" . $myrow['taxontax'] . "',
								'" . $myrow['taxcalculationorder'] . "')";
				$resultInsert = DB_query_od($sqlInsert,$ErrMsg,$DbgMsg);
				$RecordCounter++;
			}
			prnMsg("Copied into OldData DB ". locale_number_format($RecordCounter) . " records of stockmovestaxes table");
			
			$sqlDelete = "DELETE FROM stockmovestaxes 
							WHERE stkmoveno NOT IN (SELECT stkmoveno FROM stockmoves)";
			$resultDelete = DB_query($sqlDelete,$ErrMsg,$DbgMsg);
			prnMsg("Deleted stockmoves records in webERP production DB");
		}
	}else{
		prnMsg("Nothing to purge");
		$ErrorsFound = TRUE;
	}

	if (!$ErrorsFound){
		$Result = DB_Txn_Commit();
	}else{
		$Result = DB_Txn_Rollback();

	}

	// count how many records are on stockmoves in webERP production DB
	$sql = "SELECT COUNT(*) AS endrecords
			FROM stockmovestaxes";
	$result = DB_query($sql);	
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$EndRecords = $myrow['endrecords'];
	}else{
		$ErrorsFound = TRUE;
	}
	prnMsg('stockmovestaxes table now contains '. locale_number_format($EndRecords) . ' records. '. locale_number_format($StartRecords - $EndRecords) . ' records saved');
}


?>