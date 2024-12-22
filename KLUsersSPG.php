<?php
/**************************************************************************
KL RICARD WWW_Users modified for KL use ONLY to maintain SPG accounts 
***************************************************************************/
/* $Id: WWW_Users.php 6807 2014-08-11 14:12:30Z agaluski $*/

// HARDCODED FOR KL 
$ModulesAllowed = "1,0,0,0,1,0,0,1,0,0,0,0,";
$PDFLanguage = 0;
$Language = "en_GB.utf8";
$ThemeSPG = "aguapop";
$PageSize = "A4";
$CanCreateTender = 0;
$Email = '';
$Phone = '';
$SupplierID = '';
$RealName = '';
$CustomerID = '';
$BranchCode = '';

include('includes/session.php');

$Title = _('KL SPG Username Maintenance');

$ViewTopic= 'GettingStarted';
$BookMark = 'UserMaintenance';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLEmails.php');
/* ASSIGN users to groups */
include ('includes/KLRoles.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<br />';

// Make an array of the security roles 17 and 22 ONLY
$sql = "SELECT secroleid,
				secrolename
		FROM securityroles
		WHERE secroleid = 17 OR secroleid = 22
		ORDER BY secrolename";

$Sec_Result = DB_query($sql);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	// Calculate fields
	$_POST['UserID'] = $_POST['Salesman'] . '-' . substr($_POST['DefaultLocation'], 3,2);

	$sql = "SELECT cashsalecustomer,
					locationname
			FROM locations 
			WHERE loccode = '".$_POST['DefaultLocation']."'";
	$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result)){
		$CustomerID = $myrow['cashsalecustomer'];
		$BranchCode = $myrow['cashsalecustomer'];
		$LocationName = $myrow['locationname'];
	}

	$sql = "SELECT salesmanname
			FROM salesman 
			WHERE salesmancode = '".$_POST['Salesman']."'";
	$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result)){
		$SalesmanName = $myrow['salesmanname'];
	}

	$SPGName = substr($SalesmanName,0,strpos($SalesmanName, '-') -1);
	$Shopname = substr($LocationName,11,strlen($LocationName)-11);
	$RealName = 'SPG'. $_POST['Salesman'] . '-' . $SPGName . ' in ' . substr($_POST['DefaultLocation'],-2);
	$Email = "spg". strtolower($_POST['Salesman']) . "@kapal-laut.com";
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	}

	if (!isset($SelectedUser)){
		/* check to ensure the user id is not already entered */
		$result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if (DB_num_rows($result)==1){
			$InputError =1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'),'error');
		}
	}


	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $RealName . "',
						customerid='" . $CustomerID ."',
						phone='" . $Phone ."',
						email='" . $Email ."',
						" . $UpdatePassword . "
						branchcode='" . $BranchCode . "',
						supplierid='" . $SupplierID . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $PageSize . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $CanCreateTender . "',
						theme='" . $ThemeSPG . "',
						language ='" . $Language . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $PDFLanguage . "',
						department='" . $_POST['Department'] . "'
					WHERE userid = '". $SelectedUser . "'";

		prnMsg( _('The selected user record has been updated'), 'success');
		
		KLSendEmail("SpgUsernameUpdated", "Silent", $SelectedUser, $_POST['Password'], $_POST['DefaultLocation'], $_SESSION['UserID'],$_POST['Blocked']);
		
	} elseif ($InputError !=1) {

		$LocationSql = "INSERT INTO locationusers (loccode,
													userid,
													canview,
													canupd
												) VALUES (
													'" . $_POST['DefaultLocation'] . "',
													'" . $_POST['UserID'] . "',
													1,
													1
												)";
		$ErrMsg = _('The default user locations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user locations and failed was');
		$Result = DB_query($LocationSql, $ErrMsg, $DbgMsg);

		// Give SPG rights ALSO to KANTO location (needed for internal requests)
		$LocationSql = "INSERT INTO locationusers (loccode,
													userid,
													canview,
													canupd
												) VALUES (
													" . CODE_KANTOR . ",
													'" . $_POST['UserID'] . "',
													1,
													0
												)";
		$Result = DB_query($LocationSql, $ErrMsg, $DbgMsg);

		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						supplierid,
						salesman,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						cancreatetender,
						defaultlocation,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						pdflanguage,
						department)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $RealName ."',
						'" . $CustomerID ."',
						'" . $BranchCode ."',
						'" . $SupplierID ."',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) ."',
						'" . $Phone . "',
						'" . $Email ."',
						'" . $PageSize ."',
						'" . $_POST['Access'] . "',
						'" . $CanCreateTender . "',
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $ThemeSPG . "',
						'" . $Language ."',
						'" . $PDFLanguage ."',
						'" . $_POST['Department'] . "')";
		prnMsg( _('A new user record has been inserted'), 'success' );

		KLSendEmail("SpgUsernameCreated", "Silent", $_POST['UserID'], $_POST['Password'], $_POST['DefaultLocation'], $_SESSION['UserID'],$_POST['Blocked']);

	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['Salesman']);
		unset($_POST['Password']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['Blocked']);
		unset($_POST['Department']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	DeleteWeberpUser($SelectedUser,$KL_SystemAdmin);
}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT userid,
					realname,
					phone,
					www_users.email,
					customerid,
					defaultlocation,
					locationname,
					branchcode,
					supplierid,
					salesman,
					salesmanname,
					lastvisitdate,
					fullaccess,
					cancreatetender,
					pagesize,
					theme,
					blocked,
					language
				FROM www_users, locations, salesman
				WHERE www_users.defaultlocation = locations.loccode
					AND www_users.salesman = salesman.salesmancode
					AND (fullaccess = 17 OR fullaccess = 22)
				ORDER BY salesman, defaultlocation";
	// Only SPG (17) or SPG-Support	(22)	
	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('User Login') . '</th>
			<th>' . _('SPG') . '</th>
			<th>' . _('Shop') . '</th>
			<th>' . _('Last Login') . '</th>
			<th>' . _('Access Level')  . '</th>
			<th>' . _('Status') . '</th>
		</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		if ($myrow['lastvisitdate']=='') {
			$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
		} else {
			$LastVisitDate = ConvertSQLDate($myrow['lastvisitdate']);
		}

		if ($myrow['blocked']=='0') {
			$Status = 'Open';
		} else {
			$Status = 'Blocked';
		}

		/*The SecurityHeadings array is defined in config.php */

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%s&amp;SelectedUser=%s">' . _('Edit') . '</a></td>
				<td><a href="%s&amp;SelectedUser=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user?') . '\');">' . _('Delete') . '</a></td>
				</tr>',
				$myrow['userid'],
				$myrow['salesmanname'],
				$myrow['locationname'],
				$LastVisitDate,
				$SecurityRoles[($myrow['fullaccess'])],
				$Status,
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
				$myrow['userid'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['userid']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing SPG') . '</a></div><br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			supplierid,
			salesman,
			pagesize,
			fullaccess,
			cancreatetender,
			defaultlocation,
			modulesallowed,
			blocked,
			theme,
			language,
			pdflanguage,
			department
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['Department'] = $myrow['department'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
}

echo '<table class="selection">';

echo '<tr>
		<td>' . _('SPG') . ':</td>
		<td><select name="Salesman">';

$sql = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmancode";
$result = DB_query($sql);
if ((isset($_POST['Salesman']) AND $_POST['Salesman']=='') OR !isset($_POST['Salesman'])){
	echo '<option selected="selected" value=""></option>';
} else {
	echo '<option value=""></option>';
}
while ($myrow=DB_fetch_array($result)){

	if (isset($_POST['Salesman']) AND $myrow['salesmancode'] == $_POST['Salesman']){
		echo '<option selected="selected" value="' . $myrow['salesmancode'] . '">' . $myrow['salesmancode'] . ' -> ' . $myrow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmancode'] . ' -> ' . $myrow['salesmanname'] . '</option>';
	}

}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('KL Shop') . ':</td>
		<td><select name="DefaultLocation">';

$sql = "SELECT loccode, 
				locationname
		FROM locations 
		WHERE typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " 
		ORDER BY locationname";
$result = DB_query($sql);
if ((isset($_POST['DefaultLocation']) AND $_POST['DefaultLocation']=='') OR !isset($_POST['DefaultLocation'])){
	echo '<option selected="selected" value=""></option>';
} else {
	echo '<option value=""></option>';
}
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['DefaultLocation']) AND $myrow['loccode'] == $_POST['DefaultLocation']){
		echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname']  . '</option>';
	} else {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname']  . '</option>';
	}
}

