<?php


/* This script is superseded by the PDFStockLocTransfer.php which produces a multiple item stock transfer listing - this was for the old individual stock transfers where there is just single items being transferred */

include('includes/session.php');

if (!isset($_GET['TransferNo'])){
	if (isset($_POST['TransferNo'])){
		if (is_numeric($_POST['TransferNo'])){
			$_GET['TransferNo'] = $_POST['TransferNo'];
		} else {
			prnMsg(_('The entered transfer reference is expected to be numeric'),'error');
			unset($_POST['TransferNo']);
		}
	}
	if (!isset($_GET['TransferNo'])){ //still not set from a post then
	//open a form for entering a transfer number
		$Title = _('Print Stock Transfer');
		$ViewTopic = 'Inventory';
		$BookMark = '';
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print Transfer Note') . '" alt="" />' . ' ' . $Title . '</p>';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>';
		echo '<fieldset>
			<field>
				<label for="TransferNo">' . _('Print Stock Transfer Note').' : ' . '</label>
				<input type="text" class="number"  name="TransferNo" maxlength="10" size="11" />
			</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Process" value="' . _('Print Transfer Note') . '" />
			</div>
			</form>';

		echo '<form method="post" action="' . $RootPath . '/PDFShipLabel.php">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="Type" value="Transfer" />';
		echo '<fieldset>
				<field>
					<label for="ORD">' . _('Transfer docket to reprint Shipping Labels') . '</label>
					<input type="text" class="number" size="10" name="ORD" />
				</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Print" value="' . _('Print Shipping Labels') .'" />
			</div>';
		echo '</fieldset>
			</form>';

		include('includes/footer.php');
		exit();
	}
}


include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Stock Transfer Form') );
$PageNumber=1;
$LineHeight=12;

include('includes/PDFStockTransferHeader.inc');

/*Print out the category totals */

$SQL="SELECT stockmoves.stockid,
			description,
			transno,
			stockmoves.loccode,
			locationname,
			trandate,
			qty,
			reference
		FROM stockmoves
		INNER JOIN stockmaster
		ON stockmoves.stockid=stockmaster.stockid
		INNER JOIN locations
		ON stockmoves.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		WHERE transno='".$_GET['TransferNo']."'
		AND qty < 0
		AND type=16";

$Result=DB_query($SQL);
if (DB_num_rows($Result) == 0){
	$Title = _('Print Stock Transfer - Error');
	include ('includes/header.php');
	prnMsg(_('There was no transfer found with number') . ': ' . $_GET['TransferNo'], 'error');
	echo '<a href="PDFStockTransfer.php">' . _('Try Again')  . '</a>';
	include ('includes/footer.php');
	exit;
}
//get the first stock movement which will be the quantity taken from the initiating location
while ($MyRow=DB_fetch_array($Result)) {
	$StockID=$MyRow['stockid'];
	$From = $MyRow['locationname'];
	$Date=$MyRow['trandate'];
	$To = $MyRow['reference'];
	$Quantity=-$MyRow['qty'];
	$Description=$MyRow['description'];

	$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos-10,300-$Left_Margin,$FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $From);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos-10,300-$Left_Margin,$FontSize, $To);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-10,300-$Left_Margin,$FontSize, $Quantity);

	$YPos=$YPos-$LineHeight;

	if ($YPos < $Bottom_Margin + $LineHeight){
	   include('includes/PDFStockTransferHeader.inc');
	}

	$SQL = "SELECT stockmaster.controlled
			FROM stockmaster WHERE stockid ='" . $StockID . "'";
	$CheckControlledResult = DB_query($SQL,'<br />' . _('Could not determine if the item was controlled or not because') . ' ');
	$ControlledRow = DB_fetch_row($CheckControlledResult);

	if ($ControlledRow[0]==1) { /*Then its a controlled item */
		$SQL = "SELECT stockserialmoves.serialno,
				stockserialmoves.moveqty
				FROM stockmoves INNER JOIN stockserialmoves
				ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
				WHERE stockmoves.stockid='" . $StockID . "'
				AND stockmoves.type =16
				AND qty > 0
				AND stockmoves.transno='" .$_GET['TransferNo']. "'";
		$GetStockMoveResult = DB_query($SQL,_('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
		while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){
			$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos-10,300-$Left_Margin,$FontSize, _('Lot/Serial:'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $SerialStockMoves['serialno']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $SerialStockMoves['moveqty']);
			$YPos=$YPos-$LineHeight;

			if ($YPos < $Bottom_Margin + $LineHeight){
				include('includes/PDFStockTransferHeader.inc');
			} //while SerialStockMoves
		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos-10,300-$Left_Margin,$FontSize, ' ');
		$YPos=$YPos-$LineHeight;
		if ($YPos < $Bottom_Margin + $LineHeight){
			include('includes/PDFStockTransferHeader.inc');
		} //controlled item*/
	}

}
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-70,300-$Left_Margin,$FontSize, _('Date of transfer: ').$Date);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-120,300-$Left_Margin,$FontSize, _('Signed for').' '.$From.'______________________');
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-160,300-$Left_Margin,$FontSize, _('Signed for').' '.$To.'______________________');

$pdf->OutputD($_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
?>