<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Payment Methods');
$ViewTopic = 'ARTransactions';
$BookMark = 'PaymentMethods';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Payments') .
	'" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedPaymentID']) )
	$SelectedPaymentID = $_GET['SelectedPaymentID'];
elseif (isset($_POST['SelectedPaymentID']))
	$SelectedPaymentID = $_POST['SelectedPaymentID'];

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['MethodName'])) {
		$InputError = 1;
		prnMsg( __('The payment method cannot contain illegal characters') . ' ' . '" \' - &amp; or a space','error');
		$Errors[$i] = 'MethodName';
		$i++;
	}
	if ( trim($_POST['MethodName']) == "") {
		$InputError = 1;
		prnMsg( __('The payment method may not be empty.'),'error');
		$Errors[$i] = 'MethodName';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DiscountPercent']))) {
		$InputError = 1;
		prnMsg( __('The discount percentage must be a number less than 1'),'error');
		$Errors[$i] = 'DiscountPercent';
		$i++;
	} else if (filter_number_format($_POST['DiscountPercent'])>1) {
		$InputError = 1;
		prnMsg( __('The discount percentage must be a number less than 1'),'error');
		$Errors[$i] = 'DiscountPercent';
		$i++;
	} else if (filter_number_format($_POST['DiscountPercent'])<0) {
		$InputError = 1;
		prnMsg( __('The discount percentage must be either zero or less than 1'),'error');
		$Errors[$i] = 'DiscountPercent';
		$i++;
	}
	if (isset($_POST['SelectedPaymentID']) AND $InputError !=1) {

		/*SelectedPaymentID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$SQL = "SELECT count(*) FROM paymentmethods
				WHERE paymentid <> '" . $SelectedPaymentID ."'
				AND paymentname ".LIKE." '" . $_POST['MethodName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The payment method can not be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists need to be very careful here

			$SQL = "SELECT paymentname FROM paymentmethods
					WHERE paymentid = '" . $SelectedPaymentID . "'";
			$Result = DB_query($SQL);
			if ( DB_num_rows($Result) != 0 ) {
				$MyRow = DB_fetch_row($Result);
				$OldName = $MyRow[0];
				$SQL = "UPDATE paymentmethods
						SET paymentname='" . $_POST['MethodName'] . "',
							paymenttype = '" . $_POST['ForPayment'] . "',
							receipttype = '" . $_POST['ForReceipt'] . "',
							usepreprintedstationery = '" . $_POST['UsePrePrintedStationery']. "',
							opencashdrawer = '" . $_POST['OpenCashDrawer'] . "',
							percentdiscount = '" . filter_number_format($_POST['DiscountPercent']) . "'
						WHERE paymentname " . LIKE . " '".$OldName."'";

			} else {
				$InputError = 1;
				prnMsg( __('The payment method no longer exists.'),'error');
			}
		}
		$Msg = __('Record Updated');
		$ErrMsg = __('Could not update payment method');
	} elseif ($InputError !=1) {
		/*SelectedPaymentID is null cos no item selected on first time round so must be adding a record*/
		$SQL = "SELECT count(*) FROM paymentmethods
				WHERE paymentname LIKE'".$_POST['MethodName'] ."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The payment method can not be created because another with the same name already exists.'),'error');
		} else {
			$SQL = "INSERT INTO paymentmethods (paymentname,
												paymenttype,
												receipttype,
												usepreprintedstationery,
												opencashdrawer,
												percentdiscount)
								VALUES ('" . $_POST['MethodName'] ."',
										'" . $_POST['ForPayment'] ."',
										'" . $_POST['ForReceipt'] ."',
										'" . $_POST['UsePrePrintedStationery'] ."',
										'" . $_POST['OpenCashDrawer']  . "',
										'" . filter_number_format($_POST['DiscountPercent']) . "')";
		}
		$Msg = __('New payment method added');
		$ErrMsg = __('Could not insert the new payment method');
	}

	if ($InputError!=1){
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg($Msg,'success');
		echo '<br />';
	}
	unset ($SelectedPaymentID);
	unset ($_POST['SelectedPaymentID']);
	unset ($_POST['MethodName']);
	unset ($_POST['ForPayment']);
	unset ($_POST['ForReceipt']);
	unset ($_POST['OpenCashDrawer']);
	unset ($_POST['UsePrePrintedStationery']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the payment method the ID is just a secure way to find the payment method
	$SQL = "SELECT paymentname FROM paymentmethods
			WHERE paymentid = '" . $SelectedPaymentID . "'";
	$Result = DB_query($SQL);
	if ( DB_num_rows($Result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( __('Cannot delete this payment method because it no longer exist'),'warn');
	} else {
		$MyRow = DB_fetch_row($Result);
		$OldMeasureName = $MyRow[0];
		$SQL= "SELECT COUNT(*) FROM banktrans
				WHERE banktranstype LIKE '" . $OldMeasureName . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg( __('Cannot delete this payment method because bank transactions have been created using this payment method'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('bank transactions that refer to this payment method') . '</font>';
		} else {
			$SQL="DELETE FROM paymentmethods WHERE paymentname " . LIKE  . " '" . $OldMeasureName . "'";
			$Result = DB_query($SQL);
			prnMsg( $OldMeasureName . ' ' . __('payment method has been deleted') . '!','success');
			echo '<br />';
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
	unset ($_POST['OpenCashDrawer']);
}

 if (!isset($SelectedPaymentID)) {

/* A payment method could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedPaymentID will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of payment methods will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$SQL = "SELECT paymentid,
					paymentname,
					paymenttype,
					receipttype,
					usepreprintedstationery,
					opencashdrawer,
					percentdiscount
			FROM paymentmethods
			ORDER BY paymentid";

	$ErrMsg = __('Could not get payment methods because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="SortedColumn">' . __('Payment Method') . '</th>
			<th class="SortedColumn">' . __('Use For Payments') . '</th>
			<th class="SortedColumn">' . __('Use For Receipts') . '</th>
			<th class="SortedColumn">' . __('Use Pre-printed Stationery') . '</th>
			<th class="SortedColumn">' . __('Open POS Cash Drawer for Sale') . '</th>
			<th class="SortedColumn">' . __('Payment discount') . ' %</th>
			<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>' . $MyRow['paymentname'] . '</td>
				<td class="centre">' . ($MyRow['paymenttype'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">' . ($MyRow['receipttype'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">' . ($MyRow['usepreprintedstationery'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">' . ($MyRow['opencashdrawer'] ? __('Yes') : __('No')) . '</td>
				<td class="centre">' . locale_number_format($MyRow['percentdiscount']*100,2) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedPaymentID=' . $MyRow['paymentid'] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedPaymentID=' . $MyRow['paymentid'] . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this payment method?') . '\');">' . __('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table><br />';
} //end of ifs and buts!


if (isset($SelectedPaymentID)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Payment Methods') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPaymentID)) {
		//editing an existing section

		$SQL = "SELECT paymentid,
						paymentname,
						paymenttype,
						receipttype,
						usepreprintedstationery,
						opencashdrawer,
						percentdiscount
				FROM paymentmethods
				WHERE paymentid='" . $SelectedPaymentID . "'";

		$Result = DB_query($SQL);
		if ( DB_num_rows($Result) == 0 ) {
			prnMsg( __('Could not retrieve the requested payment method, please try again.'),'warn');
			unset($SelectedPaymentID);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['MethodID'] = $MyRow['paymentid'];
			$_POST['MethodName'] = $MyRow['paymentname'];
			$_POST['ForPayment'] = $MyRow['paymenttype'];
			$_POST['ForReceipt'] = $MyRow['receipttype'];
			$_POST['UsePrePrintedStationery'] = $MyRow['usepreprintedstationery'];
			$_POST['OpenCashDrawer'] = $MyRow['opencashdrawer'];
			$_POST['DiscountPercent'] = $MyRow['percentdiscount'];

			echo '<input type="hidden" name="SelectedPaymentID" value="' . $_POST['MethodID'] . '" />';
			echo '<fieldset>
					<legend>', __('Edit Payment Method'), '</legend>';
		}

	}  else {
		$_POST['MethodName']='';
		$_POST['ForPayment'] = 1; // Default is use for payment
		$_POST['ForReceipt'] = 1; // Default is use for receipts
		$_POST['UsePrePrintedStationery'] = 0; // Default is use for receipts
		$_POST['OpenCashDrawer'] = 0; //Default is not to open cash drawer
		$_POST['DiscountPercent']=0;
		echo '<fieldset>
					<legend>', __('Create Payment Method'), '</legend>';
	}
	echo '<field>
			<label for="MethodName">' . __('Payment Method') . ':</label>
			<input type="text" '. (in_array('MethodName',$Errors) ? 'class="inputerror"' : '' ) .' name="MethodName" autofocus="autofocus" required="required" size="30" maxlength="30" value="' . $_POST['MethodName'] . '" />
		</field>';
	echo '<field>
			<label for="ForPayment">' . __('Use For Payments') . ':' . '</label>
			<select required="required" name="ForPayment">
				<option' . ($_POST['ForPayment'] ? ' selected="selected"' : '') .' value="1">' . __('Yes') . '</option>
				<option' . ($_POST['ForPayment'] ? '' : ' selected="selected"') .' value="0">' . __('No') . '</option>
			</select>
		</field>';
	echo '<field>
			<label for="ForReceipt">' . __('Use For Receipts') . ':</label>
			<select required="required" name="ForReceipt">
				<option' . ($_POST['ForReceipt'] ? ' selected="selected"' : '') .' value="1">' . __('Yes') . '</option>
				<option' . ($_POST['ForReceipt'] ? '' : ' selected="selected"') .' value="0">' . __('No') . '</option>
			</select>
		</field>';
	echo '<field>
			<label for="UsePrePrintedStationery">' . __('Use Pre-printed Stationery') . ':' . '</label>
			<select name="UsePrePrintedStationery">
				<option' . ($_POST['UsePrePrintedStationery'] ? ' selected="selected"': '' ) .' value="1">' . __('Yes') . '</option>
				<option' . ($_POST['UsePrePrintedStationery']==1 ? '' : ' selected="selected"' ) .' value="0">' . __('No') . '</option>
			</select>
		</field>';
	echo '<field>
			<label for="OpenCashDrawer">' . __('Open POS Cash Drawer for Sale') . ':' . '</label>
			<select name="OpenCashDrawer">
				<option' . ($_POST['OpenCashDrawer'] ? ' selected="selected"' : '') .' value="1">' . __('Yes') . '</option>
				<option' . ($_POST['OpenCashDrawer'] ? '' : ' selected="selected"') .' value="0">' . __('No') . '</option>
			</select>
		</field>';
	echo '<field>
			<label for="DiscountPercent">' . __('Payment Discount Percent on Receipts') . ':' . '</label>
			<input type="text" class="number" min="0" max="1" name="DiscountPercent" value="' . locale_number_format($_POST['DiscountPercent'],2) . '" />
		</field>';
	echo '</fieldset>';

	echo '<div class="centre"><input type="submit" name="submit" value="' . __('Enter Information') . '" /></div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
