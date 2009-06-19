<?php

/* $Revision: 1.17 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Sales Analysis Reports Maintenance');

include('includes/header.inc');

Function GrpByDataOptions ($GroupByDataX){

/*Sales analysis headers group by data options */
 if ($GroupByDataX == 'Sales Area'){
     echo '<option selected Value="Sales Area">' . _('Sales Area');
 } else {
    echo '<option Value="Sales Area">' . _('Sales Area');
 }
 if ($GroupByDataX == 'Product Code'){
     echo '<option selected Value="Product Code">' . _('Product Code');
 } else {
    echo '<option Value="Product Code">' . _('Product Code');
 }
 if ($GroupByDataX == 'Customer Code'){
     echo '<option selected Value="Customer Code">' . _('Customer Code');
 } else {
    echo '<option Value="Customer Code">' . _('Customer Code');
 }
 if ($GroupByDataX == 'Sales Type'){
     echo '<option selected Value="Sales Type">' . _('Sales Type');
 } else {
    echo '<option Value="Sales Type">' . _('Sales Type');
 }
 if ($GroupByDataX == 'Product Type'){
     echo '<option selected Value="Product Type">' . _('Product Type');
 } else {
    echo '<option Value="Product Type">' . _('Product Type');
 }
 if ($GroupByDataX == 'Customer Branch'){
     echo '<option selected Value="Customer Branch">' . _('Customer Branch');
 } else {
    echo '<option Value="Customer Branch">' . _('Customer Branch');
 }
 if ($GroupByDataX == 'Sales Person'){
     echo '<option selected Value="Sales Person">' . _('Sales Person');
 } else {
    echo '<option Value="Sales Person">' . _('Sales Person');
 }
 if ($GroupByDataX=='Not Used' OR $GroupByDataX == '' OR ! isset($GroupByDataX) OR is_null($GroupByDataX)){
     echo "<option selected VALUE='Not Used'>" . _('Not Used');
 } else {
    echo "<option VALUE='Not Used'>" . _('Not Used');
 }
}

/* end of function  */

echo '<p>';

