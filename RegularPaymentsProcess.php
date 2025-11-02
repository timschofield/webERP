<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Process regular payments');
$ViewTopic = 'GeneralLedger';
$BookMark = 'RegularPayments';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', $Title, '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_POST['Add'])) {
	$AddedPayments = array();
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 7) == 'Payment') {
			$ID = substr($Key, 7);
			$SQL = "SELECT regularpayments.bankaccountcode,
							regularpayments.glcode,
							regularpayments.tag,
							regularpayments.amount,
							regularpayments.currabrev,
							regularpayments.narrative,
							regularpayments.nextpayment,
							regularpayments.finalpayment,
							regularpayments.days,
							regularpayments.frequency
						FROM regularpayments
						WHERE id='" . $ID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$AddedPayments[$ID]['PaymentDate'] = ConvertSQLDate($MyRow['nextpayment']);
			$AddedPayments[$ID]['FinalPaymentDate'] = ConvertSQLDate($MyRow['finalpayment']);
			$AddedPayments[$ID]['Tags'] = explode(',', $MyRow['tag']);
			$AddedPayments[$ID]['BankAccount'] = $MyRow['bankaccountcode'];
			$AddedPayments[$ID]['GLCode'] = $MyRow['glcode'];
			$AddedPayments[$ID]['FunctionalExRate'] = $_POST['FuncRate' . $ID];
			$AddedPayments[$ID]['ExchangeRate'] = $_POST['ExRate' . $ID];
			$AddedPayments[$ID]['Amount'] = $MyRow['amount'];
			$AddedPayments[$ID]['Currency'] = $MyRow['currabrev'];
			$AddedPayments[$ID]['Narrative'] = $MyRow['narrative'];
			$AddedPayments[$ID]['Frequency'] = $MyRow['frequency'];
			$AddedPayments[$ID]['Days'] = $MyRow['days'];
		}
	}

	foreach ($AddedPayments as $ID => $PaymentItem) {
		/*The functional currency amount will be the
		payment currenct amount  / the bank account currency exchange rate  - to get to the bank account currency
		then / the functional currency exchange rate to get to the functional currency */
		$TransNo = GetNextTransNo(1);
		$PeriodNo = GetPeriod($PaymentItem['PaymentDate']);

		switch ($PaymentItem['Frequency']) {
			case 'D':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'd', 1);
			break;
			case 'W':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'w', 1);
			break;
			case 'F':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'w', 2);
			break;
			case 'M':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'm', 1);
			break;
			case 'Q':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'm', 3);
			break;
			case 'Y':
				$NextPaymentDate = DateAdd($PaymentItem['PaymentDate'], 'y', 1);
			break;
		}
		if (Date1GreaterThanDate2($NextPaymentDate, $PaymentItem['FinalPaymentDate'])) {
			$Completed = 1;
		} else {
			$Completed = 0;
		}
		DB_Txn_Begin();

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									chequeno
								) VALUES (
									1,
									'" . $TransNo . "',
									'" . FormatDateForSQL($PaymentItem['PaymentDate']) . "',
									'" . $PeriodNo . "',
									'" . $PaymentItem['GLCode'] . "',
									'" . mb_substr($PaymentItem['Narrative'], 0, 200) . "',
									'" . ($PaymentItem['Amount'] / $PaymentItem['ExchangeRate'] / $PaymentItem['FunctionalExRate']) . "',
									'" . $ID . "'
								)";
		$ErrMsg = __('Cannot insert a GL entry for the payment using the SQL');
		$Result = DB_query($SQL, $ErrMsg,'', true);
		InsertGLTags($PaymentItem['Tags']);

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									chequeno
								) VALUES (
									1,
									'" . $TransNo . "',
									'" . FormatDateForSQL($PaymentItem['PaymentDate']) . "',
									'" . $PeriodNo . "',
									'" . $PaymentItem['BankAccount'] . "',
									'" . mb_substr($PaymentItem['Narrative'], 0, 200) . "',
									'" . -($PaymentItem['Amount'] / $PaymentItem['ExchangeRate'] / $PaymentItem['FunctionalExRate']) . "',
									'" . $ID . "'
								)";
		$ErrMsg = __('Cannot insert a GL entry for the payment using the SQL');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SQL = "INSERT INTO banktrans (transno,
									type,
									bankact,
									ref,
									chequeno,
									exrate,
									functionalexrate,
									transdate,
									banktranstype,
									amount,
									currcode
								) VALUES (
									'" . $TransNo . "',
									'1',
									'" . $PaymentItem['BankAccount'] . "',
									'" . $ID . "',
									'" . $ID . "',
									'" . $PaymentItem['ExchangeRate'] . "',
									'" . $PaymentItem['FunctionalExRate'] . "',
									'" . FormatDateForSQL($PaymentItem['PaymentDate']) . "',
									'1',
									'" . -($PaymentItem['Amount']) . "',
									'" . $PaymentItem['Currency'] . "'
								)";

		$ErrMsg = __('Cannot insert a bank transaction because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SQL = "UPDATE regularpayments SET nextpayment='" . FormatDateForSQL($NextPaymentDate) . "',
											completed='" . $Completed . "'
										WHERE id='" . $ID . "'";
		$Result = DB_query($SQL, $ErrMsg, '', true);
		DB_Txn_Commit();

		prnMsg(__('The regular payment has been processed'), 'success');
	} //$_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem

}

