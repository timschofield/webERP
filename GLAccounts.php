<?php
/* $Revision: 1.6 $ */

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

	if ($SelectedAccount AND $InputError !=1) {

		$sql = "UPDATE ChartMaster SET AccountName='" . $_POST['AccountName'] . "',
						Group_='" . $_POST['Group'] . "'
					WHERE AccountCode = $SelectedAccount";

		$ErrMsg = _('Could not update the account because');
		$result = DB_query($sql,$db,$ErrMsg);
		$msg = _('The general ledger account has been updated');
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		$ErrMsg = _('Could not add the new account code');
		$sql = 'INSERT INTO ChartMaster (AccountCode,
						AccountName,
						Group_)
					VALUES (' . $_POST['AccountCode'] . ",
						'" . $_POST['AccountName'] . "',
						'" . $_POST['Group'] . "')";
		$result = DB_query($sql,$db,$ErrMsg);

		/*Add the new chart details records for existing periods first */

		$ErrMsg = _('Could not add the chart details for the new account');

		$sql = 'INSERT INTO ChartDetails (AccountCode,
							Period)
				SELECT ' . $_POST['AccountCode'] . ',
					PeriodNo
				FROM Periods';
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('The new general ledger account has been added');

	}

	unset ($_POST['Group']);
	unset ($_POST['AccountCode']);
	unset ($_POST['AccountName']);
	unset($SelectedAccount);
	prnMsg($msg,'success');

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$sql= "SELECT COUNT(*) FROM ChartDetails WHERE ChartDetails.AccountCode = $SelectedAccount";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('chart details that require this account code');

	} else {
// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$sql= "SELECT COUNT(*) FROM GLTrans WHERE GLTrans.Account = $SelectedAccount";

		$ErrMsg = _('Could not test for existing transactions because');

		$result = DB_query($sql,$db,$ErrMsg);

		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this account because transactions have been created using this account'),'warn');
			echo '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions that require this account code');

		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$sql= "SELECT COUNT(*) FROM Companies WHERE DebtorsAct=$SelectedAccount OR PytDiscountAct=$SelectedAccount OR CreditorsAct=$SelectedAccount OR PayrollAct=$SelectedAccount OR GRNAct=$SelectedAccount OR ExchangeDiffAct=$SelectedAccount OR PurchasesExchangeDiffAct=$SelectedAccount OR RetainedEarnings=$SelectedAccount";


			$ErrMsg = _('Could not test for default company GL codes because');

			$result = DB_query($sql,$db,$ErrMsg);

			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this account because it is used as one of the company default accounts'),'warn');

			} else  {
				//PREVENT DELETES IF Company default accounts set up to this account
				$sql= "SELECT COUNT(*) FROM TaxAuthorities WHERE TaxGLCode=$SelectedAccount OR PurchTaxGLAccount =$SelectedAccount";

				$ErrMsg = _('Could not test for tax authority GL codes because');
				$result = DB_query($sql,$db,$ErrMsg);

				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg( _('Cannot delete this account because it is used as one of the tax authority accounts'),'warn');
				} else {
//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$sql= "SELECT COUNT(*) FROM SalesGLPostings WHERE SalesGLCode=$SelectedAccount OR DiscountGLCode=$SelectedAccount";

					$ErrMsg = _('Could not test for existing sales interface GL codes because');

					$result = DB_query($sql,$db,$ErrMsg);

					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg( _('Cannot delete this account because it is used by one of the sales GL posting interface records'),'warn');
					} else {
//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$sql= "SELECT COUNT(*) FROM COGSGLPostings WHERE GLCode=$SelectedAccount";

						$ErrMsg = _('Could not test for existing cost of sales interface codes because');

						$result = DB_query($sql,$db,$ErrMsg);

						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'),'warn');

						} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$sql= "SELECT COUNT(*) FROM StockCategory WHERE StockAct=$SelectedAccount OR AdjGLAct=$SelectedAccount OR PurchPriceVarAct=$SelectedAccount OR MaterialUseageVarAc=$SelectedAccount OR WIPAct=$SelectedAccount";

							$Errmsg = _('Could not test for existing stock GL codes because');

							$result = DB_query($sql,$db,$ErrMsg);

							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg( _('Cannot delete this account because it is used by one of the stock GL posting interface records'),'warn');
							} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$sql= "SELECT COUNT(*) FROM BankAccounts WHERE AccountCode=$SelectedAccount";
								$ErrMsg = _('Could not test for existing bank account GL codes because');

								$result = DB_query($sql,$db,$ErrMsg);

								$myrow = DB_fetch_row($result);
								if ($myrow[0]>0) {
									$CancelDelete = 1;
									prnMsg( _('Cannot delete this account because it is used by one the defined bank accounts'),'warn');
								} else {

									$sql="DELETE FROM ChartMaster WHERE AccountCode=$SelectedAccount";
									$result = DB_query($sql,$db);
									prnMsg( _('Account') . $SelectedAccount . _('has been deleted'),'succes');
								}
							}
						}
					}
				}
			}
		}
	}
}

