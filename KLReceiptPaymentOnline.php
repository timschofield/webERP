<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut Receipt Payment Online');
include('includes/header.php');
include('includes/KLDefines.php');

//Get Out if we don't have the data needed to work with
if (!isset($_GET['OrderNo']) OR $_GET['OrderNo']==''){
	prnMsg( _('We need an order number to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['PaymentCode']) OR $_GET['PaymentCode']==''){
	prnMsg( _('We need a payment code to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['CustomerCode']) OR $_GET['CustomerCode']==''){
	prnMsg( _('We need a customer code to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}
if (!isset($_GET['Amount']) OR $_GET['Amount']==''){
	prnMsg( _('We need an amount to process the payment of online order') , 'error');
	include('includes/footer.php');
	exit;
}else{
	$TotalAmount = $_GET['Amount'];
}
if (($_GET['CustomerCode'] == "WEB-KL-IDR") 
	OR ($_GET['CustomerCode'] == "TOKOPEDIA") 
	OR ($_GET['CustomerCode'] == "SHOPEE")){
	$FunctionalExRate = 1;
	$ExRate = 1;
	$Currency = "IDR";
}else{
	prnMsg( _('Script ready to process IDR online orders only') , 'error');
	include('includes/footer.php');
	exit;
}

$BatchNo = GetNextTransNo(12,$db);
$Today = date('Y-m-d');
$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
$Narrative = 'Online ' . $_GET['OrderNo'] . ' ' . $_GET['PaymentCode'];
$BankTransType = "Transfer";

if ($_GET['PaymentCode'] != "MANUAL_MARKETPLACE") {
	// apply the proper payment
	// let's find the accounts, commission, etc to charge to the different payment codes
	$SQLAccounts = "SELECT accounttransfermandiri,
				accountxenditidr,
				accountxenditcomissionidr,
				accountcomissionppn,
				comissionxenditflattransfer,
				comissionxenditflatcc,
				comissionxenditpercentcc
		FROM locations, klonlinepartners
		WHERE locations.onlinepartnercode = klonlinepartners.onlinepartnercode
			AND locations.loccode = '" . OPENCART_DEFAULT_LOCATION . "'";
	$ErrMsg ='Could not get the GL Trasnfers and Commissions for online shop payments because';
	$resultAccounts = DB_query($SQLAccounts,$ErrMsg);
	if(DB_num_rows($resultAccounts) != 0){
		$myrowAccounts = DB_fetch_array($resultAccounts);
		if ($_GET['PaymentCode'] == "bank_mandiri"){
			// bank Mandiri direct transfer has no commissions 
			$GLAccountTransfer = $myrowAccounts['accounttransfermandiri'];
			$GLAccountCommission = "";
			$GLAccountCommissionPPN = "";
			$Commission = 0;
		}elseif ($_GET['PaymentCode'] == "bank_bca"){
			// bank bca direct transfer has no commissions 
			$GLAccountTransfer = $myrowAccounts['accounttransferbca'];
			$GLAccountCommission = "";
			$GLAccountCommissionPPN = "";
			$Commission = 0;
		}elseif ($_GET['PaymentCode'] == "bank_danamon"){
			// bank Mandiri direct transfer has no commissions 
			$GLAccountTransfer = $myrowAccounts['accounttransferdanamon'];
			$GLAccountCommission = "";
			$GLAccountCommissionPPN = "";
			$Commission = 0;
		}elseif  ($_GET['PaymentCode'] == "xenditmandiriva"){
			// Xendit transfer via mandiri has commissions
			$GLAccountTransfer = $myrowAccounts['accountxenditidr'];
			$GLAccountCommission = $myrowAccounts['accountxenditcomissionidr'];
			$GLAccountCommissionPPN = $myrowAccounts['accountcomissionppn'];
			$Commission = round($myrowAccounts['comissionxenditflattransfer'],0);
		}elseif  ($_GET['PaymentCode'] == "xenditcc"){
			// Xendit transfer via CC has commissions
			$GLAccountTransfer = $myrowAccounts['accountxenditidr'];
			$GLAccountCommission = $myrowAccounts['accountxenditcomissionidr'];
			$GLAccountCommissionPPN = $myrowAccounts['accountcomissionppn'];
			$Commission = round(($myrowAccounts['comissionxenditflatcc'] + ($TotalAmount * ($myrowAccounts['comissionxenditpercentcc']/100))) ,0);
		}
		$CommissionPPN = round($Commission * PPN_PERCENT / 100, 0);
		$NetAmount = $TotalAmount - $Commission - $CommissionPPN;
	}

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
				'" . -$TotalAmount . "',
				'" . 0 . "',
				'" . $Narrative. "',
				''
			)";
			
	$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
	$ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$SQL = "UPDATE debtorsmaster
				SET lastpaiddate = '" . $Today . "',
				lastpaid='" . $TotalAmount ."'
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
			'" . $GLAccountTransfer . "',
			'" . $Narrative . "',
			'" . $ExRate . "',
			'" . $FunctionalExRate . "',
			'" . $Today . "',
			'" . $BankTransType . "',
			'" . ($NetAmount * $FunctionalExRate * $ExRate) . "',
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
			'" . $GLAccountTransfer . "',
			'" . $Narrative . "',
			'" . $NetAmount . "'
		)";
	$DbgMsg = _('The SQL that failed to insert the GL transaction from the bank account debit was');
	$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	if ($Commission > 0){
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
				'" . $GLAccountCommission . "',
				'" . $Narrative . "',
				'" . $Commission . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction from the commission was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	}

	if ($CommissionPPN > 0){
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
				'" . $GLAccountCommissionPPN . "',
				'" . $Narrative . "',
				'" . $CommissionPPN . "'
			)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction from the PPN commission was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	}

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
					'" . -$TotalAmount . "'
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
	echo '<tr><td>' . _('GL Bank Account') . ':</td> <td>' . $GLAccountTransfer . '</td></tr>';
	echo '<tr><td>' . _('Customer Code') . ':</td> <td>' . $_GET['CustomerCode'] . '</td></tr>';
	echo '<tr><td>' . _('Total Amount') . ':</td> <td>' . number_format($TotalAmount,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Net Amount') . ':</td> <td>' . number_format($NetAmount,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Commission') . ':</td> <td>' . number_format($Commission,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Commission PPN') . ':</td> <td>' . number_format($CommissionPPN,0) . ' ' . $Currency . '</td></tr>';
	echo '</table>';	//end of table of final show of order
}else{
	// marketplace customers MANUAL_MARKETPLACE, just mark the order as paid
	$result = DB_Txn_Begin();

	$SQL = "UPDATE salesorders
				SET klpaidcash = '" . $TotalAmount . "'
			WHERE salesorders.orderno='" . $_GET['OrderNo'] . "'";
	$DbgMsg = _('The SQL that failed to update the payment flag of the sales order was');
	$ErrMsg = _('Cannot update the payment flag of the sales order because');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$result = DB_Txn_Commit();

	echo '<table class="selection">
			<tr>
				<th colspan=2>' . _('Mark the MarketPlace order as paid') . '
				</th>
			</tr>';

	echo '<tr><td>' . _('Order Number') . ':</td> <td>' . $_GET['OrderNo'] . '</td></tr>';
	echo '<tr><td>' . _('Customer Code') . ':</td> <td>' . $_GET['CustomerCode'] . '</td></tr>';
	echo '</table>';	//end of table of final show of order
}
	
include('includes/footer.php');

?>
