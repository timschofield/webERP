<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,1,1,Date('Y')));
}

if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($_POST['FromDate'], $_POST['ToDate']);
} else {
    display($RootPath, $Theme);
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){

		$DefaultFromDate = FormatDateForSQL($FromDate);
		$DefaultToDate = FormatDateForSQL($ToDate);

		$StartCurrentYearPeriod=GetPeriod($FromDate);
		$CurrentPeriod=GetPeriod(Date($_SESSION['DefaultDateFormat']));	

		$StartLastYearPeriod=$StartCurrentYearPeriod-12;
		$FinishLastYearPeriod=$StartCurrentYearPeriod-1;
		
		if (TRUE){
			
			// Set value binder
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Financial Analysis")
										 ->setSubject("Financial Analysis")
										 ->setDescription("Financial Analysis")
										 ->setKeywords("")
										 ->setCategory("");

			///////////////////////////////////////////////////////////////////
			// worksheet SALES ANALYSIS
			///////////////////////////////////////////////////////////////////
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle('Sales Analysis');

			$objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('0.0%');
	
			// Add title data
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Period');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', $FromDate . ' to ' . $ToDate);
			
			$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Account');
			$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Last Year');
			$objPHPExcel->getActiveSheet()->setCellValue('E2', '');
			$objPHPExcel->getActiveSheet()->setCellValue('F2', 'YTD');
			$objPHPExcel->getActiveSheet()->setCellValue('G2', '');
 			$objPHPExcel->getActiveSheet()->setCellValue('H2', 'MTD');
			$objPHPExcel->getActiveSheet()->setCellValue('I2', '');

			$objPHPExcel->getActiveSheet()->setCellValue('C3', 'Income CC BB');
			$objPHPExcel->getActiveSheet()->setCellValue('D3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $StartLastYearPeriod,$FinishLastYearPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('E3', '=D3/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('G3', '=F3/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $CurrentPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('I3', '=H3/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C4', 'Income Cash BB');
			$objPHPExcel->getActiveSheet()->setCellValue('D4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartLastYearPeriod,$FinishLastYearPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('E4', '=D4/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('G4', '=F4/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $CurrentPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('I4', '=H4/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Income Cash');
			$objPHPExcel->getActiveSheet()->setCellValue('D5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $StartLastYearPeriod,$FinishLastYearPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('E5', '=D5/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('G5', '=F5/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $CurrentPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('I5', '=H5/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C6', 'Income Others BB');
			$objPHPExcel->getActiveSheet()->setCellValue('D6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $StartLastYearPeriod,$FinishLastYearPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('E6', '=D6/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('G6', '=F6/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $CurrentPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('I6', '=H6/$H$8');
			
			$objPHPExcel->getActiveSheet()->setCellValue('C7', 'Income Others');
			$objPHPExcel->getActiveSheet()->setCellValue('D7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $StartLastYearPeriod,$FinishLastYearPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('E7', '=D7/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('G7', '=F7/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $CurrentPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('I7', '=H7/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C8', 'TOTAL INCOME');
			$objPHPExcel->getActiveSheet()->setCellValue('D8', '=SUM(D3:D7)');
			$objPHPExcel->getActiveSheet()->setCellValue('F8', '=SUM(F3:F7)');
			$objPHPExcel->getActiveSheet()->setCellValue('H8', '=SUM(H3:H7)');

			$objPHPExcel->getActiveSheet()->setCellValue('C10', 'INCOME BB');
			$objPHPExcel->getActiveSheet()->setCellValue('D10', '=D3+D4+D6');
			$objPHPExcel->getActiveSheet()->setCellValue('E10', '=D10/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F10', '=F3+F4+F6');
			$objPHPExcel->getActiveSheet()->setCellValue('G10', '=F10/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H10', '=H3+H4+H6');
			$objPHPExcel->getActiveSheet()->setCellValue('I10', '=H10/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C11', 'INCOME CASH');
			$objPHPExcel->getActiveSheet()->setCellValue('D11', '=D5+D7');
			$objPHPExcel->getActiveSheet()->setCellValue('E11', '=D11/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F11', '=F5+F7');
			$objPHPExcel->getActiveSheet()->setCellValue('G11', '=F11/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H11', '=H5+H7');
			$objPHPExcel->getActiveSheet()->setCellValue('I11', '=H11/$H$8');

			// Auto Size columns
			foreach(range('A','C') as $ColumnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}

			///////////////////////////////////////////////////////////////////
			// worksheet Cash PT BALANCE
			///////////////////////////////////////////////////////////////////
			
			$objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setTitle('Cash PT Balance');
			$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode('#,###');

			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'CASH PT BALANCE ANALYSIS');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Period');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', $FromDate . ' to ' . $ToDate);

			$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Income from Retail Cash BB');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartCurrentYearPeriod,$CurrentPeriod));
			$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Cash from Danamon to Kantor Cash');
			$objPHPExcel->getActiveSheet()->setCellValue('B4', -MovementsFromDanamonToCashKantor($DefaultFromDate, $DefaultToDate));
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'TOTAL CASH AVAILABLE');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', '=B3+B4');
			$objPHPExcel->getActiveSheet()->setCellValue('A7', 'Expenses paid cash for Accounts BB');
			$objPHPExcel->getActiveSheet()->setCellValue('B7', -ExpensesPaidCashForAccountsPT($DefaultFromDate, $DefaultToDate));
			$objPHPExcel->getActiveSheet()->setCellValue('A9', 'BALANCE CASH BB');
			$objPHPExcel->getActiveSheet()->setCellValue('B9', '=B5-B7');

			// Auto Size columns
			foreach(range('A','B') as $ColumnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
					
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-FinancialAnalysis-' . Date('Y-m-d'). '.xlsx';
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
			$Title = _('Excel file for Financial Analysis');
			include('includes/header.php');
			prnMsg('No info selected to analyse');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Financial Analysis');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Financial Analysis') . '" alt="" />' . ' ' . _('Excel file for Financial Analysis') . '
		</p>';
	echo '<table>';	
	echo '<tr>
			<td>' . _('From') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';


	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Financial Analysis Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

function MovementAccountsBetweenPeriods($AccountList, $PeriodFrom, $PeriodTo){
	$SQL = "SELECT SUM(chartdetails.actual)
			FROM chartdetails
			WHERE chartdetails.accountcode IN " . $AccountList ."
				AND chartdetails.period >='" . $PeriodFrom . "'
				AND chartdetails.period <='" . $PeriodTo . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow[0]/JUTA;
}

function ExpensesPaidCashForAccountsPT($DateFrom, $DateTo){
	$SQL = "SELECT SUM(pcashdetails.amount) 
			FROM pcashdetails, pctabs, pcexpenses
			WHERE pcashdetails.date >= '" . $DateFrom . "'
				AND pcashdetails.date <= '" . $DateTo . "'
				AND pcashdetails.tabcode = pctabs.tabcode
				AND pcashdetails.codeexpense = pcexpenses.codeexpense
				AND pctabs.currency = 'IDR'
				AND pcashdetails.codeexpense != 'ASSIGNCASH'
				AND pctabs.tabcode NOT LIKE 'SALARIES%'
				AND pctabs.tabcode NOT LIKE '%DANAMON'
				AND pctabs.tabcode NOT LIKE 'CC-BCA%'
				AND pcexpenses.glaccount LIKE '%BB'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow[0]/JUTA;
}

function MovementsFromDanamonToCashKantor($DateFrom, $DateTo){
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $DateFrom . "'
				AND gltrans.trandate <= '" . $DateTo . "'
				AND gltrans.account = '111121105BB'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	return $MyRow[0]/JUTA;
}

?>