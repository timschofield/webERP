<?php

/* Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes. */

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Inquiry');// Screen identification.
$ViewTopic = 'ARInquiries';// Filename's id in ManualContents.php's TOC.
$BookMark = 'CustomerInquiry';// Anchor's id in the manual's html document.
include('includes/header.php');

if (isset($_POST['TransAfterDate'])){$_POST['TransAfterDate'] = ConvertSQLDate($_POST['TransAfterDate']);}

// always figure out the SQL required from the inputs available

if (!isset($_GET['CustomerID']) and !isset($_SESSION['CustomerID'])) {
	prnMsg(__('To display the enquiry a customer must first be selected from the customer selection screen'), 'info');
	echo '<br /><div class="centre"><a href="', $RootPath, '/SelectCustomer.php">', __('Select a Customer to Inquire On'), '</a></div>';
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
			if ($_SESSION['SalesmanLogin'] == $MyRow['salesman']){
				$ViewAllowed = true;
			}
		}
	} else {
		prnMsg(__('There is no salesman data set for this debtor'),'error');
		include('includes/footer.php');
		exit();
	}
	if (!$ViewAllowed){
		prnMsg(__('You have no authority to review this data'),'error');
		include('includes/footer.php');
		exit();
	}
}


if (isset($_GET['Status'])) {
	if (is_numeric($_GET['Status'])) {
		$_POST['Status'] = $_GET['Status'];
	}
} elseif (isset($_POST['Status'])) {
	if($_POST['Status'] == '' or $_POST['Status'] == 1 or $_POST['Status'] == 0) {
		$Status = $_POST['Status'];
	} else {
		prnMsg(__('The balance status should be all or zero balance or not zero balance'), 'error');
		exit();
	}
} else {
	$_POST['Status'] = '';
}

if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - $_SESSION['NumberOfMonthMustBeShown'], Date('d'), Date('Y')));
}

$SQL = "SELECT debtorsmaster.name,
		currencies.currency,
		currencies.decimalplaces,
		paymentterms.terms,
		debtorsmaster.creditlimit,
		holdreasons.dissallowinvoices,
		holdreasons.reasondescription,
		SUM(debtortrans.balance) AS balance,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
			THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN debtortrans.balance ELSE 0 END
		END) AS due,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays1'] . "
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.balance ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(debtortrans.trandate),paymentterms.dayinfollowingmonth)) >= " . $_SESSION['PastDueDays2'] . " THEN debtortrans.balance ELSE 0 END
		END) AS overdue2
		FROM debtorsmaster,
	 			paymentterms,
	 			holdreasons,
	 			currencies,
	 			debtortrans
		WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
	 		AND debtorsmaster.currcode = currencies.currabrev
	 		AND debtorsmaster.holdreason = holdreasons.reasoncode
	 		AND debtorsmaster.debtorno = '" . $CustomerID . "'
	 		AND debtorsmaster.debtorno = debtortrans.debtorno
			GROUP BY debtorsmaster.name,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";

