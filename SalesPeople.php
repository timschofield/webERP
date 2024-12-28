<?php

include('includes/session.php');
$Title = _('Sales People Maintenance');
$ViewTopic = 'SalesPeople';
$BookMark = 'SalesPeople';
if(isset($_GET['SelectedSalesPerson'])) {
	$BookMark = 'SalespeopleEdit';
}// For Edit's screen.
if(isset($_GET['delete'])) {
	$BookMark = 'SalespeopleDelete';
}// For Delete's ERROR Message Report.
include('includes/header.php');

if (isset($_GET['SelectedSalesPerson'])){
	$SelectedSalesPerson =mb_strtoupper($_GET['SelectedSalesPerson']);
} elseif(isset($_POST['SelectedSalesPerson'])){
	$SelectedSalesPerson =mb_strtoupper($_POST['SelectedSalesPerson']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

$CommissionPeriods[0] = _('No Commission');
$CommissionPeriods[1] = _('Monthly');
$CommissionPeriods[2] = _('Quarterly');
$CommissionPeriods[3] = _('Annually');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (mb_strlen($_POST['SalesmanCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The salesperson code must be three characters or less long'),'error');
		$Errors[$i] = 'SalesmanCode';
		$i++;
	} elseif (mb_strlen($_POST['SalesmanCode'])==0 OR $_POST['SalesmanCode']=='') {
		$InputError = 1;
		prnMsg(_('The salesperson code cannot be empty'),'error');
		$Errors[$i] = 'SalesmanCode';
		$i++;
	} elseif (mb_strlen($_POST['SalesmanName']) > 30) {
		$InputError = 1;
		prnMsg(_('The salesperson name must be thirty characters or less long'),'error');
		$Errors[$i] = 'SalesmanName';
		$i++;
	} elseif (mb_strlen($_POST['SManTel']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'),'error');

	} elseif (mb_strlen($_POST['SManFax']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'),'error');

	}
	if (!isset($_POST['SManTel'])){
	  $_POST['SManTel']='';
	}
	if (!isset($_POST['SManFax'])){
	  $_POST['SManFax']='';
	}
	if (!isset($_POST['Current'])){
	  $_POST['Current']=0;
	}

	if (isset($SelectedSalesPerson) AND $InputError !=1) {

		/*SelectedSalesPerson could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE salesman SET salesmanname='" . $_POST['SalesmanName'] . "',
									smantel='" . $_POST['SManTel'] . "',
									smanfax='" . $_POST['SManFax'] . "',
									current='" . $_POST['Current'] . "',
									commissionperiod='" . $_POST['CommissionPeriod'] . "',
									commissiontypeid='" . $_POST['CommissionTypeID'] . "',
									glaccount='" . $_POST['GLAccount'] . "'
								WHERE salesmancode = '" . stripslashes($SelectedSalesPerson) . "'";

		$Msg = _('Salesperson record for') . ' ' . $_POST['SalesmanName'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */

		$SQL = "INSERT INTO salesman (salesmancode,
						salesmanname,
						smantel,
						smanfax,
						current,
						commissionperiod,
						commissiontypeid,
						glaccount)
				VALUES ('" . $_POST['SalesmanCode'] . "',
						'" . $_POST['SalesmanName'] . "',
						'" . $_POST['SManTel'] . "',
						'" . $_POST['SManFax'] . "',
						'" . $_POST['Current'] . "',
						'" . $_POST['CommissionPeriod'] . "',
						'" . $_POST['CommissionTypeID'] . "',
						'" . $_POST['GLAccount'] . "'
					)";

		$Msg = _('A new salesperson record has been added for') . ' ' . $_POST['SalesmanName'];
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The insert or update of the salesperson failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL,$ErrMsg, $DbgMsg);

		prnMsg($Msg , 'success');

		unset($SelectedSalesPerson);
		unset($_POST['SalesmanCode']);
		unset($_POST['SalesmanName']);
		unset($_POST['SManFax']);
		unset($_POST['SManTel']);
		unset($_POST['Current']);
		unset($_POST['CommissionPeriod']);
		unset($_POST['CommissionTypeID']);
		unset($_POST['GLAccount']);
	}

} elseif (isset($_GET['delete'])) {
$BookMark = 'SalespeopleDelete';
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'

	$SQL= "SELECT COUNT(*) FROM custbranch WHERE  custbranch.salesman='".$SelectedSalesPerson."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(_('Cannot delete this salesperson because branches are set up referring to them') . ' - ' . _('first alter the branches concerned') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('branches that refer to this salesperson'),'error');

	} else {
		$SQL= "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.salesperson='".$SelectedSalesPerson."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(_('Cannot delete this salesperson because sales analysis records refer to them') , '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('sales analysis records that refer to this salesperson'),'error');
		} else {
			$SQL= "SELECT COUNT(*) FROM www_users WHERE salesman='".$SelectedSalesPerson."'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0]>0) {
				prnMsg(_('Cannot delete this salesperson because') , '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('user records that refer to this salesperson') . '.' ._('First delete any users that refer to this sales person'),'error');
			} else {

				$SQL="DELETE FROM salesman WHERE salesmancode='". $SelectedSalesPerson."'";
				$ErrMsg = _('The salesperson could not be deleted because');
				$Result = DB_query($SQL,$ErrMsg);

				prnMsg(_('Salesperson') . ' ' . $SelectedSalesPerson . ' ' . _('has been deleted from the database'),'success');
				unset ($SelectedSalesPerson);
				unset($Delete);
			}
		}
	} //end if Sales-person used in GL accounts
}

