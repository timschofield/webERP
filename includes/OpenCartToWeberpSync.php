<?php

function OpenCartToWeberpSync($ShowMessages, $db, $db_oc, $oc_tableprefix, $EmailText=''){
	$begintime = time_start();
	$TimeDifference = Get_SQL_to_PHP_time_difference($db);

	// connect to opencart DB
	DB_Txn_Begin($db);
		
	// check last time we run this script, so we know which records need to update from OC to webERP
	$LastTimeRun = CheckLastTimeRun('OpenCartToWeberp', $db);
	if ($ShowMessages){
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . $TimeDifference,'success');
		prnMsg('Server time now: ' . GetServerTimeNow($TimeDifference) ,'success');
	}
	if ($EmailText!=''){
		$EmailText = $EmailText . 'OpenCart to webERP Sync was last run on: ' . $LastTimeRun .  "\n\n" . 
					'Server time difference: ' . $TimeDifference . "\n\n" .
					'Server time now: ' . GetServerTimeNow($TimeDifference) . "\n\n";
	}
	// update order information
	$EmailText = SyncOrderInformation($ShowMessages, $LastTimeRun, $db, $db_oc, $oc_tableprefix, $EmailText);

	// update payment information
	$EmailText = SyncPaypalPaymentInformation($ShowMessages, $LastTimeRun, $db, $db_oc, $oc_tableprefix, $EmailText);

	// We are done!
	SetLastTimeRun('OpenCartToWeberp', $db);
	DB_Txn_Commit($db);
	if ($ShowMessages){
		time_finish($begintime);
	}
	return $EmailText;
}

