<?php

/* $Revision: 1.39 $ */

$PageSecurity = 4;

include('includes/DefinePOClass.php');
include('includes/SQL_CommonFunctions.inc');

/* Session started in header.inc for password checking 
 * and authorisation level check 
 */
include('includes/session.inc');
$title = _('Purchase Order Items');

$identifier=$_GET['identifier'];

/* If a purchase order header doesn't exist, then go to
 * PO_Header.php to create one
 */

if (!isset($_SESSION['PO'.$identifier])){
	header('Location:' . $rootpath . '/PO_Header.php?' . SID);
	exit;
} //end if (!isset($_SESSION['PO'.$identifier]))

include('includes/header.inc');

$Maximum_Number_Of_Parts_To_Show=50;

echo '<a href="'.$rootpath.'/PO_Header.php?' . SID . "identifier=".$identifier. '">' ._('Back To Purchase Order Header') . '</a><br>';

// add new request here 08-09-26
if (isset($_POST['StockID2']) && $_GET['Edit']=='') {
/* If a stock item is selected and a purchdata record
 * exists for it then find that record.
 */
	$sql = "SELECT
			stockmaster.description,
			purchdata.suppliers_partno,
			stockmaster.pkg_type,
			stockmaster.units,
			stockmaster.netweight,		
			stockmaster.kgs,
			stockmaster.volume
			FROM purchdata INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			WHERE purchdata.stockid='" . $_POST['StockID2'] . "' AND
			purchdata.supplierno='".$_SESSION['PO'.$identifier]->SupplierID."'";

	$ErrMsg = _('The stock record of the stock selected') . ': ' . $_POST['Stock'] . ' ' . 
		_('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	
	$_POST['ItemDescription'] = $myrow[0];
	$_POST['suppliers_partno'] = $myrow[1];
	$_POST['package'] = $myrow[2];
	$_POST['uom'] = $myrow[3];
	$_POST['nw'] = $myrow[4];
	$_POST['gw'] = $myrow[5];
	$_POST['cuft'] = $myrow[6];
} // end if (isset($_POST['StockID2']) && $_GET['Edit']=='')

if (isset($_POST['UpdateLines'])) {
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		if ($POLine->Deleted==False) {
			$POLine->Quantity=$_POST['Qty'.$POLine->LineNo];
			$POLine->Price=$_POST['Price'.$POLine->LineNo];
			$POLine->nw=$_POST['nw'.$POLine->LineNo];
			$POLine->ReqDelDate=$_POST['ReqDelDate'.$POLine->LineNo];
		}
	}
}

if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

/*First do some validation
 *Is the delivery information all entered
 */
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['PO'.$identifier]->DelAdd1=='' or strlen($_SESSION['PO'.$identifier]->DelAdd1)<3){
		prnMsg( _('The purchase order can not be committed to the database because there is no delivery steet address specified'),'error');
		$InputError=1;
	} elseif ($_SESSION['PO'.$identifier]->Location=='' or ! isset($_SESSION['PO'.$identifier]->Location)){
		prnMsg( _('The purchase order can not be committed to the database because there is no location specified to book any stock items into'),'error');
		$InputError=1;
	} elseif ($_SESSION['PO'.$identifier]->LinesOnOrder <=0){
		prnMsg( _('The purchase order can not be committed to the database because there are no lines entered on this order'),'error');
		$InputError=1;
	}

