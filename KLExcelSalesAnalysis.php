<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
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
    submit($db, $_POST['Categories'], $_POST['FromDate'], $_POST['ToDate'], $_POST['CodeDetail']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $ListCategories, $FromDate, $ToDate, $CodeDetail) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		$today = date('Y-m-d');
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
//		$NumDays = floor((strtotime($ToDate) - strtotime($FromDate))/(60*60*24));
		
		if ($CodeDetail == 'CodeFull'){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,
							stockmaster.lastcategoryupdate,
							(stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost) AS standardcost,
							stockmaster.discountcategory
					FROM stockmaster
					WHERE stockmaster.discontinued = 0
						AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					ORDER BY stockmaster.stockid";
		}else{
			$SQL = "SELECT SUBSTRING(stockmaster.stockid,1,6),
							AVG (stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost) AS standardcost
					FROM stockmaster
					WHERE stockmaster.discontinued = 0
						AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					GROUP BY SUBSTRING(stockmaster.stockid,1,6)
					ORDER BY SUBSTRING(stockmaster.stockid,1,6)";
		}
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			
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
			$objPHPExcel->getActiveSheet()->getStyle('C3')->getNumberFormat()->setFormatCode('#,###');
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

			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', 'DESCRIPTION');
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
 			$objPHPExcel->getActiveSheet()->setCellValue('S5', 'DAYS_QOO');
 			$objPHPExcel->getActiveSheet()->setCellValue('T5', 'DAYS_QOO');
 			$objPHPExcel->getActiveSheet()->setCellValue('U5', 'DAYS');

 			$objPHPExcel->getActiveSheet()->setCellValue('V5', 'PCS TO PO/WO');
			
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

				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['discountcategory']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, round(ItemCodeAvgPriceInvoiced($myrow['stockid'],$FromDate,$ToDate,''),0));

				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, round(ItemCodeQOH($myrow['stockid']),0));
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '=G'.$i.'*J'.$i.'');

				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, round(ItemCodeQOO_PurchaseOrders($myrow['stockid'])+ItemCodeQOO_WorkOrders($myrow['stockid']),0));
				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, '=G'.$i.'*L'.$i.'');

				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,''),0));
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, '=N'.$i.'*I'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, '=N'.$i.'*G'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, '=O'.$i.'-P'.$i.'');

				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, '=N'.$i.'/$C$3');
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '=J'.$i.'/R'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '=L'.$i.'/R'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '=S'.$i.'+T'.$i.'');
				
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, '=IF(U'.$i.'<$C$4,ROUNDUP(($C$4-U'.$i.')*R'.$i.',0),"")'.'');

				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAIL66'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILSE'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILOB'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILKS'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILBW'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILJC'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILSA'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILSU'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILSS'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILUB'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILMF'),0));
				$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i, round(ItemCodeQuantityInvoiced($myrow['stockid'],$FromDate,$ToDate,'RETAILPU'),0));
				
				$i++;
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
			$objPHPExcel->getActiveSheet()->setCellValue('U1', '=SUM(U'.$StartingRow.':U'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A2', '=SUBTOTAL(3,A'.$StartingRow.':A'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('J2', '=SUBTOTAL(9,J'.$StartingRow.':J'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('K2', '=SUBTOTAL(9,K'.$StartingRow.':K'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('L2', '=SUBTOTAL(9,L'.$StartingRow.':L'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('M2', '=SUBTOTAL(9,M'.$StartingRow.':M'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('N2', '=SUBTOTAL(9,N'.$StartingRow.':N'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('O2', '=SUBTOTAL(9,O'.$StartingRow.':O'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('P2', '=SUBTOTAL(9,P'.$StartingRow.':P'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('Q2', '=SUBTOTAL(9,Q'.$StartingRow.':Q'.$i.')');
			$objPHPExcel->getActiveSheet()->setCellValue('U2', '=SUBTOTAL(9,U'.$StartingRow.':U'.$i.')');

			$objPHPExcel->getActiveSheet()->setCellValue('A3', '=A2/A1');
			$objPHPExcel->getActiveSheet()->setCellValue('J3', '=J2/J1');
			$objPHPExcel->getActiveSheet()->setCellValue('K3', '=K2/K1');
			$objPHPExcel->getActiveSheet()->setCellValue('L3', '=L2/L1');
			$objPHPExcel->getActiveSheet()->setCellValue('M3', '=M2/M1');
			$objPHPExcel->getActiveSheet()->setCellValue('N3', '=N2/N1');
			$objPHPExcel->getActiveSheet()->setCellValue('O3', '=O2/O1');
			$objPHPExcel->getActiveSheet()->setCellValue('P3', '=P2/P1');
			$objPHPExcel->getActiveSheet()->setCellValue('Q3', '=Q2/Q1');
			$objPHPExcel->getActiveSheet()->setCellValue('U3', '=U2/U1');
		
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
			<td>' . _('Item Codes detailed as') . ':</td>
			<td><select name="CodeDetail">
				<option selected="selected" value="CodeFull">' . _('Full Item Code') . '</option>
				<option value="Code6">' . _('6 Char Item Code') . '</option>
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
			<td><input type="submit" name="submit" value="' . _('Create Prices Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');

} // End of function display()

?>