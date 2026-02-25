<?php
require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

$Title = __('Import Excel with Monthly Salary Information');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['SalaryType'])) {
	$_POST['SalaryType']='MONTHLY';
}

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (!isset($_POST['SelectedFile'])) {
    $_POST['SelectedFile'] = '';
}

if (isset($_POST['submit'])) {
    submit($_POST['PeriodSelectedByUser'], $_POST['SalaryType'], $RootPath);
} else {
    display();
}

include('includes/footer.php');


function submit($PeriodSelectedByUser, $SalaryType, $RootPath) {

	// upload to server and load it...
	// http://stackoverflow.com/questions/38581632/how-to-upload-excel-file-to-php-server-from-input-type-file

	//initialise no input errors
	$InputError = false;
	
	// Enhanced file upload validation
	if (!isset($_FILES["SelectedFile"]) || $_FILES["SelectedFile"]["error"] !== UPLOAD_ERR_OK) {
		switch ($_FILES["SelectedFile"]["error"]) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				prnMsg(__("File size exceeds the maximum allowed size"), "error");
				break;
			case UPLOAD_ERR_NO_FILE:
				prnMsg(__("No file was selected for upload"), "error");
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				prnMsg(__("Missing temporary folder for file upload"), "error");
				break;
			case UPLOAD_ERR_CANT_WRITE:
				prnMsg(__("Failed to write file to disk"), "error");
				break;
			case UPLOAD_ERR_EXTENSION:
				prnMsg(__("File upload stopped by extension"), "error");
				break;
			default:
				prnMsg(__("Unknown file upload error"), "error");
				break;
		}
		$InputError = true;
		return;
	}

	// Validate file size (max 10MB)
	if ($_FILES["SelectedFile"]["size"] > 10485760) {
		prnMsg(__("File size exceeds 10MB limit"), "error");
		$InputError = true;
		return;
	}

	// Validate file extension
	$AllowedExtensions = ['xlsx', 'xlsm', 'xls', 'ods'];
	$FileExtension = strtolower(pathinfo($_FILES["SelectedFile"]["name"], PATHINFO_EXTENSION));
	if (!in_array($FileExtension, $AllowedExtensions)) {
		prnMsg(__("Invalid file format. Only Excel files (.xlsx, .xls) and OpenOffice Calc files (.ods) are allowed"), "error");
		$InputError = true;
		return;
	}

	try {
		$Target_dir = $_SESSION['reports_dir'] . '/';
		$Target_file = $Target_dir . basename($_FILES["SelectedFile"]["name"]);
		
		// Create directory if it doesn't exist
		if (!is_dir($Target_dir)) {
			mkdir($Target_dir, 0755, true);
		}
		
		if (!move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $Target_file)) {
			throw new Exception("Failed to move uploaded file");
		}
		
		// Enhanced file validation
		validateExcelFile($Target_file);
		logImportActivity("Excel file uploaded and validated: " . basename($Target_file));
		
		// Verify file exists and is readable
		if (!file_exists($Target_file) || !is_readable($Target_file)) {
			throw new Exception("Uploaded file is not accessible");
		}
		
		$inputFileType = IOFactory::identify($Target_file);
		$objReader = IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true); // Read data only for better performance
		$SpreadSheet = $objReader->load($Target_file);
		logImportActivity("Excel file loaded successfully with type: $inputFileType");
		
	} catch (Exception $e) {
		logImportActivity("Error loading Excel file: " . $e->getMessage(), 'ERROR');
		prnMsg(__("Error loading Excel file: ") . $e->getMessage(), "error");
		$InputError = true;
		if (file_exists($Target_file)) {
			unlink($Target_file); // Clean up corrupted file
		}
		return;
	}
	
	// Validate Excel file structure and read date information
	try {
		$ExcelSheetName = "General Settings";
		
		// Check if required sheet exists
		if (!$SpreadSheet->sheetNameExists($ExcelSheetName)) {
			throw new Exception("Required sheet '" . $ExcelSheetName . "' not found in Excel file");
		}
		
		$SpreadSheet->setActiveSheetIndexByName($ExcelSheetName);
		$worksheet = $SpreadSheet->getActiveSheet();
		
		// Validate critical cell exists and has data
		if (!$worksheet->cellExists('E10') || $worksheet->getCell('E10')->getCalculatedValue() === null) {
			throw new Exception("Date cell E10 is empty or missing in General Settings sheet");
		}
		
		$ExcelLastDate = ConvertExcelDate($worksheet->getCell('E10')->getCalculatedValue(), 'Y-m-d');
		
		// Validate date format
		if ($ExcelLastDate === false || !validateDate($ExcelLastDate, 'Y-m-d')) {
			throw new Exception("Invalid date format in cell E10 of General Settings sheet");
		}
		
	} catch (Exception $e) {
			logImportActivity("Error reading Excel file structure: " . $e->getMessage(), 'ERROR');
		$InputError = true;
		if (file_exists($Target_file)) {
			unlink($Target_file);
		}
		return;
	}

	$ExcelPeriod = GetPeriod(ConvertSQLDate($ExcelLastDate));
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	if ($SalaryType == "MONTHLY"){
		$PageTitle = __('Importing Excel with Monthly Salary Information for '). MonthAndYearFromPeriodNo($ExcelPeriod);
	} elseif ($SalaryType == "THRONLY"){
		$PageTitle = __('Importing Excel with THR ONLY Salary Information for '). MonthAndYearFromPeriodNo($ExcelPeriod);
	} else {
		prnMsg("The type of Salary " . $SalaryType . " is not accepted", "warn");
		$InputError = true;
	}
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	if ($ExcelPeriod != $PeriodSelectedByUser){
		prnMsg("The month selected by the user " . MonthAndYearFromPeriodNo($PeriodSelectedByUser) . " is not the same as the month of the Excel file " .  MonthAndYearFromPeriodNo($ExcelPeriod),"warn");
		$InputError = true;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if ($PeriodNow != ($PeriodSelectedByUser + 1)){
			prnMsg("The month selected by the user and the Excel file should be last month","warn");
//			$InputError = true;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if ($PeriodNow != ($PeriodSelectedByUser)){
			prnMsg("The month selected by the user and the Excel file should be this current month","warn");
			$InputError = true;
		}
	}
	
	if (!$InputError){
	
		try {
			// Validate SalaryToPrint sheet exists
			$ExcelSheetName = "SalaryToPrint";
			if (!$SpreadSheet->sheetNameExists($ExcelSheetName)) {
				throw new Exception("Required sheet '" . $ExcelSheetName . "' not found in Excel file");
			}
			
			$SpreadSheet->setActiveSheetIndexByName($ExcelSheetName);
			$worksheet = $SpreadSheet->getActiveSheet();
			
			$highestRow = $worksheet->getHighestRow();
			if ($highestRow < 2) {
				throw new Exception("No data rows found in SalaryToPrint sheet");
			}
			
			logImportActivity("Starting to process $highestRow rows for $SalaryType salary type");
			
			// Begin database transaction for data integrity
			DB_query("BEGIN");
			
			// let's delete the previous records of that month for test purposes
			$SQL = "DELETE FROM salariescalculated
					WHERE periodno = '" . $PeriodSelectedByUser . "'
						AND salarytype = '" . $SalaryType . "'";
			DB_query($SQL);
			logImportActivity("Deleted previous records for period $PeriodSelectedByUser and type $SalaryType");
			
			$InsertErrMsg = __('The SQL to insert Imported Salary Info failed');
			$ProcessedCount = 0;
			$ErrorCount = 0;
			$ValidationErrors = array();
		
			echo '<div>';
			echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Type') . '</th>
						<th class="SortedColumn">' . __('Code Name') . '</th>
						<th class="SortedColumn">' . __('Position') . '</th>
						<th class="SortedColumn">' . __('Via') . '</th>
					</tr>
				</thead>
				<tbody>';
			$i = 1;

			for ($Row = 2; $Row <= $highestRow; ++ $Row) {
				try {
					// Validate row has data
					if ($worksheet->rangeToArray('A'.$Row.':A'.$Row)[0][0] === null) {
						continue; // Skip empty rows
					}
					
					// first check if the row belongs to an active employee or not (old one so don't need to process)
					$Active = strtoupper(trim($worksheet->getCell('A'.$Row)->getCalculatedValue() ?? ''));
					if ($Active === 'YES'){
					// dump the employee info into variables with validation
					$CodeName = trim($worksheet->getCell('B'.$Row)->getCalculatedValue() ?? '');
					$FullName = trim($worksheet->getCell('C'.$Row)->getCalculatedValue() ?? '');
					$CompanyCode = trim($worksheet->getCell('D'.$Row)->getCalculatedValue() ?? '');
					$Position = trim($worksheet->getCell('E'.$Row)->getCalculatedValue() ?? '');
					$Email = trim($worksheet->getCell('BH'.$Row)->getCalculatedValue() ?? '');
					$PaymentMethod = strtoupper(trim($worksheet->getCell('F'.$Row)->getCalculatedValue() ?? ''));
					
					// Validate required fields
					if (empty($CodeName)) {
						throw new Exception("CodeName is required (Row $Row)");
					}
					if (empty($FullName)) {
						throw new Exception("FullName is required (Row $Row)");
					}
					if (empty($Position)) {
						throw new Exception("Position is required (Row $Row)");
					}
					if (empty($PaymentMethod) || !in_array($PaymentMethod, ['BANK', 'CASH', 'CHECK'])) {
						throw new Exception("Invalid payment method '$PaymentMethod' (Row $Row)");
					}
					
					// Validate company code
					if (empty($CompanyCode)) {
						throw new Exception("Company code is required (Row $Row)");
					}
					if (!in_array($CompanyCode, ['PTADU', 'PTSMH', 'PTBB'])) {
						throw new Exception("Invalid company code '$CompanyCode'. Must be PTADU, PTSMH, or PTBB (Row $Row)");
					}
				
					// Validate and convert dates
					$JoiningDateValue = $worksheet->getCell('BF'.$Row)->getCalculatedValue();
					if ($JoiningDateValue === null || $JoiningDateValue === '') {
						throw new Exception("Joining date is required (Row $Row)");
					}
					$JoiningDate = ConvertExcelDate($JoiningDateValue);
					if ($JoiningDate === false) {
						throw new Exception("Invalid joining date format (Row $Row)");
					}
					if ($PaymentMethod == "BANK"){
						$BankCode = trim($worksheet->getCell('G'.$Row)->getCalculatedValue() ?? '');
						$BankAccount = trim($worksheet->getCell('H'.$Row)->getCalculatedValue() ?? '');
						$BankAccountHolder = trim($worksheet->getCell('I'.$Row)->getCalculatedValue() ?? '');
						
						// Validate bank details for bank payments
						if (empty($BankCode)) {
							throw new Exception("Bank code is required for bank payment method (Row $Row)");
						}
						if (empty($BankAccount)) {
							throw new Exception("Bank account is required for bank payment method (Row $Row)");
						}
						if (empty($BankAccountHolder)) {
							throw new Exception("Bank account holder name is required for bank payment method (Row $Row)");
						}
					} else {
						// cash or check payment, so bank details should be empty
						$BankCode = "";
						$BankAccount = "";
						$BankAccountHolder = "";
					}
					$ZonePPH21 = trim($worksheet->getCell('J'.$Row)->getCalculatedValue() ?? '');
					$PaymentDate = trim($worksheet->getCell('BE'.$Row)->getCalculatedValue() ?? '');
					$EmployeeWithTHR = strtoupper(trim($worksheet->getCell('BG'.$Row)->getCalculatedValue() ?? ''));

					// Validate ZonePPH21
					if (empty($ZonePPH21)) {
						throw new Exception("ZonePPH21 is required (Row $Row)");
					}
					
					// Validate and convert salary period dates
					$SalaryFromValue = $worksheet->getCell('K'.$Row)->getCalculatedValue();
					$SalaryToValue = $worksheet->getCell('O'.$Row)->getCalculatedValue();
					
					if ($SalaryFromValue === null || $SalaryFromValue === '') {
						throw new Exception("Salary from date is required (Row $Row)");
					}
					if ($SalaryToValue === null || $SalaryToValue === '') {
						throw new Exception("Salary to date is required (Row $Row)");
					}
					
					$SalaryFrom = ConvertExcelDate($SalaryFromValue);
					$SalaryTo = ConvertExcelDate($SalaryToValue);
					
					if ($SalaryFrom === false) {
						throw new Exception("Invalid salary from date format (Row $Row)");
					}
					if ($SalaryTo === false) {
						throw new Exception("Invalid salary to date format (Row $Row)");
					}
					
					// Validate numeric values with error handling
					$THRValue = $worksheet->getCell('AK'.$Row)->getCalculatedValue();
					$BulatanValue = $worksheet->getCell('AW'.$Row)->getCalculatedValue();
					
					$THR = is_numeric($THRValue) ? (float)$THRValue : 0;
					$Bulatan = is_numeric($BulatanValue) ? (float)$BulatanValue : 0;

					if ($SalaryType == "MONTHLY"){
						// Validate and extract numeric salary components with error handling
						try {
							$UpahPokok = validateNumericCell($worksheet, 'S'.$Row, 'UpahPokok', $Row);
							$TunjanganMakan = validateNumericCell($worksheet, 'T'.$Row, 'TunjanganMakan', $Row);
							$TunjanganTransport = validateNumericCell($worksheet, 'U'.$Row, 'TunjanganTransport', $Row);
							$TunjanganJabatan = validateNumericCell($worksheet, 'V'.$Row, 'TunjanganJabatan', $Row);
							$TunjanganMasaKerja = validateNumericCell($worksheet, 'Y'.$Row, 'TunjanganMasaKerja', $Row);
							$TunjanganKendaraan = validateNumericCell($worksheet, 'Z'.$Row, 'TunjanganKendaraan', $Row);
							$KomisiTetap = validateNumericCell($worksheet, 'W'.$Row, 'KomisiTetap', $Row);
							$KomisiRetail = validateNumericCell($worksheet, 'AA'.$Row, 'KomisiRetail', $Row);
							$KomisiSupport = validateNumericCell($worksheet, 'AB'.$Row, 'KomisiSupport', $Row);
							$BonusPenjualan = validateNumericCell($worksheet, 'AC'.$Row, 'BonusPenjualan', $Row);
							$FixedLembur = validateNumericCell($worksheet, 'AD'.$Row, 'FixedLembur', $Row);
							$Lembur = validateNumericCell($worksheet, 'AJ'.$Row, 'Lembur', $Row);
							$PenerimaanLain2 = validateNumericCell($worksheet, 'AL'.$Row, 'PenerimaanLain2', $Row);
							$PenerimaanLain2Notes = trim($worksheet->getCell('AM'.$Row)->getCalculatedValue() ?? '');
							$PotonganJHT = EnsureNumberIsNegativeNumber(validateNumericCell($worksheet, 'AO'.$Row, 'PotonganJHT', $Row));
							$PotonganASKES = EnsureNumberIsNegativeNumber(validateNumericCell($worksheet, 'AP'.$Row, 'PotonganASKES', $Row));
							$PotonganPPH21 = EnsureNumberIsNegativeNumber(validateNumericCell($worksheet, 'AQ'.$Row, 'PotonganPPH21', $Row));
							$PotonganAbsen = EnsureNumberIsNegativeNumber(validateNumericCell($worksheet, 'AR'.$Row, 'PotonganAbsen', $Row));
							$PotonganLain2 = EnsureNumberIsNegativeNumber(validateNumericCell($worksheet, 'AS'.$Row, 'PotonganLain2', $Row));
							$PotonganLain2Notes = trim($worksheet->getCell('AT'.$Row)->getCalculatedValue() ?? '');
						} catch (Exception $cellError) {
							throw new Exception("Error processing salary components: " . $cellError->getMessage());
						}
					} else {
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
					} else {
						$Bulatan = 0;
					}
					$TotalBawaPulang += $Bulatan;
				
					//Insert into the database if it's a Monthly salary or THR-Only is for employee
					if ((($SalaryType == "MONTHLY") 
						OR (($SalaryType == "THRONLY") AND ($EmployeeWithTHR === "YES")))
						AND ($TotalBawaPulang > 0)){
						
						// Truncate strings to match table column lengths and sanitize input
						$SalaryType = DB_escape_string(substr($SalaryType, 0, 20));
						$CodeName = DB_escape_string(substr($CodeName, 0, 30));
						$FullName = DB_escape_string(substr($FullName, 0, 80));
						$Email = DB_escape_string(substr(filter_var($Email, FILTER_SANITIZE_EMAIL), 0, 50));
						$CompanyCode = DB_escape_string(substr($CompanyCode, 0, 10));
						$Position = DB_escape_string(substr($Position, 0, 50));
						$PaymentMethod = DB_escape_string(substr($PaymentMethod, 0, 10));
						$BankCode = DB_escape_string(substr($BankCode, 0, 11));
						$BankAccount = DB_escape_string(substr($BankAccount, 0, 30));
						$BankAccountHolder = DB_escape_string(substr($BankAccountHolder, 0, 80));
						$ZonePPH21 = DB_escape_string(substr($ZonePPH21, 0, 30));
						$PaymentDate = DB_escape_string(substr($PaymentDate, 0, 30));
						$PenerimaanLain2Notes = DB_escape_string(substr($PenerimaanLain2Notes, 0, 80));
						$PotonganLain2Notes = DB_escape_string(substr($PotonganLain2Notes, 0, 80));
						
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
							$Result = DB_query($SQLInsert,$InsertErrMsg,'',false); // Don't die on error
							if (!$Result) {
								throw new Exception("Database insertion failed for employee: " . $CodeName);
							}

							echo '<tr class="striped_row">
									<td class="number">' . $i . '</td>
									<td>' . htmlspecialchars($SalaryType) . '</td>
									<td>' . htmlspecialchars($CodeName) . '</td>
									<td>' . htmlspecialchars($Position) . '</td>
									<td>' . htmlspecialchars($PaymentMethod) . '</td>
									</tr>';
							$ProcessedCount++;
							$i++;
						}
					}
				} catch (Exception $rowError) {
					$ErrorCount++;
					$ValidationErrors[] = "Row $Row: " . $rowError->getMessage();
					logImportActivity("Validation error in Row $Row: " . $rowError->getMessage(), 'WARNING');
					// Continue processing other rows
				}
			}
			
			// Commit transaction if successful
			if ($ErrorCount == 0 || $ProcessedCount > 0) {
				DB_query("COMMIT");
				logImportActivity("Transaction committed successfully. Processed: $ProcessedCount, Errors: $ErrorCount");
			} else {
				DB_query("ROLLBACK");
				logImportActivity("Transaction rolled back due to critical errors. Processed: $ProcessedCount, Errors: $ErrorCount", 'ERROR');
			}
			
		} catch (Exception $e) {
			// Rollback transaction on error
			DB_query("ROLLBACK");
			logImportActivity("Fatal error during processing: " . $e->getMessage(), 'ERROR');
			prnMsg(__("Error processing Excel file: ") . $e->getMessage(), "error");
			$InputError = true;
			if (file_exists($Target_file)) {
				unlink($Target_file);
			}
			return;
		}
		echo '</tbody>
			</table>
			</div>';
			
		// Display processing summary
		echo '<div class="centre">';
		prnMsg(__("Processing Summary: ") . $ProcessedCount . __(" records processed successfully"), "info");
		if ($ErrorCount > 0) {
			prnMsg(__("Errors encountered: ") . $ErrorCount . __(" rows had validation errors"), "warn");
			echo '<div class="centre"><strong>Validation Errors:</strong><ul>';
			foreach ($ValidationErrors as $error) {
				echo '<li>' . htmlspecialchars($error) . '</li>';
			}
			echo '</ul></div>';
		}
		echo '</div></form>';
		
		// Clean up uploaded file
		if (file_exists($Target_file)) {
			unlink($Target_file);
		}

	}
}


