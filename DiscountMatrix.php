<?php
$title = "Discount Matrix Maintenance";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");

if ($_POST['submit']=="Enter Information") {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (!is_numeric($_POST['QuantityBreak'])){
		echo "<BR>The quantity break must be entered as a positive number";
		$InputError =1;
	}

	if ($_POST['QuantityBreak']<=0){
		echo "<BR>The quantity of all items on an order (in the discount category " . $_POST['DiscountCategory'] . " at which the discount will apply is 0 or less than 0. Positive numbers are expected for this entry";
		$InputError =1;
	}
	if (!is_numeric($_POST['DiscountRate'])){
		echo "<BR>The discount rate must be entered as a positive number";
		$InputError =1;
	}
	if ($_POST['DiscountRate']<=0 OR $_POST['DiscountRate']>=70){
		echo "<BR>The discount rate applicable for this record is either less than 0 % or greater than 70% numbers between 1 and 69 are expected";
		$InputError =1;
	}

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if ($InputError !=1) {

		$sql = "INSERT INTO DiscountMatrix (SalesType, DiscountCategory, QuantityBreak, DiscountRate) VALUES('" . $_POST['SalesType'] . "', '" . $_POST['DiscountCategory'] . "', " . $_POST['QuantityBreak'] . ", " . ($_POST['DiscountRate']/100) . ")";

		$result = DB_query($sql,$db);
		echo "<BR>The discount matrix record has been added.";
		unset($_POST['DiscountCategory']);
		unset($_POST['SalesType']);
		unset($_POST['QuantityBreak']);
		unset($_POST['DiscountRate']);
	}
} elseif ($_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="Delete FROM DiscountMatrix WHERE DiscountCategory='" .$_GET['DiscountCategory'] . "' AND SalesType='" . $_GET['SalesType'] . "' AND QuantityBreak=" . $_GET['QuantityBreak'];

	$result = DB_query($sql,$db);
	echo "<BR>The discount matrix record has been deleted! <p>";
}

$sql = "SELECT Sales_Type, SalesType, DiscountCategory, QuantityBreak, DiscountRate FROM DiscountMatrix INNER JOIN SalesTypes On DiscountMatrix.SalesType=SalesTypes.TypeAbbrev ORDER BY SalesType, DiscountCategory, QuantityBreak";

$result = DB_query($sql,$db);

echo "<CENTER><table>";
echo "<tr><td class='tableheader'>Sales Type</td><td class='tableheader'>Discount<BR>Category</td><td class='tableheader'>Quantity<BR>Break</td><td class='tableheader'>Discount<BR>Rate %</td>";

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}
	$DeleteURL = $_SERVER['PHP_SELF'] . "?" . SID . "Delete=yes&SalesType=" . $myrow['SalesType'] . "&DiscountCategory=" . $myrow['DiscountCategory'] . "&QuantityBreak=" . $myrow['QuantityBreak'];

	printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href='%s'>Delete</td></tr>", $myrow['Sales_Type'], $myrow['DiscountCategory'],$myrow['QuantityBreak'],number_format($myrow['DiscountRate']*100,2) , $DeleteURL);

}

echo "</TABLE><HR>";

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";


echo "<TABLE>";

$sql = "SELECT TypeAbbrev, Sales_Type FROM SalesTypes";
$result = DB_query($sql, $db);

echo "<TR><TD>Customer Price List (Sales Type):</TD><TD>";

echo "<SELECT NAME='SalesType'>";

while ($myrow = DB_fetch_array($result)){
	if ($myrow['TypeAbbrev']==$_POST['SalesType']){
		echo "<OPTION SELECTED VALUE='" . $myrow['TypeAbbrev'] . "'>" . $myrow['Sales_Type'];
	} else {
		echo "<OPTION VALUE='" . $myrow['TypeAbbrev'] . "'>" . $myrow['Sales_Type'];
	}
}

echo "</SELECT>";


echo "<TR><TD>Discount Category Code:</TD><TD>";

echo "<INPUT TYPE='Text' NAME='DiscountCategory' MAXLENGTH=2 SIZE=2 VALUE='" . $_POST['DiscCat'] . "'></TD></TR>";

echo "<TR><TD>Quantity Break:</TD><TD><input type='Text' name='QuantityBreak' SIZE=10 MAXLENGTH=10></TD></TR>";

echo "<TR><TD>Discount Rate (%):</TD><TD><input type='Text' name='DiscountRate' SIZE=4 MAXLENGTH=4></TD></TR>";
echo "</TABLE>";

echo "<CENTER><input type='Submit' name='submit' value='Enter Information'></CENTER>";

echo "</FORM>";

include("includes/footer.inc");
?>