$ErrMsg = __('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($CustomerResult) == 0) {

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = true;

	$SQL = "SELECT debtorsmaster.name,
					debtorsmaster.currcode,
					currencies.currency,
					currencies.decimalplaces,
					paymentterms.terms,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription
			FROM debtorsmaster INNER JOIN paymentterms
			ON debtorsmaster.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
			WHERE debtorsmaster.debtorno = '" . $CustomerID . "'";

	$ErrMsg = __('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL, $ErrMsg);

} else {
	$NIL_BALANCE = false;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE == true) {
	$CustomerRecord['balance'] = 0;
	$CustomerRecord['due'] = 0;
	$CustomerRecord['overdue1'] = 0;
	$CustomerRecord['overdue2'] = 0;
}

echo '<div class="noPrint centre">
		<a href="', $RootPath, '/SelectCustomer.php">', __('Back to Customer Screen'), '</a>
	</div>';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $Theme, '/images/customer.png" title="', __('Customer'), '" alt="" />', __('Customer'), ': ', stripslashes($CustomerID), ' - ', $CustomerRecord['name'], '<br />', __('All amounts stated in'), ': ', $CustomerRecord['currency'], '<br />', __('Terms'), ': ', $CustomerRecord['terms'], '<br />', __('Credit Limit'), ': ', locale_number_format($CustomerRecord['creditlimit'], 0), '<br />', __('Credit Status'), ': ', $CustomerRecord['reasondescription'], '
	</p>';

if ($CustomerRecord['dissallowinvoices'] != 0) {
	echo '<br /><font color="red" size="4"><b>', __('ACCOUNT ON HOLD'), '</font></b><br />';
}

echo '<table class="selection" width="70%">
	<tr>
		<th style="width:20%">', __('Total Balance'), '</th>
		<th style="width:20%">', __('Current'), '</th>
		<th style="width:20%">', __('Now Due'), '</th>
		<th style="width:20%">', $_SESSION['PastDueDays1'], '-', $_SESSION['PastDueDays2'], ' ' . __('Days Overdue'), '</th>
		<th style="width:20%">', __('Over'), ' ', $_SESSION['PastDueDays2'], ' ', __('Days Overdue'), '</th>
	</tr>';

echo '<tr>
		<td class="number">', locale_number_format($CustomerRecord['balance'], $CustomerRecord['decimalplaces']), '</td>
		<td class="number">', locale_number_format(($CustomerRecord['balance'] - $CustomerRecord['due']), $CustomerRecord['decimalplaces']), '</td>
		<td class="number">', locale_number_format(($CustomerRecord['due'] - $CustomerRecord['overdue1']), $CustomerRecord['decimalplaces']), '</td>
		<td class="number">', locale_number_format(($CustomerRecord['overdue1'] - $CustomerRecord['overdue2']), $CustomerRecord['decimalplaces']), '</td>
		<td class="number">', locale_number_format($CustomerRecord['overdue2'], $CustomerRecord['decimalplaces']), '</td>
	</tr>
</table>';

echo '<div class="centre"><form onSubmit="return VerifyForm(this);" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" class="noPrint">
		<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo __('Show all transactions after'), ':<input required="required" type="date" name="TransAfterDate" value="', FormatDateForSQL($_POST['TransAfterDate']), '" maxlength="10" size="11" />';

echo '<select name="Status">';
if ($_POST['Status'] == '') {
	echo '<option value="" selected="selected">', __('All'), '</option>';
	echo '<option value="1">', __('Invoices not fully allocated'), '</option>';
	echo '<option value="0">', __('Invoices fully allocated'), '</option>';
} else {
	if ($_POST['Status'] == 0) {
		echo '<option value="">', __('All'), '</option>';
		echo '<option value="1">', __('Invoices not fully allocated'), '</option>';
		echo '<option selected="selected" value="0">', __('Invoices fully allocated'), '</option>';
	} elseif ($_POST['Status'] == 1) {
		echo '<option value="" selected="selected">', __('All'), '</option>';
		echo '<option selected="selected" value="1">', __('Invoices not fully allocated'), '</option>';
		echo '<option value="0">', __('Invoices fully allocated'), '</option>';
	}
}

echo '</select>';
echo '<input class="noPrint" name="Refresh Inquiry" type="submit" value="', __('Refresh Inquiry'), '" />
	</form></div>';

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$SQL = "SELECT systypes.typename,
				debtortrans.id,
				debtortrans.type,
				debtortrans.transno,
				debtortrans.branchcode,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				salesorders.customerref,
				debtortrans.rate,
				(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) AS totalamount,
				debtortrans.alloc AS allocated
			FROM debtortrans
			INNER JOIN systypes
				ON debtortrans.type = systypes.typeid
			LEFT JOIN salesorders
				ON salesorders.orderno=debtortrans.order_
			WHERE debtortrans.debtorno = '" . $CustomerID . "'
				AND debtortrans.trandate >= '" . $DateAfterCriteria . "'
				ORDER BY debtortrans.trandate,
					debtortrans.id";

$ErrMsg = __('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($TransResult) == 0) {
	echo '<div class="centre">', __('There are no transactions to display since'), ' ', $_POST['TransAfterDate'], '</div>';
	include('includes/footer.php');
	exit();
}

/* Show a table of the invoices returned by the SQL. */

echo '<table class="selection"><thead>
	<tr>
		<th class="SortedColumn">', __('Type'), '</th>
		<th class="SortedColumn">', __('Number'), '</th>
		<th class="SortedColumn">', __('Date'), '</th>
		<th>', __('Branch'), '</th>
		<th class="SortedColumn">', __('Reference'), '</th>
		<th>', __('Comments'), '</th>
		<th>', __('Order'), '</th>
		<th>', __('Total'), '</th>
		<th>', __('Allocated'), '</th>
		<th>', __('Balance'), '</th>
		<th class="noPrint">', __('More Info'), '</th>
		<th class="noPrint">', __('More Info'), '</th>
		<th class="noPrint">', __('More Info'), '</th>
		<th class="noPrint">', __('More Info'), '</th>
		<th class="noPrint">', __('More Info'), '</th>
	</tr>
	</thead><tbody>';

while ($MyRow = DB_fetch_array($TransResult)) {

	$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

	if ($_SESSION['InvoicePortraitFormat'] == 1) { //Invoice/credits in portrait
		$PrintCustomerTransactionScript = 'PrintCustTransPortrait.php';
	} else { //produce pdfs in landscape
		$PrintCustomerTransactionScript = 'PrintCustTrans.php';
	}

	/* if the user is allowed to create credits for invoices */
	if (in_array($_SESSION['PageSecurityArray']['Credit_Invoice.php'], $_SESSION['AllowedPageSecurityTokens']) and $MyRow['type'] == 10) {
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			/* Show transactions where:
			 * - Is invoice
			 * - User can raise credits
			 * - User can view GL transactions
			 */
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '" target="_blank">' . $MyRow['transno'] . '</a></td>
					<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/Credit_Invoice.php?InvoiceNumber=', $MyRow['transno'], '" title="', __('Click to credit the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/credit.png" /> ',
							__('Credit'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to preview the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
							__('HTML'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True" title="', __('Click for PDF'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
							__('PDF'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to email the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/email.png" /> ', __('Email'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '" title="', __('Click to view the GL entries'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ',
							__('GL Entries'), '
						</a>
					</td>
				</tr>';
		} else {
			/* Show transactions where:
			 * - Is invoice
			 * - User can raise credits
			 * - User cannot view GL transactions
			 */
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '">' . $MyRow['transno'] . '</a></td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/Credit_Invoice.php?InvoiceNumber=', $MyRow['transno'], '" title="', __('Click to credit the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/credit.png" /> ',
							__('Credit'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to preview the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
							__('HTML'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True" title="', __('Click for PDF'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
							__('PDF'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to email the invoice'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/email.png" /> ', __('Email'), '
						</a>
					</td>
					<td class="noPrint">&nbsp;</td>
				</tr>';

		}

	} elseif ($MyRow['type'] == 10) {
		/* Show transactions where:
		 * - Is invoice
		 * - User cannot raise credits
		 * - User cannot view GL transactions
		 */
		echo '<tr class="striped_row">
				<td>', __($MyRow['typename']), '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['reference'], '</td>
				<td style="width:200px">', $MyRow['invtext'], '</td>
				<td>', $MyRow['order_'], '</td>
				<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
				<td class="noPrint">&nbsp;</td>
				<td class="noPrint">
					<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to preview the invoice'), '">
						<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
						__('HTML'), '
					</a>
				</td>
				<td class="noPrint">
					<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True" title="', __('Click for PDF'), '">
						<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
						__('PDF'), '
					</a>
				</td>
				<td class="noPrint">
					<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice" title="', __('Click to email the invoice'), '">
						<img alt="" src="', $RootPath, '/css/', $Theme, '/images/email.png" /> ', __('Email'), '
					</a>
				</td>
				<td class="noPrint">&nbsp;</td>
			</tr>';

	} elseif ($MyRow['type'] == 11) {
		/* Show transactions where:
		 * - Is credit note
		 * - User can view GL transactions
		 */
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '">' . $MyRow['transno'] . '</a></td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit" title="', __('Click to preview the credit note'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
							__('HTML'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit&amp;PrintPDF=True" title="', __('Click for PDF'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
							__('PDF'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', __('Email'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', __('Click to email the credit note'), '" alt="" />
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '" title="', __('Click to allocate funds'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" /> ',
							__('Allocation'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '" title="', __('Click to view the GL entries'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ',
							__('GL Entries'), '
						</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is credit note
			* - User cannot view GL transactions
			*/
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '">' . $MyRow['transno'] . '</a></td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit" title="', __('Click to preview the credit note'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/preview.png" /> ',
							__('HTML'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit&amp;PrintPDF=True" title="', __('Click for PDF'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/pdf.png" /> ',
							__('PDF'), '
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', __('Email'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', __('Click to email the credit note'), '" alt="" />
						</a>
					</td>
					<td class="noPrint">
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '" title="', __('Click to allocate funds'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" /> ',
							__('Allocation'), '
						</a>
					</td>
					<td class="noPrint">&nbsp;</td>
				</tr>';

		}
	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] < 0) {
		/* Show transactions where:
		 * - Is receipt
		 * - User can view GL transactions
		 */
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td><a href="' . $RootPath . '/CustWhereAlloc.php?TransType=' . $MyRow['type'] . '&TransNo=' . $MyRow['transno'] . '">' . $MyRow['transno'] . '</a></td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '" title="', __('Click to allocate funds'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" /> ',
							__('Allocation'), '
						</a>
					</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '" title="', __('Click to view the GL entries'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ',
							__('GL Entries'), '
						</a>
					</td>
				</tr>';

		} else { //no permission for GLTrans Inquiries
		/* Show transactions where:
		 * - Is credit note
		 * - User cannot view GL transactions
		 */
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '" title="', __('Click to allocate funds'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" /> ',
							__('Allocation'), '
						</a>
					</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
				</tr>';

		}
	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] > 0) {
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			/* Show transactions where:
			* - Is a negative receipt
			* - User can view GL transactions
			*/
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '" title="', __('Click to view the GL entries'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ',
							__('GL Entries'), '
						</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is a negative receipt
			* - User cannot view GL transactions
			*/
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
				</tr>';
		}
	} else {
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			/* Show transactions where:
			* - Is a misc transaction
			* - User can view GL transactions
			*/
			echo '<tr class="striped_row">
					<td>', __($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '" title="', __('Click to view the GL entries'), '">
							<img alt="" src="', $RootPath, '/css/', $Theme, '/images/gl.png" /> ',
							__('GL Entries'), '
						</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is a misc transaction
			* - User cannot view GL transactions
			*/
			echo '<tr class="striped_row">
					<td>', $MyRow['typename'], '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
					<td class="noPrint">&nbsp;</td>
				</tr>';
		}
	}

}
//end of while loop

echo '</tbody></table>';
include('includes/footer.php');
