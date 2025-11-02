<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Product Spec Groups Maintenance');
$ViewTopic = 'QualityAssurance';// Filename in ManualContents.php's TOC.
$BookMark = 'QA_ProdSpecs';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Product Specification Groups') . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedGroup'])){
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif (isset($_POST['SelectedGroup'])){
	$SelectedGroup = $_POST['SelectedGroup'];
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs are sensible

	if (mb_strlen($_POST['GroupName']) < 1) {
		$InputError = 1;
		prnMsg(__('The group name must exist'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (mb_strlen($_POST['GroupName']) > 50) {
		$InputError = 1;
		prnMsg(__('The group name must be fifty characters or less long'),'error');
		$Errors[$i] = 'GroupName';
		$i++;
	}
	if (empty($_POST['GroupByNo']) OR !is_numeric(filter_number_format($_POST['GroupByNo'])) OR filter_number_format($_POST['GroupByNo']) <= 0){
		$InputError = 1;
		prnMsg( __('The group by number must be a positive numeric value') ,'error');
		$Errors[$i] = 'GroupByNo';
		$i++;
	}
	if (empty($_POST['Labels']) OR mb_strlen($_POST['Labels']) > 240) {
		$InputError = 1;
		prnMsg( __('The labels field is required and must be 240 characters or less long') ,'error');
		$Errors[$i] = 'Labels';
		$i++;
	}
	if (!empty($_POST['HeaderTitle']) AND mb_strlen($_POST['HeaderTitle']) > 100) {
		$InputError = 1;
		prnMsg( __('The header title must be 100 characters or less long') ,'error');
		$Errors[$i] = 'HeaderTitle';
		$i++;
	}
	if (!empty($_POST['TrailerText']) AND mb_strlen($_POST['TrailerText']) > 240) {
		$InputError = 1;
		prnMsg( __('The trailer text must be 240 characters or less long') ,'error');
		$Errors[$i] = 'TrailerText';
		$i++;
	}
	if (empty($_POST['NumCols']) OR !in_array($_POST['NumCols'], array('2', '3'))) {
		$InputError = 1;
		prnMsg( __('Number of columns must be either 2 or 3') ,'error');
		$Errors[$i] = 'NumCols';
		$i++;
	}

	// Validate that labels comma count matches numcols
	if (!empty($_POST['Labels']) AND !empty($_POST['NumCols'])) {
		$labelCount = count(explode(',', $_POST['Labels']));
		if ($labelCount != $_POST['NumCols']) {
			$InputError = 1;
			prnMsg( __('The number of labels (comma separated) must match the number of columns') ,'error');
			$Errors[$i] = 'Labels';
			$i++;
		}
	}

	if (isset($SelectedGroup) AND $InputError !=1) {

		/*SelectedGroup could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		// Check if groupname is being changed and if qatests exist with the old name
		$SQL = "SELECT groupname FROM prodspecgroups WHERE groupid = '" . $SelectedGroup . "'";
		$Result = DB_query($SQL);
		$OldRow = DB_fetch_array($Result);
		$OldGroupName = $OldRow['groupname'];

		if ($OldGroupName != $_POST['GroupName']) {
			// Check if qatests exist with the old name
			$SQL = "SELECT COUNT(*) FROM qatests WHERE qatests.groupby = '" . $OldGroupName . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$InputError = 1;
				prnMsg( __('Cannot change the group name because QA tests have been created referring to this group name'),'error');
				echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('QA tests that refer to this group name');
			}
		}

		if ($InputError !=1) {
			$SQL = "UPDATE prodspecgroups SET
							groupname='" . $_POST['GroupName'] . "',
							groupbyNo='" . filter_number_format($_POST['GroupByNo']) . "',
							headertitle=" . (empty($_POST['HeaderTitle']) ? 'NULL' : "'" . $_POST['HeaderTitle'] . "'") . ",
							trailertext=" . (empty($_POST['TrailerText']) ? 'NULL' : "'" . $_POST['TrailerText'] . "'") . ",
							labels='" . $_POST['Labels'] . "',
							numcols='" . $_POST['NumCols'] . "'
					WHERE groupid = '" . $SelectedGroup . "'";

			$Msg = __('The product specification group record has been updated') . '.';
		}
	} else if ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new product spec group form */

		$SQL = "INSERT INTO prodspecgroups (groupname,
							groupbyNo,
							headertitle,
							trailertext,
							labels,
							numcols)
					VALUES (
						'" . $_POST['GroupName'] . "',
						'" . filter_number_format($_POST['GroupByNo']) . "',
						" . (empty($_POST['HeaderTitle']) ? 'NULL' : "'" . $_POST['HeaderTitle'] . "'") . ",
						" . (empty($_POST['TrailerText']) ? 'NULL' : "'" . $_POST['TrailerText'] . "'") . ",
						'" . $_POST['Labels'] . "',
						'" . $_POST['NumCols'] . "'
					)";

		$Msg = __('The product specification group record has been added') . '.';
	}
	if ($InputError !=1){
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg,'success');
		unset($SelectedGroup);
		unset($_POST['GroupName']);
		unset($_POST['GroupByNo']);
		unset($_POST['HeaderTitle']);
		unset($_POST['TrailerText']);
		unset($_POST['Labels']);
		unset($_POST['NumCols']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN qatests

	// First get the groupname for the selected groupid
	$SQL = "SELECT groupname FROM prodspecgroups WHERE groupid = '" . $SelectedGroup . "'";
	$Result = DB_query($SQL);
	$GroupRow = DB_fetch_array($Result);
	$GroupName = $GroupRow['groupname'];

	$SQL= "SELECT COUNT(*) FROM qatests WHERE qatests.groupby = '" . $GroupName . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg( __('Cannot delete this product specification group because QA tests have been created referring to this group'),'warn');
		echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('QA tests that refer to this group');
	} else {
		//only delete if not used in qatests

		$SQL="DELETE FROM prodspecgroups WHERE groupid='" . $SelectedGroup . "'";
		$Result = DB_query($SQL);
		prnMsg( __('The product specification group record has been deleted') . '!','success');
	}
	//end if group used in qatests

}

if (!isset($SelectedGroup)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGroup will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of product spec groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT groupid, groupname, groupbyNo, headertitle, labels, numcols FROM prodspecgroups ORDER BY groupbyNo";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="8"><h3>' . __('Product Specification Groups') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . __('Group ID') . '</th>
			<th class="SortedColumn">' . __('Group Name') . '</th>
			<th class="SortedColumn">' . __('Group By No.') . '</th>
			<th class="SortedColumn">' . __('Header Title') . '</th>
			<th class="SortedColumn">' . __('Labels') . '</th>
			<th class="SortedColumn">' . __('Num Cols') . '</th>
		</tr>
	</thead>';

	while ($MyRow=DB_fetch_array($Result)) {

		$HeaderText = empty($MyRow['headertitle']) ? __('N/A') : $MyRow['headertitle'];

	echo '<tr class="striped_row">
			<td>', $MyRow['groupid'], '</td>
			<td>', $MyRow['groupname'], '</td>
			<td>', $MyRow['groupbyNo'], '</td>
			<td>', $HeaderText, '</td>
			<td>', $MyRow['labels'], '</td>
			<td>', $MyRow['numcols'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedGroup=', $MyRow['groupid'], '">' . __('Edit') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedGroup=', $MyRow['groupid'], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this product specification group?') . '\');">' . __('Delete') . '</a></td>
		</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedGroup)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show all Product Specification Group Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedGroup)) {
		//editing an existing product spec group

		$SQL = "SELECT groupid,
						groupname,
						groupbyNo,
						headertitle,
						trailertext,
						labels,
						numcols
					FROM prodspecgroups
					WHERE groupid='" . $SelectedGroup . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['GroupName'] = $MyRow['groupname'];
		$_POST['GroupByNo']  = $MyRow['groupbyNo'];
		$_POST['HeaderTitle']  = $MyRow['headertitle'];
		$_POST['TrailerText']  = $MyRow['trailertext'];
		$_POST['Labels']  = $MyRow['labels'];
		$_POST['NumCols']  = $MyRow['numcols'];

		echo '<input type="hidden" name="SelectedGroup" value="' . $SelectedGroup . '" />';
		echo '<fieldset>';
		echo '<legend>' . __('Update Product Specification Group.') . '</legend>';
		echo '<field>
				<label for="GroupID">' . __('Group ID') . ':</label>
				<fieldtext>' . $MyRow['groupid'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedGroup only do the else when a new record is being entered

		if (!isset($_POST['GroupName'])) $_POST['GroupName']='';
		if (!isset($_POST['GroupByNo'])) $_POST['GroupByNo']='1';
		if (!isset($_POST['HeaderTitle'])) $_POST['HeaderTitle']='';
		if (!isset($_POST['TrailerText'])) $_POST['TrailerText']='';
		if (!isset($_POST['Labels'])) $_POST['Labels']='';
		if (!isset($_POST['NumCols'])) $_POST['NumCols']='3';

		echo '<fieldset>';
		echo '<legend>' . __('New Product Specification Group.') . '</legend>';
	}

	echo '<field>
			<label for="GroupName">' . __('Group Name') . ':</label>
			<input type="text" name="GroupName"' . (in_array('GroupName',$Errors) ? ' class="inputerror"' : '' ) .' ' . (!isset($SelectedGroup) ? 'autofocus="autofocus"' : 'autofocus="autofocus"') . ' required="required" value="' . $_POST['GroupName'] . '" size="35" maxlength="50" />
			<fieldhelp>' . __('A descriptive name to identify this product specification group') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="GroupByNo">' . __('Group By Number') . ':</label>
			<input type="text"' . (in_array('GroupByNo',$Errors) ? ' class="inputerror"' : '' ) .' name="GroupByNo" required="required" class="integer" value="' . $_POST['GroupByNo'] . '" size="6" maxlength="11" />
			<fieldhelp>' . __('A numeric value used for ordering groups') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="HeaderTitle">' . __('Header Title') . ':</label>
			<input type="text"' . (in_array('HeaderTitle',$Errors) ? ' class="inputerror"' : '' ) .' name="HeaderTitle" value="' . $_POST['HeaderTitle'] . '" size="60" maxlength="100" />
			<fieldhelp>' . __('Optional header title for this group') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="TrailerText">' . __('Trailer Text') . ':</label>
			<input type="text"' . (in_array('TrailerText',$Errors) ? ' class="inputerror"' : '' ) .' name="TrailerText" value="' . $_POST['TrailerText'] . '" size="60" maxlength="240" />
			<fieldhelp>' . __('Optional trailer text for this group') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="NumCols">' . __('Number of Columns') . ':</label>
			<select name="NumCols"' . (in_array('NumCols',$Errors) ? ' class="inputerror"' : '' ) . ' required="required">
				<option value="2"' . ($_POST['NumCols'] == '2' ? ' selected="selected"' : '') . '>2</option>
				<option value="3"' . ($_POST['NumCols'] == '3' ? ' selected="selected"' : '') . '>3</option>
			</select>
			<fieldhelp>' . __('Select 2 or 3 columns for this group. If you select 3 columns the Testing Method on the group will display') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="Labels">' . __('Labels') . ':</label>
			<input type="text"' . (in_array('Labels',$Errors) ? ' class="inputerror"' : '' ) .' name="Labels" required="required" value="' . $_POST['Labels'] . '" size="60" maxlength="240" />
			<fieldhelp>' . __('Comma separated list of column labels - must match the number of columns') . '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="'.__('Enter Information').'" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.php');