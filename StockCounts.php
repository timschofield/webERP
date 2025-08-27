<?php

require(__DIR__ . '/includes/session.php');

ob_start();

$Title = __('Stock Check Sheets Entry');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

echo '<form name="EnterCountsForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
	__('Inventory Adjustment') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['Action']) AND !isset($_GET['Action'])) {
	$_GET['Action'] = 'Enter';
}
if (isset($_POST['Action'])) {
	$_GET['Action'] = $_POST['Action'];
}

if ($_GET['Action']!='View' AND $_GET['Action']!='Enter'){
	$_GET['Action'] = 'Enter';
}

echo '<table class="selection"><tr>';
if ($_GET['Action']=='View'){
	echo '<td><a href="' . $RootPath . '/StockCounts.php?&amp;Action=Enter">' . __('Resuming Entering Counts') . '</a> </td><td>' . __('Viewing Entered Counts') . '</td>';
} else {
	echo '<td>' . __('Entering Counts')  . '</td><td> <a href="' . $RootPath . '/StockCounts.php?&amp;Action=View">' . __('View Entered Counts') . '</a></td>';
}
echo '</tr></table><br />';

$FieldHeadings = array(
	'StockCode',       	//  0 'STOCKCODE',
	'QtyCounted',	 	//  1 'QTYCOUNTED',
	'Reference'      	//  2 'REFERENCE'
);

if (isset($_GET['gettemplate'])) //download an import template
{

	// clean up any previous outputs
	ob_clean();

	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");

	// disposition / encoding on response body
	header("Content-Disposition: attachment; filename=ImportTemplate.csv");
	header("Content-Transfer-Encoding: binary");

	echo '"' . implode('","',$FieldHeadings) . '"';

	// exit cleanly to prevent any unwanted outputs
	exit();
} else {
	ob_end_flush();
}