/*If all clear then proceed to update the database
 */
	if ($InputError!=1){
		$emailsql='SELECT email FROM www_users WHERE userid="'.$_SESSION['PO'.$identifier]->Initiator.'"';
		$emailresult=DB_query($emailsql, $db);
		$emailrow=DB_fetch_array($emailresult);
		$sql = 'BEGIN';
		$result = DB_query($sql,$db);

		if ($_SESSION['ExistingOrder']==0){ /*its a new order to be inserted */
			$date = date($_SESSION['DefaultDateFormat']);
			$StatusComment=$date.' - Order Created by <a href="mailto:'.$emailrow['email'].'">'.$_SESSION['PO'.$identifier]->Initiator.
				'</a> - '.$_SESSION['PO'.$identifier]->StatusMessage.'<br>';
			/*Insert to purchase order header record */
			$sql = "INSERT INTO purchorders (
					supplierno,
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
					deladd6,
					version,
					realorderno,
					revised,
					deliveryby,
					status,
					stat_comment,
					deliverydate)
				VALUES(
					'" . $_SESSION['PO'.$identifier]->SupplierID . "',
					'" . $_SESSION['PO'.$identifier]->Comments . "',
					'" . Date("Y-m-d") . "',
					'" . $_SESSION['PO'.$identifier]->ExRate . "',
					'" . $_SESSION['PO'.$identifier]->Initiator . "',
					'" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
					'" . $_SESSION['PO'.$identifier]->Location . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
					'" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
					'" . $_SESSION['PO'.$identifier]->version . "',			
					'" . $_SESSION['PO'.$identifier]->OrderNo2 . "',
					'" . FormatDateForSQL($date) . "',
					'" . $_SESSION['PO'.$identifier]->deliveryby . "',				
					'" . 'Pending' . "',
					'" . $StatusComment . "',
					'" . FormatDateForSQL($_SESSION['PO'.$identifier]->deliverydate) . "'
				)";

			$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			/*Get the auto increment value of the order number created from the SQL above */
			$_SESSION['PO'.$identifier]->OrderNo =  GetNextTransNo(18, $db);

		     /*Insert the purchase order detail records */
			foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
					$sql = "INSERT INTO purchorderdetails (
							orderno,
							itemcode,
							deliverydate,
							itemdescription,
							glcode,
							unitprice,
							quantityord,
							shiptref,
							jobref,
							itemno,
							uom,
							suppliers_partno,
							subtotal_amount,
							package,
							pcunit,
							nw,
							gw,
							cuft,
							total_quantity,
							total_amount
							)
					VALUES (
							" . $_SESSION['PO'.$identifier]->OrderNo . ",
							'" . $POLine->StockID . "',
							'" . FormatDateForSQL($POLine->ReqDelDate) . "',
							'" . $POLine->ItemDescription . "',
							" . $POLine->GLCode . ",
							" . $POLine->Price . ",
							" . $POLine->Quantity . ",
							'" . $POLine->ShiptRef . "',
							'" . $POLine->JobRef . "',
							'" . $POLine->itemno . "',
							'" . $POLine->uom . "',
							'" . $POLine->suppliers_partno . "',
							'" . $POLine->subtotal_amount . "',
							'" . $POLine->package . "',
							'" . $POLine->pcunit . "',
							'" . $POLine->nw . "',
							'" . $POLine->gw . "',
							'" . $POLine->cuft . "',					
							'" . $POLine->total_quantity . "',
							'" . $POLine->total_amount . "'
							)";
					$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
					$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
					$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			} /* end of the loop round the detail line items on the order */
			echo '<p>';
			prnMsg(_('Purchase order') . ' ' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . _('on') . ' ' . 
		     	$_SESSION['PO'.$identifier]->SupplierName . ' ' . _('has been created'),'success');
			echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . 
				_('Print') . '" alt="">' . ' ' . _('Print Purchase Order') . '';
		} else { /*its an existing order need to update the old order info */
	//	 		$_SESSION['PO'.$identifier]->version += 0.01;
			$date = date($_SESSION['DefaultDateFormat']);
		     /*Update the purchase order header with any changes */

			$sql = "UPDATE purchorders SET
		     			supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "' ,
					comments='" . $_SESSION['PO'.$identifier]->Comments . "',
					rate=" . $_SESSION['PO'.$identifier]->ExRate . ",
					initiator='" . $_SESSION['PO'.$identifier]->Initiator . "',
					requisitionno= '" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
					version= '" .  $_SESSION['PO'.$identifier]->version . "',
					deliveryby='" . $_SESSION['PO'.$identifier]->deliveryby . "',					
					deliverydate='" . FormatDateForSQL($_SESSION['PO'.$identifier]->deliverydate) . "',
					revised= '" . FormatDateForSQL($date) . "',
					intostocklocation='" . $_SESSION['PO'.$identifier]->Location . "',
					deladd1='" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
					deladd2='" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
					deladd3='" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
					deladd4='" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
					deladd5='" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
					deladd6='" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
					allowprint=" . $_SESSION['PO'.$identifier]->AllowPrintPO . "
		     		WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";

			$ErrMsg =  _('The purchase order could not be updated because');
			$DbgMsg = _('The SQL statement used to update the purchase order header record, that failed was');
			$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

			/*Now Update the purchase order detail records */
			foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
				$sql='UPDATE purchorders SET status="'._('Pending').'" WHERE orderno=' . $_SESSION['PO'.$identifier]->OrderNo;
				$result=DB_query($sql,$db);
				if ($POLine->Deleted==true) {
					if ($POLine->PODetailRec!='') {
						$sql="DELETE FROM purchorderdetails WHERE podetailitem='" . $POLine->PODetailRec . "'";
						$ErrMsg =  _('The purchase order could not be deleted because');
						$DbgMsg = _('The SQL statement used to delete the purchase order header record, that failed was');
						$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
					}
				} else if ($POLine->PODetailRec=='') {

					$sql = "INSERT INTO purchorderdetails (
									orderno,
									itemcode,
									deliverydate,
									itemdescription,
									glcode,
									unitprice,
									quantityord,
									shiptref,
									jobref,
									itemno,
									uom,
									suppliers_partno,
									subtotal_amount,
									package,
									pcunit,
									nw,
									gw,
									cuft,
									total_quantity,
									total_amount
									)
								VALUES ("
									. $_SESSION['PO'.$identifier]->OrderNo . ",
									'" . $POLine->StockID . "',
									'" . FormatDateForSQL($POLine->ReqDelDate) . "',
									'" . $POLine->ItemDescription . "',
									" . $POLine->GLCode . ",
									" . $POLine->Price . ",
									" . $POLine->Quantity . ",
									'" . $POLine->ShiptRef . "',
									'" . $POLine->JobRef . "',
									'" . $POLine->itemno . "',
									'" . $POLine->uom . "',
									'" . $POLine->suppliers_partno . "',
									'" . $POLine->subtotal_amount . "',
									'" . $POLine->package . "',
									'" . $POLine->pcunit . "',
									'" . $POLine->nw . "',
									'" . $POLine->gw . "',
									'" . $POLine->cuft . "',
									'" . $POLine->total_quantity . "',
									'" . $POLine->total_amount . "'
								)";

				} else {
					if ($POLine->Quantity==$POLine->QtyReceived){
						$sql = "UPDATE purchorderdetails SET
								itemcode='" . $POLine->StockID . "',
								deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
								itemdescription='" . $POLine->ItemDescription . "',
								glcode=" . $POLine->GLCode . ",
								unitprice=" . $POLine->Price . ",
								quantityord=" . $POLine->Quantity . ",
								shiptref='" . $POLine->ShiptRef . "',
								jobref='" . $POLine->JobRef . "',
								itemno='" . $POLine->itemno . "',
								uom='" . $POLine->uom . "',
								suppliers_partno='" . $POLine->suppliers_partno . "',
								subtotal_amount='" . $POLine->subtotal_amount . "',
								package='" . $POLine->package . "',
								pcunit='" . $POLine->pcunit . "',
								nw='" . $POLine->nw . "',
								gw='" . $POLine->gw . "',
								cuft='" . $POLine->cuft . "',
								total_quantity='" . $POLine->total_quantity . "',
								total_amount='" . $POLine->total_amount . "',
								completed=1
							WHERE podetailitem=" . $POLine->PODetailRec;
					} else {
						$sql = "UPDATE purchorderdetails SET
								itemcode='" . $POLine->StockID . "',
								deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
								itemdescription='" . $POLine->ItemDescription . "',
								glcode=" . $POLine->GLCode . ",
								unitprice=" . $POLine->Price . ",
								quantityord=" . $POLine->Quantity . ",
								shiptref='" . $POLine->ShiptRef . "',
								jobref='" . $POLine->JobRef . "',
								itemno='" . $POLine->itemno . "',
								uom='" . $POLine->uom . "',
								suppliers_partno='" . $POLine->suppliers_partno . "',
								subtotal_amount='" . $POLine->subtotal_amount . "',
								package='" . $POLine->package . "',
								pcunit='" . $POLine->pcunit . "',
								nw='" . $POLine->nw . "',
								gw='" . $POLine->gw . "',
								cuft='" . $POLine->cuft . "',
								total_quantity='" . $POLine->total_quantity . "',
								total_amount='" . $POLine->total_amount . "'
								WHERE podetailitem=" . $POLine->PODetailRec;
					}
				}

				$ErrMsg = _('One of the purchase order detail records could not be updated because');
				$DbgMsg = _('The SQL statement used to update the purchase order detail record that failed was');
				$result =DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		     } /* end of the loop round the detail line items on the order */
		     echo '<br><br>';
		     prnMsg(_('Purchase order') . ' ' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . _('has been updated'),'success');
		     if ($_SESSION['PO'.$identifier]->AllowPrintPO==1){
			 //    echo '<br><a target="_blank" href="'.$rootpath.'/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '">' . _('Print Purchase Order') . '</a>';
		     }
		} /*end of if its a new order or an existing one */

		$sql = 'COMMIT';
		$Result = DB_query($sql,$db);
		unset($_SESSION['PO'.$identifier]); /*Clear the PO data to allow a newy to be input*/
		echo "<br><a href='".$rootpath."/PO_SelectOSPurchOrder.php?" . SID . "'>" . _('Return To PO List') . '</a>';
		include('includes/footer.inc');
		exit;
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the PO object to the database  - user hit the place PO*/



