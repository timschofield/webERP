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

			$objPHPExcel->getActiveSheet()->getStyle('A:AZ')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', 'DESCRIPTION');
			$objPHPExcel->getActiveSheet()->setCellValue('C5', 'CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('D5', 'FAMILY');
			$objPHPExcel->getActiveSheet()->setCellValue('E5', 'TYPE');
			$objPHPExcel->getActiveSheet()->setCellValue('F5', 'DOB_CATEGORY');

			$objPHPExcel->getActiveSheet()->setCellValue('G5', 'STANDARD_COST');

			$objPHPExcel->getActiveSheet()->setCellValue('H5', 'RETAIL_PRICE');
			$objPHPExcel->getActiveSheet()->setCellValue('I5', '%_DISCOUNT');
			$objPHPExcel->getActiveSheet()->setCellValue('J5', 'NET_PRICE');

			$objPHPExcel->getActiveSheet()->setCellValue('K5', 'QOH');
 			$objPHPExcel->getActiveSheet()->setCellValue('L5', 'STOCK_VALUE');

  			$objPHPExcel->getActiveSheet()->setCellValue('M5', 'PCS_SOLD');
 			$objPHPExcel->getActiveSheet()->setCellValue('N5', 'SALES_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('O5', 'COST_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('P5', 'GROSS_MARGIN');
 
			$objPHPExcel->getActiveSheet()->setCellValue('AA5', 'SALES_66');
 			$objPHPExcel->getActiveSheet()->setCellValue('AB5', 'SALES_SE');
 			$objPHPExcel->getActiveSheet()->setCellValue('AC5', 'SALES_OB');
 			$objPHPExcel->getActiveSheet()->setCellValue('AD5', 'SALES_KS');
 			$objPHPExcel->getActiveSheet()->setCellValue('AE5', 'SALES_BW');
 			$objPHPExcel->getActiveSheet()->setCellValue('AF5', 'SALES_JC');
 			$objPHPExcel->getActiveSheet()->setCellValue('AG5', 'SALES_SA');
 			$objPHPExcel->getActiveSheet()->setCellValue('AH5', 'SALES_SU');
 			$objPHPExcel->getActiveSheet()->setCellValue('AI5', 'SALES_SS');
 			$objPHPExcel->getActiveSheet()->setCellValue('AJ5', 'SALES_UB');
 			$objPHPExcel->getActiveSheet()->setCellValue('AK5', 'SALES_MF');
 			$objPHPExcel->getActiveSheet()->setCellValue('AL5', 'SALES_PU');


			// Add data
			$StartingRow = 6;
			$i = $StartingRow;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['stockid']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['categoryid']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, substr($myrow['stockid'], 0,2));
				
				if (isRing($myrow['stockid'])){
					$Type = "Ring";
				}elseif (isToeRing($myrow['stockid'])){
					$Type = "ToeRing";
				}elseif (isBead($myrow['stockid'])){
					$Type = "Bead";
				}elseif (isEarring($myrow['stockid'])){
					$Type = "Earring";
				}elseif (isEarcuff($myrow['stockid'])){
					$Type = "EarCuff";
				}elseif (isBracelet($myrow['stockid'])){
					$Type = "Bracelet";
				}elseif (isAnklet($myrow['stockid'])){
					$Type = "Anklet";
				}elseif (isPendant($myrow['stockid'])){
					$Type = "Pendant";
				}elseif (isNecklace($myrow['stockid'])){
					$Type = "Necklace";
				}elseif (isPlasticBag($myrow['stockid'])){
					$Type = "Bag";
				}elseif (isTali($myrow['stockid'])){
					$Type = "Tali";
				}else{
					$Type = "Unknown";
				}
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $Type);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, ConvertSQLDate($myrow['lastcategoryupdate']));

				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, round($myrow['standardcost'],0));

				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, round($myrow['retailprice'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['discountcategory']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, '=H'.$i.'*(100-I'.$i.')/100');

				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, round($myrow['qoh'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, '=G'.$i.'*K'.$i.'');

				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, '=SUM(AA'.$i.':AL'.$i.')');
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, '=M'.$i.'*J'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '=M'.$i.'*G'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, '=M'.$i.'-O'.$i.'');

				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, round($myrow['sales66'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, round($myrow['salesSE'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, round($myrow['salesOB'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, round($myrow['salesKS'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, round($myrow['salesBW'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, round($myrow['salesJC'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, round($myrow['salesSA'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, round($myrow['salesSU'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, round($myrow['salesSS'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, round($myrow['salesUB'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, round($myrow['salesMF'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, round($myrow['salesPU'],0));
				
				$i++;
			}
			
			// Calculating totals, subtotals, etc
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'TOTAL');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', '=SUM(K'.$StartingRow.':K'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('L1', '=SUM(L'.$StartingRow.':L'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('M1', '=SUM(M'.$StartingRow.':M'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('N1', '=SUM(N'.$StartingRow.':N'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('O1', '=SUM(O'.$StartingRow.':O'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('P1', '=SUM(P'.$StartingRow.':P'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A2', 'SUBTOTAL');
			$objPHPExcel->getActiveSheet()->setCellValue('K2', '=SUBTOTAL(9,K'.$StartingRow.':K'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('L2', '=SUBTOTAL(9,L'.$StartingRow.':L'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('M2', '=SUBTOTAL(9,M'.$StartingRow.':M'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('N2', '=SUBTOTAL(9,N'.$StartingRow.':N'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('O2', '=SUBTOTAL(9,O'.$StartingRow.':O'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('P2', '=SUBTOTAL(9,P'.$StartingRow.':P'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A3', '%');
			$objPHPExcel->getActiveSheet()->setCellValue('K3', '=K2/K1');
			$objPHPExcel->getActiveSheet()->setCellValue('L3', '=L2/L1');
			$objPHPExcel->getActiveSheet()->setCellValue('M3', '=M2/M1');
			$objPHPExcel->getActiveSheet()->setCellValue('N3', '=N2/N1');
			$objPHPExcel->getActiveSheet()->setCellValue('O3', '=O2/O1');
			$objPHPExcel->getActiveSheet()->setCellValue('P3', '=P2/P1');
		
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('B6');

			// Set auto filter
			$objPHPExcel->getActiveSheet()->setAutoFilter('A5:AL' . $i);
			
			// Auto Size columns
			foreach(range('A','AL') as $columnID) {
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