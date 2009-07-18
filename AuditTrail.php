<?php

$PageSecurity=15;

include('includes/session.inc');

$title = _('Audit Trail');

include('includes/header.inc');

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail']));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate']= Date($_SESSION['DefaultDateFormat']);
}

if ((!(Is_Date($_POST['FromDate'])) OR (!Is_Date($_POST['ToDate']))) AND (isset($_POST['View']))) {
	prnMsg( _('Incorrect date format used, please re-enter'), error);
	unset($_POST['View']);
}

// Get list of tables
$tableresult = DB_show_tables($db);

// Get list of users
$userresult = DB_query('SELECT userid FROM www_users',$db);

echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
echo '<table>';

echo '<tr><td>'. _('From Date') . ' ' . $_SESSION['DefaultDateFormat'] .'</td>
	<td><input tabindex="1" type=text class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="FromDate" size="11" maxlength="10" value=' .$_POST['FromDate'].'></td></tr>';
echo '<tr><td>'. _('To Date') . ' ' . $_SESSION['DefaultDateFormat'] .'</td>
	<td><input tabindex="2" type=text class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="ToDate" size="11" maxlength="10" value=' . $_POST['ToDate'] . '></td></tr>';

// Show user selections
echo '<tr><td>'. _('User ID'). '</td>
		<td><select tabindex="3" name="SelectedUser">';
echo '<option value=ALL>ALL';
while ($users = DB_fetch_row($userresult)) {
	if (isset($_POST['SelectedUser']) and $users[0]==$_POST['SelectedUser']) {
		echo '<option selected value=' . $users[0] . '>' . $users[0];
	} else {
		echo '<option value=' . $users[0] . '>' . $users[0];
	}
}
echo '</select></td></tr>';

// Show table selections
echo '<tr><td>'. _('Table '). '</td><td><select tabindex="4" name="SelectedTable">';
echo '<option value=ALL>ALL';
while ($tables = DB_fetch_row($tableresult)) {
	if (isset($_POST['SelectedTable']) and $tables[0]==$_POST['SelectedTable']) {
		echo '<option selected value=' . $tables[0] . '>' . $tables[0];
	} else {
		echo '<option value=' . $tables[0] . '>' . $tables[0];
	}
}
echo '</select></td></tr>';

echo "<tr><td></td><td><input tabindex='5' type=submit name=View value='" . _('View') . "'></td></tr>";
echo '</table></BR>';
echo '</form>';

