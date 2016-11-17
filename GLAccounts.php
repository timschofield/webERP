<?php
/* $Id$*/
/* Defines the general ledger accounts */

include('includes/session.inc');
$Title = _('General Ledger Accounts');
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLAccounts';
include('includes/header.inc');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/transactions.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

if(isset($_POST['SelectedAccount'])) {
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif(isset($_GET['SelectedAccount'])) {
	$SelectedAccount = $_GET['SelectedAccount'];
}

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if(mb_strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg(_('The account name must be fifty characters or less long'), 'warn');
	}

	if(isset($SelectedAccount) AND $InputError != 1) {

		$Sql = "UPDATE chartmaster SET accountname='" . $_POST['AccountName'] . "',
						group_='" . $_POST['Group'] . "'
				WHERE accountcode ='" . $SelectedAccount . "'";
		$ErrMsg = _('Could not update the account because');
		$Result = DB_query($Sql, $ErrMsg);

		prnMsg (_('The general ledger account has been updated'),'success');
	} elseif($InputError != 1) {

		/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$Sql = "INSERT INTO chartmaster (accountcode,
						accountname,
						group_)
					VALUES ('" . $_POST['AccountCode'] . "',
							'" . $_POST['AccountName'] . "',
							'" . $_POST['Group'] . "')";
		$ErrMsg = _('Could not add the new account code');
		$Result = DB_query($Sql, $ErrMsg);

		prnMsg(_('The new general ledger account has been added'),'success');
	}

	unset($_POST['Group']);
	unset($_POST['AccountCode']);
	unset($_POST['AccountName']);
	unset($SelectedAccount);

} elseif(isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$Sql= "SELECT COUNT(*)
			FROM chartdetails
			WHERE chartdetails.accountcode ='" . $SelectedAccount . "'
			AND chartdetails.actual <>0";
	$Result = DB_query($Sql);
	$MyRow = DB_fetch_row($Result);
	if($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account and at least one period has postings to it'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('chart details that require this account code');

	} else {
// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$Sql = "SELECT COUNT(*)
				FROM gltrans
				WHERE gltrans.account ='" . $SelectedAccount . "'";
		$ErrMsg = _('Could not test for existing transactions because');
		$Result = DB_query($Sql, $ErrMsg);

		$MyRow = DB_fetch_row($Result);
		if($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this account because transactions have been created using this account'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions that require this account code');

		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$Sql = "SELECT COUNT(*) FROM companies
					WHERE debtorsact='" . $SelectedAccount . "'
					OR pytdiscountact='" . $SelectedAccount . "'
					OR creditorsact='" . $SelectedAccount . "'
					OR payrollact='" . $SelectedAccount . "'
					OR grnact='" . $SelectedAccount . "'
					OR exchangediffact='" . $SelectedAccount . "'
					OR purchasesexchangediffact='" . $SelectedAccount . "'
					OR retainedearnings='" . $SelectedAccount . "'";
			$ErrMsg = _('Could not test for default company GL codes because');
			$Result = DB_query($Sql, $ErrMsg);

			$MyRow = DB_fetch_row($Result);
			if($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this account because it is used as one of the company default accounts'), 'warn');

			} else {
				//PREVENT DELETES IF Company default accounts set up to this account
				$Sql = "SELECT COUNT(*) FROM taxauthorities
					WHERE taxglcode='" . $SelectedAccount ."'
					OR purchtaxglaccount ='" . $SelectedAccount ."'";
				$ErrMsg = _('Could not test for tax authority GL codes because');
				$Result = DB_query($Sql, $ErrMsg);

				$MyRow = DB_fetch_row($Result);
				if($MyRow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this account because it is used as one of the tax authority accounts'), 'warn');
				} else {
//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$Sql = "SELECT COUNT(*) FROM salesglpostings
						WHERE salesglcode='" . $SelectedAccount . "'
						OR discountglcode='" . $SelectedAccount . "'";
					$ErrMsg = _('Could not test for existing sales interface GL codes because');
					$Result = DB_query($Sql, $ErrMsg);

					$MyRow = DB_fetch_row($Result);
					if($MyRow[0] > 0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this account because it is used by one of the sales GL posting interface records'), 'warn');
					} else {
//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$Sql = "SELECT COUNT(*)
								FROM cogsglpostings
								WHERE glcode='" . $SelectedAccount . "'";
						$ErrMsg = _('Could not test for existing cost of sales interface codes because');
						$Result = DB_query($Sql, $ErrMsg);

						$MyRow = DB_fetch_row($Result);
						if($MyRow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'), 'warn');

						} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$Sql = "SELECT COUNT(*) FROM stockcategory
									WHERE stockact='" . $SelectedAccount . "'
									OR adjglact='" . $SelectedAccount . "'
									OR purchpricevaract='" . $SelectedAccount . "'
									OR materialuseagevarac='" . $SelectedAccount . "'
									OR wipact='" . $SelectedAccount . "'";
							$Errmsg = _('Could not test for existing stock GL codes because');
							$Result = DB_query($Sql,$ErrMsg);

							$MyRow = DB_fetch_row($Result);
							if($MyRow[0]>0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this account because it is used by one of the stock GL posting interface records'), 'warn');
							} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$Sql= "SELECT COUNT(*) FROM bankaccounts
								WHERE accountcode='" . $SelectedAccount ."'";
								$ErrMsg = _('Could not test for existing bank account GL codes because');
								$Result = DB_query($Sql,$ErrMsg);

								$MyRow = DB_fetch_row($Result);
								if($MyRow[0]>0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this account because it is used by one the defined bank accounts'), 'warn');
								} else {

									$Sql = "DELETE FROM chartdetails WHERE accountcode='" . $SelectedAccount ."'";
									$Result = DB_query($Sql);
									$Sql="DELETE FROM chartmaster WHERE accountcode= '" . $SelectedAccount ."'";
									$Result = DB_query($Sql);
									prnMsg(_('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'), 'succes');
								}
							}
						}
					}
				}
			}
		}
	}
}

