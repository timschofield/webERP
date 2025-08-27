<?php

require(__DIR__ . '/includes/session.php');

$Title = __('General Ledger Journal Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLJournalInquiry';
include('includes/header.php');

if (isset($_POST['FromTransDate'])){$_POST['FromTransDate'] = ConvertSQLDate($_POST['FromTransDate']);}
if (isset($_POST['ToTransDate'])){$_POST['ToTransDate'] = ConvertSQLDate($_POST['ToTransDate']);}

echo '<p class="page_title_text"><img src="' . $RootPath, '/css/', $Theme, '/images/money_add.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['Show'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>';
	echo '<legend>' . __('Selection Criteria') . '</legend>';

	$SQL = "SELECT typeid,systypes.typeno,typename FROM
		systypes INNER JOIN gltrans ON systypes.typeid=gltrans.type
		GROUP BY typeid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<field>
				<label for="TransType">' . __('Transaction Type') . ' </label>
				<select name="TransType">';
		while ($MyRow = DB_fetch_array($Result)) {
			if (!isset($MaxJournalNumberUsed)) {
				$MaxJournalNumberUsed = $MyRow['typeno'];
			}
			else {
				$MaxJournalNumberUsed = ($MyRow['typeno'] > $MaxJournalNumberUsed) ? $MyRow['typeno'] : $MaxJournalNumberUsed;
			}
			echo '<option value="' . $MyRow['typeid'] . '">' . __($MyRow['typename']) . '</option>';
		}
		echo '</select>
			</field>';

	}

	echo '<field>
			<label>' . __('Journal Number Range') . ' (' . __('Between') . ' 1 ' . __('and') . ' ' . $MaxJournalNumberUsed . ')</label>
			' . __('From') . ':' . '<input type="text" class="number" name="NumberFrom" size="10" maxlength="11" value="1" />' . '
			' . __('To') . ':' . '<input type="text" class="number" name="NumberTo" size="10" maxlength="11" value="' . $MaxJournalNumberUsed . '" />' . '
		</field>';

	$SQL = "SELECT MIN(trandate) AS fromdate,
					MAX(trandate) AS todate FROM gltrans WHERE type=0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (isset($MyRow['fromdate']) and $MyRow['fromdate'] != '') {
		$FromDate = $MyRow['fromdate'];
		$ToDate = $MyRow['todate'];
	}
	else {
		$FromDate = date('Y-m-d');
		$ToDate = date('Y-m-d');
	}

	echo '<field>
			<label>' . __('Journals Dated Between') . ':</label>
			' . __('From') . ':' . '<input name="FromTransDate" type="date" maxlength="10" size="11" value="' . $FromDate . '" />
			' . __('To') . ':' . '<input name="ToTransDate" type="date" maxlength="10" size="11" value="' . $ToDate . '" />
		</field>';

	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Show" value="' . __('Show transactions') . '" />
		</div>';
	echo '</form>';
}
else {

	$SQL = "SELECT gltrans.counterindex,
					gltrans.typeno,
				gltrans.trandate,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				gltrans.amount,
				gltrans.jobref
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode
			WHERE gltrans.type='" . $_POST['TransType'] . "'
				AND gltrans.trandate>='" . FormatDateForSQL($_POST['FromTransDate']) . "'
				AND gltrans.trandate<='" . FormatDateForSQL($_POST['ToTransDate']) . "'
				AND gltrans.typeno>='" . $_POST['NumberFrom'] . "'
				AND gltrans.typeno<='" . $_POST['NumberTo'] . "'
			ORDER BY gltrans.typeno";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(__('There are no transactions for this account in the date range selected') , 'info');
	}
	else {
		echo '<table class="selection">';
		echo '<tr>
				<th>' . __('Date') . '</th>
				<th>' . __('Journal Number') . '</th>
				<th>' . __('Account Code') . '</th>
				<th>' . __('Account Description') . '</th>
				<th>' . __('Narrative') . '</th>
				<th>' . __('Amount') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
				<th>' . __('Tag') . '</th>
				<th colspan="2"></th>
			</tr>';

		$LastJournal = 0;

		while ($MyRow = DB_fetch_array($Result)) {
			$TagsSQL = "SELECT gltags.tagref,
								tags.tagdescription
							FROM gltags
							INNER JOIN tags
								ON gltags.tagref=tags.tagref
							WHERE gltags.counterindex='" . $MyRow['counterindex'] . "'";
			$TagsResult = DB_query($TagsSQL);

			$TagDescriptions = '';
			while ($TagRows = DB_fetch_array($TagsResult)) {
				$TagDescriptions .= $TagRows['tagref'] . ' - ' . $TagRows['tagdescription'] . '<br />';
			}

			if ($MyRow['typeno'] != $LastJournal) {

				echo '<tr>
						<td colspan="8"></td>
					</tr>
					<tr class="striped_row">
					<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
					<td class="number">' . $MyRow['typeno'] . '</td>';

			}
			else {
				echo '<tr class="striped_row">
						<td colspan="2"></td>';
			}

			// if user is allowed to see the account we show it, other wise we show "OTHERS ACCOUNTS"
			$CheckSql = "SELECT count(*)
						 FROM glaccountusers
						 WHERE accountcode= '" . $MyRow['account'] . "'
							 AND userid = '" . $_SESSION['UserID'] . "'
							 AND canview = '1'";
			$CheckResult = DB_query($CheckSql);
			$CheckRow = DB_fetch_row($CheckResult);

			if ($CheckRow[0] > 0) {
				echo '<td>' . $MyRow['account'] . '</td>
						<td>' . $MyRow['accountname'] . '</td>';
			}
			else {
				echo '<td>' . __('Others') . '</td>
						<td>' . __('Other GL Accounts') . '</td>';
			}

			echo '<td>' . $MyRow['narrative'] . '</td>
					<td class="number">' . locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td valign="top">', $TagDescriptions, '</td>';

			if ($MyRow['typeno'] != $LastJournal and $CheckRow[0] > 0) {
				echo '<td class="number">
						<a href="' . $RootPath . '/PDFGLJournal.php?JournalNo=' . $MyRow['typeno'] . '&Type=' . $_POST['TransType'] . '&PDF=True" target="_blank">' . __('Print') . '</a>
					</td>';
				echo '<td class="number">
						<a href="' . $RootPath . '/PDFGLJournal.php?JournalNo=' . $MyRow['typeno'] . '&Type=' . $_POST['TransType'] . '&View=True" target="_blank">' . __('View') . '</a>
					</td></tr>';
			}

			else {
				echo '<td colspan="1"></td></tr>';
			}

		}
		echo '</table>';
	} //end if no bank trans in the range to show
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><div class="centre"><input type="submit" name="Return" value="' . __('Select Another Date') . '" /></div>';
	echo '</form>';
}
include('includes/footer.php');
