<?php
/* $Revision: 1.3 $ */

$PageSecurity = 1;

include('includes/session.inc');

$title = _('Recurring Orders Process');

include('includes/header.inc');
include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$sql = 'SELECT recurringorders.recurrorderno,
		recurringorders.debtorno,
  		recurringorders.branchcode,
  		recurringorders.customerref,
  		recurringorders.buyername,
  		recurringorders.comments,
  		recurringorders.orddate,
  		recurringorders.ordertype,
  		recurringorders.shipvia,
  		recurringorders.deladd1,
  		recurringorders.deladd2,
  		recurringorders.deladd3,
  		recurringorders.deladd4,
  		recurringorders.contactphone,
  		recurringorders.contactemail,
  		recurringorders.deliverto,
  		recurringorders.freightcost,
  		recurringorders.fromstkloc,
  		recurringorders.lastrecurrence,
  		recurringorders.stopdate,
  		recurringorders.frequency,
  		recurringorders.autoinvoice,
		debtorsmaster.name,
		debtorsmaster.currcode,
		salestypes.sales_type,
		custbranch.area,
		custbranch.taxauthority
	FROM recurringorders,
		debtorsmaster,
		custbranch,
		salestypes
	WHERE recurringorders.ordertype=salestypes.typeabbrev
	AND recurringorders.debtorno = debtorsmaster.debtorno
	AND recurringorders.debtorno = custbranch.debtorno
	AND recurringorders.branchcode = custbranch.branchcode';

$Result = DB_query($sql,$db,_('There was a problem retrieving the recurring sales order templates - the database reported:'));

