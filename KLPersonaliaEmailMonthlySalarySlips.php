<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/htmlMimeMail.php');

$Title = _('Email Salary Slips To Employees');

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
		$PageTitle = _('Slip Gaji '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Slip THR '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	include('includes/header.php');

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
		$TextAdmin = "";
		
		// populates $SQL and $Result with the data to extract the salary slips
		include('includes/KLPersonaliaSQLSalarySlips.php');
		
		if (DB_num_rows($Result) != 0){

			while ($MyRow = DB_fetch_array($Result)) {
				// Let's start the real PDF creation 
				require_once('includes/tcpdf/tcpdf.php');

 				if ($SalaryType == "MONTHLY"){
					$CoreFileName = $MyRow['codename'] . '-SlipGaji-' . substr($LastDateOfPeriod,0,7);
				}else{
					$CoreFileName = $MyRow['codename'] . '-SlipTHR-' . substr($LastDateOfPeriod,0,7);
				}
				
				include('includes/KLPersonaliaPDFNewSalarySlip.php');
				include('includes/KLPersonaliaPDFCalculatedFields.php');
				include('includes/KLPersonaliaPDFOneSalarySlip.php');

				// save the pdf file for later attachment
				$FileName= $CoreFileName . '.pdf';
				$PathFileName = $_SESSION['reports_dir'] . '/' . $FileName;

				// KL RICARD for any weird reason it fails in TCPDF 6.2 and 6.7.5. 
				// as calls to $f = TCPDF_STATIC::fopenLocal($name, 'wb');
				// but if changed into $f = fopen($name, 'wb'); then it works

				$pdf->Output($PathFileName, 'F');
				$pdf-> __destruct();

				// prepare the email fields to employees
				$Subject  = $MyRow['codename'] . " " . $PageTitle;
				$Text = EmailTextForEmployee($MyRow['fullname'],$Company, $SalaryType);
				$Text = $Text . "\n---\r\n"; // \r is needed for signature separating
				$Text = $Text . 'Email sent by ' . $AdminTeam . ' at '.date('d/M/Y H:i:s').'';
				
				$mail = new htmlMimeMail();
				$mail->setText($Text);
				$mail->setSubject($Subject);

				$attachment = $mail->getFile($PathFileName);
				$mail->addAttachment($attachment, $FileName, 'application/pdf');
				
				// set the from address depending on the company
				if ($Company == 'PTBB'){
					$mail->setFrom('accounting@bumibiru.com', $AdminTeam);
					$SendTo = 'accounting@bumibiru.com'; // default SendTo address in case employee has no email
				}elseif ($Company == 'PTSMH'){
					$mail->setFrom('accounting@ptsmh.com', $AdminTeam);
					$SendTo = 'accounting@ptsmh.com'; // default SendTo address in case employee has no email
				}else{
					$mail->setFrom('accounting@ptadu.com', $AdminTeam);
					$SendTo = 'accounting@ptadu.com'; // default SendTo address in case employee has no email
				}
	
				// set the to address
				if ($MyRow['email'] != ""){
					// if we have employee email, send to employee email, overwritting default email.
					$SendTo = $MyRow['email'];
				}

				// KL RICARD Send to a dummy address depending on the code version
				if (strpos($_SERVER['PHP_SELF'],"TEST")!== false){
					// the current script filename contains TEST, we are on TEST code
					$SendTo = 'webmaster@kapal-laut.com';
				}

				$ResultEmailEmployee = $mail->send(array($SendTo));
				if($ResultEmailEmployee){
					$TextAdmin = $TextAdmin . "Slip gaji for ". $MyRow['codename'] . " sent to " . $SendTo . " at " . date('d/M/Y H:i:s') ." \n";
				}else{
					$TextAdmin = $TextAdmin . "Email with slip gaji to ". $MyRow['codename'] . " FAILED. \n";
				}
				sleep(1);
			}
			
			// prepare the email to accounting team
			$Subject  = $Company . " slip gaji distribution " . substr($LastDateOfPeriod,0,7);
			$TextAdmin = $TextAdmin . "\n---\r\n"; // \r is needed for signature separating
			$TextAdmin = $TextAdmin . 'Email sent by ' . $AdminTeam . ' at '. date('d/M/Y H:i:s') .'';
			
			$mail = new htmlMimeMail();
			$mail->setText($TextAdmin);
			$mail->setSubject($Subject);
			// set the from address depending on the company
			if ($Company == 'PTBB'){
				$mail->setFrom('accounting@bumibiru.com', $AdminTeam);
				$SendTo = 'accounting@bumibiru.com';
			}elseif ($Company == 'PTSMH'){
				$mail->setFrom('accounting@ptsmh.com', $AdminTeam);
				$SendTo = 'accounting@ptsmh.com';
			}else{
				$mail->setFrom('accounting@ptadu.com', $AdminTeam);
				$SendTo = 'accounting@ptadu.com';
			}
			
			// KL RICARD Send to a dummy address depending on the code version
			if (strpos($_SERVER['PHP_SELF'],"TEST")!== false){
				// the current script filename contains TEST, we are on TEST code
				$SendTo = 'webmaster@kapal-laut.com';
			}

			$ResultEmailAdmin = $mail->send(array($SendTo));
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
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
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

?>