<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])) {
	$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
}
if (isset($_POST['ToDate'])) {
	$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);
}

$InputError = 0;
if (isset($_POST['FromDate']) and !Is_Date($_POST['FromDate'])) {
	$Msg = __('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}
if (isset($_POST['ToDate']) and !Is_Date($_POST['ToDate'])) {
	$Msg = __('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT salesorders.orderno,
				salesorders.deliverydate,
				salesorderdetails.actualdispatchdate,
				TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
				salesorderdetails.quantity,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.decimalplaces,
				salesorders.debtorno,
				salesorders.branchcode
			FROM salesorderdetails INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
			INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
			INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND (TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate))  >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT salesorders.orderno,
							salesorders.deliverydate,
							salesorderdetails.actualdispatchdate,
							TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
							salesorderdetails.quantity,
							salesorderdetails.stkcode,
							stockmaster.description,
							stockmaster.decimalplaces,
							salesorders.debtorno,
							salesorders.branchcode
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode=stockmaster.stockid
						INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
						INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
						AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
						AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'
						AND (TO_DAYS(salesorderdetails.actualdispatchdate)
							- TO_DAYS(salesorders.deliverydate))  >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

	} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT salesorders.orderno,
							salesorders.deliverydate,
							salesorderdetails.actualdispatchdate,
							TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
							salesorderdetails.quantity,
							salesorderdetails.stkcode,
							stockmaster.description,
							stockmaster.decimalplaces,
							salesorders.debtorno,
							salesorders.branchcode
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode=stockmaster.stockid
						INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
						INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
						AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
						AND salesorders.fromstkloc='" . $_POST['Location'] . "'
						AND (TO_DAYS(salesorderdetails.actualdispatchdate)
								- TO_DAYS(salesorders.deliverydate))  >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT salesorders.orderno,
							salesorders.deliverydate,
							salesorderdetails.actualdispatchdate,
							TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
							salesorderdetails.quantity,
							salesorderdetails.stkcode,
							stockmaster.description,
							stockmaster.decimalplaces,
							salesorders.debtorno,
							salesorders.branchcode
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode=stockmaster.stockid
						INNER JOIN salesorders ON salesorderdetails.orderno=salesorders.orderno
						INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
						AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
						AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'
						AND salesorders.fromstkloc='" . $_POST['Location'] . "'
						AND (TO_DAYS(salesorderdetails.actualdispatchdate)
								- TO_DAYS(salesorders.deliverydate)) >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

	}

	$ErrMsg = __('An error occurred getting the days between delivery requested and actual invoice');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('DIFOT Report Error');
		include('includes/header.php');
		prnMsg(__('There were no variances between deliveries and orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate'] . '. ' . __('Please try again selecting a different date range') , 'info');
		include('includes/footer.php');
		exit();
	}

	if ($_POST['CategoryID']!='All') {
		$Heading = __('For Inventory Category') . ' ' . $_POST['CategoryID'] . ' '. __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate'];
	} else {
		$Heading = __('From') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' .  $_POST['ToDate'];
	}
	if ($_POST['Location']!='All'){
		$Heading .= __('Deliveries ex') . ' '. $_POST['Location'] . ' ' . __('only');
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

	$HTML .= '<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Days Between Requested Delivery Date and Invoice Date') . '<br />
					' . $Heading . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Order') . '</th>
							<th>' . __('Item and Description') . '</th>
							<th>' . __('Quantity') . '</th>
							<th>' . __('Customer') . '</th>
							<th>' . __('Branch') . '</th>
							<th>' . __('Inv Date') . '</th>
							<th>' . __('Days') . '</th>
						</tr>
					</thead>
					<tbody>';

	$TotalDiffs = 0;

	while ($MyRow = DB_fetch_array($Result)) {

		if (DayOfWeekFromSQLDate($MyRow['actualdispatchdate']) == 1) {
			$DaysDiff = $MyRow['daydiff'] - 2;
		} else {
			$DaysDiff = $MyRow['daydiff'];
		}
		if ($DaysDiff > $_POST['DaysAcceptable']) {
			$HTML .= '<tr class="striped_row">
						<td>' . $MyRow['orderno'] . '</td>
						<td>' . $MyRow['stkcode'] . ' - ' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']) . '</td>
						<td>' . $MyRow['debtorno'] . '</td>
						<td>' . $MyRow['branchcode'] . '</td>
						<td>' . ConvertSQLDate($MyRow['actualdispatchdate']) . '</td>
						<td class="number">' . $DaysDiff . '</td>
					</tr>';

			$TotalDiffs++;

		}
	} /* end of while there are delivery differences to print */

	$HTML .= '<tr class="total_row">
				<td colspan="7">' . __('Total number of differences') . ' ' . locale_number_format($TotalDiffs) . '</td>
			</tr>';

	if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT COUNT(salesorderdetails.orderno)
			FROM salesorderdetails INNER JOIN debtortrans
				ON salesorderdetails.orderno=debtortrans.order_ INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_ INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
		AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'";

	} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_ INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
		AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
		AND salesorders.fromstkloc='" . $_POST['Location'] . "'";

	} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] != 'All') {

		$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails INNER JOIN debtortrans ON salesorderdetails.orderno=debtortrans.order_
			INNER JOIN salesorders ON salesorderdetails.orderno = salesorders.orderno
			INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
		WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
		AND categoryid='" . $_POST['CategoryID'] . "'
		AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
		AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";

	}
	$ErrMsg = __('Could not retrieve the count of sales order lines in the period under review');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	$HTML .= '<tr class="total_row">
				<td colspan="7">' . __('Total number of order lines') . ' ' . locale_number_format($MyRow[0]) . '</td>
			</tr>';

	$HTML .= '<tr class="total_row">
				<td colspan="7">' . __('DIFOT') . ' ' . locale_number_format((1 - ($TotalDiffs / $MyRow[0])) * 100, 2) . '%' . '</td>
			</tr>';

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
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '__DIFOT__' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Delivery In Full On Time (DIFOT) Report');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . __('Receipts') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else {

	$Title = __('Delivery In Full On Time (DIFOT) Report');
	$ViewTopic = 'Sales';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . __('DIFOT Report') . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria') , '</legend>
			<field>
				<label for="FromDate">' . __('Enter the date from which variances between orders and deliveries are to be listed') . ':</label>
				<input required="required" autofocus="autofocus" type="date" name="FromDate" maxlength="10" size="11" value="' . Date('Y-m-d', Mktime(0, 0, 0, Date('m') - 1, 0, Date('y'))) . '" />
			</field>
			<field>
				<label for="ToDate">' . __('Enter the date to which variances between orders and deliveries are to be listed') . ':</label>
				<input required="required" type="date" name="ToDate" maxlength="10" size="11" value="' . Date('Y-m-d') . '" />
			</field>';

	if (!isset($_POST['DaysAcceptable'])) {
		$_POST['DaysAcceptable'] = 1;
	}

	echo '<field>
				<label for="DaysAcceptable">' . __('Enter the number of days considered acceptable between delivery requested date and invoice date(ie the date dispatched)') . ':</label>
				<input type="text" class="integer" name="DaysAcceptable" maxlength="2" size="2" value="' . $_POST['DaysAcceptable'] . '" />
			</field>
			<field>
				<label for="CategoryID">' . __('Inventory Category') . '</label>';

	$SQL = "SELECT categorydescription, categoryid FROM stockcategory WHERE stocktype<>'D' AND stocktype<>'L'";
	$Result = DB_query($SQL);

	echo '<select name="CategoryID">';
	echo '<option selected="selected" value="All">' . __('Over All Categories') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="Location">' . __('Inventory Location') . ':</label>
			<select name="Location">
				<option selected="selected" value="All">' . __('All Locations') . '</option>';

	$Result = DB_query("SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Email">' . __('Email the report off') . ':</label>
			<select name="Email">
				<option selected="selected" value="No">' . __('No') . '</option>
				<option value="Yes">' . __('Yes') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . __('View') . '" />
		</div>
	</form>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}
	include('includes/footer.php');
}
