<?php

// Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL.

include('includes/DefinePOClass.php');
include('includes/SQL_CommonFunctions.php');
include('includes/ImageFunctions.php');

include('includes/session.php');

if (isset($_POST['ReqDelDate'])){$_POST['ReqDelDate'] = ConvertSQLDate($_POST['ReqDelDate']);}

$Title = __('Purchase Order Items');

$identifier = $_GET['identifier'];

/* If a purchase order header doesn't exist, then go to PO_Header.php to create one */
if (!isset($_SESSION['PO'.$identifier])) {
	header('Location:' . htmlspecialchars_decode($RootPath) . '/PO_Header.php');
	exit();
}

/* webERP manual links before header.php */
$ViewTopic = 'PurchaseOrdering';
$BookMark = 'PurchaseOrdering';
include('includes/header.php');

if (!isset($_POST['Commit'])) {
	echo '<a href="'.$RootPath.'/PO_Header.php?identifier=' . $identifier. '">' .__('Back To Purchase Order Header') . '</a><br />';
}

if (isset($_POST['UpdateLines']) OR isset($_POST['Commit'])) {
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		if ($POLine->Deleted == false) {
			if (!is_numeric(filter_number_format($_POST['ConversionFactor'.$POLine->LineNo]))){
				prnMsg(__('The conversion factor is expected to be numeric - the figure which converts from our units to the supplier units. e.g. if the supplier units is a tonne and our unit is a kilogram then the conversion factor that converts our unit to the suppliers unit is 1000'),'error');
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ConversionFactor = 1;
			} else { //a valid number for the conversion factor is entered
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ConversionFactor = filter_number_format($_POST['ConversionFactor'.$POLine->LineNo]);
			}
			if (!is_numeric(filter_number_format($_POST['SuppQty'.$POLine->LineNo]))){
				prnMsg(__('The quantity in the supplier units is expected to be numeric. Please re-enter as a number'),'error');
			} else { //ok to update the PO object variables
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->Quantity = round(filter_number_format($_POST['SuppQty'.$POLine->LineNo])*$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ConversionFactor,$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->DecimalPlaces);
			}
			if (!is_numeric(filter_number_format($_POST['SuppPrice'.$POLine->LineNo]))){
				prnMsg(__('The supplier price is expected to be numeric. Please re-enter as a number'),'error');
			} else { //ok to update the PO object variables
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->Price = filter_number_format($_POST['SuppPrice'.$POLine->LineNo])/$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ConversionFactor;
			}
			$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ReqDelDate = $_POST['ReqDelDate'.$POLine->LineNo];
            $_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ItemDescription = $_POST['ItemDescription'.$POLine->LineNo];
		}
	}
}

