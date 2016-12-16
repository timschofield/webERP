<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLCompanySelection.php');

$Title = _('Export CSV File for Transfer LLG Danamon');

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
		prnMsg("The month selected to export CSV File for Transfer LLG Danamon should be last month","warn");
		include('includes/footer.inc');
		$InputError = TRUE;
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
					AND paymentmethod = 'Bank'
				ORDER BY joiningdate,
					fullname";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// prepare CSV file
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=GajiTransferDanamon-" . $Today . ".csv");
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
								
				$Line = $myrow['bankaccount'] . $Separator . 
//						round($ValueTransfer,0) . $Separator . 
						$ValueTransfer . $Separator . 
						substr($Company . ' '. $PeriodMonth,0,30) . $Separator . 
						substr('Gaji' . ' '. $PeriodMonth,0,20) . $Separator . 
						$myrow['bankaccountholder'] . $Separator . 
						"PUSAT" . $Separator . 
						$myrow['bankcode'] . $Separator . 
						"PUSAT" . $Separator . 
						"1" . $Separator . 
						"Y" . $EOL;

				fwrite($output, $Line);
				$i++;
			}
			fclose($output);
		}else{
			include('includes/header.inc');
			prnMsg('No data to export CSV File for Transfer LLG Danamon ');
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