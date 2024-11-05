<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

if (!isset($_POST['Period'])){
	$_POST['Period'] = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
}

if (isset($_POST['submit'])) {
    submit($_POST['Period']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Period) {

	//initialise no input errors
	$InputError = 0;
	
	$CurrentMonth = $Period;
	$LastMonth = $CurrentMonth -1;
	$CurrentMonthLastYear = $CurrentMonth -12;
	
	$sql = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno = '". $CurrentMonth . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$StartOfYear = $CurrentMonth - substr($myrow['lastdate_in_period'],5,2) + 1;
	$StartOfYearLastYear = $StartOfYear - 12;
	
	//first off validate inputs sensible

	if ($InputError == 0){
		$today = date('Y-m-d');
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		
		// Set value binder
		PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("webERP")
									 ->setLastModifiedBy("webERP")
									 ->setTitle("Sales Monthly Report")
									 ->setSubject("Sales Monthly Report")
									 ->setDescription("Sales Monthly Report")
									 ->setKeywords("")
									 ->setCategory("");

		SheetSalesReport($objPHPExcel, 
						0, 
						'CatMonth01', 
						'CATEGORY',
						'SELECTED MONTH VS SAME MONTH LAST YEAR',
						'SELECTED MONTH',
						$CurrentMonth,
						$CurrentMonth,
						'SAME MONTH LAST YEAR',
						$CurrentMonthLastYear,
						$CurrentMonthLastYear);

		SheetSalesReport($objPHPExcel, 
						1, 
						'CatMonth02', 
						'CATEGORY',
						'SELECTED MONTH VS LAST MONTH',
						'SELECTED MONTH',
						$CurrentMonth,
						$CurrentMonth,
						'LAST MONTH',
						$LastMonth,
						$LastMonth);

		SheetSalesReport($objPHPExcel, 
						2, 
						'CatMonth03', 
						'CATEGORY',
						'YTD VS YTD LAST YEAR',
						'YTD',
						$StartOfYear,
						$CurrentMonth,
						'YTD LAST YEAR',
						$StartOfYearLastYear,
						$CurrentMonthLastYear);
						
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$File = 'KL-SalesMonthlyReport-' . Date('Y-m-d'). '.xlsx';
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

	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Sales Monthly Report');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sales Monthly Report') . '" alt="" />' . ' ' . _('Excel file for Sales Monthly Report') . '
		</p>';

	$sql = "SELECT periodno,
					lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";
	$Periods = DB_query($sql);

	echo '<table class="selection">';
	echo '	<tr><td>' . _('Select Month for Report') . ':</td>
				<td><select name="Period">';

	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['Period']) AND $_POST['Period']!=''){
			if( $_POST['Period']== $myrow['periodno']){
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select></td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Sales Monthly Report Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

function SalesForPeriod($PeriodFrom, $PeriodTo, $CategoryList){
	
	$SQL = "SELECT SUM(amt-disc) AS sales,
					SUM(qty) AS pcs,
					SUM(cost) AS grosscost
			FROM salesanalysis
			WHERE periodno >= '". $PeriodFrom. "'
				AND periodno <= '". $PeriodTo. "'
				AND stkcategory IN " . $CategoryList ."";
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	
	return array ($myrow['sales'], $myrow['pcs'], $myrow['grosscost']);
}

function TitleCells($objPHPExcel, $Title, $Start, $End){
	$objPHPExcel->getActiveSheet()->setCellValue($Start, $Title);
	$objPHPExcel->getActiveSheet()->mergeCells($Start .":". $End);
	$objPHPExcel->getActiveSheet()->getStyle($Start .":". $End)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);		
	$objPHPExcel->getActiveSheet()->getStyle($Start .":". $End)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);		
	$objPHPExcel->getActiveSheet()->getStyle($Start .":". $End)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
}


