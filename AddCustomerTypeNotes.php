<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Customer Type (Group) Notes');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'CustomerTypeNotes';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['NoteDate'])) {$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);}

if (isset($_GET['Id'])){
	$Id = (int)$_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['DebtorType'])){
	$DebtorType = $_POST['DebtorType'];
} elseif (isset($_GET['DebtorType'])){
	$DebtorType = $_GET['DebtorType'];
}
echo '<a class="toplink" href="' . $RootPath . '/SelectCustomer.php?DebtorType='.$DebtorType.'">' . __('Back to Select Customer') . '</a><br />';

if (isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((int)$_POST['Priority'])) {
		$InputError = 1;
		prnMsg( __('The Contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) >200) {
		$InputError = 1;
		prnMsg( __('The contacts notes must be two hundred characters or less long'), 'error');
	} elseif( trim($_POST['Note']) == '' ) {
		$InputError = 1;
		prnMsg( __('The contacts notes may not be empty'), 'error');
	}

	if (isset($Id) and $InputError !=1) {

		$SQL = "UPDATE debtortypenotes SET note='" . $_POST['Note'] . "',
											date='" . FormatDateForSQL($_POST['NoteDate']) . "',
											href='" . $_POST['Href'] . "',
											priority='" . $_POST['Priority'] . "'
										WHERE typeid ='".$DebtorType."'
										AND noteid='".$Id."'";
		$Msg = __('Customer Group Notes') . ' ' . $DebtorType  . ' ' . __('has been updated');
	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO debtortypenotes (typeid,
											href,
											note,
											date,
											priority)
									VALUES ('" . $DebtorType. "',
											'" . $_POST['Href'] . "',
											'" . $_POST['Note'] . "',
											'" . FormatDateForSQL($_POST['NoteDate']) . "',
											'" . $_POST['Priority'] . "')";
		$Msg = __('The contact group notes record has been added');
	}

	if ($InputError !=1) {
		$Result = DB_query($SQL);

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['NoteID']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL="DELETE FROM debtortypenotes
			WHERE noteid='".$Id."'
			AND typeid='".$DebtorType."'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg( __('The contact group note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);

}

if (!isset($Id)) {
	$SQLname="SELECT typename from debtortype where typeid='".$DebtorType."'";
	$Result = DB_query($SQLname);
	$MyRow = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . __('Search') . '" alt="" />'  . __('Notes for Customer Type').': <b>' .$MyRow['typename'] . '</b></p>
		<br />';

	$SQL = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE typeid='".$DebtorType."'
				ORDER BY date DESC";
	$Result = DB_query($SQL);
			//echo '<br />' . $SQL;

	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Date') . '</th>
			<th>' . __('Note') . '</th>
			<th>' . __('href') . '</th>
			<th>' . __('Priority') . '</th>
			<th colspan="2"></th>
		</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['date']), '</td>
				<td>', $MyRow['note'], '</td>
				<td>', $MyRow['href'], '</td>
				<td>', $MyRow['priority'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&amp;DebtorType=', $MyRow['typeid'], '">' .  __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&amp;DebtorType=', $MyRow['typeid'], '&amp;delete=1">' .  __('Delete') . '</a></td>
			</tr>';

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?DebtorType=' . $DebtorType . '">' . __('Review all notes for this Customer Type')  . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorType='.$DebtorType.'">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$SQL = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE noteid=".$Id."
					AND typeid='".$DebtorType."'";

		$Result = DB_query($SQL);
				//echo '<br />' . $SQL;

		$MyRow = DB_fetch_array($Result);

		$_POST['NoteID'] = $MyRow['noteid'];
		$_POST['Note']	= $MyRow['note'];
		$_POST['Href']  = $MyRow['href'];
		$_POST['NoteDate']  = ConvertSQLDate($MyRow['date']);
		$_POST['Priority']  = $MyRow['priority'];
		$_POST['typeid']  = $MyRow['typeid'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['NoteID'] . '" />';
		echo '<input type="hidden" name="DebtorType" value="' . $_POST['typeid'] . '" />';
		echo '<fieldset>
				<legend>', __('Amend Customer Type Note'), '</legend>
				<field>
					<label for="NoteID">' .  __('Note ID').':</label>
					<fieldtext>' . $_POST['NoteID'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', __('Create New Customer Type Note'), '</legend>';
		$_POST['NoteID'] = '';
		$_POST['Note']  = '';
		$_POST['Href']  = '';
		$_POST['NoteDate']  = Date($_SESSION['DefaultDateFormat']);
		$_POST['Priority']  = '1';
		$_POST['typeid']  = '';
	}

	echo '<field>
			<label for="Note">' . __('Contact Group Note').':</label>
			<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' .  $_POST['Note'] . '</textarea>
			<fieldhelp>', __('Write the customer type note here'), '</fieldhelp>
		</field>
		<field>
			<label for="Href">' .  __('Web site').':</label>
			<input type="url" name="Href" value="'. $_POST['Href'].'" size="35" maxlength="100" />
			<fieldhelp>', __('Any website associated with this note'), '</fieldhelp>
		</field>
		<field>
			<label for="NoteDate">' .  __('Date').':</label>
			<input required="required" name="NoteDate" type="date" value="'. FormatDateForSQL($_POST['NoteDate']). '" size="11" maxlength="10" />
			<fieldhelp>', __('The date of this note'), '</fieldhelp>
		</field>
		<field>
			<label for="Priority">' .  __('Priority').':</label>
			<input type="text" class="number" name="Priority" value="'. $_POST['Priority'] .'" size="1" maxlength="3" />
			<fieldhelp>', __('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="'. __('Enter Information').'" />
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
