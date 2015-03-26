<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLCountriesForRetail.php');
include('includes/WeberpOpenCartDefines.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($db, $_POST['Categories'], $_POST['FromDate'], $_POST['ToDate']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $ListCategories, $FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		$today = date('Y-m-d');
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.categoryid,
						stockmaster.lastcategoryupdate,
						(stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost) AS standardcost,
						stockmaster.discountcategory,
						(SELECT SUM(quantity)
							FROM locstock
							WHERE stockmaster.stockid = locstock.stockid) AS qoh,
						(SELECT prices.price
							FROM prices
							WHERE prices.stockid=stockmaster.stockid
								AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
								AND prices.currabrev = '". CURRENCY_CODE ."'
								AND prices.startdate <= '". $today. "' 
								AND (prices.enddate >= '". $today. "' OR prices.enddate = '0000-00-00')) AS retailprice,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAIL66') AS sales66,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILSE') AS salesSE,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILOB') AS salesOB,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILKS') AS salesKS,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILBW') AS salesBW,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILJC') AS salesJC,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILSA') AS salesSA,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILSU') AS salesSU,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILSS') AS salesSS,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILUB') AS salesUB,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILMF') AS salesMF,
						(SELECT SUM(salesorderdetails.qtyinvoiced)
							FROM salesorderdetails,
								salesorders
							WHERE salesorderdetails.orderno = salesorders.orderno
								AND salesorderdetails.stkcode = stockmaster.stockid
								AND salesorders.orddate >= '" . $FromDate . "'
								AND salesorders.orddate <= '" . $ToDate . "'
								AND salesorders.debtorno = 'RETAILPU') AS salesPU
				FROM stockmaster
				WHERE stockmaster.discontinued = 0
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				ORDER BY stockmaster.stockid";

		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sales Analysis")
										 ->setSubject("Sales Analysis")
										 ->setDescription("Sales Analysis")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ITEM CODE');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'DESCRIPTION');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'DOB_CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'QOH');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'STANDARD_COST');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'RETAIL_PRICE');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', '%_DISCOUNT');

 			$objPHPExcel->getActiveSheet()->setCellValue('L1', 'SALES_66');
 			$objPHPExcel->getActiveSheet()->setCellValue('M1', 'SALES_SE');
 			$objPHPExcel->getActiveSheet()->setCellValue('N1', 'SALES_OB');
 			$objPHPExcel->getActiveSheet()->setCellValue('O1', 'SALES_KS');
 			$objPHPExcel->getActiveSheet()->setCellValue('P1', 'SALES_BW');
 			$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'SALES_JC');
 			$objPHPExcel->getActiveSheet()->setCellValue('R1', 'SALES_SA');
 			$objPHPExcel->getActiveSheet()->setCellValue('S1', 'SALES_SU');
 			$objPHPExcel->getActiveSheet()->setCellValue('T1', 'SALES_SS');
 			$objPHPExcel->getActiveSheet()->setCellValue('U1', 'SALES_UB');
 			$objPHPExcel->getActiveSheet()->setCellValue('V1', 'SALES_MF');
 			$objPHPExcel->getActiveSheet()->setCellValue('W1', 'SALES_PU');

 			$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'SALES_PCS');
 			$objPHPExcel->getActiveSheet()->setCellValue('Z1', 'SALES_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('AA1', 'COST_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('AB1', 'GROSS_MARGIN');
 			$objPHPExcel->getActiveSheet()->setCellValue('AC1', 'QOH_COST_VALUE');
 
			// Add data
			$i = 2;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['stockid']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['categoryid']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($myrow['lastcategoryupdate']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, round($myrow['qoh'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, round($myrow['standardcost'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, round($myrow['retailprice'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['discountcategory']);

				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, round($myrow['sales66'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, round($myrow['salesSE'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, round($myrow['salesOB'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, round($myrow['salesKS'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, round($myrow['salesBW'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, round($myrow['salesJC'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, round($myrow['salesSA'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, round($myrow['salesSU'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, round($myrow['salesSS'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, round($myrow['salesUB'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, round($myrow['salesMF'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, round($myrow['salesPU'],0));

				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, '=SUM(L'.$i.':W'.$i.')');
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, '=Y'.$i.'*G'.$i.'*(100-H'.$i.')/100');
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, '=Y'.$i.'*F'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, '=Z'.$i.'-AA'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, '=E'.$i.'*F'.$i.'');
				
				$i++;
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('B2');
		
			// Auto Size columns
			foreach(range('A','W') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('Sales Analysis');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-SalesAnalysis-' . Date('Y-m-d'). '.xlsx';
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
			$Title = _('Excel file for Sales Analysis');
			include('includes/header.inc');
			prnMsg('No items selected to analyse');
			include('includes/footer.inc');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Sales Analysis');

	include('includes/header.inc');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sales Analysis') . '" alt="" />' . ' ' . _('Excel file for Price Analysis') . '
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
			<td><input type="submit" name="submit" value="' . _('Create Prices Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');

} // End of function display()

?>