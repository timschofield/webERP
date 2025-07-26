<?php

include ('includes/session.php');
use Dompdf\Dompdf;
if (isset($_POST['FromDate'])) {
	$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
}
if (isset($_POST['ToDate'])) {
	$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);
}
include ('includes/SQL_CommonFunctions.php');

$InputError = 0;

if (isset($_POST['FromDate']) and !Is_Date($_POST['FromDate'])) {
	$Msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])) {
	$Msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT invoiceno,
			orderdeliverydifferenceslog.orderno,
			orderdeliverydifferenceslog.stockid,
			stockmaster.description,
			stockmaster.decimalplaces,
			quantitydiff,
			trandate,
			orderdeliverydifferenceslog.debtorno,
			orderdeliverydifferenceslog.branch
		FROM orderdeliverydifferenceslog
		INNER JOIN stockmaster
			ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
		INNER JOIN salesorders
			ON orderdeliverydifferenceslog.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		INNER JOIN debtortrans
			ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
			AND debtortrans.type=10
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT invoiceno,
			orderdeliverydifferenceslog.orderno,
			orderdeliverydifferenceslog.stockid,
			stockmaster.description,
			stockmaster.decimalplaces,
			quantitydiff,
			trandate,
			orderdeliverydifferenceslog.debtorno,
			orderdeliverydifferenceslog.branch
		FROM orderdeliverydifferenceslog
		INNER JOIN stockmaster
			ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
		INNER JOIN salesorders
			ON orderdeliverydifferenceslog.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		INNER JOIN debtortrans
			ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
			AND debtortrans.type=10
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND categoryid='" . $_POST['CategoryID'] . "'";

	} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {
		$SQL = "SELECT invoiceno,
			orderdeliverydifferenceslog.orderno,
			orderdeliverydifferenceslog.stockid,
			stockmaster.description,
			stockmaster.decimalplaces,
			quantitydiff,
			trandate,
			orderdeliverydifferenceslog.debtorno,
			orderdeliverydifferenceslog.branch
		FROM orderdeliverydifferenceslog
		INNER JOIN stockmaster
			ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
		INNER JOIN debtortrans
			ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
		INNER JOIN salesorders
			ON orderdeliverydifferenceslog.orderno=salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE debtortrans.type=10
			AND salesorders.fromstkloc='" . $_POST['Location'] . "'
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['location'] != 'All') {

		$SQL = "SELECT invoiceno,
			orderdeliverydifferenceslog.orderno,
			orderdeliverydifferenceslog.stockid,
			stockmaster.description,
			stockmaster.decimalplaces,
			quantitydiff,
			trandate,
			orderdeliverydifferenceslog.debtorno,
			orderdeliverydifferenceslog.branch
		FROM orderdeliverydifferenceslog
		INNER JOIN stockmaster
			ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
		INNER JOIN debtortrans
			ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
			AND debtortrans.type=10
		INNER JOIN salesorders
			ON orderdeliverydifferenceslog.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE salesorders.fromstkloc='" . $_POST['Location'] . "'
			AND categoryid='" . $_POST['CategoryID'] . "'
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";
	}

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$Result = DB_query($SQL, '', '', false, false); //dont error check - see below
	if (DB_error_no() != 0) {
		$Title = _('Delivery Differences Log Report Error');
		include('includes/header.php');
		prnMsg(_('An error occurred getting the variances between deliveries and orders'), 'error');
		if ($Debug == 1) {
			prnMsg(_('The SQL used to get the variances between deliveries and orders that failed was') . '<br />' . $SQL, 'error');
		}
		include('includes/footer.php');
		exit();
	}
	elseif (DB_num_rows($Result) == 0) {
		$Title = _('Delivery Differences Log Report Error');
		include('includes/header.php');
		prnMsg(_('There were no variances between deliveries and orders found in the database within the period from') . ' ' .
			$_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' .
			_('Please try again selecting a different date range'), 'info');
		if ($Debug == 1) {
			prnMsg(_('The SQL that returned no rows was') . '<br />' . $SQL, 'error');
		}
		include('includes/footer.php');
		exit();
	}


	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<img class="logo" src=' . $_SESSION['LogoFile'] . ' /><br />';
	}

	if ($_POST['CategoryID'] != 'All') {
		$Heading = _('For Inventory Category') . ' ' . $_POST['CategoryID'] . ' ' . _('From') . ' ' .
			$_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'];
	} else {
		$Heading = _('From') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'];
	}
	if ($_POST['Location'] != 'All'){
		$Heading = _('Deliveries ex') . ' ' . $_POST['Location'] . ' ' . _('only');
	}

	$HTML .= '<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . _('Variances Between Orders and Deliveries Listing') . '<br />
					' . $Heading . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . _('Invoice') . '</th>
							<th>' . _('Order') . '</th>
							<th>' . _('Item and Description') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('Customer') . '</th>
							<th>' . _('Branch') . '</th>
							<th>' . _('Inv Date') . '</th>
						</tr>
					</thead>
					<tbody>';
	$TotalDiffs = 0;

	while ($MyRow = DB_fetch_array($Result)) {

		$HTML .= '<tr class="striped_row">
					<td>' . $MyRow['invoiceno'] . '</td>
					<td>' . $MyRow['orderno'] . '</td>
					<td>' . $MyRow['stockid'] . ' - ' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['quantitydiff'], $MyRow['decimalplaces']) . '</td>
					<td>' . $MyRow['debtorno'] . '</td>
					<td>' . $MyRow['branch'] . '</td>
					<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				</tr>';

		$TotalDiffs++;

	} /* end of while there are delivery differences to print */

	$HTML .= '<tr class="total_row">
				<th colspan="3" style="text-align:left">' . _('Total number of differences') . ' ' . locale_number_format($TotalDiffs) . '</th>
				<th colspan="4"></th>
			<tr>';

	if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT COUNT(salesorderdetails.orderno)
			FROM salesorderdetails
			INNER JOIN debtortrans
				ON salesorderdetails.orderno=debtortrans.order_
			INNER JOIN salesorders
				ON salesorderdetails.orderno = salesorders.orderno
			INNER JOIN locationusers
				ON locationusers.loccode=salesorders.fromstkloc
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	}
	elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'";

	}
	elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND salesorders.fromstkloc='" . $_POST['Location'] . "'";

	}
	elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN salesorders
		ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		INNER JOIN stockmaster
			ON salesorderdetails.stkcode = stockmaster.stockid
		WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
			AND categoryid='" . $_POST['CategoryID'] . "'
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";

	}

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$ErrMsg = _('Could not retrieve the count of sales order lines in the period under review');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	$HTML .= '<tr class="total_row">
				<th colspan="3" style="text-align:left">' . _('Total number of order lines') . ' ' .
					locale_number_format($MyRow[0]) . '</th>
				<th colspan="4"></th>
			<tr>';

	// Fix potential divide by zero error
	if ($MyRow[0] != 0) {
		$DifotPercentage = (1 - ($TotalDiffs / $MyRow[0])) * 100;
	} else {
		$DifotPercentage = 0;
	}

	$HTML .= '<tr class="total_row">
				<th colspan="3" style="text-align:left">' . _('DIFOT') . ' ' .
					locale_number_format($DifotPercentage, 2) . '%' . '</th>
				<th colspan="4"></th>
			<tr>';

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
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '__DeliveryDifferences__' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Delivery Differences Report');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Receipts') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

	if ($_POST['Email'] == 'Yes') {
		if (file_exists($_SESSION['reports_dir'] . '/' . $ReportFileName)) {
			unlink($_SESSION['reports_dir'] . '/' . $ReportFileName);
		}
		$PDF->Output($_SESSION['reports_dir'] . '/' . $ReportFileName, 'F');

		$From = $_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>';
		$To = $_SESSION['FactoryManagerEmail'];
		$Subject = _('Delivery Differences Report');
		$Body = _('Please find herewith delivery differences report from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'];
		$Attachment = $_SESSION['reports_dir'] . '/' . $ReportFileName;
		SendEmailFromWebERP($From, $To, $Subject, $Body, $Attachment);
	}

}
else {

	$Title = _('Delivery Differences Report');
	$ViewTopic = 'Sales';
	$BookMark = '';
	include ('includes/header.php');

	echo '<div class="centre"><p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . _('Delivery Differences Report') . '</p></div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Report Criteria') , '</legend>
			<field>
				<label for="FromDate">' . _('Enter the date from which variances between orders and deliveries are to be listed') . ':</label>
				<input required="required" autofocus="autofocus" type="date" name="FromDate" maxlength="10" size="11" value="' . Date('Y-m-d', Mktime(0, 0, 0, Date('m') - 1, 0, Date('y'))) . '" />
			</field>';
	echo '<field>
			<label for="ToDate">' . _('Enter the date to which variances between orders and deliveries are to be listed') . ':</label>
			<input required="required" type="date" name="ToDate" maxlength="10" size="11" value="' . Date('Y-m-d') . '" />
		</field>';
	echo '<field>
			<label for="CategoryID">' . _('Inventory Category') . '</label>';

	$SQL = "SELECT categorydescription,
					categoryid
			FROM stockcategory
			WHERE stocktype<>'D'
			AND stocktype<>'L'";

	$Result = DB_query($SQL);

	echo '<select name="CategoryID">
			<option selected="selected" value="All">' . _('Over All Categories') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}

	echo '</select>
		</field>
		<field>
			<label for="Location">' . _('Inventory Location') . ':</label>
			<select name="Location">
				<option selected="selected" value="All">' . _('All Locations') . '</option>';

	$Result = DB_query("SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
				<label for="Email">' . _('Email the report off') . ':</label>
				<select name="Email">
					<option selected="selected" value="No">' . _('No') . '</option>
					<option value="Yes">' . _('Yes') . '</option>
				</select>
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View') . '" />
			</div>
		</form>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}
	include ('includes/footer.php');
}
