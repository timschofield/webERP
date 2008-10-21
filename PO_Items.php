<?php

/* $Revision: 1.26 $ */


$PageSecurity = 4;

include('includes/DefinePOClass.php');
include('includes/SQL_CommonFunctions.inc');

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
$title = _('Purchase Order Items');
if (!isset($_SESSION['PO'])){
   header ('Location:' . $rootpath . '/PO_Header.php?' . SID);
   exit;
}
include('includes/header.inc');

$Maximum_Number_Of_Parts_To_Show = 50;

echo '<a href="$rootpath/PO_Header.php?' . SID . '">' ._('Back To Purchase Order Header') . '</a><br>';

if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

 /*First do some validation
	  Is the delivery information all entered*/
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['PO']->DelAdd1=='' or strlen($_SESSION['PO']->DelAdd1)<3){
	      prnMsg( _('The purchase order can not be committed to the database because there is no delivery steet address specified'),'error');
	      $InputError=1;
//	} elseif ($_SESSION['PO']->DelAdd2=='' or strlen($_SESSION['PO']->DelAdd2)<3){
//	      prnMsg( _('The purchase order can not be committed to the database because there is no suburb address specified'),'error');
//	      $InputError=1;
	} elseif ($_SESSION['PO']->Location=='' or ! isset($_SESSION['PO']->Location)){
	      prnMsg( _('The purchase order can not be committed to the database because there is no location specified to book any stock items into'),'error');
	      $InputError=1;
	} elseif ($_SESSION['PO']->LinesOnOrder <=0){
	     prnMsg( _('The purchase order can not be committed to the database because there are no lines entered on this order'),'error');
	     $InputError=1;
	}

	if ($InputError!=1){
		 $sql = 'BEGIN';
		 $result = DB_query($sql,$db);

		 if ($_SESSION['ExistingOrder']==0){ /*its a new order to be inserted */

		     /*Insert to purchase order header record */
		     $sql = 'INSERT INTO purchorders (supplierno,
		     					comments,
							orddate,
							rate,
							initiator,
							requisitionno,
							intostocklocation,
							deladd1,
							deladd2,
							deladd3,
							deladd4,
							deladd5,
							deladd6)
				VALUES(
				"' . $_SESSION['PO']->SupplierID . '",
				"' . $_SESSION['PO']->Comments . '",
				"' . Date("Y-m-d") . '",
				' . $_SESSION['PO']->ExRate . ',
				"' . $_SESSION['PO']->Initiator . '",
				"' . $_SESSION['PO']->RequisitionNo . '",
				"' . $_SESSION['PO']->Location . '",
				"' . $_SESSION['PO']->DelAdd1 . '",
				"' . $_SESSION['PO']->DelAdd2 . '",
				"' . $_SESSION['PO']->DelAdd3 . '",
				"' . $_SESSION['PO']->DelAdd4 . '",
				"' . $_SESSION['PO']->DelAdd5 . '",
				"' . $_SESSION['PO']->DelAdd6 . '"
				)';

			$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		     /*Get the auto increment value of the order number created from the SQL above */
		     $_SESSION['PO']->OrderNo =  GetNextTransNo(18, $db);

		     /*Insert the purchase order detail records */
		     foreach ($_SESSION['PO']->LineItems as $POLine) {
			 	if ($POLine->Deleted==false) {
					$sql = 'INSERT INTO purchorderdetails (
							orderno,
							itemcode,
							deliverydate,
							itemdescription,
							glcode,
							unitprice,
							quantityord,
							shiptref,
							jobref
							)
						VALUES (
							' . $_SESSION['PO']->OrderNo . ',
							"' . $POLine->StockID . '",
							"' . FormatDateForSQL($POLine->ReqDelDate) . '",
							"' . $POLine->ItemDescription . '",
							' . $POLine->GLCode . ',
							' . $POLine->Price . ',
							' . $POLine->Quantity . ',
							"' . $POLine->ShiptRef . '",
							"' . $POLine->JobRef . '"
						)';
					$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
					$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
					$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
		     } /* end of the loop round the detail line items on the order */
		     echo '<p>';
		     prnMsg(_('Purchase order') . ' ' . $_SESSION['PO']->OrderNo . ' ' . _('on') . ' ' . $_SESSION['PO']->SupplierName . ' ' . _('has been created'),'success');
		     echo '<br><a target="_blank" href="$rootpath/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $_SESSION['PO']->OrderNo . '">' . _('Print Order') . '</a>';
		 } else { /*its an existing order need to update the old order info */

		     /*Update the purchase order header with any changes */
			$sql = 'UPDATE purchorders SET
		     			supplierno = "' . $_SESSION['PO']->SupplierID . '" ,
					comments="' . $_SESSION['PO']->Comments . '",
					rate=' . $_SESSION['PO']->ExRate . ',
					initiator="' . $_SESSION['PO']->Initiator . '",
					requisitionno= "' . $_SESSION['PO']->RequisitionNo . '",
					intostocklocation="' . $_SESSION['PO']->Location . '",
					deladd1="' . $_SESSION['PO']->DelAdd1 . '",
					deladd2="' . $_SESSION['PO']->DelAdd2 . '",
					deladd3="' . $_SESSION['PO']->DelAdd3 . '",
					deladd4="' . $_SESSION['PO']->DelAdd4 . '",
					deladd5="' . $_SESSION['PO']->DelAdd5 . '",
					deladd6="' . $_SESSION['PO']->DelAdd6 . '",
					allowprint=' . $_SESSION['PO']->AllowPrintPO . '
		     		WHERE orderno = ' . $_SESSION['PO']->OrderNo;

			$ErrMsg =  _('The purchase order could not be updated because');
			$DbgMsg = _('The SQL statement used to update the purchase order header record, that failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

		     /*Now Update the purchase order detail records */
		     foreach ($_SESSION['PO']->LineItems as $POLine) {

				if ($POLine->Deleted==true) {
					if ($POLine->PODetailRec!='') {
						$sql='DELETE FROM purchorderdetails WHERE podetailitem="' . $POLine->PODetailRec . '"';
						$ErrMsg =  _('The purchase order could not be deleted because');
						$DbgMsg = _('The SQL statement used to delete the purchase order header record, that failed was');
						$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
					}
				} else if ($POLine->PODetailRec=='') {

					$sql = 'INSERT INTO purchorderdetails (
									orderno,
									itemcode,
									deliverydate,
									itemdescription,
									glcode,
									unitprice,
									quantityord,
									shiptref,
									jobref
									)
								VALUES ('
									. $_SESSION['PO']->OrderNo . ',
									"' . $POLine->StockID . '",
									"' . FormatDateForSQL($POLine->ReqDelDate) . '",
									"' . $POLine->ItemDescription . '",
									' . $POLine->GLCode . ',
									' . $POLine->Price . ',
									' . $POLine->Quantity . ',
									"' . $POLine->ShiptRef . '",
									"' . $POLine->JobRef . '"
								)';
				} else {
					if ($POLine->Quantity==$POLine->QtyReceived){
						$sql = 'UPDATE purchorderdetails SET
								itemcode="' . $POLine->StockID . '",
								deliverydate ="' . FormatDateForSQL($POLine->ReqDelDate) . '",
								itemdescription="' . $POLine->ItemDescription . '",
								glcode=' . $POLine->GLCode . ',
								unitprice=' . $POLine->Price . ',
								quantityord=' . $POLine->Quantity . ',
								shiptref="' . $POLine->ShiptRef . '",
								jobref="' . $POLine->JobRef . '",
								completed=1
							WHERE podetailitem=' . $POLine->PODetailRec;
					} else {
						$sql = 'UPDATE purchorderdetails SET
								itemcode="' . $POLine->StockID . '",
								deliverydate ="' . FormatDateForSQL($POLine->ReqDelDate) . '",
								itemdescription="' . $POLine->ItemDescription . '",
								glcode=' . $POLine->GLCode . ',
								unitprice=' . $POLine->Price . ',
								quantityord=' . $POLine->Quantity . ',
								shiptref="' . $POLine->ShiptRef . '",
								jobref="' . $POLine->JobRef . '"
								WHERE podetailitem=' . $POLine->PODetailRec;
					}
				}

				$ErrMsg = _('One of the purchase order detail records could not be updated because');
				$DbgMsg = _('The SQL statement used to update the purchase order detail record that failed was');
				$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		     } /* end of the loop round the detail line items on the order */
		     echo '<br><br>';
		     prnMsg(_('Purchase order') . ' ' . $_SESSION['PO']->OrderNo . ' ' . _('has been updated'),'success');
		     if ($_SESSION['PO']->AllowPrintPO==1){
			     echo '<br><a target="_blank" href="$rootpath/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $_SESSION['PO']->OrderNo . '">' . _('Re-Print Order') . '</a>';
		     }
		 } /*end of if its a new order or an existing one */

		 $sql = 'COMMIT';
		 $Result = DB_query($sql,$db);

		 unset($_SESSION['PO']); /*Clear the PO data to allow a newy to be input*/
		 echo '<br><br><a href="$rootpath/PO_Header.php?' . SID . '&NewOrder=Yes">' . _('Enter A New Purchase Order') . '</a>';
		 echo '<br><a href="$rootpath/PO_SelectOSPurchOrder.php?' . SID . '">' . _('Select An Outstanding Purchase Order') . '</a>';
		 exit;
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the PO object to the database  - user hit the place PO*/