// View the audit trail
if (isset($_POST['View'])) {
	
	$FromDate = str_replace('/','-',FormatDateForSQL($_POST['FromDate']).' 00:00:00');
	$ToDate = str_replace('/','-',FormatDateForSQL($_POST['ToDate']).' 23:59:59');
	
	// Find the query type (insert/update/delete)
	function Query_Type($SQLString) {
		$SQLArray = explode(" ", $SQLString);
		return $SQLArray[0];
	}

	function InsertQueryInfo($SQLString) {
		$SQLArray = explode('(', $SQLString);
		$_SESSION['SQLString']['table'] = $SQLArray[0];
		$SQLString = str_replace(')','',$SQLString);
		$SQLString = str_replace('(','',$SQLString);
		$SQLString = str_replace($_SESSION['SQLString']['table'],'',$SQLString);
		$SQLArray = explode('VALUES', $SQLString);
		$fieldnamearray = explode(',', $SQLArray[0]);
		$_SESSION['SQLString']['fields'] = $fieldnamearray;
		if (isset($SQLArray[1])) {
			$FieldValueArray = preg_split("/[[:space:]]*('[^']*'|[[:digit:].]+),/", $SQLArray[1], 0, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			$_SESSION['SQLString']['values'] = $FieldValueArray;
		}
	}

	function UpdateQueryInfo($SQLString) {
		$SQLArray = explode('SET', $SQLString);
		$_SESSION['SQLString']['table'] = $SQLArray[0];
		$SQLString = str_replace($_SESSION['SQLString']['table'],'',$SQLString);
		$SQLString = str_replace('SET','',$SQLString);
		$SQLString = str_replace('WHERE',',',$SQLString);
		$SQLString = str_replace('AND',',',$SQLString);
		$FieldArray = preg_split("/[[:space:]]*([[:alnum:].]+[[:space:]]*=[[:space:]]*(?:'[^']*'|[[:digit:].]+))[[:space:]]*,/", $SQLString, 0, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);		for ($i=0; $i<sizeof($FieldArray); $i++) {
			$Assigment = explode('=', $FieldArray[$i]);
			$_SESSION['SQLString']['fields'][$i] = $Assigment[0];
			if (sizeof($Assigment)>1) {
				$_SESSION['SQLString']['values'][$i] = $Assigment[1];
			}
		}
	}

	function DeleteQueryInfo($SQLString) {
		$SQLArray = explode('WHERE', $SQLString);
		$_SESSION['SQLString']['table'] = $SQLArray[0];
		$SQLString = trim(str_replace($SQLArray[0], '', $SQLString));
		$SQLString = trim(str_replace('DELETE', '', $SQLString));
		$SQLString = trim(str_replace('FROM', '', $SQLString));
		$SQLString = trim(str_replace('WHERE', '', $SQLString));
		$Assigment = explode('=', $SQLString);
		$_SESSION['SQLString']['fields'][0] = $Assigment[0];
		$_SESSION['SQLString']['values'][0] = $Assigment[1];
	}

	if ($_POST['SelectedUser'] == 'ALL') {
		$sql="SELECT transactiondate, 
				userid, 
				querystring 
			FROM audittrail 
			WHERE transactiondate 
			BETWEEN '". $FromDate."' AND '".$ToDate."'";
	} else {
		$sql="SELECT transactiondate, 
				userid, 
				querystring 
			FROM audittrail 
			WHERE userid='".$_POST['SelectedUser']."' 
			AND transactiondate BETWEEN '".$FromDate."' AND '".$ToDate."'";
	}
	$result = DB_query($sql,$db);

	echo '<table border=0 width="100%">';
	echo '<tr><th>' . _('Date/Time') . '</th>
				<th>' . _('User') . '</th>
				<th>' . _('Type') . '</th>
				<th>' . _('Table') . '</th>
				<th>' . _('Field Name') . '</th>
				<th>' . _('Value') . '</th></tr>';
	while ($myrow = DB_fetch_row($result)) {
		if (Query_Type($myrow[2]) == 'INSERT') {
			InsertQueryInfo(str_replace("INSERT INTO",'',$myrow[2]));
			$RowColour = '#a8ff90';
		}
		if (Query_Type($myrow[2]) == 'UPDATE') {
			UpdateQueryInfo(str_replace('UPDATE','',$myrow[2]));
			$RowColour = '#feff90';
		}
		if (Query_Type($myrow[2]) == 'DELETE') {
			DeleteQueryInfo(str_replace('DELETE FROM','',$myrow[2]));
			$RowColour = '#fe90bf';
		}

		if ((trim($_SESSION['SQLString']['table']) == $_POST['SelectedTable'])  ||
			($_POST['SelectedTable'] == 'ALL')) {
			if (!isset($_SESSION['SQLString']['values'])) {
				$_SESSION['SQLString']['values'][0]='';
			}
			echo '<tr style="background-color: '.$RowColour.'">
				<td>' . $myrow[0] . '</td>
				<td>' . $myrow[1] . '</td>
				<td>' . Query_Type($myrow[2]) . '</td>
				<td>' . $_SESSION['SQLString']['table'] . '</td>
				<td>' . $_SESSION['SQLString']['fields'][0] . '</td>
				<td>' . htmlentities(trim(str_replace("'","",$_SESSION['SQLString']['values'][0]))) . '</td></tr>';
			for ($i=1; $i<sizeof($_SESSION['SQLString']['fields']); $i++) {
				if (isset($_SESSION['SQLString']['values'][$i]) and (trim(str_replace("'","",$_SESSION['SQLString']['values'][$i])) != "") &
				(trim($_SESSION['SQLString']['fields'][$i]) != 'password') &
				(trim($_SESSION['SQLString']['fields'][$i]) != "www_users.password")) {
					echo '<tr bgcolor='.$RowColour.'>';
					echo '<td></td>
						<td></td>
						<td></td>
						<td></td>';
					echo '<td>'.$_SESSION['SQLString']['fields'][$i].'</td>
						<td>'.htmlentities(trim(str_replace("'","",$_SESSION['SQLString']['values'][$i])), ENT_QUOTES, _('ISO-8859-1')).'</td>';
					echo '</tr>';
				}
			}
			echo '<tr bgcolor=black><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
		}
		unset($_SESSION['SQLString']);
	}
	echo '</table>';
}
include('includes/footer.inc');

?>