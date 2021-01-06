<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut Receipt Payment Online');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

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
	OR ($_GET['CustomerCode'] == "WEB-WH-IDR") 
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

if (($_GET['CustomerCode'] == "WEB-WH-IDR") ){
	// it is a wholesale online order customer, so processed by PTADU
	$OnlinePartner = "ONLINEPTAD";
}else{
	// it is retail in iDR, so it goes to PTBB
	$OnlinePartner = "ONLINEPTBB";
}

if ($_GET['PaymentCode'] != "MANUAL_MARKETPLACE") {
	// apply the proper payment
	// let's find the accounts, commission, etc to charge to the different payment codes
	$SQLAccounts = "SELECT accounttransfermandiri,
				accounttransferbca,
				accounttransferdanamon,
				accountxenditidr,
				accountxenditcomissionidr,
				accountcomissionppn,
				comissionxenditflattransfer,
				comissionxenditflatcc,
				comissionxenditpercentcc
			FROM klonlinepartners
			WHERE klonlinepartners.onlinepartnercode = '" . $OnlinePartner . "'";
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
			// bank Danamon direct transfer has no commissions THIS IS FOR WHOLESALE ONLY, GOES TO PTADU
			$GLAccountTransfer = $myrowAccounts['accounttransferdanamon'];
			$GLAccountCommission = "";
			$GLAccountCommissionPPN = "";
			$Commission = 0;
		}elseif  ($_GET['PaymentCode'] == "snap"){
			// MidTrans has commissions but we can't integrate them. We account full order, later manually we process commissions
			$GLAccountTransfer = MIDTRANS_BANK_GL_ACCOUNT;
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
		}elseif  ($_GET['PaymentCode'] == "tokopedia"){
			// Tokopedia payments  has commissions
			$GLAccountTransfer = TOKOPEDIA_BANK_GL_ACCOUNT;
			$GLAccountCommission = TOKOPEDIA_COMMISSION_GL_ACCOUNT;
			$GLAccountCommissionPPN = ACCOUNT_PPN_BB;
			$Commission = CalculateCommissionTokopedia($_GET['CustomerCode'], $_GET['OrderNo'], $TotalAmount);
		}elseif  ($_GET['PaymentCode'] == "shopee"){
			// Shopee payments  has commissions
			$GLAccountTransfer = SHOPEE_BANK_GL_ACCOUNT;
			$GLAccountCommission = SHOPEE_COMMISSION_GL_ACCOUNT;
			$GLAccountCommissionPPN = ACCOUNT_PPN_BB;
			$Commission = CalculateCommissionShopee($_GET['CustomerCode'], $_GET['OrderNo'], $TotalAmount);
		}
		$CommissionPPN = round($Commission * PPN_PERCENT / 100, 0);
		$NetAmount = $TotalAmount - $Commission - $CommissionPPN;
	}

ProcessPaymentOnlineOrder($_GET['OrderNo'], 
						$_GET['PaymentCode'], 
						$_GET['CustomerCode'], 
						$Currency,
						$FunctionalExRate, 
						$ExRate, 
						$TotalAmount,
						$NetAmount,
						$Commission,
						$CommissionPPN,
						$GLAccountTransfer,
						$GLAccountCommission,
						$GLAccountCommissionPPN);
								
	echo '<table class="selection">
			<tr>
				<th colspan=2>' . _('Process of online order payment') . '
				</th>
			</tr>';

	echo '<tr><td>' . _('Order Number') . ':</td> <td>' . $_GET['OrderNo'] . '</td></tr>';
	echo '<tr><td>' . _('GL Bank Account') . ':</td> <td>' . $GLAccountTransfer . '</td></tr>';
	echo '<tr><td>' . _('Customer Code') . ':</td> <td>' . $_GET['CustomerCode'] . '</td></tr>';
	echo '<tr><td>' . _('Total Amount') . ':</td> <td>' . number_format($TotalAmount,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Net Amount Received') . ':</td> <td>' . number_format($NetAmount,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Gross Commission') . ':</td> <td>' . number_format($Commission + $CommissionPPN,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Net Commission') . ':</td> <td>' . number_format($Commission,0) . ' ' . $Currency . '</td></tr>';
	echo '<tr><td>' . _('Commission PPN') . ':</td> <td>' . number_format($CommissionPPN,0) . ' ' . $Currency . '</td></tr>';
	echo '</table>';	//end of table of final show of order
}else{
	// marketplace customers MANUAL_MARKETPLACE, just mark the order as paid
	// accounting has been done manually
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function CalculateCommissionTokopedia($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "TOKOPEDIA"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = tokopedia", "error");
		include('includes/footer.php');
		exit;
	}
	// 1% from all order for Tokopedia
	$CommissionTPGlobal = round($TotalAmount * TOKOPEDIA_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN

	// we need to pay comething to Tokopedia if shipper si SI-CEPAT, as it means free shipping for the customer, so we pay something
	$SQL = "SELECT salesorders.shipvia
		FROM salesorders 
		WHERE salesorders.orderno = '" . $OrderNo . "' ";			
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Shipper = $myrow['shipvia'];
		$CommissionTPFreeShipping = 0;
		if ($Shipper == '12'){
			// if shipper is 12 = GRATIS ONGKIR TOKOPEDIA... then we shipped it via free shipping, we must pay 
			// 2,5% from every item with a max 0f 10.000 for Tokopedia as cost of shipment
			$SQL = "SELECT salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent
				FROM salesorderdetails
				WHERE salesorderdetails.orderno = '" . $OrderNo . "' ";			
			$result = DB_query($SQL);
			while ($myrow = DB_fetch_array($result)) {
				$ItemPrice = $myrow['unitprice']*(1-$myrow['discountpercent']);
				$CommissionItem = min(round($ItemPrice * TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_PERCENT /100 ,0), TOKOPEDIA_COMMISSION_FREE_SHIPPING_PER_ITEM_MAXIMUM); 
				$CommissionTPFreeShipping += $CommissionItem * $myrow['qtyinvoiced']; // this commission still has PPN
			}
		}
	}else{
		prnMsg("ERROR: Could not extract shipper information for order = " . $OrderNo, "error");
		include('includes/footer.php');
		exit;
	}
	
	$Commission = $CommissionTPGlobal + $CommissionTPFreeShipping; // this commission still has PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}

function CalculateCommissionShopee($CustomerCode, $OrderNo, $TotalAmount){
	if ($CustomerCode != "SHOPEE"){
		prnMsg("ERROR: Customer code = " . $CustomerCode . " and Payment Code = shopee", "error");
		include('includes/footer.php');
		exit;
	}
	// 1,5% from all order for Shopee
	$Commission = round($TotalAmount * SHOPEE_COMMISSION_PERCENT /100 ,0); // this commission still includes PPN
	$Commission = round($Commission /((100 + PPN_PERCENT)/100) ,0); // this commision already net
	return $Commission;
}


?>
