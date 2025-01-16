<?php
require_once 'vendor/autoload.php';

include('includes/session.php');
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.inc');
include('includes/UIGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLUIGeneralFunctions.php');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['PartnerCode'])) {
    $_POST['PartnerCode'] = 'PTADU';
}

if (isset($_POST['submit'])) {
    submit($_POST['PartnerCode'], $_POST['FromDate'], $_POST['ToDate']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($PartnerCode, $FromDate, $ToDate) {

	//initialise no input errors
	$InputError = 0;

	$FromDateSQL = FormatDateForSQL($FromDate);
	$ToDateSQL = FormatDateForSQL($ToDate);

	//first off validate inputs sensible
	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}
	if (strtotime($FromDateSQL) > strtotime($ToDateSQL)) {
		$InputError = 1;
		prnMsg(_('The From Date must be before the To Date'),'error');
	}
	if (!isset($_POST['PartnerCode'])) {
		$InputError = 1;
		prnMsg(_('Invalid Cempany Code'),'error');
	}

	$ShortPartnerCode = substr($PartnerCode, 2); // Remove first 2 characters from partner code PT or PO
	
	$SQLSettings =  "SELECT klretailpartners.accountcomissioncreditcard,
						klretailpartners.accountcomissionwechat,
						klretailpartners.accountcomissionqris
					 FROM klretailpartners
					 WHERE klretailpartners.partnercode = '" . $PartnerCode . "'";
	$ResultSettings = DB_query($SQLSettings);
	if (DB_num_rows($ResultSettings)==0) {
		$InputError = 1;
		prnMsg(_('Invalid Company Settings'),'error');
	} else {
		$MyRowSettings = DB_fetch_array($ResultSettings); //get the only row returned
	}

	if ($InputError == 0){

		// Create new Spreadsheet object
		$objPHPExcel = new Spreadsheet();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("webERP")
									 ->setLastModifiedBy("webERP")
									 ->setTitle($PartnerCode . " GL Transactions")
									 ->setSubject($PartnerCode . " GL Transactions")
									 ->setDescription($PartnerCode . "GL Transactions")
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


		$WhereFrom 	= " AND trandate >= '". $FromDateSQL ."'";
		$WhereTo 	= " AND trandate <= '". $ToDateSQL ."'";

		$i = 2;
		$ErrMsg = _('The SQL to find the GL Transactions for '. $PartnerCode);

		$WhereGroupedAccounts = " ( accountgroups.groupname IN ('Penjualan', 'HPP (COGS)') 
									OR gltrans.account = '" . $MyRowSettings['accountcomissioncreditcard'] . "'
									OR gltrans.account = '" . $MyRowSettings['accountcomissionwechat'] . "'
									OR gltrans.account = '" . $MyRowSettings['accountcomissionqris'] . "'
									OR gltrans.account IN " . GL_CONSUMABLES . " 
									OR gltrans.account IN " . GL_ADJUSTMENT_STOCK . " 
									OR gltrans.account IN " . GL_COMMISSION_TOKOPEDIA . " 
									OR gltrans.account IN " . GL_COMMISSION_SHOPEE . " 
									OR gltrans.account IN " . GL_COMMISSION_PAYPAL . " " .
									") ";
		
		// Regular GL accounts (NOT HPP (COGS) OR PENJUALAN))
		$SQL = "SELECT accountgroups.groupname AS 'Group',
					gltrans.account AS 'AccountCode', 
					chartmaster" . $ShortPartnerCode . ".accountname AS 'AccountName', 
					gltrans.trandate AS 'Date', 
					ROUND(gltrans.amount,0) AS 'Amount', 
					gltrans.narrative AS 'Description'
				FROM gltrans, 
					chartmaster" . $ShortPartnerCode . ", 
					accountgroups
				WHERE gltrans.account = chartmaster" . $ShortPartnerCode . ".accountcode
					AND chartmaster" . $ShortPartnerCode . ".group_ = accountgroups.groupname
					AND (accountgroups.pandl = 1)
					AND NOT " . $WhereGroupedAccounts . " " .
					$WhereFrom .
					$WhereTo . " 
				ORDER BY accountgroups.groupname ASC, 
					gltrans.account ASC, 
					gltrans.trandate ASC ";
					
		$Result = DB_query($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){
			// Add data
			while ($MyRow = DB_fetch_array($Result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $MyRow['Group']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $MyRow['AccountCode']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $MyRow['AccountName']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($MyRow['Date']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, round($MyRow['Amount'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $MyRow['Description']);
				$i++;
			}
		}

		// Exception GL accounts grouped (HPP (COGS) OR PENJUALAN))
		$SQL = "SELECT accountgroups.groupname AS 'Group',
					gltrans.account AS 'AccountCode', 
					chartmaster" . $ShortPartnerCode . ".accountname AS 'AccountName', 
					gltrans.trandate AS 'Date', 
					SUM(ROUND(gltrans.amount,0)) AS 'Amount', 
					gltrans.narrative AS 'Description'
				FROM gltrans, 
					chartmaster" . $ShortPartnerCode . ", 
					accountgroups
				WHERE gltrans.account = chartmaster" . $ShortPartnerCode . ".accountcode
					AND chartmaster" . $ShortPartnerCode . ".group_ = accountgroups.groupname
					AND (accountgroups.pandl = 1)
					AND " . $WhereGroupedAccounts . " " .
					$WhereFrom .
					$WhereTo . " 
				GROUP BY accountgroups.groupname,
					gltrans.account, 
					gltrans.trandate 
				ORDER BY accountgroups.groupname ASC, 
					gltrans.account ASC, 
					gltrans.trandate ASC ";
					
		$Result = DB_query($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){
			// Add data
			while ($MyRow = DB_fetch_array($Result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $MyRow['Group']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $MyRow['AccountCode']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $MyRow['AccountName']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, ConvertSQLDate($MyRow['Date']));
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, round($MyRow['Amount'],0));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, 'Total harian ' . $MyRow['AccountName']);
				$i++;
			}
		}

		// Freeze panes
		$objPHPExcel->getActiveSheet()->freezePane('A2');
	
		// Auto Size columns
		foreach(range('A','F') as $ColumnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($ColumnID)
				->setAutoSize(true);
		}
		
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($PartnerCode . "GL Transactions");

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client's web browser
		if ($_POST['Format'] == 'xlsx') {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = $PartnerCode . '-GL-' . FormatDateForSQL($FromDate). '-' . FormatDateForSQL($ToDate) . '.xlsx';
		} else if ($_POST['Format'] == 'ods') {
			header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
			$File = $PartnerCode . '-GL-' . FormatDateForSQL($FromDate). '-' . FormatDateForSQL($ToDate) . '.ods';
		}
		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		if ($_POST['Format'] == 'xlsx') {
			$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
			$objWriter->save('php://output');
		} else if ($_POST['Format'] == 'ods') {
			$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($objPHPExcel);
			$objWriter->save('php://output');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Export Excel with GL Transactions for PT');
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset><legend>' . _('Export Parameters') . '</legend>';
	
	echo FieldToSelectOneRetailPartner("PartnerCode", $_POST['PartnerCode'], _('Company'), _('Select the company to export GL transactions'), '', 1, true, false);
	echo FieldToSelectOneDate('FromDate', $_POST['FromDate'], _('From Date'), '', '', 2, true, false);
	echo FieldToSelectOneDate('ToDate', $_POST['ToDate'], _('To Date'), '', '', 3, true, false);
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('File Format'), '', '', 4, true, false);
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export File GL transactions of a PT'));

	echo '</form>';
	
	include('includes/footer.php');

} // End of function display()

?>