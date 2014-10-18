<?php

/* $Id: SupplierInquiry.php 6494 2013-12-11 20:33:46Z rchacon $*/

include('includes/SQL_CommonFunctions.inc');
include('includes/session.inc');
$Title = _('Supplier Inquiry');
include('includes/header.inc');

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	echo '<br />' . _('To display the enquiry a Supplier must first be selected from the Supplier selection screen') .
		 '<br />
			<div class="centre">
				<a href="' . $RootPath . '/SelectSupplier.php">' . _('Select a Supplier to Inquire On') . '</a>
			</div>';
	include('includes/footer.inc');
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
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-12,Date('d'),Date('Y')));
}


$SQL = "SELECT suppliers.suppname,
		suppliers.currcode,
		currencies.currency,
		currencies.decimalplaces AS currdecimalplaces,
		paymentterms.terms,
		SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) > paymentterms.daysbeforedue
					AND (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') .")) >= '" . $_SESSION['PastDueDays1'] . "')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS overdue1,
		Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1','MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= '" . $_SESSION['PastDueDays2'] . "')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END ) AS overdue2
		FROM suppliers INNER JOIN paymentterms
		ON suppliers.paymentterms = paymentterms.termsindicator
     	INNER JOIN currencies
     	ON suppliers.currcode = currencies.currabrev
     	INNER JOIN supptrans
     	ON suppliers.supplierid = supptrans.supplierno
		WHERE suppliers.supplierid = '" . $SupplierID . "'
		GROUP BY suppliers.suppname,
      			currencies.currency,
      			currencies.decimalplaces,
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
					suppliers.currcode,
					currencies.currency,
					currencies.decimalplaces AS currdecimalplaces,
					paymentterms.terms
			FROM suppliers INNER JOIN paymentterms
		    ON suppliers.paymentterms = paymentterms.termsindicator
		    INNER JOIN currencies
		    ON suppliers.currcode = currencies.currabrev
			WHERE suppliers.supplierid = '" . $SupplierID . "'";

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
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Supplier') . '" alt="" /> ' .
		_('Supplier') . ': ' .
			$SupplierID . ' - ' . $SupplierRecord['suppname'] . '<br />' .
		_('All amounts stated in') . ': ' .
			$SupplierRecord['currcode'] . ' - ' . $CurrencyName[$SupplierRecord['currcode']] . '<br />' .
		_('Terms') . ': ' .
			$SupplierRecord['terms'] . '</p>';

if (isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])){

	if ($_GET['HoldStatus'] == _('Hold')){
		$SQL = "UPDATE supptrans SET hold=1
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	} elseif ($_GET['HoldStatus'] == _('Release')){
		$SQL = "UPDATE supptrans SET hold=0
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	}

	$ErrMsg = _('The Supplier Transactions could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$UpdateResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

}

echo '<table width="90%" class="selection">
		<tr><th>' . _('Total Balance') . '</th>
			<th>' . _('Current') . '</th>
			<th>' . _('Now Due') . '</th>
			<th>' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
			<th>' . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
		</tr>';

echo '<tr>
		  <td class="number">' . locale_number_format($SupplierRecord['balance'],$SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['balance'] - $SupplierRecord['due']),$SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['due']-$SupplierRecord['overdue1']),$SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['overdue1']-$SupplierRecord['overdue2']) ,$SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format($SupplierRecord['overdue2'],$SupplierRecord['currdecimalplaces']) . '</td>
	  </tr>
	</table>';

echo '<br />
	<div class="centre">
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Show all transactions after') . ': '  . '<input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="TransAfterDate" value="' . $_POST['TransAfterDate'] . '" maxlength="10" size="10" />
	    <input type="submit" name="Refresh Inquiry" value="' . _('Refresh Inquiry') . '" />
    </div>
	</form>
	<br />';
echo '</div>';
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
		AND supptrans.trandate >= '" . $DateAfterCriteria . "'
		ORDER BY supptrans.trandate";

$ErrMsg = _('No transactions were returned by the SQL because');
$DbgMsg = _('The SQL that failed was');

$TransResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($TransResult) == 0){
	echo '<br /><div class="centre">' . _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	echo '</div>';
	include('includes/footer.inc');
	exit;
}

/*show a table of the transactions returned by the SQL */


