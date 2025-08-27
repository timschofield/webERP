<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Sales Analysis Reports Maintenance');
$ViewTopic = 'SalesAnalysis';
$BookMark = 'SalesAnalysis';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

function GrpByDataOptions($GroupByDataX) {

/*Sales analysis headers group by data options */
 if ($GroupByDataX == 'Sales Area') {
     echo '<option selected="selected" value="Sales Area">' . __('Sales Area') . '</option>';
 } else {
    echo '<option value="Sales Area">' . __('Sales Area') . '</option>';
 }
 if ($GroupByDataX == 'Product Code') {
     echo '<option selected="selected" value="Product Code">' . __('Product Code') . '</option>';
 } else {
    echo '<option value="Product Code">' . __('Product Code') . '</option>';
 }
 if ($GroupByDataX == 'Customer Code') {
     echo '<option selected="selected" value="Customer Code">' . __('Customer Code') . '</option>';
 } else {
    echo '<option value="Customer Code">' . __('Customer Code') . '</option>';
 }
 if ($GroupByDataX == 'Sales Type') {
     echo '<option selected="selected" value="Sales Type">' . __('Sales Type') . '</option>';
 } else {
    echo '<option value="Sales Type">' . __('Sales Type') . '</option>';
 }
 if ($GroupByDataX == 'Product Type') {
     echo '<option selected="selected" value="Product Type">' . __('Product Type') . '</option>';
 } else {
    echo '<option value="Product Type">' . __('Product Type') . '</option>';
 }
 if ($GroupByDataX == 'Customer Branch') {
     echo '<option selected="selected" value="Customer Branch">' . __('Customer Branch') . '</option>';
 } else {
    echo '<option value="Customer Branch">' . __('Customer Branch') . '</option>';
 }
 if ($GroupByDataX == 'Sales Person') {
     echo '<option selected="selected" value="Sales Person">' . __('Sales Person') . '</option>';
 } else {
    echo '<option value="Sales Person">' . __('Sales Person') . '</option>';
 }
 if ($GroupByDataX=='Not Used' OR $GroupByDataX == '' OR ! isset($GroupByDataX) OR is_null($GroupByDataX)){
     echo '<option selected="selected" value="Not Used">' . __('Not Used') . '</option>';
 } else {
    echo '<option value="Not Used">' . __('Not Used') . '</option>';
 }
}

/* end of function  */

echo '<br />';

