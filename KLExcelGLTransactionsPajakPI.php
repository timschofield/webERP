<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');

require_once ('Classes/PHPExcel.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($_POST['FromDate'], $_POST['ToDate']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}

	if ($InputError == 0){
		$WhereFrom 	= " AND trandate >= '". FormatDateForSQL($FromDate) ."'";
		$WhereTo 	= " AND trandate <= '". FormatDateForSQL($ToDate) ."'";
		$OrderBy		= " ORDER BY accountgroups.groupname ASC, gltrans.account ASC, gltrans.trandate ASC";
		
		$sql = "SELECT accountgroups.groupname AS 'Group',
					gltrans.account AS 'AccountCode', 
					chartmasterPI.accountname AS 'AccountName', 
					gltrans.trandate AS 'Date', 
					ROUND(gltrans.amount,0) AS 'Amount', 
					gltrans.narrative AS 'Description'
				FROM gltrans, 
					chartmasterPI, 
					accountgroups
				WHERE gltrans.account = chartmasterPI.accountcode
					AND chartmasterPI.group_ = accountgroups.groupname
					AND (accountgroups.pandl = 1)".
					$WhereFrom .
					$WhereTo .
					$OrderBy
				;
		
		$ErrMsg = _('The SQL to find the KL GL Transactions ');
		$result = DB_query($sql,$ErrMsg);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("GL Transactions")
										 ->setSubject("GL Transactions")
										 ->setDescription("GL Transactions")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Group');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Account Code');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Account Name');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Date');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Amount');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Description');

			// Add data
			$i = 2;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['Group']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['AccountCode']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['AccountName']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($myrow['Date']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, round($myrow['Amount'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['Description']);
				$i++;
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','F') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('GL Transactions');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'PT ADU-GL-' . FormatDateForSQL($FromDate). '-' . FormatDateForSQL($ToDate) . '.xlsx';
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
			prnMsg('No GL transactions for the period: ' . $FromDate . ' to ' . $ToDate);
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file with GL Transactions');
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('GL Transactions for Retail Partner PI (Excel File)') . '" alt="" />' . ' ' . _('GL Transactions for Retail Partner PI (Excel File)') . '
		</p>';

	echo '<table>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File with GL Transactions') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>