<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');

$Title = _('Export PDF Salary Slips');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['DateOfFile'], $_POST['SalaryType']);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, $SalaryType) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod));
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromSQLDate($LastDateOfPeriod);

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Export PDF Monthly Salary Slips for '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Export PDF THR Only Slips for '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodExportDate + 1)){
			$InputErrorMessage = "The month selected to export PDF Monthly Salary Slips should be last month";
//			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodExportDate)){
			$InputErrorMessage = "The month selected to export PDF THR Only Salary Slips should be this current month";
			$InputError = TRUE;
		}
	}

	if(!$InputError){
		// populates $SQL and $Result with the data to extract the salary slips
		include('includes/KLPersonaliaSQLSalarySlips.php');

		if (DB_num_rows($Result) != 0){
			// Let's start the real PDF creation 
			require_once('includes/tcpdf/tcpdf.php');
			
			if ($SalaryType == "MONTHLY"){
				$CoreFileName = $Company . '-MonthlySalarySlips-' . substr($LastDateOfPeriod,0,7);
			}else{
				$CoreFileName = $Company . '-THROnlySalarySlips-' . substr($LastDateOfPeriod,0,7);
			}
			
			include('includes/KLPersonaliaPDFNewSalarySlip.php');
			
			$EmployeesByBankTransferLLG = 0;
			$AmountByBankTransferLLG = 0;
			$EmployeesByBankTransferPayroll = 0;
			$AmountByBankTransferPayroll = 0;
			$EmployeesByCheck = 0;
			$AmountByCheck = 0;
			$Check = array();
			$EmployeesByCash = 0;
			$AmountByCash = 0;
			$Cash = array();

			while ($MyRow = DB_fetch_array($Result)) {
				include('includes/KLPersonaliaPDFCalculatedFields.php');
				
				// set information depending on payment method
				if (strtoupper($MyRow['paymentmethod']) == 'BANK'){
					if (strtoupper($MyRow['bankcode']) == 'DANAMON'){
						$EmployeesByBankTransferPayroll++;
						$AmountByBankTransferPayroll += $TotalBawaPulang;
					}else{
						$EmployeesByBankTransferLLG++;
						$AmountByBankTransferLLG += $TotalBawaPulang;
					}
				}elseif (strtoupper($MyRow['paymentmethod']) == 'CHECK'){
					$EmployeesByCheck++;
					$Check[$EmployeesByCheck]['Name'] = $MyRow['codename'];
					$Check[$EmployeesByCheck]['Amount'] = $TotalBawaPulang;
					$AmountByCheck += $TotalBawaPulang;
				}elseif (strtoupper($MyRow['paymentmethod']) == 'CASH'){
					$EmployeesByCash++;
					$Cash[$EmployeesByCash]['Name'] = $MyRow['codename'];
					$Cash[$EmployeesByCash]['Amount'] = $TotalBawaPulang;
					$AmountByCash += $TotalBawaPulang;
				}
				
				// add and print one salary slip
				include('includes/KLPersonaliaPDFOneSalarySlip.php');
			}
			
			// prepare page with totals
			$pdf->AddPage();

			// Company header
			include('includes/KLPersonaliaPDFCompanyHeader.php');

			$pdf->SetFont($FontType, '', $FontBigSize);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Salary totals for ' . ConvertSQLDate($LastDateOfPeriod), 0, 'L', 0, 1, '', '', true);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Bank Danamon Transfer LLG: ' .	locale_number_format($EmployeesByBankTransferLLG), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Bank Danamon Transfer LLG: ' .	locale_number_format($AmountByBankTransferLLG), 0, 'L', 0, 1, '', '', true);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Bank Danamon Transfer Payroll: ' .	locale_number_format($EmployeesByBankTransferPayroll), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Bank Danamon Transfer Payroll: ' .	locale_number_format($AmountByBankTransferPayroll), 0, 'L', 0, 1, '', '', true);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Check : ' .	locale_number_format($EmployeesByCheck), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Check: ' .	locale_number_format($AmountByCheck), 0, 'L', 0, 1, '', '', true);
			$CheckNumber = 1;
			while($CheckNumber <= $EmployeesByCheck){
				$pdf->MultiCell($WidthColumn1, 0, $Check[$CheckNumber]['Name'], 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, locale_number_format($Check[$CheckNumber]['Amount']), 1, 'R', 0, 0, '', '', true);
				$pdf->ln(5);
				$CheckNumber++;
			}
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Cash: ' .	locale_number_format($EmployeesByCash), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Cash: ' .	locale_number_format($AmountByCash), 0, 'L', 0, 1, '', '', true);
			$CashNumber = 1;
			while($CashNumber <= $EmployeesByCash){
				$pdf->MultiCell($WidthColumn1, 0, $Cash[$CashNumber]['Name'], 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, locale_number_format($Cash[$CashNumber]['Amount']), 1, 'R', 0, 0, '', '', true);
				$pdf->ln(5);
				$CashNumber++;
			}
			
			// download the pdf file
			$FileName= $CoreFileName . '.pdf';
			$pdf->Output($FileName, 'D');
			$pdf->__destruct();
		
		
		}else{
			include('includes/header.php');
			prnMsg('No data to export PDF Monthly Salary Slips ');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	include('includes/KLPersonaliaParameterSelection.php');
	
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>