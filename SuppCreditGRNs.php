<?php

/* $Revision: 1.8 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$PageSecurity = 5;

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Enter Supplier Credit Note Against Goods Received');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier credit note must be clicked on'),'info');
	echo '<BR><A HREF="' . $rootpath . '/SelectSupplier.php?' . SID .'">' . _('Select A Supplier to Enter a Transaction For') . '</A>';
	include('includes/footer.inc');
	exit;
	/*It all stops here if there aint no supplier selected and credit note initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to Credit Note button then process this first before showing all GRNs on the credit note otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGRNToTrans'])){

	$InputError=False;

	$Complete = False;

	if (!is_numeric($_POST['ChgPrice']) AND $_POST['ChgPrice']<0){
		$InputError = True;
		prnMsg(_('The price charged in the suppliers currency is either not numeric or negative') . '. ' . _('The goods received cannot be credited at this price'),'error');
	}

	if ($InputError==False){
		$_SESSION['SuppTrans']->Add_GRN_To_Trans($_POST['GRNNumber'],
							$_POST['PODetailItem'],
							$_POST['ItemCode'],
							$_POST['ItemDescription'],
							$_POST['QtyRecd'],
							$_POST['Prev_QuantityInv'],
							$_POST['This_QuantityCredited'],
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

	$_SESSION['SuppTrans']->Remove_GRN_From_Trans($_GET['Delete']);

}


/*Show all the selected GRNs so far from the SESSION['SuppTrans']->GRNs array */

echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Credits Against Goods Received Selected');
echo '<TABLE CELLPADDING=0>';
$TableHeader = '<TR><TD class="tableheader">' . _('GRN') . '</TD>
                    <TD class="tableheader">' . _('Item Code') . '</TD>
                    <TD class="tableheader">' . _('Description') . '</TD>
                    <TD class="tableheader">' . _('Quantity Credited') . '</TD>
                    <TD class="tableheader">' . _('Price Credited in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD>
                    <TD class="tableheader">' . _('Line Value in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD></TR>';

echo $TableHeader;

$TotalValueCharged=0;

foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

	echo '<TR><TD>' . $EnteredGRN->GRNNo . '</TD>
            <TD>' . $EnteredGRN->ItemCode . '</TD>
            <TD>' . $EnteredGRN->ItemDescription . '</TD>
            <TD ALIGN=RIGHT>' . number_format($EnteredGRN->This_QuantityInv,2) . '</TD>
            <TD ALIGN=RIGHT>' . number_format($EnteredGRN->ChgPrice,2) . '</TD>
            <TD ALIGN=RIGHT>' . number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,2) . '</TD>
            <TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $EnteredGRN->GRNNo . '">' . _('Delete') . '</A></TD></TR>';

	$TotalValueCharged = $TotalValueCharged + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo $TableHeader;
	}
}

echo '<TR><TD COLSPAN=5 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total Value Credited Against Goods') . ':</FONT></TD>
          <TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalValueCharged,2) . '</U></FONT></TD></TR>';
echo '</TABLE><BR><A HREF="' . $rootpath . '/SupplierCredit.php?' . SID . '">' . _('Back to Credit Note Entry') . '</A><HR>';

/* Now get all the GRNs for this supplier from the database
after the date entered */
if (!isset($_POST['Show_Since'])){
	$_POST['Show_Since'] =  Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}

$SQL = "SELECT grnno,
               purchorderdetails.orderno,
               purchorderdetails.unitprice,
               grns.itemcode, grns.deliverydate,
               grns.itemdescription,
               grns.qtyrecd,
               grns.quantityinv,
               purchorderdetails.stdcostunit
               FROM grns,
                    purchorderdetails
               WHERE grns.podetailitem=purchorderdetails.podetailitem AND
                     grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "' AND
                     grns.deliverydate >= '" . FormatDateForSQL($_POST['Show_Since']) . "'
               ORDER BY grns.grnno";
$GRNResults = DB_query($SQL,$db);