function SyncOrderInformation($ShowMessages, $LastTimeRun, $db, $db_oc, $oc_tableprefix, $EmailText=''){
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));
	$Today = date('Y-m-d');

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Information --> Server Time = " . $ServerNow . " --> webERP Time = " .  date('d/M/Y H:i:s') . "\n\n"; 
	}

	$SQL = "SELECT 	" . $oc_tableprefix . "order.order_id,
					" . $oc_tableprefix . "order.customer_id,
					" . $oc_tableprefix . "order.firstname AS customerfirstname,
					" . $oc_tableprefix . "order.lastname AS customerlastname,
					" . $oc_tableprefix . "order.email,
					" . $oc_tableprefix . "order.telephone,
					" . $oc_tableprefix . "order.fax,
					" . $oc_tableprefix . "order.comment,
					" . $oc_tableprefix . "order.payment_firstname AS paymentfirstname,
					" . $oc_tableprefix . "order.payment_lastname AS paymentlastname,
					" . $oc_tableprefix . "order.payment_company AS paymentcompany,
					" . $oc_tableprefix . "order.payment_address_1,
					" . $oc_tableprefix . "order.payment_address_2,
					" . $oc_tableprefix . "order.payment_city,
					" . $oc_tableprefix . "order.payment_postcode,
					" . $oc_tableprefix . "order.payment_zone,
					" . $oc_tableprefix . "order.payment_country,
					" . $oc_tableprefix . "order.payment_method,
					" . $oc_tableprefix . "order.shipping_firstname AS shippingfirstname,
					" . $oc_tableprefix . "order.shipping_lastname AS shippinglastname,
					" . $oc_tableprefix . "order.shipping_company AS shippingcompany,
					" . $oc_tableprefix . "order.shipping_address_1,
					" . $oc_tableprefix . "order.shipping_address_2,
					" . $oc_tableprefix . "order.shipping_city,
					" . $oc_tableprefix . "order.shipping_postcode,
					" . $oc_tableprefix . "order.shipping_zone,
					" . $oc_tableprefix . "order.shipping_country,
					" . $oc_tableprefix . "order.shipping_method,
					" . $oc_tableprefix . "order.shipping_code,
					" . $oc_tableprefix . "order.total,
					" . $oc_tableprefix . "order.order_status_id,
					" . $oc_tableprefix . "order.currency_code,
					" . $oc_tableprefix . "order.currency_value,
					" . $oc_tableprefix . "order.date_modified
			FROM " . $oc_tableprefix . "order
			WHERE " . $oc_tableprefix . "order.order_status_id >= 1
				AND ( " . $oc_tableprefix . "order.date_added >= '" . $LastTimeRun . "'
					OR " . $oc_tableprefix . "order.date_modified >= '" . $LastTimeRun . "')
			ORDER BY " . $oc_tableprefix . "order.order_id";
			
	$result = DB_query($SQL, $db_oc);
	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Orders from OpenCart') .'</strong></p>';
			echo '<div>';
			$TableHeader = '<tr>
								<th>' . _('OC #') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('Name') . '</th>
								<th>' . _('eMail') . '</th>
								<th>' . _('Shipping Cost') . '</th>
								<th>' . _('Shipper') . '</th>
								<th>' . _('Currency') . '</th>
								<th>' . _('Country') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
							
			$TableHeaderForItems = '<tr>
								<th>' . _('OC #') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('OrderLine') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Unit Price') . '</th>
								<th>' . _('Quantity') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update OpenCart orders in webERP failed');
		$InsertErrMsg = _('The SQL to insert OpenCart orders in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				echo '<table class="selection">';
				echo $TableHeader;
				echo '<tr class="EvenTableRows">';
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCurrency($myrow['currency_code'], $db);
			$CustomerName = $myrow['customerfirstname'] . ' ' . $myrow['customerlastname'];
			$PaymentName = $myrow['paymentfirstname'] . ' ' . $myrow['paymentlastname'];
			$ShippingName = $myrow['shippingfirstname'] . ' ' . $myrow['shippinglastname'];
			$SalesType = OPENCART_DEFAULT_CUSTOMER_SALES_TYPE;
			$DefaultShipVia = GetWeberpShippingMethod($myrow['shipping_method']);
			$Quotation = 1; // is NOT a firm order until we check the payments
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $myrow['order_id'], $db_oc, $oc_tableprefix) * $myrow['currency_value'],$myrow['currency_code']);
			$CouponDiscount = RoundPriceFromCart(GetTotalFromOrder("coupon", $myrow['order_id'], $db_oc, $oc_tableprefix) * $myrow['currency_value'],$myrow['currency_code']);
			$OrderDiscount = RoundPriceFromCart(GetTotalFromOrder("dco", $myrow['order_id'], $db_oc, $oc_tableprefix) * $myrow['currency_value'],$myrow['currency_code']);
			$OpenCartOrderNumber = $myrow['order_id'];
			$Salesman = OPENCART_DEFAULT_SALESMAN;
			$Location = OPENCART_DEFAULT_LOCATION;
			
			if ($CustomerCode == 'WEB-KL-IDR'){
				$Area = OPENCART_DEFAULT_AREA_INDONESIA;
			}else{
				$Area = OPENCART_DEFAULT_AREA;
			}
			
			if ($CustomerCode != 'Error'){
				// First process order header
				if (DataExistsInWebERP($db, 'salesorders', 'customerref', $myrow['order_id'])){
					$Action = "Update";
				}else{
					$Action = "Insert";
					do {
						$OrderNo = GetNextSequenceNo(30);
						$CheckDoesntExistResult = DB_query("SELECT count(*) FROM salesorders WHERE orderno='" . $OrderNo . "'",$db);
						$CheckDoesntExistRow = DB_fetch_row($CheckDoesntExistResult);
					} while ($CheckDoesntExistRow[0]==1);

					$sqlInsert = "INSERT INTO salesorders (
									orderno,
									debtorno,
									branchcode,
									customerref,
									comments,
									orddate,
									ordertype,
									shipvia,
									deliverto,
									deladd1,
									deladd2,
									deladd3,
									deladd4,
									deladd5,
									deladd6,
									contactphone,
									contactemail,
									salesperson,
									fromstkloc,
									freightcost,
									quotation,
									area,
									deliverydate,
									quotedate,
									confirmeddate)
								VALUES (
									'". $OrderNo . "',
									'" . $CustomerCode . "',
									'" . $CustomerCode . "',
									'" . $OpenCartOrderNumber ."',
									'" . $myrow['comment'] ."',
									'" . $myrow['date_modified'] . "',
									'" . $SalesType . "',
									'" . $DefaultShipVia ."',
									'" . $ShippingName . "',
									'" . DB_escape_string($myrow['shipping_address_1']) . "',
									'" . DB_escape_string($myrow['shipping_address_2']) . "',
									'" . DB_escape_string($myrow['shipping_city']) . "',
									'" . DB_escape_string($myrow['shipping_zone']) . "',
									'" . DB_escape_string($myrow['shipping_postcode']) . "',
									'" . DB_escape_string($myrow['shipping_country']) . "',
									'" . DB_escape_string($myrow['telephone']) . "',
									'" . DB_escape_string($myrow['email']). "',
									'" . $Salesman . "',
									'" . $Location ."',
									'" . $FreightCost ."',
									'" . $Quotation ."',
									'" . $Area ."',
									'" . $myrow['date_modified'] . "',
									'" . $myrow['date_modified'] . "',
									'" . $myrow['date_modified'] . "')";
					$resultInsert = DB_query($sqlInsert,$db,$InsertErrMsg,$DbgMsg,true);
				}
				if ($ShowMessages){
					printf('<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							</tr>', 
							$myrow['order_id'],
							$OrderNo,
							$ShippingName,
							$myrow['email'],
							$FreightCost,
							$DefaultShipVia,
							$myrow['currency_code'],
							$myrow['shipping_country'],
							$Action
							);
				}
				if ($EmailText !=''){
					$EmailText = $EmailText . $myrow['order_id'] . 
											  " = " . $OrderNo . 
											  " = " . $ShippingName .
											  " = " . $myrow['email'] .
											  " = " . $myrow['currency_code'] .
											  " = " . $myrow['shipping_country'] .
											  " --> " . $Action . "\n"; 
				}
				// Now the items of the order
				$SQLItemsOrder = "SELECT " . $oc_tableprefix . "order_product.model,
										" . $oc_tableprefix . "order_product.quantity,
										" . $oc_tableprefix . "order_product.price,
										" . $oc_tableprefix . "order_product.total,
										" . $oc_tableprefix . "order_product.tax,
										" . $oc_tableprefix . "order_product.reward
								FROM " . $oc_tableprefix . "order_product
								WHERE " . $oc_tableprefix . "order_product.order_id = " . $myrow['order_id'] . "
								ORDER BY " . $oc_tableprefix . "order_product.order_product_id";
				$resultItemsOrder = DB_query($SQLItemsOrder, $db_oc);
				$ItemsOrder = 0;
				if ($ShowMessages){
					echo '<table class="selection">';
					echo $TableHeaderForItems;
					echo '<tr class="OddTableRows">';
				}
				while ($myitems = DB_fetch_array($resultItemsOrder)) {
					$ItemsOrder++;
					if ($Action == "Update"){
						$Action = "Update";
					}else{
						$Price = RoundPriceFromCart($myitems['price'] * $myrow['currency_value'],$myrow['currency_code']);
						$sqlInsert = "INSERT INTO salesorderdetails 
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $myitems['model'] . "',
											'" . $Price . "',
											'" . $myitems['quantity'] . "',
											'" . $myrow['date_modified'] . "',
											'0')"; // prices come already net from OpenCart
						$resultInsert = DB_query($sqlInsert,$db,$InsertErrMsg,$DbgMsg,true);
						
						// prepare the RL for the items just ordered online
						$sqlUpdate = "UPDATE locstock
										SET reorderlevel = reorderlevel + " . $myitems['quantity'] . " 
										WHERE stockid = '" . $myitems['model'] . "' 
										AND loccode = '" . $Location . "'";
						$resultUpdate = DB_query($sqlUpdate,$db,$UpdateErrMsg,$DbgMsg,true);
						if ($ShowMessages){
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', 
									$myrow['order_id'],
									$OrderNo,
									$ItemsOrder,
									$myitems['model'],
									$Price,
									$myitems['quantity'],
									$Action
									);
						}
						if ($EmailText !=''){
							$EmailText = $EmailText . "            " . $ItemsOrder .
													  " = " . $myitems['model'] . 
													  " = " . $ShippingName .
													  " = " . $Price .
													  " = " . $myitems['quantity'] .
													  " --> " . $Action . "\n"; 
						}
					}
				}
				if ($CouponDiscount != 0){
					$ItemsOrder++;
					// we need to register the coupon use
					$CouponCode = GetTotalTitleFromOrder("coupon", $myrow['order_id'], $db_oc, $oc_tableprefix);
					$CouponStockId = OPENCART_ONLINE_COUPON_CODE;
					$CouponQty = 1;
					if ($Action == "Update"){
						$Action = "Update";
					}else{
						$sqlInsert = "INSERT INTO salesorderdetails 
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											narrative,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $CouponStockId . "',
											'" . $CouponDiscount . "',
											'" . $CouponQty . "',
											'" . $myrow['date_modified'] . "',
											'" . $CouponCode . "',
											'0')"; // prices come already net from OpenCart
						$resultInsert = DB_query($sqlInsert,$db,$InsertErrMsg,$DbgMsg,true);
						if ($ShowMessages){
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', 
									$myrow['order_id'],
									$OrderNo,
									$ItemsOrder,
									$CouponStockId,
									$CouponDiscount,
									$CouponQty,
									$Action
									);
						}
						if ($EmailText !=''){
							$EmailText = $EmailText . "            " . $ItemsOrder .
													  " = " . $CouponStockId . 
													  " = " . $CouponDiscount .
													  " = " . $CouponQty .
													  " --> " . $Action . "\n"; 
						}
					}
				}
				if ($OrderDiscount != 0){
					$ItemsOrder++;
					// we need to register the dco discount use (GENERAL ORDER DISCOUNT)
					$DiscountCode = GetTotalTitleFromOrder("dco", $myrow['order_id'], $db_oc, $oc_tableprefix);
					$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT_CODE;
					$DiscountQty = 1;
					if ($Action == "Update"){
						$Action = "Update";
					}else{
						$sqlInsert = "INSERT INTO salesorderdetails 
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											narrative,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $DiscountStockId . "',
											'" . $OrderDiscount . "',
											'" . $DiscountQty . "',
											'" . $myrow['date_modified'] . "',
											'" . $DiscountCode . "',
											'0')"; // prices come already net from OpenCart
						$resultInsert = DB_query($sqlInsert,$db,$InsertErrMsg,$DbgMsg,true);
						if ($ShowMessages){
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', 
									$myrow['order_id'],
									$OrderNo,
									$ItemsOrder,
									$DiscountStockId,
									$OrderDiscount,
									$DiscountQty,
									$Action
									);
						}
						if ($EmailText !=''){
							$EmailText = $EmailText . "            " . $ItemsOrder .
													  " = " . $DiscountStockId . 
													  " = " . $OrderDiscount .
													  " = " . $DiscountQty .
													  " --> " . $Action . "\n"; 
						}
					}
				}
				$i++;
				if ($ShowMessages){
					echo '</table>';
					echo '</table>';
				}
			}else{
				// Order does not belong to a valid customer for any reason, escape it
				if ($ShowMessages){
					prnMsg('Sales Order from ' . $myrow['email'] .' is not valid as is not a valid currency code.', 'warn');
				}
			}
		}
		if ($ShowMessages){
			echo '</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Orders synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Orders synchronized from OpenCart to webERP') . "\n\n"; 
	}
	return $EmailText;
}

