<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut Receipt Payment Online');
include('includes/header.inc');

//Get Out if we don't have the data needed to work with
if (!isset($_GET['OrderNo']) OR $_GET['OrderNo']==''){
	prnMsg( _('We need an order number to process the payment of online order') , 'error');
	include('includes/footer.inc');
	exit;
}
if (!isset($_GET['Bank']) OR $_GET['Bank']==''){
	prnMsg( _('We need a bank code to process the payment of online order') , 'error');
	include('includes/footer.inc');
	exit;
}
if (!isset($_GET['CustomerCode']) OR $_GET['CustomerCode']==''){
	prnMsg( _('We need a customer code to process the payment of online order') , 'error');
	include('includes/footer.inc');
	exit;
}
if (!isset($_GET['Amount']) OR $_GET['Amount']==''){
	prnMsg( _('We need an amount to process the payment of online order') , 'error');
	include('includes/footer.inc');
	exit;
}
if ($_GET['CustomerCode'] == "WEB-KL-IDR"){
	$FunctionalExRate = 1;
	$ExRate = 1;
	$Currency = "IDR";
}else{
	prnMsg( _('Script ready to process IDR online orders only') , 'error');
	include('includes/footer.inc');
	exit;
}

$BatchNo = GetNextTransNo(12,$db);
$Today = date('Y-m-d');
$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
$Narrative = 'Online ' . $_GET['OrderNo'];
$BankTransType = "Transfer";

$result = DB_Txn_Begin();

$SQL = "INSERT INTO debtortrans (transno,
								type,
								debtorno,
								branchcode,
								order_,
								trandate,
								inputdate,
								prd,
								reference,
								tpe,
								rate,
								ovamount,
								ovdiscount,
								invtext,
								salesperson)
		VALUES (
			'" . $BatchNo . "',
			12,
			'" . $_GET['CustomerCode'] . "',
			'',
			'" . $_GET['OrderNo'] . "',
			'" . $Today . "',
			'" . $Today . "',
			'" . $PeriodNo . "',
			'" . $Narrative . "',
			'',
			'" . ($FunctionalExRate*$ExRate) . "',
			'" . -$_GET['Amount'] . "',
			'" . 0 . "',
			'" . $Narrative. "',
			''
		)";
		
$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

$SQL = "UPDATE debtorsmaster
			SET lastpaiddate = '" . $Today . "',
			lastpaid='" . $_GET['Amount'] ."'
		WHERE debtorsmaster.debtorno='" . $_GET['CustomerCode'] . "'";

$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

$SQL="INSERT INTO banktrans (type,
							transno,
							bankact,
							ref,
							exrate,
							functionalexrate,
							transdate,
							banktranstype,
							amount,
							currcode)
	VALUES (
		12,
		'" . $BatchNo . "',
		'" . $_GET['Bank'] . "',
		'" . $Narrative . "',
		'" . $ExRate . "',
		'" . $FunctionalExRate . "',
		'" . $Today . "',
		'" . $BankTransType . "',
		'" . ($_GET['Amount'] * $FunctionalExRate * $ExRate) . "',
		'" . $Currency . "'
	)";
$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
$ErrMsg = _('Cannot insert a bank transaction');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

$SQL="INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
	VALUES (
		12,
		'" . $BatchNo . "',
		'" . $Today . "',
		'" . $PeriodNo . "',
		'" . $_GET['Bank'] . "',
		'" . $Narrative . "',
		'" . $_GET['Amount'] . "'
	)";
$DbgMsg = _('The SQL that failed to insert the GL transaction fro the bank account debit was');
$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
			VALUES (
				12,
				'" . $BatchNo . "',
				'" . $Today . "',
				'" . $PeriodNo . "',
				'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
				'" . $Narrative . "',
				'" . -$_GET['Amount'] . "'
				)";
$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);			

$SQL = "UPDATE salesorders
			SET quotation = '0'
		WHERE salesorders.orderno='" . $_GET['OrderNo'] . "'";
$DbgMsg = _('The SQL that failed to update the quotation flag of the sales order was');
$ErrMsg = _('Cannot update the quotation flag of the sales order because');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

$result = DB_Txn_Commit();

echo '<table class="selection">
		<tr>
			<th colspan=2>' . _('Process of online order payment') . '
			</th>
		</tr>';

echo '<tr><td>' . _('Order Number') . ':</td> <td>' . $_GET['OrderNo'] . '</td></tr>';
echo '<tr><td>' . _('GL Bank Account') . ':</td> <td>' . $_GET['Bank'] . '</td></tr>';
echo '<tr><td>' . _('Customer Code') . ':</td> <td>' . $_GET['CustomerCode'] . '</td></tr>';
echo '<tr><td>' . _('Amount') . ':</td> <td>' . number_format($_GET['Amount'],0) . ' ' . $Currency . '</td></tr>';
echo '</table>';	//end of table of final show of order

	
include('includes/footer.inc');

?>
