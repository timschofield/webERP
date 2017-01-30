<?php

function webERP_in_test(){
	return (strpos($_SERVER['PHP_SELF'],"TEST"));
}


function zerofill($mStretch, $iLength = 2){
    $sPrintfString = '%0' . (int)$iLength . 's';
    return sprintf($sPrintfString, $mStretch);
}

/*************************************************************************************************
			FUNCTIONS RELATED TO P.O.S. AT SHOPS
*************************************************************************************************/
function KapalLautRetailAreaSelection($PaymentMethod){
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


function AdjustPackagingMovement($StockId, $QtyDelivered, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier, $db){

	if ($QtyDelivered != 0){
		/* Need to get the current standard cost */
		$SQL=	"SELECT (materialcost + labourcost + overheadcost)
				FROM stockmaster
				WHERE stockmaster.stockid='" . $StockId . "'";
		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0010');
		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result)==1){
			$Row = DB_fetch_row($Result);
			$StandardCost = $Row[0];
		} else {
			/* There must be some error this should never happen */
			$StandardCost = 0;
		}

		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL=	"SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $StockId . "'
					AND loccode= '" . $_SESSION['UserStockLocation'] . "'";
		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0020');
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
		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0030');
		$DbgMsg = _('The following SQL to insert the packaging used was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		
		/*	Update locstock at the shop for the qty */
		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $QtyDelivered . "
				WHERE locstock.stockid = '" . $StockId . "'
					AND loccode = '" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0040');
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
					10,
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
		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0050');
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
									VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . Date('Y-m-d') . "',
											'" . $PeriodNo . "',
											'" . $AccountCOGL . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $StockId . " x " . $QtyDelivered . " @ " . $StandardCost . "',
											'" . $StandardCost * $QtyDelivered . "',
											'" . $Tag . "')";

			$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0060');
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
									VALUES ( 10,
										'" . $InvoiceNo . "',
										'" . Date('Y-m-d') . "',
										'" . $PeriodNo . "',
										'" . $StockGLCode['stockact'] . "',
										'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $StockId . " x " . $QtyDelivered . " @ " . $StandardCost . "',
										'" . (-$StandardCost * $QtyDelivered) . "',
										'" . $Tag . "')";

			$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0070');
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

		$SQL = "SELECT *
				FROM klretailcustomers
				WHERE orderno = '" . $OrderNo . "'";
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($Result)==1){
			$Action = "UPDATE";
		} else {
			$Action = "INSERT";
		}
		
		if ($Action == "INSERT"){
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
		}else{
			$SQL = "UPDATE klretailcustomers
					SET firstname = '" . $FirstName . "',
						lastname = '" . $LastName . "',
						country = '" . $Country . "',
						date_of_birth = '" . $DateOfBirth . "',
						age = '" . $Age . "',
						email = '" . $Email . "',
						sex = '" . $Sex . "'
					WHERE orderno = '" . $OrderNo . "'";
		}
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
		$Description = _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'] . 
					 ' CC -> T:' . number_format($AmountPaid,0) . 
					 ' C:' . number_format($BankCommision,0);
	}else{
		$Description = _(' WI:') . $InvoiceNo . 
					 _(' YI:') . $CustomerReference  . 
					 _(' SPG:'). $_SESSION['SalesmanLogin'];
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


function AccountDiscountOnOrderRetail($TypeDiscount,
							$ReceiptNumber,
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

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(10,$db);
	}
	
	$Description = $Area . 
				 _(' WI:') . $InvoiceNo . 
				 ' ' . $TypeDiscount;
	
	$SQL="INSERT INTO gltrans (type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			amount,
			tag)
		VALUES (10,
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

	$Description = _(' WI:') . $InvoiceNo . 
				 _(' YI:') . $CustomerReference  . 
				 _(' SPG:'). $_SESSION['SalesmanLogin'];

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

/********************************************************************************************************/
/***                               PRINT POS RECEIPT FUNCTIONS                                        ***/
/********************************************************************************************************/


function KLPrintReceiptTestWarning($KindOfDoc){
	include('includes/wcpESCPOSCommands.php');
	$TextToPrint = $CharacterFontA;
	if (webERP_in_test()){
		$TextToPrint .= $NewLine .  $CenteredJustified . "TEST ONLY - THIS IS NOT A VALID " . $KindOfDoc . $NewLine;
	}
	return $TextToPrint;
}

function KLPrintNameOfShop(){
	include('includes/wcpESCPOSCommands.php');
	
	// name of shop
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . "Kapal-Laut" . $NewLine . $Emphasized . "Your Essential Jewellery" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . "Blink by Kapal-laut" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . "OUTLET by Kapal-Laut" . $NewLine;
	}else{
		$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . "SHOP NAME NOT FOUND" . $NewLine;
	}
	// shop address
	$TextToPrint .= $CharacterFontB;
	$TextAddress = "";
	if (isset($_SESSION['ShopAddress1'])){
		$TextAddress = $_SESSION['ShopAddress1'];
	}
	if (isset($_SESSION['ShopAddress2'])){
		$TextAddress .= " " . $_SESSION['ShopAddress2'];
	}
	if (isset($_SESSION['ShopAddress3'])){
		$TextAddress .= " " . $_SESSION['ShopAddress3'];
	}
	if (isset($_SESSION['ShopAddress4'])){
		$TextAddress .= " " . $_SESSION['ShopAddress4'];
	}
	if (isset($_SESSION['ShopAddress5'])){
		$TextAddress .= " " . $_SESSION['ShopAddress5'];
	}

	if ($TextAddress != ""){
		$TextToPrint .= $TextAddress . $NewLine;
	}

	return $TextToPrint;
}