function SyncPaypalPaymentInformation($ShowMessages, $LastTimeRun, $db, $db_oc, $oc_tableprefix, $EmailText=''){
	$ServerNow = GetServerTimeNow(Get_SQL_to_PHP_time_difference($db));
	$Today = date('Y-m-d');

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Information --> Server Time = " . $ServerNow . " --> webERP Time = " .  date('d/M/Y H:i:s') . "\n\n"; 
	}

	// Now deal with the Paypal payment/s of the order...
	$SQL = "SELECT 	" . $oc_tableprefix . "paypal_order.paypal_order_id,
				" . $oc_tableprefix . "order.order_id,
				" . $oc_tableprefix . "order.currency_code AS ordercurrency,
				" . $oc_tableprefix . "order.currency_value,
				" . $oc_tableprefix . "order.customer_id,
				" . $oc_tableprefix . "customer.email,
				" . $oc_tableprefix . "order.total AS ordertotal,
				" . $oc_tableprefix . "paypal_order.paypal_order_id,
				" . $oc_tableprefix . "paypal_order.capture_status,
				" . $oc_tableprefix . "paypal_order.currency_code AS paypalcurrency,
				" . $oc_tableprefix . "paypal_order.authorization_id,
				" . $oc_tableprefix . "paypal_order.total AS paypaltotal,
				" . $oc_tableprefix . "paypal_order_transaction.transaction_id,
				" . $oc_tableprefix . "paypal_order_transaction.created,
				" . $oc_tableprefix . "paypal_order_transaction.payment_status,
				" . $oc_tableprefix . "paypal_order_transaction.pending_reason,
				" . $oc_tableprefix . "paypal_order_transaction.transaction_entity,
				" . $oc_tableprefix . "paypal_order_transaction.amount,
				" . $oc_tableprefix . "paypal_order_transaction.debug_data,
				" . $oc_tableprefix . "paypal_order_transaction.call_data
		FROM " . $oc_tableprefix . "paypal_order, 
			 " . $oc_tableprefix . "paypal_order_transaction,
			 " . $oc_tableprefix . "order,
			 " . $oc_tableprefix . "customer
		WHERE " . $oc_tableprefix . "paypal_order.paypal_order_id = " . $oc_tableprefix . "paypal_order_transaction.paypal_order_id
				AND " . $oc_tableprefix . "paypal_order.order_id  = " . $oc_tableprefix . "order.order_id
				AND " . $oc_tableprefix . "order.customer_id  = " . $oc_tableprefix . "customer.customer_id
				AND ( " . $oc_tableprefix . "paypal_order.created >= '" . $LastTimeRun . "'
					OR " . $oc_tableprefix . "paypal_order.modified >= '" . $LastTimeRun . "')
		ORDER BY " . $oc_tableprefix . "paypal_order.paypal_order_id";
	$result = DB_query($SQL, $db_oc);

	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Paypal Payments from OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('CustomerID') . '</th>
								<th>' . _('email') . '</th>
								<th>' . _('webERP Code') . '</th>
								<th>' . _('OrderID') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('Order Total') . '</th>
								<th>' . _('Order Curr') . '</th>
								<th>' . _('Paypal Total') . '</th>
								<th>' . _('Paypal Curr') . '</th>
								<th>' . _('Paypal Trx') . '</th>
								<th>' . _('Trx Total') . '</th>
								<th>' . _('Commission') . '</th>
								<th>' . _('Date') . '</th>
								<th>' . _('Status') . '</th>
								<th>' . _('Pending reason') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update OpenCart Paypal payments in webERP failed');
		$InsertErrMsg = _('The SQL to insert OpenCart Paypal payments in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCurrency($myrow['ordercurrency'], $db);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $myrow['order_id'], $db);
			$PaymentSystem = OPENCART_DEFAULT_PAYMENT_SYSTEM;
			$CurrencyOrder = $myrow['ordercurrency'];
			$CurrencyPayment = $myrow['paypalcurrency'];
			$TotalOrder = round($myrow['ordertotal'] * $myrow['currency_value'],2); // from OC default currency to order and payment currency
			$Rate = GetWeberpCurrencyRate($CurrencyOrder, $db);
			$AmountPaid = $myrow['paypaltotal'];
			$TransactionID = $myrow['transaction_id'];
			$GLAccount = GetWeberpGLAccountFromCurrency($CurrencyPayment, $db);
			$GLCommissionAccount = GetWeberpGLCommissionAccountFromCurrency($CurrencyPayment, $db);
			$PayPalResponseArray = GetPaypalReturnDataInArray($myrow['debug_data']);
			$Commission = urldecode($PayPalResponseArray['PAYMENTINFO_0_FEEAMT']);


			if (($myrow['paypalcurrency'] == $myrow['ordercurrency']) AND ($myrow['pending_reason'] == 'None')) {
				// order currency and Paypal currency are the same
				// AND has been paid OK
				$PaymentOK = true;
			}else{
				prnMsg("HORROR: Currency mess", "warn");
				$PaymentOK = false;
			}

			if ($PaymentOK){
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
				InsertCustomerReceipt($CustomerCode, $AmountPaid, $CurrencyPayment, $Rate, $GLAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo, $db); 
				TransactionCommissionGL($CustomerCode, $GLAccount, $GLCommissionAccount, $Commission, $CurrencyPayment, $Rate, $PaymentSystem, $TransactionID, $PeriodNo, $db);
				ChangeOrderQuotationFlag($OrderNo, 0, $db); // it has been paid, so we consider it a firm order
			}
			
			if ($ShowMessages){
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$myrow['customer_id'],
						$myrow['email'],
						$CustomerCode,
						$myrow['order_id'],
						$OrderNo,
						$TotalOrder,
						$myrow['ordercurrency'],
						$AmountPaid,
						$myrow['paypalcurrency'],
						$TransactionID,
						$myrow['amount'],
						$Commission,
						$myrow['created'],
						$myrow['payment_status'],
						$myrow['pending_reason']
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['customer_id'] . 
									      " = " . $myrow['email'] . 
									      " = " . $CustomerCode . 
									      " = " . $myrow['order_id'] . 
									      " = " . $TotalOrder . 
									      " = " . $myrow['ordercurrency'] . 
									      " = " . $AmountPaid . 
									      " = " . $myrow['payment_status'] . 
										  " --> " . $Action . "\n"; 
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . _('Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Payments synchronized from OpenCart to webERP') . "\n\n"; 
	}
	return $EmailText;
}

?>