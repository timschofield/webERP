<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php'); 
include('includes/KLUIGeneralFunctions.php'); 
include('includes/KLCountriesForRetail.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

if (!isset($_POST['FromDate'])){
	$SQL = "SELECT 	salesorders.orddate
			FROM salesorders, debtorsmaster
			WHERE salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.contactemail != ''
				AND salesorders.klexported = 'N'
				AND debtorsmaster.typeid NOT IN (". CUSTOMER_TYPE_RETAIL . ")
			ORDER BY salesorders.orddate ASC";
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result) != 0){
		// get the first date only
		$MyRow = DB_fetch_array($Result);
		$_POST['FromDate'] = ConvertSQLDate($MyRow['orddate']);
	}else{
		$_POST['FromDate'] = date($_SESSION['DefaultDateFormat']);
	}
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($CountriesForRetail, $_POST['TypeCustomers'], $_POST['MarkExported'], $_POST['FromDate'], $_POST['ToDate']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($CountriesForRetail, $TypeCustomers, $MarkExported, $FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible
	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(__('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(__('Invalid To Date'),'error');
	}
	if (FormatDateForSQL($_POST['ToDate']) < FormatDateForSQL($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(__('Date To has to be greater than From Date') . " From: " . $_POST['FromDate'] . " To: " . $_POST['ToDate'],'error');
	}

	if ($InputError == 0){
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		
		if ($TypeCustomers == "WEB"){
			$SQLCustomers = " AND salesorders.debtorno LIKE 'WEB%'";
		}else{
			$SQLCustomers = " AND salesorders.debtorno NOT LIKE 'WEB%'";
		}
		
		$SQL = "SELECT 	salesorders.contactemail AS email,
						salesorders.deliverto AS firstname,
						salesorders.deladd6 AS country,
						salesorders.orddate,
						(SELECT SUM(qtyinvoiced*unitprice)
						FROM salesorderdetails
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.stkcode != 'ONLINE-VIP-PACK') AS purchase_value,
						(SELECT SUM(qtyinvoiced)
						FROM salesorderdetails
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.stkcode != 'ONLINE-VIP-PACK') AS purchase_items,
						(SELECT COUNT(*)
						FROM salesorderdetails
						WHERE salesorderdetails.orderno = salesorders.orderno
							AND salesorderdetails.stkcode = 'ONLINE-VIP-PACK') AS vipcards,
						debtorsmaster.currcode,
						currencies.rate
				FROM salesorders, debtorsmaster, currencies
				WHERE  salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.currcode = currencies.currabrev
					AND salesorders.contactemail != ''
					AND debtorsmaster.typeid NOT IN (". CUSTOMER_TYPE_RETAIL . ") ".
					$SQLCustomers . "
					AND salesorders.klexported = 'N'
					AND salesorders.orddate >= '" . $FromDate . "'
					AND salesorders.orddate <= '" . $ToDate . "'
				ORDER BY salesorders.debtorno, salesorders.orderno";
				
		$ErrMsg = __('The SQL to find the webERP Customer Data to export to Sendinblue');
		$Result = DB_query($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){
			$TxResult = DB_Txn_Begin();

			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sendinblue webERP Customers")
										 ->setSubject("Sendinblue webERP Customers")
										 ->setDescription("Sendinblue webERP Customers")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'EMAIL');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'FAMILY_NAME');
			$SpreadSheet->getActiveSheet()->setCellValue('C1', 'FIRST_NAME');
			$SpreadSheet->getActiveSheet()->setCellValue('D1', 'COUNTRY');
			$SpreadSheet->getActiveSheet()->setCellValue('E1', 'SEX');
			$SpreadSheet->getActiveSheet()->setCellValue('F1', 'DATE_OF_BIRTH');
			$SpreadSheet->getActiveSheet()->setCellValue('G1', 'AGE_AT_PURCHASE_DATE');
			$SpreadSheet->getActiveSheet()->setCellValue('H1', 'HAS_VIP_CARD');
			$SpreadSheet->getActiveSheet()->setCellValue('I1', 'PURCHASE_DATE');
			$SpreadSheet->getActiveSheet()->setCellValue('J1', 'PURCHASE_ITEMS');
			$SpreadSheet->getActiveSheet()->setCellValue('K1', 'PURCHASE_VALUE_IDR');

			// Add data
			$i = 2;
			$PreviousEmail = "";
			while ($MyRow = DB_fetch_array($Result)) {
				if ($PreviousEmail != $MyRow['email']){
					$SpreadSheet->setActiveSheetIndex(0);

					$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['email']);
					$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, CapitalizeName($MyRow['firstname']));
					$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, $MyRow['country']);
					
					if ($MyRow['vipcards'] == 0){
						$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, 'N');
					}else{
						$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, 'Y');
					}
					
					if ($MyRow['orddate'] != '1000-01-01'){
						$SpreadSheet->getActiveSheet()->setCellValue('I'.$i, $MyRow['orddate']);
					}

					$SpreadSheet->getActiveSheet()->setCellValue('J'.$i, $MyRow['purchase_items']);
					$SpreadSheet->getActiveSheet()->setCellValue('K'.$i, round($MyRow['purchase_value'] / $MyRow['rate']));
					
					$i++;
					$PreviousEmail = $MyRow['email'];
				}
			}
			
			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','F') as $ColumnID) {
				$SpreadSheet->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$SpreadSheet->getActiveSheet()->setTitle('Retail Customer');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = 'KL-webERPCustomers-' . date('Y-m-d'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = 'KL-webERPCustomers-' . date('Y-m-d'). '.ods';
			}
			header('Content-Disposition: attachment;filename="' . $File . '"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

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

			if ($MarkExported == "Y"){
				$SQL = "UPDATE salesorders, debtorsmaster
						SET klexported = 'Y' 
						WHERE salesorders.debtorno = debtorsmaster.debtorno
							AND salesorders.klexported = 'N' 
							AND salesorders.orddate >= '" . $FromDate . "'
							AND salesorders.orddate <= '" . $ToDate . "'
							AND debtorsmaster.typeid NOT IN (". CUSTOMER_TYPE_RETAIL . ")" .
						$SQLCustomers;
				DB_query($SQL, '', '', true);
			}
			DB_Txn_Commit();

		}else{
			$Title = __('Excel file for Sendinblue: Export webERP Customers');
			include('includes/header.php');
			prnMsg('No webERP Customer Data to export to Sendinblue');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  {
	$Title = __('Excel file for Sendinblue: Export webERP Customers');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Excel file for Sendinblue: Export webERP Customer') . '" alt="" />' . ' ' . __('Excel file for Sendinblue: Export webERP Customer') . '
		</p>';

	echo '<fieldset>
		<legend>' . __('Selection Criteria') . '</legend>';

	echo FieldToSelectOneDate('FromDate', __('From'), $_POST['FromDate']);
	echo FieldToSelectOneDate('ToDate', __('To'), $_POST['ToDate']);
	
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], __('File Format'));

	echo '<field>';
	echo __('Type of Customers?') . ':';
	echo '<select name="TypeCustomers">
			<option selected="selected" value="WEB">' . __('Online Only') . '</option>
			<option value="OTHERS">' . __('Others') . '</option>
			</select>';
	echo '</field>';

	echo '<field>';
	echo __('Mark as Exported?') . ':';
	echo '<select name="MarkExported">
			<option selected="selected" value="N">' . __('No') . '</option>
			<option value="Y">' . __('Yes') . '</option>
			</select>';
	echo '</field>';
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Export File for Sendinblue'));

	echo '</div>
         </form>';
	include('includes/footer.php');
} // End of function display()
