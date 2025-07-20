<?php

/////////////////////////////////////////////////////////////////////
//  Calculated fields data for salary slip prints or emails
/////////////////////////////////////////////////////////////////////

$GajiPokok = $MyRow['upahpokok'] +
		$MyRow['tunjanganmakan'] +
		$MyRow['tunjangantransport'] +
		$MyRow['tunjanganjabatan'] +
		$MyRow['komisitetap'];

$TotalPenerimaan = $GajiPokok +
				$MyRow['tunjanganmasakerja'] +
				$MyRow['tunjangankendaraan'] +
				$MyRow['komisiretail'] +
				$MyRow['komisisupport'] +
				$MyRow['bonuspenjualan'] +
				$MyRow['lembur'] +
				$MyRow['thr'] +
				$MyRow['penerimaanlain'];
		
$TotalPotongan = $MyRow['potonganjht'] +
				$MyRow['potonganaskes'] +
				$MyRow['potonganpph21'] +
				$MyRow['potonganabsen'] +
				$MyRow['potonganlain2'];

$TotalBawaPulang = $TotalPenerimaan + $TotalPotongan + $MyRow['bulatan'];
