<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
/*
if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($db, $_POST['Categories'], $_POST['FromDate'], $_POST['ToDate'], $_POST['CodeDetail']);
} else {
    display($db);
}
*/
submit($db);

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $ListCategories, $FromDate, $ToDate, $CodeDetail) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){

		if (Date('m') > $_SESSION['YearEnd']){
			/*Dates in SQL format */
			$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
			$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
		} else {
			$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
			$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
		}
		$DefaultToDate = Date('Y-m-d');
		$StartThisYear=GetPeriod($FromDate, $db);
		$Now=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);	
		$StartLastYear=$StartThisYear-12;
		$FinishLastYear=$StartThisYear-1;
		
//		if (DB_num_rows($result) != 0){
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
			// Rename worksheet
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle('Sales Analysis');

			$objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode('0.0%');
	
			// Add title data
			$objPHPExcel->getActiveSheet()->setCellValue('C2', 'Account');
			$objPHPExcel->getActiveSheet()->setCellValue('D2', 'Last Year');
			$objPHPExcel->getActiveSheet()->setCellValue('E2', '');
			$objPHPExcel->getActiveSheet()->setCellValue('F2', 'YTD');
			$objPHPExcel->getActiveSheet()->setCellValue('G2', '');
 			$objPHPExcel->getActiveSheet()->setCellValue('H2', 'MTD');
			$objPHPExcel->getActiveSheet()->setCellValue('I2', '');

			$objPHPExcel->getActiveSheet()->setCellValue('C3', 'Income CC PT');
			$objPHPExcel->getActiveSheet()->setCellValue('D3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $StartLastYear,$FinishLastYear,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('E3', '=D3/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('G3', '=F3/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H3', -MovementAccountsBetweenPeriods(GL_INCOME_CC_PT, $Now,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('I3', '=H3/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C4', 'Income Cash PT');
			$objPHPExcel->getActiveSheet()->setCellValue('D4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartLastYear,$FinishLastYear,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('E4', '=D4/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('G4', '=F4/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H4', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $Now,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('I4', '=H4/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Income Cash');
			$objPHPExcel->getActiveSheet()->setCellValue('D5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $StartLastYear,$FinishLastYear,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('E5', '=D5/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('G5', '=F5/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H5', -MovementAccountsBetweenPeriods(GL_INCOME_CASH, $Now,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('I5', '=H5/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C6', 'Income Others PT');
			$objPHPExcel->getActiveSheet()->setCellValue('D6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $StartLastYear,$FinishLastYear,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('E6', '=D6/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('G6', '=F6/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H6', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS_PT, $Now,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('I6', '=H6/$H$8');
			
			$objPHPExcel->getActiveSheet()->setCellValue('C7', 'Income Others');
			$objPHPExcel->getActiveSheet()->setCellValue('D7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $StartLastYear,$FinishLastYear,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('E7', '=D7/$D$8');
			$objPHPExcel->getActiveSheet()->setCellValue('F7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('G7', '=F7/$F$8');
			$objPHPExcel->getActiveSheet()->setCellValue('H7', -MovementAccountsBetweenPeriods(GL_INCOME_OTHERS, $Now,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('I7', '=H7/$H$8');

			$objPHPExcel->getActiveSheet()->setCellValue('C8', 'TOTAL INCOME');
			$objPHPExcel->getActiveSheet()->setCellValue('D8', '=SUM(D3:D7)');
			$objPHPExcel->getActiveSheet()->setCellValue('F8', '=SUM(F3:F7)');
			$objPHPExcel->getActiveSheet()->setCellValue('H8', '=SUM(H3:H7)');

			$objPHPExcel->getActiveSheet()->setCellValue('C10', 'INCOME PT');
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

			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('D3');

			// New worksheet
			$objPHPExcel->createSheet();
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setTitle('Cash PT Balance');
			$objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode('#,###');

			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'INCOME CASH PT');
			$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Income from Retail Cash PT');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', -MovementAccountsBetweenPeriods(GL_INCOME_CASH_PT, $StartThisYear,$Now,$db));
			$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Cash from Danamon to Kantor Cash');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', -MovementsFromDanamonToCashKantor($DefaultFromDate, $DefaultToDate, $db));
			$objPHPExcel->getActiveSheet()->setCellValue('A4', 'EXPENSES CASH PT');
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Expenses paid cash for Accounts PT');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', ExpensesPaidCashForAccountsPT($DefaultFromDate, $DefaultToDate, $db));
			$objPHPExcel->getActiveSheet()->setCellValue('A7', 'BALANCE CASH PT');
			$objPHPExcel->getActiveSheet()->setCellValue('B7', '=B2+B3-B5');
			

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
			include('includes/header.inc');
			prnMsg('No info selected to analyse');
			include('includes/footer.inc');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
/*	$Title = _('Excel file for Sales Analysis');

	include('includes/header.inc');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sales Analysis') . '" alt="" />' . ' ' . _('Excel file for Sales Analysis') . '
		</p>';

	echo '<table class="selection">
			<tr>
				<td>' . _('Select Inventory Categories') . ':</td>
				<td><select autofocus="autofocus" required="required" minlength="1" size="12" name="Categories[]"multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription 
			FROM stockcategory 
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';

	echo '<tr>
			<td>' . _('Item Codes detailed as') . ':</td>
			<td><select name="CodeDetail">
				<option selected="selected" value="CodeFull">' . _('Full Item Code') . '</option>
				<option value="CodeFullWithRings">' . _('Full Item Code + Rings Grouped') . '</option>
				<option value="Code6">' . _('Basic Item Code (6 Char)') . '</option>
			</select></td>
		</tr>';
	
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
			<td><input type="submit" name="submit" value="' . _('Create Sales Analysis Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');
*/
} // End of function display()

function MovementAccountsBetweenPeriods($AccountList, $PeriodFrom, $PeriodTo, $db){
	$SQL = "SELECT SUM(chartdetails.actual)
			FROM chartdetails
			WHERE chartdetails.accountcode IN " . $AccountList ."
				AND chartdetails.period >='" . $PeriodFrom . "'
				AND chartdetails.period <='" . $PeriodTo . "'";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	return $myrow[0]/JUTA;
}

function ExpensesPaidCashForAccountsPT($DateFrom, $DateTo, $db){
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
				AND pcexpenses.glaccount LIKE '%PT'";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	return $myrow[0]/JUTA;
}

function MovementsFromDanamonToCashKantor($DateFrom, $DateTo, $db){
	$SQL = "SELECT SUM(gltrans.amount)
			FROM gltrans
			WHERE gltrans.trandate >= '" . $DateFrom . "'
				AND gltrans.trandate <= '" . $DateTo . "'
				AND gltrans.account = '111121105PT'
				AND (gltrans.narrative LIKE '%CASH TO CASH%'
					OR gltrans.narrative LIKE '%BANK TO CASH%'
					OR gltrans.narrative LIKE '%UANG KECIL%')";
	$Result = DB_query($SQL);
	$myrow = DB_fetch_array($Result);
	return $myrow[0]/JUTA;
}

?>