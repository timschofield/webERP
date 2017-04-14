<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['submit'])) {
    submit($db, $_POST['Tabs'], $_POST['FromDate'], $_POST['ToDate']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $TabToShow, $FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		// Search absic PC Tab information
		$SQL = "SELECT pctabs.tabcode,
					   pctabs.usercode,
					   pctabs.typetabcode,
					   pctabs.currency,
					   pctabs.tablimit,
					   pctabs.assigner,
					   pctabs.authorizer
				FROM  pctabs
				WHERE pctabs.tabcode = '" . $TabToShow . "'";
		$result = DB_query($SQL);
		$myTab = DB_fetch_array($result);

		$SQL = "SELECT SUM(pcashdetails.amount) AS previous
				FROM  pcashdetails
				WHERE pcashdetails.tabcode = '" . $TabToShow . "'
					AND pcashdetails.date < '" . FormatDateForSQL($FromDate) . "'";
		$result = DB_query($SQL);
		$myPreviousBalance = DB_fetch_array($result);

		$SQL = "SELECT pcashdetails.date,
					   pcashdetails.codeexpense,
					   pcashdetails.amount,
					   pcashdetails.authorized,
					   pcashdetails.notes,
					   pcashdetails.receipt
				FROM  pcashdetails
				WHERE pcashdetails.tabcode = '" . $TabToShow . "'
					AND pcashdetails.date >= '" . FormatDateForSQL($FromDate) . "'
					AND pcashdetails.date <= '" . FormatDateForSQL($ToDate) . "'
				ORDER BY pcashdetails.date, 
					pcashdetails.counterindex";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("PC Tab Expenses List")
										 ->setSubject("PC Tab Expenses List")
										 ->setDescription("PC Tab Expenses List")
										 ->setKeywords("")
										 ->setCategory("");
			
			// Formatting
			$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setWrapText(true);
			$objPHPExcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$objPHPExcel->getActiveSheet()->getStyle('B5')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('C:D')->getNumberFormat()->setFormatCode('#,###');
			$objPHPExcel->getActiveSheet()->getStyle('E1:E2')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$objPHPExcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Tab Code');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', $myTab['tabcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A2', 'User Code');
			$objPHPExcel->getActiveSheet()->setCellValue('B2', $myTab['usercode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Type of Tab');
			$objPHPExcel->getActiveSheet()->setCellValue('B3', $myTab['typetabcode']);
			$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Currency');
			$objPHPExcel->getActiveSheet()->setCellValue('B4', $myTab['currency']);
			$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Limit');
			$objPHPExcel->getActiveSheet()->setCellValue('B5', $myTab['tablimit']);
			$objPHPExcel->getActiveSheet()->setCellValue('A6', 'Assigner');
			$objPHPExcel->getActiveSheet()->setCellValue('B6', $myTab['assigner']);
			$objPHPExcel->getActiveSheet()->setCellValue('A7', 'Authorizer');
			$objPHPExcel->getActiveSheet()->setCellValue('B7', $myTab['authorizer']);

			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'From');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', $FromDate);
			$objPHPExcel->getActiveSheet()->setCellValue('D2', 'To');
			$objPHPExcel->getActiveSheet()->setCellValue('E2', $ToDate);
			
			$objPHPExcel->getActiveSheet()->setCellValue('A9', 'Date');
			$objPHPExcel->getActiveSheet()->setCellValue('B9', 'Expense Code');
			$objPHPExcel->getActiveSheet()->setCellValue('C9', 'Amount');
			$objPHPExcel->getActiveSheet()->setCellValue('D9', 'Balance');
			$objPHPExcel->getActiveSheet()->setCellValue('E9', 'Notes');
			$objPHPExcel->getActiveSheet()->setCellValue('F9', 'Receipt');
			$objPHPExcel->getActiveSheet()->setCellValue('G9', 'Authorized');

			$objPHPExcel->getActiveSheet()->setCellValue('B10', 'Previous Balance');
			$objPHPExcel->getActiveSheet()->setCellValue('D10', $myPreviousBalance['previous']);
			
			// Add data
			$i = 11;
			while ($myrow = DB_fetch_array($result)) {

				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, ConvertSQLDate($myrow['date']));
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['codeexpense']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['amount']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '=D'.($i-1).'+C'.$i.'');
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['notes']);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['receipt']);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, ConvertSQLDate($myrow['authorized']));

				$i++;
			}

			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A10');
			
			// Auto Size columns
			foreach(range('A','G') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle($TabToShow);
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'ExpensesList-' . $TabToShow. '.xlsx';
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
			$Title = _('Excel file for Petty Cash Tab Expenses List');
			include('includes/header.php');
			prnMsg('No data to analyse');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Petty Cash Tab Expenses List');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Petty Cash Tab Expenses List') . '" alt="" />' . ' ' . _('Excel file for Petty Cash Tab Expenses List') . '
		</p>';

	# Sets default date range for current month
	if (!isset($_POST['FromDate'])){
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}
	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	echo '<table class="selection">
		<tr>
		<td>' . _('For Petty Cash Tab') . ':</td>
		<td><select name="Tabs">';

	$sql = "SELECT tabcode
			FROM pctabs 
			ORDER BY tabcode";
	$CatResult=DB_query($sql);

	while ($myrow=DB_fetch_array($CatResult)){
		echo '<option value="' . $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';
	}
	echo '</select>
			</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" />
			' . _('To') . ':<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';

	echo '
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Petty Cash Tab Expenses List Excel File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

function beginning_of_month($date){
	$date2 = explode("-",$date);
	$m = $date2[1]; 
	$y = $date2[0];
	$first_of_month = $y . '-' . $m . '-01';
	return $first_of_month;
}

?>