<?php

$title = "Purchase Order Items";

$PageSecurity = 4;

include("includes/DefinePOClass.php");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");

if (!isset($_SESSION['PO'])){
   header ("Location:" . $rootpath . "/PO_Header.php?" . SID);
   exit;
}



if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

 /*First do some validation
	  Is the delivery information all entered*/
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['PO']->DelAdd1=="" or strlen($_SESSION['PO']->DelAdd1)<3){
	      echo "<BR><B>The purchase order can not be committed to the database because there is no delivery steet address specified.";
	      $InputError=1;
	} elseif ($_SESSION['PO']->DelAdd2=="" OR strlen($_SESSION['PO']->DelAdd2)<3){
	      echo "<BR><B>The purchase order can not be committed to the database because there is no suburb address specified.";
	      $InputError=1;
	} elseif ($_SESSION['PO']->Location=="" OR ! isset($_SESSION['PO']->Location)){
	      echo "<BR><B>The purchase order can not be committed to the database because there is no location specified to book any stock items into.";
	      $InputError=1;
	} elseif ($_SESSION['PO']->LinesOnOrder <=0){
	     echo "<BR><B>The purchase order can not be committed to the database because there are no lines entered on this order.";
	     $InputError=1;
	}

	if ($InputError!=1){


		 $sql = "Begin";
		 $result = DB_query($sql,$db);

		 if ($_SESSION['ExistingOrder']==0){ /*its a new order to be inserted */

		     /*Insert to purchase order header record */
		     $sql = "INSERT INTO PurchOrders (SupplierNo, Comments, OrdDate, Rate, Initiator, RequisitionNo, IntoStockLocation, DelAdd1, DelAdd2, DelAdd3, DelAdd4) VALUES(";
		     $sql = $sql . "'" . $_SESSION['PO']->SupplierID . "', '" . $_SESSION['PO']->Comments . "','" . Date("Y-m-d") . "'," . $_SESSION['PO']->ExRate . ",'" . $_SESSION['PO']->Initiator . "', '" . $_SESSION['PO']->RequisitionNo . "', '" . $_SESSION['PO']->Location . "', '" . $_SESSION['PO']->DelAdd1 . "', '" . $_SESSION['PO']->DelAdd2 . "', '" . $_SESSION['PO']->DelAdd3 . "', '" .$_SESSION['PO']->DelAdd4 . "')";

		     $result = DB_query($sql,$db);

		     if (DB_error_no($db) !=0) {
			     echo "The purchase order header record could not be inserted into the database because - " . DB_error_msg($db);
			     if ($debug==1){
				  echo "<BR>The SQL statement used to insert the purchase order header record and failed was:<BR>$sql";
			     }
			     $result=DB_query("rollback",$db);
			     exit;
		     }
		     /*Get the auto increment value of the order number created from the SQL above */
		     $_SESSION['PO']->OrderNo = DB_Last_Insert_ID($db);

		     /*Insert the purchase order detail records */
		     foreach ($_SESSION['PO']->LineItems as $POLine) {
			 	if ($POLine->Deleted==False) {

					$sql = "INSERT INTO PurchOrderDetails (OrderNo, ItemCode, DeliveryDate, ItemDescription, GLCode, UnitPrice, QuantityOrd,  ShiptRef, JobRef) VALUES (";
					$sql = $sql . $_SESSION['PO']->OrderNo . ", '" . $POLine->StockID . "','" . FormatDateForSQL($POLine->ReqDelDate) . "','" . $POLine->ItemDescription . "', " . $POLine->GLCode . "," . $POLine->Price . ", " . $POLine->Quantity . ", '" . $POLine->ShiptRef . "', '" . $POLine->JobRef . "')";
					$result =DB_query($sql,$db);
					if (DB_error_no($db) !=0) {
						  echo "<BR>One of the purchase order detail records could not be inserted into the database because - " . DB_error_msg($db);
						  if ($debug==1){
						  echo "<BR>The SQL statement used to insert the purchase order detail record and failed was:<BR>$sql";
						  }
						  $result=DB_query("rollback",$db);
						  exit;
					} /*end of if theres an error inserting the detail line */
				}
		     } /* end of the loop round the detail line items on the order */
		     echo "<BR><BR>Purchase order " . $_SESSION['PO']->OrderNo . " on " . $_SESSION['PO']->SupplierName . " has been created.";
		     echo "<BR><A target='_blank' HREF='$rootpath/PO_PDFPurchOrder.php?" . SID . "&OrderNo=" . $_SESSION['PO']->OrderNo . "'>Print Order</A>";
		 } else { /*its an existing order need to update the old order info */

		     /*Update the purchase order header with any changes */
		     $sql = "UPDATE PurchOrders SET SupplierNo = '" . $_SESSION['PO']->SupplierID . "' , Comments='" . $_SESSION['PO']->Comments . "', Rate=" . $_SESSION['PO']->ExRate . ", Initiator='" . $_SESSION['PO']->Initiator . "', RequisitionNo= '" . $_SESSION['PO']->RequisitionNo . "', IntoStockLocation='" . $_SESSION['PO']->Location . "', DelAdd1='" . $_SESSION['PO']->DelAdd1 . "', DelAdd2='" . $_SESSION['PO']->DelAdd2 . "', DelAdd3='" . $_SESSION['PO']->DelAdd3 . "', DelAdd4='" . $_SESSION['PO']->DelAdd4 . "', AllowPrint=" . $_SESSION['PO']->AllowPrintPO;
		     $sql = $sql . " WHERE OrderNo = " . $_SESSION['PO']->OrderNo;
		     $result =DB_query($sql,$db);
		     if (DB_error_no($db) !=0) {
			  echo "<BR>The purchase order could not be updated because - " . DB_error_msg($db);
			  if ($debug==1){
			      echo "<BR>The SQL statement used to update the purchase order header record, that failed was:<BR>$sql";
			  }
			  $result=DB_query("rollback",$db);
			  include ("includes/footer.inc");
			  exit;
		     }

		     /*Now Update the purchase order detail records */
		     foreach ($_SESSION['PO']->LineItems as $POLine) {

				if ($POLine->Deleted==True) {
					if ($POLine->PODetailRec!='') {
						$sql="DELETE FROM PurchOrderDetails WHERE PODetailItem='" . $POLine->PODetailRec . "'";
						$result = DB_query($sql,$db);
					}
				} else if ($POLine->PODetailRec=='') {

					$sql = "INSERT INTO PurchOrderDetails (OrderNo, ItemCode, DeliveryDate, ItemDescription, GLCode, UnitPrice,		 QuantityOrd, ShiptRef, JobRef) VALUES (";
					$sql = $sql . $_SESSION['PO']->OrderNo . ", '" . $POLine->StockID . "','" . FormatDateForSQL($POLine->ReqDelDate) . "','" . $POLine->ItemDescription . "', " . $POLine->GLCode . "," . $POLine->Price . ", " . $POLine->Quantity . ", '" . $POLine->ShiptRef . "', '" . $POLine->JobRef . "')";
				} else {
					if ($POLine->Quantity==$POLine->QtyReceived){
						$sql = "UPDATE PurchOrderDetails SET ItemCode='" . $POLine->StockID . "', DeliveryDate ='" . FormatDateForSQL($POLine->ReqDelDate) . "', ItemDescription='" . $POLine->ItemDescription . "', GLCode=" . $POLine->GLCode . ", UnitPrice=" . $POLine->Price . ", QuantityOrd=" . $POLine->Quantity . ", ShiptRef='" . $POLine->ShiptRef . "', JobRef='" . $POLine->JobRef . "', Completed=1 WHERE PODetailItem=" . $POLine->PODetailRec;
					} else {
						$sql = "UPDATE PurchOrderDetails SET ItemCode='" . $POLine->StockID . "', DeliveryDate ='" . FormatDateForSQL($POLine->ReqDelDate) . "', ItemDescription='" . $POLine->ItemDescription . "', GLCode=" . $POLine->GLCode . ", UnitPrice=" . $POLine->Price . ", QuantityOrd=" . $POLine->Quantity . ", ShiptRef='" . $POLine->ShiptRef . "', JobRef='" . $POLine->JobRef . "' WHERE PODetailItem=" . $POLine->PODetailRec;
					}
				}
				$result =DB_query($sql,$db);
				if (DB_error_no($db) !=0) {
				      echo "<BR>One of the purchase order detail records could not be updated because - " . DB_error_msg($db);
				      if ($debug==1){
					  echo "<BR>The SQL statement used to update the purchase order detail record that failed was:<BR>$sql";
				      }
				      $result=DB_query("rollback",$db);
				      exit;
				} /*end of if theres an error updating the detail line */
		     } /* end of the loop round the detail line items on the order */
		     echo "<BR><BR>Purchase order " . $_SESSION['PO']->OrderNo . " has been updated.";
		     if ($_SESSION['PO']->AllowPrintPO==1){
			     echo "<BR><A target='_blank' HREF='$rootpath/PO_PDFPurchOrder.php?" . SID . "&OrderNo=" . $_SESSION['PO']->OrderNo . "'>Re-Print Order</A>";
		     }
		 } /*end of if its a new order or an existing one */

		 $sql = "Commit";
		 $Result = DB_query($sql,$db);

		 unset($_SESSION['PO']); /*Clear the PO data to allow a newy to be input*/
		 echo "<BR><BR><A HREF='$rootpath/PO_Header.php?" . SID . "&NewOrder=Yes'>Enter A New Purchase Order</A>";
		 echo "<BR><A HREF='$rootpath/PO_SelectPurchOrder.php?" . SID . "'>Select An Outstanding Purchase Order</A>";
		 exit;
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the PO object to the database  - user hit the place PO*/



If ($_POST['Search']){  /*ie seach for stock items */

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg="Stock description keywords have been used in preference to the Stock code extract entered.";
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces

		$i=0;
		$SearchString = "%";
		while (strpos($_POST['Keywords'], " ", $i)) {
			$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
			$i=strpos($_POST['Keywords']," ",$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

		if ($_POST['StockCat']=="All"){
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' AND StockMaster.Description LIKE '$SearchString' ORDER BY StockMaster.StockID";
		} else {
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' AND StockMaster.Description LIKE '$SearchString' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = "%" . $_POST['StockCode'] . "%";

		if ($_POST['StockCat']=="All"){
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' AND StockMaster.StockID like '" . $_POST['StockCode'] . "' ORDER BY StockMaster.StockID";
		} else {
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' AND StockMaster.StockID like '" . $_POST['StockCode'] . "' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
		}

	} else {
		if ($_POST['StockCat']=="All"){
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' ORDER BY StockMaster.StockID";
		} else {
			$SQL = "SELECT StockMaster.StockID, StockMaster.Description, StockMaster.Units FROM StockMaster INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID WHERE StockMaster.MBflag!='D' AND StockMaster.MBflag!='A' AND StockMaster.MBflag!='K' AND StockMaster.CategoryID='" . $_POST['StockCat'] . "' ORDER BY StockMaster.StockID";
		}
	}
	$SearchResult = DB_query($SQL,$db);

	if (DB_error_no($db)!=0){
		echo "<BR>There is a problem selecting the part records to display because - " . DB_error_msg($db);
	}

	if (DB_num_rows($SearchResult)==0 && $debug==1){
		echo "<BR>No products to display matching the criteria provided.";

		if ($debug==1){
			echo "<P>The SQL statement used was:<BR>$SQL";
		}
	}
	if (DB_num_rows($SearchResult)==1){

		$myrow=DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $myrow["StockID"];
		DB_data_seek($SearchResult,0);
	}

} //end of if search

/* Always do the stuff below if not looking for a supplierid */

If($_POST['Delete']=="Delete"){
	if($_SESSION['PO']->Some_Already_Received($_POST['LineNo'])==0){
		$_SESSION['PO']->remove_from_order($_POST['LineNo']);
		include ("includes/PO_UnsetFormVbls.php");
	} else {
		echo "<BR>This item cannot be deleted because some of it has already been received.";
	}
}

if ($_POST['LookupPrice']=="Lookup Price" AND $_POST['StockID']!=""){

	$sql = "SELECT PurchData.Price, PurchData.ConversionFactor, PurchData.SupplierDescription FROM PurchData WHERE  PurchData.SupplierNo = '" . $_SESSION['PO']->SupplierID . "' AND PurchData.StockID = '". $_POST['StockID'] . "'";
	    $LookupResult = DB_query($sql,$db);

	if (DB_error_no($db) !=0) {
	     echo "<BR>The supplier pricing details for " . $_POST['StockID'] . " could not be retrieved because - " . DB_error_msg($db);
	     if ($debug==1){
		  echo "<BR>The SQL used to retrieve the pricing details but failed was:<BR>$sql";
	     }
	     exit;
	}
	if (DB_num_rows($LookupResult)==1){
		$myrow = DB_fetch_array($LookupResult);
		$_POST['Price'] = $myrow["Price"]/$myrow['ConversionFactor'];
	} else {
		echo "<BR>Sorry ... there is no purchasing data set up for this supplier  - " . $_SESSION['PO']->SupplierID . " and item " . $_POST['StockID'];
	}
}

If(isset($_POST['UpdateLine']) AND $_POST['UpdateLine']=="Update Line"){
	$AllowUpdate=True; /*Start assuming the best ... now look for the worst*/

	if ($_POST['Qty']==0 OR $_POST['Price'] < 0){
		$AllowUpdate = False;
		echo "<BR><B>The Update Could Not Be Processed</B><BR>You are attempting to set the quantity ordered to zero, or the price is set to an amount less than 0";
	}

	if ($_SESSION['PO']->LineItems[$_POST['LineNo']]->QtyInv > $_POST['Qty'] OR $_SESSION['PO']->LineItems[$_POST['LineNo']]->QtyReceived > $_POST['Qty']){
		$AllowUpdate = False;
		echo "<BR><B>The Update Could Not Be Processed</B><BR>You are attempting to make the quantity ordered a quantity less than has already been invoiced or received this is of course prohibited. The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item.";

	}

	if ($_SESSION['PO']->GLLink==1) {
	/*Check for existance of GL Code selected */
		$sql = "SELECT AccountName FROM ChartMaster WHERE AccountCode =" .  $_POST['GLCode'];
		$GLActResult = DB_query($sql,$db);
		if (DB_error_no!=0 OR DB_num_rows($GLActResult)==0){
			 $AllowUpdate = False;
			 echo "<BR><B>The Update Could Not Be Processed</B><BR>The GL account code selected does not exist in the database see the listing of GL Account Codes to ensure a valid account is selected.";
		} else {
			$GLActRow = DB_fetch_row($GLActResult);
			$GLAccountName = $GLActRow[0];
		}
	}

	include ("PO_Chk_ShiptRef_JobRef.php");


	if ($AllowUpdate == True) {
						 /*$_POST['LineNo'], $_POST['Qty'], $_POST['Price'], $_POST['ItemDescription'], $_POST['GLCode'], $GLAccountName,$_POST['ReqDelDate'],$_POST['ShiptRef'], $_POST['JobRef'] */
	      $_SESSION['PO']->update_order_item($_POST['LineNo'], $_POST['Qty'], $_POST['Price'], $_POST['ItemDescription'], $_POST['GLCode'], $GLAccountName, $_POST['ReqDelDate'], $_POST['ShiptRef'], $_POST['JobRef'] );
	      include ("includes/PO_UnsetFormVbls.php");

	}
}

If (isset($_POST['EnterLine']) AND $_POST['EnterLine']=="Enter Line" ){ /*Inputs from the form directly without selecting a stock item from the search */

     $AllowUpdate = True; /*always assume the best */

     if (!is_numeric($_POST['Qty'])){
	   $AllowUpdate = False;
	   echo "<BR><BR>Cannot Enter this order line</B><BR>The quantity of the order item must be numeric.";
     }
     if ($_POST['Qty']<0){
	   $AllowUpdate = False;
	   echo "<BR><BR>Cannot Enter this order line</B><BR>The quantity of the ordered item entered must be a positive amount";
     }
     if (!is_numeric($_POST['Price'])){
	   $AllowUpdate = False;
	   echo "<BR><BR>Cannot Enter this order line</B><BR>The price entered must be numeric.";
     }
     if (!is_date($_POST['ReqDelDate'])){
	 $AllowUpdate = False;
	 echo "<BR><BR>Cannot Enter this order line</B><BR>The date entered must be in the format $DefaultDateFormat";
     }

     include ("PO_Chk_ShiptRef_JobRef.php");


     if ($_POST['StockID']!="" AND $AllowUpdate==True){ /* A stock item has been entered - skip if inputs crook*/

		$_POST['StockID'] = strtoupper($_POST['StockID']);

		if ($PO_AllowSameItemMultipleTimes ==False){
			if (count($_SESSION['PO']->LineItems)>0){
				foreach ($_SESSION['PO']->LineItems AS $OrderItem) { /*now test for the worst */

					/* do a loop round the items on the order to see that the item
					is not already on this order */

					if (($OrderItem->StockID == $_POST['StockID']) AND ($OrderItem->Deleted==False)) {
						$AllowUpdate = False;
						echo "<BR><B>Warning:</B> the part " . $_POST['StockID'] . " is already on this order - the system will not allow the same item on the order more than once. However, you can change the quantity by selecting it from the order summary";
					}
				} /* end of the foreach loop to look for pre-existing items of the same code */
			}
		} /*Only check for multiples if not allowed */

		if ($AllowUpdate == True){ /*Dont bother with this lot if already discovered input is stuffed */

		    if ($_SESSION['PO']->GLLink==1){
			   $sql = "SELECT StockMaster.Description, StockMaster.Units, StockMaster.MBflag, StockCategory.StockAct, ChartMaster.AccountName FROM StockCategory, ChartMaster, StockMaster WHERE ChartMaster.AccountCode = StockCategory.StockAct AND StockCategory.CategoryID = StockMaster.CategoryID AND StockMaster.StockID = '". $_POST['StockID'] . "'";
		    } else {
			   $sql = "SELECT StockMaster.Description, StockMaster.Units, StockMaster.MBflag FROM StockMaster WHERE StockMaster.StockID = '". $_POST['StockID'] . "'";
		    }
		    $result1 = DB_query($sql,$db);

		    if (DB_error_no($db) !=0) {
			     echo "<BR>The stock details for " . $_POST['StockID'] . " could not be retrieved because - " . DB_error_msg($db);
			     if ($debug==1){
				  echo "<BR>The SQL used to retrieve the details of the item, but failed was:<BR>$sql";
			     }
			     include("includes/footer.inc");
			     exit;
		    }

		    if (DB_num_rows($result1)==0){ // the stock item does not exist in the DB
			$AllowUpdate = False;
		    }

		   if ($myrow = DB_fetch_array($result1) AND $AllowUpdate == True AND $myrow["MBflag"]!="A" AND $myrow["MBflag"]!="K" AND $myrow["MBflag"]!="D"){

						 	/*$_POST['LineNo'], $_POST['StockID'],		 $_POST['Qty'], 	   $ItemDescr,	   $_POST['Price'],	 $UOM, 	    $_POST['GLCode'],	    $_POST['ReqDelDate'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['Qty']Inv, $_POST['Qty']Recd ,   GLAccountName*/
			    	if ($_SESSION['PO']->GLLink==1){
					 $_SESSION['PO']->add_to_order ($_POST['LineNo'], $_POST['StockID'], $_POST['Qty'], $myrow["Description"], $_POST['Price'], $myrow["Units"], $myrow["StockAct"], $_POST['ReqDelDate'],$_POST['ShiptRef'],$_POST['JobRef'],	     0,	0, $myrow["AccountName"]);
			    	} else {
					 $_SESSION['PO']->add_to_order ($_POST['LineNo'], $_POST['StockID'], $_POST['Qty'], $myrow["Description"], $_POST['Price'], $myrow["Units"], 0, $_POST['ReqDelDate'],$_POST['ShiptRef'],$_POST['JobRef'],	  0,	    0, "");
			    	}
			    	include ("includes/PO_UnsetFormVbls.php");
		   } else {
			     echo "<BR><BR>Cannot Enter this order line:<BR>Either the part code '" . $_POST['StockID'] . "' does not exist in the database OR the part is an assembly, kit or dummy part and therefore cannot be purchased.</FONT><P>";
			     if ($debug==1){
				    echo "<BR>$sql";
			     }
		   }

		} /* end of if not already on the order and allow input was true*/


     } /*end if its a stock item */
	else { /*Then its not a stock item */

	   /*need to check GL Code is valid if GLLink is active */
	   if ($_SESSION['PO']->GLLink==1){

	      $sql = "SELECT AccountName FROM ChartMaster WHERE AccountCode =" . (int) $_POST['GLCode'];
	      $GLValidResult = DB_query($sql,$db);
	      if (DB_error_no($db) !=0) {
		      $AllowUpdate = False;
		      echo "<BR>The validation process for the GL Code entered could not be executed because - " . DB_error_msg($db);
		      if ($debug==1){
			     echo "<BR>The SQL used to validate the code entered, but failed was:<BR>$sql";
		      }
		      exit;
	      }
	      if (DB_num_rows($GLValidResult) == 0) { /*The GLCode entered does not exist */
		     $AllowUpdate = False;
		      echo "<BR><BR>Cannot enter this order line:<BR>The general ledger code - " . $_POST['GLCode'] . " is not a general ledger code that is defined in the chart of accounts. Please use a code that is already defined. See the Chart list from the link below.</FONT>";
	      } else {
		      $myrow = DB_fetch_row($GLValidResult);
		      $GLAccountName = $myrow[0];
	      }
	   } /* dont bother checking the GL Code if there is no GL code to check ie not linked to GL */
	     else {
		$_POST['GLCode']=0;
	   }
	   if (strlen($_POST['ItemDescription'])<=3){
		   $AllowUpdate = False;
		   echo "<BR><BR>Cannot enter this order line:<BR>The description of the item being purchase is required where a non-stock item is being ordered";
	    }

	    if ($AllowUpdate == True){
					 /*$_POST['LineNo'],	   StockID,$_POST['Qty'],  $ItemDescr,    $_POST['Price'], $UOM,	$_POST['GLCode'],  $_POST['ReqDelDate'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['Qty']Inv, $_POST['Qty']Recd ,   GLAccountName*/
		   $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1, "", $_POST['Qty'], $_POST['ItemDescription'], $_POST['Price'], "each", $_POST['GLCode'], $_POST['ReqDelDate'],$_POST['ShiptRef'],$_POST['JobRef'],	  0,	    0, $GLAccountName);
		   include ("includes/PO_UnsetFormVbls.php");
	    }

     }

} /*end if Enter line button was hit */


If (isset($_GET['NewItem'])){ /* NewItem is set from the part selection list as the part code selected */
/* take the form entries and enter the data from the form into the PurchOrder class variable */
	$AlreadyOnThisOrder =0;

	if (count($_SESSION['PO']->LineItems)!=0){

		foreach ($_SESSION['PO']->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		    if (($OrderItem->StockID == $_GET['NewItem'])  AND ($OrderItem->Deleted==False)) {
			  $AlreadyOnThisOrder = 1;
			  echo "<BR><B>Warning:</B> the part " . $_GET['NewItem'] . " is already on this order - the system will not allow the same item on the order more than once. However, you can change the quantity ordered of the existing line if necessary";
		    }
		} /* end of the foreach loop to look for preexisting items of the same code */
	}
	if ($AlreadyOnThisOrder!=1){

	    $sql = "SELECT StockMaster.Description, StockMaster.StockID, StockMaster.Units, StockCategory.StockAct, ChartMaster.AccountName, PurchData.Price, PurchData.ConversionFactor, PurchData.SupplierDescription FROM StockCategory, ChartMaster, StockMaster LEFT JOIN PurchData ON StockMaster.StockID = PurchData.StockID AND PurchData.SupplierNo = '" . $_SESSION['PO']->SupplierID . "' WHERE ChartMaster.AccountCode = StockCategory.StockAct AND StockCategory.CategoryID = StockMaster.CategoryID AND StockMaster.StockID = '". $_GET['NewItem'] . "'";
	    $result1 = DB_query($sql,$db);

	    if (DB_error_no($db) !=0) {
		     echo "<BR>The supplier pricing details for " . $_GET['NewItem'] . " could not be retrieved because - " . DB_error_msg($db);
		     if ($debug==1){
			  echo "<BR>The SQL used to retrieve the pricing details but failed was:<BR>$sql";
		     }
		     exit;
	    }

	   if ($myrow = DB_fetch_array($result1)){
		      if (is_numeric($myrow["Price"])){
						 /*$_POST['LineNo'],		 $_POST['StockID'], $_POST['Qty'],	$ItemDescr,		 $_POST['Price'],		 $UOM,      $_POST['GLCode'], 	$_POST['ReqDelDate'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['Qty']Inv, $_POST['Qty']Recd ,   GLAccountName*/
			     $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1, $_GET['NewItem'], 1, $myrow["Description"], $myrow["Price"], $myrow["Units"], $myrow["StockAct"], Date($DefaultDateFormat),0,	 0,	     0,	0, $myrow["AccountName"]);
		      } else { /*There was no supplier purchasing data for the item selected so enter a purchase order line with zero price */
						 /*$_POST['LineNo'],		 $_POST['StockID'], $_POST['Qty'],	$ItemDescr,	 $_POST['Price'],   $UOM,		 $_POST['GLCode'],	$_POST['ReqDelDate'], $_POST['ShiptRef'], $_POST['JobRef'], $_POST['Qty']Inv, $_POST['Qty']Recd */
			     $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1, $_GET['NewItem'], 1, $myrow["Description"], 0, $myrow["Units"], $myrow["StockAct"], Date($DefaultDateFormat), 0,	  0,	    0,       0, $myrow["AccountName"]);
		      }
		      /*Make sure the line is also available for editing by default without additional clicks */
		      $_GET['Edit'] = $_SESSION['PO']->LinesOnOrder; /* this is a bit confusing but it was incremented by the add_to_order function */
	   } else {
		      echo "<BR><FONT COLOR=RED>The part code '" . $_GET['NewItem'] . "' does not exist in the database and therefore cannot be added to the order.</FONT><P>";
		      if ($debug==1){
		      		echo "<BR>$sql";
				exit;
		      }
	   }

	} /* end of if not already on the order */

} /* end of if its a new item */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<CENTER>Purchase Order: <FONT COLOR=BLUE SIZE=4><B>" . $_SESSION['PO']->OrderNo . " " . $_SESSION['PO']->SupplierName . " </B></FONT> - All amounts stated in " . $_SESSION['PO']->CurrCode . "<BR>";