function SheetSalesReport($objPHPExcel, 
						$SheetCode, 
						$SheetTitle, 
						$TitleBy1,
						$TitleBy2,
						$TitlePeriodAB,
						$MonthA,
						$MonthB,
						$TitlePeriodCD,
						$MonthC,
						$MonthD){

	// Select sheet
	if ($SheetCode > 0){
		$objPHPExcel->createSheet();
	}
	$objPHPExcel->setActiveSheetIndex($SheetCode);

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle($SheetTitle);

	// Add cell formats
	$objPHPExcel->getActiveSheet()->getStyle('B:L')->getNumberFormat()->setFormatCode('#,###');
	$objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('0.0%');
	$objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()->setFormatCode('0.0%');
	$objPHPExcel->getActiveSheet()->getStyle('J:K')->getNumberFormat()->setFormatCode('0.0%');
	$objPHPExcel->getActiveSheet()->getStyle('M:O')->getNumberFormat()->setFormatCode('0.0%');
	
	// Titles
	TitleCells($objPHPExcel, $TitleBy1, 'A3', 'A6');
	TitleCells($objPHPExcel, $TitleBy2, 'B3', 'O3');
	TitleCells($objPHPExcel, 'SALES', 'B4', 'H4');
	TitleCells($objPHPExcel, 'GROSS PROFIT', 'I4', 'O4');
	TitleCells($objPHPExcel, $TitlePeriodAB, 'B5', 'D5');
	TitleCells($objPHPExcel, $TitlePeriodCD, 'E5', 'G5');
	TitleCells($objPHPExcel, $TitlePeriodAB, 'I5', 'K5');
	TitleCells($objPHPExcel, $TitlePeriodCD, 'L5', 'N5');
	TitleCells($objPHPExcel, 'Sales IDR', 'B6', 'B6');
	TitleCells($objPHPExcel, 'Pcs', 'C6', 'C6');
	TitleCells($objPHPExcel, 'Cont', 'D6', 'D6');
	TitleCells($objPHPExcel, 'Sales IDR', 'E6', 'E6');
	TitleCells($objPHPExcel, 'Pcs', 'F6', 'F6');
	TitleCells($objPHPExcel, 'Cont', 'G6', 'G6');
	TitleCells($objPHPExcel, 'Var %', 'H5', 'H6');
	TitleCells($objPHPExcel, 'Gross Profit', 'I6', 'I6');
	TitleCells($objPHPExcel, 'GP %', 'J6', 'J6');
	TitleCells($objPHPExcel, 'Cont', 'K6', 'K6');
	TitleCells($objPHPExcel, 'Gross Profit', 'L6', 'L6');
	TitleCells($objPHPExcel, 'GP %', 'M6', 'M6');
	TitleCells($objPHPExcel, 'Cont', 'N6', 'N6');
	TitleCells($objPHPExcel, 'Var %', 'O5', 'O6');
	
	// Get data for current Month
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthA, $MonthB, LIST_STOCK_CATEGORIES_KAPAL_LAUT);						 
	$objPHPExcel->getActiveSheet()->setCellValue('A7', "STABLE KL");
	$objPHPExcel->getActiveSheet()->setCellValue('B7', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('C7', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('I7', $SalesMonth-$CostMonth);
								 
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthA, $MonthB, LIST_STOCK_CATEGORIES_BLINK);						 
	$objPHPExcel->getActiveSheet()->setCellValue('A8', "BLINK JEWELLERY");
	$objPHPExcel->getActiveSheet()->setCellValue('B8', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('C8', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('I8', $SalesMonth-$CostMonth);
	

	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthA, $MonthB, LIST_STOCK_CATEGORIES_GENERAL);						 
	$objPHPExcel->getActiveSheet()->setCellValue('A10', "ACCESSORIES");
	$objPHPExcel->getActiveSheet()->setCellValue('B10', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('C10', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('I10', $SalesMonth-$CostMonth);
								 
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthA, $MonthB, LIST_STOCK_CATEGORIES_CONSIGNMENT);						 
	$objPHPExcel->getActiveSheet()->setCellValue('A11', "CONSIGNMENT");
	$objPHPExcel->getActiveSheet()->setCellValue('B11', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('C11', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('I11', $SalesMonth-$CostMonth);

	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthA, $MonthB, LIST_STOCK_CATEGORIES_OUTLET);						 
	$objPHPExcel->getActiveSheet()->setCellValue('A12', "DISCOUNTED");
	$objPHPExcel->getActiveSheet()->setCellValue('B12', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('C12', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('I12', $SalesMonth-$CostMonth);

	
	// Get data for current month last year
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthC, $MonthD, LIST_STOCK_CATEGORIES_KAPAL_LAUT);						 
	$objPHPExcel->getActiveSheet()->setCellValue('E7', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('F7', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('L7', $SalesMonth-$CostMonth);
								 
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthC, $MonthD, LIST_STOCK_CATEGORIES_BLINK);						 
	$objPHPExcel->getActiveSheet()->setCellValue('E8', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('F8', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('L8', $SalesMonth-$CostMonth);
	
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthC, $MonthD, LIST_STOCK_CATEGORIES_GENERAL);						 
	$objPHPExcel->getActiveSheet()->setCellValue('E10', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('F10', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('L10', $SalesMonth-$CostMonth);
								 
	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthC, $MonthD, LIST_STOCK_CATEGORIES_CONSIGNMENT);						 
	$objPHPExcel->getActiveSheet()->setCellValue('E11', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('F11', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('L11', $SalesMonth-$CostMonth);

	list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($MonthC, $MonthD, LIST_STOCK_CATEGORIES_OUTLET);						 
	$objPHPExcel->getActiveSheet()->setCellValue('E12', $SalesMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('F12', $PcsMonth);
	$objPHPExcel->getActiveSheet()->setCellValue('L12', $SalesMonth-$CostMonth);

	// Get the calculations done :-)
	
	// Totals
	$objPHPExcel->getActiveSheet()->setCellValue('A13', "TOTAL");
	$objPHPExcel->getActiveSheet()->setCellValue('B13', '=SUM(B7:B12)');
	$objPHPExcel->getActiveSheet()->setCellValue('C13', '=SUM(C7:C12)');
	$objPHPExcel->getActiveSheet()->setCellValue('D13', '=SUM(D7:D12)');
	$objPHPExcel->getActiveSheet()->setCellValue('E13', '=SUM(E7:E12)');
	$objPHPExcel->getActiveSheet()->setCellValue('F13', '=SUM(F7:F12)');
	$objPHPExcel->getActiveSheet()->setCellValue('G13', '=SUM(G7:G12)');
	$objPHPExcel->getActiveSheet()->setCellValue('I13', '=SUM(I7:I12)');
	$objPHPExcel->getActiveSheet()->setCellValue('K13', '=SUM(K7:K12)');
	$objPHPExcel->getActiveSheet()->setCellValue('L13', '=SUM(L7:L12)');
	$objPHPExcel->getActiveSheet()->setCellValue('M13', '=SUM(M7:M12)');
	$objPHPExcel->getActiveSheet()->setCellValue('N13', '=SUM(N7:N12)');
	$objPHPExcel->getActiveSheet()->setCellValue('O13', '=SUM(O7:O12)');
	
	// Current Month Sales Contribution
	$objPHPExcel->getActiveSheet()->setCellValue('D7', '=B7/B13');
	$objPHPExcel->getActiveSheet()->setCellValue('D8', '=B8/B13');
	$objPHPExcel->getActiveSheet()->setCellValue('D9', '=B9/B13');
	$objPHPExcel->getActiveSheet()->setCellValue('D10', '=B10/B13');
	$objPHPExcel->getActiveSheet()->setCellValue('D11', '=B11/B13');
	$objPHPExcel->getActiveSheet()->setCellValue('D12', '=B12/B13');

	// Current Month Last Year Sales Contribution
	$objPHPExcel->getActiveSheet()->setCellValue('G7', '=E7/E13');
	$objPHPExcel->getActiveSheet()->setCellValue('G8', '=E8/E13');
	$objPHPExcel->getActiveSheet()->setCellValue('G9', '=E9/E13');
	$objPHPExcel->getActiveSheet()->setCellValue('G10', '=E10/E13');
	$objPHPExcel->getActiveSheet()->setCellValue('G11', '=E11/E13');
	$objPHPExcel->getActiveSheet()->setCellValue('G12', '=E12/E13');

	// Variation Current Month vs Current Month Last Year
	$objPHPExcel->getActiveSheet()->setCellValue('H7', '=(B7-E7)/E7');
	$objPHPExcel->getActiveSheet()->setCellValue('H8', '=(B8-E8)/E8');
	$objPHPExcel->getActiveSheet()->setCellValue('H9', '=(B9-E9)/E9');
	$objPHPExcel->getActiveSheet()->setCellValue('H10', '=(B10-E10)/E10');
	$objPHPExcel->getActiveSheet()->setCellValue('H11', '=(B11-E11)/E11');
	$objPHPExcel->getActiveSheet()->setCellValue('H12', '=(B12-E12)/E12');
	$objPHPExcel->getActiveSheet()->setCellValue('H13', '=(B13-E13)/E13');

	// Gross Profit % Current Month
	$objPHPExcel->getActiveSheet()->setCellValue('J7', '=I7/B7');
	$objPHPExcel->getActiveSheet()->setCellValue('J8', '=I8/B8');
	$objPHPExcel->getActiveSheet()->setCellValue('J9', '=I9/B9');
	$objPHPExcel->getActiveSheet()->setCellValue('J10', '=I10/B10');
	$objPHPExcel->getActiveSheet()->setCellValue('J11', '=I11/B11');
	$objPHPExcel->getActiveSheet()->setCellValue('J12', '=I12/B12');
	$objPHPExcel->getActiveSheet()->setCellValue('J13', '=I13/B13');

	// Gross Profit % Current Month Contribution
	$objPHPExcel->getActiveSheet()->setCellValue('K7', '=I7/I13');
	$objPHPExcel->getActiveSheet()->setCellValue('K8', '=I8/I13');
	$objPHPExcel->getActiveSheet()->setCellValue('K9', '=I9/I13');
	$objPHPExcel->getActiveSheet()->setCellValue('K10', '=I10/I13');
	$objPHPExcel->getActiveSheet()->setCellValue('K11', '=I11/I13');
	$objPHPExcel->getActiveSheet()->setCellValue('K12', '=I12/I13');

	// Gross Profit % Current Month Last Year
	$objPHPExcel->getActiveSheet()->setCellValue('M7', '=L7/E7');
	$objPHPExcel->getActiveSheet()->setCellValue('M8', '=L8/E8');
	$objPHPExcel->getActiveSheet()->setCellValue('M9', '=L9/E9');
	$objPHPExcel->getActiveSheet()->setCellValue('M10', '=L10/E10');
	$objPHPExcel->getActiveSheet()->setCellValue('M11', '=L11/E11');
	$objPHPExcel->getActiveSheet()->setCellValue('M12', '=L12/E12');
	$objPHPExcel->getActiveSheet()->setCellValue('M13', '=L13/E13');

	// Gross Profit % Current Month Last Year Contribution
	$objPHPExcel->getActiveSheet()->setCellValue('N7', '=L7/L13');
	$objPHPExcel->getActiveSheet()->setCellValue('N8', '=L8/L13');
	$objPHPExcel->getActiveSheet()->setCellValue('N9', '=L9/L13');
	$objPHPExcel->getActiveSheet()->setCellValue('N10', '=L10/L13');
	$objPHPExcel->getActiveSheet()->setCellValue('N11', '=L11/L13');
	$objPHPExcel->getActiveSheet()->setCellValue('N12', '=L12/L13');

	// Variation Gross profit Current Month vs Current Month Last Year
	$objPHPExcel->getActiveSheet()->setCellValue('O7', '=(I7-L7)/L7');
	$objPHPExcel->getActiveSheet()->setCellValue('O8', '=(I8-L8)/L8');
	$objPHPExcel->getActiveSheet()->setCellValue('O9', '=(I9-L9)/L9');
	$objPHPExcel->getActiveSheet()->setCellValue('O10', '=(I10-L10)/L10');
	$objPHPExcel->getActiveSheet()->setCellValue('O11', '=(I11-L11)/L11');
	$objPHPExcel->getActiveSheet()->setCellValue('O12', '=(I12-L12)/L12');
	$objPHPExcel->getActiveSheet()->setCellValue('O13', '=(I13-L13)/L13');

	// Auto Size columns
	foreach(range('A','O') as $columnID) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
	}
	
	
}

?>