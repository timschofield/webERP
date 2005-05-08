<?php
/* $Revision: 1.9 $ */

$PageSecurity = 3;

include('includes/session.inc');
$title = _('Sales People Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedSaleperson'])){
	$SelectedSaleperson =strtoupper($_GET['SelectedSaleperson']);
} elseif(isset($_POST['SelectedSaleperson'])){
	$SelectedSaleperson =strtoupper($_POST['SelectedSaleperson']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['SalesmanCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The salesperson code must be three characters or less long'),'error');

	} elseif (strlen($_POST['SalesmanCode'])==0 OR $_POST['SalesmanCode']=='') {
		$InputError = 1;
		prnMsg(_('The salesperson code cannot be empty'),'error');

	} elseif (strlen($_POST['SalesmanName']) > 30) {
		$InputError = 1;
		prnMsg(_('The salesperson name must be thity characters or less long'),'error');

	} elseif (strlen($_POST['SManTel']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'),'error');

	} elseif (strlen($_POST['SManFax']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'),'error');

	} elseif (!is_double((double)$_POST['CommissionRate1']) OR !is_double((double) $_POST['CommissionRate2'])) {
		$InputError = 1;
		prnMsg(_('The commission rates must be a floating point number'),'error');
	} elseif (!is_double((double)$_POST['Breakpoint'])) {
		$InputError = 1;
		prnMsg(_('The breakpoint should be a floating point number'),'error');
	}

	if (!isset($_POST['SManTel'])){
	  $_POST['SManTel']='';
	}
	if (!isset($_POST['SManFax'])){
	  $_POST['SManFax']='';
	}
	if (!isset($_POST['CommissionRate1'])){
	  $_POST['CommissionRate1']=0;
	}
	if (!isset($_POST['CommissionRate2'])){
	  $_POST['CommissionRate2']=0;
	}
	if (!isset($_POST['Breakpoint'])){
	  $_POST['Breakpoint']=0;
	}

	if (isset($SelectedSaleperson) AND $InputError !=1) {

		/*SelectedSaleperson could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE salesman SET salesmanname='" . $_POST['SalesmanName'] . "',
						commissionrate1=" . $_POST['CommissionRate1'] . ",
						smantel='" . $_POST['SManTel'] . "',
						smanfax='" . $_POST['SManFax'] . "',
						breakpoint=" . $_POST['Breakpoint'] . ",
						commissionrate2=" . $_POST['CommissionRate2'] . "
				WHERE salesmancode = '$SelectedSaleperson'";

		$msg = _('Salesperson record for') . ' ' . $_POST['SalesmanName'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */

		$sql = "INSERT INTO salesman (salesmancode,
						salesmanname,
						commissionrate1,
						commissionrate2,
						breakpoint,
						smantel,
						smanfax)
				VALUES ('" . $_POST['SalesmanCode'] . "',
					'" . $_POST['SalesmanName'] . "',
					" . $_POST['CommissionRate1'] . ",
					" . $_POST['CommissionRate2'] . ",
					" . $_POST['Breakpoint'] . ",
					'" . $_POST['SManTel'] . "',
					'" . $_POST['SManFax'] . "'
					)";

		$msg = _('A new salesperson record has been added for') . ' ' . $_POST['SalesmanName'];
	}
	//run the SQL from either of the above possibilites
	$ErrMsg = _('The insert or update of the salesperson failed because');
	$DbgMsg = _('The SQL that was used and failed was');
	$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);

	prnMsg($msg , 'success');

	unset($SelectedSalesperson);
	unset($_POST['SalesmanCode']);
	unset($_POST['SalesmanName']);
	unset($_POST['CommissionRate1']);
	unset($_POST['CommissionRate2']);
	unset($_POST['Breakpoint']);
	unset($_POST['SManFax']);
	unset($_POST['SManTel']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'

	$sql= "SELECT COUNT(*) FROM custbranch WHERE  custbranch.salesman='$SelectedSaleperson'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this salesperson because branches are set up referring to them') . ' - ' . _('first alter the branches concerned') . '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches that refer to this salesperson'),'error');

	} else {
		$sql= "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.salesperson='$SelectedSaleperson'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg(_('Cannot delete this salesperson because sales analysis records refer to them') , '<BR>' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records that refer to this salesperson'),'error');
		} else {

			$sql="DELETE FROM salesman WHERE salesmancode='$SelectedSaleperson'";
			$ErrMsg = _('The salesperson could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);

			prnMsg(_('Salesperson') . ' ' . $SelectedSalesperson . ' ' . _('has been deleted from the database'),'success');
			unset ($SelectedSalesperson);
			unset($delete);
		}
	} //end if Sales-person used in GL accounts

}

