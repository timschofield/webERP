<?php

include('includes/session.php');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLCountriesForRetail.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (!isset($_POST['DaysTopSales'])) {
    $_POST['DaysTopSales'] = '60';
}

if (isset($_POST['submit'])) {
    submit($_POST['Categories'], $_POST['DaysTopSales']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($ListCategories, $DaysTopSales) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		$Today = date('Y-m-d');
		$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				stockmaster.lastcategoryupdate,
				(SELECT SUM(quantity)
					FROM locstock
					WHERE stockmaster.stockid = locstock.stockid) AS qoh,
				prices.startdate AS DOB_price,
				prices.price AS retailprice,
				(stockmaster.actualcost) AS standardcost
			FROM stockmaster, prices			
			WHERE stockmaster.stockid = prices.stockid	
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.discontinued = 0	
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate <= CURRENT_DATE 
				AND prices.enddate >= CURRENT_DATE
			ORDER BY stockmaster.stockid";
		
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){

			// Create new PHPSpreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Price Analysis")
										 ->setSubject("Price Analysis")
										 ->setDescription("Price Analysis")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'CODE');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'DESCRIPTION');
			$SpreadSheet->getActiveSheet()->setCellValue('C1', 'CATEGORY');
			$SpreadSheet->getActiveSheet()->setCellValue('D1', 'DOB_CATEGORY');
			$SpreadSheet->getActiveSheet()->setCellValue('E1', 'QOH');
			$SpreadSheet->getActiveSheet()->setCellValue('F1', 'TOP_ITEM');
			$SpreadSheet->getActiveSheet()->setCellValue('G1', 'STANDARD_COST');
			$SpreadSheet->getActiveSheet()->setCellValue('H1', 'DOB_PRICE');
			$SpreadSheet->getActiveSheet()->setCellValue('I1', 'RETAILPRICE');
			$SpreadSheet->getActiveSheet()->setCellValue('J1', 'PRICEFACTOR');
 
			// Add data
			$i = 2;
			while ($MyRow = DB_fetch_array($Result)) {
				$SpreadSheet->setActiveSheetIndex(0);
				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['stockid']);
				$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);
				$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, $MyRow['categoryid']);
				$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($MyRow['lastcategoryupdate']));
				$SpreadSheet->getActiveSheet()->setCellValue('E'.$i, $MyRow['qoh']);
				$SpreadSheet->getActiveSheet()->setCellValue('F'.$i, PositionTopSalesItem($MyRow['stockid'], $DaysTopSales));
				$SpreadSheet->getActiveSheet()->setCellValue('G'.$i, round($MyRow['standardcost'],0));
				$SpreadSheet->getActiveSheet()->setCellValue('H'.$i, ConvertSQLDate($MyRow['DOB_price']));
				$SpreadSheet->getActiveSheet()->setCellValue('I'.$i, $MyRow['retailprice']);
				$SpreadSheet->getActiveSheet()->setCellValue('J'.$i, round(($MyRow['retailprice']/$MyRow['standardcost']),2));
				
				$i++;
			}
			
			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','J') as $ColumnID) {
				$SpreadSheet->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$SpreadSheet->getActiveSheet()->setTitle('Price Analysis');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = 'KL-PriceAnalysis-' . Date('Y-m-d'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = 'KL-PriceAnalysis-' . Date('Y-m-d'). '.ods';
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

		}else{
			$Title = _('Excel file for Price Analysis');
			include('includes/header.php');
			prnMsg('No items selected to analyse');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Price Analysis');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Price Analysis') . '" alt="" />' . ' ' . _('Excel file for Price Analysis') . '
		</p>';

	echo '<fieldset>
	        <legend>' . _('Analysis Parameters') . '</legend>';

	echo FieldToSelectMultipleStockCategories('Categories', isset($_POST['Categories']) ? $_POST['Categories'] : array(), _('Select Inventory Categories'), '', '', 1, true, true);

	echo FieldToSelectFromThreeOptions('30', _('30 days'),
									'60', _('60 days'),
									'90', _('90 days'),
									'DaysTopSales', $_POST['DaysTopSales'],	_('# Days for Top Sales Ranking'), '', '', 2, true, false);

	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('Format'), '', '', 3, true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export Price Analysis File'));
	
	echo '</form>';
	include('includes/footer.php');

} // End of function display()
