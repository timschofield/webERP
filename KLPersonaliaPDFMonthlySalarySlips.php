<?php

include('includes/session.php');

$Title = __('Export PDF Salary Slips');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (isset($_POST['submit'])) {
	submit($_POST['Company'], $_POST['PeriodOfFile'], $_POST['SalaryType']);
} else {
	display($Title);
}

function submit($Company, $PeriodOfFile, $SalaryType) {

	//initialise no input errors
	$InputError = false;

	//first off validate inputs sensible
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromPeriodNo($PeriodOfFile);


	if ($SalaryType == "MONTHLY"){
		$PageTitle = __('Export PDF Monthly Salary Slips for ') . $PeriodMonth;
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = __('Export PDF THR Only Slips for ') . $PeriodMonth;
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = true;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodOfFile + 1)){
			$InputErrorMessage = "The month selected to export PDF Monthly Salary Slips should be last month";
			$InputError = true;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodOfFile)){
			$InputErrorMessage = "The month selected to export PDF THR Only Salary Slips should be this current month";
			$InputError = true;
		}
	}

	if(!$InputError){
		// populates $SQL and $Result with the data to extract the salary slips
		include('includes/KLPersonaliaSQLSalarySlips.php');

		if (DB_num_rows($Result) != 0){
			// Let's start the real PDF creation 
			 // Ensure DomPDF is loaded via Composer
		
			if ($SalaryType == "MONTHLY"){
				$CoreFileName = $Company . '-MonthlySalarySlips-' . $PeriodMonth;
			}else{
				$CoreFileName = $Company . '-THROnlySalarySlips-' . $PeriodMonth;
			}
			
			// Generate initial HTML and CSS styles
			$HTML = ''; // Initialize HTML with no whitespace
			// Make sure the included file doesn't start with whitespace or newlines
			ob_start(); // Start output buffering
			
			include('includes/KLPersonaliaPDFNewSalarySlip.php');
			$HTML .= trim(ob_get_clean()); // Trim to remove any leading/trailing whitespace
			
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

			$firstSalarySlip = true; // Flag to track first salary slip

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
				
				// add one salary slip HTML
				// Pass the firstSalarySlip flag to the included file
				$isFirstSlip = $firstSalarySlip;
				include('includes/KLPersonaliaPDFOneSalarySlip.php');
				$firstSalarySlip = false; // After first slip, set flag to false
			}
			
			// prepare page with totals
			$HTML .= '<div class="page-break"></div>';

			// Company header
			include('includes/KLPersonaliaPDFCompanyHeader.php');

			$HTML .= '<div class="font-large bold margin-top-5">';
			$HTML .= 'Salary totals for ' . $PeriodMonth;
			$HTML .= '</div>';
			$HTML .= '<div class="margin-top-5">';
			$HTML .= 'Total Employees by Bank Danamon Transfer LLG: ' . locale_number_format($EmployeesByBankTransferLLG) . '<br>';
			$HTML .= 'Total Amount by Bank Danamon Transfer LLG: ' . locale_number_format($AmountByBankTransferLLG) . '<br>';
			$HTML .= '</div>';
			$HTML .= '<div class="margin-top-5">';
			$HTML .= 'Total Employees by Bank Danamon Transfer Payroll: ' . locale_number_format($EmployeesByBankTransferPayroll) . '<br>';
			$HTML .= 'Total Amount by Bank Danamon Transfer Payroll: ' . locale_number_format($AmountByBankTransferPayroll) . '<br>';
			$HTML .= '</div>';
			$HTML .= '<div class="margin-top-5">';
			$HTML .= 'Total Employees by Check: ' . locale_number_format($EmployeesByCheck) . '<br>';
			$HTML .= 'Total Amount by Check: ' . locale_number_format($AmountByCheck) . '<br>';
			$HTML .= '</div>';

			$CheckNumber = 1;
			if ($EmployeesByCheck > 0) {
				$HTML .= '<table class="bordered" style="width: 60%;">'; // Keep bordered class
				while($CheckNumber <= $EmployeesByCheck) {
					$HTML .= '<tr>';
					$HTML .= '<td class="text-right">' . $Check[$CheckNumber]['Name'] . '</td>';
					$HTML .= '<td class="text-right">' . locale_number_format($Check[$CheckNumber]['Amount']) . '</td>';
					$HTML .= '</tr>';
					$CheckNumber++;
				}
				$HTML .= '</table>';
			}

			$HTML .= '<div class="margin-top-5">';
			$HTML .= 'Total Employees by Cash: ' . locale_number_format($EmployeesByCash) . '<br>';
			$HTML .= 'Total Amount by Cash: ' . locale_number_format($AmountByCash) . '<br>';
			$HTML .= '</div>';

			$CashNumber = 1;
			if ($EmployeesByCash > 0) {
				$HTML .= '<table class="bordered" style="width: 60%;">'; // Keep bordered class
				while($CashNumber <= $EmployeesByCash) {
					$HTML .= '<tr>';
					$HTML .= '<td class="text-right">' . $Cash[$CashNumber]['Name'] . '</td>';
					$HTML .= '<td class="text-right">' . locale_number_format($Cash[$CashNumber]['Amount']) . '</td>';
					$HTML .= '</tr>';
					$CashNumber++;
				}
				$HTML .= '</table>';
			}
			
			// Initialize dompdf and output the PDF
			$options = new \Dompdf\Options();
			$options->set('isHtml5ParserEnabled', true);
			$options->set('isRemoteEnabled', true);
			$dompdf = new \Dompdf\Dompdf($options);
			$dompdf->loadHtml($HTML);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			
			// download the pdf file
			$FileName = $CoreFileName . '.pdf';
			$dompdf->stream($FileName, array('Attachment' => true));
		
		}else{
			include('includes/header.php');
			prnMsg('No data to export PDF Monthly Salary Slips', 'info');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
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
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
		<legend>' . __('Parameters Selection') . '</legend>';

	include('includes/KLPersonaliaParameterSelection.php');

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', $Title);

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()
