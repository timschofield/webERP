<?php
/* $Revision: 1.16 $ */
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
				purchorders.allowprint, 
				purchorders.requisitionno, 
				purchorders.intostocklocation, 
				purchorders.initiator, 
				purchorders.version, 
				purchorders.status, 
				purchorders.stat_comment, 
				purchorders.deliverydate,
				suppliers.currcode,
				locations.managed 
			FROM purchorders 
				LEFT JOIN locations ON purchorders.intostocklocation=locations.loccode,
				suppliers 
			WHERE purchorders.supplierno = suppliers.supplierid 
			AND purchorders.orderno = ' . $_GET['ModifyOrderNumber'];

       $ErrMsg =  _('The order cannot be retrieved because');
       $DbgMsg =  _('The SQL statement that was used and failed was');
       $GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

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
              $_SESSION['PO'.$identifier]->Managed = $myrow['managed'];
			$_SESSION['PO'.$identifier]->version = $myrow['version'];
			$_SESSION['PO'.$identifier]->Stat = $myrow['status'];
			$_SESSION['PO'.$identifier]->StatComments = $myrow['stat_comment'];
			$_SESSION['PO'.$identifier]->deliverydate = ConvertSQLDate($myrow['deliverydate']);
			$_SESSION['ExistingOrder'] = $_SESSION['PO'.$identifier]->OrderNo;
			
/*now populate the line PO array with the purchase order details records */

              $LineItemsSQL = 'SELECT podetailitem, itemcode,
				stockmaster.description,
				deliverydate,
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
				stockmaster.controlled,
				stockmaster.serialised,
				stockmaster.decimalplaces
				FROM purchorderdetails LEFT JOIN stockmaster
					ON purchorderdetails.itemcode=stockmaster.stockid
					LEFT JOIN chartmaster
					ON purchorderdetails.glcode=chartmaster.accountcode
				WHERE purchorderdetails.completed=0
					AND purchorderdetails.orderno =' . $_GET['ModifyOrderNumber'] . "
				ORDER BY podetailitem";

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
							$myrow['decimalplaces']);

				    $_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
	                $_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
             } /* line PO from purchase order details */
      } //end is there were lines on the order
   } // end if there was a header for the order
?>