<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = __('Export CSV File for Transfer Danamon Cash Connect');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['PeriodOfFile'], $_POST['SalaryType']);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $PeriodOfFile, $SalaryType) {

	//initialise no input errors
	$InputError = false;

	//first off validate inputs sensible
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromPeriodNo($PeriodOfFile);
	
	if ($SalaryType == "MONTHLY"){
		$PageTitle = __('Export CSV Danamon Cash Connect Monthly Salary for '). ConvertSQLDate($PeriodOfFile);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = __('Export CSV Danamon Cash Connect THR Only for '). ConvertSQLDate($PeriodOfFile);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = true;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodOfFile + 1)){
			$InputErrorMessage = "The month selected to export Monthly Salary CSV File for Transfer Danamon Cash Connect should be last month";
			$InputError = true;
		}
	}
		
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodOfFile)){
			$InputErrorMessage = "The month selected to export THR Only CSV File for Transfer LLG Danamon should be current month";
			$InputError = true;
		}
	}

	if(!$InputError){
		$SQL = "SELECT bankaccount,
						bankaccountholder,
						bankcode,
						fullname,
						email,
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
						potonganjht,
						potonganaskes,
						potonganpph21,
						potonganabsen,
						potonganlain2,
						bulatan
				FROM salariescalculated
				WHERE company = '" . $Company . "'
					AND periodno = '" . $PeriodOfFile . "'
					AND salarytype = '" . $SalaryType . "'
					AND UPPER(paymentmethod) = 'BANK'
				ORDER BY joiningdate,
					fullname";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			
			if ($SalaryType == "MONTHLY"){
				$HeaderDisposition = "Content-Disposition: attachment; filename=GajiTransferDanamon-" . $Today . ".csv";
				$FromAccountDescriptionHeader = "Monthly Salaries " . $PeriodMonth;
				$ToAccountDescrption = substr('Gaji' . ' '. $PeriodMonth,0,60);
			}else{
				$HeaderDisposition = "Content-Disposition: attachment; filename=THRTransferDanamon-" . $Today . ".csv";
				$FromAccountDescriptionHeader = "THR Salaries " . $PeriodMonth;
				$ToAccountDescrption = substr('THR' . ' '. $PeriodMonth,0,60);
			}
			
			if ($Company == "PTADU"){
				$DebitAccount = DANAMON_ACCOUNT_GAJI_PTADU;
			}elseif ($Company == "PTSMH"){
				$DebitAccount = DANAMON_ACCOUNT_GAJI_PTSMH;
			}else{
				$DebitAccount = DANAMON_ACCOUNT_GAJI_PTBB;
			}

			// prepare CSV file
			header("Content-Type: text/csv");
			header($HeaderDisposition);
			$output = fopen("php://output", "w");
			$Separator = ",";
			$EOL = "\n";
			
			// create initial line
			$Line = "H" . $Separator . 
					$DebitAccount . $Separator . 
					substr($FromAccountDescriptionHeader,0,60) . $Separator . 
					"S" . $Separator . 
					"Y" . $Separator . 
					"" . $Separator . 
					"" . $Separator . 
					"" . $Separator . 
					"" . $EOL;

			fwrite($output, $Line);
			
			$i = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				
				$ValueTransfer = $MyRow['upahpokok'] +
								$MyRow['tunjanganmakan'] +
								$MyRow['tunjangantransport'] +
								$MyRow['tunjanganjabatan'] +
								$MyRow['tunjanganmasakerja'] +
								$MyRow['tunjangankendaraan'] +
								$MyRow['komisitetap'] +
								$MyRow['komisiretail'] +
								$MyRow['komisisupport'] +
								$MyRow['bonuspenjualan'] +
								$MyRow['lembur'] +
								$MyRow['thr'] +
								$MyRow['penerimaanlain'] +
								$MyRow['potonganjht'] +
								$MyRow['potonganaskes'] +
								$MyRow['potonganpph21'] +
								$MyRow['potonganabsen'] +
								$MyRow['potonganlain2'];
								
				$BeneficiaryBankCode = FindBeneficiaryBankCode($MyRow['bankcode']);
				
				if ($BeneficiaryBankCode == "DANAMON"){
					// internal Danamon transfer
					$Line =	"D" . $Separator . 
							"HAC" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							$MyRow['bankaccount'] . $Separator . 
							$MyRow['bankaccountholder'] . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							$MyRow['email'] . $Separator . 
							"" . $Separator . 
							$ToAccountDescrption . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							$ValueTransfer . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"Y" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"2150" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $EOL;
				}else{
					// other banks transfer
					$Line =	"D" . $Separator . 
							"LAC" . $Separator . 
							"1" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							$BeneficiaryBankCode . $Separator . 
							"" . $Separator . 
							$MyRow['bankaccount'] . $Separator . 
							$MyRow['bankaccountholder'] . $Separator . 
							"IDR" . $Separator . 
							"Bali" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							$MyRow['email'] . $Separator . 
							"" . $Separator . 
							$ToAccountDescrption . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"IDR" . $Separator . 
							$ValueTransfer . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"REM" . $Separator . 
							"S" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"Y" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $Separator . 
							"" . $EOL;
				}

				fwrite($output, $Line);
				$i++;
			}
			fclose($output);
		}else{
			include('includes/header.php');
			prnMsg('No data to export CSV File for Transfer Cash Connect ');
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

	echo '<fieldset>
		<legend>' . __('Parameters') . '</legend>';
	
	include('includes/KLPersonaliaParameterSelection.php');

	echo '</fieldset>';
	
	echo OneButtonCenteredForm('submit', $Title);

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

function FindBeneficiaryBankCode($ExcelBankCode){
	$ExcelBankCode = strtoupper($ExcelBankCode);
	switch ($ExcelBankCode) {
    case "BCA":
		$BeneficiaryBankCode = "0140397";
        break;
    case "BII":
 		$BeneficiaryBankCode = "0160131";
        break;
    case "BPD BALI":
 		$BeneficiaryBankCode = "1290013";
        break;
    case "BNI":
 		$BeneficiaryBankCode = "0090010";
        break;
    case "BRI":
 		$BeneficiaryBankCode = "0020307";
        break;
    case "CIMB NIAGA":
 		$BeneficiaryBankCode = "0220026";
        break;
    case "DANAMON":
 		$BeneficiaryBankCode = "DANAMON";
        break;
    case "MANDIRI":
 		$BeneficiaryBankCode = "0080017";
        break;
    case "MAYAPADA":
 		$BeneficiaryBankCode = "0970017";
        break;
    case "MAYBANK":
 		$BeneficiaryBankCode = "0160131";
        break;
    case "PERMATA":
 		$BeneficiaryBankCode = "0130475";
        break;
    default:
		$BeneficiaryBankCode = "NOT FOUND";
	}
	return $BeneficiaryBankCode;
}
