<?php

include('includes/session.php');

$Title = __('Chart of Accounts Maintenance for PT. Sungai Mutiara Hitam');
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLAccounts';
include('includes/header.php');

include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		__('General Ledger Accounts') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg( __('The account name must be fifty characters or less long'),'warn');
	}

	if (isset($SelectedAccount) AND $InputError !=1) {

		$SQL = "UPDATE chartmasterSMH SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
				WHERE accountcode ='" . $SelectedAccount . "'";

		$ErrMsg = __('Could not update the account because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg(__('The general ledger account has been updated'),'success');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = __('Could not add the new account code');
		$SQL = "INSERT INTO chartmasterSMH (accountcode,
						accountname,
						group_)
					VALUES ('" . $_POST['AccountCode'] . "',
							'" . $_POST['AccountName'] . "',
							'" . $_POST['Group'] . "')";
		$Result = DB_query($SQL,$ErrMsg);

		prnMsg(__('The new general ledger account has been added'),'success');
	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	$SQL="DELETE FROM chartmasterSMH WHERE accountcode= '" . $SelectedAccount ."'";
	$Result = DB_query($SQL);
	prnMsg( __('Account') . ' ' . $SelectedAccount . ' ' . __('has been deleted'),'succes');
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" name="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedAccount)) {
		//editing an existing account

		$SQL = "SELECT accountcode, accountname, group_ FROM chartmasterSMH WHERE accountcode='" . $SelectedAccount ."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['AccountCode'] = $MyRow['accountcode'];
		$_POST['AccountName']	= $MyRow['accountname'];
		$_POST['Group'] = $MyRow['group_'];

		echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
		echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
		
		echo '<fieldset><legend>' . __('Account Details') . '</legend>';
		echo FieldToSelectOneText('AccountCode', $_POST['AccountCode'], 30, 30, __('Account Code'), '', '', '', true, true);
	} else {
		echo '<fieldset><legend>' . __('Account Details') . '</legend>';
		echo FieldToSelectOneText('AccountCode', '', 30, 30, __('Account Code'), '', '', '', true, true);
	}

	if (!isset($_POST['AccountName'])) {
		$_POST['AccountName']='';
	}
	echo FieldToSelectOneText('AccountName', $_POST['AccountName'], 51, 50, __('Account Name'), '', '', '', true, false);
	echo FieldToSelectOneGLAccountGroup('Group', (isset($_POST['Group']) ? $_POST['Group'] : ''), __('Account Group'), '', '', '', true, false);
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Enter PT. SMH GL Account Details'));

	echo '</form>';

} //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of chartmasterSMH will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . __('Balance Sheet') . "' ELSE '" . __('Profit/Loss') . "' END AS acttype
		FROM chartmasterSMH,
			accountgroups
		WHERE chartmasterSMH.group_=accountgroups.groupname
		ORDER BY chartmasterSMH.accountcode";

	$ErrMsg = __('The chart accounts could not be retrieved because');

	$Result = DB_query($SQL,$ErrMsg);

	echo '<br /><table class="selection">
			<thead>
				<tr>
					<th>' . __('Account Code') . '</th>
					<th>' . __('Account Name') . '</th>
					<th>' . __('Account Group') . '</th>
					<th>' . __('P/L or B/S') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_row($Result)) {
		echo '<tr class="striped_row">';
		echo "<td>{$MyRow[0]}</td>
			<td>{$MyRow[1]}</td>
			<td>{$MyRow[2]}</td>
			<td>{$MyRow[3]}</td>
			<td><a href=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "?&SelectedAccount={$MyRow[0]}\">" . __('Edit') . "</td>
			<td><a href=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "?&SelectedAccount={$MyRow[0]}&delete=1\" onclick=\"return confirm('" . __('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\">" . __('Delete') . "</td>
			</tr>";
	}
	echo '</tbody></table></div></form>';
} //END IF selected ACCOUNT

//end of ifs and buts!

echo '<p>';

if (isset($SelectedAccount)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">' .  __('Show All Accounts') . '</a></div>';
}

echo '<p />';

include('includes/footer.php');
