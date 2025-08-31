<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php'); 
include('includes/KLGeneralFunctions.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (!isset($_POST['CodeDetail'])) {
    $_POST['CodeDetail'] = 'CODE_FULL';
}

if (isset($_POST['submit'])) {
    submit($_POST['Categories'], $_POST['FromDate'], $_POST['ToDate'], $_POST['CodeDetail']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($ListCategories, $FromDate, $ToDate, $CodeDetail) {

	//initialise no input errors
	$InputError = 0;
	ob_start(); // Start output buffering

	//first off validate inputs sensible

	if ($InputError == 0){
		$Today = date('Y-m-d');
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		
		if ($CodeDetail == 'CODE_FULL'){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,
							stockmaster.lastcategoryupdate,
							(stockmaster.actualcost) AS standardcost,
							stockmaster.discountcategory,
							(SELECT supplierno
								FROM purchdata
								WHERE purchdata.stockid = stockmaster.stockid
									AND preferred = 1
								ORDER BY effectivefrom DESC
								LIMIT 1) AS preferredsupplier
					FROM stockmaster
					WHERE stockmaster.discontinued = 0
						AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					ORDER BY stockmaster.stockid";
		}elseif($CodeDetail == 'CODE_FULL_WITH_RINGS'){
			$SQL = "SELECT CASE WHEN SUBSTRING(stockmaster.stockid,3,2) = 'AN' THEN SUBSTRING(stockmaster.stockid,1,6) ELSE stockmaster.stockid END AS stockid,
							stockmaster.description,
							stockmaster.categoryid,
							stockmaster.lastcategoryupdate,
							(stockmaster.actualcost) AS standardcost,
							stockmaster.discountcategory,
							(SELECT supplierno
								FROM purchdata
								WHERE purchdata.stockid = stockmaster.stockid
									AND preferred = 1
								ORDER BY effectivefrom DESC
								LIMIT 1) AS preferredsupplier
					FROM stockmaster
					WHERE stockmaster.discontinued = 0
						AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					GROUP BY stockid
					ORDER BY stockid";
		}else{
			$SQL = "SELECT SUBSTRING(stockmaster.stockid,1,6) AS stockid,
							COUNT(stockmaster.stockid) AS flavours,
							AVG (stockmaster.actualcost) AS standardcost
					FROM stockmaster
					WHERE stockmaster.discontinued = 0
						AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					GROUP BY SUBSTRING(stockmaster.stockid,1,6)
					ORDER BY SUBSTRING(stockmaster.stockid,1,6)";
		}
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			
			// Set value binder
			\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());

			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sales Analysis")
										 ->setSubject("Sales Analysis")
										 ->setDescription("Sales Analysis")
										 ->setKeywords("")
										 ->setCategory("");

			$SpreadSheet->getActiveSheet()->getStyle('A:AZ')->getNumberFormat()->setFormatCode('#,###');
			$SpreadSheet->getActiveSheet()->getStyle('R')->getNumberFormat()->setFormatCode('#,##0.0');
			$SpreadSheet->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');
			$SpreadSheet->getActiveSheet()->getStyle('B3:C3')->getNumberFormat()->setFormatCode('#,##0');
			$SpreadSheet->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
		
			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);

			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'Sales From:');
			$SpreadSheet->getActiveSheet()->setCellValue('B2', 'Sales To:');
			$SpreadSheet->getActiveSheet()->setCellValue('B3', '# Days:');
 			$SpreadSheet->getActiveSheet()->setCellValue('B4', 'Optimum Stock days');

			$SpreadSheet->getActiveSheet()->setCellValue('C1', ConvertSQLDate($FromDate));
			$SpreadSheet->getActiveSheet()->setCellValue('C2', ConvertSQLDate($ToDate));
			$SpreadSheet->getActiveSheet()->setCellValue('C3', '=C2-C1');
 			$SpreadSheet->getActiveSheet()->setCellValue('C4', 150);

			if ($CodeDetail == 'CODE_FULL'){
				$SpreadSheet->getActiveSheet()->setCellValue('E1', 'ALL CODES');
			}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
				$SpreadSheet->getActiveSheet()->setCellValue('E1', 'RINGS GROUPED');
			}else{
				$SpreadSheet->getActiveSheet()->setCellValue('E1', '6 LETTER CODES');
			}

			$SpreadSheet->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			if ($CodeDetail == 'CODE_FULL'){
				$ColumnTitle = 'DESCRIPTION';
			}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
				$ColumnTitle = 'TEXT';
			}else{
				$ColumnTitle = 'FLAVOURS';
			}
			$SpreadSheet->getActiveSheet()->setCellValue('B5', $ColumnTitle);
			
			$SpreadSheet->getActiveSheet()->setCellValue('C5', 'CATEGORY');
			$SpreadSheet->getActiveSheet()->setCellValue('D5', 'FAMILY');
			$SpreadSheet->getActiveSheet()->setCellValue('E5', 'TYPE');
			$SpreadSheet->getActiveSheet()->setCellValue('F5', 'DOB_CATEGORY');

			$SpreadSheet->getActiveSheet()->setCellValue('G5', 'STANDARD_COST');

			$SpreadSheet->getActiveSheet()->setCellValue('H5', 'DISCOUNT');
			$SpreadSheet->getActiveSheet()->setCellValue('I5', 'AVG_PRICE');

			$SpreadSheet->getActiveSheet()->setCellValue('J5', 'QOH');
 			$SpreadSheet->getActiveSheet()->setCellValue('K5', 'STOCK_VALUE');

			$SpreadSheet->getActiveSheet()->setCellValue('L5', 'QOO');
 			$SpreadSheet->getActiveSheet()->setCellValue('M5', 'ORDER_VALUE');

  			$SpreadSheet->getActiveSheet()->setCellValue('N5', 'PCS_SOLD');
 			$SpreadSheet->getActiveSheet()->setCellValue('O5', 'SALES_VALUE');
 			$SpreadSheet->getActiveSheet()->setCellValue('P5', 'COST_VALUE');
 			$SpreadSheet->getActiveSheet()->setCellValue('Q5', 'GROSS_MARGIN');

			$SpreadSheet->getActiveSheet()->setCellValue('R5', 'SALES/DAY');
 			$SpreadSheet->getActiveSheet()->setCellValue('S5', 'DAYS_QOH');
 			$SpreadSheet->getActiveSheet()->setCellValue('T5', 'DAYS_QOO');
 			$SpreadSheet->getActiveSheet()->setCellValue('U5', 'DAYS QOH+QOO');

 			$SpreadSheet->getActiveSheet()->setCellValue('V5', 'PCS TO PO/WO');
 			$SpreadSheet->getActiveSheet()->setCellValue('W5', 'SUPPLIER');

			// Add data
			$StartingRow = 6;
			$i = $StartingRow;
			$LastStockid = '';
			while ($MyRow = DB_fetch_array($Result)) {
				$SpreadSheet->setActiveSheetIndex(0);

				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['stockid']);
				
				if ($LastStockid != $MyRow['stockid']){
				
					if ($CodeDetail == 'CODE_FULL'){
						$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);
						$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, $MyRow['categoryid']);
					}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
						$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);
						$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, $MyRow['categoryid']);
					}else{
						$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, round($MyRow['flavours'],0));
					}

					$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, substr($MyRow['stockid'], 0,2));
					$SpreadSheet->getActiveSheet()->setCellValue('E'.$i, TypeOfItem($MyRow['stockid']));

					if ($CodeDetail != 'CODE_6'){
						$SpreadSheet->getActiveSheet()->setCellValue('F'.$i, ConvertSQLDate($MyRow['lastcategoryupdate']));
					}
					
					$SpreadSheet->getActiveSheet()->setCellValue('G'.$i, round($MyRow['standardcost'],0));

					if ($CodeDetail != 'CODE_6'){
						$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, $MyRow['discountcategory']);
					}
					
					$SpreadSheet->getActiveSheet()->setCellValue('I'.$i, round(ItemCodeAvgPriceInvoiced($MyRow['stockid'],$FromDate,$ToDate,'',$CodeDetail),0));

					$SpreadSheet->getActiveSheet()->setCellValue('J'.$i, round(ItemCodeQOH($MyRow['stockid'],$CodeDetail, "ALL"),0));
					$SpreadSheet->getActiveSheet()->setCellValue('K'.$i, '=G'.$i.'*J'.$i.'');

					$SpreadSheet->getActiveSheet()->setCellValue('L'.$i, round(ItemCodeQOO_PurchaseOrders($MyRow['stockid'],$CodeDetail)+ItemCodeQOO_WorkOrders($MyRow['stockid'],$CodeDetail),0));
					$SpreadSheet->getActiveSheet()->setCellValue('M'.$i, '=G'.$i.'*L'.$i.'');

					$SpreadSheet->getActiveSheet()->setCellValue('N'.$i, round(ItemCodeQuantityInvoiced($MyRow['stockid'],$FromDate,$ToDate,'',$CodeDetail),0));
					$SpreadSheet->getActiveSheet()->setCellValue('O'.$i, '=N'.$i.'*I'.$i.'');
					$SpreadSheet->getActiveSheet()->setCellValue('P'.$i, '=N'.$i.'*G'.$i.'');
					$SpreadSheet->getActiveSheet()->setCellValue('Q'.$i, '=O'.$i.'-P'.$i.'');

					$SpreadSheet->getActiveSheet()->setCellValue('R'.$i, '=N'.$i.'/$C$3');
					$SpreadSheet->getActiveSheet()->setCellValue('S'.$i, '=IF(R'.$i.'>0,J'.$i.'/R'.$i.',99999)'.'');
					$SpreadSheet->getActiveSheet()->setCellValue('T'.$i, '=IF(R'.$i.'>0,L'.$i.'/R'.$i.',99999)'.'');
					$SpreadSheet->getActiveSheet()->setCellValue('U'.$i, '=S'.$i.'+T'.$i.'');
					
					$SpreadSheet->getActiveSheet()->setCellValue('V'.$i, '=IF(U'.$i.'<$C$4,ROUNDUP(($C$4-U'.$i.')*R'.$i.',0),"")'.'');
					$SpreadSheet->getActiveSheet()->setCellValue('W'.$i, $MyRow['preferredsupplier']);
					
					$LastStockid = $MyRow['stockid'];
					$i++;
				}
			}
			
			// Calculating totals, subtotals, etc
			$SpreadSheet->getActiveSheet()->setCellValue('A1', '=COUNTA(A'.$StartingRow.':A'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('J1', '=SUM(J'.$StartingRow.':J'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('K1', '=SUM(K'.$StartingRow.':K'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('L1', '=SUM(L'.$StartingRow.':L'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('M1', '=SUM(M'.$StartingRow.':M'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('N1', '=SUM(N'.$StartingRow.':N'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('O1', '=SUM(O'.$StartingRow.':O'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('P1', '=SUM(P'.$StartingRow.':P'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('Q1', '=SUM(Q'.$StartingRow.':Q'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('V1', '=SUM(V'.$StartingRow.':V'.$i.')');

			$SpreadSheet->getActiveSheet()->setCellValue('A2', '=SUBTOTAL(3,A'.$StartingRow.':A'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('J2', '=SUBTOTAL(9,J'.$StartingRow.':J'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('K2', '=SUBTOTAL(9,K'.$StartingRow.':K'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('L2', '=SUBTOTAL(9,L'.$StartingRow.':L'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('M2', '=SUBTOTAL(9,M'.$StartingRow.':M'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('N2', '=SUBTOTAL(9,N'.$StartingRow.':N'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('O2', '=SUBTOTAL(9,O'.$StartingRow.':O'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('P2', '=SUBTOTAL(9,P'.$StartingRow.':P'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('Q2', '=SUBTOTAL(9,Q'.$StartingRow.':Q'.$i.')');
			$SpreadSheet->getActiveSheet()->setCellValue('V2', '=SUBTOTAL(9,V'.$StartingRow.':V'.$i.')');

			$SpreadSheet->getActiveSheet()->setCellValue('A3', '=A2/A1');
			$SpreadSheet->getActiveSheet()->setCellValue('J3', '=J2/J1');
			$SpreadSheet->getActiveSheet()->setCellValue('K3', '=K2/K1');
			$SpreadSheet->getActiveSheet()->setCellValue('L3', '=L2/L1');
			$SpreadSheet->getActiveSheet()->setCellValue('M3', '=M2/M1');
			$SpreadSheet->getActiveSheet()->setCellValue('N3', '=N2/N1');
			$SpreadSheet->getActiveSheet()->setCellValue('O3', '=O2/O1');
			$SpreadSheet->getActiveSheet()->setCellValue('P3', '=P2/P1');
			$SpreadSheet->getActiveSheet()->setCellValue('Q3', '=Q2/Q1');
			$SpreadSheet->getActiveSheet()->setCellValue('V3', '=V2/V1');
		
			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('B6');

			// Set auto filter
			$SpreadSheet->getActiveSheet()->setAutoFilter('A5:AL' . $i);
			
			// Auto Size columns
			foreach(range('A','AL') as $ColumnID) {
				$SpreadSheet->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$SpreadSheet->getActiveSheet()->setTitle('Sales Analysis');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			 // Clean (erase) the output buffer and turn off output buffering
			ob_end_clean();

			// Redirect output to a client's web browser
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = 'KL-SalesAnalysis-' . Date('Y-m-d'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = 'KL-SalesAnalysis-' . Date('Y-m-d'). '.ods';
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
			} else if ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
			}
			$objWriter->save('php://output');
			exit(); // Ensure no further output is sent

		}else{
			$Title = __('Excel file for Sales Analysis');
			include('includes/header.php');
			prnMsg('No items selected to analyse');
			include('includes/footer.php');
		}
	}
} // End of function submit()

//####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####

function display($RootPath, $Theme){
// Display form fields. This function is called the first time
// the page is called.
	$Title = __('Excel file for Sales Analysis');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Excel file for Sales Analysis') . '" alt="" />' . ' ' . __('Excel file for Sales Analysis') . '
		</p>';

	echo '<fieldset>
			<legend>' . __('Report Parameters') . '</legend>';

	echo FieldToSelectMultipleStockCategories('Categories', isset($_POST['Categories']) ? $_POST['Categories'] : array(), __('Select Inventory Categories'), '', '', 1, true, true);

	echo FieldToSelectFromThreeOptions('CODE_FULL', __('Full Item Code'),
									'CODE_FULL_WITH_RINGS', __('Full Item Code + Rings Grouped'),
									'CODE_6', __('Basic Item Code (6 Char)'),
									'CodeDetail', $_POST['CodeDetail'],	__('Item Codes detailed as'), '', '', 2, true, false);
	echo FieldToSelectOneDate('FromDate', $_POST['FromDate'], __('From'), '', '', 3, true, false);
	echo FieldToSelectOneDate('ToDate', $_POST['ToDate'], __('To'), '', '', 4, true, false);
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], __('File Format'));

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Export Sales Analysis File'));

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()
