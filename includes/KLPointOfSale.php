<?php

/*

*************************************************************************************************
			FUNCTIONS RELATED TO P.O.S. AT SHOPS
*************************************************************************************************
function KapalLautRetailAreaSelection($Debtor, $PaymentMethod, $db)
function KapalLautRetailBankAccountSelection($Debtor, $PaymentMethod, $db)
function KapalLautRetailTagSelection($Debtor, $db)
function webERP_in_test()

*/

function webERP_in_test(){
	return (strpos($_SERVER['PHP_SELF'],"TEST"));
}


/*************************************************************************************************
			FUNCTIONS RELATED TO P.O.S. AT SHOPS
*************************************************************************************************/
function KapalLautRetailAreaSelection($Debtor, $PaymentMethod, $db){
	if($PaymentMethod == PAYMENT_BY_CASH){
		// Cash
		// Needs to be splitted into Cash PT and Cash normal
		// We produce a random number between 0 and 100, to separate them.
		$CashDraw = mt_rand(1,10000)/100;
		if ($CashDraw <= PERCENTAGE_SALES_CASH_TO_PT){
			// PERCENTAGE_SALES_CASH_TO_PT% of cash invoices go to PT
			$Area = "REC";
		}else{
			// 100 - PERCENTAGE_SALES_CASH_TO_PT% of cash invoices go cash others
			$Area = "REZ";
		}
	}elseif($PaymentMethod == PAYMENT_BY_CREDITCARD){
		// Credit Card
		$Area = "RER";
	}else{
		$Area = "";	
		prnMsg(_('Error calculating customer area from payment method. Seek help from the administrator.'),'error');
		include('includes/footer.inc');
		exit;
	}
	return $Area;
}

function KapalLautRetailBankAccountSelection($Debtor, $PaymentMethod, $db){
	if($PaymentMethod == PAYMENT_BY_CASH){
		if($Debtor == "RETAIL66"){
			$Bank = ACCOUNT_CASH_TOK66;
		}elseif($Debtor == "RETAILSA"){
			$Bank = ACCOUNT_CASH_TOKSA;
		}elseif($Debtor == "RETAILKS"){
			$Bank = ACCOUNT_CASH_TOKKS;
		}elseif($Debtor == "RETAILLE"){
			$Bank = ACCOUNT_CASH_TOKLE;
		}elseif($Debtor == "RETAILJC"){
			$Bank = ACCOUNT_CASH_TOKJC;
		}elseif($Debtor == "RETAILBW"){
			$Bank = ACCOUNT_CASH_TOKBW;
		}elseif($Debtor == "RETAILMF"){
			$Bank = ACCOUNT_CASH_TOKMF;
		}elseif($Debtor == "RETAILUB"){
			$Bank = ACCOUNT_CASH_TOKUB;
		}elseif($Debtor == "RETAILSE"){
			$Bank = ACCOUNT_CASH_TOKSE;
		}elseif($Debtor == "RETAILPU"){
			$Bank = ACCOUNT_CASH_TOKPU;
		}elseif($Debtor == "RETAILSU"){
			$Bank = ACCOUNT_CASH_TOKSU;
		}elseif($Debtor == "RETAILOB"){
			$Bank = ACCOUNT_CASH_TOKOB;
		}elseif($Debtor == "RETAILSS"){
			$Bank = ACCOUNT_CASH_TOKSS;
		}elseif($Debtor == "RETAILPA"){
			$Bank = ACCOUNT_CASH_TOKPA;
		}else{
			prnMsg(_('Error calculating Cash Bank Account from the shop. Seek help from the administrator.'),'error');
			include('includes/footer.inc');
			exit;
		}
	}elseif($PaymentMethod == PAYMENT_BY_CREDITCARD){
		// No sense from v2.00 since 2 banks have EDC at shops It is resolved by constants in main code
		$Bank = 0;
	}else{
		prnMsg(_('Error calculating Cash Bank Account. Seek help from the administrator.'),'error');
		include('includes/footer.inc');
		exit;
	}
	return $Bank;
}

