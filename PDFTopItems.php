<?php
/* $Revision: 1.2 $ */
/* $Id$*/

include ('includes/session.inc');
include ('includes/PDFStarter.php');
$FontSize = 10;
$pdf->addInfo('Title', _('Top Items Search Result'));
$PageNumber = 1;
$line_height = 12;
include ('includes/PDFTopItemsHeader.inc');
$FontSize = 10;
$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$_GET['NumberOfDays']));

//the situation if the location and customer type selected "All"
if (($_GET['Location'] == 'All') and ($_GET['Customers'] == 'All')) {
	$SQL = "SELECT 	salesorderdetails.stkcode,
				SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
				SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
				stockmaster.description,
				stockmaster.units
			FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
			WHERE 	salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorderdetails.actualdispatchdate >='" . $FromDate . "' 
			GROUP BY salesorderdetails.stkcode
			ORDER BY '" . $_GET['Sequence'] . "' DESC
			LIMIT " . $_GET['NumberOfTopItems'] ;
} else { //the situation if only location type selected "All"
	if ($_GET['Location'] == 'All') {
		$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
					stockmaster.description,
					stockmaster.units
				FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
				WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND debtorsmaster.typeid = '" . $_GET['Customers'] . "'
						AND salesorderdetails.ActualDispatchDate >= '" . $FromDate . "' 
				GROUP BY salesorderdetails.stkcode
				ORDER BY '" . $_GET['Sequence'] . "' DESC
				LIMIT " . $_GET['NumberOfTopItems'];
	} else {
		//the situation if the customer type selected "All"
		if ($_GET['Customers'] == 'All') {
			$SQL = "SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET['Location'] . "'
						AND salesorderdetails.ActualDispatchDate >= '" . $FromDate . "' 
					GROUP BY salesorderdetails.stkcode
					ORDER BY '" . $_GET['Sequence'] . "' DESC
					LIMIT 0," . $_GET['NumberOfTopItems'];
		} else {
			//the situation if the location and customer type not selected "All"
			$SQL = "SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET['Location'] . "'
						AND debtorsmaster.typeid = '" . $_GET['Customers'] . "'
						AND salesorderdetails.actualdispatchdate >= '" . $FromDate . "' 
					GROUP BY salesorderdetails.stkcode
					ORDER BY '" . $_GET['Sequence'] . "' DESC
					LIMIT " . $_GET['NumberOfTopItems'];
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
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 1, $YPos, 300 - $Left_Margin, $FontSize, $myrow['stkcode']);
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos, 270 - $Left_Margin, $FontSize, $myrow['description']);
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 330, $YPos, 30, $FontSize, $myrow['totalinvoiced'], 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 370, $YPos, 300 - $Left_Margin, $FontSize, $myrow['units'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 70, $FontSize, number_format($myrow['valuesales'], 2), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 490, $YPos, 30, $FontSize, $ohRow[0], 'right');
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
	echo '<br><a href="' . $rootpath . '/index.php">' . _('Back to the menu') . '</a>';
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
