<?php

/* $Revision: 1.7 $ */

/* Script to delete all supplier payments entered or created from a payment run on a specified day
 */
$PageSecurity=15;
include ('includes/session.inc');
$title = _('Reverse and Delete Supplier Payments');
include('includes/header.inc');


/*Only do deletions if user hits the button */
if (isset($_POST['RevPayts']) AND Is_Date($_POST['PaytDate'])==1){

	$SQLTranDate = FormatDateForSQL($_POST['PaytDate']);

	$SQL = "SELECT transno,
			supplierno,
			ovamount,
			suppreference,
			rate
		FROM supptrans
		WHERE type = 22
		AND trandate = '" . $SQLTranDate . "'";

	$Result = DB_query($SQL,$db);
	prnMsg(_('The number of payments that will be deleted is') . ' :' . DB_num_rows($Result),'info');

	while ($Payment = DB_fetch_array($Result)){
		prnMsg(_('Deleting payment number') . ' ' . $Payment['transno'] . ' ' . _('to supplier code') . ' ' . $Payment['supplierno'] . ' ' . _('for an amount of') . ' ' . $Payment['ovamount'],'info');

		$SQL = 'DELETE FROM supptrans
			WHERE type=22
			AND transno=' . $Payment['transno'] . "
			AND trandate='" . $SQLTranDate . "'";

		$DelResult = DB_query($SQL,$db);
		prnMsg(_('Deleted the SuppTran record'),'success');


		$SQL = "SELECT transno,
				typeno,
				amt
			FROM suppallocs
			WHERE paytno = " .  $Payment['transno'] . '
			AND payttypeno=22';

		$AllocsResult = DB_query($SQL,$db);
		while ($Alloc = DB_fetch_array($AllocsResult)){

			$SQL= 'UPDATE supptrans SET settled=0,
							alloc=alloc-' . $Alloc['amt'] . ',
							diffonexch = diffonexch - ((' . $Alloc['Amt'] . '/rate ) - ' . $Alloc['amt']/$Payment['rate'] . ')
				WHERE type=' . $Alloc['typeno'] . '
				AND transno=' . $Alloc['transno'];

			$ErrMsg =_('The update to the suppliers charges that were settled by the payment failed because');
			$UpdResult = DB_query($SQL,$db,$ErrMsg);

		}

		prnMsg(' ... ' . _('reversed the allocations'),'info');
		$SQL= 'DELETE FROM suppallocs WHERE paytno=' . $Payment['transno'] . '
			AND payttypeno=22';
		$DelResult = DB_query($SQL,$db);
		prnMsg(' ... ' . _('deleted the SuppAllocs records'),'info');

		$SQL= 'DELETE FROM gltrans WHERE typeno=' . $Payment['transno'] . ' AND type=22';
		$DelResult = DB_query($SQL,$db);
		prnMsg(' .... ' . _('the GLTrans records (if any)'),'info');

		$SQL= "DELETE FROM banktrans
				WHERE ref='" . $Payment['suppreference'] . ' ' . $Payment['supplierno'] . "'
				AND amount=" . $Payment['ovamount'] . "
				AND transdate = '" . $SQLTranDate . "'";
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