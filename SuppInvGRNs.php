<?php

/* $Revision: 1.19 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */


$PageSecurity = 5;

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');
$title = _('Enter Supplier Invoice Against Goods Received');
include('includes/header.inc');
$Complete=false;
if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice must be clicked on'),'info');
	echo "<br><a href='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier to Enter a Transaction For') . '</a>';
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
    	if (isset($_POST['GRNNo_' . $GRNTmp->GRNNo])) {
    		$_POST['GRNNo_' . $GRNTmp->GRNNo] = true;
    	} else {
    		$_POST['GRNNo_' . $GRNTmp->GRNNo] = false;
    	}
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

echo '<div class="centre"><font size=4 color=BLUE>' . _('Invoiced Goods Received Selected');
echo '</font></div><table cellpadding=1>';

$tableheader = "<tr bgcolor=#800000>
			<th>" . _('Sequence') . " #</th>
			<th>" . _('Item Code') . "</th>
			<th>" . _('Description') . "</th>
			<th>" . _('Quantity Charged') . "</th>
			<th>" . _('Price Charge in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
			<th>" . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th></tr>';

echo $tableheader;

$TotalValueCharged=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

	echo '<tr><td>' . $EnteredGRN->GRNNo . '</td>
		<td>' . $EnteredGRN->ItemCode . '</td>
		<td>' . $EnteredGRN->ItemDescription . '</td>
		<td align=right>' . number_format($EnteredGRN->This_QuantityInv,2) . '</td>
		<td align=right>' . number_format($EnteredGRN->ChgPrice,2) . '</td>
		<td align=right>' . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . "</td>
		<td><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Modify=' . $EnteredGRN->GRNNo . "'>". _('Modify') . "</a></td>
		<td><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $EnteredGRN->GRNNo . "'>" . _('Delete') . "</a></td>
	</tr>";

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $tableheader;
	}
}

echo '<tr>
	<td colspan=5 align=right><font size=4 color=BLUE>' . _('Total Value of Goods Charged') . ':</font></td>
	<td align=right><font size=4 color=BLUE><U>' . number_format($TotalValueCharged,2) . '</U></font></td>
</tr>';
echo "</table>";
echo "<div class='centre'><br><a href='$rootpath/SupplierInvoice.php?" . SID ."'>" . _('Back to Invoice Entry') . '</a><hr></div>';


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
	prnMsg(_('There are no outstanding goods received from') . ' ' . $_SESSION['SuppTrans']->SupplierName . ' ' . _('that have not been invoiced by them') . '<br>' . _('The goods must first be received using the link below to select purchase orders to receive'),'warn');
	echo "<div class='centre'><p><a href='$rootpath/PO_SelectOSPurchOrder.php?" . SID . 'SupplierID=' . $_SESSION['SuppTrans']->SupplierID ."'>" . _('Select Purchase Orders to receive') .'</a></div>';
	include('includes/footer.inc');
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

