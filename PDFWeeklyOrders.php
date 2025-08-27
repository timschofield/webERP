<?php

/// @todo this should probably not be hardcoded here. What about using $DefaultDatabase from config.php?
$DatabaseName = 'weberp';
$AllowAnyone = true;

require(__DIR__ . '/includes/session.php');

use PHPMailer\PHPMailer\PHPMailer;

include('includes/SQL_CommonFunctions.php');
include('includes/class.cpdf.php');

$_POST['FromDate']=date('Y-m-01');
$_POST['ToDate']= FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
$WeekStartDate = Date(($_SESSION['DefaultDateFormat']), strtotime($WeekStartDate . ' - 7 days'));
$Recipients = GetMailList('WeeklyOrders');
if (sizeOf($Recipients) == 0) {
	$Title = __('Weekly Orders') . ' - ' . __('Problem Report');
	include('includes/header.php');
	prnMsg( __('There are no members of the Weekly Orders Recipients email group'), 'warn');
	include('includes/footer.php');
	exit();
}

$SQL= "SELECT salesorders.orderno,
			  salesorders.orddate,
			  salesorderdetails.stkcode,
			  salesorderdetails.unitprice,
			  stockmaster.description,
			  stockmaster.units,
			  stockmaster.decimalplaces,
			  salesorderdetails.quantity,
			  salesorderdetails.qtyinvoiced,
			  salesorderdetails.completed,
			  salesorderdetails.discountpercent,
			  stockmaster.actualcost AS standardcost,
			  debtorsmaster.name
		 FROM salesorders
			 INNER JOIN salesorderdetails
			 ON salesorders.orderno = salesorderdetails.orderno
			 INNER JOIN stockmaster
			 ON salesorderdetails.stkcode = stockmaster.stockid
			 INNER JOIN debtorsmaster
			 ON salesorders.debtorno=debtorsmaster.debtorno
		 WHERE salesorders.orddate >='" . FormatDateForSQL($WeekStartDate) . "'
			  AND salesorders.orddate <='" . $_POST['ToDate'] . "'
		 AND salesorders.quotation=0
		 ORDER BY salesorders.orderno";

$ErrMsg = __('An error occurred getting the orders details');
$Result = DB_query($SQL, $ErrMsg);

$PaperSize="Letter_Landscape";
include('includes/PDFStarter.php');
$pdf->addInfo('Title',__('Weekly Orders Report'));
$pdf->addInfo('Subject',__('Orders from') . ' ' . $_POST['FromDate'] . ' ' . __('to') . ' ' . $_POST['ToDate']);
$LineHeight=12;
$PageNumber = 1;
$TotalDiffs = 0;
include('includes/PDFWeeklyOrdersPageHeader.php');
$Col1=2;
$Col2=40;
$Col3=160;
$Col4=210;
$Col5=260;
$Col6=390;
$Col7=450;
$Col8=510;
$Col9=570;
$Col10=610;
$Col11=660;

$pdf->addTextWrap($Left_Margin+$Col1,$YPos,$Col2-$Col1-5,$FontSize,__('Order'), 'left');
$pdf->addTextWrap($Left_Margin+$Col2,$YPos,$Col3-$Col2-5,$FontSize,__('Customer'), 'left');
$pdf->addTextWrap($Left_Margin+$Col3,$YPos,$Col4-$Col3-5,$FontSize,__('Order Date'), 'left');
$pdf->addTextWrap($Left_Margin+$Col4,$YPos,$Col5-$Col4-5,$FontSize,__('Item'), 'left');
$pdf->addTextWrap($Left_Margin+$Col5,$YPos,$Col6-$Col5-5,$FontSize,__('Description'), 'left');
$pdf->addTextWrap($Left_Margin+$Col6,$YPos,$Col7-$Col6-5,$FontSize,__('Quantity'), 'right');
$pdf->addTextWrap($Left_Margin+$Col7,$YPos,$Col8-$Col7-5,$FontSize,__('Sales'), 'right');
$pdf->addTextWrap($Left_Margin+$Col8,$YPos,$Col9-$Col8-5,$FontSize,__('Cost'), 'right');
$pdf->addTextWrap($Left_Margin+$Col9,$YPos,$Col10-$Col9-5,$FontSize,__('GP %'), 'right');
$pdf->addTextWrap($Left_Margin+$Col10,$YPos,$Col11-$Col10-5,$FontSize,__('Status'), 'Left');

$YPos-=$LineHeight;
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);
$YPos-=$LineHeight;

