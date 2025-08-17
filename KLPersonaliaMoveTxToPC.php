<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = __('Move Monthly Salaries Data to Petty Cash');
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['submit'])) {
	submit($_POST['Company'], $_POST['PeriodOfFile'], $_POST['PaymentDate'], $_POST['SalaryType']);
} else {
	display($Title);
}

include('includes/footer.php');


function submit($Company, $PeriodOfFile, $PaymentDate, $SalaryType) {
	global $RootPath, $Theme;
	
	$PaymentDate = FormatDateForSQL($PaymentDate);
	
	//initialise no input errors
	$InputError = FALSE;
	
	//first off validate inputs sensible
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	if ($SalaryType == "MONTHLY"){
		$PageTitle = __('Move Monthly Salary to Petty Cash for '). MonthAndYearFromPeriodNo($PeriodOfFile);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = __('Move THR Only to Petty Cash for '). MonthAndYearFromPeriodNo($PeriodOfFile);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodOfFile + 1)){
			$InputErrorMessage = "The month selected to Move Monthly Salaries Data to Petty Cash should be last month";
			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodOfFile)){
			$InputErrorMessage = "The month selected to Move THR Only Data to Petty Cash should be this current month";
			$InputError = TRUE;
		}
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
					AND periodno = '" . $PeriodOfFile . "'
					AND salarytype = '" . $SalaryType . "'
				ORDER BY paymentmethod,
					joiningdate,
					codename";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			echo '<div>';
			echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('Name') . '</th>
						<th class="SortedColumn">' . __('Fixed') . '</th>
						<th class="SortedColumn">' . __('Makan') . '</th>
						<th class="SortedColumn">' . __('Bensin') . '</th>
						<th class="SortedColumn">' . __('Commissions') . '</th>
						<th class="SortedColumn">' . __('Shifts') . '</th>
						<th class="SortedColumn">' . __('THR') . '</th>
						<th class="SortedColumn">' . __('Lain2') . '</th>
						<th class="SortedColumn">' . __('JHT ASKES') . '</th>
						<th class="SortedColumn">' . __('PPH21') . '</th>
						<th class="SortedColumn">' . __('Rounding') . '</th>
					</tr>
				</thead>
				<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				// Fixed
				$FixedSalary = $MyRow['upahpokok'] +
							$MyRow['tunjanganjabatan'] +
							$MyRow['tunjanganmasakerja'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "FIXED", $PaymentDate, $FixedSalary, $MyRow['codename']);
				
				// Makan
				$Makan = $MyRow['tunjanganmakan'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "MAKAN", $PaymentDate, $Makan, $MyRow['codename']);

				// Bensin
				$Bensin = $MyRow['tunjangantransport'] +
						$MyRow['tunjangankendaraan'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "BENSIN", $PaymentDate, $Bensin, $MyRow['codename']);
				
				//Commissions
				$Commissions = $MyRow['komisitetap'] +
							$MyRow['komisiretail'] +
							$MyRow['komisisupport'] +
							$MyRow['bonuspenjualan'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "COMMISSIONS", $PaymentDate, $Commissions, $MyRow['codename']);
				
				//Shifts
				$Shifts = $MyRow['lembur'] +
						$MyRow['potonganabsen'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "SHIFTS", $PaymentDate, $Shifts, $MyRow['codename']);
				
				//THR
				$THR = $MyRow['thr'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "THR", $PaymentDate, $THR, $MyRow['codename']);
				
				//Lain2
				$Lain2 = $MyRow['penerimaanlain'] +
						$MyRow['potonganlain2'];

				if (($MyRow['codename'] == 'Ricard') OR 
					($MyRow['codename'] == 'Laia')){
					// Dividends paid as lain2 to shareholders for PTADU goes to different GL than karyawan
					MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "COMM-SHAREHOLDERS", $PaymentDate, $Lain2, $MyRow['codename']);
				}else{
					MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "OTHERS", $PaymentDate, $Lain2, $MyRow['codename']);
				}
				
				//JHT
				$JHT = $MyRow['potonganjht'] +
						$MyRow['potonganaskes'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "JAMSOSTEK", $PaymentDate, $JHT, $MyRow['codename']);

				//PPH21
				$PPH21 = $MyRow['potonganpph21'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "PPH21", $PaymentDate, $PPH21, $MyRow['codename']);

				//Rounding
				$Rounding = $MyRow['bulatan'];
				MoveSalaryTxToPC($Company, $MyRow['paymentmethod'], "ROUND", $PaymentDate, $Rounding, $MyRow['codename']);

				echo '<tr class="striped_row">
						<td>'.$MyRow['codename'].'</td>
						<td class="number">'.locale_number_format_zero_blank($FixedSalary,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Makan,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Bensin,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Commissions,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Shifts,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($THR,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Lain2,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($JHT,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($PPH21,0).'</td>
						<td class="number">'.locale_number_format_zero_blank($Rounding,0).'</td>
						</tr>';
			}
			echo '</tbody>
				</table>
				</div>
				</form>';
		}else{
			prnMsg('No data to Move Monthly Salaries Data to Petty Cash ');
		}
	}else{
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
	}
} // End of function submit()

function MoveSalaryTxToPC($Company, $PaymentMethod, $Expense, $PaymentDate, $Amount, $Receipt){
	$PaymentMethod = strtoupper($PaymentMethod);
	if($PaymentMethod != "CASH"){
		$PaymentMethod = "BANK";
	}
	if ($Amount != 0){
		$SQL = "SELECT pctabcode,
						pcexpensecode
				FROM pcsalaries
				WHERE salariescompany = '" . $Company . "'
					AND salariespaymentmethod = '" . $PaymentMethod . "'
					AND salariesexpense = '" . $Expense . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			$MyRow = DB_fetch_array($Result);
			$TabCode = $MyRow['pctabcode'];
			$ExpenseType = $MyRow['pcexpensecode'];
			$InsertErrMsg = __('The SQL to insert Salary Transaction to Petty Cash failed');
			$SQL = "INSERT INTO pcashdetails (tabcode, 
											date, 
											codeexpense, 
											amount, 
											authorized, 
											posted, 
											notes, 
											receipt)
					VALUES ('" . $TabCode . "',
							'" . $PaymentDate . "',
							'" . $ExpenseType . "',
							 " . -$Amount . ",
							 '1000-01-01',
							 0,
							 '',
							 '" . $Receipt . "')";
			DB_query($SQL,$InsertErrMsg,'',true);
		}else{
			prnMsg('ERROR CODE: PERS00001. Can not find the PC info for expense: '. $SQL, 'error');
		}
	}
}

function display($Title) 
{
	// Display form fields. This function is called the first time the page is called.
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset><legend>' . __('Parameters Selection') . '</legend>';
	
	include('includes/KLPersonaliaParameterSelection.php');

 	echo FieldToSelectOneDate('PaymentDate', ConvertSQLDate(EndDateSQLFromPeriodNo($PeriodNow - 1)), __('Payment date'), '', '', '', true, false);
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', $Title);

	echo '</div>
         </form>';

} // End of function display()
