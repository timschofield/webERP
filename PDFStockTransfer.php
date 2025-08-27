<?php

/* This script is superseded by the PDFStockLocTransfer.php which produces a multiple item stock transfer listing - this was for the old individual stock transfers where there is just single items being transferred */

require(__DIR__ . '/includes/session.php');

if (!isset($_GET['TransferNo'])){
	if (isset($_POST['TransferNo'])){
		if (is_numeric($_POST['TransferNo'])){
			$_GET['TransferNo'] = $_POST['TransferNo'];
		} else {
			prnMsg(__('The entered transfer reference is expected to be numeric'),'error');
			unset($_POST['TransferNo']);
		}
	}
	if (!isset($_GET['TransferNo'])){ //still not set from a post then
	//open a form for entering a transfer number
		$Title = __('Print Stock Transfer');
		$ViewTopic = 'Inventory';
		$BookMark = '';
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print Transfer Note') . '" alt="" />' . ' ' . $Title . '</p>';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>';
		echo '<fieldset>
			<field>
				<label for="TransferNo">' . __('Print Stock Transfer Note').' : ' . '</label>
				<input type="text" class="number"  name="TransferNo" maxlength="10" size="11" />
			</field>
			</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Process" value="' . __('Print Transfer Note') . '" />
			</div>
			</form>';

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
		echo '</fieldset>
			</form>';

		include('includes/footer.php');
		exit();
	}
}


include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Stock Transfer Form') );
$PageNumber=1;
$LineHeight=12;

include('includes/PDFStockTransferHeader.php');

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

$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0){
	$Title = __('Print Stock Transfer - Error');
	include('includes/header.php');
	prnMsg(__('There was no transfer found with number') . ': ' . $_GET['TransferNo'], 'error');
	echo '<a href="' . $RootPath . '/PDFStockTransfer.php">' . __('Try Again')  . '</a>';
	include('includes/footer.php');
	exit();
}
//get the first stock movement which will be the quantity taken from the initiating location
while ($MyRow=DB_fetch_array($Result)) {
	$StockID=$MyRow['stockid'];
	$From = $MyRow['locationname'];
	$Date=$MyRow['trandate'];
	$To = $MyRow['reference'];
	$Quantity=-$MyRow['qty'];
	$Description=$MyRow['description'];

	$pdf->addTextWrap($Left_Margin+1,$YPos-10,300-$Left_Margin,$FontSize, $StockID);
	$pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $Description);
	$pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $From);
	$pdf->addTextWrap($Left_Margin+350,$YPos-10,300-$Left_Margin,$FontSize, $To);
	$pdf->addTextWrap($Left_Margin+475,$YPos-10,300-$Left_Margin,$FontSize, $Quantity);

	$YPos=$YPos-$LineHeight;

	if ($YPos < $Bottom_Margin + $LineHeight){
	   include('includes/PDFStockTransferHeader.php');
	}

	$SQL = "SELECT stockmaster.controlled
			FROM stockmaster WHERE stockid ='" . $StockID . "'";
	$CheckControlledResult = DB_query($SQL,'<br />' . __('Could not determine if the item was controlled or not because') . ' ');
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
		$GetStockMoveResult = DB_query($SQL,__('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
		while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)){
			$pdf->addTextWrap($Left_Margin+40,$YPos-10,300-$Left_Margin,$FontSize, __('Lot/Serial:'));
			$pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $SerialStockMoves['serialno']);
			$pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $SerialStockMoves['moveqty']);
			$YPos=$YPos-$LineHeight;

			if ($YPos < $Bottom_Margin + $LineHeight){
				include('includes/PDFStockTransferHeader.php');
			} //while SerialStockMoves
		}
		$pdf->addTextWrap($Left_Margin+40,$YPos-10,300-$Left_Margin,$FontSize, ' ');
		$YPos=$YPos-$LineHeight;
		if ($YPos < $Bottom_Margin + $LineHeight){
			include('includes/PDFStockTransferHeader.php');
		} //controlled item*/
	}

}
$pdf->addTextWrap($Left_Margin,$YPos-70,300-$Left_Margin,$FontSize, __('Date of transfer: ').$Date);

$pdf->addTextWrap($Left_Margin,$YPos-120,300-$Left_Margin,$FontSize, __('Signed for').' '.$From.'______________________');
$pdf->addTextWrap($Left_Margin,$YPos-160,300-$Left_Margin,$FontSize, __('Signed for').' '.$To.'______________________');

$pdf->OutputD($_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
