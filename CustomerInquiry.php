<?php
/*	$Id$*/

include('includes/session.inc');
$Title = _('Customer Inquiry');
$ViewTopic = 'ARInquiries';// Filename in ManualContents.php's TOC.
$BookMark = 'CustomerInquiry';// Anchor's id in the manual's html document.
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

// always figure out the SQL required from the inputs available

if(!isset($_GET['CustomerID']) AND !isset($_SESSION['CustomerID'])) {
	prnMsg(_('To display the enquiry a customer must first be selected from the customer selection screen'),'info');
	echo '<br /><div class="centre"><a href="'. $RootPath . '/SelectCustomer.php?' . SID . '">' . _('Select a Customer to Inquire On') . '</a><br /></div>';
	include('includes/footer.inc');
	exit;
} else {
	if (isset($_GET['CustomerID'])) {
		$_SESSION['CustomerID'] = $_GET['CustomerID'];
	}
	$CustomerID = $_SESSION['CustomerID'];
}

if (!isset($_POST['TransAfterDate'])) {
	$sql = "SELECT confvalue
			FROM `config`
			WHERE confname ='NumberOfMonthMustBeShown'";
	$ErrMsg=_('The config value NumberOfMonthMustBeShown cannot be retrieved');
	$result = DB_query($sql,$db,$ErrMsg);
	$row = DB_fetch_array($result);
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-$row['confvalue'],Date('d'),Date('Y')));
}

$SQL = "SELECT debtorsmaster.name,
		currencies.currency,
		currencies.decimalplaces,
		paymentterms.terms,
		debtorsmaster.creditlimit,
		holdreasons.dissallowinvoices,
		holdreasons.reasondescription,
		SUM(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
- debtortrans.alloc) AS balance,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))', 'DAY') . ")) >= 0 THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " .
		$_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, ". INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
			THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount
			- debtortrans.alloc ELSE 0 END
		END) AS overdue1,
		SUM(CASE WHEN (paymentterms.daysbeforedue > 0) THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue
			AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays2'] . ") THEN debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc ELSE 0 END
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
     		AND debtorsmaster.debtorno = debtortrans.debtorno";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " GROUP BY debtorsmaster.name,
			currencies.currency,
			paymentterms.terms,
			paymentterms.daysbeforedue,
			paymentterms.dayinfollowingmonth,
			debtorsmaster.creditlimit,
			holdreasons.dissallowinvoices,
			holdreasons.reasondescription";

$ErrMsg = _('The customer details could not be retrieved by the SQL because');
$CustomerResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($CustomerResult)==0) {

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
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason = holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode = currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $CustomerID . "'";

	$ErrMsg =_('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL,$db,$ErrMsg);

} else {
	$NIL_BALANCE = False;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE==True) {
	$CustomerRecord['balance']=0;
	$CustomerRecord['due']=0;
	$CustomerRecord['overdue1']=0;
	$CustomerRecord['overdue2']=0;
}
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" /> ' .
	_('Customer') . ': ' .
		$CustomerID . ' - ' . $CustomerRecord['name'] . '<br />' .
	_('All amounts stated in') . ': ' .
		$CustomerRecord['currency'] . '<br />' . // To be replaced by: 
/*		$CustomerRecord['currcode'] . ' - ' . $CurrencyName[$CustomerRecord['currcode']] . '<br />' . // <-- Replacement */
	_('Terms') . ': ' .
		$CustomerRecord['terms'] . '<br />' .
	_('Credit Limit') . ': ' .
		locale_number_format($CustomerRecord['creditlimit'],0) . '<br />' .
	_('Credit Status') . ': ' .
		$CustomerRecord['reasondescription'] . '</p>';

if ($CustomerRecord['dissallowinvoices']!=0) {
	echo '<br /><font color="red" size="4"><b>' . _('ACCOUNT ON HOLD') . '</font></b><br />';
}

echo '<table class="selection" width="70%">
	<tr>
		<th style="width:20%">' . _('Total Balance') . '</th>
		<th style="width:20%">' . _('Current') . '</th>
		<th style="width:20%">' . _('Now Due') . '</th>
		<th style="width:20%">' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
		<th style="width:20%">' . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th></tr>';

echo '<tr>
		<td class="number">' . locale_number_format($CustomerRecord['balance'],$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($CustomerRecord['balance'] - $CustomerRecord['due']),$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($CustomerRecord['due']-$CustomerRecord['overdue1']),$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format(($CustomerRecord['overdue1']-$CustomerRecord['overdue2']) ,$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CustomerRecord['overdue2'],$CustomerRecord['decimalplaces']) . '</td>
	</tr>
	</table>';

echo '<br />
	<div class="centre">
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
        <div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />'
		. _('Show all transactions after') . ': <input tabindex="1" type="text" required="required" title="' . _('A date after which all transactions for the customer should be displayed must be entered') . '" class="date" alt="' .$_SESSION['DefaultDateFormat']. '" id="datepicker" name="TransAfterDate" value="' . $_POST['TransAfterDate'] . '" maxlength="10" size="12" />
		<input tabindex="2" type="submit" name="Refresh Inquiry" value="' . _('Refresh Inquiry') . '" />
	    </div>
	</form>
    </div>
	<br />';

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
				debtortrans.rate,
				(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) AS totalamount,
				debtortrans.alloc AS allocated
		FROM debtortrans INNER JOIN systypes
		ON debtortrans.type = systypes.typeid
		WHERE debtortrans.debtorno = '" . $CustomerID . "'
		AND debtortrans.trandate >= '" . $DateAfterCriteria . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$SQL .= " ORDER BY debtortrans.id";

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($TransResult)==0) {
	echo '<div class="centre">' . _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'] . '</div>';
	include('includes/footer.inc');
	exit;
}
/* Show a table of the invoices returned by the SQL. */

