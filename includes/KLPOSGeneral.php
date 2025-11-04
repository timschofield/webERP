<?php

/**************************************************************************************************************
FUNCTION LIST (in alphabetical order):
- AccountDebtorDiscount: Records a discount for a debtor in the system
- AccountDebtorPayment: Records a payment from a debtor in the system
- AccountDiscountOnOrderRetail: Records a discount on a retail order
- AccountPaymentRetail: Records a payment for a retail transaction
- AdjustPackagingMovement: Manages packaging inventory movements when products are sold
- DoubleJustified: Helper function for receipt printing to align text left and right
- GetFilenameFromPOSIdentifier: Converts a POS identifier to a filename
- GetItemPackagingDescription: Retrieves packaging description for a product
- GetPOSIdentifier: Generates a unique identifier for POS transactions
- InsertItemSoldIntoSalesAnalysis: Records sales data in the sales analysis tables
- KapalLautRetailAreaSelection: Determines the sales area based on payment method
- KLPrintNameOfShop: Prints the shop name on receipts
- KLPrintReceiptCustomerFooter: Generates the customer copy footer for receipts
- KLPrintReceiptHeader: Generates the header for receipts
- KLPrintReceiptShopFooter: Generates the shop copy footer for receipts
- KLPrintReceiptTestWarning: Adds a test warning on receipts when in test mode
- KLPrintReturnTransferToKantor: Generates the text for a receipt for a return transfer to the main office
- RecordRetailCustomerInformation: Stores customer information from retail sales
- zerofill: Pads a number with leading zeros
**************************************************************************************************************/

/**************************************************************************************************************
* Brief description: Pads a number with leading zeros
* Parameters:
*   $MStretch - The number or string to pad
*   $ILength - The desired length of the resulting string (default: 2)
* Returns: String padded with leading zeros
**************************************************************************************************************/
function zerofill($MStretch, $ILength = 2){
	$SPrintfString = '%0' . (int)$ILength . 's';
	return sprintf($SPrintfString, $MStretch);
}

/**************************************************************************************************************
* Brief description: Determines the sales area based on payment method
* Parameters:
*   $PaymentMethod - The method of payment (cash or credit card)
* Returns: The appropriate sales area code
**************************************************************************************************************/
function KapalLautRetailAreaSelection($PaymentMethod){
	if ($PaymentMethod == PAYMENT_BY_CASH){
		if ($_SESSION['CashSalesReported'] <= 0){
			// all cash sales go to Others
			$Area = $_SESSION['AreaSalesCashOthers'];
		} elseif ($_SESSION['CashSalesReported'] >= 100){
			// all cash sales go to cash PTADU, PTBB or PTSMH
			$Area = $_SESSION['AreaSalesCash'];
		} else {
			// Needs to be splitted into Cash official and Cash others
			// We produce a random number between 0 and 100, to separate them.
			$CashDraw = mt_rand(1, 10000) / 100;
			if ($CashDraw <= $_SESSION['CashSalesReported']){
				$Area = $_SESSION['AreaSalesCash'];
			} else {
				$Area = $_SESSION['AreaSalesCashOthers'];
			}
		}
	} elseif ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		// Credit Card
		$Area = $_SESSION['AreaSalesCreditCard'];
	} else {
		$Area = "";
		prnMsg(__('Error calculating customer area from payment method. Seek help from the administrator.'), 'error');
		include('includes/footer.php');
		exit();
	}
	return $Area;
}

/**************************************************************************************************************
* Brief description: Manages packaging inventory movements when products are sold
* Parameters:
*   $StockID - The stock ID of the packaging item
*   $QtyDelivered - The quantity of packaging used
*   $InvoiceNo - The invoice number
*   $PeriodNo - The accounting period
*   $OrderNo - The order number
*   $Area - The sales area
*   $Tag - The GL tag
*   $Identifier - The POS transaction identifier
* Returns: None
**************************************************************************************************************/
function AdjustPackagingMovement($StockID, $QtyDelivered, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $Identifier){

	if ($QtyDelivered != 0){
		/* Need to get the current standard cost */
		$SQL = "SELECT actualcost
				FROM stockmaster
				WHERE stockmaster.stockid='" . $StockID . "'";
		$ErrMsg = __('ERROR: Contact the office!!! -> AdjustPackagingMovement-0010');
		$Result = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($Result) == 1){
			$Row = DB_fetch_row($Result);
			$StandardCost = $Row[0];
		} else {
			/* There must be some error this should never happen */
			$StandardCost = 0;
		}

		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $StockID . "'
					AND loccode= '" . $_SESSION['UserStockLocation'] . "'";
		$ErrMsg = __('ERROR: Contact the office!!! -> AdjustPackagingMovement-0020');
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result) == 1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			/* There must be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		/* Insert movement at packaging used . Strictly not needed as it can be calculated from Stockmoves type 17 
		but there can be small differences */
		$SQL = "INSERT INTO packagingused (
					orderno,
					fromlocation,
					stockid,
					qty,
					date)
				VALUES ('" . $OrderNo . "',
					'" . $_SESSION['UserStockLocation'] . "',
					'" . $StockID . "',
					'" . $QtyDelivered . "',
					CURRENT_DATE)";
		$ErrMsg = __('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0030');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		
		/*	Update locstock at the shop for the qty */
		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $QtyDelivered . "
				WHERE locstock.stockid = '" . $StockID . "'
					AND loccode = '" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = __('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0040');
		$Result = DB_query($SQL, $ErrMsg, '', true);

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
				VALUES ('" . $StockID . "',
					10,
					'" . $InvoiceNo . "',
					'" . $_SESSION['UserStockLocation'] . "',
					CURRENT_DATE,
					'" . $_SESSION['UserID'] . "',
					'" . (isset($_SESSION['Items'.$Identifier]) ? $_SESSION['Items'.$Identifier]->DebtorNo : '') . "',
					'" . (isset($_SESSION['Items'.$Identifier]) ? $_SESSION['Items'.$Identifier]->Branch : '') . "',
					'" . 0 . "',
					'" . $PeriodNo . "',
					'" . $OrderNo . "',
					'" . -$QtyDelivered . "',
					'" . 0 . "',
					'" . $StandardCost . "',
					'" . ($QtyOnHandPrior - $QtyDelivered) . "',
					'" . __('Shop Packaging used') . "' )";
		$ErrMsg = __('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0050');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		
		/* Now account for the cost of sale and loss of stock */
		if ($StandardCost != 0){
			/*first the cost of sales entry*/
			$AccountCOGL = GetCOGSGLAccount($Area, $StockID, $_SESSION['Items'.$Identifier]->DefaultSalesType);
			InsertIntoGLTrans("10", 
							$InvoiceNo, 
							Date('Y-m-d'),
							$PeriodNo,
							$AccountCOGL,
							$StockID . " x " . $QtyDelivered . " @ " . round($StandardCost),
							round($StandardCost * $QtyDelivered),
							$Tag,
							'ERROR-POS-00101'
							);

			/*now the stock entry*/
			$StockGLCode = GetStockGLCode($StockID);
			InsertIntoGLTrans("10", 
							$InvoiceNo, 
							Date('Y-m-d'),
							$PeriodNo,
							$StockGLCode['stockact'],
							$StockID . " x " . $QtyDelivered . " @ " . round($StandardCost),
							round(-$StandardCost * $QtyDelivered),
							$Tag,
							'ERROR-POS-00102'
							);
		} /* end of if GL and stock integrated and standard cost !=0 */
	}
}

