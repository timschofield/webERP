<?php

include('includes/session.inc');
$Title = _('Excel file with GL Transactions');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/** Include PHPExcel */
require_once ('/Classes/PHPExcel.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit'])) {
    submit($db, $_POST['FromDate'], $_POST['ToDate']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $FromDate, $ToDate) {

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

	$WhereFrom 	= " AND trandate >= '". FormatDateForSQL($FromDate) ."'";
	$WhereTo 	= " AND trandate <= '". FormatDateForSQL($ToDate) ."'";
	$OrderBy		= " ORDER BY accountgroups.groupname ASC, gltrans.account ASC, gltrans.trandate ASC";
	
	$sql = "SELECT accountgroups.groupname AS 'Group',
				gltrans.account AS 'AccountCode', 
				chartmaster.accountname AS 'AccountName', 
				gltrans.trandate AS 'Date', 
				ROUND(gltrans.amount,0) AS 'Amount', 
				gltrans.narrative AS 'Description',
				tags.tagdescription AS 'Tag'
			FROM gltrans, 
				chartmaster, 
				accountgroups, 
				tags
			WHERE gltrans.account = chartmaster.accountcode
				AND chartmaster.group_ = accountgroups.groupname
				AND gltrans.tag = tags.tagref
				AND (accountgroups.pandl = 1)".
				$WhereFrom .
				$WhereTo .
				$OrderBy
			;
	
//	echo "<br/>".$sql."<br/>";
	
	$ErrMsg = _('The SQL to find the KL GL Transactions ');
	$result = DB_query($sql,$db,$ErrMsg);
	if (DB_num_rows($result) != 0){

	// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("TEST")
									 ->setLastModifiedBy("webERP")
									 ->setTitle("TEST GL Transactions")
									 ->setSubject("TEST GL Transactions")
									 ->setDescription("TEST GL Transactions")
									 ->setKeywords("")
									 ->setCategory("");

	
		// Add title data
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Group')
					->setCellValue('B1', 'Account Code')
					->setCellValue('C1', 'Account Name')
					->setCellValue('D1', 'Date')
					->setCellValue('E1', 'Amount')
					->setCellValue('F1', 'Description')
					->setCellValue('G1', 'Tag');
	
		echo '<p class="page_title_text" align="center"><strong>' . "GL Transactions from: " . $FromDate . ' to ' . $ToDate . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '
						<tr>
							<th>' . _('Account Code') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Amount') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;

		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['AccountCode'], 
					ConvertSQLDate($myrow['Date']),
					locale_number_format($myrow['Amount'],0)
					);
			$i++;
		}
		echo '</table>
				</div>';

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('GL Transactions');


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="01simple.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');


	}else{
		prnMsg('No GL tranaactions for the period: ' . $FromDate . ' to ' . $ToDate);
	}
	
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';


  echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File') . '" /></td>
		</tr>
		</table>
	<br />';
   echo '</div>
         </form>';

} // End of function display()

include('includes/footer.inc');
?>