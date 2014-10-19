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
	$Area = "RE";	
	if($PaymentMethod == PAYMENT_BY_CASH){
		// Cash
		// Needs to be splitted into Cash CV and Cash normal
		// We produce a random number between 0 and 100, to separate them.
		$CashDraw = mt_rand(1,10000)/100;
		if ($CashDraw <= PERCENTAGE_SALES_CASH_TO_PT){
			// PERCENTAGE_SALES_CASH_TO_PT% of cash invoices go to CV
			$Area = $Area . "C";
		}else{
			// 100 - PERCENTAGE_SALES_CASH_TO_PT% of cash invoices go cash others
			$Area = $Area . "Z";
		}
	}elseif($PaymentMethod == PAYMENT_BY_CREDITCARD){
		// Credit Card
		$Area = $Area . "R";
	}else{
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
	}elseif($Debtor == "RETAILPA"){
		$Tag = 12;
	}elseif($Debtor == "RETAILPU"){
		$Tag = 13;
	}elseif($Debtor == "RETAILSU"){
		$Tag = 14;
	}elseif($Debtor == "RETAILOB"){
		$Tag = 15;
	}elseif($Debtor == "RETAILSS"){
		$Tag = 16;
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
		$Result = DB_query($SQL, $db, $ErrMsg);
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
		$Result = DB_query($SQL, $db, $ErrMsg);

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
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		
		/*	Update locstock at the shop for the qty */
		$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $QtyDelivered . "
					WHERE locstock.stockid = '" . $StockId . "'
					AND loccode = '" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR CALL THE OFFICE') . ': ' . _('Location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*	Update stockmoves at the shop for the qty */
		$SQL = "INSERT INTO stockmoves (
					stockid,
					type,
					transno,
					loccode,
					trandate,
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
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
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
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} /* end of if GL and stock integrated and standard cost !=0 */
	}
}

?>