if (isset($_POST['Search'])){  /*ie seach for stock items */

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
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
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				AND stockmaster.description " . LIKE . " '$SearchString'
				ORDER BY stockmaster.stockid";
		} else {
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				AND stockmaster.description " . LIKE . " '$SearchString'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
				ORDER BY stockmaster.stockid";
		} else {
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat']=='All'){
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				ORDER BY stockmaster.stockid";
		} else {
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				and stockmaster.discontinued!=1
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
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

if(isset($_GET['Delete'])){
	if($_SESSION['PO'.$identifier]->Some_Already_Received($_POST['LineNo'])==0){
		$_SESSION['PO'.$identifier]->LineItems[$_GET['Delete']]->Deleted=True;
		include ('includes/PO_UnsetFormVbls.php');
	} else {
		prnMsg( _('This item cannot be deleted because some of it has already been received'),'warn');
	}
}


if (isset($_POST['LookupPrice']) and isset($_POST['StockID2'])){
	$sql = "SELECT purchdata.price,
			purchdata.conversionfactor,
			purchdata.supplierdescription
		FROM purchdata
		WHERE  purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
		AND purchdata.stockid = '". strtoupper($_POST['StockID2']) . "'";

	$ErrMsg = _('The supplier pricing details for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
	$LookupResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($LookupResult)==1){
		$myrow = DB_fetch_array($LookupResult);
		$_POST['Price'] = $myrow['price']/$myrow['conversionfactor'];
	} else {
		prnMsg(_('Sorry') . ' ... ' . _('there is no purchasing data set up for this supplier') . '  - ' . $_SESSION['PO'.$identifier]->SupplierID . ' ' . _('and item') . ' ' . strtoupper($_POST['StockID']),'warn');
	}
}

if (isset($_POST['UpdateLine'])){
	$AllowUpdate=true; /*Start assuming the best ... now look for the worst*/

	if ($_POST['Qty']==0 OR $_POST['Price'] < 0){
		$AllowUpdate = false;
		prnMsg( _('The Update Could Not Be Processed') . '<br>' . _('You are attempting to set the quantity ordered to zero, or the price is set to an amount less than 0'),'error');
	}

	if ($_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->QtyInv > $_POST['Qty'] OR $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->QtyReceived > $_POST['Qty']){
		$AllowUpdate = false;
		prnMsg( _('The Update Could Not Be Processed') . '<br>' . _('You are attempting to make the quantity ordered a quantity less than has already been invoiced or received this is of course prohibited') . '. ' . _('The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item'),'error');
	}

	if ($_SESSION['PO'.$identifier]->GLLink==1) {
	/*Check for existance of GL Code selected */
		$sql = 'SELECT accountname 
				FROM chartmaster 
				WHERE accountcode =' .  $_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->GLCode;
		$ErrMsg = _('The account name for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the account details but failed was');
		$GLActResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_error_no($db)!=0 OR DB_num_rows($GLActResult)==0){
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

		$_SESSION['PO'.$identifier]->update_order_item(
					$_POST['LineNo'],
					$_POST['Qty'],
					$_POST['Price'],
					$_POST['ItemDescription'],
					$_POST['GLCode'],
					$GLAccountName,
					$_POST['ReqDelDate'],
					$_POST['ShiptRef'],
					$_POST['JobRef'],
					$_POST['itemno'],
					$_SESSION['PO'.$identifier]->LineItems[$_POST['LineNo']]->uom,
					$_POST['suppliers_partno'],
					$_POST['Qty']*$_POST['Price'],
					$_POST['package'],
					$_POST['pcunit'],
					$_POST['nw'],
					$_POST['gw'],
					$_POST['cuft'],
					$_POST['Qty'],
					$_POST['Qty']*$_POST['Price']
					);

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
// mark on 081013
		$AllowUpdate = False;
		prnMsg( _('Cannot Enter this order line') . '</b><br>' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}

//	include ('PO_Chk_ShiptRef_JobRef.php');

 /*Then its not a stock item */

		/*need to check GL Code is valid if GLLink is active */
		if ($_SESSION['PO'.$identifier]->GLLink==1){

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

			$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
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
						$GLAccountName,
						2,
						$_POST['itemno'],						
						$_POST['uom'],
						$_POST['suppliers_partno'],
						$_POST['subtotal_amount'],
						$_POST['package'],
						$_POST['pcunit'],
						$_POST['nw'],
						$_POST['gw'],
						$_POST['cuft'],
						$_POST['total_quantity'],
						$_POST['total_amount']
						);
		   include ('includes/PO_UnsetFormVbls.php');
		}
	}
 /*end if Enter line button was hit */


if (isset($_POST['NewItem'])){ /* NewItem is set from the part selection list as the part code selected */
/* take the form entries and enter the data from the form into the PurchOrder class variable */
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 3)=='qty') {
			$ItemCode=substr($key, 3, strlen($key)-3);
			$Quantity=$value;
			$AlreadyOnThisOrder =0;

			if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
				if (count($_SESSION['PO'.$identifier]->LineItems)!=0){

					foreach ($_SESSION['PO'.$identifier]->LineItems AS $OrderItem) {

					/* do a loop round the items on the order to see that the item
					is not already on this order */
						if (($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted==false)) {
							$AlreadyOnThisOrder = 1;
							prnMsg( _('The item') . ' ' . $ItemCode . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'),'error');
						}
					} /* end of the foreach loop to look for preexisting items of the same code */
				}
			}
			if ($AlreadyOnThisOrder!=1 and $Quantity>0){
				$purchdatasql='SELECT COUNT(supplierno)
								FROM purchdata
								WHERE purchdata.supplierno = "' . $_SESSION['PO'.$identifier]->SupplierID . '"
								AND purchdata.stockid="'. $ItemCode . '"';
				$purchdataresult=DB_query($purchdatasql, $db);
				$myrow=DB_fetch_row($purchdataresult);
				if ($myrow[0]>0) {
					$sql = "SELECT stockmaster.description,
						stockmaster.stockid,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmaster.kgs,
						stockmaster.netweight,
						stockcategory.stockact,
						chartmaster.accountname,
						purchdata.price,
						purchdata.conversionfactor,				
						purchdata.supplierdescription,
						purchdata.suppliersuom,
						purchdata.suppliers_partno,
						purchdata.leadtime
					FROM stockcategory,
						chartmaster,
						stockmaster LEFT JOIN purchdata
					ON stockmaster.stockid = purchdata.stockid
					AND purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
					WHERE chartmaster.accountcode = stockcategory.stockact
						AND stockcategory.categoryid = stockmaster.categoryid
						AND stockmaster.stockid = '". $ItemCode . "'
						AND purchdata.effectivefrom = 
							(SELECT max(effectivefrom) 
								FROM purchdata 
								WHERE purchdata.stockid='". $ItemCode . "'
								AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "')";
				} else {
					$sql='SELECT stockmaster.description,
						stockmaster.stockid,
						stockmaster.units,
						stockmaster.decimalplaces,
						stockmaster.kgs,
						stockmaster.netweight,
						stockcategory.stockact,
						chartmaster.accountname
					FROM stockcategory,
						chartmaster,
						stockmaster
					WHERE chartmaster.accountcode = stockcategory.stockact
						AND stockcategory.categoryid = stockmaster.categoryid
						AND stockmaster.stockid = "'. $ItemCode . '"';
				}

				$ErrMsg = _('The supplier pricing details for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the pricing details but failed was');
				$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if ($myrow = DB_fetch_array($result1)){
					if (is_numeric($myrow['price'])){

						$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
							$ItemCode,
							0, /*Serialised */
							0, /*Controlled */
							$Quantity, /* Qty */
							$myrow['description'],
							$myrow['price'],
							$myrow['units'],
							$myrow['stockact'],
							$_SESSION['PO'.$identifier]->deliverydate,
							0,
							0,
							0,
							0,
							0,
							$myrow['accountname'],
							$myrow['decimalplaces'],
							$ItemCode,
							$myrow['suppliersuom'],
							$myrow['suppliers_partno'],
							$Quantity*$myrow['price'],
							$myrow['leadtime'],
							'',
							$myrow['netweight'],
							$myrow['kgs'],
							'',
							$Quantity,
							$Quantity*$myrow['price']							
							);
					} else { /*There was no supplier purchasing data for the item selected so enter a purchase order line with zero price */

						$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
							$ItemCode,
							0, /*Serialised */
							0, /*Controlled */
							$Quantity, /* Qty */
							$myrow['description'],
							0,
							$myrow['units'],
							$myrow['stockact'],
							$_SESSION['PO'.$identifier]->deliverydate,
							0,
							0,
							0,
							0,
							0,
							$myrow['accountname'],
							0,
							$ItemCode,
							'',
							'',
							0,
							0,
							'',
							0,
							0,
							0,
							0,
							0
							);
					}
			/*Make sure the line is also available for editing by default without additional clicks */
//					$_GET['Edit'] = $_SESSION['PO'.$identifier]->LinesOnOrder; /* this is a bit confusing but it was incremented by the add_to_order function */
				} else {
					prnMsg (_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the order'),'error');
					if ($debug==1){
						echo "<br>".$sql;
					}
					include('includes/footer.inc');
					exit;
				}
			} /* end of if not already on the order */
		}
	}
} /* end of if its a new item */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo "<form name=form1 action='" . $_SERVER['PHP_SELF'] . "?" . SID . "identifier=".$identifier. "' method=post>";

