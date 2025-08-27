<?php

require(__DIR__ . '/includes/session.php');

include('includes/StockFunctions.php');

include('includes/PDFStarter.php');
$FontSize = 10;
$pdf->addInfo('Title', __('Top Items Search Result'));
$PageNumber = 1;
$LineHeight = 12;
include('includes/PDFTopItemsHeader.php');
$FontSize = 10;
$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -$_GET['NumberOfDays']));

//the situation if the location and customer type selected "All"
if (($_GET['Location'] == 'All') AND ($_GET['Customers'] == 'All')) {
	$SQL = "SELECT 	salesorderdetails.stkcode,
				SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
				SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
			debtorsmaster,stockmaster
			WHERE 	salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.stkcode = stockmaster.stockid
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorderdetails.actualdispatchdate >='" . $FromDate . "'
			GROUP BY salesorderdetails.stkcode
			ORDER BY `" . $_GET['Sequence'] . "` DESC
			LIMIT " . intval($_GET['NumberOfTopItems']) ;
} else { //the situation if only location type selected "All"
	if ($_GET['Location'] == 'All') {
		$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
					stockmaster.description,
					stockmaster.units
				FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
				debtorsmaster,stockmaster
				WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND debtorsmaster.typeid = '" . $_GET['Customers'] . "'
						AND salesorderdetails.ActualDispatchDate >= '" . $FromDate . "'
				GROUP BY salesorderdetails.stkcode
				ORDER BY `" . $_GET['Sequence'] . "` DESC
				LIMIT " . intval($_GET['NumberOfTopItems']);
	} else {
		//the situation if the customer type selected "All"
		if ($_GET['Customers'] == 'All') {
			$SQL = "SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
					debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET['Location'] . "'
						AND salesorderdetails.ActualDispatchDate >= '" . $FromDate . "'
					GROUP BY salesorderdetails.stkcode
					ORDER BY `" . $_GET['Sequence'] . "` DESC
					LIMIT 0," . intval($_GET['NumberOfTopItems']);
		} else {
			//the situation if the location and customer type not selected "All"
			$SQL = "SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) totalinvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS valuesales,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
					debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_GET['Location'] . "'
						AND debtorsmaster.typeid = '" . $_GET['Customers'] . "'
						AND salesorderdetails.actualdispatchdate >= '" . $FromDate . "'
					GROUP BY salesorderdetails.stkcode
					ORDER BY `" . $_GET['Sequence'] . "` DESC
					LIMIT " . intval($_GET['NumberOfTopItems']);
		}
	}
}
$Result = DB_query($SQL);
if (DB_num_rows($Result)>0){
	$YPos = $YPos - 6;
	while ($MyRow = DB_fetch_array($Result)) {
		//find the quantity onhand item
		$QOH = GetQuantityOnHand($MyRow['stkcode'], 'USER_CAN_VIEW');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 1, $YPos, 80, $FontSize, $MyRow['stkcode']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 100, $YPos, 100, $FontSize, $MyRow['description']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 330, $YPos, 30, $FontSize, locale_number_format($MyRow['totalinvoiced'],$MyRow['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 370, $YPos, 300 - $Left_Margin, $FontSize, $MyRow['units'], 'left');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 400, $YPos, 70, $FontSize, locale_number_format($MyRow['valuesales'], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 490, $YPos, 30, $FontSize, locale_number_format($QOH,$MyRow['decimalplaces']), 'right');
		if (mb_strlen($LeftOvers) > 1) {
			$LeftOvers = $pdf->addTextWrap($Left_Margin + 1 + 94, $YPos - $LineHeight, 270, $FontSize, $LeftOvers, 'left');
			$YPos-= $LineHeight;
		}
		if ($YPos - $LineHeight <= $Bottom_Margin) {
			/* We reached the end of the page so finish off the page and start a newy */
			$PageNumber++;
			include('includes/PDFTopItemsHeader.php');
			$FontSize = 10;
		} //end if need a new page headed up
		/*increment a line down for the next line item */
		$YPos-= $LineHeight;
	}

	$pdf->OutputD($_SESSION['DatabaseName'] . '_TopItemsListing_' . date('Y-m-d').'.pdf');
	$pdf->__destruct();
}
/*end of else not PrintPDF */