function KLPrintReceiptHeader($identifier, $OrderNo){
	
	include('includes/wcpESCPOSCommands.php');

	$TextToPrint = $InitPrinter . $CenteredJustified;

	// name of shop
	$TextToPrint .= KLPrintNameOfShop();
	
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE"). $NewLine . $LeftJustified;
	
	// identification of the sale
	$TextInvoiceNumber = 'Invoice: ' . $_SESSION['Items'.$identifier]->CustRef;
	$TextOrderNumber = 'Order: ' . $OrderNo;
	$TextToPrint .=  DoubleJustified($TextInvoiceNumber, $TextOrderNumber, $LineLenghtCharA, " ");

	$TextDateTime = DisplayDateTime();
	$TextSPG = 'SPG: ' . $_SESSION['SalesmanLogin'];
	$TextToPrint .=  DoubleJustified($TextDateTime, $TextSPG, $LineLenghtCharA, " ");

	$TextToPrint .=  $NewLine . $NewLine;

	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$Total = $Total + $SubTotal;

		$CodeSide = $OrderLine->Quantity . " " . $OrderLine->StockID;

		if (isRing($OrderLine->StockID)){
			$CodeSide .= " " . "Ring";
		}elseif (isToeRing($OrderLine->StockID)){
			$CodeSide .= " " . "Toe Ring";
		}elseif (isBead($OrderLine->StockID)){
			$CodeSide .= " " . "Beads";
		}elseif (isBrooche($OrderLine->StockID)){
			$CodeSide .= " " . "Brooche";
		}elseif (isEarring($OrderLine->StockID)  OR isEarcuff($OrderLine->StockID)) {
			$CodeSide .= " " . "Earrings";
		}elseif (isBracelet($OrderLine->StockID)){
			$CodeSide .= " " . "Bracelet";
		}elseif (isAnklet($OrderLine->StockID)){
			$CodeSide .= " " . "Anklet";
		}elseif (isPendant($OrderLine->StockID)){
			$CodeSide .= " " . "Pendant";
		}elseif (isNecklace($OrderLine->StockID)){
			$CodeSide .= " " . "Necklace";
		}elseif (isFoulard($OrderLine->StockID)){
			$CodeSide .= " " . "Foulard";
		}elseif (isBag($OrderLine->StockID) OR isPlasticBag($OrderLine->StockID)){
			$CodeSide .= " " . "Bag";
		}elseif (isTali($OrderLine->StockID)){
			$CodeSide .= " " . "Cord";
		}

		if(($OrderLine->Quantity > 1) OR ($OrderLine->DiscountPercent != 0)){
			$CodeSide .= " @ " . number_format($OrderLine->Price);
		}
		if($OrderLine->DiscountPercent != 0){
			$CodeSide .= " (-" .number_format($OrderLine->DiscountPercent*100) . "%)";
		}

		$SubTotalSide = number_format($SubTotal);
		$TextToPrint .=  DoubleJustified($CodeSide, $SubTotalSide, $LineLenghtCharA, " ");
	}

	$Goods = $Total / ((100 + PERCENTAGE_PPN) / 100);
	$PPN = $Total-$Goods;
	
	$TextToPrint .= $NewLine . $NewLine . $RightJustified . $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= 'Total: Rp. ' . number_format($Total) . $CharacterFontA. $NewLine;
	$TextToPrint .= 'Goods: Rp. ' . number_format($Goods) . $NewLine;
	$TextToPrint .= 'PPN 10%: Rp.  ' . number_format($PPN) . $NewLine;
	
	return $TextToPrint;
}

