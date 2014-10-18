<?php

/* $Id: CustomerTransInquiry.php 6112 2013-07-19 10:07:00Z daintree $*/

include('includes/session.inc');
$Title = _('Customer Transactions Inquiry');

$ViewTopic = 'ARInquiries';
$BookMark = 'ARTransInquiry';

include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Transaction Inquiry') . '" alt="" />' . ' ' . _('Transaction Inquiry') . '
	</p>';
echo '<div class="page_help_text">' . _('Choose which type of transaction to report on.') . '</div>
	<br />';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<td>' . _('Type') . ':</td>
			<td><select tabindex="1" name="TransType"> ';

$sql = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 10
		AND typeid <= 14";

$resultTypes = DB_query($sql,$db);

echo '<option value="All">' . _('All') . '</option>';
while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow['typeid'] == $_POST['TransType']){
		     echo '<option selected="selected" value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		}
	} else {
		     echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
	}
}
echo '</select></td>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<td>' . _('From') . ':</td>
	<td><input tabindex="2" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" name="FromDate"  required="required" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
	<td>' . _('To') . ':</td>
	<td><input tabindex="3" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" name="ToDate"  required="required" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="4" type="submit" name="ShowResults" value="' . _('Show Transactions') . '" />
	</div>
    </div>
	</form>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $sql = "SELECT transno,
		   		trandate,
				debtortrans.debtorno,
				branchcode,
				reference,
				invtext,
				order_,
				debtortrans.rate,
				ovamount+ovgst+ovfreight+ovdiscount as totalamt,
				currcode,
				typename,
				decimalplaces AS currdecimalplaces
			FROM debtortrans
			INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN systypes ON debtortrans.type = systypes.typeid
			WHERE ";

   $sql = $sql . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$sql .= " AND type = '" . $_POST['TransType']."'";
	}
	$sql .=  " ORDER BY id";

   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');
   $TransResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);

   echo '<br />
		<table class="selection">';

   $TableHeader = '<tr>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Date') . '</th>
					<th>' . _('Customer') . '</th>
					<th>' . _('Branch') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Comments') . '</th>
					<th>' . _('Order') . '</th>
					<th>' . _('Ex Rate') . '</th>
					<th>' . _('Amount') . '</th>
					<th>' . _('Currency') . '</th>
				</tr>';
	echo $TableHeader;

	$RowCounter = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$format_base = '<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td style="width:200px">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>';

		if ($_POST['TransType']==10){ /* invoices */

			printf($format_base .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice"><img src="%s" title="' . _('Click to preview the invoice') . '" /></a></td>
					</tr>',
					$myrow['typename'],
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode'],
					$RootPath,
					$myrow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.gif');

		} elseif ($_POST['TransType']==11){ /* credit notes */
			printf($format_base .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit"><img src="%s" title="' . _('Click to preview the credit') . '" /></a></td>
					</tr>',
					$myrow['typename'],
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode'],
					$RootPath,
					$myrow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.gif');
		} else {  /* otherwise */
			printf($format_base . '</tr>',
					$myrow['typename'],
					$myrow['transno'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['debtorno'],
					$myrow['branchcode'],
					$myrow['reference'],
					$myrow['invtext'],
					$myrow['order_'],
					locale_number_format($myrow['rate'],6),
					locale_number_format($myrow['totalamt'],$myrow['currdecimalplaces']),
					$myrow['currcode']);
		}

	}
	//end of while loop

 echo '</table>';
}

include('includes/footer.inc');

?>
