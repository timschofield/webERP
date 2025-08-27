<?php

/* need to allow this script to run from Cron or windows scheduler */
//$AllowAnyone = true;

/* Get this puppy to run from cron (cd webERP && php -f RecurringSalesOrdersProcess.php "weberpdemo") or direct URL (RecurringSalesOrdersProcess.php?Database=weberpdemo) */
if (isset($_GET['Database'])) {
	/// @todo we make this safer, by eg. defining a whitelist of accessible databases...
	$_SESSION['DatabaseName'] = $_GET['Database'];
	$DatabaseName = $_GET['Database'];
	$_POST['CompanyNameField'] = $_GET['Database'];
}

if (isset($argc)) {
	if (isset($argv[1])) {
		$_SESSION['DatabaseName'] = $argv[1];
		$DatabaseName = $argv[1];
		$_POST['CompanyNameField'] = $argv[1];
	}
}

require(__DIR__ . '/includes/session.php');

$Title = __('Recurring Orders Process');
$ViewTopic = "SalesOrders";
$BookMark = "RecurringSalesOrders";
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GetSalesTransGLCodes.php');

$SQL = "SELECT recurringsalesorders.recurrorderno,
			recurringsalesorders.debtorno,
	  		recurringsalesorders.branchcode,
	  		recurringsalesorders.customerref,
	  		recurringsalesorders.buyername,
	  		recurringsalesorders.comments,
	  		recurringsalesorders.orddate,
	  		recurringsalesorders.ordertype,
	  		recurringsalesorders.shipvia,
	  		recurringsalesorders.deladd1,
	  		recurringsalesorders.deladd2,
	  		recurringsalesorders.deladd3,
	  		recurringsalesorders.deladd4,
	  		recurringsalesorders.deladd5,
	  		recurringsalesorders.deladd6,
	  		recurringsalesorders.contactphone,
	  		recurringsalesorders.contactemail,
	  		recurringsalesorders.deliverto,
	  		recurringsalesorders.freightcost,
	  		recurringsalesorders.fromstkloc,
	  		recurringsalesorders.lastrecurrence,
	  		recurringsalesorders.stopdate,
	  		recurringsalesorders.frequency,
	  		recurringsalesorders.autoinvoice,
			debtorsmaster.name,
			debtorsmaster.currcode,
			salestypes.sales_type,
			custbranch.area,
			custbranch.taxgroupid,
			locations.contact,
			locations.email
		FROM recurringsalesorders INNER JOIN locationusers ON locationusers.loccode=recurringsalesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1,
			debtorsmaster,
			custbranch,
			salestypes,
			locations
		WHERE recurringsalesorders.ordertype=salestypes.typeabbrev
		AND recurringsalesorders.debtorno = debtorsmaster.debtorno
		AND recurringsalesorders.debtorno = custbranch.debtorno
		AND recurringsalesorders.branchcode = custbranch.branchcode
		AND recurringsalesorders.fromstkloc=locations.loccode
		AND recurringsalesorders.ordertype=salestypes.typeabbrev
		AND (TO_DAYS(NOW()) - TO_DAYS(recurringsalesorders.lastrecurrence)) > (365/recurringsalesorders.frequency)
		AND DATE_ADD(recurringsalesorders.lastrecurrence, " . INTERVAL ('365/recurringsalesorders.frequency', 'DAY') . ") <= recurringsalesorders.stopdate";

$RecurrOrdersDueResult = DB_query($SQL,__('There was a problem retrieving the recurring sales order templates. The database reported:'));

if (DB_num_rows($RecurrOrdersDueResult)==0){
	prnMsg(__('There are no recurring order templates that are due to have another recurring order created'),'warn');
	include('includes/footer.php');
	exit();
}

prnMsg(__('The number of recurring orders to process is') .' : ' . DB_num_rows($RecurrOrdersDueResult),'info');

