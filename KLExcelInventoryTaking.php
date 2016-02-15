<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

if (isset($_POST['submit'])) {
    submit($db, $_POST['Categories'], $_POST['StockLocation']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $ListCategories, $Location) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		$Now = Date('Y-m-d H-i-s');

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.categoryid,
						locstock.quantity,
						(SELECT SUM(shipqty-recqty)
							FROM loctransfers
							WHERE loctransfers.stockid = locstock.stockid
								AND shiploc='" . $Location . "') AS intransitout,
						(SELECT SUM(-shipqty+recqty) as intransit
							FROM loctransfers
							WHERE loctransfers.stockid = locstock.stockid
								AND recloc='" . $Location . "') AS intransitin
				FROM locstock, stockmaster
				WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = '" . $Location . "'
					AND stockmaster.discontinued = 0
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				ORDER BY stockmaster.stockid";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			
			// Set value binder
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

/*			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
*/			
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Inventory Taking")
										 ->setSubject("Inventory Taking")
										 ->setDescription("Inventory Taking")
										 ->setKeywords("")
										 ->setCategory("");

/*			$objPHPExcel->getActiveSheet()->getStyle('A:AZ')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('R')->getNumberFormat()->setFormatCode('#,##0.0');
			$objPHPExcel->getActiveSheet()->getStyle('3')->getNumberFormat()->setFormatCode('0.0%');
			$objPHPExcel->getActiveSheet()->getStyle('B3:C3')->getNumberFormat()->setFormatCode('#,##0');
			$objPHPExcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
*/		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle('Inventory');

			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Location:');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Date:');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', 'Category:');
 
			$objPHPExcel->getActiveSheet()->setCellValue('C1', $Location);
			$objPHPExcel->getActiveSheet()->setCellValue('C2', $Now);
			$objPHPExcel->getActiveSheet()->setCellValue('C3', implode("','",$_POST['Categories']));
 
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'ITEM CODE');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', 'DESCRIPTION');
			$objPHPExcel->getActiveSheet()->setCellValue('C5', 'CATEGORY');

			$objPHPExcel->getActiveSheet()->setCellValue('D5', 'QOH');
			$objPHPExcel->getActiveSheet()->setCellValue('E5', 'TRANSIT OUT');
			$objPHPExcel->getActiveSheet()->setCellValue('F5', 'TRANSIT IN');

			$objPHPExcel->getActiveSheet()->setCellValue('G5', 'TO COUNT');
			$objPHPExcel->getActiveSheet()->setCellValue('H5', 'COUNTED');
			$objPHPExcel->getActiveSheet()->setCellValue('I5', 'RESULT');
			$objPHPExcel->getActiveSheet()->setCellValue('J5', 'DIFFRENCE');

			$objPHPExcel->createSheet(1);
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setTitle('Barcodes');

			// Add data
			$StartingRow = 6;
			$i = $StartingRow;
			$objPHPExcel->setActiveSheetIndex(0);
			while ($myrow = DB_fetch_array($result)) {

				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['stockid']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['categoryid']);

				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, round($myrow['quantity'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, round($myrow['intransitout'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, round($myrow['intransitin'],0));

// We need to count whatever is in QOH, not in transit				
//				$Available = $myrow['quantity']+($myrow['intransitin']+$myrow['intransitout']);
				$Available = $myrow['quantity'];

				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, round($Available,0));
				
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, '=COUNTIFS(Barcodes!$A$1:$A$999,A'.$i.')');
				
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, '=H'.$i.'-D'.$i.'');

/*				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '=G'.$i.'*J'.$i.'');

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
*/
				$i++;
			}
			
			// Calculating totals, subtotals, etc
/*			$objPHPExcel->getActiveSheet()->setCellValue('A1', '=COUNTA(A'.$StartingRow.':A'.$i.')');
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
*/		
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('B6');

			// Set auto filter
			$objPHPExcel->getActiveSheet()->setAutoFilter('A5:I' . $i);
			
			// Auto Size columns
			foreach(range('D','I') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File ='Inventory-' .  $Location . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
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
			$Title = _('Excel file for Inventory Taking');
			include('includes/header.inc');
			prnMsg('Inventory Taking: No items to count');
			include('includes/footer.inc');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Inventory Taking');

	include('includes/header.inc');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Inventory Taking') . '" alt="" />' . ' ' . _('Excel file for Inventory Taking') . '
		</p>';

	echo '<table class="selection">
			<tr>
				<td>' . _('Inventory Categories') . ':</td>
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

	echo '<tr><td>'. _('Location').':</td>
			<td><select name="StockLocation" onchange="submit();"> ';
	$SQL = "SELECT locations.loccode, 
					locationname 
			FROM locations 
			INNER JOIN locationusers 
				ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			ORDER BY locationname";
	$LocResult = DB_query($SQL);
	while ($myrow=DB_fetch_array($LocResult)){
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Inventory Taking Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');

} // End of function display()

?>