if (!isset($SelectedAccount)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of ChartMaster will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT AccountCode,
			AccountName,
			Group_,
			CASE WHEN PandL=0 THEN 'Balance Sheet' ELSE 'Profit/Loss' END AS ActType
		FROM ChartMaster,
			AccountGroups
		WHERE ChartMaster.Group_=AccountGroups.GroupName
		ORDER BY ChartMaster.AccountCode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$db,$ErrMsg);

	echo '<CENTER><table border=1>';
	echo "<tr>
		<td class='tableheader'>" . _('Account Code') . "</td>
		<td class='tableheader'>" . _('Account Name') . "</td>
		<td class='tableheader'>" . _('Account Group') . "</td>
		<td class='tableheader'>" . _('P/L or B/S') . "</td>
	</tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}


	printf("<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s&SelectedAccount=%s\">" . _('Edit') . "</td>
		<td><a href=\"%s&SelectedAccount=%s&delete=1\">" . _('Delete') . "</td>
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
	echo '</CENTER></TABLE>';
} //END IF SELECTED ACCOUNT

//end of ifs and buts!

echo '<P>';

if (isset($SelectedAccount)) {
	echo "<Center><a href='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>" .  _('Show All Accounts') . '</a></CENTER>';
}

echo '<P>';

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] .  '?' . SID . "'>";

	if ($SelectedAccount) {
		//editing an existing account

		$sql = "SELECT AccountCode, AccountName, Group_ FROM ChartMaster WHERE AccountCode=$SelectedAccount";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow['AccountCode'];
		$_POST['AccountName']	= $myrow['AccountName'];
		$_POST['Group'] = $myrow['Group_'];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccount' VALUE=$SelectedAccount>";
		echo "<INPUT TYPE=HIDDEN NAME='AccountCode' VALUE=" . $_POST['AccountCode'] .">";
		echo "<CENTER><TABLE><TR><TD>" . _('Account Code') . ":</TD><TD>" . $_POST['AccountCode'] . "</TD></TR>";
	} else {
		echo "<CENTER><TABLE>";
		echo "<TR><TD>Account Code</TD><TD><INPUT TYPE=TEXT NAME='AccountCode' SIZE=11 MAXLENGTH=10></TD></TR>";
	}


	echo '<TR><TD>' . _('Account Name') . ":</TD><TD><input type='Text' SIZE=51 MAXLENGTH=50 name='AccountName' value='" . $_POST['AccountName'] . "'></TD></TR>";

	$sql = 'SELECT GroupName FROM AccountGroups ORDER BY SequenceInTB';
	$result = DB_query($sql, $db);

	echo '<TR><TD>' . _('Account Group') . '</TD><TD><SELECT NAME=Group>';

	while ($myrow = DB_fetch_array($result)){
		if ($myrow[0]==$_POST['Group']){
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow[0] . "'>" . $myrow[0];
	}

	?>

	</SELECT></TD></TR>

	</TABLE></CENTER>

	<CENTER><input type="Submit" name="submit" value="<?php echo _('Enter Information'); ?>"></CENTER>

	</FORM>

<?php } //end if record deleted no point displaying form to add record
include('includes/footer.inc');
?>
