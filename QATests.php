<?php

require(__DIR__ . '/includes/session.php');

$Title = __('QA Tests Maintenance');
$ViewTopic = 'QualityAssurance';
$BookMark = 'QA_Tests';
include('includes/header.php');

if (isset($_GET['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_GET['SelectedQATest']);
} elseif(isset($_POST['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_POST['SelectedQATest']);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (mb_strlen($_POST['QATestName']) > 50) {
		$InputError = 1;
		prnMsg(__('The QA Test name must be fifty characters or less long'),'error');
		$Errors[$i] = 'QATestName';
		$i++;
	}

	if (mb_strlen($_POST['Type']) =='') {
		$InputError = 1;
		prnMsg(__('The Type must not be blank'),'error');
		$Errors[$i] = 'Type';
		$i++;
	}
	$SQL= "SELECT COUNT(*) FROM qatests WHERE qatests.name='".$_POST['QATestName']."'
										AND qatests.testid <> '" .$SelectedQATest. "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		$InputError = 1;
		prnMsg(__('The QA Test name already exists'),'error');
		$Errors[$i] = 'QATestName';
		$i++;
	}

	if (isset($SelectedQATest) AND $InputError !=1) {

		/*SelectedQATest could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE qatests SET name='" . $_POST['QATestName'] . "',
									method='" . $_POST['Method'] . "',
									groupby='" . $_POST['GroupBy'] . "',
									units='" . $_POST['Units'] . "',
									type='" . $_POST['Type'] . "',
									defaultvalue='" . $_POST['DefaultValue'] . "',
									numericvalue='" . $_POST['NumericValue'] . "',
									showoncert='" . $_POST['ShowOnCert'] . "',
									showonspec='" . $_POST['ShowOnSpec'] . "',
									showontestplan='" . $_POST['ShowOnTestPlan'] . "',
									active='" . $_POST['Active'] . "'
				WHERE qatests.testid = '".$SelectedQATest."'";

		$Msg = __('QA Test record for') . ' ' . $_POST['QATestName'] . ' ' . __('has been updated');
	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new QA Test form */

		$SQL = "INSERT INTO qatests (name,
						method,
						groupby,
						units,
						type,
						defaultvalue,
						numericvalue,
						showoncert,
						showonspec,
						showontestplan,
						active)
				VALUES ('" . $_POST['QATestName'] . "',
					'" . $_POST['Method'] . "',
					'" . $_POST['GroupBy'] . "',
					'" . $_POST['Units'] . "',
					'" .$_POST['Type'] . "',
					'" . $_POST['DefaultValue'] . "',
					'" . $_POST['NumericValue'] . "',
					'" . $_POST['ShowOnCert'] . "',
					'" . $_POST['ShowOnSpec'] . "',
					'" . $_POST['ShowOnTestPlan'] . "',
					'" . $_POST['Active'] . "'
					)";

		$Msg = __('A new QA Test record has been added for') . ' ' . $_POST['QATestName'];
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = __('The insert or update of the QA Test failed because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg($Msg , 'success');

		unset($SelectedQATest);
		unset($_POST['QATestName']);
		//unset($_POST['Method']);
		//unset($_POST['GroupBy']);
		//unset($_POST['Units']);
		//unset($_POST['Type']);
		unset($_POST['DefaultValue']);
		unset($_POST['NumericValue']);
		//unset($_POST['ShowOnCert']);
		//unset($_POST['ShowOnSpec']);
		//unset($_POST['ShowOnTestPlan']);
		//unset($_POST['Active']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS

	$SQL= "SELECT COUNT(*) FROM prodspec WHERE  prodspec.testid='".$SelectedQATest."'";
	//$Result = DB_query($SQL);
	//$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this QA Test because Product Specs are using it'),'error');
	} else {
		$SQL = "DELETE FROM qatests WHERE testid='". $SelectedQATest."'";
		$ErrMsg = __('The QA Test could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('QA Test') . ' ' . $SelectedQATest . ' ' . __('has been deleted from the database'),'success');
		unset ($SelectedQATest);
		unset($Delete);
		unset ($_GET['delete']);
	}
}

if (isset($SelectedQATest)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show All QA Tests') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedQATest)) {
		//editing an existing Sales-person

		$SQL = "SELECT testid,
				name,
				method,
				groupby,
				units,
				type,
				defaultvalue,
				numericvalue,
				showoncert,
				showonspec,
				showontestplan,
				active
				FROM qatests
				WHERE testid='".$SelectedQATest."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SelectedQATest'] = $MyRow['testid'];
		$_POST['QATestName'] = $MyRow['name'];
		$_POST['Method'] = $MyRow['method'];
		$_POST['GroupBy'] = $MyRow['groupby'];
		$_POST['Type'] = $MyRow['type'];
		$_POST['Units'] = $MyRow['units'];
		$_POST['DefaultValue'] = $MyRow['defaultvalue'];
		$_POST['NumericValue'] = $MyRow['numericvalue'];
		$_POST['ShowOnCert'] = $MyRow['showoncert'];
		$_POST['ShowOnSpec'] = $MyRow['showonspec'];
		$_POST['ShowOnTestPlan'] = $MyRow['showontestplan'];
		$_POST['Active'] = $MyRow['active'];


		echo '<input type="hidden" name="SelectedQATest" value="' . $SelectedQATest . '" />';
		echo '<input type="hidden" name="TestID" value="' . $_POST['SelectedQATest'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit QA Test'), '</legend>
				<field>
					<label for="SelectedQATest">' . __('QA Test ID') . ':</label>
					<fieldtext>' . $_POST['SelectedQATest'] . '</fieldtext>
				</field>';

	} else { //end of if $SelectedQATest only do the else when a new record is being entered

		echo '<fieldset>
				<legend>', __('Create New QA Test'), '</legend>';

	}
	if (!isset($_POST['QATestName'])){
		$_POST['QATestName']='';
	}
	if (!isset($_POST['Method'])){
		$_POST['Method']='';
	}
	if (!isset($_POST['GroupBy'])){
		$_POST['GroupBy']='';
	}
	if (!isset($_POST['Units'])){
		$_POST['Units']='';
	}
	if (!isset($_POST['Type'])) {
		$_POST['Type']=4;
	}
	if (!isset($_POST['Active'])) {
		$_POST['Active']=1;
	}
	if (!isset($_POST['NumericValue'])) {
		$_POST['NumericValue']=1;
	}
	if (!isset($_POST['ShowOnCert'])) {
		$_POST['ShowOnCert']=1;
	}
	if (!isset($_POST['ShowOnSpec'])) {
		$_POST['ShowOnSpec']=1;
	}
	if (!isset($_POST['ShowOnTestPlan'])) {
		$_POST['ShowOnTestPlan']=1;
	}
	if (!isset($_POST['DefaultValue'])) {
		$_POST['DefaultValue'] = '';
	}
	echo '<field>
			<label for="QATestName">' . __('QA Test Name') . ':</label>
			<input type="text" '. (in_array('QATestName',$Errors) ? 'class="inputerror"' : '' ) .' name="QATestName"  required="required" title="" size="30" maxlength="50" value="' . $_POST['QATestName'] . '" />
			<fieldhelp>' . __('The name of the Test you are setting up') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Method">' . __('Method') . ':</label>
			<input type="text" name="Method" title="" size="20" maxlength="20" value="' . $_POST['Method'] . '" />
			<fieldhelp>' . __('ASTM, ISO, UL or other') . '</fieldhelp>
		</field>';
	//echo '<field>
	//		<label for="GroupBy">' . __('Group By') . ':</label>
	//		<input type="text" name="GroupBy" title="" size="20" maxlength="20" value="' . $_POST['GroupBy'] . '" />
	//		<fieldhelp>' . __('Can be used to group certain Tests on the Product Specification or Certificate of Analysis or left blank') . '</fieldhelp>
	//	</field>';
	
	
	$result2 = DB_query("SELECT groupname FROM prodspecgroups", $db);
	// Error if no groups  setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="ProdSpecGroups.php" target="_parent">' . _('Setup Product Spec Groups') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Product Spec Groups defined') . '&nbsp;&nbsp;<a href="ProdSpecGroups.php" target="_parent">' . _('Setup Product Spec Groups') . '</a></td></tr>', 'error');
		include('includes/footer.php');
		exit();
	} else {
		// if OK show select box with available options to choose
		echo '<field>
			<label for="Type">' . __('Group By') . ':</label>
			<td><select title="" name="GroupBy">';
		while ($myrow = DB_fetch_array($result2)) {
			if (isset($_POST['GroupBy']) AND $_POST['GroupBy'] == $myrow['groupname']) {
				echo '<option selected="selected" value="' . $myrow['groupname'] . '">' . $myrow['groupname'] . '</option>';
			} else {
				echo '<option value="' . $myrow['groupname'] . '">' . $myrow['groupname'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td></tr>';
		echo '</select>
			<fieldhelp>' . __('Can be used to group certain Tests on the Product Specification or Certificate of Analysis or left blank') . '</fieldhelp>
			</field>';
	}
				
	
	echo '<field>
			<label for="Units">' . __('Units') . ':</label>
			<input type="text" name="Units" title="" size="20" maxlength="20" value="' . $_POST['Units'] . '" />
			<fieldhelp>' . __('How this is measured. PSI, Fahrenheit, Celsius etc.') . '</fieldhelp>
		</field>';
	echo '<field>
			<label for="Type">' . __('Type') . ':</label>
			<td><select title="" name="Type">';
	if ($_POST['Type']==0){
		echo '<option selected="selected" value="0">' . __('Text Box') . '</option>';
	} else {
		echo '<option value="0">' . __('Text Box') . '</option>';
	}
	if ($_POST['Type']==1){
		echo '<option selected="selected" value="1">' . __('Select Box') . '</option>';
	} else {
		echo '<option value="1">' . __('Select Box') . '</option>';
	}
	if ($_POST['Type']==2){
		echo '<option selected="selected" value="2">' . __('Check Box') . '</option>';
	} else {
		echo '<option value="2">' . __('Check Box') . '</option>';
	}
	if ($_POST['Type']==3){
		echo '<option selected="selected" value="3">' . __('Date Box') . '</option>';
	} else {
		echo '<option value="3">' . __('Date Box') . '</option>';
	}
	if ($_POST['Type']==4){
		echo '<option selected="selected" value="4">' . __('Range') . '</option>';
	} else {
		echo '<option value="4">' . __('Range') . '</option>';
	}
	echo '</select>
		<fieldhelp>' . __('What sort of data field is required to record the results for this test') . '</fieldhelp>
	</field>';

	echo '<field>
			<label for="DefaultValue">' . __('Possible Values') . ':</label>
			<input type="text" name="DefaultValue" size="50" maxlength="150" value="' . $_POST['DefaultValue']. '" />
		</field>';

	echo '<field>
			<label for="NumericValue">' . __('Numeric Value?') . ':</label>
			<select name="NumericValue">';
	if ($_POST['NumericValue']==1){
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['NumericValue']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnCert">' . __('Show On Cert?') . ':</label>
			<select name="ShowOnCert">';
	if ($_POST['ShowOnCert']==1){
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['ShowOnCert']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnSpec">' . __('Show On Spec?') . ':</label>
			<select name="ShowOnSpec">';
	if ($_POST['ShowOnSpec']==1){
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['ShowOnSpec']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowOnTestPlan">' . __('Show On Test Plan?') . ':</label>
			<select name="ShowOnTestPlan">';
	if ($_POST['ShowOnTestPlan']==1){
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['ShowOnTestPlan']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Active">' . __('Active?') . ':</label>
			<select name="Active">';
	if ($_POST['Active']==1){
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['Active']==0){
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record
if (!isset($SelectedQATest)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedQATest will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of QA Test will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT testid,
				name,
				method,
				groupby,
				units,
				type,
				defaultvalue,
				numericvalue,
				showoncert,
				showonspec,
				showontestplan,
				active
			FROM qatests
			ORDER BY name";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<thead>
			<tr>
			<th class="SortedColumn">' . __('Test ID') . '</th>
			<th class="SortedColumn">' . __('Name') . '</th>
			<th class="SortedColumn">' . __('Method') . '</th>
			<th class="SortedColumn">' . __('Group By') . '</th>
			<th class="SortedColumn">' . __('Units') . '</th>
			<th class="SortedColumn">' . __('Type') . '</th>
			<th>' . __('Possible Values') . '</th>
			<th class="SortedColumn">' . __('Numeric Value') . '</th>
			<th class="SortedColumn">' . __('Show on Cert') . '</th>
			<th class="SortedColumn">' . __('Show on Spec') . '</th>
			<th class="SortedColumn">' . __('Show on Test Plan') . '</th>
			<th class="SortedColumn">' . __('Active') . '</th>
			<th colspan="2"></th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow=DB_fetch_array($Result)) {

	if ($MyRow['active'] == 1) {
		$ActiveText = __('Yes');
	} else {
		$ActiveText = __('No');
	}
	if ($MyRow['numericvalue'] == 1) {
		$IsNumeric = __('Yes');
	} else {
		$IsNumeric = __('No');
	}
	if ($MyRow['showoncert'] == 1) {
		$ShowOnCertText = __('Yes');
	} else {
		$ShowOnCertText = __('No');
	}
	if ($MyRow['showonspec'] == 1) {
		$ShowOnSpecText = __('Yes');
	} else {
		$ShowOnSpecText = __('No');
	}
	if ($MyRow['showontestplan'] == 1) {
		$ShowOnTestPlanText = __('Yes');
	} else {
		$ShowOnTestPlanText = __('No');
	}

	switch ($MyRow['type']) {
		case 0; //textbox
			$TypeDisp='Text Box';
			break;
		case 1; //select box
			$TypeDisp='Select Box';
			break;
		case 2; //checkbox
			$TypeDisp='Check Box';
			break;
		case 3; //datebox
			$TypeDisp='Date Box';
			break;
		case 4; //range
			$TypeDisp='Range';
			break;
	} //end switch

	echo '<tr class="striped_row">
			<td class="number">', $MyRow['testid'], '</td>
			<td>', $MyRow['name'], '</td>
			<td>', $MyRow['method'], '</td>
			<td>', $MyRow['groupby'], '</td>
			<td>', $MyRow['units'], '</td>
			<td>', $TypeDisp, '</td>
			<td>', $MyRow['defaultvalue'], '</td>
			<td>', $IsNumeric, '</td>
			<td>', $ShowOnCertText, '</td>
			<td>', $ShowOnSpecText, '</td>
			<td>', $ShowOnTestPlanText, '</td>
			<td>', $ActiveText, '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedQATest=', $MyRow['testid'], '">' .  __('Edit') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedQATest=', $MyRow['testid'], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this QA Test ?') . '\');">' . __('Delete') . '</a></td>
		</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table>';
} //end of ifs and buts!
include('includes/footer.php');
