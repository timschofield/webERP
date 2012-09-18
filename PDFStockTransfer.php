<?php

/* $Id$*/

/* This script is superseded by the PDFStockLocTransfer.php which produces a multiple item stock transfer listing - this was for the old individual stock transfers where there is just single items being transferred */

include('includes/session.inc');

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
		$title = _('Print Stock Transfer');
		include('includes/header.inc');
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print Transfer Note') . '" alt="" />' . ' ' . $title.'</p><br />';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form">';
        echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">
			<tr>
				<td>'._('Print Stock Transfer Note').' : '.'</td>
				<td><input type="text" class="number"  name="TransferNo" maxlength="10" size="11" /></td>
			</tr>
			</table>';
		echo '<br />
			<div class="centre">
				<input type="submit" name="Process" value="' . _('Print Transfer Note') . '" />
			</div>
            </div>
			</form>';
		include('includes/footer.inc');
		exit();
	}
}


include('includes/PDFStarter.php');
$pdf->addInfo('Title', _('Stock Transfer Form') );
$PageNumber=1;
$line_height=12;

include('includes/PDFStockTransferHeader.inc');
$FontSize =10;

/*Print out the category totals */

$sql="SELECT stockmoves.stockid,
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
		WHERE transno='".$_GET['TransferNo']."'
		AND qty < 0
		AND type=16";

$result=DB_query($sql, $db);
if (DB_num_rows($result) == 0){
	$title = _('Print Stock Transfer - Error');
	include ('includes/header.inc');
	prnMsg(_('There was no transfer found with number') . ': ' . $_GET['TransferNo'], 'error');
	echo '<a href="PDFStockTransfer.php">' . _('Try Again') .'</a>';
	include ('includes/footer.inc');
	exit;
}
//get the first stock movement which will be the quantity taken from the initiating location
while ($myrow=DB_fetch_array($result)) {
	$StockID=$myrow['stockid'];
	$From = $myrow['locationname'];
	$Date=$myrow['trandate'];
	$To = $myrow['reference'];
	$Quantity=-$myrow['qty'];
	$Description=$myrow['description'];

	$LeftOvers = $pdf->addTextWrap($Left_Margin+1,$YPos-10,300-$Left_Margin,$FontSize, $StockID);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize, $Description);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-10,300-$Left_Margin,$FontSize, $From);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos-10,300-$Left_Margin,$FontSize, $To);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+475,$YPos-10,300-$Left_Margin,$FontSize, $Quantity);

	$YPos=$YPos-$line_height;

	if ($YPos < $Bottom_Margin + $line_height){
	   include('includes/PDFStockTransferHeader.inc');
	}
}
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-70,300-$Left_Margin,$FontSize, _('Date of transfer: ').$Date);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-120,300-$Left_Margin,$FontSize, _('Signed for ').$From.'______________________');
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-160,300-$Left_Margin,$FontSize, _('Signed for ').$To.'______________________');

$pdf->OutputD($_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
?>