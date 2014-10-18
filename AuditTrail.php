<?php

/* $Id: AuditTrail.php 6310 2013-08-29 10:42:50Z daintree $ */

include('includes/session.inc');

$Title = _('Audit Trail');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

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

if (isset($_POST['ContainingText'])){
	$ContainingText = trim(mb_strtoupper($_POST['ContainingText']));
} elseif (isset($_GET['ContainingText'])){
	$ContainingText = trim(mb_strtoupper($_GET['ContainingText']));
}

// Get list of tables
$TableResult = DB_show_tables($db);

// Get list of users
$UserResult = DB_query("SELECT userid FROM www_users ORDER BY userid",$db);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

echo '<tr>
		<td>' .  _('From Date') . ' ' . $_SESSION['DefaultDateFormat']  . '</td>
		<td><input tabindex="1" type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="FromDate" size="11" maxlength="10" autofocus="autofocus" required="required" value="' .$_POST['FromDate']. '" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')"/></td>
	</tr>
	<tr>
		<td>' .  _('To Date') . ' ' . $_SESSION['DefaultDateFormat']  . '</td>
		<td><input tabindex="2" type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ToDate" size="11" maxlength="10" required="required" value="' . $_POST['ToDate'] . '" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')"/></td>
	</tr>';

// Show user selections
echo '<tr>
		<td>' .  _('User ID'). '</td>
		<td><select tabindex="3" name="SelectedUser">
			<option value="ALL">' . _('All') . '</option>';
while ($Users = DB_fetch_row($UserResult)) {
	if (isset($_POST['SelectedUser']) and $users[0]==$_POST['SelectedUser']) {
		echo '<option selected="selected" value="' . $Users[0] . '">' . $Users[0] . '</option>';
	} else {
		echo '<option value="' . $Users[0] . '">' . $Users[0] . '</option>';
	}
}
echo '</select></td></tr>';

// Show table selections
echo '<tr>
		<td>' .  _('Table '). '</td>
		<td><select tabindex="4" name="SelectedTable">
			<option value="ALL">' . _('All') . '</option>';
while ($Tables = DB_fetch_row($TableResult)) {
	if (isset($_POST['SelectedTable']) and $Tables[0]==$_POST['SelectedTable']) {
		echo '<option selected="selected" value="' . $Tables[0] . '">' . $Tables[0] . '</option>';
	} else {
		echo '<option value="' . $Tables[0] . '">' . $Tables[0] . '</option>';
	}
}
echo '</select></td></tr>';

if(!isset($_POST['ContainingText'])){
	$_POST['ContainingText']='';
}
// Show the text
echo '<tr>
		<td>' . _('Containing text') . ':</td>
		<td><input type="text" name="ContainingText" size="20" maxlength="20" value="'. $_POST['ContainingText'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="View" value="' . _('View') . '" />
	</div>
	</div>
	</form>';

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
		$FieldArray = preg_split("/[[:space:]]*([[:alnum:].]+[[:space:]]*=[[:space:]]*(?:'[^']*'|[[:digit:].]+))[[:space:]]*,/", $SQLString, 0, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		for ($i=0; $i<sizeof($FieldArray); $i++) {
			$Assigment = explode('=', $FieldArray[$i]);
			$_SESSION['SQLString']['fields'][$i] = $Assigment[0];
			if (sizeof($Assigment)>1) {
				$_SESSION['SQLString']['values'][$i] = $Assigment[1];
			}
		}
	}

	function DeleteQueryInfo($SQLString) {
		$SQLArray = explode("WHERE", $SQLString);
		$_SESSION['SQLString']['table'] = $SQLArray[0];
		$SQLString = trim(str_replace($SQLArray[0], '', $SQLString));
		$SQLString = trim(str_replace("DELETE", '', $SQLString));
		$SQLString = trim(str_replace("FROM", '', $SQLString));
		$SQLString = trim(str_replace("WHERE", '', $SQLString));
		$Assigment = explode('=', $SQLString);
		$_SESSION['SQLString']['fields'][0] = $Assigment[0];
		$_SESSION['SQLString']['values'][0] = $Assigment[1];
	}

	if (mb_strlen($ContainingText) > 0) {
	    $ContainingText = " AND querystring LIKE '%" . $ContainingText . "%' ";
	}else{
	    $ContainingText = "";
	}

	if ($_POST['SelectedUser'] == 'ALL') {
		$sql="SELECT transactiondate,
				userid,
				querystring
			FROM audittrail
			WHERE transactiondate BETWEEN '". $FromDate."' AND '".$ToDate."'" . $ContainingText;
	} else {
		$sql="SELECT transactiondate,
				userid,
				querystring
			FROM audittrail
			WHERE userid='".$_POST['SelectedUser']."'
			AND transactiondate BETWEEN '".$FromDate."' AND '".$ToDate."'" . $ContainingText;
	}
	$result = DB_query($sql,$db);

	echo '<table border="0" width="98%" class="selection">
		<tr>
			<th>' . _('Date/Time') . '</th>
			<th>' . _('User') . '</th>
			<th>' . _('Type') . '</th>
			<th>' . _('Table') . '</th>
			<th>' . _('Field Name') . '</th>
			<th>' . _('Value') . '</th></tr>';
	while ($myrow = DB_fetch_row($result)) {
		if (Query_Type($myrow[2]) == "INSERT") {
			InsertQueryInfo(str_replace("INSERT INTO",'',$myrow[2]));
			$RowColour = '#a8ff90';
		}
		if (Query_Type($myrow[2]) == "UPDATE") {
			UpdateQueryInfo(str_replace("UPDATE",'',$myrow[2]));
			$RowColour = '#feff90';
		}
		if (Query_Type($myrow[2]) == "DELETE") {
			DeleteQueryInfo(str_replace("DELETE FROM",'',$myrow[2]));
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
				<td>' . trim(str_replace("'","",$_SESSION['SQLString']['values'][0])) . '</td></tr>';
			for ($i=1; $i<sizeof($_SESSION['SQLString']['fields']); $i++) {
				if (isset($_SESSION['SQLString']['values'][$i]) and (trim(str_replace("'","",$_SESSION['SQLString']['values'][$i])) != "") &
				(trim($_SESSION['SQLString']['fields'][$i]) != 'password') &
				(trim($_SESSION['SQLString']['fields'][$i]) != 'www_users.password')) {
					echo '<tr style="background-color:' . $RowColour . '">';
					echo '<td></td>
						<td></td>
						<td></td>
						<td></td>';
					echo '<td>' . $_SESSION['SQLString']['fields'][$i] . '</td>
						<td>' .  trim(str_replace("'","",$_SESSION['SQLString']['values'][$i]))  . '</td>';
					echo '</tr>';
				}
			}
			echo '<tr style="background-color:black"> <td colspan="6"></td> </tr>';
		}
		unset($_SESSION['SQLString']);
	}
	echo '</table>';
}
include('includes/footer.inc');

?>