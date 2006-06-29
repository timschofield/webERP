<?php

/* $Revision: 1.11 $ */

include('includes/SQL_CommonFunctions.inc');

$PageSecurity = 1;

include('includes/session.inc');
$title = _('Customer Inquiry');
include('includes/header.inc');

// always figure out the SQL required from the inputs available

if(!isset($_GET['CustomerID']) AND !isset($_SESSION['CustomerID'])){
	prnMsg(_('To display the enquiry a customer must first be selected from the customer selection screen'),'info');
	echo "<BR><CENTER><A HREF='". $rootpath . "/SelectCustomer.php?" . SID . "'>" . _('Select a Customer to Inquire On') . '</A><BR></CENTER>';
	include('includes/footer.inc');
	exit;
} else {
	if (isset($_GET['CustomerID'])){
		$_SESSION['CustomerID'] = $_GET['CustomerID'];
	}
	$CustomerID = $_SESSION['CustomerID'];
}


if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-6,Date('d'),Date('Y')));
}

$SQL = "SELECT debtorsmaster.name, 
		currencies.currency, 
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
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ")) >= " . $_SESSION['PastDueDays1'] . ")
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
$CustomerResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($CustomerResult)==0){

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT debtorsmaster.name, currencies.currency, paymentterms.terms,
	debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription
	FROM debtorsmaster,
	     paymentterms,
	     holdreasons,
	     currencies
	WHERE
	     debtorsmaster.paymentterms = paymentterms.termsindicator
	     AND debtorsmaster.currcode = currencies.currabrev
	     AND debtorsmaster.holdreason = holdreasons.reasoncode
	     AND debtorsmaster.debtorno = '" . $CustomerID . "'";

	$ErrMsg =_('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL,$db,$ErrMsg);

} else {
	$NIL_BALANCE = False;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE==True){
	$CustomerRecord['balance']=0;
	$CustomerRecord['due']=0;
	$CustomerRecord['overdue1']=0;
	$CustomerRecord['overdue2']=0;
}

echo '<CENTER><FONT SIZE=4>' . $CustomerRecord['name'] . ' </FONT></B> - (' . _('All amounts stated in') . ' ' . $CustomerRecord['currency'] . ')</CENTER><BR><B><FONT COLOR=BLUE>' . _('Terms') . ': ' . $CustomerRecord['terms'] . '<BR>' . _('Credit Limit') . ': </B></FONT> ' . number_format($CustomerRecord['creditlimit'],0) . '  <B><FONT COLOR=BLUE>' . _('Credit Status') . ':</B></FONT> ' . $CustomerRecord['reasondescription'];

if ($CustomerRecord['dissallowinvoices']!=0){
	echo '<BR><FONT COLOR=RED SIZE=4><B>' . _('ACCOUNT ON HOLD') . '</FONT></B><BR>';
}

echo "<TABLE WIDTH=100% BORDER=1>
	<TR>
		<td class='tableheader'>" . _('Total Balance') . "</TD>
		<td class='tableheader'>" . _('Current') . "</TD>
		<td class='tableheader'>" . _('Now Due') . "</TD>
		<td class='tableheader'>" . $_SESSION['PastDueDays1'] . "-" . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . "</TD>
		<td class='tableheader'>" . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</TD></TR>';

echo '<TR><TD ALIGN=RIGHT>' . number_format($CustomerRecord['balance'],2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format(($CustomerRecord['balance'] - $CustomerRecord['due']),2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format(($CustomerRecord['due']-$CustomerRecord['overdue1']),2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format(($CustomerRecord['overdue1']-$CustomerRecord['overdue2']) ,2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format($CustomerRecord['overdue2'],2) . '</TD>
	</TR>
	</TABLE>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";
echo _('Show all transactions after') . ": <INPUT type=text name='TransAfterDate' Value='" . $_POST['TransAfterDate'] . "' MAXLENGTH =10 SIZE=12><INPUT TYPE=SUBMIT NAME='Refresh Inquiry' VALUE='" . _('Refresh Inquiry') . "'></FORM>";

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
	FROM debtortrans,
		systypes
	WHERE debtortrans.type = systypes.typeid
	AND debtortrans.debtorno = '" . $CustomerID . "'
	AND debtortrans.trandate >= '$DateAfterCriteria'
	ORDER BY debtortrans.id";

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($TransResult)==0){
	echo _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	include('includes/footer.inc');
	exit;
}
/*show a table of the invoices returned by the SQL */

echo '<TABLE CELLPADDING=2 COLSPAN=7>';

$tableheader = "<TR BGCOLOR =#800000>
		<TD class='tableheader'>" . _('Type') . "</TD>
		<TD class='tableheader'>" . _('Number') . "</TD>
		<TD class='tableheader'>" . _('Date') . "</TD>
		<TD class='tableheader'>" . _('Branch') . "</TD>
		<TD class='tableheader'>" . _('Reference') . "</TD>
		<TD class='tableheader'>" . _('Comments') . "</TD>
		<TD class='tableheader'>" . _('Order') . "</TD>
		<TD class='tableheader'>" . _('Total') . "</TD>
		<TD class='tableheader'>" . _('Allocated') . "</TD>
		<TD class='tableheader'>" . _('Balance') . "</TD></TR>";

echo $tableheader;


$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow['trandate']);

	$base_formatstr = "<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td width='200'>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>";
	$credit_invoice_str = "<td><a href='%s/Credit_Invoice.php?InvoiceNumber=%s'><IMG SRC='%s/credit.gif' TITLE='" . _('Click to credit the invoice') . "'></a></td>";
	$preview_invoice_str = "<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s/preview.gif' TITLE='" . _('Click to preview the invoice') . "'></a></td>
				<td><a target='_blank' href='%s/EmailCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s/email.gif' TITLE='" . _('Click to email the invoice') . "'></a></td>";
	$preview_credit_str = "<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s/preview.gif' TITLE='" . _('Click to preview the credit note') . "'></a></td>
				<td><a target='_blank' href='%s/EmailCustTrans.php?FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s/email.gif' TITLE='" . _('Click to email the credit note') . "'></a></td>";

	if (in_array(3,$_SESSION['AllowedPageSecurityTokens']) && $myrow['type']==10){ /*Show a link to allow an invoice to be credited */

		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])){
			printf($base_formatstr .
				$credit_invoice_str .
				$preview_invoice_str .
				"<td><A HREF='%s/GLTransInquiry.php?%s&TypeID=%s&TransNo=%s'>" . _('GL') . "<A></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno']);
		} else {
			printf($base_formatstr .
				$credit_invoice_str .
				$preview_invoice_str .
				'</tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath, $myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images');
		}

	} elseif($myrow['type']==10) { /*its an invoice but not high enough priveliges to credit it */

		printf($base_formatstr .
			$preview_invoice_str .
			'</tr>',
			$myrow['typename'],
			$myrow['transno'],
			ConvertSQLDate($myrow['trandate']),
			$myrow['branchcode'],
			$myrow['reference'],
			$myrow['invtext'],
			$myrow['order_'],
			number_format($myrow['totalamount'],2),
			number_format($myrow['allocated'],2),
			number_format($myrow['totalamount']-$myrow['allocated'],2),
			$rootpath,
			$myrow['transno'],
			$rootpath.'/css/'.$theme.'/images',
			$rootpath,
			$myrow['transno'],
			$rootpath.'/css/'.$theme.'/images');

	} elseif ($myrow['type']==11) { /*its a credit note */
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])){
			printf($base_formatstr .
				$preview_credit_str .
				"<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'><IMG SRC='%s/allocation.gif' TITLE='" . _('Click to allocate funds') . "'></a></td>
				<td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>" . _('GL') . '<A></td></tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['id'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno']);
		} else {
			printf($base_formatstr .
				$preview_credit_str .
				"<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'><IMG SRC='%s/allocation.gif' TITLE='" . _('Click to allocate funds') . "'></a></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images',
				$rootpath,
				$myrow['id'],
				$rootpath.'/css/'.$theme.'/images');
		}
	} elseif ($myrow['type']==12 AND $myrow['totalamount']<0) { /*its a receipt  which could have an allocation*/
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])){
			printf($base_formatstr .
				"<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'>" . _('Allocation') . "</a></td>
				<td><A HREF='%s/GLTransInquiry.php?%s&TypeID=%s&TransNo=%s'>" . _('Whole') . '<BR>' . _('Batch GL') . "<A></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['id'],
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno']);
		} else {
			printf($base_formatstr .
				"<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'>" . _('Allocation') . "</a></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				$myrow['id']);
		}
	} elseif ($myrow['type']==12 AND $myrow['totalamount']>0) { /*its a negative receipt */
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])){
			printf($base_formatstr .
				"<td><A HREF='%s/GLTransInquiry.php?%s&TypeID=%s&TransNo=%s'>" . _('Whole') . '<BR>' . _('Batch GL') . '<A></td></tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno']);
		} else {
			printf($base_formatstr . '<td></tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2));
		}
	} else {
		if ($_SESSION['CompanyRecord']['gllink_debtors']== 1 AND in_array(8,$_SESSION['AllowedPageSecurityTokens'])){
			printf($base_formatstr .
				"<td><A HREF='%s/GLTransInquiry.php?%s&TypeID=%s&TransNo=%s'>" . _('GL') . '<A></td></tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno']);
		} else {
			printf($base_formatstr . '</tr>',
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2));
		}
	}

	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
	//end of page full new headings if
}
//end of while loop

echo '</table>';
include('includes/footer.inc');
?>