if (DB_num_rows($GRNResults)==0){
	prnMsg(_('There are no goods received records for') . ' ' . $_SESSION['SuppTrans']->SupplierName . '<BR> ' . _('To enter a credit against goods received') . ', ' . _('the goods must first be received using the link below to select purchase orders to receive'),'info');
	echo '<P><A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . 'SupplierID=' . $_SESSION['SuppTrans']->SupplierID . '">' . _('Select Purchase Orders to Receive') . '</A>';
	include('includes/footer.inc');
	exit;
}

/*Set up a table to show the GRNs outstanding for selection */
echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '<BR>' . _('Show Goods Received Since') . ': <INPUT TYPE=Text NAME="Show_Since" MAXLENGTH=11 SIZE=12 VALUE="' . $_POST['Show_Since'] . '">';
echo '<FONT SIZE=4 COLOR=BLUE> ' . _('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;

echo '<TABLE CELLPADDING=2 COLSPAN=7>';

$TableHeader = '<TR><TD class="tableheader">' . _('GRN') . '</TD>
                    <TD class="tableheader">' . _('Order') . '</TD>
                    <TD class="tableheader">' . _('Item Code') . '</TD>
                    <TD class="tableheader">' . _('Description') . '</TD>
                    <TD class="tableheader">' . _('Delivered') . '</TD>
                    <TD class="tableheader">' . _('Total Qty') . '<BR>' . _('Received') . '</TD>
                    <TD class="tableheader">' . _('Qty Already') . '<BR>' . _('credit noted') . '</TD>
                    <TD class="tableheader">' . _('Qty Yet') . '<BR>' . _('To credit note') . '</TD>
                    <TD class="tableheader">' . _('Order Price') . '<BR>' . $_SESSION['SuppTrans']->CurrCode . '</TD>
                    <TD class="tableheader">' . _('Line Value') . '<BR>' . _('In') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD>
                    </TR>';

echo $TableHeader;

$i=0;
while ($myrow=DB_fetch_array($GRNResults)){

	$GRNAlreadyOnCredit = False;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
		if ($EnteredGRN->GRNNo == $myrow['grnno']) {
			$GRNAlreadyOnCredit = True;
		}
	}
	if ($GRNAlreadyOnCredit == False){
		echo '<TR><TD><INPUT TYPE=Submit NAME="GRNNo" Value="' . $myrow['grnno'] . '"></TD>
              		<TD>' . $myrow['orderno'] . '</TD>
              		<TD>' . $myrow['itemcode'] . '</TD>
              		<TD>' . $myrow['itemdescription'] . '</TD>
              		<TD>' . ConvertSQLDate($myrow['deliverydate']) . '</TD>
              		<TD ALIGN=RIGHT>' . number_format($myrow['qtyrecd'],2) . '</TD>
              		<TD ALIGN=RIGHT>' . number_format($myrow['quantityinv'],2) . '</TD>
              		<TD ALIGN=RIGHT>' . number_format($myrow['qtyrecd'] - $myrow['quantityinv'],2) . '</TD>
              		<TD ALIGN=RIGHT>' . number_format($myrow['unitprice'],2) . '</TD>
              		<TD ALIGN=RIGHT>' . number_format($myrow['unitprice']*($myrow['qtyrecd'] - $myrow['quantityinv']),2) . '</TD>
              	</TR>';
		$i++;
		if ($i>15){
			$i=0;
			echo $TableHeader;
		}
	}
}

echo '</TABLE>';