if (isset($_POST['Search'])){  /*ie seach for stock items */

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces

		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		if ($_POST['StockCat']=='All'){
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				and stockmaster.description ' . LIKE . ' "$SearchString"
				ORDER BY stockmaster.stockid';
		} else {
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				and stockmaster.description ' . LIKE . ' "$SearchString"
				and stockmaster.categoryid="' . $_POST['StockCat'] . '"
				ORDER BY stockmaster.stockid';
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				and stockmaster.stockid ' . LIKE . ' "' . $_POST['StockCode'] . '"
				ORDER BY stockmaster.stockid';
		} else {
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				and stockmaster.stockid ' . LIKE . ' "' . $_POST['StockCode'] . '"
				and stockmaster.categoryid="' . $_POST['StockCat'] . '"
				ORDER BY stockmaster.stockid';
		}

	} else {
		if ($_POST['StockCat']=='All'){
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				ORDER BY stockmaster.stockid';
		} else {
			$sql = 'SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!="D"
				and stockmaster.mbflag!="A"
				and stockmaster.mbflag!="K"
				and stockmaster.discontinued!=1
				and stockmaster.categoryid="' . $_POST['StockCat'] . '"
				ORDER BY stockmaster.stockid';
		}
	}

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($SearchResult)==0 && $debug==1){
		prnMsg( _('There are no products to display matching the criteria provided'),'warn');
	}
	if (DB_num_rows($SearchResult)==1){

		$myrow=DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $myrow['stockid'];
		DB_data_seek($SearchResult,0);
	}

} //end of if search

