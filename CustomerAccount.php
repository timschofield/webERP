<?php

/* Shows customer account/statement on screen rather than PDF. */

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Account');// Screen identification.
$ViewTopic = 'ARInquiries';// Filename in ManualContents.php's TOC.
$BookMark = 'CustomerAccount';// Anchor's id in the manual's html document.
include('includes/header.php');

if (isset($_POST['TransAfterDate'])) {$_POST['TransAfterDate'] = ConvertSQLDate($_POST['TransAfterDate']);}

// always figure out the SQL required from the inputs available

if (!isset($_GET['CustomerID']) and !isset($_SESSION['CustomerID'])) {
	prnMsg(__('To display the account a customer must first be selected from the customer selection screen'), 'info');
	echo '<br /><div class="centre"><a href="', $RootPath, '/SelectCustomer.php">', __('Select a Customer Account to Display'), '</a></div>';
	include('includes/footer.php');
	exit();
} else {
	if (isset($_GET['CustomerID'])) {
		$_SESSION['CustomerID'] = stripslashes($_GET['CustomerID']);
	}
	$CustomerID = $_SESSION['CustomerID'];
}
//Check if the users have proper authority
if ($_SESSION['SalesmanLogin'] != '') {
	$ViewAllowed = false;
	$SQL = "SELECT salesman FROM custbranch WHERE debtorno = '" . $CustomerID . "'";
	$ErrMsg = __('Failed to retrieve sales data');
	$Result = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($Result)>0) {
		while($MyRow = DB_fetch_array($Result)) {
			if ($_SESSION['SalesmanLogin'] == $MyRow['salesman']) {
				$ViewAllowed = true;
			}
		}
	} else {
		prnMsg(__('There is no salesman data set for this customer'),'error');
		include('includes/footer.php');
		exit();
	}
	if (!$ViewAllowed) {
		prnMsg(__('You have no authority to review this customer account'),'error');
		include('includes/footer.php');
		exit();
	}
}


if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - $_SESSION['NumberOfMonthMustBeShown'], Date('d'), Date('Y')));
}

$Transactions = array();

/*now get all the settled transactions which were allocated this month */
$ErrMsg = __('There was a problem retrieving the transactions that were settled over the course of the last month for'). ' ' . $CustomerID . ' ' . __('from the database');
if ($_SESSION['Show_Settled_LastMonth']==1) {
	$SQL = "SELECT DISTINCT debtortrans.id,
						debtortrans.type,
						systypes.typename,
						debtortrans.branchcode,
						debtortrans.reference,
						debtortrans.invtext,
						debtortrans.order_,
						debtortrans.transno,
						debtortrans.trandate,
						debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst AS totalamount,
						debtortrans.alloc,
						debtortrans.balance AS balance,
						debtortrans.settled
				FROM debtortrans INNER JOIN systypes
					ON debtortrans.type=systypes.typeid
				INNER JOIN custallocns
					ON (debtortrans.id=custallocns.transid_allocfrom
						OR debtortrans.id=custallocns.transid_allocto)
				WHERE custallocns.datealloc >='" . FormatDateForSQL($_POST['TransAfterDate']) . "'
				AND debtortrans.debtorno='" . $CustomerID . "'
				AND debtortrans.settled=1
				ORDER BY debtortrans.id";

	$SetldTrans=DB_query($SQL, $ErrMsg);
	$NumberOfRecordsReturned = DB_num_rows($SetldTrans);
	while ($MyRow=DB_fetch_array($SetldTrans)) {
		$Transactions[] =  $MyRow;
	}
} else {
	$NumberOfRecordsReturned=0;
}

/*now get all the outstanding transaction ie Settled=0 */
$ErrMsg =  __('There was a problem retrieving the outstanding transactions for') . ' ' .	$CustomerID . ' '. __('from the database') . '.';
$SQL = "SELECT debtortrans.id,
			debtortrans.type,
			systypes.typename,
			debtortrans.branchcode,
			debtortrans.reference,
			debtortrans.invtext,
			debtortrans.order_,
			debtortrans.transno,
			debtortrans.trandate,
			debtortrans.ovamount+debtortrans.ovdiscount+debtortrans.ovfreight+debtortrans.ovgst as totalamount,
			debtortrans.alloc,
			debtortrans.balance as balance,
			debtortrans.settled
		FROM debtortrans INNER JOIN systypes
			ON debtortrans.type=systypes.typeid
		WHERE debtortrans.debtorno='" . $CustomerID . "'
		AND debtortrans.settled=0";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " ORDER BY debtortrans.id";

$OstdgTrans=DB_query($SQL, $ErrMsg);
while ($MyRow=DB_fetch_array($OstdgTrans)) {
	$Transactions[] =  $MyRow;
}

$NumberOfRecordsReturned += DB_num_rows($OstdgTrans);