echo '<table width="90%" class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . _('Trans') . ' #'  . '</th>
					<th class="ascending">' . _('Type') .	'</th>
					<th class="ascending">' . _('Supplier Ref') . '</th>
					<th class="ascending">' . _('Date') . '</th>
					<th class="ascending">' . _('Total') . '</th>
					<th class="ascending">' . _('Allocated') . '</th>
					<th class="ascending">' . _('Balance') . '</th>
					<th class="ascending">' . _('Comments') . '</th>
					<th colspan="2"></th>
				</tr>';

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
//		echo "<tr bgcolor='#DD99BB'>";
	}elseif ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow['trandate']);

	if ($myrow['type'] == 20){ /*Show a link to allow GL postings to be viewed but no link to allocate */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){
			if ($myrow['totalamount'] - $myrow['allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				echo '<td>' . $myrow['transno'] . '</td>
					<td>' . $myrow['typename'] . '</td>
					<td>' . $myrow['suppreference'] . '</td>
					<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
					<td class="number">' . locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces'])  . '</td>
					<td class="number">' . locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']) . '</td>
					<td class="number">' . locale_number_format($myrow['totalamount']-$myrow['allocated'],$SupplierRecord['currdecimalplaces']) . '</td>
					<td align="left">' . $myrow['transtext'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $myrow['type'] . '&amp;TransNo=' . $myrow['transno'] .'">' . _('View GL Postings') . '</a></td>
					<td><a href="' . $RootPath . '/PaymentAllocations.php?SuppID=' . $myrow['supplierno'] . '&amp;InvID=' . $myrow['suppreference'] .'">' . _('View Payments') . '</a></td>
					</tr>';
			} else {
				echo '<td>' . $myrow['transno'] . '</td>
					<td>' . $myrow['typename'] . '</td>
					<td>' . $myrow['suppreference'] . '</td>
					<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
					<td class="number">' . locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces']) . '</td>
					<td class="number">' .locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']) . '</td>
					<td class="number">' . locale_number_format($myrow['totalamount'] - $myrow['allocated'],$SupplierRecord['currdecimalplaces'])  . '</td>
					<td align="left">' . $myrow['transtext'] . '</td>';

				$AuthSQL="SELECT offhold
							FROM purchorderauth
							WHERE userid='" . $_SESSION['UserID'] . "'
							AND currabrev='" . $SupplierRecord['currcode']."'";

				$AuthResult=DB_query($AuthSQL, $db);

				$AuthRow=DB_fetch_array($AuthResult);

				if ($AuthRow[0]==0) {
					echo '<td><a href="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?HoldType=' . $myrow['type'] . '&amp;HoldTrans=' . $myrow['transno']. '&amp;HoldStatus=' . $HoldValue . '&amp;FromDate=' . $_POST['TransAfterDate'].'">' . $HoldValue  . '</a></td>';
				} else {
					if ($HoldValue==_('Release')) {
						echo '<td>' . $HoldValue  . '</a></td>';
					} else {
						echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?' . 'HoldType=' . $myrow['type'] .'&amp;HoldTrans=' . $myrow['transno'] . '&amp;HoldStatus=' . $HoldValue . '&amp;FromDate=' . $_POST['TransAfterDate'] .'">' . $HoldValue  . '</a></td>';
					}
				}
				echo '<td><a target="_blank" href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $myrow['type'] .'&amp;TransNo=' . $myrow['transno'] .'">' ._('View GL Postings') . '</a></td></tr>';
			}
		} else {

			if ($myrow['totalamount'] - $myrow['allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td align="left">%s</td>
						</td></tr>',
						$myrow['transno'],
						$myrow['typename'],
						$myrow['suppreference'],
						ConvertSQLDate($myrow['trandate']),
						locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces']),
						locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']),
						locale_number_format($myrow['totalamount'] - $myrow['allocated'],$SupplierRecord['currdecimalplaces']),
						$myrow['transtext']);

			} else {

				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td align=left>%s</td>
						<td><a href="%s?HoldType=%s&amp;HoldTrans=%s&amp;HoldStatus=%s&amp;FromDate=%s">%s</a></td>
						<td><a href="%s/PaymentAllocations.php?SuppID=%s&amp;InvID=%s">' . _('View Payments') . '</a></tr>',
						$myrow['transno'],
						$myrow['typename'],
						$myrow['suppreference'],
						ConvertSQLDate($myrow['trandate']),
						locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces']),
						locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']),
						locale_number_format($myrow['totalamount']-$myrow['allocated'],$SupplierRecord['currdecimalplaces']),
						$myrow['transtext'],
						htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
						$myrow['type'],
						$myrow['transno'],
						$HoldValue,
						$_POST['TransAfterDate'],
						$HoldValue,
						$RootPath,
						$myrow['supplierno'],
						$myrow['suppreference']);
			}
		}

	} else { /*its a credit note or a payment */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){

			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td align="left">%s</td>
					<td><a href="%s/SupplierAllocations.php?AllocTrans=%s">' . _('View Allocations') . '</a></td>
					<td><a target="_blank" href="%s/GLTransInquiry.php?TypeID=%s&amp;TransNo=%s">' . _('View GL Postings') . '</a></td>
					</tr>',
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces']),
					locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']),
					locale_number_format($myrow['totalamount']-$myrow['allocated'],$SupplierRecord['currdecimalplaces']),
					$myrow['transtext'],
					$RootPath,
					$myrow['id'],
					$RootPath,
					$myrow['type'],
					$myrow['transno'] );

		} else { /*Not linked to GL */

			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td align="left">%s</td>
					<td><a href="%s/SupplierAllocations.php?AllocTrans=%s">' . _('View Allocations') . '</a></td>
					</tr>',
					$myrow['transno'],
					$myrow['typename'],
					$myrow['suppreference'],
					ConvertSQLDate($myrow['trandate']),
					locale_number_format($myrow['totalamount'],$SupplierRecord['currdecimalplaces']),
					locale_number_format($myrow['allocated'],$SupplierRecord['currdecimalplaces']),
					locale_number_format($myrow['totalamount'] - $myrow['allocated'],$SupplierRecord['currdecimalplaces']),
					$myrow['transtext'],
					$RootPath,
					$myrow['id']);

		}
	}//end of page full new headings if
}//end of while loop

echo '</table>';
include('includes/footer.inc');
?>