/* Always do the stuff below if not looking for a supplierid */

if(isset($_POST['Delete'])){
	if($_SESSION['PO']->Some_Already_Received($_POST['LineNo'])==0){
		$_SESSION['PO']->remove_from_order($_POST['LineNo']);
		include ('includes/PO_UnsetFormVbls.php');
	} else {
		prnMsg( _('This item cannot be deleted because some of it has already been received'),'warn');
	}
}

if (isset($_POST['LookupPrice']) and $_POST['StockID']!=''){

	$sql = 'SELECT purchdata.price,
			purchdata.conversionfactor,
			purchdata.supplierdescription
		FROM purchdata
		WHERE  purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
		and purchdata.stockid = "'. strtoupper($_POST['StockID']) . '"';

	$ErrMsg = _('The supplier pricing details for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
	$LookupResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($LookupResult)==1){
		$myrow = DB_fetch_array($LookupResult);
		$_POST['Price'] = $myrow['price']/$myrow['conversionfactor'];
	} else {
		prnMsg(_('Sorry') . ' ... ' . _('there is no purchasing data set up for this supplier') . '  - ' . $_SESSION['PO']->SupplierID . ' ' . _('and item') . ' ' . strtoupper($_POST['StockID']),'warn');
	}
}

if(isset($_POST['UpdateLine'])){
	$AllowUpdate=true; /*Start assuming the best ... now look for the worst*/

	if ($_POST['Qty']==0 or $_POST['Price'] < 0){
		$AllowUpdate = false;
		prnMsg( _('The Update Could Not Be Processed') . '<br>' . _('You are attempting to set the quantity ordered to zero, or the price is set to an amount less than 0'),'error');
	}

	if ($_SESSION['PO']->LineItems[$_POST['LineNo']]->QtyInv > $_POST['Qty'] or $_SESSION['PO']->LineItems[$_POST['LineNo']]->QtyReceived > $_POST['Qty']){
		$AllowUpdate = false;
		prnMsg( _('The Update Could Not Be Processed') . '<br>' . _('You are attempting to make the quantity ordered a quantity less than has already been invoiced or received this is of course prohibited') . '. ' . _('The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item'),'error');
	}

	if ($_SESSION['PO']->GLLink==1) {
	/*Check for existance of GL Code selected */
		$sql = 'SELECT accountname
				FROM chartmaster
				WHERE accountcode =' .  $_POST['GLCode'];
		$ErrMsg = _('The account name for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the account details but failed was');
		$GLActResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_error_no($db)!=0 or DB_num_rows($GLActResult)==0){
			 $AllowUpdate = false;
			 prnMsg( _('The Update Could Not Be Processed') . '<br>' . _('The GL account code selected does not exist in the database see the listing of GL Account Codes to ensure a valid account is selected'),'error');
		} else {
			$GLActRow = DB_fetch_row($GLActResult);
			$GLAccountName = $GLActRow[0];
		}
	}

	include ('PO_Chk_ShiptRef_JobRef.php');

	if (!isset($_POST['JobRef'])) {
		$_POST['JobRef']='';
	}

	if ($AllowUpdate == true) {

	      $_SESSION['PO']->update_order_item(
	      				$_POST['LineNo'],
					$_POST['Qty'],
					$_POST['Price'],
					$_POST['ItemDescription'],
					$_POST['GLCode'],
					$GLAccountName,
					$_POST['ReqDelDate'],
					$_POST['ShiptRef'],
					$_POST['JobRef'] );

	      include ('includes/PO_UnsetFormVbls.php');

	}
}