function KapalLautRetailTagSelection($Debtor, $db){
	$Tag = 0;
	if($Debtor      == "RETAIL66"){
		$Tag = 2;
	}elseif($Debtor == "RETAILSA"){
		$Tag = 3;
	}elseif($Debtor == "RETAILKS"){
		$Tag = 4;
	}elseif($Debtor == "RETAILLE"){
		$Tag = 5;
	}elseif($Debtor == "RETAILJC"){
		$Tag = 6;
	}elseif($Debtor == "RETAILBW"){
		$Tag = 7;
	}elseif($Debtor == "RETAILKB"){
		$Tag = 8;
	}elseif($Debtor == "RETAILUB"){
		$Tag = 9;
	}elseif($Debtor == "RETAILMF"){
		$Tag = 10;
	}elseif($Debtor == "RETAILSE"){
		$Tag = 11;
	}elseif($Debtor == "RETAILPU"){
		$Tag = 13;
	}elseif($Debtor == "RETAILSU"){
		$Tag = 14;
	}elseif($Debtor == "RETAILOB"){
		$Tag = 15;
	}elseif($Debtor == "RETAILSS"){
		$Tag = 16;
	}elseif($Debtor == "RETAILPA"){
		$Tag = 17;
	}else{
		prnMsg(_('Error calculating accounting TAG from the shop. Seek help from the administrator.'),'error');
		prnMsg($Debtor,'error');
		include('includes/footer.inc');
		exit;
	}
	return $Tag;
}


function AdjustPackagingMovement($StockId, $QtyDelivered, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db){

	if ($QtyDelivered != 0){
		/* Need to get the current standard cost */
		$SQL="SELECT (materialcost + labourcost + overheadcost)
						FROM stockmaster
						WHERE stockmaster.stockid='" . $StockId . "'";
		$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve standard cost');
		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result)==1){
			$Row = DB_fetch_row($Result);
			$StandardCost = $Row[0];
		} else {
			/* There must be some error this should never happen */
			$StandardCost = 0;
		}

		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $StockId . "'
						AND loccode= '" . $_SESSION['UserStockLocation'] . "'";
		$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			/* There must be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		/* Insert movement at packaging used . Strictly not needed as it can be calculated from Stockmoves type 17 but there can be small differences */
		$SQL = "INSERT INTO packagingused (
					orderno,
					fromlocation,
					stockid,
					qty,
					date)
				VALUES ('" . $OrderNo . "',
					'" . $_SESSION['UserStockLocation'] . "',
					'" . $StockId . "',
					'" . $QtyDelivered . "',
					'" . Date('Y-m-d') . "')";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Packaging Used records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the packaging used was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		
		/*	Update locstock at the shop for the qty */
		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $QtyDelivered . "
					WHERE locstock.stockid = '" . $StockId . "'
					AND loccode = '" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		/*	Update stockmoves at the shop for the qty */
		$SQL = "INSERT INTO stockmoves (
					stockid,
					type,
					transno,
					loccode,
					trandate,
					userid,
					debtorno,
					branchcode,
					price,
					prd,
					reference,
					qty,
					discountpercent,
					standardcost,
					newqoh,
					narrative )
				VALUES ('" . $StockId . "',
					17,
					'" . $InvoiceNo . "',
					'" . $_SESSION['UserStockLocation'] . "',
					'" . Date('Y-m-d') . "',
					'" . $_SESSION['UserID'] . "',
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					'" . $_SESSION['Items'.$identifier]->Branch . "',
					'" . 0 . "',
					'" . $PeriodNo . "',
					'" . $OrderNo . "',
					'" . -$QtyDelivered . "',
					'" . 0 . "',
					'" . $StandardCost . "',
					'" . ($QtyOnHandPrior - $QtyDelivered) . "',
					'" . _('Shop Packaging used') . "' )";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Stock movement records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		
		/* Now account for the cost of sale and loss of stock */
		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $StandardCost !=0){
			/*first the cost of sales entry*/
				$AccountCOGL = GetCOGSGLAccount($Area, $StockId, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag)
									VALUES ( 17,
											'" . $InvoiceNo . "',
											'" . Date('Y-m-d') . "',
											'" . $PeriodNo . "',
											'" . $AccountCOGL . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $StockId . " x " . $QtyDelivered . " @ " . $StandardCost . "',
											'" . $StandardCost * $QtyDelivered . "',
											'" . $Tag . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The cost of COGSGLAccount GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			/*now the stock entry*/
			$StockGLCode = GetStockGLCode($StockId,$db);
			$SQL = "INSERT INTO gltrans (	type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag
											)
									VALUES ( 17,
										'" . $InvoiceNo . "',
										'" . Date('Y-m-d') . "',
										'" . $PeriodNo . "',
										'" . $StockGLCode['stockact'] . "',
										'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $StockId . " x " . $QtyDelivered . " @ " . $StandardCost . "',
										'" . (-$StandardCost * $QtyDelivered) . "',
										'" . $Tag . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The stock side of the cost of sales StockGLCode GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		} /* end of if GL and stock integrated and standard cost !=0 */
	}
}

