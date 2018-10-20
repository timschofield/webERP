<?php

/* Defines the employees that require timesheets */

include('includes/session.php');
$Title = _('Employee Maintenance');// Screen identification.
$ViewTopic = 'Labour';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Employees';// Anchor's id in the manual's html document.

include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/user.png" title="',// Icon image.
	_('Employee'), '" /> ',// Icon title.
	_('Employee Maintenance'), '</p>';// Page title.

if(isset($_GET['SelectedEmployee'])) {
	$SelectedEmployee = $_GET['SelectedEmployee'];
} elseif(isset($_POST['SelectedEmployee'])) {
	$SelectedEmployee = $_POST['SelectedEmployee'];
}

if(isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if(trim($_POST['Surname']) == '') {
		$InputError = 1;
		prnMsg(_('The employee\'s surname must not be empty'), 'error');
	}
	if($_POST['FirstName'] =='') {
		$InputError = 1;
		prnMsg(_('The employee\'s first name must not be empty'), 'error');
	}
	//end of checking the input

	if(isset($SelectedEmployee) AND $InputError !=1) {


		$sql = "UPDATE employees SET surname='" . $_POST['Surname'] . "',
									firstname='" . $_POST['FirstName'] . "',
									stockid='" . $_POST['StockID'] . "',
									manager='" . $_POST['Manager'] . "',
									normalhours='" . $_POST['NormalHours'] . "',
									userid='" . $_POST['UserID'] . "',
									email='" . $_POST['Email'] . "'
						WHERE id = '" . $SelectedEmployee . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedEmployee . ' ' . _('employee record because');
		$DbgMsg = _('The SQL used to update the employee record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The employee record has been updated'),'success');
	
	} elseif($InputError !=1) {

		/*SelectedEmployee is null cos no employee selected on first time round so must be adding a	record must be submitting a new employee form */

		$sql = "INSERT INTO employees (	surname, 
										firstname,
										stockid,
										manager,
										normalhours,
										userid,
										email )
						VALUES ('" . $_POST['Surname'] . "',
								'" . $_POST['FirstName'] . "',
								'" . $_POST['StockID'] . "',
								'" . $_POST['Manager'] . "',
								'" . $_POST['NormalHours'] . "',
								'" . $_POST['UserID'] . "',
								'" . $_POST['Email'] . "')";

		$ErrMsg = _('An error occurred inserting the new employee record because');
		$DbgMsg = _('The SQL used to insert the employee record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The new employee record has been added'),'success');

	}

	unset($_POST['Surname']);
	unset($_POST['FirstName']);
	unset($_POST['StockID']);
	unset($_POST['Manager']);
	unset($_POST['NormalHours']);
	unset($_POST['UserID']);
	unset($_POST['Email']);
	unset($SelectedEmployee);

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS
/* once timesheets are defined
	$sql= "SELECT COUNT(*) FROM timesheets WHERE employeeid='". $SelectedEmployee . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this employee because timesheets have been created for this person'),'warn');
		echo _('There are') . ' ' . $myrow[0] . ' ' . _('timesheet records for this person');
	}
*/
	if(! $CancelDelete) {
		$result = DB_query("DELETE FROM employees WHERE id='" . $SelectedEmployee . "'");

		prnMsg(_('Employee') . ' ' . $SelectedEmployee . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedEmployee);
	}//end if Delete Location
	unset($SelectedEmployee);
	unset($_GET['delete']);
}
/*
echo '<br /> Selected employee = ' . $SelectedEmployee;
if (isset($SelectedEmployee)) {
	echo '<br /> Selected employee is actually set!!!!';
}
*/


