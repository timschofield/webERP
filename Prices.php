<?php
/* $Revision: 1.2 $ */
$title = "Item Prices";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/SQL_CommonFunctions.inc");

//initialise no input errors assumed initially before we test
$InputError = 0;


if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($_POST['TypeAbbrev']) OR $_POST['TypeAbbrev']==""){
	$_POST['TypeAbbrev'] = $DefaultPriceList;
}

if (!isset($_POST['CurrAbrev'])){
	$CompanyRecord = ReadInCompanyRecord(&$db);
	$_POST['CurrAbrev'] = $CompanyRecord["CurrencyDefault"];
}

$result = DB_query("SELECT Description, MBflag FROM StockMaster WHERE StockID='$Item'",$db);
$myrow = DB_fetch_row($result);

if (DB_num_rows($result)==0){
	echo "<BR>The part code entered does not exist in the database. Only valid parts can have prices entered against them.";
	$InputError=1;
}


if (!isset($Item)){
	die ("An item must first be selected before this page is called. The product selection page should call this page with a valid product code.");
}



echo "<BR><FONT COLOR=BLUE SIZE=3><B>$Item - " . $myrow[0] . "</B></FONT> ";

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo "Pricing for part:<input type=text name='Item' maxsize=22 value='$Item' maxlength=20><INPUT TYPE=SUBMIT NAME=NewPart Value='Review Prices'>";
echo "<HR>";

if ($myrow[1]=="K"){
	echo "<P><FONT SIZE=4 COLOR=RED>Problem Report:<BR></FONT>The part selected is a kit set item, these items explode into their components when selected on an order, prices must be set up for the components and no price can be set for the whole kit";
	exit;
}

if ($_POST['submit']) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		$msg = "<BR>The price entered must be numeric";
	}

	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price
		$sql = "UPDATE Prices SET TypeAbbrev='" . $_POST['TypeAbbrev'] . "', CurrAbrev='" . $_POST['CurrAbrev'] . "', Price=" . $_POST['Price'] . " WHERE StockID='$Item' AND TypeAbbrev='" . $_POST['OldTypeAbbrev'] . "' AND CurrAbrev='" . $_POST['OldCurrAbrev'] . "' AND DebtorNo=''";

		$msg = "<BR>This price has been updated.";
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */

		$sql = "INSERT INTO Prices (StockID, TypeAbbrev, CurrAbrev, DebtorNo, Price) VALUES ('$Item', '" . $_POST['TypeAbbrev'] . "', '" . $_POST['CurrAbrev'] . "', ''," . $_POST['Price'] . ")";
		$msg = "<BR>The new price has been added.";
	}
	//run the SQL from either of the above possibilites only if there were no input errors
	if ($InputError !=1){
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			If ($msg=="<BR><BR>This price has been updated"){
				$msg = "<BR>The price could not be updated because - " . DB_error_msg($db);
			} else {
				$msg = "<BR>The price could not be added because - " . DB_error_msg($db);
			}
			if ($debug==1){
				echo "<BR>The SQL that caused the problem was:<BR>$sql";
			}
		} else {
			unset($_POST['Price']);
		}
	}
	echo "<BR>$msg";

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM Prices WHERE StockID = '". $Item ."' AND TypeAbbrev='". $_GET['TypeAbbrev'] ."' AND CurrAbrev ='". $_GET['CurrAbrev'] ."' AND DebtorNo=''";
	$result = DB_query($sql,$db);
	echo "<BR>The selected price has been deleted ! <p>";

}

//Always do this stuff
if ($InputError ==0){
	$sql = "SELECT Currencies.Currency, SalesTypes.Sales_Type, Prices.Price, Prices.StockID, Prices.TypeAbbrev, Prices.CurrAbrev FROM Prices, SalesTypes, Currencies WHERE Prices.CurrAbrev=Currencies.CurrAbrev AND Prices.TypeAbbrev = SalesTypes.TypeAbbrev AND StockID='$Item' AND Prices.DebtorNo='' ORDER BY CurrAbrev, TypeAbbrev";

	$result = DB_query($sql,$db);

	echo "<CENTER><table>";
	echo "<tr><td class='tableheader'>Currency</td><td class='tableheader'>Sales Type</td><td class='tableheader'>Price</td></tr>\n";


	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		/*Only allow access to modify prices if securiy access 5 or more */
		if (in_array(5,$SecurityGroups[$_SESSION['AccessLevel']])) {
/*			currency      sales type             price		   path  SID    Stockid     TypeAbb	currabrev price    */
			printf("<td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td><td><a href='%s?%sItem=%s&TypeAbbrev=%s&CurrAbrev=%s&Price=%s&Edit=1'>Edit</td><td><a href='%s?%sItem=%s&TypeAbbrev=%s&CurrAbrev=%s&delete=yes'>DELETE</td></tr>", $myrow["Currency"], $myrow["Sales_Type"], $myrow["Price"], $_SERVER['PHP_SELF'], SID, $myrow["StockID"], $myrow["TypeAbbrev"], $myrow["CurrAbrev"], $myrow["Price"], $_SERVER['PHP_SELF'], SID, $myrow["StockID"], $myrow["TypeAbbrev"], $myrow["CurrAbrev"]);
		} else {
			printf("<td>%s</td><td>%s</td><td ALIGN=RIGHT>%0.2f</td></tr>", $myrow["Currency"], $myrow["Sales_Type"], $myrow["Price"]);

		}

	}
	//END WHILE LIST LOOP
	echo "</table></CENTER><p>";

	if (DB_num_rows($result) == 0) {
		echo "<BR>There are no prices set up for this part";
	}

	if ($_GET['Edit']==1){
		echo "<INPUT TYPE=HIDDEN NAME='OldTypeAbbrev' VALUE='" . $_GET['TypeAbbrev'] ."'>";
		echo "<INPUT TYPE=HIDDEN NAME='OldCurrAbrev' VALUE='" . $_GET['CurrAbrev'] . "'>";
		$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
		$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
		$_POST['Price'] = $_GET['Price'];
	}

	$SQL = "SELECT CurrAbrev, Currency FROM Currencies";
	$result = DB_query($SQL,$db);

	echo "<CENTER><TABLE><TR><TD>Currency:</TD><TD><SELECT name='CurrAbrev'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["CurrAbrev"]==$_POST['CurrAbrev']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["CurrAbrev"] . "'>" . $myrow["Currency"];
	} //end while loop

	DB_free_result($result);

	echo "</SELECT>	</TD></TR><TR><TD>Sales Type Price List:</TD><TD><SELECT name='TypeAbbrev'>";

	$SQL = "SELECT TypeAbbrev, Sales_Type FROM SalesTypes";
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow["TypeAbbrev"]==$_POST['TypeAbbrev']) {
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow["TypeAbbrev"] . "'>" . $myrow["Sales_Type"];

	} //end while loop

	DB_free_result($result);
	?>

	</SELECT>
	</TD></TR>

	<TR><TD>Price:</TD>
	<TD>
	<input type="Text" name="Price" SIZE=6 MAXLENGTH=6 value=<?php echo $_POST['Price'];?>>

	</TD></TR>

	</TABLE>

	<input type="Submit" name="submit" value="Enter/Amend Price">
	</CENTER>

<?php
 } 
 
echo "</form>";
include("includes/footer.inc"); 
?>
