<?php
/* $Id: GLAccounts.php 4837 2012-01-24 23:41:46Z vvs2012 $*/

include('includes/session.php');
$Title = _('Chart of Accounts Maintenance for Retail Partner IK');

$ViewTopic= 'GeneralLedger';
$BookMark = 'GLAccounts';

include('includes/header.php');

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

		$sql = "UPDATE chartmasterIK SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
				WHERE accountcode ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not update the account because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = _('Could not add the new account code');
		$sql = "INSERT INTO chartmasterIK (accountcode,
						accountname,
						group_)
					VALUES ('" . $_POST['AccountCode'] . "',
							'" . $_POST['AccountName'] . "',
							'" . $_POST['Group'] . "')";
		$result = DB_query($sql,$ErrMsg);

		prnMsg(_('The new general ledger account has been added'),'success');
	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	$sql="DELETE FROM chartmasterIK WHERE accountcode= '" . $SelectedAccount ."'";
	$result = DB_query($sql);
	prnMsg( _('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'),'succes');
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" name="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedAccount)) {
		//editing an existing account

		$sql = "SELECT accountcode, accountname, group_ FROM chartmasterIK WHERE accountcode='" . $SelectedAccount ."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow['accountcode'];
		$_POST['AccountName']	= $myrow['accountname'];
		$_POST['Group'] = $myrow['group_'];

		echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
		echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
		echo '<table class="selection">
				<tr><td>' . _('Account Code') . ':</td>
					<td>' . $_POST['AccountCode'] . '</td></tr>';
	} else {
		echo '<table class="selection">';
		echo '<tr><td>' . _('Account Code') . ':</td>
					<td><input type="text" name="AccountCode" size="30" class="number" maxlength="30" /></td>
				</tr>';
	}

	if (!isset($_POST['AccountName'])) {$_POST['AccountName']='';}
	echo '<tr><td>' . _('Account Name') . ':</td><td><input type="Text" size="51" maxlength="50" name="AccountName" value="' . $_POST['AccountName'] . '" /></td></tr>';

	$sql = 'SELECT groupname FROM accountgroups ORDER BY sequenceintb';
	$result = DB_query($sql);

	echo '<tr><td>' . _('Account Group') . ':</td><td><select name=Group>';

	while ($myrow = DB_fetch_array($result)){
		if (isset($_POST['Group']) and $myrow[0]==$_POST['Group']){
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow[0] . '">' . $myrow[0] . '</option>';
	}

	if (!isset($_GET['SelectedAccount']) or $_GET['SelectedAccount']=='') {
		echo '<script>defaultControl(document.GLAccounts.AccountCode);</script>';
	} else {
		echo '<script>defaultControl(document.GLAccounts.AccountName);</script>';
	}

	echo '</select></td></tr></table>';

	echo '<br /><div class="centre"><input type="Submit" name="submit" value="'. _('Enter Information') . '" /></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of chartmasterIK will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmasterIK,
			accountgroups
		WHERE chartmasterIK.group_=accountgroups.groupname
		ORDER BY chartmasterIK.accountcode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);

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

	while ($myrow = DB_fetch_row($result)) {
		echo '<tr class="striped_row">';
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedAccount=%s\">" . _('Edit') . "</td>
			<td><a href=\"%s&SelectedAccount=%s&delete=1\" onclick=\"return confirm('" . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</td>
			</tr>",
			$myrow[0],
			$myrow[1],
			$myrow[2],
			$myrow[3],
			htmlspecialchars($_SERVER['PHP_SELF']) . '?',
			$myrow[0],
			htmlspecialchars($_SERVER['PHP_SELF']) . '?',
			$myrow[0]);
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