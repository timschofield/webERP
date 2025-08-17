<?php

function OpenCartToWeberpSync($ShowMessages , $EmailText=''){
	$begintime = time_start();

	// connect to opencart DB
	DB_Txn_Begin();

	// check last time we run this script, so we know which records need to update from OC to webERP
	$LastTimeRun = CheckLastTimeRun('OpenCartToWeberp');
	$TimeDifference = Get_SQL_OC_to_PHP_time_difference();
	
	if ($ShowMessages){
		prnMsg('This script was last run on: ' . $LastTimeRun . ' webERP Database time.','success');
		prnMsg('Server time difference(PHP and OpenCart Database): ' . $TimeDifference,'success');
		prnMsg('OpenCart Database Server time (JKT Time as DOKU requirements) now: ' . GetServerTimeNow($TimeDifference) ,'success');
	}
	if ($EmailText!=''){
		$EmailText = $EmailText . 'OpenCart to webERP Sync was last run on: ' . $LastTimeRun .  "\n" .
					PrintTimeInformation();
	}
	// update order information
	$EmailText = SyncOrderInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText);

	// update payment information by PayPal
	$EmailText = SyncPaypalPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText);

	// update payment information by snap Midtrans 
	$EmailText = SyncSnapPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText);

	// update order status from OpenCart to webERP
	$EmailText = SyncOrderStatus($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText);

	// send emails to team of new orders arrived
	$EmailText = EmailOrdersReadyToPrepare($ShowMessages, $EmailText);

	// We are done!
	SetLastTimeRun('OpenCartToWeberp');
	DB_Txn_Commit();
	if ($ShowMessages){
		time_finish($begintime);
	}
	return $EmailText;
}

function SyncOrderInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Information" . "\n" . PrintTimeInformation();
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

	$Result = DB_query_oc($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . __('Orders from OpenCart') .'</strong></p>';
			echo '<div>';
			$TableHeader = '<thead>
								<tr>
									<th>' . __('OC #') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('Name') . '</th>
									<th>' . __('eMail') . '</th>
									<th>' . __('Shipping Cost') . '</th>
									<th>' . __('Shipper') . '</th>
									<th>' . __('Currency') . '</th>
									<th>' . __('Payment Code') . '</th>
									<th>' . __('Country') . '</th>
									<th>' . __('Action') . '</th>
								</tr>
							</thead>
							<tbody>';

			$TableHeaderForItems = '<thead>
								<tr>
									<th>' . __('OC #') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('OrderLine') . '</th>
									<th>' . __('Code') . '</th>
									<th>' . __('Unit Price') . '</th>
									<th>' . __('Quantity') . '</th>
									<th>' . __('Action') . '</th>
								</tr>
							</thead>
							<tbody>';
		}
		$UpdateErrMsg = __('The SQL to update OpenCart orders in webERP failed');
		$InsertErrMsg = __('The SQL to insert OpenCart orders in webERP failed');

		
		while ($MyRow = DB_fetch_array($Result)) {
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $MyRow['currency_code']);
			$CustomerName = CleanStringForWebERP(CapitalizeName($MyRow['customerfirstname'] . ' ' . $MyRow['customerlastname']));
			$PaymentName = CleanStringForWebERP(CapitalizeName($MyRow['paymentfirstname'] . ' ' . $MyRow['paymentlastname']));
			$ShippingName = CleanStringForWebERP(CapitalizeName($MyRow['shippingfirstname'] . ' ' . $MyRow['shippinglastname']));
			$SalesType = OPENCART_DEFAULT_CUSTOMER_SALES_TYPE;
			$DefaultShipVia = GetWeberpShippingMethod($MyRow['shipping_code']);
			if ($CustomerCode == "WEB-WH-IDR"){
				$Quotation = 0; // wholesale customers in IDR, are exceptions: or they pay regularly by bank transfer or they paid / will pay in IDR at the shop at pick up time. 
								// Either case we are aware of the order.
			}else{
				$Quotation = 1; // is NOT a firm order until we check the payments
			}
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $MyRow['order_id']) * $MyRow['currency_value'],$MyRow['currency_code']);
			$CouponDiscount = RoundPriceFromCart(GetTotalFromOrder("coupon", $MyRow['order_id']) * $MyRow['currency_value'],$MyRow['currency_code']);
			$OrderDiscount = RoundPriceFromCart(GetTotalFromOrder("discountrule", $MyRow['order_id']) * $MyRow['currency_value'],$MyRow['currency_code']);
			$OpenCartOrderNumber = $MyRow['order_id'];
			$Salesman = OPENCART_DEFAULT_SALESMAN;
			$Location = OPENCART_DEFAULT_LOCATION;
			$Comments =  CleanStringForWebERP($MyRow['comment']);
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $MyRow['date_modified'] . -$TimeDifference . ' hours'));
			$Area = GetAreaFromCustomer($CustomerCode);
			$PaymentCode = $MyRow['payment_code'];
			
			if($DefaultShipVia == 10){
				// if shipping is "Pickup From Store"
				$ShippingAddress1 = "Pick up from store";
				$ShippingAddress2 = "";
				$ShippingCity     = "";
				$ShippingZone	  = "";
				$ShippingPostCode = "";
				$ShippingCountry  = DB_escape_string($MyRow['shipping_country']);
			}else{
				// any other shipping method, we need the details
				$ShippingAddress1 = DB_escape_string($MyRow['shipping_address_1']);
				$ShippingAddress2 = DB_escape_string($MyRow['shipping_address_2']);
				$ShippingCity     = DB_escape_string($MyRow['shipping_city']);
				$ShippingZone     = DB_escape_string($MyRow['shipping_zone']);
				$ShippingPostCode = DB_escape_string($MyRow['shipping_postcode']);
				$ShippingCountry  = DB_escape_string($MyRow['shipping_country']);
			}

			if ($CustomerCode != 'Error'){
				// First process order header
				if (DataExistsInWebERP('salesorders', 'debtorno', $CustomerCode, 'customerref', $MyRow['order_id'])){
					$Action = "Update";
				}else{
					$Action = "Insert";
					do {
						$OrderNo = GetNextSequenceNo(30);
						$CheckDoesntExistResult = DB_query("SELECT count(*) FROM salesorders WHERE orderno='" . $OrderNo . "'");
						$CheckDoesntExistRow = DB_fetch_row($CheckDoesntExistResult);
					} while ($CheckDoesntExistRow[0]==1);

					$SQLInsert = "INSERT INTO salesorders (
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
									'" . DB_escape_string($MyRow['telephone']) . "',
									'" . DB_escape_string($MyRow['email']). "',
									'" . $Salesman . "',
									'" . $Location ."',
									'" . $FreightCost ."',
									'" . $Quotation ."',
									'" . $Area ."',
									'" . $PaymentCode ."',
									'" . $MyRow['date_modified'] . "',
									'" . $MyRow['date_modified'] . "',
									'" . $MyRow['date_modified'] . "')";
					DB_query($SQLInsert,$InsertErrMsg,'',true);
				}
				if ($ShowMessages){
					echo '<table class="selection">' . $TableHeader;
					echo '<tr class="striped_row">
							<td>' . $MyRow['order_id'] . '</td>
							<td>' . $OrderNo . '</td>
							<td>' . $ShippingName . '</td>
							<td>' . $MyRow['email'] . '</td>
							<td>' . $FreightCost . '</td>
							<td>' . $DefaultShipVia . '</td>
							<td>' . $MyRow['currency_code'] . '</td>
							<td>' . $PaymentCode . '</td>
							<td>' . $ShippingCountry . '</td>
							<td>' . $Action . '</td>
						</tr>';
				}
				if ($EmailText !=''){
					$EmailText = $EmailText . $MyRow['order_id'] .
											  " = " . $OrderNo .
											  " = " . $ShippingName .
											  " = " . $MyRow['email'] .
											  " = " . $MyRow['currency_code'] .
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
								WHERE oc_order_product.order_id = " . $MyRow['order_id'] . "
								ORDER BY oc_order_product.order_product_id";
				$ResultItemsOrder = DB_query_oc($SQLItemsOrder);
				$ItemsOrder = 0;
				if ($ShowMessages){
					echo '</table>
							<table class="selection">
							' . $TableHeaderForItems;
					echo '<tr class="striped_row">';
				}
				while ($myitems = DB_fetch_array($ResultItemsOrder)) {
					$ItemsOrder++;
					if ($Action == "Update"){
						$Action = "Update";
					}else{
						$Price = RoundPriceFromCart($myitems['price'] * $MyRow['currency_value'],$MyRow['currency_code']);
						$SQLInsert = "INSERT INTO salesorderdetails
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
											'" . $MyRow['date_modified'] . "',
											'0')"; // prices come already net from OpenCart
						DB_query($SQLInsert,$InsertErrMsg,'',true);

						// prepare the RL for the items just ordered online
						$SQLUpdate = "UPDATE locstock
										SET reorderlevel = reorderlevel + " . $myitems['quantity'] . "
										WHERE stockid = '" . $myitems['model'] . "'
										AND loccode = '" . $Location . "'";
						DB_query($SQLUpdate,$UpdateErrMsg,'',true);
						if ($ShowMessages){
							echo '<td>' . $MyRow['order_id'] . '</td>
									<td>' . $OrderNo . '</td>
									<td>' . $ItemsOrder . '</td>
									<td>' . $myitems['model'] . '</td>
									<td>' . $Price . '</td>
									<td>' . $myitems['quantity'] . '</td>
									<td>' . $Action . '</td>
									</tr>';
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
					$CouponCode = GetTotalTitleFromOrder("coupon", $MyRow['order_id']);
					
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
						$SQLInsert = "INSERT INTO salesorderdetails
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
											'" . $MyRow['date_modified'] . "',
											'" . $CouponCode . "',
											'0')"; // prices come already net from OpenCart
						DB_query($SQLInsert,$InsertErrMsg,'',true);
						if ($ShowMessages){
							echo '<td>' . $MyRow['order_id'] . '</td>
									<td>' . $OrderNo . '</td>
									<td>' . $ItemsOrder . '</td>
									<td>' . $CouponStockId . '</td>
									<td>' . $CouponDiscount . '</td>
									<td>' . $CouponQty . '</td>
									<td>' . $Action . '</td>
									</tr>';
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
					$DiscountCode = GetTotalTitleFromOrder("discountrule", $MyRow['order_id']);
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
						$SQLInsert = "INSERT INTO salesorderdetails
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
											'" . $MyRow['date_modified'] . "',
											'" . $DiscountCode . "',
											'0')"; // prices come already net from OpenCart
						DB_query($SQLInsert,$InsertErrMsg,'',true);
						if ($ShowMessages){
							echo '<td>' . $MyRow['order_id'] . '</td>
									<td>' . $OrderNo . '</td>
									<td>' . $ItemsOrder . '</td>
									<td>' . $DiscountStockId . '</td>
									<td>' . $OrderDiscount . '</td>
									<td>' . $DiscountQty . '</td>
									<td>' . $Action . '</td>
									</tr>';
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
					echo '</tbody>
							</table>
							</table>';
				}
			}else{
				// Order does not belong to a valid customer for any reason, escape it
				if ($ShowMessages){
					prnMsg('Sales Order from ' . $MyRow['email'] .' is not valid as is not a valid currency code.', 'warn');
				}
			}
		}
		if ($ShowMessages){
			echo '</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . __('Orders synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . __('Orders synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function SyncPaypalPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order PayPal Payment Information" . "\n" . PrintTimeInformation();
	}

	// Now deal with the Paypal payment/s of the order...

	// Lock OpenCart tables to ensure consistency during read and subsequent updates within this function's scope
	$ErrMsg = __('Failed to lock OpenCart PayPal related tables.');
	$LockQuery = "LOCK TABLES oc_paypal_order READ, oc_paypal_order_transaction READ, oc_order WRITE, oc_order_total READ";
	DB_query_oc($LockQuery, $ErrMsg, '', true);

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
	$Result = DB_query_oc($SQL);
	$i = 0;

	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . __('Paypal Payments from OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<thead>
								<tr>
									<th>' . __('CustomerID') . '</th>
									<th>' . __('email') . '</th>
									<th>' . __('webERP Code') . '</th>
									<th>' . __('OrderID') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('Order Total') . '</th>
									<th>' . __('Order Curr') . '</th>
									<th>' . __('Paypal Total') . '</th>
									<th>' . __('Shipment') . '</th>
									<th>' . __('Paypal Curr') . '</th>
									<th>' . __('Paypal Trx') . '</th>
									<th>' . __('Trx Total') . '</th>
									<th>' . __('Commission') . '</th>
									<th>' . __('Date') . '</th>
									<th>' . __('Status') . '</th>
									<th>' . __('Pending reason') . '</th>
								</tr>
							</thead>
							<tbody>';
			echo $TableHeader;
		}
	
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				
					echo '<tr class="striped_row">';
					
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $MyRow['ordercurrency']);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $MyRow['order_id']);
			$PaymentSystem = OPENCART_DEFAULT_PAYMENT_SYSTEM;
			$CurrencyOrder = $MyRow['ordercurrency'];
			$CurrencyPayment = $MyRow['paypalcurrency'];
			$TotalOrder = round($MyRow['ordertotal'] * $MyRow['currency_value'],2); // from OC default currency to order and payment currency
			$Rate = GetWeberpCurrencyRate($CurrencyOrder);
			$AmountPaid = $MyRow['paypaltotal'];
			$TransactionID = $MyRow['transaction_id'];
			$GLAccount = GetWeberpGLAccountPayPalFromCustomer($CustomerCode);
			$GLCommissionAccount = GetWeberpGLCommissionAccountPayPalFromCustomer($CustomerCode);
			$PayPalResponseArray = GetPaypalReturnDataInArray($MyRow['debug_data']);
			$Commission = urldecode($PayPalResponseArray['PAYMENTINFO_0_FEEAMT']);
			$WebERPDateOrder = date('Y-m-d H:i:s', strtotime( $MyRow['created'] . -$TimeDifference . ' hours'));
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $MyRow['order_id']) * $MyRow['currency_value'],$MyRow['ordercurrency']);


			if (($MyRow['paypalcurrency'] == $MyRow['ordercurrency']) AND ($MyRow['pending_reason'] == 'None')) {
				// order currency and Paypal currency are the same
				// AND has been paid OK
				$PaymentOK = true;
			}else{
				prnMsg("HORROR: Currency mess in PayPal Payments", "warn");
				$PaymentOK = false;
			}

			if ($PaymentOK){
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
				InsertCustomerReceipt($CustomerCode, $AmountPaid, $FreightCost, $CurrencyPayment, $Rate, $GLAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo);
				TransactionCommissionGL($CustomerCode, $GLAccount, $GLCommissionAccount, $Commission, $CurrencyPayment, $Rate, $PaymentSystem, $TransactionID, $PeriodNo);
				ChangeOrderQuotationFlag($OrderNo, 0); // it has been paid, so we consider it a firm order
				$OnlineOrderNo = GetOnlineOrderNoFromWeberp($OrderNo);
				UpdateOpenCartOrderPayment($OnlineOrderNo);
			}

			if ($ShowMessages){
				echo '<td class="number">' . $MyRow['customer_id'] . '</td>
						<td>' . $MyRow['email'] . '</td>
						<td>' . $CustomerCode . '</td>
						<td class="number">' . $MyRow['order_id'] . '</td>
						<td class="number">' . $OrderNo . '</td>
						<td class="number">' . $TotalOrder . '</td>
						<td>' . $MyRow['ordercurrency'] . '</td>
						<td class="number">' . $AmountPaid . '</td>
						<td class="number">' . $FreightCost . '</td>
						<td>' . $MyRow['paypalcurrency'] . '</td>
						<td>' . $TransactionID . '</td>
						<td class="number">' . $MyRow['amount'] . '</td>
						<td class="number">' . $Commission . '</td>
						<td>' . $WebERPDateOrder . '</td>
						<td>' . $MyRow['payment_status'] . '</td>
						<td>' . $MyRow['pending_reason'] . '</td>
						</tr>';
			}
			if ($EmailText !=''){
				$Action = "Paypal Payment Synchronized";
				$EmailText = $EmailText . $MyRow['customer_id'] .
									      " = " . $MyRow['email'] .
									      " = " . $CustomerCode .
									      " = " . $MyRow['order_id'] .
									      " = " . $TotalOrder .
									      " = " . $MyRow['ordercurrency'] .
									      " = " . $AmountPaid .
									      " = " . $FreightCost .
									      " = " . $MyRow['payment_status'] .
										  " --> " . $Action . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</tbody>
					</table>
					</div>
					</form>';
		}
	}
	// Unlock OpenCart tables
	$ErrMsg = __('Failed to unlock OpenCart tables.');
	DB_query_oc("UNLOCK TABLES", $ErrMsg, '', true);

	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . __('PayPal Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . __('PayPal Payments synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function SyncOrderStatus($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync OpenCart Order Status " . "\n" . PrintTimeInformation();
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
	$Result = DB_query_oc($SQL);
	$i = 0;

	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . __('Updated Order Status from OpenCart') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<thead>
								<tr>
									<th>' . __('OpenCart #') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('OC Status') . '</th>
									<th>' . __('webERP Status') . '</th>
									<th>' . __('Message') . '</th>
								</tr>
							</thead>
							<tbody>';
			echo $TableHeader;
		}

		
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages){
				
					echo '<tr class="striped_row">';
					
			}
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $MyRow['currency_code']);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $MyRow['order_id']);
			$webERPStatusText = "";
			$OCStatusText = GetOpenCartStatusTextFromCode($MyRow['order_status_id']);

			UpdateOpenCartOrderStatusInWeberp($OrderNo, $MyRow['order_status_id']);

			if ($ShowMessages){
				echo '<td class="number">' . $MyRow['order_id'] . '</td>
						<td class="number">' . $OrderNo . '</td>
						<td>' . $OCStatusText . '</td>
						<td>' . $webERPStatusText . '</td>
						<td>' . $MyRow['comment'] . '</td>
						</tr>';
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $MyRow['order_id'] .
									      " = " . $OrderNo .
									      " --> " . $OCStatusText .
									      " = " . $webERPStatusText .
									      " = " . $MyRow['comment'] . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</tbody>
					</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . __('Updated Order Status from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . __('Updated Order Status from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function SyncSnapPaymentInformation($TimeDifference, $ShowMessages, $LastTimeRun , $EmailText=''){

	if ($EmailText !=''){
		$EmailText = $EmailText . "Sync Snap (MIDTRANS) OpenCart Order Payments " . "\n" . PrintTimeInformation();
	}

	$SQL = "SELECT oc_order.order_id,
				oc_order.customer_group_id,
				oc_order.currency_code,
				oc_order.date_modified,
				oc_order.total
			FROM oc_order
			WHERE oc_order.order_status_id = 2
				AND oc_order.payment_code = 'snap'
				AND oc_order.kl_payment_sync_to_weberp = '1000-01-01 00:00:00'
				AND ( oc_order.date_added >= '" . $LastTimeRun . "'
					OR oc_order.date_modified >= '" . $LastTimeRun . "')
			ORDER BY oc_order.order_id ASC";
	$Result = DB_query_oc($SQL);
	$i = 0;

	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . __('Sync Snap (MIDTRANS) OpenCart Order Payments') .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<thead>
								<tr>
									<th>' . __('OpenCart #') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('Total Paid') . '</th>
									<th>' . __('Payment Time') . '</th>
									<th>' . __('Result') . '</th>
								</tr>
							</thead>
							<tbody>';
			echo $TableHeader;
		}
		
		while ($MyRow = DB_fetch_array($Result)) {
			/* FIELD MATCHING */
			$CustomerCode = GetWeberpCustomerIdFromCustomerGroupAndCurrency($MyRow['customer_group_id'], $MyRow['currency_code']);
			$OrderNo = GetWeberpOrderNo($CustomerCode, $MyRow['order_id']);

			if ($MyRow['currency_code'] == 'IDR'){
				$Result = ProcessPaymentOnlineOrder($OrderNo, 'snap', $CustomerCode, $MyRow['total']);
			}else{
				$Result = "ERROR";
			}
			if ($Result != "ERROR"){
				$Result = "OK";
			}
			if ($ShowMessages){
				echo '<tr class="striped_row">
						<td class="number">' . $MyRow['order_id'] . '</td>
						<td class="number">' . $OrderNo . '</td>
						<td>' . locale_number_format($MyRow['total'],0) . '</td>
						<td>' . $MyRow['date_modified'] . '</td>
						<td>' . $Result . '</td>
					</tr>';
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $MyRow['order_id'] .
									      " = " . $OrderNo .
									      " --> " . locale_number_format($MyRow['total'],0) .
									      " = " . $MyRow['date_modified'] .
									      " = " . $Result . "\n";
			}
			$i++;
		}
		if ($ShowMessages){
			echo '</tbody>
					</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages){
		prnMsg(locale_number_format($i,0) . ' ' . __('snap (MIDTRANS) Payments synchronized from OpenCart to webERP'),'success');
	}
	if ($EmailText !=''){
		$EmailText = $EmailText . locale_number_format($i,0) . ' ' . __('snap (MIDTRANS) Payments synchronized from OpenCart to webERP') . "\n\n";
	}
	return $EmailText;
}

function EmailOrdersReadyToPrepare($ShowMessages, $EmailText){
	$SectionTitle = "Send Emails to Shop Support Team for orders just received";

	if ($EmailText !=''){
		$EmailText = $EmailText . $SectionTitle . "\n" . PrintTimeInformation();
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
					AND salesorders.klemailpaymentconfirm = '1000-01-01'
				ORDER BY salesorders.orderno";			
	$Result = DB_query($SQL);
	$i = 0;

	if (DB_num_rows($Result) != 0){
		if ($ShowMessages){
			echo '<p class="page_title_text" align="center"><strong>' . $SectionTitle .'</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<thead>
								<tr>
									<th>' . __('OpenCart #') . '</th>
									<th>' . __('webERP #') . '</th>
									<th>' . __('Customer') . '</th>
								</tr>
							</thead>
							<tbody>';
			echo $TableHeader;
		}

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
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
			if (substr($MyRow['debtorno'],4,2) == "WH"){
				$TypeCustomer = "WHOLESALE";
			}else{
				$TypeCustomer = "RETAIL";
			}
			$Address = BuildAddress($MyRow['customername'], 
									$MyRow['deladd1'], 
									$MyRow['deladd2'], 
									$MyRow['deladd3'], 
									$MyRow['deladd4'], 
									$MyRow['deladd5'], 
									$MyRow['deladd6'],
									12);

			$SQL = "SELECT salesorderdetails.stkcode,
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
				WHERE salesorderdetails.orderno=" . $MyRow['orderno'] . "
				ORDER BY poline";
			$Result2=DB_query($SQL);
			$ProductLines = "";
			$TotalModels = 0;
			$TotalPieces = 0;
			$TotalOrder = 0;
			if (DB_num_rows($Result2)>0){
				while ($MyRow2=DB_fetch_array($Result2)){
					$GrossPrice = round($MyRow2['unitprice'],$MyRow['decimalplaces']);
					$LineTotal = $GrossPrice * $MyRow2['quantity'];
					$TotalModels += 1;
					$TotalPieces += $MyRow2['quantity'];
					$TotalOrder += $LineTotal;
					$ProductLines .= str_pad($MyRow2['quantity'],3," ", STR_PAD_LEFT) . " x " .
									str_pad($MyRow2['stkcode'],13, " ", STR_PAD_RIGHT) . 
									str_pad($MyRow2['description'],50, " ", STR_PAD_RIGHT) . 
									str_pad(locale_number_format($GrossPrice,$MyRow['decimalplaces']),11," ", STR_PAD_LEFT) . 
									str_pad(locale_number_format($LineTotal,$MyRow['decimalplaces']),11," ", STR_PAD_LEFT) . 
									" " . $MyRow['currcode'] . "\n";
				}
			}
			
			KLSendEmail("PrepareOrderOnline",
						"Silent",
						$MyRow['orderno'],
						$MyRow['customerref'],
						$Address,
						$MyRow['comments'],
						$MyRow['shippername'],
						$ProductLines,
						str_pad(locale_number_format($TotalOrder,$MyRow['decimalplaces']),12," ", STR_PAD_LEFT),
						str_pad($TotalModels,3," ", STR_PAD_LEFT),
						str_pad($TotalPieces,3," ", STR_PAD_LEFT),
						$TypeCustomer,
						$MyRow['currcode'],
						$MyRow['contactphone'],
						$MyRow['contactemail'],
						$MyRow['freightcost']
						);

			// update the sales order, as we start the process
			$SQLUpdate = "UPDATE salesorders 
					SET klemailpaymentconfirm = CURRENT_DATE
					WHERE orderno =	'" . $MyRow['orderno'] . "'";
			$ErrMsg =__('Could not update the sales order KL email payment confirmation date because');
			DB_query($SQLUpdate,$ErrMsg);

			if ($ShowMessages){
				echo '<td class="number">' . $MyRow['customerref'] . '</td>
						<td class="number">' . $MyRow['orderno'] . '</td>
						<td>' . $MyRow['customername'] . '</td>
						</tr>';
			}
			if ($EmailText !=''){
				$EmailText = $EmailText . $MyRow['customerref'] .
									      " = " . $MyRow['orderno'] .
									      " = " . $MyRow['customername'] . "\n";
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
