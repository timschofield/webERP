<?php
require_once ('Classes/PHPExcel.php');
require_once ('Classes/PHPExcel/IOFactory.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Import Excel with Monthly Salary Information');

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['submit'])) {
    submit($db, $_POST['DateOfFile'], $_POST['SelectedFile']);
} else {
    display($db);
}

include('includes/footer.inc');



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $DateOfFile, $SelectedFile) {

	// upload to server and load it...
	// http://stackoverflow.com/questions/38581632/how-to-upload-excel-file-to-php-server-from-input-type-file

	$target_dir =  $_SESSION['reports_dir'] . '/';
	$target_file = $target_dir . basename($_FILES["SelectedFile"]["name"]);
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $target_file);
	$inputFileType = PHPExcel_IOFactory::identify($target_file);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($target_file);
	
	//initialise no input errors
	$InputError = FALSE;
	
	// The date on the excel should be the same as the date selected by the user
	$ExcelSheetName = "General Settings";
	$objPHPExcel->setActiveSheetIndexByName($ExcelSheetName);
	$worksheet = $objPHPExcel->getActiveSheet();
	$ExcelPeriodLastDate = ConvertExcelDate($worksheet->getCell('E10'));
	$MonthOfSalary = $worksheet->getCell('E11')->getCalculatedValue();
	if($ExcelPeriodLastDate != $DateOfFile){
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
			</p>';
		prnMsg("The month selected by the user is not the same as the month of the Excel file","warn");
		$InputError = TRUE;
	}

	// The month selected should be last month
	$PeriodDateOfFile = GetPeriod(ConvertSQLDate($DateOfFile), $db);
	$PeriodNow = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	if($PeriodNow != ($PeriodDateOfFile + 1)){
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
			</p>';
		prnMsg("The month selected by the user and the Excel file should be last month","warn");
		$InputError = TRUE;
	}
	
	if(!$InputError){
		
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . 
					_('Importing Excel with Salary Information for ') . $MonthOfSalary . '" alt="" />' . ' ' . 
					_('Importing Excel with Salary Information for ') . $MonthOfSalary . '
			</p>';

		// let's delete the previous records of that month for test purposes
		$SQL = "DELETE FROM salariescalculated
				WHERE periodno = '" . $PeriodDateOfFile . "'";
		$result = DB_query($SQL);
		
		$ExcelSheetName = "SalaryToPrint";
		$objPHPExcel->setActiveSheetIndexByName($ExcelSheetName);
		$worksheet = $objPHPExcel->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$InsertErrMsg = _('The SQL to insert Imported Salary Info failed');
		
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Code Name') . '</th>
							<th class="ascending">' . _('Position') . '</th>
							<th class="ascending">' . _('Via') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;

		for ($row = 2; $row <= $highestRow; ++ $row) {
			// first check if the row belongs to an active employee or not (old one so don't need to process)
			$Active = $worksheet->getCell('A'.$row)->getCalculatedValue();
			if ($Active === 'YES'){
				// dump the employee info into variables
				$CodeName = $worksheet->getCell('B'.$row)->getCalculatedValue();
				$FullName = $worksheet->getCell('C'.$row)->getCalculatedValue();
				$CompanyCode = $worksheet->getCell('D'.$row)->getCalculatedValue();
				$JoiningDate = ConvertExcelDate($worksheet->getCell('BF'.$row));
				$Position = $worksheet->getCell('E'.$row)->getCalculatedValue();
				$PaymentMethod = $worksheet->getCell('F'.$row)->getCalculatedValue();
				if ($PaymentMethod == "Bank"){
					$BankCode = $worksheet->getCell('G'.$row)->getCalculatedValue();
					$BankAccount = $worksheet->getCell('H'.$row)->getCalculatedValue();
					$BankAccountHolder = $worksheet->getCell('I'.$row)->getCalculatedValue();
				}else{
					$BankCode = "";
					$BankAccount = "";
					$BankAccountHolder = "";
				}
				$ZonePPH21 = $worksheet->getCell('J'.$row)->getCalculatedValue();
				$SalaryFrom = ConvertExcelDate($worksheet->getCell('K'.$row));
				$SalaryTo = ConvertExcelDate($worksheet->getCell('O'.$row));
				$PaymentDate = $worksheet->getCell('BE'.$row)->getCalculatedValue();
				$UpahPokok = $worksheet->getCell('S'.$row)->getCalculatedValue();
				$TunjanganMakan = $worksheet->getCell('T'.$row)->getCalculatedValue();
				$TunjanganTransport = $worksheet->getCell('U'.$row)->getCalculatedValue();
				$TunjanganJabatan = $worksheet->getCell('V'.$row)->getCalculatedValue();
				$TunjanganMasaKerja = $worksheet->getCell('Y'.$row)->getCalculatedValue();
				$TunjanganKendaraan = $worksheet->getCell('Z'.$row)->getCalculatedValue();
				$KomisiTetap = $worksheet->getCell('W'.$row)->getCalculatedValue();
				$KomisiRetail = $worksheet->getCell('AA'.$row)->getCalculatedValue();
				$KomisiSupport = $worksheet->getCell('AB'.$row)->getCalculatedValue();
				$BonusPenjualan = $worksheet->getCell('AC'.$row)->getCalculatedValue();
				$FixedLembur = $worksheet->getCell('AD'.$row)->getCalculatedValue();
				$Lembur = $worksheet->getCell('AJ'.$row)->getCalculatedValue();
				$THR = $worksheet->getCell('AK'.$row)->getCalculatedValue();
				$PenerimaanLain2 = $worksheet->getCell('AL'.$row)->getCalculatedValue();
				$PenerimaanLain2Notes = $worksheet->getCell('AM'.$row)->getCalculatedValue();
				$PotonganJHT = NegativeNumber($worksheet->getCell('AO'.$row)->getCalculatedValue());
				$PotonganASKES = NegativeNumber($worksheet->getCell('AP'.$row)->getCalculatedValue());
				$PotonganPPH21 = NegativeNumber($worksheet->getCell('AQ'.$row)->getCalculatedValue());
				$PotonganAbsen = NegativeNumber($worksheet->getCell('AR'.$row)->getCalculatedValue());
				$PotonganLain2 = NegativeNumber($worksheet->getCell('AS'.$row)->getCalculatedValue());
				$PotonganLain2Notes = $worksheet->getCell('AT'.$row)->getCalculatedValue();
				$Bulatan = $worksheet->getCell('AW'.$row)->getCalculatedValue();

				//Insert into the database
				$sqlInsert = "INSERT INTO salariescalculated
								(periodno,
								codename,
								fullname,
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
								('" . $PeriodDateOfFile . "',
								'" . $CodeName . "',
								'" . $FullName . "',
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
				$resultInsert = DB_query($sqlInsert,$InsertErrMsg,$DbgMsg,true);
				
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i,
						$CodeName,
						$Position,
						$PaymentMethod
						);
				$i++;
			}
		}
		echo '</table>
				</div>
				</form>';

	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
	// Display form fields. This function is called the first time the page is called.
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
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
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Excel file with Gaji Information:') . '</td><td><input type="file"  name="SelectedFile" id="SelectedFile"/></td><td>
			</td></tr>
		</table>';

	echo '<table>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Import File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
		</form>';

} // End of function display()


function ConvertExcelDate($cell, $format = 'Y-m-d'){
	// converts an excel cell into a valid date to work with
	if(PHPExcel_Shared_Date::isDateTime($cell)) {
		$ConvertedDate = date($format,PHPExcel_Shared_Date::ExcelToPHP($cell->getCalculatedValue()));                          
	}else{
		$ConvertedDate = '0000-00-00';                          
	}
	return $ConvertedDate;
}

?>