if(!isset($_GET['delete'])) {

	echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedAccount)) {
		//editing an existing account

		$Sql = "SELECT accountcode, accountname, group_ FROM chartmaster WHERE accountcode='" . $SelectedAccount ."'";

		$Result = DB_query($Sql);
		$MyRow = DB_fetch_array($Result);

		$_POST['AccountCode'] = $MyRow['accountcode'];
		$_POST['AccountName']	= $MyRow['accountname'];
		$_POST['Group'] = $MyRow['group_'];

		echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
		echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
		echo '<table class="selection">
				<tr><td>' . _('Account Code') . ':</td>
					<td>' . $_POST['AccountCode'] . '</td></tr>';
	} else {
		echo '<table class="selection">';
		echo '<tr>
				<td>' . _('Account Code') . ':</td>
				<td><input type="text" name="AccountCode" required="required" autofocus="autofocus" data-type="no-illegal-chars" title="' . _('Enter up to 20 alpha-numeric characters for the general ledger account code') . '" size="20" maxlength="20" /></td>
			</tr>';
	}

	if(!isset($_POST['AccountName'])) {
		$_POST['AccountName']='';
	}
	echo '<tr>
			<td>' . _('Account Name') . ':</td>
			<td><input type="text" size="51" required="required" ' . (isset($_POST['AccountCode']) ? 'autofocus="autofocus"':'') . ' title="' . _('Enter up to 50 alpha-numeric characters for the general ledger account name') . '" maxlength="50" name="AccountName" value="' . $_POST['AccountName'] . '" /></td></tr>';

	$Sql = "SELECT groupname FROM accountgroups ORDER BY sequenceintb";
	$Result = DB_query($Sql);

	echo '<tr>
			<td>' . _('Account Group') . ':</td>
			<td><select required="required" name="Group">';

	while($MyRow = DB_fetch_array($Result)) {
		if(isset($_POST['Group']) and $MyRow[0]==$_POST['Group']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow[0] . '">' . $MyRow[0] . '</option>';
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="'. _('Enter Information') . '" />
		</div>
		</div>
		</form>';

} //end if record deleted no point displaying form to add record


if(!isset($SelectedAccount)) {
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of ChartMaster will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	echo '<br />
		<table class="selection">
			<tr>
				<th class="ascending">' . _('Account Code') . '</th>
				<th class="ascending">' . _('Account Name') . '</th>
				<th class="ascending">' . _('Account Group') . '</th>
				<th class="ascending">' . _('P/L or B/S') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>';

	$Sql = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		ORDER BY chartmaster.accountcode";
	$ErrMsg = _('The chart accounts could not be retrieved because');
	$Result = DB_query($Sql, $ErrMsg);

	$k = 0;// Row colour counter.
	while($MyRow = DB_fetch_row($Result)) {
		if($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo	'<td>', $MyRow[0], '</td>
				<td>', htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8'), '</td>
				<td>', $MyRow[2], '</td>
				<td>', $MyRow[3], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedAccount=', $MyRow[0], '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedAccount=', $MyRow[0], '&amp;delete=1" onclick="return confirm(\'', _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.'), '\');">', _('Delete'), '</a></td>
			</tr>';
	}// END WHILE LIST LOOP.
	echo '</table>';
} //END IF selected ACCOUNT

//end of ifs and buts!

echo '<br />';

if(isset($SelectedAccount)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Accounts') . '</a></div>';
}

include('includes/footer.inc');
?>