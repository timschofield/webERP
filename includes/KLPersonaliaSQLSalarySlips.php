<?php

/////////////////////////////////////////////////////////////////////
//  SQL sentence to extract data for salary slip prints or emails
/////////////////////////////////////////////////////////////////////

$SQL = "SELECT 	codename,
				fullname,
				email,
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
			AND periodno = '" . $PeriodOfFile . "'
			AND salarytype = '" . $SalaryType . "'
		ORDER BY paymentmethod,
			codename";
$Result = DB_query($SQL);

?>