function RecordRetailCustomerInformation($OrderNo, $FirstName, $LastName, $Country, $DateOfBirth, $Email, $Sex, $db){
	// If some field is filled, record it.
	// For some reason, Country = 0 if empty
	if (Is_date($DateOfBirth)){
		$DateOfBirth = FormatDateForSQL($DateOfBirth);
	}else{
		$DateOfBirth = '0000-00-00';
	}
	if (($Country != '0') 
		OR ($DateOfBirth != '0000-00-00') 
		OR ($Email != '') 
		OR ($Sex != '')){ 

		$FirstName = CapitalizeName($FirstName);
		$LastName = CapitalizeName($LastName);
		$Email = mb_strtolower($Email);
		$Today  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		if (($DateOfBirth != '') AND ($DateOfBirth != '0000-00-00') AND ($Today > $DateOfBirth)){
			$Age = date_diff(date_create($DateOfBirth), date_create($Today))->y; 
		}else{
			$Age = 0;
		}

		$SQL = "INSERT INTO klretailcustomers (orderno,
												firstname,
												lastname,
												country,
												date_of_birth,
												age,
												email,
												sex
												)
						VALUES (" . $OrderNo . ",
							'" . $FirstName . "',
							'" . $LastName . "',
							'" . $Country . "',
							'" . $DateOfBirth . "',
							'" . $Age . "',
							'" . $Email . "',
							'" . $Sex . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('The Retail Customer Info could not be inserted because');
		$DbgMsg = _('The following SQL to insert the retail customer data was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	}
}

function AccountPaymentRetail($PaymentMethod,
							$PeriodNo,
							$BankAccount,
							$Area,
							$InvoiceNo,
							$CustomerReference,
							$Location,
							$AmountPaid,
							$BankCommision,
							$NetPayment,
							$Tag,
							$GLAccountBankCommission,
							$ExRate){

	$ReceiptNumber = GetNextTransNo(12,$db);

	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$Description = $Area . 
					 _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
					 ' ' . $Location . 
					 ' CC -> T:' . number_format($AmountPaid,0) . 
					 ' C:' . number_format($BankCommision,0);
	}else{
		$Description = $Area . 
					 _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
					 ' ' . $Location;
	}
	
	$SQL="INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			amount,
			tag)
		VALUES (12,
			'" . $ReceiptNumber . "',
			'" . Date('Y-m-d') . "',
			'" . $PeriodNo . "',
			'" . $BankAccount . "',
			'" . $Description . "',
			'" . $NetPayment/$ExRate . "',
			'" . $Tag . "')";
	$DbgMsg = _('The SQL that failed to insert the NET GL transaction for the bank account debit was');
	$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	// $BankCommision va a la compte $GLAccountBankCommission per comissió de CC
	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$SQL="INSERT INTO gltrans (type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag)
			VALUES (12,
				'" . $ReceiptNumber . "',
				'" . Date('Y-m-d') . "',
				'" . $PeriodNo . "',
				'" . $GLAccountBankCommission . "',
				'" . $Description . "',
				'" . $BankCommision/$ExRate . "',
				'" . $Tag . "')";
		$DbgMsg = _('The SQL that failed to insert the bank Commission GL transaction for the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	}
	/* Now Credit Debtors account with receipt */
	$SQL="INSERT INTO gltrans ( type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			amount,
			tag)
	VALUES (12,
		'" . $ReceiptNumber . "',
		'" . Date('Y-m-d') . "',
		'" . $PeriodNo . "',
		'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
		'" . $Area . 
			 _(' WI:') . $InvoiceNo . 
			 _(' YI:') . $CustomerReference  . 
			 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
			 ' ' . $Location . "',
		'" . -($AmountPaid/$ExRate) . "',
		'" . $Tag . "')";
	$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
	$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	return $ReceiptNumber;
}