while ($RecurrOrderRow = DB_fetch_array($Result)){

	$LastRecurrence = ConvertSQLDate($RecurrOrderRow['lastrecurrence']);

	$DaysSinceLastRecurrence = DateDiff($LastRecurrence,Date($_SESSION'DefaultDateFormat']),'d');

	echo '<BR>The number of days since last recurrence for this order is ' . $DaysSinceLastRecurrence;

	$PastStopDate = Date1GreaterThanDate2(ConvertSQLDate($RecurrOrderRow['stopdate']),Date($_SESSION'DefaultDateFormat']));

	if ($DaysSinceLastRecurrence >= (365/$RecurrOrderRow['frequency'] AND ! $PastStopDate){

		$DelDate = FormatDateforSQL(DateAdd($LastRecurrence,'d',365/$RecurrOrderRow['frequency']);

		$HeaderSQL = "INSERT INTO salesorders (
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
					contactphone,
					contactemail,
					freightcost,
					fromstkloc,
					deliverydate )
				VALUES (
					'" . $RecurrOrderRow['debtorno'] . "',
					'" . $RecurrOrderRow['branchcode'] . "',
					'". DB_escape_string($RecurrOrderRow['customerref']) ."',
					'". DB_escape_string($RecurrOrderRow['comments']) ."',
					'" . $DelDate . "',
					'" . $RecurrOrderRow['ordertype'] . "',
					" . $RecurrOrderRow['shipvia'] .",
					'" . DB_escape_string($RecurrOrderRow['deliverto']) . "',
					'" . DB_escape_string($RecurrOrderRow['bradd1']) . "',
					'" . DB_escape_string($RecurrOrderRow['bradd2']) . "',
					'" . DB_escape_string($RecurrOrderRow['bradd3']) . "',
					'" . DB_escape_string($RecurrOrderRow['bradd4']) . "',
					'" . DB_escape_string($RecurrOrderRow['phoneno']) . "',
					'" . DB_escape_string($RecurrOrderRow['email']) . "',
					" . $RecurrOrderRow['freightcost'] .",
					'" . $RecurrOrderRow['location'] ."',
					'" . $DelDate . "')";

		$ErrMsg = _('The order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg);

		$OrderNo = DB_Last_Insert_ID($db,'salesorders','orderno');

		/*need to look up RecurringOrder from the template and populate the line RecurringOrder array with the sales order details records */
		$LineItemsSQL = "SELECT recurrorderdetails.stkcode,
				recurrorderdetails.unitprice,
				recurrorderdetails.quantity,
				recurrorderdetails.discountpercent,
				recurrorderdetails.narrative,
				FROM recurrorderdetails
				WHERE recurrorderdetails.orderno =" . $RecurrOrderRow['recurrorderno'];

		$ErrMsg = _('The line items of the recurring order cannot be retrieved because');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);

		if (db_num_rows($LineItemsResult)>0) {

			$OrderTotal =0; //intialise
			$OrderLineTotal =0;
			$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
							orderno,
							stkcode,
							unitprice,
							quantity,
							discountpercent,
							narrative)
						VALUES (" . $OrderNo . ', ';

			while ($RecurrOrderLineRow=DB_fetch_array($LineItemsResult)) {
				$LineItemsSQL = $StartOf_LineItemsSQL .
						"'" . $RecurrOrderLineRow['stkcode'] . "',
						". $RecurrOrderLineRow['unitprice'] . ',
						' . $RecurrOrderLineRow['quantity'] . ',
						' . floatval($RecurrOrderLineRow['discountpercent']) . ",
						'" . DB_escape_string($RecurrOrderLineRow['narrative']) . "')";

				$Ins_LineItemResult = DB_query($LineItemsSQL,$db);	/*Populating a new order line items*/

			} /* line items from recurring sales order details */
		} //end if there are line items on the recurring order

		prnMsg(_('Recurring order was created for') . ' ' . $RecurrOrderRow['name'] . ' ' . _('with order Number') . ' ' . $OrderNo,'success');

		if ($RecurrOrderRow['autoinvoice']==1){
			/*Only dummy item orders can have autoinvoice =1
                        so no need to worry about assemblies/kitsets/controlled items*/

			/* Now Get the area where the sale is to from the branches table */

			$SQL = "SELECT area,
					defaultshipvia,
					taxauth
				FROM custbranch
				WHERE custbranch.debtorno ='". $RecurrOrderRow['debtorno'] . "'
				AND custbranch.branchcode = '" . $RecurrOrderRow['branchcode'] . "'";

			$ErrMsg = _('Unable to determine the area where the sale is to, from the customr branches table, please select an area for this branch');
			$Result = DB_query($SQL,$db, $ErrMsg);
			$myrow = DB_fetch_row($Result);
			$Area = $myrow[0];
			$DefaultShipVia = $myrow[1];
			$CustTaxAuth = $myrow[2];
			DB_free_result($Result);


			$SQL = 'SELECT rate
                                       FROM currencies INNER JOIN debtorsmaster
                                       ON debtorsmaster.currcode=currencies.currabrev
                                       WHERE debtorno="' . $RecurrOrderRow['debtorno'] . '"';
                        $ErrMsg = _('The exchange rate for the customer currency could not be retrieved from the currency table because:');
                        $Result = DB_query($SQL,$db,$ErrMsg);
                        $myrow = DB_fetch_row($Result);
                        $CurrencyRate = $myrow[0];

                        $SQL = 'SELECT taxauth FROM locations WHERE loccode="' . $RecurrOrderRow['location'] .'"';
                        $ErrMsg = _('Could not retreive the tax authority of the location from where the order was fulfilled because:');
                        $Result = DB_query($SQL,$db,$ErrMsg);
                        $myrow=DB_fetch_row($Result);
                        $DispTaxAuth = $myrow[0];

		/*Now Get the next invoice number - function in SQL_CommonFunctions*/

			$InvoiceNo = GetNextTransNo(10, $db);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);


		/*Start an SQL transaction */

			$SQL = "BEGIN";
			$Result = DB_query($SQL,$db);

			DB_data_seek($LineItemsResult,0);
			while ($RecurrOrderLineRow=DB_fetch_array($LineItemsResult)) {

				$LineNetAmount = $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'] *(1- floatval($RecurrOrderLineRow['discountpercent']));

                                /*Need to get tax level first of the item being invoiced */
                                $SQL = 'SELECT taxlevel,
                                               categoryid
                                        FROM stockmaster
                                        WHERE stockid ="' . DB_escape_string($RecurrOrderLineRow['stkcode']) . '"';
                                $ErrMsg = _('The tax level of the item could not be retrieved because:');
                                $Result = DB_query($SQL,$db,$ErrMsg);
                                $myrow = DB_fetch_row($Result);
                                $TaxLevel = $myrow[0];
                                $CategoryID = $myrow[1];

                                $TaxRate = GetTaxRate($CustTaxAuth,$DispTaxAuth,$TaxLevel);
                                $LineTaxAmount = $TaxRate *$LineNetAmount;

                 		/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */

                                $SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $RecurrOrderLineRow['quantity'] . ",
					actualdispatchdate = '" . $DelDate .  "',
					completed=1
					WHERE orderno = " . $OrderNo . "
					AND stkcode = '" . $RecurrOrderLineRow['StkCode'] . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
					$DbgMsg = _('The following SQL to update the sales order detail record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


					// Insert stock movements - with unit cost

					$LocalCurrencyPrice= ($RecurrOrderLineRow['unitprice'] *(1- floatval($RecurrOrderLineRow['discountpercent'])))/ $CurrencyRate;

                  			// its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
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
								taxrate,
								narrative
								)
							VALUES (
								'" . $RecurrOrderLineRow['stkcode'] . "',
								10,
								" . $InvoiceNo . ",
								'" . $RecurrOrderRow['location'] . "',
								'" . $DelDate . "',
								'" . $RecurrOrderRow['debtorno'] . "',
								'" . $RecurrOrderRow['branchcode'] . "',
								" . $LocalCurrencyPrice . ",
								" . $PeriodNo . ",
								'" . $OrderNo . "',
								" . -$RecurrOrderLineRow['quantity'] . ",
								" . $RecurrOrderLineRow['discountpercent'] . ",
								0,
								" . $TaxRate . ",
								'" . DB_escape_string($RecurrOrderLineRow['narrative'] . "'
							)";
					}


					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
					$DbgMsg = _('The following SQL to insert the stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Insert Sales Analysis records */

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
						AND salesanalysis.area=custbranch.area
						AND salesanalysis.salesperson=custbranch.salesman
						AND salesanalysis.typeabbrev ='" . $_SESSION['Items']->DefaultSalesType . "'
						AND salesanalysis.periodno=" . $PeriodNo . "
						AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items']->DebtorNo . "'
						AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items']->Branch . "'
						AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
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
					$DbgMsg = '<P>'. _('SQL to count the no of sales analysis records');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					$myrow = DB_fetch_row($Result);

					if ($myrow[0]>0){  /*Update the existing record that already exists */

						$SQL = "UPDATE salesanalysis
							SET amt=amt+" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							cost=cost+" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
							qty=qty +" . $OrderLine->QtyDispatched . ",
							disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
							WHERE salesanalysis.area='" . $myrow[2] . "'
							AND salesanalysis.salesperson='" . $myrow[3] . "'
							AND typeabbrev ='" . $_SESSION['Items']->DefaultSalesType . "'
							AND periodno = " . $PeriodNo . "
							AND cust " . LIKE . " '" . $_SESSION['Items']->DebtorNo . "'
							AND custbranch " . LIKE . " '" . $_SESSION['Items']->Branch . "'
							AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
							AND salesanalysis.stkcategory ='" . $myrow[1] . "'
							AND budgetoractual=1";

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
							SELECT '" . $_SESSION['Items']->DefaultSalesType . "',
								" . $PeriodNo . ",
								" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
								" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
								'" . $_SESSION['Items']->DebtorNo . "',
								'" . $_SESSION['Items']->Branch . "',
								" . $OrderLine->QtyDispatched . ",
								" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
								'" . $OrderLine->StockID . "',
								custbranch.area,
								1,
								custbranch.salesman,
								stockmaster.categoryid
							FROM stockmaster,
								custbranch
							WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
							AND custbranch.debtorno = '" . $_SESSION['Items']->DebtorNo . "'
							AND custbranch.branchcode='" . $_SESSION['Items']->Branch . "'";
					}

					$ErrMsg = _('Sales analysis record could not be added or updated because');
					$DbgMsg = _('The following SQL to insert the sales analysis record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

					if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){

		/*first the cost of sales entry*/

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
								" . $InvoiceNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db) . ",
								'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
								" . $OrderLine->StandardCost * $OrderLine->QtyDispatched . "
							)";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
						$DbgMsg = _('The following SQL to insert the GLTrans record was used');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*now the stock entry*/
						$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

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
								" . $InvoiceNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $StockGLCode['stockact'] . ",
								'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
								" . (-$OrderLine->StandardCost * $OrderLine->QtyDispatched) . "
							)";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
						$DbgMsg = _('The following SQL to insert the GLTrans record was used');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					} /* end of if GL and stock integrated and standard cost !=0 */

					if ($_SESSION['CompanyRecord']['gllink_debtors']==1 && $OrderLine->Price !=0){

			//Post sales transaction to GL credit sales
						$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items']->DefaultSalesType, $db);

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
								" . $InvoiceNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $SalesGLAccounts['salesglcode'] . ",
								'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "',
								" . (-$OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']) . "
							)";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
						$DbgMsg = '<BR>' ._('The following SQL to insert the GLTrans record was used');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

						if ($OrderLine->DiscountPercent !=0){

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
									" . $InvoiceNo . ",
									'" . $DefaultDispatchDate . "',
									" . $PeriodNo . ",
									" . $SalesGLAccounts['discountglcode'] . ",
									'" . $_SESSION['Items']->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
									" . ($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . "
								)";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
							$DbgMsg = _('The following SQL to insert the GLTrans record was used');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						} /*end of if discount !=0 */
					} /*end of if sales integrated with debtors */

				} /*Quantity dispatched is more than 0 */
			} /*end of OrderLine loop */


			if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

		/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
				if (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal) !=0) {
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
								" . $InvoiceNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $_SESSION['CompanyRecord']['debtorsact'] . ",
								'" . $_SESSION['Items']->DebtorNo . "',
								" . (($_SESSION['Items']->total + $_POST['ChargeFreightCost'] + $TaxTotal)/$_SESSION['CurrencyRate']) . "
							)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}

				/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

				if ($_POST['ChargeFreightCost'] !=0) {
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
							" . $InvoiceNo . ",
							'" . $DefaultDispatchDate . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']['freightact'] . ",
							'" . $_SESSION['Items']->DebtorNo . "',
							" . (-($_POST['ChargeFreightCost'])/$_SESSION['CurrencyRate']) . "
						)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
				if ($TaxTotal !=0){
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
							" . $InvoiceNo . ",
							'" . $DefaultDispatchDate . "',
							" . $PeriodNo . ",
							" . $_SESSION['TaxGLCode'] . ",
							'" . $_SESSION['Items']->DebtorNo . "',
							" . (-$TaxTotal/$_SESSION['CurrencyRate']) . "
						)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			} /*end of if Sales and GL integrated */




		/*Update order header for invoice charged on */
			$SQL = "UPDATE salesorders SET comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " . $OrderNo;

			$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order header could not be updated with the invoice number');
			$DbgMsg = _('The following SQL to update the sales order was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Now insert the DebtorTrans */

			$SQL = "INSERT INTO debtortrans (
					transno,
					type,
					debtorno,
					branchcode,
					trandate,
					prd,
					reference,
					tpe,
					order_,
					ovamount,
					ovgst,
					ovfreight,
					rate,
					invtext,
					shipvia,
					consignment
					)
				VALUES (
					". $InvoiceNo . ",
					10,
					'" . $RecurrOrderRow['debtorno'] . "',
					'" . $RecurrOrderRow['branchcode'] . "',
					'" . $DelDate . "',
					" . $PeriodNo . ",
					'',
					'" . $RecurrOrderRowDefaultSalesType . "',
					" . $_SESSION['ProcessingOrder'] . ",
					" . $_SESSION['Items']->total . ",
					" . $TaxTotal . ",
					" . $_POST['ChargeFreightCost'] . ",
					" . $CurrencyRate . ",
					'" . $_POST['InvoiceText'] . "',
					" . $_SESSION['Items']->ShipVia . ",
					'"  . $_POST['Consignment'] . "'
				)";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SQL='COMMIT';
			$Result = DB_query($SQL,$db);

			unset($_SESSION['Items']->LineItems);
			unset($_SESSION['Items']);
			unset($_SESSION['ProcessingOrder']);

			echo _('Invoice number'). ' '. $InvoiceNo .' '. _('processed'). '<BR>';
















			
			
			
			
			
			
			
			
			
			
		
		
		
		}
	}/*end if there was a recurring order that are due to have another order made up*/

}/*end while there are recurring order templates to check */






		
?>
