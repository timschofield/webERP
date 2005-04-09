<?php
/* $Revision: 1.6 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Stock Category Maintenance');

include('includes/header.inc');

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['CategoryID'] = strtoupper($_POST['CategoryID']);

	if (strlen($_POST['CategoryID']) > 6) {
		$InputError = 1;
		prnMsg(_('The Inventory Category code must be six characters or less long'),'error');
	} elseif (strlen($_POST['CategoryID'])==0) {
		$InputError = 1;
		prnMsg(_('The Inventory category code must be at least 1 character but less than six characters long'),'error');
	} elseif (strlen($_POST['CategoryDescription']) >20) {
		$InputError = 1;
		prnMsg(_('The Sales category description must be twenty characters or less long'),'error');
	} elseif ($_POST['StockType'] !='D' AND $_POST['StockType'] !='L' AND $_POST['StockType'] !='F' AND $_POST['StockType'] !='M') {
		$InputError = 1;
		prnMsg(_('The stock type selected must be one of') . ' "D" - ' . _('Dummy item') . ', "L" - ' . _('Labour stock item') . ', "F" - ' . _('Finished product') . ' ' . _('or') . ' "M" - ' . _('Raw Materials'),'error');
	}

	if ($SelectedCategory AND $InputError !=1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE stockcategory SET stocktype = '" . $_POST['StockType'] . "',
                                     categorydescription = '" . DB_escape_string($_POST['CategoryDescription']) . "',
                                     stockact = " . $_POST['StockAct'] . ",
                                     adjglact = " . $_POST['AdjGLAct'] . ",
                                     purchpricevaract = " . $_POST['PurchPriceVarAct'] . ",
                                     materialuseagevarac = " . $_POST['MaterialUseageVarAc'] . ",
                                     wipact = " . $_POST['WIPAct'] . "
                                     WHERE
                                     categoryid = '$SelectedCategory'";
		$msg = _('The stock category record has been updated');
	} elseif ($InputError !=1) {

	/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$sql = "INSERT INTO stockcategory (categoryid,
                                       stocktype,
                                       categorydescription,
                                       stockact,
                                       adjglact,
                                       purchpricevaract,
                                       materialuseagevarac,
                                       wipact)
                                       VALUES (
                                       '" . DB_escape_string($_POST['CategoryID']) . "',
                                       '" . $_POST['StockType'] . "',
                                       '" . DB_escape_string($_POST['CategoryDescription']) . "',
                                       " . $_POST['StockAct'] . ",
                                       " . $_POST['AdjGLAct'] . ",
                                       " . $_POST['PurchPriceVarAct'] . ",
                                       " . $_POST['MaterialUseageVarAc'] . ",
                                       " . $_POST['WIPAct'] . ")";
		$msg = _('A new stock category record has been added');
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	unset ($SelectedCategory);
	unset($_POST['CategoryID']);
	unset($_POST['StockType']);
	unset($_POST['CategoryDescription']);
	unset($_POST['StockAct']);
	unset($_POST['AdjGLAct']);
	unset($_POST['PurchPriceVarAct']);
	unset($_POST['MaterialUseageVarAc']);
	unset($_POST['WIPAct']);
	prnMsg($msg,'success');

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'

	$sql= "SELECT COUNT(*) FROM stockmaster WHERE stockmaster.categoryid='$SelectedCategory'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this stock category because stock items have been created using this stock category') .
			'<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('items referring to this stock category code'),'warn');

	} else {
		$sql = "SELECT COUNT(*) FROM salesglpostings WHERE stkcat='$SelectedCategory'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg(_('Cannot delete this stock category because it is used by the sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Sales GL Interface set up using this stock category first'),'warn');
		} else {
			$sql = "SELECT COUNT(*) FROM cogsglpostings WHERE stkcat='$SelectedCategory'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg(_('Cannot delete this stock category because it is used by the cost of sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Cost of Sales GL Interface set up using this stock category first'),'warn');
			} else {
				$sql="DELETE FROM stockcategory WHERE categoryid='$SelectedCategory'";
				$result = DB_query($sql,$db);
				prnMsg(_('The stock category') . ' ' . $SelectedCategory . ' ' . _('has been deleted') . ' !','success');
				unset ($SelectedCategory);
			}
		}
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT * FROM stockcategory";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo '<tr><td class="tableheader">' . _('Cat Code') . '</td>
            <td class="tableheader">' . _('Description') . '</td>
            <td class="tableheader">' . _('Type') . '</td>
            <td class="tableheader">' . _('Stock GL') . '</td>
            <td class="tableheader">' . _('Adjts GL') . '</td>
            <td class="tableheader">' . _('Price Var GL') . '</td>
            <td class="tableheader">' . _('Usage Var GL') . '</td>
            <td class="tableheader">' . _('WIP GL') . "</td></tr>\n";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
		printf("<td>%s</td>
            		<td>%s</td>
            		<td>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td><a href=\"%sSelectedCategory=%s\">" . _('Edit') . "</td>
            		<td><a href=\"%sSelectedCategory=%s&delete=yes\">" . _('Delete') . "</td>
            		</tr>",
            		$myrow[0],
            		$myrow[1],
            		$myrow[2],
            		$myrow[3],
            		$myrow[4],
            		$myrow[5],
            		$myrow[6],
            		$myrow[7],
            		$_SERVER['PHP_SELF'] . '?' . SID,
            		$myrow[0],
            		$_SERVER['PHP_SELF'] . '?' . SID,
            		$myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table></CENTER>';
}

//end of ifs and buts!

?>

<p>
<?php
if ($SelectedCategory) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID;?>"><?php echo _('Show All Stock Categories'); ?></a></Center>
<?php } ?>

<P>

<?php

if (! isset($_GET['delete'])) {

	echo '<FORM METHOD="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (isset($SelectedCategory)) {
		//editing an existing stock category

		$sql = "SELECT categoryid,
                   	stocktype,
                   	categorydescription,
                   	stockact,
                   	adjglact,
                   	purchpricevaract,
                   	materialuseagevarac,
                   	wipact
                   FROM stockcategory
                   WHERE categoryid='" . DB_escape_string($SelectedCategory) . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['CategoryID'] = $myrow['categoryid'];
		$_POST['StockType']  = $myrow['stocktype'];
		$_POST['CategoryDescription']  = $myrow['categorydescription'];
		$_POST['StockAct']  = $myrow['stockact'];
		$_POST['AdjGLAct']  = $myrow['adjglact'];
		$_POST['PurchPriceVarAct']  = $myrow['purchpricevaract'];
		$_POST['MaterialUseageVarAc']  = $myrow['materialuseagevarac'];
		$_POST['WIPAct']  = $myrow['wipact'];

		echo '<INPUT TYPE=HIDDEN NAME="SelectedCategory" VALUE="' . $SelectedCategory . '">';
		echo '<INPUT TYPE=HIDDEN NAME="CategoryID" VALUE="' . $_POST['CategoryID'] . '">';
		echo '<CENTER><TABLE><TR><TD>' . _('Category Code') . ':</TD><TD>"' . $_POST['CategoryID'] . '"</TD></TR>';

	} else { //end of if $SelectedCategory only do the else when a new record is being entered

		echo '<CENTER><TABLE><TR><TD>' . _('Category Code') . ':</TD>
                             <TD><input type="Text" name="CategoryID" SIZE=7 MAXLENGTH=6 value="' . $_POST['CategoryID'] . '"></TD></TR>';
	}

	//SQL to poulate account selection boxes
	$sql = "SELECT accountcode,
                 accountname
                 FROM chartmaster,
                      accountgroups
                 WHERE chartmaster.group_=accountgroups.groupname and
                       accountgroups.pandl=0
                 ORDER BY accountcode";

	$result = DB_query($sql,$db);

	echo '<TR><TD>' . _('Category Description') . ':</TD>
            <TD><input type="Text" name="CategoryDescription" SIZE=22 MAXLENGTH=20 value="' . $_POST['CategoryDescription'] . '"></TD></TR>';

	echo '<TR><TD>' . _('Stock Type') . ':</TD>
            <TD><SELECT name="StockType">';
		if ($_POST['StockType']=='F') {
			echo '<OPTION SELECTED VALUE="F">' . _('Finished Goods');
		} else {
			echo '<OPTION VALUE="F">' . _('Finished Goods');
		}
		if ($_POST['StockType']=='M') {
			echo '<OPTION SELECTED VALUE="M">' . _('Raw Materials');
		} else {
			echo '<OPTION VALUE="M">' . _('Raw Materials');
		}
		if ($_POST['StockType']=='D') {
			echo '<OPTION SELECTED VALUE="D">' . _('Dummy Item - (No Movements)');
		} else {
			echo '<OPTION VALUE="D">' . _('Dummy Item - (No Movements)');
		}
		if ($_POST['StockType']=='L') {
			echo '<OPTION SELECTED VALUE="L">' . _('Labour');
		} else {
			echo '<OPTION VALUE="L">' . _('Labour');
		}

	echo '</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Stock GL Code') . ':</TD><TD><SELECT name="StockAct">';

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['StockAct']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';
	} //end while loop
	DB_data_seek($result,0);
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('WIP GL Code') . ':</TD><TD><SELECT name="WIPAct">';

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['WIPAct']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	echo '</SELECT></TD></TR>';

	$sql = "SELECT accountcode,
                 accountname
                 FROM chartmaster,
                      accountgroups
                 WHERE chartmaster.group_=accountgroups.groupname and
                       accountgroups.pandl!=0
                 ORDER BY accountcode";

	$result1 = DB_query($sql,$db);

	echo '<TR><TD>' . _('Stock Adjustments GL Code') . ':</TD>
            <TD><SELECT name="AdjGLAct">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['AdjGLAct']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_data_seek($result1,0);
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Price Variance GL Code') . ':</TD>
            <TD><SELECT name="PurchPriceVarAct">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['PurchPriceVarAct']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_data_seek($result1,0);

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Usage Variance GL Code') . ':</TD><TD><SELECT name="MaterialUseageVarAc">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['MaterialUseageVarAc']) {
			echo '<OPTION SELECTED VALUE=';
		} else {
			echo '<OPTION VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_free_result($result1);
	echo '</SELECT></TD></TR></TABLE>';

	echo '<CENTER><input type="Submit" name="submit" value="' . _('Enter Information') . '">';

	echo '</FORM>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>