function AccountDebtorPayment($ReceiptNumber,
							$PaymentMethod,
							$PeriodNo,
							$BankAccount,
							$Area,
							$InvoiceNo,
							$CustomerReference,
							$Location,
							$AmountPaid,
							$NetPayment,
							$ExRate,
							$DebtorTransID,
							$OrderNo,
							$Currency,
							$DebtorNo){

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(12,$db);
	}

	$Description = $Area . 
				 _(' WI:') . $InvoiceNo . 
				 _(' YI:') . $CustomerReference  . 
				 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
				 ' ' . $Location;

	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$Description = $Description . ' CC';
	}

	//Now need to add the receipt banktrans record
	//First get the account currency that it has been banked into
	$result = DB_query("SELECT rate FROM currencies
						INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
						WHERE bankaccounts.accountcode='" . $BankAccount . "'");
	$myrow = DB_fetch_row($result);
	$BankAccountExRate = $myrow[0];

	//insert the banktrans record in the currency of the bank account
	// RICARD: Only the NET amount (after bank comissions) gets its way to the bank account. :-(((

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
			VALUES (12,
				'" . $ReceiptNumber . "',
				'" . $BankAccount . "',
				'" . $Description . "',
				'" . $ExRate . "',
				'" . $BankAccountExRate . "',
				'" . Date('Y-m-d') . "',
				'3',
				'" . $NetPayment . "',
				'" . $Currency . "')";

	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-001 FAILED Insert banktrans');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	//insert a new debtortrans for the receipt

	$SQL = "INSERT INTO debtortrans (transno,
					type,
					debtorno,
					trandate,
					inputdate,
					prd,
					reference,
					order_,
					rate,
					ovamount,
					alloc,
					invtext)
			VALUES ('" . $ReceiptNumber . "',
				12,
				'" . $DebtorNo . "',
				'" . Date('Y-m-d') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $InvoiceNo . "',
				'" . $OrderNo . "',
				'" . $ExRate . "',
				'" . -$AmountPaid . "',
				'" . -$AmountPaid . "',
				'" . $Description . "')";
	$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-002 FAILED Insert debtortrans');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$ReceiptDebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

	$SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . Date('Y-m-d') . "',
									lastpaid='" . $AmountPaid . "'
							WHERE debtorsmaster.debtorno='" . $DebtorNo . "'";

	$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-003 FAILED Update debtorsmaster');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	//and finally add the allocation record between receipt and invoice

	$SQL = "INSERT INTO custallocns (	amt,
										datealloc,
										transid_allocfrom,
										transid_allocto )
							VALUES  ('" . $AmountPaid . "',
									'" . Date('Y-m-d') . "',
									 '" . $ReceiptDebtorTransID . "',
									 '" . $DebtorTransID . "')";
	$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-004 FAILED Insert custallocns');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);							
	return $ReceiptNumber;
}

function AccountDebtorDiscount($ReceiptNumber,
							$Type,
							$PeriodNo,
							$Area,
							$InvoiceNo,
							$CustomerReference,
							$Location,
							$AmountDiscount,
							$ExRate,
							$OrderNo,
							$DebtorNo){

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(12,$db);
	}

	if ($Type == 'VOUCHER_DISCOUNT'){
		$Description = $Area . 
					 _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
					 ' ' . $Location . ' Voucher/Discount';
	}else{
		$Description = $Area . 
					 _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
					 ' ' . $Location . ' Returned Goods';
	}
	//insert a new debtortrans for the receipt

	$SQL = "INSERT INTO debtortrans (transno,
					type,
					debtorno,
					trandate,
					inputdate,
					prd,
					reference,
					order_,
					rate,
					ovamount,
					ovdiscount,
					alloc,
					invtext)
			VALUES ('" . $ReceiptNumber . "',
				12,
				'" . $DebtorNo . "',
				'" . Date('Y-m-d') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $InvoiceNo . "',
				'" . $OrderNo . "',
				'" . $ExRate . "',
				'" . 0 . "',
				'" . -$AmountDiscount . "',
				'" . 0 . "',
				'" . $Description . "')";
	$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorDiscount ERROR-002 FAILED Insert debtortrans');
	$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	return $ReceiptNumber;
}

?>