if (isset($_GET['SelectedReport'])) {
	$SelectedReport = $_GET['SelectedReport'];
} elseif (isset($_POST['SelectedReport'])) {
	$SelectedReport = $_POST['SelectedReport'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['ReportHeading']) <2) {
		$InputError = 1;
		prnMsg(__('The report heading must be more than two characters long') . '. ' . __('No report heading was entered'),'error',__('Heading too long'));
	}
	if ($_POST['GroupByData1']=='' OR !isset($_POST['GroupByData1']) OR $_POST['GroupByData1']=='Not Used') {
	      $InputError = 1;
	      prnMsg(__('A group by item must be specified for the report to have any output'),'error',__('No Group By selected'));
	}
	if ($_POST['GroupByData3']=='Not Used' AND $_POST['GroupByData4']!='Not Used') {
		// If GroupByData3 is blank but GroupByData4 is used then move GroupByData3 to GroupByData2
		$_POST['GroupByData3'] = $_POST['GroupByData4'];
		$_POST['Lower3'] = $_POST['Lower4'];
		$_POST['Upper3'] = $_POST['Upper4'];
	}
	if ($_POST['GroupByData2']=='Not Used' AND $_POST['GroupByData3']!='Not Used') {
	     /*If GroupByData2 is blank but GroupByData3 is used then move GroupByData3 to GroupByData2 */
	     $_POST['GroupByData2'] = $_POST['GroupByData3'];
	     $_POST['Lower2'] = $_POST['Lower3'];
	     $_POST['Upper2'] = $_POST['Upper3'];
	}
	if (($_POST['Lower1']=='' OR $_POST['Upper1']=='')) {
	     $InputError = 1;
	     prnMsg(__('Group by Level 1 is set but the upper and lower limits are not set') . ' - ' . __('these must be specified for the report to have any output'),'error',__('Upper/Lower limits not set'));
	}
	if (($_POST['GroupByData2']!='Not Used') AND ($_POST['Lower2']=='' OR $_POST['Upper2']=='')) {
	     $InputError = 1;
	     prnMsg( __('Group by Level 2 is set but the upper and lower limits are not set') . ' - ' . __('these must be specified for the report to have any output'),'error',__('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData3']!='Not Used') AND ($_POST['Lower3']=='' OR $_POST['Upper3']=='')) {
	     $InputError = 1;
	     prnMsg( __('Group by Level 3 is set but the upper and lower limits are not set') . ' - ' . __('these must be specified for the report to have any output'),'error',__('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData4']!='Not Used') AND ($_POST['Lower4']=='' OR $_POST['Upper4']=='')) {
		$InputError = 1;
		prnMsg( __('Group by Level 4 is set but the upper and lower limits are not set') . ' - ' . __('these must be specified for the report to have any output'),'error',__('Upper/Lower Limits not set'));
	}
	if ($_POST['GroupByData1']!='Not Used' AND $_POST['Lower1'] > $_POST['Upper1']) {
	     $InputError = 1;
	     prnMsg(__('Group by Level 1 is set but the lower limit is greater than the upper limit') . ' - ' . __('the report will have no output'),'error',__('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData2']!='Not Used' AND $_POST['Lower2'] > $_POST['Upper2']) {
	     $InputError = 1;
	     prnMsg(__('Group by Level 2 is set but the lower limit is greater than the upper limit') . ' - ' . __('the report will have no output'),'error',__('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData3']!='Not Used' AND $_POST['Lower3'] > $_POST['Upper3']) {
	     $InputError = 1;
	     prnMsg(__('Group by Level 3 is set but the lower limit is greater than the upper limit') . ' - ' . __('the report will have no output'),'error',__('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData4']!='Not Used' AND $_POST['Lower4'] > $_POST['Upper4']) {
		$InputError = 1;
		prnMsg(__('Group by Level 4 is set but the lower limit is greater than the upper limit') . ' - ' . __('the report will have no output'),'error',__('Lower Limit > Upper Limit'));
	}

	if (isset($SelectedReport) AND $InputError !=1) {

		/*SelectedReport could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE reportheaders SET
						reportheading='" . $_POST['ReportHeading'] . "',
						groupbydata1='" . $_POST['GroupByData1'] . "',
						groupbydata2='" . $_POST['GroupByData2'] . "',
						groupbydata3='" . $_POST['GroupByData3'] . "',
						groupbydata4='" . $_POST['GroupByData4'] . "',
						newpageafter1='" . $_POST['NewPageAfter1'] . "',
						newpageafter2='" . $_POST['NewPageAfter2'] . "',
						newpageafter3='" . $_POST['NewPageAfter3'] . "',
						lower1='" . filter_number_format($_POST['Lower1']) . "',
						upper1='" . filter_number_format($_POST['Upper1']) . "',
						lower2='" . filter_number_format($_POST['Lower2']) . "',
						upper2='" . filter_number_format($_POST['Upper2']) . "',
						lower3='" . filter_number_format($_POST['Lower3']) . "',
						upper3='" . filter_number_format($_POST['Upper3']) . "',
						lower4='" . filter_number_format($_POST['Lower4']) . "',
						upper4='" . filter_number_format($_POST['Upper4']) . "'
				WHERE reportid = " . $SelectedReport;

		$ErrMsg = __('The report could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg( __('The') .' ' . $_POST['ReportHeading'] . ' ' . __('report has been updated'),'success', 'Report Updated');
		unset($SelectedReport);
		unset($_POST['ReportHeading']);
		unset($_POST['GroupByData1']);
		unset($_POST['GroupByData2']);
		unset($_POST['GroupByData3']);
		unset($_POST['GroupByData4']);
		unset($_POST['NewPageAfter1']);
		unset($_POST['NewPageAfter2']);
		unset($_POST['NewPageAfter3']);
		unset($_POST['Lower1']);
		unset($_POST['Upper1']);
		unset($_POST['Lower2']);
		unset($_POST['Upper2']);
		unset($_POST['Lower3']);
		unset($_POST['Upper3']);
		unset($_POST['Lower4']);
		unset($_POST['Upper4']);

	} elseif ($InputError !=1) {

	/*SelectedReport is null cos no item selected on first time round so must be adding a new report */

		$SQL = "INSERT INTO reportheaders (
						reportheading,
						groupbydata1,
						groupbydata2,
						groupbydata3,
						groupbydata4,
						newpageafter1,
						newpageafter2,
						newpageafter3,
						lower1,
						upper1,
						lower2,
						upper2,
						lower3,
						upper3,
						lower4,
						upper4 )
				VALUES (
					'" . $_POST['ReportHeading'] . "',
					'" . $_POST['GroupByData1']. "',
					'" . $_POST['GroupByData2'] . "',
					'" . $_POST['GroupByData3'] . "',
					'" . $_POST['GroupByData4'] . "',
					'" . $_POST['NewPageAfter1'] . "',
					'" . $_POST['NewPageAfter2'] . "',
					'" . $_POST['NewPageAfter3'] . "',
					'" . filter_number_format($_POST['Lower1']) . "',
					'" . filter_number_format($_POST['Upper1']) . "',
					'" . filter_number_format($_POST['Lower2']) . "',
					'" . filter_number_format($_POST['Upper2']) . "',
					'" . filter_number_format($_POST['Lower3']) . "',
					'" . filter_number_format($_POST['Upper3']) . "',
					'" . filter_number_format($_POST['Lower4']) . "',
					'" . filter_number_format($_POST['Upper4']) . "'
					)";

		$ErrMsg = __('The report could not be added because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The') . ' ' . $_POST['ReportHeading'] . ' ' . __('report has been added to the database'),'success','Report Added');

		unset($SelectedReport);
		unset($_POST['ReportHeading']);
		unset($_POST['GroupByData1']);
		unset($_POST['GroupByData2']);
		unset($_POST['GroupByData3']);
		unset($_POST['GroupByData4']);
		unset($_POST['NewPageAfter1']);
		unset($_POST['NewPageAfter2']);
		unset($_POST['NewPageAfter3']);
		unset($_POST['Lower1']);
		unset($_POST['Upper1']);
		unset($_POST['Lower2']);
		unset($_POST['Upper2']);
		unset($_POST['Lower3']);
		unset($_POST['Upper3']);
		unset($_POST['Lower4']);
		unset($_POST['Upper4']);

	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$SQL="DELETE FROM reportcolumns WHERE reportid='".$SelectedReport."'";
	$ErrMsg = __('The deletion of the report column failed because');

	$Result = DB_query($SQL, $ErrMsg);

	$SQL="DELETE FROM reportheaders WHERE reportid='".$SelectedReport."'";
	$ErrMsg = __('The deletion of the report heading failed because');
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg(__('Report Deleted') ,'info');
	unset($SelectedReport);
	include('includes/footer.php');
	exit();

}

if (!isset($SelectedReport)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReport will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Reports will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/


	$Result = DB_query("SELECT reportid, reportheading FROM reportheaders ORDER BY reportid");

	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Report No') . '</th>
			<th>' . __('Report Title') . '</th>
			<th colspan="5"></th>
          </tr>';

while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
			<td>', $MyRow[0], '</td>
			<td>', $MyRow[1], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedReport=', $MyRow[0], '">' . __('Design') . '</a></td>
			<td><a href="', $RootPath, '/SalesAnalReptCols.php?ReportID=', $MyRow[0], '">' . __('Define Columns') . '</a></td>
			<td><a href="', $RootPath, '/SalesAnalysis_UserDefined.php?ReportID=', $MyRow[0], '&amp;ProducePDF=True">' . __('Make PDF Report') . '</a></td>
			<td><a href="', $RootPath, '/SalesAnalysis_UserDefined.php?ReportID=', $MyRow[0], '&amp;ProduceCVSFile=True">' . __('Make CSV File') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedReport=', $MyRow[0], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to remove this report design?') . '\');">' . __('Delete') . '</a></td>
			</tr>';

	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedReport)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show All Defined Reports') . '</a>';
}