//echo  _('Purchase Order') . ': <font color=BLUE size=4><b>' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . $_SESSION['PO'.$identifier]->SupplierName . ' </b></font> - ' . _('All amounts stated in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . '<br>';

/*need to set up entry for item description where not a stock item and GL Codes */

if (count($_SESSION['PO'.$identifier]->LineItems)>0 and !isset($_GET['Edit'])){
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . 
		_('Purchase Order') . '" alt="">';
		
	if (isset($_SESSION['PO'.$identifier]->OrderNo)) {
		echo  ' ' . _('Purchase Order') .' '. $_SESSION['PO'.$identifier]->OrderNo ;
	}
	echo '<br><b>'._(' Order Summary') . '</b>';
	echo '<table cellpadding=2 colspan=7 border=1>';
	echo "<tr>
		<th>" . _('Item Code') . "</th>
		<th>" . _('Description') . "</th>
		<th>" . _('Quantity') . "</th>
		<th>" . _('UOM') ."</th>
		<th>" . _('Weight') . "</th>
		<th>" . _('Price') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ")</th>
		<th>" . _('Subtotal') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ")</th>
		<th>" . _('Deliver By') ."</th>
		</tr>";

	$_SESSION['PO'.$identifier]->total = 0;
	$k = 0;  //row colour counter
	
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {

		if ($POLine->Deleted==False) {
			$LineTotal = $POLine->Quantity * $POLine->Price;
			// Note decimal places should not fixed at 2, use POLine->DecimalPlaces instead
			//              $DisplayLineTotal = number_format($LineTotal,2);
			$DisplayLineTotal = number_format($LineTotal,2);
			// Note if the price is greater than 1 use 2 decimal place, if the price is a fraction of 1, use 4 decimal places
			// This should help display where item-price is a fraction
			if ($POLine->Price > 1) {
				$DisplayPrice = number_format($POLine->Price,2,'.','');
			} else {
				$DisplayPrice = number_format($POLine->Price,2,'.','');
			}
			$DisplayQuantity = number_format($POLine->Quantity,$POLine->DecimalPlaces,'.','');

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$uomsql='SELECT conversionfactor, suppliersuom
					FROM purchdata
					WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
					AND stockid="'.$POLine->StockID.'"';

			$uomresult=DB_query($uomsql, $db);
			if (DB_num_rows($uomresult)>0) {
				$uomrow=DB_fetch_array($uomresult);
				if (strlen($uomrow['suppliersuom'])>0) {
					$uom=$uomrow['suppliersuom'];
				} else {
					$uom=$POLine->Units;
				}
			} else {
				$uom=$POLine->Units;
			}
			//			echo "<td>$POLine->StockID</td><td>$POLine->ItemDescription</td>td> align=right>$DisplayQuantity</td><td>$POLine->Units</td><td>$POLine->ReqDelDate</td>td> align=right>$DisplayPrice</td>td> align=right>$DisplayLineTotal</font></td><td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Edit=" . $POLine->LineNo . "'>" . _('Select') . "</a></td></tr>";
			echo "<td>$POLine->StockID</td>
				<td>".$POLine->ItemDescription."</td>
				<td><input type=text class=number name=Qty$POLine->LineNo size=11 value=".$DisplayQuantity."></td>
				<td>$uom</td>
				<td><input type=text class=number name=nw$POLine->LineNo size=11 value=".$POLine->nw."></td>
				<td><input type=text class=number name=Price$POLine->LineNo size=11 value=".$DisplayPrice."></td>
				<td class=number>$DisplayLineTotal</td>
				<td><input type=text class=date alt='".$_SESSION['DefaultDateFormat']."' name=ReqDelDate$POLine->LineNo size=11 value=".$POLine->ReqDelDate."></td>
				<td><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "identifier=".$identifier. "&Delete=" . $POLine->LineNo . "'>" . _('Delete') . "</a></td></tr>";
			$_SESSION['PO'.$identifier]->total = $_SESSION['PO'.$identifier]->total + $LineTotal;
		}
	}

	$DisplayTotal = number_format($_SESSION['PO'.$identifier]->total,2);
	echo '<tr><td colspan=6 align=right>' . _('TOTAL') . _(' excluding Tax') . '</td><td class=number><b>' . $DisplayTotal . '</b></td></tr></table>';
	echo '<br><div class="centre"><input type="submit" name="UpdateLines" value="Update Order Lines">';
	echo '<br><input type="submit" name="Commit" value="Process Order">';
	if (!isset($_POST['NewItem']) and isset($_GET['Edit'])) {
	
	/*show a form for putting in a new line item with or without a stock entry */
	 
	}
} /*Only display the order line items if there are any !! */


