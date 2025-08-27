<?php

/* Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding. */

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Inquiry');
$ViewTopic = 'AccountsPayable';// RChacon: Is there any content for Supplier Inquiry?
$BookMark = 'AccountsPayable';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['TransAfterDate'])){$_POST['TransAfterDate'] = ConvertSQLDate($_POST['TransAfterDate']);}

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])) {
	echo '<br />' . __('To display the enquiry a Supplier must first be selected from the Supplier selection screen') .
		 '<br />
			<div class="centre">
				<a href="' . $RootPath . '/SelectSupplier.php">' . __('Select a Supplier to Inquire On') . '</a>
			</div>';
	include('includes/footer.php');
	exit();
} else {
	if(isset($_GET['SupplierID'])) {
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if(isset($_GET['FromDate'])) {
	$_POST['TransAfterDate']=$_GET['FromDate'];
}
if(!isset($_POST['TransAfterDate']) OR !Is_Date($_POST['TransAfterDate'])) {
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
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) > paymentterms.daysbeforedue
					AND (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= '" . $_SESSION['PastDueDays1'] . "'
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS overdue1,
		Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(ADDDATE(last_day(supptrans.trandate),paymentterms.dayinfollowingmonth)) >= '" . $_SESSION['PastDueDays2'] . "'
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
$ErrMsg = __('The supplier details could not be retrieved by the SQL because');
$SupplierResult = DB_query($SQL, $ErrMsg);

if(DB_num_rows($SupplierResult) == 0) {

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = true;

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

	$ErrMsg = __('The supplier details could not be retrieved by the SQL because');

	$SupplierResult = DB_query($SQL, $ErrMsg);

} else {
	$NIL_BALANCE = false;
}

$SupplierRecord = DB_fetch_array($SupplierResult);

if($NIL_BALANCE == true) {
	$SupplierRecord['balance'] = 0;
	$SupplierRecord['due'] = 0;
	$SupplierRecord['overdue1'] = 0;
	$SupplierRecord['overdue2'] = 0;
}
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="', // Icon image.
	__('Supplier'), '" /> ', // Icon title.
	__('Supplier'), ': ', $SupplierID, ' - ', $SupplierRecord['suppname'], '<br />',
		__('All amounts stated in'), ': ', $SupplierRecord['currcode'], ' - ', $CurrencyName[$SupplierRecord['currcode']], '<br />',
		__('Terms'), ': ', $SupplierRecord['terms'], '</p>';// Page title.

if(isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])) {
	if($_GET['HoldStatus'] == __('Hold')) {
		$SQL = "UPDATE supptrans SET hold=1
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	} elseif($_GET['HoldStatus'] == __('Release')) {
		$SQL = "UPDATE supptrans SET hold=0
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	}
	$ErrMsg = __('The Supplier Transactions could not be updated because');
	$UpdateResult = DB_query($SQL, $ErrMsg);
}

echo '<table class="selection">
	<tr>
		<th>' . __('Total Balance') . '</th>
		<th>' . __('Current') . '</th>
		<th>' . __('Now Due') . '</th>
		<th>' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . __('Days Overdue') . '</th>
		<th>' . __('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . __('Days Overdue') . '</th>
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
echo __('Show all transactions after') . ': '  . '<input type="date" name="TransAfterDate" value="' . FormatDateForSQL($_POST['TransAfterDate']) . '" maxlength="10" size="10" />
	    <input class="noPrint" name="Refresh Inquiry" type="submit" value="' . __('Refresh Inquiry') . '" />
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
$ErrMsg = __('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL, $ErrMsg);

if(DB_num_rows($TransResult) == 0) {
	echo '<br /><div class="centre">' . __('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	echo '</div>';
	include('includes/footer.php');
	exit();
}

/*show a table of the transactions returned by the SQL */

echo '<table class="selection"><thead>
	<thead>
	<tr>
		<th class="SortedColumn">' . __('Date') . '</th>
		<th class="SortedColumn">' . __('Type') . '</th>
		<th class="SortedColumn">' . __('Number') . '</th>
		<th class="SortedColumn">' . __('Reference') . '</th>
		<th class="SortedColumn">' . __('Comments') . '</th>
		<th class="SortedColumn">' . __('Total') . '</th>
		<th class="SortedColumn">' . __('Allocated') . '</th>
		<th class="SortedColumn">' . __('Balance') . '</th>
		<th class="noPrint">' . __('More Info') . '</th>
		<th class="noPrint">' . __('More Info') . '</th>
	</tr>
	</thead>
	<tbody>';

$AuthSQL = "SELECT offhold
			FROM purchorderauth
			WHERE userid='" . $_SESSION['UserID'] . "'
			AND currabrev='" . $SupplierRecord['currcode']."'";
$AuthResult = DB_query($AuthSQL);
$AuthRow = DB_fetch_array($AuthResult);

$j = 1;

while($MyRow = DB_fetch_array($TransResult)) {
	if($MyRow['hold'] == 0 AND $MyRow['settled'] == 0) {
		$HoldValue = __('Hold');
	} elseif($MyRow['settled'] == 1) {
		$HoldValue = '';
	} else {
		$HoldValue = __('Release');
	}

	// Comment: All table-row (tag tr) must have 10 table-datacells (tag td).

	if($MyRow['hold'] == 1) {
		echo '<tr style="backgroud-color:#DD99BB">';
	} else {
		echo '<tr class="striped_row">';
	}

	// Prints first 8 columns that are in common (columns 1-8):
	echo '<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
		<td class="text">', __($MyRow['typename']), '</td>
		<td class="number"><a href="', $RootPath, '/SuppWhereAlloc.php?TransType=', $MyRow['type'], '&TransNo=', $MyRow['transno'], '">', $MyRow['transno'], '</a></td>
		<td class="text">', $MyRow['suppreference'], '</td>
		<td class="text">', $MyRow['transtext'], '</td>
		<td class="number">', locale_number_format($MyRow['totalamount'], $SupplierRecord['currdecimalplaces']), '</td>
		<td class="number">', locale_number_format($MyRow['allocated'], $SupplierRecord['currdecimalplaces']), '</td>
		<td class="number">', locale_number_format($MyRow['totalamount']-$MyRow['allocated'], $SupplierRecord['currdecimalplaces']), '</td>';

	// STORE "Link to GL transactions inquiry" column to use in some of the cases (column 10):
	$GLEntriesTD1 = '<td class="noPrint"><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&amp;TransNo=' . $MyRow['transno'] . '" target="_blank" title="' . __('Click to view the GL entries') . '"><img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/gl.png" width="16" /> ' . __('GL Entries') . '</a></td>';

	// Now prints columns 9 and 10:
	if($MyRow['type'] == 20) {// It is a Purchase Invoice (systype = 20).
		if($_SESSION['CompanyRecord']['gllink_creditors'] == true) {// Show a link to GL transactions inquiry:
/*			if($MyRow['totalamount'] - $MyRow['allocated'] == 0) {// The transaction is settled so don't show option to hold:*/
			if($MyRow['totalamount'] == $MyRow['allocated']) {// The transaction is settled so don't show option to hold:
				echo '<td class="noPrint"><a href="', $RootPath, '/PaymentAllocations.php?SuppID=', $MyRow['supplierno'], '&amp;InvID=', $MyRow['suppreference'], '" title="', __('Click to view payments'), '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/money_delete.png" width="16"/> ', __('Payments'), '</a></td>';// Payment column (column 9).
			} else {// The transaction is NOT settled so show option to hold:
				if($AuthRow['offhold'] == 0) {
					echo '<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8'), '?HoldType=', $MyRow['type'], '&amp;HoldTrans=', $MyRow['transno'], '&amp;HoldStatus=', $HoldValue, '&amp;FromDate=', $_POST['TransAfterDate'], '">', $HoldValue, '</a></td>';// Column 9.
				} else {
					if($HoldValue == __('Release')) {
						echo '<td class="noPrint">', $HoldValue , '</a></td>';// Column 9.
					} else {
						echo '<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8'), '?HoldType=', $MyRow['type'], '&amp;HoldTrans=', $MyRow['transno'], '&amp;HoldStatus=', $HoldValue, '&amp;FromDate=', $_POST['TransAfterDate'], '">', $HoldValue, '</a></td>';// Column 9.
					}
				}
			}
			echo $GLEntriesTD1;// Column 10.

		} else {// Do NOT show a link to GL transactions inquiry:
/*			if($MyRow['totalamount'] - $MyRow['allocated'] == 0) {// The transaction is settled so don't show option to hold:*/
			if($MyRow['totalamount'] == $MyRow['allocated']) {// The transaction is settled so don't show option to hold:
				echo '<td class="noPrint">&nbsp;</td>',// Column 9.
					'<td class="noPrint">&nbsp;</td>';// Column 10.
			} else {// The transaction is NOT settled so show option to hold:
				echo '<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8'), '/PaymentAllocations.php?SuppID=',
						$MyRow['type'], '&amp;InvID=', $MyRow['transno'], '">', __('View Payments'), '</a></td>',// Column 9.
					'<td class="noPrint"><a href="' .htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8'), '?HoldType=', $_POST['TransAfterDate'], '&amp;HoldTrans=', $HoldValue, '&amp;HoldStatus=' .
						$RootPath, '&amp;FromDate=', $MyRow['supplierno'], '">' . $MyRow['suppreference'], '</a></td>';// Column 10.
			}
		}

	} else {// It is NOT a Purchase Invoice (a credit note or a payment).
		echo '<td class="noPrint"><a href="', $RootPath, '/SupplierAllocations.php?AllocTrans=', $MyRow['id'], '" title="', __('Click to allocate funds'), '"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/allocation.png" /> ', __('Allocation'), '</a></td>';// Allocation column (column 9).
		if($_SESSION['CompanyRecord']['gllink_creditors'] == true) {// Show a link to GL transactions inquiry:
			echo $GLEntriesTD1;// Column 10.
		} else {// Do NOT show a link to GL transactions inquiry:
			echo '<td class="noPrint">&nbsp;</td>';// Column 10.
		}
	}// END printing columns 9 and 10.
	echo '</tr>';// Close the table row.
}// End of while loop

echo '</tbody></table>';
include('includes/footer.php');