while ($MyRow=DB_fetch_array($Result)){

	if ($MyRow['completed']==1) {
		$Status="Closed";
		$Qty=$MyRow['qtyinvoiced'];
	} else {
		$Qty=$MyRow['quantity'];
		if ($MyRow['qtyinvoiced']==0) {
			$Status= __('Ordered');
		} else {
			$Status= __('Partial');
		}
	}
	$SalesValue=$Qty*$MyRow['unitprice']*(1-$MyRow['discountpercent']);
	$SalesCost=$Qty*$MyRow['standardcost'];
	if ($SalesValue <> 0) {
		$GP=($SalesValue-$SalesCost)/$SalesValue *100;
	} else {
		$GP=0;
	}

	$pdf->addTextWrap($Left_Margin+$Col1,$YPos,$Col2-$Col1-5,$FontSize,$MyRow['orderno'], 'left');
	$pdf->addTextWrap($Left_Margin+$Col2,$YPos,$Col3-$Col2-5,$FontSize,html_entity_decode($MyRow['name'],ENT_QUOTES,'UTF-8'), 'left');
	$pdf->addTextWrap($Left_Margin+$Col3,$YPos,$Col4-$Col3-5,$FontSize,ConvertSQLDate($MyRow['orddate']), 'left');
	$pdf->addTextWrap($Left_Margin+$Col4,$YPos,$Col5-$Col4-5,$FontSize,$MyRow['stkcode'], 'left');
	$pdf->addTextWrap($Left_Margin+$Col5,$YPos,$Col6-$Col5-5,$FontSize,$MyRow['description'], 'left');
	$pdf->addTextWrap($Left_Margin+$Col6,$YPos,$Col7-$Col6-5,$FontSize,locale_number_format($MyRow['quantity'],$_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$pdf->addTextWrap($Left_Margin+$Col7,$YPos,$Col8-$Col7-5,$FontSize,locale_number_format($SalesValue,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$pdf->addTextWrap($Left_Margin+$Col8,$YPos,$Col9-$Col8-5,$FontSize,locale_number_format($SalesCost,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$pdf->addTextWrap($Left_Margin+$Col9,$YPos,$Col10-$Col9-5,$FontSize,locale_number_format($GP,2), 'right');
	$pdf->addTextWrap($Left_Margin+$Col10,$YPos,$Col11-$Col10-5,$FontSize,$Status, 'left');
	if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
		$PageNumber++;
		include('includes/PDFWeeklyOrdersPageHeader.php');
	} /*end of new page header  */
	$YPos -= $LineHeight;
	$TotalSalesValue += $SalesValue;
	$TotalSalesCost  += $SalesCost;
	$TotalSalesVolume  += $MyRow['quantity'];
} //while
if ($TotalSalesValue <> 0) {
	$TotalGP=($TotalSalesValue-$TotalSalesCost)/$TotalSalesValue *100;
} else {
	$TotalGP=0;
}
$pdf->addTextWrap($Left_Margin+$Col2,$YPos,$Col3-$Col2-5,$FontSize,__('Total Order Amounts'), 'left');
$pdf->addTextWrap($Left_Margin+$Col6,$YPos,$Col7-$Col6-5,$FontSize,locale_number_format($TotalSalesVolume,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col7,$YPos,$Col8-$Col7-5,$FontSize,locale_number_format($TotalSalesValue,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col8,$YPos,$Col9-$Col8-5,$FontSize,locale_number_format($TotalSalesCost,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col9,$YPos,$Col10-$Col9-5,$FontSize,locale_number_format($TotalGP,2), 'right');
if ($YPos - (2 *$LineHeight) < $Bottom_Margin){
	$PageNumber++;
	include('includes/PDFWeeklyOrdersPageHeader.php');
} /*end of new page header  */

$YPos -= $LineHeight;
$YPos -= $LineHeight;
$TotalSalesValue=0;
$TotalSalesCost=0;
$TotalSalesVolume=0;
$TotalGP=0;
$SQL = "SELECT 	trandate,
				(price*(1-discountpercent)* (-qty)) as salesvalue,
				(CASE WHEN mbflag='A' THEN 0 ELSE (standardcost * -qty) END) as cost,
				stockmoves.stockid,
				description,
				reference,
				qty,
				transno
			FROM stockmoves
			INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			INNER JOIN custbranch
			ON stockmoves.debtorno=custbranch.debtorno
				AND stockmoves.branchcode=custbranch.branchcode
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND trandate>='" . $_POST['FromDate']  . "'
			AND trandate<='" . $_POST['ToDate']  . "'";

$ErrMsg = __('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
$SalesResult = DB_query($SQL, $ErrMsg);
while ($DaySalesRow=DB_fetch_array($SalesResult)) {
	$TotalSalesValue += $DaySalesRow['salesvalue'];
	$TotalSalesCost += $DaySalesRow['cost'];
	$TotalSalesVolume -= $DaySalesRow['qty'];
}
if ($TotalSalesValue <> 0) {
	$TotalGP=($TotalSalesValue-$TotalSalesCost)/$TotalSalesValue *100;
} else {
	$TotalGP=0;
}
$pdf->addTextWrap($Left_Margin+$Col2,$YPos,$Col3-$Col2-5,$FontSize,__('Monthly Invoiced Total'), 'left');
$pdf->addTextWrap($Left_Margin+$Col6,$YPos,$Col7-$Col6-5,$FontSize,locale_number_format($TotalSalesVolume,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col7,$YPos,$Col8-$Col7-5,$FontSize,locale_number_format($TotalSalesValue,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col8,$YPos,$Col9-$Col8-5,$FontSize,locale_number_format($TotalSalesCost,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
$pdf->addTextWrap($Left_Margin+$Col9,$YPos,$Col10-$Col9-5,$FontSize,locale_number_format($TotalGP,2), 'right');

$FileName=$_SESSION['reports_dir'] .  '/WeeklyOrders.pdf';
$pdf->Output($FileName, 'F');
$pdf->__destruct();
$mail = new PHPMailer(true);
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$Attachment = $mail->getFile($FileName);
SendEmailFromWebERP($SysAdminEmail,
					$Recipients,
					__('Weekly Orders Report'),
					__('Please find the weekly order report'),
					array($FileName)
				);

if($Result){
		$Title = __('Print Weekly Orders');
		include('includes/header.php');
		prnMsg(__('The Weekly Orders report has been mailed'),'success');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();

}else{
		$Title = __('Print Weekly Orders Error');
		include('includes/header.php');
		prnMsg(__('There are errors lead to mails not sent'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();

}
