<?php
include('includes/session.php');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = _('Import Excel with Monthly Salary Information');
include('includes/header.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if(!isset($_POST['SalaryType'])) {
	$_POST['SalaryType']='MONTHLY';
}

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (!isset($_POST['SelectedFile'])) {
    $_POST['SelectedFile'] = '';
}

if (isset($_POST['submit'])) {
    submit($_POST['PeriodSelectedByUser'], $_POST['SelectedFile'], $_POST['SalaryType'], $RootPath);
} else {
    display();
}

include('includes/footer.php');


function submit($PeriodSelectedByUser, $SelectedFile, $SalaryType, $RootPath) {

	// upload to server and load it...
	// http://stackoverflow.com/questions/38581632/how-to-upload-excel-file-to-php-server-from-input-type-file

	$Target_dir =  $_SESSION['reports_dir'] . '/';
	$Target_file = $Target_dir . basename($_FILES["SelectedFile"]["name"]);
	$ImageFileType = pathinfo($Target_file,PATHINFO_EXTENSION);
	move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $Target_file);
	$inputFileType = IOFactory::identify($Target_file);
	$objReader = IOFactory::createReader($inputFileType);
	$SpreadSheet = $objReader->load($Target_file);
	
	//initialise no input errors
	$InputError = FALSE;
	
	// The date on the excel should be the same as the date selected by the user
	$ExcelSheetName = "General Settings";
	$SpreadSheet->setActiveSheetIndexByName($ExcelSheetName);
	$worksheet = $SpreadSheet->getActiveSheet();
    $ExcelLastDate = ConvertExcelDate($worksheet->getCell('E10')->getCalculatedValue(), 'Y-m-d');

	$ExcelPeriod = GetPeriod(ConvertSQLDate($ExcelLastDate));
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	$MonthOfSalary = $worksheet->getCell('E11')->getCalculatedValue();

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Importing Excel with Monthly Salary Information for '). MonthAndYearFromPeriodNo($ExcelPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Importing Excel with THR ONLY Salary Information for '). MonthAndYearFromPeriodNo($ExcelPeriod);
	}else{
		prnMsg("The type of Salary " . $SalaryType . " is not accepted", "warn");
		$InputError = TRUE;
	}
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	if($ExcelPeriod != $PeriodSelectedByUser){
		prnMsg("The month selected by the user " . MonthAndYearFromPeriodNo($PeriodSelectedByUser) . " is not the same as the month of the Excel file " .  MonthAndYearFromPeriodNo($ExcelPeriod),"warn");
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodSelectedByUser + 1)){
			prnMsg("The month selected by the user and the Excel file should be last month","warn");
//			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodSelectedByUser)){
			prnMsg("The month selected by the user and the Excel file should be this current month","warn");
			$InputError = TRUE;
		}
	}
	
	if(!$InputError){
	
		// let's delete the previous records of that month for test purposes
		$SQL = "DELETE FROM salariescalculated
				WHERE periodno = '" . $PeriodSelectedByUser . "'
					AND salarytype = '" . $SalaryType . "'";
		$Result = DB_query($SQL);
		
		$ExcelSheetName = "SalaryToPrint";
		$SpreadSheet->setActiveSheetIndexByName($ExcelSheetName);
		$worksheet = $SpreadSheet->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
		$InsertErrMsg = _('The SQL to insert Imported Salary Info failed');
		
		echo '<div>';
		echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('#') . '</th>
					<th class="SortedColumn">' . _('Type') . '</th>
					<th class="SortedColumn">' . _('Code Name') . '</th>
					<th class="SortedColumn">' . _('Position') . '</th>
					<th class="SortedColumn">' . _('Via') . '</th>
				</tr>
			</thead>
			<tbody>';
		$i = 1;

		for ($Row = 2; $Row <= $highestRow; ++ $Row) {
			// first check if the row belongs to an active employee or not (old one so don't need to process)
			$Active = $worksheet->getCell('A'.$Row)->getCalculatedValue();
			if ($Active === 'YES'){
				// dump the employee info into variables
				$CodeName = $worksheet->getCell('B'.$Row)->getCalculatedValue();
				$FullName = $worksheet->getCell('C'.$Row)->getCalculatedValue();
				$CompanyCode = $worksheet->getCell('D'.$Row)->getCalculatedValue();
				$JoiningDate = ConvertExcelDate($worksheet->getCell('BF'.$Row)->getCalculatedValue());
				$Position = $worksheet->getCell('E'.$Row)->getCalculatedValue();
				$Email = $worksheet->getCell('BH'.$Row)->getCalculatedValue();
				$PaymentMethod = strtoupper($worksheet->getCell('F'.$Row)->getCalculatedValue());
				if ($PaymentMethod == "BANK"){
					$BankCode = $worksheet->getCell('G'.$Row)->getCalculatedValue();
					$BankAccount = $worksheet->getCell('H'.$Row)->getCalculatedValue();
					$BankAccountHolder = $worksheet->getCell('I'.$Row)->getCalculatedValue();
				}else{
					$BankCode = "";
					$BankAccount = "";
					$BankAccountHolder = "";
				}
				$ZonePPH21 = $worksheet->getCell('J'.$Row)->getCalculatedValue();
				$SalaryFrom = ConvertExcelDate($worksheet->getCell('K'.$Row)->getCalculatedValue());
				$SalaryTo = ConvertExcelDate($worksheet->getCell('O'.$Row)->getCalculatedValue());
				$PaymentDate = $worksheet->getCell('BE'.$Row)->getCalculatedValue();

				$EmployeeWithTHR = $worksheet->getCell('BG'.$Row)->getCalculatedValue();
				$THR = $worksheet->getCell('AK'.$Row)->getCalculatedValue();
				$Bulatan = $worksheet->getCell('AW'.$Row)->getCalculatedValue();

				if ($SalaryType == "MONTHLY"){
					$UpahPokok = $worksheet->getCell('S'.$Row)->getCalculatedValue();
					$TunjanganMakan = $worksheet->getCell('T'.$Row)->getCalculatedValue();
					$TunjanganTransport = $worksheet->getCell('U'.$Row)->getCalculatedValue();
					$TunjanganJabatan = $worksheet->getCell('V'.$Row)->getCalculatedValue();
					$TunjanganMasaKerja = $worksheet->getCell('Y'.$Row)->getCalculatedValue();
					$TunjanganKendaraan = $worksheet->getCell('Z'.$Row)->getCalculatedValue();
					$KomisiTetap = $worksheet->getCell('W'.$Row)->getCalculatedValue();
					$KomisiRetail = $worksheet->getCell('AA'.$Row)->getCalculatedValue();
					$KomisiSupport = $worksheet->getCell('AB'.$Row)->getCalculatedValue();
					$BonusPenjualan = $worksheet->getCell('AC'.$Row)->getCalculatedValue();
					$FixedLembur = $worksheet->getCell('AD'.$Row)->getCalculatedValue();
					$Lembur = $worksheet->getCell('AJ'.$Row)->getCalculatedValue();
					$PenerimaanLain2 = $worksheet->getCell('AL'.$Row)->getCalculatedValue();
					$PenerimaanLain2Notes = $worksheet->getCell('AM'.$Row)->getCalculatedValue();
					$PotonganJHT = EnsureNumberIsNegativeNumber($worksheet->getCell('AO'.$Row)->getCalculatedValue());
					$PotonganASKES = EnsureNumberIsNegativeNumber($worksheet->getCell('AP'.$Row)->getCalculatedValue());
					$PotonganPPH21 = EnsureNumberIsNegativeNumber($worksheet->getCell('AQ'.$Row)->getCalculatedValue());
					$PotonganAbsen = EnsureNumberIsNegativeNumber($worksheet->getCell('AR'.$Row)->getCalculatedValue());
					$PotonganLain2 = EnsureNumberIsNegativeNumber($worksheet->getCell('AS'.$Row)->getCalculatedValue());
					$PotonganLain2Notes = $worksheet->getCell('AT'.$Row)->getCalculatedValue();
				}else{
					$UpahPokok = 0;
					$TunjanganMakan = 0;
					$TunjanganTransport = 0;
					$TunjanganJabatan = 0;
					$TunjanganMasaKerja = 0;
					$TunjanganKendaraan = 0;
					$KomisiTetap = 0;
					$KomisiRetail = 0;
					$KomisiSupport = 0;
					$BonusPenjualan = 0;
					$FixedLembur = 0;
					$Lembur = 0;
					$PenerimaanLain2 = 0;
					$PenerimaanLain2Notes = '';
					$PotonganJHT = 0;
					$PotonganASKES = 0;
					$PotonganPPH21 = 0;
					$PotonganAbsen = 0;
					$PotonganLain2 = 0;
					$PotonganLain2Notes = '';
				}
				$TotalBawaPulang = $UpahPokok +
								$TunjanganMakan +
								$TunjanganTransport +
								$TunjanganJabatan +
								$KomisiTetap +
								$TunjanganMasaKerja +
								$TunjanganKendaraan +
								$KomisiRetail +
								$KomisiSupport +
								$BonusPenjualan +
								$Lembur +
								$THR +
								$PenerimaanLain2 +
								$PotonganJHT +
								$PotonganASKES +
								$PotonganPPH21 +
								$PotonganAbsen +
								$PotonganLain2;
				
				if ($PaymentMethod == "CASH"){
					$Bulatan = AdjustBulatan($TotalBawaPulang, 500);
				}else{
					$Bulatan = 0;
				}
				$TotalBawaPulang += $Bulatan;
				
				//Insert into the database if it's a Monthly salary or THR-Only is for employee
				if ((($SalaryType == "MONTHLY") 
					OR (($SalaryType == "THRONLY") AND ($EmployeeWithTHR == "YES")))
					AND ($TotalBawaPulang > 0)){
					$SQLInsert = "INSERT INTO salariescalculated
									(periodno,
									salarytype,
									codename,
									fullname,
									email,
									company,
									joiningdate,
									position,
									paymentmethod,
									bankcode,
									bankaccount,
									bankaccountholder,
									zonepph21,
									salaryfrom,
									salaryto,
									paymentday,
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
									fixedlembur,
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
									bulatan)
								VALUES
									('" . $PeriodSelectedByUser . "',
									'" . $SalaryType . "',
									'" . $CodeName . "',
									'" . $FullName . "',
									'" . $Email . "',
									'" . $CompanyCode . "',
									'" . $JoiningDate . "',
									'" . $Position . "',
									'" . $PaymentMethod . "',
									'" . $BankCode . "',
									'" . $BankAccount . "',
									'" . $BankAccountHolder . "',
									'" . $ZonePPH21 . "',
									'" . $SalaryFrom . "',
									'" . $SalaryTo . "',
									'" . $PaymentDate . "',
									'" . $UpahPokok . "',
									'" . $TunjanganMakan . "',
									'" . $TunjanganTransport . "',
									'" . $TunjanganJabatan . "',
									'" . $TunjanganMasaKerja . "',
									'" . $TunjanganKendaraan . "',
									'" . $KomisiTetap . "',
									'" . $KomisiRetail . "',
									'" . $KomisiSupport . "',
									'" . $BonusPenjualan . "',
									'" . $FixedLembur . "',
									'" . $Lembur . "',
									'" . $THR . "',
									'" . $PenerimaanLain2 . "',
									'" . $PenerimaanLain2Notes . "',
									'" . $PotonganJHT . "',
									'" . $PotonganASKES . "',
									'" . $PotonganPPH21 . "',
									'" . $PotonganAbsen . "',
									'" . $PotonganLain2 . "',
									'" . $PotonganLain2Notes . "',
									'" . $Bulatan . "'
									)";
					$ResultInsert = DB_query($SQLInsert,$InsertErrMsg,'',true);
					
					echo '<tr class="striped_row">
							<td class="number">' . $i . '</td>
							<td>' . $SalaryType . '</td>
							<td>' . $CodeName . '</td>
							<td>' . $Position . '</td>
							<td>' . $PaymentMethod . '</td>
							</tr>';
					$i++;
				}
			}
		}
		echo '</tbody>
			</table>
			</div>
			</form>';

	}
} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
	global $RootPath;	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
		</p>';

	echo '<fieldset>';

	echo FieldToSelectOnePeriod('PeriodSelectedByUser',
								isset($_POST['PeriodSelectedByUser']) ? $_POST['PeriodSelectedByUser'] : GetPeriod(Date($_SESSION['DefaultDateFormat'])) - 1,
								_('Select Month of the Salaries'));

	echo FieldToSelectFromTwoOptions('MONTHLY', _('Monthly Salary'),
									'THRONLY', _('THR Only'),
									'SalaryType', 
									$_POST['SalaryType'],
									_('Type Of Salary'));

	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('File Format'));

	echo FieldToSelectOneFile('SelectedFile', _('File with Gaji Information'));
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Import File'));

	echo '</div>
		</form>';

} // End of function display()




?>