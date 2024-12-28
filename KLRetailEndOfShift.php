<?php

define("VERSIONFILE", "1.01"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('SPG End Of Shift Report '. VERSIONFILE);
include ('includes/header.php');
include ('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');

include ('includes/WebClientPrint/WebClientPrint.php');
use Neodynamic\SDK\Web\WebClientPrint;
include('includes/wcpESCPOSCommands.php');

$Today = date('Y-m-d');

$TextToPrint = $InitPrinter . $CenteredJustified;
// name of shop
$TextToPrint .= KLPrintNameOfShop();
$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . 'SPG END OF SHIFT REPORT' . $NewLine;
// warning if it is a TEST
$TextToPrint .= KLPrintReceiptTestWarning("END OF SHIFT"). $NewLine . $CenteredJustified;
$TextToPrint .= DisplayDateTime() . $NewLine;
$TextToPrint .= 'SPG Code: ' . $_SESSION['SalesmanLogin'] . $NewLine;
$TextToPrint .= 'Shop Code: ' . substr($_SESSION['UserStockLocation'],3,2) . $NewLine;
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
		WHERE salesorders.orddate >= '". $Today ."'
			AND salesorders.salesperson = '" . $_SESSION['SalesmanLogin'] . "'
		ORDER BY salesorders.orderno ASC";
		
$Result = DB_query($SQL);

if (DB_num_rows($Result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . $_SESSION['SalesmanLogin'] . ' SPG End Of Shift Report' . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Order#') . '</th>
						<th>' . _('Invoice#') . '</th>
						<th>' . _('Cash') . '</th>
						<th>' . _('Credit Card') . '</th>
						<th>' . _('Returned Goods') . '</th>
						<th>' . _('Voucher Discount') . '</th>
						<th>' . _('Total') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	
	while ($MyRow = DB_fetch_array($Result)) {
		$Invoices++;
		$SubTotal = $MyRow['klpaidcash'] + $MyRow['klpaidcreditcard'] + $MyRow['klreturnedgoods'] + $MyRow['klvouchers'];
		$TotalCash += $MyRow['klpaidcash'];
		$TotalCreditCard += $MyRow['klpaidcreditcard'];
		$TotalReturned += $MyRow['klreturnedgoods'];
		$TotalVouchers += $MyRow['klvouchers'];
		$Total += $SubTotal;
		$FirstLinePrinted = FALSE;

		$TextInvoice = $MyRow['customerref'];
		if ($MyRow['klpaidcash'] > 0){
			$TextPayment = 'Cash: ' . number_format($MyRow['klpaidcash'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klpaidcreditcard'] > 0){
			$TextPayment = 'Credit Card: ' . number_format($MyRow['klpaidcreditcard'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klreturnedgoods'] > 0){
			$TextPayment = 'Returned Goods: ' . number_format($MyRow['klreturnedgoods'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($MyRow['klvouchers'] > 0){
			$TextPayment = 'Voucher/Discount: ' . number_format($MyRow['klvouchers'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$Invoices, 
				number_format($MyRow['orderno']), 
				$MyRow['customerref'], 
				number_format($MyRow['klpaidcash']), 
				number_format($MyRow['klpaidcreditcard']), 
				number_format($MyRow['klreturnedgoods']), 
				number_format($MyRow['klvouchers']), 
				number_format($SubTotal)
				);
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
	
	printf('<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			$Invoices, 
			'', 
			'TOTALS', 
			number_format($TotalCash), 
			number_format($TotalCreditCard), 
			number_format($TotalReturned), 
			number_format($TotalVouchers), 
			number_format($Total)
			);
	echo '</table>
			</div>';
}else{
	$TextToPrint .= $Emphasized . $CenteredJustified . "NO SALES TODAY" . $NewLine;
}

// warning if it is a TEST
$TextToPrint .= KLPrintReceiptTestWarning("END OF SHIFT"). $NewLine . $LeftJustified;
$TextToPrint .= $CutPaper;

//################## PRINTING STUFF ##################### 
$identifier=GetPOSIdentifier();
$FileName = GetFilenameFromPOSIdentifier($identifier);   
file_put_contents($FileName, $TextToPrint);
$textActionToPrint = 'Print the Daily SPG End Of Shift';
include ('includes/SilentPrinting.php');
//################## PRINTING STUFF ##################### 

include ('includes/footer.php');
?>