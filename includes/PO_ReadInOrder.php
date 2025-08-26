<?php

/*PO_ReadInOrder.php is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

if (isset($_SESSION['PO'.$identifier])){
	unset ($_SESSION['PO'.$identifier]->LineItems);
	unset ($_SESSION['PO'.$identifier]);
}

$_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
$_SESSION['RequireSupplierSelection'] = 0;
$_SESSION['PO'.$identifier] = new PurchOrder;

$_SESSION['PO'.$identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

$OrderHeaderSQL = "SELECT purchorders.supplierno,
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
							purchorders.deliveryby,
							purchorders.port,
							suppliers.currcode,
							locations.managed,
							purchorders.paymentterms,
							currencies.decimalplaces
						FROM purchorders
						INNER JOIN locations ON purchorders.intostocklocation=locations.loccode
						INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
						INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
						WHERE purchorders.orderno = '" . $_GET['ModifyOrderNumber'] . "'";

   $ErrMsg =  __('The order cannot be retrieved because');
   $GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult)==1 and !isset($_SESSION['PO'.$identifier]->OrderNo )) {

		$MyRow = DB_fetch_array($GetOrdHdrResult);
		$_SESSION['PO'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['PO'.$identifier]->SupplierID = $MyRow['supplierno'];
		$_SESSION['PO'.$identifier]->SupplierName = $MyRow['suppname'];
		$_SESSION['PO'.$identifier]->CurrCode = $MyRow['currcode'];
		$_SESSION['PO'.$identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
		$_SESSION['PO'.$identifier]->Orig_OrderDate = $MyRow['orddate'];
		$_SESSION['PO'.$identifier]->AllowPrintPO = $MyRow['allowprint'];
		$_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted = $MyRow['dateprinted'];
		$_SESSION['PO'.$identifier]->Comments = $MyRow['comments'];
		$_SESSION['PO'.$identifier]->ExRate = $MyRow['rate'];
		$_SESSION['PO'.$identifier]->Location = $MyRow['intostocklocation'];
		$_SESSION['PO'.$identifier]->Initiator = $MyRow['initiator'];
		$_SESSION['PO'.$identifier]->RequisitionNo = $MyRow['requisitionno'];
		$_SESSION['PO'.$identifier]->DelAdd1 = $MyRow['deladd1'];
		$_SESSION['PO'.$identifier]->DelAdd2 = $MyRow['deladd2'];
		$_SESSION['PO'.$identifier]->DelAdd3 = $MyRow['deladd3'];
		$_SESSION['PO'.$identifier]->DelAdd4 = $MyRow['deladd4'];
		$_SESSION['PO'.$identifier]->DelAdd5 = $MyRow['deladd5'];
		$_SESSION['PO'.$identifier]->DelAdd6 = $MyRow['deladd6'];
		$_SESSION['PO'.$identifier]->Tel = $MyRow['tel'];
		$_SESSION['PO'.$identifier]->SuppDelAdd1 = $MyRow['suppdeladdress1'];
		$_SESSION['PO'.$identifier]->SuppDelAdd2 = $MyRow['suppdeladdress2'];
		$_SESSION['PO'.$identifier]->SuppDelAdd3 = $MyRow['suppdeladdress3'];
		$_SESSION['PO'.$identifier]->SuppDelAdd4 = $MyRow['suppdeladdress4'];
		$_SESSION['PO'.$identifier]->SuppDelAdd5 = $MyRow['suppdeladdress5'];
		$_SESSION['PO'.$identifier]->SuppDelAdd6 = $MyRow['suppdeladdress6'];
		$_SESSION['PO'.$identifier]->SupplierContact = $MyRow['suppliercontact'];
		$_SESSION['PO'.$identifier]->SuppTel= $MyRow['supptel'];
		$_SESSION['PO'.$identifier]->Contact = $MyRow['contact'];
		$_SESSION['PO'.$identifier]->Managed = $MyRow['managed'];
		$_SESSION['PO'.$identifier]->Version = $MyRow['version'];
		$_SESSION['PO'.$identifier]->Port = $MyRow['port'];
		$_SESSION['PO'.$identifier]->DeliveryBy = $MyRow['deliveryby'];
		$_SESSION['PO'.$identifier]->Status = $MyRow['status'];
		$_SESSION['PO'.$identifier]->StatusComments = html_entity_decode($MyRow['stat_comment'],ENT_QUOTES,'UTF-8');
		$_SESSION['PO'.$identifier]->DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
		$_SESSION['ExistingOrder'] = $_SESSION['PO'.$identifier]->OrderNo;
		$_SESSION['PO'.$identifier]->PaymentTerms= $MyRow['paymentterms'];

		$SupplierSQL = "SELECT suppliers.supplierid,
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

		$ErrMsg = __('The searched supplier records requested cannot be retrieved because');
		$Result_SuppSelect = DB_query($SupplierSQL, $ErrMsg);

		if (DB_num_rows($Result_SuppSelect)==1){
			$MyRow=DB_fetch_array($Result_SuppSelect);
		} elseif (DB_num_rows($Result_SuppSelect)==0){
			prnMsg( __('No supplier records contain the selected text') . ' - ' .
				__('please alter your search criteria and try again'),'info');
		}

/*now populate the line PO array with the purchase order details records */

		  $LineItemsSQL = "SELECT podetailitem,
								purchorderdetails.itemcode,
								stockmaster.description,
								purchorderdetails.deliverydate,
								purchorderdetails.itemdescription,
								glcode,
								accountname,
								purchorderdetails.qtyinvoiced,
								purchorderdetails.unitprice,
								stockmaster.units,
								purchorderdetails.quantityord,
								purchorderdetails.quantityrecd,
								purchorderdetails.shiptref,
								purchorderdetails.completed,
								purchorderdetails.jobref,
								purchorderdetails.stdcostunit,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.decimalplaces,
								purchorderdetails.assetid,
								purchorderdetails.conversionfactor,
								purchorderdetails.suppliersunit,
								purchorderdetails.suppliers_partno
								FROM purchorderdetails
								LEFT JOIN stockmaster
								ON purchorderdetails.itemcode=stockmaster.stockid
								INNER JOIN purchorders
								ON purchorders.orderno=purchorderdetails.orderno
								LEFT JOIN chartmaster
								ON purchorderdetails.glcode=chartmaster.accountcode
								WHERE purchorderdetails.completed=0
								AND purchorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY podetailitem";

		$ErrMsg =  __('The lines on the purchase order cannot be retrieved because');
		$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

	  if (DB_num_rows($LineItemsResult) > 0) {

			while ($MyRow=DB_fetch_array($LineItemsResult)) {

				 if (is_null($MyRow['glcode'])){
					$GLCode = '';
				 } else {
					$GLCode = $MyRow['glcode'];
				 }
				if (is_null($MyRow['units'])){
					$Units = __('each');
				} else {
					$Units = $MyRow['units'];
				}
				if (is_null($MyRow['itemcode'])){
					$StockID = '';
				} else {
					$StockID = $MyRow['itemcode'];
				}

				$_SESSION['PO'.$identifier]->add_to_order($_SESSION['PO'.$identifier]->LinesOnOrder+1,
														$StockID,
														$MyRow['serialised'],
														$MyRow['controlled'],
														$MyRow['quantityord'],
														stripslashes($MyRow['itemdescription']),
														$MyRow['unitprice'],
														$Units,
														$GLCode,
														ConvertSQLDate($MyRow['deliverydate']),
														$MyRow['shiptref'],
														$MyRow['completed'],
														$MyRow['jobref'],
														$MyRow['qtyinvoiced'],
														$MyRow['quantityrecd'],
														$MyRow['accountname'],
														$MyRow['decimalplaces'],
														$MyRow['suppliersunit'],
														$MyRow['conversionfactor'],
														1,
														$MyRow['suppliers_partno'],
														$MyRow['assetid'] );

				$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->PODetailRec = $MyRow['podetailitem'];
				$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->StandardCost = $MyRow['stdcostunit'];  /*Needed for receiving goods and GL interface */
		 } /* line PO from purchase order details */
  } //end is there were lines on the order
} // end if there was a header for the order
