<?php
/* $Revision: 1.21 $ */

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Chart of Accounts Maintenance');

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_long((integer)$_POST['AccountCode'])) {
		$InputError = 1;
		prnMsg(_('The account code must be an integer'),'warn');
	} elseif (strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg( _('The account name must be fifty characters or less long'),'warn');
	}

	if (isset($SelectedAccount) AND $InputError !=1) {

		$sql = "UPDATE chartmaster SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
					WHERE accountcode = $SelectedAccount";

		$ErrMsg = _('Could not update the account because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = _('Could not add the new account code');
		$sql = 'INSERT INTO chartmaster (accountcode,
						accountname,
						group_)
					VALUES (' . $_POST['AccountCode'] . ",
						'" . $_POST['AccountName'] . "',
						'" . $_POST['Group'] . "')";
		$result = DB_query($sql,$db,$ErrMsg);

		/*Add the new chart details records for existing periods first */

		$ErrMsg = _('Could not add the chart details for the new account');

		$sql = 'INSERT INTO chartdetails (accountcode, period)
				SELECT chartmaster.accountcode, periods.periodno
					FROM chartmaster
						CROSS  JOIN periods
				WHERE ( chartmaster.accountcode, periods.periodno ) NOT
					IN ( SELECT chartdetails.accountcode, chartdetails.period FROM chartdetails )';

		$result = DB_query($sql,$db,$ErrMsg);

		prnMsg(_('The new general ledger account has been added'),'success');
	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'



	$sql= "SELECT COUNT(*) FROM chartdetails WHERE chartdetails.accountcode = $SelectedAccount AND chartdetails.actual <>0";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account and at least one period has postings to it'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('chart details that require this account code');

	} else {
// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$sql= "SELECT COUNT(*) FROM gltrans WHERE gltrans.account = $SelectedAccount";

		$ErrMsg = _('Could not test for existing transactions because');

		$result = DB_query($sql,$db,$ErrMsg);

		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this account because transactions have been created using this account'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions that require this account code');

		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$sql= "SELECT COUNT(*) FROM companies
				WHERE debtorsact=$SelectedAccount
				OR pytdiscountact=$SelectedAccount
				OR creditorsact=$SelectedAccount
				OR payrollact=$SelectedAccount
				OR grnact=$SelectedAccount
				OR exchangediffact=$SelectedAccount
				OR purchasesexchangediffact=$SelectedAccount
				OR retainedearnings=$SelectedAccount";


			$ErrMsg = _('Could not test for default company GL codes because');

			$result = DB_query($sql,$db,$ErrMsg);

			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this account because it is used as one of the company default accounts'),'warn');

			} else  {
				//PREVENT DELETES IF Company default accounts set up to this account
				$sql= "SELECT COUNT(*) FROM taxauthorities
					WHERE taxglcode=$SelectedAccount
					OR purchtaxglaccount =$SelectedAccount";

				$ErrMsg = _('Could not test for tax authority GL codes because');
				$result = DB_query($sql,$db,$ErrMsg);

				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg( _('Cannot delete this account because it is used as one of the tax authority accounts'),'warn');
				} else {
//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$sql= "SELECT COUNT(*) FROM salesglpostings
						WHERE salesglcode=$SelectedAccount
						OR discountglcode=$SelectedAccount";

					$ErrMsg = _('Could not test for existing sales interface GL codes because');

					$result = DB_query($sql,$db,$ErrMsg);

					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg( _('Cannot delete this account because it is used by one of the sales GL posting interface records'),'warn');
					} else {
//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$sql= "SELECT COUNT(*) FROM cogsglpostings WHERE glcode=$SelectedAccount";

						$ErrMsg = _('Could not test for existing cost of sales interface codes because');

						$result = DB_query($sql,$db,$ErrMsg);

						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'),'warn');

						} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$sql= "SELECT COUNT(*) FROM stockcategory
								WHERE stockact=$SelectedAccount
								OR adjglact=$SelectedAccount
								OR purchpricevaract=$SelectedAccount
								OR materialuseagevarac=$SelectedAccount
								OR wipact=$SelectedAccount";

							$Errmsg = _('Could not test for existing stock GL codes because');

							$result = DB_query($sql,$db,$ErrMsg);

							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg( _('Cannot delete this account because it is used by one of the stock GL posting interface records'),'warn');
							} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$sql= "SELECT COUNT(*) FROM bankaccounts
								WHERE accountcode=$SelectedAccount";
								$ErrMsg = _('Could not test for existing bank account GL codes because');

								$result = DB_query($sql,$db,$ErrMsg);

								$myrow = DB_fetch_row($result);
								if ($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg( _('Cannot delete this account because it is used by one the defined bank accounts'),'warn');
								} else {

									$sql = 'DELETE FROM chartdetails WHERE accountcode=' . $SelectedAccount;
									$result = DB_query($sql,$db);
									$sql="DELETE FROM chartmaster WHERE accountcode= $SelectedAccount";
									$result = DB_query($sql,$db);
									prnMsg( _('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'),'succes');
								}
							}
						}
					}
				}
			}
		}
	}
}

