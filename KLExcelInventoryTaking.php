<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (!isset($_POST['Categories'])) {
	$_POST['Categories'] = [];
}
if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation'] = '';
}
if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (isset($_POST['submit'])) {
    submit($_POST['Categories'], $_POST['StockLocation']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($ListCategories, $Location) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		$Now = date('Y-m-d H-i-s');

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.categoryid,
						locstock.quantity,
						(SELECT SUM(pendingqty)
							FROM loctransfers
							WHERE loctransfers.stockid = locstock.stockid
								AND shiploc='" . $Location . "') AS intransitout,
						(SELECT SUM(pendingqty) as intransit
							FROM loctransfers
							WHERE loctransfers.stockid = locstock.stockid
								AND recloc='" . $Location . "') AS intransitin
				FROM locstock, stockmaster
				WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = '" . $Location . "'
					AND stockmaster.discontinued = 0
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				ORDER BY stockmaster.stockid";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			
			// Set value binder
			\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
		
			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Inventory Taking")
										 ->setSubject("Inventory Taking")
										 ->setDescription("Inventory Taking")
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setTitle('Inventory');

			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'Location:');
			$SpreadSheet->getActiveSheet()->setCellValue('B2', 'Date:');
//			$SpreadSheet->getActiveSheet()->setCellValue('B3', 'Category:');
 
			$SpreadSheet->getActiveSheet()->setCellValue('C1', $Location);
			$SpreadSheet->getActiveSheet()->setCellValue('C2', $Now);
//			$SpreadSheet->getActiveSheet()->setCellValue('C3', implode("','",$_POST['Categories']));
 			$SpreadSheet->getActiveSheet()->setCellValue('C3', 'COPY the formula of H6 until the end of stock');

			$SpreadSheet->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			$SpreadSheet->getActiveSheet()->setCellValue('B5', 'DESCRIPTION');
			$SpreadSheet->getActiveSheet()->setCellValue('C5', 'CATEGORY');

			$SpreadSheet->getActiveSheet()->setCellValue('D5', 'QOH');
			$SpreadSheet->getActiveSheet()->setCellValue('E5', 'TRANSIT OUT');
			$SpreadSheet->getActiveSheet()->setCellValue('F5', 'TRANSIT IN');

			$SpreadSheet->getActiveSheet()->setCellValue('G5', 'TO COUNT');
			$SpreadSheet->getActiveSheet()->setCellValue('H5', 'COUNTED');
			$SpreadSheet->getActiveSheet()->setCellValue('I5', 'DIFFERENCE');
			$SpreadSheet->getActiveSheet()->setCellValue('J5', 'COUNTED MANUALLY');
			$SpreadSheet->getActiveSheet()->setCellValue('K5', 'FINAL DIFFERENCE');
			$SpreadSheet->getActiveSheet()->setCellValue('L5', 'COMMENTS');

			$SpreadSheet->getActiveSheet()->getStyle('A:G')->getNumberFormat()->setFormatCode('#,###');
			$SpreadSheet->getActiveSheet()->getStyle('H:K')->getNumberFormat()->setFormatCode('#,##0');
			$SpreadSheet->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');

			$SpreadSheet->getActiveSheet()->setCellValue('H6', '=COUNTIFS(Barcodes!$A$1:$C$10000,A6)');

			$SpreadSheet->createSheet(1);
			$SpreadSheet->setActiveSheetIndex(1);
			$SpreadSheet->getActiveSheet()->setTitle('Barcodes');

			// Add data
			$StartingRow = 6;
			$i = $StartingRow;
			$SpreadSheet->setActiveSheetIndex(0);
			$ActiveSheet = $SpreadSheet->getActiveSheet();
			
			while ($MyRow = DB_fetch_array($Result)) {

				$ActiveSheet->setCellValue('A'.$i, $MyRow['stockid']);
				$ActiveSheet->setCellValue('B'.$i, $MyRow['description']);
				$ActiveSheet->setCellValue('C'.$i, $MyRow['categoryid']);

				$ActiveSheet->setCellValue('D'.$i, round($MyRow['quantity'] ?? 0, 0)); // Also added null coalescing here for consistency
				$ActiveSheet->setCellValue('E'.$i, round($MyRow['intransitout'] ?? 0, 0));
				$ActiveSheet->setCellValue('F'.$i, round($MyRow['intransitin'] ?? 0, 0));

// We need to count whatever is in QOH - transit OUT, not transit IN
//				$Available = $MyRow['quantity']+$MyRow['intransitin']-$MyRow['intransitout'];
				$Available = ($MyRow['quantity'] ?? 0) - ($MyRow['intransitout'] ?? 0); // Added null coalescing here too

				$ActiveSheet->setCellValue('G'.$i, round($Available,0));
//				$ActiveSheet->setCellValue('H'.$i, '=COUNTIFS(Barcodes!$A$1:$A$9999,A'.$i.')');
				$ActiveSheet->setCellValue('I'.$i, '=H'.$i.'-G'.$i.'');

				$ActiveSheet->setCellValue('K'.$i, '=IF(ISBLANK(J'.$i.'),"",J'.$i.'-G'.$i.')');

				$i++;
			}
			
			// Calculating totals, subtotals, etc
			$ActiveSheet->setCellValue('A1', '=COUNTA(A'.$StartingRow.':A'.$i.')');
			$ActiveSheet->setCellValue('A2', '=SUBTOTAL(3,A'.$StartingRow.':A'.$i.')');
			$ActiveSheet->setCellValue('A3', '=A2/A1');

			foreach(range('G','K') as $ColumnID) {
				$ActiveSheet->setCellValue(''.$ColumnID.'1', '=SUM('.$ColumnID.$StartingRow.':'.$ColumnID.$i.')');
				$ActiveSheet->setCellValue(''.$ColumnID.'2', '=SUBTOTAL(9,'.$ColumnID.$StartingRow.':'.$ColumnID.$i.')');
				$ActiveSheet->setCellValue(''.$ColumnID.'3', '='.$ColumnID.'2/'.$ColumnID.'1');
			}

			// Freeze panes
			$ActiveSheet->freezePane('B6');

			// Set auto filter
			$ActiveSheet->setAutoFilter('A5:L' . $i);
			
			// Auto Size columns
			foreach(range('E','L') as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
			

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output based on format
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File ='Inventory-' .  $Location . '-' . date('Y-m-d-H-i-s'). '.xlsx';
			} else {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File ='Inventory-' .  $Location . '-' . date('Y-m-d-H-i-s'). '.ods';
			}
			header('Content-Disposition: attachment;filename="' . $File . '"');
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

		}else{
			$Title = __('Excel file for Inventory Taking');
			include('includes/header.php');
			prnMsg('Inventory Taking: No items to count');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = __('Export file for Inventory Taking at a location');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Excel file for Inventory Taking') . '" alt="" />' . ' ' . __('Excel file for Inventory Taking') . '
		</p>';

	echo '<fieldset><legend>' . __('Inventory Taking at Location Selection') . '</legend>';

	echo FieldToSelectMultipleStockCategories('Categories', $_POST['Categories'], __('Inventory Categories'), __('Select the categories to perform inventory taking at location'), '', 1, true, true);
	echo FieldToSelectOneLocation('StockLocation', $_POST['StockLocation'], __('Location'), '', 'CANVIEW', 2, true, false);
	echo FieldToSelectSpreadSheetFormat("Format", $_POST['Format'], 'Spreadsheet File Format', '', '', 3, true, false);
	
	echo '</fieldset>';
	
	echo OneButtonCenteredForm('submit', __('Export Inventory Taking File'));

	echo '</form>';
	include('includes/footer.php');

} // End of function display()