/**************************************************************************************************************
* Brief description: Records sales data in the sales analysis tables
* Parameters:
*   $Area - The sales area
*   $SalesType - The type of sale
*   $PeriodNo - The accounting period
*   $DebtorNo - The customer account number
*   $DebtorBranch - The customer branch
*   $StockID - The product ID
*   $Price - The unit price
*   $Quantity - The quantity sold
*   $ExRate - The exchange rate
*   $StandardCost - The standard cost of the product
*   $DiscountPercent - The discount percentage applied
* Returns: None
**************************************************************************************************************/
function InsertItemSoldIntoSalesAnalysis($Area,
										$SalesType,
										$PeriodNo,
										$DebtorNo,
										$DebtorBranch,
										$StockID,
										$Price,
										$Quantity,
										$ExRate,
										$StandardCost,
										$DiscountPercent
										){

	/* Query optimized by Gemini 2.5 on 24/10/2025
	 * Also proposed the creation of index. 
	 * CREATE INDEX salesanalysis_composite_idx ON salesanalysis
	 *  (area, typeabbrev, periodno, budgetoractual, cust, custbranch, stockid, stkcategory, salesperson);
	 * As this query is performed every time an item is sold, it makes sense to optimize it.
	*/
	$SQL = "SELECT COUNT(*),
				sa.stockid,
				sa.stkcategory,
				sa.cust,
				sa.custbranch,
				sa.area,
				sa.periodno,
				sa.typeabbrev,
				sa.salesperson
			FROM salesanalysis AS sa
			INNER JOIN stockmaster AS sm
				ON sa.stkcategory = sm.categoryid
					AND sa.stockid = sm.stockid
			INNER JOIN custbranch AS cb
				ON sa.cust = cb.debtorno
					AND sa.custbranch = cb.branchcode
					AND sa.salesperson = cb.salesman
			WHERE sa.area = '" . $Area ."'
				AND sa.typeabbrev = '" . $SalesType . "'
				AND sa.periodno = '" . $PeriodNo . "'
				AND sa.cust " . LIKE . " '" . $DebtorNo . "'
				AND sa.custbranch " . LIKE . " '" . $DebtorBranch . "'
				AND sa.stockid " . LIKE . " '" . $StockID . "'
				AND sa.budgetoractual = 1
			GROUP BY 
				sa.stockid,
				sa.stkcategory,
				sa.cust,
				sa.custbranch,
				sa.area,
				sa.periodno,
				sa.typeabbrev,
				sa.salesperson";

	$ErrMsg = __('The count of existing Sales analysis records could not run because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	$MyRow = DB_fetch_row($Result);

	// Added division-by-zero protection for $ExRate
	if ($ExRate == 0) {
		$ExRate = 1;
	}

	if ($MyRow[0] > 0){  /*Update the existing record that already exists */
		
		$SQL = "UPDATE salesanalysis
				SET amt = amt + " . ($Price * $Quantity / $ExRate) . ",
					cost = cost + " . ($StandardCost * $Quantity) . ",
					qty = qty + " . $Quantity . ",
					disc = disc + " . ($DiscountPercent * $Price * $Quantity / $ExRate) . "
				WHERE salesanalysis.area='" . $MyRow[5] . "'
					AND salesanalysis.salesperson='" . $MyRow[8] . "'
					AND typeabbrev ='" . $SalesType . "'
					AND periodno = '" . $PeriodNo . "'
					AND cust " . LIKE . " '" . $DebtorNo . "'
					AND custbranch " . LIKE . " '" . $DebtorBranch . "'
					AND stockid " . LIKE . " '" . $StockID . "'
					AND salesanalysis.stkcategory ='" . $MyRow[2] . "'
					AND budgetoractual = 1";

	} else { /* insert a new sales analysis record */
		
		$SQL = "INSERT INTO salesanalysis (	typeabbrev,
											periodno,
											amt,
											cost,
											cust,
											custbranch,
											qty,
											disc,
											stockid,
											area,
											budgetoractual,
											salesperson,
											stkcategory	)
			SELECT '" . $SalesType . "',
				'" . $PeriodNo . "',
				'" . ($Price * $Quantity / $ExRate) . "',
				'" . ($StandardCost * $Quantity) . "',
				'" . $DebtorNo . "',
				'" . $DebtorBranch . "',
				'" . $Quantity . "',
				'" . ($DiscountPercent * $Price * $Quantity / $ExRate) . "',
				'" . $StockID . "',
				'" . $Area . "',
				1,
				custbranch.salesman,
				stockmaster.categoryid
			FROM stockmaster,
				custbranch
			WHERE stockmaster.stockid = '" . $StockID . "'
				AND custbranch.debtorno = '" . $DebtorNo . "'
				AND custbranch.branchcode='" . $DebtorBranch . "'";
	}

	$ErrMsg = __('Sales analysis record could not be added or updated because');
	DB_query($SQL, $ErrMsg, '', true);
}