while ($RecurrOrderRow = DB_fetch_array($RecurrOrdersDueResult)){

	$EmailText ='';
	echo '<br />' . __('Recurring order') . ' ' . $RecurrOrderRow['recurrorderno'] . ' ' . __('for') . ' ' . $RecurrOrderRow['debtorno'] . ' - ' . $RecurrOrderRow['branchcode'] . ' ' . __('is being processed');

	DB_Txn_Begin();

	/*the last recurrence was the date of the last time the order recurred
	the frequency is the number of times per annum that the order should recurr
	so 365 / frequency gives the number of days between recurrences */

	$DelDate = FormatDateforSQL(DateAdd(ConvertSQLDate($RecurrOrderRow['lastrecurrence']),'d',(365/$RecurrOrderRow['frequency'])));

	echo '<br />' . __('Date calculated for the next recurrence was') .': ' . $DelDate;
	$OrderNo = GetNextTransNo(30);

	$HeaderSQL = "INSERT INTO salesorders (
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
							freightcost,
							fromstkloc,
							deliverydate )
						VALUES (
							'" . $OrderNo . "',
							'" . $RecurrOrderRow['debtorno'] . "',
							'" . $RecurrOrderRow['branchcode'] . "',
							'". $RecurrOrderRow['customerref'] ."',
							'". $RecurrOrderRow['comments'] ."',
							'" . $DelDate . "',
							'" . $RecurrOrderRow['ordertype'] . "',
							'" . $RecurrOrderRow['shipvia'] ."',
							'" . $RecurrOrderRow['deliverto'] . "',
							'" . $RecurrOrderRow['deladd1'] . "',
							'" . $RecurrOrderRow['deladd2'] . "',
							'" . $RecurrOrderRow['deladd3'] . "',
							'" . $RecurrOrderRow['deladd4'] . "',
							'" . $RecurrOrderRow['deladd5'] . "',
							'" . $RecurrOrderRow['deladd6'] . "',
							'" . $RecurrOrderRow['contactphone'] . "',
							'" . $RecurrOrderRow['contactemail'] . "',
							'" . $RecurrOrderRow['freightcost'] ."',
							'" . $RecurrOrderRow['fromstkloc'] ."',
							'" . $DelDate . "')";

	$ErrMsg = __('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL, $ErrMsg,true);

	$EmailText = __('A new order has been created from a recurring order template for customer') .' ' .  $RecurrOrderRow['debtorno'] . ' ' . $RecurrOrderRow['branchcode'] . "\n" . __('The order number is:') . ' ' . $OrderNo;

	/*need to look up RecurringOrder from the template and populate the line RecurringOrder array with the sales order details records */
	$LineItemsSQL = "SELECT recurrsalesorderdetails.stkcode,
							recurrsalesorderdetails.unitprice,
							recurrsalesorderdetails.quantity,
							recurrsalesorderdetails.discountpercent,
							recurrsalesorderdetails.narrative,
							stockmaster.taxcatid
						FROM recurrsalesorderdetails INNER JOIN stockmaster
							ON recurrsalesorderdetails.stkcode = stockmaster.stockid
						WHERE recurrsalesorderdetails.recurrorderno ='" . $RecurrOrderRow['recurrorderno'] . "'";

	$ErrMsg = __('The line items of the recurring order cannot be retrieved because');
	$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

	$LineCounter = 0;

	if (DB_num_rows($LineItemsResult)>0) {

		$OrderTotal =0; //intialise
		$OrderLineTotal =0;
		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
															orderno,
															orderlineno,
															stkcode,
															unitprice,
															quantity,
															discountpercent,
															narrative)
														VALUES ('" . $OrderNo . "', ";

		while ($RecurrOrderLineRow=DB_fetch_array($LineItemsResult)) {
			$LineItemsSQL = $StartOf_LineItemsSQL .
							" '" . $LineCounter . "',
							'" . $RecurrOrderLineRow['stkcode'] . "',
							'". $RecurrOrderLineRow['unitprice'] . "',
							'" . $RecurrOrderLineRow['quantity'] . "',
							'" . floatval($RecurrOrderLineRow['discountpercent']) . "',
							'" . $RecurrOrderLineRow['narrative'] . "')";

			$Ins_LineItemResult = DB_query($LineItemsSQL,__('Could not insert the order lines from the recurring order template'),true);	/*Populating a new order line items*/
			$LineCounter ++;
		} /* line items from recurring sales order details */
	} //end if there are line items on the recurring order

	$SQL = "UPDATE recurringsalesorders SET lastrecurrence = '" . $DelDate . "'
			WHERE recurrorderno='" . $RecurrOrderRow['recurrorderno'] ."'";
	$ErrMsg = __('Could not update the last recurrence of the recurring order template. The database reported the error:');
	$Result = DB_query($SQL, $ErrMsg, true);

	DB_Txn_Commit();

	prnMsg(__('Recurring order was created for') . ' ' . $RecurrOrderRow['name'] . ' ' . __('with order Number') . ' ' . $OrderNo, 'success');

	if ($RecurrOrderRow['autoinvoice']==1){
		/*Only dummy item orders can have autoinvoice =1
		so no need to worry about assemblies/kitsets/controlled items*/

		/* Now Get the area where the sale is to from the branches table */

		$SQL = "SELECT area,
						defaultshipvia
				FROM custbranch
				WHERE custbranch.debtorno ='". $RecurrOrderRow['debtorno'] . "'
				AND custbranch.branchcode = '" . $RecurrOrderRow['branchcode'] . "'";

		$ErrMsg = __('Unable to determine the area where the sale is to, from the customer branches table, please select an area for this branch');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		$Area = $MyRow[0];
		$DefaultShipVia = $MyRow[1];
//		$CustTaxAuth = $MyRow[2];
		DB_free_result($Result);

		$SQL = "SELECT rate
				FROM currencies INNER JOIN debtorsmaster
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE debtorno='" . $RecurrOrderRow['debtorno'] . "'";
		$ErrMsg = __('The exchange rate for the customer currency could not be retrieved from the currency table because:');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		$CurrencyRate = $MyRow[0];

		$SQL = "SELECT taxprovinceid FROM locations WHERE loccode='" . $RecurrOrderRow['fromstkloc'] ."'";
		$ErrMsg = __('Could not retrieve the tax province of the location from where the order was fulfilled because:');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow=DB_fetch_row($Result);
		$DispTaxProvinceID = $MyRow[0];

	/*Now Get the next invoice number - function in SQL_CommonFunctions*/
		$InvoiceNo = GetNextTransNo(10);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	/*Start an SQL transaction */
		DB_Txn_Begin();

		$TotalFXNetInvoice = 0;
		$TotalFXTax = 0;

		DB_data_seek($LineItemsResult,0);

		$LineCounter =0;

		while ($RecurrOrderLineRow = DB_fetch_array($LineItemsResult)) {

			$LineNetAmount = $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] *(1- floatval($RecurrOrderLineRow['discountpercent']));

			/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
			and the taxprovince of the dispatch location */

			$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid='" . $RecurrOrderRow['taxgroupid'] . "'
			AND taxauthrates.dispatchtaxprovince='" . $DispTaxProvinceID . "'
			AND taxauthrates.taxcatid = '" . $RecurrOrderLineRow['taxcatid'] . "'
			ORDER BY taxgrouptaxes.calculationorder";

			$ErrMsg = __('The taxes and rates for this item could not be retrieved because');
			$GetTaxRatesResult = DB_query($SQL, $ErrMsg);

			$LineTaxAmount = 0;
			$TaxTotals =array();

			while ($MyRow = DB_fetch_array($GetTaxRatesResult)){
				if (!isset($TaxTotals[$MyRow['taxauthid']]['FXAmount'])) {
					$TaxTotals[$MyRow['taxauthid']]['FXAmount']=0;
				}
				$TaxAuthID=$MyRow['taxauthid'];
				$TaxTotals[$MyRow['taxauthid']]['GLCode'] = $MyRow['taxglcode'];
				$TaxTotals[$MyRow['taxauthid']]['TaxRate'] = $MyRow['taxrate'];
				$TaxTotals[$MyRow['taxauthid']]['TaxAuthDescription'] = $MyRow['description'];

				if ($MyRow['taxontax'] ==1){
					  $TaxAuthAmount = ($LineNetAmount+$LineTaxAmount) * $MyRow['taxrate'];
					  $TaxTotals[$MyRow['taxauthid']]['FXAmount'] += ($LineNetAmount+$LineTaxAmount) * $MyRow['taxrate'];
				} else {
					$TaxAuthAmount =  $LineNetAmount * $MyRow['taxrate'];
					$TaxTotals[$MyRow['taxauthid']]['FXAmount'] += $LineNetAmount * $MyRow['taxrate'];
				}

				/*Make an array of the taxes and amounts including GLcodes for later posting - need debtortransid
				so can only post once the debtor trans is posted - can only post debtor trans when all tax is calculated */
				$LineTaxes[$LineCounter][$MyRow['calculationorder']] = array('TaxCalculationOrder' =>$MyRow['calculationorder'],
												'TaxAuthID' =>$MyRow['taxauthid'],
												'TaxAuthDescription'=>$MyRow['description'],
												'TaxRate'=>$MyRow['taxrate'],
												'TaxOnTax'=>$MyRow['taxontax'],
												'TaxAuthAmount'=>$TaxAuthAmount);
				$LineTaxAmount += $TaxAuthAmount;

			}

			$LineNetAmount = $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] *(1- floatval($RecurrOrderLineRow['discountpercent']));

			$TotalFXNetInvoice += $LineNetAmount;
			$TotalFXTax += $LineTaxAmount;

			/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */
			$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $RecurrOrderLineRow['quantity'] . ",
						actualdispatchdate = '" . $DelDate .  "',
						completed='1'
				WHERE orderno = '" . $OrderNo . "'
				AND stkcode = '" . $RecurrOrderLineRow['stkcode'] . "'";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales order detail record could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			// Insert stock movements - with unit cost
			$LocalCurrencyPrice= ($RecurrOrderLineRow['unitprice'] *(1- floatval($RecurrOrderLineRow['discountpercent'])))/ $CurrencyRate;

			// its a dummy item dummies always have nil stock (by definition so new qty on hand will be nil
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
						narrative
						)
					VALUES (
						'" . $RecurrOrderLineRow['stkcode'] . "',
						'10',
						'" . $InvoiceNo . "',
						'" . $RecurrOrderRow['fromstkloc'] . "',
						'" . $DelDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $RecurrOrderRow['debtorno'] . "',
						'" . $RecurrOrderRow['branchcode'] . "',
						'" . $LocalCurrencyPrice . "',
						'" . $PeriodNo . "',
						'" . $OrderNo . "',
						'" . -$RecurrOrderLineRow['quantity'] . "',
						'" . $RecurrOrderLineRow['discountpercent'] . "',
						'0',
						'" . $RecurrOrderLineRow['narrative'] . "')";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

			/*Insert the taxes that applied to this line */
			foreach ($LineTaxes[$LineCounter] as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES ('" . $StkMoveNo . "',
							'" . $Tax['TaxAuthID'] . "',
							'" . $Tax['TaxRate'] . "',
							'" . $Tax['TaxCalculationOrder'] . "',
							'" . $Tax['TaxOnTax'] . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Taxes and rates applicable to this invoice line item could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}
			/*Insert Sales Analysis records */

			$SQL="SELECT COUNT(*),
					salesanalysis.stkcategory,
					salesanalysis.area,
					salesanalysis.salesperson,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.stockid
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
				AND salesanalysis.stockid=stockmaster.stockid
				AND salesanalysis.cust=custbranch.debtorno
				AND salesanalysis.custbranch=custbranch.branchcode
				AND salesanalysis.area=custbranch.area
				AND salesanalysis.salesperson=custbranch.salesman
				AND salesanalysis.typeabbrev ='" . $RecurrOrderRow['ordertype'] . "'
				AND salesanalysis.periodno='" . $PeriodNo . "'
				AND salesanalysis.cust " . LIKE . "  '" . $RecurrOrderRow['debtorno'] . "'
				AND salesanalysis.custbranch  " . LIKE . " '" . $RecurrOrderRow['branchcode'] . "'
				AND salesanalysis.stockid  " . LIKE . " '" . $RecurrOrderLineRow['stkcode'] . "'
				AND salesanalysis.budgetoractual='1'
				GROUP BY salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson";

			$ErrMsg = __('The count of existing Sales analysis records could not run because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			$MyRow = DB_fetch_row($Result);

			if ($MyRow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis
					SET amt=amt+" . filter_number_format($RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] / $CurrencyRate) . ",
					qty=qty +" . $RecurrOrderLineRow['quantity'] . ",
					disc=disc+" . filter_number_format($RecurrOrderLineRow['discountpercent'] * $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] / $CurrencyRate) . "
					WHERE salesanalysis.area='" . $MyRow[2] . "'
					AND salesanalysis.salesperson='" . $MyRow[3] . "'
					AND typeabbrev ='" . $RecurrOrderRow['ordertype'] . "'
					AND periodno = '" . $PeriodNo . "'
					AND cust  " . LIKE . " '" . $RecurrOrderRow['debtorno'] . "'
					AND custbranch  " . LIKE . "  '" . $RecurrOrderRow['branchcode'] . "'
					AND stockid  " . LIKE . " '" . $RecurrOrderLineRow['stkcode'] . "'
					AND salesanalysis.stkcategory ='" . $MyRow[1] . "'
					AND budgetoractual='1'";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (
									typeabbrev,
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
									stkcategory
									)
								SELECT '" . $RecurrOrderRow['ordertype']. "',
									'" . $PeriodNo . "',
									'" . filter_number_format($RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] / $CurrencyRate) . "',
									0,
									'" . $RecurrOrderRow['debtorno'] . "',
									'" . $RecurrOrderRow['branchcode'] . "',
									'" . $RecurrOrderLineRow['quantity'] . "',
									'" . filter_number_format($RecurrOrderLineRow['discountpercent'] * $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] / $CurrencyRate) . "',
									'" . $RecurrOrderLineRow['stkcode'] . "',
									custbranch.area,
									1,
									custbranch.salesman,
									stockmaster.categoryid
								FROM stockmaster,
									custbranch
								WHERE stockmaster.stockid = '" . $RecurrOrderLineRow['stkcode'] . "'
								AND custbranch.debtorno = '" . $RecurrOrderRow['debtorno'] . "'
								AND custbranch.branchcode='" . $RecurrOrderRow['branchcode'] . "'";
			}

			$ErrMsg = __('Sales analysis record could not be added or updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $RecurrOrderLineRow['unitprice'] !=0){

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $RecurrOrderLineRow['stkcode'], $RecurrOrderRow['ordertype']);

				$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						)
					VALUES (
						'10',
						'" . $InvoiceNo . "',
						'" . $DelDate . "',
						'" . $PeriodNo . "',
						'" . $SalesGLAccounts['salesglcode'] . "',
						'" . mb_substr($RecurrOrderRow['debtorno'] . " - " . $RecurrOrderLineRow['stkcode'] . " x " . $RecurrOrderLineRow['quantity'] . " @ " . $RecurrOrderLineRow['unitprice'], 0, 200) . "',
						'" . filter_number_format(-$RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity']/$CurrencyRate) . "'
					)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/* Don't care about COGS because it can only be a dummy items being invoiced ... no cost of sales to mess with */

				if ($RecurrOrderLineRow['discountpercent'] !=0){

					$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						)
						VALUES (
							'10',
							'" . $InvoiceNo . "',
							'" . $DelDate . "',
							'" . $PeriodNo . "',
							'" . $SalesGLAccounts['discountglcode'] . "',
							'" . mb_substr($RecurrOrderRow['debtorno'] . " - " . $RecurrOrderLineRow['stkcode'] . ' @ ' . ($RecurrOrderLineRow['discountpercent'] * 100) . "%", 0, 200) . "',
							'" . filter_number_format($RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] * $RecurrOrderLineRow['discountpercent']/$CurrencyRate) . "'
						)";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales discount GL posting could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);

				} /*end of if discount !=0 */

			} /*end of if sales integrated with gl */

			$LineCounter++;
		} /*end of OrderLine loop */

		$TotalInvLocalCurr = ($TotalFXNetInvoice + $TotalFXTax + $RecurrOrderRow['freightcost'])/$CurrencyRate;

		if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

			/*Now post the tax to the GL at local currency equivalent */
			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $TaxAuthAmount !=0) {


				/*Loop through the tax authorities array to post each total to the taxauth glcode */
				foreach ($TaxTotals as $Tax){
					$SQL = "INSERT INTO gltrans (
											type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount
											)
											VALUES (
											10,
											'" . $InvoiceNo . "',
											'" . $DelDate. "',
											'" . $PeriodNo . "',
											'" . $Tax['GLCode'] . "',
											'" . mb_substr($RecurrOrderRow['debtorno'] . "-" . $Tax['TaxAuthDescription'], 0, 200) . "',
											'" . filter_number_format(-$Tax['FXAmount']/$CurrencyRate) . "'
											)";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The tax GL posting could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}
			}

			/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
			if (($TotalInvLocalCurr) !=0) {
				$SQL = "INSERT INTO gltrans (
										type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount
										)
									VALUES (
										'10',
										'" . $InvoiceNo . "',
										'" . $DelDate . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
										'" . mb_substr($RecurrOrderRow['debtorno'], 0, 200) . "',
										'" . filter_number_format($TotalInvLocalCurr) . "'
									)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The total debtor GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}

			/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

			if ($RecurrOrderRow['freightcost'] !=0) {
				$SQL = "INSERT INTO gltrans (
											type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										10,
										'" . $InvoiceNo . "',
										'" . $DelDate . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['freightact'] . "',
										'" . mb_substr($RecurrOrderRow['debtorno'], 0, 200) . "',
										'" . filter_number_format(-$RecurrOrderRow['freightcost']/$CurrencyRate) . "'
									)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The freight GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}
		} /*end of if Sales and GL integrated */

	/*Update order header for invoice charged on */
		$SQL = "UPDATE salesorders SET comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= '" . $OrderNo . "'";

		$ErrMsg = __('CRITICAL ERROR') . ' ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales order header could not be updated with the invoice number');
		$Result = DB_query($SQL, $ErrMsg, '', true);

	/*Now insert the DebtorTrans */

		$SQL = "INSERT INTO debtortrans (
										transno,
										type,
										debtorno,
										branchcode,
										trandate,
										inputdate,
										prd,
										reference,
										tpe,
										order_,
										ovamount,
										ovgst,
										ovfreight,
										rate,
										invtext,
										shipvia)
									VALUES (
										'". $InvoiceNo . "',
										10,
										'" . $RecurrOrderRow['debtorno'] . "',
										'" . $RecurrOrderRow['branchcode'] . "',
										'" . $DelDate . "',
										'" . date('Y-m-d H-i-s') . "',
										'" . $PeriodNo . "',
										'" . $RecurrOrderRow['customerref'] . "',
										'" . $RecurrOrderRow['sales_type'] . "',
										'" . $OrderNo . "',
										'" . filter_number_format($TotalFXNetInvoice) . "',
										'" . filter_number_format($TotalFXTax) . "',
										'" . filter_number_format($RecurrOrderRow['freightcost']) . "',
										'" . filter_number_format($CurrencyRate) . "',
										'" . $RecurrOrderRow['comments'] . "',
										'" . $RecurrOrderRow['shipvia'] . "')";

		$ErrMsg =__('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The debtor transaction record could not be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$DebtorTransID = DB_Last_Insert_ID('debtortrans','id');

		$SQL = "INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES ('" . $DebtorTransID . "',
					'" . $TaxAuthID . "',
					'" . filter_number_format($Tax['FXAmount']/$CurrencyRate) . "')";

		$ErrMsg =__('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The debtor transaction taxes records could not be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();

		prnMsg(__('Invoice number'). ' '. $InvoiceNo .' '. __('processed'),'success');

		$EmailText .= "\n" . __('This recurring order was set to produce the invoice automatically on invoice number') . ' ' . $InvoiceNo;
	} /*end if the recurring order is set to auto invoice */

	if (IsEmailAddress($RecurrOrderRow['email'])){
		$From = $_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] . ">";
		$To = $RecurrOrderRow['email'];
		$Subject = __('Recurring Order Created Advice');
		$Body = $EmailText;

		if (!SendEmailFromWebERP($From, $To, $Subject, $Body)) {
			prnMsg(__('Failed to send email advice for this order to') . ' ' . $To, 'error');
		}

	} else {
		prnMsg(__('No email advice was sent for this order because the location has no email contact defined with a valid email address'),'warn');
	}

}/*end while there are recurring orders due to have a new order created */

include('includes/footer.php');
