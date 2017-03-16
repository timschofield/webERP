<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLCompanySelection.php');

$Title = _('Move Monthly Salaries Data to Petty Cash');
include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['submit'])) {
	submit($Title, $Company, $_POST['DateOfFile'], $_POST['PaymentDate'], $_POST['SalaryType'], $db);
} else {
	display($Title, $db);
}

include('includes/footer.inc');

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, $PaymentDate, $SalaryType, &$db) {

	$PaymentDate = FormatDateForSQL($PaymentDate);
	
	//initialise no input errors
	$InputError = FALSE;
	
	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod), $db);
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
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
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th class="ascending">' . _('Name') . '</th>
								<th class="ascending">' . _('Fixed') . '</th>
								<th class="ascending">' . _('Makan') . '</th>
								<th class="ascending">' . _('Bensin') . '</th>
								<th class="ascending">' . _('Commissions') . '</th>
								<th class="ascending">' . _('Shifts') . '</th>
								<th class="ascending">' . _('THR') . '</th>
								<th class="ascending">' . _('Lain2') . '</th>
								<th class="ascending">' . _('JHT ASKES') . '</th>
								<th class="ascending">' . _('PPH21') . '</th>
								<th class="ascending">' . _('Rounding') . '</th>
							</tr>';
			echo $TableHeader;
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				// Fixed
				$FixedSalary = $myrow['upahpokok'] +
								$myrow['tunjanganjabatan'] +
								$myrow['tunjanganmasakerja'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-FIXMONTH", "SAL-FIXMONTH-BLACK", $PaymentDate, $FixedSalary, $myrow['codename'], $db);
				
				// Makan
				$Makan = $myrow['tunjanganmakan'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-MAKAN", "SAL-MAKAN-BLACK", $PaymentDate, $Makan, $myrow['codename'], $db);

				// Bensin
				$Bensin = $myrow['tunjangantransport'] +
								$myrow['tunjangankendaraan'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-BENSIN", "SAL-BENSIN-BLACK", $PaymentDate, $Bensin, $myrow['codename'], $db);
				
				//Commissions
				$Commissions = $myrow['komisitetap'] +
								$myrow['komisiretail'] +
								$myrow['komisisupport'] +
								$myrow['bonuspenjualan'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-COMMISSION", "SAL-COMMISSION-BLACK", $PaymentDate, $Commissions, $myrow['codename'], $db);
				
				//Shifts
				$Shifts = $myrow['lembur'] +
								$myrow['potonganabsen'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-SHIFTS", "SAL-SHIFTS-BLACK", $PaymentDate, $Shifts, $myrow['codename'], $db);
				
				//THR
				$THR = $myrow['thr'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-THR", "SAL-THR-BLACK", $PaymentDate, $THR, $myrow['codename'], $db);
				
				//Lain2
				$Lain2 = $myrow['penerimaanlain'] +
								$myrow['potonganlain2'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-LAINLAIN", "SAL-LAINLAIN-BLACK", $PaymentDate, $Lain2, $myrow['codename'], $db);
				
				//JHT
				$JHT = $myrow['potonganjht'] +
								$myrow['potonganaskes'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-DED-JAMSOSTEK", "SAL-LAINLAIN-BLACK", $PaymentDate, $JHT, $myrow['codename'], $db);

				//PPH21
				$PPH21 = $myrow['potonganpph21'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-DED-PPH21", "SAL-LAINLAIN-BLACK", $PaymentDate, $PPH21, $myrow['codename'], $db);

				//Rounding
				$Rounding = $myrow['bulatan'];
				MoveSalaryTxToPC($myrow['paymentmethod'], "SAL-ROUNDING", "SAL-ROUNDING", $PaymentDate, $Rounding, $myrow['codename'], $db);

				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
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
						locale_number_format_zero_blank($FixedSalary),
						locale_number_format_zero_blank($Makan),
						locale_number_format_zero_blank($Bensin),
						locale_number_format_zero_blank($Commissions),
						locale_number_format_zero_blank($Shifts),
						locale_number_format_zero_blank($THR),
						locale_number_format_zero_blank($Lain2),
						locale_number_format_zero_blank($JHT),
						locale_number_format_zero_blank($PPH21),
						locale_number_format_zero_blank($Rounding)
						);
			}
			echo '</table>
				</div>
				</form>';

		}else{
			include('includes/header.inc');
			prnMsg('No data to Move Monthly Salaries Data to Petty Cash ');
			include('includes/footer.inc');
		}
	}else{
		include('includes/header.inc');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.inc');
	}
} // End of function submit()

function MoveSalaryTxToPC($PaymentMethod, $BankExpenseType, $CashExpenseType, $PaymentDate, $Amount, $Receipt, $db){
	if ($Amount != 0){
		if($PaymentMethod == "Cash"){
			$ExpenseType = $CashExpenseType;
			$TabCode = "SALARIES-CASH";
		}else{
			$ExpenseType = $BankExpenseType;
			$TabCode = "SALARIES-BANK";
		}
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
	}
}


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Company" value="' . $_GET['Company'] . '" />';

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
	echo '</select></td></tr>';

	// check the type of salary to import
	if(!isset($_POST['SalaryType'])) {
		$_POST['SalaryType']='MONTHLY';
	}

	echo '<tr>
			<td>' . _('Type Of Salary') . ':</td>
			<td><select name="SalaryType">';
	if($_POST['SalaryType']=="MONTHLY") {
		echo '<option selected="selected" value="MONTHLY">' . _('Monthly Salary') . '</option>';
		echo '<option value="THRONLY">' . _('THR Only') . '</option>';
	} else {
		echo '<option selected="selected" value="THRONLY">' . _('THR Only') . '</option>';
		echo '<option value="MONTHLY">' . _('Monthly Salary') . '</option>';
	}
	echo '</select></td></tr>';	

	echo '<tr>
		<td>' . _('Payment date') . ':</td>
		<td><input type="text" size="11" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="PaymentDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" />';
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