if (isset($_POST['EnterLine'])){ /*Inputs from the form directly without selecting a stock item from the search */

     $AllowUpdate = true; /*always assume the best */

     if (!is_numeric($_POST['Qty'])){
	   $AllowUpdate = false;
	   prnMsg( _('Cannot Enter this order line') . '<br>' . _('The quantity of the order item must be numeric'),'error');
     }
     if ($_POST['Qty']<0){
	   $AllowUpdate = false;
	   prnMsg( _('Cannot Enter this order line') . '<br>' . _('The quantity of the ordered item entered must be a positive amount'),'error');
     }
     if (!is_numeric($_POST['Price'])){
	   $AllowUpdate = false;
	   prnMsg( _('Cannot Enter this order line') . '<br>' . _('The price entered must be numeric'),'error');
     }
     if (!is_date($_POST['ReqDelDate'])){
	 $AllowUpdate = false;
	 prnMsg( '<b>'. _('Cannot Enter this order line') . '</b><br>' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
     }

     include ('PO_Chk_ShiptRef_JobRef.php');


     if ($_POST['StockID']!='' and $AllowUpdate==true){ /* A stock item has been entered - skip if inputs crook*/

		$_POST['StockID'] = strtoupper($_POST['StockID']);

		if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
			if (count($_SESSION['PO']->LineItems)>0){
				foreach ($_SESSION['PO']->LineItems AS $OrderItem) { /*now test for the worst */
					/* do a loop round the items on the order to see that the item
					is not already on this order */

					if (($OrderItem->StockID == $_POST['StockID']) and ($OrderItem->Deleted==false)) {
						$AllowUpdate = false;
						prnMsg(_('The item') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('is already on this order') . ' - ' . _('the system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity by selecting it from the order summary'),'warn');
					}
				} /* end of the foreach loop to look for pre-existing items of the same code */
			}
		} /*Only check for multiples if not allowed */

		if ($AllowUpdate == true){ /*Dont bother with this lot if already discovered input is stuffed */

			if ($_SESSION['PO']->GLLink==1){
				$sql = 'SELECT stockmaster.description,
			   			stockmaster.units,
						stockmaster.mbflag,
						stockcategory.stockact,
						chartmaster.accountname,
						stockmaster.decimalplaces
					FROM stockcategory,
						chartmaster,
						stockmaster
					WHERE chartmaster.accountcode = stockcategory.stockact
					and stockcategory.categoryid = stockmaster.categoryid
					and stockmaster.stockid = "'. strtoupper($_POST['StockID']) . '"';
			} else {
				$sql = 'SELECT stockmaster.description,
			   			stockmaster.units,
						stockmaster.mbflag,
						stockmaster.decimalplaces
					FROM stockmaster
					WHERE stockmaster.stockid = "'. strtoupper($_POST['StockID']) . '"';
			}

		$ErrMsg =  _('The stock details for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be retrieved because');
		$DbgMsg =  _('The SQL used to retrieve the details of the item, but failed was');
		$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result1)==0){ // the stock item does not exist in the DB
			$AllowUpdate = false;
		}

		if ($myrow = DB_fetch_array($result1)
			and $AllowUpdate == true
			and $myrow['mbflag']!='A'
			and $myrow['mbflag']!='K'
			and $myrow['mbflag']!='D'){

				if ($_SESSION['PO']->GLLink==1){

					 $_SESSION['PO']->add_to_order ($_POST['LineNo'],
					 				strtoupper($_POST['StockID']),
					 				0, /*Serialised */
									0, /*Controlled */
									$_POST['Qty'],
									$myrow['description'],
									$_POST['Price'],
									$myrow['units'],
									$myrow['stockact'],
									$_POST['ReqDelDate'],
									$_POST['ShiptRef'],
									$_POST['JobRef'],
									0,
									0,
									$myrow['accountname'],
									$myrow['decimalplaces']);


				} else {
					 $_SESSION['PO']->add_to_order ($_POST['LineNo'],
					 				strtoupper($_POST['StockID']),
									0, /*Serialised */
									0, /*Controlled */
									$_POST['Qty'],
									$myrow['description'],
									$_POST['Price'],
									$myrow['units'],
									0,
									$_POST['ReqDelDate'],
									$_POST['ShiptRef'],
									$_POST['JobRef'],
									0,
									0,
									'',
									$myrow['decimalplaces']);
			    	}
			    	include ('includes/PO_UnsetFormVbls.php');
		   } else {
			    prnMsg( _('Cannot Enter this order line') . ':<br>' . _('Either the part code') . " '" . strtoupper($_POST['StockID']) . "' " . _('does not exist in the database or the part is an assembly or kit or dummy part and therefore cannot be purchased'),'warn');
			     if ($debug==1){
				    echo '<br>$sql';
			     }
		   }

		} /* end of if not already on the order and allow input was true*/


	} /*end if its a stock item */
	else { /*Then its not a stock item */

	   /*need to check GL Code is valid if GLLink is active */
	   if ($_SESSION['PO']->GLLink==1){

		$sql = 'SELECT accountname
				FROM chartmaster
				WHERE accountcode =' . (int) $_POST['GLCode'];
		$ErrMsg =  _('The account details for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
		$DbgMsg =  _('The SQL used to retrieve the details of the account, but failed was');
		$GLValidResult = DB_query($sql,$db,$ErrMsg,$DbgMsg,false,false);
		if (DB_error_no($db) !=0) {
			$AllowUpdate = false;
			prnMsg( _('The validation process for the GL Code entered could not be executed because') . ' ' . DB_error_msg($db), 'error');
			if ($debug==1){
			     prnMsg (_('The SQL used to validate the code entered was') . ' ' . $sql,'error');
		      }
		      include('includes/footer.inc');
		      exit;
	      }
	      if (DB_num_rows($GLValidResult) == 0) { /*The GLCode entered does not exist */
		     $AllowUpdate = false;
		      prnMsg( _('Cannot enter this order line') . ':<br>' . _('The general ledger code') . ' - ' . $_POST['GLCode'] . ' ' . _('is not a general ledger code that is defined in the chart of accounts') . ' . ' . _('Please use a code that is already defined') . '. ' . _('See the Chart list from the link below'),'error');
	      } else {
		      $myrow = DB_fetch_row($GLValidResult);
		      $GLAccountName = $myrow[0];
	      }
	   } /* dont bother checking the GL Code if there is no GL code to check ie not linked to GL */
	     else {
		$_POST['GLCode']=0;
	   }
	   if (strlen($_POST['ItemDescription'])<=3){
		   $AllowUpdate = false;
		   prnMsg(_('Cannot enter this order line') . ':<br>' . _('The description of the item being purchase is required where a non-stock item is being ordered'),'warn');
	    }

	    if ($AllowUpdate == true){

		   $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1,
		   				'',
						0, /*Serialised */
						0, /*Controlled */
						$_POST['Qty'],
						$_POST['ItemDescription'],
						$_POST['Price'],
						_('each'),
						$_POST['GLCode'],
						$_POST['ReqDelDate'],
						$_POST['ShiptRef'],
						$_POST['JobRef'],
						0,
						0,
						$GLAccountName);
		   include ('includes/PO_UnsetFormVbls.php');
	    }

     }

} /*end if Enter line button was hit */