echo '<table class="selection">
	<tr>
		<th class="ascending">' . _('Date') . '</th>
		<th class="ascending">' . _('Type') . '</th>
		<th class="ascending">' . _('Number') . '</th>
		<th class="ascending">' . _('Reference') . '</th>
		<th>' . _('Comments') . '</th>
		<th>' . _('Branch') . '</th>
		<th>' . _('Order') . '</th>
		<th>' . _('Total') . '</th>
		<th>' . _('Allocated') . '</th>
		<th>' . _('Balance') . '</th>
		<th>' . _('More Info') . '</th>
		<th>' . _('More Info') . '</th>
		<th>' . _('More Info') . '</th>
		<th>' . _('More Info') . '</th>
		<th>' . _('More Info') . '</th>
	</tr>';

$j = 1;
$k = 0;// Row colour counter.
while ($myrow=DB_fetch_array($TransResult)) {

	if ($k==1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

/*	$FormatedTranDate = ConvertSQLDate($myrow['trandate']);*/

	if ($_SESSION['InvoicePortraitFormat']==1){// Invoice/credits in portrait.
		$PrintCustomerTransactionScript = 'PrintCustTransPortrait.php';
	} else {// Produce pdfs in landscape.
		$PrintCustomerTransactionScript = 'PrintCustTrans.php';
	}

	// All table-row (tag tr) must have 15 table-datacells (tag td).

	$BaseTD10 = '<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
		<td>' . _($myrow['typename']) . '</td>
		<td class="number">' . $myrow['transno'] . '</td>
		<td>' . $myrow['reference'] . '</td>
		<td>' . $myrow['invtext'] . '</td>
		<td>' . $myrow['branchcode'] . '</td>
		<td class="number">' . $myrow['order_'] . '</td>
		<td class="number">' . locale_number_format($myrow['totalamount'],$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($myrow['allocated'],$CustomerRecord['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($myrow['totalamount']-$myrow['allocated'],$CustomerRecord['decimalplaces']) . '</td>';

	$CreditInvoiceTD1 = '<td><a href="' . $RootPath . '/Credit_Invoice.php?InvoiceNumber=%s" title="' .
			_('Click to credit the invoice') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/credit.gif" /> ' . _('Credit') . '</a></td>';

	$AllocationTD1 = '<td><a href="' . $RootPath . '/CustomerAllocations.php?AllocTrans=%s" title="' .
			_('Click to allocate funds') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/allocation.png" /> ' . _('Allocation') . '</a></td>';

	$PreviewInvoiceTD3 = '<td><a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Invoice" title="' .
			_('Click to preview the invoice') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/preview.gif" /> ' . _('HTML') . '</a></td>
		<td><a href="' . $RootPath .'/%s?FromTransNo=%s&amp;InvOrCredit=Invoice&amp;PrintPDF=True" title="' .
			_('Click for PDF') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/pdf.png" /> ' .	_('PDF') . ' </a></td>
		<td><a href="' . $RootPath .'/EmailCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Invoice" title="' .
			_('Click to email the invoice') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/email.gif" /> ' . _('Email') . '</a></td>';

	$PreviewCreditTD3 = '<td><a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Credit" title="' .
			_('Click to preview the credit note') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/preview.gif" /> ' . _('HTML') . '</a></td>
		<td><a href="' . $RootPath . '/%s?FromTransNo=%s&amp;InvOrCredit=Credit&amp;PrintPDF=True" title="' .
			_('Click for PDF') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/pdf.png" /> ' . _('PDF') . ' </a></td>
		<td><a href="' . $RootPath . '/EmailCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Credit" title="' .
			_('Click to email the credit note') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/email.gif" /> ' . _('Email') . '</a></td>';

	$GLEntriesTD1 = '<td><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=%s&amp;TransNo=%s" target="_blank" title="' .
			_('Click to view the GL entries') . '"><img alt="" src="' . $RootPath .
			'/css/' . $Theme . '/images/gl.png" width="16" /> ' . _('GL Entries') . '</a></td>';

	/* assumed allowed page security token 3 allows the user to create credits for invoices */
	if (in_array(3,$_SESSION['AllowedPageSecurityTokens']) AND $myrow['type']==10) {
		/*Show a link to allow an invoice to be credited */

		/* assumed allowed page security token 8 allows the user to see GL transaction information */
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])) {
			/* format string with GL inquiry options and for invoice to be credited */
			printf($BaseTD10 . $CreditInvoiceTD1 . $PreviewInvoiceTD3 . $GLEntriesTD1 . '</tr>',
				// $CreditInvoiceTD1 parameters:
				$myrow['transno'],
				// $PreviewInvoiceTD3 parameters:
                $myrow['transno'],
				$PrintCustomerTransactionScript,
				$myrow['transno'],
				$myrow['transno'],
				// $GLEntriesTD1 parameters:
				$myrow['type'],
				$myrow['transno']);

		} else {// No permission to view GL entries.
			printf($BaseTD10 . $CreditInvoiceTD1 . $PreviewInvoiceTD3 . '<td>&nbsp;</td></tr>',
				// $CreditInvoiceTD1 parameters:
				$myrow['transno'],
				// $PreviewInvoiceTD3 parameters:
                $myrow['transno'],
				$PrintCustomerTransactionScript,
				$myrow['transno'],
				$myrow['transno']);

		}
	} elseif($myrow['type']==10) { /*its an invoice but not high enough priveliges to credit it */
		printf($BaseTD10 . '<td>&nbsp;</td>' . $PreviewInvoiceTD3 . '<td>&nbsp;</td></tr>',
				// $PreviewInvoiceTD3 parameters:
                $myrow['transno'],
				$PrintCustomerTransactionScript,
				$myrow['transno'],
				$myrow['transno']);

	} elseif ($myrow['type']==11) { /*its a credit note */
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])) {
			printf($BaseTD10 . $AllocationTD1 . $PreviewCreditTD3 . $GLEntriesTD1 . '</tr>',
				// $AllocationTD1 parameters:
				$myrow['id'],
				// $PreviewCreditTD3 parameters:
				$myrow['transno'],
				$PrintCustomerTransactionScript,
				$myrow['transno'],
				$myrow['transno'],
				// $GLEntriesTD1 parameters:
				$myrow['type'],
				$myrow['transno']);

		} else {// No permission to view GL entries.
			printf($BaseTD10 . $AllocationTD1 . $PreviewCreditTD3 . '<td>&nbsp;</td></tr>',
				// $AllocationTD1 parameters:
				$myrow['id'],
				// $PreviewCreditTD3 parameters:
				$myrow['transno'],
				$PrintCustomerTransactionScript,
				$myrow['transno'],
				$myrow['transno']);

		}
	} elseif ($myrow['type']==12 AND $myrow['totalamount']<0) { /*its a receipt  which could have an allocation*/

		//If security token 8 in the allowed page security tokens then assumed ok for GL trans inquiries
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])) {
			printf($BaseTD10 . $AllocationTD1 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>' . $GLEntriesTD1 . '</tr>',
				// $AllocationTD1 parameters:
				$myrow['id'],
				// $GLEntriesTD1 parameters:
				$myrow['type'],
				$myrow['transno']);

		} else {// No permission to view GL entries.
			printf($BaseTD10 . $AllocationTD1 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>',
				// $AllocationTD1 parameters:
				$myrow['id']);

		}
	} elseif ($myrow['type']==12 AND $myrow['totalamount']>0) { /*its a negative receipt */

		//If security token 8 in the allowed page security tokens then assumed ok for GL trans inquiries
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])) {
			printf($BaseTD10 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>' . $GLEntriesTD1 . '</tr>',
				// $GLEntriesTD1 parameters:
				$myrow['type'],
				$myrow['transno']);

		} else {// No permission to view GL entries.
			printf($BaseTD10 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'
				);

		}
	} else {
		//If security token 8 in the allowed page security tokens then assumed ok for GL trans inquiries
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])) {
			printf($BaseTD10 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>' . $GLEntriesTD1 . '</tr>',
				// $GLEntriesTD1 parameters:
				$myrow['type'],
				$myrow['transno']);

		} else {
			printf($BaseTD10 . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'
				);
		}
	}

}
//end of while loop

echo '</table>';
include('includes/footer.inc');
?>
