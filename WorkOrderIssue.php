<?php
$title = "Issue Stock Items to Work Order";

$PageSecurity = 10;

$path_to_root="..";
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['OrderNumber']) AND $_GET['OrderNumber']!=""){
	$_POST['OrderNumber'] = $_GET['OrderNumber'];
}

if ($_POST['OrderNumber'] !="") {
	$_POST['WORef'] = $_POST['OrderNumber'];
} else {
}

function clearData()
{
}

$input_error = false;

if ($_POST['SetQuantity'] OR $_POST['Process']) {
	if (!is_numeric($_POST['IssueQuantity'])){
		echo ("<BR>The quantity to issue entered must be numeric.");
		$input_error = true;
		$_POST['IssueQuantity'] = 0;
	} elseif ($_POST['IssueQuantity']<=0){
		echo ("<BR>The quantity to issue entered must be a positive number greater than zero.");
		$input_error = true;
		$_POST['IssueQuantity'] = 0;
	}
}

if ($_POST['Process'] AND ($input_error == false)) {

	$BOMResult = getWORequirements($_POST['WORef']);

	$sql = "Begin";
	$result = DB_query($sql,$db);

	// insert all the woissues
	$totalCost = 0;
	while ($myrow=DB_fetch_array($BOMResult)) {

		$sql = "INSERT INTO WOIssues (IssueNo, WORef, StockID, IssueType, WorkCentre, QtyIssued, StdCost)
				VALUES ('" . '1' . "', '" .
					$_POST['WORef'] . "', '"	.
					$myrow["StockID"] . "', '"	.
					$myrow["ResourceType"] . "', '"	.
					$myrow["WrkCentre"] . "', '"	.
					($myrow["UnitsReq"]* $_POST['IssueQuantity']) . "', '"	.
					$myrow["StdCost"] . "')";
		$result = DB_query($sql,$db);
    	
    	if (DB_error_no($db) !=0) {
    		displayError("The work order issues could not be added", $sql);
			$SQL = "rollback";
			$Result = DB_query($SQL,$db);    		
    		exit;
    	} 		
    	
    	$totalCost += ($myrow["UnitsReq"]* $_POST['IssueQuantity'] * $myrow["StdCost"]); 
		
	}//end of while

	// update the accumulated cost for the work order
	$_POST['AccumValueIssued'] = $_POST['AccumValueIssued'] + $totalCost;
	
	$sql = "UPDATE WorksOrders SET AccumValueIssued='" . $_POST['AccumValueIssued'] . 
			"' WHERE WORef = '" . $_POST['WORef'] . "'";	
	$result = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		displayError("The work order could not be updated with the accumulated value issued", $sql);
		$SQL = "rollback";
		$Result = DB_query($SQL,$db);		
		exit;
	} 		
	
	$sql="Commit";
	$result = DB_query($sql,$db);		

	echo "<CENTER>" . _("The work order issues were successfully added.");
	echo "<BR>";
	Hyperlink_NoParams("OutstandingWorkOrders.php", _("Select another Work Order to Issue"));
	echo "<BR><BR>";
	exit;	

} else if ($_POST['Update']) {
} 

$sql="SELECT * FROM WorksOrders WHERE WorksOrders.WORef='" . $_POST['WORef'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if (strlen($myrow[0])==0) {
	echo _("The order number sent is not valid.");
	exit;
}

$_POST['AccumValueIssued'] = $myrow["AccumValueIssued"];

displayHeading(_("Work Order Ref.") . " " . $_POST['WORef']);

echo "<CENTER><table $TableStyle>";
echo "<tr><td class='tableheader'>" . _("Manufactured Item") . "</td><td>" . $myrow["StockID"] . "</td>";
echo "<td class='tableheader'>" . _("Work Centre") . "</td><td>" . $myrow["LocCode"] . "</td></tr>";
echo "<td class='tableheader'>" . _("Quantity Required") . "</td><td>" . $myrow["UnitsReqd"] . "</td>";
echo "<td class='tableheader'>" . _("Standard Cost per Unit") . "</td><td>" . $myrow["StdCost"] . "</td></tr>";
echo "<tr><td class='tableheader'>" . _("Required By Date") . "</td><td>" . ConvertSQLDate($myrow["RequiredBy"]) . "</td>";
echo "<td class='tableheader'>" . _("Released Date") . "</td><td>" . ConvertSQLDate($myrow["ReleasedDate"]) . "</td></tr>";
echo "<tr><td class='tableheader'>" . _("Accumulated Value Issued") . "</td><td>" . $myrow["AccumValueIssued"] . "</td>";
echo "<td class='tableheader'>" . _("Accumulated Value Transferred") . "</td><td>" . $myrow["AccumValueTrfd"] . "</td></tr>";
echo "<td class='tableheader'>" . _("Units Transferred to Finished Goods") . "</td><td>" . '0' . "</td></tr>";
echo "</TABLE>";

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

echo "<INPUT TYPE=HIDDEN NAME=WORef VALUE=" .$_POST['WORef'] . ">";
echo "<INPUT TYPE=HIDDEN NAME=AccumValueIssued VALUE=" .$_POST['AccumValueIssued'] . ">";

if ($_POST['IssueQuantity']=='') {
	$_POST['IssueQuantity'] = $myrow["UnitsReqd"];
}

echo "<table>";
TextInput_TableRowWithInputSubmit(_("Quantity to Issue :"), 'IssueQuantity', $_POST['IssueQuantity'], 12, 12, 'SetQuantity', _("Update"));
echo "</table><BR>";

displayWORequirements($_POST['WORef'], $_POST['IssueQuantity']);

echo "<BR>";
//echo "<TABLE width=20%><tr><td>";
//Input_Submit('UpdateData', _("Update"));
//echo "</td><td>";
//Input_Submit('Cancel', _("Cancel This Issue"));
//echo "</td><td>";
Input_Submit('Process', _("Process This Issue"));
//echo "</td></table>";

echo "</FORM>";

include("includes/footer.inc");

?>
