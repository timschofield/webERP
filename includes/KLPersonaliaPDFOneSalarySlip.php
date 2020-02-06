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
$pdf->MultiCell($WidthColumn2, 0, $myrow['codename'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell($WidthColumn1, 0, 'Nama Lengkap:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $myrow['fullname'], 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell($WidthColumn1, 0, 'e-mail:', 0, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, $myrow['email'], 0, 'L', 0, 1, '', '', true);
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

$pdf->MultiCell($WidthColumn1, 0, 'Potongan BPJS TK:', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn2, 0, '', 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn3, 0, locale_number_format($myrow['potonganjht']), 1, 'R', 0, 0, '', '', true);
$pdf->MultiCell($WidthColumn4, 0, '', 1, 'L', 0, 1, '', '', true);

$pdf->MultiCell($WidthColumn1, 0, 'Potongan BPJS Kes:', 1, 'R', 0, 0, '', '', true);
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
if (strtoupper($myrow['paymentmethod']) == 'BANK'){
	$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat bank transfer ke bank ' . 
									$myrow['bankcode'] . 
									' rekening nomor ' . 
									$myrow['bankaccount'], 0, 'L', 0, 1, '', '', true);
}elseif (strtoupper($myrow['paymentmethod']) == 'CHECK'){
	$pdf->MultiCell($WidthColumn4, 0, 'Pembayaran lewat cek dari Bank Danamon', 0, 'L', 0, 1, '', '', true);
}elseif (strtoupper($myrow['paymentmethod']) == 'CASH'){
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

?>
