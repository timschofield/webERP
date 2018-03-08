<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');

$Title = _('Export PDF Salary Slips');

if (isset($_POST['submit'])) {
	submit($Title, $_POST['Company'], $_POST['DateOfFile'], $_POST['SalaryType'], $db);
} else {
	display($Title, $db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $Company, $LastDateOfPeriod, $SalaryType, &$db) {

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible
	$PeriodExportDate = GetPeriod(ConvertSQLDate($LastDateOfPeriod), $db);
	$Today = date('Y-m-d');
	$PeriodNow = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$PeriodMonth = MonthAndYearFromSQLDate($LastDateOfPeriod);

	if ($SalaryType == "MONTHLY"){
		$PageTitle = _('Export PDF Monthly Salary Slips for '). ConvertSQLDate($LastDateOfPeriod);
	}elseif($SalaryType == "THRONLY"){
		$PageTitle = _('Export PDF THR Only Slips for '). ConvertSQLDate($LastDateOfPeriod);
	}else{
		$InputErrorMessage = "The type of Salary " . $SalaryType . " is not accepted";
		$InputError = TRUE;
	}

	// The month selected should be last month for Monthly salaries
	if ($SalaryType == "MONTHLY"){
		if($PeriodNow != ($PeriodExportDate + 1)){
			$InputErrorMessage = "The month selected to export PDF Monthly Salary Slips should be last month";
//			$InputError = TRUE;
		}
	}
	
	// The month selected should be current month for THR Only salaries
	if ($SalaryType == "THRONLY"){
		if($PeriodNow != ($PeriodExportDate)){
			$InputErrorMessage = "The month selected to export PDF THR Only Salary Slips should be this current month";
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
					codename";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// Let's start the real PDF creation 
			require_once('includes/tcpdf/tcpdf.php');
			
			$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
			if ($SalaryType == "MONTHLY"){
				$CoreFileName = $Company . '-MonthlySalarySlips-' . substr($LastDateOfPeriod,0,7);
			}else{
				$CoreFileName = $Company . '-THROnlySalarySlips-' . substr($LastDateOfPeriod,0,7);
			}
			// set PDF document information
			$pdf->SetCreator('webERP');
			$pdf->SetAuthor('webERP');
			$pdf->SetTitle($CoreFileName);
			$pdf->SetSubject($CoreFileName);
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			
			$FontType = 'helvetica';
			$FontBigSize = 12;
			$FontNormalSize = 10;
			$FontSmallSize = 8;
			
			$EmployeesByBank = 0;
			$AmountByBank = 0;
			$EmployeesByCheck = 0;
			$AmountByCheck = 0;
			$Check = array();
			$EmployeesByCash = 0;
			$AmountByCash = 0;
			$Cash = array();

			while ($myrow = DB_fetch_array($result)) {
				
				$GajiPokok = $myrow['upahpokok'] +
						$myrow['tunjanganmakan'] +
						$myrow['tunjangantransport'] +
						$myrow['tunjanganjabatan'] +
						$myrow['komisitetap'];

				$TotalPenerimaan = $GajiPokok +
								$myrow['tunjanganmasakerja'] +
								$myrow['tunjangankendaraan'] +
								$myrow['komisiretail'] +
								$myrow['komisisupport'] +
								$myrow['bonuspenjualan'] +
								$myrow['lembur'] +
								$myrow['thr'] +
								$myrow['penerimaanlain'];
						
				$TotalPotongan = $myrow['potonganjht'] +
								$myrow['potonganaskes'] +
								$myrow['potonganpph21'] +
								$myrow['potonganabsen'] +
								$myrow['potonganlain2'];
				
				$TotalBawaPulang = $TotalPenerimaan + $TotalPotongan + $myrow['bulatan'];
				// set information depending on payment method
				if ($myrow['paymentmethod'] == 'Bank'){
					$SalaryCopiesToPrint = 2;
					$EmployeesByBank++;
					$AmountByBank += $TotalBawaPulang;
				}elseif ($myrow['paymentmethod'] == 'Check'){
					$SalaryCopiesToPrint = 2;
					$EmployeesByCheck++;
					$Check[$EmployeesByCheck]['Name'] = $myrow['codename'];
					$Check[$EmployeesByCheck]['Amount'] = $TotalBawaPulang;
					$AmountByCheck += $TotalBawaPulang;
				}elseif ($myrow['paymentmethod'] == 'Cash'){
					$SalaryCopiesToPrint = 1;
					$EmployeesByCash++;
					$Cash[$EmployeesByCash]['Name'] = $myrow['codename'];
					$Cash[$EmployeesByCash]['Amount'] = $TotalBawaPulang;
					$AmountByCash += $TotalBawaPulang;
				}
				$CopiesPrinted = 0;
				
				while ($CopiesPrinted < $SalaryCopiesToPrint){
					$pdf->AddPage();
					// https://tcpdf.org/examples/example_005/
					// https://tcpdf.org/docs/source_docs/classTCPDF/#aa81d4b585de305c054760ec983ed3ece
					
					// Company header
					if ($Company == 'PTBB'){
						$pdf->SetFont($FontType, 'B', $FontBigSize);
						$pdf->MultiCell(0, 0, 'PT. Bumi Biru', 0, 'L', 0, 1, '', '', true);
						$pdf->SetFont($FontType, '', $FontSmallSize);
						$pdf->MultiCell(0, 0, 'Jl. Kesambi 1, Kerobokan - Bali - Indonesia', 0, 'L', 0, 1, '', '', true);
						$pdf->MultiCell(0, 0, 'Ph. +62 81 238 167 94', 0, 'L', 0, 1, '', '', true);
					}elseif ($Company == 'PTADU'){
						$pdf->SetFont($FontType, 'B', $FontBigSize);
						$pdf->MultiCell(0, 0, 'PT. Angin Utara Dingin', 0, 'L', 0, 1, '', '', true);
						$pdf->SetFont($FontType, '', $FontSmallSize);
						$pdf->MultiCell(0, 0, 'Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali', 0, 'L', 0, 1, '', '', true);
						$pdf->MultiCell(0, 0, 'Ph. +62 812 381 6795', 0, 'L', 0, 1, '', '', true);
					}				
					
					$pdf->SetFont($FontType, '', $FontNormalSize);
					// employee header
					$WidthColumn1 = 120;
					$WidthColumn2 = 0;
					$pdf->MultiCell($WidthColumn1, 0, 'Nama Panggilan:', 0, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, $myrow['codename'], 0, 'L', 0, 1, '', '', true);
					$pdf->MultiCell($WidthColumn1, 0, 'Nama Lengkap:', 0, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, $myrow['fullname'], 0, 'L', 0, 1, '', '', true);
					$pdf->MultiCell($WidthColumn1, 0, 'Posisi:', 0, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, $myrow['position'], 0, 'L', 0, 1, '', '', true);
					if ($SalaryType == "MONTHLY"){
						$pdf->MultiCell($WidthColumn1, 0, 'Slip Gaji Periode:', 0, 'R', 0, 0, '', '', true);
						$pdf->MultiCell($WidthColumn2, 0, ConvertSQLDate($myrow['salaryfrom']) . ' - ' . 
														ConvertSQLDate($myrow['salaryto']), 0, 'L', 0, 1, '', '', true);
					}else{
						$pdf->MultiCell($WidthColumn1, 0, 'Slip THR:', 0, 'R', 0, 0, '', '', true);
						$pdf->MultiCell($WidthColumn2, 0, $PeriodMonth, 0, 'L', 0, 1, '', '', true);
					}
					
					$pdf->ln(5);
					
					// gaji details
					$WidthColumn1 = 45;
					$WidthColumn2 = 33;
					$WidthColumn3 = 33;
					$WidthColumn4 =  0;
					$pdf->MultiCell($WidthColumn1, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, 'Penerimaan', 1, 'C', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, 'Potongan', 1, 'C', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, 'Catatan', 1, 'C', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Upah:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['upahpokok']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					
					$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan makan:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['tunjanganmakan']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					
					$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan transport:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['tunjangantransport']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					
					$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan jabatan:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['tunjanganjabatan']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Komisi tetap:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['komisitetap']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->SetFont($FontType, 'B', $FontNormalSize);
					$pdf->MultiCell($WidthColumn1, 0, 'Gaji pokok:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, 'Rp. ' . locale_number_format($GajiPokok), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					$pdf->SetFont($FontType, '', $FontNormalSize);

					$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan pengalaman:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['tunjanganmasakerja']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan operasional:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['tunjangankendaraan']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Komisi Senior:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['komisiretail']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Komisi Junior/Support:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['komisisupport']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Bonus:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['bonuspenjualan']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Lembur:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['lembur']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'THR:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['thr']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Penerimaan lain lain:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['penerimaanlain']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, $myrow['penerimaanlainnotes'], 1, 'L', 0, 1, '', '', true);

					$pdf->SetFont($FontType, 'B', $FontNormalSize);
					$pdf->MultiCell($WidthColumn1, 0, 'Total Penerimaan:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, 'Rp. ' . locale_number_format($TotalPenerimaan), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					$pdf->SetFont($FontType, '', $FontNormalSize);
					
					$pdf->MultiCell($WidthColumn1, 0, 'Potongan JHT:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganjht']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Potongan ASKES:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganaskes']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Potongan PPh21:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganpph21']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Potongan Absen:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganabsen']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->MultiCell($WidthColumn1, 0, 'Potongan lain lain:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganlain2']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, $myrow['potonganlain2notes'], 1, 'L', 0, 1, '', '', true);

					$pdf->SetFont($FontType, 'B', $FontNormalSize);
					$pdf->MultiCell($WidthColumn1, 0, 'Total Potongan:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, 'Rp. ' . locale_number_format($TotalPotongan), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					$pdf->SetFont($FontType, '', $FontNormalSize);

					$pdf->MultiCell($WidthColumn1, 0, 'Bulatan:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, locale_number_format($myrow['bulatan']), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

					$pdf->SetFont($FontType, 'B', $FontNormalSize);
					$pdf->MultiCell($WidthColumn1, 0, 'Total bawa pulang:', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn2, 0, 'Rp. ' . locale_number_format($TotalBawaPulang), 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
					$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
					$pdf->SetFont($FontType, '', $FontNormalSize);

					// payment details
					$pdf->ln(5);
					if ($myrow['paymentmethod'] == 'Bank'){
						$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat bank transfer ke bank ' . 
														$myrow['bankcode'] . 
														' rekening nomor ' . 
														$myrow['bankaccount'], 0, 'L', 0, 1, '', '', true);
					}elseif ($myrow['paymentmethod'] == 'Check'){
						$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat cek dari Bank Danamon', 0, 'L', 0, 1, '', '', true);
					}elseif ($myrow['paymentmethod'] == 'Cash'){
						$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran Tunai' , 0, 'L', 0, 1, '', '', true);
					}
					
					// footer
					$pdf->ln(5);
					if ($SalaryType == "MONTHLY"){
						$TextMenerima = 'Saya telah menerima gaji sebesar jumlah tertera di atas pada tanggal: ';
					}else{
						$TextMenerima = 'Saya telah menerima THR sebesar jumlah tertera di atas pada tanggal: ';
					}
					$pdf->MultiCell($WidthColumn4, 0, $TextMenerima . $myrow['paymentday'], 0, 'L', 0, 1, '', '', true);

					$pdf->ln(40);
					$pdf->MultiCell($WidthColumn4, 0, 'Tanda tangan: ' . $myrow['fullname'], 0, 'l', 0, 1, '', '', true);
				
					// update copy counter
					$CopiesPrinted++;
				}
			}
			
			// prepare page with totals
			$pdf->AddPage();
			// Company header
			if ($Company == 'PTBB'){
				$pdf->SetFont($FontType, 'B', $FontBigSize);
				$pdf->MultiCell(0, 0, 'PT. Bumi Biru', 0, 'L', 0, 1, '', '', true);
				$pdf->SetFont($FontType, '', $FontSmallSize);
				$pdf->MultiCell(0, 0, 'Jl. Kesambi 1, Kerobokan - Bali - Indonesia', 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell(0, 0, 'Ph. +62 81 238 167 94', 0, 'L', 0, 1, '', '', true);
			}elseif ($Company == 'PTADU'){
				$pdf->SetFont($FontType, 'B', $FontBigSize);
				$pdf->MultiCell(0, 0, 'PT. Angin Utara Dingin', 0, 'L', 0, 1, '', '', true);
				$pdf->SetFont($FontType, '', $FontSmallSize);
				$pdf->MultiCell(0, 0, 'Jl. Kesambi 1-B, Kerobokan - Bali - Indonesia', 0, 'L', 0, 1, '', '', true);
				$pdf->MultiCell(0, 0, 'Ph. +62', 0, 'L', 0, 1, '', '', true);
			}
			$pdf->SetFont($FontType, '', $FontBigSize);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Salary totals for ' . ConvertSQLDate($LastDateOfPeriod), 0, 'L', 0, 1, '', '', true);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Bank Transfer: ' .	locale_number_format($EmployeesByBank), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Bank Transfer: ' .	locale_number_format($AmountByBank), 0, 'L', 0, 1, '', '', true);
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Check : ' .	locale_number_format($EmployeesByCheck), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Check: ' .	locale_number_format($AmountByCheck), 0, 'L', 0, 1, '', '', true);
			$CheckNumber = 1;
			while($CheckNumber <= $EmployeesByCheck){
				$pdf->MultiCell($WidthColumn1, 0, $Check[$CheckNumber]['Name'], 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, locale_number_format($Check[$CheckNumber]['Amount']), 1, 'R', 0, 0, '', '', true);
				$pdf->ln(5);
				$CheckNumber++;
			}
			$pdf->ln(5);
			$pdf->MultiCell(0, 0, 'Total Employees by Cash: ' .	locale_number_format($EmployeesByCash), 0, 'L', 0, 1, '', '', true);
			$pdf->MultiCell(0, 0, 'Total Amount by Cash: ' .	locale_number_format($AmountByCash), 0, 'L', 0, 1, '', '', true);
			$CashNumber = 1;
			while($CashNumber <= $EmployeesByCash){
				$pdf->MultiCell($WidthColumn1, 0, $Cash[$CashNumber]['Name'], 1, 'R', 0, 0, '', '', true);
				$pdf->MultiCell($WidthColumn2, 0, locale_number_format($Cash[$CashNumber]['Amount']), 1, 'R', 0, 0, '', '', true);
				$pdf->ln(5);
				$CashNumber++;
			}
			
			// download the pdf file
			$FileName= $CoreFileName . '.pdf';
			$pdf->Output($FileName, 'D');
			$pdf->__destruct();
		
		
		}else{
			include('includes/header.php');
			prnMsg('No data to export PDF Monthly Salary Slips ');
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


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
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
	
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>