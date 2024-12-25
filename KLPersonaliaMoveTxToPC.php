<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Move Monthly Salaries Data to Petty Cash');
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['DateOfFile'], $_POST['PaymentDate'], $_POST['SalaryType']);
} else {
	display($Title);
}

include('includes/footer.php');

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, $PaymentDate, $SalaryType) {

	$PaymentDate = FormatDateForSQL($PaymentDate);
	
	//initialise no input errors
	$InputError = FALSE;
	
	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod));
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromSQLDate($LastDateOfPeriod);

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Move Monthly Salary to Petty Cash for '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Move THR Only to Petty Cash for '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodExportDate + 1)){
			$InputErrorMessage = "The month selected to Move Monthly Salaries Data to Petty Cash should be last month";
			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodExportDate)){
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
					AND periodno = '" . $PeriodExportDate . "'
					AND salarytype = '" . $SalaryType . "'
				ORDER BY paymentmethod,
					joiningdate,
					codename";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			echo '<div>';
			echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Name') . '</th>
						<th class="SortedColumn">' . _('Fixed') . '</th>
						<th class="SortedColumn">' . _('Makan') . '</th>
						<th class="SortedColumn">' . _('Bensin') . '</th>
						<th class="SortedColumn">' . _('Commissions') . '</th>
						<th class="SortedColumn">' . _('Shifts') . '</th>
						<th class="SortedColumn">' . _('THR') . '</th>
						<th class="SortedColumn">' . _('Lain2') . '</th>
						<th class="SortedColumn">' . _('JHT ASKES') . '</th>
						<th class="SortedColumn">' . _('PPH21') . '</th>
						<th class="SortedColumn">' . _('Rounding') . '</th>
					</tr>
				</thead>
				<tbody>';
			while ($myrow = DB_fetch_array($result)) {
				// Fixed
				$FixedSalary = $myrow['upahpokok'] +
								$myrow['tunjanganjabatan'] +
								$myrow['tunjanganmasakerja'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "FIXED", $PaymentDate, $FixedSalary, $myrow['codename']);
				
				// Makan
				$Makan = $myrow['tunjanganmakan'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "MAKAN", $PaymentDate, $Makan, $myrow['codename']);

				// Bensin
				$Bensin = $myrow['tunjangantransport'] +
								$myrow['tunjangankendaraan'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "BENSIN", $PaymentDate, $Bensin, $myrow['codename']);
				
				//Commissions
				$Commissions = $myrow['komisitetap'] +
								$myrow['komisiretail'] +
								$myrow['komisisupport'] +
								$myrow['bonuspenjualan'];
//				if (($myrow['codename'] == 'Ricard') OR 
//					($myrow['codename'] == 'Laia')){
//					// Bonus paid as commissions to Ricard and Laia for PTADU goes to different GL than karyawan
//					MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "COMM-SHAREHOLDERS", $PaymentDate, $Commissions, $myrow['codename']);
//				}else{
					MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "COMMISSIONS", $PaymentDate, $Commissions, $myrow['codename']);
//				}
				
				//Shifts
				$Shifts = $myrow['lembur'] +
								$myrow['potonganabsen'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "SHIFTS", $PaymentDate, $Shifts, $myrow['codename']);
				
				//THR
				$THR = $myrow['thr'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "THR", $PaymentDate, $THR, $myrow['codename']);
				
				//Lain2
				$Lain2 = $myrow['penerimaanlain'] +
								$myrow['potonganlain2'];
				if (($myrow['codename'] == 'Ricard') OR 
					($myrow['codename'] == 'Laia')){
					// Dividends paid as lain2 to shareholders for PTADU goes to different GL than karyawan
					MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "COMM-SHAREHOLDERS", $PaymentDate, $Lain2, $myrow['codename']);
				}else{
					MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "OTHERS", $PaymentDate, $Lain2, $myrow['codename']);
				}
				
				//JHT
				$JHT = $myrow['potonganjht'] +
								$myrow['potonganaskes'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "JAMSOSTEK", $PaymentDate, $JHT, $myrow['codename']);

				//PPH21
				$PPH21 = $myrow['potonganpph21'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "PPH21", $PaymentDate, $PPH21, $myrow['codename']);

				//Rounding
				$Rounding = $myrow['bulatan'];
				MoveSalaryTxToPC($Company, $myrow['paymentmethod'], "ROUND", $PaymentDate, $Rounding, $myrow['codename']);

				printf('<tr class="striped_row">
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$myrow['codename'],
						locale_number_format_zero_blank($FixedSalary,0),
						locale_number_format_zero_blank($Makan,0),
						locale_number_format_zero_blank($Bensin,0),
						locale_number_format_zero_blank($Commissions,0),
						locale_number_format_zero_blank($Shifts,0),
						locale_number_format_zero_blank($THR,0),
						locale_number_format_zero_blank($Lain2,0),
						locale_number_format_zero_blank($JHT,0),
						locale_number_format_zero_blank($PPH21,0),
						locale_number_format_zero_blank($Rounding,0)
						);
			}
			echo '</tbody>
				</table>
				</div>
				</form>';
		}else{
			include('includes/header.php');
			prnMsg('No data to Move Monthly Salaries Data to Petty Cash ');
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
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			$myrow = DB_fetch_array($result);
			$TabCode = $myrow['pctabcode'];
			$ExpenseType = $myrow['pcexpensecode'];
			$InsertErrMsg = _('The SQL to insert Salary Transaction to Petty Cash failed');
			$SQL = "INSERT INTO pcashdetails (counterindex, 
											tabcode, 
											date, 
											codeexpense, 
											amount, 
											authorized, 
											posted, 
											notes, 
											receipt)
					VALUES ('',
							'" . $TabCode . "',
							'" . $PaymentDate . "',
							'" . $ExpenseType . "',
							 " . -$Amount . ",
							 0,
							 0,
							 '',
							 '" . $Receipt . "')";
			$resultInsert = DB_query($SQL,$InsertErrMsg,$DbgMsg,true);
		}else{
			prnMsg('ERROR CODE: PERS00001. Can not find the PC info for expense: '. $SQL, 'error');
		}
	}
}


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	
	include('includes/KLPersonaliaParameterSelection.php');

	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodsResult = DB_query("SELECT lastdate_in_period, periodno FROM periods WHERE periodno = '" . ($PeriodNow - 1) . "'");
	$PeriodRow = DB_fetch_row($PeriodsResult);
	$LastDate = $PeriodRow[0];
	
	echo '<tr>
		<td>' . _('Payment date') . ':</td>
		<td><input type="text" size="11" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="PaymentDate" value="' . ConvertSQLDate($LastDate) . '" />';
	echo '</td></tr>';
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';

} // End of function display()

?>