/**************************************************************************************************************
* Brief description: Stores customer information from retail sales
* Parameters:
*   $OrderNo - The order number
*   $FirstName - Customer's first name
*   $LastName - Customer's last name
*   $Country - Customer's country
*   $DateOfBirth - Customer's date of birth
*   $Email - Customer's email address
*   $Sex - Customer's gender
* Returns: None
**************************************************************************************************************/
function RecordRetailCustomerInformation($OrderNo, $FirstName, $LastName, $Country, $DateOfBirth, $Email, $Sex){
	// If some field is filled, record it.
	// For some reason, Country = 0 if empty
	if (Is_date($DateOfBirth)){
		$DateOfBirth = FormatDateForSQL($DateOfBirth);
	} else {
		$DateOfBirth = '1000-01-01';
	}
	if (($Country != '0') 
		OR ($DateOfBirth != '1000-01-01') 
		OR ($Email != '') 
		OR ($Sex != '')){ 

		$FirstName = CapitalizeName($FirstName);
		$LastName = CapitalizeName($LastName);
		$Email = mb_strtolower($Email);
		$Today = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		if (($DateOfBirth != '') AND ($DateOfBirth != '1000-01-01') AND ($Today > $DateOfBirth)){
			$Age = date_diff(date_create($DateOfBirth), date_create($Today))->y; 
		} else {
			$Age = 0;
		}

		$SQL = "SELECT *
				FROM klretailcustomers
				WHERE orderno = '" . $OrderNo . "'";
		$Result = DB_query($SQL, '', '', true);
		
		if (DB_num_rows($Result) == 1){
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

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . 
					__('The Retail Customer Info could not be inserted because');
		} else {
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
		DB_query($SQL, $ErrMsg, '', true);
	}
}

/**************************************************************************************************************
* Brief description: Records a payment for a retail transaction
* Parameters:
*   $PaymentMethod - The method of payment
*   $BankAccount - The bank account to record payment to
*   $InvoiceNo - The invoice number
*   $CustomerReference - The customer reference
*   $AmountPaid - The amount paid by the customer
*   $BankCommision - The bank commission on the transaction
*   $NetPayment - The net amount after commission
*   $Tag - The GL tag
*   $GLAccountBankCommission - The GL account for bank commissions
*   $DaysDelaySettlement - Days to delay settlement
*   $ExRate - The exchange rate
* Returns: The receipt number
**************************************************************************************************************/
function AccountPaymentRetail($PaymentMethod,
							$BankAccount,
							$InvoiceNo,
							$CustomerReference,
							$AmountPaid,
							$BankCommision,
							$NetPayment,
							$Tag,
							$GLAccountBankCommission,
							$DaysDelaySettlement,
							$ExRate){

	$ReceiptNumber = GetNextTransNo(12);
	$SettlementDate = date('Y-m-d', strtotime("+" . (int)$DaysDelaySettlement . " days"));
	$SettlementPeriodNo = GetPeriod(ConvertSQLDate($SettlementDate));

	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$Description = $CustomerReference . 
					' (' . $InvoiceNo . 
					') SPG:' . $_SESSION['SalesmanLogin'] . 
					' CC -> T:' . number_format($AmountPaid, 0) . 
					' C:' . number_format($BankCommision, 0);
	} else {
		$Description = $CustomerReference . 
					' (' . $InvoiceNo . 
					') SPG:' . $_SESSION['SalesmanLogin'];
	}
	
	// Added division-by-zero protection for $ExRate
	if ($ExRate == 0) {
		$ExRate = 1;
	}

	InsertIntoGLTrans("12", 
					$ReceiptNumber, 
					$SettlementDate,
					$SettlementPeriodNo,
					$BankAccount,
					$Description,
					round($NetPayment / $ExRate),
					$Tag,
					'ERROR-POS-00103'
					);

	// $BankCommision va a la compte $GLAccountBankCommission per comissió de CC
	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		InsertIntoGLTrans("12", 
						$ReceiptNumber, 
						$SettlementDate,
						$SettlementPeriodNo,
						$GLAccountBankCommission,
						$Description,
						round($BankCommision / $ExRate),
						$Tag,
						'ERROR-POS-00104'
						);
	}
	/* Now Credit Debtors account with receipt */
	InsertIntoGLTrans("12", 
					$ReceiptNumber, 
					$SettlementDate,
					$SettlementPeriodNo,
					$_SESSION['AccountPOSReceivable'],
					$Description,
					round(-$AmountPaid / $ExRate),
					$Tag,
					'ERROR-POS-00105'
					);
	return $ReceiptNumber;
}

/**************************************************************************************************************
* Brief description: Records a discount on a retail order
* Parameters:
*   $TypeDiscount - The type of discount
*   $ReceiptNumber - The receipt number (optional)
*   $PeriodNo - The accounting period
*   $BankAccount - The bank account
*   $InvoiceNo - The invoice number
*   $CustomerReference - The customer reference
*   $NetPayment - The net amount
*   $Tag - The GL tag
*   $ExRate - The exchange rate
* Returns: The receipt number
**************************************************************************************************************/
function AccountDiscountOnOrderRetail($TypeDiscount,
							$ReceiptNumber,
							$PeriodNo,
							$BankAccount,
							$InvoiceNo,
							$CustomerReference,
							$NetPayment,
							$Tag,
							$ExRate){

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(10);
	}
	
	$Description = $CustomerReference . 
					' (' . $InvoiceNo . 
					') ' . $TypeDiscount;
					
	// Added division-by-zero protection for $ExRate
	if ($ExRate == 0) {
		$ExRate = 1;
	}

	InsertIntoGLTrans("10", 
					$ReceiptNumber, 
					Date('Y-m-d'),
					$PeriodNo,
					$BankAccount,
					$Description,
					round($NetPayment / $ExRate),
					$Tag,
					'ERROR-POS-00106'
					);
	return $ReceiptNumber;
}