if (isset($_GET['NewItem'])){ /* NewItem is set from the part selection list as the part code selected */
/* take the form entries and enter the data from the form into the PurchOrder class variable */
	$AlreadyOnThisOrder =0;

	if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
		if (count($_SESSION['PO']->LineItems)!=0){

			foreach ($_SESSION['PO']->LineItems AS $OrderItem) {

		/* do a loop round the items on the order to see that the item
		is not already on this order */
			    if (($OrderItem->StockID == $_GET['NewItem'])  and ($OrderItem->Deleted==false)) {
				  $AlreadyOnThisOrder = 1;
				  prnMsg( _('The item') . ' ' . $_GET['NewItem'] . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'),'error');
			    }
			} /* end of the foreach loop to look for preexisting items of the same code */
		}
	}
	if ($AlreadyOnThisOrder!=1){

	    $sql = 'SELECT stockmaster.description,
	    			stockmaster.stockid,
				stockmaster.units,
				stockmaster.decimalplaces,
				stockcategory.stockact,
				chartmaster.accountname,
				purchdata.price,
				purchdata.conversionfactor,
				purchdata.supplierdescription
			FROM stockcategory,
				chartmaster,
				stockmaster LEFT JOIN purchdata
				ON stockmaster.stockid = purchdata.stockid
				and purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
			WHERE chartmaster.accountcode = stockcategory.stockact
			and stockcategory.categoryid = stockmaster.categoryid
			and stockmaster.stockid = "'. $_GET['NewItem'] . '"';

	    $ErrMsg = _('The supplier pricing details for') . ' ' . $_GET['NewItem'] . ' ' . _('could not be retrieved because');
	    $DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
	    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	   if ($myrow = DB_fetch_array($result1)){
		      if (is_numeric($myrow['price'])){

			     $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1,
			     				$_GET['NewItem'],
							0, /*Serialised */
							0, /*Controlled */
							1, /* Qty */
							$myrow['description'],
							$myrow['price'],
							$myrow['units'],
							$myrow['stockact'],
							Date($_SESSION['DefaultDateFormat']),
							0,
							0,
							0,
							0,
							$myrow['accountname'],
							$myrow['decimalplaces']);
		      } else { /*There was no supplier purchasing data for the item selected so enter a purchase order line with zero price */

			     $_SESSION['PO']->add_to_order ($_SESSION['PO']->LinesOnOrder+1,
			     				$_GET['NewItem'],
							0, /*Serialised */
							0, /*Controlled */
							1, /* Qty */
							$myrow['description'],
							0,
							$myrow['units'],
							$myrow['stockact'],
							Date($_SESSION['DefaultDateFormat']),
							0,
							0,
							0,
							0,
							$myrow['accountname'],
							$myrow['decimalplaces']);
		      }
		      /*Make sure the line is also available for editing by default without additional clicks */
		      $_GET['Edit'] = $_SESSION['PO']->LinesOnOrder; /* this is a bit confusing but it was incremented by the add_to_order function */
	   } else {
		      prnMsg (_('The item code') . ' ' . $_GET['NewItem'] . ' ' . _('does not exist in the database and therefore cannot be added to the order'),'error');
		      if ($debug==1){
		      		echo "<br>$sql";

		      }
		      include('includes/footer.inc');
		      exit;
	   }

	} /* end of if not already on the order */

} /* end of if its a new item */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

echo '<center>' . _('Purchase Order') . ': <font color=blue size=4><b>' . $_SESSION['PO']->OrderNo . ' ' . $_SESSION['PO']->SupplierName . ' </b></font> - ' . _('All amounts stated in') . ' ' . $_SESSION['PO']->CurrCode . '<br>';

echo '<center><b>' . _('Order Summary') . '</b>';
echo '<table cellpadding=2 colspan=7 border=1>';

/*need to set up entry for item description where not a stock item and GL Codes */