if (isset($_POST['Commit'])){ /*User wishes to commit the order to the database */

/*First do some validation
 *Is the delivery information all entered
 */
	$InputError=0; /*Start off assuming the best */
	if ($_SESSION['PO'.$identifier]->DelAdd1=='' or mb_strlen($_SESSION['PO'.$identifier]->DelAdd1)<3){
		prnMsg( __('The purchase order cannot be committed to the database because there is no delivery street address specified'),'error');
		$InputError=1;
	} elseif ($_SESSION['PO'.$identifier]->Location=='' or ! isset($_SESSION['PO'.$identifier]->Location)){
		prnMsg( __('The purchase order can not be committed to the database because there is no location specified to book any stock items into'),'error');
		$InputError=1;
	} elseif ($_SESSION['PO'.$identifier]->LinesOnOrder <=0){
		prnMsg( __('The purchase order can not be committed to the database because there are no lines entered on this order'),'error');
		$InputError=1;
	}

/*If all clear then proceed to update the database
 */
	if ($InputError!=1){

		DB_Txn_Begin();

		/*figure out what status to set the order to */
		if (IsEmailAddress($_SESSION['UserEmail'])){
			$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
		} else {
			$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
		}
		if ($_SESSION['AutoAuthorisePO']==1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL ="SELECT authlevel
						FROM purchorderauth
						WHERE userid='".$_SESSION['UserID']."'
						AND currabrev='".$_SESSION['PO'.$identifier]->CurrCode."'";

			$AuthResult = DB_query($AuthSQL);
			$AuthRow=DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $_SESSION['PO'.$identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created and Authorised by') . $UserDetails . '<br />' .  $_SESSION['PO'.$identifier]->StatusComments . '<br />';
				$_SESSION['PO'.$identifier]->AllowPrintPO=1;
				$_SESSION['PO'.$identifier]->Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) ==0){
					$AuthMessage = __('Your authority to approve purchase orders in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . __('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = __('You can only authorise up to').' '.$_SESSION['PO'.$identifier]->CurrCode.' '.$AuthRow['authlevel'] .'.<br />';
				}

				prnMsg( __('You do not have permission to authorise this purchase order').'.<br />' .  __('This order is for').' '.
					$_SESSION['PO'.$identifier]->CurrCode . ' '. $_SESSION['PO'.$identifier]->Order_Value() .'. '.
					$AuthMessage .
					__('If you think this is a mistake please contact the systems administrator') . '<br />' .
					__('The order will be created with a status of pending and will require authorisation'), 'warn');

				$_SESSION['PO'.$identifier]->AllowPrintPO=0;
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . $UserDetails . '<br />' . $_SESSION['PO'.$identifier]->StatusComments . '<br />';
				$_SESSION['PO'.$identifier]->Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$_SESSION['PO'.$identifier]->AllowPrintPO=0;
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . __('Order Created by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusComments . '<br />';
			$_SESSION['PO'.$identifier]->Status = 'Pending';
		}

		if ($_SESSION['ExistingOrder']==0){ /*its a new order to be inserted */

			/*Get the order number */
			$_SESSION['PO'.$identifier]->OrderNo =  GetNextTransNo(18);

			/*Insert to purchase order header record */
			if(!isset( $_SESSION['PO' . $identifier]->DeliveryDate)){
				$_SESSION['PO' . $identifier]->DeliveryDate = ConvertSQLDate('1000-01-01');
			}
			$SQL = "INSERT INTO purchorders ( orderno,
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
											tel,
											suppdeladdress1,
											suppdeladdress2,
											suppdeladdress3,
											suppdeladdress4,
											suppdeladdress5,
											suppdeladdress6,
											suppliercontact,
											supptel,
											contact,
											version,
											revised,
											deliveryby,
											status,
											stat_comment,
											deliverydate,
											paymentterms,
											allowprint)
							VALUES(	'" . $_SESSION['PO'.$identifier]->OrderNo . "',
									'" . $_SESSION['PO'.$identifier]->SupplierID . "',
									'" . $_SESSION['PO'.$identifier]->Comments . "',
									CURRENT_DATE,
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
									'" . $_SESSION['PO'.$identifier]->Tel . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd1 . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd2 . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd3 . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd4 . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd5 . "',
									'" . $_SESSION['PO'.$identifier]->SuppDelAdd6 . "',
									'" . $_SESSION['PO'.$identifier]->SupplierContact . "',
									'" . $_SESSION['PO'.$identifier]->SuppTel. "',
									'" . $_SESSION['PO'.$identifier]->Contact . "',
									'" . $_SESSION['PO'.$identifier]->Version . "',
									CURRENT_DATE,
									'" . $_SESSION['PO'.$identifier]->DeliveryBy . "',
									'" . $_SESSION['PO'.$identifier]->Status . "',
									'" . htmlspecialchars($StatusComment,ENT_QUOTES,'UTF-8') . "',
									'" . FormatDateForSQL($_SESSION['PO'.$identifier]->DeliveryDate) . "',
									'" . $_SESSION['PO'.$identifier]->PaymentTerms. "',
									'" . $_SESSION['PO'.$identifier]->AllowPrintPO . "' )";

			$ErrMsg =  __('The purchase order header record could not be inserted into the database because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		     /*Insert the purchase order detail records */
			foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
				if ($POLine->Deleted==False) {
					$SQL = "INSERT INTO purchorderdetails
									(orderno,
									itemcode,
									deliverydate,
									itemdescription,
									glcode,
									unitprice,
									quantityord,
									shiptref,
									jobref,
									suppliersunit,
									suppliers_partno,
									assetid,
									conversionfactor )
							VALUES ('" . $_SESSION['PO'.$identifier]->OrderNo . "',
									'" . $POLine->StockID . "',
									'" . FormatDateForSQL($POLine->ReqDelDate) . "',
									'" . DB_escape_string($POLine->ItemDescription) . "',
									'" . $POLine->GLCode . "',
									'" . $POLine->Price . "',
									'" . $POLine->Quantity . "',
									'" . $POLine->ShiptRef . "',
									'" . $POLine->JobRef . "',
									'" . $POLine->SuppliersUnit . "',
									'" . DB_escape_string($POLine->Suppliers_PartNo) . "',
									'" . $POLine->AssetID . "',
									'" . $POLine->ConversionFactor . "')";
					$ErrMsg =__('One of the purchase order detail records could not be inserted into the database because');

					$Result = DB_query($SQL, $ErrMsg, '', true);
				}
			} /* end of the loop round the detail line items on the order */
			echo '<p />';
			prnMsg(__('Purchase Order') . ' ' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . __('on') . ' ' . $_SESSION['PO'.$identifier]->SupplierName . ' ' . __('has been created'),'success');
                        if ($_SESSION['PO'.$identifier]->AllowPrintPO==1
				AND ($_SESSION['PO'.$identifier]->Status=='Authorised'
				OR $_SESSION['PO'.$identifier]->Status=='Printed')){

				echo '<br /><div class="centre"><a target="_blank" href="'.$RootPath.'/PO_PDFPurchOrder.php?OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '">' . __('Print Purchase Order') . '</a></div>';
			}

		} else { /*its an existing order need to update the old order info */
			/*Check to see if there are any incomplete lines on the order */
			$Completed = true; //assume it is completed i.e. all lines are flagged as completed
			foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
				if ($POLine->Completed==0){
					$Completed = false;
					break;
				}
			}
			if ($Completed){
				$_SESSION['PO'.$identifier]->Status = 'Completed';
				$_SESSION['PO'.$identifier]->StatusComments = date($_SESSION['DefaultDateFormat']).' - ' . __('Order completed by') . $UserDetails  . '<br />' . $_SESSION['PO'.$identifier]->StatusComments;
			} else {
				$_SESSION['PO'.$identifier]->StatusComments = date($_SESSION['DefaultDateFormat']).' - ' . __('Order modified by') . $UserDetails  . '<br />' . $_SESSION['PO'.$identifier]->StatusComments;
			}
		     /*Update the purchase order header with any changes */

			$SQL = "UPDATE purchorders SET supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "' ,
										comments='" . $_SESSION['PO'.$identifier]->Comments . "',
										rate='" . $_SESSION['PO'.$identifier]->ExRate . "',
										initiator='" . $_SESSION['PO'.$identifier]->Initiator . "',
										requisitionno= '" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
										version= '" .  $_SESSION['PO'.$identifier]->Version . "',
										deliveryby='" . $_SESSION['PO'.$identifier]->DeliveryBy . "',
										deliverydate='" . FormatDateForSQL($_SESSION['PO'.$identifier]->DeliveryDate) . "',
										revised= CURRENT_DATE,
										intostocklocation='" . $_SESSION['PO'.$identifier]->Location . "',
										deladd1='" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
										deladd2='" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
										deladd3='" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
										deladd4='" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
										deladd5='" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
										deladd6='" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
										tel='" . $_SESSION['PO'.$identifier]->Tel . "',
										suppdeladdress1='" . $_SESSION['PO'.$identifier]->SuppDelAdd1 . "',
										suppdeladdress2='" . $_SESSION['PO'.$identifier]->SuppDelAdd2 . "',
										suppdeladdress3='" . $_SESSION['PO'.$identifier]->SuppDelAdd3 . "',
										suppdeladdress4='" . $_SESSION['PO'.$identifier]->SuppDelAdd4 . "',
										suppdeladdress5='" . $_SESSION['PO'.$identifier]->SuppDelAdd5 . "',
										suppdeladdress6='" . $_SESSION['PO'.$identifier]->SuppDelAdd6 . "',
										suppliercontact='" . $_SESSION['PO'.$identifier]->SupplierContact . "',
										supptel='" . $_SESSION['PO'.$identifier]->SuppTel . "',
										contact='" . $_SESSION['PO'.$identifier]->Contact . "',
										paymentterms='" . $_SESSION['PO'.$identifier]->PaymentTerms . "',
										allowprint='" . $_SESSION['PO'.$identifier]->AllowPrintPO . "',
										status = '" . $_SESSION['PO'.$identifier]->Status . "',
										stat_comment = '" . htmlspecialchars($_SESSION['PO'.$identifier]->StatusComments,ENT_QUOTES,'UTF-8') . "'
										WHERE orderno = '" . $_SESSION['PO'.$identifier]->OrderNo ."'";

			$ErrMsg =  __('The purchase order could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Now Update the purchase order detail records */
			foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {

				if ($POLine->Deleted==true) {
					if ($POLine->PODetailRec!='') {
						$SQL="DELETE FROM purchorderdetails WHERE podetailitem='" . $POLine->PODetailRec . "'";
						$ErrMsg =  __('The purchase order detail line could not be deleted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);
					}
				} else if ($POLine->PODetailRec=='') {
						/*When the purchase order line is an existing record the auto-increment
						 * field PODetailRec is given to the session for that POLine
						 * So it will only be a new POLine if PODetailRec is empty
						*/
					$SQL = "INSERT INTO purchorderdetails
										( orderno,
										itemcode,
										deliverydate,
										itemdescription,
										glcode,
										unitprice,
										quantityord,
										shiptref,
										jobref,
										suppliersunit,
										suppliers_partno,
										assetid,
										conversionfactor)
							VALUES (
									'" . $_SESSION['PO'.$identifier]->OrderNo . "',
									'" . $POLine->StockID . "',
									'" . FormatDateForSQL($POLine->ReqDelDate) . "',
									'" . DB_escape_string($POLine->ItemDescription) . "',
									'" . $POLine->GLCode . "',
									'" . $POLine->Price . "',
									'" . $POLine->Quantity . "',
									'" . $POLine->ShiptRef . "',
									'" . $POLine->JobRef . "',
									'" . $POLine->SuppliersUnit . "',
									'" . $POLine->Suppliers_PartNo . "',
									'" . $POLine->AssetID . "',
									'" . $POLine->ConversionFactor . "')";

				} else {
					if ($POLine->Quantity==$POLine->QtyReceived){
						$CompletedLine = 1;
					} else {
						$CompletedLine = 0;
					}
					$SQL = "UPDATE purchorderdetails
							SET itemcode='" . $POLine->StockID . "',
									deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
									itemdescription='" . DB_escape_string($POLine->ItemDescription) . "',
									glcode='" . $POLine->GLCode . "',
									unitprice='" . $POLine->Price . "',
									quantityord='" . $POLine->Quantity . "',
									shiptref='" . $POLine->ShiptRef . "',
									jobref='" . $POLine->JobRef . "',
									suppliersunit='" . $POLine->SuppliersUnit . "',
									suppliers_partno='" . DB_escape_string($POLine->Suppliers_PartNo) . "',
									completed='" . $CompletedLine . "',
									assetid='" . $POLine->AssetID . "',
									conversionfactor = '" . $POLine->ConversionFactor . "'
								WHERE podetailitem='" . $POLine->PODetailRec . "'";
				}

				$ErrMsg = __('One of the purchase order detail records could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			} /* end of the loop round the detail line items on the order */
			echo '<br /><br />';
			prnMsg(__('Purchase Order') . ' ' . $_SESSION['PO'.$identifier]->OrderNo . ' ' . __('has been updated'),'success');
			if ($_SESSION['PO'.$identifier]->AllowPrintPO==1
					AND ($_SESSION['PO'.$identifier]->Status=='Authorised'
					OR $_SESSION['PO'.$identifier]->Status=='Printed')){

				echo '<br /><div class="centre"><a target="_blank" href="'.$RootPath.'/PO_PDFPurchOrder.php?OrderNo=' . $_SESSION['PO'.$identifier]->OrderNo . '">' . __('Print Purchase Order') . '</a></div>';
			}

		} /*end of if its a new order or an existing one */


		DB_Txn_Commit();
		/* Only show the link to auto receive the order if the user has permission to receive goods and permission to authorise and has authorised the order */
		if ($_SESSION['PO'.$identifier]->Status == 'Authorised'
                   AND in_array($_SESSION['PageSecurityArray']['GoodsReceived.php'], $_SESSION['AllowedPageSecurityTokens'])){

                	echo '<a href="SupplierInvoice.php?SupplierID=' . $_SESSION['PO'.$identifier]->SupplierID . '&amp;ReceivePO=' . $_SESSION['PO'.$identifier]->OrderNo . '&amp;DeliveryDate=' . $_SESSION['PO'.$identifier]->DeliveryDate . '">' . __('Receive and Enter Purchase Invoice') . '</a>';
		}

		unset($_SESSION['PO'.$identifier]); /*Clear the PO data to allow a newy to be input*/
		include('includes/footer.php');
		exit();
	} /*end if there were no input errors trapped */
} /* end of the code to do transfer the PO object to the database  - user hit the place PO*/


/* Always do the stuff below if not looking for a supplierid */

if(isset($_GET['Delete'])){
	if($_SESSION['PO'.$identifier]->Some_Already_Received($_GET['Delete'])==0){
		$_SESSION['PO'.$identifier]->remove_from_order($_GET['Delete']);
		include('includes/PO_UnsetFormVbls.php');
	} else {
		prnMsg( __('This item cannot be deleted because some of it has already been received'),'warn');
	}
}

if(isset($_GET['Complete'])){
	$_SESSION['PO'.$identifier]->LineItems[$_GET['Complete']]->Completed=1;
}

if (isset($_POST['EnterLine'])){ /*Inputs from the form directly without selecting a stock item from the search */

	$AllowUpdate = true; /*always assume the best */
	if (!is_numeric(filter_number_format($_POST['Qty']))){
		$AllowUpdate = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The quantity of the order item must be numeric'),'error');
	}
	if (filter_number_format($_POST['Qty'])<0){
		$AllowUpdate = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The quantity of the ordered item entered must be a positive amount'),'error');
	}
	if (!is_numeric(filter_number_format($_POST['Price']))){
		$AllowUpdate = false;
		prnMsg( __('Cannot Enter this order line') . '<br />' . __('The price entered must be numeric'),'error');
	}
	if (!Is_Date($_POST['ReqDelDate'])){
		$AllowUpdate = False;
		prnMsg( __('Cannot Enter this order line') . '</b><br />' . __('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}

 /*It's not a stock item
  * need to check GL Code is valid if GLLink is active
  * [icedlava] GL Code is required for non stock item variance in price vs purchase order when supplier invoice generated even if stock not linked to GL, but AP is else
  * there will be an sql error  in SupplierInvoice.php without a valid GL Code
	*/
	if ($_SESSION['PO'.$identifier]->GLLink==1 OR $_SESSION['CompanyRecord']['gllink_creditors']==1){

		$SQL = "SELECT accountname
				FROM chartmaster
				WHERE accountcode ='" . $_POST['GLCode'] . "'";
		$ErrMsg =  __('The account details for') . ' ' . $_POST['GLCode'] . ' ' . __('could not be retrieved');
		$GLValidResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($GLValidResult) == 0) { /*The GLCode entered does not exist */
			$AllowUpdate = false;
			prnMsg( __('Cannot enter this order line') . ':<br />' . __('The general ledger code') . ' - ' . $_POST['GLCode'] . ' ' . __('is not a general ledger code that is defined in the chart of accounts') . ' . ' . __('Please use a code that is already defined') . '. ' . __('See the Chart list from the link below'),'error');
		} else {
			$MyRow = DB_fetch_row($GLValidResult);
			$GLAccountName = $MyRow[0];
		}
	} /* dont bother checking the GL Code if there is no GL code to check ie not linked to GL */
	else {
		$_POST['GLCode']=0;
	}
	if ($_POST['AssetID'] !='Not an Asset'){
		$ValidAssetResult = DB_query("SELECT assetid,
											description,
											costact
										FROM fixedassets
										INNER JOIN fixedassetcategories
											ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
										WHERE assetid='" . $_POST['AssetID'] . "'");
		if (DB_num_rows($ValidAssetResult)==0){ // then the asset id entered doesn't exist
			$AllowUpdate = false;
			prnMsg(__('An asset code was entered but it does not yet exist. Only pre-existing asset ids can be entered when ordering a fixed asset'),'error');
		} else {
			$AssetRow = DB_fetch_array($ValidAssetResult);
			$_POST['GLCode'] = $AssetRow['costact'];
			if ($_POST['ItemDescription']==''){
				$_POST['ItemDescription'] = $AssetRow['description'];
			}
		}
	} /*end if an AssetID is entered */
	else {
		$_POST['AssetID'] = 0; // cannot commit a string to an integer field so make it 0 if AssetID = 'Not an Asset'
	}
	if (mb_strlen($_POST['ItemDescription'])<=3){
		$AllowUpdate = false;
		prnMsg(__('Cannot enter this order line') . ':<br />' . __('The description of the item being purchased is required where a non-stock item is being ordered'),'warn');
	}

	if ($AllowUpdate == true){
	//adding the non-stock item

		$_SESSION['PO'.$identifier]->add_to_order($_SESSION['PO'.$identifier]->LinesOnOrder+1,
												'',
												0, /*Serialised */
												0, /*Controlled */
												filter_number_format($_POST['Qty']),
												$_POST['ItemDescription'],
												filter_number_format($_POST['Price']),
												$_POST['SuppliersUnit'],
												$_POST['GLCode'],
												$_POST['ReqDelDate'],
												'',
												0,
												'',
												0,
												0,
												$GLAccountName,
												2,
												$_POST['SuppliersUnit'],
												1,
												1,
												'',
												$_POST['AssetID']);
		include('includes/PO_UnsetFormVbls.php');
	}
}
 /*end if Enter line button was hit - adding non stock items */

//Add variables $_SESSION['PO_ItemsResubmitForm' . $identifier] and $_POST['PO_ItemsResubmitFormValue'] to prevent from page refreshing effect

$_SESSION['PO_ItemsResubmitForm' . $identifier] = (empty($_SESSION['PO_ItemsResubmitForm' . $identifier]))? '1' : $_SESSION['PO_ItemsResubmitForm' . $identifier];
if (isset($_POST['NewItem'])
	AND !empty($_POST['PO_ItemsResubmitFormValue'])
	AND $_SESSION['PO_ItemsResubmitForm' . $identifier] == $_POST['PO_ItemsResubmitFormValue']){ //only submit values can be processed

	/* NewItem is set from the part selection list as the part code selected
	* take the form entries and enter the data from the form into the PurchOrder class variable
	* A series of form variables of the format "NewQty" with the ItemCode concatenated are created on the search for adding new
	* items for each of these form variables need to parse out the item code and look up the details to add them to the purchase
	* order  $_POST is of course the global array of all posted form variables
	*/

	foreach ($_POST as $FormVariableName => $Quantity) {
		/*The form entity name is of the format NewQtyX where X is the index number that identifies the stock item code held in the hidden StockIDX form variable
		 * */
		if (mb_substr($FormVariableName, 0, 6)=='NewQty' AND filter_number_format($Quantity)!=0) { //if the form variable represents a Qty to add to the order

			$ItemCode = $_POST['StockID' . mb_substr($FormVariableName, 6)];
			$AlreadyOnThisOrder = 0;

			if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
				if (count($_SESSION['PO'.$identifier]->LineItems)!=0){

					foreach ($_SESSION['PO'.$identifier]->LineItems AS $OrderItem) {

					/* do a loop round the items on the order to see that the item is not already on this order */
						if (($OrderItem->StockID == $ItemCode) AND ($OrderItem->Deleted==false)) {
							$AlreadyOnThisOrder = 1;
							prnMsg( __('The item') . ' ' . $ItemCode . ' ' . __('is already on this order') . '. ' . __('The system will not allow the same item on the order more than once') . '. ' . __('However you can change the quantity ordered of the existing line if necessary'),'error');
						}
					} /* end of the foreach loop to look for preexisting items of the same code */
				}
			}
			if ($AlreadyOnThisOrder!=1 AND filter_number_format($Quantity) > 0){
				$SQL = "SELECT description,
							longdescription,
							stockid,
							units,
							decimalplaces,
							stockact,
							accountname
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockcategory.categoryid = stockmaster.categoryid
						INNER JOIN chartmaster
							ON chartmaster.accountcode = stockcategory.stockact
						WHERE stockmaster.stockid = '". $ItemCode . "'";

				$ErrMsg = __('The item details for') . ' ' . $ItemCode . ' ' . __('could not be retrieved because');
				$ItemResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($ItemResult)==1){
					$ItemRow = DB_fetch_array($ItemResult);

					$SQL = "SELECT price,
								conversionfactor,
								supplierdescription,
								suppliersuom,
								suppliers_partno,
								leadtime,
								MAX(purchdata.effectivefrom) AS latesteffectivefrom
							FROM purchdata
							WHERE purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
								AND purchdata.effectivefrom <= CURRENT_DATE
								AND purchdata.stockid = '". $ItemCode . "'
							GROUP BY purchdata.price,
								purchdata.conversionfactor,
								purchdata.supplierdescription,
								purchdata.suppliersuom,
								purchdata.suppliers_partno,
								purchdata.leadtime
							ORDER BY latesteffectivefrom DESC";

					$ErrMsg = __('The purchasing data for') . ' ' . $ItemCode . ' ' . __('could not be retrieved because');
					$PurchDataResult = DB_query($SQL, $ErrMsg);
					if (DB_num_rows($PurchDataResult)>0){ //the purchasing data is set up
						$PurchRow = DB_fetch_array($PurchDataResult);

						/* Now to get the applicable discounts */
						$SQL = "SELECT discountpercent,
										discountamount
								FROM supplierdiscounts
								WHERE supplierno= '" . $_SESSION['PO'.$identifier]->SupplierID . "'
									AND effectivefrom <= CURRENT_DATE
									AND effectiveto >= CURRENT_DATE
									AND stockid = '". $ItemCode . "'";

						$ItemDiscountPercent = 0;
						$ItemDiscountAmount = 0;
						$ErrMsg = __('Could not retrieve the supplier discounts applicable to the item');
						$DiscountResult = DB_query($SQL, $ErrMsg);
						while ($DiscountRow = DB_fetch_array($DiscountResult)) {
							$ItemDiscountPercent += $DiscountRow['discountpercent'];
							$ItemDiscountAmount += $DiscountRow['discountamount'];
						}
						if ($ItemDiscountPercent != 0) {
							prnMsg(__('Taken accumulated supplier percentage discounts of') .  ' ' . locale_number_format($ItemDiscountPercent*100,2) . '%','info');
						}
						if ($ItemDiscountAmount != 0 ){
							prnMsg(__('Taken accumulated round sum supplier discount of') .  ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) . ' (' . __('per supplier unit') . ')','info');
						}
						$PurchPrice = ($PurchRow['price']*(1-$ItemDiscountPercent) - $ItemDiscountAmount)/$PurchRow['conversionfactor'];
						$ConversionFactor = $PurchRow['conversionfactor'];
						if (mb_strlen($PurchRow['supplierdescription'])>2){
							$SupplierDescription = $PurchRow['supplierdescription'];
						} else {
							$SupplierDescription = $ItemRow['description'];
						}
						$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
						$SuppliersPartNo = $PurchRow['suppliers_partno'];
						$LeadTime = $PurchRow['leadtime'];
						/* Work out the delivery date based on today + lead time
					 * if > header DeliveryDate then set DeliveryDate to today + leadtime
				        */
						$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$LeadTime);
						if (Date1GreaterThanDate2($_SESSION['PO'.$identifier]->DeliveryDate,$DeliveryDate)){
							$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
						}
					} else { // no purchasing data setup
						$PurchPrice = 0;
						$ConversionFactor = 1;
						$SupplierDescription = 	$ItemRow['description'];
						$SuppliersUnitOfMeasure = $ItemRow['units'];
						$SuppliersPartNo = '';
						$LeadTime=1;
						if (!isset($_SESSION['PO'.$identifier]->DeliveryDate)){
							$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',1);
						} else {
							$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
						}
					}

					$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
															$ItemCode,
															0, /*Serialised */
															0, /*Controlled */
															filter_number_format($Quantity)*$ConversionFactor, /* Qty */
															$SupplierDescription,
															$PurchPrice,
															$ItemRow['units'],
															$ItemRow['stockact'],
															$DeliveryDate,
															0,
															0,
															0,
															0,
															0,
															$ItemRow['accountname'],
															$ItemRow['decimalplaces'],
															$SuppliersUnitOfMeasure,
															$ConversionFactor,
															$LeadTime,
															$SuppliersPartNo);
				} else { //no rows returned by the SQL to get the item
					prnMsg(__('The item code') . ' ' . $ItemCode . ' ' . __('does not exist in the database and therefore cannot be added to the order'),'error');
					include('includes/footer.php');
					exit();
				}
			} /* end of if not already on the order */
		} /* end if the $_POST has NewQty in the variable name */
	} /* end loop around the $_POST array */
	$_SESSION['PO_ItemsResubmitForm' . $identifier]++; //change the $_SESSION VALUE
} /* end of if its a new item */

if (isset($_POST['UploadFile'])) {
	if (isset($_FILES['CSVFile']) && $_FILES['CSVFile']['name']) {
		//check file info
		$FileName = $_FILES['CSVFile']['name'];
		$TempName = $_FILES['CSVFile']['tmp_name'];
		$FileSize = $_FILES['CSVFile']['size'];
		//get file handle
		$FileHandle = fopen($TempName, 'r');
		$Row = 0;
		$InsertNum = 0;

		while (($FileRow = fgetcsv($FileHandle, 10000, ",")) !== False) {
			++$Row;
			if (filter_number_format($FileRow[1])!=0) { //if the form variable represents a Qty to add to the order

				$ItemCode = $FileRow[0];
				$Quantity = $FileRow[1];
				$AlreadyOnThisOrder = 0;

				if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
					if (count($_SESSION['PO'.$identifier]->LineItems)!=0){

						foreach ($_SESSION['PO'.$identifier]->LineItems AS $OrderItem) {

						/* do a loop round the items on the order to see that the item is not already on this order */
							if (($OrderItem->StockID == $ItemCode) AND ($OrderItem->Deleted==false)) {
								$AlreadyOnThisOrder = 1;
								prnMsg( __('The item') . ' ' . $ItemCode . ' ' . __('is already on this order') . '. ' . __('The system will not allow the same item on the order more than once') . '. ' . __('However you can change the quantity ordered of the existing line if necessary'),'error');
							}
						} /* end of the foreach loop to look for preexisting items of the same code */
					}
				}
				if ($AlreadyOnThisOrder!=1 AND filter_number_format($Quantity) > 0){
					$SQL = "SELECT description,
								longdescription,
								stockid,
								units,
								decimalplaces,
								stockact,
								accountname
							FROM stockmaster
							INNER JOIN stockcategory
								ON stockcategory.categoryid = stockmaster.categoryid
							INNER JOIN chartmaster
								ON chartmaster.accountcode = stockcategory.stockact
							WHERE  stockmaster.stockid = '". $ItemCode . "'";

					$ErrMsg = __('The item details for') . ' ' . $ItemCode . ' ' . __('could not be retrieved because');
					$ItemResult = DB_query($SQL, $ErrMsg);
					if (DB_num_rows($ItemResult)==1){
						$ItemRow = DB_fetch_array($ItemResult);

						$SQL = "SELECT price,
									conversionfactor,
									supplierdescription,
									suppliersuom,
									suppliers_partno,
									leadtime,
									MAX(purchdata.effectivefrom) AS latesteffectivefrom
								FROM purchdata
								WHERE purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
									AND purchdata.effectivefrom <= CURRENT_DATE
									AND purchdata.stockid = '". $ItemCode . "'
								GROUP BY purchdata.price,
									purchdata.conversionfactor,
									purchdata.supplierdescription,
									purchdata.suppliersuom,
									purchdata.suppliers_partno,
									purchdata.leadtime
								ORDER BY latesteffectivefrom DESC";

						$ErrMsg = __('The purchasing data for') . ' ' . $ItemCode . ' ' . __('could not be retrieved because');
						$PurchDataResult = DB_query($SQL, $ErrMsg);
						if (DB_num_rows($PurchDataResult)>0){ //the purchasing data is set up
							$PurchRow = DB_fetch_array($PurchDataResult);

							/* Now to get the applicable discounts */
							$SQL = "SELECT discountpercent,
											discountamount
									FROM supplierdiscounts
									WHERE supplierno= '" . $_SESSION['PO'.$identifier]->SupplierID . "'
										AND effectivefrom <= CURRENT_DATE
										AND effectiveto >= CURRENT_DATE
										AND stockid = '". $ItemCode . "'";

							$ItemDiscountPercent = 0;
							$ItemDiscountAmount = 0;
							$ErrMsg = __('Could not retrieve the supplier discounts applicable to the item');
							$DiscountResult = DB_query($SQL, $ErrMsg);
							while ($DiscountRow = DB_fetch_array($DiscountResult)) {
								$ItemDiscountPercent += $DiscountRow['discountpercent'];
								$ItemDiscountAmount += $DiscountRow['discountamount'];
							}
							if ($ItemDiscountPercent != 0) {
								prnMsg(__('Taken accumulated supplier percentage discounts of') .  ' ' . locale_number_format($ItemDiscountPercent*100,2) . '%','info');
							}
							if ($ItemDiscountAmount != 0 ){
								prnMsg(__('Taken accumulated round sum supplier discount of') .  ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) . ' (' . __('per supplier unit') . ')','info');
							}
							$PurchPrice = ($PurchRow['price']*(1-$ItemDiscountPercent) - $ItemDiscountAmount)/$PurchRow['conversionfactor'];
							$ConversionFactor = $PurchRow['conversionfactor'];
							if (mb_strlen($PurchRow['supplierdescription'])>2){
								$SupplierDescription = $PurchRow['supplierdescription'];
							} else {
								$SupplierDescription = $ItemRow['description'];
							}
							$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
							$SuppliersPartNo = $PurchRow['suppliers_partno'];
							$LeadTime = $PurchRow['leadtime'];
							/* Work out the delivery date based on today + lead time
						 * if > header DeliveryDate then set DeliveryDate to today + leadtime
							*/
							$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$LeadTime);
							if (Date1GreaterThanDate2($_SESSION['PO'.$identifier]->DeliveryDate,$DeliveryDate)){
								$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
							}
						} else { // no purchasing data setup
							$PurchPrice = 0;
							$ConversionFactor = 1;
							$SupplierDescription = 	$ItemRow['description'];
							$SuppliersUnitOfMeasure = $ItemRow['units'];
							$SuppliersPartNo = '';
							$LeadTime=1;
							$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
						}

						$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
																$ItemCode,
																0, /*Serialised */
																0, /*Controlled */
																filter_number_format($Quantity)*$ConversionFactor, /* Qty */
																$SupplierDescription,
																$PurchPrice,
																$ItemRow['units'],
																$ItemRow['stockact'],
																$DeliveryDate,
																0,
																0,
																0,
																0,
																0,
																$ItemRow['accountname'],
																$ItemRow['decimalplaces'],
																$SuppliersUnitOfMeasure,
																$ConversionFactor,
																$LeadTime,
																$SuppliersPartNo);
						++$InsertNum;
					} else { //no rows returned by the SQL to get the item
						prnMsg(__('The item code') . ' ' . $ItemCode . ' ' . __('does not exist in the database and therefore cannot be added to the order'),'error');
					}
				} /* end of if not already on the order */
			} /* end if the $_POST has NewQty in the variable name */
		}
	}
	$_SESSION['PO_ItemsResubmitForm' . $identifier]++; //change the $_SESSION VALUE
	prnMsg($InsertNum . ' ' . __('of') . ' ' . $Row . ' ' . __('rows have been added to the order'), 'info');
} /* end of if its items uploaded from csv */


