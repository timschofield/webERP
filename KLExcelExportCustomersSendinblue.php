<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLCountriesForRetail.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

if (!isset($_POST['FromDate'])){
	$SQL = "SELECT 	salesorders.orddate
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND klretailcustomers.email != ''
				AND klretailcustomers.exported = 'N'
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
    submit($CountriesForRetail, $_POST['MarkExported'], $_POST['FromDate'], $_POST['ToDate']);
} else {
	display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($CountriesForRetail, $MarkExported, $FromDate, $ToDate) {

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
		prnMsg(_('Date To has to be greater than From Date'),'error');
	}

	if ($InputError == 0){
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		
		$SQL = "SELECT 	klretailcustomers.email,
						klretailcustomers.firstname,
						klretailcustomers.lastname,
						klretailcustomers.country,
						klretailcustomers.date_of_birth,
						klretailcustomers.age,
						klretailcustomers.sex,
						salesorders.orddate,
						(salesorders.klpaidcash + salesorders.klpaidcreditcard + salesorders.klreturnedgoods + klvouchers) AS purchase_value,
						(SELECT SUM(qtyinvoiced)
						FROM salesorderdetails
						WHERE salesorderdetails.orderno = klretailcustomers.orderno
							AND salesorderdetails.stkcode != 'ONLINE-VIP-PACK') AS purchase_items,
						(SELECT COUNT(*)
						FROM salesorderdetails
						WHERE salesorderdetails.orderno = klretailcustomers.orderno
							AND salesorderdetails.stkcode = 'ONLINE-VIP-PACK') AS vipcards
				FROM klretailcustomers, salesorders
				WHERE klretailcustomers.orderno = salesorders.orderno
					AND klretailcustomers.email != ''
					AND klretailcustomers.exported = 'N'
					AND salesorders.orddate >= '" . $FromDate . "'
					AND salesorders.orddate <= '" . $ToDate . "'
				ORDER BY klretailcustomers.orderno";
		
		$ErrMsg = _('The SQL to find the Retail Customer Data to export to Sendinblue');
		$Result = DB_query($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){
			$TxResult = DB_Txn_Begin();

		// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sendinblue Customers")
										 ->setSubject("Sendinblue Customers")
										 ->setDescription("Sendinblue Customers")
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
			while ($MyRow = DB_fetch_array($Result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, ReviseEmailAddress($MyRow['email']));
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, CapitalizeName($MyRow['lastname']));
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, CapitalizeName($MyRow['firstname']));
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $CountriesForRetail[$MyRow['country']]);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $MyRow['sex']);
				
				if (($MyRow['date_of_birth'] != '0000-00-00') AND ($MyRow['date_of_birth'] < Date('Y-m-d'))){
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $MyRow['date_of_birth']);
				}
				
				if ($MyRow['age'] != '0'){
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $MyRow['age']);
				}
				
				if ($MyRow['vipcards'] == 0){
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'N');
				}else{
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, 'Y');
				}
				
				if ($MyRow['orddate'] != '0000-00-00'){
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $MyRow['orddate']);
				}

				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $MyRow['purchase_items']);
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $MyRow['purchase_value']);
				
				$i++;
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

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-RetailCustomers-' . Date('Y-m-d'). '.xlsx';
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
				$SQL = "UPDATE klretailcustomers 
						SET exported = 'Y' 
						WHERE exported = 'N' 
							AND EXISTS (SELECT *
										FROM salesorders
										WHERE salesorders.orderno = klretailcustomers.orderno
											AND salesorders.orddate >= '" . $FromDate . "'
											AND salesorders.orddate <= '" . $ToDate . "')";
				$ResultUpdate = DB_query($SQL,'','',true);
			}
			DB_Txn_Commit();

		}else{
			$Title = _('Excel file for Sendinblue: Export Retail Customer');
			include('includes/header.php');
			prnMsg('No Retail Customer Data to export to Sendinblue');
			include('includes/footer.php');
		}
	}
} // End of function submit()

//####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
function display($RootPath, $Theme) {
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Sendinblue: Export Retail Customer');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sendinblue: Export Retail Customer') . '" alt="" />' . ' ' . _('Excel file for Sendinblue: Export Retail Customer') . '
		</p>';

	echo '<table>';

	echo '<tr>
			<td>' . _('From:') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';
	echo '<tr><td>' . _('Mark as Exported?') . ':</td>
			<td><select name="MarkExported">
				<option selected="selected" value="N">' . _('No') . '</option>
				<option value="Y">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File for Sendinblue') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>