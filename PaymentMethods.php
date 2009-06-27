<?php
/* $Revision: 1.9 $ */

$PageSecurity = 15;

include('includes/session.inc');

$title = _('Payment Methods');

include('includes/header.inc');

if ( isset($_GET['SelectedPaymentID']) )
	$SelectedPaymentID = $_GET['SelectedPaymentID'];
elseif (isset($_POST['SelectedPaymentID']))
	$SelectedPaymentID = $_POST['SelectedPaymentID'];

if (isset($Errors)) {
	unset($Errors);
}
	
$Errors = array();	

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (strpos($_POST['MethodName'],'&')>0 OR strpos($_POST['MethodName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('The payment method cannot contain the character') . " '&' " . _('or the character') ." '",'error');
		$Errors[$i] = 'MethodName';
		$i++;		
	}
	if ( trim($_POST['MethodName']) == "") {
		$InputError = 1;
		prnMsg( _('The payment method may not be empty.'),'error');
		$Errors[$i] = 'MethodName';
		$i++;		
	}
	if ($_POST['SelectedPaymentID']!='' AND $InputError !=1) {

		/*SelectedPaymentID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM paymentmethods 
				WHERE paymentid <> " . $SelectedPaymentID ."
				AND paymentname ".LIKE." '" . $_POST['MethodName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The payment method can not be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists need to be very careful here
			
			$sql = "SELECT paymentname FROM paymentmethods 
				WHERE paymentid = " . $SelectedPaymentID;
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				$myrow = DB_fetch_row($result);
				$OldName = $myrow[0];
				$sql = "UPDATE paymentmethods
					SET paymentname='" . $_POST['MethodName'] . "',
						paymenttype = " . $_POST['ForPayment'] . ",
						receipttype = " . $_POST['ForReceipt'] . "
					WHERE paymentname ".LIKE." '".$OldName."'";
				
				/* lets leave well alone existing entries 
				if ($_POST['MethodName'] != $OldMeasureName ) {
					// Less work if not required this could take a while.
					$sql = "UPDATE banktrans
						SET banktranstype='" . $_POST['MethodName'] . "'
						WHERE banktranstype ".LIKE." '" . $OldMeasureName . "'";
				}
				*/
			} else {
				$InputError = 1;
				prnMsg( _('The payment method no longer exists.'),'error');
			}
		}
		$msg = _('Record Updated');
		$ErrMsg = _('Could not update payment method');
	} elseif ($InputError !=1) {
		/*SelectedPaymentID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM paymentmethods 
				WHERE paymentname " .LIKE. " '".$_POST['MethodName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The payment method can not be created because another with the same name already exists.'),'error');
		} else {
			$sql = "INSERT INTO paymentmethods (
						paymentname, 
						paymenttype, 
						receipttype)
				VALUES (
					'" . $_POST['MethodName'] ."',
					" . $_POST['ForPayment'] . ",
					" . $_POST['ForReceipt'] . "
					)";
		}
		$msg = _('Record inserted');
		$ErrMsg = _('Could not insert payment method');
	}

	if ($InputError!=1){
		$result = DB_query($sql,$db, $ErrMsg);
		prnMsg($msg,'success');
	}
	unset ($SelectedPaymentID);
	unset ($_POST['SelectedPaymentID']);
	unset ($_POST['MethodName']);
	unset ($_POST['ForPayment']);
	unset ($_POST['ForReceipt']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the payment method the ID is just a secure way to find the payment method
	$sql = "SELECT paymentname FROM paymentmethods 
		WHERE paymentid = " . $SelectedPaymentID;
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('Cannot delete this payment method because it no longer exist'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM banktrans WHERE banktranstype ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this payment method because bank transactions have been created using this payment method'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('bank transactions that refer to this payment method') . '</font>';
		} else {
			$sql="DELETE FROM paymentmethods WHERE paymentname ".LIKE."'" . $OldMeasureName . "'";
			$result = DB_query($sql,$db);
			prnMsg( $OldMeasureName . ' ' . _('payment method has been deleted') . '!','success');
		} //end if not used
	} //end if payment method exist
	unset ($SelectedPaymentID);
	unset ($_GET['SelectedPaymentID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedPaymentID']);
	unset ($_POST['MethodID']);
	unset ($_POST['MethodName']);
	unset ($_POST['ForPayment']);
	unset ($_POST['ForReceipt']);
}

 if (!isset($SelectedPaymentID)) {

/* A payment method could be posted when one has been edited and is being updated 
  or GOT when selected for modification
  SelectedPaymentID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of payment methods will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT paymentid,
			paymentname,
			paymenttype,
			receipttype
			FROM paymentmethods
			ORDER BY paymentid";

	$ErrMsg = _('Could not get payment methods because');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<table>
		<tr>
		<th>" . _('Payment Method') . "</th>
		<th>" . _('For Payments') . "</th>
		<th>" . _('For Receipts') . "</th>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow['paymentname'] . '</td>';
		echo '<td>' . ($myrow['paymenttype'] ? _('Yes') : _('No')) . '</td>';
		echo '<td>' . ($myrow['receipttype'] ? _('Yes') : _('No')) . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedPaymentID=' . $myrow['paymentid'] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedPaymentID=' . $myrow['paymentid'] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedPaymentID)) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Review Payment Methods') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedPaymentID)) {
		//editing an existing section

		$sql = "SELECT paymentid,
				paymentname,
				paymenttype,
				receipttype
				FROM paymentmethods
				WHERE paymentid=" . $SelectedPaymentID;

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested payment method, please try again.'),'warn');
			unset($SelectedPaymentID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MethodID'] = $myrow['paymentid'];
			$_POST['MethodName'] = $myrow['paymentname'];
			$_POST['ForPayment'] = $myrow['paymenttype'];
			$_POST['ForReceipt'] = $myrow['receipttype'];

			echo "<input type=hidden name='SelectedPaymentID' VALUE='" . $_POST['MethodID'] . "'>";
			echo "<table>";
		}

	}  else {
		$_POST['MethodName']='';
		$_POST['ForPayment'] = 1; // Default is use for payment
		$_POST['ForReceipt'] = 1; // Default is use for receipts
		echo "<table>";
	}
	echo "<tr>
		<td>" . _('Payment Method') . ':' . "</td>
		<td><input type='Text' ". (in_array('MethodName',$Errors) ? 'class="inputerror"' : '' ) ." name='MethodName' size=30 maxlength=30 value='" . $_POST['MethodName'] . "'></td>
		</tr>";
	echo "<tr>
		<td>" . _('Use For Payments') . ':' . "</td>
		<td><select name='ForPayment'>";
	echo "<option".($_POST['ForPayment'] ? ' selected' : '') ." VALUE='1'>" . _('Yes');
	echo "<option".($_POST['ForPayment'] ? '' : ' selected') ." VALUE='0'>" . _('No');
	echo "</select></td></tr>";
	echo "<tr>
		<td>" . _('Use For Receipts') . ':' . "</td>
		<td><select name='ForReceipt'>";
	echo "<option".($_POST['ForReceipt'] ? ' selected' : '') ." VALUE='1'>" . _('Yes');
	echo "<option".($_POST['ForReceipt'] ? '' : ' selected') ." VALUE='0'>" . _('No');
	echo "</select></td></tr>";

	echo '</table>';

	echo '<div class="centre"><input type=Submit name=submit value=' . _('Enter Information') . '></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