if (isset($_POST['NonStockOrder'])) {
	echo '<br><table><tr><td>'._('Description of Item').'</td>';
	echo '<td><input type=text name=ItemDescription size=40></td></tr>';
	echo '<tr><td>'._('General Ledger Code').'</td>';
	echo '<td><select name="GLCode">';
	$sql='SELECT 
			accountcode, 
			accountname 
		  FROM chartmaster 
		  ORDER BY accountcode ASC';
	$result=DB_query($sql, $db);
	while ($myrow=DB_fetch_array($result)) {
		echo '<option value="'.$myrow['accountcode'].'">'.$myrow['accountcode'].' - '.$myrow['accountname'].'</option>';
	}
	echo '</td></tr>';
	echo '<tr><td>'._('Quantity to purchase').'</td>';
	echo '<td><input type=text class=number name=Qty size=10></td></tr>';
	echo '<tr><td>'._('Price per item').'</td>';
	echo '<td><input type=text class=number name=Price size=10></td></tr>';
	echo '<tr><td>'._('Delivery Date').'</td>';
	echo '<td><input type=text class=date alt="'.$_SESSION['DefaultDateFormat'].'" name=ReqDelDate size=11
			value="'.$_SESSION['PO'.$identifier]->deliverydate .'"></td></tr>';
	echo '</table>';
	echo '<input type=submit name="EnterLine" value="Enter Item">';
}

