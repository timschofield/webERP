<?php

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountReport';

use Dompdf\Dompdf;

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])) {
	$SelectedPeriod = $_GET['Period'];
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(__('A period or range of periods must be selected from the list box'), 'info');
		include('includes/footer.php');
		exit();
	}
	if (!isset($_POST['Account'])) {
		prnMsg(__('An account or range of accounts must be selected from the list box'), 'info');
		include('includes/footer.php');
		exit();
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('GL Account Report') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('User') . ': ' . $_SESSION['UserID'] . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Type') . '</th>
							<th>' . __('Reference') . '</th>
							<th>' . __('Date') . '</th>
							<th>' . __('Debit') . '</th>
							<th>' . __('Credit') . '</th>
							<th>' . __('Narrative') . '</th>
							<th>' . __('Tag') . '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($_POST['Account'] as $SelectedAccount) {
		// Get account info
		$Result = DB_query("SELECT chartmaster.accountname,
								accountgroups.pandl
							FROM accountgroups
							INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
							WHERE chartmaster.accountcode='" . $SelectedAccount . "'");
		$AccountDetailRow = DB_fetch_row($Result);
		$AccountName = $AccountDetailRow[0];
		$PandLAccount = ($AccountDetailRow[1] == 1);

		$FirstPeriodSelected = min($SelectedPeriod);
		$LastPeriodSelected = max($SelectedPeriod);

		// Get transactions
		$SQL = "SELECT gltrans.counterindex,
					gltrans.type,
					typename,
					gltrans.typeno,
					gltrans.trandate,
					gltrans.narrative,
					gltrans.amount,
					gltrans.periodno,
					gltags.tagref AS tag
					FROM gltrans
					INNER JOIN systypes
						ON gltrans.type=systypes.typeid
					LEFT JOIN gltags
						ON gltrans.counterindex=gltags.counterindex
					WHERE gltrans.account = '" . $SelectedAccount . "'
						AND periodno>='" . $FirstPeriodSelected . "'
						AND periodno<='" . $LastPeriodSelected . "'";

		if (isset($_POST['tag']) and $_POST['tag'] != -1) {
			$SQL .= " AND gltags.tagref='" . $_POST['tag'] . "'";
		}

		$SQL .= " ORDER BY periodno,
						gltrans.trandate,
						gltrans.counterindex";

		$ErrMsg = __('The transactions for account') . ' ' . $SelectedAccount . ' ' . __('could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);
		$HTML .= '<tr class="total_row">
					<td colspan="7"><h3>' . $SelectedAccount . ' - ' . $AccountName . ' ' . ': ' . __('Listing for Period') . ' ' . $FirstPeriodSelected . ' ' . __('to') . ' ' . $LastPeriodSelected . '</h3></td>
				<tr>';
		if ($PandLAccount) {
			$RunningTotal = 0;
		} else {
			// Calculate the brought forward balance from gltotals
			$SQL = "SELECT SUM(amount) AS bfwd
					FROM gltotals
					WHERE gltotals.account = '" . $SelectedAccount . "'
					AND gltotals.period < '" . $FirstPeriodSelected . "'";
			$ErrMsg = __('The brought forward balance for account') . ' ' . $SelectedAccount . ' ' . __('could not be retrieved');
			$BfwdResult = DB_query($SQL, $ErrMsg);
			$BfwdRow = DB_fetch_array($BfwdResult);
			$RunningTotal = $BfwdRow['bfwd'];

			$HTML .= '<tr class="total_row"><td colspan="3">' . __('Brought Forward Balance') . '</td>';
			if ($RunningTotal < 0) {
				$HTML .= '<td></td><td class="number">' . locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
			} else {
				$HTML .= '<td class="number">' . locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
			}
			$HTML .= '<td colspan="3"></td></tr>';
		}

		$PeriodTotal = 0;
		$PeriodNo = -9999;

		while ($MyRow = DB_fetch_array($TransResult)) {
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
			if ($MyRow['periodno'] != $PeriodNo) {
				if ($PeriodNo != -9999) { // not first
					$HTML .= '<tr class="total_row">
						<td colspan="3">' . __('Period Total') . '</td>';
					if ($PeriodTotal < 0) {
						$HTML .= '<td></td><td class="number">' . locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
					} else {
						$HTML .= '<td class="number">' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td><td></td>';
					}
					$HTML .= '<td colspan="2"></td></tr>';
				}
				$PeriodNo = $MyRow['periodno'];
				$PeriodTotal = 0;
			}

			$RunningTotal += $MyRow['amount'];
			$PeriodTotal += $MyRow['amount'];

			if ($MyRow['amount'] >= 0) {
				$DebitAmount = locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
				$CreditAmount = '';
			} else {
				$CreditAmount = locale_number_format(-$MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
				$DebitAmount = '';
			}

			$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

			$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
			$TagResult = DB_query($TagSQL);
			$TagRow = DB_fetch_array($TagResult);

			$HTML .= '<tr class="striped_row">
				<td class="centre">' . $MyRow['typename'] . '</td>
				<td class="number">' . $MyRow['typeno'] . '</td>
				<td class="centre">' . $FormatedTranDate . '</td>
				<td class="number">' . $DebitAmount . '</td>
				<td class="number">' . $CreditAmount . '</td>
				<td>' . $MyRow['narrative'] . '</td>
				<td>' . $TagDescriptions . '</td>
			</tr>';
		}

		$HTML .= '<tr class="total_row">';
		if ($PandLAccount) {
			$HTML .= '<td>' . __('Total Period Movement') . '</td>';
		} else {
			$HTML .= '<td>' . __('Balance C/Fwd') . '</td>';
		}
		if ($RunningTotal < 0) {
			$HTML .= '<td colspan="3">
					</td><td class="number">' . locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td colspan="2"></td>';
		} else {
			$HTML .= '<td colspan="2">
					</td><td class="number">' . locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td colspan="3"></td>';
		}
		$HTML .= '</tr>';
	}

	if (count($_POST['Account']) == 0) {
		prnMsg(__('An account or range of accounts must be selected from the list box'), 'info');
		include('includes/footer.php');
		exit();
	}

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="number">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_GL_Account_report_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Inventory Planning Report');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('General Ledger Account Report') . '" alt="" />' . ' ' . __('General Ledger Account Report') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else {
	$Title = __('General Ledger Account Report');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath, '/css/', $Theme, '/images/transactions.png" title="' . __('General Ledger Account Inquiry') . '" alt="" />' . ' ' . __('General Ledger Account Report') . '</p>';

	echo '<div class="page_help_text">' . __('Use the keyboard Shift key to select multiple accounts and periods') . '</div><br />';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	/*Dates in SQL format for the last day of last month*/
	$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m'), 0, Date('Y')));

	/*Show a form to allow input of criteria for the report */
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Account">' . __('Selected Accounts') . ':</label>
				<select name="Account[]" size="12" multiple="multiple">';
	$SQL = "SELECT chartmaster.accountcode,
				   chartmaster.accountname
			FROM chartmaster
			INNER JOIN glaccountusers
				ON glaccountusers.accountcode=chartmaster.accountcode
				AND glaccountusers.userid='" . $_SESSION['UserID'] . "'
				AND glaccountusers.canview=1
			ORDER BY chartmaster.accountcode";
	$AccountsResult = DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($AccountsResult)) {
		if (isset($_POST['Account'][$i]) AND $MyRow['accountcode'] == $_POST['Account'][$i]) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . $MyRow['accountname'] . '</option>';
			$i++;
		} else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . $MyRow['accountname'] . '</option>';
		}
	}
	echo '</select>';

	echo '<field>
			<label for="Period">' . __('For Period range') . ':</label>
			<select Name=Period[] size="12" multiple="multiple">';
	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);
	$id = 0;

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($SelectedPeriod[$id]) and $MyRow['periodno'] == $SelectedPeriod[$id]) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
			$id++;
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		}
	}
	echo '</select>
		</field>';

	//Select the tag
	echo '<field>
			<label for="tag">' . __('Select Tag') . ':</label>
			<select name="tag">';

	$SQL = "SELECT tagref,
					tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="-1">-1 - ' . __('All tags') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	// End select tag
	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="'.__('PDF Report').'" />
			<input type="submit" name="View" title="View" value="' . __('View Report') .'" />
		</div>
		</form>';

	include('includes/footer.php');
	exit();
}
