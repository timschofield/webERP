<?php
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

       if (isset($_SESSION['PO'])){
              unset ($_SESSION['PO']->LineItems);
              unset ($_SESSION['PO']);
       }

       Session_register("PO");
       Session_register("RequireSupplierSelection");
       Session_register("ExistingOrder");


       $_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
       $_SESSION['RequireSupplierSelection'] = 0;
       $_SESSION['PO'] = new PurchOrder;

       $CompanyRecord = ReadInCompanyRecord($db);
       $_SESSION['PO']->GLLink = $CompanyRecord["GLLink_Stock"];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

       $OrderHeaderSQL = "SELECT PurchOrders.SupplierNo, Suppliers.SuppName, PurchOrders.Comments, PurchOrders.OrdDate, PurchOrders.Rate, PurchOrders.DatePrinted, PurchOrders.DelAdd1, PurchOrders.DelAdd2, PurchOrders.DelAdd3, PurchOrders.DelAdd4, PurchOrders.AllowPrint, PurchOrders.RequisitionNo, PurchOrders.IntoStockLocation, PurchOrders.Initiator, Suppliers.CurrCode FROM PurchOrders, Suppliers WHERE PurchOrders.SupplierNo = Suppliers.SupplierID AND PurchOrders.OrderNo = " . $_GET['ModifyOrderNumber'];

       $GetOrdHdrResult = DB_query($OrderHeaderSQL,$db);
       if (DB_error_no($db) !=0) {
              echo "<BR>The order cannot be retrieved because - " . DB_error_msg($db);
              if ($debug==1){
                 echo "<BR>The SQL statement that was used and failed was:<BR>$OrderHeaderSQL";
              }
       } elseif (DB_num_rows($GetOrdHdrResult)==1) {

              $myrow = DB_fetch_array($GetOrdHdrResult);
              $_SESSION['PO']->OrderNo = $_GET['ModifyOrderNumber'];
              $_SESSION['PO']->SupplierID = $myrow["SupplierNo"];
              $_SESSION['PO']->SupplierName = $myrow["SuppName"];
              $_SESSION['PO']->CurrCode = $myrow["CurrCode"];
              $_SESSION['PO']->Orig_OrderDate = $myrow["OrdDate"];
              $_SESSION['PO']->AllowPrintPO = $myrow["AllowPrint"];
              $_SESSION['PO']->DatePurchaseOrderPrinted = $myrow["DatePrinted"];
              $_SESSION['PO']->Comments = $myrow["Comments"];
              $_SESSION['PO']->ExRate = $myrow["Rate"];
              $_SESSION['PO']->Location = $myrow["IntoStockLocation"];
              $_SESSION['PO']->Initiator = $myrow["Initiator"];
              $_SESSION['PO']->RequisitionNo = $myrow["RequisitionNo"];
              $_SESSION['PO']->DelAdd1 = $myrow["DelAdd1"];
              $_SESSION['PO']->DelAdd2 = $myrow["DelAdd2"];
              $_SESSION['PO']->DelAdd3 = $myrow["DelAdd3"];
              $_SESSION['PO']->DelAdd4 = $myrow["DelAdd4"];

/*now populate the line PO array with the purchase order details records */

              $LineItemsSQL = "SELECT PODetailItem, ItemCode, StockMaster.Description, DeliveryDate, ItemDescription, GLCode, AccountName, QtyInvoiced, UnitPrice, Units, QuantityOrd, QuantityRecd, ShiptRef, JobRef, StdCostUnit FROM PurchOrderDetails LEFT JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID LEFT JOIN ChartMaster ON PurchOrderDetails.GLCode=ChartMaster.AccountCode WHERE PurchOrderDetails.Completed=0 AND OrderNo =" . $_GET['ModifyOrderNumber'] . " ORDER BY PODetailItem";

              $LineItemsResult = db_query($LineItemsSQL,$db);

              if (DB_error_no($db) !=0) {
                     echo "<BR>The lines on the purchase order cannot be retrieved because - " . DB_error_msg($db);
                     if ($debug==1){
                           echo "<BR>The SQL statement that was used to retrieve the purchase order lines was:<BR>$LineItemsSQL";
                     }
              } elseif (db_num_rows($LineItemsResult) > 0) {

                     while ($myrow=db_fetch_array($LineItemsResult)) {

                             if (is_null($myrow["GLCode"])){
                                $GLCode = "";
                             } else {
                               $GLCode = $myrow["GLCode"];
                             }
                             if (is_null($myrow["Units"])){
                                $Units = "each";
                             } else {
                                $Units = $myrow["Units"];
                             }
                             if (is_null($myrow["ItemCode"])){
                                $StockID = "";
                             } else {
                               $StockID = $myrow["ItemCode"];
                             }
                                                 /*$LineNo,  				      StockID,           $Qty,             $ItemDescr,                 $Price,      $UOM,  $GLCode,                     $ReqDelDate,              $ShiptRef,          $JobRef,            $QtyInv,                $QtyRecd ,    GLAccountName*/
                            $_SESSION['PO']->add_to_order($_SESSION['PO']->LinesOnOrder+1, $StockID, $myrow["QuantityOrd"],$myrow["ItemDescription"], $myrow["UnitPrice"],$Units,$GLCode,ConvertSQLDate($myrow["DeliveryDate"]),$myrow["ShiptRef"],$myrow["JobRef"], $myrow["QtyInvoiced"], $myrow["QuantityRecd"], $myrow["AccountName"]);
                            $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->PODetailRec = $myrow["PODetailItem"];
                            $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->StandardCost = $myrow["StdCostUnit"];  /*Needed for receiving goods and GL interface */
                     } /* line PO from purchase order details */
              } //end of checks on returned data set
       }

?>