if (!isset($SelectedSalesPerson)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedSalesPerson will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT salesmancode,
				salesmanname,
				smantel,
				smanfax,
				current,
				commissionperiod,
				commissiontypeid,
				glaccount
			FROM salesman";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Telephone') . '</th>
			<th>' . _('Facsimile') . '</th>
			<th class="SortedColumn">', _('Current'), '</th>
			<th class="SortedColumn">', _('Commission Period'), '</th>
			<th class="SortedColumn">', _('Commission Calculation Method'), '</th>
			<th class="SortedColumn">', _('GL Account'), '</th>
			<th colspan="3"></th>
		</tr>';

	while ($MyRow=DB_fetch_array($Result)) {

		if ($MyRow['current'] == 1) {
			$ActiveText = _('Yes');
		} else {
			$ActiveText = _('No');
		}

		if ($MyRow['commissiontypeid'] == 0) {
			$TypeRow['commissiontypename'] = _('No Commission');
		} else {
			$SQL = "SELECT commissiontypename FROM salescommissiontypes WHERE commissiontypeid='" . $MyRow['commissiontypeid'] . "'";
			$TypeResult = DB_query($SQL);
			$TypeRow = DB_fetch_array($TypeResult);
		}

		$SQL = "SELECT accountname
					FROM chartmaster
					WHERE accountcode='" . $MyRow['glaccount'] . "'";
		$GLResult = DB_query($SQL);
		$GLRow = DB_fetch_array($GLResult);

		echo'<tr class="striped_row">
				<td>', $MyRow['salesmancode'], '</td>
				<td>', $MyRow['salesmanname'], '</td>
				<td>', $MyRow['smantel'], '</td>
				<td>', $MyRow['smanfax'], '</td>
				<td>', $ActiveText, '</td>
				<td>', $CommissionPeriods[$MyRow['commissionperiod']], '</td>
				<td>', $MyRow['commissiontypeid'], ' - ', $TypeRow['commissiontypename'], '</td>
				<td>', $MyRow['glaccount'], ' - ', $GLRow['accountname'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedSalesPerson=', urlencode($MyRow['salesmancode']), '">', _('Edit'), '</a></td>
				<td><a href="', $RootPath, '/SalesCommissionRates.php?SelectedSalesPerson=', urlencode($MyRow['salesmancode']), '">', _('Edit Commission Rates'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedSalesPerson=', urlencode($MyRow['salesmancode']), '&amp;delete=1" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this sales person?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedSalesPerson)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Sales People') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSalesPerson)) {
		//editing an existing Sales-person

		$SQL = "SELECT salesmancode,
					salesmanname,
					smantel,
					smanfax,
					current,
					commissionperiod,
					commissiontypeid,
					glaccount
				FROM salesman
				WHERE salesmancode='".$SelectedSalesPerson."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SalesmanCode'] = $MyRow['salesmancode'];
		$_POST['SalesmanName'] = $MyRow['salesmanname'];
		$_POST['SManTel'] = $MyRow['smantel'];
		$_POST['SManFax'] = $MyRow['smanfax'];
		$_POST['Current'] = $MyRow['current'];
		$_POST['CommissionPeriod'] = $MyRow['commissionperiod'];
		$_POST['CommissionTypeID'] = $MyRow['commissiontypeid'];
		$_POST['GLAccount'] = $MyRow['glaccount'];


		echo '<input type="hidden" name="SelectedSalesPerson" value="' . $SelectedSalesPerson . '" />';
		echo '<input type="hidden" name="SalesmanCode" value="' . $_POST['SalesmanCode'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit the details for'), ' ', $_POST['SalesmanCode'], ' - ', $_POST['SalesmanName'], '</legend>
				<field>
					<label for="SalesmanCode">', _('Salesperson code'), ':</label>
					<div class="fieldtext">', $_POST['SalesmanCode'], '</div>
				</field>';

	} else { //end of if $SelectedSalesPerson only do the else when a new record is being entered

		echo '<fieldset>
				<legend>', _('Create a new sales person record'), '</legend>
				<field>
					<label for="SalesmanCode">', _('Salesperson code'), ':</label>
					<input type="text" name="SalesmanCode" size="3" autofocus="autofocus" required="required" maxlength="3" />
					<fieldhelp>', _('Enter a three character code for this sales person.'), '</fieldhelp>
				</field>';
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
	if (!isset($_POST['Current'])){
	  $_POST['Current']=1;
	}

	echo '<field>
			<label for="SalesmanName">' . _('Salesperson Name') . ':</label>
			<input type="text" '. (in_array('SalesmanName',$Errors) ? 'class="inputerror"' : '' ) .' name="SalesmanName"  size="30" maxlength="30" value="' . $_POST['SalesmanName'] . '" />
			<fieldhelp>', _('Enter the name by which this sales person will be known.'), '</fieldhelp>
		</field>';
	echo '<field>
			<label for="SManTel">' . _('Telephone No') . ':</label>
			<input type="text" name="SManTel" size="20" maxlength="20" value="' . $_POST['SManTel'] . '" />
			<fieldhelp>', _('Contact telephone number for this sales person.'), '</fieldhelp>
		</field>';
	echo '<field>
			<label for="SManFax">' . _('Facsimile No') . ':</label>
			<input type="text" name="SManFax" size="20" maxlength="20" value="' . $_POST['SManFax'] . '" />
			<fieldhelp>', _('Contact fax number for this sales person.'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="CommissionPeriod">', _('Commission Period'), ':</label>
			<select required="required" name="CommissionPeriod">';
	foreach ($CommissionPeriods as $Index => $PeriodName) {
		if ($Index == $_POST['CommissionPeriod']) {
			echo '<option selected="selected" value="', $Index, '">', $PeriodName, '</option>';
		} else {
			echo '<option value="', $Index, '" />', $PeriodName, '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the period over which this sales person has there commission calculated.'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="CommissionTypeID">', _('Commission Calculation Method'), ':</label>
			<select name="CommissionTypeID">';
	$SQL = "SELECT commissiontypeid, commissiontypename FROM salescommissiontypes ORDER BY commissiontypename";
	$ErrMsg = _('An error occurred in retrieving the sales commission types from the database');
	$DbgMsg = _('The SQL that was used to retrieve the commission type information and that failed in the process was');
	$CommissionTypeResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (!isset($_POST['CommissionTypeID']) or $_POST['CommissionTypeID'] == 0) {
		echo '<option selected="selected" value="0">', _('No Commission'), '</option>';
	} else {
		echo '<option value="0">', _('No Commission'), '</option>';
	}
	while ($CommissionTypeRow = DB_fetch_array($CommissionTypeResult)) {
		if ($_POST['CommissionTypeID'] == $CommissionTypeRow['commissiontypeid']) {
			echo '<option selected="selected" value="', $CommissionTypeRow['commissiontypeid'], '">', $CommissionTypeRow['commissiontypename'], ' (', $CommissionTypeRow['commissiontypeid'], ')</option>';
		} else {
			echo '<option value="', $CommissionTypeRow['commissiontypeid'], '">', $CommissionTypeRow['commissiontypename'], ' (', $CommissionTypeRow['commissiontypeid'], ')</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the calculation method used to calculate commission for this sales person. This is only used if a commission period is selected.'), '</fieldhelp>
	</field>';

	$Result = DB_query("SELECT accountcode,
							accountname
						FROM chartmaster
						INNER JOIN accountgroups
							ON chartmaster.group_=accountgroups.groupname
						WHERE accountgroups.pandl=1
						ORDER BY chartmaster.accountcode");
	echo '<field>
			<label for="GLAccount">', _('GL Commission Account'), ':</label>
			<select name="GLAccount">';

	while ($MyRow = DB_fetch_row($Result)) {
		if ($_POST['GLAccount'] == $MyRow[0]) {
			echo '<option selected="selected" value="', $MyRow[0], '">', htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8'), ' (', $MyRow[0], ')</option>';
		} else {
			echo '<option value="', $MyRow[0], '">', htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8'), ' (', $MyRow[0], ')</option>';
		}
	} //end while loop
	echo '</select>
		<fieldhelp>', _('Select the general ledger account to post the sales commission for this sales person to.'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="Current">', _('Current?'), ':</label>
			<select required="required" name="Current">';
	if ($_POST['Current'] == 1) {
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="1">', _('Yes'), '</option>';
	}
	if ($_POST['Current'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
	}
	echo '</select>
		<fieldhelp>', _('If this sales person is currently employed, select Yes, otherwise select No.'), '</fieldhelp>
	</field>';

	echo '</fieldset>';
	
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
        </div>
	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>