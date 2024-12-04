<?php

require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['submit'])) {
    submit($_POST['Tabs']);
} else {
    display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($TabToShow) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		// Creation of beginning of SQL query
		$SQL = "SELECT pcexpenses.codeexpense,";

		// Creation of periods SQL query
		$PeriodToday=GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$SQLPeriods = "SELECT periodno,
						lastdate_in_period
				FROM periods
				WHERE periodno <= ". $PeriodToday ."
				ORDER BY periodno DESC
				LIMIT 24";
		$Periods = DB_query($SQLPeriods);
		$NumPeriod = 0;
		$LabelsArray = array();
		while ($MyRow=DB_fetch_array($Periods)){

			$NumPeriod++;
			$LabelsArray[$NumPeriod] = MonthAndYearFromSQLDate($MyRow['lastdate_in_period']);
			$SQL = $SQL . "(SELECT SUM(pcashdetails.amount)
							FROM pcashdetails
							WHERE pcashdetails.codeexpense = pcexpenses.codeexpense";
			if ($TabToShow!='All'){
				$SQL = $SQL." 	AND pcashdetails.tabcode = '". $TabToShow ."'";
			}
			$SQL = $SQL . "		AND date >= '" . beginning_of_month($MyRow['lastdate_in_period']). "'
								AND date <= '" . $MyRow['lastdate_in_period'] . "') AS expense_period".$NumPeriod.", ";
		}
		// Creation of final part of SQL
		$SQL = $SQL." pcexpenses.description
				FROM  pcexpenses
				ORDER BY pcexpenses.codeexpense";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Petty Cash Expenses Analysis")
										 ->setSubject("Petty Cash Expenses Analysis")
										 ->setDescription("Petty Cash Expenses Analysis")
										 ->setKeywords("")
										 ->setCategory("");

			// Formatting

			$objPHPExcel->getActiveSheet()->getStyle('C:AB')->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle('4')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A:B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Petty Cash Tab(s)');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', $TabToShow);
			$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Expense Code');
			$objPHPExcel->getActiveSheet()->setCellValue('B4', 'Description');

			$objPHPExcel->getActiveSheet()->setCellValue('C4', 'Total 12 Months');
			$objPHPExcel->getActiveSheet()->setCellValue('D4', 'Average 12 Months');

			$objPHPExcel->getActiveSheet()->setCellValue('E4', $LabelsArray[24]);
			$objPHPExcel->getActiveSheet()->setCellValue('F4', $LabelsArray[23]);
			$objPHPExcel->getActiveSheet()->setCellValue('G4', $LabelsArray[22]);
			$objPHPExcel->getActiveSheet()->setCellValue('H4', $LabelsArray[21]);
 			$objPHPExcel->getActiveSheet()->setCellValue('I4', $LabelsArray[20]);
 			$objPHPExcel->getActiveSheet()->setCellValue('J4', $LabelsArray[19]);
 			$objPHPExcel->getActiveSheet()->setCellValue('K4', $LabelsArray[18]);
 			$objPHPExcel->getActiveSheet()->setCellValue('L4', $LabelsArray[17]);
 			$objPHPExcel->getActiveSheet()->setCellValue('M4', $LabelsArray[16]);
 			$objPHPExcel->getActiveSheet()->setCellValue('N4', $LabelsArray[15]);
 			$objPHPExcel->getActiveSheet()->setCellValue('O4', $LabelsArray[14]);
 			$objPHPExcel->getActiveSheet()->setCellValue('P4', $LabelsArray[13]);
 			$objPHPExcel->getActiveSheet()->setCellValue('Q4', $LabelsArray[12]);
 			$objPHPExcel->getActiveSheet()->setCellValue('R4', $LabelsArray[11]);
 			$objPHPExcel->getActiveSheet()->setCellValue('S4', $LabelsArray[10]);
 			$objPHPExcel->getActiveSheet()->setCellValue('T4', $LabelsArray[9]);
 			$objPHPExcel->getActiveSheet()->setCellValue('U4', $LabelsArray[8]);
 			$objPHPExcel->getActiveSheet()->setCellValue('V4', $LabelsArray[7]);
 			$objPHPExcel->getActiveSheet()->setCellValue('W4', $LabelsArray[6]);
 			$objPHPExcel->getActiveSheet()->setCellValue('X4', $LabelsArray[5]);
 			$objPHPExcel->getActiveSheet()->setCellValue('Y4', $LabelsArray[4]);
 			$objPHPExcel->getActiveSheet()->setCellValue('Z4', $LabelsArray[3]);
 			$objPHPExcel->getActiveSheet()->setCellValue('AA4', $LabelsArray[2]);
 			$objPHPExcel->getActiveSheet()->setCellValue('AB4', $LabelsArray[1]);

			// Add data
			$i = 5;
			while ($MyRow = DB_fetch_array($Result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $MyRow['codeexpense']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);

				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, '=SUM(Q'.$i.':AB'.$i.')');
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '=AVERAGE(Q'.$i.':AB'.$i.')');

				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, -$MyRow['expense_period24']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, -$MyRow['expense_period23']);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, -$MyRow['expense_period22']);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, -$MyRow['expense_period21']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, -$MyRow['expense_period20']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, -$MyRow['expense_period19']);
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, -$MyRow['expense_period18']);
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, -$MyRow['expense_period17']);
				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, -$MyRow['expense_period16']);
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, -$MyRow['expense_period15']);
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, -$MyRow['expense_period14']);
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, -$MyRow['expense_period13']);
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, -$MyRow['expense_period12']);
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, -$MyRow['expense_period11']);
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, -$MyRow['expense_period10']);
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, -$MyRow['expense_period9']);
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, -$MyRow['expense_period8']);
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, -$MyRow['expense_period7']);
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, -$MyRow['expense_period6']);
				$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, -$MyRow['expense_period5']);
				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, -$MyRow['expense_period4']);
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, -$MyRow['expense_period3']);
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, -$MyRow['expense_period2']);
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, -$MyRow['expense_period1']);

				$i++;
			}

			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('E5');

			// Auto Size columns
			for($col = 'A'; $col !== $objPHPExcel->getActiveSheet()->getHighestDataColumn(); $col++) {
				$objPHPExcel->getActiveSheet()
					->getColumnDimension($col)
					->setAutoSize(true);
}

			// Rename worksheet
			if ($TabToShow=='All'){
				$objPHPExcel->getActiveSheet()->setTitle('All Accounts');
			}else{
				$objPHPExcel->getActiveSheet()->setTitle($TabToShow);
			}
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'PCExpensesAnalysis-' . Date('Y-m-d'). '.xlsx';
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
			$Title = _('Excel file for Petty Cash Expenses Analysis');
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
	$Title = _('Excel file for Petty Cash Expenses Analysis');
	$ViewTopic = 'PettyCash';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'top';// Anchor's id in the manual's html document.

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Excel file for Petty Cash Expenses Analysis') . '" alt="" />' . ' ' . _('Excel file for Petty Cash Expenses Analysis') . '
		</p>';

	echo '<table class="selection">
		<tr>
		<td>' . _('For Petty Cash Tabs') . ':</td>
		<td><select name="Tabs">';

	$SQL = "SELECT tabcode
			FROM pctabs
			ORDER BY tabcode";
	$CatResult = DB_query($SQL);

	echo '<option value="All">' . _('All Tabs') . '</option>';

	while ($MyRow = DB_fetch_array($CatResult)){
		echo '<option value="' . $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';
	}
	echo '</select>
			</td>
		</tr>';

	echo '
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Petty Cash Expenses Excel File') . '" /></td>
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