function display(){
	global $RootPath;	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . __('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . __('Import Excel with Monthly Salary Information') . '
		</p>';

	echo '<fieldset>';

	echo FieldToSelectOnePeriod('PeriodSelectedByUser',
								isset($_POST['PeriodSelectedByUser']) ? $_POST['PeriodSelectedByUser'] : GetPeriod(Date($_SESSION['DefaultDateFormat'])) - 1,
								__('Select Month of the Salaries'));

	echo FieldToSelectFromTwoOptions('MONTHLY', __('Monthly Salary'),
									'THRONLY', __('THR Only'),
									'SalaryType', 
									$_POST['SalaryType'],
									__('Type Of Salary'));

	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], __('File Format'));

	echo FieldToSelectOneFile('SelectedFile', __('File with Gaji Information'));
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Import File'));

	echo '</div>
		</form>';

}

// Helper function to validate numeric cells with proper error handling
function validateNumericCell($worksheet, $cellAddress, $fieldName, $rowNumber) {
	try {
		// Check if cell exists first
		if (!$worksheet->cellExists($cellAddress)) {
			return 0; // Default to 0 for missing cells
		}
		
		$cellValue = $worksheet->getCell($cellAddress)->getCalculatedValue();
		
		// Handle null or empty values
		if ($cellValue === null || $cellValue === '') {
			return 0; // Default to 0 for empty numeric fields
		}
		
		// Remove any non-numeric characters except decimal point and minus sign
		$cleanValue = preg_replace('/[^-0-9.]/', '', $cellValue);
		
		// Check if it's a valid number after cleaning
		if (!is_numeric($cleanValue)) {
			error_log("Excel Import Warning: $fieldName contains invalid value '$cellValue' in Row $rowNumber, Cell $cellAddress");
			return 0; // Default to 0 for invalid values
		}
		
		// Validate reasonable range for financial data (prevent extremely large numbers)
		$numericValue = (float)$cleanValue;
		if (abs($numericValue) > 999999999) {
			throw new Exception("$fieldName value exceeds reasonable range (±999,999,999) in Row $rowNumber, Cell $cellAddress");
		}
		
		return $numericValue;
		
	} catch (Exception $e) {
		error_log("Excel Import Error in validateNumericCell: " . $e->getMessage());
		throw new Exception("Error reading $fieldName: " . $e->getMessage());
	}
}