$SQL = "SELECT debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			currencies.currency,
			currencies.decimalplaces,
			paymentterms.terms,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription,
			SUM(debtortrans.balance) AS balance,
			SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >=
				paymentterms.daysbeforedue
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1', 'MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= 0
				THEN debtortrans.balance
				ELSE 0 END
			END) AS due,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
				AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >=
				(paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " . interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') .")) >= " . $_SESSION['PastDueDays1'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			END) AS overdue1,
			Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
				CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
				AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue +
				" . $_SESSION['PastDueDays2'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			ELSE
				CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . interval('1','MONTH') . "), " .
				interval('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . "))
				>= " . $_SESSION['PastDueDays2'] . ")
				THEN debtortrans.balance
				ELSE 0 END
			END) AS overdue2
		FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
		INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
		INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
		INNER JOIN debtortrans
			ON debtorsmaster.debtorno = debtortrans.debtorno
		WHERE
			debtorsmaster.debtorno = '" . $CustomerID . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " GROUP BY
			debtorsmaster.name,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			currencies.decimalplaces,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";

$ErrMsg = __('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);

$CustomerRecord = DB_fetch_array($CustomerResult);

echo '<div class="noPrint centre">
		<a href="', $RootPath, '/SelectCustomer.php">', __('Back to Customer Screen'), '</a>
	</div>';

echo '<table width="100%">
		<tr><th colspan="2">', __('Customer Statement For'), ': ', stripslashes($CustomerID), ' - ', $CustomerRecord['name'], '</th></tr>
		<tr><td colspan="2">', $CustomerRecord['address1'], '</td></tr>';
if($CustomerRecord['address2']!='') {// If not empty, output this line.
	echo '<tr><td colspan="2">', $CustomerRecord['address2'], '</td></tr>';
}
if($CustomerRecord['address3']!='') {// If not empty, output this line.
	echo '<tr><td colspan="2">', $CustomerRecord['address3'], '</td></tr>';
}
echo '	<tr><td colspan="2">', $CustomerRecord['address4'], '</td></tr>
		<tr><td colspan="2">', $CustomerRecord['address5'], ' ', $CustomerRecord['address6'], '</td></tr>
		<tr><th>', __('All amounts stated in'), ':</th><td>', $CustomerRecord['currency'], '</td></tr>
		<tr><th>', __('Terms'), ':</th><td>', $CustomerRecord['terms'], '</th></tr>
		<tr><th>', __('Credit Limit'), ':</th><td>', locale_number_format($CustomerRecord['creditlimit'], 0), '</td></tr>
		<tr><th>', __('Credit Status'), ':</th><td>', $CustomerRecord['reasondescription'], '</td></tr>
	</table>';

if ($CustomerRecord['dissallowinvoices'] != 0) {
	echo '<br /><b><font color="red" size="4">', __('ACCOUNT ON HOLD'), '</font></b><br />';
}
echo '<form onSubmit="return VerifyForm(this);" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" class="centre noPrint">';
echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

echo __('Show all transactions after'), ':
		<input type="date" maxlength="10" name="TransAfterDate" required="required" size="11" tabindex="1" value="', FormatDateForSQL($_POST['TransAfterDate']), '" />',
		'<input name="Refresh Inquiry" tabindex="3" type="submit" value="', __('Refresh Inquiry'), '" />
	</form>';

/* Show a table of the invoices returned by the SQL. */

echo '<br /><table class="selection">
	<thead>
		<tr>
			<th class="SortedColumn">', __('Type'), '</th>
			<th class="SortedColumn">', __('Number'), '</th>
			<th class="SortedColumn">', __('Date'), '</th>
			<th>', __('Branch'), '</th>
			<th class="SortedColumn">', __('Reference'), '</th>
			<th>', __('Comments'), '</th>
			<th>', __('Order'), '</th>
			<th>', __('Charges'), '</th>
			<th>', __('Credits'), '</th>
			<th>', __('Allocated'), '</th>
			<th>', __('Balance'), '</th>
			<th class="noPrint" colspan="4">&nbsp;</th>
		</tr>
	</thead><tbody>';

$OutstandingOrSettled = '';
if ($_SESSION['InvoicePortraitFormat'] == 1) { //Invoice/credits in portrait
	$Orientation = 'portrait';
} else { //produce pdfs in landscape
	$Orientation = 'landscape';
}
foreach ($Transactions as $MyRow) {

	if ($MyRow['settled']==1 AND $OutstandingOrSettled=='') {
		echo '<tr><th colspan="11">', __('TRANSACTIONS SETTLED SINCE'), ' ', $_POST['TransAfterDate'], '</th><th class="noPrint" colspan="4">&nbsp;</th></tr>';
		$OutstandingOrSettled='Settled';
	} elseif (($OutstandingOrSettled=='Settled' OR $OutstandingOrSettled=='') AND $MyRow['settled']==0) {
		echo '<tr><th colspan="11">', __('OUTSTANDING TRANSACTIONS'), ' ', $_POST['TransAfterDate'], '</th><th class="noPrint" colspan="4">&nbsp;</th></tr>';
		$OutstandingOrSettled='Outstanding';
	}

	$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

	if ($MyRow['type']==10) { //its an invoice
		echo '<tr class="striped_row">
			<td>', __($MyRow['typename']), '</td>
			<td class="number">', $MyRow['transno'], '</td>
			<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
			<td>', $MyRow['branchcode'], '</td>
			<td>', $MyRow['reference'], '</td>
			<td style="width:200px">', $MyRow['invtext'], '</td>
			<td class="number">', $MyRow['order_'], '</td>
			<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
			<td>&nbsp;</td>
			<td class="number">', locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']), '</td>
			<td class="number">', locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']), '</td>
			<td class="noPrint">
				<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&View=Yes" title="', __('Click to preview the invoice'), '" target="_blank">
					<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
					__('HTML'), '
				</a>
			</td>
			<td class="noPrint">
				<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True&orientation=' . $Orientation . '" title="', __('Click for PDF'), '" target="_blank">
					<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
					__('PDF'), '
				</a>
			</td>
			<td class="noPrint" title="', __('Click to email the invoice'), '">
				<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/email.png" />', __('Email'), '</a>
			</td>
			<td class="noPrint">&nbsp;</td>
		</tr>';

	} elseif ($MyRow['type'] == 11) {
		echo '<tr class="striped_row">
				<td>', __($MyRow['typename']), '</td>
				<td class="number">', $MyRow['transno'], '</td>
				<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['reference'], '</td>
				<td style="width:200px">', $MyRow['invtext'], '</td>
				<td class="number">', $MyRow['order_'], '</td>
				<td>&nbsp;</td>
				<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']), '</td>
				<td class="noPrint" title="', __('Click to preview the credit note'), '">
					<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" />', __('HTML'), '</a>
				</td>
				<td class="noPrint" title="', __('Click for PDF'), '">
					<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit&amp;PrintPDF=True"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" />', __('PDF'), '</a>
				</td>
				<td class="noPrint" title="', __('Click to email the credit note'), '">
					<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/email.png" />', __('Email'), '</a>
				</td>
				<td class="noPrint" title="', __('Click to allocate funds'), '">
					<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" />', __('Allocation'), '</a>
				</td>
			</tr>';

	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] < 0) {
		/* Show transactions where:
		 * - Is receipt
		 */
		echo '<tr class="striped_row">
				<td>', __($MyRow['typename']), '</td>
				<td class="number">', $MyRow['transno'], '</td>
				<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['reference'], '</td>
				<td style="width:200px">', $MyRow['invtext'], '</td>
				<td class="number">', $MyRow['order_'], '</td>
				<td>&nbsp;</td>
				<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']), '</td>
				<td class="noPrint" title="', __('Click to allocate funds'), '">
					<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" />', __('Allocation'), '</a>
				</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">&nbsp;</td>
			</tr>';

	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] > 0) {
		/* Show transactions where:
		* - Is a negative receipt
		* - User cannot view GL transactions
		*/
		echo '<tr class="striped_row">
				<td>', __($MyRow['typename']), '</td>
				<td class="number">', $MyRow['transno'], '</td>
				<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['reference'], '</td>
				<td style="width:200px">', $MyRow['invtext'], '</td>
				<td class="number">', $MyRow['order_'], '</td>
				<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
				<td>&nbsp;</td>
				<td class="number">', locale_number_format($MyRow['alloc'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['balance'], $CustomerRecord['decimalplaces']), '</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">&nbsp;</td>
			</tr>';
	}
}
//end of while loop

