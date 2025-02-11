<?php

include('includes/session.php');
$Title = _('Chart of Accounts Maintenance for PT. Bumi Biru');

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
		_('General Ledger Accounts') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg( _('The account name must be fifty characters or less long'),'warn');
	}

	if (isset($SelectedAccount) AND $InputError !=1) {

		$SQL = "UPDATE chartmasterBB SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
				WHERE accountcode ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not update the account because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = _('Could not add the new account code');
		$SQL = "INSERT INTO chartmasterBB (accountcode,
						accountname,
						group_)
					VALUES ('" . $_POST['AccountCode'] . "',
							'" . $_POST['AccountName'] . "',
							'" . $_POST['Group'] . "')";
		$Result = DB_query($SQL,$ErrMsg);

		prnMsg(_('The new general ledger account has been added'),'success');
	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	$SQL="DELETE FROM chartmasterBB WHERE accountcode= '" . $SelectedAccount ."'";
	$Result = DB_query($SQL);
	prnMsg( _('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'),'succes');
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" name="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedAccount)) {
		//editing an existing account

		$SQL = "SELECT accountcode, accountname, group_ FROM chartmasterBB WHERE accountcode='" . $SelectedAccount ."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['AccountCode'] = $MyRow['accountcode'];
		$_POST['AccountName']	= $MyRow['accountname'];
		$_POST['Group'] = $MyRow['group_'];

		echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
		echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
		
		echo '<fieldset><legend>' . _('Account Details') . '</legend>';
		echo FieldToSelectOneText('AccountCode', $_POST['AccountCode'], 30, 30, _('Account Code'), '', '', '', true, true);
	} else {
		echo '<fieldset><legend>' . _('Account Details') . '</legend>';
		echo FieldToSelectOneText('AccountCode', '', 30, 30, _('Account Code'), '', '', '', true, true);
	}

	if (!isset($_POST['AccountName'])) {
		$_POST['AccountName']='';
	}
	echo FieldToSelectOneText('AccountName', $_POST['AccountName'], 51, 50, _('Account Name'), '', '', '', true, false);
	echo FieldToSelectOneGLAccountGroup('Group', (isset($_POST['Group']) ? $_POST['Group'] : ''), _('Account Group'), '', '', '', true, false);
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Enter PT. BB GL Account Details'));

	echo '</form>';

} //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of chartmasterBB will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmasterBB,
			accountgroups
		WHERE chartmasterBB.group_=accountgroups.groupname
		ORDER BY chartmasterBB.accountcode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$Result = DB_query($SQL,$ErrMsg);

	echo '<br /><table class="selection">
			<thead>
				<tr>
					<th>' . _('Account Code') . '</th>
					<th>' . _('Account Name') . '</th>
					<th>' . _('Account Group') . '</th>
					<th>' . _('P/L or B/S') . '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_row($Result)) {
		echo '<tr class="striped_row">';
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedAccount=%s\">" . _('Edit') . "</td>
			<td><a href=\"%s&SelectedAccount=%s&delete=1\" onclick=\"return confirm('" . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</td>
			</tr>",
			$MyRow[0],
			$MyRow[1],
			$MyRow[2],
			$MyRow[3],
			htmlspecialchars($_SERVER['PHP_SELF']) . '?',
			$MyRow[0],
			htmlspecialchars($_SERVER['PHP_SELF']) . '?',
			$MyRow[0]);
	}
	echo '</tbody></table></div></form>';
} //END IF selected ACCOUNT

//end of ifs and buts!

echo '<p>';

if (isset($SelectedAccount)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">' .  _('Show All Accounts') . '</a></div>';
}

echo '<p />';

include('includes/footer.php');
?>