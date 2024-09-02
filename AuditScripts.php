<?php

include('includes/session.php');
$Title = _('Audit Scripts');
include('includes/header.php');
include('includes/KLGeneralFunctions.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']= Date($_SESSION['DefaultDateFormat']);
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
$UserResult = DB_query("SELECT userid FROM www_users ORDER BY userid");
echo '<tr>
		<td>' .  _('User ID'). '</td>
		<td><select tabindex="3" name="SelectedUser">
			<option value="ALL">' . _('All') . '</option>';
while ($Users = DB_fetch_row($UserResult)) {
	if (isset($_POST['SelectedUser']) and $Users[0]==$_POST['SelectedUser']) {
		echo '<option selected="selected" value="' . $Users[0] . '">' . $Users[0] . '</option>';
	} else {
		echo '<option value="' . $Users[0] . '">' . $Users[0] . '</option>';
	}
}
echo '</select></td></tr>';

if(!isset($_POST['ContainingText'])){
	$_POST['ContainingText']='';
}
echo '<tr>
		<td>' . _('Containing text') . ':</td>
		<td><input type="text" tabindex="4" name="ContainingText" size="80" maxlength="80" value="'. $_POST['ContainingText'] . '" /></td>
	</tr>';

if (!isset($_POST['DetailedReport'])){
	$_POST['DetailedReport'] = 'No';
}
echo '<tr>
		<td>' . _('Summary or detailed report') . ':' . '</td>
		<td><select tabindex="5" name="DetailedReport">
			<option selected="selected" value="No">' . _('Summary Report') . '</option>
			<option value="Yes">' . _('Detailed Report') . '</option>
			</select>
		</td>
	</tr>';
	
echo '</table>
	<br />
	<div class="centre">
		<input tabindex="6" type="submit" name="View" value="' . _('View') . '" />
	</div>
	</div>
	</form>';

// View the audit trail
if (isset($_POST['View'])) {

	$FromDate = str_replace('/','-',FormatDateForSQL($_POST['FromDate']).' 00:00:00');
	$ToDate = str_replace('/','-',FormatDateForSQL($_POST['ToDate']).' 23:59:59');

	if (mb_strlen($ContainingText) > 0) {
	    $ContainingText = " AND scripttitle LIKE '%" . $ContainingText . "%' ";
	}else{
	    $ContainingText = "";
	}

	if ($_POST['SelectedUser'] == 'ALL') {
		$UserSql=" ";
	} else {
		$UserSql=" AND userid='".$_POST['SelectedUser']."'";
	}

	/**************************************************************
	SCRIPT USAGE
	***************************************************************/
	
	$sql="SELECT scripttitle, 
			COUNT(scripttitle) AS numscripts, 
			SUM(secondsrunning) AS sumseconds
		FROM auditscripts
		WHERE executiondate BETWEEN '".$FromDate."' AND '".$ToDate."'" 
		. $UserSql
		. $ContainingText
		.' GROUP BY scripttitle';

	$result = DB_query($sql);

	echo '<p class="page_title_text" align="center"><strong>' . 'General Script Usage' .'</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th class="ascending">' . _('Script') . '</th>
						<th class="ascending">' . _('# Executions') . '</th>
						<th class="ascending">' . _('Seconds Needed') . '</th>
						<th class="ascending">' . _('Secs/Execution') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$TotalScripts = 0;
	$TotalSeconds = 0;

	while ($myrow = DB_fetch_array($result)) {
		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$myrow['scripttitle'], 
				locale_number_format($myrow['numscripts'],0),
				locale_number_format($myrow['sumseconds'],5),
				locale_number_format($myrow['sumseconds']/$myrow['numscripts'],5)
				);
		$TotalScripts += $myrow['numscripts'];
		$TotalSeconds += $myrow['sumseconds'];
	}
	printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		'TOTALS', 
		locale_number_format($TotalScripts,0),
		locale_number_format($TotalSeconds,5),
		locale_number_format($TotalSeconds/$TotalScripts,5)
		);
	echo '</table></div>';
	
	/**************************************************************
	USERS USAGE
	***************************************************************/
	
	$sql="SELECT userid, 
			COUNT(scripttitle) AS numscripts, 
			SUM(secondsrunning) AS sumseconds
		FROM auditscripts
		WHERE executiondate BETWEEN '".$FromDate."' AND '".$ToDate."'" 
		. $UserSql
		. $ContainingText
		.' GROUP BY userid';

	$result = DB_query($sql);

	echo '<p class="page_title_text" align="center"><strong>' . 'General Users Usage' .'</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th class="ascending">' . _('User') . '</th>
						<th class="ascending">' . _('# Executions') . '</th>
						<th class="ascending">' . _('Seconds Needed') . '</th>
						<th class="ascending">' . _('Secs/Execution') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	$TotalScripts = 0;
	$TotalSeconds = 0;

	while ($myrow = DB_fetch_array($result)) {
		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$myrow['userid'], 
				locale_number_format($myrow['numscripts'],0),
				locale_number_format($myrow['sumseconds'],5),
				locale_number_format($myrow['sumseconds']/$myrow['numscripts'],5)
				);
		$TotalScripts += $myrow['numscripts'];
		$TotalSeconds += $myrow['sumseconds'];
	}
	printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		'TOTALS', 
		locale_number_format($TotalScripts,0),
		locale_number_format($TotalSeconds,5),
		''
		);
	echo '</table></div>';

	/**************************************************************
	QUERY DETAILED
	***************************************************************/
	if ($_POST['DetailedReport'] == "Yes"){
		$sql="SELECT executiondate,
				userid,
				secondsrunning,
				scripttitle
			FROM auditscripts
			WHERE executiondate BETWEEN '".$FromDate."' AND '".$ToDate."'" 
			. $UserSql
			. $ContainingText;

		$result = DB_query($sql);

		echo '<p class="page_title_text" align="center"><strong>' . 'Detailed Script usage' .'</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('Date/Time') . '</th>
							<th class="ascending">' . _('User') . '</th>
							<th class="ascending">' . _('Seconds') . '</th>
							<th class="ascending">' . _('Script') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;

		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					$myrow['executiondate'], 
					$myrow['userid'], 
					locale_number_format($myrow['secondsrunning'],5),
					$myrow['scripttitle']
					);
		}
		echo '</table></div>';
	}
}
include('includes/footer.php');

?>
