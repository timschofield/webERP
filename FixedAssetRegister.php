<?php

// Produces a csv, html or pdf report of the fixed assets over a period showing period depreciation, additions and disposals.

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['FromDate'])) {
	$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
}
if (isset($_POST['ToDate'])) {
	$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);
}

// Reports being generated in HTML, PDF and CSV/EXCEL format
if (isset($_POST['submit']) or isset($_POST['PrintPDF']) or isset($_POST['Spreadsheet'])) {

	$DisposalSQL = '';
	if ($_POST['DisposalStatus'] == 'ALL') {
		$DisposalSQL .= " AND (fixedassets.disposaldate = '1000-01-01'
								OR fixedassets.disposaldate >='" . $DateFrom . "')";
	}
	elseif ($_POST['DisposalStatus'] == 'ACTIVE') {
		$DisposalSQL .= ' AND disposaldate = "1000-01-01"';
	}
	else {
		$DisposalSQL .= ' AND disposaldate != "1000-01-01"';
	}

	$DateFrom = FormatDateForSQL($_POST['FromDate']);
	$DateTo = FormatDateForSQL($_POST['ToDate']);
	$SQL = "SELECT fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,
					fixedassetlocations.locationdescription,
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassets.assetlocation,
					fixedassets.disposaldate,
					SUM(CASE WHEN (fixedassettrans.transdate <'" . $DateFrom . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costbfwd,
					SUM(CASE WHEN (fixedassettrans.transdate <'" . $DateFrom . "' AND fixedassettrans.fixedassettranstype='depn') THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd,
					SUM(CASE WHEN (fixedassettrans.transdate >='" . $DateFrom . "'  AND fixedassettrans.transdate <='" . $DateTo . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS periodadditions,
					SUM(CASE WHEN fixedassettrans.transdate >='" . $DateFrom . "'  AND fixedassettrans.transdate <='" . $DateTo . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS perioddepn,
					SUM(CASE WHEN fixedassettrans.transdate >='" . $DateFrom . "'  AND fixedassettrans.transdate <='" . $DateTo . "' AND fixedassettrans.fixedassettranstype='disposal' THEN fixedassettrans.amount ELSE 0 END) AS perioddisposal
			FROM fixedassets
			INNER JOIN fixedassetcategories ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
			INNER JOIN fixedassetlocations ON fixedassets.assetlocation=fixedassetlocations.locationid
			INNER JOIN fixedassettrans ON fixedassets.assetid=fixedassettrans.assetid
			WHERE fixedassets.assetcategoryid " . LIKE . "'" . $_POST['AssetCategory'] . "'
			AND fixedassets.assetid " . LIKE . "'" . $_POST['AssetID'] . "'
			AND fixedassets.assetlocation " . LIKE . "'" . $_POST['AssetLocation'] . "'" . $DisposalSQL . "
			GROUP BY fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,
					fixedassetlocations.locationdescription,
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassets.assetlocation";
	$Result = DB_query($SQL);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('From') . ':' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate'] . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<form id="RegisterForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<table>
					<thead>
						<tr>
							<th></th>
						</tr>
						<tr>
							<th>' . __('Asset ID') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Serial Number') . '</th>
							<th>' . __('Location') . '</th>
							<th>' . __('Date Acquired') . '</th>
							<th>' . __('Cost B/fwd') . '</th>
							<th>' . __('Depn B/fwd') . '</th>
							<th>' . __('Additions') . '</th>
							<th>' . __('Depn') . '</th>
							<th>' . __('Cost C/fwd') . '</th>
							<th>' . __('Depn C/fwd') . '</th>
							<th>' . __('NBV') . '</th>
							<th>' . __('Disposal Value') . '</th>
							<th>' . __('Disposal Date') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotalCostBfwd = 0;
	$TotalCostCfwd = 0;
	$TotalDepnBfwd = 0;
	$TotalDepnCfwd = 0;
	$TotalAdditions = 0;
	$TotalDepn = 0;
	$TotalDisposals = 0;
	$TotalNBV = 0;

	while ($MyRow = DB_fetch_array($Result)) {

		if (Date1GreaterThanDate2(ConvertSQLDate($MyRow['disposaldate']) , $_POST['FromDate']) or $MyRow['disposaldate'] = '1000-01-01') {

			if ($MyRow['disposaldate'] != '1000-01-01' and Date1GreaterThanDate2($_POST['ToDate'], ConvertSQLDate($MyRow['disposaldate']))) {
				/*The asset was disposed during the period */
				$CostCfwd = 0;
				$AccumDepnCfwd = 0;
			}
			else {
				$CostCfwd = $MyRow['periodadditions'] + $MyRow['costbfwd'];
				$AccumDepnCfwd = $MyRow['perioddepn'] + $MyRow['depnbfwd'];
			}
			if ($MyRow['disposaldate'] == '1000-01-01') {
				$DisposalDate = "";
			}
			else {
				$DisposalDate = $MyRow['disposaldate'];
			}
			$HTML .= '<tr class="striped_row">
						<td style="vertical-align:top">' . $MyRow['assetid'] . '</td>
						<td style="vertical-align:top">' . $MyRow['longdescription'] . '</td>
						<td style="vertical-align:top">' . $MyRow['serialno'] . '</td>
						<td>' . $MyRow['locationdescription'] . '<br />';

			if ($MyRow['disposaldate'] == '1000-01-01') {
				$DisposalDate = "";
			}
			else {
				$DisposalDate = ConvertSQLDate($MyRow['disposaldate']);
			}
			$HTML .= '</td>
					<td style="vertical-align:top">' . ConvertSQLDate($MyRow['datepurchased']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($MyRow['costbfwd'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($MyRow['depnbfwd'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($MyRow['periodadditions'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($MyRow['perioddepn'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($CostCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($AccumDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($CostCfwd - $AccumDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($MyRow['perioddisposal'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="date">' . $DisposalDate . '</td>
				</tr>';
		} // end of if the asset was either not disposed yet or disposed after the start date
		$TotalCostBfwd += $MyRow['costbfwd'];
		$TotalCostCfwd += ($MyRow['costbfwd'] + $MyRow['periodadditions']);
		$TotalDepnBfwd += $MyRow['depnbfwd'];
		$TotalDepnCfwd += ($MyRow['depnbfwd'] + $MyRow['perioddepn']);
		$TotalAdditions += $MyRow['periodadditions'];
		$TotalDepn += $MyRow['perioddepn'];
		$TotalDisposals += $MyRow['perioddisposal'];

		$TotalNBV += ($CostCfwd - $AccumDepnCfwd);
	}

	//Total Values
	$HTML .= '<tr class="total_row">
				<th style="vertical-align:top" colspan="5">' . __('TOTAL') . '</th>
				<th style="text-align:right">' . locale_number_format($TotalCostBfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalDepnBfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalAdditions, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalDepn, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalCostCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalNBV, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th style="text-align:right">' . locale_number_format($TotalDisposals, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th></th>
			</tr>';
	$HTML .= '</table>';

	$HTML .= '<input type="hidden" name="FromDate" value="' . $_POST['FromDate'] . '" />';
	$HTML .= '<input type="hidden" name="ToDate" value="' . $_POST['ToDate'] . '" />';
	$HTML .= '<input type="hidden" name="AssetCategory" value="' . $_POST['AssetCategory'] . '" />';
	$HTML .= '<input type="hidden" name="AssetID" value="' . $_POST['AssetID'] . '" />';
	$HTML .= '<input type="hidden" name="AssetLocation" value="' . $_POST['AssetLocation'] . '" />';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	}
	else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_FixedAssetRegister_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	}
	elseif (isset($_POST['Spreadsheet'])) {
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

		$File = 'FixedAssetRegister-' . Date('Y-m-d') . '.' . 'ods';

		header('Content-Disposition: attachment;filename="' . $File . '"');
		header('Cache-Control: max-age=0');
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
		$spreadsheet = $reader->loadFromString($HTML);

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
		$writer->save('php://output');
	}
	else {
		$Title = __('Fixed Asset Register');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
}
else {
	$Title = __('Fixed Asset Register');

	$ViewTopic = 'FixedAssets';
	$BookMark = 'AssetRegister';

	include('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	$Result = DB_query('SELECT categoryid,categorydescription FROM fixedassetcategories');
	echo '<form id="RegisterForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria') , '</legend>';
	echo '<field>
			<label for="AssetCategory">' . __('Asset Category') . '</label>
			<select name="AssetCategory">
				<option value="%">' . __('ALL') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['AssetCategory']) and $MyRow['categoryid'] == $_POST['AssetCategory']) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	$SQL = "SELECT  locationid, locationdescription FROM fixedassetlocations";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="AssetLocation">' . __('Asset Location') . '</label>
			<select name="AssetLocation">
				<option value="%">' . __('ALL') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['AssetLocation']) and $MyRow['locationid'] == $_POST['AssetLocation']) {
			echo '<option selected="selected" value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['locationid'] . '">' . $MyRow['locationdescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	$SQL = "SELECT assetid, description FROM fixedassets";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="AssetID">' . __('Asset') . '</label>
			<select name="AssetID">
				<option value="%">' . __('ALL') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['AssetID']) and $MyRow['assetid'] == $_POST['AssetID']) {
			echo '<option selected="selected" value="' . $MyRow['assetid'] . '">' . $MyRow['assetid'] . ' - ' . $MyRow['description'] . '</option>';
		}
		else {
			echo '<option value="' . $MyRow['assetid'] . '">' . $MyRow['assetid'] . ' - ' . $MyRow['description'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['DisposalStatus'])) {
		$_POST['DisposalStatus'] = "ACTIVE";
	}

	echo '<field>
			<label for="DisposalStatus">' . __('Asset Disposal Status') . '</label>
			<select name="DisposalStatus">';

	if ($_POST['DisposalStatus'] == 'ALL') {
		echo '	<option selected="selected" value="ALL">' . __('All') . '</option>
				<option value="ACTIVE">' . __('Active') . '</option>
				<option value="DISPOSED">' . __('Disposed') . '</option>';
	}
	elseif ($_POST['DisposalStatus'] == 'ACTIVE') {
		echo '	<option value="ALL">' . __('All') . '</option>
				<option selected="selected" value="ACTIVE">' . __('Active') . '</option>
				<option value="DISPOSED">' . __('Disposed') . '</option>';
	}
	else {
		echo '	<option value="ALL">' . __('All') . '</option>
				<option value="ACTIVE">' . __('Active') . '</option>
				<option selected="selected" value="DISPOSED">' . __('Disposed') . '</option>';
	}

	echo '	</select>
		</field>';

	if (empty($_POST['FromDate'])) {
		$_POST['FromDate'] = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date('m') , date('d') , date('Y') - 1));
	}
	if (empty($_POST['ToDate'])) {
		$_POST['ToDate'] = date($_SESSION['DefaultDateFormat']);
	}

	echo '<field>
			<label for="FromDate">', __('From Date') , '</label>
			<input type="date" name="FromDate" required="required" title="" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
			<fieldhelp>' . __('Enter the start date to show the cost and accumulated depreciation from') . '</fieldhelp>
		</field>
		<field>
			<label for="ToDate">', __('To Date') , '</label>
			<input type="date" name="ToDate" required="required" title="" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
			<fieldhelp>' . __('Enter the end date to show the cost and accumulated depreciation to') . '</fieldhelp>
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" title="View" value="' . __('Show Assets') . '" />&nbsp;
		<input type="submit" name="PrintPDF" value="' . __('Print as a PDF') . '" />&nbsp;
		<input type="submit" name = "Spreadsheet" title="Spreadsheet" value="' . __('Spreadsheet') . '" />
	</div>
	</form>';

	include('includes/footer.php');
}