echo '<hr>';

/* Now show the stock item selection search stuff below */

if (!isset($_GET['Edit'])) {
	$sql="SELECT categoryid,
			categorydescription
		FROM stockcategory
		WHERE stocktype<>'L'
		AND stocktype<>'D'
		ORDER BY categorydescription";
	$ErrMsg = _('The supplier category details could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the category details but failed was');
	$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<table><tr><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . 
		_('Print') . '" alt="">' . ' ' . _('Search For Stock Items') . '';

	echo ":</font></tr><tr><td><select name='StockCat'>";

	echo "<option selected value='All'>" . _('All');
	while ($myrow1 = DB_fetch_array($result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat']==$myrow1['categoryid']){
			echo "<option selected value=". $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		} else {
			echo "<option value=". $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		}
	}

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords']='';
	}
  	 
	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode']='';
	}

	unset($_POST['Keywords']);
	unset($_POST['StockCode']);
	echo '</select></td>
		<td><font size=2>' . _('Enter text extracts in the description') . ":</font></td>
		<td><input type='text' name='Keywords' size=20 maxlength=25 value='" . $_POST['Keywords'] . "'></td></tr>
		<tr><td></td>
		<td><font size=3><b>" . _('OR') . ' </b></font><font size=2>' . _('Enter extract of the Stock Code') . 
			":</font></td>
		<td><input type='text' name='StockCode' size=15 maxlength=18 value='" . $_POST['StockCode'] . "'></td>
		</tr>
		<tr><td></td>
		<td><font size=3><b>" . _('OR') . ' </b></font><font size=2><a target="_blank" href="'.$rootpath.'/Stocks.php?"' . SID .
			 '">' . _('Create a New Stock Item') . "</a></font></td></tr>
		</table><br>
		<div class='centre'><input type=submit name='Search' value='" . _('Search Now') . "'>
		<input type=submit name='NonStockOrder' value='" . _('Order a non stock item') . "'>
		</div><br>";


	$PartsDisplayed =0;
}

