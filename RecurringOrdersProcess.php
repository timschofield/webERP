<?php
/* $Revision: 1.2 $ */

$PageSecurity = 1;

include('includes/session.inc');

$title = _('Recurring Orders Process');

include('includes/DefineCartClass.php');
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
				$OrderLineTotal = $RecurrOrderLineRow['unitprice'] * $RecurrOrderLineRow['quantity'];
				$RecurrOrderLineRow['OrderLineTotal'] = $OrderLineTotal;
				
			} /* line items from recurring sales order details */
		} //end if there are line items on the recurring order
		
		prnMsg(_('Recurring order was created for') . ' ' . $RecurrOrderRow['name'] . ' ' . _('with order Number') . ' ' . $OrderNo,'success');
	
		if ($RecurrOrderRow['autoinvoice']==1){ 
			/*Only dummy item orders can have autoinvoice =1  so no need to worry about assemblies/kitsets/controlled items*/
					
			/* Now Get the area where the sale is to from the branches table */
		
			$SQL = "SELECT area,
					defaultshipvia
				FROM custbranch
				WHERE custbranch.debtorno ='". $RecurrOrderRow['debtorno'] . "'
				AND custbranch.branchcode = '" . $RecurrOrderRow['branchcode'] . "'";
		
			$ErrMsg = _('Unable to determine the area where the sale is to, from the customr branches table, please select an area for this branch');
			$Result = DB_query($SQL,$db, $ErrMsg);
			$myrow = DB_fetch_row($Result);
			$Area = $myrow[0];
			$DefaultShipVia = $myrow[1];
			DB_free_result($Result);
		
		/*Now Get the next invoice number - function in SQL_CommonFunctions*/
		
			$InvoiceNo = GetNextTransNo(10, $db);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
		
		/*Start an SQL transaction */
		
			$SQL = "BEGIN";
			$Result = DB_query($SQL,$db);
		
					
			
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
					" . $_SESSION['CurrencyRate'] . ",
					'" . $_POST['InvoiceText'] . "',
					" . $_SESSION['Items']->ShipVia . ",
					'"  . $_POST['Consignment'] . "'
				)";
		
			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		
		
		/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */
		
				if ($OrderLine->QtyDispatched !=0 AND $OrderLine->QtyDispatched!="" AND $OrderLine->QtyDispatched) {
		
					// Test above to see if the line is completed or not
					if ($OrderLine->QtyDispatched>=($OrderLine->Quantity - $OrderLine->QtyInv) OR $_POST['BOPolicy']=="CAN"){
						$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
							actualdispatchdate = '" . $DefaultDispatchDate .  "',
							completed=1
							WHERE orderno = " . $_SESSION['ProcessingOrder'] . "
							AND stkcode = '" . $OrderLine->StockID . "'";
					} else {
						$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
							actualdispatchdate = '" . $DefaultDispatchDate .  "'
							WHERE orderno = " . $_SESSION['ProcessingOrder'] . "
							AND stkcode = '" . $OrderLine->StockID . "'";
		
					}
		
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
					$DbgMsg = _('The following SQL to update the sales order detail record was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
					/* Update location stock records if not a dummy stock item
					need the MBFlag later too so save it to $MBFlag */
					$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'",$db,"<BR>Can't retrieve the mbflag");
		
					$myrow = DB_fetch_row($Result);
					$MBFlag = $myrow[0];
		
					if ($MBFlag=="B" OR $MBFlag=="M") {
						$Assembly = False;
		
						/* Need to get the current location quantity
						will need it later for the stock movement */
						$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $OrderLine->StockID . "'
							AND loccode= '" . $_SESSION['Items']->Location . "'";
						$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
						$Result = DB_query($SQL, $db, $ErrMsg);
		
						if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
							/* There must be some error this should never happen */
							$QtyOnHandPrior = 0;
						}
		
						$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $OrderLine->QtyDispatched . "
							WHERE locstock.stockid = '" . $OrderLine->StockID . "'
							AND loccode = '" . $_SESSION['Items']->Location . "'";
		
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
						$DbgMsg = _('The following SQL to update the location stock record was used');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
					} else if ($MBFlag=='A'){ /* its an assembly */
						/*Need to get the BOM for this part and make
						stock moves for the components then update the Location stock balances */
						$Assembly=True;
						$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
						$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
							FROM bom,
								stockmaster
							WHERE bom.component=stockmaster.stockid
							AND bom.parent='" . $OrderLine->StockID . "'
							AND bom.effectiveto > '" . Date("Y-m-d") . "'
							AND bom.effectiveafter < '" . Date("Y-m-d") . "'";
		
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
						$DbgMsg = _('The SQL that failed was');
						$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
						while ($AssParts = DB_fetch_array($AssResult,$db)){
							$StandardCost += $AssParts['Standard'];
							/* Need to get the current location quantity
							will need it later for the stock movement */
							$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $AssParts['component'] . "'
								AND loccode= '" . $_SESSION['Items']->Location . "'";
		
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
							$DbgMsg = _('The SQL that failed was');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
							if (DB_num_rows($Result)==1){
								$LocQtyRow = DB_fetch_row($Result);
								$QtyOnHandPrior = $LocQtyRow[0];
							} else {
								/*There must be some error this should never happen */
								$QtyOnHandPrior = 0;
							}
		
							$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									debtorno,
									branchcode,
									prd,
									reference,
									qty,
									standardcost,
									show_on_inv_crds,
									newqoh
								) VALUES (
									'" . $AssParts['component'] . "',
									10,
									" . $InvoiceNo . ",
									'" . $_SESSION['Items']->Location . "',
									'" . $DefaultDispatchDate . "',
									'" . $_SESSION['Items']->DebtorNo . "',
									'" . $_SESSION['Items']->Branch . "',
									" . $PeriodNo . ",
									'Assembly: " . $OrderLine->StockID . " Order: " . $_SESSION['ProcessingOrder'] . "',
									" . -$AssParts['quantity'] * $OrderLine->QtyDispatched . ",
									" . $AssParts['standard'] . ",
									0,
									" . ($QtyOnHandPrior -($AssParts['quantity'] * $OrderLine->QtyDispatched)) . "
								)";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
							$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		
							$SQL = "UPDATE locstock
								SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
								WHERE locstock.stockid = '" . $AssParts['component'] . "'
								AND loccode = '" . $_SESSION['Items']->Location . "'";
		
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
							$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						} /* end of assembly explosion and updates */
		
						/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
						$_SESSION['Items']->LineItems[$OrderLine->StockID]->StandardCost = $StandardCost;
						$OrderLine->StandardCost = $StandardCost;
					} /* end of its an assembly */
		
					// Insert stock movements - with unit cost
					$LocalCurrencyPrice= ($OrderLine->Price / $_SESSION['CurrencyRate']);
		
					if ($MBFlag=="B" OR $MBFlag=="M"){
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
								taxrate,
								narrative
								)
							VALUES (
								'" . $OrderLine->StockID . "',
								10,
								" . $InvoiceNo . ",
								'" . $_SESSION['Items']->Location . "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['Items']->DebtorNo . "',
								'" . $_SESSION['Items']->Branch . "',
								" . $LocalCurrencyPrice . ",
								" . $PeriodNo . ",
								'" . $_SESSION['ProcessingOrder'] . "',
								" . -$OrderLine->QtyDispatched . ",
								" . $OrderLine->DiscountPercent . ",
								" . $OrderLine->StandardCost . ",
								" . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . ",
								" . $OrderLine->TaxRate . ",
								'" . addslashes($OrderLine->Narrative) . "'
							)";
					} else {
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
								'" . $OrderLine->StockID . "',
								10,
								" . $InvoiceNo . ",
								'" . $_SESSION['Items']->Location . "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['Items']->DebtorNo . "',
								'" . $_SESSION['Items']->Branch . "',
								" . $LocalCurrencyPrice . ",
								" . $PeriodNo . ",
								'" . $_SESSION['ProcessingOrder'] . "',
								" . -$OrderLine->QtyDispatched . ",
								" . $OrderLine->DiscountPercent . ",
								" . $OrderLine->StandardCost . ",
								" . $OrderLine->TaxRate . ",
								'" . addslashes($OrderLine->Narrative) . "'
							)";
					}
		
		
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
					$DbgMsg = _('The following SQL to insert the stock movement records was used');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		/*Get the ID of the StockMove... */
					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
		
		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
		
					if ($OrderLine->Controlled ==1){
						foreach($OrderLine->SerialItems as $Item){
						/*We need to add the StockSerialItem record and
						The StockSerialMoves as well */
		
							$SQL = "UPDATE stockserialitems
									SET quantity= quantity - " . $Item->BundleQty . "
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode='" . $_SESSION['Items']->Location . "'
									AND serialno='" . $Item->BundleRef . "'";
		
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		
							/* now insert the serial stock movement */
		
							$SQL = "INSERT INTO stockserialmoves (stockmoveno, 
												stockid, 
												serialno, 
												moveqty) 
								VALUES (" . $StkMoveNo . ", 
									'" . $OrderLine->StockID . "', 
									'" . $Item->BundleRef . "', 
									" . -$Item->BundleQty . ")";
									
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		
						}/* foreach controlled item in the serialitems array */
					} /*end if the orderline is a controlled item */
		
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