<?php

include('includes/session.php');

$Title = _('Sales Area Maintenance');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'Areas';
include('includes/header.php');

if (isset($_GET['SelectedArea'])){
	$SelectedArea = mb_strtoupper($_GET['SelectedArea']);
} elseif (isset($_POST['SelectedArea'])){
	$SelectedArea = mb_strtoupper($_POST['SelectedArea']);
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$_POST['AreaCode'] = mb_strtoupper($_POST['AreaCode']);
	$SQL = "SELECT areacode FROM areas WHERE areacode='".$_POST['AreaCode']."'";
	$Result = DB_query($SQL);
	// mod to handle 3 char area codes
	if (mb_strlen($_POST['AreaCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The area code must be three characters or less long'),'error');
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif (DB_num_rows($Result)>0 AND !isset($SelectedArea)){
		$InputError = 1;
		prnMsg(_('The area code entered already exists'),'error');
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif (mb_strlen($_POST['AreaDescription']) >25) {
		$InputError = 1;
		prnMsg(_('The area description must be twenty five characters or less long'),'error');
		$Errors[$i] = 'AreaDescription';
		$i++;
	} elseif ( trim($_POST['AreaCode']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area code may not be empty'),'error');
		$Errors[$i] = 'AreaCode';
		$i++;
	} elseif ( trim($_POST['AreaDescription']) == '' ) {
		$InputError = 1;
		prnMsg(_('The area description may not be empty'),'error');
		$Errors[$i] = 'AreaDescription';
		$i++;
	}

	if (isset($SelectedArea) AND $InputError !=1) {

		/*SelectedArea could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE areas SET areadescription='" . $_POST['AreaDescription'] . "'
								WHERE areacode = '" . $SelectedArea . "'";

		$Msg = _('Area code') . ' ' . $SelectedArea  . ' ' . _('has been updated');

	} elseif ($InputError !=1) {

	/*Selectedarea is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new area form */

		$SQL = "INSERT INTO areas (areacode,
									areadescription
								) VALUES (
									'" . $_POST['AreaCode'] . "',
									'" . $_POST['AreaDescription'] . "'
								)";

		$SelectedArea = $_POST['AreaCode'];
		$Msg = _('New area code') . ' ' . $_POST['AreaCode'] . ' ' . _('has been inserted');
	} else {
		$Msg = '';
	}

	//run the SQL from either of the above possibilites
	if ($InputError !=1) {
		$ErrMsg = _('The area could not be added or updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		unset($SelectedArea);
		unset($_POST['AreaCode']);
		unset($_POST['AreaDescription']);
		prnMsg($Msg,'success');
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'

	$SQL= "SELECT COUNT(branchcode) AS branches FROM custbranch WHERE custbranch.area='$SelectedArea'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['branches']>0) {
		$CancelDelete = 1;
		prnMsg( _('Cannot delete this area because customer branches have been created using this area'),'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow['branches'] . ' ' . _('branches using this area code');

	} else {
		$SQL= "SELECT COUNT(area) AS records FROM salesanalysis WHERE salesanalysis.area ='$SelectedArea'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow['records']>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this area because sales analysis records exist that use this area'),'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow['records'] . ' ' . _('sales analysis records referring this area code');
		}
	}

	if ($CancelDelete==0) {
		$SQL="DELETE FROM areas WHERE areacode='" . $SelectedArea . "'";
		$Result = DB_query($SQL);
		prnMsg(_('Area Code') . ' ' . $SelectedArea . ' ' . _('has been deleted') .' !','success');
	} //end if Delete area
	unset($SelectedArea);
	unset($_GET['delete']);
}

if (!isset($SelectedArea)) {

	$SQL = "SELECT areacode,
					areadescription
				FROM areas";
	$Result = DB_query($SQL);

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<table class="selection">
			<tr>
				<th>' . _('Area Code') . '</th>
				<th>' . _('Area Name') . '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['areacode'] . '</td>
				<td>' . $MyRow['areadescription'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedArea=' . $MyRow['areacode'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedArea=' . $MyRow['areacode'] . '&amp;delete=yes">' . _('Delete') . '</a></td>
				<td><a href="SelectCustomer.php?Area=' . $MyRow['areacode'] . '">' . _('View Customers from this Area') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedArea)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Areas Defined') . '</a></div>';
}


if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedArea)) {
		//editing an existing area

		$SQL = "SELECT areacode,
						areadescription
					FROM areas
					WHERE areacode='" . $SelectedArea . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['AreaCode'] = $MyRow['areacode'];
		$_POST['AreaDescription']  = $MyRow['areadescription'];

		echo '<input type="hidden" name="SelectedArea" value="' . $SelectedArea . '" />';
		echo '<input type="hidden" name="AreaCode" value="' .$_POST['AreaCode'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit existing Sales Area details'), '</legend>
				<field>
					<label for="AreaCode">' . _('Area Code') . ':</label>
					<fieldtext>' . $_POST['AreaCode'] . '</fieldtext>
				</field>';

	} else {
		if (!isset($_POST['AreaCode'])) {
			$_POST['AreaCode'] = '';
		}
		if (!isset($_POST['AreaDescription'])) {
			$_POST['AreaDescription'] = '';
		}
		echo '<fieldset>
				<legend>', _('Create New Sales Area details'), '</legend>
			<field>
				<label for="AreaCode">' . _('Area Code') . ':</label>
				<input tabindex="1" ' . (in_array('AreaCode',$Errors) ? 'class="inputerror"' : '' ) .' type="text" name="AreaCode" required="required" autofocus="autofocus" value="' . $_POST['AreaCode'] . '" size="3" maxlength="3" title="" />
				<fieldhelp>' . _('Enter the sales area code - up to 3 characters are allowed') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="AreaDescription">' . _('Area Name') . ':</label>
			<input tabindex="2" ' . (in_array('AreaDescription',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" required="required" name="AreaDescription" value="' . $_POST['AreaDescription'] .'" size="26" maxlength="25" title="" />
			<fieldhelp>' . _('Enter the description of the sales area') . '</fieldhelp>
		</field>';

	echo '</fieldset>
				<div class="centre">
					<input tabindex="3" type="submit" name="submit" value="' . _('Enter Information') .'" />
				</div>
	</form>';

 } //end if record deleted no point displaying form to add record

include('includes/footer.php');
