<?php
/* $Revision: 1.5 $ */

$PageSecurity = 10;

$path_to_root="..";
include('includes/session.inc');

$title = _('Issue Stock Items to Work Order');

include('includes/header.inc');

if (isset($_GET['OrderNumber']) AND $_GET['OrderNumber']!=''){
	$_POST['OrderNumber'] = $_GET['OrderNumber'];
}

if ($_POST['OrderNumber'] !='') {
	$_POST['WORef'] = $_POST['OrderNumber'];
} else {
}

function clearData(){
}

$input_error = false;

if ($_POST['SetQuantity'] OR $_POST['Process']) {
	if (!is_numeric($_POST['IssueQuantity'])){
		echo '<BR>' . _('The quantity to issue entered must be numeric');
		$input_error = true;
		$_POST['IssueQuantity'] = 0;
	} elseif ($_POST['IssueQuantity']<=0){
		echo '<BR>' . _('The quantity to issue entered must be a positive number greater than zero');
		$input_error = true;
		$_POST['IssueQuantity'] = 0;
	}
}

if ($_POST['Process'] AND ($input_error == false)) {

	$BOMResult = getWORequirements($_POST['WORef']);

	$sql = 'Begin';
	$result = DB_query($sql,$db);

	// insert all the woissues
	$totalCost = 0;
	while ($myrow=DB_fetch_array($BOMResult)) {

		$sql = "INSERT INTO woissues (issueno, woref, stockid, issuetype, workcentre, qtyissued, stdcost)
				VALUES ('" . '1' . "', '" .
					$_POST['WORef'] . "', '"	.
					$myrow['stockid'] . "', '"	.
					$myrow['resourcetype'] . "', '"	.
					$myrow['wrkcentre'] . "', '"	.
					($myrow['unitsreq']* $_POST['IssueQuantity']) . "', '"	.
					$myrow['stdcost'] . "')";
		$result = DB_query($sql,$db);
    	
    	if (DB_error_no($db) !=0) {
    		displayError(_('The work order issues could not be added'), $sql);
			$SQL = 'rollback';
			$Result = DB_query($SQL,$db);    		
    		exit;
    	} 		
    	
    	$totalCost += ($myrow['unitsreq']* $_POST['IssueQuantity'] * $myrow['stdcost']); 
		
	}//end of while

	// update the accumulated cost for the work order
	$_POST['AccumValueIssued'] = $_POST['AccumValueIssued'] + $totalCost;
	
	$sql = "UPDATE worksorders SET accumvalueissued='" . $_POST['AccumValueIssued'] . 
			"' WHERE woref = '" . $_POST['WORef'] . "'";	
	$result = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		$ErrMsg = _('The work order could not be updated with the accumulated value issued');
		$SQL = 'rollback';
		$Result = DB_query($SQL,$db);		
		exit;
	} 		
	
	$sql='Commit';
	$result = DB_query($sql,$db);		

	echo '<CENTER>' . _('The work order issues were successfully added');
	echo '<BR>';
	Hyperlink_NoParams('OutstandingWorkOrders.php', _('Select another Work Order to Issue'));
	echo '<BR><BR>';
	exit;	

} else if ($_POST['Update']) {
} 

$sql="SELECT * FROM worksorders WHERE worksorders.woref='" . $_POST['WORef'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if (strlen($myrow[0])==0) {
	echo _('The order number sent is not valid');
	exit;
}

$_POST['AccumValueIssued'] = $myrow['accumvalueissued'];

displayHeading(_('Work Order Ref') . ' ' . $_POST['WORef']);

echo "<CENTER><table $TableStyle>";
echo "<tr><td class='tableheader'>" . _('Manufactured Item') . '</td><td>' . $myrow['stockid'] . '</td>';
echo "<td class='tableheader'>" . _('Work Centre') . '</td><td>' . $myrow['loccode'] . '</td></tr>';
echo "<td class='tableheader'>" . _('Quantity Required') . '</td><td>' . $myrow['unitsreqd'] . '</td>';
echo "<td class='tableheader'>" . _('Standard Cost per Unit') . '</td><td>' . $myrow['stdcost'] . '</td></tr>';
echo "<tr><td class='tableheader'>" . _('Required By Date') . '</td><td>' . ConvertSQLDate($myrow['requiredby']) . '</td>';
echo "<td class='tableheader'>" . _('Released Date') . '</td><td>' . ConvertSQLDate($myrow['releaseddate']) . '</td></tr>';
echo "<tr><td class='tableheader'>" . _('Accumulated Value Issued') . '</td><td>' . $myrow['accumvalueissued'] . '</td>';
echo "<td class='tableheader'>" . _('Accumulated Value Transferred') . '</td><td>' . $myrow['accumvaluetrfd'] . '</td></tr>';
echo "<td class='tableheader'>" . _('Units Transferred to Finished Goods') . '</td><td>' . '0' . '</td></tr>';
echo '</TABLE>';

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '>';

echo "<INPUT TYPE=HIDDEN NAME=WORef VALUE=" .$_POST['WORef'] . '>';
echo "<INPUT TYPE=HIDDEN NAME=AccumValueIssued VALUE=" .$_POST['AccumValueIssued'] . '>';

if ($_POST['IssueQuantity']=='') {
	$_POST['IssueQuantity'] = $myrow['unitsreqd'];
}

echo '<table>';
TextInput_TableRowWithInputSubmit(_('Quantity to Issue') . ' ' , 'IssueQuantity', $_POST['IssueQuantity'], 12, 12, 'SetQuantity', _('Update'));
echo '</table><BR>';

displayWORequirements($_POST['WORef'], $_POST['IssueQuantity']);

echo '<BR>';
//echo "<TABLE width=20%><tr><td>";
//Input_Submit('UpdateData', _("Update"));
//echo "</td><td>";
//Input_Submit('Cancel', _("Cancel This Issue"));
//echo "</td><td>";
Input_Submit('Process', _('Process This Issue'));
//echo "</td></table>";

echo '</FORM>';

include('includes/footer.inc');

?>
