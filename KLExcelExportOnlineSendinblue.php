<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php'); 
include('includes/KLUIFunctions.php'); 
include('includes/KLCountriesForRetail.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

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
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
	}
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
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
		prnMsg(_('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}
	if (FormatDateForSQL($_POST['ToDate']) < FormatDateForSQL($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Date To has to be greater than From Date') . " From: " . $_POST['FromDate'] . " To: " . $_POST['ToDate'],'error');
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
				
		$ErrMsg = _('The SQL to find the webERP Customer Data to export to Sendinblue');
		$Result = DB_query($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){
			$TxResult = DB_Txn_Begin();

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sendinblue webERP Customers")
										 ->setSubject("Sendinblue webERP Customers")
										 ->setDescription("Sendinblue webERP Customers")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'EMAIL');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'FAMILY_NAME');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'FIRST_NAME');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'COUNTRY');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'SEX');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'DATE_OF_BIRTH');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'AGE_AT_PURCHASE_DATE');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', 'HAS_VIP_CARD');
			$objPHPExcel->getActiveSheet()->setCellValue('I1', 'PURCHASE_DATE');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', 'PURCHASE_ITEMS');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', 'PURCHASE_VALUE_IDR');

			// Add data
			$i = 2;
			$PreviousEmail = "";
			while ($MyRow = DB_fetch_array($Result)) {
				if ($PreviousEmail != $MyRow['email']){
					$objPHPExcel->setActiveSheetIndex(0);

					$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $MyRow['email']);
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, CapitalizeName($MyRow['firstname']));
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $MyRow['country']);
					
					if ($MyRow['vipcards'] == 0){
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'N');
					}else{
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Y');
					}
					
					if ($MyRow['orddate'] != '0000-00-00'){
						$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $MyRow['orddate']);
					}

					$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $MyRow['purchase_items']);
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, round($MyRow['purchase_value'] / $MyRow['rate']));
					
					$i++;
					$PreviousEmail = $MyRow['email'];
				}
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','F') as $ColumnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('Retail Customer');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client�s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-webERPCustomers-' . Date('Y-m-d'). '.xlsx';
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

			if ($MarkExported == "Y"){
				$SQL = "UPDATE salesorders, debtorsmaster
						SET klexported = 'Y' 
						WHERE salesorders.debtorno = debtorsmaster.debtorno
							AND salesorders.klexported = 'N' 
							AND salesorders.orddate >= '" . $FromDate . "'
							AND salesorders.orddate <= '" . $ToDate . "'
							AND debtorsmaster.typeid NOT IN (". CUSTOMER_TYPE_RETAIL . ")" .
						$SQLCustomers;
				$ResultUpdate = DB_query($SQL,'','',true);
			}
			DB_Txn_Commit();

		}else{
			$Title = _('Excel file for Sendinblue: Export webERP Customers');
			include('includes/header.php');
			prnMsg('No webERP Customer Data to export to Sendinblue');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  {
	$Title = _('Excel file for Sendinblue: Export webERP Customers');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sendinblue: Export webERP Customer') . '" alt="" />' . ' ' . _('Excel file for Sendinblue: Export webERP Customer') . '
		</p>';

	echo '<fieldset>
		<legend>' . _('Selection Criteria') . '</legend>';

	echo FieldToSelectOneDate('FromDate', _('From'), $_POST['FromDate']);
	echo FieldToSelectOneDate('ToDate', _('To'), $_POST['ToDate']);
	
	echo '<field>';
	echo _('Type of Customers?') . ':';
	echo '<select name="TypeCustomers">
			<option selected="selected" value="WEB">' . _('Online Only') . '</option>
			<option value="OTHERS">' . _('Others') . '</option>
			</select>';
	echo '</field>';

	echo '<field>';
	echo _('Mark as Exported?') . ':';
	echo '<select name="MarkExported">
			<option selected="selected" value="N">' . _('No') . '</option>
			<option value="Y">' . _('Yes') . '</option>
			</select>';
	echo '</field>';
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Create Excel File for Sendinblue'));

	echo '</div>
         </form>';
	include('includes/footer.php');
} // End of function display()

?>