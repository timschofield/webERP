<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLCountriesForRetail.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

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
		$today = date('Y-m-d');
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
				AND prices.startdate <= '". $today. "' 
				AND (prices.enddate >= '". $today. "' OR prices.enddate = '9999-12-31')
			ORDER BY stockmaster.stockid";
		
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Price Analysis")
										 ->setSubject("Price Analysis")
										 ->setDescription("Price Analysis")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'CODE');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'DESCRIPTION');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'DOB_CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'QOH');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'TOP_ITEM');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'STANDARD_COST');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', 'DOB_PRICE');
			$objPHPExcel->getActiveSheet()->setCellValue('I1', 'RETAILPRICE');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', 'PRICEFACTOR');
 
			// Add data
			$i = 2;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['stockid']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['categoryid']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($myrow['lastcategoryupdate']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['qoh']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, PositionTopSalesItem($myrow['stockid'], $DaysTopSales));
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, round($myrow['standardcost'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, ConvertSQLDate($myrow['DOB_price']));
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['retailprice']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, round(($myrow['retailprice']/$myrow['standardcost']),2));
				
				$i++;
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','J') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('Price Analysis');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-PriceAnalysis-' . Date('Y-m-d'). '.xlsx';
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

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Price Analysis') . '" alt="" />' . ' ' . _('Excel file for Price Analysis') . '
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

	//View number of days for Top Sales Calculations
	echo '<tr>
			<td>' . _('# Days for Top Sales Ranking') . ':</td>
			<td><select name="DaysTopSales">';
		echo '<option value="30">' . _('30 days') . '</option>';
		echo '<option selected="selected" value="60">' . _('60 days') . '</option>';
		echo '<option value="90">' . _('90 days') . '</option>';
	echo '</select></td></tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Prices Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>