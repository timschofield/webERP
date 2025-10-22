<?php

// Use DomPDF for PDF generation
use Dompdf\Dompdf;

require(__DIR__ . '/includes/session.php');
include('includes/SQL_CommonFunctions.php');

//Get Out if we have no order number to work with
if (!isset($_GET['QuotationNo']) || $_GET['QuotationNo']==""){
	$Title = __('Select Quotation To Print');
	include('includes/header.php');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( __('Select a Quotation to Print before calling this page') , 'error');
	echo '<br /><br /><br />
			<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<a href="'. $RootPath . '/SelectSalesOrder.php?Quotations=Quotes_Only">' . __('Quotations') . '</a></td>
				</tr>
			</table>
			</div><br /><br /><br />';
	include('includes/footer.php');
	exit();
}

$Orientation = $_GET['orientation'];
// Get company info from session
$Company = $_SESSION['CompanyRecord'];
$CompanyName = $Company['coyname'];
$CompanyAddress = [
	$Company['regoffice1'],
	$Company['regoffice2'],
	$Company['regoffice3'],
	$Company['regoffice4'],
	$Company['regoffice5'],
	$Company['regoffice6']
];

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the quotation header details for Order Number') . ' ' . $_GET['QuotationNo'] . ' ' . __('from the database');

$SQL = "SELECT salesorders.customerref,
				salesorders.comments,
				salesorders.orddate,
				salesorders.deliverto,
				salesorders.deladd1,
				salesorders.deladd2,
				salesorders.deladd3,
				salesorders.deladd4,
				salesorders.deladd5,
				salesorders.deladd6,
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				salesorders.printedpackingslip,
				salesorders.datepackingslipprinted,
				salesorders.quotedate,
				salesorders.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM salesorders INNER JOIN debtorsmaster
			ON salesorders.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
			ON salesorders.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON salesorders.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE salesorders.quotation=1
			AND salesorders.orderno='" . $_GET['QuotationNo'] ."'";

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){
	$Title = __('Print Quotation Error');
	include('includes/header.php');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg( __('Unable to Locate Quotation Number') . ' : ' . $_GET['QuotationNo'] . ' ', 'error');
	echo '<br /><br /><br />
			<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<a href="'. $RootPath . '/SelectSalesOrder.php?Quotations=Quotes_Only">' . __('Outstanding Quotations') . '</a>
				</td>
			</tr>
			</table>
			</div><br /><br /><br />';
	include('includes/footer.php');
	exit();
} elseif (DB_num_rows($Result)==1) {
	$MyRow = DB_fetch_array($Result);
}

/* Now ... Has the order got any line items still outstanding to be invoiced */
$ErrMsg = __('There was a problem retrieving the quotation line details for quotation Number') . ' ' .
	$_GET['QuotationNo'] . ' ' . __('from the database');

$SQL = "SELECT salesorderdetails.stkcode,
		stockmaster.description,
		salesorderdetails.quantity,
		salesorderdetails.qtyinvoiced,
		salesorderdetails.unitprice,
		salesorderdetails.discountpercent,
		stockmaster.taxcatid,
		salesorderdetails.narrative,
		stockmaster.decimalplaces
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";

$Result = DB_query($SQL, $ErrMsg);

$ListCount = 0;
$QuotationTotal = 0;
$QuotationTotalEx = 0;
$TaxTotal = 0;

