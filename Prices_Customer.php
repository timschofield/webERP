<?php
/* $Revision: 1.2 $ */
$title = "Special Prices for ";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($Item) OR !isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==""){
	die ("<BR><HR>A customer must be selected from the customer selection screen, then an item must be selected before this page is called. The product selection page should call this page with a valid product code.<HR>");
}

$result = DB_query("SELECT Name, CurrCode, SalesType FROM DebtorsMaster WHERE DebtorNo='" . $_SESSION['CustomerID'] . "'",$db);
$myrow = DB_fetch_row($result);
echo "<FONT COLOR=BLUE><B>" . $myrow[0] . " in " . $myrow[1] . "<BR> for ";

$CurrCode = $myrow[1];
$SalesType = $myrow[2];

$result = DB_query("SELECT Description FROM StockMaster WHERE StockID='$Item'",$db);
$myrow = DB_fetch_row($result);
echo $Item . " - " . $myrow[0] . "</B></FONT><HR>";

if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		$msg = "The price entered must be numeric";
	}

	if ($_POST['Branch'] !=''){
		$sql = "SELECT BranchCode FROM CustBranch WHERE DebtorNo='" . $_SESSION['CustomerID'] . "' AND BranchCode='" . $_POST['Branch'] . "'";
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) ==0){
			$InputError =1;
			$msg = "The branch code entered is not currently defined";
		}
	}

	if ($_POST['Editing']=="Yes" AND strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price

		$sql = "UPDATE Prices SET TypeAbbrev='$SalesType', CurrAbrev='$CurrCode', Price=" . $_POST['Price'] . ", BranchCode ='" . $_POST['Branch'] . "' WHERE StockID='$Item' AND TypeAbbrev='$SalesType' AND CurrAbrev='$CurrCode' AND DebtorNo='" . $_SESSION['CustomerID'] . "'";
		$msg = "Price Updated.";
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */
		$sql = "INSERT INTO Prices (StockID, TypeAbbrev, CurrAbrev, DebtorNo, Price, BranchCode) VALUES ('$Item', '$SalesType', '$CurrCode', '" . $_SESSION['CustomerID'] . "', " . $_POST['Price'] . ", '" . $_POST['Branch'] . "')";
		$msg = "Price added.";
	}
	//run the SQL from either of the above possibilites
	if ($InputError!=1){
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			If ($msg=="Price Updated"){
				$msg = "The price could not be updated because - " . DB_error_msg($db);
			} else {
				$msg = "The price could not be added because - " . DB_error_msg($db);
			}
		}else {
			unset($_POST['Price']);
		}
	}

	echo "<BR>$msg";

} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM Prices WHERE StockID = '". $Item ."' AND TypeAbbrev='". $SalesType ."' AND CurrAbrev ='". $CurrCode ."' AND DebtorNo='" . $_SESSION['CustomerID'] . "' AND BranchCode='" . $_GET['Branch'] . "'";
	$result = DB_query($sql,$db);
	echo "This price has been deleted ! <p>";

}


//Always do this stuff
//Show the normal prices in the currency of this customer

$sql = "SELECT Prices.Price, Prices.TypeAbbrev FROM Prices WHERE Prices.TypeAbbrev = '$SalesType' AND Prices.StockID='$Item' AND Prices.DebtorNo='' AND Prices.CurrAbrev='$CurrCode' ORDER BY TypeAbbrev";

$result = DB_query($sql,$db);

if (DB_error_no($db)!=0){
	echo "Could not retrieve the normal prices set up because " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL used to retreive these records was <BR>$sql";
	}
}

echo "<CENTER><table border=1><TR><TD valign=top>";
echo "<table>";

if (DB_num_rows($result) == 0) {
	echo "<TR><TD>There are no default prices set up for this part</TD></TR>";
} else {
	echo "<tr><td class='tableheader'>Normal Price</td></tr>";
	while ($myrow = DB_fetch_array($result)) {
		printf("<tr bgcolor='#EEEEEE'><td ALIGN=RIGHT>%0.2f</td></tr>", $myrow["Price"]);
	}
}

echo "</table></TD><TD VALIGN=TOP>";

//now get the prices for the customer selected

$sql = "SELECT Prices.Price, Prices.BranchCode, CustBranch.BrName FROM Prices LEFT JOIN CustBranch ON Prices.BranchCode= CustBranch.BranchCode WHERE Prices.TypeAbbrev = '$SalesType' AND Prices.StockID='$Item' AND Prices.DebtorNo='" . $_SESSION['CustomerID'] . "' AND Prices.CurrAbrev='$CurrCode' AND (CustBranch.DebtorNo='" . $_SESSION['CustomerID'] . "' OR CustBranch.DebtorNo IS NULL)";

$result = DB_query($sql,$db);

if (DB_error_no($db)!=0){
	echo "Could not retrieve the special prices set up because " . DB_error_msg($db);
	if ($debug==1){
		echo " The SQL used to retreive these records was <BR>$sql";
	}
}
echo "<table>";

if (DB_num_rows($result) == 0) {
	echo "<TR><TD>There are no special prices set up for this part</TD></TR>";
} else {
/*THERE IS ALREADY A spl price setup */
	echo "<tr><td class='tableheader'>Special Price</td><td class='tableheader'>Branch</td></tr>\n";

	while ($myrow = DB_fetch_array($result)) {

	if ($myrow['BranchCode']==''){
		$Branch = 'All Branches';
	} else {
		$Branch = $myrow['BrName'];
	}

	printf("<tr bgcolor='#CCCCCC'><td ALIGN=RIGHT>%0.2f</td><td>%s</td><td><a href='%s?Item=%s&Price=%s&Branch=%s&Edit=1'>Edit</td><td><a href='%s?Item=%s&Branch=%s&delete=yes'>DELETE</td></tr>", $myrow["Price"],$Branch, $_SERVER['PHP_SELF'], $Item, $myrow["Price"],$myrow['BranchCode'], $_SERVER['PHP_SELF'], $Item,$myrow['BranchCode']);

	}
//END WHILE LIST LOOP
}

?>

</table></tr></table></center>

<p>

<?php
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo "<INPUT TYPE=HIDDEN NAME='Item' VALUE='$Item'>";

	if ($_GET['Edit']==1){
		echo "<INPUT TYPE=HIDDEN NAME='Editing' VALUE='Yes'>";
		$_POST['Price']=$_GET['Price'];
		$_POST['Branch']=$_GET['Branch'];
	}

	echo "<CENTER><TABLE><TR><TD>Branch:</TD><TD><input type='Text' name='Branch' SIZE=11 MAXLENGTH=10 value=" . $_POST['Branch'] . "></TD></TR>";
	echo "<TR><TD>Price:</TD><TD><input type='Text' name='Price' SIZE=11 MAXLENGTH=10 value=" . $_POST['Price'] . "></TD></TR></TABLE></CENTER>";
	echo "<CENTER><input type='Submit' name='submit' value='Enter Information'>";

	echo "</form>";
	include("includes/footer.inc");
?>