/**************************************************************************************************************
* Brief description: Records a payment from a debtor in the system
* Parameters:
*   $ReceiptNumber - The receipt number (optional)
*   $PaymentMethod - The payment method
*   $PeriodNo - The accounting period
*   $BankAccount - The bank account
*   $InvoiceNo - The invoice number
*   $CustomerReference - The customer reference
*   $AmountPaid - The amount paid
*   $NetPayment - The net payment amount
*   $ExRate - The exchange rate
*   $DebtorTransID - The debtor transaction ID
*   $OrderNo - The order number
*   $Currency - The currency
*   $DebtorNo - The debtor number
* Returns: The receipt number
**************************************************************************************************************/
function AccountDebtorPayment($ReceiptNumber,
							$PaymentMethod,
							$PeriodNo,
							$BankAccount,
							$InvoiceNo,
							$CustomerReference,
							$AmountPaid,
							$NetPayment,
							$ExRate,
							$DebtorTransID,
							$OrderNo,
							$Currency,
							$DebtorNo){

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(12);
	}

	$Description = $CustomerReference . 
					' (' . $InvoiceNo . 
					') SPG:' . $_SESSION['SalesmanLogin'];
						
	
	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$Description = $Description . ' CC';
	}

	//Now need to add the receipt banktrans record
	//First get the account currency that it has been banked into
	$Result = DB_query("SELECT rate FROM currencies
						INNER JOIN bankaccounts
							ON currencies.currabrev = bankaccounts.currcode
						WHERE bankaccounts.accountcode = '" . $BankAccount . "'");
	$MyRow = DB_fetch_row($Result);
	$BankAccountExRate = $MyRow[0];

	//insert the banktrans record in the currency of the bank account
	// RICARD: Only the NET amount (after bank comissions) gets its way to the bank account. :-(((

	$SQL = "INSERT INTO banktrans (type,
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
				CURRENT_DATE,
				'3',
				'" . $NetPayment . "',
				'" . $Currency . "')";

	$ErrMsg = __('Report to Office: AccountDebtorPayment ERROR-001 FAILED Insert banktrans');
	$Result = DB_query($SQL, $ErrMsg, '', true);

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
				'" . date('Y-m-d H-i-s') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $CustomerReference . "',
				'" . $OrderNo . "',
				'" . $ExRate . "',
				'" . -$AmountPaid . "',
				'" . -$AmountPaid . "',
				'" . $Description . "')";
	$ErrMsg = __('Report to Office: AccountDebtorPayment ERROR-002 FAILED Insert debtortrans');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	$ReceiptDebtorTransID = DB_Last_Insert_ID('debtortrans', 'id');

	$SQL = "UPDATE debtorsmaster SET lastpaiddate = CURRENT_DATE,
									lastpaid='" . $AmountPaid . "'
							WHERE debtorsmaster.debtorno='" . $DebtorNo . "'";

	$ErrMsg = __('Report to Office: AccountDebtorPayment ERROR-003 FAILED Update debtorsmaster');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	//and finally add the allocation record between receipt and invoice

	$SQL = "INSERT INTO custallocns (	amt,
										datealloc,
										transid_allocfrom,
										transid_allocto )
							VALUES  ('" . $AmountPaid . "',
									CURRENT_DATE,
									 '" . $ReceiptDebtorTransID . "',
									 '" . $DebtorTransID . "')";
	$ErrMsg = __('Report to Office: AccountDebtorPayment ERROR-004 FAILED Insert custallocns');
	$Result = DB_query($SQL, $ErrMsg, '', true);							
	return $ReceiptNumber;
}

/**************************************************************************************************************
* Brief description: Records a discount for a debtor in the system
* Parameters:
*   $ReceiptNumber - The receipt number (optional)
*   $Type - The type of discount
*   $PeriodNo - The accounting period
*   $InvoiceNo - The invoice number
*   $CustomerReference - The customer reference
*   $AmountDiscount - The discount amount
*   $ExRate - The exchange rate
*   $OrderNo - The order number
*   $DebtorNo - The debtor number
* Returns: The receipt number
**************************************************************************************************************/
function AccountDebtorDiscount($ReceiptNumber,
							$Type,
							$PeriodNo,
							$InvoiceNo,
							$CustomerReference,
							$AmountDiscount,
							$ExRate,
							$OrderNo,
							$DebtorNo){

	if (!isset($ReceiptNumber)){
		$ReceiptNumber = GetNextTransNo(12);
	}

	if ($Type == 'VOUCHER_DISCOUNT'){
		$Description = $CustomerReference . 
						' (' . $InvoiceNo . 
						') SPG:' . $_SESSION['SalesmanLogin'] . 
						' ' . ' Voucher/Discount';
	} else {
		$Description = $CustomerReference . 
						' (' . $InvoiceNo . 
						') SPG:' . $_SESSION['SalesmanLogin'] . 
						' ' . ' Returned Goods';
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
				'" . date('Y-m-d H-i-s') . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $CustomerReference . "',
				'" . $OrderNo . "',
				'" . $ExRate . "',
				'" . 0 . "',
				'" . -$AmountDiscount . "',
				'" . 0 . "',
				'" . $Description . "')";
	$ErrMsg = __('Report to Office: AccountDebtorDiscount ERROR-002 FAILED Insert debtortrans');
	DB_query($SQL, $ErrMsg, '', true);

	return $ReceiptNumber;
}
	
