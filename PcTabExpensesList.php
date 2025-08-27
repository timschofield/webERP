<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_POST['submit'])) {

	$TabToShow= $_POST['Tabs'];
	$FromDate = $_POST['FromDate'];
	$ToDate = $_POST['ToDate'];

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		// Search absic PC Tab information
		$SQL = "SELECT pctabs.tabcode,
					   pctabs.usercode,
					   pctabs.typetabcode,
					   pctabs.currency,
					   pctabs.tablimit,
					   pctabs.assigner,
					   pctabs.authorizer,
					   pctabs.authorizerexpenses
				FROM  pctabs
				WHERE pctabs.tabcode = '" . $TabToShow . "'";
		$Result = DB_query($SQL);
		$MyTab = DB_fetch_array($Result);

		$SQL = "SELECT decimalplaces FROM currencies WHERE currabrev='" . $MyTab['currency'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$CurrDecimalPlaces = $MyRow['decimalplaces'];

		$SQL = "SELECT SUM(pcashdetails.amount) AS previous
				FROM  pcashdetails
				WHERE pcashdetails.tabcode = '" . $TabToShow . "'
					AND pcashdetails.date < '" . FormatDateForSQL($FromDate) . "'";
		$Result = DB_query($SQL);
		$MyPreviousBalance = DB_fetch_array($Result);

		$SQL = "SELECT counterindex,
						tabcode,
						date,
						codeexpense,
						amount,
						authorized,
						posted,
						purpose,
						notes,
						receipt
				FROM  pcashdetails
				WHERE pcashdetails.tabcode = '" . $TabToShow . "'
					AND pcashdetails.date >= '" . FormatDateForSQL($FromDate) . "'
					AND pcashdetails.date <= '" . FormatDateForSQL($ToDate) . "'
				ORDER BY pcashdetails.date,
					pcashdetails.counterindex";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){

			// Create new PHPExcel object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("PC Tab Expenses List")
										 ->setSubject("PC Tab Expenses List")
										 ->setDescription("PC Tab Expenses List")
										 ->setKeywords("")
										 ->setCategory("");

			// Formatting

			$SpreadSheet->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
			$SpreadSheet->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$SpreadSheet->getActiveSheet()->getStyle('B5')->getNumberFormat()->setFormatCode('#,##0.00');
			$SpreadSheet->getActiveSheet()->getStyle('C:E')->getNumberFormat()->setFormatCode('#,##0.00');
			$SpreadSheet->getActiveSheet()->getStyle('E1:E2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$SpreadSheet->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$SpreadSheet->getActiveSheet()->getStyle('A:J')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
			$SpreadSheet->getActiveSheet()->getStyle('10')->getFont()->setBold(true);
			$SpreadSheet->getActiveSheet()->getStyle('A1:A8')->getFont()->setBold(true);
			$SpreadSheet->getActiveSheet()->getStyle('D1:D2')->getFont()->setBold(true);

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'Tab Code');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', $MyTab['tabcode']);
			$SpreadSheet->getActiveSheet()->setCellValue('A2', 'User Code');
			$SpreadSheet->getActiveSheet()->setCellValue('B2', $MyTab['usercode']);
			$SpreadSheet->getActiveSheet()->setCellValue('A3', 'Type of Tab');
			$SpreadSheet->getActiveSheet()->setCellValue('B3', $MyTab['typetabcode']);
			$SpreadSheet->getActiveSheet()->setCellValue('A4', 'Currency');
			$SpreadSheet->getActiveSheet()->setCellValue('B4', $MyTab['currency']);
			$SpreadSheet->getActiveSheet()->setCellValue('A5', 'Limit');
			$SpreadSheet->getActiveSheet()->setCellValue('B5', $MyTab['tablimit']);
			$SpreadSheet->getActiveSheet()->setCellValue('A6', 'Cash Assigner');
			$SpreadSheet->getActiveSheet()->setCellValue('B6', $MyTab['assigner']);
			$SpreadSheet->getActiveSheet()->setCellValue('A7', 'Authorizer - Cash');
			$SpreadSheet->getActiveSheet()->setCellValue('B7', $MyTab['authorizer']);
			$SpreadSheet->getActiveSheet()->setCellValue('A8', 'Authorizer - Expenses');
			$SpreadSheet->getActiveSheet()->setCellValue('B8', $MyTab['authorizerexpenses']);

			$SpreadSheet->getActiveSheet()->setCellValue('D1', 'From');
			$SpreadSheet->getActiveSheet()->setCellValue('E1', $FromDate);
			$SpreadSheet->getActiveSheet()->setCellValue('D2', 'To');
			$SpreadSheet->getActiveSheet()->setCellValue('E2', $ToDate);

			$SpreadSheet->getActiveSheet()->setCellValue('A10', 'Date');
			$SpreadSheet->getActiveSheet()->setCellValue('B10', 'Expense Code');
			$SpreadSheet->getActiveSheet()->setCellValue('C10', 'Gross Amount');
			$SpreadSheet->getActiveSheet()->setCellValue('D10', 'Balance');
			$SpreadSheet->getActiveSheet()->setCellValue('E10', 'Tax');
			$SpreadSheet->getActiveSheet()->setCellValue('F10', 'Tax Group');
			$SpreadSheet->getActiveSheet()->setCellValue('H10', 'Business Purpose');
			$SpreadSheet->getActiveSheet()->setCellValue('I10', 'Notes');
			$SpreadSheet->getActiveSheet()->setCellValue('J10', 'Receipt Attachment');
			$SpreadSheet->getActiveSheet()->setCellValue('K10', 'Date Authorized');

			$SpreadSheet->getActiveSheet()->setCellValue('B11', 'Previous Balance');
			$SpreadSheet->getActiveSheet()->setCellValue('D11', $MyPreviousBalance['previous']);

			// Add data
			$i = 12;
			while ($MyRow = DB_fetch_array($Result)) {

				$SQLDes = "SELECT description
							FROM pcexpenses
							WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";
				$ResultDes = DB_query($SQLDes);
				$Description=DB_fetch_array($ResultDes);
				if (!isset($Description[0])) {
						$ExpenseCodeDes = 'ASSIGNCASH';
				} else {
						$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];
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
					$TaxesDescription .= $MyTaxRow['description'];
					$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces);
				}

				//Generate download link for expense receipt, or show text if no receipt file is found.
				$ReceiptSupportedExt = array('png','jpg','jpeg','pdf','doc','docx','xls','xlsx'); //Supported file extensions
				$ReceiptDir = $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/expenses_receipts/'; //Receipts upload directory
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
					$ReceiptText = __('Open Attachment');
					$ReceiptURL = htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $ReceiptPath, ENT_QUOTES, 'UTF-8');
				} elseif ($ExpenseCodeDes == 'ASSIGNCASH') {
				$ReceiptText = '';
				} else {
				$ReceiptText = __('No attachment');
				}

				if ($MyRow['authorized'] == '1000-01-01') {
					$AuthorisedDate = __('Unauthorised');
				} else {
					$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
				}

				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, ConvertSQLDate($MyRow['date']));
				$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, $ExpenseCodeDes);
				$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, $MyRow['amount']);
				$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, '=D'.($i-1).'+C'.$i.'');
				$SpreadSheet->getActiveSheet()->setCellValue('E'.$i, $TaxesTaxAmount);
				$SpreadSheet->getActiveSheet()->setCellValue('F'.$i, $TaxesDescription);
				$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, $MyRow['purpose']);
				$SpreadSheet->getActiveSheet()->setCellValue('I'.$i, $MyRow['notes']);
				$SpreadSheet->getActiveSheet()->setCellValue('J'.$i, $ReceiptText);
				if (isset($ReceiptURL)) {
					$SpreadSheet->getActiveSheet()->getCell('J'.$i)->getHyperlink()->setUrl($ReceiptURL);
					$SpreadSheet->getActiveSheet()->getStyle('J'.$i)->applyFromArray(array( 'font' => array( 'color' => ['rgb' => '0000FF'], 'underline' => 'single' )));
				}
				$SpreadSheet->getActiveSheet()->setCellValue('K'.$i, $AuthorisedDate);

				$i++;
			}

			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('A11');

			// Auto Size columns
			foreach(range('A','K') as $ColumnID) {
				$SpreadSheet->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}

			// Rename worksheet
			$SpreadSheet->getActiveSheet()->setTitle($TabToShow);
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'ExpensesList-' . $TabToShow. '.' . $_POST['Format'];
			header('Content-Disposition: attachment;filename="' . $File . '"');
			/// @todo review caching headers
			header('Cache-Control: max-age=0');
			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0


			if ($_POST['Format'] == 'xlsx') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($SpreadSheet);
				$objWriter->save('php://output');
			} else if ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
				$objWriter->save('php://output');
			}

		} else {
			$Title = __('Excel file for Petty Cash Tab Expenses List');
			include('includes/header.php');
			prnMsg('There is no data to analyse');
			include('includes/footer.php');
		}
	}
} else {
	$Title = __('Excel file for Petty Cash Tab Expenses List');
	$ViewTopic = 'PettyCash';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'top';// Anchor's id in the manual's html document.
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . __('Excel file for Petty Cash Tab Expenses List') . '" alt="" />' . ' ' . __('Excel file for Petty Cash Tab Expenses List') . '
		</p>';

	# Sets default date range for current month
	if (!isset($_POST['FromDate'])){
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}
	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	echo '<fieldset>
			<legend>', __('Select Criteria'), '</legend>';

	echo '<field>
			<label for="Tabs">' . __('For Petty Cash Tab') . ':</label>
			<select name="Tabs">';

	$SQL = "SELECT tabcode
			FROM pctabs
			ORDER BY tabcode";
	$CatResult = DB_query($SQL);

	while ($MyRow = DB_fetch_array($CatResult)){
		echo '<option value="' . $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label>' . __('Date Range') . ':</label>
			<input type="date" name="FromDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
				' . __('To') . ':<input type="date" name="ToDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>';

	echo '<field>
			<label for="Format">', __('Output Format'), '</label>
			<select name="Format">
				<option value="xlsx">', __('Excel Format (.xlsx)'), '</option>
				<option value="ods" selected="selected">', __('Open Document Format (.ods)'), '</option>
			</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Create Petty Cash Tab Expenses List Excel File') . '" />
		</div>';

	echo '</form>';
	include('includes/footer.php');
}

function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

} // End of function display()

function beginning_of_month($Date){
	$Date2 = explode("-",$Date);
	$M = $Date2[1];
	$Y = $Date2[0];
	$FirstOfMonth = $Y . '-' . $M . '-01';
	return $FirstOfMonth;
}