if (count($_SESSION['PO']->LineItems)>0){

   echo '<tr>
   		<th>' . _('Item Code') . '</th>
		<th>' . _('Item Description') . '</th>
		<th>' . _('Quantity') . '</th>
		<th>' . _('Unit') . '</th>
		<th>' . _('Delivery') . '</th>
		<th>' . _('Price') . '</th>
		<th>' . _('Total') . '</th>
	</tr>';

   $_SESSION['PO']->total = 0;
   $k = 0;  //row colour counter
   foreach ($_SESSION['PO']->LineItems as $POLine) {

		if ($POLine->Deleted==false) {
		$LineTotal =	$POLine->Quantity * $POLine->Price;
		$DisplayLineTotal = number_format($LineTotal,2);
		$DisplayPrice = number_format($POLine->Price,2);
		$DisplayQuantity = number_format($POLine->Quantity,$POLine->DecimalPlaces);

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo "<td>$POLine->StockID</td><td>$POLine->ItemDescription</td><TD ALIGN=RIGHT>$DisplayQuantity</td><td>$POLine->Units</td><td>$POLine->ReqDelDate</td><TD ALIGN=RIGHT>$DisplayPrice</td><TD ALIGN=RIGHT>$DisplayLineTotal</FONT></td><td><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Edit=" . $POLine->LineNo . "'>" . _('Select') . "</A></td></tr>";
		$_SESSION['PO']->total = $_SESSION['PO']->total + $LineTotal;
		}
    }

    $DisplayTotal = number_format($_SESSION['PO']->total,2);
    echo '<tr><td colspan=6 align=rifgt>' . _('TOTAL Excl Tax') . '</td><td align=right><b>$DisplayTotal</b></td></tr></table>';

	if (!isset($_POST['StockID']) and !isset($_GET['Edit'])) {
 	/*show a form for putting in a new line item with or without a stock entry */

		if (!isset($_POST['StockID'])) {
			$_POST['StockID']='';
		}
		if (!isset($_POST['ItemDescription'])) {
			$_POST['ItemDescription']='';
		}
		if (!isset($_POST['GLCode'])) {
			$_POST['GLCode']='';
		}
		echo '<input type="hidden" name="LineNo" value=' . ($_SESSION['PO']->LinesOnOrder + 1) .'>';

		echo '<table><tr><td>' . _('Stock Code for Item Ordered') . ': <font size=1>(' . _('Leave blank if NOT a stock order') . ')</td>
			<td><input type="text" name="StockID" size=21 maxlength=20 value="' . strtoupper($_POST['StockID']) . '"></td></tr>';

		echo '<tr><td>' . _('Ordered item Description') . ':<br><font size=1>(' . _('If a stock code is entered above, its description will overide this entry') . ')</font></td>
			<td><textarea name="ItemDescription" cols=50 rows=2>' . $_POST['ItemDescription'] . '</textarea></td></tr>';
		if ($_SESSION['PO']->GLLink==1) {
			echo '<tr><td>' . _('GL Code') . ': <font size=1>(' . _('Only necessary if NOT a stock order') . ')</font></td>
				<td><input type="text" size=15 maxlength=14 name="GLCode" value=' . $_POST['GLCode'] . '> <a target="_blank" href="$rootpath/GLCodesInquiry.php?' . SID . '">' . _('List GL Codes') . '</a></td></tr>';
		}

		/*default the order quantity to 1 unit */
		if (!isset($_POST['Qty'])){
			$_POST['Qty'] = 1;
		}

		if (!isset($_POST['Price'])) {
			$_POST['Price'] = 0;
		}

		echo '<tr><td>' . _('Order Quantity') . ':</td>
			<td><input type="text" size=7 maxlength=6 name="Qty" value=' . $_POST['Qty'] . '></td></tr>';
		echo '<tr><td>' . _('Price') . ':</td>
			<td><input type="text" size=15 maxlength=14 name="Price" value=' . $_POST['Price'] . '><input type=submit name="LookupPrice" Value="' . _('Lookup Price') . '"></td></tr>';

		/*Default the required delivery date to tomorrow as a starting point */
		$_POST['ReqDelDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')));

		echo '<tr><td>' . _('Required Delivery Date') . ':</td>
			<td><input type="text" size=12 maxlength=11 name="ReqDelDate" value=' . $_POST['ReqDelDate'] . '></td></tr>';

		if (!isset($_POST['ShiptRef'])) {
			$_POST['ShiptRef']='';
		}
		echo '<tr><td>' . _('Shipment Ref') . ': <font size=1>' . _('(Leave blank if N/A)') . '</font></td>
			<td><input type="text" size=10 maxlength=9 name="ShiptRef" value=' . $_POST['ShiptRef'] . ' > <a target="_blank" href="$rootpath/ShiptsList.php?' . SID . '&SupplierID=' . $_SESSION['PO']->SupplierID . '&SupplierName=' . $_SESSION['PO']->SupplierName . '">' . _('Show Open Shipments') . '</a></td></tr>';

		echo '</table><center><input type=submit name="EnterLine" value="' . _('Enter Line') . '"><br><br>';

		echo '<input type=submit name="Commit" value="' . _('Place Order') . '"></center>';

	}
} /*Only display the order line items if there are any !! */

# has the user requested to modify an item
# or insert a new one and EditItem set to 1 above

