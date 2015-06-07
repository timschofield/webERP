<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

if (!isset($_POST['Period'])){
	$_POST['Period'] = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
}

if (isset($_POST['submit'])) {
    submit($db, $_POST['Period']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $Period) {

	//initialise no input errors
	$InputError = 0;
	
	$CurrentMonth = $Period;
	$LastMonth = $CurrentMonth -1;
	$CurrentMonthLastYear = $CurrentMonth -12;
	
	
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

		 // Add title data
		$objPHPExcel->setActiveSheetIndex(0);

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('By Category');

		// Add cell formats
		$objPHPExcel->getActiveSheet()->getStyle('B:L')->getNumberFormat()->setFormatCode('#,###');
		$objPHPExcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode('0.0%');
		$objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()->setFormatCode('0.0%');
		$objPHPExcel->getActiveSheet()->getStyle('J:K')->getNumberFormat()->setFormatCode('0.0%');
		$objPHPExcel->getActiveSheet()->getStyle('M:O')->getNumberFormat()->setFormatCode('0.0%');

		
		// Titles
		TitleCells($objPHPExcel, 'CATEGORY', 'A3', 'A6');
		TitleCells($objPHPExcel, 'CURRENT MONTH', 'B3', 'O3');
		TitleCells($objPHPExcel, 'SALES', 'B4', 'H4');
		TitleCells($objPHPExcel, 'GROSS PROFIT', 'I4', 'O4');
		TitleCells($objPHPExcel, 'ACTUAL', 'B5', 'D5');
		TitleCells($objPHPExcel, 'LAST YEAR', 'E5', 'H5');
		TitleCells($objPHPExcel, 'ACTUAL', 'I5', 'K5');
		TitleCells($objPHPExcel, 'LAST YEAR', 'L5', 'O5');
		TitleCells($objPHPExcel, 'Sales IDR', 'B6', 'B6');
		TitleCells($objPHPExcel, 'Pcs', 'C6', 'C6');
		TitleCells($objPHPExcel, 'Cont', 'D6', 'D6');
		TitleCells($objPHPExcel, 'Sales IDR', 'E6', 'E6');
		TitleCells($objPHPExcel, 'Pcs', 'F6', 'F6');
		TitleCells($objPHPExcel, 'Cont', 'G6', 'G6');
		TitleCells($objPHPExcel, 'Var %', 'H6', 'H6');
		TitleCells($objPHPExcel, 'Gross Profit', 'I6', 'I6');
		TitleCells($objPHPExcel, 'GP %', 'J6', 'J6');
		TitleCells($objPHPExcel, 'Cont', 'K6', 'K6');
		TitleCells($objPHPExcel, 'Gross Profit', 'L6', 'L6');
		TitleCells($objPHPExcel, 'GP %', 'M6', 'M6');
		TitleCells($objPHPExcel, 'Cont', 'N6', 'N6');
		TitleCells($objPHPExcel, 'Var %', 'O6', 'O6');
		
		// Get data for current Month
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_SILVER);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A7', "SILVER");
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C7', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I7', $SalesMonth-$CostMonth);
									 
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_FASHION_JEWELLERY);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A8', "FASHION JEWELLERY");
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C8', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $SalesMonth-$CostMonth);
		
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_STAINLESS);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A9', "STAINLESS STEEL");
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C9', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $SalesMonth-$CostMonth);
		
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_ACCESSORIES);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A10', "ACCESSORIES");
		$objPHPExcel->getActiveSheet()->setCellValue('B10', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C10', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $SalesMonth-$CostMonth);
									 
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_CONSIGNMENT);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A11', "CONSIGNMENT");
		$objPHPExcel->getActiveSheet()->setCellValue('B11', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C11', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $SalesMonth-$CostMonth);

		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonth, LIST_STOCK_CATEGORIES_DISCOUNT);						 
		$objPHPExcel->getActiveSheet()->setCellValue('A12', "DISCOUNT/OUTLET");
		$objPHPExcel->getActiveSheet()->setCellValue('B12', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('C12', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $SalesMonth-$CostMonth);

		
		// Get data for current month last year
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_SILVER);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E7', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F7', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L7', $SalesMonth-$CostMonth);
									 
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_FASHION_JEWELLERY);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E8', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F8', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L8', $SalesMonth-$CostMonth);
		
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_STAINLESS);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E9', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F9', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L9', $SalesMonth-$CostMonth);
		
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_ACCESSORIES);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E10', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F10', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L10', $SalesMonth-$CostMonth);
									 
		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_CONSIGNMENT);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E11', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F11', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L11', $SalesMonth-$CostMonth);

		list ($SalesMonth, $PcsMonth, $CostMonth) = SalesForPeriod($CurrentMonthLastYear, LIST_STOCK_CATEGORIES_DISCOUNT);						 
		$objPHPExcel->getActiveSheet()->setCellValue('E12', $SalesMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('F12', $PcsMonth);
		$objPHPExcel->getActiveSheet()->setCellValue('L12', $SalesMonth-$CostMonth);
	
		// Get the calculations done
		
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

		// Variation Current Month vs Current Month Last Year
		$objPHPExcel->getActiveSheet()->setCellValue('J7', '=I7/B7');
		$objPHPExcel->getActiveSheet()->setCellValue('J8', '=I8/B8');
		$objPHPExcel->getActiveSheet()->setCellValue('J9', '=I9/B9');
		$objPHPExcel->getActiveSheet()->setCellValue('J10', '=I10/B10');
		$objPHPExcel->getActiveSheet()->setCellValue('J11', '=I11/B11');
		$objPHPExcel->getActiveSheet()->setCellValue('J12', '=I12/B12');
		$objPHPExcel->getActiveSheet()->setCellValue('J13', '=I13/B13');


		
 /*		$objPHPExcel->getActiveSheet()->getStyle('A:AZ')->getNumberFormat()->setFormatCode('#,###');
		$objPHPExcel->getActiveSheet()->getStyle('R')->getNumberFormat()->setFormatCode('#,##0.0');
		$objPHPExcel->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');
		$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->getNumberFormat()->setFormatCode('#,##0');
		$objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

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
		$objPHPExcel->getActiveSheet()->setCellValue('S5', 'DAYS_QOH');
		$objPHPExcel->getActiveSheet()->setCellValue('T5', 'DAYS_QOO');
		$objPHPExcel->getActiveSheet()->setCellValue('U5', 'DAYS QOH+QOO');

		$objPHPExcel->getActiveSheet()->setCellValue('V5', 'PCS TO PO/WO');
		$objPHPExcel->getActiveSheet()->setCellValue('W5', 'SUPPLIER');
		
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
			
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, TypeOfItem($myrow['stockid']));
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
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '=IF(R'.$i.'>0,J'.$i.'/R'.$i.',99999)'.'');
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '=IF(R'.$i.'>0,L'.$i.'/R'.$i.',99999)'.'');
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '=S'.$i.'+T'.$i.'');
			
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, '=IF(U'.$i.'<$C$4,ROUNDUP(($C$4-U'.$i.')*R'.$i.',0),"")'.'');
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $myrow['preferredsupplier']);

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
*/		
		// Auto Size columns
		foreach(range('A','O') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		

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


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Sales Monthly Report');

	include('includes/header.inc');

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
	include('includes/footer.inc');

} // End of function display()

function SalesForPeriod($Period, $CategoryList){
	
	$SQL = "SELECT SUM(amt) AS sales,
					SUM(qty) AS pcs,
					SUM(cost) AS grosscost
			FROM salesanalysis
			WHERE periodno = '". $Period. "'
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
}


?>