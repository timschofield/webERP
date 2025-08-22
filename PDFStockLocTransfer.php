<?php

include('includes/session.php');
$Title = __('Stock Location Transfer Docket Error');

include('includes/PDFStarter.php');

if (isset($_POST['TransferNo'])) {
	$_GET['TransferNo']=$_POST['TransferNo'];
}

if (!isset($_GET['TransferNo'])){

	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') .
		'" alt="" />' . ' ' . __('Reprint transfer docket') . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Transfer Docket Criteria'), '</legend>';
	echo '<fieldset>
			<field>
				<label for="TransferNo">' . __('Transfer docket to reprint') . '</label>
				<input type="text" class="number" size="10" name="TransferNo" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print') .'" />
		</div>';
    echo '</form>';

	echo '<form method="post" action="' . $RootPath . '/PDFShipLabel.php">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="Type" value="Transfer" />';
	echo '<fieldset>
			<field>
				<label for="ORD">' . __('Transfer docket to reprint Shipping Labels') . '</label>
				<input type="text" class="number" size="10" name="ORD" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . __('Print Shipping Labels') .'" />
		</div>';
	echo '</fieldset>';
    echo '</form>';

	include('includes/footer.php');
	exit();
}

$pdf->addInfo('Title', __('Inventory Location Transfer BOL') );
$pdf->addInfo('Subject', __('Inventory Location Transfer BOL') . ' # ' . $_GET['TransferNo']);
$FontSize=10;
$PageNumber=1;
$LineHeight=30;

$ErrMsg = __('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  __('This page must be called with a location transfer reference number').'.';
$SQL = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.description,
			   loctransfers.shipqty,
			   loctransfers.recqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname,
			   stockmaster.decimalplaces
		FROM loctransfers
		INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
		INNER JOIN locations ON loctransfers.shiploc=locations.loccode
		INNER JOIN locations AS locationsrec ON loctransfers.recloc = locationsrec.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		INNER JOIN locationusers as locationusersrec ON locationusersrec.loccode=locationsrec.loccode AND locationusersrec.userid='" .  $_SESSION['UserID'] . "' AND locationusersrec.canview=1
		WHERE loctransfers.reference='" . $_GET['TransferNo'] . "'";

$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result)==0){

	include('includes/header.php');
	prnMsg(__('The transfer reference selected does not appear to be set up') . ' - ' . __('enter the items to be transferred first'),'error');
	include('includes/footer.php');
	exit();
}

$TransferRow = DB_fetch_array($Result);

include('includes/PDFStockLocTransferHeader.php');
$LineHeight=30;
$FontSize=10;

do {

	$pdf->addTextWrap($Left_Margin, $YPos, 100, $FontSize, $TransferRow['stockid'], 'left');
	$pdf->addTextWrap($Left_Margin+100, $YPos, 250, $FontSize, $TransferRow['description'], 'left');
	$pdf->addTextWrap($Page_Width-$Right_Margin-100-100, $YPos, 100, $FontSize, locale_number_format($TransferRow['shipqty'],$TransferRow['decimalplaces']), 'right');
	$pdf->addTextWrap($Page_Width-$Right_Margin-100, $YPos, 100, $FontSize, locale_number_format($TransferRow['recqty'],$TransferRow['decimalplaces']), 'right');

	$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

	$YPos -= $LineHeight;

	if ($YPos < $Bottom_Margin + $LineHeight) {
		$PageNumber++;
		include('includes/PDFStockLocTransferHeader.php');
	}

} while ($TransferRow = DB_fetch_array($Result));
$pdf->OutputD($_SESSION['DatabaseName'] . '_StockLocTrfShipment_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
