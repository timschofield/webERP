<?php

$PageSecurity=15;

include('includes/session.inc');

$title = _('Audit Trail');

include('includes/header.inc');

if ((!(Is_Date($_POST['From'])) || (!Is_Date($_POST['To']))) && (isset($_POST['View']))) {
	prnMsg( _('Incorrerct date format used, please re-enter'), error);
	unset($_POST['View']);
}

// Get list of tables
$tablesql = 'SHOW TABLES';
$tableresult = DB_query($tablesql,$db);

// Get list of users
$usersql = 'SELECT userid FROM www_users';
$userresult = DB_query($usersql,$db);

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';
echo '<CENTER><TABLE>';

echo '<TR><TD>'. _('From Date ').$_SESSION['DefaultDateFormat'] .'</TD><TD><INPUT TYPE=text name="From" value='
	.$_POST['From'].'></TD></TR>';
echo '<TR><TD>'. _('To Date ').$_SESSION['DefaultDateFormat'] .'</TD><TD><INPUT TYPE=text name="To" value='
	.$_POST['To'].'></TD></TR>';

// Show user selections
echo '<TR><TD>'. _('User ID '). '</TD><TD><SELECT name="SelectedUser">';
echo '<OPTION value=ALL>ALL';
while ($users = DB_fetch_row($userresult)) {
	if ($users[0] == $_POST['SelectedUser']) {
		echo '<OPTION SELECTED value=' . $users[0] . '>' . $users[0];
	} else {
		echo '<OPTION value=' . $users[0] . '>' . $users[0];
	}
}
echo '</SELECT></TD></TR>';

// Show table selections
echo '<TR><TD>'. _('Table '). '</TD><TD><SELECT name="SelectedTable">';
echo '<OPTION value=ALL>ALL';
while ($tables = DB_fetch_row($tableresult)) {
	if ($tables[0] == $_POST['SelectedTable']) {
		echo '<OPTION SELECTED value=' . $tables[0] . '>' . $tables[0];
	} else {
		echo '<OPTION value=' . $tables[0] . '>' . $tables[0];
	}
}
echo '</SELECT></TD></TR>';

echo "<TR><TD></TD><TD><INPUT TYPE=SUBMIT name=View value='" . _('View') . "'></TD></TR>";
echo '</TABLE></BR>';
echo '</CENTER></FORM>';

