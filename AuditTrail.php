<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Audit Trail');
$ViewTopic = 'Setup';
$BookMark = 'AuditTrail';
include('includes/header.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail']));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate']= Date($_SESSION['DefaultDateFormat']);
}

if ((!(Is_Date($_POST['FromDate'])) OR (!Is_Date($_POST['ToDate']))) AND (isset($_POST['View']))) {
	prnMsg( __('Incorrect date format used, please re-enter'), 'error');
	unset($_POST['View']);
}
$ContainingText = '';
if (isset($_POST['ContainingText'])){
	$ContainingText = trim(mb_strtoupper($_POST['ContainingText']));
} elseif (isset($_GET['ContainingText'])){
	$ContainingText = trim(mb_strtoupper($_GET['ContainingText']));
}

// Get list of tables
$TableResult = DB_show_tables();

// Get list of users
$UserResult = DB_query("SELECT userid FROM www_users ORDER BY userid");

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', __('Report Criteria'), '</legend>';

echo '<field>
		<label for="FromDate">' .  __('From Date') . '</label>
		<input tabindex="1" type="date" name="FromDate" size="11" maxlength="10" autofocus="autofocus" required="required" value="' . FormatDateForSQL($_POST['FromDate']) . '" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')"/>
	</field>
	<field>
		<label for="ToDate">' .  __('To Date') . '</label>
		<input tabindex="2" type="date" name="ToDate" size="11" maxlength="10" required="required" value="' . FormatDateForSQL($_POST['ToDate']) . '" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')"/>
	</field>';

// Show user selections
echo '<field>
		<label for="SelectedUser">' .  __('User ID'). '</label>
		<select tabindex="3" name="SelectedUser">
			<option value="ALL">' . __('All') . '</option>';
while ($Users = DB_fetch_row($UserResult)) {
	if (isset($_POST['SelectedUser']) and $Users[0]==$_POST['SelectedUser']) {
		echo '<option selected="selected" value="' . $Users[0] . '">' . $Users[0] . '</option>';
	} else {
		echo '<option value="' . $Users[0] . '">' . $Users[0] . '</option>';
	}
}
echo '</select>
	</field>';

// Show table selections
echo '<field>
		<label for="SelectedTable">' .  __('Table '). '</label>
		<select tabindex="4" name="SelectedTable">
			<option value="ALL">' . __('All') . '</option>';
while ($Tables = DB_fetch_row($TableResult)) {
	if (isset($_POST['SelectedTable']) and $Tables[0]==$_POST['SelectedTable']) {
		echo '<option selected="selected" value="' . $Tables[0] . '">' . $Tables[0] . '</option>';
	} else {
		echo '<option value="' . $Tables[0] . '">' . $Tables[0] . '</option>';
	}
}
echo '</select>
	</field>';

if(!isset($_POST['ContainingText'])){
	$_POST['ContainingText']='';
}
// Show the text
echo '<field>
		<label for="ContainingText">' . __('Containing text') . ':</label>
		<input type="text" name="ContainingText" size="20" maxlength="20" value="'. $_POST['ContainingText'] . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input tabindex="5" type="submit" name="View" value="' . __('View') . '" />
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
		$FieldNameArray = explode(',', $SQLArray[0]);
		$_SESSION['SQLString']['fields'] = $FieldNameArray;
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
		if (isset($Assigment[1])) {$_SESSION['SQLString']['values'][0] = $Assigment[1];}
	}

	if (mb_strlen($ContainingText) > 0) {
	    $ContainingText = " AND querystring LIKE '%" . $ContainingText . "%' ";
	}else{
	    $ContainingText = "";
	}

	if ($_POST['SelectedUser'] == 'ALL') {
		$SQL="SELECT transactiondate,
				userid,
				querystring
			FROM audittrail
			WHERE transactiondate BETWEEN '". $FromDate."' AND '".$ToDate."'" . $ContainingText;
	} else {
		$SQL="SELECT transactiondate,
				userid,
				querystring
			FROM audittrail
			WHERE userid='".$_POST['SelectedUser']."'
			AND transactiondate BETWEEN '".$FromDate."' AND '".$ToDate."'" . $ContainingText;
	}
	$Result = DB_query($SQL);

	echo '<table border="0" width="98%" class="selection">
		<tr>
			<th>' . __('Date/Time') . '</th>
			<th>' . __('User') . '</th>
			<th>' . __('Type') . '</th>
			<th>' . __('Table') . '</th>
			<th>' . __('Field Name') . '</th>
			<th>' . __('Value') . '</th></tr>';
	while ($MyRow = DB_fetch_row($Result)) {
		if (Query_Type($MyRow[2]) == "INSERT") {
			InsertQueryInfo(str_replace("INSERT INTO",'',$MyRow[2]));
			$RowColour = '#a8ff90';
		}
		if (Query_Type($MyRow[2]) == "UPDATE") {
			UpdateQueryInfo(str_replace("UPDATE",'',$MyRow[2]));
			$RowColour = '#feff90';
		}
		if (Query_Type($MyRow[2]) == "DELETE") {
			DeleteQueryInfo(str_replace("DELETE FROM",'',$MyRow[2]));
			$RowColour = '#fe90bf';
		}

		if ((trim($_SESSION['SQLString']['table']) == $_POST['SelectedTable'])  ||
			($_POST['SelectedTable'] == 'ALL')) {
			if (!isset($_SESSION['SQLString']['values'])) {
				$_SESSION['SQLString']['values'][0]='';
			}
			echo '<tr style="background-color: '.$RowColour.'">
				<td>' . $MyRow[0] . '</td>
				<td>' . $MyRow[1] . '</td>
				<td>' . Query_Type($MyRow[2]) . '</td>
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
include('includes/footer.php');
