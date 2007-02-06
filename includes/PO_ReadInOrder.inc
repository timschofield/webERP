<?php
/* $Revision: 1.13 $ */
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

       if (isset($_SESSION['PO'])){
              unset ($_SESSION['PO']->LineItems);
              unset ($_SESSION['PO']);
       }

       $_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
       $_SESSION['RequireSupplierSelection'] = 0;
       $_SESSION['PO'] = new PurchOrder;

       $_SESSION['PO']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

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
              $_SESSION['PO']->OrderNo = $_GET['ModifyOrderNumber'];
              $_SESSION['PO']->SupplierID = $myrow['supplierno'];
              $_SESSION['PO']->SupplierName = $myrow['suppname'];
              $_SESSION['PO']->CurrCode = $myrow['currcode'];
              $_SESSION['PO']->Orig_OrderDate = $myrow['orddate'];
              $_SESSION['PO']->AllowPrintPO = $myrow['allowprint'];
              $_SESSION['PO']->DatePurchaseOrderPrinted = $myrow['dateprinted'];
              $_SESSION['PO']->Comments = $myrow['comments'];
              $_SESSION['PO']->ExRate = $myrow['rate'];
              $_SESSION['PO']->Location = $myrow['intostocklocation'];
              $_SESSION['PO']->Initiator = $myrow['initiator'];
              $_SESSION['PO']->RequisitionNo = $myrow['requisitionno'];
              $_SESSION['PO']->DelAdd1 = $myrow['deladd1'];
              $_SESSION['PO']->DelAdd2 = $myrow['deladd2'];
              $_SESSION['PO']->DelAdd3 = $myrow['deladd3'];
              $_SESSION['PO']->DelAdd4 = $myrow['deladd4'];
              $_SESSION['PO']->DelAdd5 = $myrow['deladd5'];
              $_SESSION['PO']->DelAdd6 = $myrow['deladd6'];
              $_SESSION['PO']->Managed = $myrow['managed'];

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

					$_SESSION['PO']->add_to_order($_SESSION['PO']->LinesOnOrder+1,
							$StockID,
							$myrow['controlled'],
							$myrow['serialised'],
							$myrow['quantityord'],
							stripslashes($myrow['itemdescription']),
							$myrow['unitprice'],
							$Units,
							$GLCode,
							ConvertSQLDate($myrow['deliverydate']),
							$myrow['shiptref'],
							$myrow['jobref'],
							$myrow['qtyinvoiced'],
							$myrow['quantityrecd'],
							$myrow['accountname'],
							$myrow['decimalplaces']);

				    $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
	                $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
             } /* line PO from purchase order details */
      } //end is there were lines on the order
   } // end if there was a header for the order
?>