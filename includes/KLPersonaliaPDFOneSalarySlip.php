<?php

/////////////////////////////////////////////////////////////////////
//  Adds and prints one PDF page with one salary slip
/////////////////////////////////////////////////////////////////////

$pdf->AddPage();
// https://tcpdf.org/examples/example_005/
// https://tcpdf.org/docs/source_docs/classTCPDF/#aa81d4b585de305c054760ec983ed3ece

// Company header
include('includes/KLPersonaliaPDFCompanyHeader.php');

$pdf->SetFont($FontType, '', $FontNormalSize);
// employee header
$WidthColumn1 = 120;
$WidthColumn2 = 0;
$pdf->MultiCell($WidthColumn1, 0, 'Nama Panggilan:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $MyRow['codename'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell($WidthColumn1, 0, 'Nama Lengkap:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $MyRow['fullname'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell($WidthColumn1, 0, 'e-mail:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $MyRow['email'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell($WidthColumn1, 0, 'Posisi:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $MyRow['position'], 0, 'L', 0, 1, '', '', true);
if ($SalaryType == "MONTHLY"){
	$pdf->MultiCell($WidthColumn1, 0, 'Slip Gaji Periode:', 0, 'R', 0, 0, '', '', true);
	$pdf->MultiCell($WidthColumn2, 0, ConvertSQLDate($MyRow['salaryfrom']) . ' - ' . 
									ConvertSQLDate($MyRow['salaryto']), 0, 'L', 0, 1, '', '', true);
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
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['upahpokok']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan makan:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['tunjanganmakan']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan transport:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['tunjangantransport']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan jabatan:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['tunjanganjabatan']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Komisi tetap:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['komisitetap']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->SetFont($FontType, 'B', $FontNormalSize);
$pdf->MultiCell($WidthColumn1, 0, 'Gaji pokok:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, 'Rp. ' . locale_number_format($GajiPokok), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
$pdf->SetFont($FontType, '', $FontNormalSize);

$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan pengalaman:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['tunjanganmasakerja']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Tunjangan operasional:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['tunjangankendaraan']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Komisi Senior:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['komisiretail']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Komisi Junior/Support:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['komisisupport']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Bonus:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['bonuspenjualan']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Lembur:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['lembur']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'THR:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['thr']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Penerimaan lain lain:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['penerimaanlain']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, $MyRow['penerimaanlainnotes'], 1, 'L', 0, 1, '', '', true);

$pdf->SetFont($FontType, 'B', $FontNormalSize);
$pdf->MultiCell($WidthColumn1, 0, 'Total Penerimaan:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, 'Rp. ' . locale_number_format($TotalPenerimaan), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
$pdf->SetFont($FontType, '', $FontNormalSize);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan BPJS TK:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($MyRow['potonganjht']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan BPJS Kes:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($MyRow['potonganaskes']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan PPh21:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($MyRow['potonganpph21']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan Absen:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($MyRow['potonganabsen']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan lain lain:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($MyRow['potonganlain2']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, $MyRow['potonganlain2notes'], 1, 'L', 0, 1, '', '', true);

$pdf->SetFont($FontType, 'B', $FontNormalSize);
$pdf->MultiCell($WidthColumn1, 0, 'Total Potongan:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, 'Rp. ' . locale_number_format($TotalPotongan), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);
$pdf->SetFont($FontType, '', $FontNormalSize);

$pdf->MultiCell($WidthColumn1, 0, 'Bulatan:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, locale_number_format($MyRow['bulatan']), 1, 'R', 0, 0, '', '', true);
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
if (strtoupper($MyRow['paymentmethod']) == 'BANK'){
	$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat bank transfer ke bank ' . 
									$MyRow['bankcode'] . 
									' rekening nomor ' . 
									$MyRow['bankaccount'], 0, 'L', 0, 1, '', '', true);
}elseif (strtoupper($MyRow['paymentmethod']) == 'CHECK'){
	$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat cek dari Bank Danamon', 0, 'L', 0, 1, '', '', true);
}elseif (strtoupper($MyRow['paymentmethod']) == 'CASH'){
	$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran Tunai' , 0, 'L', 0, 1, '', '', true);
}

// footer
$pdf->ln(5);
if ($SalaryType == "MONTHLY"){
	$TextMenerima = 'Saya telah menerima gaji sebesar jumlah tertera di atas pada tanggal: ';
}else{
	$TextMenerima = 'Saya telah menerima THR sebesar jumlah tertera di atas pada tanggal: ';
}
$pdf->MultiCell($WidthColumn4, 0, $TextMenerima . $MyRow['paymentday'], 0, 'L', 0, 1, '', '', true);

$pdf->ln(40);
$pdf->MultiCell($WidthColumn4, 0, 'Tanda tangan: ' . $MyRow['fullname'], 0, 'l', 0, 1, '', '', true);

?>