function KLPrintReceiptCustomerFooter($identifier, $OrderNo){

	include('includes/wcpESCPOSCommands.php');
	
	$TextToPrint .= $NewLine;

	// Discounted items no refund...
	$DiscountedItems = FALSE;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		if ($OrderLine->DiscountPercent != 0){
			$DiscountedItems = TRUE;
		}
	}
	if ($DiscountedItems){
		$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified;
		$TextToPrint .= "No exchange, no refund, no warranty" . $NewLine . "for discounted or outlet items." . $NewLine . $NewLine;
	}

	// read terms and conditions
	$TextToPrint .= $CharacterFontB . $LeftJustified;
	$TextToPrint .= "This invoice is the only valid proof of purchase. Keep it. ";
	$TextToPrint .= "No refund. Exchange within 7 days with this original invoice, packaging and perfect conditions. We reserve the right to refuse any exchange. ";
	$TextToPrint .= "For  more information on our catalog, promotions, shop locations, job opportunities, news and warranty terms and conditions check our website. ";
	$TextToPrint .= "PT.Bumi Biru Jl. Kesambi No 1 Kerobokan, Bali NPWP:31.780.967.1-906.000" . $NewLine;
	
	// website
	$TextToPrint .= $NewLine . $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified;
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		$TextToPrint .= "www.kapal-laut.com" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		$TextToPrint .= "blink.kapal-laut.com" . $NewLine;
	}else if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		$TextToPrint .= "outlet.kapal-laut.com" . $NewLine;
	}else{
		$TextToPrint .= "SHOP NAME NOT FOUND" . $NewLine;
	}

	// Follow us
	$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified . $NewLine;
	$TextToPrint .= "Follow us on" . $NewLine ;
	$TextToPrint .= "Facebook: KapalLautBali" . $NewLine ;
	$TextToPrint .= "Twitter: @KapalLautBali" . $NewLine ;

	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE");

	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "CUSTOMER COPY" . $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;

}