if (!isset($_GET['delete'])) {

	echo "<form method='post' name='GLAccounts' action='" . $_SERVER['PHP_SELF'] .  '?' . SID . "'>";

	if (isset($SelectedAccount)) {
		//editing an existing account

		$sql = "SELECT accountcode, accountname, group_ FROM chartmaster WHERE accountcode=$SelectedAccount";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow['accountcode'];
		$_POST['AccountName']	= $myrow['accountname'];
		$_POST['Group'] = $myrow['group_'];

		echo "<input type=hidden name='SelectedAccount' VALUE=$SelectedAccount>";
		echo "<input type=hidden name='AccountCode' VALUE=" . $_POST['AccountCode'] .">";
		echo "<table><tr><td>" . _('Account Code') . ":</td><td>" . $_POST['AccountCode'] . "</td></tr>";
	} else {
		echo "<table>";
		echo "<tr><td>" . _('Account Code') . ":</td><td><input type=TEXT name='AccountCode' size=11 class=number maxlength=10></td></tr>";
	}

	if (!isset($_POST['AccountName'])) {$_POST['AccountName']='';}
	echo '<tr><td>' . _('Account Name') . ":</td><td><input type='Text' size=51 maxlength=50 name='AccountName' value='" . $_POST['AccountName'] . "'></td></tr>";

	$sql = 'SELECT groupname FROM accountgroups ORDER BY sequenceintb';
	$result = DB_query($sql, $db);

	echo '<tr><td>' . _('Account Group') . ':</td><td><select name=Group>';

	while ($myrow = DB_fetch_array($result)){
		if (isset($_POST['Group']) and $myrow[0]==$_POST['Group']){
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow[0] . "'>" . $myrow[0];
	}

	if (!isset($_GET['SelectedAccount']) or $_GET['SelectedAccount']=='') {
		echo "<script>defaultControl(document.GLAccounts.AccountCode);</script>";
	} else {
		echo "<script>defaultControl(document.GLAccounts.AccountName);</script>";
	}
	
	?>

	</select></td></tr>

	</table>

	<div class="centre"><input type="Submit" name="submit" value="<?php echo _('Enter Information'); ?>"></div>

	</form>

<?php } //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of ChartMaster will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		ORDER BY chartmaster.accountcode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$db,$ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Account Code') . "</th>
		<th>" . _('Account Name') . "</th>
		<th>" . _('Account Group') . "</th>
		<th>" . _('P/L or B/S') . "</th>
	</tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}


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
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0]);

	}
	//END WHILE LIST LOOP
	echo '</table>';
} //END IF selected ACCOUNT

//end of ifs and buts!

echo '<p>';

if (isset($SelectedAccount)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>" .  _('Show All Accounts') . '</a></div>';
}

echo '<p>';

include('includes/footer.inc');
?>