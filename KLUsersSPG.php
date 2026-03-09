<?php
/**************************************************************************
KL RICARD WWW_Users modified for KL use ONLY to maintain SPG accounts 
***************************************************************************/

require(__DIR__ . '/includes/session.php');

$Title = __('KL SPG User Maintenance');
$ViewTopic= 'GettingStarted';
$BookMark = 'UserMaintenance';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLEmails.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<br />';
	
// HARDCODED FOR KL SPG
$ModulesAllowed = "1,0,0,0,1,0,0,1,0,0,0,0,";
$PDFLanguage = 0;
$Language = "en_GB.utf8";
$ThemeSPG = "aguapop";
$PageSize = "A4";
$CanCreateTender = 0;
$Email = '';
$Phone = '';
$SupplierID = '';
$SPGFullName = '';
$CustomerID = '';
$BranchCode = '';
$Timeout = 30;

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
	$_POST['UserID'] = 'SPG-' . $_POST['Salesman']; // one only username per SPG

	$SQL = "SELECT cashsalecustomer,
					locationname,
					departmentid
			FROM locations 
			WHERE loccode = '".$_POST['DefaultLocation']."'";
	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)){
		$CustomerID = $MyRow['cashsalecustomer'];
		$BranchCode = $MyRow['cashsalecustomer'];
		$DepartmentID = $MyRow['departmentid'];
		$LocationName = $MyRow['locationname'];
	}

	$SQL = "SELECT salesmanname
			FROM salesman 
			WHERE salesmancode = '".$_POST['Salesman']."'";
	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)){
		$SalesmanName = $MyRow['salesmanname'];
	}

	$SPGCodeName = trim(substr($SalesmanName,0,strpos($SalesmanName, '-') -1));
	$SPGFullName = trim(substr($SalesmanName, strpos($SalesmanName, '-') + 1));
	$Email = "spg". strtolower($_POST['Salesman']) . "@kapal-laut.com";
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(__('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(__('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . __('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(__('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(__('The password cannot contain the user id'),'error');
	}

	if (!isset($SelectedUser)){
		/* check to ensure the user id is not already entered */
		$Result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if (DB_num_rows($Result)==1){
			$InputError =1;
			prnMsg(__('The user ID') . ' ' . $_POST['UserID'] . ' ' . __('already exists and cannot be used again'),'error');
		}
	}


	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}



		$SQL = "UPDATE www_users SET realname='" . $SPGFullName . "',
						customerid='" . $CustomerID ."',
						phone='" . $Phone ."',
						email='" . $Email ."',
						" . $UpdatePassword . "
						branchcode='" . $BranchCode . "',
						supplierid='" . $SupplierID . "',
						salesman='" . $_POST['Salesman'] . "',
						fullaccess='" . $_POST['Access'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						blocked='" . $_POST['Blocked'] . "',
						department='" . $DepartmentID . "'
					WHERE userid = '". $SelectedUser . "'";

		AssignLocationsToSPG($_POST['UserID'], $_POST['DefaultLocation']);

		prnMsg( __('The selected user record has been updated'), 'success');
		KLSendEmail("SpgUsernameUpdated", "Silent", $SelectedUser, $_POST['Password'], $_POST['DefaultLocation'], $_SESSION['UserID'],$_POST['Blocked']);
		
	} elseif ($InputError !=1) {

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
						timeout,
						department)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $SPGFullName ."',
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
						'" . $Timeout ."',
						'" . $DepartmentID . "')";

		AssignLocationsToSPG($_POST['UserID'], $_POST['DefaultLocation']);

		prnMsg( __('A new user record has been inserted'), 'success' );
		KLSendEmail("SpgUsernameCreated", "Silent", $_POST['UserID'], $_POST['Password'], $_POST['DefaultLocation'], $_SESSION['UserID'],$_POST['Blocked']);

	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = __('The user update could not be processed because');
		$Result = DB_query($SQL,$ErrMsg,'');

		unset($_POST['UserID']);
		unset($_POST['Salesman']);
		unset($_POST['Password']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['Blocked']);
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
	echo '<thead>';
	echo '<tr>
			<th class="SortedColumn">' . __('SPG Username') . '</th>
			<th class="SortedColumn">' . __('SPG') . '</th>
			<th class="SortedColumn">' . __('Shop') . '</th>
			<th class="SortedColumn">' . __('Last Login') . '</th>
			<th class="SortedColumn">' . __('Access Level')  . '</th>
			<th class="SortedColumn">' . __('Status') . '</th>
		</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		if ($MyRow['lastvisitdate']=='') {
			$LastVisitDate = 'Never';
		} else {
			$LastVisitDate = ConvertSQLDate($MyRow['lastvisitdate']);
		}

		if ($MyRow['blocked']=='0') {
			$Status = 'Open';
		} else {
			$Status = 'Blocked';
		}

		/*The SecurityHeadings array is defined in config.php */

		echo '<tr class="striped_row">
				<td>' . $MyRow['userid'] . '</td>
				<td>' . $MyRow['salesmanname'] . '</td>
				<td>' . $MyRow['locationname'] . '</td>
				<td>' . $LastVisitDate . '</td>
				<td>' . $SecurityRoles[($MyRow['fullaccess'])] . '</td>
				<td>' . $Status . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?&amp;SelectedUser=' . $MyRow['userid'] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedUser=' . $MyRow['userid'] . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this user?') . '\');">' . __('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . __('Review Existing SPG') . '</a></div><br />';
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
			fullaccess,
			defaultlocation,
			blocked
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['UserID'] = $MyRow['userid'];
	$_POST['RealName'] = $MyRow['realname'];
	$_POST['Salesman'] = $MyRow['salesman'];
	$_POST['Access'] = $MyRow['fullaccess'];
	$_POST['DefaultLocation'] = $MyRow['defaultlocation'];
	$_POST['Blocked'] = $MyRow['blocked'];

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="RealName" value="' . $_POST['RealName'] . '" />';
	echo '<input type="hidden" name="Salesman" value="' . $_POST['Salesman'] . '" />';
}

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['Salesman'])) {
	$_POST['Salesman']='';
	$SalesmanName = '';
}
$SQL = "SELECT salesmanname
		FROM salesman 
		WHERE salesmancode = '".$_POST['Salesman']."'";
