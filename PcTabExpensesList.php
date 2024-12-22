<?php

require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['submit'])) {
    submit($_POST['Tabs'], $_POST['FromDate'], $_POST['ToDate']);
} else {
    display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($TabToShow, $FromDate, $ToDate) {

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

		$SQL = "SELECT SUM(pcashdetails.amount) AS previous
				FROM  pcashdetails
				WHERE pcashdetails.tabcode = '" . $TabToShow . "'
					AND pcashdetails.date < '" . FormatDateForSQL($FromDate) . "'";
		$Result = DB_query($SQL);
		$MyPreviousBalance = DB_fetch_array($Result);

		$SQL = "SELECT counterindex,
						tabcode,
						tag,
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
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("PC Tab Expenses List")
										 ->setSubject("PC Tab Expenses List")
										 ->setDescription("PC Tab Expenses List")
										 ->setKeywords("")
										 ->setCategory("");

			// Formatting

			$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('C:E')->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('E1:E2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$objPHPExcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$objPHPExcel->getActiveSheet()->getStyle('A:J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objPHPExcel->getActiveSheet()->getStyle('10')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A1:A8')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('D1:D2')->getFont()->setBold(true);

			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Tab Code');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', $MyTab['tabcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A2', 'User Code');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', $MyTab['usercode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Type of Tab');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', $MyTab['typetabcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Currency');
			$objPHPExcel->getActiveSheet()->setCellValue('B4', $MyTab['currency']);
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Limit');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', $MyTab['tablimit']);
			$objPHPExcel->getActiveSheet()->setCellValue('A6', 'Cash Assigner');
			$objPHPExcel->getActiveSheet()->setCellValue('B6', $MyTab['assigner']);
			$objPHPExcel->getActiveSheet()->setCellValue('A7', 'Authorizer - Cash');
			$objPHPExcel->getActiveSheet()->setCellValue('B7', $MyTab['authorizer']);
			$objPHPExcel->getActiveSheet()->setCellValue('A8', 'Authorizer - Expenses');
			$objPHPExcel->getActiveSheet()->setCellValue('B8', $MyTab['authorizerexpenses']);

			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'From');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', $FromDate);
			$objPHPExcel->getActiveSheet()->setCellValue('D2', 'To');
			$objPHPExcel->getActiveSheet()->setCellValue('E2', $ToDate);

			$objPHPExcel->getActiveSheet()->setCellValue('A10', 'Date');
			$objPHPExcel->getActiveSheet()->setCellValue('B10', 'Expense Code');
			$objPHPExcel->getActiveSheet()->setCellValue('C10', 'Gross Amount');
			$objPHPExcel->getActiveSheet()->setCellValue('D10', 'Balance');
			$objPHPExcel->getActiveSheet()->setCellValue('E10', 'Tax');
			$objPHPExcel->getActiveSheet()->setCellValue('F10', 'Tax Group');
			$objPHPExcel->getActiveSheet()->setCellValue('G10', 'Tag');
			$objPHPExcel->getActiveSheet()->setCellValue('H10', 'Business Purpose');
			$objPHPExcel->getActiveSheet()->setCellValue('I10', 'Notes');
			$objPHPExcel->getActiveSheet()->setCellValue('J10', 'Receipt Attachment');
			$objPHPExcel->getActiveSheet()->setCellValue('K10', 'Date Authorized');

			$objPHPExcel->getActiveSheet()->setCellValue('B11', 'Previous Balance');
			$objPHPExcel->getActiveSheet()->setCellValue('D11', $MyPreviousBalance['previous']);

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

				$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
				$TagResult = DB_query($TagSQL);
				$TagRow = DB_fetch_array($TagResult);
				if ($MyRow['tag'] == 0) {
					$TagRow['tagdescription'] = _('None');
				}
				$TagTo = $MyRow['tag'];
				$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];

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
					$ReceiptText = _('Open Attachment');
					$ReceiptURL = htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $ReceiptPath, ENT_QUOTES, 'UTF-8');
				} elseif ($ExpenseCodeDes == 'ASSIGNCASH') {
				$ReceiptText = '';
				} else {
				$ReceiptText = _('No attachment');
				}

				if ($MyRow['authorized'] == '0000-00-00') {
					$AuthorisedDate = _('Unauthorised');
				} else {
					$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
				}

				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, ConvertSQLDate($MyRow['date']));
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $ExpenseCodeDes);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $MyRow['amount']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '=D'.($i-1).'+C'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $TaxesTaxAmount);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $TaxesDescription);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $TagDescription);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $MyRow['purpose']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $MyRow['notes']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $ReceiptText);
				if (isset($ReceiptURL)) {
					$objPHPExcel->getActiveSheet()->getCell('J'.$i)->getHyperlink()->setUrl($ReceiptURL);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$i)->applyFromArray(array( 'font' => array( 'color' => ['rgb' => '0000FF'], 'underline' => 'single' )));
				}
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $AuthorisedDate);

				$i++;
			}

			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A11');

			// Auto Size columns
			foreach(range('A','K') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}

			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle($TabToShow);
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'ExpensesList-' . $TabToShow. '.xlsx';
			header('Content-Disposition: attachment;filename="' . $File . '"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');

		}else{
			$Title = _('Excel file for Petty Cash Tab Expenses List');
			include('includes/header.php');
			prnMsg('There is no data to analyse');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Petty Cash Tab Expenses List');
	$ViewTopic = 'PettyCash';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'top';// Anchor's id in the manual's html document.

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Excel file for Petty Cash Tab Expenses List') . '" alt="" />' . ' ' . _('Excel file for Petty Cash Tab Expenses List') . '
		</p>';

	# Sets default date range for current month
	if (!isset($_POST['FromDate'])){
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}
	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	echo '<table class="selection">
		<tr>
		<td>' . _('For Petty Cash Tab') . ':</td>
		<td><select name="Tabs">';

	$SQL = "SELECT tabcode
			FROM pctabs
			ORDER BY tabcode";
	$CatResult = DB_query($SQL);

	while ($MyRow = DB_fetch_array($CatResult)){
		echo '<option value="' . $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';
	}
	echo '</select>
			</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" name="FromDate" size="11" maxlength="10" value="' . $_POST['FromDate'] . '" />
			' . _('To') . ':<input type="text" class="date" name="ToDate" size="11" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';

	echo '
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Petty Cash Tab Expenses List Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

function beginning_of_month($Date){
	$Date2 = explode("-",$Date);
	$M = $Date2[1];
	$Y = $Date2[0];
	$FirstOfMonth = $Y . '-' . $M . '-01';
	return $FirstOfMonth;
}

?>