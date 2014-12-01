<?php
/* $Id: CustomerInquiry.php 7004 2014-11-24 15:56:19Z rchacon $*/

include('includes/session.inc');
$Title = _('Customer Inquiry');
/* Manual links before header.inc */
$ViewTopic = 'ARInquiries'; // Filename in ManualContents.php's TOC.
$BookMark = 'CustomerInquiry'; // Anchor's id in the manual's html document.
include('includes/header.inc');

// always figure out the SQL required from the inputs available

if (!isset($_GET['CustomerID']) and !isset($_SESSION['CustomerID'])) {
	prnMsg(_('To display the enquiry a customer must first be selected from the customer selection screen'), 'info');
	echo '<br /><div class="centre"><a href="', $RootPath, '/SelectCustomer.php">', _('Select a Customer to Inquire On'), '</a></div>';
	include('includes/footer.inc');
	exit;
} else {
	if (isset($_GET['CustomerID'])) {
		$_SESSION['CustomerID'] = stripslashes($_GET['CustomerID']);
	}
	$CustomerID = $_SESSION['CustomerID'];
}
//Check if the users have proper authority
if ($_SESSION['SalesmanLogin'] != '') {
	$ViewAllowed = false;
	$sql = "SELECT salesman FROM custbranch WHERE debtorno = '" . $CustomerID . "'";
	$ErrMsg = _('Failed to retrieve sales data');
	$result = DB_query($sql,$ErrMsg);
	if(DB_num_rows($result)>0) {
		while($myrow = DB_fetch_array($result)) {
			if ($_SESSION['SalesmanLogin'] == $myrow['salesman']){
				$ViewAllowed = true;
			}
		}
	} else {
		prnMsg(_('There is no salesman data set for this debtor'),'error');
		include('includes/footer.inc');
		exit;
	}
	if (!$ViewAllowed){
		prnMsg(_('You have no authority to review this data'),'error');
		include('includes/footer.inc');
		exit;
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
		prnMsg(_('The balance status should be all or zero balance or not zero balance'), 'error');
		exit;
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
		SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) AS balance,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0 THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
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

$ErrMsg = _('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($CustomerResult) == 0) {

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

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

	$ErrMsg = _('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL, $ErrMsg);

} else {
	$NIL_BALANCE = False;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE == True) {
	$CustomerRecord['balance'] = 0;
	$CustomerRecord['due'] = 0;
	$CustomerRecord['overdue1'] = 0;
	$CustomerRecord['overdue2'] = 0;
}

echo '<div class="toplink">
		<a href="', $RootPath, '/SelectCustomer.php">', _('Back to Customer Screen'), '</a>
	</div>';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $Theme, '/images/customer.png" title="', _('Customer'), '" alt="" />', _('Customer'), ': ', stripslashes($CustomerID), ' - ', $CustomerRecord['name'], '<br />', _('All amounts stated in'), ': ', $CustomerRecord['currency'], '<br />', _('Terms'), ': ', $CustomerRecord['terms'], '<br />', _('Credit Limit'), ': ', locale_number_format($CustomerRecord['creditlimit'], 0), '<br />', _('Credit Status'), ': ', $CustomerRecord['reasondescription'], '
	</p>';

if ($CustomerRecord['dissallowinvoices'] != 0) {
	echo '<br /><font color="red" size="4"><b>', _('ACCOUNT ON HOLD'), '</font></b><br />';
}

echo '<table class="selection" width="70%">
	<tr>
		<th style="width:20%">', _('Total Balance'), '</th>
		<th style="width:20%">', _('Current'), '</th>
		<th style="width:20%">', _('Now Due'), '</th>
		<th style="width:20%">', $_SESSION['PastDueDays1'], '-', $_SESSION['PastDueDays2'], ' ' . _('Days Overdue'), '</th>
		<th style="width:20%">', _('Over'), ' ', $_SESSION['PastDueDays2'], ' ', _('Days Overdue'), '</th>
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
echo _('Show all transactions after'), ':<input tabindex="1" type="text" required="required" class="date" alt="', $_SESSION['DefaultDateFormat'], '" id="datepicker" name="TransAfterDate" value="', $_POST['TransAfterDate'], '" minlength="0" maxlength="10" size="12" />';

echo '<select name="Status">';
if ($_POST['Status'] == '') {
	echo '<option value="" selected="selected">', _('All'), '</option>';
	echo '<option value="1">', _('Invoices not fully allocated'), '</option>';
	echo '<option value="0">', _('Invoices fully allocated'), '</option>';
} else {
	if ($_POST['Status'] == 0) {
		echo '<option value="">', _('All'), '</option>';
		echo '<option value="1">', _('Invoices not fully allocated'), '</option>';
		echo '<option selected="selected" value="0">', _('Invoices fully allocated'), '</option>';
	} elseif ($_POST['Status'] == 1) {
		echo '<option value="" selected="selected">', _('All'), '</option>';
		echo '<option selected="selected" value="1">', _('Invoices not fully allocated'), '</option>';
		echo '<option value="0">', _('Invoices fully allocated'), '</option>';
	}
}

echo '</select>';
echo '<input tabindex="3" type="submit" name="Refresh Inquiry" value="', _('Refresh Inquiry'), '" />
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
				ORDER BY debtortrans.id";

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($TransResult) == 0) {
	echo '<div class="centre">', _('There are no transactions to display since'), ' ', $_POST['TransAfterDate'], '</div>';
	include('includes/footer.inc');
	exit;
}

/* Show a table of the invoices returned by the SQL. */

echo '<table class="selection">
		<tr>
			<th class="ascending">', _('Type'), '</th>
			<th class="ascending">', _('Number'), '</th>
			<th class="ascending">', _('Date'), '</th>
			<th>', _('Branch'), '</th>
			<th class="ascending">', _('Reference'), '</th>
			<th>', _('Comments'), '</th>
			<th>', _('Order'), '</th>
			<th>', _('Total'), '</th>
			<th>', _('Allocated'), '</th>
			<th>', _('Balance'), '</th>
			<th>', _('More Info'), '</th>
			<th>', _('More Info'), '</th>
			<th>', _('More Info'), '</th>
			<th>', _('More Info'), '</th>
			<th>', _('More Info'), '</th>
		</tr>';

$k = 0; //row colour counter
while ($MyRow = DB_fetch_array($TransResult)) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

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
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/Credit_Invoice.php?InvoiceNumber=', $MyRow['transno'], '">', _('Credit '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/credit.png" title="', _('Click to credit the invoice'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('HTML '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/preview.png" title="', _('Click to preview the invoice'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True">', _('PDF '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/pdf.png" title="', _('Click for PDF'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('Email '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', _('Click to email the invoice'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '">', _('View GL Entries'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', _('View GL Entries'), '" alt="" />
						</a>
					</td>
				</tr>';
		} else {
			/* Show transactions where:
			 * - Is invoice
			 * - User can raise credits
			 * - User cannot view GL transactions
			 */
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/Credit_Invoice.php?InvoiceNumber=', $MyRow['transno'], '">' . _('Credit ')  . '
							<img src="', $RootPath, '/css/', $Theme, '/images/credit.png" title="', _('Click to credit the invoice'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('HTML '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/preview.png" title="', _('Click to preview the invoice'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True">', _('PDF '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/pdf.png" title="', _('Click for PDF'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('Email ') . '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', _('Click to email the invoice'), '" alt="" />
						</a>
					</td>
					<td></td>
				</tr>';

		}

	} elseif ($MyRow['type'] == 10) {
		/* Show transactions where:
		 * - Is invoice
		 * - User cannot raise credits
		 * - User cannot view GL transactions
		 */
		echo '<td>', _($MyRow['typename']), '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['reference'], '</td>
				<td style="width:200px">', $MyRow['invtext'], '</td>
				<td>', $MyRow['order_'], '</td>
				<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
				<td></td>
				<td>
					<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('HTML '), '
						<img src="', $RootPath, '/css/', $Theme, '/images/preview.png" title="', _('Click to preview the invoice'), '" alt="" />
					</a>
				</td>
				<td>
					<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice&amp;PrintPDF=True">' . _('PDF ') . '
						<img src="', $RootPath, '/css/', $Theme, '/images/pdf.png" title="', _('Click for PDF'), '" alt="" />
					</a>
				</td>
				<td>
					<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Invoice">', _('Email ') . '
						<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', _('Click to email the invoice'), '" alt="" />
					</a>
				</td>
				<td></td>
			</tr>';

	} elseif ($MyRow['type'] == 11) {
		/* Show transactions where:
		 * - Is credit note
		 * - User can view GL transactions
		 */
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', _('HTML '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/preview.png" title="', _('Click to preview the credit note'), '" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit&amp;PrintPDF=True">', _('PDF '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/pdf.png" title="', _('Click for PDF'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', _('Email'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', _('Click to email the credit note'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '">', _('Allocation'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/allocation.png" title="', _('Click to allocate funds'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '">' . _('View GL Entries') . '
							<img src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', _('View GL Entries'), '" alt="" />
						</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is credit note
			* - User cannot view GL transactions
			*/
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', _('HTML '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/preview.png" title="', _('Click to preview the credit note'), '" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/', $PrintCustomerTransactionScript, '?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit&amp;PrintPDF=True">', _('PDF '), '
							<img src="', $RootPath, '/css/', $Theme, '/images/pdf.png" title="', _('Click for PDF'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/EmailCustTrans.php?FromTransNo=', $MyRow['transno'], '&amp;InvOrCredit=Credit">', _('Email'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/email.png" title="', _('Click to email the credit note'), '" alt="" />
						</a>
					</td>
					<td>
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '">', _('Allocation'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/allocation.png" title="', _('Click to allocate funds'), '" alt="" />
						</a>
					</td>
					<td></td>
				</tr>';

		}
	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] < 0) {
		/* Show transactions where:
		 * - Is receipt
		 * - User can view GL transactions
		 */
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '">', _('Allocation'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/allocation.png" title="', _('Click to allocate funds'), '" alt="" />
						</a>
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '">', _('View GL Entries'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', _('View GL Entries'), '" alt="" />
						</a>
					</td>
				</tr>';

		} else { //no permission for GLTrans Inquiries
		/* Show transactions where:
		 * - Is credit note
		 * - User cannot view GL transactions
		 */
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td>
						<a href="', $RootPath, '/CustomerAllocations.php?AllocTrans=', $MyRow['id'], '">', _('Allocation'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/allocation.png" title="', _('Click to allocate funds'), '" alt="" />
						</a>
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>';

		}
	} elseif ($MyRow['type'] == 12 and $MyRow['totalamount'] > 0) {
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			/* Show transactions where:
			* - Is a negative receipt
			* - User can view GL transactions
			*/
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '">' . _('View GL Entries') . '
						<img src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', _('View GL Entries'), '" alt="" />
					</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is a negative receipt
			* - User cannot view GL transactions
			*/
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>';
		}
	} else {
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and in_array($_SESSION['PageSecurityArray']['GLTransInquiry.php'], $_SESSION['AllowedPageSecurityTokens'])) {
			/* Show transactions where:
			* - Is a misc transaction
			* - User can view GL transactions
			*/
			echo '<td>', _($MyRow['typename']), '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<a href="', $RootPath, '/GLTransInquiry.php?TypeID=', $MyRow['type'], '&amp;TransNo=', $MyRow['transno'], '">', _('View GL Entries'), '
							<img src="', $RootPath, '/css/', $Theme, '/images/gl.png" title="', _('View GL Entries'), '" alt="" />
						</a>
					</td>
				</tr>';

		} else {
			/* Show transactions where:
			* - Is a misc transaction
			* - User cannot view GL transactions
			*/
			echo '<td>', $MyRow['typename'], '</td>
					<td>', $MyRow['transno'], '</td>
					<td>', ConvertSQLDate($MyRow['trandate']), '</td>
					<td>', $MyRow['branchcode'], '</td>
					<td>', $MyRow['reference'], '</td>
					<td style="width:200px">', $MyRow['invtext'], '</td>
					<td>', $MyRow['order_'], '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['totalamount'] - $MyRow['allocated'], $CustomerRecord['decimalplaces']), '</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>';
		}
	}

}
//end of while loop

echo '</table>';
include('includes/footer.inc');
?>