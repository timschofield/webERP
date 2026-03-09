<?php

require(__DIR__ . '/includes/session.php');

$Title = __('SPG End Of Shift Report');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLPOSGeneral.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

include(__DIR__ . '/includes/WebClientPrint/WebClientPrint.php');
include(__DIR__ . '/includes/KLESCPOSCommands.php');

$Today = date('Y-m-d');

$TextToPrint = $InitPrinter . $CenteredJustified;
// name of shop
$TextToPrint .= KLPrintNameOfShop();
$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . 'SPG END OF SHIFT REPORT' . $NewLine;
// warning if it is a TEST
$TextToPrint .= KLPrintReceiptTestWarning("END OF SHIFT"). $NewLine . $CenteredJustified;
$TextToPrint .= DisplayDateTime() . $NewLine;
$TextToPrint .= 'SPG Code: ' . $_SESSION['SalesmanLogin'] . $NewLine;
$TextToPrint .= 'Shop Code: ' . $_SESSION['UserStockLocation'] . $NewLine;
$TextToPrint .=  $NewLine . $NewLine;

$Invoices = 0;
$TotalCash = 0;
$TotalCreditCard = 0;
$TotalReturned = 0;
$TotalVouchers = 0;
$Total = 0;

$SQL = "SELECT salesorders.orderno,
				salesorders.customerref,
				salesorders.klpaidcash,
				salesorders.klpaidcreditcard,
				salesorders.klreturnedgoods,
				salesorders.klvouchers
		FROM salesorders
		WHERE salesorders.orddate >= CURRENT_DATE
			AND salesorders.salesperson = '" . $_SESSION['SalesmanLogin'] . "'
		ORDER BY salesorders.orderno ASC";
		
$Result = DB_query($SQL);

if (DB_num_rows($Result) != 0){
	$TableTitleText = $_SESSION['SalesmanLogin'] . __('SPG End Of Shift Report');
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<thead><tr>
						<th>' . __('#') . '</th>
						<th>' . __('Order#') . '</th>
						<th>' . __('Invoice#') . '</th>
						<th>' . __('Cash') . '</th>
						<th>' . __('Credit Card') . '</th>
						<th>' . __('Returned Goods') . '</th>
						<th>' . __('Voucher Discount') . '</th>
						<th>' . __('Total') . '</th>
					</tr></thead><tbody>';
	echo $TableHeader;
	
	while ($MyRow = DB_fetch_array($Result)) {
		$Invoices++;
		$SubTotal = $MyRow['klpaidcash'] + $MyRow['klpaidcreditcard'] + $MyRow['klreturnedgoods'] + $MyRow['klvouchers'];
		$TotalCash += $MyRow['klpaidcash'];
		$TotalCreditCard += $MyRow['klpaidcreditcard'];
		$TotalReturned += $MyRow['klreturnedgoods'];
		$TotalVouchers += $MyRow['klvouchers'];
		$Total += $SubTotal;
		$FirstLinePrinted = false;

		$TextInvoice = $MyRow['customerref'];
		if ($MyRow['klpaidcash'] > 0){
			$TextPayment = 'Cash: ' . number_format($MyRow['klpaidcash'],0);
			if (!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = true;
			} else {
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klpaidcreditcard'] > 0){
			$TextPayment = 'Credit Card: ' . number_format($MyRow['klpaidcreditcard'],0);
			if (!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = true;
			} else {
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klreturnedgoods'] > 0){
			$TextPayment = 'Returned Goods: ' . number_format($MyRow['klreturnedgoods'],0);
			if (!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = true;
			} else {
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klvouchers'] > 0){
			$TextPayment = 'Voucher/Discount: ' . number_format($MyRow['klvouchers'],0);
			if (!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = true;
			} else {
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		
		echo '<tr class="striped_row">
				<td class="number">'.$Invoices.'</td>
				<td class="number">'.number_format($MyRow['orderno']).'</td>
				<td>'.$MyRow['customerref'].'</td>
				<td class="number">'.number_format($MyRow['klpaidcash']).'</td>
				<td class="number">'.number_format($MyRow['klpaidcreditcard']).'</td>
				<td class="number">'.number_format($MyRow['klreturnedgoods']).'</td>
				<td class="number">'.number_format($MyRow['klvouchers']).'</td>
				<td class="number">'.number_format($SubTotal).'</td>
				</tr>';
	}
	$TextToPrint .= $NewLine;
	$TextInvoice = "# Invoices: " . $Invoices;
	$TextCash = "Total Cash: " . number_format($TotalCash);
	$TextToPrint .= DoubleJustified($TextInvoice, $TextCash, $LineLenghtCharA, " ") . $RightJustified;
	$TextToPrint .= "Total Credit Card: " . number_format($TotalCreditCard) . $NewLine;
	$TextToPrint .= "Total Returned Goods: " . number_format($TotalReturned) . $NewLine;
	$TextToPrint .= "Total Voucher/Discounts: " . number_format($TotalVouchers) . $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= "Total include returns/vouchers: " . number_format($Total) . $NewLine;
	$TextToPrint .= "Total Personal Sales SPG: " . number_format($TotalCash + $TotalCreditCard) . $NewLine;
	
	echo '<tr class="striped_row">
			<td class="number">'.$Invoices.'</td>
			<td>'.''.'</td>
			<td>'.'TOTALS'.'</td>
			<td class="number">'.number_format($TotalCash).'</td>
			<td class="number">'.number_format($TotalCreditCard).'</td>
			<td class="number">'.number_format($TotalReturned).'</td>
			<td class="number">'.number_format($TotalVouchers).'</td>
			<td class="number">'.number_format($Total).'</td>
			</tr>';
	echo '</tbody></table>
			</div>';
} else {
	$TextToPrint .= $Emphasized . $CenteredJustified . "NO SALES TODAY" . $NewLine;
}

// warning if it is a TEST
$TextToPrint .= KLPrintReceiptTestWarning("END OF SHIFT"). $NewLine . $LeftJustified;
$TextToPrint .= $CutPaper;

//################## PRINTING STUFF ##################### 
$identifier=GetPOSIdentifier();
$FileName = GetFilenameFromPOSIdentifier($identifier);   
file_put_contents($FileName, $TextToPrint);
$TextActionToPrint = 'Print the Daily SPG End Of Shift';
include(__DIR__ . '/includes/KLSilentPrinting.php');
//################## PRINTING STUFF ##################### 

include(__DIR__ . '/includes/footer.php');