echo "<CENTER><B>Order Summary</B>";
echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";

/*need to set up entry for item description where not a stock item and GL Codes */

if (count($_SESSION['PO']->LineItems)>0){

   echo "<TR><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Unit</TD><TD class='tableheader'>Delivery</TD><TD class='tableheader'>Price</TD><TD class='tableheader'>Total</TD></TR>";

   $_SESSION['PO']->total = 0;
   $k = 0;  //row colour counter
   foreach ($_SESSION['PO']->LineItems as $POLine) {

		if ($POLine->Deleted==False) {
		$LineTotal =	$POLine->Quantity * $POLine->Price;
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($POLine->Price,2);
		$DisplayQuantity = number_format($POLine->Quantity,2);

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		echo "<TD>$POLine->StockID</TD><TD>$POLine->ItemDescription</TD><TD ALIGN=RIGHT>$DisplayQuantity</TD><TD>$POLine->Units</TD><TD>$POLine->ReqDelDate</TD><TD ALIGN=RIGHT>$DisplayPrice</TD><TD ALIGN=RIGHT>$DisplayLineTotal</FONT></TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Edit=" . $POLine->LineNo . "'>Select</A></TD></TR>";
		$_SESSION['PO']->total = $_SESSION['PO']->total + $LineTotal;
		}
    }

    $DisplayTotal = number_format($_SESSION['PO']->total,2);
    echo "<TR><TD COLSPAN=6 ALIGN=RIGHT>TOTAL Excl Tax</TD><TD ALIGN=RIGHT><B>$DisplayTotal</B></TD></TR></TABLE>";

} /*Only display the order line items if there are any !! */

