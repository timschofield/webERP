<?php

function zerofill($mStretch, $iLength = 2){
    $sPrintfString = '%0' . (int)$iLength . 's';
    return sprintf($sPrintfString, $mStretch);
}

/*************************************************************************************************
			FUNCTIONS RELATED TO P.O.S. AT SHOPS
*************************************************************************************************/
function KapalLautRetailAreaSelection($PaymentMethod){
	if ($PaymentMethod == PAYMENT_BY_CASH){
		if ($_SESSION['CashSalesReported'] <= 0){
			// all cash sales go to Others
			$Area = $_SESSION['AreaSalesCashOthers'];
		} elseif ($_SESSION['CashSalesReported'] >= 100){
			// all cash sales go to cash PTADU, PTBB or POXX
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
		prnMsg(_('Error calculating customer area from payment method. Seek help from the administrator.'), 'error');
		include('includes/footer.php');
		exit;
	}
	return $Area;
}

function AdjustPackagingMovement($StockID, $QtyDelivered, $InvoiceNo, $PeriodNo, $OrderNo, $Area, $Tag, $identifier){

	if ($QtyDelivered != 0){
		/* Need to get the current standard cost */
		$SQL=	"SELECT (actualcost)
				FROM stockmaster
				WHERE stockmaster.stockid='" . $StockID . "'";
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
				WHERE locstock.stockid='" . $StockID . "'
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
					'" . $StockID . "',
					'" . $QtyDelivered . "',
					CURRENT_DATE)";
		$ErrMsg = _('ERROR: Contact the office!!!  -> AdjustPackagingMovement-0030');
		$DbgMsg = _('The following SQL to insert the packaging used was used');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		
		/*	Update locstock at the shop for the qty */
		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $QtyDelivered . "
				WHERE locstock.stockid = '" . $StockID . "'
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
				VALUES ('" . $StockID . "',
					10,
					'" . $InvoiceNo . "',
					'" . $_SESSION['UserStockLocation'] . "',
					CURRENT_DATE,
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
		if ($StandardCost !=0){
			/*first the cost of sales entry*/
			$AccountCOGL = GetCOGSGLAccount($Area, $StockID, $_SESSION['Items'.$identifier]->DefaultSalesType);
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

function InsertItemSoldIntoSalesAnalysis ($Area,
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

	$SQL="SELECT COUNT(*),
			salesanalysis.stockid,
			salesanalysis.stkcategory,
			salesanalysis.cust,
			salesanalysis.custbranch,
			salesanalysis.area,
			salesanalysis.periodno,
			salesanalysis.typeabbrev,
			salesanalysis.salesperson
		FROM salesanalysis,
			custbranch,
			stockmaster
		WHERE salesanalysis.stkcategory=stockmaster.categoryid
			AND salesanalysis.stockid=stockmaster.stockid
			AND salesanalysis.cust=custbranch.debtorno
			AND salesanalysis.custbranch=custbranch.branchcode
			AND salesanalysis.area='" . $Area ."'
			AND salesanalysis.salesperson=custbranch.salesman
			AND salesanalysis.typeabbrev ='" . $SalesType . "'
			AND salesanalysis.periodno='" . $PeriodNo . "'
			AND salesanalysis.cust " . LIKE . " '" . $DebtorNo . "'
			AND salesanalysis.custbranch " . LIKE . " '" . $DebtorBranch . "'
			AND salesanalysis.stockid " . LIKE . " '" . $StockID . "'
			AND salesanalysis.budgetoractual=1
		GROUP BY salesanalysis.stockid,
			salesanalysis.stkcategory,
			salesanalysis.cust,
			salesanalysis.custbranch,
			salesanalysis.area,
			salesanalysis.periodno,
			salesanalysis.typeabbrev,
			salesanalysis.salesperson";

	$ErrMsg = _('The count of existing Sales analysis records could not run because');
	$DbgMsg = _('SQL to count the no of sales analysis records');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0]>0){  /*Update the existing record that already exists */

		$SQL = "UPDATE salesanalysis
				SET amt=amt+" . ($Price * $Quantity / $ExRate) . ",
					cost=cost+" . ($StandardCost * $Quantity) . ",
					qty=qty +" . $Quantity . ",
					disc=disc+" . ($DiscountPercent * $Price * $Quantity / $ExRate) . "
				WHERE salesanalysis.area='" . $MyRow[5] . "'
					AND salesanalysis.salesperson='" . $MyRow[8] . "'
					AND typeabbrev ='" . $SalesType . "'
					AND periodno = '" . $PeriodNo . "'
					AND cust " . LIKE . " '" . $DebtorNo . "'
					AND custbranch " . LIKE . " '" . $DebtorBranch . "'
					AND stockid " . LIKE . " '" . $StockID . "'
					AND salesanalysis.stkcategory ='" . $MyRow[2] . "'
					AND budgetoractual=1";

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

	$ErrMsg = _('Sales analysis record could not be added or updated because');
	$DbgMsg = _('The following SQL to insert the sales analysis record was used');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	
}

function RecordRetailCustomerInformation($OrderNo, $FirstName, $LastName, $Country, $DateOfBirth, $Email, $Sex){
	// If some field is filled, record it.
	// For some reason, Country = 0 if empty
	if (Is_date($DateOfBirth)){
		$DateOfBirth = FormatDateForSQL($DateOfBirth);
	}else{
		$DateOfBirth = '1000-01-01';
	}
	if (($Country != '0') 
		OR ($DateOfBirth != '1000-01-01') 
		OR ($Email != '') 
		OR ($Sex != '')){ 

		$FirstName = CapitalizeName($FirstName);
		$LastName = CapitalizeName($LastName);
		$Email = mb_strtolower($Email);
		$Today  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		if (($DateOfBirth != '') AND ($DateOfBirth != '1000-01-01') AND ($Today > $DateOfBirth)){
			$Age = date_diff(date_create($DateOfBirth), date_create($Today))->y; 
		}else{
			$Age = 0;
		}

		$SQL = "SELECT *
				FROM klretailcustomers
				WHERE orderno = '" . $OrderNo . "'";
		$Result = DB_query($SQL,'','',true);
		
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
		$Description = $CustomerReference  . 
					' (' . $InvoiceNo . 
					') SPG:'. $_SESSION['SalesmanLogin'] . 
					 ' CC -> T:' . number_format($AmountPaid,0) . 
					 ' C:' . number_format($BankCommision,0);
	}else{
		$Description = $CustomerReference  . 
					' (' . $InvoiceNo . 
					') SPG:'. $_SESSION['SalesmanLogin'];
	}

	InsertIntoGLTrans("12", 
					$ReceiptNumber, 
					$SettlementDate,
					$SettlementPeriodNo,
					$BankAccount,
					$Description,
					round($NetPayment/$ExRate),
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
						round($BankCommision/$ExRate),
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
					round(-$AmountPaid/$ExRate),
					$Tag,
					'ERROR-POS-00105'
					);
	return $ReceiptNumber;
}

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
	
	$Description = $CustomerReference  . 
					' (' . $InvoiceNo . 
					') ' . $TypeDiscount;

	InsertIntoGLTrans("10", 
					$ReceiptNumber, 
					Date('Y-m-d'),
					$PeriodNo,
					$BankAccount,
					$Description,
					round($NetPayment/$ExRate),
					$Tag,
					'ERROR-POS-00106'
					);
	return $ReceiptNumber;
}

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

	$Description = $CustomerReference  . 
					' (' . $InvoiceNo . 
					') SPG:'. $_SESSION['SalesmanLogin'];
						
	
	if ($PaymentMethod == PAYMENT_BY_CREDITCARD){
		$Description = $Description . ' CC';
	}

	//Now need to add the receipt banktrans record
	//First get the account currency that it has been banked into
	$Result = DB_query("SELECT rate FROM currencies
						INNER JOIN bankaccounts ON currencies.currabrev=bankaccounts.currcode
						WHERE bankaccounts.accountcode='" . $BankAccount . "'");
	$MyRow = DB_fetch_row($Result);
	$BankAccountExRate = $MyRow[0];

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
				CURRENT_DATE,
				'3',
				'" . $NetPayment . "',
				'" . $Currency . "')";

	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-001 FAILED Insert banktrans');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

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
	$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-002 FAILED Insert debtortrans');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$ReceiptDebtorTransID = DB_Last_Insert_ID('debtortrans','id');

	$SQL = "UPDATE debtorsmaster SET lastpaiddate = CURRENT_DATE,
									lastpaid='" . $AmountPaid . "'
							WHERE debtorsmaster.debtorno='" . $DebtorNo . "'";

	$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-003 FAILED Update debtorsmaster');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	//and finally add the allocation record between receipt and invoice

	$SQL = "INSERT INTO custallocns (	amt,
										datealloc,
										transid_allocfrom,
										transid_allocto )
							VALUES  ('" . $AmountPaid . "',
									CURRENT_DATE,
									 '" . $ReceiptDebtorTransID . "',
									 '" . $DebtorTransID . "')";
	$DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
	$ErrMsg = _('Report to Office: AccountDebtorPayment ERROR-004 FAILED Insert custallocns');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);							
	return $ReceiptNumber;
}

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
		$Description = $CustomerReference  . 
						' (' . $InvoiceNo . 
						') SPG:'. $_SESSION['SalesmanLogin'] . 
						' ' . ' Voucher/Discount';
	}else{
		$Description = $CustomerReference  . 
						' (' . $InvoiceNo . 
						') SPG:'. $_SESSION['SalesmanLogin'] . 
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
	$DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
	$ErrMsg = _('Report to Office: AccountDebtorDiscount ERROR-002 FAILED Insert debtortrans');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	return $ReceiptNumber;
}
	
