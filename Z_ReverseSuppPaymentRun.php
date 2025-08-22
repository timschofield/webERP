<?php

/* Script to delete all supplier payments entered or created from a payment run on a specified day
 */

include('includes/session.php');
$Title = __('Reverse and Delete Supplier Payments');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');


/*Only do deletions if user hits the button */
if (isset($_POST['RevPayts']) AND Is_Date($_POST['PaytDate'])==1){

	$SQLTranDate = FormatDateForSQL($_POST['PaytDate']);

	$SQL = "SELECT id,
			transno,
			supplierno,
			ovamount,
			suppreference,
			rate
		FROM supptrans
		WHERE supptrans.type = 22
		AND trandate = '" . $SQLTranDate . "'";

	$Result = DB_query($SQL);
	prnMsg(__('The number of payments that will be deleted is') . ' :' . DB_num_rows($Result),'info');

	while ($Payment = DB_fetch_array($Result)){
		prnMsg(__('Deleting payment number') . ' ' . $Payment['transno'] . ' ' . __('to supplier code') . ' ' . $Payment['supplierno'] . ' ' . __('for an amount of') . ' ' . $Payment['ovamount'],'info');

		$SQL = "SELECT supptrans.transno,
				supptrans.type,
				suppallocs.amt
			FROM supptrans INNER JOIN suppallocs
			ON supptrans.id=suppallocs.transid_allocto
			WHERE suppallocs.transid_allocfrom = " .  $Payment['id'];

		$AllocsResult = DB_query($SQL);
		while ($Alloc = DB_fetch_array($AllocsResult)){

			$SQL= "UPDATE supptrans SET settled=0,
										alloc=alloc-" . $Alloc['amt'] . ",
										diffonexch = diffonexch - ((" . $Alloc['amt'] . "/rate ) - " . $Alloc['amt']/$Payment['rate'] . ")
					WHERE supptrans.type='" . $Alloc['type'] . "'
					AND transno='" . $Alloc['transno'] . "'";

			$ErrMsg =__('The update to the suppliers charges that were settled by the payment failed because');
			$UpdResult = DB_query($SQL, $ErrMsg);

		}

		prnMsg(' ... ' . __('reversed the allocations'),'info');
		$SQL= "DELETE FROM suppallocs WHERE transid_allocfrom='" . $Payment['id'] . "'";
		$DelResult = DB_query($SQL);
		prnMsg(' ... ' . __('deleted the SuppAllocs records'),'info');

		$SQL = "DELETE FROM supptrans
			WHERE type=22
			AND transno='" . $Payment['transno'] . "'
			AND trandate='" . $SQLTranDate . "'";

		$DelResult = DB_query($SQL);
		prnMsg(__('Deleted the SuppTran record'),'success');


		$SQL= "DELETE FROM gltrans WHERE typeno='" . $Payment['transno'] . "' AND type=22";
		$DelResult = DB_query($SQL);
		prnMsg(' .... ' . __('the GLTrans records (if any)'),'info');

		$SQL= "DELETE FROM banktrans
				WHERE ref='" . $Payment['suppreference'] . ' ' . $Payment['supplierno'] . "'
				AND amount=" . $Payment['ovamount'] . "
				AND transdate = '" . $SQLTranDate . "'";
		$DelResult = DB_query($SQL);
		prnMsg(' .... ' . __('and the BankTrans record'),'info');

	}
}


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />' . __('Enter the date of the payment run') . ': <input type="text" name="PaytDate" maxlength="11" size="11" value="' . $_POST['PaytDate'] . '" />';
echo '<input type="submit" name="RevPayts" value="' . __('Reverse Supplier Payments on the Date Entered') . '" />';
echo '</div>
      </form>';

include('includes/footer.php');