# has the user requested to modify an item
# or insert a new one and EditItem set to 1 above

If ($_GET['Edit']){

	echo "<input type='hidden' name='LineNo' value=" . $_GET['Edit'] .">";

	echo "<TABLE>";
	if ($_SESSION['PO']->LineItems[$_GET['Edit']]->StockID =="") { /*No stock item on this line */
	      echo "<TR><TD>Description:</TD><TD><textarea name='ItemDescription' cols=50 rows=2>" . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . "</textarea></TD></TR>";
	      if ($_SESSION['PO']->GLLink==1) {
		      echo "<TR><TD>GL Code:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='GLCode' value=" .$_SESSION['PO']->LineItems[$_GET['Edit']]->GLCode . "> <a target='_blank' href='$rootpath/GLCodesInquiry.php'>List GL Codes</a></TD></TR>";
	      } else {
		      echo "<input type='hidden' name='GLCode' value=''>";
	      }
	} else {
	      echo "<TR><TD>Stock Item Ordered:</TD><TD>" . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . "</TD></TR>";
	      echo "<INPUT TYPE=hidden name=ItemDescription value='" . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . "'>";
	      echo "<INPUT TYPE=hidden name=GLCode value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->GLCode . ">";

	}
	echo "<TR><TD>Order Quantity:</TD><TD><input type='Text' SIZE=7 MAXLENGTH=6 name='Qty' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->Quantity . "></TD></TR>";
	echo "<TR><TD>Price:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='Price' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->Price . "></TD><TD><INPUT TYPE=SUBMIT NAME='LookupPrice' Value='Lookup Price'></TD></TR>";
	echo "<TR><TD>Required Delivery Date:</TD><TD><input type='Text' SIZE=12 MAXLENGTH=11 name='ReqDelDate' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->ReqDelDate . "></TD></TR>";
if ($hide_incomplete_features == False)	{
	echo "<TR><TD>Shipment Ref: <FONT SIZE=1>(Leave blank if N/A)</FONT></TD><TD><input type='Text' SIZE=10 MAXLENGTH=9 name='ShiptRef' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->ShiptRef . "> <a target='_blank' href='$rootpath/ShiptsList.php?" . SID . "SupplierID=" . $_SESSION['PO']->SupplierID . "&SupplierName=" . $_SESSION['PO']->SupplierName . "'>Show Open Shipments</a></TD></TR>";
	echo "<TR><TD>Contract Ref: <FONT SIZE=1>(Leave blank if N/A)</FONT></TD><TD><input type='Text' SIZE=10 MAXLENGTH=9 name='JobRef' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->JobRef . "> <a target='_blank' href='$rootpath/ContractsList.php?" . SID . "'>Show Contracts</a></TD></TR>";
}
	echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME='UpdateLine' VALUE='Update Line'> <INPUT TYPE=SUBMIT NAME='Delete' VALUE='Delete'><BR>";
} elseif ($_SESSION['ExistingOrder']==0) { /* ITS A NEWY */
 /*show a form for putting in a new line item with or without a stock entry */

	echo "<input type='hidden' name='LineNo' value=" . ($_SESSION['PO']->LinesOnOrder + 1) .">";

	echo "<TABLE><TR><TD>Stock Code for Item Ordered: <FONT SIZE=1>(Leave blank if NOT a stock order)</TD><TD><input type='text' name='StockID' size=21 maxlength=20 value='" . $_POST['StockID'] . "'></TD></TR>";

	echo "<TR><TD>Ordered item Description:<BR><FONT SIZE=1>(If a stock code is entered above, its description will overide this entry)</FONT></TD><TD><textarea name='ItemDescription' cols=50 rows=2>" . $_POST['ItemDescription'] . "</textarea></TD></TR>";
	if ($_SESSION['PO']->GLLink==1) {
		echo "<TR><TD>GL Code: <FONT SIZE=1> (Only necessary if NOT a stock order)</FONT></TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='GLCode' value=" . $_POST['GLCode'] . "> <a target='_blank' href='$rootpath/GLCodesInquiry.php?" . SID . "'>List GL Codes</a></TD></TR>";
	}

	/*default the order quantity to 1 unit */
	if (!isset($_POST['Qty'])){
		$_POST['Qty'] = 1;
	}

	echo "<TR><TD>Order Quantity:</TD><TD><input type='Text' SIZE=7 MAXLENGTH=6 name='Qty' value=" . $_POST['Qty'] . "></TD></TR>";
	echo "<TR><TD>Price:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='Price' value=" . $_POST['Price'] . "><INPUT TYPE=SUBMIT NAME='LookupPrice' Value='Lookup Price'></TD></TR>";

	/*Default the required delivery date to tomorrow as a starting point */
	$_POST['ReqDelDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m"),Date("d")+1,Date("y")));

	echo "<TR><TD>Required Delivery Date:</TD><TD><input type='Text' SIZE=12 MAXLENGTH=11 name='ReqDelDate' value=" . $_POST['ReqDelDate'] . "></TD></TR>";

if ($hide_incomplete_features==False)	{
	echo "<TR><TD>Shipment Ref: <FONT SIZE=1>(Leave blank if N/A)</FONT></TD><TD><input type='Text' SIZE=10 MAXLENGTH=9 name='ShiptRef' value=" . $_POST['ShiptRef'] . " > <a target='_blank' href='$rootpath/ShiptsList.php?" . SID . "SupplierID=" . $_SESSION['PO']->SupplierID . "&SupplierName=" . $_SESSION['PO']->SupplierName . "'>Show Open Shipments</a></TD></TR>";
	echo "<TR><TD>Contract Ref: <FONT SIZE=1>(Leave blank if N/A)</FONT></TD><TD><input type='Text' SIZE=10 MAXLENGTH=9 name='JobRef' value=" . $_POST['JobRef'] . "> <a target='_blank' href='$rootpath/ContractsList.php?" . SID . "'>Show Contracts</a></TD></TR>";
}
	echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME='EnterLine' VALUE='Enter Line'><BR><BR>";

	echo "<INPUT TYPE=SUBMIT NAME='Commit' VALUE='Place Order'></CENTER>";

} elseif ($_SESSION['ExistingOrder']>0){

	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='Commit' VALUE='Update Order'></CENTER>";

	if ($_SESSION['AccessLevel']>60){ /*Allow link to receive PO */
	    echo "<BR><A HREF='$rootpath/GoodsReceived.php?" . SID . "PONumber=" . $_SESSION['ExistingOrder'] . "'>Receive Items On This Order</A>";
	}

}

echo "<BR><A HREF='$rootpath/PO_Header.php?" . SID . "'>Back To Purchase Order Header</A>";


echo "<HR>";

/* Now show the stock item selection search stuff below */


$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory WHERE StockType<>'L' AND StockType<>'D' ORDER BY CategoryDescription";
$result1 = DB_query($SQL,$db);

echo "<B>Search For Stock Items</B><TABLE><TR><TD><FONT SIZE=2>Select a stock category:</FONT><SELECT NAME='StockCat'>";

echo "<OPTION SELECTED VALUE='All'>All";
while ($myrow1 = DB_fetch_array($result1)) {
	if ($_POST['StockCat']==$myrow1["CategoryID"]){
		echo "<OPTION SELECTED VALUE=". $myrow1["CategoryID"] . ">" . $myrow1["CategoryDescription"];
	} else {
		echo "<OPTION VALUE=". $myrow1["CategoryID"] . ">" . $myrow1["CategoryDescription"];
	}
}

?>

</SELECT>
<TD><FONT SIZE=2>Enter text extract(s) in the <B>description</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php echo $_POST['Keywords']; ?>"></TD></TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B>OR </B></FONT><FONT SIZE=2>Enter extract of the <B>Stock Code</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php echo $_POST['StockCode']; ?>"></TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now">

<?php

$PartsDisplayed =0;

If ($SearchResult) {

	echo "<CENTER><TABLE CELLPADDING=1 COLSPAN=7 BORDER=1>";

	$tableheader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Units</TD></TR>";
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($SearchResult)) {

		$ImageSource = $rootpath . "/" . $part_pics_dir . "/" . $myrow["StockID"] . ".jpg";

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		printf("<td>%s</td><td>%s</td><td>%s</td><td><img src=%s></td><td><a href='%s/PO_Items.php?%sNewItem=%s'>Order some</a></td></tr>", $myrow["StockID"], $myrow["Description"], $myrow["Units"], $ImageSource, $rootpath, SID, $myrow["StockID"]);

		$PartsDisplayed++;
		if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){
			break;
		}
		$j++;
		If ($j == 20){
			$j=1;
			echo $tableheader;
		}
#end of page full new headings if
	}
#end of while loop
	echo "</TABLE>";
	if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){

	/*$Maximum_Number_Of_Parts_To_Show defined in config.php */

		echo "<BR><B>Only the first $Maximum_Number_Of_Parts_To_Show can be displayed. Please restrict your search to only the parts required</B>";
	}
}#end if SearchResults to show

echo "<HR>";
echo "<a target='_blank' href='$rootpath/Stocks.php?" .SID . "'>Add a New Stock Item</a>";
echo "</CENTER>";


echo "<form>";
include("includes/footer.inc");
?>
