<?php
/* $Id$*/
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

	if (isset($_SESSION['PO'.$identifier])){
		unset ($_SESSION['PO'.$identifier]->LineItems);
		unset ($_SESSION['PO'.$identifier]);
	}

	$_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_SESSION['PO'.$identifier] = new PurchOrder;

	$_SESSION['PO'.$identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

	$OrderHeaderSQL = 'SELECT purchorders.supplierno,
				suppliers.suppname,
				purchorders.comments,
				purchorders.orddate,
				purchorders.rate,
				purchorders.dateprinted,
				purchorders.deladd1,
				purchorders.deladd2,
				purchorders.deladd3,
				purchorders.deladd4,
				purchorders.deladd5,
				purchorders.deladd6,
				purchorders.tel,
				purchorders.suppdeladdress1,
				purchorders.suppdeladdress2,
				purchorders.suppdeladdress3,
				purchorders.suppdeladdress4,
				purchorders.suppdeladdress5,
				purchorders.suppdeladdress6,
				purchorders.suppliercontact,
				purchorders.supptel,
				purchorders.contact,
				purchorders.allowprint,
				purchorders.requisitionno,
				purchorders.intostocklocation,
				purchorders.initiator,
				purchorders.version,
				purchorders.status,
				purchorders.stat_comment,
				purchorders.deliverydate,
				purchorders.port,
				suppliers.currcode,
				locations.managed ,
				purchorders.paymentterms
			FROM purchorders
				LEFT JOIN locations ON purchorders.intostocklocation=locations.loccode,
				suppliers
			WHERE purchorders.supplierno = suppliers.supplierid
			AND purchorders.orderno = ' . $_GET['ModifyOrderNumber'];

	   $ErrMsg =  _('The order cannot be retrieved because');
	   $DbgMsg =  _('The SQL statement that was used and failed was');
	   $GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1 and !isset($_SESSION['PO'.$identifier]->OrderNo )) {

			$myrow = DB_fetch_array($GetOrdHdrResult);
			$_SESSION['PO'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
			$_SESSION['PO'.$identifier]->SupplierID = $myrow['supplierno'];
			$_SESSION['PO'.$identifier]->SupplierName = $myrow['suppname'];
			$_SESSION['PO'.$identifier]->CurrCode = $myrow['currcode'];
			$_SESSION['PO'.$identifier]->Orig_OrderDate = $myrow['orddate'];
			$_SESSION['PO'.$identifier]->AllowPrintPO = $myrow['allowprint'];
			$_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted = $myrow['dateprinted'];
			$_SESSION['PO'.$identifier]->Comments = $myrow['comments'];
			$_SESSION['PO'.$identifier]->ExRate = $myrow['rate'];
			$_SESSION['PO'.$identifier]->Location = $myrow['intostocklocation'];
			$_SESSION['PO'.$identifier]->Initiator = $myrow['initiator'];
			$_SESSION['PO'.$identifier]->RequisitionNo = $myrow['requisitionno'];
			$_SESSION['PO'.$identifier]->DelAdd1 = $myrow['deladd1'];
			$_SESSION['PO'.$identifier]->DelAdd2 = $myrow['deladd2'];
			$_SESSION['PO'.$identifier]->DelAdd3 = $myrow['deladd3'];
			$_SESSION['PO'.$identifier]->DelAdd4 = $myrow['deladd4'];
			$_SESSION['PO'.$identifier]->DelAdd5 = $myrow['deladd5'];
			$_SESSION['PO'.$identifier]->DelAdd6 = $myrow['deladd6'];
			$_SESSION['PO'.$identifier]->tel = $myrow['tel'];
			$_SESSION['PO'.$identifier]->suppDelAdd1 = $myrow['suppdeladdress1'];
			$_SESSION['PO'.$identifier]->suppDelAdd2 = $myrow['suppdeladdress2'];
			$_SESSION['PO'.$identifier]->suppDelAdd3 = $myrow['suppdeladdress3'];
			$_SESSION['PO'.$identifier]->suppDelAdd4 = $myrow['suppdeladdress4'];
			$_SESSION['PO'.$identifier]->suppDelAdd5 = $myrow['suppdeladdress5'];
			$_SESSION['PO'.$identifier]->suppDelAdd6 = $myrow['suppdeladdress6'];
			$_SESSION['PO'.$identifier]->SupplierContact = $myrow['suppliercontact'];
			$_SESSION['PO'.$identifier]->supptel= $myrow['supptel'];
			$_SESSION['PO'.$identifier]->contact = $myrow['contact'];
			$_SESSION['PO'.$identifier]->Managed = $myrow['managed'];
			$_SESSION['PO'.$identifier]->version = $myrow['version'];
			$_SESSION['PO'.$identifier]->port = $myrow['port'];
			$_SESSION['PO'.$identifier]->Stat = $myrow['status'];
			$_SESSION['PO'.$identifier]->StatComments = $myrow['stat_comment'];
			$_SESSION['PO'.$identifier]->deliverydate = ConvertSQLDate($myrow['deliverydate']);
			$_SESSION['ExistingOrder'] = $_SESSION['PO'.$identifier]->OrderNo;
			$_SESSION['PO'.$identifier]->paymentterms= $myrow['paymentterms'];

			$supplierSQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode
				FROM suppliers
				WHERE suppliers.supplierid='" . $_SESSION['PO'.$identifier]->SupplierID."'
				ORDER BY suppliers.supplierid";

			$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
			$result_SuppSelect = DB_query($supplierSQL,$db,$ErrMsg);

			if (DB_num_rows($result_SuppSelect)==1){
				$myrow=DB_fetch_array($result_SuppSelect);
//				$_POST['Select'] = $myrow['supplierid'];
			} elseif (DB_num_rows($result_SuppSelect)==0){
				prnMsg( _('No supplier records contain the selected text') . ' - ' .
					_('please alter your search criteria and try again'),'info');
			}

/*now populate the line PO array with the purchase order details records */

			  $LineItemsSQL = "SELECT podetailitem,
										itemcode,
										stockmaster.description,
										purchorderdetails.deliverydate,
										itemdescription,
										glcode,
										accountname,
										qtyinvoiced,
										unitprice,
										units,
										quantityord,
										quantityrecd,
										shiptref,
										completed,
										jobref,
										stdcostunit,
										itemno,
										stockmaster.controlled,
										stockmaster.serialised,
										stockmaster.decimalplaces,
										subtotal_amount,
										package,
										pcunit,
										nw,
										gw,
										cuft,
										total_quantity,
										total_amount
										FROM purchorderdetails
											LEFT JOIN stockmaster
											ON purchorderdetails.itemcode=stockmaster.stockid
											LEFT JOIN purchorders
											ON purchorders.orderno=purchorderdetails.orderno
											LEFT JOIN chartmaster
											ON purchorderdetails.glcode=chartmaster.accountcode
										WHERE purchorderdetails.completed=0
											AND purchorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . '
										ORDER BY podetailitem';

		  $ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
		  $DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
			  $LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);

		  if (db_num_rows($LineItemsResult) > 0) {

				while ($myrow=db_fetch_array($LineItemsResult)) {

					 if (is_null($myrow['glcode'])){
						$GLCode = '';
					 } else {
						$GLCode = $myrow['glcode'];
					 }
					if (is_null($myrow['units'])){
						$Units = _('each');
					} else {
						$Units = $myrow['units'];
					}
					if (is_null($myrow['itemcode'])){
						$StockID = '';
					} else {
						$StockID = $myrow['itemcode'];
					}
					$datesql='SELECT max(effectivefrom)
							FROM purchdata
							WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
								AND stockid="'.$StockID.'"
								AND effectivefrom<="'.$_SESSION['PO'.$identifier]->Orig_OrderDate.'"';
					$dateresult=DB_query($datesql, $db);
					$date=DB_fetch_row($dateresult);
					if ($date[0]!='') {
						$sql='SELECT unitsofmeasure.unitname,
									suppliers_partno
								FROM purchdata
								LEFT JOIN unitsofmeasure
								ON purchdata.suppliersuom=unitsofmeasure.unitid
								WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
								AND stockid="'.$StockID.'"
								AND effectivefrom="'.$date[0].'"';
						$purchdataresult=DB_query($sql, $db);
						$myrow2=DB_fetch_array($purchdataresult);
					} else {
						$sql='SELECT units as unitname, stockid as suppliers_partno FROM stockmaster WHERE stockid="'.$StockID.'"';
						$purchdataresult=DB_query($sql, $db);
						$myrow2=DB_fetch_array($purchdataresult);
					}

					$_SESSION['PO'.$identifier]->add_to_order($_SESSION['PO'.$identifier]->LinesOnOrder+1,
							$StockID,
							$myrow['serialised'],
							$myrow['controlled'],
							$myrow['quantityord'],
							stripslashes($myrow['itemdescription']),
							$myrow['unitprice'],
							$Units,
							$GLCode,
							ConvertSQLDate($myrow['deliverydate']),
							$myrow['shiptref'],
							$myrow['completed'],
							$myrow['jobref'],
							$myrow['qtyinvoiced'],
							$myrow['quantityrecd'],
							$myrow['accountname'],
							$myrow['decimalplaces'],
							$myrow['itemno'],
							$myrow2['unitname'],
							$myrow2['suppliers_partno'],
							$myrow['subtotal_amount'],
							0,
							$myrow['package'],
							$myrow['pcunit'],
							$myrow['nw'],
							$myrow['gw'],
							$myrow['cuft'],
							$myrow['total_quantity'],
							$myrow['total_amount']);

					$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
					$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
			 } /* line PO from purchase order details */
	  } //end is there were lines on the order
   } // end if there was a header for the order
?>