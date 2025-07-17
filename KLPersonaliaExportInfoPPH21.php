<?php

include('includes/session.php');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$Title = _('Export Info for PPH21 Deduction');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (isset($_POST['submit'])) {
	submit($_POST['Company'], $_POST['PeriodOfFile'], $_POST['SalaryType'], $_POST['Format'], $Title);
} else {
	display($Title);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Company, $PeriodOfFile, $SalaryType, $Format, $Title) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodMonth = MonthAndYearFromPeriodNo($PeriodOfFile);

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Export PPh21 Monthly Info for '). $PeriodMonth;
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Export PPh21 THR Only Info for '). $PeriodMonth;
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodOfFile + 1)){
			$InputErrorMessage = "The month selected to export PPh21 Monthly Salary Slips should be last month";
			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodOfFile)){
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
					AND periodno = '" . $PeriodOfFile . "'
					AND salarytype = '" . $SalaryType . "'
					AND UPPER(paymentmethod) != 'CASH'
				ORDER BY zonepph21,
					fullname";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			
			// Set value binder
			\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
		
			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Info Deduction PPH21")
										 ->setSubject("Info Deduction PPH21")
										 ->setDescription("Info Deduction PPH21")
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setTitle($PeriodMonth);

			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'Zone PPH21');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'Full Name');
			$SpreadSheet->getActiveSheet()->setCellValue('C1', 'Upah Pokok');
			$SpreadSheet->getActiveSheet()->setCellValue('D1', 'Tunjangan Makan');
			$SpreadSheet->getActiveSheet()->setCellValue('E1', 'Tunjangan Transport');
			$SpreadSheet->getActiveSheet()->setCellValue('F1', 'Tunjangan Jabatan');
			$SpreadSheet->getActiveSheet()->setCellValue('G1', 'Tunjangan Masa Kerja');
			$SpreadSheet->getActiveSheet()->setCellValue('H1', 'Tunjangan Kendaraan');
			$SpreadSheet->getActiveSheet()->setCellValue('I1', 'Komisi Tetap');
			$SpreadSheet->getActiveSheet()->setCellValue('J1', 'Komisi Retail');
			$SpreadSheet->getActiveSheet()->setCellValue('K1', 'Komisi Support');
			$SpreadSheet->getActiveSheet()->setCellValue('L1', 'Bonus Penjualan');
			$SpreadSheet->getActiveSheet()->setCellValue('M1', 'Lembur');
			$SpreadSheet->getActiveSheet()->setCellValue('N1', 'THR');
			$SpreadSheet->getActiveSheet()->setCellValue('O1', 'Penerima lain-lain');
			$SpreadSheet->getActiveSheet()->setCellValue('P1', 'Penerima lain-lain Notes');
			$SpreadSheet->getActiveSheet()->setCellValue('Q1', 'Potongan JHT');
			$SpreadSheet->getActiveSheet()->setCellValue('R1', 'Potongan ASKES');
			$SpreadSheet->getActiveSheet()->setCellValue('S1', 'Potongan Absen');

			$SpreadSheet->getActiveSheet()->getStyle('C:S')->getNumberFormat()->setFormatCode('#,##0');

			// Add data
			$StartingRow = 2;
			$i = $StartingRow;
			$SpreadSheet->setActiveSheetIndex(0);
			$ActiveSheet = $SpreadSheet->getActiveSheet();
			
			while ($MyRow = DB_fetch_array($Result)) {

				$ActiveSheet->setCellValue('A'.$i, $MyRow['zonepph21']);
				$ActiveSheet->setCellValue('B'.$i, $MyRow['fullname']);
				$ActiveSheet->setCellValue('C'.$i, round($MyRow['upahpokok'],0));
				$ActiveSheet->setCellValue('D'.$i, round($MyRow['tunjanganmakan'],0));
				$ActiveSheet->setCellValue('E'.$i, round($MyRow['tunjangantransport'],0));
				$ActiveSheet->setCellValue('F'.$i, round($MyRow['tunjanganjabatan'],0));
				$ActiveSheet->setCellValue('G'.$i, round($MyRow['tunjanganmasakerja'],0));
				$ActiveSheet->setCellValue('H'.$i, round($MyRow['tunjangankendaraan'],0));
				$ActiveSheet->setCellValue('I'.$i, round($MyRow['komisitetap'],0));
				$ActiveSheet->setCellValue('J'.$i, round($MyRow['komisiretail'],0));
				$ActiveSheet->setCellValue('K'.$i, round($MyRow['komisisupport'],0));
				$ActiveSheet->setCellValue('L'.$i, round($MyRow['bonuspenjualan'],0));
				$ActiveSheet->setCellValue('M'.$i, round($MyRow['lembur'],0));
				$ActiveSheet->setCellValue('N'.$i, round($MyRow['thr'],0));
				$ActiveSheet->setCellValue('O'.$i, round($MyRow['penerimaanlain'],0));
				$ActiveSheet->setCellValue('P'.$i, $MyRow['penerimaanlainnotes']);
				$ActiveSheet->setCellValue('Q'.$i, round($MyRow['potonganjht'],0));
				$ActiveSheet->setCellValue('R'.$i, round($MyRow['potonganaskes'],0));
				$ActiveSheet->setCellValue('S'.$i, round($MyRow['potonganabsen'],0));

				$i++;
			}

			// Freeze panes
			$ActiveSheet->freezePane('A2');

			// Auto Size columns
			foreach(range('A','B') as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
			
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser
			if ($Format == 'xlsx') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if ($SalaryType == "MONTHLY"){
                    $File ='InfoPPH21-' .  $Company . '-' . $PeriodMonth. '.xlsx';
                }else{
                    $File ='InfoPPH21-THR-' .  $Company . '-' . $PeriodMonth. '.xlsx';
                }
            } else {
                header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
                if ($SalaryType == "MONTHLY"){
                    $File ='InfoPPH21-' .  $Company . '-' . $PeriodMonth. '.ods';
                }else{
                    $File ='InfoPPH21-THR-' .  $Company . '-' . $PeriodMonth. '.ods';
                }
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

            if ($Format == 'xlsx') {
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($SpreadSheet);
        	} else if ($Format == 'ods') {
                $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
            }
			$objWriter->save('php://output');

		}else{
			include('includes/header.php');
			prnMsg('No data to export for PPH21 deduction calculation');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' .  $_SESSION['Theme'] . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
		<legend>' . _('PPH21 Export Parameters') . '</legend>';

	include ('includes/KLPersonaliaParameterSelection.php');

    echo FieldToSelectSpreadSheetFormat('Format', 
                                    isset($_POST['Format']) ? $_POST['Format'] : 'xlsx',
                                    _('File Format'));

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export File for PPH21 Deduction'));

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()
