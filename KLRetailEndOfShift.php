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

$today = date('Y-m-d');

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

$SQL = "SELECT salesorders.orderno,
				salesorders.customerref,
				salesorders.klpaidcash,
				salesorders.klpaidcreditcard,
				salesorders.klreturnedgoods,
				salesorders.klvouchers
		FROM salesorders
		WHERE salesorders.orddate >= '". $today ."'
			AND salesorders.salesperson = '" . $_SESSION['SalesmanLogin'] . "'
		ORDER BY salesorders.orderno ASC";
		
$result = DB_query($SQL);

if (DB_num_rows($result) != 0){
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
	$Invoices = 0;
	$TotalCash = 0;
	$TotalCreditCard = 0;
	$TotalReturned = 0;
	$TotalVouchers = 0;
	$Total = 0;

	
	while ($myrow = DB_fetch_array($result)) {
		$Invoices++;
		$SubTotal = $myrow['klpaidcash'] + $myrow['klpaidcreditcard'] + $myrow['klreturnedgoods'] + $myrow['klvouchers'];
		$TotalCash += $myrow['klpaidcash'];
		$TotalCreditCard += $myrow['klpaidcreditcard'];
		$TotalReturned += $myrow['klreturnedgoods'];
		$TotalVouchers += $myrow['klvouchers'];
		$Total += $SubTotal;
		$FirstLinePrinted = FALSE;

		$TextInvoice = $myrow['customerref'];
		if ($myrow['klpaidcash'] > 0){
			$TextPayment = 'Cash: ' . number_format($myrow['klpaidcash'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($myrow['klpaidcreditcard'] > 0){
			$TextPayment = 'Credit Card: ' . number_format($myrow['klpaidcreditcard'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($myrow['klreturnedgoods'] > 0){
			$TextPayment = 'Returned Goods: ' . number_format($myrow['klreturnedgoods'],0);
			if(!$FirstLinePrinted){
				$TextToPrint .= DoubleJustified($TextInvoice, $TextPayment, $LineLenghtCharA, " ");
				$FirstLinePrinted = TRUE;
			}else{
				$TextToPrint .=  $RightJustified . $TextPayment . $NewLine;
			}
		}
		if ($myrow['klvouchers'] > 0){
			$TextPayment = 'Voucher/Discount: ' . number_format($myrow['klvouchers'],0);
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
				number_format($myrow['orderno']), 
				$myrow['customerref'], 
				number_format($myrow['klpaidcash']), 
				number_format($myrow['klpaidcreditcard']), 
				number_format($myrow['klreturnedgoods']), 
				number_format($myrow['klvouchers']), 
				number_format($SubTotal)
				);
	}
	$TextToPrint .= $NewLine;
	$TextInvoice = "# Invoices: " . $Invoices;
	$TextCash .= "Total Cash: " . number_format($TotalCash);
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
$filename = GetFilenameFromPOSIdentifier($identifier);   
file_put_contents($filename, $TextToPrint);
$textActionToPrint = 'Print the Daily SPG End Of Shift';
include ('includes/SilentPrinting.php');
//################## PRINTING STUFF ##################### 

include ('includes/footer.php');
?>