<?php
/* $Revision: 1.2 $ */
$title = "Currencies Maintenance";

$PageSecurity = 9;

include("includes/session.inc");
include("includes/header.inc");
include("includes/SQL_CommonFunctions.inc");

if (isset($_GET['SelectedCurrency'])){
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])){
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$CompanyRecord = ReadInCompanyRecord($db);
$FunctionalCurrency = $CompanyRecord['CurrencyDefault'];


if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible

	if (strlen($_POST['Abbreviation']) > 3) {
		$InputError = 1;
		echo "<BR>The currency abbreviation must be 3 characters or less long";
	} elseif (!is_real((real) $_POST['ExchangeRate'])){
		$InputError = 1;
		echo "<BR>The exchange rate must be numeric.";
	} elseif (strlen($_POST['CurrencyName']) > 20) {
		$InputError = 1;
		echo "<BR>The currency name must be 20 characters or less long";
	} elseif (strlen($_POST['Country']) > 50) {
		$InputError = 1;
		echo "<BR>The currency country must be 50 characters or less long";
	} elseif (strlen($_POST['HundredsName']) > 15) {
		$InputError = 1;
		echo "<BR>The hundredths name must be 15 characters or less long";
	} elseif ($SelectedCurrency == $FunctionalCurrency){
		$InputError = 1;
		echo "<BR>The functional currency cannot be modified or deleted";
	}

	if (isset($SelectedCurrency) AND $InputError !=1) {

		/*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$sql = "UPDATE Currencies SET Currency='" . $_POST['CurrencyName'] . "', Country='". $_POST['Country']. "', HundredsName='" . $_POST['HundredsName'] . "', Rate=" .$_POST['ExchangeRate'] . " WHERE CurrAbrev = '" . $SelectedCurrency . "'";

		$msg = "<BR>The currency definition record has been updated.";
	} else if ($InputError !=1) {

	/*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
	$sql = "INSERT INTO Currencies (Currency, CurrAbrev, Country, HundredsName, Rate) VALUES ('" . $_POST['CurrencyName'] . "', '" . $_POST['Abbreviation'] . "', '" . $_POST['Country'] . "', '" . $_POST['HundredsName'] .  "', " . $_POST['ExchangeRate'] . ")";

	$msg = "<BR>The currency definition record has been added.";
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	echo "<BR>$msg";
	unset($SelectedCurrency);

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*) FROM DebtorsMaster WHERE DebtorsMaster.CurrCode = '$SelectedCurrency'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0)
	{
		echo "<BR>Cannot delete this currency, because customer accounts have been created referring to this currency.";
		echo "<br> There are " . $myrow[0] . " customer accounts that refer to this currency.";
	}
	else
	{
		$sql= "SELECT COUNT(*) FROM Suppliers WHERE Suppliers.CurrCode = '$SelectedCurrency'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0)
		{
			echo "<BR>Cannot delete this currency, because supplier accounts have been created referring to this currency";
			echo "<br> There are " . $myrow[0] . " supplier accounts that refer to this currency";
		} else {
			$sql= "SELECT COUNT(*) FROM BankTrans WHERE BankTrans.CurrCode = '$SelectedCurrency'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0){
				echo "<BR>Cannot delete this currency, because there are bank transactions that use this currency";
				echo "<br> There are " . $myrow[0] . " bank transactions that refer to this currency";
			} elseif ($FunctionalCurrency==$SelectedCurrency){
				echo "<BR>Cannot delete this currency, because it is the functional currency of the company.";
			} else {
				//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
				$sql="DELETE FROM Currencies WHERE CurrAbrev='$SelectedCurrency'";
				$result = DB_query($sql,$db);
				echo "<BR>The currency definition record has been deleted ! <p>";
			}
		}
	}
	//end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT Currency, CurrAbrev, Country, HundredsName, Rate FROM Currencies";
	$result = DB_query($sql, $db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td class='tableheader'>Abbreviation</td><td class='tableheader'>Currency Name</td><td class='tableheader'>Country</td><td class='tableheader'>Hundredths Name</td><td class='tableheader'>Exchange Rate</td></tr>\n";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {
		if ($myrow[1]==$FunctionalCurrency){
			echo "<tr bgcolor='#FFbbbb'>";
		} elseif ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		if ($myrow[1]!=$FunctionalCurrency){
			printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%sSelectedCurrency=%s\">EDIT</a></td><td><a href=\"%sSelectedCurrency=%s&delete=1\">DELETE</a></td></tr>", $myrow[1], $myrow[0], $myrow[2], $myrow[3], $myrow[4], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[1], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[1]);
		} else {
			printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td colspan=2>Functional Currency</td></tr>", $myrow[1], $myrow[0], $myrow[2], $myrow[3], $myrow[4]);
		}

	} //END WHILE LIST LOOP
} //end of ifs and buts!

?>


</table></CENTER>
<p>
<?php if (isset($SelectedCurrency)) {  ?>
	<Center><a href='<?php echo $_SERVER['PHP_SELF']  . "?" . SID; ?>'>Show all Currency Definitions</a></Center>
<?php } ?>

<P>


<?php

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedCurrency) AND $SelectedCurrency!="") {
		//editing an existing payment terms

		$sql = "SELECT Currency, CurrAbrev, Country, HundredsName, Rate FROM Currencies WHERE CurrAbrev='$SelectedCurrency'";

		$result = DB_query($sql, $db);

		if (DB_error_no!=0){
			echo "<BR>An error occurred in retrieving the currency information. The error said:<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was:<BR>" . $sql;
			}
		}

		$myrow = DB_fetch_array($result);

		$_POST['Abbreviation'] = $myrow["CurrAbrev"];
		$_POST['CurrencyName']  = $myrow["Currency"];
		$_POST['Country']  = $myrow["Country"];
		$_POST['HundredsName']  = $myrow["HundredsName"];
		$_POST['ExchangeRate']  = $myrow["Rate"];



		echo "<INPUT TYPE=HIDDEN NAME='SelectedCurrency' VALUE='" . $SelectedCurrency . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='Abbreviation' VALUE='" . $_POST['Abbreviation'] . "'>";
		echo "<CENTER><TABLE><TR><TD>Currency Abbreviation:</TD><TD>";
		echo $_POST['Abbreviation'] . "</TD></TR>";

	} else { //end of if $SelectedCurrency only do the else when a new record is being entered

		echo "<CENTER><TABLE><TR><TD>Currency Abbreviation:</TD><TD><input type='Text' name='Abbreviation' value='" . $_POST['Abbreviation'] . "' SIZE=4 MAXLENGTH=3></TD></TR>";
	}

	?>


	<TR><TD>Currency Name:</TD>
	<TD>
	<INPUT TYPE="text" name="CurrencyName" VALUE="<?php echo $_POST['CurrencyName'];?>" SIZE=20 MAXLENGTH=20>
	</TD></TR>
	<TR><TD>Country:</TD>
	<TD>
	<INPUT TYPE="text" name="Country" VALUE="<?php echo $_POST['Country'];?>" SIZE=30 MAXLENGTH=50>
	</TD></TR>
	<TR><TD>Hundredths Name:</TD>
	<TD>
	<INPUT TYPE="text" name="HundredsName" VALUE="<?php echo $_POST['HundredsName'];?>" SIZE=10 MAXLENGTH=15>
	</TD></TR>
	<TR><TD>Exchange Rate:</TD>
	<TD>
	<INPUT TYPE="text" name="ExchangeRate" VALUE="<?php echo $_POST['ExchangeRate'];?>" SIZE=10 MAXLENGTH=9>
	</TD></TR>
	</TABLE>

	<CENTER><input type="Submit" name="submit" value="Enter Information">

	</FORM>

<?php
} //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>