if (isset($SearchResult)) {

	echo "<table cellpadding=1 colspan=7 border=1>";

	$tableheader = "<tr>
			<th>" . _('Code')  . "</th>
			<th>" . _('Description') . "</th>
			<th>" . _('Units') . "</th>
			<th><a href='#end'>"._('Go to end of list')."</a></th>
			</tr>";
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter

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

			$ImageSource = '<img src="'.$rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . 
				'.jpg" width="50" height="50">';

		} else {
			$ImageSource = '<i>'._('No Image').'</i>';
		}

			$uomsql='SELECT conversionfactor, suppliersuom
					FROM purchdata
					WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
					AND stockid="'.$myrow['stockid'].'"';

			$uomresult=DB_query($uomsql, $db);
			if (DB_num_rows($uomresult)>0) {
				$uomrow=DB_fetch_array($uomresult);
				if (strlen($uomrow['suppliersuom'])>0) {
					$uom=$uomrow['suppliersuom'];
				} else {
					$uom=$myrow['units'];
				}
			} else {
				$uom=$myrow['units'];
			}
			printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><input class='number' type='text' size=6 value=0 name='qty%s'></td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$uom,
			$ImageSource,
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

		prnMsg( _('Only the first') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . _('can be displayed') . '. ' . 
			_('Please restrict your search to only the parts required'),'info');
	}
	echo '<a name="end"></a><br><div class="centre"><input type="submit" name="NewItem" value="Order some"></div>';
}#end if SearchResults to show

echo '<hr>';

echo '</form>';
include('includes/footer.inc');
?>