function KLPrintReceiptShopFooter($identifier, $OrderNo){

	include('includes/wcpESCPOSCommands.php');

	// payment descriptions
	$TextToPrint .= $CharacterFontA. $NewLine;
	if ($_POST['AmountPaidCash'] > 0){
		$TextToPrint .= 'Paid Cash: ' . number_format($_POST['AmountPaidCash'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCDanamon'] > 0){
		$TextToPrint .= 'Paid CC EDC Danamon: ' . number_format($_POST['AmountPaidCCDanamon'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCMandiri'] > 0){
		$TextToPrint .= 'Paid CC EDC Mandiri: ' . number_format($_POST['AmountPaidCCMandiri'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBCA'] > 0){
		$TextToPrint .= 'Paid CC EDC BCA: ' . number_format($_POST['AmountPaidCCBCA'],0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBCA'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BCA: ' . number_format($_POST['AmountPaidAmexBCA'],0) . $NewLine;
	}
	if ($_POST['AmountReturnedGoods'] > 0){
		$TextToPrint .= 'Returned Goods: ' . number_format($_POST['AmountReturnedGoods'],0) . $NewLine;
	}
	if ($_POST['AmountVouchers'] > 0){
		$TextToPrint .= 'Voucher/Discounts: ' . number_format($_POST['AmountVouchers'],0) . $NewLine;
	}
	
	$TextToPrint .= $Emphasized . $LeftJustified. $NewLine;
	$TextToPrint .= "Packaging included";
	$TextToPrint .= $CharacterFontA. $NewLine;

	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_KAPAL_LAUT)){
		if ($_POST['PackagingBox01L'] != 0){
			$TextToPrint .= "KL Box-L: ". $_POST['PackagingBox01L'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01M'] != 0){
			$TextToPrint .= "KL Box-M: ". $_POST['PackagingBox01M'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01S'] != 0){
			$TextToPrint .= "KL Box-S: ". $_POST['PackagingBox01S'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01L'] != 0){
			$TextToPrint .= "KL Pouchbag-L: ". $_POST['PackagingPouchBag01L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01M'] != 0){
			$TextToPrint .= "KL Pouchbag-M: ". $_POST['PackagingPouchBag01M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01S'] != 0){
			$TextToPrint .= "KL Pouchbag-S: ". $_POST['PackagingPouchBag01S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02L'] != 0){
			$TextToPrint .= "KL Shopping Bag-L: ". $_POST['ShoppingBag02L'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02M'] != 0){
			$TextToPrint .= "KL Shopping Bag-L: ". $_POST['ShoppingBag02M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02S'] != 0){
			$TextToPrint .= "KL Shopping Bag-S: ". $_POST['ShoppingBag02S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_BLINK)){
		if ($_POST['BlinkPouchBag03L'] != 0){
			$TextToPrint .= "Blink Pouchbag-L: ". $_POST['BlinkPouchBag03L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03M'] != 0){
			$TextToPrint .= "Blink Pouchbag-M: ". $_POST['BlinkPouchBag03M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03S'] != 0){
			$TextToPrint .= "Blink Pouchbag-S: ". $_POST['BlinkPouchBag03S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04L'] != 0){
			$TextToPrint .= "Blink Shopping Bag-L: ". $_POST['BlinkShoppingBag04L'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04M'] != 0){
			$TextToPrint .= "Blink Shopping Bag-M: ". $_POST['BlinkShoppingBag04M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04S'] != 0){
			$TextToPrint .= "Blink Shopping Bag-S: ". $_POST['BlinkShoppingBag04S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if (ItemInList($_SESSION['UserStockLocation'], LIST_SHOPS_OUTLET)){
		if ($_POST['OutletPouchBag02L'] != 0){
			$TextToPrint .= "Outlet Pouchbag-L: ". $_POST['OutletPouchBag02L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletPouchBag02M'] != 0){
			$TextToPrint .= "Outlet Pouchbag-M: ". $_POST['OutletPouchBag02M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletPouchBag02S'] != 0){
			$TextToPrint .= "Outlet Pouchbag-S: ". $_POST['OutletPouchBag02S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['OutletShoppingBag03M'] != 0){
			$TextToPrint .= "Outlet Shopping Bag-M: ". $_POST['OutletShoppingBag03M'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}

	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE");
	
	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "SHOP COPY" . $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;

}

function DoubleJustified($left, $right, $lenght, $fillchar){
	include('includes/wcpESCPOSCommands.php');
	return str_pad($left, $lenght - strlen($right), $fillchar) . $right . $NewLine;
}


?>
