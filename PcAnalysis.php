<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['submit'])) {
	//initialise no input errors
	$InputError = 0;
	$TabToShow = $_POST['Tabs'];
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

			// Create new PHPSpreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Petty Cash Expenses Analysis")
										 ->setSubject("Petty Cash Expenses Analysis")
										 ->setDescription("Petty Cash Expenses Analysis")
										 ->setKeywords("")
										 ->setCategory("");

			// Formatting

			$SpreadSheet->getActiveSheet()->getStyle('C:AB')->getNumberFormat()->setFormatCode('#,##0.00');
			$SpreadSheet->getActiveSheet()->getStyle('4')->getFont()->setBold(true);
			$SpreadSheet->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
			$SpreadSheet->getActiveSheet()->getStyle('A:B')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A2', 'Petty Cash Tab(s)');
			$SpreadSheet->getActiveSheet()->setCellValue('B2', $TabToShow);
			$SpreadSheet->getActiveSheet()->setCellValue('A4', 'Expense Code');
			$SpreadSheet->getActiveSheet()->setCellValue('B4', 'Description');

			$SpreadSheet->getActiveSheet()->setCellValue('C4', 'Total 12 Months');
			$SpreadSheet->getActiveSheet()->setCellValue('D4', 'Average 12 Months');

			$SpreadSheet->getActiveSheet()->setCellValue('E4', $LabelsArray[24]);
			$SpreadSheet->getActiveSheet()->setCellValue('F4', $LabelsArray[23]);
			$SpreadSheet->getActiveSheet()->setCellValue('G4', $LabelsArray[22]);
			$SpreadSheet->getActiveSheet()->setCellValue('H4', $LabelsArray[21]);
 			$SpreadSheet->getActiveSheet()->setCellValue('I4', $LabelsArray[20]);
 			$SpreadSheet->getActiveSheet()->setCellValue('J4', $LabelsArray[19]);
 			$SpreadSheet->getActiveSheet()->setCellValue('K4', $LabelsArray[18]);
 			$SpreadSheet->getActiveSheet()->setCellValue('L4', $LabelsArray[17]);
 			$SpreadSheet->getActiveSheet()->setCellValue('M4', $LabelsArray[16]);
 			$SpreadSheet->getActiveSheet()->setCellValue('N4', $LabelsArray[15]);
 			$SpreadSheet->getActiveSheet()->setCellValue('O4', $LabelsArray[14]);
 			$SpreadSheet->getActiveSheet()->setCellValue('P4', $LabelsArray[13]);
 			$SpreadSheet->getActiveSheet()->setCellValue('Q4', $LabelsArray[12]);
 			$SpreadSheet->getActiveSheet()->setCellValue('R4', $LabelsArray[11]);
 			$SpreadSheet->getActiveSheet()->setCellValue('S4', $LabelsArray[10]);
 			$SpreadSheet->getActiveSheet()->setCellValue('T4', $LabelsArray[9]);
 			$SpreadSheet->getActiveSheet()->setCellValue('U4', $LabelsArray[8]);
 			$SpreadSheet->getActiveSheet()->setCellValue('V4', $LabelsArray[7]);
 			$SpreadSheet->getActiveSheet()->setCellValue('W4', $LabelsArray[6]);
 			$SpreadSheet->getActiveSheet()->setCellValue('X4', $LabelsArray[5]);
 			$SpreadSheet->getActiveSheet()->setCellValue('Y4', $LabelsArray[4]);
 			$SpreadSheet->getActiveSheet()->setCellValue('Z4', $LabelsArray[3]);
 			$SpreadSheet->getActiveSheet()->setCellValue('AA4', $LabelsArray[2]);
 			$SpreadSheet->getActiveSheet()->setCellValue('AB4', $LabelsArray[1]);

			// Add data
			$i = 5;
			while ($MyRow = DB_fetch_array($Result)) {
				$SpreadSheet->setActiveSheetIndex(0);
				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['codeexpense']);
				$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);

				$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, '=SUM(Q'.$i.':AB'.$i.')');
				$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, '=AVERAGE(Q'.$i.':AB'.$i.')');

				$SpreadSheet->getActiveSheet()->setCellValue('E'.$i, -$MyRow['expense_period24']);
				$SpreadSheet->getActiveSheet()->setCellValue('F'.$i, -$MyRow['expense_period23']);
				$SpreadSheet->getActiveSheet()->setCellValue('G'.$i, -$MyRow['expense_period22']);
				$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, -$MyRow['expense_period21']);
				$SpreadSheet->getActiveSheet()->setCellValue('I'.$i, -$MyRow['expense_period20']);
				$SpreadSheet->getActiveSheet()->setCellValue('J'.$i, -$MyRow['expense_period19']);
				$SpreadSheet->getActiveSheet()->setCellValue('K'.$i, -$MyRow['expense_period18']);
				$SpreadSheet->getActiveSheet()->setCellValue('L'.$i, -$MyRow['expense_period17']);
				$SpreadSheet->getActiveSheet()->setCellValue('M'.$i, -$MyRow['expense_period16']);
				$SpreadSheet->getActiveSheet()->setCellValue('N'.$i, -$MyRow['expense_period15']);
				$SpreadSheet->getActiveSheet()->setCellValue('O'.$i, -$MyRow['expense_period14']);
				$SpreadSheet->getActiveSheet()->setCellValue('P'.$i, -$MyRow['expense_period13']);
				$SpreadSheet->getActiveSheet()->setCellValue('Q'.$i, -$MyRow['expense_period12']);
				$SpreadSheet->getActiveSheet()->setCellValue('R'.$i, -$MyRow['expense_period11']);
				$SpreadSheet->getActiveSheet()->setCellValue('S'.$i, -$MyRow['expense_period10']);
				$SpreadSheet->getActiveSheet()->setCellValue('T'.$i, -$MyRow['expense_period9']);
				$SpreadSheet->getActiveSheet()->setCellValue('U'.$i, -$MyRow['expense_period8']);
				$SpreadSheet->getActiveSheet()->setCellValue('V'.$i, -$MyRow['expense_period7']);
				$SpreadSheet->getActiveSheet()->setCellValue('W'.$i, -$MyRow['expense_period6']);
				$SpreadSheet->getActiveSheet()->setCellValue('X'.$i, -$MyRow['expense_period5']);
				$SpreadSheet->getActiveSheet()->setCellValue('Y'.$i, -$MyRow['expense_period4']);
				$SpreadSheet->getActiveSheet()->setCellValue('Z'.$i, -$MyRow['expense_period3']);
				$SpreadSheet->getActiveSheet()->setCellValue('AA'.$i, -$MyRow['expense_period2']);
				$SpreadSheet->getActiveSheet()->setCellValue('AB'.$i, -$MyRow['expense_period1']);

				$i++;
			}

			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('E5');

			// Auto Size columns
			for($col = 'A'; $col !== $SpreadSheet->getActiveSheet()->getHighestDataColumn(); $col++) {
				$SpreadSheet->getActiveSheet()
					->getColumnDimension($col)
					->setAutoSize(true);
}

			// Rename worksheet
			if ($TabToShow=='All'){
				$SpreadSheet->getActiveSheet()->setTitle('All Accounts');
			}else{
				$SpreadSheet->getActiveSheet()->setTitle($TabToShow);
			}
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

			$File = 'PCExpensesAnalysis-' . Date('Y-m-d'). '.' . $_POST['Format'];

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

		}else{
			$Title = __('Excel file for Petty Cash Expenses Analysis');
			include('includes/header.php');
			prnMsg('There is no data to analyse');
			include('includes/footer.php');
		}
	}
} else {
// Display form fields. This function is called the first time
// the page is called.
	$Title = __('Excel file for Petty Cash Expenses Analysis');
	$ViewTopic = 'PettyCash';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'top';// Anchor's id in the manual's html document.

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . __('Excel file for Petty Cash Expenses Analysis') . '" alt="" />' . ' ' . __('Excel file for Petty Cash Expenses Analysis') . '
		</p>';

	echo '<fieldset>
			<legend>', __('Petty Cash Tab To Analyse'), '</legend>';

	echo '<field>
			<label for="Tabs">' . __('For Petty Cash Tabs') . ':</label>
			<select name="Tabs">';

	$SQL = "SELECT tabcode
			FROM pctabs
			ORDER BY tabcode";
	$CatResult = DB_query($SQL);

	echo '<option value="All">' . __('All Tabs') . '</option>';

	while ($MyRow = DB_fetch_array($CatResult)){
		echo '<option value="' . $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';
	}
	echo '</select>
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
			<input type="submit" name="submit" value="' . __('Create Petty Cash Expenses Excel File') . '" />
		</div>';

	echo '</form>';
	include('includes/footer.php');

}

function beginning_of_month($Date){
	$Date2 = explode("-",$Date);
	$M = $Date2[1];
	$Y = $Date2[0];
	$FirstOfMonth = $Y . '-' . $M . '-01';
	return $FirstOfMonth;
}