if (isset($_GET['Edit'])){

	echo '<input type="hidden" name="LineNo" value=' . $_GET['Edit'] .'>';

	echo '<table>';
	if ($_SESSION['PO']->LineItems[$_GET['Edit']]->StockID =='') { /*No stock item on this line */
	      echo '<tr><td>' . _('Description') . ':</td><td><textarea name="ItemDescription" cols=50 rows=2>' . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . '</textarea></td></tr>';
	      if ($_SESSION['PO']->GLLink==1) {
		      echo '<tr><td>' . _('GL Code') . ':</td><td><input type="text" size=15 maxlength=14 name="GLCode" value=' .$_SESSION['PO']->LineItems[$_GET['Edit']]->GLCode . '> <a target="_blank" href="$rootpath/GLCodesInquiry.php">' . _('List GL Codes') . '</a></td></tr>';
	      } else {
		      echo '<input type="hidden" name="GLCode" value="0">';
	      }
	} else {
	      echo '<tr><td>' . _('Stock Item Ordered') . ':</td><td>' . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . '</td></tr>';
	      echo '<input type=hidden name=ItemDescription value="' . $_SESSION['PO']->LineItems[$_GET['Edit']]->ItemDescription . '">';
	      echo '<input type=hidden name=GLCode value=' . $_SESSION['PO']->LineItems[$_GET['Edit']]->GLCode . '>';

	}
	echo '<tr><td>' . _('Order Quantity') . ':</td>
		<td><input type="text" size=7 maxlength=6 name="Qty" value=' . $_SESSION['PO']->LineItems[$_GET['Edit']]->Quantity . '></td></tr>';
	echo '<tr><td>' . _('Price') . ':</td>
		<td><input type="text" size=15 maxlength=14 name="Price" value=' . $_SESSION['PO']->LineItems[$_GET['Edit']]->Price . '></td>
		<td><input type=submit name="LookupPrice" value="' . _('Lookup Price') . '"></td></tr>';
	echo '<tr><td>' . _('Required Delivery Date') . ':</td>
		<td><input type="text" size=12 maxlength=11 name="ReqDelDate" value=' . $_SESSION['PO']->LineItems[$_GET['Edit']]->ReqDelDate . '></td></tr>';
	echo '<tr><td>' . _('Shipment Ref') . ': <FONT SIZE=1>(' . _('Leave blank if N/A') . ')</FONT></td>
		<td><input type="text" size=10 maxlength=9 name="ShiptRef" value=' . $_SESSION['PO']->LineItems[$_GET['Edit']]->ShiptRef . '><a target="_blank" href="$rootpath/ShiptsList.php?' . SID . 'SupplierID=' . $_SESSION['PO']->SupplierID . '&SupplierName=' . $_SESSION['PO']->SupplierName . '">' . _('Show Open Shipments') . '</a></td></tr>';
	/*
	echo '<tr><td>' . _('Contract Ref') . ': <FONT SIZE=1>(' . _('Leave blank if N/A') . ")</FONT></td>
		<td><input type='text' SIZE=10 MAXLENGTH=9 name='JobRef' value=" . $_SESSION['PO']->LineItems[$_GET['Edit']]->JobRef . "> <a target='_blank' href='$rootpath/ContractsList.php?" . SID . "'>" . _('Show Contracts') . '</a></td></tr>';
	*/

	echo '</table><center><input type=submit name="UpdateLine" value="' . _('Update Line') . '"> <input type=submit name="Delete" VALUE="' . _('Delete') . '"><br>';
} elseif ($_SESSION['ExistingOrder']==0) { /* ITS A NEWY */
 /*show a form for putting in a new line item with or without a stock entry */
	if (!isset($_POST['StockID'])) {
		$_POST['StockID']='';
	}
	if (!isset($_POST['ItemDescription'])) {
		$_POST['ItemDescription']='';
	}
	if (!isset($_POST['GLCode'])) {
		$_POST['GLCode']='';
	}
	echo '<input type="hidden" name="LineNo" value=' . ($_SESSION['PO']->LinesOnOrder + 1) .'>';

	echo '<table><tr><td>' . _('Stock Code for Item Ordered') . ': <font size=1>(' . _('Leave blank if NOT a stock order') . ')</td>
			<td><input type="text" name="StockID" size=21 maxlength=20 value="' . strtoupper($_POST['StockID']) . '"></td></tr>';

	echo '<tr><td>' . _('Ordered item Description') . ':<br><font size=1>(' . _('If a stock code is entered above, its description will overide this entry') . ')</font></td>
		<td><textarea name="ItemDescription" cols=50 rows=2>' . $_POST['ItemDescription'] . '</textarea></td></tr>';
	if ($_SESSION['PO']->GLLink==1) {
		echo '<tr><td>' . _('GL Code') . ': <font size=1>(' . _('Only necessary if NOT a stock order') . ')</font></td>
			<td><input type="text" size=15 maxlength=14 name="GLCode" value=' . $_POST['GLCode'] . '> <a target="_blank" href="$rootpath/GLCodesInquiry.php?' . SID . '">' . _('List GL Codes') . '</a></td></tr>';
	}

	/*default the order quantity to 1 unit */
	if (!isset($_POST['Qty'])){
		$_POST['Qty'] = 1;
	}

	if (!isset($_POST['Price'])) {
		$_POST['Price'] = 0;
	}

	echo '<tr><td>' . _('Order Quantity') . ':</td>
		<td><input type="text" size=7 maxlength=6 name="Qty" value=' . $_POST['Qty'] . '></td></tr>';
	echo '<tr><td>' . _('Price') . ':</td>
		<td><input type="text" size=15 maxlength=14 name="Price" value=' . $_POST['Price'] .
			'><input type=submit name="LookupPrice" value="' . _('Lookup Price') . '"></td></tr>';

	/*Default the required delivery date to tomorrow as a starting point */
	$_POST['ReqDelDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')+1,Date('y')));

	echo '<tr><td>' . _('Required Delivery Date') . ':</td>
		<td><input type="text" size=12 maxlength=11 name="ReqDelDate" value=' . $_POST['ReqDelDate'] . '></td></tr>';

	if (!isset($_POST['ShiptRef'])) {
		$_POST['ShiptRef']='';
	}
	echo '<tr><td>' . _('Shipment Ref') . ': <FONT SIZE=1>' . _('(Leave blank if N/A)') . '</font></td>
		<td><input type="text" size=10 maxlength=9 name="ShiptRef" value=' . $_POST['ShiptRef'] . ' > <a target="_blank" href="$rootpath/ShiptsList.php?' . SID . '&SupplierID=' . $_SESSION['PO']->SupplierID . '&SupplierName=' . $_SESSION['PO']->SupplierName . '">' . _('Show Open Shipments') . '</a></td></tr>';
