<?php
/* $Revision: 1.5 $ */
/* Script to delete all supplier payments entered or created from a payment run on a specified day
 */
$PageSecurity=15;
include ('includes/session.inc');
$title = _('Reverse and Delete Supplier Payments');
include('includes/header.inc');


/*Only do deletions if user hits the button */
if (isset($_POST['RevPayts']) AND Is_Date($_POST['PaytDate'])==1){

	$SQLTranDate = FormatDateForSQL($_POST['PaytDate']);

	$SQL = "SELECT TransNo,
			SupplierNo,
			OvAmount,
			SuppReference,
			Rate
		FROM SuppTrans
		WHERE Type = 22
		AND TranDate = '" . $SQLTranDate . "'";

	$Result = DB_query($SQL,$db);
	prnMsg(_('The number of payments that will be deleted is') . ' :' . DB_num_rows($Result),'info');

	while ($Payment = DB_fetch_array($Result)){
		prnMsg(_('Deleting payment number') . ' ' . $Payment['TransNo'] . ' ' . _('to supplier code') . ' ' . $Payment['SupplierNo'] . ' ' . _('for an amount of') . ' ' . $Payment['OvAmount'],'info');

		$SQL = 'DELETE FROM SuppTrans
			WHERE Type=22
			AND TransNo=' . $Payment['TransNo'] . "
			AND TranDate='" . $SQLTranDate . "'";

		$DelResult = DB_query($SQL,$db);
		prnMsg(_('Deleted the SuppTran record'),'success');


		$SQL = "SELECT TransNo,
				TypeNo,
				Amt
			FROM SuppAllocs
			WHERE PaytNo = " .  $Payment['TransNo'] . '
			AND PaytTypeNo=22';

		$AllocsResult = DB_query($SQL,$db);
		while ($Alloc = DB_fetch_array($AllocsResult)){

			$SQL= 'UPDATE SuppTrans SET Settled=0,
							Alloc=Alloc-' . $Alloc['Amt'] . ',
							DiffOnExch = DiffOnExch - ((' . $Alloc['Amt'] . '/Rate ) - ' . $Alloc['Amt']/$Payment['Rate'] . ')
				WHERE Type=' . $Alloc['TypeNo'] . '
				AND TransNo=' . $Alloc['TransNo'];

			$ErrMsg =_('The update to the suppliers charges that were settled by the payment failed because');
			$UpdResult = DB_query($SQL,$db,$ErrMsg);

		}

		prnMsg(' ... ' . _('reversed the allocations'),'info');
		$SQL= 'Delete FROM SuppAllocs W
			HERE PaytNo=' . $Payment['TransNo'] . '
			AND PaytTypeNo=22';
		$DelResult = DB_query($SQL,$db);
		prnMsg(' ... ' . _('deleted the SuppAllocs records'),'info');

		$SQL= 'Delete FROM GLTrans WHERE TypeNo=' . $Payment['TransNo'] . ' AND Type=22';
		$DelResult = DB_query($SQL,$db);
		prnMsg(' .... ' . _('the GLTrans records (if any)'),'info');

		$SQL= "Delete FROM BankTrans
				WHERE Ref='" . $Payment['SuppReference'] . ' ' . $Payment['SupplierNo'] . "'
				AND Amount=" . $Payment['OvAmount'] . "
				AND TransDate = '" . $SQLTranDate . "'";
		$DelResult = DB_query($SQL,$db);
		prnMsg(' .... ' . _('and the BankTrans record'),'info');

	}


}


echo "<FORM METHOD=POST ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
echo '<BR>' . _('Enter the date of the payment run') . ": <INPUT TYPE=text name='PaytDate' maxlength=11 size=11 value='" . $_POST['PaytDate'] . "'>";
echo "<INPUT TYPE=submit name='RevPayts' value='" . _('Reverse Supplier Payments on the Date Entered') . "'>";
echo '</FORM>';

include('includes/footer.inc');
?>