/********************************************************************************************************/
/***                               PRINT POS RECEIPT FUNCTIONS                                        ***/
/********************************************************************************************************/

/**************************************************************************************************************
* Brief description: Generates a unique identifier for POS transactions
* Parameters: None
* Returns: A unique identifier string
**************************************************************************************************************/
function GetPOSIdentifier(){
	$Id = date('U') . zerofill(mt_rand(0, 999999), 6);
	return $Id;
}

/**************************************************************************************************************
* Brief description: Converts a POS identifier to a filename
* Parameters:
*   $Id - The POS identifier
* Returns: The filename for the POS document
**************************************************************************************************************/
function GetFilenameFromPOSIdentifier($Id){
	$F = 'includes/WebClientPrint/wcpcache/' . $Id . '.pos';
	return $F;
}

/**************************************************************************************************************
* Brief description: Adds a test warning on receipts when in test mode
* Parameters:
*   $KindOfDoc - The type of document (e.g., "INVOICE")
* Returns: The text to print for the warning
**************************************************************************************************************/
function KLPrintReceiptTestWarning($KindOfDoc){
	include('includes/KLESCPOSCommands.php');
	$TextToPrint = $CharacterFontA;
	if (KLwebERPScriptCalledFromTEST()){
		$TextToPrint .= $NewLine . $CenteredJustified . "TEST ONLY - THIS IS NOT A VALID " . $KindOfDoc . $NewLine;
	}
	return $TextToPrint;
}

