<?php
$title = "General Ledger Chart of Accounts Maintenance";
$PageSecurity = 10;
include("includes/session.inc");
include("includes/header.inc");

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
		echo "The account code must be an integer.";
	} elseif (strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		echo "The account name must be fifty characters or less long";
	}

	if ($SelectedAccount AND $InputError !=1) {

		$sql = "UPDATE ChartMaster SET AccountName='" . $_POST['AccountName'] . "', Group_='" . $_POST['Group'] . "' WHERE AccountCode = $SelectedAccount";
		$msg = "The general ledger account has been updated.";
	} elseif ($InputError !=1) {

	/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */
		/*Add the new chart details records for existing periods first */
		$sql = "INSERT INTO ChartDetails (AccountCode, Period) SELECT " . $_POST["AccountCode"] . ", PeriodNo FROM Periods";
		$result = DB_query($sql,$db);

		$sql = "INSERT INTO ChartMaster (AccountCode, AccountName, Group_) VALUES (" . $_POST['AccountCode'] . ", '" . $_POST['AccountName'] . "', '" . $_POST['Group'] . "')";
		$msg = "The new general ledger account has been added.";

	}

	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "The general ledger account could not be inserted/updated because - " . DB_error_msg($db) . ". The SQL that was used and failed was:<BR>" . $sql;
		exit;
	} else {
		unset ($_POST['Group']);
		unset ($_POST['AccountCode']);
		unset ($_POST['AccountName']);
		unset($SelectedAccount);
		echo "<BR>$msg";

	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$sql= "SELECT COUNT(*) FROM ChartDetails WHERE ChartDetails.AccountCode = $SelectedAccount";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo "Cannot delete this account because chart details have been created using this account.";
		echo "<br> There are " . $myrow[0] . " chart details that require this account code";

	} else {
// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$sql= "SELECT COUNT(*) FROM GLTrans WHERE GLTrans.Account = $SelectedAccount";
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "Couldn't test for existing transactions because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that was used and failed was:<BR>" . $sql;
			}
			exit;
		}
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo "Cannot delete this account because transactions have been created using this account.";
			echo "<br> There are " . $myrow[0] . " transactions that require this account code";

		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$sql= "SELECT COUNT(*) FROM Companies WHERE DebtorsAct=$SelectedAccount OR PytDiscountAct=$SelectedAccount OR CreditorsAct=$SelectedAccount OR PayrollAct=$SelectedAccount OR GRNAct=$SelectedAccount OR ExchangeDiffAct=$SelectedAccount OR PurchasesExchangeDiffAct=$SelectedAccount OR RetainedEarnings=$SelectedAccount";

			$result = DB_query($sql,$db);
			if (DB_error_no($db)!=0){
				echo "Couldn't test for default company GL codes because - " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The SQL that was used and failed was:<BR>" . $sql;
				}
				exit;
			}

			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				echo "Cannot delete this account because it is used as one of the company default accounts.";

			} else  {
				//PREVENT DELETES IF Company default accounts set up to this account
				$sql= "SELECT COUNT(*) FROM TaxAuthorities WHERE TaxGLCode=$SelectedAccount OR PurchTaxGLAccount =$SelectedAccount";
				$result = DB_query($sql,$db);
				if (DB_error_no($db)!=0){
					echo "<BR>Couldn't test for tax authority GL codes because - " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The SQL that was used and failed was:<BR>" . $sql;
					}
					exit;
				}

				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					echo "<BR>Cannot delete this account because it is used as one of the tax authority accounts.";

				}else {
//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$sql= "SELECT COUNT(*) FROM SalesGLPostings WHERE SalesGLCode=$SelectedAccount OR DiscountGLCode=$SelectedAccount";
					$result = DB_query($sql,$db);
					if (DB_error_no($db)!=0){
						echo "<BR>Couldn't test for existing sales interface GL codes because - " . DB_error_msg($db);
						if ($debug==1){
							echo "<BR>The SQL that was used and failed was:<BR>". $sql;
						}
						exit;
					}

					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						echo "<BR>Cannot delete this account because it is used by one of the  sales GL posting interface records.";
					} else {
//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$sql= "SELECT COUNT(*) FROM COGSGLPostings WHERE GLCode=$SelectedAccount";
						$result = DB_query($sql,$db);

						if (DB_error_no($db)!=0){
							echo "<BR>Couldn't test for existing cost of sales interface codes because - " . DB_error_msg($db);
							if ($debug==1){
								echo "<BR>The SQL that was used and failed was:<BR>$sql";
							}
							exit;
						}

						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							echo "<BR>Cannot delete this account because it is used by one of the cost of sales GL posting interface records.";

						} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$sql= "SELECT COUNT(*) FROM StockCategory WHERE StockAct=$SelectedAccount OR AdjGLAct=$SelectedAccount OR PurchPriceVarAct=$SelectedAccount OR MaterialUseageVarAc=$SelectedAccount OR WIPAct=$SelectedAccount";
							$result = DB_query($sql,$db);
							if (DB_error_no($db)!=0){
								echo "<BR>Couldn't test for existing stock GL codes because - " . DB_error_msg($db);
								if ($debug==1){
									echo "<BR>The SQL that was used and failed was:<BR>" . $sql;
								}
								exit;
							}
							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								echo "<BR>Cannot delete this account because it is used by one of the stock GL posting interface records.";
							} else {
//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$sql= "SELECT COUNT(*) FROM BankAccounts WHERE AccountCode=$SelectedAccount";
								$result = DB_query($sql,$db);
								if (DB_error_no($db)!=0){
									echo "<BR>Couldn't test for existing bank account GL codes because - " . DB_error_msg($db);
									if ($debug==1){
										echo "<BR>The SQL that was used and failed was:<BR>" . $sql;
									}
									exit;
								}
								$myrow = DB_fetch_row($result);
								if ($myrow[0]>0) {
									$CancelDelete = 1;
									echo "<BR>Cannot delete this account because it is used by one the defined bank accounts.";
								} else {

									$sql="DELETE FROM ChartMaster WHERE AccountCode=$SelectedAccount";
									$result = DB_query($sql,$db);
									echo "<BR><CENTER><FONT COLOR=RED><B>Account $SelectedAccount has been deleted ! </B></FONT></CENTER><p>";
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

	$sql = "SELECT AccountCode, AccountName, Group_, CASE WHEN PandL=0 THEN 'Balance Sheet' ELSE 'Profit/Loss' END AS ActType FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName ORDER BY ChartMaster.AccountCode";

	$result = DB_query($sql,$db);

	if (DB_error_no($db)!=0){
		echo "The chart accounts could not be retrieved because - " . DB_error_msg($db) . ". The sql that was used and failed was:<BR>$sql";
		exit;
	}

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Account Code</td><td class='tableheader'>Account Name</td><td class='tableheader'>Account Group</td><td class='tableheader'>P/L Or B/S</td></tr>";

$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}


	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%sSelectedAccount=%s\">Edit</td><td><a href=\"%sSelectedAccount=%s&delete=1\">Delete</td></tr>", $myrow[0], $myrow[1], $myrow[2],$myrow[3],$_SERVER['PHP_SELF'] . "?" . SID, $myrow[0], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[0]);

	}
	//END WHILE LIST LOOP
} //END IF SELECTED ACCOUNT

