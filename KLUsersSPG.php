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
$SQL = "SELECT secroleid,
				secrolename
		FROM securityroles
		WHERE secroleid = 17 OR secroleid = 22
		ORDER BY secrolename";

$Sec_Result = DB_query($SQL);
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

	$SQL = "SELECT cashsalecustomer,
					locationname
			FROM locations 
			WHERE loccode = '".$_POST['DefaultLocation']."'";
	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)){
		$CustomerID = $MyRow['cashsalecustomer'];
		$BranchCode = $MyRow['cashsalecustomer'];
		$LocationName = $MyRow['locationname'];
	}

	$SQL = "SELECT salesmanname
			FROM salesman 
			WHERE salesmancode = '".$_POST['Salesman']."'";
	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)){
		$SalesmanName = $MyRow['salesmanname'];
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
		$Result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if (DB_num_rows($Result)==1){
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

		$SQL = "UPDATE www_users SET realname='" . $RealName . "',
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

		$SQL = "INSERT INTO www_users (userid,
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
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg);

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

	$SQL = "SELECT userid,
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
	$Result = DB_query($SQL);

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

	while ($MyRow = DB_fetch_array($Result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		if ($MyRow['lastvisitdate']=='') {
			$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
		} else {
			$LastVisitDate = ConvertSQLDate($MyRow['lastvisitdate']);
		}

		if ($MyRow['blocked']=='0') {
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
				$MyRow['userid'],
				$MyRow['salesmanname'],
				$MyRow['locationname'],
				$LastVisitDate,
				$SecurityRoles[($MyRow['fullaccess'])],
				$Status,
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
				$MyRow['userid'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$MyRow['userid']);

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

	$SQL = "SELECT userid,
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

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['UserID'] = $MyRow['userid'];
	$_POST['Salesman'] = $MyRow['salesman'];
	$_POST['Access'] = $MyRow['fullaccess'];
	$_POST['DefaultLocation'] = $MyRow['defaultlocation'];
	$_POST['Blocked'] = $MyRow['blocked'];
	$_POST['Department'] = $MyRow['department'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
}

echo '<table class="selection">';

echo '<tr>
		<td>' . _('SPG') . ':</td>
		<td><select name="Salesman">';

$SQL = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmancode";
$Result = DB_query($SQL);
if ((isset($_POST['Salesman']) AND $_POST['Salesman']=='') OR !isset($_POST['Salesman'])){
	echo '<option selected="selected" value=""></option>';
} else {
	echo '<option value=""></option>';
}
while ($MyRow=DB_fetch_array($Result)){

	if (isset($_POST['Salesman']) AND $MyRow['salesmancode'] == $_POST['Salesman']){
		echo '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . ' -> ' . $MyRow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . ' -> ' . $MyRow['salesmanname'] . '</option>';
	}

}
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('KL Shop') . ':</td>
		<td><select name="DefaultLocation">';

$SQL = "SELECT loccode, 
				locationname
		FROM locations 
		WHERE typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . " 
		ORDER BY locationname";
$Result = DB_query($SQL);
if ((isset($_POST['DefaultLocation']) AND $_POST['DefaultLocation']=='') OR !isset($_POST['DefaultLocation'])){
	echo '<option selected="selected" value=""></option>';
} else {
	echo '<option value=""></option>';
}
while ($MyRow=DB_fetch_array($Result)){
	if (isset($_POST['DefaultLocation']) AND $MyRow['loccode'] == $_POST['DefaultLocation']){
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']  . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']  . '</option>';
	}
}

echo '</select></td>
	</tr>';

/* Allowed Department for Internal Requests */

echo '<tr>
		<td>' . _('KL Shop for Internal Requests') . ':</td>';

$SQL="SELECT departmentid,
			description
		FROM departments
		WHERE departmentid <> 1
		ORDER BY description";

$Result=DB_query($SQL);
echo '<td><select name="Department">';
if ((isset($_POST['Department']) AND $_POST['Department']=='0') OR !isset($_POST['Department'])){
	echo '<option selected="selected" value="0"></option>';
} else {
	echo '<option value=""></option>';
}
while ($MyRow=DB_fetch_array($Result)){
	if (isset($_POST['Department']) AND $MyRow['departmentid'] == $_POST['Department']){
		echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
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