/**************************************************************************************************************
* Brief description: Prints the shop name on receipts
* Parameters: None
* Returns: The formatted text for the shop name and address
**************************************************************************************************************/
function KLPrintNameOfShop(){
	include('includes/KLESCPOSCommands.php');
	
	// name of shop
	if ($_SESSION['TypeLoc'] == "SHOPKL"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "Kapal-Laut" . $NewLine . 
						$Emphasized . "Your Essential Jewellery" . $NewLine;
	} elseif ($_SESSION['TypeLoc'] == "SHOPBL"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "Blink" . $NewLine . 
						$Emphasized . "Fashion Jewellery" . $NewLine;
	} elseif ($_SESSION['TypeLoc'] == "SHOPOU"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "OUTLET by Kapal-Laut" . $NewLine;
	} else {
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "SHOP NAME NOT FOUND" . $NewLine;
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

/**************************************************************************************************************
* Brief description: Generates the header for receipts
* Parameters:
*   $Identifier - The POS transaction identifier
*   $OrderNo - The order number
* Returns: The formatted header text for the receipt
**************************************************************************************************************/
function KLPrintReceiptHeader($Identifier, $OrderNo){
	
	include('includes/KLESCPOSCommands.php');

	$TextToPrint = $InitPrinter . $CenteredJustified;

	// name of shop
	$TextToPrint .= KLPrintNameOfShop();
	
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE") . $NewLine . $LeftJustified;
	
	// identification of the sale
	$TextInvoiceNumber = 'Invoice: ' . $_SESSION['Items' . $Identifier]->CustRef;
	$TextOrderNumber = 'Order: ' . $OrderNo;
	$TextToPrint .= DoubleJustified($TextInvoiceNumber, $TextOrderNumber, $LineLenghtCharA, " ");

	$TextDateTime = DisplayDateTime();
	$TextSPG = 'SPG: ' . $_SESSION['SalesmanLogin'];
	$TextToPrint .= DoubleJustified($TextDateTime, $TextSPG, $LineLenghtCharA, " ");

	$TextToPrint .= $NewLine . $NewLine;
	$Total = 0;
	$TotalNumberOfItems = 0;
	
	foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$Total = $Total + $SubTotal;
		$TotalNumberOfItems = $TotalNumberOfItems + $OrderLine->Quantity;

		$CodeSide = $OrderLine->Quantity . " " . $OrderLine->StockID;

		if (isRing($OrderLine->StockID)){
			$CodeSide .= " " . "Ring";
		} elseif (isToeRing($OrderLine->StockID)){
			$CodeSide .= " " . "Toe Ring";
		} elseif (isBead($OrderLine->StockID)){
			$CodeSide .= " " . "Beads";
		} elseif (isBrooche($OrderLine->StockID)){
			$CodeSide .= " " . "Brooche";
		} elseif (isEarring($OrderLine->StockID) OR isEarcuff($OrderLine->StockID)) {
			$CodeSide .= " " . "Earrings";
		} elseif (isBracelet($OrderLine->StockID)){
			$CodeSide .= " " . "Bracelet";
		} elseif (isPiercing($OrderLine->StockID)){
			$CodeSide .= " " . "Piercing";
		} elseif (isAnklet($OrderLine->StockID)){
			$CodeSide .= " " . "Anklet";
		} elseif (isPendant($OrderLine->StockID)){
			$CodeSide .= " " . "Pendant";
		} elseif (isNecklace($OrderLine->StockID)){
			$CodeSide .= " " . "Necklace";
		} elseif (isFoulard($OrderLine->StockID)){
			$CodeSide .= " " . "Foulard";
		} elseif (isFaceMask($OrderLine->StockID)){
			$CodeSide .= " " . "Face Mask";
		} elseif (isJewelleryBox($OrderLine->StockID)){
			$CodeSide .= " " . "Jewellery Box";
		} elseif (isJewelleryRoll($OrderLine->StockID)){
			$CodeSide .= " " . "Jewellery Roll";
		} elseif (isBag($OrderLine->StockID) OR isPlasticBag($OrderLine->StockID)){
			$CodeSide .= " " . "Bag";
		} elseif (isTali($OrderLine->StockID)){
			$CodeSide .= " " . "Cord";
		} elseif (isPolishingCloth($OrderLine->StockID)){
			$CodeSide .= " " . "Polishing Cloth";
		} elseif (isKeyRing($OrderLine->StockID)){
			$CodeSide .= " " . "Key Ring";
		}

		if (($OrderLine->Quantity > 1) OR ($OrderLine->DiscountPercent != 0)){
			$CodeSide .= " @ " . number_format($OrderLine->Price);
		}
		if ($OrderLine->DiscountPercent != 0){
			$CodeSide .= " (-" . number_format($OrderLine->DiscountPercent * 100) . "%)";
		}

		$SubTotalSide = number_format($SubTotal);
		$TextToPrint .= DoubleJustified($CodeSide, $SubTotalSide, $LineLenghtCharA, " ");
	}

	
	$TextToPrint .= $NewLine . $NewLine . $RightJustified . $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= 'Total: Rp. ' . number_format($Total) . $CharacterFontA . $NewLine;

	if ($_SESSION['PPN'] > 0){
		$Goods = $Total / ((100 + $_SESSION['PPN']) / 100);
		$PPN = $Total - $Goods;
		$TextToPrint .= 'Goods: Rp. ' . number_format($Goods) . $NewLine;
		$TextToPrint .= 'PPN ' . number_format($_SESSION['PPN']) . '%: Rp.  ' . number_format($PPN) . $NewLine;
	}

	$TextToPrint .= $NewLine . $RightJustified . $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= '# Items: ' . number_format($TotalNumberOfItems) . $CharacterFontA . $NewLine;
	
	return $TextToPrint;
}

/**************************************************************************************************************
* Brief description: Generates the customer copy footer for receipts
* Parameters:
*   $Identifier - The POS transaction identifier
*   $OrderNo - The order number
* Returns: The formatted footer text for the customer receipt copy
**************************************************************************************************************/
function KLPrintReceiptCustomerFooter($Identifier){

	include('includes/KLESCPOSCommands.php');
	
	$TextToPrint = $NewLine;

	// Discounted items no refund...
	$DiscountedItems = false;
	foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
		if ($OrderLine->DiscountPercent != 0){
			$DiscountedItems = true;
		}
	}
	if ($DiscountedItems){
		$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified;
		$TextToPrint .= "No exchange, no refund, no warranty" . $NewLine . "for discounted items." . $NewLine . $NewLine;
	}

	// read terms and conditions
	$TextToPrint .= $CharacterFontB . $LeftJustified;
	$TextToPrint .= "This invoice is the only valid proof of purchase. Keep it. Bank or credit card statement is not a valid proof of purchase." . $NewLine;
	$TextToPrint .= "No refund. Exchange within 7 days with this original invoice, packaging and goods in perfect and unused conditions. We reserve the right to refuse any exchange. Warranty only valid with this original invoice." . $NewLine;
	$TextToPrint .= "For more information on our business terms and conditions, warranty, promotions, shop locations, job opportunities and news check our online shop." . $NewLine;
	if ((isset($_SESSION['PartnerName'])) AND ($_SESSION['PartnerName'] != '')){
		$TextToPrint .= $_SESSION['PartnerName'];
	}
	if ((isset($_SESSION['PartnerAddress'])) AND ($_SESSION['PartnerAddress'] != '')){
		$TextToPrint .= " " . $_SESSION['PartnerAddress'];
	}
	if ((isset($_SESSION['PartnerNPWP'])) AND ($_SESSION['PartnerNPWP'] != '')){
		$TextToPrint .= " NPWP:" . $_SESSION['PartnerNPWP'];
	}

	// website
	$TextToPrint .= $NewLine . $NewLine . $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified;
	if ($_SESSION['TypeLoc'] == "SHOPKL"){
		$TextToPrint .= "kapal-laut.com" . $NewLine;
	} elseif ($_SESSION['TypeLoc'] == "SHOPBL"){
		$TextToPrint .= "blinkfashionjewellery.com" . $NewLine;
	} else {
		$TextToPrint .= "kapal-laut.com" . $NewLine;
	}

	// Follow us
	$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified . $NewLine;
	$TextToPrint .= "Follow us on" . $NewLine;
	if (($_SESSION['TypeLoc'] == "SHOPKL") 
		OR ($_SESSION['TypeLoc'] == "SHOPOU")){
		$TextToPrint .= "Instagram: @KapalLautJewellery" . $NewLine;
		$TextToPrint .= "Facebook: KapalLautJewellery" . $NewLine;
	} else {
		$TextToPrint .= "Instagram: @BlinkFashionJewellery" . $NewLine;
		$TextToPrint .= "Facebook: BlinkFashionJewellery" . $NewLine;
	}
	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE");

	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "CUSTOMER COPY" . $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;
}