// Start building the HTML for DomPDF
$HTML = '
<style>
	body { font-family: Arial, sans-serif; font-size: 10pt; }
	.header { font-size: 12pt; text-align: left; margin-bottom: 20px; position:absolute; top:20px; float:right}
	.subheader { font-size: 10pt; margin-top: 10px;  }
	.small { font-size: 10pt; }
	table { border-collapse: collapse; width: 100%; margin-bottom: 10px;}
	th, td { border: 1px solid #000; padding: 5px; }
	th { background: #eee; }
</style>';
$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
$HTML .= '<div><img class="logo" src="' . $_SESSION['LogoFile'] . '" /></div>';
$HTML .= '<div><span class="label">' . $CompanyName . '</span></div>';

foreach ($CompanyAddress as $line) {
	if (trim($line) != '') {
		$HTML .= '<div>' . $line . '</div>';
	}
}

$HTML .= '<div class="header">
<div class="subheader">' . __('Quotation No.') . ': ' . $_GET['QuotationNo'] . '</div>
<div class="small">' . __('Date') . ': ' . date('Y-m-d', strtotime($MyRow['quotedate'])) . '</div>
<div class="small">' . __('Customer') . ': ' . htmlspecialchars($MyRow['name']) . '</div>
<div class="small">' . __('Customer Ref') . ': ' . htmlspecialchars($MyRow['customerref']) . '</div>
<div class="small">' . __('Deliver To') . ': ' . htmlspecialchars($MyRow['deliverto']) . '</div>
</div><br />
<table>
	<tr>
		<th>' . __('Item Code') . '</th>
		<th>' . __('Description') . '</th>
		<th>' . __('Quantity') . '</th>
		<th>' . __('Unit Price') . '</th>
		<th>' . __('Discount') . '</th>
		<th>' . __('Tax Rate') . '</th>
		<th>' . __('Tax Amount') . '</th>
		<th>' . __('Line Total') . '</th>
	</tr>';

while ($MyRow2 = DB_fetch_array($Result)) {
	$ListCount ++;
	$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);
	$DisplayPrice = locale_number_format($MyRow2['unitprice'],$MyRow['currdecimalplaces']);
	$DisplayDiscount = locale_number_format($MyRow2['discountpercent']*100,2) . '%';
	$SubTot =  $MyRow2['unitprice']*$MyRow2['quantity']*(1-$MyRow2['discountpercent']);
	$TaxProv = $MyRow['taxprovinceid'];
	$TaxCat = $MyRow2['taxcatid'];
	$Branch = $MyRow['branchcode'];
	$SQL3 = "SELECT taxgrouptaxes.taxauthid
				FROM taxgrouptaxes INNER JOIN custbranch
				ON taxgrouptaxes.taxgroupid=custbranch.taxgroupid
				WHERE custbranch.branchcode='" .$Branch ."'";
	$Result3=DB_query($SQL3, $ErrMsg);
	$TaxAuth = 0;
	while ($MyRow3=DB_fetch_array($Result3)){
		$TaxAuth = $MyRow3['taxauthid'];
	}
	$SQL4 = "SELECT * FROM taxauthrates
				WHERE dispatchtaxprovince='" .$TaxProv ."'
				AND taxcatid='" .$TaxCat ."'
				AND taxauthority='" .$TaxAuth ."'";
	$Result4=DB_query($SQL4, $ErrMsg);
	$TaxClass = 0;
	while ($MyRow4=DB_fetch_array($Result4)){
		$TaxClass = 100 * $MyRow4['taxrate'];
	}
	$DisplayTaxClass = $TaxClass . '%';
	$TaxAmount =  (($SubTot/100)*(100+$TaxClass))-$SubTot;
	$DisplayTaxAmount = locale_number_format($TaxAmount,$MyRow['currdecimalplaces']);
	$LineTotal = $SubTot + $TaxAmount;
	$DisplayTotal = locale_number_format($LineTotal,$MyRow['currdecimalplaces']);
	$QuotationTotal += $LineTotal;
	$QuotationTotalEx += $SubTot;
	$TaxTotal += $TaxAmount;

	$HTML .= '
	<tr>
		<td>' . htmlspecialchars($MyRow2['stkcode']) . '</td>
		<td>' . htmlspecialchars($MyRow2['description']) . '<br /><span class="small">' . nl2br(htmlspecialchars($MyRow2['narrative'])) . '</span></td>
		<td style="text-align:right;">' . $DisplayQty . '</td>
		<td style="text-align:right;">' . $DisplayPrice . '</td>
		<td style="text-align:right;">' . $DisplayDiscount . '</td>
		<td style="text-align:right;">' . $DisplayTaxClass . '</td>
		<td style="text-align:right;">' . $DisplayTaxAmount . '</td>
		<td style="text-align:right;">' . $DisplayTotal . '</td>
	</tr>';
}

$HTML .= '</table>';

if ($ListCount == 0){
	$Title = __('Print Quotation Error');
	include('includes/header.php');
	prnMsg(__('There were no items on the quotation') . '. ' . __('The quotation cannot be printed'),'info');
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?Quotation=Quotes_only">' .  __('Print Another Quotation'). '</a>
			<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
}

// Totals
$HTML .= '
<table style="width:60%; margin-left:auto; margin-bottom:20px;">
	<tr>
		<th style="text-align:right;">' . __('Quotation Excluding Tax') . '</th>
		<td style="text-align:right;">' . locale_number_format($QuotationTotalEx,$MyRow['currdecimalplaces']) . '</td>
	</tr>
	<tr>
		<th style="text-align:right;">' . __('Total Tax') . '</th>
		<td style="text-align:right;">' . locale_number_format($TaxTotal,$MyRow['currdecimalplaces']) . '</td>
	</tr>
	<tr>
		<th style="text-align:right;">' . __('Quotation Including Tax') . '</th>
		<td style="text-align:right;">' . locale_number_format($QuotationTotal,$MyRow['currdecimalplaces']) . '</td>
	</tr>
</table>';

// Notes
if (mb_strlen(trim($MyRow['comments'])) > 1) {
	$comments = $MyRow['comments'];
	$comments = str_replace('\n', ' ', $comments);
	$comments = str_replace('\r', '', $comments);
	$comments = str_replace('\t', '', $comments);
	$HTML .= '<div class="subheader">' . __('Notes') . ':</div><div class="small">' . nl2br(htmlspecialchars($comments)) . '</div>';
}

// Generate PDF with DomPDF
$PdfFileName = $_SESSION['DatabaseName'] . '_Quotation_No_' . $_GET['QuotationNo'] . ' _ ' . date('Y-m-d') . '.pdf';
// Display PDF in browser
$dompdf = new Dompdf(['chroot' => __DIR__]);
$dompdf->loadHtml($HTML);

$dompdf->setPaper($_SESSION['PageSize'], $Orientation);

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream($PdfFileName, array("Attachment" => false));
