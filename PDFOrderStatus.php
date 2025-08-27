<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {
	if ($_POST['CategoryID'] == 'All' AND $_POST['Location'] == 'All') {
		$SQL = "SELECT salesorders.orderno,
				  salesorders.debtorno,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.orddate,
				  salesorders.fromstkloc,
				  salesorders.printedpackingslip,
				  salesorders.datepackingslipprinted,
				  salesorderdetails.stkcode,
				  stockmaster.description,
				  stockmaster.units,
				  stockmaster.decimalplaces,
				  salesorderdetails.quantity,
				  salesorderdetails.qtyinvoiced,
				  salesorderdetails.completed,
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname
			 FROM salesorders
				 INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
				 INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
				 INNER JOIN debtorsmaster
				 ON salesorders.debtorno=debtorsmaster.debtorno
				 INNER JOIN custbranch
				 ON custbranch.debtorno=salesorders.debtorno
				 AND custbranch.branchcode=salesorders.branchcode
				 INNER JOIN locations
				 ON salesorders.fromstkloc=locations.loccode
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			 AND salesorders.quotation=0";

	} elseif ($_POST['CategoryID'] != 'All' AND $_POST['Location'] == 'All') {
		$SQL = "SELECT salesorders.orderno,
				  salesorders.debtorno,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.orddate,
				  salesorders.fromstkloc,
				  salesorders.printedpackingslip,
				  salesorders.datepackingslipprinted,
				  salesorderdetails.stkcode,
				  stockmaster.description,
				  stockmaster.units,
				  stockmaster.decimalplaces,
				  salesorderdetails.quantity,
				  salesorderdetails.qtyinvoiced,
				  salesorderdetails.completed,
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname
			 FROM salesorders
				 INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
				 INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
				 INNER JOIN debtorsmaster
				 ON salesorders.debtorno=debtorsmaster.debtorno
				 INNER JOIN custbranch
				 ON custbranch.debtorno=salesorders.debtorno
				 AND custbranch.branchcode=salesorders.branchcode
				 INNER JOIN locations
				 ON salesorders.fromstkloc=locations.loccode
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
				  AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			 AND salesorders.quotation=0";

	} elseif ($_POST['CategoryID'] == 'All' AND $_POST['Location'] != 'All') {
		$SQL = "SELECT salesorders.orderno,
				  salesorders.debtorno,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.orddate,
				  salesorders.fromstkloc,
				  salesorders.printedpackingslip,
				  salesorders.datepackingslipprinted,
				  salesorderdetails.stkcode,
				  stockmaster.description,
				  stockmaster.units,
				  stockmaster.decimalplaces,
				  salesorderdetails.quantity,
				  salesorderdetails.qtyinvoiced,
				  salesorderdetails.completed,
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname
			 FROM salesorders
				 INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
				 INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
				 INNER JOIN debtorsmaster
				 ON salesorders.debtorno=debtorsmaster.debtorno
				 INNER JOIN custbranch
				 ON custbranch.debtorno=salesorders.debtorno
				 AND custbranch.branchcode=salesorders.branchcode
				 INNER JOIN locations
				 ON salesorders.fromstkloc=locations.loccode
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
				  AND salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			 AND salesorders.quotation=0";

	} elseif ($_POST['CategoryID'] != 'All' AND $_POST['location'] != 'All') {

		$SQL = "SELECT salesorders.orderno,
				  salesorders.debtorno,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.orddate,
				  salesorders.fromstkloc,
				  salesorders.printedpackingslip,
				  salesorders.datepackingslipprinted,
				  salesorderdetails.stkcode,
				  stockmaster.description,
				  stockmaster.units,
				  stockmaster.decimalplaces,
				  salesorderdetails.quantity,
				  salesorderdetails.qtyinvoiced,
				  salesorderdetails.completed,
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname
			 FROM salesorders
				 INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
				 INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
				 INNER JOIN debtorsmaster
				 ON salesorders.debtorno=debtorsmaster.debtorno
				 INNER JOIN custbranch
				 ON custbranch.debtorno=salesorders.debtorno
				 AND custbranch.branchcode=salesorders.branchcode
				 INNER JOIN locations
				 ON salesorders.fromstkloc=locations.loccode
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
				  AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				  AND salesorders.orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND salesorders.orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			 AND salesorders.quotation=0";
	}

	if ($_POST['BackOrders'] == 'Yes') {
		$SQL .= " AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced >0";
	}
	//Add salesman role control
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$SQL .= " ORDER BY salesorders.orderno";

	$ErrMsg = __('An error occurred getting the orders details');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)==0){
		$Title=__('Order Status Report - No Data');
		include('includes/header.php');
		prnMsg(__('There were no orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' '. $_POST['ToDate'] . '. ' . __('Please try again selecting a different date range'),'info');
		include('includes/footer.php');
		exit();
	}

	$OrderNo =0; /*initialise */

	if ($_POST['CategoryID'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'";
		$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['CategoryID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$CategoryDescription = $MyRow[1];
	} else {
		$WhereCategory = "";
		$CategoryDescription = __('All');
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
				' . __('Order Status Listing') . '<br />
				' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				' . __('Category') . ' - ' . $_POST['CategoryID'] . ' - ' . $CategoryDescription . '<br />
				' . __('Location') . ' - ' . $_POST['Location'] . '<br />
			</div>';

	$HTML .= '<table>
				<tbody>';
	while ($MyRow=DB_fetch_array($Result)){

		if ($MyRow['orderno']!=$OrderNo	){
			$HTML .= '<tr>
						<th>' . __('Order') . '</th>
						<th>' . __('Customer') . '</th>
						<th>' . __('Branch') . '</th>
						<th>' . __('Ord Date') . '</th>
						<th>' . __('Location') . '</th>
						<th>' . __('Status') . '</th>
					</tr>';

			$HTML .= '<tr>
						<td>' . $MyRow['orderno'] . '</td>
						<td>' . $MyRow['name'] . '</td>
						<td>' . $MyRow['brname'] . '</td>
						<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
						<td>' . $MyRow['locationname'] . '</td>';

			if ($MyRow['printedpackingslip']==1){
				$PackingSlipPrinted = __('Printed') . ' ' . ConvertSQLDate($MyRow['datepackingslipprinted']);
			} else {
				$PackingSlipPrinted =__('Not yet printed');
			}

			$HTML .= '<td>' . $PackingSlipPrinted . '</td>
					</tr>';
			/*Its not the first line */
			$OrderNo = $MyRow['orderno'];
			$HTML .= '<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Ordered') . '</th>
						<th>' . __('Invoiced') . '</th>
						<th>' . __('Outstanding') . '</th>
					</tr>';

		}
		$HTML .= '<tr>
					<td>' . $MyRow['stkcode'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['qtyinvoiced'],$MyRow['decimalplaces']) . '</td>';

		if ($MyRow['quantity']>$MyRow['qtyinvoiced']){
			$HTML .= '<td class="number">' . locale_number_format($MyRow['quantity']-$MyRow['qtyinvoiced'],$MyRow['decimalplaces']). '</td>';
		} else {
			$HTML .= '<td>' . __('Complete') . '</td>';
		}

		$HTML .= '</tr>';

	} /* end of while there are delivery differences to print */

	$Result = DB_query($SQL, '', '', false, false); //dont trap errors here
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_OrderStatus_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	} else {
		$Title = __('Order Status Report');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . __('Order Status Report') . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}
} else { /*The option to print PDF was not hit so display form */

	$Title = __('Order Status Report');
	$ViewTopic = 'Sales';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . __('Order Status Report') . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="FromDate">' . __('Enter the date from which orders are to be listed') . ':</label>
				<input required="required" autofocus="autofocus" type="date" name="FromDate" maxlength="10" size="11" value="' . Date('Y-m-d', Mktime(0, 0, 0, Date('m'), Date('d') - 1, Date('y'))) . '" />
			</field>
			<field>
				<label for="ToDate">' . __('Enter the date to which orders are to be listed') . ':</label>
				<input required="required" type="date" name="ToDate" maxlength="10" size="11" value="' . Date('Y-m-d') . '" />
			</field>
			<field>
				<label for="CategoryID">' . __('Inventory Category') . '</label>';

	$SQL = "SELECT categorydescription, categoryid FROM stockcategory WHERE stocktype<>'D' AND stocktype<>'L'";
	$Result = DB_query($SQL);

	echo '<select required="required" name="CategoryID">
			<option selected="selected" value="All">' . __('Over All Categories') . '</option>';

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
			<label for="BackOrders">' . __('Back Order Only') . ':</label>
			<select name="BackOrders">
				<option value="Yes">' . __('Only Show Back Orders') . '</option>
				<option selected="selected" value="No">' . __('Show All Orders') . '</option>
			</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . __('View') . '" />
			</div>';
	echo '</form>';
	include('includes/footer.php');

} /*end of else not PrintPDF */
