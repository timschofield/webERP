<?php
/* $Revision: 1.5 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Work Order Entry');

include('includes/header.inc');

function clearData()
{
	global $EditingExisting;
	unset($_POST['WORef']);
	unset($_POST['Quantity']);
	unset($_POST['RequDate']);
	unset($_POST['ReleasedDate']);
	unset($_POST['StockLocation']);
	unset($_POST['Cost']);
	unset($_POST['Released']);
	unset($_POST['Closed']);
	unset($_POST['StockID']);
	unset($_GET['ModifyOrderNumber']);
	unset($_POST['ModifyOrderNumber']);
	unset($_POST['submit']);
	$EditingExisting = false;
}

if (isset($_GET['ModifyOrderNumber']) AND $_GET['ModifyOrderNumber']!=''){
	$_POST['ModifyOrderNumber'] = $_GET['ModifyOrderNumber'];
}

if ($_POST['ModifyOrderNumber'] !='') {
	$EditingExisting = true;
	$_POST['WORef'] = $_POST['ModifyOrderNumber'];
} else {
	$EditingExisting = false;
}

if ($_POST['submit']) {

	$input_error = false;

	if (strlen($_POST['WORef']) > 20 OR strlen($_POST['WORef'])==0) {
		$InputError = 1;
		echo '<BR>' . _('The work order reference must be entered and be less than 20 characters long');
		$input_error = true;
	} elseif (!is_numeric($_POST['Quantity'])){
		echo '<BR>' . _('The quantity entered must be numeric');
		$input_error = true;
	} elseif ($_POST['Quantity']<=0){
		echo '<BR>' . _('The quantity entered must be a positive number greater than zero');
		$input_error = true;
	} elseif (!Is_Date($_POST['RequDate'])){
		echo '<BR>' . _('The date entered is in an invalid format');
		$input_error = true;
	}

	// check for unique reference - better to default to new number - auto_increment column ??
	if (($EditingExisting == false) AND ($input_error == false)) {
		$sql= "SELECT count(*) FROM worksorders WHERE woref='" . $_POST['WORef'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
    		echo '<BR>' . _('The entered work order reference is already in use') . '. ' . _('Please re-enter');
    		$input_error = true;
		}
	}

	if ($input_error == false) {
		$SQL_ReqDate = FormatDateForSQL($_POST['RequDate']);
		$SQL_ReleasedDate= FormatDateForSQL($_POST['ReleasedDate']);

		if ($EditingExisting == false) {

    		$sql = "INSERT INTO worksorders (woref, loccode, unitsreqd, stockid, stdcost, requiredby, closed, released, releaseddate)
    			VALUES ('" . $_POST['WORef'] . "', '" .
    			$_POST['StockLocation']	. "', '" .
    			$_POST['Quantity'] . "', '" .
    			$_POST['StockID'] . "', '"	.
    			$_POST['Cost'] .	"', '" .
    			$SQL_ReqDate .	"', '" .
    			'0' . "', '" .
    			'0' . "', '" .
    			$SQL_ReleasedDate. "')";
    			$msg = _('The work order been added');
		} else {
			$sql = "UPDATE worksorders SET loccode='" . $_POST['StockLocation'] .
			"', unitsreqd='" . $_POST['Quantity'] .
			"', stockid='" . $_POST['StockID'] .
			"', stdcost='" . $_POST['Cost'] .
			"', requiredby='" . $SQL_ReqDate .
			"' WHERE woref = '" . $_POST['WORef'] . "'";

			$msg = _('The work order has been updated');
		}

    	//run the SQL from either of the above possibilites
    	$result = DB_query($sql,$db);
    	if (DB_error_no($db) !=0) {
    		echo '<BR>' . _('The work order could not be added/updated');
		if ($debug==1){
			echo '<BR>' . _('The SQL statement that failed was') . "<BR>$sql";
		}
    	} else {
    		echo "<CENTER><BR>$msg<BR>";
   			clearData();
			echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>" . _('Enter a new work order') . "</A>";
			echo "<BR><A HREF='" . $rootpath . "/OutstandingWorkOrders.php?" . SID . "'>" . _('Select an existing outstanding work order') . "</A>";
			echo "<BR><BR>";
			exit;
    	}
	}
} elseif (isset($_POST['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete=false;

	// can't delete it there are open work issues
	if (hasWOIssues($_POST['WORef'])){
		echo '<BR>' . _('This work order cannot be deleted because it has work issues related to it');
		$CancelDelete=true;
	}
	// can't delete if it's closed

	if ($CancelDelete==false) { //ie not cancelled the delete as a result of above tests
		// delete the work order requirements
    		$sql="DELETE FROM worequirements WHERE woref='" . $_POST['WORef'] . "'";
    		$result = DB_query($sql,$db);
    		if (DB_error_no($db) !=0) {
    			echo '<BR>' . _('The work order requirements could not be deleted');
			if ($debug==1){
				echo '<BR>' . _('The sql that failed was') . " <BR>$sql";
			}
    		}

		// delete the actual work order
		$sql="DELETE FROM worksorders WHERE woref='" . $_POST['WORef'] . "'";
		$result = DB_query($sql,$db);
    	if (DB_error_no($db) !=0) {
    		echo '<BR>' . _('The work order could not be deleted');
		if ($debug==1){
			echo '<BR>' . _('The SQL that failed was') . "<BR>$sql";
		}
    	} else {
			echo '<CENTER><BR>' . _('The work order has been deleted') . '<BR><BR>';
			echo "<A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>" . _('Enter a new work order') . "</A>";
			echo "<A HREF='" . $rootpath . "OutstandingWorkOrders.php?" . SID . "'>" . _('Select an existing outstanding work order') . "</A>";
			echo '<BR><BR>';
    	}
		exit;
	}
}elseif (isset($_POST['release'])) {

	$input_error = false;

	// make sure has not been released before
	if (isReleased($_POST['WORef'])) {
		displayError(_('This Work Order has already been released'));
		$input_error = true;
	}

	// make sure stock item has components - only if it's just been released
	if (!hasBOM($_POST['StockID'])) {
		displayError(_('This Work Order cannot be released') . '. ' . _('The selected item to manufacture does not have a BOM'));
		$input_error = true;
	}

	if ($input_error == false) {

    	$sql = 'Begin';
    	$result = DB_query($sql,$db);

		$_POST['ReleasedDate'] = Date($_SESSION['DefaultDateFormat']);
		$SQL_ReleasedDate= FormatDateForSQL($_POST['ReleasedDate']);

		// update the released date in the work order
    	$sql = "UPDATE worksorders SET releaseddate='" . $SQL_ReleasedDate.
    			"', released='" . '1' .
    			"' WHERE woref = '" . $_POST['WORef'] . "'";
    	$result = DB_query($sql,$db);
    	if (DB_error_no($db) !=0) {
    		echo '<BR>' . _('The work order could not be updated with the accumulated value issued');
		if ($debug==1){
			echo '<BR>' . _('The SQL that failed was') . "<BR>$sql";
		}
    		$SQL = 'rollback';
    		$Result = DB_query($SQL,$db);
    		exit;
    	}

		// create Work Order Requirements based on the BOM
		$BOMResult = getBOM($_POST['StockID']);

		$TotalCost = 0;

		while ($myrow=DB_fetch_array($BOMResult)) {

			$sql = "INSERT INTO worequirements (woref, stockid, wrkcentre, unitsreq, stdcost, resourcetype)
				VALUES ('" . $_POST['WORef'] . "', '" .
				$myrow['component'] . "', '"	.
				$myrow['workcentreadded'] . "', '"	.
				$myrow['quantity'] . "', '"	.
				$myrow['standardcost'] . "', '"	.
				$myrow['resourcetype'] . "')";

		$result = DB_query($sql,$db);
        	if (DB_error_no($db) !=0) {
        		echo '<BR>' . _('The work order requirements could not be added');
			if ($debug==1){
				echo '<BR>' . _('The SQL that failed was') . "<BR>$sql";
			}

        		$SQL = 'rollback';
        		$Result = DB_query($SQL,$db);
        		exit;
        	}

			$TotalCost += $myrow['componentcost'];
		}

		$_POST['Cost'] = $TotalCost;

    	$sql='Commit';
    	$result = DB_query($sql,$db);
    	if (DB_error_no($db) !=0) {
    		echo '<BR>' . _('This work order cannot be released');
		if ($debug==1){
			echo '<BR>' . _('The SQL that failed was') . "<BR>$sql";
		}

    		exit;
    	} else {
    		echo '<BR>' . _('This work order has been successfully released to manufacturing');
    		unset($_POST['release']);
    	}

	}
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

echo '<CENTER><TABLE>';

echo "<INPUT TYPE=HIDDEN NAME=ReleasedDate VALUE=" . $_POST['ReleasedDate'] . '>';

if (!isset($_POST['FromStockLocation'])){
	if (isset($_SESSION['UserStockLocation'])){
		$_POST['FromStockLocation']=$_SESSION['UserStockLocation'];
	}
}

if ($EditingExisting == false) {
	echo '<TR><TD>' . _('Work Order Reference') . ":</TD><TD><INPUT TYPE=TEXT NAME='WORef' VALUE=" . $_POST['WORef'] . ' SIZE=20 MAXLENTH=20></TD</TR>';

} else {

	$sql="SELECT * FROM worksorders WHERE worksorders.woref='" . $_POST['WORef'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);
	if (strlen($myrow[0])==0) {
		echo '<BR>' . _('The order number entered is not valid');
		exit;
	}

	$_POST['Quantity'] = $myrow['unitsreqd'];
	$_POST['StockLocation'] = $myrow['loccode'];
	$_POST['Cost'] = $myrow['stdcost'];
	$_POST['Released'] = $myrow['released'];
	$_POST['Closed'] = $myrow['closed'];
	$_POST['StockID'] = $myrow['stockid'];
	$_POST['RequDate'] = ConvertSQLDate($myrow['requiredby']);
	$_POST['ReleasedDate'] = ConvertSQLDate($myrow['releaseddate']);
	$_POST['AlreadyReleased'] = $_POST['Released'];

	echo "<INPUT TYPE=HIDDEN NAME='WORef' VALUE=" .$_POST['WORef'] . '>';
	echo "<INPUT TYPE=HIDDEN NAME='ModifyOrderNumber' VALUE=" .$_POST['ModifyOrderNumber'] . '>';
	echo '<TR><TD>' . _('Work Order Reference') . ':</TD><TD>' . $_POST['WORef'] . '</TD></TR>';
}

if ($_POST['AlreadyReleased']==true) {
	echo "<INPUT TYPE=HIDDEN NAME=StockID VALUE=" .$_POST['StockID'] . '>';
	echo "<INPUT TYPE=HIDDEN NAME=StockLocation VALUE=" .$_POST['StockLocation'] . '>';
	echo '<TR><TD>' . _('Work Centre') . ':</TD><TD>' . $_POST['StockLocation'] . '</TD></TR>';
	echo '<TR><TD>' . _('Item to Manufacture') . ':</TD><TD>' . $_POST['StockID'] . '</TD></TR>';
} else {
	workCenterList_TableRow(_('Work Centre') . ' ' , $db, 'StockLocation', $_POST['StockLocation']);
	stockBOMItemsList_TableRow(_('Item to Manufacture') . ' ' , $db, 'StockID', $_POST['StockID']);
}

if (!isset($_POST['Quantity']) OR $_POST['Quantity']=='' OR $_POST['Quantity']==0) {
	$_POST['Quantity'] = 1;
}
echo '<TR><TD>' ._('Quantity Required') . ":</TD><TD><INPUT TYPE=TEXT NAME='Quantity' VALUE=" . $_POST['Quantity'] . ' SIZE=12 MAXLENGTH=12></TD</TR>';

if (!$_POST['RequDate'] OR !IsDate($_POST['RequDate'])){
   $_POST['RequDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<TR><TD>' . _('Date Required By') . " ($_SESSION['DefaultDateFormat']):</TD><TD><INPUT TYPE=TEXT NAME='RequDate' VALUE=" . $_POST['RequDate'] . ' SIZE=12 MAXLENGTH=12></TD</TR>';

echo "<INPUT TYPE=HIDDEN NAME='Released' VALUE=" .$_POST['Released'] . '>';
echo "<INPUT TYPE=HIDDEN NAME='ReleasedDate' VALUE=" .$_POST['ReleasedDate'] . '>';

if ($_POST['AlreadyReleased']==true) {
	echo '<TR><TD>' . _('Released On') . ':</TD><TD>' . $_POST['ReleasedDate'] . '</TD></TR>';
}

echo '</TABLE>';

echo '<CENTER>';
echo "<INPUT TYPE=SUBMIT NAME='submit' VALUE='Add/Update'>";

if ($EditingExisting == true) {
	echo '<BR><BR><TABLE><TR>';

	if ($_POST['AlreadyReleased']==false) {
		echo "<TD><INPUT TYPE=SUBMIT NAME='release' VALUE='" . _('Release This Work Order') . "'></TD>";
	}

	if ($_POST['Released']) {
		echo "<TD><INPUT TYPE=SUBMIT NAME='close' VALUE='" . _('Close This Work Order') . "'></TD>";
	}

	echo "<TD><INPUT TYPE=SUBMIT NAME='delete' VALUE='" . _('Delete This Work Order') . "'></TD>";

	echo '</TR></TABLE>';
}

if (($EditingExisting == true)){
	// display the WO requirements (ie the BOM)
	echo '<BR>';
	if ($_POST['AlreadyReleased']==false) {
	displayHeading2(_('BOM for item') . ': ' . $_POST['StockID']);
	displayBOM($_POST['StockID']);
	} else {
		echo '<table><tr><td>';
		displayHeading2(_('Work Order Requirements'));
		displayWORequirements($_POST['WORef'], $_POST['Quantity']);
		echo '</td><td>';
		displayHeading2(_('Issues against this Work Order'));
		displayWOIssues($_POST['WORef']);
		echo '</tr></table>';
	}
}

echo '</FORM>';

include('includes/footer.inc');

?>