echo '</tbody></table>
	<br />
	<table class="selection" width="70%">
		<tr>
			<th style="width:20%">', __('Total Balance'), '</th>
			<th style="width:20%">', __('Current'), '</th>
			<th style="width:20%">', __('Now Due'), '</th>
			<th style="width:20%">', $_SESSION['PastDueDays1'], '-', $_SESSION['PastDueDays2'], ' ', __('Days Overdue'), '</th>
			<th style="width:20%">', __('Over'), ' ', $_SESSION['PastDueDays2'], ' ', __('Days Overdue'), '</th>
		</tr>
		<tr>
			<td class="number">', locale_number_format($CustomerRecord['balance'], $CustomerRecord['decimalplaces']), '</td>
			<td class="number">', locale_number_format(($CustomerRecord['balance'] - $CustomerRecord['due']), $CustomerRecord['decimalplaces']), '</td>
			<td class="number">', locale_number_format(($CustomerRecord['due'] - $CustomerRecord['overdue1']), $CustomerRecord['decimalplaces']), '</td>
			<td class="number">', locale_number_format(($CustomerRecord['overdue1'] - $CustomerRecord['overdue2']), $CustomerRecord['decimalplaces']), '</td>
			<td class="number">', locale_number_format($CustomerRecord['overdue2'], $CustomerRecord['decimalplaces']), '</td>
		</tr>
	</table>';

include('includes/footer.php');
