<?php

require(__DIR__ . '/includes/session.php');

$Title = __('General Ledger Account Report');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountCSV';
include('includes/header.php');

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
}
elseif (isset($_GET['Period'])) {
	$SelectedPeriod = $_GET['Period'];
}

echo '<p class="page_title_text"><img src="' . $RootPath, '/css/', $Theme, '/images/transactions.png" title="' . __('General Ledger Account Inquiry') . '" alt="" />' . ' ' . __('General Ledger Account Report') . '</p>';

echo '<div class="page_help_text">' . __('Use the keyboard Shift key to select multiple accounts and periods') . '</div><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m') , 0, Date('Y')));

/*Show a form to allow input of criteria for the report */
echo '<fieldset>
		<legend>', __('Report Criteria') , '</legend>
		<field>
			<label for="Account">' . __('Selected Accounts') . ':</label>
			<select name="Account[]" size="12" multiple="multiple">';
$SQL = "SELECT chartmaster.accountcode,
			   chartmaster.accountname
		FROM chartmaster
		INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		ORDER BY chartmaster.accountcode";
$AccountsResult = DB_query($SQL);
$i = 0;
while ($MyRow = DB_fetch_array($AccountsResult)) {
	if (isset($_POST['Account'][$i]) AND $MyRow['accountcode'] == $_POST['Account'][$i]) {
		echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		$i++;
	}
	else {
		echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Period">' . __('For Period range') . ':</label>
		<select name="Period[]" size="12" multiple="multiple">';
$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
$Periods = DB_query($SQL);
$id = 0;

while ($MyRow = DB_fetch_array($Periods)) {
	if (isset($SelectedPeriod[$id]) and $MyRow['periodno'] == $SelectedPeriod[$id]) {
		echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		$id++;
	}
	else {
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
	}
	else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
}
echo '</select>
	</field>';
// End select tag
echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="MakeCSV" value="' . __('Make CSV File') . '" />
	</div>
</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['MakeCSV'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(__('A period or range of periods must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();
	}
	if (!isset($_POST['Account'])) {
		prnMsg(__('An account or range of accounts must be selected from the list box') , 'info');
		include('includes/footer.php');
		exit();
	}

	if (!file_exists($_SESSION['reports_dir'])) {
		$Result = mkdir('./' . $_SESSION['reports_dir']);
	}

	$FileName = $_SESSION['reports_dir'] . '/Accounts_Listing_' . Date('Y-m-d') . '.csv';

	$fp = fopen($FileName, 'w');

	if ($fp == false) {
		prnMsg(__('Could not open or create the file under') . ' ' . $FileName, 'error');
		include('includes/footer.php');
		exit();
	}

	foreach ($_POST['Account'] as $SelectedAccount) {
		/*Is the account a balance sheet or a profit and loss account */
		$SQL = "SELECT chartmaster.accountname,
								accountgroups.pandl
							    FROM accountgroups
							    INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
							    WHERE chartmaster.accountcode='" . $SelectedAccount . "'";
		$Result = DB_query($SQL);
		$AccountDetailRow = DB_fetch_row($Result);
		$AccountName = $AccountDetailRow[1];
		if ($AccountDetailRow[1] == 1) {
			$PandLAccount = true;
		}
		else {
			$PandLAccount = false; /*its a balance sheet account */
		}

		$FirstPeriodSelected = min($SelectedPeriod);
		$LastPeriodSelected = max($SelectedPeriod);

		$SQL = "SELECT gltrans.type,
					systypes.typename,
					gltrans.typeno,
					gltrans.trandate,
					gltrans.narrative,
					gltrans.amount,
					gltrans.periodno,
					gltags.tagref AS tag
				FROM gltrans
				INNER JOIN systypes
					ON systypes.typeid=gltrans.type
				LEFT JOIN gltags
					ON gltrans.counterindex=gltags.counterindex
				WHERE gltrans.account = '" . $SelectedAccount . "'
					AND systypes.typeid=gltrans.type
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

		fwrite($fp, $SelectedAccount . ' - ' . $AccountName . ' ' . __('for period') . ' ' . $FirstPeriodSelected . ' ' . __('to') . ' ' . $LastPeriodSelected . "\n");
		if ($PandLAccount == true) {
			$RunningTotal = 0;
		}
		else {
			// Get the opening balance using gltotals
			$SQL = "SELECT SUM(amount) AS bfwd
					FROM gltotals
					WHERE gltotals.account = '" . $SelectedAccount . "'
					AND gltotals.period < '" . $FirstPeriodSelected . "'";

			$ErrMsg = __('The opening balance for account') . ' ' . $SelectedAccount . ' ' . __('could not be retrieved');
			$BalanceResult = DB_query($SQL, $ErrMsg);
			$BalanceRow = DB_fetch_array($BalanceResult);
			$RunningTotal = $BalanceRow['bfwd'];

			if ($RunningTotal < 0) {
				fwrite($fp, $SelectedAccount . ', ' . $FirstPeriodSelected . ', ' . __('Brought Forward Balance') . ',,,,' . -$RunningTotal . "\n");
			}
			else {
				fwrite($fp, $SelectedAccount . ', ' . $FirstPeriodSelected . ', ' . __('Brought Forward Balance') . ',,,' . $RunningTotal . "\n");
			}
		}
		$PeriodTotal = 0;
		$PeriodNo = - 9999;

		while ($MyRow = DB_fetch_array($TransResult)) {

			if ($MyRow['periodno'] != $PeriodNo) {
				if ($PeriodNo != - 9999) { //ie its not the first time around
					// Removed the query to chartdetails here as it's no longer needed
					if ($PeriodTotal < 0) {
						fwrite($fp, $SelectedAccount . ', ' . $PeriodNo . ', ' . __('Period Total') . ',,,,' . -$PeriodTotal . "\n");
					}
					else {
						fwrite($fp, $SelectedAccount . ', ' . $PeriodNo . ', ' . __('Period Total') . ',,,' . $PeriodTotal . "\n");
					}
				}
				$PeriodNo = $MyRow['periodno'];
				$PeriodTotal = 0;
			}

			$RunningTotal += $MyRow['amount'];
			$PeriodTotal += $MyRow['amount'];

			$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

			$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
			$TagResult = DB_query($TagSQL);
			$TagRow = DB_fetch_array($TagResult);
			if (!isset($TagRow['tagdescription'])) {
				$TagRow['tagdescription'] = '';
			}
			if ($MyRow['amount'] < 0) {
				fwrite($fp, $SelectedAccount . ',' . $MyRow['periodno'] . ', ' . $MyRow['typename'] . ',' . $MyRow['typeno'] . ',' . $FormatedTranDate . ',,' . -$MyRow['amount'] . ',' . $MyRow['narrative'] . ',' . $TagRow['tagdescription'] . "\n");
			}
			else {
				fwrite($fp, $SelectedAccount . ',' . $MyRow['periodno'] . ', ' . $MyRow['typename'] . ',' . $MyRow['typeno'] . ',' . $FormatedTranDate . ',' . $MyRow['amount'] . ',,' . $MyRow['narrative'] . ',' . $TagRow['tagdescription'] . "\n");
			}
		} //end loop around GLtrans
		if ($PeriodTotal <> 0) {
			if ($PeriodTotal < 0) {
				fwrite($fp, $SelectedAccount . ', ' . $PeriodNo . ', ' . __('Period Total') . ',,,,' . -$PeriodTotal . "\n");
			}
			else {
				fwrite($fp, $SelectedAccount . ', ' . $PeriodNo . ', ' . __('Period Total') . ',,,' . $PeriodTotal . "\n");
			}
		}
		if ($PandLAccount == true) {
			if ($RunningTotal < 0) {
				fwrite($fp, $SelectedAccount . ',' . $LastPeriodSelected . ', ' . __('Total Period Movement') . ',,,,' . -$RunningTotal . "\n");
			}
			else {
				fwrite($fp, $SelectedAccount . ',' . $LastPeriodSelected . ', ' . __('Total Period Movement') . ',,,' . $RunningTotal . "\n");
			}
		}
		else { /*its a balance sheet account*/
			if ($RunningTotal < 0) {
				fwrite($fp, $SelectedAccount . ',' . $LastPeriodSelected . ', ' . __('Balance C/Fwd') . ',,,,' . -$RunningTotal . "\n");
			}
			else {
				fwrite($fp, $SelectedAccount . ',' . $LastPeriodSelected . ', ' . __('Balance C/Fwd') . ',,,' . $RunningTotal . "\n");
			}
		}

	} /*end for each SelectedAccount */
	fclose($fp);
	echo '<p><a href="' . $FileName . '">' . __('click here') . '</a> ' . __('to view the file') . '<br />';
} /* end of if CreateCSV button hit */

include('includes/footer.php');
