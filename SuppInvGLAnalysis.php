<?php
/* $Revision: 1.5 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */
$PageSecurity=5;
include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Supplier Invoice General Ledger Analysis');

include('includes/header.inc');


if (!isset($_SESSION['SuppInv'])){
	prnMsg( _('To enter a supplier invoice the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice must be clicked on'),'info');
	echo '<BR><A HREF="' . $rootpath . '/SelectSupplier.php?' . SID . '">' . _('Select A Supplier to Enter an Invoice For') . '</A>';
	include('includes/footer.inc');
	exit;
	/*It all stops here if there aint no supplier selected and invoice initiated ie $_SESSION['SuppInv'] started off*/
}

/*If the user hit the Add to Invoice button then process this first before showing  all GL codes on the invoice otherwise it wouldnt show the latest addition*/

if (isset($_POST['AddGLCodeToInvoice']) ){

	$InputError=False;

	if ($InputError==False){
		$_SESSION['SuppInv']->Add_GLCodes_To_Trans($GLCode,
								$Amount,
								$ShiptRef,
								$JobRef,
								$Narrative);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppInv']->Remove_GLCodes_From_Trans($_GET['Delete']);

}


/*Show all the selected GLCodes so far from the SESSION['SuppInv']->GLCodes array */
echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppInv']->SupplierName;
echo '<TABLE CELLPADDING=2><TR><TD class="tableheader">' . _('Account') . '</TD>
                               <TD class="tableheader">' . _('Name') . '</TD>
                               <TD class="tableheader">' . _('Amount') . '<BR>' . _('in') . ' ' . $_SESSION['SuppInv']->CurrCode . '</TD>
                               <TD class="tableheader">' . _('Shipment') . '</TD>
                               <TD class="tableheader">' . _('Job') . '</TD>
                               <TD class="tableheader">' . _('Narrative') . '</TD></TR>';

$TotalGLValue=0;

foreach ($_SESSION['SuppInv']->GLCodes as $EnteredGLCode){

	echo '<TR><TD>' . $EnteredGLCode->GLCode . '</TD>
            <TD>' . $EnteredGLCode->GLActName . '</TD>
            <TD ALIGN=RIGHT>' . number_format($EnteredGLCode->Amount,2) . '</TD>
            <TD>' . $EnteredGLCode->ShiptRef . '</TD>
            <TD>' .$EnteredGLCode->JobRef . '</TD>
            <TD>' . $EnteredGLCode->Narrative . '</TD>
            <TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $EnteredGLCode->Counter . '">' . _('Delete') . '</A></TD></TR>';

	$TotalGLValue = $TotalGLValue + ($EnteredGLCode->ChgPrice * $EnteredGLCode->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo '<TR><TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Account') . '</B></TD>
              <TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Name') . '</B></TD>
              <TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Amount') . '<BR>' . _('in') . ' ' . $_SESSION['SuppInv']->CurrCode . '</B></TD>
              <TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Shipment') . '</B></TD>
              <TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Job') . '</B></TD>
              <TD BGCOLOR=#800000><FONT COLOR=#ffffff><B>' . _('Narrative') . '</B></TD></TR>';
	}
}

echo '<TR><TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total') . ':</FONT></TD>
          <TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalGLValue,2) . '</U></FONT></TD></TR>';
echo '</TABLE><BR><A HREF="' . $rootpath . '/SupplierInvoice.php?' . SID . '">' . _('Back to Invoice Entry') . '</A><HR>';

/*Set up a form to allow input of new GL entries */
echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '</FORM>';
include('includes/footer.inc');
?>
