<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLCompanySelection.php');

$Title = _('Export PDF Monthly Salary Slips');

if (isset($_POST['submit'])) {
	submit($Title, $Company, $_POST['DateOfFile'], $db);
} else {
	display($Title, $db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, &$db) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod), $db);
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$PeriodMonth = MonthAndYearFromSQLDate($LastDateOfPeriod);
	
	
	if($PeriodNow != ($PeriodExportDate + 1)){
		include('includes/header.inc');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
			</p>';
		prnMsg("The month selected to export PDF Monthly Salary Slips should be last month","warn");
		include('includes/footer.inc');
		$InputError = TRUE;
	}

	if(!$InputError){
		$SQL = "SELECT 	codename,
						fullname,
						position,
						salaryfrom,
						salaryto,
						paymentday,
						paymentmethod,
						bankaccount,
						bankaccountholder,
						bankcode,
						upahpokok,
						tunjanganmakan,
						tunjangantransport,
						tunjanganjabatan,
						tunjanganmasakerja,
						tunjangankendaraan,
						komisitetap,
						komisiretail,
						komisisupport,
						bonuspenjualan,
						lembur,
						thr,
						penerimaanlain,
						penerimaanlainnotes,
						potonganjht,
						potonganaskes,
						potonganpph21,
						potonganabsen,
						potonganlain2,
						potonganlain2notes,
						bulatan
				FROM salariescalculated
				WHERE company = '" . $Company . "'
					AND periodno = '" . $PeriodExportDate . "'
				ORDER BY paymentmethod,
					codename";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// Let's start the real PDF creation 
			require_once('includes/tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
			$CoreFileName = $Company . '-SalarySlips-' . $LastDateOfPeriod;
			
			// set PDF document information
			$pdf->SetCreator('webERP');
			$pdf->SetAuthor('webERP');
			$pdf->SetTitle($CoreFileName);
			$pdf->SetSubject($CoreFileName);
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			while ($myrow = DB_fetch_array($result)) {
				$pdf->AddPage();
				// https://tcpdf.org/examples/example_005/
				// https://tcpdf.org/docs/source_docs/classTCPDF/#aa81d4b585de305c054760ec983ed3ece
				$pdf->MultiCell(100, 0, 'Nama Panggilan:', 0, 'R', 0, 0, '', '', true);
				$pdf->MultiCell(100, 0, $myrow['codename'], 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell(100, 0, 'Nama Lengkap:', 0, 'R', 0, 0, '', '', true);
				$pdf->MultiCell(100, 0, $myrow['fullname'], 0, 'L', 0, 1, '', '', true);

			}
			
			// download the pdf file
			$FileName= $CoreFileName . '.pdf';
			$pdf->Output($FileName, 'D');
			$pdf->__destruct();
		
		
		}else{
			include('includes/header.inc');
			prnMsg('No data to export PDF Monthly Salary Slips ');
			include('includes/footer.inc');
		}
	}
} // End of function submit()


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.inc');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Company" value="' . $_GET['Company'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	echo '<tr><td>' . _('Select Month of the Salaries') . '</td>
							<td><select name="DateOfFile">';
							
	$PeriodNow = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$PeriodsResult = DB_query("SELECT lastdate_in_period, periodno FROM periods ORDER BY periodno");
	while ($PeriodRow = DB_fetch_row($PeriodsResult)){
		if ($PeriodRow[1] == ($PeriodNow-1)){
			echo '<option selected="selected" value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
		}else{
			echo '<option value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
		}
	}
	echo '</select></td></tr>
		</table>';

	echo '<table>';
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');

} // End of function display()

?>