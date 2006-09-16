<?php

/* $Revision: 1.14 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */


$PageSecurity = 5;

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
$title = _('Enter Supplier Invoice Against Goods Received');
include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice must be clicked on'),'info');
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier to Enter a Transaction For') . '</A>';
	include('includes/footer.inc');
	exit;
	/*It all stops here if there aint no supplier selected and invoice initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Invoice button then process this first before showing  all GRNs on the invoice 
otherwise it wouldnt show the latest additions*/
if (isset($_POST['AddPOToTrans']) AND $_POST['AddPOToTrans']!=''){
    foreach($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
        if ($_POST['AddPOToTrans']==$GRNTmp->PONo) {
		    $_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
		    $_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
        }
    }
}

if (isset($_POST['AddGRNToTrans'])){ /*adding a GRN to the invoice */
    foreach($_SESSION['SuppTransTmp']->GRNs as $GRNTmp) {
        $Selected = $_POST['GRNNo_' . $GRNTmp->GRNNo];
        if ($Selected==True) {
		    $_SESSION['SuppTrans']->Copy_GRN_To_Trans($GRNTmp);
		    $_SESSION['SuppTransTmp']->Remove_GRN_From_Trans($GRNTmp->GRNNo);
        }
    }
}

if (isset($_POST['ModifyGRN'])){

	$InputError=False;

	if ($_POST['This_QuantityInv'] >= ($_POST['QtyRecd'] - $_POST['Prev_QuantityInv'])){
		$Complete = True;
	} else {
		$Complete = False;
	}
	if ($_SESSION['Check_Qty_Charged_vs_Del_Qty']==True) {
		if (($_POST['This_QuantityInv']+ $_POST['Prev_QuantityInv'])/($_POST['QtyRecd'] ) > (1+ ($_SESSION['OverChargeProportion'] / 100))){
			prnMsg(_('The quantity being invoiced is more than the outstanding quantity by more than') . ' ' . $_SESSION['OverChargeProportion'] . ' ' . _('percent. The system is set up to prohibit this. See the system administrator to modify the set up parameters if necessary'),'error');
			$InputError = True;
		}
	}
	if (!is_numeric($_POST['ChgPrice']) AND $_POST['ChgPrice']<0){
		$InputError = True;
		prnMsg(_('The price charged in the suppliers currency is either not numeric or negative') . '. ' . _('The goods received cannot be invoiced at this price'),'error');
	} elseif ($_SESSION['Check_Price_Charged_vs_Order_Price'] == True) {
		if ($_POST['ChgPrice']/$_POST['OrderPrice'] > (1+ ($_SESSION['OverChargeProportion'] / 100))){
			prnMsg(_('The price being invoiced is more than the purchase order price by more than') . ' ' . $_SESSION['OverChargeProportion'] . '%. ' . _('The system is set up to prohibit this') . '. ' . _('See the system administrator to modify the set up parameters if necessary'),'error');
			$InputError = True;
		}
	}

	if ($InputError==False){
//        $_SESSION['SuppTrans']->Remove_GRN_From_Trans($_POST['GRNNumber']);
        $_SESSION['SuppTrans']->Modify_GRN_To_Trans($_POST['GRNNumber'],
							$_POST['PODetailItem'],
							$_POST['ItemCode'],
							$_POST['ItemDescription'],
							$_POST['QtyRecd'],
							$_POST['Prev_QuantityInv'],
							$_POST['This_QuantityInv'],
							$_POST['OrderPrice'],
							$_POST['ChgPrice'],
							$Complete,
							$_POST['StdCostUnit'],
							$_POST['ShiptRef'],
							$_POST['JobRef'],
							$_POST['GLCode']);
	}
}

if (isset($_GET['Delete'])){
	$_SESSION['SuppTransTmp']->Copy_GRN_To_Trans($_SESSION['SuppTrans']->GRNs[$_GET['Delete']]);
	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);
}


/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Invoiced Goods Received Selected');
echo '<TABLE CELLPADDING=1>';

$tableheader = "<TR BGCOLOR=#800000>
			<TD class='tableheader'>" . _('Sequence') . " #</TD>
			<TD class='tableheader'>" . _('Item Code') . "</TD>
			<TD class='tableheader'>" . _('Description') . "</TD>
			<TD class='tableheader'>" . _('Quantity Charged') . "</TD>
			<TD class='tableheader'>" . _('Price Charge in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</TD>
			<TD class='tableheader'>" . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD></TR>';

echo $tableheader;

