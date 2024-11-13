<?php

function OpenCartToWeberpSync($ShowMessages, $db, $db_oc, $EmailText=''){
	$begintime = time_start();

	// connect to opencart DB
	DB_Txn_Begin();

	// check last time we run this script, so we know which records need to update from OC to webERP
	$LastTimeRun = CheckLastTimeRun('OpenCartToWeberp', $db);
	$TimeDifference = Get_SQL_OC_to_PHP_time_difference();
	
	if ($ShowMessages){
		prnMsg('This script was last run on: ' . $LastTimeRun . ' webERP Database time.','success');
		prnMsg('Server time difference(PHP and OpenCart Database): ' . $TimeDifference,'success');
		prnMsg('OpenCart Database Server time (JKT Time as DOKU requirements) now: ' . GetServerTimeNow($TimeDifference) ,'success');
	}
	if ($EmailText!=''){
		$EmailText = $EmailText . 'OpenCart to webERP Sync was last run on: ' . $LastTimeRun .  "\n" .
					PrintTimeInformation($db);
	}
	// update order information
	$EmailText = SyncOrderInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText);

	// update payment information by PayPal
	$EmailText = SyncPaypalPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText);

	// update payment information by snap Midtrans 
	$EmailText = SyncSnapPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText);

	// update order status from OpenCart to webERP
	$EmailText = SyncOrderStatus($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText);

	// send emails to team of new orders arrived
	$EmailText = EmailOrdersReadyToPrepare($ShowMessages, $db, $EmailText);

	// We are done!
	SetLastTimeRun('OpenCartToWeberp', $db);
	DB_Txn_Commit();
	if ($ShowMessages){
		time_finish($begintime);
	}
	return $EmailText;
}

function SyncOrderInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Information" . "\n" . PrintTimeInformation($db);
	}

	$SQL = "SELECT 	oc_order.order_id,
					oc_order.customer_id,
					oc_order.firstname AS customerfirstname,
					oc_order.lastname AS customerlastname,
					oc_order.email,
					oc_order.telephone,
					oc_order.fax,
					oc_order.comment,
					oc_order.payment_firstname AS paymentfirstname,
					oc_order.payment_lastname AS paymentlastname,
					oc_order.payment_company AS paymentcompany,
					oc_order.payment_address_1,
					oc_order.payment_address_2,
					oc_order.payment_city,
					oc_order.payment_postcode,
					oc_order.payment_zone,
					oc_order.payment_country,
					oc_order.payment_method,
					oc_order.payment_code,
					oc_order.shipping_firstname AS shippingfirstname,
					oc_order.shipping_lastname AS shippinglastname,
					oc_order.shipping_company AS shippingcompany,
					oc_order.shipping_address_1,
					oc_order.shipping_address_2,
					oc_order.shipping_city,
					oc_order.shipping_postcode,
					oc_order.shipping_zone,
					oc_order.shipping_country,
					oc_order.shipping_method,
					oc_order.shipping_code,
					oc_order.total,
					oc_order.order_status_id,
					oc_order.currency_code,
					oc_order.currency_value,
					oc_order.date_modified,
					oc_order.customer_group_id
			FROM oc_order
			WHERE oc_order.order_status_id = 2
				AND ( oc_order.date_added >= '" . $LastTimeRun . "'
					OR oc_order.date_modified >= '" . $LastTimeRun . "')
			ORDER BY oc_order.order_id";

	$result = DB_query_oc($SQL);
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
								<th>' . _('Payment Code') . '</th>
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
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($myrow['customer_group_id'], $myrow['currency_code'], $db);
			$CustomerName = CleanStringForWebERP(CapitalizeName($myrow['customerfirstname'] . ' ' . $myrow['customerlastname']));
			$PaymentName = CleanStringForWebERP(CapitalizeName($myrow['paymentfirstname'] . ' ' . $myrow['paymentlastname']));
			$ShippingName = CleanStringForWebERP(CapitalizeName($myrow['shippingfirstname'] . ' ' . $myrow['shippinglastname']));
			$SalesType = OPENCART_DEFAULT_CUSTOMER_SALES_TYPE;
			$DefaultShipVia = GetWeberpShippingMethod($myrow['shipping_code']);
			if ($CustomerCode == "WEB-WH-IDR"){
				$Quotation = 0; // wholesale customers in IDR, are exceptions: or they pay regularly by bank transfer or they paid / will pay in IDR at the shop at pick up time. 
								// Either case we are aware of the order.
			}else{
				$Quotation = 1; // is NOT a firm order until we check the payments
			}
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $myrow['order_id'], $db_oc) * $myrow['currency_value'],$myrow['currency_code']);
			$CouponDiscount = RoundPriceFromCart(GetTotalFromOrder("coupon", $myrow['order_id'], $db_oc) * $myrow['currency_value'],$myrow['currency_code']);
			$OrderDiscount = RoundPriceFromCart(GetTotalFromOrder("discountrule", $myrow['order_id'], $db_oc) * $myrow['currency_value'],$myrow['currency_code']);
			$OpenCartOrderNumber = $myrow['order_id'];
			$Salesman = OPENCART_DEFAULT_SALESMAN;
			$Location = OPENCART_DEFAULT_LOCATION;
			$Comments =  CleanStringForWebERP($myrow['comment']);
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $myrow['date_modified'] . -$TimeDifference . ' hours'));
			$Area = GetAreaFromCustomer($CustomerCode);
			$PaymentCode = $myrow['payment_code'];
			
			if($DefaultShipVia == 10){
				// if shipping is "Pickup From Store"
				$ShippingAddress1 = "Pick up from store";
				$ShippingAddress2 = "";
				$ShippingCity     = "";
				$ShippingZone	  = "";
				$ShippingPostCode = "";
				$ShippingCountry  = DB_escape_string($myrow['shipping_country']);
			}else{
				// any other shipping method, we need the details
				$ShippingAddress1 = DB_escape_string($myrow['shipping_address_1']);
				$ShippingAddress2 = DB_escape_string($myrow['shipping_address_2']);
				$ShippingCity     = DB_escape_string($myrow['shipping_city']);
				$ShippingZone     = DB_escape_string($myrow['shipping_zone']);
				$ShippingPostCode = DB_escape_string($myrow['shipping_postcode']);
				$ShippingCountry  = DB_escape_string($myrow['shipping_country']);
			}

			if ($CustomerCode != 'Error'){
				// First process order header
				if (DataExistsInWebERP('salesorders', 'debtorno', $CustomerCode, 'customerref', $myrow['order_id'])){
					$Action = "Update";
				}else{
					$Action = "Insert";
					do {
						$OrderNo = GetNextSequenceNo(30);
						$CheckDoesntExistResult = DB_query("SELECT count(*) FROM salesorders WHERE orderno='" . $OrderNo . "'");
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
									klocpaymentcode,
									deliverydate,
									quotedate,
									confirmeddate)
								VALUES (
									'". $OrderNo . "',
									'" . $CustomerCode . "',
									'" . $CustomerCode . "',
									'" . $OpenCartOrderNumber ."',
									'" . $Comments ."',
									'" . $WebERPDateOrder . "',
									'" . $SalesType . "',
									'" . $DefaultShipVia ."',
									'" . $ShippingName . "',
									'" . $ShippingAddress1 . "',
									'" . $ShippingAddress2 . "',
									'" . $ShippingCity . "',
									'" . $ShippingZone . "',
									'" . $ShippingPostCode . "',
									'" . $ShippingCountry . "',
									'" . DB_escape_string($myrow['telephone']) . "',
									'" . DB_escape_string($myrow['email']). "',
									'" . $Salesman . "',
									'" . $Location ."',
									'" . $FreightCost ."',
									'" . $Quotation ."',
									'" . $Area ."',
									'" . $PaymentCode ."',
									'" . $myrow['date_modified'] . "',
									'" . $myrow['date_modified'] . "',
									'" . $myrow['date_modified'] . "')";
					$resultInsert = DB_query($sqlInsert,$InsertErrMsg,$DbgMsg,true);
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
							<td>%s</td>
							</tr>',
							$myrow['order_id'],
							$OrderNo,
							$ShippingName,
							$myrow['email'],
							$FreightCost,
							$DefaultShipVia,
							$myrow['currency_code'],
							$PaymentCode,
							$ShippingCountry,
							$Action
							);
				}
				if ($EmailText !=''){
					$EmailText = $EmailText . $myrow['order_id'] .
											  " = " . $OrderNo .
											  " = " . $ShippingName .
											  " = " . $myrow['email'] .
											  " = " . $myrow['currency_code'] .
											  " = " . $ShippingCountry .
											  " = " . $PaymentCode .
											  " --> " . $Action . "\n";
				}
				// Now the items of the order
				$SQLItemsOrder = "SELECT oc_order_product.model,
										oc_order_product.quantity,
										oc_order_product.price,
										oc_order_product.total,
										oc_order_product.tax,
										oc_order_product.reward
								FROM oc_order_product
								WHERE oc_order_product.order_id = " . $myrow['order_id'] . "
								ORDER BY oc_order_product.order_product_id";
				$resultItemsOrder = DB_query_oc($SQLItemsOrder);
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
						$resultInsert = DB_query($sqlInsert,$InsertErrMsg,$DbgMsg,true);

						// prepare the RL for the items just ordered online
						$sqlUpdate = "UPDATE locstock
										SET reorderlevel = reorderlevel + " . $myitems['quantity'] . "
										WHERE stockid = '" . $myitems['model'] . "'
										AND loccode = '" . $Location . "'";
						$resultUpdate = DB_query($sqlUpdate,$UpdateErrMsg,$DbgMsg,true);
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
					$CouponCode = GetTotalTitleFromOrder("coupon", $myrow['order_id'], $db_oc);
					
					if (strpos(strtoupper($CouponCode),"VBP-") !== false){ 
						// the 100% VIP Cards
						$CouponStockId = OPENCART_VIP_PLATINUM_CODE;
					}else if (strpos(strtoupper($CouponCode),"VBE-") !== false){ 
						// the 50% VIP cards
						$CouponStockId = OPENCART_VIP_ELITE_CODE;
					}else if (strpos(strtoupper($CouponCode),"VBG-") !== false){ 
						// the 30% VIP Cards
						$CouponStockId = OPENCART_VIP_GOLD_CODE;
					}else if (strpos(strtoupper($CouponCode),"VBS-") !== false){
						// the 15% VIP Cards
						$CouponStockId = OPENCART_VIP_SILVER_CODE;
					}else if (strpos(strtoupper($CouponCode),"VN1-") !== false){
						// the 10% online cards
						$CouponStockId = OPENCART_VIP_ONLINE_CODE;
					}else if (strpos(strtoupper($CouponCode),"RF-") !== false){
						// customer refunds for any reason
						$CouponStockId = OPENCART_CUSTOMER_REFUND_CODE;  
					}else if (strpos(strtoupper($CouponCode),"WH-") !== false){
						// wholesale vouches for any reason
						$CouponStockId = OPENCART_WHOLESALE_DISCOUNT;
					}else{
						// any other promotional discount
						$CouponStockId = OPENCART_PROMOTION_DISCOUNT_CODE;
					}
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
						$resultInsert = DB_query($sqlInsert,$InsertErrMsg,$DbgMsg,true);
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
					$DiscountCode = GetTotalTitleFromOrder("discountrule", $myrow['order_id'], $db_oc);
					if (strpos(strtoupper($DiscountCode),"10") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT10;
					}else if (strpos(strtoupper($DiscountCode),"20") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT20;
					}else if (strpos(strtoupper($DiscountCode),"30") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT30;
					}else if (strpos(strtoupper($DiscountCode),"40") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT40;
					}else if (strpos(strtoupper($DiscountCode),"50") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT50;
					}else if (strpos(strtoupper($DiscountCode),"60") !== false){
						$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT60;
					}else{
						$DiscountStockId = OPENCART_WHOLESALE_DISCOUNT;
					}
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
						$resultInsert = DB_query($sqlInsert,$InsertErrMsg,$DbgMsg,true);
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

function SyncPaypalPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order PayPal Payment Information" . "\n" . PrintTimeInformation($db);
	}

	// Now deal with the Paypal payment/s of the order...
	$SQL = "SELECT 	oc_paypal_order.paypal_order_id,
				oc_order.order_id,
				oc_order.currency_code AS ordercurrency,
				oc_order.currency_value,
				oc_order.customer_id,
				oc_order.customer_group_id,
				oc_order.email,
				oc_order.total AS ordertotal,
				oc_paypal_order.paypal_order_id,
				oc_paypal_order.capture_status,
				oc_paypal_order.currency_code AS paypalcurrency,
				oc_paypal_order.authorization_id,
				oc_paypal_order.total AS paypaltotal,
				oc_paypal_order_transaction.transaction_id,
				oc_paypal_order_transaction.date_added,
				oc_paypal_order_transaction.payment_status,
				oc_paypal_order_transaction.pending_reason,
				oc_paypal_order_transaction.transaction_entity,
				oc_paypal_order_transaction.amount,
				oc_paypal_order_transaction.debug_data,
				oc_paypal_order_transaction.call_data
		FROM oc_paypal_order,
			 oc_paypal_order_transaction,
			 oc_order
		WHERE oc_paypal_order.paypal_order_id = oc_paypal_order_transaction.paypal_order_id
				AND oc_paypal_order.order_id  = oc_order.order_id
				AND ( oc_paypal_order.date_added >= '" . $LastTimeRun . "'
					OR oc_paypal_order.date_modified >= '" . $LastTimeRun . "')
		ORDER BY oc_paypal_order.paypal_order_id";
	$result = DB_query_oc($SQL);

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
								<th>' . _('Shipment') . '</th>
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
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($myrow['customer_group_id'], $myrow['ordercurrency'], $db);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $myrow['order_id'], $db);
			$PaymentSystem = OPENCART_DEFAULT_PAYMENT_SYSTEM;
			$CurrencyOrder = $myrow['ordercurrency'];
			$CurrencyPayment = $myrow['paypalcurrency'];
			$TotalOrder = round($myrow['ordertotal'] * $myrow['currency_value'],2); // from OC default currency to order and payment currency
			$Rate = GetWeberpCurrencyRate($CurrencyOrder, $db);
			$AmountPaid = $myrow['paypaltotal'];
			$TransactionID = $myrow['transaction_id'];
			$GLAccount = GetWeberpGLAccountPayPalFromCustomer($CustomerCode, $db);
			$GLCommissionAccount = GetWeberpGLCommissionAccountPayPalFromCustomer($CustomerCode, $db);
			$PayPalResponseArray = GetPaypalReturnDataInArray($myrow['debug_data']);
			$Commission = urldecode($PayPalResponseArray['PAYMENTINFO_0_FEEAMT']);
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $myrow['created'] . -$TimeDifference . ' hours'));
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $myrow['order_id'], $db_oc) * $myrow['currency_value'],$myrow['ordercurrency']);


			if (($myrow['paypalcurrency'] == $myrow['ordercurrency']) AND ($myrow['pending_reason'] == 'None')) {
				// order currency and Paypal currency are the same
				// AND has been paid OK
				$PaymentOK = true;
			}else{
				prnMsg("HORROR: Currency mess in PayPal Payments", "warn");
				$PaymentOK = false;
			}

			if ($PaymentOK){
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
				InsertCustomerReceipt($CustomerCode, $AmountPaid, $FreightCost, $CurrencyPayment, $Rate, $GLAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo, $db);
				TransactionCommissionGL($CustomerCode, $GLAccount, $GLCommissionAccount, $Commission, $CurrencyPayment, $Rate, $PaymentSystem, $TransactionID, $PeriodNo, $db);
				ChangeOrderQuotationFlag($OrderNo, 0, $db); // it has been paid, so we consider it a firm order
				$OnlineOrderNo = GetOnlineOrderNoFromWeberp($OrderNo);
				UpdateOpenCartOrderPayment($OnlineOrderNo);
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
						$FreightCost,
						$myrow['paypalcurrency'],
						$TransactionID,
						$myrow['amount'],
						$Commission,
						$WebERPDateOrder,
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
									      " = " . $FreightCost .
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
		prnMsg(locale_number_format($i,0) . ' ' . _('PayPal Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('PayPal Payments synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function SyncOrderStatus($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Status " . "\n" . PrintTimeInformation($db);
	}

	// Now deal with the Order Status, in case it changed due to payments received or any other weird stuff happening
	$SQL = "SELECT oc_order.order_id,
				oc_order.customer_group_id,
				oc_order.currency_code,
				oc_order_history.order_status_id,
				oc_order_history.comment
			FROM oc_order,
				oc_order_history
			WHERE oc_order.order_id = oc_order_history.order_id
				AND oc_order.order_status_id >= 1
				AND oc_order_history.date_added >= '" . $LastTimeRun . "'
			ORDER BY oc_order.order_id ASC,
					oc_order_history.order_history_id ASC";
	$result = DB_query_oc($SQL);

	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Updated Order Status from OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('OpenCart #') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('OC Status') . '</th>
								<th>' . _('webERP Status') . '</th>
								<th>' . _('Message') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Order Status in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($myrow['customer_group_id'], $myrow['currency_code'], $db);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $myrow['order_id'], $db);
			$webERPStatusText = "";
			$OCStatusText = GetOpenCartStatusTextFromCode($myrow['order_status_id'], $db_oc);

			UpdateOpenCartOrderStatusInWeberp($OrderNo, $myrow['order_status_id']);

			if ($ShowMessages){
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$myrow['order_id'],
						$OrderNo,
						$OCStatusText,
						$webERPStatusText,
						$myrow['comment']
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['order_id'] .
									      " = " . $OrderNo .
									      " --> " . $OCStatusText .
									      " = " . $webERPStatusText .
									      " = " . $myrow['comment'] .
									      " = " . $TotalOrder . "\n";
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
		prnMsg(locale_number_format($i,0) . ' ' . _('Updated Order Status from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('Updated Order Status from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function SyncSnapPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun, $db, $db_oc, $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Snap (MIDTRANS) OpenCart Order Payments " . "\n" . PrintTimeInformation($db);
	}

	$SQL = "SELECT oc_order.order_id,
				oc_order.customer_group_id,
				oc_order.currency_code,
				oc_order.date_modified,
				oc_order.total
			FROM oc_order
			WHERE oc_order.order_status_id = 2
				AND oc_order.payment_code = 'snap'
				AND oc_order.kl_payment_sync_to_weberp = '0000-00-00 00:00:00'
				AND ( oc_order.date_added >= '" . $LastTimeRun . "'
					OR oc_order.date_modified >= '" . $LastTimeRun . "')
			ORDER BY oc_order.order_id ASC";
	$result = DB_query_oc($SQL);

	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . _('Sync Snap (MIDTRANS) OpenCart Order Payments') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('OpenCart #') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('Total Paid') . '</th>
								<th>' . _('Payment Time') . '</th>
								<th>' . _('Result') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Order Status in webERP failed');

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($myrow['customer_group_id'], $myrow['currency_code'], $db);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $myrow['order_id'], $db);

			if ($myrow['currency_code'] == 'IDR'){
				$Result = ProcessPaymentOnlineOrder($OrderNo, 'snap', $CustomerCode, $myrow['total']);
			}else{
				$Result = "ERROR";
			}
			if ($Result != "ERROR"){
				$Result = "OK";
			}
			if ($ShowMessages){
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$myrow['order_id'],
						$OrderNo,
						locale_number_format($myrow['total'],0),
						$myrow['date_modified'],
						$Result
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['order_id'] .
									      " = " . $OrderNo .
									      " --> " . locale_number_format($myrow['total'],0) .
									      " = " . $myrow['date_modified'] .
									      " = " . $Result . "\n";
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
		prnMsg(locale_number_format($i,0) . ' ' . _('snap (MIDTRANS) Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . _('snap (MIDTRANS) Payments synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function EmailOrdersReadyToPrepare($ShowMessages, $db, $EmailText){
	$SectionTitle = "Send Emails to Shop Support Team for orders just received";

	if ($EmailText !=''){
		$EmailText = $EmailText . $SectionTitle . "\n" . PrintTimeInformation($db);
	}

		$SQL = "SELECT salesorders.orderno,	
					salesorders.customerref,
					salesorders.klemailpaymentconfirm,
					salesorders.klemailtrackingconfirm,
					salesorders.klemailthankyouorder,
					debtorsmaster.debtorno,
					salesorders.comments,
					salesorders.orddate,
					salesorders.deliverto AS customername,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.contactphone,
					salesorders.contactemail,
					salesorders.freightcost,
					debtorsmaster.currcode,
					shippers.shippername,
					currencies.decimalplaces
				FROM salesorders 
					INNER JOIN debtorsmaster 
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN shippers 
						ON salesorders.shipvia = shippers.shipper_id
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
				WHERE debtorsmaster.typeid IN (". CUSTOMER_TYPE_WEBSITE . ")
					AND salesorders.quotation = 0
					AND salesorders.klemailpaymentconfirm = '0000-00-00'
				ORDER BY salesorders.orderno";			
	$result = DB_query($SQL);

	if (DB_num_rows($result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . $SectionTitle .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('OpenCart #') . '</th>
								<th>' . _('webERP #') . '</th>
								<th>' . _('Customer') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = 'The SQL to ' . $SectionTitle . ' failed';

		$k = 0; //row colour counter
		$i = 0;
		while ($myrow = DB_fetch_array($result)) {
			if ($ShowMessages){
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
			}
			/* FIELD MATCHING */
			if (substr($myrow['debtorno'],4,2) == "WH"){
				$TypeCustomer = "WHOLESALE";
			}else{
				$TypeCustomer = "RETAIL";
			}
			$Address = BuildAddress($myrow['customername'], 
									$myrow['deladd1'], 
									$myrow['deladd2'], 
									$myrow['deladd3'], 
									$myrow['deladd4'], 
									$myrow['deladd5'], 
									$myrow['deladd6'],
									12);

			$sql = "SELECT salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.grossweight,
					stockmaster.volume,
					salesorderdetails.quantity,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.unitprice,
					salesorderdetails.discountpercent,
					salesorderdetails.narrative,
					salesorderdetails.poline,
					salesorderdetails.itemdue
				FROM salesorderdetails INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				WHERE salesorderdetails.orderno=" . $myrow['orderno'] . "
				ORDER BY poline";
			$result2=DB_query($sql, $ErrMsg);
			$ProductLines = "";
			$TotalModels = 0;
			$TotalPieces = 0;
			$TotalOrder = 0;
			if (DB_num_rows($result2)>0){
				while ($myrow2=DB_fetch_array($result2)){
					$GrossPrice = round($myrow2['unitprice'],$myrow['decimalplaces']);
					$LineTotal = $GrossPrice * $myrow2['quantity'];
					$TotalModels += 1;
					$TotalPieces += $myrow2['quantity'];
					$TotalOrder += $LineTotal;
					$ProductLines .= str_pad($myrow2['quantity'],3," ", STR_PAD_LEFT) . " x " .
									str_pad($myrow2['stkcode'],13, " ", STR_PAD_RIGHT) . 
									str_pad($myrow2['description'],50, " ", STR_PAD_RIGHT) . 
									str_pad(locale_number_format($GrossPrice,$myrow['decimalplaces']),11," ", STR_PAD_LEFT) . 
									str_pad(locale_number_format($LineTotal,$myrow['decimalplaces']),11," ", STR_PAD_LEFT) . 
									" " . $myrow['currcode'] . "\n";
				}
			}
			
			KLSendEmail("PrepareOrderOnline",
						"Silent",
						$myrow['orderno'],
						$myrow['customerref'],
						$Address,
						$myrow['comments'],
						$myrow['shippername'],
						$ProductLines,
						str_pad(locale_number_format($TotalOrder,$myrow['decimalplaces']),12," ", STR_PAD_LEFT),
						str_pad($TotalModels,3," ", STR_PAD_LEFT),
						str_pad($TotalPieces,3," ", STR_PAD_LEFT),
						$TypeCustomer,
						$myrow['currcode'],
						$myrow['contactphone'],
						$myrow['contactemail'],
						$myrow['freightcost']
						);

			// update the sales order, as we start the process
			$sqlUpdate = "UPDATE salesorders 
					SET klemailpaymentconfirm = '" . Date('Y-m-d') . "'
					WHERE orderno =	'" . $myrow['orderno'] . "'";
			$ErrMsg =_('Could not update the sales order KL email payment confirmation date because');
			$resultUpdate = DB_query($sqlUpdate,$ErrMsg);

			if ($ShowMessages){
				printf('<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>',
						$myrow['customerref'],
						$myrow['orderno'],
						$myrow['customername']
						);
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $myrow['customerref'] .
									      " = " . $myrow['orderno'] .
									      " = " . $myrow['customername'] . "\n";
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
		prnMsg(locale_number_format($i,0) . ' ' . $SectionTitle,'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . $SectionTitle . "\n\n";
	}
	return $EmailText;	
	
}

function BuildAddress($Name, $Line1, $Line2, $Line3, $Line4, $Line5, $Line6, $Indent){
	$Address = "";
	if ($Name != ""){
		$Address .= $Name . "\n";
	}
	if ($Line1 != ""){
		$Address .= repeatText(" ", $Indent) .$Line1 . "\n";
	}
	if ($Line2 != ""){
		$Address .= repeatText(" ", $Indent) .$Line2 . "\n";
	}
	if ($Line3 != ""){
		$Address .= repeatText(" ", $Indent) .$Line3 . "\n";
	}
	if ($Line4 != ""){
		$Address .= repeatText(" ", $Indent) .$Line4 . "\n";
	}
	if ($Line5 != ""){
		$Address .= repeatText(" ", $Indent) .$Line5 . "\n";
	}
	if ($Line6 != ""){
		$Address .= repeatText(" ", $Indent) .$Line6 . "\n";
	}
	return $Address;
}


?>