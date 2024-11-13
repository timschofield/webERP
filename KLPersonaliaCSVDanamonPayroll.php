<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');

$Title = _('Export CSV File for Transfer to Danamon Accounts');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['DateOfFile'], $_POST['SalaryType'], $db);
} else {
	display($Title, $db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, $SalaryType, &$db) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod));
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromSQLDate($LastDateOfPeriod);
	
	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Export CSV Danamon (between Danamon Payroll Accounts) Monthly Salary for '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Export CSV Danamon (between Danamon Payroll Accounts) THR Only for '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodExportDate + 1)){
			$InputErrorMessage = "The month selected to export Monthly Salary CSV File for Transfer between Danamon Payroll Accounts should be last month";
			$InputError = TRUE;
		}
	}
		
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodExportDate)){
			$InputErrorMessage = "The month selected to export THR Only CSV File for Transfer between Danamon Payroll Accounts should be current month";
			$InputError = TRUE;
		}
	}

	if(!$InputError){
		$SQL = "SELECT bankaccount,
						bankaccountholder,
						bankcode,
						fullname,
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
					AND periodno = '" . $PeriodExportDate . "'
					AND salarytype = '" . $SalaryType . "'
					AND UPPER(paymentmethod) = 'BANK'
					AND bankcode LIKE '%Danamon%'
				ORDER BY joiningdate,
					fullname";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// prepare CSV file
			header("Content-Type: text/csv");
			if ($SalaryType == "MONTHLY"){
				header("Content-Disposition: attachment; filename=GajiTransferDanamonPayroll-" . $Today . ".csv");
			}else{
				header("Content-Disposition: attachment; filename=THRTransferDanamonpayroll-" . $Today . ".csv");
			}
			$output = fopen("php://output", "w");
			$Separator = ",";
			$EOL = "\n";
			$i = 0;
			while ($myrow = DB_fetch_array($result)) {
				$ValueTransfer = $myrow['upahpokok'] +
								$myrow['tunjanganmakan'] +
								$myrow['tunjangantransport'] +
								$myrow['tunjanganjabatan'] +
								$myrow['tunjanganmasakerja'] +
								$myrow['tunjangankendaraan'] +
								$myrow['komisitetap'] +
								$myrow['komisiretail'] +
								$myrow['komisisupport'] +
								$myrow['bonuspenjualan'] +
								$myrow['lembur'] +
								$myrow['thr'] +
								$myrow['penerimaanlain'] +
								$myrow['potonganjht'] +
								$myrow['potonganaskes'] +
								$myrow['potonganpph21'] +
								$myrow['potonganabsen'] +
								$myrow['potonganlain2'];

				if ($SalaryType == "MONTHLY"){
					$TextMessage = substr('Gaji' . ' '. $PeriodMonth,0,20);
				}else{
					$TextMessage = substr('THR' . ' '. $PeriodMonth,0,20);
				}
								
				$Line = $myrow['bankaccount'] . $Separator . 
						$ValueTransfer . $Separator . 
						$TextMessage . $Separator . 
						substr($myrow['fullname'],0,30) . $EOL;

				fwrite($output, $Line);
				$i++;
			}
			fclose($output);
		}else{
			include('includes/header.php');
			prnMsg('No data to export CSV File for Transfer between Danamon Payroll Accounts ');
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


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
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