if (!isset( $_SESSION['SuppTransTmp'])){
    $_SESSION['SuppTransTmp'] = new SuppTrans;
    while ($myrow=DB_fetch_array($GRNResults)){

	    $GRNAlreadyOnInvoice = False;

	    foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		    if ($EnteredGRN->GRNNo == $myrow['grnbatch']) {
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

	echo '<p><div class="centre"><font size=4 color=BLUE><b>' . _('GRN Selected For Adding To A Purchase Invoice') . '</font></b></div>';
	echo "<table>
		<tr bgcolor=#800000>
			<th>" . _('Sequence') . " #</th>
			<th>" . _('Item') . "</th>
			<th>" . _('Qty Outstanding') . "</th>
			<th>" . _('Qty Invoiced') . "</th>
			<th>" . _('Order Price in') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</th>
			<th>" . _('Actual Price in') . ' ' .  $_SESSION['SuppTrans']->CurrCode . "</th>
		</tr>";

	echo '<tr>
		<td>' . $GRNTmp->GRNNo . '</td>
		<td>' . $GRNTmp->ItemCode . ' ' . $GRNTmp->ItemDescription . '</td>
		<td align=right>' . number_format($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv,2) . "</td>
		<td><input type=Text class='number' Name='This_QuantityInv' Value=" . $GRNTmp->This_QuantityInv . ' size=11 maxlength=10></td>
		<td align=right>' . $GRNTmp->OrderPrice . '</td>
		<td><input type=Text class="number" Name="ChgPrice" Value=' . $GRNTmp->ChgPrice . ' size=11 maxlength=10></td>
	</tr>';
	echo '</table>';

/*	if ($myrow['closed']==1){ //Shipment is closed so pre-empt problems later by warning the user - need to modify the order first
		echo "<input type=hidden name='ShiptRef' Value=''>";
		echo "<p>Unfortunately, the shipment that this purchase order line item was allocated to has been closed - if you add this item to the transaction then no shipments will not be updated. If you wish to allocate the order line item to a different shipment the order must be modified first.";
	} else {    */
		echo "<input type=hidden name='ShiptRef' Value='" . $GRNTmp->ShiptRef . "'>";
//	}

	echo "<div class='centre'><p><input type=Submit Name='ModifyGRN' Value='" . _('Modify Line') . "'></div>";


	echo "<input type=hidden name='GRNNumber' VALUE=" . $GRNTmp->GRNNo . '>';
	echo "<input type=hidden name='ItemCode' VALUE='" . $GRNTmp->ItemCode . "'>";
	echo "<input type=hidden name='ItemDescription' VALUE='" . $GRNTmp->ItemDescription . "'>";
	echo "<input type=hidden name='QtyRecd' VALUE=" . $GRNTmp->QtyRecd . ">";
	echo "<input type=hidden name='Prev_QuantityInv' VALUE=" . $GRNTmp->Prev_QuantityInv . '>';
	echo "<input type=hidden name='OrderPrice' VALUE=" . $GRNTmp->OrderPrice . '>';
	echo "<input type=hidden name='StdCostUnit' VALUE=" . $GRNTmp->StdCostUnit . '>';
	echo "<input type=hidden name='JobRef' Value='" . $GRNTmp->JobRef . "'>";
	echo "<input type=hidden name='GLCode' Value='" . $GRNTmp->GLCode . "'>";
	echo "<input type=hidden name='PODetailItem' Value='" . $GRNTmp->PODetailItem . "'>";
}
else {
    if (count( $_SESSION['SuppTransTmp']->GRNs)>0){   /*if there are any outstanding GRNs then */
        echo '<div class="centre"><font size=4 color=BLUE>' . _('Goods Received Yet to be Invoiced From') . ' ' . $_SESSION['SuppTrans']->SupplierName.'</div>';
        echo "<table cellpadding=1 colspan=7>";

        $tableheader = "<tr bgcolor=#800000><th>" . _('Select') . "</th>
				<th>" . _('Sequence') . " #</th>
				<th>" . _('Order') . "</th>
				<th>" . _('Item Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('Total Qty Received') . "</th>
				<th>" . _('Qty Already Invoiced') . "</th>
				<th>" . _('Qty Yet To Invoice') . "</th>
				<th>" . _('Order Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</th>
				<th>" . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th></tr>';

        $i = 0;
        $POs = array();
        foreach ($_SESSION['SuppTransTmp']->GRNs as $GRNTmp){

		$_SESSION['SuppTransTmp']->GRNs[$GRNTmp->GRNNo]->This_QuantityInv = $GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv;	

		if (isset($POs[$GRNTmp->PONo]) and $POs[$GRNTmp->PONo] != $GRNTmp->PONo) {
                	$POs[$GRNTmp->PONo] = $GRNTmp->PONo;
                	echo "<tr><td><input type=Submit Name='AddPOToTrans' Value='" . $GRNTmp->PONo . "'></td><td colspan=3>" . _('Add Whole PO to Invoice') . '</td></tr>';
                	$i = 0;
        	}
        	if ($i == 0){
        		echo $tableheader;
		}
		if (isset($_POST['SelectAll'])) {
			echo "<tr><td><input type=checkbox checked name='GRNNo_" . $GRNTmp->GRNNo . "'></td>";
		} else {
			echo "<tr><td><input type=checkbox name='GRNNo_" . $GRNTmp->GRNNo . "'></td>";
		}
		echo "<td>" . $GRNTmp->GRNNo . '</td>
		<td>' . $GRNTmp->PONo . '</td>
		<td>' . $GRNTmp->ItemCode . '</td>
		<td>' . $GRNTmp->ItemDescription . '</td>
		<td class=number>' . $GRNTmp->QtyRecd . '</td>
		<td class=number>' . $GRNTmp->Prev_QuantityInv . '</td>
		<td class=number>' . ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv) . '</td>
		<td class=number>' . $GRNTmp->OrderPrice . '</td>
		<td class=number>' . number_format($GRNTmp->OrderPrice * ($GRNTmp->QtyRecd - $GRNTmp->Prev_QuantityInv),2) . '</td>
		</tr>';
		$i++;
		if ($i>15){
			$i=0;
		}
        }
        echo '</table>';
        echo "<div class='centre'><input type=Submit Name='SelectAll' Value='" . _('Select All') . "'>";
        echo "<input type=Submit Name='DeSelectAll' Value='" . _('Deselect All') . "'>";
        echo "<br><input type=Submit Name='AddGRNToTrans' Value='" . _('Add to Invoice') . "'></div>";
    }
}

echo '</form>';
include('includes/footer.inc');
?>