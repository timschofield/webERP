<?php
/* $Revision: 1.4 $ */
include("includes/session.inc");

$title = _("Discount Categories Maintenance");

$PageSecurity = 11;

include("includes/header.inc");

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$result = DB_query("SELECT StockID FROM StockMaster WHERE MBflag <>'K' AND MBflag<>'D' AND StockID='" . $_POST['StockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		$InputError = 1;
		echo "<BR>". _("The stock item entered must be set up as a stock item as either a manufactured, purchased or assembly item.");
	}

	if ($InputError !=1) {

		$sql = "UPDATE StockMaster SET DiscountCategory='" . $_POST['DiscountCategory'] . "' WHERE StockID='" . $_POST['StockID'] . "'";

		$result = DB_query($sql,$db, "The discount category " . $_POST['DiscountCategory'] . " record for " . $_POST['StockID'] . " could not be updated because");

		echo "<BR>". _("The stock master has been updated with this discount category.");
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif ($_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="UPDATE StockMaster SET DiscountCategory='' WHERE StockID='" . $_GET['StockID'] ."'";
	$result = DB_query($sql,$db);
	echo "<BR>". _("The stock master record has been updated to no discount category!") ." <p>";
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

$sql = "SELECT DISTINCT DiscountCategory FROM StockMaster WHERE DiscountCategory <>''";

$result = DB_query($sql, $db);

echo "<CENTER>". _("Discount Category Code") .": ";

echo "<SELECT NAME='DiscCat'>";

while ($myrow = DB_fetch_array($result)){
	if ($myrow['DiscountCategory']==$_POST['DiscCat']){
		echo "<OPTION SELECTED VALUE='" . $myrow['DiscountCategory'] . "'>" . $myrow['DiscountCategory'];
	} else {
		echo "<OPTION VALUE='" . $myrow['DiscountCategory'] . "'>" . $myrow['DiscountCategory'];
	}
	echo "</OPTION>";
}

echo "</SELECT>";

if (! isset($_POST['DiscCat'])){ /*set DiscCat to something to show results for first cat defined */

	if (DB_num_rows($result)>0){
		DB_data_seek($result,0);
		$myrow = DB_fetch_array($result);
		$_POST['DiscCat'] = $myrow['DiscountCategory'];
	} else {
		$_POST['DiscCat']='0';
	}
}

if ($_POST['DiscCat']!='0'){

	$sql = "SELECT StockMaster.StockID, StockMaster.Description, DiscountCategory FROM StockMaster WHERE DiscountCategory='" . $_POST['DiscCat'] . "' ORDER BY StockMaster.StockID";
	$result = DB_query($sql,$db);

	echo "<table border=1>\n";
	echo "<tr>
		<td class='tableheader'>". _("Discount Category") ."</td><td class='tableheader'>". _("Item") ."</td>\n";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
		$DeleteURL = $_SERVER['PHP_SELF'] . "?" . SID . "Delete=yes&StockID=" . $myrow['StockID'] . "&DiscountCategory=" . $myrow['DiscountCategory'];

		printf("<td>%s</td><td>%s - %s</td><td><a href='%s'>". _("Delete") ."</td></tr>", $myrow['DiscountCategory'], $myrow['StockID'], $myrow['Description'], $DeleteURL);

	}

	echo "</TABLE>";

} else { /* $_POST['DiscCat'] ==0 */

	echo "</CENTER><BR>". _("There are currently no discount categories defined. Enter a two character abbreviation for the discount category and the stock code to which this category will apply to. Discount rules can then be applied to this discount category.");
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";


echo "<CENTER><TABLE><TR><TD>". _("Discount Category Code") .":</TD><TD>";

echo "<INPUT TYPE='Text' NAME='DiscountCategory' MAXLENGTH=2 SIZE=2 VALUE='" . $_POST['DiscCat'] . "'></TD></TR>";

echo "<TR><TD>". _("Stock Code") .":</TD><TD><input type='Text' name='StockID' SIZE=20 MAXLENGTH=20></TD></TR>";

echo "</TABLE>";

echo "<CENTER><input type='Submit' name='submit' value='". _("Enter Information") ."'></CENTER>";

echo "</FORM>";

include("includes/footer.inc");
?>