if (isset($_POST['GRNNo']) AND $_POST['GRNNo']!=''){

	$SQL = 'SELECT grnno,
                 grns.podetailitem,
                 purchorderdetails.unitprice,
                 purchorderdetails.glcode,
                 grns.itemcode,
                 grns.deliverydate,
                 grns.itemdescription,
                 grns.quantityinv,
                 grns.qtyrecd,
                 grns.qtyrecd - grns.quantityinv
                 AS qtyostdg,
                    purchorderdetails.stdcostunit,
                    purchorderdetails.shiptref,
                    purchorderdetails.jobref,
                    shipments.closed
                 FROM grns,
                      purchorderdetails
                 LEFT JOIN shipments ON purchorderdetails.shiptref=shipments.shiptref
                 WHERE grns.podetailitem=purchorderdetails.podetailitem AND
                       grns.grnno=' .$_POST['GRNNo'];
	$GRNEntryResult = DB_query($SQL,$db);
	$myrow = DB_fetch_array($GRNEntryResult);

	echo '<P><FONT SIZE=4 COLOR=BLUE><B>' . _('GRN Selected For Adding To A Suppliers Credit Note') . '</FONT></B>';

	echo '<TABLE><TR><TD class="tableheader">' . _('GRN') . '</TD>
                   <TD class="tableheader">' . _('Item') . '</TD>
                   <TD class="tableheader">' . _('Quantity') . '<BR>' . _('Outstanding') . '</TD>
                   <TD class="tableheader">' . _('Quantity') . '<BR>' . _('credited') . '</TD>
                   <TD class="tableheader">' . _('Order') . '<BR>' . _('Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD>
                   <TD class="tableheader">' . _('Credit') . '<BR>' . _('Price') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</TD>
                   </TR>';

	echo '<TR><TD>' . $_POST['GRNNo'] . '</TD>
            <TD>' . $myrow['itemcode'] . ' ' . $myrow['itemdescription'] . '</TD>
            <TD ALIGN=RIGHT>' . number_format($myrow['qtyostdg'],2) . '</TD>
            <TD><INPUT TYPE=Text Name="This_QuantityCredited" Value=' . $myrow['qtyostdg'] . ' SIZE=11 MAXLENGTH=10></TD>
            <TD ALIGN=RIGHT>' . $myrow['unitprice'] . '</TD>
            <TD><INPUT TYPE=Text Name="ChgPrice" Value=' . $myrow['unitprice'] . ' SIZE=11 MAXLENGTH=10></TD>
            </TR>';
	echo '</TABLE>';

	if ($myrow['closed']==1){ /*Shipment is closed so pre-empt problems later by warning the user - need to modify the order first */
		echo '<INPUT TYPE=HIDDEN NAME="ShiptRef" Value="">';
		prnMsg(_('Unfortunately the shipment that this purchase order line item was allocated to has been closed') . ' - ' . _('if you add this item to the transaction then no shipments will not be updated') . '. ' . _('If you wish to allocate the order line item to a different shipment the order must be modified first'),'error');
	} else {
		echo '<INPUT TYPE=HIDDEN NAME="ShiptRef" Value="' . $myrow['shiptref'] . '">';
	}

	echo '<P><INPUT TYPE=Submit Name="AddGRNToTrans" Value="' . _('Add to Credit Note') . '">';


	echo '<INPUT TYPE=HIDDEN NAME="GRNNumber" VALUE=' . $_POST['GRNNo'] . '>';
	echo '<INPUT TYPE=HIDDEN NAME="ItemCode" VALUE="' . $myrow['itemcode'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="ItemDescription" VALUE="' . $myrow['itemdescription'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="QtyRecd" VALUE=' . $myrow['qtyrecd'] . '>';
	echo '<INPUT TYPE=HIDDEN NAME="Prev_QuantityInv" VALUE=' . $myrow['quantityinv'] . '>';
	echo '<INPUT TYPE=HIDDEN NAME="OrderPrice" VALUE=' . $myrow['unitprice'] . '>';
	echo '<INPUT TYPE=HIDDEN NAME="StdCostUnit" VALUE=' . $myrow['stdcostunit'] . '>';

	echo '<INPUT TYPE=HIDDEN NAME="JobRef" Value="' . $myrow['jobref'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="GLCode" Value="' . $myrow['glcode'] . '">';
	echo '<INPUT TYPE=HIDDEN NAME="PODetailItem" Value="' . $myrow['podetailitem'] . '">';
}

echo '</FORM>';
include('includes/footer.inc');
?>
