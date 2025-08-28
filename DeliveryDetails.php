<?php

/* Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc. */

/*
This is where the delivery details are confirmed/entered/modified and the order committed to the database once the place order/modify order button is hit.
*/

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineCartClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Order Delivery Details');// Screen identification.
$ViewTopic = 'SalesOrders';// Filename's id in ManualContents.php's TOC.
$BookMark = 'DeliveryDetails';// Anchor's id in the manual's html document.
include('includes/header.php');

if (isset($_POST['DeliveryDate'])){$_POST['DeliveryDate'] = ConvertSQLDate($_POST['DeliveryDate']);}
if (isset($_POST['QuoteDate'])){$_POST['QuoteDate'] = ConvertSQLDate($_POST['QuoteDate']);}
if (isset($_POST['ConfirmedDate'])){$_POST['ConfirmedDate'] = ConvertSQLDate($_POST['ConfirmedDate']);}

include('includes/FreightCalculation.php');
include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');
include('includes/CountriesArray.php');

if(isset($_GET['identifier'])) {
	$identifier=$_GET['identifier'];
}

unset($_SESSION['WarnOnce']);
if(!isset($_SESSION['Items'.$identifier]) OR !isset($_SESSION['Items'.$identifier]->DebtorNo)) {
	prnMsg(__('This page can only be read if an order has been entered') . '. ' . __('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.php');
	exit();
}

if($_SESSION['Items'.$identifier]->ItemsOrdered == 0) {
	prnMsg(__('This page can only be read if an there are items on the order') . '. ' . __('To enter an order select customer transactions then sales order entry'),'error');
	include('includes/footer.php');
	exit();
}

/*Calculate the earliest dispacth date in DateFunctions.php */

$EarliestDispatch = CalcEarliestDispatchDate();

if(isset($_POST['ProcessOrder']) OR isset($_POST['MakeRecurringOrder'])) {

	/*need to check for input errors in any case before order processed */
	$_POST['Update']='Yes rerun the validation checks';//no need for gettext!

	/*store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

	$OldFreightCost = round($_POST['FreightCost'],2);

}

if(isset($_POST['Update'])
	OR isset($_POST['BackToLineDetails'])
	OR isset($_POST['MakeRecurringOrder'])) {

	$InputErrors =0;
	if(mb_strlen($_POST['DeliverTo'])<=1) {
		$InputErrors =1;
		prnMsg(__('You must enter the person or company to whom delivery should be made'),'error');
	}
	if(mb_strlen($_POST['BrAdd1'])<=1) {
		$InputErrors =1;
		prnMsg(__('You should enter the street address in the box provided') . '. ' . __('Orders cannot be accepted without a valid street address'),'error');
	}
//	if(mb_strpos($_POST['BrAdd1'],__('Box'))>0) {
//		prnMsg(__('You have entered the word') . ' "' . __('Box') . '" ' . __('in the street address') . '. ' . __('Items cannot be delivered to') . ' ' .__('box') . ' ' . __('addresses'),'warn');
//	}
	if(!is_numeric($_POST['FreightCost'])) {
		$InputErrors =1;
		prnMsg( __('The freight cost entered is expected to be numeric'),'error');
	}
	if(isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation']==1) {
		$InputErrors =1;
		prnMsg( __('A recurring order cannot be made from a quotation'),'error');
	}
	if(($_POST['DeliverBlind'])<=0) {
		$InputErrors =1;
		prnMsg(__('You must select the type of packlist to print'),'error');
	}

/*	if(mb_strlen($_POST['BrAdd3'])==0 OR !isset($_POST['BrAdd3'])) {
		$InputErrors =1;
		echo "<br />A region or city must be entered.<br />";
	}

	Maybe appropriate in some installations but not here
	if(mb_strlen($_POST['BrAdd2'])<=1) {
		$InputErrors =1;
		echo "<br />You should enter the suburb in the box provided. Orders cannot be accepted without a valid suburb being entered.<br />";
	}

*/
// Check the date is OK
	if(isset($_POST['DeliveryDate']) and !Is_Date($_POST['DeliveryDate'])) {
		$InputErrors =1;
		prnMsg(__('An invalid date entry was made') . '. ' . __('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
// Check the date is OK
	if(isset($_POST['QuoteDate']) and !Is_Date($_POST['QuoteDate'])) {
		$InputErrors =1;
		prnMsg(__('An invalid date entry was made') . '. ' . __('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}
// Check the date is OK
	if(isset($_POST['ConfirmedDate']) and !Is_Date($_POST['ConfirmedDate'])) {
		$InputErrors =1;
		 prnMsg(__('An invalid date entry was made') . '. ' . __('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
	}

	 /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

	 elseif(Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat'],$EarliestDispatch), $_POST['DeliveryDate'])) {
		$InputErrors =1;
		echo '<br /><b>' . __('The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is') . $_SESSION['DispatchCutOffTime'] . __(':00 hrs. Orders placed after this time will be dispatched the following working day.');
	}

	*/

	if($InputErrors==0) {

		if($_SESSION['DoFreightCalc']==true) {
			list ($_POST['FreightCost'], $BestShipper) = CalcFreightCost($_SESSION['Items'.$identifier]->total,
																		$_POST['BrAdd2'],
																		$_POST['BrAdd3'],
																		$_POST['BrAdd4'],
																		$_POST['BrAdd5'],
																		$_POST['BrAdd6'],
																		$_SESSION['Items'.$identifier]->totalVolume,
																		$_SESSION['Items'.$identifier]->totalWeight,
																		$_SESSION['Items'.$identifier]->Location,
																		$_SESSION['Items'.$identifier]->DefaultCurrency);
			if( !empty($BestShipper) ) {
				$_POST['FreightCost'] = round($_POST['FreightCost'],2);
				$_POST['ShipVia'] = $BestShipper;
			} else {
				prnMsg(__($_POST['FreightCost']),'warn');
			}
		}
		$SQL = "SELECT custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
					custbranch.email,
					custbranch.defaultlocation,
					custbranch.defaultshipvia,
					custbranch.deliverblind,
					custbranch.specialinstructions,
					custbranch.estdeliverydays,
					custbranch.salesman
				FROM custbranch
				WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = __('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . __('cannot be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		if(DB_num_rows($Result)==0) {

			prnMsg(__('The branch details for branch code') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . __('against customer code') . ': ' . $_POST['Select'] . ' ' . __('could not be retrieved') . '. ' . __('Check the set up of the customer and branch'),'error');

			include('includes/footer.php');
			exit();
		}
		if(!isset($_POST['SpecialInstructions'])) {
			$_POST['SpecialInstructions']='';
		}
		if(!isset($_POST['DeliveryDays'])) {
			$_POST['DeliveryDays']=0;
		}
		if(!isset($_SESSION['Items'.$identifier])) {
			$MyRow = DB_fetch_row($Result);
			$_SESSION['Items'.$identifier]->DeliverTo = $MyRow[0];
			$_SESSION['Items'.$identifier]->DelAdd1 = $MyRow[1];
			$_SESSION['Items'.$identifier]->DelAdd2 = $MyRow[2];
			$_SESSION['Items'.$identifier]->DelAdd3 = $MyRow[3];
			$_SESSION['Items'.$identifier]->DelAdd4 = $MyRow[4];
			$_SESSION['Items'.$identifier]->DelAdd5 = $MyRow[5];
			$_SESSION['Items'.$identifier]->DelAdd6 = $MyRow[6];
			$_SESSION['Items'.$identifier]->PhoneNo = $MyRow[7];
			$_SESSION['Items'.$identifier]->Email = $MyRow[8];
			$_SESSION['Items'.$identifier]->Location = $MyRow[9];
			$_SESSION['Items'.$identifier]->ShipVia = $MyRow[10];
			$_SESSION['Items'.$identifier]->DeliverBlind = $MyRow[11];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $MyRow[12];
			$_SESSION['Items'.$identifier]->DeliveryDays = $MyRow[13];
			$_SESSION['Items'.$identifier]->SalesPerson = $MyRow[14];
			$_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items'.$identifier]->QuoteDate = $_POST['QuoteDate'];
			$_SESSION['Items'.$identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
			$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
			$_SESSION['Items'.$identifier]->Comments = $_POST['Comments'];
			$_SESSION['Items'.$identifier]->FreightCost = round($_POST['FreightCost'],2);
			$_SESSION['Items'.$identifier]->Quotation = $_POST['Quotation'];
		} else {
			$_SESSION['Items'.$identifier]->DeliverTo = $_POST['DeliverTo'];
			$_SESSION['Items'.$identifier]->DelAdd1 = $_POST['BrAdd1'];
			$_SESSION['Items'.$identifier]->DelAdd2 = $_POST['BrAdd2'];
			$_SESSION['Items'.$identifier]->DelAdd3 = $_POST['BrAdd3'];
			$_SESSION['Items'.$identifier]->DelAdd4 = $_POST['BrAdd4'];
			$_SESSION['Items'.$identifier]->DelAdd5 = $_POST['BrAdd5'];
			$_SESSION['Items'.$identifier]->DelAdd6 = $_POST['BrAdd6'];
			$_SESSION['Items'.$identifier]->PhoneNo = $_POST['PhoneNo'];
			$_SESSION['Items'.$identifier]->Email = $_POST['Email'];
			$_SESSION['Items'.$identifier]->Location = $_POST['Location'];
			$_SESSION['Items'.$identifier]->ShipVia = $_POST['ShipVia'];
			$_SESSION['Items'.$identifier]->DeliverBlind = $_POST['DeliverBlind'];
			$_SESSION['Items'.$identifier]->SpecialInstructions = $_POST['SpecialInstructions'];
			$_SESSION['Items'.$identifier]->DeliveryDays = $_POST['DeliveryDays'];
			$_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
			$_SESSION['Items'.$identifier]->QuoteDate = $_POST['QuoteDate'];
			$_SESSION['Items'.$identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
			$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
			$_SESSION['Items'.$identifier]->Comments = $_POST['Comments'];
			$_SESSION['Items'.$identifier]->SalesPerson = $_POST['SalesPerson'];
			$_SESSION['Items'.$identifier]->FreightCost = round(floatval($_POST['FreightCost']),2);
			$_SESSION['Items'.$identifier]->Quotation = $_POST['Quotation'];
		}
		/*$_SESSION['DoFreightCalc'] is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */


		/* What to do if the shipper is not calculated using the system
		- first check that the default shipper defined in config.php is in the database
		if so use this
		- then check to see if any shippers are defined at all if not report the error
		and show a link to set them up
		- if shippers defined but the default shipper is bogus then use the first shipper defined
		*/
		if((isset($BestShipper) AND $BestShipper=='') AND ($_POST['ShipVia']=='' OR !isset($_POST['ShipVia']))) {
			$SQL = "SELECT shipper_id
						FROM shippers
						WHERE shipper_id='" . $_SESSION['Default_Shipper']."'";
			$ErrMsg = __('There was a problem testing for the default shipper');
			$TestShipperExists = DB_query($SQL, $ErrMsg);

			if(DB_num_rows($TestShipperExists)==1) {

				$BestShipper = $_SESSION['Default_Shipper'];

			} else {

				$SQL = "SELECT shipper_id
							FROM shippers";
				$TestShipperExists = DB_query($SQL, $ErrMsg);

				if(DB_num_rows($TestShipperExists)>=1) {
					$ShipperReturned = DB_fetch_row($TestShipperExists);
					$BestShipper = $ShipperReturned[0];
				} else {
					prnMsg(__('We have a problem') . ' - ' . __('there are no shippers defined'). '. ' . __('Please use the link below to set up shipping or freight companies') . ', ' . __('the system expects the shipping company to be selected or a default freight company to be used'),'error');
					echo '<a href="' . $RootPath . 'Shippers.php">' . __('Enter') . '/' . __('Amend Freight Companies') . '</a>';
				}
			}
			if(isset($_SESSION['Items'.$identifier]->ShipVia) AND $_SESSION['Items'.$identifier]->ShipVia!='') {
				$_POST['ShipVia'] = $_SESSION['Items'.$identifier]->ShipVia;
			} else {
				$_POST['ShipVia']=$BestShipper;
			}
		}
	}
}

if(isset($_POST['MakeRecurringOrder']) AND ! $InputErrors) {

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/RecurringSalesOrders.php?identifier='.$identifier  . '&amp;NewRecurringOrder=Yes">';
	prnMsg(__('You should automatically be forwarded to the entry of recurring order details page') . '. ' . __('If this does not happen') . '(' . __('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/RecurringOrders.php?identifier='.$identifier . '&amp;NewRecurringOrder=Yes">' . __('click here') . '</a> '. __('to continue'),'info');
	include('includes/footer.php');
	exit();
}


if(isset($_POST['BackToLineDetails']) and $_POST['BackToLineDetails']==__('Modify Order Lines')) {

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SelectOrderItems.php?identifier='.$identifier  . '">';
	prnMsg(__('You should automatically be forwarded to the entry of the order line details page') . '. ' . __('If this does not happen') . '(' . __('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/SelectOrderItems.php?identifier='.$identifier . '">' . __('click here') . '</a> '. __('to continue'),'info');
	include('includes/footer.php');
	exit();

}

if(isset($_POST['ProcessOrder'])) {
	/*Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
	if($InputErrors ==0) {
		$OK_to_PROCESS = 1;
	}
	if($_POST['FreightCost'] != $OldFreightCost AND $_SESSION['DoFreightCalc']==true) {
		$OK_to_PROCESS = 0;
		prnMsg(__('The freight charge has been updated') . '. ' . __('Please reconfirm that the order and the freight charges are acceptable and then confirm the order again if OK') .' <br /> '. __('The new freight cost is') .' ' . $_POST['FreightCost'] . ' ' . __('and the previously calculated freight cost was') .' '. $OldFreightCost,'warn');
	} else {

/*check the customer's payment terms */
		$SQL = "SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = __('The customer terms cannot be determined') . '. ' . __('This order cannot be processed because');
		$TermsResult = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($TermsResult);
		if($MyRow['daysbeforedue']==0 AND $MyRow['dayinfollowingmonth']==0) {

/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED   */

			$OK_to_PROCESS =1;


		} #end if cash sale detected

	} #end if else freight charge not altered
} #end if process order

if(isset($OK_to_PROCESS) AND $OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]==0) {

/* finally write the order header to the database and then the order line details */

	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);

	DB_Txn_Begin();

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
								salesperson,
								freightcost,
								fromstkloc,
								deliverydate,
								quotedate,
								confirmeddate,
								quotation,
								deliverblind)
							VALUES (
								'". $OrderNo . "',
								'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
								'" . $_SESSION['Items'.$identifier]->Branch . "',
								'". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
								CURRENT_DATE,
								'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
								'" . $_POST['ShipVia'] ."',
								'". DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
								'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
								'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
								'" . $_SESSION['Items'.$identifier]->Email . "',
								'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
								'" . $_SESSION['Items'.$identifier]->FreightCost ."',
								'" . $_SESSION['Items'.$identifier]->Location ."',
								'" . $DelDate . "',
								'" . $QuotDate . "',
								'" . $ConfDate . "',
								'" . $_SESSION['Items'.$identifier]->Quotation . "',
								'" . $_SESSION['Items'.$identifier]->DeliverBlind ."'
								)";

	$ErrMsg = __('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL, $ErrMsg);


	$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
											orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue)
										VALUES (";
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		$LineItemsSQL = $StartOf_LineItemsSQL ."
					'" . $StockItem->LineNumber . "',
					'" . $OrderNo . "',
					'" . $StockItem->StockID . "',
					'" . $StockItem->Price . "',
					'" . $StockItem->Quantity . "',
					'" . floatval($StockItem->DiscountPercent) . "',
					'" . DB_escape_string($StockItem->Narrative) . "',
					'" . $StockItem->POLine . "',
					'" . FormatDateForSQL($StockItem->ItemDue) . "'
				)";
		$ErrMsg = __('Unable to add the sales order line');
		$Ins_LineItemResult = DB_query($LineItemsSQL, $ErrMsg,'',true);

		/*Now check to see if the item is manufactured
		 * 			and AutoCreateWOs is on
		 * 			and it is a real order (not just a quotation)*/

		if($StockItem->MBflag=='M'
			AND $_SESSION['AutoCreateWOs']==1
			AND $_SESSION['Items'.$identifier]->Quotation!=1) {//oh yeah its all on!

			echo '<br />';

			//now get the data required to test to see if we need to make a new WO
			$QOH = GetQuantityOnHand($StockItem->StockID, 'ALL');

			$QuantityDemand = GetDemand($StockItem->StockID, 'ALL');

			$QuantityOnOrder = GetQuantityOnOrder($StockItem->StockID, 'ALL');

			//Now we have the data - do we need to make any more?
			$ShortfallQuantity = $QOH-$QuantityDemand+$QuantityOnOrder;

			if($ShortfallQuantity < 0) {//then we need to make a work order
				//How many should the work order be for??
				if($ShortfallQuantity + $StockItem->EOQ < 0) {
					$WOQuantity = -$ShortfallQuantity;
				} else {
					$WOQuantity = $StockItem->EOQ;
				}

				$WONo = GetNextTransNo(40);
				$ErrMsg = __('Unable to insert a new work order for the sales order item');
				$InsWOResult = DB_query("INSERT INTO workorders (wo,
												 loccode,
												 requiredby,
												 startdate)
								 VALUES ('" . $WONo . "',
										'" . $_SESSION['DefaultFactoryLocation'] . "',
										CURRENT_DATE,
										'" . FormatDateForSQL($StockItem->ItemDue) . "')",
										$ErrMsg,
										'',
										true);
				//Need to get the latest BOM to roll up cost
				$CostResult = DB_query("SELECT SUM((actualcost)*bom.quantity) AS cost
													FROM stockmaster INNER JOIN bom
													ON stockmaster.stockid=bom.component
													WHERE bom.parent='" . $StockItem->StockID . "'
													AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'");
				$CostRow = DB_fetch_row($CostResult);
				if(is_null($CostRow[0]) OR $CostRow[0]==0) {
					$Cost =0;
					prnMsg(__('In automatically creating a work order for') . ' ' . $StockItem->StockID . ' ' . __('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
				} else {
					$Cost = $CostRow[0];
				}

				// insert parent item info
				$SQL = "INSERT INTO woitems (wo,
											 stockid,
											 qtyreqd,
											 stdcost)
								 VALUES ( '" . $WONo . "',
										 '" . $StockItem->StockID . "',
										 '" . $WOQuantity . "',
										 '" . $Cost . "')";
				$ErrMsg = __('The work order item could not be added');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
				WoRealRequirements($WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

				$FactoryManagerEmail = __('A new work order has been created for') .
									":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
									"\n" . __('These are for') . ' ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . __('there order ref') . ': ' . $_SESSION['Items'.$identifier]->CustRef . ' ' .__('our order number') . ': ' . $OrderNo;

				if($StockItem->Serialised AND $StockItem->NextSerialNo>0) {
						//then we must create the serial numbers for the new WO also
						$FactoryManagerEmail .= "\n" . __('The following serial numbers have been reserved for this work order') . ':';

						for ($i=0;$i<$WOQuantity;$i++) {

							$Result = DB_query("SELECT serialno FROM stockserialitems
												WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
												AND stockid='" . $StockItem->StockID ."'");
							if(DB_num_rows($Result)!=0) {
								$WOQuantity++;
								prnMsg(($StockItem->NextSerialNo + $i) . ': ' . __('This automatically generated serial number already exists - it cannot be added to the work order'),'error');
							} else {
								$SQL = "INSERT INTO woserialnos (wo,
																stockid,
																serialno)
													VALUES ('" . $WONo . "',
															'" . $StockItem->StockID . "',
															'" . ($StockItem->NextSerialNo + $i) . "')";
								$ErrMsg = __('The serial number for the work order item could not be added');
								$Result = DB_query($SQL, $ErrMsg, '', true);
								$FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
							}
						}//end loop around creation of woserialnos
						$NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity +1);
						$ErrMsg = __('Could not update the new next serial number for the item');
						$UpdateNextSerialNoResult = DB_query("UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'", $ErrMsg, '', true);
				}// end if the item is serialised and nextserialno is set

				$EmailSubject = __('New Work Order Number') . ' ' . $WONo . ' ' . __('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
				//Send email to the Factory Manager
				SendEmailFromWebERP($SysAdminEmail,
									$_SESSION['FactoryManagerEmail'],
									$EmailSubject,
									$FactoryManagerEmail,
									'',
									false);

			}//end if with this sales order there is a shortfall of stock - need to create the WO
		}//end if auto create WOs in on
	} /* end inserted line items into sales order details */

	 DB_Txn_Commit();
	echo '<br />';
	if($_SESSION['Items'.$identifier]->Quotation==1) {
		prnMsg(__('Quotation Number') . ' ' . $OrderNo . ' ' . __('has been entered'),'success');
	} else {
		prnMsg(__('Order Number') . ' ' . $OrderNo . ' ' . __('has been entered'),'success');
	}

	if(count($_SESSION['AllowedPageSecurityTokens'])>1) {
		/* Only allow print of packing slip for internal staff - customer logon's cannot go here */

		if($_POST['Quotation']==0) { /*then its not a quotation its a real order */

			echo '<fieldset>
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" /></td>
						<td>' . ' ' . '<a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' . __('Print packing slip') . ' (' . __('Preprinted stationery') . ')' . '</a></td>
					</tr>';
			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" /></td>
					<td>' . ' ' . '<a target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' . __('Print packing slip') . ' (' . __('Laser') . ')' . '</a></td>
				</tr>';

			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . __('Invoice') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/ConfirmDispatch_Invoice.php?identifier='.$identifier . '&amp;OrderNumber=' . $OrderNo .'">' . __('Confirm Dispatch and Produce Invoice') . '</a></td>
				</tr>';

			echo '</fieldset>';

		} else {
			/*link to print the quotation */
			echo '<fieldset>
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Order') . '" alt=""></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotation.php?identifier='.$identifier . '&amp;QuotationNo=' . $OrderNo . '" target="_blank">' . __('Print Quotation (Landscape)') . '</a></td>
					</tr>
					</table>';
			echo '<fieldset>
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Order') . '" alt="" /></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotationPortrait.php?identifier='.$identifier . '&amp;QuotationNo=' . $OrderNo . '" target="_blank">' .  __('Print Quotation (Portrait)')  . '</a></td>
					</tr>
					</fieldset>';
		}
		echo '<fieldset>
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Order') . '" alt="" /></td>
					<td>' . ' ' . '<a href="'. $RootPath .'/SelectOrderItems.php?identifier='.$identifier . '&amp;NewOrder=Yes">' .  __('Add Another Sales Order')  . '</a></td>
				</tr>
				</fieldset>';
	} else {
		/*its a customer logon so thank them */
		prnMsg(__('Thank you for your business'),'success');
	}

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.php');
	exit();

} elseif(isset($OK_to_PROCESS) AND ($OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]!=0)) {

/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);

	DB_Txn_Begin();

	/*see if this is a contract quotation being changed to an order? */
	if($_SESSION['Items'.$identifier]->Quotation==0) {//now its being changed? to an order
		$ContractResult = DB_query("SELECT contractref,
											requireddate
									FROM contracts WHERE orderno='" .  $_SESSION['ExistingOrder'.$identifier] ."'
									AND status=1");
		if(DB_num_rows($ContractResult)==1) {//then it is a contract quotation being changed to an order
			$ContractRow = DB_fetch_array($ContractResult);
			$WONo = GetNextTransNo(40);
			$ErrMsg = __('Could not update the contract status');
			$UpdContractResult = DB_query("UPDATE contracts SET status=2,
															wo='" . $WONo . "'
										WHERE orderno='" .$_SESSION['ExistingOrder'.$identifier] . "'",
										$ErrMsg,
										'',
										true);
			$ErrMsg = __('Could not insert the contract bill of materials');
			$InsContractBOM = DB_query("INSERT INTO bom (parent,
														 component,
														 workcentreadded,
														 loccode,
														 effectiveafter,
														 effectiveto,
													 	 quantity)
											SELECT contractref,
													stockid,
													workcentreadded,
													'" . $_SESSION['Items'.$identifier]->Location ."',
													CURRENT_DATE,
													'2099-12-31',
													quantity
											FROM contractbom
											WHERE contractref='" . $ContractRow['contractref'] . "'",
											$ErrMsg);

			$ErrMsg = __('Unable to insert a new work order for the sales order item');
			$InsWOResult = DB_query("INSERT INTO workorders (wo,
															 loccode,
															 requiredby,
															 startdate)
											 VALUES ('" . $WONo . "',
													'" . $_SESSION['Items'.$identifier]->Location ."',
													'" . $ContractRow['requireddate'] . "',
													'" . Date('Y-m-d'). "')",
										$ErrMsg);
			//Need to get the latest BOM to roll up cost but also add the contract other requirements
			$CostResult = DB_query("SELECT SUM((actualcost)*contractbom.quantity) AS cost
									FROM stockmaster INNER JOIN contractbom
									ON stockmaster.stockid=contractbom.stockid
									WHERE contractbom.contractref='" .  $ContractRow['contractref'] . "'");
			$CostRow = DB_fetch_row($CostResult);
			if(is_null($CostRow[0]) OR $CostRow[0]==0) {
				$Cost =0;
				prnMsg(__('In automatically creating a work order for') . ' ' . $ContractRow['contractref'] . ' ' . __('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
			} else {
				$Cost = $CostRow[0];//cost of contract BOM
			}
			$CostResult = DB_query("SELECT SUM(costperunit*quantity) AS cost
									FROM contractreqts
									WHERE contractreqts.contractref='" .  $ContractRow['contractref'] . "'");
			$CostRow = DB_fetch_row($CostResult);
			//add other requirements cost to cost of contract BOM
			$Cost += $CostRow[0];

			// insert parent item info
			$SQL = "INSERT INTO woitems (wo,
										 stockid,
										 qtyreqd,
										 stdcost)
							 VALUES ( '" . $WONo . "',
									 '" . $ContractRow['contractref'] . "',
									 '1',
									 '" . $Cost . "')";
			$ErrMsg = __('The work order item could not be added');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			WoRealRequirements($WONo, $_SESSION['Items'.$identifier]->Location, $ContractRow['contractref']);

		}//end processing if the order was a contract quotation being changed to an order
	}//end test to see if the order was a contract quotation being changed to an order


	$HeaderSQL = "UPDATE salesorders SET debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "',
										customerref = '". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
										comments = '". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
										ordertype = '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										shipvia = '" . $_POST['ShipVia'] . "',
										deliverydate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "',
										quotedate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->QuoteDate)) . "',
										confirmeddate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->ConfirmedDate)) . "',
										deliverto = '" . DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
										deladd1 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
										deladd2 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
										deladd3 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
										deladd4 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
										deladd5 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
										deladd6 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
										contactphone = '" . $_SESSION['Items'.$identifier]->PhoneNo . "',
										contactemail = '" . $_SESSION['Items'.$identifier]->Email . "',
										salesperson = '" .  $_SESSION['Items'.$identifier]->SalesPerson . "',
										freightcost = '" . $_SESSION['Items'.$identifier]->FreightCost ."',
										fromstkloc = '" . $_SESSION['Items'.$identifier]->Location ."',
										printedpackingslip = '" . $_POST['ReprintPackingSlip'] . "',
										quotation = '" . $_SESSION['Items'.$identifier]->Quotation . "',
										deliverblind = '" . $_SESSION['Items'.$identifier]->DeliverBlind . "'
						WHERE salesorders.orderno='" . $_SESSION['ExistingOrder'.$identifier] ."'";

	$ErrMsg = __('The order cannot be updated because');
	$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, '', true);

	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		/* Check to see if the quantity reduced to the same quantity
		as already invoiced - so should set the line to completed */
		if($StockItem->Quantity == $StockItem->QtyInv) {
			$Completed = 1;
		} else {  /* order line is not complete */
			$Completed = 0;
		}

		$LineItemsSQL = "UPDATE salesorderdetails SET unitprice='"  . $StockItem->Price . "',
													quantity='" . $StockItem->Quantity . "',
													discountpercent='" . floatval($StockItem->DiscountPercent) . "',
													completed='" . $Completed . "',
													poline='" . $StockItem->POLine . "',
													itemdue='" . FormatDateForSQL($StockItem->ItemDue) . "'
						WHERE salesorderdetails.orderno='" . $_SESSION['ExistingOrder'.$identifier] . "'
						AND salesorderdetails.orderlineno='" . $StockItem->LineNumber . "'";

		$ErrMsg = __('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL, $ErrMsg, '', true);

	} /* updated line items into sales order details */

	DB_Txn_Commit();
	$Quotation = $_SESSION['Items'.$identifier]->Quotation;
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);

	if($Quotation) {//handle Quotations and Orders print after modification
		prnMsg(__('Quotation Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . __('has been updated'),'success');

		/*link to print the quotation */
		echo '<fieldset>
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Order') . '" alt=""></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotation.php?identifier='.$identifier . '&amp;QuotationNo=' . $_SESSION['ExistingOrder'.$identifier] . '" target="_blank">' .  __('Print Quotation (Landscape)')  . '</a></td>
				</tr>
				</fieldset>';
		echo '<fieldset>
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Order') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotationPortrait.php?identifier='.$identifier . '&amp;QuotationNo=' . $_SESSION['ExistingOrder'.$identifier] . '" target="_blank">' .  __('Print Quotation (Portrait)')  . '</a></td>
				</tr>
				</fieldset>';
	} else {

	prnMsg(__('Order Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . __('has been updated'),'success');

	echo '<fieldset>
			<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" /></td>
			<td><a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  __('Print packing slip - pre-printed stationery')  . '</a></td>
			</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" /></td>
			<td><a  target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  __('Print packing slip') . ' (' . __('Laser') . ')'  . '</a></td>
		</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . __('Invoice') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/ConfirmDispatch_Invoice.php?identifier='.$identifier  . '&amp;OrderNumber=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  __('Confirm Order Delivery Quantities and Produce Invoice')  . '</a></td>
		</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Order') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/SelectSalesOrder.php?identifier='.$identifier   . '">' .  __('Select A Different Order')  . '</a></td>
		</tr>
		</fieldset>';
	}//end of print orders
	include('includes/footer.php');
	exit();
}


if(isset($_SESSION['Items'.$identifier]->SpecialInstructions) and mb_strlen($_SESSION['Items'.$identifier]->SpecialInstructions)>0) {
	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'info');
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Delivery') . '" alt="" />' . ' ' . __('Delivery Details') . '</p>';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Customer') . '" alt="" />' . ' ' . __('Customer Code') . ' :<b> ' . $_SESSION['Items'.$identifier]->DebtorNo . '<br />';
echo '</b>&nbsp;' . __('Customer Name') . ' :<b> ' . $_SESSION['Items'.$identifier]->CustomerName . '</b></p>';


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


/*Display the order with or without discount depending on access level*/
if(in_array(2,$_SESSION['AllowedPageSecurityTokens'])) {

	echo '<table>';

	if($_SESSION['Items'.$identifier]->Quotation==1) {
		echo '<tr><th colspan="7">' . __('Quotation Summary') . '</th></tr>';
	} else {
		echo '<tr><th colspan="7">' . __('Order Summary') . '</th></tr>';
	}
	echo '<tr>
				<th>' .  __('Item Code')  . '</th>
				<th>' .  __('Item Description')  . '</th>
				<th>' .  __('Quantity')  . '</th>
				<th>' .  __('Unit')  . '</th>
				<th>' .  __('Price')  . '</th>
				<th>' .  __('Discount') .' %</th>
				<th>' .  __('Total')  . '</th>
			</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;

	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayPrice = locale_number_format($StockItem->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($StockItem->Quantity,$StockItem->DecimalPlaces);
		$DisplayDiscount = locale_number_format(($StockItem->DiscountPercent * 100),2);


		echo '<tr class="striped_row">
			<td>' . $StockItem->StockID . '</td>
			<td title="' . $StockItem->LongDescription . '">' . $StockItem->ItemDescription . '</td>
			<td class="number">' . $DisplayQuantity . '</td>
			<td>' . $StockItem->Units . '</td>
			<td class="number">' . $DisplayPrice . '</td>
			<td class="number">' . $DisplayDiscount . '</td>
			<td class="number">' . $DisplayLineTotal . '</td>
		</tr>';

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
	}

	$DisplayTotal = number_format($_SESSION['Items'.$identifier]->total,2);
	echo '<tr class="striped_row">
			<td colspan="6" class="number"><b>' .  __('TOTAL Excl Tax/Freight')  . '</b></td>
			<td class="number">' . $DisplayTotal . '</td>
		</tr>
		</table>';

	$DisplayVolume = locale_number_format($_SESSION['Items'.$identifier]->totalVolume,5);
	$DisplayWeight = locale_number_format($_SESSION['Items'.$identifier]->totalWeight,2);
	echo '<br />
		<table>
		<tr class="striped_row">
			<td>' .  __('Total Weight') .':</td>
			<td class="number">' . $DisplayWeight . '</td>
			<td>' .  __('Total Volume') .':</td>
			<td class="number">' . $DisplayVolume . '</td>
		</tr>
		</table>';

} else {

/*Display the order without discount */

	echo '<div class="centre"><b>' . __('Order Summary') . '</b></div>
	<table class="selection">
	<tr>
		<th>' .  __('Item Description')  . '</th>
		<th>' .  __('Quantity')  . '</th>
		<th>' .  __('Unit')  . '</th>
		<th>' .  __('Price')  . '</th>
		<th>' .  __('Total')  . '</th>
	</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;

	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayPrice = locale_number_format($StockItem->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($StockItem->Quantity,$StockItem->DecimalPlaces);

		echo '<tr class="striped_row">
				<td>' . $StockItem->ItemDescription  . '</td>
				<td class="number">' . $DisplayQuantity . '</td>
				<td>' . $StockItem->Units . '</td>
				<td class="number">' . $DisplayPrice . '</td>
				<td class="number">' . $DisplayLineTotal . '</font></td>
			</tr>';

		$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
		$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $StockItem->Quantity * $StockItem->Volume;
		$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $StockItem->Quantity * $StockItem->Weight;

	}

	$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);

	$DisplayVolume = locale_number_format($_SESSION['Items'.$identifier]->totalVolume,5);
	$DisplayWeight = locale_number_format($_SESSION['Items'.$identifier]->totalWeight,2);
	echo '<table class="selection">
			<tr>
				<td>' . __('Total Weight') . ':</td>
				<td>' . $DisplayWeight . '</td>
				<td>' . __('Total Volume') . ':</td>
				<td>' . $DisplayVolume . '</td>
			</tr>
		</table>';

}

echo '<fieldset>
		<field>
			<label for="DeliverTo">' .  __('Deliver To') .':</label>
			<input type="text" autofocus="autofocus" required="required" size="42" maxlength="40" name="DeliverTo" value="' .  stripslashes($_SESSION['Items' . $identifier]->DeliverTo) . '" title="' . __('Enter the name of the customer to deliver this order to') . '" />
		</field>';

echo '<field>
		<label for="Location">', __('Deliver from the warehouse at'), ':</label>
		<select name="Location">';

if($_SESSION['Items'.$identifier]->Location=='' OR !isset($_SESSION['Items'.$identifier]->Location)) {
	$_SESSION['Items'.$identifier]->Location = $DefaultStockLocation;
}

$SQL = "SELECT locations.loccode, locationname
	FROM locations
	INNER JOIN locationusers
	ON locationusers.loccode=locations.loccode
		AND locationusers.userid='" . $_SESSION['UserID'] . "'
		AND locationusers.canupd=1
	WHERE locations.allowinvoicing='1'
	ORDER BY locations.locationname";
$ErrMsg = __('The stock locations could not be retrieved');
$StkLocsResult = DB_query($SQL, $ErrMsg);
// COMMENT: What if there is no authorized locations available for this user?
while($MyRow=DB_fetch_array($StkLocsResult)) {
	echo '<option', ($_SESSION['Items'.$identifier]->Location==$MyRow['loccode'] ? ' selected="selected"' : ''), ' value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
}
echo '</select>
	</field>';

// Set the default date to earliest possible date if not set already
if(!isset($_SESSION['Items'.$identifier]->DeliveryDate)) {
	$_SESSION['Items'.$identifier]->DeliveryDate = Date('Y-m-d',$EarliestDispatch);
}
if(!isset($_SESSION['Items'.$identifier]->QuoteDate)) {
	$_SESSION['Items'.$identifier]->QuoteDate = Date('Y-m-d',$EarliestDispatch);
}
if(!isset($_SESSION['Items'.$identifier]->ConfirmedDate)) {
	$_SESSION['Items'.$identifier]->ConfirmedDate = Date('Y-m-d',$EarliestDispatch);
}

// The estimated Dispatch date or Delivery date for this order
echo '<field>
		<label for="DeliveryDate">' .  __('Estimated Delivery Date') .':</label>
		<input type="date" size="11" maxlength="10" name="DeliveryDate" value="' . $_SESSION['Items'.$identifier]->DeliveryDate . '" title=""/>
		<fieldhelp>' . __('Enter the estimated delivery date requested by the customer') . '</fieldhelp>
	</field>';
// The date when a quote was issued to the customer
echo '<field>
		<label for="QuoteDate">' .  __('Quote Date') .':</label>
		<input type="date" size="11" maxlength="10" name="QuoteDate" value="' . $_SESSION['Items'.$identifier]->QuoteDate . '" />
	</field>';
// The date when the customer confirmed their order
echo '<field>
		<label for="ConfirmedDate">' .  __('Confirmed Order Date') .':</label>
		<input type="date" size="11" maxlength="10" name="ConfirmedDate" value="' . $_SESSION['Items'.$identifier]->ConfirmedDate . '" />
	</field>
	<field>
		<label for="BrAdd1">' .  __('Delivery Address 1 (Street)') . ':</label>
		<input type="text" size="42" maxlength="40" name="BrAdd1" value="' . $_SESSION['Items'.$identifier]->DelAdd1 . '" />
	</field>
	<field>
		<label for="BrAdd2">' .  __('Delivery Address 2 (Street)') . ':</label>
		<td><input type="text" size="42" maxlength="40" name="BrAdd2" value="' . $_SESSION['Items'.$identifier]->DelAdd2 . '" /></td>
	</field>
	<field>
		<label for="BrAdd3">' .  __('Delivery Address 3 (Suburb/City)') . ':</label>
		<td><input type="text" size="42" maxlength="40" name="BrAdd3" value="' . $_SESSION['Items'.$identifier]->DelAdd3 . '" /></td>
	</field>
	<field>
		<label for="BrAdd4">' .  __('Delivery Address 4 (State/Province)') . ':</label>
		<td><input type="text" size="42" maxlength="40" name="BrAdd4" value="' . $_SESSION['Items'.$identifier]->DelAdd4 . '" /></td>
	</field>
	<field>
		<label for="BrAdd5">' .  __('Delivery Address 5 (Postal Code)') . ':</label>
		<td><input type="text" size="42" maxlength="40" name="BrAdd5" value="' . $_SESSION['Items'.$identifier]->DelAdd5 . '" /></td>
	</field>';
echo '<field>
		<label for="BrAdd6">' . __('Country') . ':</label>
		<select name="BrAdd6">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if(isset($_POST['BrAdd6']) AND (strtoupper($_POST['BrAdd6']) == strtoupper($CountryName))) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
	} elseif(!isset($_POST['BrAdd6']) AND $CountryName == $_SESSION['Items'.$identifier]->DelAdd6) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName  . '</option>';
	} else {
		echo '<option value="' . $CountryName . '">' . $CountryName  . '</option>';
	}
}
echo '</select>
	</field>';

echo'<field>
		<label for="PhoneNo">' .  __('Contact Phone Number') .':</label>
		<input type="tel" size="25" maxlength="25" required="required" name="PhoneNo" value="' . $_SESSION['Items'.$identifier]->PhoneNo . '" title="" />
		<fieldhelp>' . __('Enter the telephone number of the contact at the delivery address.') . '</fieldhelp>
	</field>
	<field>
		<label for="Email">' . __('Contact Email') . ':</label>
		<input type="email" size="40" maxlength="38" name="Email" value="' . $_SESSION['Items'.$identifier]->Email . '" title="" />
		<fieldhelp>' . __('Enter the email address of the contact at the delivery address') . '</fieldhelp>
	</field>
	<field>
		<label for="CustRef">' .  __('Customer Reference') .':</label>
		<input type="text" size="25"  maxlength="50" name="CustRef" value="' . $_SESSION['Items'.$identifier]->CustRef . '" title="" />
		<fieldhelp>' . __('Enter the customer\'s purchase order reference relevant to this order') . '</fieldhelp>
	</field>
	<field>
		<label for="Comments">' .  __('Comments') .':</label>
		<textarea name="Comments" cols="31" rows="5">' . $_SESSION['Items'.$identifier]->Comments  . '</textarea>
	</field>';

	if($CustomerLogin  == 1) {
		echo '<input type="hidden" name="SalesPerson" value="' . $_SESSION['Items'.$identifier]->SalesPerson . '" />
			<input type="hidden" name="DeliverBlind" value="1" />
			<input type="hidden" name="FreightCost" value="0" />
			<input type="hidden" name="ShipVia" value="' . $_SESSION['Items'.$identifier]->ShipVia . '" />
			<input type="hidden" name="Quotation" value="0" />';
	} else {
		echo '<field>
				<label for="SalesPerson">' . __('Sales person'). ':</label>
				<select name="SalesPerson">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1");
		if(!isset($_POST['SalesPerson']) AND $_SESSION['SalesmanLogin']!=NULL ) {
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		}

		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)) {
			if($SalesPersonRow['salesmancode']==$_SESSION['Items'.$identifier]->SalesPerson) {
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}

		echo '</select>
			</field>';

		/* This field will control whether or not to display the company logo and
		address on the packlist */

		echo '<field>
				<label for="DeliverBlind">' . __('Packlist Type') . ':</label>
				<select name="DeliverBlind">';

		if($_SESSION['Items'.$identifier]->DeliverBlind ==2) {
			echo '<option value="1">' . __('Show Company Details/Logo') . '</option>';
			echo '<option selected="selected" value="2">' . __('Hide Company Details/Logo') . '</option>';
		} else {
			echo '<option selected="selected" value="1">' . __('Show Company Details/Logo') . '</option>';
			echo '<option value="2">' . __('Hide Company Details/Logo') . '</option>';
		}
		echo '</select>
			</field>';

		if(isset($_SESSION['PrintedPackingSlip']) AND $_SESSION['PrintedPackingSlip']==1) {

			echo '<field>
					<label for="ReprintPackingSlip">' .  __('Reprint packing slip') .':</label>
					<select name="ReprintPackingSlip">';
			echo '<option value="0">' . __('Yes') . '</option>';
			echo '<option selected="selected" value="1">' . __('No') . '</option>';
			echo '</select>	'. __('Last printed') .': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</field>';
		} else {
			echo '<field><td><input type="hidden" name="ReprintPackingSlip" value="0" /></td></field>';
		}

		echo '<field>
				<label for="FreightCost">' .  __('Charge Freight Cost ex tax') .':</label>
				<input type="text" class="number" size="10" maxlength="12" name="FreightCost" value="' . $_SESSION['Items'.$identifier]->FreightCost . '" />';

		if($_SESSION['DoFreightCalc']==true) {
			echo '<td><input type="submit" name="Update" value="' . __('Recalc Freight Cost') . '" /></td>';
		}
		echo '</field>';

		if((!isset($_POST['ShipVia']) OR $_POST['ShipVia']=='') AND isset($_SESSION['Items'.$identifier]->ShipVia)) {
			$_POST['ShipVia'] = $_SESSION['Items'.$identifier]->ShipVia;
		}

		echo '<field>
				<label for="ShipVia">' .  __('Freight/Shipper Method') .':</label>
				<select name="ShipVia">';

		$ErrMsg = __('The shipper details could not be retrieved');

		$SQL = "SELECT shipper_id, shippername FROM shippers ORDER BY shippername";
		$ShipperResults = DB_query($SQL, $ErrMsg);
		while ($MyRow=DB_fetch_array($ShipperResults)) {
			if($MyRow['shipper_id']==$_POST['ShipVia']) {
				echo '<option selected="selected" value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
			}
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="Quotation">' .  __('Quotation Only') .':</label>
				<select name="Quotation">';
		if($_SESSION['Items'.$identifier]->Quotation==1) {
			echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
			echo '<option value="0">' . __('No') . '</option>';
		} else {
			echo '<option value="1">' . __('Yes') . '</option>';
			echo '<option selected="selected" value="0">' . __('No') . '</option>';
		}
		echo '</select>
			</field>';
	}//end if it is NOT a CustomerLogin

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="BackToLineDetails" value="' . __('Modify Order Lines') . '" />';

if($_SESSION['ExistingOrder'.$identifier]==0) {
	echo '<input type="submit" name="ProcessOrder" value="' . __('Place Order') . '" />';
	echo '<input type="submit" name="MakeRecurringOrder" value="' . __('Create Recurring Order') . '" />';
} else {
	echo '<input type="submit" name="ProcessOrder" value="' . __('Commit Order Changes') . '" />';
}

echo '</div>
      </form>';
include('includes/footer.php');
