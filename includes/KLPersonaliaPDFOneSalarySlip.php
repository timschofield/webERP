<?php

/////////////////////////////////////////////////////////////////////
//  Adds and prints one PDF page with one salary slip as HTML
/////////////////////////////////////////////////////////////////////

// Add page break only if this is not the first salary slip
if (isset($isFirstSlip) and $isFirstSlip == false) {
    $HTML .= '<div class="page-break"></div>';
}

// Company header
include('includes/KLPersonaliaPDFCompanyHeader.php');

// employee header
$HTML .= '<div class="margin-top-10">';
$HTML .= '<table class="employee-table table-no-border">'; // Added table-no-border class
$HTML .= '<tr><td>Nama Panggilan: ' . $MyRow['codename'] . '</td></tr>';
$HTML .= '<tr><td>Nama Lengkap: ' . $MyRow['fullname'] . '</td></tr>';
$HTML .= '<tr><td>e-mail: ' . $MyRow['email'] . '</td></tr>';
$HTML .= '<tr><td>Posisi: ' . $MyRow['position'] . '</td></tr>';

if ($SalaryType == "MONTHLY") {
    $HTML .= '<tr><td>Slip Gaji Periode: ' . 
        ConvertSQLDate($MyRow['salaryfrom']) . ' - ' . 
        ConvertSQLDate($MyRow['salaryto']) . '</td></tr>';
} else {
    $HTML .= '<tr><td>Slip THR: ' . $PeriodMonth . '</td></tr>';
}
$HTML .= '</table>';
$HTML .= '</div>';

// gaji details
$HTML .= '<div class="margin-top-15">';
$HTML .= '<table class="full-width salary-table">';
$HTML .= '<tr class="table-header">
            <th class="col-label"></th>
            <th class="col-value text-center">Penerimaan</th>
            <th class="col-deduction text-center">Potongan</th>
            <th class="col-notes">Catatan</th>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Upah:</td>
            <td class="text-right">' . locale_number_format($MyRow['upahpokok']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Tunjangan makan:</td>
            <td class="text-right">' . locale_number_format($MyRow['tunjanganmakan']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Tunjangan transport:</td>
            <td class="text-right">' . locale_number_format($MyRow['tunjangantransport']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Tunjangan jabatan:</td>
            <td class="text-right">' . locale_number_format($MyRow['tunjanganjabatan']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Komisi tetap:</td>
            <td class="text-right">' . locale_number_format($MyRow['komisitetap']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right bold-text">Gaji pokok:</td>
            <td class="text-right bold-text">Rp. ' . locale_number_format($GajiPokok) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Tunjangan pengalaman:</td>
            <td class="text-right">' . locale_number_format($MyRow['tunjanganmasakerja']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Tunjangan operasional:</td>
            <td class="text-right">' . locale_number_format($MyRow['tunjangankendaraan']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Komisi Senior:</td>
            <td class="text-right">' . locale_number_format($MyRow['komisiretail']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Komisi Junior/Support:</td>
            <td class="text-right">' . locale_number_format($MyRow['komisisupport']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Bonus:</td>
            <td class="text-right">' . locale_number_format($MyRow['bonuspenjualan']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Lembur:</td>
            <td class="text-right">' . locale_number_format($MyRow['lembur']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">THR:</td>
            <td class="text-right">' . locale_number_format($MyRow['thr']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Penerimaan lain lain:</td>
            <td class="text-right">' . locale_number_format($MyRow['penerimaanlain']) . '</td>
            <td></td>
            <td>' . $MyRow['penerimaanlainnotes'] . '</td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right bold-text">Total Penerimaan:</td>
            <td class="text-right bold-text">Rp. ' . locale_number_format($TotalPenerimaan) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Potongan BPJS TK:</td>
            <td></td>
            <td class="text-right">' . locale_number_format($MyRow['potonganjht']) . '</td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Potongan BPJS Kes:</td>
            <td></td>
            <td class="text-right">' . locale_number_format($MyRow['potonganaskes']) . '</td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Potongan PPh21:</td>
            <td></td>
            <td class="text-right">' . locale_number_format($MyRow['potonganpph21']) . '</td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Potongan Absen:</td>
            <td></td>
            <td class="text-right">' . locale_number_format($MyRow['potonganabsen']) . '</td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Potongan lain lain:</td>
            <td></td>
            <td class="text-right">' . locale_number_format($MyRow['potonganlain2']) . '</td>
            <td>' . $MyRow['potonganlain2notes'] . '</td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right bold-text">Total Potongan:</td>
            <td></td>
            <td class="text-right bold-text">Rp. ' . locale_number_format($TotalPotongan) . '</td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right">Bulatan:</td>
            <td class="text-right">' . locale_number_format($MyRow['bulatan']) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '<tr>
            <td class="text-right bold-text">Total bawa pulang:</td>
            <td class="text-right bold-text">Rp. ' . locale_number_format($TotalBawaPulang) . '</td>
            <td></td>
            <td></td>
        </tr>';

$HTML .= '</table>';
$HTML .= '</div>';

// payment details
$HTML .= '<div class="margin-top-15">';
if (strtoupper($MyRow['paymentmethod']) == 'BANK') {
    $HTML .= 'Pembayaran lewat bank transfer ke bank ' . 
        $MyRow['bankcode'] . 
        ' rekening nomor ' . 
        $MyRow['bankaccount'];
} elseif (strtoupper($MyRow['paymentmethod']) == 'CHECK') {
    $HTML .= 'Pembayaran lewat cek dari Bank Danamon';
} elseif (strtoupper($MyRow['paymentmethod']) == 'CASH') {
    $HTML .= 'Pembayaran Tunai';
}
$HTML .= '</div>';

// footer
$HTML .= '<div class="margin-top-15">';
if ($SalaryType == "MONTHLY") {
    $TextMenerima = 'Saya telah menerima gaji sebesar jumlah tertera di atas pada tanggal: ';
} else {
    $TextMenerima = 'Saya telah menerima THR sebesar jumlah tertera di atas pada tanggal: ';
}
$HTML .= $TextMenerima . $MyRow['paymentday'];
$HTML .= '</div>';

$HTML .= '<div class="signature-space">Tanda tangan: ' . $MyRow['fullname'] . '</div>';

