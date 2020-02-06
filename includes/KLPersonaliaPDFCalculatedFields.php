<?php

/////////////////////////////////////////////////////////////////////
//  Calculated fields data for salary slip prints or emails
/////////////////////////////////////////////////////////////////////

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

?>