/**************************************************************************************************************
* Brief description: Generates the shop copy footer for receipts
* Parameters:
*   $Identifier - The POS transaction identifier
*   $OrderNo - The order number
* Returns: The formatted footer text for the shop receipt copy
**************************************************************************************************************/
function KLPrintReceiptShopFooter(){

	include('includes/KLESCPOSCommands.php');

	// payment descriptions
	$TextToPrint = $CharacterFontA . $NewLine;
	if ($_POST['AmountPaidCash'] > 0){
		$TextToPrint .= 'Paid Cash: ' . number_format($_POST['AmountPaidCash'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidCCDanamon'] > 0){
		$TextToPrint .= 'Paid CC EDC Danamon: ' . number_format($_POST['AmountPaidCCDanamon'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBNI'] > 0){
		$TextToPrint .= 'Paid CC EDC BNI: ' . number_format($_POST['AmountPaidCCBNI'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidCCMandiri'] > 0){
		$TextToPrint .= 'Paid CC EDC Mandiri: ' . number_format($_POST['AmountPaidCCMandiri'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBCA'] > 0){
		$TextToPrint .= 'Paid CC EDC BCA: ' . number_format($_POST['AmountPaidCCBCA'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBRI'] > 0){
		$TextToPrint .= 'Paid CC EDC BRI: ' . number_format($_POST['AmountPaidCCBRI'], 0) . $NewLine;
	}

	if ($_POST['AmountPaidAmexDanamon'] > 0){
		$TextToPrint .= 'Paid AMEX EDC Danamon: ' . number_format($_POST['AmountPaidAmexDanamon'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBNI'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BNI: ' . number_format($_POST['AmountPaidAmexBNI'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexMandiri'] > 0){
		$TextToPrint .= 'Paid AMEX EDC Mandiri: ' . number_format($_POST['AmountPaidAmexMandiri'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBCA'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BCA: ' . number_format($_POST['AmountPaidAmexBCA'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBRI'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BRI: ' . number_format($_POST['AmountPaidAmexBRI'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidWeChat'] > 0){
		$TextToPrint .= 'Paid Alipay/WeChat: ' . number_format($_POST['AmountPaidWeChat'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidQRISMandiri'] > 0){
		$TextToPrint .= 'Paid QRIS Mandiri: ' . number_format($_POST['AmountPaidQRISMandiri'], 0) . $NewLine;
	}
	if ($_POST['AmountPaidQRISBRI'] > 0){
		$TextToPrint .= 'Paid QRIS BRI: ' . number_format($_POST['AmountPaidQRISBRI'], 0) . $NewLine;
	}
	if ($_POST['AmountReturnedGoods'] > 0){
		$TextToPrint .= 'Returned Goods: ' . number_format($_POST['AmountReturnedGoods'], 0) . $NewLine;
	}
	if ($_POST['AmountVouchers'] > 0){
		$TextToPrint .= 'Voucher/Discounts: ' . number_format($_POST['AmountVouchers'], 0) . $NewLine;
	}
	
	$TextToPrint .= $Emphasized . $LeftJustified . $NewLine;
	$TextToPrint .= "Packaging included";
	$TextToPrint .= $CharacterFontA . $NewLine;

	if ($_SESSION['TypeLoc'] == "SHOPKL"){
		if ($_POST['PackagingBox01L'] != 0){
			$TextToPrint .= "KL Box-L: " . $_POST['PackagingBox01L'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01M'] != 0){
			$TextToPrint .= "KL Box-M: " . $_POST['PackagingBox01M'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox01S'] != 0){
			$TextToPrint .= "KL Box-S: " . $_POST['PackagingBox01S'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01L'] != 0){
			$TextToPrint .= "KL Pouchbag-L: " . $_POST['PackagingPouchBag01L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01M'] != 0){
			$TextToPrint .= "KL Pouchbag-M: " . $_POST['PackagingPouchBag01M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingPouchBag01S'] != 0){
			$TextToPrint .= "KL Pouchbag-S: " . $_POST['PackagingPouchBag01S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02M'] != 0){
			$TextToPrint .= "KL Shopping Bag-M: " . $_POST['ShoppingBag02M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02S'] != 0){
			$TextToPrint .= "KL Shopping Bag-S: " . $_POST['ShoppingBag02S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if ($_SESSION['TypeLoc'] == "SHOPBL"){
		if ($_POST['PackagingBox02L'] != 0){
			$TextToPrint .= "BL Box-L: " . $_POST['PackagingBox02L'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox02M'] != 0){
			$TextToPrint .= "BL Box-M: " . $_POST['PackagingBox02M'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox02S'] != 0){
			$TextToPrint .= "BL Box-S: " . $_POST['PackagingBox02S'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03L'] != 0){
			$TextToPrint .= "Blink Pouchbag-L: " . $_POST['BlinkPouchBag03L'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03M'] != 0){
			$TextToPrint .= "Blink Pouchbag-M: " . $_POST['BlinkPouchBag03M'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03S'] != 0){
			$TextToPrint .= "Blink Pouchbag-S: " . $_POST['BlinkPouchBag03S'] . " pouches";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04L'] != 0){
			$TextToPrint .= "Blink Shopping Bag-L: " . $_POST['BlinkShoppingBag04L'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04M'] != 0){
			$TextToPrint .= "Blink Shopping Bag-M: " . $_POST['BlinkShoppingBag04M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkShoppingBag04S'] != 0){
			$TextToPrint .= "Blink Shopping Bag-S: " . $_POST['BlinkShoppingBag04S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}

	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE");
	
	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "SHOP COPY" . $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;
}

/**************************************************************************************************************
* Brief description: Helper function for receipt printing to align text left and right
* Parameters:
*   $Left - The text to align on the left
*   $Right - The text to align on the right
*   $Lenght - The total length of the line
*   $Fillchar - The character to fill the space between left and right text
* Returns: The formatted text line with proper alignment
**************************************************************************************************************/
function DoubleJustified($Left, $Right, $Lenght, $Fillchar){
	include('includes/KLESCPOSCommands.php');
	return str_pad($Left, $Lenght - strlen($Right), $Fillchar) . $Right . $NewLine;
}

/**************************************************************************************************************
* Brief description: Retrieves packaging description for a product
* Parameters:
*   $StockID - The stock ID of the product
* Returns: The packaging description or empty string if not found
**************************************************************************************************************/
function GetItemPackagingDescription($StockID){
	$ErrMsg = __('Can not retrieve the packaging description because');

	$SQL = "SELECT klp.packagingdescription 
			FROM stockmaster AS sm
			INNER JOIN klpackaging AS klp 
				ON klp.packagingcode = sm.klpackaging
			WHERE sm.stockid = '" . $StockID . "'";
	$Result = DB_query($SQL, $ErrMsg, '', true);
	if (DB_num_rows($Result) == 0){
		// no packaging description found, return empty string
		return '';
	} else {
		$MyRow = DB_fetch_row($Result);
		return $MyRow[0];
	}
}


/**************************************************************************************************************
* Brief description: Generates the text for a receipt for a return transfer of items to the main office (Kantor)
* Parameters:
*   $Reference - The reference number of the transfer
* Returns: The formatted text string for the receipt
**************************************************************************************************************/
function KLPrintReturnTransferToKantor($Reference){
	include('includes/KLESCPOSCommands.php');

	$CorrectTransfer = true;

	$TextToPrint = $InitPrinter . $CenteredJustified;
	// name of shop
	$TextToPrint .= KLPrintNameOfShop();
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . 'TRANSFER TO KANTOR' . $NewLine;
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("RETURN TRANSFER"). $NewLine . $CenteredJustified;
	$TextToPrint .= DisplayDateTime() . $NewLine;
	$TextToPrint .= 'SPG Code: ' . $_SESSION['SalesmanLogin'] . $NewLine;
	$TextToPrint .= 'Shop Code: ' . substr($_SESSION['UserStockLocation'],3,2) . $NewLine;
	$TextToPrint .= 'Transfer Number: ' . $Reference . $NewLine;
	$TextToPrint .=  $NewLine . $NewLine;
	$TextToPrint .=  $LeftJustified;

	$NumberOfItems = 0;
	// loop for all the items in the transfer
	$SQL = "SELECT reference,
					loctransfers.stockid,
					shipqty,
					shiploc,
					recloc,
					decimalplaces
			FROM loctransfers
			INNER JOIN stockmaster
				ON loctransfers.stockid = stockmaster.stockid
			WHERE reference = '" . $Reference . "'
			ORDER BY stockid ASC";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0){
		$CorrectTransfer = false;
		$TextToPrint .= 'No items found in this transfer or wrong transfer number.' . $NewLine;
	} else {
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['shiploc'] != $_SESSION['UserStockLocation']){
				$CorrectTransfer = false;
				$TextToPrint .= 'Item ' . $MyRow['stockid'] . ' has wrong shipping location.' . $NewLine;
			} 
			if ($MyRow['recloc'] != 'KANTO'){
				$CorrectTransfer = false;
				$TextToPrint .= 'Item ' . $MyRow['stockid'] . ' has wrong receiving location.' . $NewLine;
			}
			if ($CorrectTransfer){
				$NumberOfItems += $MyRow['shipqty'];
				$TextToPrint .= round(filter_number_format($MyRow['shipqty']), $MyRow['decimalplaces']) .
					' x ' . $MyRow['stockid'] . 
					' - (QOH = ' . GetQuantityOnHand($MyRow['stockid'], $_SESSION['UserStockLocation']) . ')' .
					$NewLine;
			}
		}
		if ($CorrectTransfer){
			// footer
			$TextToPrint .= $NewLine. $Emphasized . '# Pieces in this transfer: ' . filter_number_format($NumberOfItems) . $NewLine;

			$TextToPrint .= $NewLine. $Emphasized . 'Prepared by: ' . $_SESSION['SalesmanLogin'] . $NewLine;
			$TextToPrint .= $CharacterFontA . 'Date: ' . DisplayDateTime() . $NewLine;
			$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;
			
			$TextToPrint .= $Emphasized . 'Shipped by: ' . $NewLine;
			$TextToPrint .= $CharacterFontA . 'Date: ' . $NewLine;
			$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;

			$TextToPrint .= $Emphasized . 'Received by: ' . $NewLine;
			$TextToPrint .= $CharacterFontA . 'Date: ' . $NewLine;
			$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;
		}
	}
	
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("RETURN TRANSFER"). $NewLine . $LeftJustified;
	$TextToPrint .= $CutPaper;

	return $TextToPrint;
}

function KLPrintCustomerServiceReceiptHeader($StockID, $Description, $Fee, $Message1, $Message2, $Warranty){
	include('includes/KLESCPOSCommands.php');
	
	$TextToPrint = $InitPrinter . $CenteredJustified;
	// name of shop
	$TextToPrint .= KLPrintNameOfShop();
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . 'SERVICE RECEIPT' . $NewLine;
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("SERVICE RECEIPT"). $NewLine . $CenteredJustified;
	$TextToPrint .= DisplayDateTime() . $NewLine;
	$TextToPrint .= 'SPG Code: ' . $_SESSION['SalesmanLogin'] . $NewLine;
	$TextToPrint .= 'Shop Code: ' . substr($_SESSION['UserStockLocation'],3,2) . $NewLine . $NewLine;
	$TextToPrint .= $LeftJustified;
	$TextToPrint .= 'Item code: ' . $StockID . $NewLine;
	$TextToPrint .= 'Item description: ' . $Description . $NewLine . $NewLine;
	$TextToPrint .= $Message1 . ' ' . $Message2 . $NewLine . $NewLine;
	if ($Warranty == 'NO' and $Fee > 0){
		$TextToPrint .= 'Fee: ' . number_format($Fee) . ' IDR' . $NewLine . $NewLine;
	}

	return $TextToPrint;
}

function KLPrintCustomerServiceReceiptCustomerFooter(){
	include('includes/KLESCPOSCommands.php');
	
	$TextToPrint .= 'We will contact you in a few days, blah, blah, blah: ' . $NewLine . $NewLine;

	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("SERVICE RECEIPT"). $NewLine . $LeftJustified;
	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "CUSTOMER COPY" . $NewLine;
	$TextToPrint .= $CutPaper;

	return $TextToPrint;
}

function KLPrintCustomerServiceReceiptShopFooter($ServiceCode){
	include('includes/KLESCPOSCommands.php');
	
	$TextToPrint .= $LeftJustified;
	$TextToPrint .= 'Customer name: ' . $NewLine . $NewLine;
	$TextToPrint .= 'Customer phone: ' . $NewLine . $NewLine;
	$TextToPrint .= 'Customer email: ' . $NewLine . $NewLine;

	$TextToPrint .= 'Service Code: ' . $ServiceCode . $NewLine;

	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("SERVICE RECEIPT"). $NewLine . $LeftJustified;
	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "SHOP COPY" . $NewLine;
	$TextToPrint .= $CutPaper;

	return $TextToPrint;
}
