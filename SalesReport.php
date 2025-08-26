<?php

/*
Shows a report of sales to customers for the range of selected dates.
This program is under the GNU General Public License, last version. 2016-12-18.
This creative work is under the CC BY-NC-SA, last version. 2016-12-18.

This script is "mirror-symmetric" to script PurchasesReport.php.
*/

// BEGIN: Functions division ===================================================
// END: Functions division =====================================================

// BEGIN: Procedure division ===================================================
include('includes/session.php');

use Dompdf\Dompdf;

$Title = __('Sales to Customers');
$ViewTopic = 'Sales';
$BookMark = 'SalesReport';

if (isset($_POST['PeriodFrom'])){$_POST['PeriodFrom'] = ConvertSQLDate($_POST['PeriodFrom']);}
if (isset($_POST['PeriodTo'])){$_POST['PeriodTo'] = ConvertSQLDate($_POST['PeriodTo']);}

// Merges gets into posts:
if(isset($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if(isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if(isset($_GET['ShowDetails'])) {
	$_POST['ShowDetails'] = $_GET['ShowDetails'];
}

// Validates the data submitted in the form:
if(isset($_POST['PeriodFrom']) AND isset($_POST['PeriodTo'])) {
	if(Date1GreaterThanDate2($_POST['PeriodFrom'], $_POST['PeriodTo'])) {
		// The beginning is after the end.
		$_POST['NewReport'] = 'on';
		prnMsg(__('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
	}
}

// Main code:
if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	// If PeriodFrom and PeriodTo are set and it is not a NewReport, generates the report:
	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Reorder Level Report') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Date Range') . ' - ' . $_POST['PeriodFrom'] . ' to ' . $_POST['PeriodTo'] . '<br />
				</div>
				<table>
					<thead>
						<tr>';
	// $CommonHead is the common table head between ShowDetails=off and ShowDetails=on:
	$CommonHead ='<th>' . __('Original Overall Amount') . '</th>
				<th>' . __('Original Overall Taxes') . '</th>
				<th>' . __('Original Overall Total') . '</th>
				<th>' . __('GL Overall Amount') . '</th>
				<th>' . __('GL Overall Taxes') . '</th>
				<th>' . __('GL Overall Total') . '</th>
			</tr>';

	$TotalGlAmount = 0;
	$TotalGlTax = 0;
	$PeriodFrom = FormatDateForSQL($_POST['PeriodFrom']);
	$PeriodTo = FormatDateForSQL($_POST['PeriodTo']);
	if (isset($_POST['ShowDetails'])) {// Parameters: PeriodFrom, PeriodTo, ShowDetails=on.
		$HTML .= '<th>' . __('Date') . '</th>
				<th>' . __('Sales Invoice') . '</th>
				<th>' . __('Reference') . '</th>' .
					$CommonHead;
		// Includes $CurrencyName array with currency three-letter alphabetic code and name based on ISO 4217:
		include('includes/CurrenciesArray.php');
		$CustomerId = '';
		$CustomerOvAmount = 0;
		$CustomerOvTax = 0;
		$CustomerGlAmount = 0;
		$CustomerGlTax = 0;
		$SQL = "SELECT
					debtortrans.debtorno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					debtortrans.trandate,
					debtortrans.reference,
					debtortrans.transno,
					debtortrans.ovamount,
					debtortrans.ovgst,
					debtortrans.rate
				FROM debtortrans
					INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				WHERE debtortrans.trandate>='" . $PeriodFrom . "'
					AND debtortrans.trandate<='" . $PeriodTo . "'
					AND debtortrans.type=10
				ORDER BY debtortrans.debtorno, debtortrans.trandate";
		$Result = DB_query($SQL);
		foreach($Result as $MyRow) {
			if($MyRow['debtorno'] != $CustomerId) {// If different, prints customer totals:
				if($CustomerId != '') {// If NOT the first line.
					$HTML .= '<tr>
							<td colspan="3">&nbsp;</td>
							<td class="number">' . locale_number_format($CustomerOvAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($CustomerOvAmount+$CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($CustomerGlAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($CustomerGlAmount+$CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				$HTML .= '<tr><td class="text" colspan="9"><a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $MyRow['debtorno'] . '">' . $MyRow['debtorno'] . ' - ' . $MyRow['name'] . '</a> - ' . $MyRow['currcode'] . ' ' . $CurrencyName[$MyRow['currcode']] . '</td></tr>';
				$TotalGlAmount += $CustomerGlAmount;
				$TotalGlTax += $CustomerGlTax;
				$CustomerId = $MyRow['debtorno'];
				$CustomerOvAmount = 0;
				$CustomerOvTax = 0;
				$CustomerGlAmount = 0;
				$CustomerGlTax = 0;
			}

			$GlAmount = $MyRow['ovamount']/$MyRow['rate'];
			$GlTax = $MyRow['ovgst']/$MyRow['rate'];
			$HTML .= '<tr class="striped_row">
						<td class="centre">' . $MyRow['trandate'] . '</td>
						<td class="number">' . $MyRow['transno'] . '</td>
						<td class="text">' . $MyRow['reference'] . '</td>
						<td class="number">' . locale_number_format($MyRow['ovamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number"><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=10&TransNo=' . $MyRow['transno'] . '&amp;ScriptFrom=SalesReport" target="_blank" title="' . __('Click to view where allocated') . '">' . locale_number_format($MyRow['ovamount']+$MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']) . '</a></td>
						<td class="number">' . locale_number_format($GlAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' .	locale_number_format($GlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number"><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=10&amp;TransNo=' . $MyRow['transno'] . '&amp;ScriptFrom=SalesReport" target="_blank" title="' . __('Click to view the GL entries') . '">' . locale_number_format($GlAmount+$GlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</a></td>
				</tr>';
			$CustomerOvAmount += $MyRow['ovamount'];
			$CustomerOvTax += $MyRow['ovgst'];
			$CustomerGlAmount += $GlAmount;
			$CustomerGlTax += $GlTax;
		}

		// Prints last customer total:
		$HTML .= '<tr class="total_row">
					<td colspan="3">' . __('Total for') . ' ' . $MyRow['name'] . '</td>
					<td class="number">' . locale_number_format($CustomerOvAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CustomerOvAmount+$CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CustomerGlAmount, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CustomerGlAmount+$CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';

		$TotalGlAmount += $CustomerGlAmount;
		$TotalGlTax += $CustomerGlTax;

	} else {// Parameters: PeriodFrom, PeriodTo, ShowDetails=off.
		// RChacon: Needs to update the table_sort function to use in this table.
		$HTML .= '<th>' . __('Customer Code') . '</th>
					<th>' . __('Customer Name') . '</th>
					<th>' . __('Customer\'s Currency') . '</th>' .
					$CommonHead;
		$SQL = "SELECT
					debtortrans.debtorno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					SUM(debtortrans.ovamount) AS CustomerOvAmount,
					SUM(debtortrans.ovgst) AS CustomerOvTax,
					SUM(debtortrans.ovamount/debtortrans.rate) AS CustomerGlAmount,
					SUM(debtortrans.ovgst/debtortrans.rate) AS CustomerGlTax
				FROM debtortrans
					INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				WHERE debtortrans.trandate>='" . $PeriodFrom . "'
					AND debtortrans.trandate<='" . $PeriodTo . "'
					AND debtortrans.type=10
				GROUP BY
					debtortrans.debtorno
				ORDER BY debtortrans.debtorno, debtortrans.trandate";
		$Result = DB_query($SQL);
		foreach($Result as $MyRow) {
			$HTML .= '<tr class="striped_row">
						<td class="text">' . $MyRow['debtorno'] . '</td>
						<td class="text"><a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $MyRow['debtorno'] . '">' . $MyRow['name'] . '</a></td>
						<td class="text">' . $MyRow['currcode'] . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerOvAmount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerOvTax'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerOvAmount']+$MyRow['CustomerOvTax'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerGlAmount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerGlTax'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['CustomerGlAmount']+$MyRow['CustomerGlTax'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					</tr>';
			$TotalGlAmount += $MyRow['CustomerGlAmount'];
			$TotalGlTax += $MyRow['CustomerGlTax'];
		}
	}
	// Prints all debtors total:
	$HTML .= '<tr class="total_row">
				<td class="text" colspan="6">&nbsp;</td>
				<td class="number">' . locale_number_format($TotalGlAmount, $_SESSION['CompanyRecord']['decimalplaces']). '</td>
				<td class="number">' . locale_number_format($TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($TotalGlAmount+$TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
	$HTML .= '</tbody></table>';
	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_SalesReport_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Sales Report');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . __('Sales Report') . '" alt="" />' . ' ' . __('Sales Report') . '
			</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else {
	include('includes/header.php');
	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', // Icon image.
		$Title, '" /> ', // Icon title.
		$Title, '</p>';// Page title.
	fShowPageHelp(// Shows the page help text if $_SESSION['ShowFieldHelp'] is true or is not set
		__('Shows a report of sales to customers for the range of selected dates.'));// Function fShowPageHelp() in ~/includes/MiscFunctions.php
	echo // Shows a form to input the report parameters:
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" target="_blank">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Input table:
		'<fieldset>', // Content of the header and footer of the input table:
		'<legend>', __('Report Parameters'), '</legend>',
	// Content of the body of the input table:
	// Select period from:
			'<field>
				<label for="PeriodFrom">', __('Period from'), '</label>';
	if(!isset($_POST['PeriodFrom'])) {
		$_POST['PeriodFrom'] = date($_SESSION['DefaultDateFormat'], strtotime("-1 year", time()));// One year before current date.
	}
	echo '<input type="date" id="PeriodFrom" maxlength="10" name="PeriodFrom" required="required" size="11" value="', FormatDateForSQL($_POST['PeriodFrom']), '" />',
		'<fieldhelp>', __('Select the beginning of the reporting period'), '</fieldhelp>
			</field>',
			// Select period to:
			'<field>',
				'<label for="PeriodTo">', __('Period to'), '</label>';
	if(!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = date($_SESSION['DefaultDateFormat']);
	}
	echo '<input type="date" id="PeriodTo" maxlength="10" name="PeriodTo" required="required" size="11" value="', FormatDateForSQL($_POST['PeriodTo']), '" />',
		'<fieldhelp>', __('Select the end of the reporting period'), '</fieldhelp>
		</field>',
	// Select to show or not sales invoices:
			'<field>',
			 	'<label for="ShowDetails">', __('Show details'), '</label>',
			 	'<input id="ShowDetails" name="ShowDetails" type="checkbox" />', // If $_POST['ShowDetails'] is set AND it is true, shows this input checked.
				'<fieldhelp>', __('Check this box to show sales invoices'), '</fieldhelp>
			</field>',
		 '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . __('View') . '" />
			</div>';
	include('includes/footer.php');
}
echo '</form>';
// END Procedure division ======================================================
