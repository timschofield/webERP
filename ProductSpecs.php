<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Product Specifications Maintenance');
$ViewTopic = 'QualityAssurance';// Filename in ManualContents.php's TOC.
$BookMark = 'QA_ProdSpecs';// Anchor's id in the manual's html document.
include('includes/header.php');

if (isset($_GET['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_GET['SelectedQATest']);
} elseif(isset($_POST['SelectedQATest'])){
	$SelectedQATest =mb_strtoupper($_POST['SelectedQATest']);
}
if (isset($_GET['KeyValue'])){
	$KeyValue =mb_strtoupper($_GET['KeyValue']);
} elseif(isset($_POST['KeyValue'])){
	$KeyValue =mb_strtoupper($_POST['KeyValue']);
} else {
	$KeyValue = '';
}

if (!isset($_POST['RangeMin']) OR $_POST['RangeMin']=='') {
	$RangeMin = 'NULL';
} else {
	$RangeMin = "'" . $_POST['RangeMin'] . "'";
}
if (!isset($_POST['RangeMax']) OR $_POST['RangeMax']=='') {
	$RangeMax = 'NULL';
} else {
	$RangeMax = "'" . $_POST['RangeMax'] . "'";
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['CopySpec']) OR isset($_POST['CopySpec'])) {
	if (!isset($_POST['CopyTo']) OR $_POST['CopyTo']=='' ) {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo __('Enter The Item, Fixed Asset or Template to Copy this Specification to') . ':<input type="text" name="CopyTo" size="25" maxlength="25" />
			<div class="centre">
				<input type="hidden" name="KeyValue" value="' . $KeyValue . '" />
				<input type="submit" name="CopySpec" value="' . __('Copy') . '" />
			</div>
			</form>';
		include('includes/footer.php');
		exit();
	} else {
		$SQL = "INSERT IGNORE INTO prodspecs
							(keyval,
							testid,
							defaultvalue,
							targetvalue,
							rangemin,
							rangemax,
							showoncert,
							showonspec,
							showontestplan,
							active)
					SELECT '"  . $_POST['CopyTo'] . "',
								testid,
								defaultvalue,
								targetvalue,
								rangemin,
								rangemax,
								showoncert,
								showonspec,
								showontestplan,
								active
					FROM prodspecs WHERE keyval='" .$KeyValue. "'";
			$Msg = __('A Product Specification has been copied to') . ' ' . $_POST['CopyTo']  . ' from ' . ' ' . $KeyValue ;
			$ErrMsg = __('The insert of the Product Specification failed because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg($Msg , 'success');
		$KeyValue=$_POST['CopyTo'];
		unset($_GET['CopySpec']);
		unset($_POST['CopySpec']);
	} //else
} //CopySpec

if (!isset($KeyValue) OR $KeyValue=='') {
	//prompt user for Key Value
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
				<field>
					<label for="KeyValue">' . __('Enter Specification Name') .':</label>
					<input type="text" name="KeyValue" size="25" maxlength="25" />
				</field>
			</fieldset>
			<div>
				<input type="submit" name="pickspec" value="' . __('Submit') . '" />
			</div>
		</form>
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
				<field>
					<label for="KeyValue">' . __('Or Select Existing Specification') .':</label>';

	$SQLSpecSelect="SELECT DISTINCT(keyval),
							description
						FROM prodspecs LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=prodspecs.keyval";


	$ResultSelection=DB_query($SQLSpecSelect);
	echo '<select name="KeyValue">';

	while ($MyRowSelection=DB_fetch_array($ResultSelection)){
		echo '<option value="' . $MyRowSelection['keyval'] . '">' . $MyRowSelection['keyval'].' - ' .htmlspecialchars($MyRowSelection['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
	}
	echo 	'</select>
			</field>
		</fieldset>
		<div>
			<input type="submit" name="pickspec" value="' . __('Submit') . '" />
		</div>
		</form>';


} else {
	//show header
	$SQLSpecSelect="SELECT description
						FROM stockmaster
						WHERE stockmaster.stockid='" .$KeyValue. "'";

	$ResultSelection=DB_query($SQLSpecSelect);
	$MyRowSelection=DB_fetch_array($ResultSelection);
	echo '<h3>' . __('Product Specification for') . ' ' . $KeyValue . '-' . $MyRowSelection['description'] . '</h3>';
}
if (isset($_GET['ListTests'])) {
	$SQL = "SELECT qatests.testid,
				name,
				method,
				units,
				type,
				numericvalue,
				qatests.defaultvalue
			FROM qatests
			LEFT JOIN prodspecs
			ON prodspecs.testid=qatests.testid
			AND prodspecs.keyval='".$KeyValue."'
			WHERE qatests.active='1'
			AND prodspecs.keyval IS NULL
			ORDER BY name";
	$Result = DB_query($SQL);
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	if (DB_num_rows($Result) > 0) {
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Add') . '</th>
					<th class="SortedColumn">' . __('Name') . '</th>
					<th class="SortedColumn">' . __('Method') . '</th>
					<th class="SortedColumn">' . __('Units') . '</th>
					<th>' . __('Possible Values') . '</th>
					<th>' . __('Target Value') . '</th>
					<th>' . __('Range Min') . '</th>
					<th>' . __('Range Max') . '</th>
				</tr>
			</thead>
			<tbody>';
	}

	$x=0;
	while ($MyRow=DB_fetch_array($Result)) {

	$x++;
	$Class='';
	$RangeMin='';
	$RangeMax='';
	if ($MyRow['numericvalue'] == 1) {
		$IsNumeric = __('Yes');
		$Class="number";
	} else {
		$IsNumeric = __('No');
	}

	switch ($MyRow['type']) {
	 	case 0; //textbox
	 		$TypeDisp=__('Text Box');
	 		break;
	 	case 1; //select box
	 		$TypeDisp=__('Select Box');
			break;
		case 2; //checkbox
			$TypeDisp=__('Check Box');
			break;
		case 3; //datebox
			$TypeDisp=__('Date Box');
			$Class="date";
			break;
		case 4; //range
			$TypeDisp=__('Range');
			$RangeMin='<input  class="' .$Class. '" type="text" name="AddRangeMin' .$x.'" />';
			$RangeMax='<input  class="' .$Class. '" type="text" name="AddRangeMax' .$x.'" />';
			break;
	} //end switch
		echo '<tr class="striped_row">
				<td><input type="checkbox" name="AddRow' .$x.'"><input type="hidden" name="AddTestID' .$x.'" value="' .$MyRow['testid']. '"></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['method'], '</td>
				<td>', $MyRow['units'], '</td>
				<td>', $MyRow['defaultvalue'], '</td>
				<td><input  class="' .$Class. '" type="text" name="AddTargetValue' .$x.'" /></td>
				<td>', $RangeMin, '</td>
				<td>', $RangeMax, '</td>
			</tr>';

	} //END WHILE LIST LOOP

	echo '</tbody>
		</table>
			<div class="centre">
				<input type="hidden" name="KeyValue" value="' . $KeyValue . '" />
				<input type="hidden" name="AddTestsCounter" value="' . $x . '" />
				<input type="submit" name="AddTests" value="' . __('Add') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}  //ListTests
if (isset($_POST['AddTests'])) {
	for ($i=0;$i<=$_POST['AddTestsCounter'];$i++){
		if ($_POST['AddRow' .$i]=='on') {
			if ($_POST['AddRangeMin' .$i]=='') {
				$AddRangeMin="NULL";
			} else {
				$AddRangeMin="'" . $_POST['AddRangeMin' .$i] . "'";
			}
			if ($_POST['AddRangeMax' .$i]=='') {
				$AddRangeMax="NULL";
			} else {
				$AddRangeMax="'" . $_POST['AddRangeMax' .$i] . "'";
			}

			$SQL = "INSERT INTO prodspecs
							(keyval,
							testid,
							defaultvalue,
							targetvalue,
							rangemin,
							rangemax,
							showoncert,
							showonspec,
							showontestplan,
							active)
						SELECT '"  . $KeyValue . "',
								testid,
								defaultvalue,
								'"  .  $_POST['AddTargetValue' .$i] . "',
								"  . $AddRangeMin . ",
								"  . $AddRangeMax. ",
								showoncert,
								showonspec,
								showontestplan,
								active
						FROM qatests WHERE testid='" .$_POST['AddTestID' .$i]. "'";
			$Msg = __('A Product Specification record has been added for Test ID') . ' ' . $_POST['AddTestID' .$i]  . ' for ' . ' ' . $KeyValue ;
			$ErrMsg = __('The insert of the Product Specification failed because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg($Msg , 'success');
		} //if on
	} //for
} //AddTests

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (isset($SelectedQATest) AND $InputError !=1) {

		/*SelectedQATest could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE prodspecs SET defaultvalue='" . $_POST['DefaultValue'] . "',
									targetvalue='" . $_POST['TargetValue'] . "',
									rangemin=" . $RangeMin . ",
									rangemax=" . $RangeMax . ",
									showoncert='" . $_POST['ShowOnCert'] . "',
									showonspec='" . $_POST['ShowOnSpec'] . "',
									showontestplan='" . $_POST['ShowOnTestPlan'] . "',
									active='" . $_POST['Active'] . "'
				WHERE prodspecs.keyval = '".$KeyValue."'
				AND prodspecs.testid = '".$SelectedQATest."'";

		$Msg = __('Product Specification record for') . ' ' . $_POST['QATestName']  . ' for ' . ' ' . $KeyValue .  __('has been updated');
		$ErrMsg = __('The update of the Product Specification failed because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg($Msg , 'success');

		unset($SelectedQATest);
		unset($_POST['DefaultValue']);
		unset($_POST['TargetValue']);
		unset($_POST['RangeMax']);
		unset($_POST['RangeMin']);
		unset($_POST['ShowOnCert']);
		unset($_POST['ShowOnSpec']);
		unset($_POST['Active']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS

	$SQL= "SELECT COUNT(*) FROM qasamples
			INNER JOIN sampleresults on sampleresults.sampleid=qasamples.sampleid AND sampleresults.testid='". $SelectedQATest."'
			WHERE qasamples.prodspeckey='".$KeyValue."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this Product Specification because there are test results tied to it'),'error');
	} else {
		$SQL="DELETE FROM prodspecs WHERE keyval='". $KeyValue."'
									AND testid='". $SelectedQATest."'";
		$ErrMsg = __('The Product Specification could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Product Specification') . ' ' . $SelectedQATest . ' for ' . ' ' . $KeyValue . __('has been deleted from the database'),'success');
		unset ($SelectedQATest);
		unset($Delete);
		unset ($_GET['delete']);
	}
}

if (!isset($SelectedQATest)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedQATest will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of QA Test will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT prodspecs.testid,
				name,
				method,
				units,
				type,
				numericvalue,
				prodspecs.defaultvalue,
				prodspecs.targetvalue,
				prodspecs.rangemin,
				prodspecs.rangemax,
				prodspecs.showoncert,
				prodspecs.showonspec,
				prodspecs.showontestplan,
				prodspecs.active
			FROM prodspecs INNER JOIN qatests
			ON qatests.testid=prodspecs.testid
			WHERE prodspecs.keyval='" .$KeyValue."'
			ORDER BY name";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="SortedColumn">' . __('Name') . '</th>
			<th class="SortedColumn">' . __('Method') . '</th>
			<th class="SortedColumn">' . __('Units') . '</th>
			<th class="SortedColumn">' . __('Type') . '</th>
			<th>' . __('Possible Values') . '</th>
			<th>' . __('Target Value') . '</th>
			<th>' . __('Range Min') . '</th>
			<th>' . __('Range Max') . '</th>
			<th class="SortedColumn">' . __('Show on Cert') . '</th>
			<th class="SortedColumn">' . __('Show on Spec') . '</th>
			<th class="SortedColumn">' . __('Show on Test Plan') . '</th>
			<th class="SortedColumn">' . __('Active') . '</th>
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
		$Class="number";
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
			$Class="date";
			break;
		case 4; //range
			$TypeDisp='Range';
			break;
	} //end switch

		echo '<tr class="striped_row">
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['method'], '</td>
				<td>', $MyRow['units'], '</td>
				<td>', $TypeDisp, '</td>
				<td>', $MyRow['defaultvalue'], '</td>
				<td>', $MyRow['targetvalue'], '</td>
				<td>', $MyRow['rangemin'], '</td>
				<td>', $MyRow['rangemax'], '</td>
				<td>', $ShowOnCertText, '</td>
				<td>', $ShowOnSpecText, '</td>
				<td>', $ShowOnTestPlanText, '</td>
				<td>', $ActiveText, '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedQATest=', $MyRow['testid'], '&amp;KeyValue=', $KeyValue, '">' .  __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedQATest=', $MyRow['testid'], '&amp;KeyValue=', $KeyValue, '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this Product Specification ?') . '\');">' . __('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table><br />';
} //end of ifs and buts!

if (isset($SelectedQATest)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?KeyValue=' .$KeyValue .'">' . __('Show All Product Specs') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedQATest)) {
		//editing an existing Prod Spec

		$SQL = "SELECT prodspecs.testid,
						name,
						method,
						units,
						type,
						numericvalue,
						prodspecs.defaultvalue,
						prodspecs.targetvalue,
						prodspecs.rangemin,
						prodspecs.rangemax,
						prodspecs.showoncert,
						prodspecs.showonspec,
						prodspecs.showontestplan,
						prodspecs.active
				FROM prodspecs INNER JOIN qatests
				ON qatests.testid=prodspecs.testid
				WHERE prodspecs.keyval='".$KeyValue."'
				AND prodspecs.testid='".$SelectedQATest."'";

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
		$_POST['TargetValue'] = $MyRow['targetvalue'];
		$_POST['RangeMin'] = $MyRow['rangemin'];
		$_POST['RangeMax'] = $MyRow['rangemax'];
		$_POST['ShowOnCert'] = $MyRow['showoncert'];
		$_POST['ShowOnSpec'] = $MyRow['showonspec'];
		$_POST['ShowOnTestPlan'] = $MyRow['showontestplan'];
		$_POST['Active'] = $MyRow['active'];


		echo '<input type="hidden" name="SelectedQATest" value="' . $SelectedQATest . '" />';
		echo '<input type="hidden" name="KeyValue" value="' . $KeyValue . '" />';
		echo '<input type="hidden" name="TestID" value="' . $_POST['SelectedQATest'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' . __('Test Name') . ':</td>
					<td>' . $_POST['QATestName'] . '</td>
				</tr>';

		if (!isset($_POST['Active'])) {
			$_POST['Active']=1;
		}
		if (!isset($_POST['ShowOnCert'])) {
			$_POST['ShowOnCert']=1;
		}
		if (!isset($_POST['ShowOnSpec'])) {
			$_POST['ShowOnSpec']=1;
		}
		if ($MyRow['numericvalue'] == 1) {
			$IsNumeric = __('Yes');
			$Class="number";
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
				$Class="date";
				break;
			case 4; //range
				$TypeDisp='Range';
				break;
		} //end switch
		if ($TypeDisp=='Select Box') {
			echo '<tr>
					<td>' . __('Possible Values') . ':</td>
					<td><input type="text" name="DefaultValue" size="50" maxlength="150" value="' . $_POST['DefaultValue']. '" /></td>
				</tr>';
		}
		echo '<tr>
				<td>' . __('Target Value') . ':</td>
				<td><input type="text" class="' . $Class.'" name="TargetValue" size="15" maxlength="15" value="' . $_POST['TargetValue']. '" />&nbsp;'.$_POST['Units'].'</td>
			</tr>';

		if ($TypeDisp=='Range') {
			echo '<tr>
					<td>' . __('Range Min') . ':</td>
					<td><input class="' . $Class.'" type="text" name="RangeMin" size="10" maxlength="10" value="' . $_POST['RangeMin']. '" /></td>
				</tr>';
			echo '<tr>
					<td>' . __('Range Max') . ':</td>
					<td><input class="' . $Class.'" type="text" name="RangeMax" size="10" maxlength="10" value="' . $_POST['RangeMax']. '" /></td>
				</tr>';
		}
		echo '<tr>
				<td>' . __('Show On Cert?') . ':</td>
				<td><select name="ShowOnCert">';
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
		echo '</select></td></tr><tr>
				<td>' . __('Show On Spec?') . ':</td>
				<td><select name="ShowOnSpec">';
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
		echo '</select></td></tr><tr>
			<td>' . __('Show On Test Plan?') . ':</td>
			<td><select name="ShowOnTestPlan">';
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
		echo '</select></td></tr><tr>
				<td>' . __('Active?') . ':</td>
				<td><select name="Active">';
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
		echo '</select></td>
			</tr>
			</table>
			<div class="centre">
				<input type="submit" name="submit" value="' . __('Enter Information') . '" />
			</div>
			</form>';
	}
	if (isset($KeyValue)) {
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ListTests=yes&amp;KeyValue=' .$KeyValue .'">' . __('Add More Tests') . '</a></div>';
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?CopySpec=yes&amp;KeyValue=' .$KeyValue .'">' . __('Copy This Specification') . '</a></div>';
		echo '<div class="centre"><a target="_blank" href="'. $RootPath . '/PDFProdSpec.php?KeyValue=' .$KeyValue .'">' . __('Print Product Specification') . '</a></div>';
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Product Specification Main Page') . '</a></div>';
	}
} //end if record deleted no point displaying form to add record

include('includes/footer.php');
