<?php
// CustomerTransInquiry.php

include('includes/session.php');
$Title = _('Customer Transactions Inquiry');
$ViewTopic = 'ARInquiries';
$BookMark = 'ARTransInquiry';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Transaction Inquiry') . '" alt="" />' . ' ' . _('Transaction Inquiry') . '
	</p>';
echo '<div class="page_help_text">' . _('Choose which type of transaction to report on.') . '</div>
	<br />';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<table class="selection">
		<tr>
			<td>' . _('Type') . ':</td>
			<td><select tabindex="1" name="TransType"> ';

$SQL = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 10
		AND typeid <= 14";

$ResultTypes = DB_query($SQL);

echo '<option value="All">' . _('All') . '</option>';
while($MyRow=DB_fetch_array($ResultTypes)) {
	echo '<option';
	if(isset($_POST['TransType'])) {
		if($MyRow['typeid'] == $_POST['TransType']) {
		     echo ' selected="selected"' ;
		}
	}
	echo ' value="' . $MyRow['typeid'] . '">' . _($MyRow['typename']) . '</option>';
}
echo '</select></td>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<td>' . _('From') . ':</td>
	<td><input class="date" maxlength="10" name="FromDate" required="required" size="11" tabindex="2" type="text" value="' . $_POST['FromDate'] . '" /></td>
	<td>' . _('To') . ':</td>
	<td><input class="date" maxlength="10" name="ToDate" required="required" size="11" tabindex="3" type="text" value="' . $_POST['ToDate'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input name="ShowResults" tabindex="4" type="submit" value="' . _('Show transactions') . '" />
	</div>
    </div>
	</form>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $SQL = "SELECT transno,
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

   $SQL = $SQL . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$SQL .= " AND type = '" . $_POST['TransType']."'";
	}
	$SQL .=  " ORDER BY id";

   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
   $DbgMsg =  _('The SQL that failed was');
   $TransResult = DB_query($SQL,$ErrMsg,$DbgMsg);

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

	while ($MyRow=DB_fetch_array($TransResult)) {

		$FormatBase = '<tr class="striped_row">
						<td>%s</td>
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

			printf($FormatBase .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice"><img src="%s" title="' . _('Click to preview the invoice') . '" /></a></td>
					</tr>',
					_($MyRow['typename']),
					$MyRow['transno'],
					ConvertSQLDate($MyRow['trandate']),
					$MyRow['debtorno'],
					$MyRow['branchcode'],
					$MyRow['reference'],
					$MyRow['invtext'],
					$MyRow['order_'],
					locale_number_format($MyRow['rate'],6),
					locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']),
					$MyRow['currcode'],
					$RootPath,
					$MyRow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.png');

		} elseif($_POST['TransType']==11) { /* credit notes */
			printf($FormatBase .
					'<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit"><img src="%s" title="' . _('Click to preview the credit') . '" /></a></td>
					</tr>',
					_($MyRow['typename']),
					$MyRow['transno'],
					ConvertSQLDate($MyRow['trandate']),
					$MyRow['debtorno'],
					$MyRow['branchcode'],
					$MyRow['reference'],
					$MyRow['invtext'],
					$MyRow['order_'],
					locale_number_format($MyRow['rate'],6),
					locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']),
					$MyRow['currcode'],
					$RootPath,
					$MyRow['transno'],
					$RootPath.'/css/'.$Theme.'/images/preview.png');
		} else {  /* otherwise */
			printf($FormatBase . '</tr>',
					_($MyRow['typename']),
					$MyRow['transno'],
					ConvertSQLDate($MyRow['trandate']),
					$MyRow['debtorno'],
					$MyRow['branchcode'],
					$MyRow['reference'],
					$MyRow['invtext'],
					$MyRow['order_'],
					locale_number_format($MyRow['rate'],6),
					locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']),
					$MyRow['currcode']);
		}

	}
	//end of while loop

 echo '</table>';
}

include('includes/footer.php');

?>
