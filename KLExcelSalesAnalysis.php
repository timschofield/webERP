<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
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

	//first off validate inputs sensible

	if ($InputError == 0){
		$today = date('Y-m-d');
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
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

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
			$objPHPExcel->getActiveSheet()->getStyle('R')->getNumberFormat()->setFormatCode('#,##0.0');
			$objPHPExcel->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->getNumberFormat()->setFormatCode('#,##0');
			$objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);

			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Sales From:');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Sales To:');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', '# Days:');
 			$objPHPExcel->getActiveSheet()->setCellValue('B4', 'Optimum Stock days');

			$objPHPExcel->getActiveSheet()->setCellValue('C1', ConvertSQLDate($FromDate));
			$objPHPExcel->getActiveSheet()->setCellValue('C2', ConvertSQLDate($ToDate));
			$objPHPExcel->getActiveSheet()->setCellValue('C3', '=C2-C1');
 			$objPHPExcel->getActiveSheet()->setCellValue('C4', 150);

			if ($CodeDetail == 'CODE_FULL'){
				$objPHPExcel->getActiveSheet()->setCellValue('E1', 'ALL CODES');
			}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
				$objPHPExcel->getActiveSheet()->setCellValue('E1', 'RINGS GROUPED');
			}else{
				$objPHPExcel->getActiveSheet()->setCellValue('E1', '6 LETTER CODES');
			}

			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			if ($CodeDetail == 'CODE_FULL'){
				$ColumnTitle = 'DESCRIPTION';
			}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
				$ColumnTitle = 'TEXT';
			}else{
				$ColumnTitle = 'FLAVOURS';
			}
			$objPHPExcel->getActiveSheet()->setCellValue('B5', $ColumnTitle);
			
			$objPHPExcel->getActiveSheet()->setCellValue('C5', 'CATEGORY');
			$objPHPExcel->getActiveSheet()->setCellValue('D5', 'FAMILY');
			$objPHPExcel->getActiveSheet()->setCellValue('E5', 'TYPE');
			$objPHPExcel->getActiveSheet()->setCellValue('F5', 'DOB_CATEGORY');

			$objPHPExcel->getActiveSheet()->setCellValue('G5', 'STANDARD_COST');

			$objPHPExcel->getActiveSheet()->setCellValue('H5', 'DISCOUNT');
			$objPHPExcel->getActiveSheet()->setCellValue('I5', 'AVG_PRICE');

			$objPHPExcel->getActiveSheet()->setCellValue('J5', 'QOH');
 			$objPHPExcel->getActiveSheet()->setCellValue('K5', 'STOCK_VALUE');

			$objPHPExcel->getActiveSheet()->setCellValue('L5', 'QOO');
 			$objPHPExcel->getActiveSheet()->setCellValue('M5', 'ORDER_VALUE');

  			$objPHPExcel->getActiveSheet()->setCellValue('N5', 'PCS_SOLD');
 			$objPHPExcel->getActiveSheet()->setCellValue('O5', 'SALES_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('P5', 'COST_VALUE');
 			$objPHPExcel->getActiveSheet()->setCellValue('Q5', 'GROSS_MARGIN');

			$objPHPExcel->getActiveSheet()->setCellValue('R5', 'SALES/DAY');
 			$objPHPExcel->getActiveSheet()->setCellValue('S5', 'DAYS_QOH');
 			$objPHPExcel->getActiveSheet()->setCellValue('T5', 'DAYS_QOO');
 			$objPHPExcel->getActiveSheet()->setCellValue('U5', 'DAYS QOH+QOO');

 			$objPHPExcel->getActiveSheet()->setCellValue('V5', 'PCS TO PO/WO');
 			$objPHPExcel->getActiveSheet()->setCellValue('W5', 'SUPPLIER');

			// Add data
			$StartingRow = 6;
			$i = $StartingRow;
			$LastStockid = '';
			while ($MyRow = DB_fetch_array($Result)) {
				$objPHPExcel->setActiveSheetIndex(0);

				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $MyRow['stockid']);
				
				if ($LastStockid != $MyRow['stockid']){
				
					if ($CodeDetail == 'CODE_FULL'){
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $MyRow['categoryid']);
					}elseif ($CodeDetail == 'CODE_FULL_WITH_RINGS'){
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $MyRow['description']);
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $MyRow['categoryid']);
					}else{
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, round($MyRow['flavours'],0));
					}

					$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, substr($MyRow['stockid'], 0,2));
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, TypeOfItem($MyRow['stockid']));

					if ($CodeDetail != 'CODE_6'){
						$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, ConvertSQLDate($MyRow['lastcategoryupdate']));
					}
					
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, round($MyRow['standardcost'],0));

					if ($CodeDetail != 'CODE_6'){
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $MyRow['discountcategory']);
					}
					
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, round(ItemCodeAvgPriceInvoiced($MyRow['stockid'],$FromDate,$ToDate,'',$CodeDetail),0));

					$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, round(ItemCodeQOH($MyRow['stockid'],$CodeDetail, "ALL"),0));
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '=G'.$i.'*J'.$i.'');

					$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, round(ItemCodeQOO_PurchaseOrders($MyRow['stockid'],$CodeDetail)+ItemCodeQOO_WorkOrders($MyRow['stockid'],$CodeDetail),0));
					$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, '=G'.$i.'*L'.$i.'');

					$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, round(ItemCodeQuantityInvoiced($MyRow['stockid'],$FromDate,$ToDate,'',$CodeDetail),0));
					$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '=N'.$i.'*I'.$i.'');
					$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, '=N'.$i.'*G'.$i.'');
					$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, '=O'.$i.'-P'.$i.'');

					$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, '=N'.$i.'/$C$3');
					$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '=IF(R'.$i.'>0,J'.$i.'/R'.$i.',99999)'.'');
					$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '=IF(R'.$i.'>0,L'.$i.'/R'.$i.',99999)'.'');
					$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '=S'.$i.'+T'.$i.'');
					
					$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, '=IF(U'.$i.'<$C$4,ROUNDUP(($C$4-U'.$i.')*R'.$i.',0),"")'.'');
					$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $MyRow['preferredsupplier']);
					
					$LastStockid = $MyRow['stockid'];
					$i++;
				}
			}
			
			// Calculating totals, subtotals, etc
			$objPHPExcel->getActiveSheet()->setCellValue('A1', '=COUNTA(A'.$StartingRow.':A'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', '=SUM(J'.$StartingRow.':J'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', '=SUM(K'.$StartingRow.':K'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('L1', '=SUM(L'.$StartingRow.':L'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('M1', '=SUM(M'.$StartingRow.':M'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('N1', '=SUM(N'.$StartingRow.':N'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('O1', '=SUM(O'.$StartingRow.':O'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('P1', '=SUM(P'.$StartingRow.':P'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('Q1', '=SUM(Q'.$StartingRow.':Q'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('V1', '=SUM(V'.$StartingRow.':V'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A2', '=SUBTOTAL(3,A'.$StartingRow.':A'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('J2', '=SUBTOTAL(9,J'.$StartingRow.':J'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('K2', '=SUBTOTAL(9,K'.$StartingRow.':K'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('L2', '=SUBTOTAL(9,L'.$StartingRow.':L'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('M2', '=SUBTOTAL(9,M'.$StartingRow.':M'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('N2', '=SUBTOTAL(9,N'.$StartingRow.':N'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('O2', '=SUBTOTAL(9,O'.$StartingRow.':O'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('P2', '=SUBTOTAL(9,P'.$StartingRow.':P'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('Q2', '=SUBTOTAL(9,Q'.$StartingRow.':Q'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('V2', '=SUBTOTAL(9,V'.$StartingRow.':V'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A3', '=A2/A1');
			$objPHPExcel->getActiveSheet()->setCellValue('J3', '=J2/J1');
			$objPHPExcel->getActiveSheet()->setCellValue('K3', '=K2/K1');
			$objPHPExcel->getActiveSheet()->setCellValue('L3', '=L2/L1');
			$objPHPExcel->getActiveSheet()->setCellValue('M3', '=M2/M1');
			$objPHPExcel->getActiveSheet()->setCellValue('N3', '=N2/N1');
			$objPHPExcel->getActiveSheet()->setCellValue('O3', '=O2/O1');
			$objPHPExcel->getActiveSheet()->setCellValue('P3', '=P2/P1');
			$objPHPExcel->getActiveSheet()->setCellValue('Q3', '=Q2/Q1');
			$objPHPExcel->getActiveSheet()->setCellValue('V3', '=V2/V1');
		
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
	$Title = _('Excel file for Sales Analysis');

	include('includes/header.php');

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
				<option selected="selected" value="CODE_FULL">' . _('Full Item Code') . '</option>
				<option value="CODE_FULL_WITH_RINGS">' . _('Full Item Code + Rings Grouped') . '</option>
				<option value="CODE_6">' . _('Basic Item Code (6 Char)') . '</option>
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
	include('includes/footer.php');

} // End of function display()

?>