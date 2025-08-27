<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Email Salary Slips To Employees');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['PeriodOfFile'], $_POST['SalaryType']);
} else {
	display($Title);
}

function submit($Title, $Company, $PeriodOfFile, $SalaryType) {

	//initialise no input errors
	$InputError = false;

	//first off validate inputs sensible
	$YearMonth = YearAndMonthFromSQLDate(EndDateSQLFromPeriodNo($PeriodOfFile), true);
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromPeriodNo($PeriodOfFile);

	if ($SalaryType == "MONTHLY"){
		$PageTitle = __('Slip Gaji ') . $PeriodMonth;
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = __('Slip THR ') . $PeriodMonth;
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = true;
	}

	include('includes/header.php');

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
		$TextAdmin = "";
		
		// populates $SQL and $Result with the data to extract the salary slips
		include('includes/KLPersonaliaSQLSalarySlips.php');
		
		if (DB_num_rows($Result) != 0){
			// Initialize DomPDF
			

			while ($MyRow = DB_fetch_array($Result)) {
				// Prepare filename
				if ($SalaryType == "MONTHLY"){
					$CoreFileName = $MyRow['codename'] . '-SlipGaji-' . $YearMonth;
				}else{
					$CoreFileName = $MyRow['codename'] . '-SlipTHR-' . $YearMonth;
				}
				
				// Generate PDF using DomPDF
				$HTML = ''; // Initialize HTML with no whitespace
				
				ob_start(); // Start output buffering
				include('includes/KLPersonaliaPDFNewSalarySlip.php');
				$HTML .= trim(ob_get_clean()); // Get and trim output buffer
				
				// Set the first slip flag to true since we're creating individual PDFs
				$isFirstSlip = true;
				
				include('includes/KLPersonaliaPDFCalculatedFields.php');
				include('includes/KLPersonaliaPDFOneSalarySlip.php');
				
				$HTML .= '</body></html>';
				
				// Create the PDF
				$options = new \Dompdf\Options();
				$options->set('isHtml5ParserEnabled', true);
				$options->set('isRemoteEnabled', true);
				$dompdf = new \Dompdf\Dompdf($options);
				$dompdf->loadHtml($HTML);
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->render();
				
				// Save the PDF file for later attachment
				$FileName = $CoreFileName . '.pdf';
				$PathFileName = $_SESSION['reports_dir'] . '/' . $FileName;
				
				file_put_contents($PathFileName, $dompdf->output());

				// prepare the email fields to employees
				$Subject  = $MyRow['codename'] . " " . $PageTitle;
				
				$Text = EmailTextForEmployee($MyRow['fullname'],$Company, $SalaryType);
				$Text .= "\n---\r\n"; // \r is needed for signature separating
				$Text .= 'Email sent by ' . $AdminTeam . ' at '.date('d/M/Y H:i:s').'';
				
				// set the from address depending on the company
				if ($Company == 'PTBB'){
					$SendFrom = 'accounting@bumibiru.com';
					$SendTo = 'accounting@bumibiru.com'; // default SendTo address in case employee has no email
				}elseif ($Company == 'PTSMH'){
					$SendFrom = 'accounting@ptsmh.com';
					$SendTo = 'accounting@ptsmh.com'; // default SendTo address in case employee has no email
				}else{
					$SendFrom = 'accounting@ptadu.com';
					$SendTo = 'accounting@ptadu.com'; // default SendTo address in case employee has no email
				}
	
				// set the to address
				if ($MyRow['email'] != ""){
					// if we have employee email, send to employee email, overwritting default email.
					$SendTo = $MyRow['email'];
				}

				$ResultEmailEmployee = SendEmailFromWebERP($SendFrom, 
														$SendTo,
														$Subject,
														$Text,
														$PathFileName,
														true);

				if($ResultEmailEmployee){
					$TextAdmin = $TextAdmin . "Slip gaji for ". $MyRow['codename'] . " sent to " . $SendTo . " at " . date('d/M/Y H:i:s') ." \n";
				}else{
					$TextAdmin = $TextAdmin . "Email with slip gaji to ". $MyRow['codename'] . " FAILED. \n";
				}
				sleep(1);
			}
			
			// prepare the email to accounting team
			$Subject  = $Company . " slip gaji distribution " . $PeriodMonth;
			$TextAdmin = $TextAdmin . "\n---\r\n"; // \r is needed for signature separating
			$TextAdmin = $TextAdmin . 'Email sent by ' . $AdminTeam . ' at '. date('d/M/Y H:i:s') .'';
			
			// set the from and to addresses depending on the company
			if ($Company == 'PTBB'){
				$SendFrom = 'accounting@bumibiru.com';
				$SendTo = 'accounting@bumibiru.com';
			}elseif ($Company == 'PTSMH'){
				$SendFrom = 'accounting@ptsmh.com';
				$SendTo = 'accounting@ptsmh.com';
			}else{
				$SendFrom = 'accounting@ptadu.com';
				$SendTo = 'accounting@ptadu.com';
			}
			
			$ResultEmailAdmin = SendEmailFromWebERP($SendFrom, 
													$SendTo,
													$Subject,
													$TextAdmin,
													'',
													true);
			
			if($ResultEmailAdmin){
				prnMsg("Details of slip gaji distribution sent to " . $SendTo);
			}else{
				prnMsg("Details of slip gaji distribution email FAILED", "warn");
			}

		}else{
			prnMsg('No data to send emails with Monthly Salary Slips', 'warn');
		}
	}else{
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
	}
	include('includes/footer.php');
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

function EmailTextForEmployee($Name, $Company, $SalaryType){
	$Text = "Dear ". $Name . ",\n\n";
	if ($SalaryType == "MONTHLY"){
		$Text = $Text . "Terlampir adalah slip gaji anda bulan ini.\n"; 
	}else{
		$Text = $Text . "Terlampir adalah slip THR anda bulan ini.\n"; 
	}
	$Text = $Text . "Kami mengucapkan terima kasih atas kerja keras dan dedikasi yang telah anda berikan.\n"; 
	$Text = $Text . "Silakan hubungi bagian personalia jika ada pertanyaan atau keluhan.\n"; 
	$Text = $Text . "Tiada kesuksesan tanpa perjuangan dan kerja keras!\n\n"; 
	$Text = $Text . "Terima kasih,\n"; 
	if ($Company == 'PTBB'){
		$Text = $Text . "PT. Bumi Biru Admin Team"; 
	}elseif ($Company == 'PTSMH'){
		$Text = $Text . "PT. Sungai Mutiara Hitam Admin Team"; 
	}else{
		$Text = $Text . "PT. Angin Dingin Utara Admin Team"; 
	}
	return $Text;
}
