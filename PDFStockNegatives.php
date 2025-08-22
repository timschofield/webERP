<?php

include('includes/session.php');

include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Inventory Negatives Listing') );
$pdf->addInfo('Subject', __('Inventory Negatives Listing'));
$FontSize=9;
$PageNumber=1;
$LineHeight=15;

$Title = __('Negative Stock Listing Error');
$ErrMsg = __('An error occurred retrieving the negative quantities.');

$SQL = "SELECT stockmaster.stockid,
               stockmaster.description,
               stockmaster.categoryid,
               stockmaster.decimalplaces,
               locstock.loccode,
               locations.locationname,
               locstock.quantity
        FROM stockmaster INNER JOIN locstock
        ON stockmaster.stockid=locstock.stockid
        INNER JOIN locations
        ON locstock.loccode = locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
        WHERE locstock.quantity < 0
        ORDER BY locstock.loccode,
			stockmaster.categoryid,
			stockmaster.stockid,
			stockmaster.decimalplaces";

$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result)==0){
	include('includes/header.php');
	prnMsg(__('There are no negative stocks to list'),'error');
	include('includes/footer.php');
	exit();
}

$NegativesRow = DB_fetch_array($Result);

include('includes/PDFStockNegativesHeader.php');
$LineHeight=15;
$FontSize=10;

do {

	$pdf->addTextWrap($Left_Margin,$YPos,130,$FontSize, $NegativesRow['loccode'] . ' - ' . $NegativesRow['locationname'], 'left');
	$pdf->addTextWrap(170,$YPos,350,$FontSize,$NegativesRow['stockid'] . ' - ' .$NegativesRow['description'], 'left');
	$pdf->addTextWrap(520,$YPos,30,$FontSize,locale_number_format($NegativesRow['quantity'],$NegativesRow['decimalplaces']), 'right');

	$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

	$YPos -= $LineHeight;

	if ($YPos < $Bottom_Margin + $LineHeight) {
		$PageNumber++;
		include('includes/PDFStockNegativesHeader.php');
	}

} while ($NegativesRow = DB_fetch_array($Result));

if (DB_num_rows($Result)>0){
	$pdf->OutputD($_SESSION['DatabaseName'] . '_NegativeStocks_' . date('Y-m-d') . '.pdf');
	$pdf->__destruct();
} else {
	$Title = __('Negative Stock Listing Problem');
	include('includes/header.php');
	prnMsg(__('There are no negative stocks to list'),'info');
	include('includes/footer.php');
}