$Result = DB_query($SQL);
while ($MyRow=DB_fetch_array($Result)){
	$SalesmanName = $MyRow['salesmanname'];
}

$SPGCodeName = trim(substr($SalesmanName,0,strpos($SalesmanName, '-') -1));
$SPGFullName = trim(substr($SalesmanName, strpos($SalesmanName, '-') + 1));

if (isset($SelectedUser)) {	
	echo '<fieldset><legend>'  . $SelectedUser. '  webERP User Details' . '</legend>';
	echo FixedField('Salesman', $_POST['Salesman'], 'SPG Code', ''); 
	echo FixedField('RealName', $SPGFullName, 'SPG Name', ''); 
} else {
	echo '<fieldset><legend>' . __('New SPG webERP User') . '</legend>';
	echo FieldToSelectOneSalesPerson('Salesman', isset($_POST['Salesman']) ? $_POST['Salesman'] : '', __('SPG'), '', 'CURRENT', false, 1, true, true);
}
echo FieldToSelectOnePassword('Password', $_POST['Password'], 22, 20, __('Password'), '', 2, false, true);
echo FieldToSelectOneLocation('DefaultLocation', isset($_POST['DefaultLocation']) ? $_POST['DefaultLocation'] : '', __('KL Shop'), '', 'BALISHOPS', 3, true, false);
echo FieldToSelectOneEntryFromArray($SecurityRoles, 'Access', isset($_POST['Access']) ? $_POST['Access'] : '', __('Access Level'));
echo FieldToSelectFromTwoOptions('0', __('Open'),
								'1', __('Blocked'), 'Blocked', 
								isset($_POST['Blocked']) ? $_POST['Blocked'] : '0', __('Account Status'), '', '', 5, true, false);

echo '</fieldset>';

echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'" />';

echo OneButtonCenteredForm('submit', __('Enter Information'));

echo '</div>
	</form>';

include(__DIR__ . '/includes/footer.php');

function AssignLocationsToSPG($UserID, $LocationCode) {

	// Delete all previous locations for this user
	$LocationSql = "DELETE FROM locationusers 
					WHERE userid = '" . $_POST['UserID'] . "'";

	$ErrMsg = __('The default user locations could not be deleted because');
	DB_query($LocationSql, $ErrMsg, '');

	// Assign the default location to the user
	$LocationSql = "INSERT INTO locationusers (loccode,
		userid,
		canview,
		canupd
	) VALUES (
		'" . $LocationCode . "',
		'" . $UserID . "',
		1,
		1
	)";
	$ErrMsg = __('The default user locations could not be processed because');
	DB_query($LocationSql, $ErrMsg, '');

	// Give SPG rights ALSO to KANTO location (needed for internal requests)
	$LocationSql = "INSERT INTO locationusers (loccode,
		userid,
		canview,
		canupd
	) VALUES (
		'" . CODE_KANTOR . "',
		'" . $UserID . "',
		1,
		0
	)";
	DB_query($LocationSql, $ErrMsg);
}
