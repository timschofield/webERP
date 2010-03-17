<?php
/* $Revision: 1.2 $ */
/* $Id$*/
$PageSecurity = 2;
include ('includes/session.inc');
include ('includes/PDFStarter.php');
$FontSize = 10;
$pdf->addinfo('Title', _('Top Items Search Result'));
$PageNumber = 1;
$line_height = 12;
include ('includes/PDFTopItemsHeader.inc');
$FontSize = 10;
//the situation if the location and customer type selected "All"
if (($_GET["location"] == "All") and ($_GET["customers"] == "All")) {
	$SQL = "SELECT 	salesorderdetails.stkcode,
				SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
				SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
				stockmaster.description,
				stockmaster.units
			FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
			WHERE 	salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_GET["numberofdays"] . " DAY)
			GROUP BY salesorderdetails.stkcode
			ORDER BY " . $_GET['order'] . " DESC
			LIMIT 0," . $_GET['NumberOfTopItems'] . "";
} else { //the situation if only location type selected "All"
	if ($_GET["location"] == "All") {
		$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
					stockmaster.description,
					stockmaster.units
				FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
				WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND debtorsmaster.typeid = '" . $_GET["customers"] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_GET["numberofdays"] . " DAY)
				GROUP BY salesorderdetails.stkcode
				ORDER BY " . $_GET['order'] . " DESC
				LIMIT 0," . $_GET['NumberOfTopItems'] . "";
	} else {
		//the situation if the customer type selected "All"
		if ($_GET["customers"] == "All") {
			$SQL = "
					SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET["location"] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_GET["numberofdays"] . " DAY)
					GROUP BY salesorderdetails.stkcode
					ORDER BY " . $_GET['order'] . " DESC
					LIMIT 0," . $_GET['NumberOfTopItems'] . "";
		} else {
			//the situation if the location and customer type not selected "All"
			$SQL = "
					SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET["location"] . "'
						AND debtorsmaster.typeid = '" . $_GET["customers"] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_GET["numberofdays"] . " DAY)
					GROUP BY salesorderdetails.stkcode
					ORDER BY " . $_GET['order'] . " DESC
					LIMIT 0," . $_GET['NumberOfTopItems'] . "";
		}
	}
}
$result = DB_query($SQL, $db);
$YPos = $YPos - 6;
while ($myrow = DB_fetch_array($result)) {
	//find the quantity onhand item
	$sqloh = "SELECT   sum(quantity)as qty
						FROM     `locstock`
						WHERE     stockid='" . $myrow['0'] . "'";
	$oh = db_query($sqloh, $db);
	$ohRow = db_fetch_row($oh);
	$OnHand = $ohRow[0];
	$ValueSales = number_format($myrow['2'], 2);
	$Code = $myrow['0']; //stkcode
	$Desc = $myrow['3']; //desc
	$TotalInvoice = $myrow['1']; //total invoice here
	$Unit = $myrow['4']; //unit
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 1, $YPos, 300 - $Left_Margin, $FontSize, $Code);
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos, 270 - $Left_Margin, $FontSize, $Desc);
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 330, $YPos, 30, $FontSize, $TotalInvoice, 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 370, $YPos, 300 - $Left_Margin, $FontSize, $Unit, 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 70, $FontSize, $ValueSales, 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 490, $YPos, 30, $FontSize, $OnHand, 'right');
	if (strlen($LeftOvers) > 1) {
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 1 + 94, $YPos - $line_height, 270, $FontSize, $LeftOvers, 'left');
		$YPos-= $line_height;
	}
	if ($YPos - $line_height <= $Bottom_Margin) {
		/* We reached the end of the page so finish off the page and start a newy */
		$PageNumber++;
		include ('includes/PDFTopItemsHeader.inc');
		$FontSize = 10;
	} //end if need a new page headed up
	/*increment a line down for the next line item */
	$YPos-= $line_height;
}
$pdfcode = $pdf->output();
$len = strlen($pdfcode);
if (DB_num_rows($result) == 0) {
	$title = _('Print Price List Error');
	include ('includes/header.inc');
	prnMsg(_('There were no records returned '), 'warn');
	echo '<br><a href="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</a>';
	include ('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=TopItems.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	$pdf->Output('TopItems.pdf', 'I');
}
/*end of else not PrintPDF */
?>