echo '<br />';


if (!isset($_GET['delete'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedReport)) {
		//editing an existing Report

		$SQL = "SELECT reportid,
						reportheading,
						groupbydata1,
						newpageafter1,
						upper1,
						lower1,
						groupbydata2,
						newpageafter2,
						upper2,
						lower2,
						groupbydata3,
						upper3,
						lower3,
						newpageafter3,
						groupbydata4,
						upper4,
						lower4
				FROM reportheaders
				WHERE reportid='".$SelectedReport."'";

		$ErrMsg = __('The reports for display could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($Result);

		$ReportID = $MyRow['reportid'];
		$_POST['ReportHeading']  = $MyRow['reportheading'];
		$_POST['GroupByData1'] = $MyRow['groupbydata1'];
		$_POST['NewPageAfter1'] = $MyRow['newpageafter1'];
		$_POST['Upper1'] = $MyRow['upper1'];
		$_POST['Lower1'] = $MyRow['lower1'];
		$_POST['GroupByData2'] = $MyRow['groupbydata2'];
		$_POST['NewPageAfter2'] = $MyRow['newpageafter2'];
		$_POST['Upper2'] = $MyRow['upper2'];
		$_POST['Lower2'] = $MyRow['lower2'];
		$_POST['GroupByData3'] = $MyRow['groupbydata3'];
		$_POST['Upper3'] = $MyRow['upper3'];
		$_POST['Lower3'] = $MyRow['lower3'];
		$_POST['GroupByData4'] = $MyRow['groupbydata4'];
       	$_POST['Upper4'] = $MyRow['upper4'];
       	$_POST['Lower4'] = $MyRow['lower4'];

		echo '<input type="hidden" name="SelectedReport" value="' . $SelectedReport . '" />';
		echo '<input type="hidden" name="ReportID" value="' . $ReportID . '" />';
		echo '<table width="98%" class="selection">
				<tr>
					<th colspan="8"><h3>' . __('Edit The Selected Report') . '</h3></th>
				</tr>';
	} else {
		echo '<table width="98%" class="selection">
				<tr>
					<th colspan="8"><h3>' . __('Define A New Report') . '</h3></th>
				</tr>';
	}

	if (!isset($_POST['ReportHeading'])) {
		$_POST['ReportHeading']='';
	}
	echo '<tr>
			<td class="number">' . __('Report Heading') . ':</td>
			<td colspan="2"><input type="text" size="80" maxlength="80" name="ReportHeading" value="' . $_POST['ReportHeading'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('Group By 1') . ': <select name="GroupByData1">';

	GrpByDataOptions($_POST['GroupByData1']);

	echo '</select></td>
			<td>' . __('Page Break After') . ': <select name="NewPageAfter1">';

	if ($_POST['NewPageAfter1']==0){
	  echo '<option selected="selected" value="0">' . __('No') . '</option>';
	  echo '<option value="1">' . __('Yes') . '</option>';
	} else {
	  echo '<option value="0">' . __('No') . '</option>';
	  echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	}

	echo '</select></td>';

	if (!isset($_POST['Lower1'])) {
		$_POST['Lower1'] = '';
	}

	if (!isset($_POST['Upper1'])) {
		$_POST['Upper1'] = '';
	}
	echo '<td>' . __('From') . ': <input type="text" name="Lower1" size="10" maxlength="10" value="' . $_POST['Lower1'] . '" /></td>
			<td>' . __('To') . ': <input type="text" name="Upper1" size="10" maxlength="10" value="' . $_POST['Upper1'] .'" /></td>
		</tr>
		<tr>
			<td>' . __('Group By 2') . ': <select name="GroupByData2">';

	GrpByDataOptions($_POST['GroupByData2']);

	echo '</select></td>
			<td>' . __('Page Break After') . ': <select name="NewPageAfter2">';

	if ($_POST['NewPageAfter2']==0){
	  echo '<option selected="selected" value="0">' . __('No') . '</option>';
	  echo '<option value="1">' . __('Yes') . '</option>';
	} else {
	  echo '<option value="0">' . __('No') . '</option>';
	  echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	}

	if (!isset($_POST['Lower2'])) {
		$_POST['Lower2'] = '';
	}

	if (!isset($_POST['Upper2'])) {
		$_POST['Upper2'] = '';
	}

	echo '</select></td>';
	echo '<td>' . __('From') . ': <input type="text" name="Lower2" size="10" maxlength="10" value="' . $_POST['Lower2'] . '" /></td>
			<td>' . __('To') . ': <input type="text" name="Upper2" size="10" maxlength="10" value="' . $_POST['Upper2'] . '" /></td>
		</tr>
		<tr>
			<td>' . __('Group By 3') . ': <select name="GroupByData3">';

	GrpByDataOptions($_POST['GroupByData3']);

	echo '</select></td>
			<td>' . __('Page Break After') . ': <select name="NewPageAfter3">';

	if ($_POST['NewPageAfter3']==0){
	 	echo '<option selected="selected" value="0">' . __('No') . '</option>';
	 	echo '<option value="1">' . __('Yes') . '</option>';
	} else {
	 	echo '<option value="0">' . __('No') . '</option>';
	 	echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	}

	echo '</select></td>';

	if (!isset($_POST['Lower3'])) {
		$_POST['Lower3'] = '';
	}

	if (!isset($_POST['Upper3'])) {
		$_POST['Upper3'] = '';
	}

	echo '<td>' . __('From') . ': <input type="text" name="Lower3" size="10" maxlength="10" value="' . $_POST['Lower3'] . '" /></td>
			<td>' . __('To') . ': <input type="text" name="Upper3" size="10" maxlength="10" value="' . $_POST['Upper3'] . '" /></td>
		</tr>
		<tr>
			<td>' . __('Group By 4') . ': <select name="GroupByData4">';

	GrpByDataOptions($_POST['GroupByData4']);

	echo '</select></td>
		<td></td>';

	if (!isset($_POST['Lower4'])) {
		$_POST['Lower4'] = '';
	}

	if (!isset($_POST['Upper4'])) {
		$_POST['Upper4'] = '';
	}

	echo '<td>' . __('From') .': <input type="text" name="Lower4" size="10" maxlength="10" value="' . $_POST['Lower4'] . '" /></td>
			<td>' . __('To') . ': <input type="text" name="Upper4" size="10" maxlength="10" value="' . $_POST['Upper4'] . '" /></td>
		</tr>';

	echo '</table>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . __('Enter Information') . '" />
			</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
