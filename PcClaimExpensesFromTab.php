<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Claim Petty Cash Expenses From Tab');
$ViewTopic = 'PettyCash';
$BookMark = 'ExpenseClaim';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['Date'])){$_POST['Date'] = ConvertSQLDate($_POST['Date']);}

if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}
if (isset($_POST['SelectedIndex'])) {
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])) {
	$SelectedIndex = $_GET['SelectedIndex'];
}
if (isset($_POST['Days'])) {
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])) {
	$Days = filter_number_format($_GET['Days']);
}
if (isset($_POST['Cancel'])) {
	unset($SelectedTabs);
	unset($SelectedIndex);
	unset($Days);
	unset($_POST['Amount']);
	unset($_POST['Purpose']);
	unset($_POST['Notes']);
	unset($_FILES['Receipt']);
}
if (isset($_POST['Process'])) {
	if ($_POST['SelectedTabs'] == '') {
		prnMsg(__('You have not selected a tab to claim the expenses on'), 'error');
		unset($SelectedTabs);
	}
}
if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(__('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}
//Define receipt attachment upload functions and variables which are used in various places within script
$ReceiptSupportedExt = array('png','jpg','jpeg','pdf','doc','docx','xls','xlsx'); //Supported file extensions
$ReceiptDir = $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/expenses_receipts/'; //Receipts upload directory
if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if ($_POST['SelectedExpense'] == '') {
		$InputError = 1;
		prnMsg(__('You have not selected an expense to claim on this tab'), 'error');
	} elseif ($_POST['Amount'] == 0) {
		$InputError = 1;
		prnMsg(__('The amount must be greater than 0'), 'error');
	}
	if (!is_date($_POST['Date'])) {
		$InputError = 1;
		prnMsg(__('The date input is not in the correct format'), 'error');
	}
	if (isset($SelectedIndex) and $InputError != 1) { //Edit
		$SQL = "UPDATE pcashdetails
			SET date = '" . FormatDateForSQL($_POST['Date']) . "',
				codeexpense = '" . $_POST['SelectedExpense'] . "',
				amount = '" . -filter_number_format($_POST['Amount']) . "',
				purpose='" . $_POST['Purpose'] . "',
				notes = '" . $_POST['Notes'] . "'
			WHERE counterindex = '" . $SelectedIndex . "'";
		$Msg = __('The expense record on tab') . ' ' . $SelectedTabs . ' ' . __('has been updated');
		$Result = DB_query($SQL);
		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "UPDATE pcashdetailtaxes SET pccashdetail='" . $_POST['PcCashDetail' . $Index] . "',
													calculationorder='" . $_POST['CalculationOrder' . $Index] . "',
													description='" . $_POST['Description' . $Index] . "',
													taxauthid='" . $_POST['TaxAuthority' . $Index] . "',
													purchtaxglaccount='" . $_POST['TaxGLAccount' . $Index] . "',
													taxontax='" . $_POST['TaxOnTax' . $Index] . "',
													taxrate='" . $_POST['TaxRate' . $Index] . "',
													amount='" . -$_POST['TaxAmount' . $Index] . "'
												WHERE counterindex='" . $Index ."'";
				$Result = DB_query($SQL);
			}
		}
		$SQL = "DELETE FROM pctags WHERE pccashdetail='" . $SelectedIndex . "'";
		$Result = DB_query($SQL);
		foreach ($_POST['tag'] as $Tag) {
			$SQL = "INSERT INTO pctags (pccashdetail,
										tag)
									VALUES (
										'" . $SelectedIndex . "',
										'" . $Tag . "'
									)";
			$Result = DB_query($SQL);
		}
		if (isset($_FILES['Receipt']) and $_FILES['Receipt']['name'] != '') {
			$UploadOriginalName = $_FILES['Receipt']['name'];
			$UploadTempName = $_FILES['Receipt']['tmp_name'];
			$UploadSize = $_FILES['Receipt']['size'];
			$UploadType = $_FILES['Receipt']['type'];
			$UploadError = $_FILES['Receipt']['error'];
			$UploadTheFile = 'Yes'; //Assume all is well to start off with, but check for the worst
			$ReceiptSupportedMime = array('image/png','image/jpeg','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //list of support mime types, corresponding to the list of support file extensions in $ReceiptSupportedExt
			if ($UploadSize > ($_SESSION['MaxImageSize'] * 1024)) { //Server-side file size check. This will usually be caught by $UploadError == 2 (MAX_FILE_SIZE), but we must not trust the user.
				prnMsg(__('The uploaded file exceeds the maximum file size of') . ' ' . $_SESSION['MaxImageSize'] . 'KB', 'warn');
				$UploadTheFile = 'No';
			} elseif (!in_array($UploadType, $ReceiptSupportedMime) and $UploadError != 2) { //File type check. If $UploadError == 2, then $UploadType will be empty.
				prnMsg(__('File type not accepted. Only the following file types can be attached') . ': ' . implode(', ', $ReceiptSupportedExt), 'warn');
				$UploadTheFile = 'No';
			} elseif ($UploadError == 1 ) {  //upload_max_filesize error check
				prnMsg(__('The uploaded file exceeds the upload_max_filesize directive in php.ini. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 2 ) {  //Client-side file size error check (MAX_FILE_SIZE)
				prnMsg(__('The uploaded file exceeds the maximum file size of') . ' ' . $_SESSION['MaxImageSize'] . 'KB', 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 3 ) {  //Partial upload error check
				prnMsg( __('The uploaded file was only partially uploaded. Please try again.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 4 ) {  //No file uploaded error check
				prnMsg( __('No file was uploaded'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 5 ) {  //Undefined error check
				prnMsg( __('Undefined error'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 6 ) {  //Temp directory error check
				prnMsg( __('A necessary temporary folder is missing. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 7 ) {  //Disk write failure error check
				prnMsg( __('Cannot write file to disk. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 8 ) {  //Upload stopped by PHP extension error check
				prnMsg( __('The file upload was stopped by a PHP extension. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			}
			if ($UploadTheFile == 'Yes') { //Passed all the above validation
				$ReceiptSQL = "SELECT hashfile,
								extension
								FROM pcreceipts
								WHERE pccashdetail='" . $SelectedIndex . "'
								LIMIT 1";
					$ReceiptResult = DB_query($ReceiptSQL);
					$ReceiptRow = DB_fetch_assoc($ReceiptResult);
				if (DB_num_rows($ReceiptResult) > 0) { //If expenses record already has an uploaded receipt
					//Delete existing receipt files from directory
					$ReceiptHash = $ReceiptRow['hashfile'];
					$ReceiptExt = $ReceiptRow['extension'];
					$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
					$ReceiptPath = $ReceiptDir . $ReceiptFileName;
					unlink($ReceiptPath);
					//Upload the new receipt file.
					if (!file_exists($ReceiptDir)) { //Create the receipts directory if it doesn't already exist
					mkdir($ReceiptDir, 0775, true);
					}
					$ReceiptHash = md5(md5_file($UploadTempName) . microtime()); //MD5 hash of uploaded file with timestamp
					$ReceiptExt = strtolower(pathinfo($UploadOriginalName, PATHINFO_EXTENSION)); //Grab the file extension of the uploaded file
					$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt; //Rename the uploaded file with the expenses index number
					$ReceiptPath = $ReceiptDir . $ReceiptFileName;
					move_uploaded_file($UploadTempName, $ReceiptPath); //Move the uploaded file from the temp directory to the receipts directory
					//Update receipt file info in database
					$ReceiptSQL = "UPDATE pcreceipts SET hashfile='" . $ReceiptHash . "',
													type='" . $UploadType . "',
													extension='" . $ReceiptExt . "',
													size=" . $UploadSize . "
												WHERE pccashdetail='" . $SelectedIndex . "'";
					$ReceiptResult = DB_query($ReceiptSQL);
				} else { //If expenses record does not already have an uploaded receipt
					if (!file_exists($ReceiptDir)) { //Create the receipts directory if it doesn't already exist
					mkdir($ReceiptDir, 0775, true);
					}
					$ReceiptExt = strtolower(pathinfo($UploadOriginalName, PATHINFO_EXTENSION)); //Grab the file extension of the uploaded file
					$ReceiptHash = md5(md5_file($UploadTempName) . microtime()); //MD5 hash of uploaded file with timestamp
					$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt; //Rename the uploaded file with the expenses index number
					$ReceiptPath = $ReceiptDir . $ReceiptFileName;
					move_uploaded_file($UploadTempName, $ReceiptPath); //Move the uploaded file from the temp directory to the receipts directory
					$ReceiptSQL = "INSERT INTO pcreceipts (counterindex,
													pccashdetail,
													hashfile,
													type,
													extension,
													size
												) VALUES (
													NULL,
													'" . $SelectedIndex . "',
													'" . $ReceiptHash . "',
													'" . $UploadType . "',
													'" . $ReceiptExt . "',
													" . $UploadSize . "
													)";
					$ReceiptResult = DB_query($ReceiptSQL);
				}
			}
		}
		prnMsg($Msg, 'success');
	} elseif ($InputError != 1) {
		// First check the type is not being duplicated
		// Add new record on submit
		$SQL = "INSERT INTO pcashdetails (counterindex,
										tabcode,
										date,
										codeexpense,
										amount,
										authorized,
										posted,
										purpose,
										notes)
								VALUES (NULL,
										'" . $_POST['SelectedTabs'] . "',
										'" . FormatDateForSQL($_POST['Date']) . "',
										'" . $_POST['SelectedExpense'] . "',
										'" . -filter_number_format($_POST['Amount']) . "',
										'1000-01-01',
										0,
										'" . $_POST['Purpose'] . "',
										'" . $_POST['Notes'] . "'
										)";
		$Msg = __('The expense claim on tab') . ' ' . $_POST['SelectedTabs'] . ' ' . __('has been created');
		$Result = DB_query($SQL);
		$SelectedIndex = DB_Last_Insert_ID('pcashdetails', 'counterindex');

		foreach ($_POST['tag'] as $Tag) {
			$SQL = "INSERT INTO pctags (pccashdetail,
										tag)
									VALUES (
										'" . $SelectedIndex . "',
										'" . $Tag . "'
									)";
			$Result = DB_query($SQL);
		}

		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "INSERT INTO pcashdetailtaxes (counterindex,
														pccashdetail,
														calculationorder,
														description,
														taxauthid,
														purchtaxglaccount,
														taxontax,
														taxrate,
														amount
												) VALUES (
														NULL,
														'" . $SelectedIndex . "',
														'" . $_POST['CalculationOrder' . $Index] . "',
														'" . $_POST['Description' . $Index] . "',
														'" . $_POST['TaxAuthority' . $Index] . "',
														'" . $_POST['TaxGLAccount' . $Index] . "',
														'" . $_POST['TaxOnTax' . $Index] . "',
														'" . $_POST['TaxRate' . $Index] . "',
														'" . -$_POST['TaxAmount' . $Index] . "'
												)";
				$Result = DB_query($SQL);
			}
		}
		if (isset($_FILES['Receipt']) and $_FILES['Receipt']['name'] != '') {
			$UploadOriginalName = $_FILES['Receipt']['name'];
			$UploadTempName = $_FILES['Receipt']['tmp_name'];
			$UploadSize = $_FILES['Receipt']['size'];
			$UploadType = $_FILES['Receipt']['type'];
			$UploadError = $_FILES['Receipt']['error'];
			$UploadTheFile = 'Yes'; //Assume all is well to start off with, but check for the worst
			$ReceiptSupportedMime = array('image/png','image/jpeg','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //list of support mime types, corresponding to the list of support file extensions in $ReceiptSupportedExt
			if ($UploadSize > ($_SESSION['MaxImageSize'] * 1024)) { //Server-side file size check. This will usually be caught by $UploadError == 2 (MAX_FILE_SIZE), but we must not trust the user.
				prnMsg(__('The uploaded file exceeds the maximum file size of') . ' ' . $_SESSION['MaxImageSize'] . 'KB', 'warn');
				$UploadTheFile = 'No';
			} elseif (!in_array($UploadType, $ReceiptSupportedMime) and $UploadError != 2) { //File type check. If $UploadError == 2, then $UploadType will be empty.
				prnMsg(__('File type not accepted. Only the following file types can be attached') . ': ' . implode(', ', $ReceiptSupportedExt), 'warn');
				$UploadTheFile = 'No';
			} elseif ($UploadError == 1 ) {  //upload_max_filesize error check
				prnMsg(__('The uploaded file exceeds the upload_max_filesize directive in php.ini. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 2 ) {  //Client-side file size error check (MAX_FILE_SIZE)
				prnMsg(__('The uploaded file exceeds the maximum file size of') . ' ' . $_SESSION['MaxImageSize'] . 'KB', 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 3 ) {  //Partial upload error check
				prnMsg( __('The uploaded file was only partially uploaded. Please try again.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 4 ) {  //No file uploaded error check
				prnMsg( __('No file was uploaded'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 5 ) {  //Undefined error check
				prnMsg( __('Undefined error'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 6 ) {  //Temp directory error check
				prnMsg( __('A necessary temporary folder is missing. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 7 ) {  //Disk write failure error check
				prnMsg( __('Cannot write file to disk. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			} elseif ($UploadError == 8 ) {  //Upload stopped by PHP extension error check
				prnMsg( __('The file upload was stopped by a PHP extension. Please contact your system administrator.'), 'warn');
				$UploadTheFile ='No';
			}
			if ($UploadTheFile == 'Yes') { //Passed all the above validation
				if (!file_exists($ReceiptDir)) { //Create the receipts directory if it doesn't already exist
				mkdir($ReceiptDir, 0775, true);
				}
				$ReceiptHash = md5(md5_file($UploadTempName) . microtime()); //MD5 hash of uploaded file with timestamp
				$ReceiptExt = strtolower(pathinfo($UploadOriginalName, PATHINFO_EXTENSION)); //Grab the file extension of the uploaded file
				$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt; //Rename the uploaded file with the expenses index number
				$ReceiptPath = $ReceiptDir . $ReceiptFileName;
				move_uploaded_file($UploadTempName, $ReceiptPath); //Move the uploaded file from the temp directory to the receipts directory
				$ReceiptSQL = "INSERT INTO pcreceipts (counterindex,
												pccashdetail,
												hashfile,
												type,
												extension,
												size
											) VALUES (
												NULL,
												'" . $SelectedIndex . "',
												'" . $ReceiptHash . "',
												'" . $UploadType . "',
												'" . $ReceiptExt . "',
												" . $UploadSize . "
												)";
				$ReceiptResult = DB_query($ReceiptSQL);
			}
		}
		prnMsg($Msg, 'success');
	}
	if ($InputError != 1) {
		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Tag']);
		unset($_POST['Date']);
		unset($_POST['Purpose']);
		unset($_POST['Notes']);
		unset($_FILES['Receipt']);
	}
} elseif (isset($_GET['delete'])) {
	$ReceiptSQL = "SELECT hashfile,
					extension
					FROM pcreceipts
					WHERE pccashdetail='" . $SelectedIndex . "'
					LIMIT 1";
		$ReceiptResult = DB_query($ReceiptSQL);
		$ReceiptRow = DB_fetch_assoc($ReceiptResult);
	if (DB_num_rows($ReceiptResult) > 0) {
	//Delete receipt files from directory
	$ReceiptHash = $ReceiptRow['hashfile'];
	$ReceiptExt = $ReceiptRow['extension'];
	$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
	$ReceiptPath = $ReceiptDir . $ReceiptFileName;
	unlink($ReceiptPath);
	//Delete receipt file info from database
	$SQL = "DELETE FROM pcreceipts
			WHERE pccashdetail='" . $SelectedIndex . "'";
	$ErrMsg = __('Petty Cash Expense record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	}
	//Delete associated taxes
	$SQL = "DELETE FROM pcashdetailtaxes
			WHERE pcashdetailtaxes.pccashdetail = '" . $SelectedIndex . "'";
	$Result = DB_query($SQL, $ErrMsg);

	//Delete expenses record
	$SQL = "DELETE FROM pcashdetails
			WHERE pcashdetails.counterindex = '" . $SelectedIndex . "'";
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(__('The expense record on tab') . ' ' . $SelectedTabs . ' ' . __('has been deleted'), 'success');
	unset($_GET['delete']);
} //end of get delete
if (!isset($SelectedTabs)) {
	/* It could still be the first time the page has been run and a record has been selected for modification - SelectedTabs will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', __('Payment Entry'), '" alt="" />', ' ', $Title, '
		</p>';
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>
			<field>
				<label for="SelectedTabs">', __('Clain expenses on petty cash tab'), ':</label>
				<select required="required" name="SelectedTabs">';
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE usercode='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	echo '<option value="">', __('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';
	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', __('Accept'), '" />
			<input type="reset" name="Cancel" value="', __('Cancel'), '" />
		</div>';
	echo '</form>';
} else { // isset($SelectedTabs)
	echo '<a class="toplink" href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', __('Select another tab'), '</a>';
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', __('Petty Cash Claim Entry'), '" alt="" />', ' ', $Title, ': ', $SelectedTabs, '
		</p>';
	if (!isset($_GET['edit']) or isset($_POST['GO'])) {
		if (!isset($Days)) {
			$Days = 30;
		}
		/* Retrieve decimal places to display */
		$SQLDecimalPlaces = "SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
		$Result = DB_query($SQLDecimalPlaces);
		$MyRow = DB_fetch_array($Result);
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		//Limit expenses history to X days
		echo '<fieldset>
				<field>
					<label for="SelectedTabs">', __('Detail of Tab Movements For Last '), ':</label>
					<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
					<input type="text" class="number" name="Days" value="', $Days, '" required="required" maxlength="3" size="4" /> ', __('Days'), '
					<input type="submit" name="Go" value="', __('Go'), '" />
				</field>
			</fieldset>';
		if (isset($_POST['Cancel'])) {
			unset($_POST['SelectedExpense']);
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['Purpose']);
			unset($_POST['Notes']);
			unset($_FILES['Receipt']);
		}
		$SQL = "SELECT counterindex,
						tabcode,
						date,
						codeexpense,
						amount,
						authorized,
						posted,
						purpose,
						notes
					FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'
						AND date >=DATE_SUB(CURDATE(), INTERVAL " . $Days . " DAY)
					ORDER BY date,
							counterindex ASC";
		$Result = DB_query($SQL);

		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', __('Date of Expense'), '</th>
						<th class="SortedColumn">', __('Expense Code'), '</th>
						<th class="SortedColumn">', __('Gross Amount'), '</th>
						<th>', __('Tax'), '</th>
						<th>', __('Tax Group'), '</th>
						<th>', __('Tag'), '</th>
						<th>', __('Business Purpose'), '</th>
						<th>', __('Notes'), '</th>
						<th>', __('Receipt Attachment'), '</th>
						<th>', __('Date Authorised'), '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {
			$SQLDes = "SELECT description
						FROM pcexpenses
						WHERE codeexpense='" . $MyRow['codeexpense'] . "'";
			$ResultDes = DB_query($SQLDes);
			$Description = DB_fetch_array($ResultDes);

			if (!isset($Description[0])) {
				$ExpenseCodeDes = 'ASSIGNCASH';
			} else {
					$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];
			}

			if ($MyRow['authorized'] == '1000-01-01' or $MyRow['authorized'] == '0000-00-00') {
				$AuthorisedDate = __('Unauthorised');
			} else {
				$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
			}

			//Generate download link for expense receipt, or show text if no receipt file is found.
			$ReceiptSQL = "SELECT hashfile,
								extension
								FROM pcreceipts
								WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
					$ReceiptResult = DB_query($ReceiptSQL);
					$ReceiptRow = DB_fetch_array($ReceiptResult);
			if (DB_num_rows($ReceiptResult) > 0) { //If receipt exists in database
				$ReceiptHash = $ReceiptRow['hashfile'];
				$ReceiptExt = $ReceiptRow['extension'];
				$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
				$ReceiptPath = $ReceiptDir . $ReceiptFileName;
				$ReceiptText = '<a href="' . $ReceiptPath . '" download="ExpenseReceipt-' . mb_strtolower($SelectedTabs) . '-[' . $MyRow['date'] . ']-[' . $MyRow['counterindex'] . ']">' . __('Download attachment') . '</a>';
			} elseif ($ExpenseCodeDes == 'ASSIGNCASH') {
				$ReceiptText = '';
			} else {
				$ReceiptText = __('No attachment');
			}

			$TagSQL = "SELECT tagref, tagdescription FROM tags INNER JOIN pctags ON tags.tagref=pctags.tag WHERE pctags.pccashdetail='" . $MyRow['counterindex'] . "'";
			$TagResult = DB_query($TagSQL);
			$TagDescription = '';
			while ($TagRow = DB_fetch_array($TagResult)) {
				if ($TagRow['tagref'] == 0) {
					$TagRow['tagdescription'] = __('None');
				}
				if ($ExpenseCodeDes != 'ASSIGNCASH') {
					$TagDescription .= $TagRow['tagref'] . ' - ' . $TagRow['tagdescription'] . '</br>';
				}
			}

			$TaxesDescription = '';
			$TaxesTaxAmount = '';
			$TaxSQL = "SELECT counterindex,
								pccashdetail,
								calculationorder,
								description,
								taxauthid,
								purchtaxglaccount,
								taxontax,
								taxrate,
								amount
							FROM pcashdetailtaxes
							WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TaxResult = DB_query($TaxSQL);
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
				$TaxesDescription .= $MyTaxRow['description'] . '<br />';
				$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
			}
			if (($MyRow['authorized'] == '1000-01-01') and ($ExpenseCodeDes != 'ASSIGNCASH')) {
				// only movements NOT authorised can be modified or deleted
				echo '<tr class="striped_row">
						<td class="date">', ConvertSQLDate($MyRow['date']), '</td>
						<td>', $ExpenseCodeDes, '</td>
						<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
						<td class="number">', $TaxesTaxAmount, '</td>
						<td>', $TaxesDescription, '</td>
						<td>', $TagDescription, '</td>
						<td>', $MyRow['purpose'], '</td>
						<td>', $MyRow['notes'], '</td>
						<td>', $ReceiptText, '</td>
						<td>', $AuthorisedDate, '</td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedIndex=', $MyRow['counterindex'], '&SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . __('Edit') . '</a></td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedIndex=', $MyRow['counterindex'], '&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return confirm("' . __('Are you sure you wish to delete this expense?') . '");\'>' . __('Delete') . '</a></td>
					</tr>';
			} else {
				echo '<tr class="striped_row">
						<td class="date">', ConvertSQLDate($MyRow['date']), '</td>
						<td>', $ExpenseCodeDes, '</td>
						<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
						<td class="number">', $TaxesTaxAmount, '</td>
						<td>', $TaxesDescription, '</td>
						<td>', $TagDescription, '</td>
						<td>', $MyRow['purpose'], '</td>
						<td>', $MyRow['notes'], '</td>
						<td>', $ReceiptText, '</td>
						<td>', $AuthorisedDate, '</td>
					</tr>';
			}
		}
		//END WHILE LIST LOOP
		$CurrentBalance = PettyCashTabCurrentBalance($SelectedTabs);
		echo '</tbody>
				<tfoot>
					<tr class="total_row">
						<td colspan="2" class="number">', __('Current balance'), ':</td>
						<td class="number">', locale_number_format($CurrentBalance, $CurrDecimalPlaces), '</td>
						<td colspan="9"></td>
					</tr>
				</tfoot>';
		echo '</table>';
		echo '</form>';
	}
	if (!isset($_GET['delete'])) {
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							tabcode,
							date,
							codeexpense,
							amount,
							authorized,
							posted,
							purpose,
							notes
						FROM pcashdetails
						WHERE counterindex='" . $SelectedIndex . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_POST['Date'] = ConvertSQLDate($MyRow['date']);
			$_POST['SelectedExpense'] = $MyRow['codeexpense'];
			$_POST['Amount'] = -$MyRow['amount'];
			$_POST['Purpose'] = $MyRow['purpose'];
			$_POST['Notes'] = $MyRow['notes'];
			echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
			echo '<input type="hidden" name="SelectedIndex" value="', $SelectedIndex, '" />';
			echo '<input type="hidden" name="Days" value="', $Days, '" />';
			$TagSQL = "SELECT tag FROM pctags WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TagResult = DB_query($TagSQL);
			while ($TagRow = DB_fetch_array($TagResult)) {
				$TagArray[] = $TagRow['tag'];
			}
		} //end of Get Edit
		if (!isset($_POST['Date'])) {
			$_POST['Date'] = Date($_SESSION['DefaultDateFormat']);
		}
		echo '<fieldset>';
		if (isset($_GET['SelectedIndex'])) {
			echo '<legend>', __('Update Expense'), '</legend>';
		} else {
			echo '<legend>', __('New Expense'), '</legend>';
		}
		echo '<field>
				<label for="Date">', __('Date of Expense'), ':</label>
				<input type="date" name="Date" size="11" required="required" maxlength="10" value="', FormatDateForSQL($_POST['Date']), '" />
			</field>
			<field>
				<label for="SelectedExpense">', __('Expense Code'), ':</label>
				<select required="required" name="SelectedExpense">';
		DB_free_result($Result);
		$SQL = "SELECT pcexpenses.codeexpense,
					pcexpenses.description
			FROM pctabexpenses, pcexpenses, pctabs
			WHERE pctabexpenses.codeexpense = pcexpenses.codeexpense
				AND pctabexpenses.typetabcode = pctabs.typetabcode
				AND pctabs.tabcode = '" . $SelectedTabs . "'
			ORDER BY pcexpenses.codeexpense ASC";
		$Result = DB_query($SQL);
		echo '<option value="">', __('Not Yet Selected'), '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
				echo '<option selected="selected" value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			}
		} //end while loop
		echo '</select>
			</field>';
		if (!isset($_POST['Amount'])) {
			$_POST['Amount'] = 0;
		}
		echo '<field>
				<label for="Amount">', __('Gross Amount'), ':</label>
				<input type="text" class="number" name="Amount" size="12" required="required" maxlength="11" value="', $_POST['Amount'], '" />
			</field>';
		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $SelectedIndex . "'";
			$TaxesResult = DB_query($SQL);
			while ($MyTaxRow = DB_fetch_array($TaxesResult)) {
				echo '<input type="hidden" name="index', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['counterindex'], '" />';
				echo '<input type="hidden" name="PcCashDetail', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['pccashdetail'], '" />';
				echo '<input type="hidden" name="CalculationOrder', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<field>
						<label for="TaxAmount">', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%</label>
						<input type="text" class="number" size="12" name="TaxAmount', $MyTaxRow['counterindex'], '" value="', -$MyTaxRow['amount'], '" />
					</field>';
			}
		} else {
			$SQL = "SELECT taxgrouptaxes.calculationorder,
							taxauthorities.description,
							taxgrouptaxes.taxauthid,
							taxauthorities.purchtaxglaccount,
							taxgrouptaxes.taxontax,
							taxauthrates.taxrate
						FROM taxauthrates
						INNER JOIN taxgrouptaxes
							ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
						INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
						INNER JOIN taxgroups
							ON taxgroups.taxgroupid=taxgrouptaxes.taxgroupid
						INNER JOIN pctabs
							ON pctabs.taxgroupid=taxgroups.taxgroupid
						WHERE taxauthrates.taxcatid = " . $_SESSION['DefaultTaxCategory'] . "
							AND pctabs.tabcode='" . $SelectedTabs . "'
						ORDER BY taxgrouptaxes.calculationorder";
			$TaxResult = DB_query($SQL);
			$i = 0;
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
				echo '<input type="hidden" name="index', $i, '" value="', $i, '" />';
				echo '<input type="hidden" name="CalculationOrder', $i, '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $i, '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $i, '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $i, '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $i, '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $i, '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<field>
						<label for="TaxAmount">', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%:</label>
						<input type="text" class="number" size="12" name="TaxAmount', $i, '" value="0" />
					</field>';
				++$i;
			}
		}

		//Select the tag
		$SQL = "SELECT tagref,
						tagdescription
				FROM tags
				ORDER BY tagref";
		$Result = DB_query($SQL);
		echo '<field>
				<label for="tag">', __('Tag'), '</label>
				<select multiple="multiple" name="tag[]">';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($TagArray) and in_array($MyRow['tagref'], $TagArray)) {
				echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
			}
		}
		echo '</select>
			</field>';
		// End select tag

		//For the accept attribute of the file element, prefix dots to the front of each supported file extension.
		$ReceiptSupportedExtDotPrefix = array_map(function($ReceiptSupportedExt) {
			return '.' . $ReceiptSupportedExt;
		}, $ReceiptSupportedExt);
		echo '<field>
				<label for="Receipt">', __('Attach Receipt'), ':</label>
				<input type="hidden" name="MAX_FILE_SIZE" value="' . $_SESSION['MaxImageSize'] * 1024 . '" />
				<input type="file" name="Receipt" id="Receipt" accept="' . implode(',', $ReceiptSupportedExtDotPrefix) . '" title="', __('Accepted file types'), ': ', implode(', ', $ReceiptSupportedExt), '" />
			</field>';

		if (!isset($_POST['Purpose'])) {
			$_POST['Purpose'] = '';
		}
		echo '<field>
				<label for="Purpose">', __('Business Purpose'), ':</label>
				<input type="text" name="Purpose" size="50" maxlength="49" required="required" value="', $_POST['Purpose'], '" />s
			</field>';

		if (!isset($_POST['Notes'])) {
			$_POST['Notes'] = '';
		}
		echo '<field>
				<label for="Notes">', __('Notes'), ':</label>
				<input type="text" name="Notes" size="50" maxlength="49" value="', $_POST['Notes'], '" />
			</field>';

		echo '<div class="centre">
			<input type="submit" name="submit" value="', __('Accept'), '" />
			<input type="reset" name="Cancel" value="', __('Cancel'), '" />
			</div>';
		echo '</fieldset>'; // close main table
		echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
		echo '<input type="hidden" name="Days" value="', $Days, '" />';
		echo '</form>';
	} // end if user wish to delete
}
include('includes/footer.php');