if (isset($_GET['SelectedReport'])){
	$SelectedReport = $_GET['SelectedReport'];
} elseif (isset($_POST['SelectedReport'])){
	$SelectedReport = $_POST['SelectedReport'];
}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['ReportHeading']) <2) {
		$InputError = 1;
		prnMsg(_('The report heading must be more than two characters long') . '. ' . _('No report heading was entered'),'error',_('Heading too long'));
	}
	if ($_POST['GroupByData1']=='' OR !isset($_POST['GroupByData1']) OR $_POST['GroupByData1']=='Not Used'){
	      $InputError = 1;
	      prnMsg (_('A group by item must be specified for the report to have any output'),'error',_('No Group By selected'));
	}
	if ($_POST['GroupByData3']=='Not Used' AND $_POST['GroupByData4']!='Not Used'){
		// If GroupByData3 is blank but GroupByData4 is used then move GroupByData3 to GroupByData2
		$_POST['GroupByData3'] = $_POST['GroupByData4'];
		$_POST['Lower3'] = $_POST['Lower4'];
		$_POST['Upper3'] = $_POST['Upper4'];
	}
	if ($_POST['GroupByData2']=='Not Used' AND $_POST['GroupByData3']!='Not Used'){
	     /*If GroupByData2 is blank but GroupByData3 is used then move GroupByData3 to GroupByData2 */
	     $_POST['GroupByData2'] = $_POST['GroupByData3'];
	     $_POST['Lower2'] = $_POST['Lower3'];
	     $_POST['Upper2'] = $_POST['Upper3'];
	}
	if (($_POST['Lower1']=='' OR $_POST['Upper1']=='')){
	     $InputError = 1;
	     prnMsg (_('Group by Level 1 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'),'error',_('Upper/Lower limits not set'));
	}
	if (($_POST['GroupByData2']!='Not Used') AND ($_POST['Lower2']=='' || $_POST['Upper2']=='')){
	     $InputError = 1;
	     prnMsg( _('Group by Level 2 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'),'error',_('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData3']!='Not Used') AND ($_POST['Lower3']=='' || $_POST['Upper3']=='')){
	     $InputError = 1;
	     prnMsg( _('Group by Level 3 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'),'error',_('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData4']!='Not Used') AND ($_POST['Lower4']=='' || $_POST['Upper4']=='')){
		$InputError = 1;
		prnMsg( _('Group by Level 4 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'),'error',_('Upper/Lower Limits not set'));
	}
	if ($_POST['GroupByData1']!='Not Used' AND $_POST['Lower1'] > $_POST['Upper1']){
	     $InputError = 1;
	     prnMsg(_('Group by Level 1 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'),'error',_('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData2']!='Not Used' AND $_POST['Lower2'] > $_POST['Upper2']){
	     $InputError = 1;
	     prnMsg(_('Group by Level 2 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'),'error',_('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData3']!='Not Used' AND $_POST['Lower3'] > $_POST['Upper3']){
	     $InputError = 1;
	     prnMsg(_('Group by Level 3 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'),'error',_('Lower Limit > Upper Limit'));
	}
	if ($_POST['GroupByData4']!='Not Used' AND $_POST['Lower4'] > $_POST['Upper4']){
		$InputError = 1;
		prnMsg(_('Group by Level 4 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'),'error',_('Lower Limit > Upper Limit'));
	}



	if (isset($SelectedReport) AND $InputError !=1) {

		/*SelectedReport could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE reportheaders SET 
				reportheading='" . $_POST['ReportHeading'] . "', 
				groupbydata1='" . $_POST['GroupByData1'] . "', 
				groupbydata2='" . $_POST['GroupByData2'] . "',
				groupbydata3='" . $_POST['GroupByData3'] . "', 
				groupbydata4='" . $_POST['GroupByData4'] . "', 
				newpageafter1=" . $_POST['NewPageAfter1'] . ", 
				newpageafter2=" . $_POST['NewPageAfter2'] . ", 
				newpageafter3=" . $_POST['NewPageAfter3'] . ", 
				lower1='" . $_POST['Lower1'] . "', 
				upper1='" . $_POST['Upper1'] . "', 
				lower2='" . $_POST['Lower2'] . "', 
				upper2='" . $_POST['Upper2'] . "', 
				lower3='" . $_POST['Lower3'] . "', 
				upper3='" . $_POST['Upper3'] . "', 
				lower4='" . $_POST['Lower4'] . "', 
				upper4='" . $_POST['Upper4'] . "' 
			WHERE reportid = " . $SelectedReport;

		$ErrMsg = _('The report could not be updated because');
		$Dbgmsg = _('The SQL used to update the report headers was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg( _('The') .' ' . $_POST['ReportHeading'] . ' ' . _('report has been updated'),'success', 'Report Updated');
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

		$sql = "INSERT INTO reportheaders (
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
						upper4
						)
				VALUES (
					'" . $_POST['ReportHeading'] . "',
					'" . $_POST['GroupByData1']. "',
					'" . $_POST['GroupByData2'] . "',
					'" . $_POST['GroupByData3'] . "',
					'" . $_POST['GroupByData4'] . "',
					" . $_POST['NewPageAfter1'] . ",
					" . $_POST['NewPageAfter2'] . ",
					" . $_POST['NewPageAfter3'] . ",
					'" . $_POST['Lower1'] . "',
					'" . $_POST['Upper1'] . "',
					'" . $_POST['Lower2'] . "',
					'" . $_POST['Upper2'] . "',
					'" . $_POST['Lower3'] . "',
					'" . $_POST['Upper3'] . "',
					'" . $_POST['Lower4'] . "',
					'" . $_POST['Upper4'] . "'
					)";

		$ErrMsg = _('The report could not be added because');
		$DbgMsg = _('The SQL used to add the report header was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('The') . ' ' . $_POST['ReportHeading'] . ' ' . _('report has been added to the database'),'success','Report Added');

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

	$sql="DELETE FROM reportcolumns WHERE reportid=$SelectedReport";
	$ErrMsg = _("The deletion of the report's columns failed because");
	$DbgMsg = _("The SQL used to delete the report's columns was");

	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$sql="DELETE FROM reportheaders WHERE reportid=$SelectedReport";
	$ErrMsg = _('The deletion of the report heading failed because');
	$DbgMsg = _('The SQL used to delete the report headers was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo "<p>" . _('Report Deleted') . '<p>';
	unset($SelectedReport);
	include ('includes/footer.inc');
	exit;

}

if (!isset($SelectedReport)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReport will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Reports will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT reportid, reportheading FROM reportheaders ORDER BY reportid";
	$result = DB_query($sql,$db);

	echo '<table border=1>';
	echo '<tr><th>' . _('Report No') . '</th>
		<th>' . _('Report Title') . '</th>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}


	printf("<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s&SelectedReport=%s\">" . _('Design') . "</a></td>
		<td><a href=\"%s/SalesAnalReptCols.php?" . SID . "&ReportID=%s\">" . _('Define Columns') . "</a></td>
		<td><a href=\"%s/SalesAnalysis_UserDefined.php?" . SID . "&ReportID=%s&ProducePDF=True\">" . _('Make PDF Report') . "</a></td>
		<td><a href=\"%s/SalesAnalysis_UserDefined.php?" . SID . "&ReportID=%s&ProduceCVSFile=True\">" . _('Make CSV File') . "</a></td>
		<td><a href=\"%s&SelectedReport=%s&delete=1\" onclick=\"return confirm('" . _('Are you sure you wish to remove this report design?') . "');\">" . _('Delete') . "</td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0],
		$rootpath,
		$myrow[0],
		$rootpath,
		$myrow[0],
		$rootpath,
		$myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0]);

	}
	//END WHILE LIST LOOP
	echo '</table><p>';
}

//end of ifs and buts!



if (isset($SelectedReport)) {
	echo "<a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>" . _('Show All Defined Reports') . '</a>';
}

echo '<p>';


if (!isset($_GET['delete'])) {
	echo "<hr>";
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedReport)) {
		//editing an existing Report

		$sql = "SELECT reportid,
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
			WHERE reportid=$SelectedReport";

		$ErrMsg = _('The reports for display could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the report headers was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

		$myrow = DB_fetch_array($result);

		$ReportID = $myrow['reportid'];
		$_POST['ReportHeading']  = $myrow['reportheading'];
		$_POST['GroupByData1'] = $myrow['groupbydata1'];
		$_POST['NewPageAfter1'] = $myrow['newpageafter1'];
		$_POST['Upper1'] = $myrow['upper1'];
		$_POST['Lower1'] = $myrow['lower1'];
		$_POST['GroupByData2'] = $myrow['groupbydata2'];
		$_POST['NewPageAfter2'] = $myrow['newpageafter2'];
		$_POST['Upper2'] = $myrow['upper2'];
		$_POST['Lower2'] = $myrow['lower2'];
		$_POST['GroupByData3'] = $myrow['groupbydata3'];
		$_POST['Upper3'] = $myrow['upper3'];
		$_POST['Lower3'] = $myrow['lower3'];
		$_POST['GroupByData4'] = $myrow['groupbydata4'];
        	$_POST['Upper4'] = $myrow['upper4'];
        	$_POST['Lower4'] = $myrow['lower4'];

		echo "<input type=hidden name='SelectedReport' VALUE=$SelectedReport>";
		echo "<input type=hidden name='ReportID' VALUE=$ReportID>";
		echo '<font size=3 color=BLUE><b>' . _('Edit The Selected Report') . '</b></font>';
	} else {
		echo '<font size=3 color=BLUE><b>' . _('Define A New Report') . '</b></font>';
	}
	
	if (!isset($_POST['ReportHeading'])) {
		$_POST['ReportHeading']='';
	}
	echo '<table WIDTH=100% colspan=4><tr><td align=right>' . _('Report Heading') . ":</td><td colspan=2><input type='TEXT' size=80 maxlength=80 name=ReportHeading value='" . $_POST['ReportHeading'] . "'></td></tr>";

	echo '<tr><td>' . _('Group By 1') . ': <select name=GroupByData1>';

	GrpByDataOptions($_POST['GroupByData1']);

	echo '</select></td><td>' . _('Page Break After') . ': <select name=NewPageAfter1>';

	if ($_POST['NewPageAfter1']==0){
	  echo "<option selected value=0>" . _('No');
	  echo "<option value=1>" . _('Yes');
	} Else {
	  echo '<option value=0>' . _('No');
	  echo '<option selected value=1>' . _('Yes');
	}

	echo '</select></td>';
	
	if (!isset($_POST['Lower1'])) {
		$_POST['Lower1'] = '';
	}
	
	if (!isset($_POST['Upper1'])) {
		$_POST['Upper1'] = '';
	}
	echo '<td>' . _('From') . ": <input type='TEXT' name='Lower1' size=10 maxlength=10 VALUE='" . $_POST['Lower1'] . "'></td>";
	echo '<td>' . _('To') . ": <input type='TEXT' name='Upper1' size=10 maxlength=10 VALUE='" . $_POST['Upper1'] . "'></td></tr>";

	echo '<tr><td>' . _('Group By 2') . ': <select name=GroupByData2>';

	GrpByDataOptions($_POST['GroupByData2']);

	echo '</select></td><td>' . _('Page Break After') . ': <select name=NewPageAfter2>';

	if ($_POST['NewPageAfter2']==0){
	  echo '<option selected value=0>' . _('No');
	  echo '<option value=1>' . _('Yes');
	} Else {
	  echo '<option value=0>' . _('No');
	  echo '<option selected value=1>' . _('Yes');
	}
	
	if (!isset($_POST['Lower2'])) {
		$_POST['Lower2'] = '';
	}
	
	if (!isset($_POST['Upper2'])) {
		$_POST['Upper2'] = '';
	}

	echo '</select></td>';
	echo '<td>' . _('From') . ": <input type='TEXT' name='Lower2' size=10 maxlength=10 VALUE='" . $_POST['Lower2'] . "'></td>";
	echo '<td>' . _('To') . ": <input type='TEXT' name='Upper2' size=10 maxlength=10 VALUE='" . $_POST['Upper2'] . "'></td></tr>";

	echo '<tr><td>' . _('Group By 3') . ': <select name=GroupByData3>';

	GrpByDataOptions($_POST['GroupByData3']);

	echo '</select></td><td>' . _('Page Break After') . ': <select name=NewPageAfter3>';

	if ($_POST['NewPageAfter3']==0){
	 	echo '<option selected value=0>' . _('No');
	 	echo '<option value=1>' . _('Yes');
	} else {
	 	echo 'OPTION value=0>' . _('No');
	 	echo '<option selected value=1>' . _('Yes');
	}

	echo '</select></td>';
	
	if (!isset($_POST['Lower3'])) {
		$_POST['Lower3'] = '';
	}
	
	if (!isset($_POST['Upper3'])) {
		$_POST['Upper3'] = '';
	}

	echo '<td>' . _('From') . ": <input type='TEXT' name='Lower3' size=10 maxlength=10 VALUE='" . $_POST['Lower3'] . "'></td>";
	echo '<td>' . _('To') . ": <input type='TEXT' name='Upper3' size=10 maxlength=10 VALUE='" . $_POST['Upper3'] . "'></td></tr>";

	echo '<tr><td>' . _('Group By 4') .": <select name=GroupByData4>";

	GrpByDataOptions($_POST['GroupByData4']);

	echo "</select></td><td></td>";
	
	if (!isset($_POST['Lower4'])) {
		$_POST['Lower4'] = '';
	}
	
	if (!isset($_POST['Upper4'])) {
		$_POST['Upper4'] = '';
	}

	echo '<td>' . _('From') .": <input type='TEXT' name='Lower4' size=10 maxlength=10 VALUE='" . $_POST['Lower4'] . "'></td>";
	echo '<td>' . _('To') . ": <input type='TEXT' name='Upper4' size=10 maxlength=10 VALUE='" . $_POST['Upper4'] . "'></td></tr>";

	echo '</table>';

	echo "<div class='centre'><input type='Submit' name='submit' value='" . _('Enter Information') . "'></div></form>";

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>