<?php
/* $Id: ImportStockQuantities.php 5296 2012-04-29 15:28:19Z daintree $*/
$LocationCode = 'CA';

include('includes/session.inc');
$title = _('Import Item Quantities From CSV');
include('includes/header.inc');

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed

$FieldNames = array(
	'Location #',   //  0 'Location #',
	'T #',     		//  1 'T #',
	'BOX #', 		//  2 'BOX #',
	'PART NUMBER',  //  3 'PART NUMBER',
	'MAKE',         //  4 'MAKE',
	'FPD Serial #', //  5 'FPD Serial #',
	'MFG SERIAL #', //  6 'EOQ',
	'COND:',    	//  7 'COND:',
	'QTY:',      	//  8 'QTY:',
	'NOTES:',      	//  9 'NOTES:',
	'DATE:',      	// 10 'DATE:',
	'Other notes:' // 11 'Other notes:',
);

if ($_FILES['userfile']['name']) { //start file processing

	//initialize
	$AllowType='text/csv';
	$fieldTarget = 12;
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$tmpName  = $_FILES['userfile']['tmp_name'];
	$fileSize = $_FILES['userfile']['size'];
	$FileType = $_FILES['userfile']['type'];
	if ($FileType != $AllowType) {
		prnMsg (_('File has type '. $FileType. ', but only '. $AllowType. ' is allowed.'),'error');
		include('includes/footer.inc');
		exit;
	}

	//get file handle
	$FileHandle = fopen($tmpName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ",");

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldNames) ) {
		prnMsg (_('File contains '. count($HeadRow). ' columns, expected '. count($FieldNames). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include('includes/footer.inc');
		exit;
	}

	//test header row field name and sequence
	$Head = 0;
	foreach ($HeadRow as $HeadField) {
		if ( mb_strtoupper($HeadField) != mb_strtoupper($FieldNames[$Head]) ) {
			prnMsg (_('File contains incorrect headers ('. mb_strtoupper($HeadField). ' != '. mb_strtoupper($Header[$Head]). '. Try downloading a new template.'),'error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}
		$Head++;
	}

	/* OK now create a temporary table of the items in the table */
	$result = DB_query("DROP TABLE IF EXISTS tempitemqty",$db);
	$sql = "CREATE TEMPORARY TABLE  (stockid char(20),
									quantity double) DEFAULT CHARSET=utf8";
	$ErrMsg = _('The SQL to to create temporary item quantity table failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	//loop through file rows
	$row = 1;
	while ( ($myrow = fgetcsv($FileHandle, 10000, ",")) !== FALSE ) {

		//check for correct number of fields
		$FieldCount = count($myrow);
		if ($FieldCount != $fieldTarget){
			prnMsg ($fieldTarget. ' fields required, '. $FieldCount. ' fields received','error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		if (mb_strtoupper($myrow[7]) =='NEW'){
			$Condition = 'N';
		} else {
			$Condition = mb_strtoupper($myrow[7]);
		}
		$StockID = $Condition  . trim(mb_strtoupper($myrow[3]));
		if (is_numeric($myrow[8])){
			//attempt to insert the stock item
			$sql = "INSERT INTO tempitemqty (stockid,
											quantity)
					VALUES ('$StockID',
						'" . $myrow[7]	. "')";
			$ErrMsg =  _('The item could not be added because');
			$DbgMsg = _('The SQL that was used to add the item failed was');
			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
		} else {
			prnMsg('The quantity field for item ' . $StockID . ' was not numeric - it has not been added','error'); 
		}
	} //end loop around lines of csv file

	fclose($FileHandle);
	
	/*OK now the quantites are imported into the tempitemqty table - need to figure out the adjustments */
	
	$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
	$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	$AdjustmentNumber = GetNextTransNo(17,$db);

	$result = DB_query("SELECT stockid, SUM(quantity) as totalquantity FROM tempitemqty GROUP BY stockid",$db);
	
	DB_Txn_Begin($db);
	
	while ($myrow = DB_fetch_array($result)){ //loop through the item totals from the csv

		$ItemQtyResult = DB_query("SELECT quantity FROM locstock WHERE stockid='" . $myrow['stockid'] . "' AND loccode='" . $LocationCode . "'",$db);	
		if (DB_num_rows($ItemQtyResult)==1){
			$LocQtyRow = DB_fetch_row($ItemQtyResult);
			$StockQtyDifference = $$LocQtyRow[0] - $myrow['totalquantity'];
			$QtyOnHandPrior = $LocQtyRow[0];
			
			if ($StockQtyDifference !=0){ // only adjust stock if there is an adjustment to make!!
		
				$SQL = "INSERT INTO stockmoves (stockid,
								type,
								transno,
								loccode,
								trandate,
								prd,
								reference,
								qty,
								newqoh)
						VALUES ('" . $myrow['stockid'] . "',
							17,
							'" . $AdjustmentNumber . "',
							'" . $myrow['loccode'] . "',
							'" . $SQLAdjustmentDate . "',
							'" . $PeriodNo . "',
							'" . _('Inventory Check') . "',
							'" . $StockQtyDifference . "',
							'" . ($QtyOnHandPrior + $StockQtyDifference) . "'
						)";
	
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	
				$SQL = "UPDATE locstock
						SET quantity = quantity + '" . $StockQtyDifference . "'
						WHERE stockid='" . $myrow['stockid'] . "'
						AND loccode='" . $LocationCode . "'";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	
				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $myrow['standardcost'] > 0){
	
					$StockGLCodes = GetStockGLCode($myrow['stockid'],$db);
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
	
					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" .$AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" .  $StockGLCodes['adjglact'] . "',
								'" . $myrow['standardcost'] * -($StockQtyDifference) . "',
								'" . $myrow['stockid'] . " x " . $StockQtyDifference . " @ " . $myrow['standardcost'] . " - " . _('Inventory Check') . "')";
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
	
					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" .$AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" .  $StockGLCodes['stockact'] . "',
								'" . $myrow['standardcost'] * $StockQtyDifference . "',
								'" . $myrow['stockid'] . " x " . $StockQtyDifference . " @ " . $myrow['standardcost'] . " - " . _('Inventory Check') . "')";
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	
				} //END INSERT GL TRANS
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Unable to COMMIT transaction while adjusting stock in StockCheckAdjustmet report');
				DB_Txn_Commit($db);
	
			} // end if $StockQtyDifference !=0




	

} elseif ( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldNames). '"<br /><br /><br />';

} else { //show file upload form

	echo '
		<br />
		<a href="ImportStockQuantities.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="' $_SERVER['PHP_SELF'] . '" method="post">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('Send File') . '" />
        </div>
		</form>';

}


include('includes/footer.inc');
?>