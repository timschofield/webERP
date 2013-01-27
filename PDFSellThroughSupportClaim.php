<?php

/* $Id: PDFSellThroughSupportClaim.php 5788 2013-01-02 03:22:38Z daintree $*/

include('includes/session.inc');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Sell Through Support Claim'));
	$pdf->addInfo('Subject', _('Sell Through Support Claim'));
	$FontSize=10;
	$PageNumber=1;
	$line_height=12;

	$Title = _('Sell Through Support Claim') . ' - ' . _('Problem Report');

	if (! Is_Date($_POST['FromDate']) OR ! Is_Date($_POST['ToDate'])){
		include('includes/header.inc');
		prnMsg(_('The dates entered must be in the format') . ' '  . $_SESSION['DefaultDateFormat'],'error');
		include('includes/footer.inc');
		exit;
	}

	  /*Now figure out the data to report for the category range under review */
	$SQL = "SELECT sellthroughsupport.supplierno,
					suppliers.suppname,
					suppliers.currcode,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					stockmoves.transno,
					stockmoves.trandate,
					systypes.typename,
					stockmoves.qty,
					stockmoves.debtorno,
					debtorsmaster.name,
					stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
					purchdata.price as fxcost,
					sellthroughsupport.rebatepercent,
					sellthroughsupport.rebateamount
				FROM stockmaster INNER JOIN stockmoves
					ON stockmaster.stockid=stockmoves.stockid
				INNER JOIN sellthroughsupport
				INNER JOIN systypes
					ON stockmoves.type=systypes.typeid
				INNER JOIN debtorsmaster
					ON stockmoves.debtorno=debtorsmaster.debtorno
				INNER JOIN suppliers
					ON sellthroughsupport.supplierno=suppliers.supplierid
				INNER JOIN purchdata
					ON purchdata.stockid = stockmaster.stockid
					AND purchdata.supplierno = sellthroughsupport.supplierno
				INNER JOIN currencies
					ON currencies.currabrev=suppliers.currcode
				WHERE stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND (stockmoves.type=10 OR stockmoves.type=11)
				AND (sellthroughsupport.stockid=stockmoves.stockid OR sellthroughsupport.categoryid=stockmaster.categoryid)
				AND (sellthroughsupport.debtorno=stockmoves.debtorno OR sellthroughsupport.debtorno='')
				ORDER BY sellthroughsupport.supplierno,
					stockmaster.stockid";

	$ClaimsResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {

	  include('includes/header.inc');
		prnMsg(_('The sell through support items to claim could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db),'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	if (DB_num_rows($LowGPSalesResult) == 0) {

		include('includes/header.inc');
		prnMsg(_('No sell through support items retrieved'), 'warn');
		echo '<br /><a href="'  . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  echo '<br />' .  $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFSellThroughSuppPageHeader.inc');
	$SupplierClaimTotal=0;
	$Supplier = '';
	$FontSize=8;
	while ($SellThroRow = DB_fetch_array($ClaimsResult,$db)){

		$YPos -=$line_height;
		if ($SellThroRow['suppname']!=$Supplier){
			$FontSize = 10;
			$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,30,$FontSize,$SellThroRow['suppname']);
			$YPos -=$line_height;
			
			if ($SupplierClaimTotal > 0) {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,30,$FontSize,$Supplier . ' ' . _('Total Claim:') . ' (' . $CurrCode . ')');
				$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize, locale_number_format($SupplierClaimTotal,$CurrDecimalPlaces), 'right');
				include('includes/PDFLowGPPageHeader.inc');
			}
			$Supplier = $SellThroRow['suppname'];
			$CurrDeciamlPlaces = $SellThroRow['currdecimalplaces'];
			$CurrCode = $SellThroRow['currcode'];
			$SupplierClaimTotal=0;
			$FontSize =8;
		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,30,$FontSize,$SellThroRow['typename']);
		$LeftOvers = $pdf->addTextWrap(100,$YPos,30,$FontSize,$SellThroRow['transno']);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,50,$FontSize,$SellThroRow['stockid']);
		$LeftOvers = $pdf->addTextWrap(220,$YPos,50,$FontSize,$SellThroRow['name']);
		$DisplaySellingPrice = locale_number_format($SellThroRow['sellingprice'],$_SESSION['CompanyRecord']['decimalplaces']);
		$LeftOvers = $pdf->addTextWrap(330,$YPos,60,$FontSize,$DisplaySellingPrice,'right');
		$ClaimAmount = (($SellThroRow['fxcost']*$SellThroRow['rebatepercent']) + $SellThroRow['rebateamount']) * -$SellThroRow['qty'];
		$SupplierClaimTotal += $ClaimTotal;
		
		
		$LeftOvers = $pdf->addTextWrap(380,$YPos,60,$FontSize,locale_number_format(-$SellThroRow['qty']), 'right');
		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,locale_number_format($ClaimAmount,$CurrDecimalPlaces), 'right');

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFLowGPPageHeader.inc');
		}

	} /*end sell through support claims while loop */

	$FontSize =10;

	$YPos -= (2*$line_height);
	$pdf->OutputD($_SESSION['DatabaseName'] . '_SellThroughSupportClaim_' . date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. _('Sell Through Support Claims Report') . '</p>';

	if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])) {

	/*if $FromDate is not set then show a form to allow input */
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat']);
		$_POST['ToDate']=Date($_SESSION['DefaultDateFormat']);
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
        echo '<div>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<table class="selection">
					<tr>
						<td>' . _('Sales Made From') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
						<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
					</tr>
					<tr>
						<td>' . _('Sales Made To') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</td>
						<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
					</tr>
				</table>
				<br />
				<div class="centre">
					<input type="submit" name="PrintPDF" value="' . _('Create Claims Report') . '" />
				</div>';
        echo '</div>
              </form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>