// View the audit trail
if (isset($_POST['View'])) {
	
	$fromdate = str_replace('/','-',FormatDateForSQL($_POST['From']).' 00:00:00');
	$todate = str_replace('/','-',FormatDateForSQL($_POST['To']).' 23:59:59');
	
	// Find the query type (insert/update/delete)
	function Query_Type($sqlstring) {
		$sqlarray = explode(" ", $sqlstring);
		return $sqlarray[0];
	}

	function InsertQueryInfo($sqlstring) {
		$sqlarray = explode("(", $sqlstring);
		$_SESSION['sqlstring']['table'] = $sqlarray[0];
		$sqlstring = str_replace(')','',$sqlstring);
		$sqlstring = str_replace('(','',$sqlstring);
		$sqlstring = str_replace($_SESSION['sqlstring']['table'],'',$sqlstring);
		$sqlarray = explode("VALUES", $sqlstring);
		$fieldnamearray = explode(",", $sqlarray[0]);
		$_SESSION['sqlstring']['fields'] = $fieldnamearray;
		$fieldvaluearray = explode(",", $sqlarray[1]);
		$_SESSION['sqlstring']['values'] = $fieldvaluearray;
	}

	function UpdateQueryInfo($sqlstring) {
		$sqlarray = explode("SET", $sqlstring);
		$_SESSION['sqlstring']['table'] = $sqlarray[0];
		$sqlstring = str_replace($_SESSION['sqlstring']['table'],'',$sqlstring);
		$sqlstring = str_replace('SET','',$sqlstring);
		$sqlstring = str_replace('WHERE',',',$sqlstring);
		$sqlstring = str_replace('AND',',',$sqlstring);
		$fieldarray = explode(",", $sqlstring);
		for ($i=0; $i<sizeof($fieldarray); $i++) {
			$assignment = explode("=", $fieldarray[$i]);
			$_SESSION['sqlstring']['fields'][$i] = $assignment[0];
			$_SESSION['sqlstring']['values'][$i] = $assignment[1];
		}
	}

	function DeleteQueryInfo($sqlstring) {
		$sqlarray = explode("WHERE", $sqlstring);
		$_SESSION['sqlstring']['table'] = $sqlarray[0];
		$sqlstring = trim(str_replace($sqlarray[0], '', $sqlstring));
		$sqlstring = trim(str_replace("DELETE", '', $sqlstring));
		$sqlstring = trim(str_replace("FROM", '', $sqlstring));
		$sqlstring = trim(str_replace("WHERE", '', $sqlstring));
		$assignment = explode("=", $sqlstring);
		$_SESSION['sqlstring']['fields'][0] = $assignment[0];
		$_SESSION['sqlstring']['values'][0] = $assignment[1];
	}

	if ($_POST['SelectedUser'] == "ALL") {
		$sql="SELECT transactiondate, userid, querystring FROM audittrail WHERE transactiondate between '".
			$fromdate."' AND '".$todate."'";
	} else {
		$sql="SELECT transactiondate, userid, querystring FROM audittrail WHERE userid='".$_POST['SelectedUser']."' AND ".
		  "transactiondate between '".$fromdate."' AND '".$todate."'";
	}
	$result = DB_query($sql,$db);

	echo '<CENTER><TABLE BORDER=0>';
	echo '<TR bgcolor=e6e6e6><TD>Date/Time</TD><TD>User</TD><TD>Type' .
				'</TD><TD>Table</TD><TD>Field Name</TD><TD>Value</TD></TR>';
	while ($myrow = DB_fetch_row($result)) {
		if (Query_Type($myrow[2]) == "INSERT") {
			InsertQueryInfo(str_replace("INSERT INTO",'',$myrow[2]));
			$rowcolour = 'a8ff90';
		}
		if (Query_Type($myrow[2]) == "UPDATE") {
			UpdateQueryInfo(str_replace("UPDATE",'',$myrow[2]));
			$rowcolour = 'feff90';
		}
		if (Query_Type($myrow[2]) == "DELETE") {
			DeleteQueryInfo(str_replace("DELETE FROM",'',$myrow[2]));
			$rowcolour = 'fe90bf';
		}

		if ((trim($_SESSION['sqlstring']['table']) == $_POST['SelectedTable'])  ||
		 ($_POST['SelectedTable'] == 'ALL')) {
			echo '<TR bgcolor='.$rowcolour.'><TD>'.$myrow[0].'</TD><TD>'.$myrow[1].'</TD><TD>'.Query_Type($myrow[2]).
				'</TD><TD>'.$_SESSION['sqlstring']['table'].'</TD><TD>'
					.$_SESSION['sqlstring']['fields'][0].'</TD><TD>'.
			  		trim(str_replace("'","",$_SESSION['sqlstring']['values'][0])).'</TD></TR>';
			for ($i=1; $i<sizeof($_SESSION['sqlstring']['fields']); $i++) {
				if ((trim(str_replace("'","",$_SESSION['sqlstring']['values'][$i])) != "") &
		  	 (trim($_SESSION['sqlstring']['fields'][$i]) != "password") &
		   		(trim($_SESSION['sqlstring']['fields'][$i]) != "www_users.password")) {
					echo '<TR bgcolor='.$rowcolour.'>';
					echo '<TD></TD><TD></TD><TD></TD><TD></TD>';
					echo '<TD>'.$_SESSION['sqlstring']['fields'][$i].'</TD>
						<TD>'.trim(str_replace("'","",$_SESSION['sqlstring']['values'][$i])).'</TD>';
					echo '</TR>';
				}
			}
			echo '<TR bgcolor=black><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD></TR>';
		}
		unset($_SESSION['sqlstring']);
	}
	echo '</TABLE></CENTER>';
}
include('includes/footer.inc');

?>