/********************************************************************************************************/
/***                               PRINT POS RECEIPT FUNCTIONS                                        ***/
/********************************************************************************************************/

function GetPOSIdentifier(){
	$id = date('U').zerofill(mt_rand(0,999999),6);
	return $id;
}

function GetFilenameFromPOSIdentifier($id){
	$f = 'includes/WebClientPrint/wcpcache/'.$id.'.pos';
	return $f;
}


function KLPrintReceiptTestWarning($KindOfDoc){
	include('includes/KLESCPOSCommands.php');
	$TextToPrint = $CharacterFontA;
	if (KLwebERPScriptCalledFromTEST()){
		$TextToPrint .= $NewLine .  $CenteredJustified . "TEST ONLY - THIS IS NOT A VALID " . $KindOfDoc . $NewLine;
	}
	return $TextToPrint;
}

function KLPrintNameOfShop(){
	include('includes/KLESCPOSCommands.php');
	
	// name of shop
	if ($_SESSION['TypeLoc'] == "SHOPKL"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "Kapal-Laut" . $NewLine . $Emphasized . "Your Essential Jewellery" . $NewLine;
	}else if ($_SESSION['TypeLoc'] == "SHOPBL"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "Blink by Kapal-Laut" . $NewLine;
	}else if ($_SESSION['TypeLoc'] == "SHOPOU"){
		$TextToPrint = $EmphasizedDoubleHeightDoubleWidth . "OUTLET by Kapal-Laut" . $NewLine;
	}else{
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


function KLPrintReceiptHeader($identifier, $OrderNo){
	
	include('includes/KLESCPOSCommands.php');

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
	$Total = 0;
	$TotalNumberOfItems = 0;
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		$Total = $Total + $SubTotal;
		$TotalNumberOfItems = $TotalNumberOfItems + $OrderLine->Quantity;

		$CodeSide = $OrderLine->Quantity . " " . $OrderLine->StockID;

		if (isRing($OrderLine->StockID)){
			$CodeSide .= " " . "Ring";
		}elseif (isToeRing($OrderLine->StockID)){
			$CodeSide .= " " . "Toe Ring";
		}elseif (isBead($OrderLine->StockID)){
			$CodeSide .= " " . "Beads";
		}elseif (isBrooche($OrderLine->StockID)){
			$CodeSide .= " " . "Brooche";
		}elseif (isEarring($OrderLine->StockID) OR isEarcuff($OrderLine->StockID)) {
			$CodeSide .= " " . "Earrings";
		}elseif (isBracelet($OrderLine->StockID)){
			$CodeSide .= " " . "Bracelet";
		}elseif (isPiercing($OrderLine->StockID)){
			$CodeSide .= " " . "Piercing";
		}elseif (isAnklet($OrderLine->StockID)){
			$CodeSide .= " " . "Anklet";
		}elseif (isPendant($OrderLine->StockID)){
			$CodeSide .= " " . "Pendant";
		}elseif (isNecklace($OrderLine->StockID)){
			$CodeSide .= " " . "Necklace";
		}elseif (isFoulard($OrderLine->StockID)){
			$CodeSide .= " " . "Foulard";
		}elseif (isFaceMask($OrderLine->StockID)){
			$CodeSide .= " " . "Face Mask";
		}elseif (isJewelleryBox($OrderLine->StockID)){
			$CodeSide .= " " . "Jewellery Box";
		}elseif (isJewelleryRoll($OrderLine->StockID)){
			$CodeSide .= " " . "Jewellery Roll";
		}elseif (isBag($OrderLine->StockID) OR isPlasticBag($OrderLine->StockID)){
			$CodeSide .= " " . "Bag";
		}elseif (isTali($OrderLine->StockID)){
			$CodeSide .= " " . "Cord";
		}elseif (isPolishingCloth($OrderLine->StockID)){
			$CodeSide .= " " . "Polishing Cloth";
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

	
	$TextToPrint .= $NewLine . $NewLine . $RightJustified . $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= 'Total: Rp. ' . number_format($Total) . $CharacterFontA. $NewLine;

	if ($_SESSION['PPN'] > 0){
		$Goods = $Total / ((100 + $_SESSION['PPN']) / 100);
		$PPN = $Total-$Goods;
		$TextToPrint .= 'Goods: Rp. ' . number_format($Goods) . $NewLine;
		$TextToPrint .= 'PPN ' . number_format($_SESSION['PPN']) . '%: Rp.  ' . number_format($PPN) . $NewLine;
	}

	$TextToPrint .= $NewLine . $RightJustified . $EmphasizedDoubleHeightDoubleWidth;
	$TextToPrint .= '# Items: ' . number_format($TotalNumberOfItems) . $CharacterFontA. $NewLine;
	
	return $TextToPrint;
}

function KLPrintReceiptCustomerFooter($identifier, $OrderNo){

	include('includes/KLESCPOSCommands.php');
	
	$TextToPrint = $NewLine;

	// Discounted items no refund...
	$DiscountedItems = FALSE;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		if ($OrderLine->DiscountPercent != 0){
			$DiscountedItems = TRUE;
		}
	}
	if ($DiscountedItems){
		$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified;
		$TextToPrint .= "No exchange, no refund, no warranty" . $NewLine . "for discounted items." . $NewLine . $NewLine;
	}

	// read terms and conditions
	$TextToPrint .= $CharacterFontB . $LeftJustified;
	$TextToPrint .= "This invoice is the only valid proof of purchase. Keep it. Bank or credit card statement is not a valid proof of purchase.". $NewLine;
	$TextToPrint .= "No refund. Exchange within 7 days with this original invoice, packaging and goods in perfect and unused conditions. We reserve the right to refuse any exchange. Warranty only valid with this original invoice.". $NewLine;
	$TextToPrint .= "For more information on our business terms and conditions, warranty, promotions, shop locations, job opportunities and news check our online shop.". $NewLine;
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
	}else if ($_SESSION['TypeLoc'] == "SHOPBL"){
		$TextToPrint .= "blink.kapal-laut.com" . $NewLine;
	}else{
		$TextToPrint .= "kapal-laut.com" . $NewLine;
	}

	// Follow us
	$TextToPrint .= $CharacterFontA . $Emphasized . $CenteredJustified . $NewLine;
	$TextToPrint .= "Follow us on" . $NewLine ;
	if (($_SESSION['TypeLoc'] == "SHOPKL") 
		OR ($_SESSION['TypeLoc'] == "SHOPOU")){
		$TextToPrint .= "Instagram: @KapalLautJewellery" . $NewLine ;
		$TextToPrint .= "Facebook: KapalLautJewellery" . $NewLine ;
	}else {
		$TextToPrint .= "Instagram: @BlinkFashionJewellery" . $NewLine ;
		$TextToPrint .= "Facebook: BlinkFashionJewellery" . $NewLine ;
	}
	$TextToPrint .= KLPrintReceiptTestWarning("INVOICE");

	$TextToPrint .= $NewLine;
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . $CenteredJustified . "CUSTOMER COPY" . $NewLine;
	$TextToPrint .= $CutPaper;
	
	return $TextToPrint;
}

function KLPrintReceiptShopFooter($identifier, $OrderNo){

	include('includes/KLESCPOSCommands.php');

	// payment descriptions
	$TextToPrint = $CharacterFontA. $NewLine;
	if ($_POST['AmountPaidCash'] > 0){
		$TextToPrint .= 'Paid Cash: ' . number_format($_POST['AmountPaidCash'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCDanamon'] > 0){
		$TextToPrint .= 'Paid CC EDC Danamon: ' . number_format($_POST['AmountPaidCCDanamon'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBNI'] > 0){
		$TextToPrint .= 'Paid CC EDC BNI: ' . number_format($_POST['AmountPaidCCBNI'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCMandiri'] > 0){
		$TextToPrint .= 'Paid CC EDC Mandiri: ' . number_format($_POST['AmountPaidCCMandiri'],0) . $NewLine;
	}
	if ($_POST['AmountPaidCCBCA'] > 0){
		$TextToPrint .= 'Paid CC EDC BCA: ' . number_format($_POST['AmountPaidCCBCA'],0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBNI'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BNI: ' . number_format($_POST['AmountPaidAmexBNI'],0) . $NewLine;
	}
	if ($_POST['AmountPaidAmexBCA'] > 0){
		$TextToPrint .= 'Paid AMEX EDC BCA: ' . number_format($_POST['AmountPaidAmexBCA'],0) . $NewLine;
	}
	if ($_POST['AmountPaidWeChat'] > 0){
		$TextToPrint .= 'Paid Alipay/WeChat: ' . number_format($_POST['AmountPaidWeChat'],0) . $NewLine;
	}
	if ($_POST['AmountPaidQRIS'] > 0){
		$TextToPrint .= 'Paid QRIS Mandiri: ' . number_format($_POST['AmountPaidQRIS'],0) . $NewLine;
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

	if ($_SESSION['TypeLoc'] == "SHOPKL"){
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
		if ($_POST['ShoppingBag02M'] != 0){
			$TextToPrint .= "KL Shopping Bag-M: ". $_POST['ShoppingBag02M'] . " bags";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['ShoppingBag02S'] != 0){
			$TextToPrint .= "KL Shopping Bag-S: ". $_POST['ShoppingBag02S'] . " bags";
			$TextToPrint .= $NewLine;
		}
	}
	if ($_SESSION['TypeLoc'] == "SHOPBL"){
		if ($_POST['PackagingBox02L'] != 0){
			$TextToPrint .= "BL Box-L: ". $_POST['PackagingBox02L'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox02M'] != 0){
			$TextToPrint .= "BL Box-M: ". $_POST['PackagingBox02M'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['PackagingBox02S'] != 0){
			$TextToPrint .= "BL Box-S: ". $_POST['PackagingBox02S'] . " boxes";
			$TextToPrint .= $NewLine;
		}
		if ($_POST['BlinkPouchBag03XL'] != 0){
			$TextToPrint .= "Blink Pouchbag-XL: ". $_POST['BlinkPouchBag03XL'] . " pouches";
			$TextToPrint .= $NewLine;
		}
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
	if (($_SESSION['TypeLoc'] == "SHOPOU")
		OR ($_SESSION['TypeLoc'] == "SHOPKL")
		OR ($_SESSION['TypeLoc'] == "SHOPBL")){
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
	include('includes/KLESCPOSCommands.php');
	return str_pad($left, $lenght - strlen($right), $fillchar) . $right . $NewLine;
}


?>