// Helper function to validate date format
function validateDate($date, $format = 'Y-m-d') {
	if (empty($date)) {
		return false;
	}
	try {
		$dateTime = DateTime::createFromFormat($format, $date);
		return $dateTime && $dateTime->format($format) === $date;
	} catch (Exception $e) {
		error_log("Date validation error: " . $e->getMessage());
		return false;
	}
}

// Helper function to log import activity
function logImportActivity($message, $level = 'INFO') {
	$timestamp = date('Y-m-d H:i:s');
	$logMessage = "[$timestamp] [$level] Excel Salary Import: $message" . PHP_EOL;
	error_log($logMessage);
}

// Enhanced file type validation
function validateExcelFile($filePath) {
	if (!file_exists($filePath) || filesize($filePath) === 0) {
		throw new Exception("File does not exist or is empty");
	}
	
	// Check file signature (magic numbers) for better security
	$handle = fopen($filePath, 'rb');
	if (!$handle) {
		throw new Exception("Cannot open file for reading");
	}
	
	$signature = fread($handle, 8);
	fclose($handle);
	
	// Check for Excel file signatures
	$validSignatures = [
		"\x50\x4B", // ZIP format (XLSX, ODS)
		"\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", // Microsoft Compound Document (XLS)
	];
	
	$isValid = false;
	foreach ($validSignatures as $validSig) {
		if (strpos($signature, $validSig) === 0) {
			$isValid = true;
			break;
		}
	}
	
	if (!$isValid) {
		throw new Exception("File is not a valid Excel format");
	}
}