if(!isset($SelectedEmployee)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedEmployee will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of employees will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT employees.id,
					employees.surname,
					employees.firstname,
					employees.stockid,
					employees.manager,
					employees2.firstname as managerfirstname,
					employees2.surname as managersurname,
					employees.normalhours,
					employees.email,
					employees.userid
			FROM employees LEFT JOIN employees AS employees2
			ON employees.manager=employees2.id";

	$result = DB_query($sql);
	if (DB_num_rows($result) > 0) {
		echo '<table class="selection">
			<thead>
			<tr>
				<th class="ascending">', _('ID'), '</th>
				<th class="ascending">', _('First name'), '</th>
				<th class="ascending">', _('Surname'), '</th>
				<th class="ascending">', _('Type'), '</th>
				<th class="ascending">', _('Manager'), '</th>
				<th class="ascending">', _('Email'), '</th>
				<th class="noprint" colspan="2">&nbsp;</th>
				</tr>
			</thead>
			<tbody>';
	
	while ($myrow = DB_fetch_array($result)) {
	
		printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="mailto:%s">%s</a></td>
					<td class="noprint"><a href="%sSelectedEmployee=%s">' . _('Edit') . '</a></td>
					<td class="noprint"><a href="%sSelectedEmployee=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to remove this employee?') . '\');">' . _('Delete') . '</a></td>
				</tr>',
				$myrow['id'],
				$myrow['firstname'],
				$myrow['surname'],
				$myrow['stockid'],
				$myrow['managerfirstname'] . ' ' . $myrow['managersurname'],
				$myrow['email'],
				$myrow['email'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['id'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['id']);
		}
		//END WHILE LIST LOOP
		echo '</tbody></table>';
	} else {
		prnMsg(_('No employees have been set up yet'),'info');
	}
}

//end of ifs and buts!

echo '<br />';
if(isset($SelectedEmployee)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Employees') . '</a>';
}
echo '<br />';



if(!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedEmployee)) {
		//editing an existing Location

		$sql = "SELECT id,
						surname,
						firstname,
						stockid,
						manager,
						normalhours,
						userid,
						email
				FROM employees
				WHERE employees.id='" . $SelectedEmployee . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['Surname'] = $myrow['surname'];
		$_POST['FirstName'] = $myrow['firstname'];
		$_POST['StockID'] = $myrow['stockid'];
		$_POST['NormalHours'] = $myrow['normalhours'];
		$_POST['Manager'] = $myrow['manager'];
		$_POST['UserID'] = $myrow['userid'];
		$_POST['Email'] = $myrow['email'];
		
		echo '<input type="hidden" name="SelectedEmployee" value="' . $SelectedEmployee . '" />';
		
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Employee details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Employee Code') . ':</td>
				<td>' . $SelectedEmployee . '</td>
			</tr>';
	} else {//end of if $SelectedEmployee only do the else when a new record is being entered
		if(!isset($_POST['LocCode'])) {
			$_POST['SelectedEmployee'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Employee details') . '</h3></th>
				</tr>';
	}
	if(!isset($_POST['Surname'])) {
		$_POST['Surname'] = '';
	}
	if(!isset($_POST['FirstName'])) {
		$_POST['FirstName'] = '';
	}
	if(!isset($_POST['StockID'])) {
		$_POST['StockID'] = ' ';
	}
	if(!isset($_POST['NormalHours'])) {
		$_POST['NormalHours'] = '40';
	}
	if(!isset($_POST['Manager'])) {
		$_POST['Manager'] = '';
	}
	if(!isset($_POST['UserID'])) {
		$_POST['UserID'] = '';
	}
	if(!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}
	
	echo '<tr>
			<td><label for="FirstName">' . _('First Name') . ':' . '</label></td>
			<td><input type="text" name="FirstName" required="required" value="' . $_POST['FirstName'] . '" title="' . _('Enter the employee\'s first name') . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td><label for="Surname">' . _('Surname') . ':' . '</label></td>
			<td><input type="text" name="Surname" required="required" value="'. $_POST['Surname'] . '" title="' . _('Enter the employee\'s surname') . '" namesize="21" maxlength="20" /></td>
		</tr>
		
		<tr>
			<td><label for="StockID">' . _('Labour Type') . ':</label></td>
			<td><select name="StockID" />';

	$LabourTypeItemsResult = DB_query("SELECT stockid, description FROM
										stockmaster INNER JOIN stockcategory
											ON stockmaster.categoryid = stockcategory.categoryid
										WHERE stockcategory.stocktype='L'
										ORDER BY stockid");
	while ($myrow=DB_fetch_array($LabourTypeItemsResult)) {
		if($_POST['StockID']==$myrow['stockid']) {
			echo '<option selected="selected" value="' , $myrow['stockid'] , '">' , $myrow['description'] , '</option>';
		} else {
			echo '<option value="' , $myrow['stockid'] . '">' , $myrow['description'] , '</option>';
		}
	}

	echo '</select></td>
		</tr>
		<tr title="', _('The email address should be an email format such as adm@weberp.org'), '">
			<td><label for="Email">', _('Email'), ':</label></td>
			<td><input id="Email" maxlength="55" name="Email" size="31" type="email" value="', $_POST['Email'], '" /></td>
		</tr>
		<tr>
			<td><label for="NormalHours">' . _('Normal Weekly Hours') . ':' . '</label></td>
			<td><input class="number" type="text" name="NormalHours" value="' , $_POST['NormalHours'] , '" title="' , _('Enter the employee\'s normal hours per week') , '" size="3" maxlength="2" /></td>
		</tr>
		<tr>
			<td><label for="Manager">' , _('Manager') , ':' , '</label></td>
			<td><select name="Manager" />';

	$ManagersResult = DB_query("SELECT id, CONCAT(firstname, ' ', surname) AS managername
								FROM employees
								WHERE id != '" . $SelectedEmployee . "' 
								ORDER BY surname");
	if($_POST['Manager']==''){
		echo '<option selected="selected" value="0">' , _('Not Managed') , '</option>';
	} else {
		echo '<option value="0">' , _('Not Managed') , '</option>';
	}
	while ($myrow=DB_fetch_array($ManagersResult)) {
		if($_POST['Manager']==$myrow['id']) {
			echo '<option selected="selected" value="' , $myrow['id'] , '">' , $myrow['managername'] , '</option>';
		} else {
			echo '<option value="' , $myrow['id'] , '">' , $myrow['managername'] , '</option>';
		}
	}

	echo '</select></td>
		</tr>
		<tr>
			<td>' , _('webERP User') , ':' , '</td>
			<td><select name="UserID" title="' , _('Select the employee\'s system user account so when the user logs in to enter a time sheet the system knows the employee record to use') , '"/>';
	if($_POST['UserID']==''){
		echo '<option selected="selected" value="">' , _('Not a webERP User') , '</option>';
	} else {
		echo '<option value="">' , _('Not a webERP User') , '</option>';
	}
	$UsersResult = DB_query("SELECT userid, realname FROM www_users");
	while ($myrow=DB_fetch_array($UsersResult)) {
		if($_POST['UserID']==$myrow['userid']) {
			echo '<option selected="selected" value="' . $myrow['userid'] . '">' . $myrow['realname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['userid'] . '">' . $myrow['realname'] . '</option>';
		}
	}

	
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' , _('Enter Information') , '" />
		</div>
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>