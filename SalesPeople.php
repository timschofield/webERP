<?php

/*****************************************************************************************
KL RICARD MODIFICATIONS:
- Simplified fields not used in KL (or anyway in webERP)
*****************************************************************************************/

include('includes/session.php');
$Title = _('SPG Maintenance');
$ViewTopic = 'SalesPeople';
$BookMark = 'SalesPeople';
if(isset($_GET['SelectedSalesPerson'])) {
	$BookMark = 'SalespeopleEdit';
}// For Edit's screen.
if(isset($_GET['delete'])) {
	$BookMark = 'SalespeopleDelete';
}// For Delete's ERROR Message Report.
include('includes/header.php');
include('includes/KLEmails.php');

if (isset($_GET['SelectedSalesPerson'])){
	$SelectedSalesPerson =mb_strtoupper($_GET['SelectedSalesPerson']);
} elseif(isset($_POST['SelectedSalesPerson'])){
	$SelectedSalesPerson =mb_strtoupper($_POST['SelectedSalesPerson']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	// KL RICARD INit these variables otherwise gives error
	if (!isset($_POST['CommissionRate1'])){
		$_POST['CommissionRate1']=0;
	  }
	  if (!isset($_POST['CommissionRate2'])){
		$_POST['CommissionRate2']=0;
	  }
	  if (!isset($_POST['Breakpoint'])){
		$_POST['Breakpoint']=0;
	  }
	// KL RICARD END

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
	} elseif (!is_numeric(filter_number_format($_POST['CommissionRate1']))
			OR !is_numeric(filter_number_format($_POST['CommissionRate2']))) {
		$InputError = 1;
		prnMsg(_('The commission rates must be a floating point number'),'error');
	} elseif (!is_numeric(filter_number_format($_POST['Breakpoint']))) {
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
	if (!isset($_POST['Current'])){
	  $_POST['Current']=0;
	}

	if (isset($SelectedSalesPerson) AND $InputError !=1) {

		/*SelectedSalesPerson could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE salesman SET salesmanname='" . $_POST['SalesmanName'] . "',
						commissionrate1='" . filter_number_format($_POST['CommissionRate1']) . "',
						smantel='" . $_POST['SManTel'] . "',
						smanfax='" . $_POST['SManFax'] . "',
						breakpoint='" . filter_number_format($_POST['Breakpoint']) . "',
						commissionrate2='" . filter_number_format($_POST['CommissionRate2']) . "',
						current='" . $_POST['Current'] . "'
				WHERE salesmancode = '".$SelectedSalesPerson."'";

		$Msg = _('Salesperson record for') . ' ' . $_POST['SalesmanName'] . ' ' . _('has been updated');

		KLSendEmail("SpgCodeUpdated", "Silent", $SelectedSalesPerson, $_POST['SalesmanName'], $_SESSION['UserID']);

	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */

		$SQL = "INSERT INTO salesman (salesmancode,
						salesmanname,
						commissionrate1,
						commissionrate2,
						breakpoint,
						smantel,
						smanfax,
						current)
				VALUES ('" . $_POST['SalesmanCode'] . "',
					'" . $_POST['SalesmanName'] . "',
					'" . filter_number_format($_POST['CommissionRate1']) . "',
					'" . filter_number_format($_POST['CommissionRate2']) . "',
					'" . filter_number_format($_POST['Breakpoint']) . "',
					'" . $_POST['SManTel'] . "',
					'" . $_POST['SManFax'] . "',
					'" . $_POST['Current'] . "'
					)";

		$Msg = _('A new salesperson record has been added for') . ' ' . $_POST['SalesmanName'];

		KLSendEmail("SpgCodeCreated", "Silent",$_POST['SalesmanCode'], $_POST['SalesmanName'], $_SESSION['UserID']);
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
		unset($_POST['CommissionRate1']);
		unset($_POST['CommissionRate2']);
		unset($_POST['Breakpoint']);
		unset($_POST['SManFax']);
		unset($_POST['SManTel']);
		unset($_POST['Current']);
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
				KLSendEmail("SpgCodeDeleted", "Silent", $SelectedSalesPerson, $_SESSION['UserID']);
				unset ($SelectedSalesPerson);
				unset($delete);
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
				commissionrate1,
				breakpoint,
				commissionrate2,
				current
			FROM salesman
			ORDER BY current DESC,
				salesmancode";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Current') . '</th>
		</tr>';

	while ($MyRow=DB_fetch_array($Result)) {

	if ($MyRow[7] == 1) {
		$ActiveText = _('Yes');
	} else {
		$ActiveText = _('No');
	}

		printf('<tr class="striped_row">
			<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedSalesPerson=%s">' .  _('Edit') . '</a></td>
			<td><a href="%sSelectedSalesPerson=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this sales person?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$MyRow['salesmancode'],
			$MyRow['salesmanname'],
			$ActiveText,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$MyRow['salesmancode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$MyRow['salesmancode']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedSalesPerson)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Sales People') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSalesPerson)) {
		//editing an existing Sales-person

		$SQL = "SELECT salesmancode,
					salesmanname,
					smantel,
					smanfax,
					commissionrate1,
					breakpoint,
					commissionrate2,
					current
				FROM salesman
				WHERE salesmancode='".$SelectedSalesPerson."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SalesmanCode'] = $MyRow['salesmancode'];
		$_POST['SalesmanName'] = $MyRow['salesmanname'];
		$_POST['SManTel'] = $MyRow['smantel'];
		$_POST['SManFax'] = $MyRow['smanfax'];
		$_POST['CommissionRate1']  = locale_number_format($MyRow['commissionrate1'],'Variable');
		$_POST['Breakpoint'] = locale_number_format($MyRow['breakpoint'],$_SESSION['CompanyRecord']['decimalplaces']);
		$_POST['CommissionRate2']  = locale_number_format($MyRow['commissionrate2'],'Variable');
		$_POST['Current']  = $MyRow['current'];


		echo '<input type="hidden" name="SelectedSalesPerson" value="' . $SelectedSalesPerson . '" />';
		echo '<input type="hidden" name="SalesmanCode" value="' . $_POST['SalesmanCode'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' . _('SPG code') . ':</td>
					<td>' . $_POST['SalesmanCode'] . '</td>
				</tr>';

	} else { //end of if $SelectedSalesPerson only do the else when a new record is being entered

		echo '<table class="selection">
				<tr>
					<td>' . _('SPG code') . ':</td>
					<td><input type="text" '. (in_array('SalesmanCode',$Errors) ? 'class="inputerror"' : '' ) .' name="SalesmanCode" size="3" maxlength="3" /></td>
				</tr>';
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

	echo '<tr>
			<td>' . _('SPG Name') . ':</td>
			<td><input type="text" '. (in_array('SalesmanName',$Errors) ? 'class="inputerror"' : '' ) .' name="SalesmanName"  size="30" maxlength="30" value="' . $_POST['SalesmanName'] . '" /></td>
		</tr>';
/*	echo '<tr>
			<td>' . _('Telephone No') . ':</td>
			<td><input type="text" name="SManTel" size="20" maxlength="20" value="' . $_POST['SManTel'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Facsimile No') . ':</td>
			<td><input type="text" name="SManFax" size="20" maxlength="20" value="' . $_POST['SManFax'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Commission Rate 1') . ':</td>
			<td><input type="text" class="number" name="CommissionRate1" size="5" maxlength="5" value="' . $_POST['CommissionRate1'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Breakpoint') . ':</td>
			<td><input type="text" class="number" name="Breakpoint" size="6" maxlength="6" value="' . $_POST['Breakpoint'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Commission Rate 2') . ':</td>
			<td><input type="text" class="number" name="CommissionRate2" size="5" maxlength="5" value="' . $_POST['CommissionRate2']. '" /></td>
		</tr>';
*/
	echo '<tr>
			<td>' . _('Current?') . ':</td>
			<td><select name="Current">';
	if (!isset($_POST['Current'])){
		$_POST['Current']=1;
	}
	if ($_POST['Current']==1){
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['Current']==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