$TotalValueCharged=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

	echo '<TR><TD>' . $EnteredGRN->GRNNo . '</TD>
		<TD>' . $EnteredGRN->ItemCode . '</TD>
		<TD>' . $EnteredGRN->ItemDescription . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredGRN->This_QuantityInv,2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredGRN->ChgPrice,2) . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . "</TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Modify=' . $EnteredGRN->GRNNo . "'>". _('Modify') . "</A></TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $EnteredGRN->GRNNo . "'>" . _('Delete') . "</A></TD>
	</TR>";

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $tableheader;
	}
}

echo '<TR>
	<TD COLSPAN=5 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total Value of Goods Charged') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalValueCharged,2) . '</U></FONT></TD>
</TR>';
echo "</TABLE><BR><A HREF='$rootpath/SupplierInvoice.php?" . SID ."'>" . _('Back to Invoice Entry') . '</A><HR>';


/* Now get all the outstanding GRNs for this supplier from the database*/

$SQL = "SELECT grnbatch,
		grnno,
		purchorderdetails.orderno,
		purchorderdetails.unitprice,
		grns.itemcode,
		grns.deliverydate,
		grns.itemdescription,
		grns.qtyrecd,
		grns.quantityinv,
		grns.stdcostunit,
		purchorderdetails.glcode,
		purchorderdetails.shiptref,
		purchorderdetails.jobref,
		purchorderdetails.podetailitem
	FROM grns INNER JOIN purchorderdetails
		ON  grns.podetailitem=purchorderdetails.podetailitem
	WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
	AND grns.qtyrecd - grns.quantityinv > 0
	ORDER BY grns.grnno";
$GRNResults = DB_query($SQL,$db);

if (DB_num_rows($GRNResults)==0){
	prnMsg(_('There are no outstanding goods received from') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . _('that have not been invoiced by them') . '<BR>' . _('The goods must first be received using the link below to select purchase orders to receive'),'error');
	echo "<P><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . 'SupplierID=' . $_SESSION['SuppTrans']->SupplierID ."'>" . _('Select Purchase Orders to receive') .'</A>';
	include('includes/footer.inc');
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

if (!isset( $_SESSION['SuppTransTmp'])){
    $_SESSION['SuppTransTmp'] = new SuppTrans;
    while ($myrow=DB_fetch_array($GRNResults)){

	    $GRNAlreadyOnInvoice = False;

	    foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		    if ($EnteredGRN->GRNNo == $myrow['grnno']) {
			    $GRNAlreadyOnInvoice = True;
		    }
	    }
	    if ($GRNAlreadyOnInvoice == False){
		    $_SESSION['SuppTransTmp']->Add_GRN_To_Trans($myrow['grnno'],
		    						$myrow['podetailitem'],
								$myrow['itemcode'],
								$myrow['itemdescription'],
								$myrow['qtyrecd'],
								$myrow['quantityinv'],
								$myrow['qtyrecd'] - $myrow['quantityinv'],
								$myrow['unitprice'],
								$myrow['unitprice'],
								$Complete,
								$myrow['stdcostunit'],
								$myrow['shiptref'],
								$myrow['jobref'],
								$myrow['glcode'],
								$myrow['orderno']);
	    }
    }
}


