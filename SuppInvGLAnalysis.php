<?php
/* $Revision: 1.7 $ */
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
	echo '<br><a href="' . $rootpath . '/SelectSupplier.php?' . SID . '">' . _('Select A Supplier to Enter an Invoice For') . '</a>';
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
echo '<div class="centre"><font size=4 color=blue>' . _('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppInv']->SupplierName.'</div>';
echo '<table cellpadding=2><tr><th>' . _('Account') . '</th>
                               <th>' . _('Name') . '</th>
                               <th>' . _('Amount') . '<br>' . _('in') . ' ' . $_SESSION['SuppInv']->CurrCode . '</th>
                               <th>' . _('Shipment') . '</th>
                               <th>' . _('Job') . '</th>
                               <th>' . _('Narrative') . '</th></tr>';

$TotalGLValue=0;

foreach ($_SESSION['SuppInv']->GLCodes as $EnteredGLCode){

	echo '<tr><td>' . $EnteredGLCode->GLCode . '</td>
            <td>' . $EnteredGLCode->GLActName . '</td>
            <td align=right>' . number_format($EnteredGLCode->Amount,2) . '</td>
            <td>' . $EnteredGLCode->ShiptRef . '</td>
            <td>' .$EnteredGLCode->JobRef . '</td>
            <td>' . $EnteredGLCode->Narrative . '</td>
            <td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $EnteredGLCode->Counter . '">' . _('Delete') . '</a></td></tr>';

	$TotalGLValue = $TotalGLValue + ($EnteredGLCode->ChgPrice * $EnteredGLCode->This_QuantityInv);

	$i++;
	if ($i>15){
		$i=0;
		echo '<tr><td bgcolor=#800000><font color=#ffffff><b>' . _('Account') . '</b></td>
              <td bgcolor=#800000><font color=#ffffff><b>' . _('Name') . '</b></td>
              <td bgcolor=#800000><font color=#ffffff><b>' . _('Amount') . '<br>' . _('in') . ' ' . $_SESSION['SuppInv']->CurrCode . '</b></td>
              <td bgcolor=#800000><font color=#ffffff><b>' . _('Shipment') . '</b></td>
              <td bgcolor=#800000><font color=#ffffff><b>' . _('Job') . '</b></td>
              <td bgcolor=#800000><font color=#ffffff><b>' . _('Narrative') . '</b></td></tr>';
	}
}

echo '<tr><td colspan=2 align=right><font size=4 color=BLUE>' . _('Total') . ':</font></td>
          <td align=right><font size=4 color=BLUE><U>' . number_format($TotalGLValue,2) . '</U></font></td></tr>';
echo '</table><br><a href="' . $rootpath . '/SupplierInvoice.php?' . SID . '">' . _('Back to Invoice Entry') . '</a><hr>';

/*Set up a form to allow input of new GL entries */
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

echo '</form>';
include('includes/footer.inc');
?>
