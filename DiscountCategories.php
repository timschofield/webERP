<?php
/* $Revision: 1.2 $ */
$title = "Discount Categories Maintenance";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");

if ($_POST['submit']=="Enter Information") {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$result = DB_query("SELECT StockID FROM StockMaster WHERE MBflag <>'K' AND MBflag<>'D' AND StockID='" . $_POST['StockID'] . "'",$db);
	if (DB_num_rows($result)==0){
		$InputError = 1;
		echo "<BR>The stock item entered must be set up as a stock item as either a manufactured, purchased or assembly item.";
	}

	if ($InputError !=1) {

		$sql = "UPDATE StockMaster SET DiscountCategory='" . $_POST['DiscountCategory'] . "' WHERE StockID='" . $_POST['StockID'] . "')";

		$result = DB_query($sql,$db);
		echo "<BR>The stock master has been updated with this discount category.";
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif ($_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="UPDATE StockMaster SET DiscountCategory='' WHERE StockID='" . $_GET['StockID'] ."'";
	$result = DB_query($sql,$db);
	echo "<BR>The stock master record has been updated to no discount category! <p>";
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

$sql = "SELECT DISTINCT DiscountCategory FROM StockMaster WHERE DiscountCategory <>''";
$result = DB_query($sql, $db);

echo "<CENTER>Discount Category Code: ";

echo "<SELECT NAME='DiscCat'>";

while ($myrow = DB_fetch_array($result)){
	if ($myrow['DiscountCategory']==$_POST['DiscCat']){
		echo "<OPTION SELECTED VALUE='" . $myrow['DiscountCategory'] . "'>" . $myrow['DiscountCategory'];
	} else {
		echo "<OPTION VALUE='" . $myrow['DiscountCategory'] . "'>" . $myrow['DiscountCategory'];
	}
}

if (!isset($_POST['DiscCat'])){ /*set DiscCat to something to show results for first cat defined */
	DB_data_seek($result,0);
	$myrow = DB_fetch_array($result);
	$_POST['DiscCat'] = $myrow['DiscountCategory'];
}


echo "</SELECT>";


$sql = "SELECT StockMaster.StockID, StockMaster.Description, DiscountCategory FROM StockMaster WHERE DiscountCategory='" . $_POST['DiscCat'] . "' ORDER BY StockMaster.StockID";
$result = DB_query($sql,$db);

echo "<table border=1>\n";
echo "<tr><td class='tableheader'>Discount<BR>Category</td><td class='tableheader'>Item</td>\n";

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

	printf("<td>%s</td><td>%s - %s</td><td><a href='%s'>Delete</td></tr>", $myrow['DiscountCategory'], $myrow['StockID'], $myrow['Description'], $DeleteURL);

}

echo "</TABLE>";

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";


echo "<CENTER><TABLE><TR><TD>Discount Category Code:</TD><TD>";

echo "<INPUT TYPE='Text' NAME='DiscountCategory' MAXLENGTH=2 SIZE=2 VALUE='" . $_POST['DiscCat'] . "'></TD></TR>";

echo "<TR><TD>Stock Code:</TD><TD><input type='Text' name='StockID' SIZE=20 MAXLENGTH=20></TD></TR>";

echo "</TABLE>";

echo "<CENTER><input type='Submit' name='submit' value='Enter Information'></CENTER>";

echo "</FORM>";

include("includes/footer.inc");
?>