//end of ifs and buts!

?>
</CENTER></table>
<p>
<?php
if (isset($SelectedAccount)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . "?" . SID;?>">Show All Accounts</a></Center>
<?php } ?>

<P>

<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] .  "?" . SID . "'>";

	if ($SelectedAccount) {
		//editing an existing account

		$sql = "SELECT AccountCode, AccountName, Group_ FROM ChartMaster WHERE AccountCode=$SelectedAccount";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow["AccountCode"];
		$_POST['AccountName']	= $myrow["AccountName"];
		$_POST['Group'] = $myrow["Group_"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedAccount' VALUE=$SelectedAccount>";
		echo "<INPUT TYPE=HIDDEN NAME='AccountCode' VALUE=" . $_POST['AccountCode'] .">";
		echo "<CENTER><TABLE><TR><TD>Account Code:</TD><TD>" . $_POST['AccountCode'] . "</TD></TR>";
	} else {
		echo "<CENTER><TABLE>";
		echo "<TR><TD>Account Code</TD><TD><INPUT TYPE=TEXT NAME='AccountCode' SIZE=11 MAXLENGTH=10></TD></TR>";
	}


	echo "<TR><TD>Account Name:</TD><TD><input type='Text' SIZE=51 MAXLENGTH=50 name='AccountName' value='" . $_POST['AccountName'] . "'></TD></TR>";

	$sql = "SELECT GroupName FROM AccountGroups ORDER BY SequenceInTB";
	$result = DB_query($sql, $db);

	echo "<TR><TD>Account Group</TD><TD><SELECT NAME=Group>";

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

	<CENTER><input type="Submit" name="submit" value="Enter Information"></CENTER>

	</FORM>

<?php } //end if record deleted no point displaying form to add record
include("includes/footer.inc");
?>