if (!isset($SelectedSaleperson)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedSaleperson will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT salesmancode,
			salesmanname,
			smantel,
			smanfax,
			commissionrate1,
			breakpoint,
			commissionrate2
		FROM salesman";
	$result = DB_query($sql,$db);

	echo '<CENTER><TABLE BORDER=1>';
	echo "<tr><td class='tableheader'>" . _('Code') . "</td>
		<td class='tableheader'>" . _('Name') . "</td>
		<td class='tableheader'>" . _('Telephone') . "</td>
		<td class='tableheader'>" . _('Facsimile') . "</td>
		<td class='tableheader'>" . _('Comm Rate 1') . "</td>
		<td class='tableheader'>" . _('Break') . "</td>
		<td class='tableheader'>" . _('Comm Rate 2') . "</td></tr>";

	while ($myrow=DB_fetch_row($result)) {


	printf("<tr>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%sSelectedSaleperson=%s\">". _('Edit') . "</a></td>
		<td><a href=\"%sSelectedSaleperson=%s&delete=1\">" . _('Delete') . "</a></td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$myrow[2],
		$myrow[3],
		$myrow[4],
		$myrow[5],
		$myrow[6],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID . '&',
		$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table></CENTER>';
} //end of ifs and buts!

if (isset($SelectedSaleperson)) {
	echo "<CENTER><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Show All Sales People') . "</A></CENTER>";
}

if (! isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedSaleperson)) {
		//editing an existing Sales-person

		$sql = "SELECT salesmancode,
				salesmanname,
				smantel,
				smanfax,
				commissionrate1,
				breakpoint,
				commissionrate2
			FROM salesman
			WHERE salesmancode='$SelectedSaleperson'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SalesmanCode'] = $myrow['salesmancode'];
		$_POST['SalesmanName'] = $myrow['salesmanname'];
		$_POST['SManTel'] = $myrow['smantel'];
		$_POST['SManFax'] = $myrow['smanfax'];
		$_POST['CommissionRate1']  = $myrow['commissionrate1'];
		$_POST['Breakpoint'] = $myrow['breakpoint'];
		$_POST['CommissionRate2']  = $myrow['commissionrate2'];


		echo "<INPUT TYPE=HIDDEN NAME='SelectedSaleperson' VALUE='" . $SelectedSaleperson . "'>";
		echo "<INPUT TYPE=HIDDEN NAME='SalesmanCode' VALUE='" . $_POST['SalesmanCode'] . "'>";
		echo '<CENTER><TABLE> <TR><TD>' . _('Salesperson code') . ':</TD><TD>';
		echo $_POST['SalesmanCode'] . '</TD></TR>';

	} else { //end of if $SelectedSaleperson only do the else when a new record is being entered

		echo '<CENTER><TABLE><TR><TD>' . _('Salesperson code') . ":</TD>
			<TD><input type='Text' name='SalesmanCode' SIZE=3 MAXLENGTH=3></TD></TR>";
	}
	if (!isset($_POST['SalesmanName'])){
	  $_POST['SalesmanName']='';
	}
	if (!isset($_POST['SManTel'])){
	  $_POST['SManTel']='';
	}
	if (!isset($_POST['SManFax'])){
	  $_POST['SManFax']='';
	}
	if (!isset($_POST['CommissionRate1'])){
	  $_POST['CommissionRate1']=0;
	}
	if (!isset($_POST['CommissionRate2'])){
	  $_POST['CommissionRate2']=0;
	}
	if (!isset($_POST['Breakpoint'])){
	  $_POST['Breakpoint']=0;
	}


	echo '<TR><TD>' . _('Salesperson Name') . ":</TD><TD><INPUT TYPE='text' name='SalesmanName'  SIZE=30 MAXLENGTH=30 VALUE='" . $_POST['SalesmanName'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Telephone No') . ":</TD><TD><INPUT TYPE='Text' name='SManTel' SIZE=20 MAXLENGTH=20 VALUE='" . $_POST['SManTel'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Facsimile No') . ":</TD><TD><INPUT TYPE='Text' name='SManFax' SIZE=20 MAXLENGTH=20 VALUE='" . $_POST['SManFax'] . "'></TD></TR>";
	echo '<TR><TD>' . _('Commission Rate 1') . ":</TD><TD><INPUT TYPE='Text' name='CommissionRate1' SIZE=5 MAXLENGTH=5 VALUE=" . $_POST['CommissionRate1'] . '></TD></TR>';
	echo '<TR><TD>' . _('Breakpoint') . ":</TD><TD><INPUT TYPE='Text' name='Breakpoint' SIZE=6 MAXLENGTH=6 VALUE=" . $_POST['Breakpoint'] . '></TD></TR>';
	echo '<TR><TD>' . _('Commission Rate 2') . ":</TD><TD><INPUT TYPE='Text' name='CommissionRate2' SIZE=5 MAXLENGTH=5 VALUE=" . $_POST['CommissionRate2']. '></TD></TR>';

	echo '</TABLE>';

	echo "<CENTER><input type='Submit' name='submit' value='" . _('Enter Information') . "'></CENTER>";

	echo '</FORM>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>