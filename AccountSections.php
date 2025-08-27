<?php

/* Defines the sections in the general ledger reports */

require(__DIR__ . '/includes/session.php');

$Title = __('Account Sections');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountSections';
include('includes/header.php');

// SOME TEST TO ENSURE THAT AT LEAST INCOME AND COST OF SALES ARE THERE
	$SQL= "SELECT sectionid FROM accountsection WHERE sectionid=1";
	$Result = DB_query($SQL);

	if( DB_num_rows($Result) == 0 ) {
		$SQL = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (1,
											'Income')";
		$Result = DB_query($SQL);
	}

	$SQL= "SELECT sectionid FROM accountsection WHERE sectionid=2";
	$Result = DB_query($SQL);

	if( DB_num_rows($Result) == 0 ) {
		$SQL = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (2,
											'Cost Of Sales')";
		$Result = DB_query($SQL);
	}
// DONE WITH MINIMUM TESTS


$Errors = array();

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (isset($_POST['SectionID'])) {
		$SQL = "SELECT sectionid
					FROM accountsection
					WHERE sectionid='".$_POST['SectionID']."'";
		$Result = DB_query($SQL);

		if((DB_num_rows($Result)!=0 AND !isset($_POST['SelectedSectionID']))) {
			$InputError = 1;
			prnMsg( __('The account section already exists in the database'),'error');
			$Errors[$i] = 'SectionID';
			$i++;
		}
	}
	if(ContainsIllegalCharacters($_POST['SectionName'])) {
		$InputError = 1;
		prnMsg( __('The account section name cannot contain any illegal characters') . ' ' . '" \' - &amp; or a space' ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if(mb_strlen($_POST['SectionName'])==0) {
		$InputError = 1;
		prnMsg( __('The account section name must contain at least one character') ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if(isset($_POST['SectionID']) AND (!is_numeric($_POST['SectionID']))) {
		$InputError = 1;
		prnMsg( __('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}
	if(isset($_POST['SectionID']) AND mb_strpos($_POST['SectionID'],".")>0) {
		$InputError = 1;
		prnMsg( __('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}

	if(isset($_POST['SelectedSectionID']) AND $_POST['SelectedSectionID']!='' AND $InputError !=1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/

		$SQL = "UPDATE accountsection SET sectionname='" . $_POST['SectionName'] . "'
				WHERE sectionid = '" . $_POST['SelectedSectionID'] . "'";

		$Msg = __('Record Updated');
	} elseif($InputError !=1) {

	/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */

		$SQL = "INSERT INTO accountsection (sectionid,
											sectionname
										) VALUES (
											'" . $_POST['SectionID'] . "',
											'" . $_POST['SectionName'] ."')";
		$Msg = __('Record inserted');
	}

	if($InputError!=1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg,'success');
		unset ($_POST['SelectedSectionID']);
		unset ($_POST['SectionID']);
		unset ($_POST['SectionName']);
	}

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$SQL= "SELECT COUNT(sectioninaccounts) AS sections FROM accountgroups WHERE sectioninaccounts='" . $_GET['SelectedSectionID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if($MyRow['sections']>0) {
		prnMsg( __('Cannot delete this account section because general ledger accounts groups have been created using this section'),'warn');
		echo '<div>',
			'<br />', __('There are'), ' ', $MyRow['sections'], ' ', __('general ledger accounts groups that refer to this account section'),
			'</div>';

	} else {
		//Fetch section name
		$SQL = "SELECT sectionname FROM accountsection WHERE sectionid='".$_GET['SelectedSectionID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$SectionName = $MyRow['sectionname'];

		$SQL="DELETE FROM accountsection WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";
		$Result = DB_query($SQL);
		prnMsg( $SectionName . ' ' . __('section has been deleted') . '!','success');

	} //end if account group used in GL accounts
	unset ($_GET['SelectedSectionID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedSectionID']);
	unset ($_POST['SectionID']);
	unset ($_POST['SectionName']);
}

if(!isset($_GET['SelectedSectionID']) AND !isset($_POST['SelectedSectionID'])) {

/*	An account section could be posted when one has been edited and is being updated
	or GOT when selected for modification
	SelectedSectionID will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT sectionid,
			sectionname
		FROM accountsection
		ORDER BY sectionid";

	$ErrMsg = __('Could not get account group sections because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<p class="page_title_text"><img alt="" class="noPrint" src="', $RootPath, '/css/', $Theme,
		'/images/maintenance.png" title="', // Icon image.
		__('Account Sections'), '" /> ', // Icon title.
		__('Account Sections'), '</p>';// Page title.

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">', __('Section Number'), '</th>
				<th class="SortedColumn">', __('Section Description'), '</th>
				<th class="noPrint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td class="number">', $MyRow['sectionid'], '</td>
				<td class="text">', $MyRow['sectionname'], '</td>
				<td class="noPrint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'].'?SelectedSectionID='.urlencode($MyRow['sectionid']), ENT_QUOTES, 'UTF-8'), '">', __('Edit'), '</a></td>
				<td class="noPrint">';
		if( $MyRow['sectionid'] == '1' or $MyRow['sectionid'] == '2' ) {
			echo '<b>', __('Restricted'), '</b>';
		} else {
			echo '<a href="', htmlspecialchars($_SERVER['PHP_SELF'].'?SelectedSectionID='.urlencode($MyRow['sectionid']).'&delete=1', ENT_QUOTES, 'UTF-8'), '">', __('Delete'), '</a>';
		}
		echo '</td>
			</tr>';
	} //END WHILE LIST LOOP
	echo '</tbody>
		</table>';
} //end of ifs and buts!


if(isset($_POST['SelectedSectionID']) or isset($_GET['SelectedSectionID'])) {
	echo '<a class="toplink" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Account Sections') . '</a>';
}

if(! isset($_GET['delete'])) {

	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" id="AccountSections" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	if(isset($_GET['SelectedSectionID'])) {
		//editing an existing section

		$SQL = "SELECT sectionid,
				sectionname
			FROM accountsection
			WHERE sectionid='" . $_GET['SelectedSectionID'] ."'";

		$Result = DB_query($SQL);
		if( DB_num_rows($Result) == 0 ) {
			prnMsg( __('Could not retrieve the requested section please try again.'),'warn');
			unset($_GET['SelectedSectionID']);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['SectionID'] = $MyRow['sectionid'];
			$_POST['SectionName'] = $MyRow['sectionname'];

			echo '<input name="SelectedSectionID" type="hidden" value="', $_POST['SectionID'], '" />';

			echo '<fieldset>
					<legend>', __('Edit Account Section Details'), '</legend>
					<field>
						<label for="SectionID">', __('Section Number'), ':</label>
						<fieldtext>', $_POST['SectionID'], '</fieldtext>
					</field>';
		}

	} else {

		if(!isset($_POST['SelectedSectionID'])) {
			$_POST['SelectedSectionID']='';
		}
		if(!isset($_POST['SectionID'])) {
			$_POST['SectionID']='';
		}
		if(!isset($_POST['SectionName'])) {
			$_POST['SectionName']='';
		}
		echo '<fieldset>
				<legend>', __('New Account Section Details'), '</legend>
				<field>
					<label for="SectionID">', __('Section Number'), ':</label>
					<input autofocus="autofocus" ',
						( in_array('SectionID',$Errors) ? 'class="inputerror number"' : 'class="number" ' ),
						'maxlength="4" name="SectionID" required="required" size="4" tabindex="1" type="text" value="', $_POST['SectionID'], '" />
					<fieldhelp>', __('Enter a unique integer identifier for this section'), '</fieldhelp>
				</field>';
	}
	echo	'<field>
				<label for="SectionName">', __('Section Description'), ':</label>
				<input ',
					( in_array('SectionName',$Errors) ? 'class="inputerror text" ' : 'class="text" ' ),
					'maxlength="30" name="SectionName" required="required" size="30" tabindex="2" type="text" value="', $_POST['SectionName'], '" />
				<fieldhelp>', __('Enter a description for this section'), '</fieldhelp>
			</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input name="submit" tabindex="3" type="submit" value="', __('Enter Information'), '" />
		</div>
	</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.php');
