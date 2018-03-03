<?php
/* Defines the sections in the general ledger reports */

include('includes/session.php');
$Title = _('Account Sections');
$ViewTopic = 'GeneralLedger';
$BookMark = 'AccountSections';
include('includes/header.php');

// SOME TEST TO ENSURE THAT AT LEAST INCOME AND COST OF SALES ARE THERE
	$sql= "SELECT sectionid FROM accountsection WHERE sectionid=1";
	$result = DB_query($sql);

	if( DB_num_rows($result) == 0 ) {
		$sql = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (1,
											'Income')";
		$result = DB_query($sql);
	}

	$sql= "SELECT sectionid FROM accountsection WHERE sectionid=2";
	$result = DB_query($sql);

	if( DB_num_rows($result) == 0 ) {
		$sql = "INSERT INTO accountsection (sectionid,
											sectionname)
									VALUES (2,
											'Cost Of Sales')";
		$result = DB_query($sql);
	}
// DONE WITH MINIMUM TESTS


if(isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if(isset($_POST['SectionID'])) {
		$sql="SELECT sectionid
					FROM accountsection
					WHERE sectionid='".$_POST['SectionID']."'";
		$result=DB_query($sql);

		if((DB_num_rows($result)!=0 AND !isset($_POST['SelectedSectionID']))) {
			$InputError = 1;
			prnMsg( _('The account section already exists in the database'),'error');
			$Errors[$i] = 'SectionID';
			$i++;
		}
	}
	if(ContainsIllegalCharacters($_POST['SectionName'])) {
		$InputError = 1;
		prnMsg( _('The account section name cannot contain any illegal characters') ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if(mb_strlen($_POST['SectionName'])==0) {
		$InputError = 1;
		prnMsg( _('The account section name must contain at least one character') ,'error');
		$Errors[$i] = 'SectionName';
		$i++;
	}
	if(isset($_POST['SectionID']) AND (!is_numeric($_POST['SectionID']))) {
		$InputError = 1;
		prnMsg( _('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}
	if(isset($_POST['SectionID']) AND mb_strpos($_POST['SectionID'],".")>0) {
		$InputError = 1;
		prnMsg( _('The section number must be an integer'),'error');
		$Errors[$i] = 'SectionID';
		$i++;
	}

	if(isset($_POST['SelectedSectionID']) AND $_POST['SelectedSectionID']!='' AND $InputError !=1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the delete code below*/

		$sql = "UPDATE accountsection SET sectionname='" . $_POST['SectionName'] . "'
				WHERE sectionid = '" . $_POST['SelectedSectionID'] . "'";

		$msg = _('Record Updated');
	} elseif($InputError !=1) {

	/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */

		$sql = "INSERT INTO accountsection (sectionid,
											sectionname
										) VALUES (
											'" . $_POST['SectionID'] . "',
											'" . $_POST['SectionName'] ."')";
		$msg = _('Record inserted');
	}

	if($InputError!=1) {
		//run the SQL from either of the above possibilites
		$result = DB_query($sql);
		prnMsg($msg,'success');
		unset ($_POST['SelectedSectionID']);
		unset ($_POST['SectionID']);
		unset ($_POST['SectionName']);
	}

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$sql= "SELECT COUNT(sectioninaccounts) AS sections FROM accountgroups WHERE sectioninaccounts='" . $_GET['SelectedSectionID'] . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);
	if($myrow['sections']>0) {
		prnMsg( _('Cannot delete this account section because general ledger accounts groups have been created using this section'),'warn');
		echo '<div>',
			'<br />', _('There are'), ' ', $myrow['sections'], ' ', _('general ledger accounts groups that refer to this account section'),
			'</div>';

	} else {
		//Fetch section name
		$sql = "SELECT sectionname FROM accountsection WHERE sectionid='".$_GET['SelectedSectionID'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);
		$SectionName = $myrow['sectionname'];

		$sql="DELETE FROM accountsection WHERE sectionid='" . $_GET['SelectedSectionID'] . "'";
		$result = DB_query($sql);
		prnMsg( $SectionName . ' ' . _('section has been deleted') . '!','success');

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

	$sql = "SELECT sectionid,
			sectionname
		FROM accountsection
		ORDER BY sectionid";

	$ErrMsg = _('Could not get account group sections because');
	$result = DB_query($sql,$ErrMsg);

	echo '<p class="page_title_text"><img alt="" class="noprint" src="', $RootPath, '/css/', $Theme,
		'/images/maintenance.png" title="', // Icon image.
		_('Account Sections'), '" /> ', // Icon title.
		_('Account Sections'), '</p>';// Page title.

	echo '<br />
	<table class="selection">
		<thead>
			<tr>
				<th class="ascending">', _('Section Number'), '</th>
				<th class="ascending">', _('Section Description'), '</th>
				<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($myrow = DB_fetch_array($result)) {

		echo '<tr class="striped_row">
				<td class="number">', $myrow['sectionid'], '</td>
				<td class="text">', $myrow['sectionname'], '</td>
				<td class="noprint"><a href="', htmlspecialchars($_SERVER['PHP_SELF'].'?SelectedSectionID='.urlencode($myrow['sectionid']), ENT_QUOTES, 'UTF-8'), '">', _('Edit'), '</a></td>
				<td class="noprint">';
		if( $myrow['sectionid'] == '1' or $myrow['sectionid'] == '2' ) {
			echo '<b>', _('Restricted'), '</b>';
		} else {
			echo '<a href="', htmlspecialchars($_SERVER['PHP_SELF'].'?SelectedSectionID='.urlencode($myrow['sectionid']).'&delete=1', ENT_QUOTES, 'UTF-8'), '">', _('Delete'), '</a>';
		}
		echo '</td>
			</tr>';
	} //END WHILE LIST LOOP
	echo '</tbody>
		</table>';
} //end of ifs and buts!


if(isset($_POST['SelectedSectionID']) or isset($_GET['SelectedSectionID'])) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Account Sections') . '</a></div>';
}

if(! isset($_GET['delete'])) {

	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" id="AccountSections" method="post">',
		'<div class="noprint"><br />',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	if(isset($_GET['SelectedSectionID'])) {
		//editing an existing section

		$sql = "SELECT sectionid,
				sectionname
			FROM accountsection
			WHERE sectionid='" . $_GET['SelectedSectionID'] ."'";

		$result = DB_query($sql);
		if( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested section please try again.'),'warn');
			unset($_GET['SelectedSectionID']);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['SectionID'] = $myrow['sectionid'];
			$_POST['SectionName'] = $myrow['sectionname'];

			echo '<input name="SelectedSectionID" type="hidden" value="', $_POST['SectionID'], '" />
				<table class="selection">
				<thead>
					<tr>
						<th colspan="2">', _('Edit Account Section Details'), '</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>', _('Section Number'), ':</td>
						<td>', $_POST['SectionID'], '</td>
					</tr>';
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
		echo '<table class="selection">
			<thead>
				<tr>
					<th colspan="2">', _('New Account Section Details'), '</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>', _('Section Number'), ':</td>
					<td><input autofocus="autofocus" ',
						( in_array('SectionID',$Errors) ? 'class="inputerror number"' : 'class="number" ' ),
						'maxlength="4" name="SectionID" required="required" size="4" tabindex="1" type="text" value="', $_POST['SectionID'], '" /></td>
				</tr>';
	}
	echo	'<tr>
				<td>', _('Section Description'), ':</td>
				<td><input ',
					( in_array('SectionName',$Errors) ? 'class="inputerror text" ' : 'class="text" ' ),
					'maxlength="30" name="SectionName" required="required" size="30" tabindex="2" type="text" value="', $_POST['SectionName'], '" /></td>
			</tr>
			<tr>
				<td class="centre" colspan="2"><input name="submit" tabindex="3" type="submit" value="', _('Enter Information'), '" /></td>
			</tr>
		</tbody>
		</table>
		<br />
		</div>
		</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