/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo '<form id="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post" enctype="multipart/form-data">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*need to set up entry for item description where not a stock item and GL Codes */

if (count($_SESSION['PO'.$identifier]->LineItems)>0 and !isset($_GET['Edit'])){
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' .
		__('Purchase Order') . '" alt="" />  '.$_SESSION['PO'.$identifier]->SupplierName;

	if (isset($_SESSION['PO'.$identifier]->OrderNo)) {
		echo  ' ' . __('Purchase Order') .' '. $_SESSION['PO'.$identifier]->OrderNo ;
	}
	echo '<br /><b>', __('Order Summary'), '</b></p>';
	echo '<table cellpadding="2" class="selection">
		<thead>
			<tr>
			<th class="SortedColumn">' . __('Item Code') . '</th>
			<th class="SortedColumn">' . __('Description') . '</th>
			<th class="SortedColumn">' . __('Quantity Our Units') . '</th>
			<th>' . __('Our Unit')  . '</th>
			<th class="SortedColumn">' . __('Price Our Units') .' (' . $_SESSION['PO'.$identifier]->CurrCode .  ')</th>
			<th>' . __('Unit Conversion Factor') . '</th>
			<th class="SortedColumn">' . __('Order Quantity') . '<br />' . __('Supplier Units') . '</th>
			<th>' .  __('Supplier Unit') . '</th>
			<th class="SortedColumn">' . __('Order Price') . '<br />' . __('Supp Units') . ' ('.$_SESSION['PO'.$identifier]->CurrCode.  ')</th>
			<th class="SortedColumn">' . __('Sub-Total') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ')</th>
			<th class="SortedColumn">' . __('Deliver By')  . '</th>
			</tr>
		</thead>
		<tbody>';

	$_SESSION['PO'.$identifier]->Total = 0;

	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {

		if ($POLine->Deleted==False) {
			$LineTotal = $POLine->Quantity * $POLine->Price;
			$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
			// Note if the price is greater than 1 use 2 decimal place, if the price is a fraction of 1, use 4 decimal places
			// This should help display where item-price is a fraction
			if ($POLine->Price > 1) {
				$DisplayPrice = locale_number_format($POLine->Price,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
				$SuppPrice = locale_number_format(round(($POLine->Price *$POLine->ConversionFactor),$_SESSION['PO'.$identifier]->CurrDecimalPlaces),$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
			} else {
				$DisplayPrice = locale_number_format($POLine->Price,($_SESSION['PO'.$identifier]->CurrDecimalPlaces + 2));
				$SuppPrice = locale_number_format(round(($POLine->Price *$POLine->ConversionFactor),($_SESSION['PO'.$identifier]->CurrDecimalPlaces+2)),($_SESSION['PO'.$identifier]->CurrDecimalPlaces+2));
			}

			echo '<tr class="striped_row">
				<td>' . $POLine->StockID  . '</td>
                <td><input type="text" name="ItemDescription' . $POLine->LineNo.'" size="30" value="' . stripslashes($POLine->ItemDescription) . '" /></td>
				<td class="number">' . locale_number_format($POLine->Quantity,$POLine->DecimalPlaces) . '</td>
				<td>' . $POLine->Units . '</td>
				<td class="number">' . $DisplayPrice . '</td>
				<td><input type="text" class="number" name="ConversionFactor' . $POLine->LineNo .'" size="8" value="' . locale_number_format($POLine->ConversionFactor,'Variable') . '" /></td>
				<td><input type="text" class="number" name="SuppQty' . $POLine->LineNo .'" size="10" value="' . locale_number_format(round($POLine->Quantity/$POLine->ConversionFactor,$POLine->DecimalPlaces),$POLine->DecimalPlaces) . '" /></td>
				<td>' . $POLine->SuppliersUnit . '</td>
				<td><input type="text" class="number" name="SuppPrice' . $POLine->LineNo . '" size="10" value="' . $SuppPrice .'" /></td>
				<td class="number">' . $DisplayLineTotal . '</td>
				<td><input type="date" name="ReqDelDate' . $POLine->LineNo.'" size="10" value="' .FormatDateForSQL($POLine->ReqDelDate) .'" /></td>';
			if ($POLine->QtyReceived !=0 AND $POLine->Completed!=1){
				echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Complete=' . $POLine->LineNo . '">' . __('Complete') . '</a></td>';
			} elseif ($POLine->QtyReceived ==0) {
				echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Delete=' . $POLine->LineNo . '">' . __('Delete'). '</a></td>';
			}
			echo '</tr>';
			$_SESSION['PO'.$identifier]->Total += $LineTotal;
		}
	}

	$DisplayTotal = locale_number_format($_SESSION['PO'.$identifier]->Total,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
	echo '</tbody>
		<tfoot>
			<tr>',
/*				'<td colspan="9" class="number">' . __('TOTAL') . __(' excluding Tax') . '</td>',*/
				'<td class="number" colspan="9">', __('Total Excluding Tax'), '</td>',
				'<td class="number"><b>', $DisplayTotal, '</b></td>
			</tr>
		</tfoot>
		</table>
		<br />
			<div class="centre">
			<input type="submit" name="UpdateLines" value="' . __('Update Order Lines') . '" />
			&nbsp;<input type="submit" name="Commit" value="' . __('Process Order') . '" />
			</div>';

} /*Only display the order line items if there are any !! */


if (isset($_POST['NonStockOrder'])) {

	echo '<br /><table class="selection"><tr>
				<td>' . __('Item Description') . '</td>';
	echo '<td><input type="text" name="ItemDescription" size="40" /></td></tr>';
	echo '<tr>
			<td>' . __('General Ledger Code') . '</td>
			<td><select name="GLCode">';
	$SQL="SELECT accountcode,
				  accountname
				FROM chartmaster
				ORDER BY accountcode ASC";

	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)) {
		echo '<option value="'.$MyRow['accountcode'].'">' . $MyRow['accountcode'].' - '.$MyRow['accountname'] . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr>
			<td>' . __('OR Asset ID'). '</td>
			<td><select name="AssetID">';
	$AssetsResult = DB_query("SELECT assetid,
									description,
									datepurchased
								FROM fixedassets
								ORDER BY assetid DESC");
	echo '<option selected="selected" value="Not an Asset">' . __('Not an Asset') . '</option>';
	while ($AssetRow = DB_fetch_array($AssetsResult)){
		if ($AssetRow['datepurchased']=='1000-01-01'){
			$DatePurchased = __('Not yet purchased');
		} else {
			$DatePurchased = ConvertSQLDate($AssetRow['datepurchased']);
		}
		echo '<option value="' . $AssetRow['assetid'] . '">'  . $AssetRow['assetid'] . ' - '.  $DatePurchased . ' - ' . $AssetRow['description'] . '</option>';
	}

	echo'</select><a href="FixedAssetItems.php" target=_blank>' .  __('New Fixed Asset') . '</a></td></tr>
		<tr>
			<td>' . __('Quantity to purchase') . '</td>
			<td><input type="text" class="number" name="Qty" size="10" value="1" /></td>
		</tr>
		<tr>
			<td>' . __('Price per item') . '</td>
			<td><input type="text" class="number" name="Price" size="10" /></td>
		</tr>
		<tr>
			<td>' . __('Unit') . '</td>
			<td><input type="text" name="SuppliersUnit" size="10" value="' . __('each') . '" /></td>
		</tr>
		<tr>
			<td>' . __('Delivery Date') . '</td>
			<td><input type="date" name="ReqDelDate" size="11" value="'.$_SESSION['PO'.$identifier]->DeliveryDate .'" /></td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="EnterLine" value="' . __('Enter Item') . '" />
		</div>';
}

/* Now show the stock item selection search stuff below */
if (isset($_POST['Search']) OR isset($_POST['Prev']) OR isset($_POST['Next'])){  /*ie seach for stock items */

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( __('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
			if ($_POST['SupplierItemsOnly']=='on'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'G'
							AND stockmaster.discontinued<>1
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.description " . LIKE . " '" . $SearchString ."'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid";
			} else { // not just supplier purchdata items
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.description " . LIKE . " '" . $SearchString ."'
					ORDER BY stockmaster.stockid ";
			}
		} else { //for a specific stock category
			if ($_POST['SupplierItemsOnly']=='on'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'G'
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.discontinued<>1
							AND stockmaster.description " . LIKE . " '". $SearchString ."'
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
							WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'G'
							AND stockmaster.discontinued<>1
							AND stockmaster.description " . LIKE . " '". $SearchString ."'
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid ";
			}
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			if ($_POST['SupplierItemsOnly']=='on'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'G'
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.discontinued<>1
							AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid ";
			}
		} else { //for a specific stock category and LIKE stock code
			if ($_POST['SupplierItemsOnly']=='on'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'G'
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.discontinued<>1
							AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
			}
		}

	} else {
		if ($_POST['StockCat']=='All'){
			if (isset($_POST['SupplierItemsOnly'])){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'G'
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.discontinued<>1
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
					ORDER BY stockmaster.stockid ";
			}
		} else { // for a specific stock category
			if (isset($_POST['SupplierItemsOnly']) AND $_POST['SupplierItemsOnly']=='on'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
							ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
							AND stockmaster.mbflag<>'A'
							AND stockmaster.mbflag<>'K'
							AND stockmaster.mbflag<>'G'
							AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND stockmaster.discontinued<>1
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
			}
		}
	}

	$SQLCount = substr($SQL,strpos($SQL,   "FROM"));
	$SQLCount = substr($SQLCount,0, strpos($SQLCount,   "ORDER"));
	$SQLCount = 'SELECT COUNT(*) '.$SQLCount;
	$ErrMsg = __('Failed to retrieve result count');
	$SearchResult = DB_query($SQLCount, $ErrMsg);
	$MyRow=DB_fetch_array($SearchResult);
	DB_free_result($SearchResult);
	unset($SearchResult);
	$ListCount = $MyRow[0];
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax'])-1;
	if ($ListPageMax < 0) {
		$ListPageMax = 0;
	}
	if (isset($_POST['Next'])) {
		$Offset = $_POST['currpage']+1;
	}
	if (isset($_POST['Prev'])) {
		$Offset = $_POST['currpage']-1;
	}
	if (!isset($Offset)) {
		$Offset = 0;
	}
	if($Offset < 0){
		$Offset = 0;
	}
	if($Offset > $ListPageMax) {
		$Offset = $ListPageMax;
	}

	$SQL = $SQL . " LIMIT " . $_SESSION['DisplayRecordsMax']." OFFSET " . strval($_SESSION['DisplayRecordsMax']*$Offset);

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult)==0){
		prnMsg( __('There are no products to display matching the criteria provided'),'warn');
	}
	if (DB_num_rows($SearchResult)==1){

		$MyRow=DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult,0);
	}

} //end of if search

