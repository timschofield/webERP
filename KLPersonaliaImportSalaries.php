<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Import Excel with Monthly Salary Information');

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
	</p>';

if (isset($_POST['submit'])) {
    submit($db, $_POST['DateOfFile'], $_POST['SelectedFile']);
} else {
    display($db);
}

include('includes/footer.inc');



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $DateOfFile, $SelectedFile) {

	//initialise no input errors
	$InputError = FALSE;
	$InsertErrMsg = _('The SQL to insert Imported Salary Info failed');
	
	$PeriodDateOfFile = GetPeriod(ConvertSQLDate($DateOfFile), $db);
	$PeriodNow = $PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	if($PeriodNow =! ($PeriodDateOfFile + 1)){
		prnMsg("The month selected should be last month","warn");
	}
	
	if(!$InputError){
		// upload to server and read later on...
		// http://stackoverflow.com/questions/34127361/phpexcel-iofactoryloadtarget-file-is-working-on-localhost-but-not-on-server
		
		
		$ExcelFile = "/home4/kurakura/public_html/bumibiru.com/weberp/" . $_SESSION['reports_dir'] . '/' ."PT Gaji.xlsx";
		$objPHPExcel = PHPExcel_IOFactory::load($ExcelFile);
		
		$ExcelSheetName = "SalaryToPrint";
		$objPHPExcel->setActiveSheetIndexByName($ExcelSheetName);
		$worksheet = $objPHPExcel->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		
		for ($row = 2; $row <= $highestRow; ++ $row) {
			// first check if the row belongs to an active employee or not (old one so don't need to process)
			$Active = $worksheet->getCell('A'.$row)->getCalculatedValue();
			if ($Active === 'YES'){
				// dump the employee info into variables
				$CodeName = $worksheet->getCell('B'.$row)->getCalculatedValue();
				$FullName = $worksheet->getCell('C'.$row)->getCalculatedValue();
				$CompanyCode = $worksheet->getCell('D'.$row)->getCalculatedValue();
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
				$PotonganJHT = $worksheet->getCell('AO'.$row)->getCalculatedValue();
				$PotonganASKES = $worksheet->getCell('AP'.$row)->getCalculatedValue();
				$PotonganPPH21 = $worksheet->getCell('AQ'.$row)->getCalculatedValue();
				$PotonganAbsen = $worksheet->getCell('AR'.$row)->getCalculatedValue();
				$PotonganLain2 = $worksheet->getCell('AS'.$row)->getCalculatedValue();
				$PotonganLain2Notes = $worksheet->getCell('AT'.$row)->getCalculatedValue();
				$Bulatan = $worksheet->getCell('AW'.$row)->getCalculatedValue();

				//Insert into the database
				$sqlInsert = "INSERT INTO salariescalculated
								(periodno,
								codename,
								fullname,
								company,
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
				
				prnMsg($CodeName . " Imported", "success");
			}
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.


	echo '<table class="selection">';

	echo '<tr><td>' . _('Select Month of the Salaries') . '</td>
							<td><select name="DateOfFile">';
							
	$PeriodsResult = DB_query("SELECT lastdate_in_period FROM periods ORDER BY periodno");
	
	while ($PeriodRow = DB_fetch_row($PeriodsResult)){
		echo '<option value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _('PTGaji file:') . '</td><td><input name="SelectedFile" type="file" />
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