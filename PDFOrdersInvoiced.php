<?php

include ('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}
use Dompdf\Dompdf;
$Title = _('Orders Invoiced Report');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if ($_POST['CategoryID']=='All' AND $_POST['Location']=='All'){
		$SQL= "SELECT salesorders.orderno,
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
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
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
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'";


	} elseif ($_POST['CategoryID']!='All' AND $_POST['Location']=='All') {
		$SQL= "SELECT salesorders.orderno,
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
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
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
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
				  AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	} elseif ($_POST['CategoryID']=='All' AND $_POST['Location']!='All') {
		$SQL= "SELECT salesorders.orderno,
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
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
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
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			 WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
				  AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

	} elseif ($_POST['CategoryID']!='All' AND $_POST['location']!='All'){
		$SQL= "SELECT salesorders.orderno,
				  salesorders.debtorno,
				  salesorders.branchcode,
				  salesorders.customerref,
				  salesorders.orddate,
				  salesorders.fromstkloc,
				  salesorderdetails.stkcode,
				  stockmaster.description,
				  stockmaster.units,
				  stockmaster.decimalplaces,
				  debtorsmaster.name,
				  custbranch.brname,
				  locations.locationname,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced
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
				 INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE stockmaster.categoryid ='" . $_POST['CategoryID'] . "'
				  AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				  AND orddate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				  AND orddate <='" . FormatDateForSQL($_POST['ToDate']) . "'";
	}

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$SQL .= " GROUP BY salesorders.orderno,
					salesorders.debtorno,
					salesorders.branchcode,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.fromstkloc,
					salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.units,
					stockmaster.decimalplaces
			ORDER BY salesorders.orderno";

	$Result=DB_query($SQL,'','',false,false); //dont trap errors here

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
				' . _('Order Invoiced Listing') . '<br />
				' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				' . _('Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '<br />
			</div>';

	$OrderNo =0; /*initialise */
	$AccumTotalInv =0;
	$AccumOrderTotal =0;

	$HTML .= '<table>
				<tbody>';

	while ($MyRow=DB_fetch_array($Result)){

		if ($OrderNo != $MyRow['orderno']){

			if ($AccumOrderTotal !=0){
				$HTML .= '<tr>
							<th colspan=5">' . _('Total Invoiced for order') . ' ' . $OrderNo . '</th>';
				$HTML .= '<th class="number">' . locale_number_format($AccumOrderTotal,$_SESSION['CompanyRecord']['decimalplaces']). '</th>
						<th></th>
					</tr>';
				$AccumOrderTotal =0;
			}

			$HTML .= '<tr>
						<th>' . _('Order') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Customer Ref') . '</th>
						<th>' . _('Ord Date') . '</th>
						<th>' . _('Location') . '</th>
						<th></th>
					</tr>';
		}
		if ($MyRow['orderno']!=$OrderNo){

			$HTML .= '<tr>
						<td>' . $MyRow['orderno'] . '</td>
						<td>' . $MyRow['name'] . '</td>
						<td>' . $MyRow['brname'] . '</td>
						<td>' . $MyRow['customerref'] . '</td>
						<td>' . $MyRow['orddate'] . '</td>
						<td>' . $MyRow['locationname'] . '</td>
						<td></td>
					</tr>';

			if (isset($PackingSlipPrinted)) {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+400,$YPos,100,$FontSize,$PackingSlipPrinted, 'left');
			}

		}
		$OrderNo = $MyRow['orderno'];
		/*Set up the headings for the order */

		$HTML .= '<tr>
					<th></th>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Ordered') . '</th>
					<th>' . _('Invoiced') . '</th>
					<th>' . _('Outstanding') . '</th>
					<th></th>
				</tr>';

		$HTML .= '<tr>
					<td></td>
					<td>' . $MyRow['stkcode'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totqty'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['totqtyinvoiced'],$MyRow['decimalplaces']) . '</td>';
		if ($MyRow['totqty']>$MyRow['totqtyinvoiced']){
			$HTML .= '<td class="number">' . locale_number_format($MyRow['totqty']-$MyRow['totqtyinvoiced'],$MyRow['decimalplaces']) . '</td>';
		} else {
			$HTML .= '<td>' . _('Complete') . '</td>';
		}
		$HTML .= '<td></td>
				</tr>';

		/*OK now get the invoices where the item was charged */
		$SQL = "SELECT debtortrans.order_,
					systypes.typename,
					debtortrans.transno,
					debtortrans.trandate,
			 		stockmoves.price *(1-stockmoves.discountpercent) AS netprice,
					-stockmoves.qty AS quantity,
					stockmoves.narrative
				FROM debtortrans INNER JOIN stockmoves
					ON debtortrans.type = stockmoves.type
					AND debtortrans.transno=stockmoves.transno
					INNER JOIN systypes ON debtortrans.type=systypes.typeid
				WHERE debtortrans.order_ ='" . $OrderNo . "'
				AND stockmoves.stockid ='" . $MyRow['stkcode'] . "'";

		$InvoicesResult =DB_query($SQL);
		if (DB_num_rows($InvoicesResult)>0){
			$HTML .= '<tr>
						<th></th>
						<th>' . _('Date') . '</th>
						<th>' . _('Transaction Number') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Total') . '</th>
						<th>' . _('Narrative') . '</th>
					</tr>';
		}

		while ($InvRow=DB_fetch_array($InvoicesResult)){

			$ValueInvoiced = $InvRow['netprice']*$InvRow['quantity'];
			$HTML .= '<tr>
						<td></td>
						<td>' . ConvertSQLDate($InvRow['trandate']) . '</td>
						<td>' . $InvRow['typename'] . ' ' . $InvRow['transno'] . '</td>
						<td class="number">' . locale_number_format($InvRow['quantity'],$MyRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($InvRow['netprice'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($ValueInvoiced,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td>' . $InvRow['narrative'] . '</td>
					</tr>';

			$AccumOrderTotal += $ValueInvoiced;
			$AccumTotalInv += $ValueInvoiced;
		}
	} /* end of while there are invoiced orders to print */

	$HTML .= '<tr>
				<th colspan="3"></th>
				<th colspan="2">' . _('GRAND TOTAL INVOICED') . '</th>
				<th class="number">' . locale_number_format($AccumTotalInv,$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
				<th></th>
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
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_OrdersInvoiced_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	} else {
		$Title = _('Orders Invoiced Report');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
			</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */
	$ViewPage = 'Sales';
	$BookMark = '';
	include ('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. _('Orders Invoiced Report') . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="FromDate">' . _('Enter the date from which orders are to be listed') . ':</label>
				<input required="required" autofocus="autofocus" type="date" name="FromDate" maxlength="10" size="11" value="' . Date('Y-m-d', Mktime(0,0,0,Date('m'),Date('d')-1,Date('y'))) . '" />
			</field>
			<field>
				<label for="ToDate">' . _('Enter the date to which orders are to be listed') . ':</label>
				<input required="required" type="date" name="ToDate" maxlength="10" size="11" value="' . Date('Y-m-d') . '" />
			</field>
			<field>
				<label for="CategoryID">' . _('Inventory Category') . '</label>';

	$SQL = "SELECT categorydescription, categoryid FROM stockcategory";
	$Result = DB_query($SQL);

	echo '<select required="required" name="CategoryID">';
	echo '<option selected="selected" value="All">' . _('Over All Categories') . '</option>';

	while ($MyRow=DB_fetch_array($Result)){
	echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
	echo '</select>
		</field>
		<field>
			<label for="Location">' . _('Inventory Location') . ':</label>
			<select required="required" name="Location">
				<option selected="selected" value="All">' . _('All Locations') . '</option>';

	$Result= DB_query("SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1");
	while ($MyRow=DB_fetch_array($Result)){
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View') . '" />
			</div>';
	echo '</form>';

	include('includes/footer.php');
}