if (!isset($_GET['Edit'])) {
	$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype<>'D'
			ORDER BY categorydescription";
	$ErrMsg = __('The supplier category details could not be retrieved because');
	$Result1 = DB_query($SQL, $ErrMsg);

	echo '<fieldset>
			<legend>' .  __('Search For Stock Items') . ':</legend>';

	echo '<field>
			<label for="StockCat">' . __('Item Category') . ':</label>
			<select name="StockCat">

			<option selected="selected" value="All">' . __('All') . '</option>';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat']==$MyRow1['categoryid']){
			echo '<option selected="selected" value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}

	unset($_POST['Keywords']);
	unset($_POST['StockCode']);

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords']='';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode']='';
	}

	if (isset($_POST['SupplierItemsOnly'])) {
		$Checked = 'checked';
	} else {
		$Checked = '';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label from="Keywords">' . __('Enter text extracts in the description') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" value="' . $_POST['Keywords'] . '" />
		</field>
		<field>
			<label for="SupplierItemsOnly">' . __('Only items defined as from this Supplier') . '</label>
			<input type="checkbox" ' . $Checked . ' name="SupplierItemsOnly" />
		</field>
		<field>
			<label for="StockCode"><b>' . __('OR') . ' </b>' . __('Enter extract of the Stock Code') . ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" />
		</field>
		<field>
			<label><b>' . __('OR') . ' </b>', __('Create Stock Item'), '</label>
			<a target="_blank" href="'.$RootPath.'/Stocks.php">' . __('Insert New Item') . '</a>
		</field>
		<field>
			<label for="UploadFile"><b>' . __('OR') . ' </b>' . __('Upload items from csv file') . '</label>
			<input type="file" name="CSVFile" />
			<input type="submit" name="UploadFile" value="' . __('Upload File') . '" />
		</field>		</fieldset>

		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
			<input type="submit" name="NonStockOrder" value="' . __('Order a non stock item') . '" />
		</div>';

	$PartsDisplayed =0;
}