if ($_GET['Action'] == 'Enter') {

	if (isset($_POST['EnterCounts'])){

		$Added=0;
		$Counter = isset($_POST['RowCount'])?$_POST['RowCount'] : 10; // Arbitrary number of 10 hard coded as default as originally used - should there be a setting?
			for ($i=1;$i<=$Counter;$i++){
			$InputError =false; //always assume the best to start with

			$Quantity = 'Qty_' . $i;
			$BarCode = 'BarCode_' . $i;
			$StockID = 'StockID_' . $i;
			$Reference = 'Ref_' . $i;

			if (strlen($_POST[$BarCode])>0){
				$SQL = "SELECT stockmaster.stockid
								FROM stockmaster
								WHERE stockmaster.barcode='". $_POST[$BarCode] ."'";

				$ErrMsg = __('Could not determine if the part being ordered was a kitset or not because');
				$KitResult = DB_query($SQL, $ErrMsg);
				$MyRow=DB_fetch_array($KitResult);

				$_POST[$StockID] = strtoupper($MyRow['stockid']);
			}

			if (mb_strlen($_POST[$StockID])>0){
				if (!is_numeric($_POST[$Quantity])){
					$InputError=true;
				}
			$SQL = "SELECT stockid FROM stockcheckfreeze WHERE stockid='" . $_POST[$StockID] . "'";
				$Result = DB_query($SQL);
				if (DB_num_rows($Result)==0){
					prnMsg( __('The stock code entered on line') . ' ' . $i . ' ' . __('is not a part code that has been added to the stock check file') . ' - ' . __('the code entered was') . ' ' . $_POST[$StockID] . '. ' . __('This line will have to be re-entered'),'warn');
					$InputError = true;
				}

				if ($InputError==false){
					$Added++;
					$SQL = "INSERT INTO stockcounts (stockid,
									loccode,
									qtycounted,
									reference)
								VALUES ('" . $_POST[$StockID] . "',
									'" . $_POST['Location'] . "',
									'" . $_POST[$Quantity] . "',
									'" . $_POST[$Reference] . "')";

					$ErrMsg = __('The stock count line number') . ' ' . $i . ' ' . __('could not be entered because');
					$EnterResult = DB_query($SQL, $ErrMsg);
				}
			}
		} // end of loop
		prnMsg($Added . __(' Stock Counts Entered'), 'success' );
		unset($_POST['EnterCounts']);
	} // end of if enter counts button hit
	else if(isset($_FILES['userfile']) and $_FILES['userfile']['name'])
	{
		//initialize
		$FieldTarget = count($FieldHeadings);
		$InputError = 0;

		//check file info
		$FileName = $_FILES['userfile']['name'];
		$TempName  = $_FILES['userfile']['tmp_name'];
		$FileSize = $_FILES['userfile']['size'];

		//get file handle
		$FileHandle = fopen($TempName, 'r');

		//get the header row
		$HeadRow = fgetcsv($FileHandle, 10000, ",",'"');  // Modified to handle " "" " enclosed csv - useful if you need to include commas in your text descriptions

		//check for correct number of fields
		if ( count($HeadRow) != count($FieldHeadings) ) {
			prnMsg(__('File contains '. count($HeadRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		//test header row field name and sequence
		$Head = 0;
		foreach ($HeadRow as $HeadField) {
			if ( mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head]) ) {
				prnMsg(__('File contains incorrect headers '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$Head]). '. Try downloading a new template.'),'error');  //Fixed $FieldHeadings from $Headings
				fclose($FileHandle);
				include('includes/footer.php');
				exit();
			}
			$Head++;
		}

		//start database transaction
		DB_Txn_Begin();

		//loop through file rows
		$Row = 1;
		while ( ($MyRow = fgetcsv($FileHandle, 10000, ",")) !== false ) {

			//check for correct number of fields
			$FieldCount = count($MyRow);
			if ($FieldCount != $FieldTarget){
				prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
				fclose($FileHandle);
				include('includes/footer.php');
				exit();
			}

			// cleanup the data (csv files often import with empty strings and such)
			$StockID = mb_strtoupper($MyRow[0]);
			foreach ($MyRow as &$Value) {
				$Value = trim($Value);
			}

			//first off check if the item is in freeze
			$SQL = "SELECT stockid FROM stockcheckfreeze WHERE stockid='" . $StockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result)==0){
				$InputError = 1;
				prnMsg( __('Stock item '. $StockID. ' is not a part code that has been added to the stock check file'),'warn');
			}

			//next validate inputs are sensible
			if (mb_strlen($MyRow[2]) >20) {
				$InputError = 1;
				prnMsg(__('The reference field must be 20 characters or less long'),'error');
			}
			else if (!is_numeric($MyRow[1])) {
				$InputError = 1;
				prnMsg(__('The quantity counted must be numeric') ,'error');
			}
			else if ($MyRow[1] < 0) {
				$InputError = 1;
				prnMsg(__('The quantity counted must be zero or a positive number'),'error');
			}

			if ($InputError !=1){

				//attempt to insert the stock item
				$SQL = "INSERT INTO stockcounts (stockid,
									loccode,
									qtycounted,
									reference)
								VALUES ('" . $MyRow[0] . "',
									'" . $_POST['Location'] . "',
									'" . $MyRow[1] . "',
									'" . $MyRow[2] . "')";

				$ErrMsg = __('The stock count line number') . ' ' . $Row . ' ' . __('could not be entered because');
				$EnterResult = DB_query($SQL, $ErrMsg, '', true);

				if (DB_error_no() != 0) {
					$InputError = 1;
					prnMsg(__($EnterResult),'error');
				}
			}

			if ($InputError == 1) { //this row failed so exit loop
				break;
			}
			$Row++;
		}

		if ($InputError == 1) { //exited loop with errors so rollback
			prnMsg(__('Failed on row '. $Row. '. Batch import has been rolled back.'),'error');
			DB_Txn_Rollback();
		} else { //all good so commit data transaction
			DB_Txn_Commit();
			prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');
		}

		fclose($FileHandle);
	} // end of if import file button hit

	$CatsResult = DB_query("SELECT DISTINCT stockcategory.categoryid,
								categorydescription
						FROM stockcategory INNER JOIN stockmaster
							ON stockcategory.categoryid=stockmaster.categoryid
							INNER JOIN stockcheckfreeze
							ON stockmaster.stockid=stockcheckfreeze.stockid");

	if (DB_num_rows($CatsResult) ==0) {
		prnMsg(__('The stock check sheets must be run first to create the stock check. Only once these are created can the stock counts be entered. Currently there is no stock check to enter counts for'),'error');
		echo '<div class="center"><a href="' . $RootPath . '/StockCheck.php">' . __('Create New Stock Check') . '</a></div>';
	} else {
		echo '<table cellpadding="2" class="selection">';
		echo '<tr>
				<th colspan="3">' .__('Stock Check Counts at Location') . ':<select name="Location">';
		$SQL = "SELECT locations.loccode, locationname FROM locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
		$Result = DB_query($SQL);

		while ($MyRow=DB_fetch_array($Result)){

			if (isset($_POST['Location']) AND $MyRow['loccode']==$_POST['Location']){
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}
		echo '</select>&nbsp;<input type="submit" name="EnterByCat" value="' . __('Enter By Category') . '" /><select name="StkCat" onChange="ReloadForm(EnterCountsForm.EnterByCat)" >';

		echo '<option value="">' . __('Not Yet Selected') . '</option>';

		while ($MyRow=DB_fetch_array($CatsResult)){
			if ($_POST['StkCat']==$MyRow['categoryid']) {
				echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
			}
		}
		echo '</select></th></tr>';

		echo '<tr>
				<td></td><td>OR</td>
			</tr>
			<tr>
				<th colspan="3">
					<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
					' . __('Upload file') . ': <input name="userfile" type="file" />
					<input type="submit" value="' . __('Send File') . '" />
				</th>
				<td><a href="' . $RootPath . '/StockCounts.php?gettemplate=1">Get Import Template</a></td>
			</tr>
			<tr><td></td></tr>';

		if (isset($_POST['EnterByCat'])){

			$StkCatResult = DB_query("SELECT categorydescription FROM stockcategory WHERE categoryid='" . $_POST['StkCat'] . "'");
			$StkCatRow = DB_fetch_row($StkCatResult);

			echo '<tr>
					<th colspan="4">' . __('Entering Counts For Stock Category') . ': ' . $StkCatRow[0] . '</th>
				</tr>
				<tr>
					<th>' . __('Stock Code') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Quantity') . '</th>
					<th>' . __('Reference') . '</th>
				</tr>';
			$StkItemsResult = DB_query("SELECT stockcheckfreeze.stockid,
												description
										FROM stockcheckfreeze INNER JOIN stockmaster
										ON stockcheckfreeze.stockid=stockmaster.stockid
										WHERE categoryid='" . $_POST['StkCat'] . "' AND loccode = '" . $_POST['Location'] . "'
										ORDER BY stockcheckfreeze.stockid");

			$RowCount=1;
			while ($StkRow = DB_fetch_array($StkItemsResult)) {
				echo '<tr>
						<td><input type="hidden" name="StockID_' . $RowCount . '" value="' . $StkRow['stockid'] . '" />' . $StkRow['stockid'] . '</td>
						<td>' . $StkRow['description'] . '</td>
						<td><input type="text" name="Qty_' . $RowCount . '" maxlength="10" size="10" /></td>
						<td><input type="text" name="Ref_' . $RowCount . '" maxlength="20" size="20" /></td>
					</tr>';
				$RowCount++;
			}

		} else {
			echo '<tr>
					<th>' . __('Bar Code') . '</th>
					<th>' . __('Stock Code') . '</th>
					<th>' . __('Quantity') . '</th>
					<th>' . __('Reference') . '</th>
				</tr>';

			for ($RowCount=1;$RowCount<=10;$RowCount++){

				echo '<tr>
						<td><input type="text" name="BarCode_' . $RowCount . '" maxlength="20" size="20" /></td>
						<td><input type="text" name="StockID_' . $RowCount . '" maxlength="20" size="20" /></td>
						<td><input type="text" name="Qty_' . $RowCount . '" maxlength="10" size="10" /></td>
						<td><input type="text" name="Ref_' . $RowCount . '" maxlength="20" size="20" /></td>
					</tr>';

			}
		}

		echo '</table>
				<br />
				<div class="centre">
					<input type="hidden" name="RowCount" value="' .$RowCount . '" />
					<input type="submit" name="EnterCounts" value="' . __('Enter Above Counts') . '" />
				</div>';
	} // there is a stock check to enter counts for
//END OF action=ENTER
} elseif ($_GET['Action']=='View'){

	if (isset($_POST['DEL']) AND is_array($_POST['DEL']) ){
		foreach ($_POST['DEL'] as $id=>$val){
			if ($val == 'on'){
				$id = (int)$id;
				$SQL = "DELETE FROM stockcounts WHERE id='".$id."'";
				$ErrMsg = __('Failed to delete StockCount ID #').' '.$i;
				$EnterResult = DB_query($SQL, $ErrMsg);
				prnMsg( __('Deleted Id #') . ' ' . $id, 'success');
			}
		}
	}

	//START OF action=VIEW
	$SQL = "select stockcounts.*,
					canupd from stockcounts
					INNER JOIN locationusers ON locationusers.loccode=stockcounts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$Result = DB_query($SQL);
	echo '<input type="hidden" name="Action" value="View" />';
	echo '<table cellpadding="2" class="selection">';
	echo '<tr>
			<th>' . __('Stock Code') . '</th>
			<th>' . __('Location') . '</th>
			<th>' . __('Qty Counted') . '</th>
			<th>' . __('Reference') . '</th>
			<th>' . __('Delete?') . '</th></tr>';
	while ($MyRow=DB_fetch_array($Result)){
		echo '<tr>
			<td>'.$MyRow['stockid'].'</td>
			<td>'.$MyRow['loccode'].'</td>
			<td>'.$MyRow['qtycounted'].'</td>
			<td>'.$MyRow['reference'].'</td>
			<td>';
		if ($MyRow['canupd']==1) {
			echo '<input type="checkbox" name="DEL[' . $MyRow['id'] . ']" maxlength="20" size="20" />';

		}
		echo '</td></tr>';

	}
	echo '</table><br /><div class="centre"><input type="submit" name="SubmitChanges" value="' . __('Save Changes') . '" /></div>';

//END OF action=VIEW
}

echo '</div>
      </form>';
include('includes/footer.php');