//if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=''){
if (isset($_GET['Modify'])){
    $GRNNo = $_GET['Modify'];
    $GRNTmp = $_SESSION['SuppTrans']->GRNs[$GRNNo];

	echo '<P><FONT SIZE=4 COLOR=BLUE><B>' . _('GRN Selected For Adding To A Purchase Invoice') . '</FONT></B>';
	echo "<TABLE>
		<TR BGCOLOR=#800000>
			<TD class='tableheader'>" . _('Sequence') . " #</TD>
			<TD class='tableheader'>" . _('Item') . "</TD>
			<TD class='tableheader'>" . _('Qty Outstanding') . "</TD>
			<TD class='tableheader'>" . _('Qty Invoiced') . "</TD>
			<TD class='tableheader'>" . _('Order Price in') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</TD>
			<TD class='tableheader'>" . _('Actual Price in') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</TD>
		</TR>";

	echo '<TR>
		<TD>' . $GRNTmp->GRNNo . '</TD>
		<TD>' . $GRNTmp->ItemCode . ' ' . $GRNTmp->ItemDescription . '</TD>
		<TD ALIGN=RIGHT>' . number_format($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv,2) . "</TD>
		<TD><INPUT TYPE=Text Name='This_QuantityInv' Value=" . $GRNTmp->This_QuantityInv . ' SIZE=11 MAXLENGTH=10></TD>
		<TD ALIGN=RIGHT>' . $GRNTmp->OrderPrice . '</TD>
		<TD><INPUT TYPE=Text Name="ChgPrice" Value=' . $GRNTmp->ChgPrice . ' SIZE=11 MAXLENGTH=10></TD>
	</TR>';
	echo '</TABLE>';

/*	if ($myrow['closed']==1){ //Shipment is closed so pre-empt problems later by warning the user - need to modify the order first
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value=''>";
		echo "<P>Unfortunately, the shipment that this purchase order line item was allocated to has been closed - if you add this item to the transaction then no shipments will not be updated. If you wish to allocate the order line item to a different shipment the order must be modified first.";
	} else {    */
		echo "<INPUT TYPE=HIDDEN NAME='ShiptRef' Value='" . $GRNTmp->ShiptRef . "'>";
//	}

	echo "<P><INPUT TYPE=Submit Name='ModifyGRN' Value='" . _('Modify Line') . "'>";


	echo "<INPUT TYPE=HIDDEN NAME='GRNNumber' VALUE=" . $GRNTmp->GRNNo . '>';
	echo "<INPUT TYPE=HIDDEN NAME='ItemCode' VALUE='" . $GRNTmp->ItemCode . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ItemDescription' VALUE='" . $GRNTmp->ItemDescription . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='QtyRecd' VALUE=" . $GRNTmp->QtyRecd . ">";
	echo "<INPUT TYPE=HIDDEN NAME='Prev_QuantityInv' VALUE=" . $GRNTmp->Prev_QuantityInv . '>';
	echo "<INPUT TYPE=HIDDEN NAME='OrderPrice' VALUE=" . $GRNTmp->OrderPrice . '>';
	echo "<INPUT TYPE=HIDDEN NAME='StdCostUnit' VALUE=" . $GRNTmp->StdCostUnit . '>';
	echo "<INPUT TYPE=HIDDEN NAME='JobRef' Value='" . $GRNTmp->JobRef . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='GLCode' Value='" . $GRNTmp->GLCode . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='PODetailItem' Value='" . $GRNTmp->PODetailItem . "'>";
}
else {
    if (count( $_SESSION['SuppTransTmp']->GRNs)>0){   /*if there are any outstanding GRNs then */
        echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Goods Received Yet to be Invoiced From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
        echo "<TABLE CELLPADDING=1 COLSPAN=7>";

        $tableheader = "<TR BGCOLOR=#800000><TD class='tableheader'>" . _('Select') . "</TD>
				<TD class='tableheader'>" . _('Sequence') . " #</TD>
				<TD  class='tableheader'>" . _('Order') . "</TD>
				<TD  class='tableheader'>" . _('Item Code') . "</TD>
				<TD class='tableheader'>" . _('Description') . "</TD>
				<TD class='tableheader'>" . _('Total Qty Received') . "</TD>
				<TD class='tableheader'>" . _('Qty Already Invoiced') . "</TD>
				<TD class='tableheader'>" . _('Qty Yet To Invoice') . "</TD>
				<TD class='tableheader'>" . _('Order Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</TD>
				<TD class='tableheader'>" . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD></TR>';

        $i = 0;
        $POs = array();
        foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp){

		$_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;	

		if ($POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
                	$POs[$GRNTmp->PONo] = $GRNTmp->PONo;
                	echo "<TR><TD><INPUT TYPE=Submit Name='AddPOToTrans' Value='" . $GRNTmp->PONo . "'></TD><TD COLSPAN=3>" . _('Add Whole PO to Invoice') . '</TD></TR>';
                	$i = 0;
        	}
        	if ($i == 0){
        		echo $tableheader;
		}
        	echo "<TR>
	    		<TD><INPUT TYPE=checkbox NAME='GRNNo_" . $GRNTmp->GRNNo . "'></TD>
			<TD>" . $GRNTmp->GRNNo . '</TD>
			<TD>' . $GRNTmp->PODetailItem . '</TD>
			<TD>' . $GRNTmp->ItemCode . '</TD>
			<TD>' . $GRNTmp->ItemDescription . '</TD>
			<TD ALIGN=RIGHT>' . $GRNTmp->QtyRecd . '</TD>
			<TD ALIGN=RIGHT>' . $GRNTmp->Prev_QuantityInv . '</TD>
			<TD ALIGN=RIGHT>' . ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv) . '</TD>
			<TD ALIGN=RIGHT>' . $GRNTmp->OrderPrice . '</TD>
			<TD ALIGN=RIGHT>' . number_format($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv),2) . '</TD>
			</TR>';
		$i++;
		if ($i>15){
			$i=0;
		}
        }
        echo '</TABLE>';
        echo "<P><INPUT TYPE=Submit Name='AddGRNToTrans' Value='" . _('Add to Invoice') . "'>";
    }
}

echo '</FORM>';
include('includes/footer.inc');
?>