/*	echo '<tr><td>' . _('Contract Ref') . ': <FONT SIZE=1>' . _('(Leave blank if N/A)') . "</FONT></td>
		<td><input type='text' SIZE=10 MAXLENGTH=9 name='JobRef' value=" . $_POST['JobRef'] . "> <a target='_blank' href='$rootpath/ContractsList.php?" . SID . "'>" . _('Show Contracts') . '</a></td></tr>';
*/
	echo '</table><center><input type=submit name="EnterLine" value="' . _('Enter Line') . '"><br><br>';

	echo '<input type=submit name="Commit" value="' . _('Place Order') . '"></center>';

} elseif ($_SESSION['ExistingOrder']>0){

	echo '<br><br><input type=submit name="Commit" value="' . _('Update Order') . '"></center>';

	if ($_SESSION['AccessLevel']>60){ /*Allow link to receive PO */
	    echo '<br><a href="$rootpath/GoodsReceived.php?' . SID . '&PONumber=' . $_SESSION['ExistingOrder'] . '">' . _('Receive Items On This Order') . '</a>';
	}
}

echo '<br><a href="$rootpath/PO_Header.php?' . SID . '">' ._('Back To Purchase Order Header') . '</a>';

echo '<hr>';

/* Now show the stock item selection search stuff below */


$sql='SELECT categoryid,
		categorydescription
	FROM stockcategory
	WHERE stocktype<>"L"
	AND stocktype<>"D"
	ORDER BY categorydescription';
$ErrMsg = _('The supplier category details could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the category details but failed was');
$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<b>' . _('Search For Stock Items') . '</b>
	<table><tr>
		<td><font size=2>' . _('Select a stock category') . ':</font><select name="StockCat">';

echo '<option selected value="All">' . _('All');
while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $_POST['StockCat']==$myrow1['categoryid']){
		echo '<option selected value='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	} else {
		echo '<option value='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	}
}

if (!isset($_POST['Keywords'])) {
	$_POST['Keywords']='';
}

if (!isset($_POST['StockCode'])) {
	$_POST['StockCode']='';
}

echo '</select>
	<td><font size=2>' . _('Enter text extracts in the description') . ':</font></td>
	<td><input type="text" name="Keywords" size=20 maxlength=25 value="' . $_POST['Keywords'] . '"></td></tr>
	<tr><td></td>
	<td><font size 3><b>' . _('or') . ' </b></font><font size=2>' . _('Enter extract of the Stock Code') . ':</font></td>
	<td><input type="text" name="StockCode" size=15 maxlength=18 value="' . $_POST['StockCode'] . '"></td>
	</tr>
	</table>
	<center><input type=submit name="Search" VALUE="' . _('Search Now') . '">';


$PartsDisplayed =0;

if (isset($SearchResult)) {

	echo "<CENTER><TABLE CELLPADDING=1 COLSPAN=7 BorDER=1>";

	$tableheader = '<tr>
			<th>' . _('Code')  . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Units') . '</th>
			</tr>';
	echo $tableheader;

	$j = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($SearchResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$filename = $myrow['stockid'] . '.jpg';
		if (file_exists( $_SESSION['part_pics_dir'] . '/' . $filename) ) {

			$ImageSource = '<img src="'.$rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';

		} else {
			$ImageSource = '<i>'._('No Image').'</i>';
		}

		printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=CENTER>%s</td>
			<td><a href="%s/PO_Items.php?%s&NewItem=%s">' . _('Order some') . '</a></td>
			</tr>',
			$myrow['stockid'],
			$myrow['description'],
			$myrow['units'],
			$ImageSource,
			$rootpath,
			SID,
			$myrow['stockid']);


		$PartsDisplayed++;
		if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){
			break;
		}
#end of page full new headings if
	}
#end of while loop
	echo '</table>';
	if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){

	/*$Maximum_Number_Of_Parts_To_Show defined in config.php */

		prnMsg( _('Only the first') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . _('can be displayed') . '. ' . _('Please restrict your search to only the parts required'),'info');
	}
}#end if SearchResults to show

echo '<hr>';
echo '<a target="_blank" href="$rootpath/Stocks.php?' . SID . '">' . _('Add a New Stock Item') . '</a>';
echo '</center>';

echo '</form>';
include('includes/footer.inc');
?>