if (isset($SearchResult)) {
	$PageBar = '<tr><td><input type="hidden" name="currpage" value="'.$Offset.'">';
	if($Offset>0)
		$PageBar .= '<input type="submit" name="Prev" value="'.__('Prev').'" />';
	else
		$PageBar .= '<input type="submit" name="Prev" value="'.__('Prev').'" disabled="disabled"/>';
	$PageBar .= '</td><td class="centre" colspan="4"><input type="submit" value="'.__('Order some').'" name="NewItem"/></td><td>';
	if($Offset<$ListPageMax)
		$PageBar .= '<input type="submit" name="Next" value="'.__('Next').'" />';
	else
		$PageBar .= '<input type="submit" name="Next" value="'.__('Next').'" disabled="disabled"/>';
	$PageBar .= '</td></tr>';

	echo '<table cellpadding="1" class="selection">';
	echo $PageBar;
	$TableHeader = '<tr>
						<th class="SortedColumn">' . __('Code')  . '</th>
						<th class="SortedColumn">' . __('Description') . '</th>
						<th>' . __('Our Units') . '</th>
						<th>' . __('Conversion') . '<br />' .__('Factor') . '</th>
						<th>' . __('Supplier/Order') . '<br />' .  __('Units') . '</th>
						<th colspan="2"><a href="#end">' . __('Go to end of list') . '</a></th>
					</tr>';
	echo $TableHeader;

	$j = 1;

	while ($MyRow=DB_fetch_array($SearchResult)) {

		$SupportedImgExt = array('png','jpg','jpeg');

		$ImageFilearray = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
		$ImageFile = reset($ImageFilearray);
		$ImageSource = GetImageLink($ImageFile, $MyRow['stockid'], 64, 64, "", "");

		/*Get conversion factor and supplier units if any */
		$SQL =  "SELECT purchdata.conversionfactor,
						purchdata.suppliersuom
					FROM purchdata
					WHERE purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND purchdata.stockid='" . $MyRow['stockid'] . "'";
		$ErrMsg = __('Could not retrieve the purchasing data for the item');
		$PurchDataResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($PurchDataResult)>0) {
			$PurchDataRow = DB_fetch_array($PurchDataResult);
			$OrderUnits = $PurchDataRow['suppliersuom'];
			$ConversionFactor = locale_number_format($PurchDataRow['conversionfactor'], 'Variable');
		} else {
			$OrderUnits = $MyRow['units'];
			$ConversionFactor = 1;
		}
		echo '<tr class="striped_row">
			<td>' . $MyRow['stockid']  . '</td>
			<td>' . $MyRow['description']  . '</td>
			<td>' . $MyRow['units']  . '</td>
			<td class="number">' . $ConversionFactor  . '</td>
			<td>' . $OrderUnits . '</td>
			<td>' . $ImageSource . '</td>
			<td><input class="number" type="text" size="6" value="0" name="NewQty' . $j . '" /></td>
			<input type="hidden" name="StockID' . $j . '" value="' . $MyRow['stockid'] . '" />
			</tr>';
		$j++;
		$PartsDisplayed++;
#end of page full new headings if
	}

	echo $PageBar;
#end of while loop
	echo '</table>';
	echo '<input type="hidden" name="PO_ItemsResubmitFormValue" value="' . $_SESSION['PO_ItemsResubmitForm' . $identifier] . '" />';
	echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="NewItem" value="' . __('Order some') . '" /></div>';
}#end if SearchResults to show

echo '</div>
      </form>';
include('includes/footer.php');
