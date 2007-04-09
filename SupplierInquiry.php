<?php

/* $Revision: 1.12 $ */

include('includes/SQL_CommonFunctions.inc');

$PageSecurity=2;

include('includes/session.inc');

$title = _('Supplier Inquiry');

include('includes/header.inc');

echo "<A HREF='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</A><BR>';

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	echo '<BR>' . _('To display the enquiry a Supplier must first be selected from the Supplier selection screen') . 
		  "<BR><CENTER><A HREF='". $rootpath . "/SelectSupplier.php'>" . _('Select a Supplier to Inquire On') . '</A></CENTER>';
	exit;
} else {
	if (isset($_GET['SupplierID'])){
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if (isset($_GET['FromDate'])){
	$_POST['TransAfterDate']=$_GET['FromDate'];
}
if (!isset($_POST['TransAfterDate']) OR !Is_Date($_POST['TransAfterDate'])) {

	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m")-12,Date("d"),Date("Y")));
}


$SQL = 'SELECT suppliers.suppname, 
		currencies.currency, 
		paymentterms.terms,
		SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE 
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1', 'MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ')) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN 
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) > paymentterms.daysbeforedue 
					AND (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays1'] . ')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1','MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') .')) >= ' . $_SESSION['PastDueDays1'] . ')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS overdue1,
		Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays2'] . ') 
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1','MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ')) >= ' . $_SESSION['PastDueDays2'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END ) AS overdue2
		FROM suppliers,
     			paymentterms,
     			currencies,
     			supptrans
		WHERE suppliers.paymentterms = paymentterms.termsindicator
     		AND suppliers.currcode = currencies.currabrev
     		AND suppliers.supplierid = '" . $SupplierID . "'
     		AND suppliers.supplierid = supptrans.supplierno
		GROUP BY suppliers.suppname,
      			currencies.currency,
      			paymentterms.terms,
      			paymentterms.daysbeforedue,
      			paymentterms.dayinfollowingmonth";

$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
$DbgMsg = _('The SQL that failed was');

$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($SupplierResult) == 0){

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT suppliers.suppname, 
			currencies.currency, 
			paymentterms.terms
		FROM suppliers,
	     		paymentterms,
	     		currencies
		WHERE suppliers.paymentterms = paymentterms.termsindicator
		AND suppliers.currcode = currencies.currabrev
		AND suppliers.supplierid = '" . $SupplierID . "'";

	$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
	$DbgMsg = _('The SQL that failed was');

	$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

} else {

	$NIL_BALANCE = False;

}

$SupplierRecord = DB_fetch_array($SupplierResult);

if ($NIL_BALANCE == True){
	$SupplierRecord['balance'] = 0;
	$SupplierRecord['due'] = 0;
	$SupplierRecord['overdue1'] = 0;
	$SupplierRecord['overdue2'] = 0;
}

echo '<CENTER><FONT SIZE=4>' . $SupplierRecord['suppname'] . ' </FONT></B> - (' . _('All amounts stated in') . 
	  ' ' . $SupplierRecord['currency'] . ')</CENTER><BR><B><FONT COLOR=BLUE>' . _('Terms') . ': ' . $SupplierRecord['terms'];

if (isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])){

	if ($_GET['HoldStatus'] == _('Hold')){
		$SQL = 'UPDATE supptrans SET hold=1 WHERE type=' . $_GET['HoldType'] . ' AND transno=' . $_GET['HoldTrans'];
	} elseif ($_GET['HoldStatus'] == _('Release')){
		$SQL = 'UPDATE supptrans SET hold=0 WHERE type=' . $_GET['HoldType'] . ' AND transno=' . $_GET['HoldTrans'];
	}

	$ErrMsg = _('The Supplier Transactions could not be updated because');
	$DbgMsg = _('The SQL that failed was');

	$UpdateResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

}

echo "<TABLE WIDTH=100% BORDER=1><TR><TD CLASS='tableheader'>" . _('Total Balance') . 
	  "</TD><TD CLASS='tableheader'>" . _('Current') . 
	  "</TD><TD CLASS='tableheader'>" . _('Now Due') . 
	  "</TD><TD CLASS='tableheader'>" . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . 
	  ' ' . _('Days Overdue') . 
	  "</TD><TD CLASS='tableheader'>" . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</TD></TR>';

echo '<TR><TD ALIGN=RIGHT>' . number_format($SupplierRecord['balance'],2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['balance'] - $SupplierRecord['due']),2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['due']-$SupplierRecord['overdue1']),2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['overdue1']-$SupplierRecord['overdue2']) ,2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format($SupplierRecord['overdue2'],2) . '</TD></TR></TABLE>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Show all transactions after') . ': ' ."<INPUT TYPE=text NAME='TransAfterDate' VALUE='" . 
	  $_POST['TransAfterDate'] . "' MAXLENGTH =10 SIZE=10><INPUT TYPE=SUBMIT NAME='Refresh Inquiry' VALUE='" . _('Refresh Inquiry') . "'></FORM>";

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$SQL = "SELECT supptrans.id, 
		systypes.typename, 
		supptrans.type, 
		supptrans.transno, 
		supptrans.trandate, 
		supptrans.suppreference, 
		supptrans.rate, 
		(supptrans.ovamount + supptrans.ovgst) AS totalamount, 
		supptrans.alloc AS allocated, 
		supptrans.hold, 
		supptrans.settled, 
		supptrans.transtext,
		supptrans.supplierno
	FROM supptrans, 
		systypes 
	WHERE supptrans.type = systypes.typeid 
	AND supptrans.supplierno = '" . $SupplierID . "' 
	AND supptrans.trandate >= '$DateAfterCriteria' 
	ORDER BY supptrans.trandate";

