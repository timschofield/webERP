<?php
// CustomerTransInquiry.php

include('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};

$Title = _('Customer Transactions Inquiry');
$ViewTopic = 'ARInquiries';
$BookMark = 'ARTransInquiry';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Transaction Inquiry') . '" alt="" />' . ' ' . _('Transaction Inquiry') . '
	</p>';
echo '<div class="page_help_text">' . _('Choose which type of transaction to report on.') . '</div>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="TransType">' . _('Type') . ':</label>
				<select tabindex="1" name="TransType"> ';

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
echo '</select>
	</field>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<field>
		<label for="FromDate">' . _('From') . ':</label>
		<input type="date" maxlength="10" name="FromDate" required="required" size="11" tabindex="2" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>
	<field>
		<label for="ToDate">' . _('To') . ':</label>
		<input type="date" maxlength="10" name="ToDate" required="required" size="11" tabindex="3" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input name="ShowResults" tabindex="4" type="submit" value="' . _('Show transactions') . '" />
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

   echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Type') . '</th>
					<th class="SortedColumn">' . _('Number') . '</th>
					<th class="SortedColumn">' . _('Date') . '</th>
					<th class="SortedColumn">' . _('Customer') . '</th>
					<th class="SortedColumn">' . _('Branch') . '</th>
					<th class="SortedColumn">' . _('Reference') . '</th>
					<th class="SortedColumn">' . _('Comments') . '</th>
					<th class="SortedColumn">' . _('Order') . '</th>
					<th class="SortedColumn">' . _('Ex Rate') . '</th>
					<th class="SortedColumn">' . _('Amount') . '</th>
					<th class="SortedColumn">' . _('Currency') . '</th>
				</tr>
			</thead>
			<tbody>';

	$RowCounter = 1;

	while ($MyRow=DB_fetch_array($TransResult)) {

		if ($_POST['TransType']==10){ /* invoices */

			echo '<tr class="striped_row">
						<td>', _($MyRow['typename']), '</td>
						<td>', $MyRow['transno'], '</td>
						<td class="date">', ConvertSQLDate($MyRow['trandate']), '</td>
						<td>', $MyRow['debtorno'], '</td>
						<td>', $MyRow['branchcode'], '</td>
						<td>', $MyRow['reference'], '</td>
						<td style="width:200px">', $MyRow['invtext'], '</td>
						<td>', $MyRow['order_'], '</td>
						<td class="number">', locale_number_format($MyRow['rate'],6), '</td>
						<td class="number">', locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']), '</td>
						<td>', $MyRow['currcode'], '</td>
						<td>
							<a target="_blank" href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&InvOrCredit=Invoice">
							<img src="', $RootPath.'/css/'.$Theme.'/images/preview.png', '" title="' . _('Click to preview the invoice') . '" /></a>
						</td>
					</tr>';

		} elseif($_POST['TransType']==11) { /* credit notes */
			echo '<tr class="striped_row">
						<td>', _($MyRow['typename']), '</td>
						<td>', $MyRow['transno'], '</td>
						<td>', ConvertSQLDate($MyRow['trandate']), '</td>
						<td>', $MyRow['debtorno'], '</td>
						<td>', $MyRow['branchcode'], '</td>
						<td>', $MyRow['reference'], '</td>
						<td style="width:200px">', $MyRow['invtext'], '</td>
						<td>', $MyRow['order_'], '</td>
						<td class="number">', locale_number_format($MyRow['rate'],6), '</td>
						<td class="number">', locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']), '</td>
						<td>', $MyRow['currcode'], '</td>
						<td>
							<a target="_blank" href="', $RootPath, 'PrintCustTrans.php?FromTransNo=', $MyRow['transno'], '&InvOrCredit=Credit">
							<img src="', $RootPath.'/css/'.$Theme.'/images/preview.png', '" title="' . _('Click to preview the credit') . '" /></a>
						</td>
					</tr>';
		} else {  /* otherwise */
			echo '<tr class="striped_row">
						<td>', _($MyRow['typename']), '</td>
						<td>', $MyRow['transno'], '</td>
						<td>', ConvertSQLDate($MyRow['trandate']), '</td>
						<td>', $MyRow['debtorno'], '</td>
						<td>', $MyRow['branchcode'], '</td>
						<td>', $MyRow['reference'], '</td>
						<td style="width:200px">', $MyRow['invtext'], '</td>
						<td>', $MyRow['order_'], '</td>
						<td class="number">', locale_number_format($MyRow['rate'],6), '</td>
						<td class="number">', locale_number_format($MyRow['totalamt'],$MyRow['currdecimalplaces']), '</td>
						<td>', $MyRow['currcode'], '</td>
					</tr>';
		}

	}
	//end of while loop

 echo '</tbody>
	</table>';
}

include('includes/footer.php');
