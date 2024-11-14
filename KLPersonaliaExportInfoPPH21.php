<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');

if (isset($_POST['submit'])) {
	submit($_POST['Company'], $_POST['DateOfFile'], $_POST['SalaryType']);
} else {
	display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Company, $LastDateOfPeriod, $SalaryType) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod));
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Export PPh21 Monthly Info for '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Export PPh21 THR Only Info for '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodExportDate + 1)){
			$InputErrorMessage = "The month selected to export PPh21 Monthly Salary Slips should be last month";
			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodExportDate)){
			$InputErrorMessage = "The month selected to export PPh21 THR Only Salary Slips should be this current month";
			$InputError = TRUE;
		}
	}
	
	if(!$InputError){
		$SQL = "SELECT codename,
						fullname,
						zonepph21,
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
						potonganabsen
				FROM salariescalculated
				WHERE company = '" . $Company . "'
					AND periodno = '" . $PeriodExportDate . "'
					AND salarytype = '" . $SalaryType . "'
					AND UPPER(paymentmethod) != 'CASH'
				ORDER BY zonepph21,
					fullname";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			
			// Set value binder
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
		
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Info Deduction PPH21")
										 ->setSubject("Info Deduction PPH21")
										 ->setDescription("Info Deduction PPH21")
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$PeriodName = MonthAndYearFromSQLDate($LastDateOfPeriod);
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle($PeriodName);

			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Zone PPH21');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Full Name');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Upah Pokok');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Tunjangan Makan');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Tunjangan Transport');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Tunjangan Jabatan');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Tunjangan Masa Kerja');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Tunjangan Kendaraan');
			$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Komisi Tetap');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Komisi Retail');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Komisi Support');
			$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Bonus Penjualan');
			$objPHPExcel->getActiveSheet()->setCellValue('M1', 'Lembur');
			$objPHPExcel->getActiveSheet()->setCellValue('N1', 'THR');
			$objPHPExcel->getActiveSheet()->setCellValue('O1', 'Penerima lain-lain');
			$objPHPExcel->getActiveSheet()->setCellValue('P1', 'Penerima lain-lain Notes');
			$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Potongan JHT');
			$objPHPExcel->getActiveSheet()->setCellValue('R1', 'Potongan ASKES');
			$objPHPExcel->getActiveSheet()->setCellValue('S1', 'Potongan Absen');

			$objPHPExcel->getActiveSheet()->getStyle('C:S')->getNumberFormat()->setFormatCode('#,##0');

			// Add data
			$StartingRow = 2;
			$i = $StartingRow;
			$objPHPExcel->setActiveSheetIndex(0);
			$ActiveSheet = $objPHPExcel->getActiveSheet();
			
			while ($myrow = DB_fetch_array($result)) {

				$ActiveSheet->setCellValue('A'.$i, $myrow['zonepph21']);
				$ActiveSheet->setCellValue('B'.$i, $myrow['fullname']);
				$ActiveSheet->setCellValue('C'.$i, round($myrow['upahpokok'],0));
				$ActiveSheet->setCellValue('D'.$i, round($myrow['tunjanganmakan'],0));
				$ActiveSheet->setCellValue('E'.$i, round($myrow['tunjangantransport'],0));
				$ActiveSheet->setCellValue('F'.$i, round($myrow['tunjanganjabatan'],0));
				$ActiveSheet->setCellValue('G'.$i, round($myrow['tunjanganmasakerja'],0));
				$ActiveSheet->setCellValue('H'.$i, round($myrow['tunjangankendaraan'],0));
				$ActiveSheet->setCellValue('I'.$i, round($myrow['komisitetap'],0));
				$ActiveSheet->setCellValue('J'.$i, round($myrow['komisiretail'],0));
				$ActiveSheet->setCellValue('K'.$i, round($myrow['komisisupport'],0));
				$ActiveSheet->setCellValue('L'.$i, round($myrow['bonuspenjualan'],0));
				$ActiveSheet->setCellValue('M'.$i, round($myrow['lembur'],0));
				$ActiveSheet->setCellValue('N'.$i, round($myrow['thr'],0));
				$ActiveSheet->setCellValue('O'.$i, round($myrow['penerimaanlain'],0));
				$ActiveSheet->setCellValue('P'.$i, $myrow['penerimaanlainnotes']);
				$ActiveSheet->setCellValue('Q'.$i, round($myrow['potonganjht'],0));
				$ActiveSheet->setCellValue('R'.$i, round($myrow['potonganaskes'],0));
				$ActiveSheet->setCellValue('S'.$i, round($myrow['potonganabsen'],0));

				$i++;
			}

			// Freeze panes
			$ActiveSheet->freezePane('A2');

			// Auto Size columns
			foreach(range('A','B') as $columnID) {
				$ActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
			
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			if ($SalaryType == "MONTHLY"){
				$File ='InfoPPH21-' .  $Company . '-' . $LastDateOfPeriod. '.xlsx';
			}else{
				$File ='InfoPPH21-THR-' .  $Company . '-' . $LastDateOfPeriod. '.xlsx';
			}
			header('Content-Disposition: attachment;filename="' . $File . '"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');

		}else{
			$Title = _('Export Info for PPH21 Deduction');
			include('includes/header.php');
			prnMsg('No data to export for PPH21 deduction calculation');
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


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Export Info for PPH21 Deduction');

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

	echo '<table>';
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Export Info for PPH21 Deduction') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>