echo '<form method="post" id="RegularPaymentsProcess" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$SQL = "SELECT regularpayments.id,
				regularpayments.frequency,
				regularpayments.days,
				regularpayments.glcode,
				chartmaster.accountname,
				bankaccounts.bankaccountname,
				regularpayments.tag,
				regularpayments.amount,
				regularpayments.currabrev,
				regularpayments.narrative,
				regularpayments.firstpayment,
				regularpayments.finalpayment,
				regularpayments.nextpayment
			FROM regularpayments
			INNER JOIN bankaccounts
				ON bankaccounts.accountcode=regularpayments.bankaccountcode
			INNER JOIN chartmaster
				ON chartmaster.accountcode=regularpayments.glcode
			WHERE completed=0
				AND nextpayment <= CURRENT_DATE";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0 and !isset($_GET['Edit'])) {
	echo '<table>
			<tr>
				<th>', __('Bank Account'), '</th>
				<th>', __('GL Account'), '</th>
				<th>', __('GL Tags'), '</th>
				<th>', __('Amount'), '</th>
				<th>', __('Functional'), '<br />', __('Rate'), '</th>
				<th>', __('Exchange'), '<br />', __('Rate'), '</th>
				<th>', __('Currency'), '</th>
				<th>', __('Description'), '</th>
				<th>', __('Next payment Date'), '</th>
				<th></th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {

		/*Get suggested FunctionalExRate */
		$FuncExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $MyRow['currabrev'] . "'");
		$FuncExRateRow = DB_fetch_row($FuncExRateResult);
		$SuggestedFunctionalExRate = $FuncExRateRow[0];

		/*Get the exchange rate between the functional currency and the payment currency*/
		$ExRateResult = DB_query("SELECT decimalplaces, rate FROM currencies WHERE currabrev='" . $MyRow['currabrev'] . "'");
		$ExRateRow = DB_fetch_row($ExRateResult);
		$TableExRate = $ExRateRow[1]; //this is the rate of exchange between the functional currency and the payment currency
		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		if ($SuggestedFunctionalExRate != 0) {
			$SuggestedExRate = $TableExRate / $SuggestedFunctionalExRate;
		} else {
			$SuggestedExRate = 0;
		}
		$DecimalPLaces = $ExRateRow[0];
		$Tags = explode(',', $MyRow['tag']);
		$TagText = GetDescriptionsFromTagArray($Tags);
		echo '<tr class="striped_row">
				<td>', $MyRow['bankaccountname'], '</td>
				<td>', $MyRow['glcode'], ' - ', $MyRow['accountname'], '</td>
				<td>', $TagText, '</td>
				<td class="number">', locale_number_format($MyRow['amount'], $DecimalPLaces), '</td>
				<td class="number"><input type="text" size="10" class="number" name="FuncRate', $MyRow['id'], '" value="', $SuggestedFunctionalExRate, '" /></td>
				<td class="number"><input type="text" size="10" class="number" name="ExRate', $MyRow['id'], '" value="', $SuggestedExRate, '" /></td>
				<td>', $MyRow['currabrev'], '</td>
				<td>', $MyRow['narrative'], '</td>
				<td>', ConvertSQLDate($MyRow['nextpayment']), '</td>
				<td><input type="checkbox" name="Payment', $MyRow['id'], '" />', __('Process'), '</td>
			</tr>';
	}
	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="Add" value="', __('Process Selected payments'), '" />
		</div>';
} else {
	prnMsg(__('There are no regular payments due'), 'info');
}

echo '</form>';

include('includes/footer.php');