$ErrMsg = _('No transactions were returned by the SQL because');
$DbgMsg = _('The SQL that failed was');

$TransResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($TransResult) == 0){
	echo '<P>' . _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	include('includes/footer.inc');
	exit;
}

/*show a table of the transactions returned by the SQL */


echo '<TABLE CELLPADDING=2 COLSPAN=7>';
$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Trans') . ' #' . 
		"</TD><TD CLASS='tableheader'>" . _('Type') . 
		"</TD><TD CLASS='tableheader'>" . _('Supplier Ref') . ' #' . 
		"</TD><TD CLASS='tableheader'>" . _('Date') . 
		"</TD><TD CLASS='tableheader'>" . _('Total') . 
		"</TD><TD CLASS='tableheader'>" . _('Allocated') . 
		"</TD><TD CLASS='tableheader'>" . _('Balance') . 
		"</TD><TD CLASS='tableheader'>" . _('Comments') . '</TD></TR>';

echo $TableHeader;

$j = 1;
$k = 0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {

	if ($myrow['hold'] == 0 AND $myrow['settled'] == 0){
		$HoldValue = _('Hold');
	} elseif ($myrow['settled'] == 1) {
		$HoldValue = '';
	}else {
		$HoldValue = _('Release');
	}
	if ($myrow['hold'] == 1){
		echo "<TR BGCOLOR='#DD99BB'>";
	}elseif ($k == 1){
		echo "<TR BGCOLOR='#CCCCCC'>";
		$k = 0;
	} else {
		echo "<TR BGCOLOR='#EEEEEE'>";
		$k = 1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow['trandate']);

	if ($myrow['type'] == 20){ /*Show a link to allow GL postings to be viewed but no link to allocate */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){
			If ($myrow['totalamount'] - $myrow['allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					<TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?%s&TypeID=%s&TransNo=%s'>" . _('View GL Postings') . "</A></td>
					<TD><A HREF='%s/PaymentAllocations.php?%sSuppID=%s&InvID=%s'>" . _('View Payments') . '</A></TD></tr>',
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					number_format($myrow['totalamount'],2),
					number_format($myrow['allocated'],2),
					number_format($myrow['totalamount']-$myrow['allocated'],2),
					$myrow['transtext'],
					$rootpath,
					SID,
					$myrow['type'],
					$myrow['transno'],
					$rootpath,
                                        SID,
                                        $myrow['supplierno'],
                                        $myrow['suppreference']);

			} else {

				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					<TD><A HREF='%s?%s&HoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></TD>
					<TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?TypeID=%s&TransNo=%s'>" .
						  _('View GL Postings') . '</A></TD></TR>',
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					number_format($myrow['totalamount'],2),
					number_format($myrow['allocated'],2),
					number_format($myrow['totalamount'] - $myrow['allocated'],2),
					$myrow['transtext'],
					$_SERVER['PHP_SELF'],
					SID,
					$myrow['type'],
					$myrow['transno'],
					$HoldValue,
					$_POST['TransAfterDate'],
					$HoldValue,
					$rootpath,
					$myrow['type'],
					$myrow['transno']);
			}

		} else {

			if ($myrow['totalamount'] - $myrow['allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				printf("<TD>%s</TD>
					<TD>%s</TD>
                                        <TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					</TD></TR>",
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					number_format($myrow['totalamount'],2),
					number_format($myrow['allocated'],2),
					number_format($myrow['totalamount'] - $myrow['allocated'],2),
					$myrow['transtext']);

			} else {

				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					<TD><A HREF='%s?%s&HoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></TD>
                                        <TD><A HREF='%s/PaymentAllocations.php?%sSuppID=%s&InvID=%s'>" . _('View Payments') . "</A></TR>",
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					number_format($myrow['totalamount'],2),
					number_format($myrow['allocated'],2),
					number_format($myrow['totalamount']-$myrow['allocated'],2),
					$myrow['transtext'],
					$_SERVER['PHP_SELF'],
					SID,
					$myrow['type'],
					$myrow['transno'],
					$HoldValue,
					$_POST['TransAfterDate'],
					$HoldValue,
                                        $rootpath,
                                        SID,
                                        $myrow['supplierno'],
                                        $myrow['suppreference']);

			}
		}

	} else { /*its a credit note or a payment */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){

			printf("<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=LEFT>%s</TD>
				<TD><A HREF='%s/SupplierAllocations.php?%sAllocTrans=%s'>" .
					  _('View Allocations') . "</A></TD>
				<TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>" .
					  _('View GL Postings') . '</A></TD>
				</TR>',
				$myrow['transno'],
				$myrow['typename'],
				$myrow['suppreference'],
				ConvertSQLDate($myrow['trandate']),
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount']-$myrow['allocated'],2),
				$myrow['transtext'],
				$rootpath,
				SID,
				$myrow['id'],
				$rootpath,
				SID,
				$myrow['type'],
				$myrow['transno'] );

		} else { /*Not linked to GL */

			printf("<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=LEFT>%s</TD>
				<TD><A HREF='%s/SupplierAllocations.php?%sAllocTrans=%s'>" . _('View Allocations') . '</A></TD>
				</TR>',
				$myrow['transno'],
				$myrow['typename'],
				$myrow['suppreference'],
				ConvertSQLDate($myrow['trandate']),
				number_format($myrow['totalamount'],2),
				number_format($myrow['allocated'],2),
				number_format($myrow['totalamount'] - $myrow['allocated'],2),
				$myrow['transtext'],
				$rootpath,
				SID,
				$myrow['id']);

		}
	}

	$j++;
	if ($j == 12){
		$j = 1;
		echo $TableHeader;
	}

//end of page full new headings if

}

//end of while loop

echo '</TABLE>';
include('includes/footer.inc');

?>
