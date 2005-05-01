<?php


/* $Revision: 1.7 $ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of Shipts objects - containing details of all shipment charges for invoicing
Shipment charges are posted to the debit of GRN suspense if the Creditors - GL link is on
This is cleared against credits to the GRN suspense when the products are received into stock and any
purchase price variance calculated when the shipment is closed */

include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started here for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Shipment Charges or Credits');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('Shipment charges or credits are entered against supplier invoices or credit notes respectively') . '. ' . _('To enter supplier transactions the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or credit note must be clicked on'),'info');
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier') . '</A>';
	exit;
	/*It all stops here if there aint no supplier selected and invoice/credit initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddShiptChgToInvoice'])){

	$InputError = False;
	if ($_POST['ShiptRef'] == ""){
		$_POST['ShiptRef'] = $_POST['ShiptSelection'];
	}
	if (!is_numeric($_POST['ShiptRef'])){
		prnMsg(_('The shipment reference must be numeric') . '. ' . _('This shipment charge cannot be added to the invoice'),'error');
		$InputError = True;
	}

	if (!is_numeric($_POST['Amount'])){
		prnMsg(_('The amount entered is not numeric') . '. ' . _('This shipment charge cannot be added to the invoice'),'error');
		$InputError = True;
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_Shipt_To_Trans($_POST['ShiptRef'], $_POST['Amount']);
		unset($_POST['ShiptRef']);
		unset($_POST['Amount']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_Shipt_From_Trans($_GET['Delete']);
}

/*Show all the selected ShiptRefs so far from the SESSION['SuppInv']->Shipts array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit=='Invoice'){
	echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Shipment charges on Invoice') . ' ';
} else {
	echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('Shipment credits on Credit Note') . ' ';
}

echo $_SESSION['SuppTrans']->SuppReference . ' ' ._('From') . ' ' . $_SESSION['SuppTrans']->SupplierName;

echo "<TABLE CELLPADDING=2>";
$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Shipment') . "</TD>
		<TD CLASS='tableheader'>" . _('Amount') . '</TD></TR>';
echo $TableHeader;

$TotalShiptValue = 0;

foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

	echo '<TR><TD>' . $EnteredShiptRef->ShiptRef . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredShiptRef->Amount,2) . "</TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $EnteredShiptRef->Counter . "'>" . _('Delete') . '</A></TD></TR>';

	$TotalShiptValue = $TotalShiptValue + $EnteredShiptRef->Amount;

	$i++;
	if ($i>15){
		$i = 0;
		echo $TableHeader;
	}
}

echo '<TR>
	<TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalShiptValue,2) . '</U></FONT></TD>
</TR>
</TABLE>';

if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo "<BR><A HREF='$rootpath/SupplierInvoice.php?" . SID . "'>" . _('Back to Invoice Entry') . '</A><HR>';
} else {
	echo "<BR><A HREF='$rootpath/SupplierCredit.php?" . SID . "'>" . _('Back to Credit Note Entry') . '</A><HR>';
}

/*Set up a form to allow input of new Shipment charges */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo '<TABLE>';
echo '<TR><TD>' . _('Shipment Reference') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='ShiptRef' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['ShiptRef'] . '></TD></TR>';
echo '<TR><TD>' . _('Shipment Selection') . ':<BR><FONT SIZE=1>' . _('If you know the code enter it above') . '<BR>' . _('otherwise select the shipment from the list') . "</FONT></TD><TD><SELECT NAME='ShiptSelection'>";

$sql = "SELECT shiptref, 
		vessel, 
		eta, 
		suppname 
	FROM shipments INNER JOIN suppliers 
		ON shipments.supplierid=suppliers.supplierid 
	WHERE closed=0";

$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['shiptref']==$_POST['ShiptSelection']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['shiptref'] . '>' . $myrow['shiptref'] . ' - ' . $myrow['vessel'] . ' ' . _('ETA') . ' ' . ConvertSQLDate($myrow['eta']) . ' ' . _('from') . ' ' . $myrow['suppname'];
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Amount') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='Amount' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['Amount'] . '></TD></TR>';
echo '</TABLE>';

echo "<INPUT TYPE='Submit' NAME='AddShiptChgToInvoice' VALUE='" . _('Enter Shipment Charge') . "'>";

echo '</FORM>';
include('includes/footer.inc');
?>
