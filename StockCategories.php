<?php
/* $Revision: 1.10 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Stock Category Maintenance');

include('includes/header.inc');

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['DeleteProperty'])){

	$ErrMsg = _('Could not delete the property') . ' ' . $_GET['DeleteProperty'] . ' ' . _('because');
	$sql = "DELETE FROM stockitemproperties WHERE stkcatpropid=" . DB_escape_string($_GET['DeleteProperty']);
	$result = DB_query($sql,$db,$ErrMsg);
	$sql = "DELETE FROM stockcatproperties WHERE stkcatpropid=" . DB_escape_string($_GET['DeleteProperty']);
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Deleted the property') . ' ' . $_GET['DeleteProperty'],'success');
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
        $ErrMsg = _('Could not update the stock category') . DB_escape_string($_POST['CategoryDescription']) . _('because');
        $result = DB_query($sql,$db,$ErrMsg);

        for ($i=0;$i<=$_POST['PropertyCounter'];$i++){

        	if ($_POST['PropReqSO' .$i] == true){
        			$_POST['PropReqSO' .$i] =1;
        	} else {
        			$_POST['PropReqSO' .$i] =0;
        	}
        	if ($_POST['PropID' .$i] =='NewProperty' AND strlen($_POST['PropLabel'.$i])>0){
        		$sql = "INSERT INTO stockcatproperties (categoryid,
        												label,
        												controltype,
        												defaultvalue,
        												reqatsalesorder)
        									VALUES ('" . $SelectedCategory . "',
        											'" . DB_escape_string($_POST['PropLabel' . $i]) . "',
        											" . $_POST['PropControlType' . $i] . ",
        											'" . DB_escape_string($_POST['PropDefault' .$i]) . "',
        											" . $_POST['PropReqSO' .$i] . ')';
        		$ErrMsg = _('Could not insert a new category property for') . $_POST['PropLabel' . $i];
        		$result = DB_query($sql,$db,$ErrMsg);
        	} elseif ($_POST['PropID' .$i] !='NewProperty') { //we could be amending existing properties
        		$sql = "UPDATE stockcatproperties SET label ='" . DB_escape_string($_POST['PropLabel' . $i]) . "',
        											  controltype = " . $_POST['PropControlType' . $i] . ",
        											  defaultvalue = '"	. DB_escape_string($_POST['PropDefault' .$i]) . "',
        											  reqatsalesorder = " . $_POST['PropReqSO' .$i] . "
        				WHERE stkcatpropid =" . $_POST['PropID' .$i];
        		$ErrMsg = _('Updated the stock category property for') . ' ' . $_POST['PropLabel' . $i];
        		$result = DB_query($sql,$db,$ErrMsg);
        	}

        } //end of loop round properties

        prnMsg(_('Updated the stock category record for') . ' ' . DB_escape_string($_POST['CategoryDescription']),'success');

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
        $ErrMsg = _('Could not insert the new stock category') . DB_escape_string($_POST['CategoryDescription']) . _('because');
        $result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('A new stock category record has been added for') . ' ' . DB_escape_string($_POST['CategoryDescription']),'success');

	}
	//run the SQL from either of the above possibilites

	unset($_POST['StockType']);
	unset($_POST['CategoryDescription']);
	unset($_POST['StockAct']);
	unset($_POST['AdjGLAct']);
	unset($_POST['PurchPriceVarAct']);
	unset($_POST['MaterialUseageVarAc']);
	unset($_POST['WIPAct']);


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
            		<td><a href=\"%sSelectedCategory=%s&delete=yes\" onclick=\"return confirm('" . _('Are you sure you wish to delete this stock category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</td>
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

		echo '<input type=hidden name="SelectedCategory" value="' . $SelectedCategory . '">';
		echo '<input type=hidden name="CategoryID" value="' . $_POST['CategoryID'] . '">';
		echo '<center><table><tr><td>' . _('Category Code') . ':</td><td>' . $_POST['CategoryID'] . '</td></tr>';

	} else { //end of if $SelectedCategory only do the else when a new record is being entered

		echo '<center><table><tr><td>' . _('Category Code') . ':</td>
                             <TD><input type="Text" name="CategoryID" size=7 maxlength=6 value="' . $_POST['CategoryID'] . '"></td></tr>';
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

	echo '<tr><td>' . _('Category Description') . ':</td>
            <td><input type="Text" name="CategoryDescription" size=22 maxlength=20 value="' . $_POST['CategoryDescription'] . '"></td></tr>';

	echo '<tr><td>' . _('Stock Type') . ':</td>
            <td><select name="StockType">';
		if ($_POST['StockType']=='F') {
			echo '<option selected value="F">' . _('Finished Goods');
		} else {
			echo '<option value="F">' . _('Finished Goods');
		}
		if ($_POST['StockType']=='M') {
			echo '<option selected value="M">' . _('Raw Materials');
		} else {
			echo '<option value="M">' . _('Raw Materials');
		}
		if ($_POST['StockType']=='D') {
			echo '<option selected value="D">' . _('Dummy Item - (No Movements)');
		} else {
			echo '<option value="D">' . _('Dummy Item - (No Movements)');
		}
		if ($_POST['StockType']=='L') {
			echo '<option selected value="L">' . _('Labour');
		} else {
			echo '<option value="L">' . _('Labour');
		}

	echo '</select></td></tr>';


	echo '<tr><td>' . _('Stock GL Code') . ':</td><td><select name="StockAct">';

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['StockAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';
	} //end while loop
	DB_data_seek($result,0);
	echo '</select></td></tr>';

	echo '<tr><td>' . _('WIP GL Code') . ':</td><td><select name="WIPAct">';

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['accountcode']==$_POST['WIPAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	echo '</select></td></tr>';

	$sql = "SELECT accountcode,
                 accountname
                 FROM chartmaster,
                      accountgroups
                 WHERE chartmaster.group_=accountgroups.groupname and
                       accountgroups.pandl!=0
                 ORDER BY accountcode";

	$result1 = DB_query($sql,$db);

	echo '<tr><td>' . _('Stock Adjustments GL Code') . ':</td>
            <td><select name="AdjGLAct">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['AdjGLAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_data_seek($result1,0);
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Price Variance GL Code') . ':</td>
            <td><select name="PurchPriceVarAct">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['PurchPriceVarAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_data_seek($result1,0);

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Usage Variance GL Code') . ':</td><td><select name="MaterialUseageVarAc">';

	while ($myrow = DB_fetch_array($result1)) {
		if ($myrow['accountcode']==$_POST['MaterialUseageVarAc']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

	} //end while loop
	DB_free_result($result1);
	echo '</select></td></tr>
			</table>';


	if (isset($SelectedCategory)) {
		//editing an existing stock category

		$sql = "SELECT stkcatpropid,
						label,
						controltype,
						defaultvalue,
						reqatsalesorder
                   FROM stockcatproperties
                   WHERE categoryid='" . DB_escape_string($SelectedCategory) . "'
                   ORDER BY stkcatpropid";

		$result = DB_query($sql, $db);

/*		echo '<br>Number of rows returned by the sql = ' . DB_num_rows($result) .
			'<br>The SQL was:<br>' . $sql;
*/
		echo '<hr><table>';
		$TableHeader = '<tr><td class="tableheader">' . _('Property Label') . '</td>
						<td class="tableheader">' . _('Control Type') . '</td>
						<td class="tableheader">' . _('Default Value') . '</td>
						<td class="tableheader">' . _('Require in SO') . '</td>
					</tr>';
		echo $TableHeader;
		$PropertyCounter =0;
		$HeadingCounter =0;
		while ($myrow = DB_fetch_array($result)) {
			if ($HeadingCounter>15){
				echo $TableHeader;
				$HeadingCounter=0;
			} else {
				$HeadingCounter++;
			}
			echo '<input type="hidden" name="PropID' . $PropertyCounter .'" value=' . $myrow['stkcatpropid'] . '>';
			echo '<tr><td><input type="textbox" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100" value="' . $myrow['label'] . '"></td>
						<td><select name="PropControlType' . $PropertyCounter . '">';
			if ($myrow['controltype']==0){
				echo '<option selected value=0>' . _('Text Box') . '</option>';
			} else {
				echo '<option value=0>' . _('Text Box') . '</option';
			}
			if ($myrow['controltype']==1){
				echo '<option selected value=1>' . _('Select Box') . '</option>';
			} else {
				echo '<option value=1>' . _('Select Box') . '</option';
			}
			if ($myrow['controltype']==2){
				echo '<option selected value=2>' . _('Check Box') . '</option>';
			} else {
				echo '<option value=2>' . _('Check Box') . '</option>';
			}

			echo '</select></td>
					<td><input type="textbox" name="PropDefault' . $PropertyCounter . '" value="' . $myrow['defaultvalue'] . '"></td>
					<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter .'"';
			if ($myrow['reqatsalesorder']==1){
					echo '"checked"';
			}
			echo '></td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&DeleteProperty=' . $myrow['stkcatpropid'] .'&SelectedCategory=' . $SelectedCategory . '" onclick=\'return confirm("' . _('Are you sure you wish to delete this property? All properties of this type set up for stock items will also be deleted.') . '");\'>' . _('Delete') . '</td></tr>';

			$PropertyCounter++;
		} //end loop around defined properties for this category
		echo '<input type="hidden" name="PropID' . $PropertyCounter .'" value="NewProperty">';
		echo '<tr><td><input type="textbox" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100"></td>
					<td><select name="PropControlType' . $PropertyCounter . '">';
		echo '<option selected value=0>' . _('Text Box') . '</option>';
		echo '<option value=1>' . _('Select Box') . '</option>';
		echo '<option value=2>' . _('Check Box') . '</option>';
		echo '</select></td>
				<td><input type="textbox" name="PropDefault' . $PropertyCounter . '"></td>
				<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter .'"></td></tr>';
		echo '</table>';
		echo '<input type=hidden name="PropertyCounter" value=' . $PropertyCounter . '>';

	} /* end if there is a category selected */


	echo '<CENTER><input type="Submit" name="submit" value="' . _('Enter Information') . '">';


	echo '</FORM>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>