echo '</select></td>
	</tr>';

/* Allowed Department for Internal Requests */

echo '<tr>
		<td>' . _('KL Shop for Internal Requests') . ':</td>';

$sql="SELECT departmentid,
			description
		FROM departments
		WHERE departmentid <> 1
		ORDER BY description";

$result=DB_query($sql);
echo '<td><select name="Department">';
if ((isset($_POST['Department']) AND $_POST['Department']=='0') OR !isset($_POST['Department'])){
	echo '<option selected="selected" value="0"></option>';
} else {
	echo '<option value=""></option>';
}
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['Department']) AND $myrow['departmentid'] == $_POST['Department']){
		echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
	} else {
		echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
	}
}
echo '</select></td>
	</tr>';
	
	
if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
echo '<tr>
		<td>' . _('Password') . ':</td>
		<td><input type="password" pattern=".{5,}" name="Password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
	
echo '<tr>
		<td>' . _('Access Level') . ':</td>
		<td><select name="Access">';

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']){
		echo '<option selected="selected" value="' . $SecKey . '">' . $SecVal  . '</option>';
	} else {
		echo '<option value="' . $SecKey . '">' . $SecVal  . '</option>';
	}
}
echo '</select>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" /></td>

    </tr>';

/* Account status */

echo '<tr>
		<td>' . _('Account Status') . ':</td>
		<td><select required="required" name="Blocked">';
if ($_POST['Blocked']==0){
	echo '<option selected="selected" value="0">' . _('Open') . '</option>';
	echo '<option value="1">' . _('Blocked') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Blocked') . '</option>';
	echo '<option value="0">' . _('Open') . '</option>';
}
echo '</select></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.php');
?>