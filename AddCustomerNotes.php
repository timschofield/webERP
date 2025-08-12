<?php

include('includes/session.php');
global $RootPath, $Theme;

if (isset($_POST['NoteDate'])){$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);}

$Title = _('Customer Notes');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'CustomerNotes';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['Id'])){
	$Id = (int)$_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}

echo '<a class="toplink" href="' . $RootPath . '/SelectCustomer.php?DebtorNo=' . $DebtorNo . '">' . _('Back to Select Customer') . '</a>';

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer)$_POST['Priority'])) {
		$InputError = 1;
		prnMsg( _('The contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) >200) {
		$InputError = 1;
		prnMsg( _('The contact\'s notes must be two hundred characters or less long'), 'error');
	} elseif( trim($_POST['Note']) == '' ) {
		$InputError = 1;
		prnMsg( _('The contact\'s notes may not be empty'), 'error');
	}

	if (isset($Id) and $InputError !=1) {

		$SQL = "UPDATE custnotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "',
									href='" . $_POST['Href'] . "',
									priority='" . $_POST['Priority'] . "'
				WHERE debtorno ='".$DebtorNo."'
				AND noteid='".$Id."'";
		$Msg = _('Customer Notes') . ' ' . $DebtorNo  . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO custnotes (debtorno,
										href,
										note,
										date,
										priority)
				VALUES ('" . $DebtorNo. "',
						'" . $_POST['Href'] . "',
						'" . $_POST['Note'] . "',
						'" . FormatDateForSQL($_POST['NoteDate']) . "',
						'" . $_POST['Priority'] . "')";
		$Msg = _('The contact notes record has been added');
	}

	if ($InputError !=1) {
		$Result = DB_query($SQL);
				//echo '<br />' . $SQL;

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['Noteid']);
		unset($_POST['NoteDate']);
		unset($_POST['Href']);
		unset($_POST['Priority']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL="DELETE FROM custnotes
			WHERE noteid='".$Id."'
			AND debtorno='".$DebtorNo."'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg( _('The contact note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);
}

if (!isset($Id)) {
	$SQLname="SELECT * FROM debtorsmaster
				WHERE debtorno='".$DebtorNo."'";
	$Result = DB_query($SQLname);
	$Row = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . _('Notes for Customer').': <b>' .$Row['name'] . '</b></p>';

	$SQL = "SELECT noteid,
					debtorno,
					href,
					note,
					date,
					priority
				FROM custnotes
				WHERE debtorno='".$DebtorNo."'
				ORDER BY date DESC";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('WWW') . '</th>
			<th>' . _('Priority') . '</th>
			<th colspan="2"></th>
		</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['date']), '</td>
				<td>', $MyRow['note'], '</td>
				<td><a href="', $MyRow['href'], '">', $MyRow['href'], '</a></td>
				<td>', $MyRow['priority'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&DebtorNo=', $MyRow['debtorno'], '">' .  _('Edit').' </td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&DebtorNo=', $MyRow['debtorno'], '&delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this customer note?') . '\');">' .  _('Delete'). '</td>
			</tr>';

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="'.htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo='.$DebtorNo.'">' . _('Review all notes for this Customer') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo=' . $DebtorNo . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$SQL = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
					FROM custnotes
					WHERE noteid='".$Id."'
						AND debtorno='".$DebtorNo."'";

		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);

		$_POST['Noteid'] = $MyRow['noteid'];
		$_POST['Note']	= $MyRow['note'];
		$_POST['Href']  = $MyRow['href'];
		$_POST['NoteDate']  = $MyRow['date'];
		$_POST['Priority']  = $MyRow['priority'];
		$_POST['debtorno']  = $MyRow['debtorno'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['Noteid'] . '" />';
		echo '<input type="hidden" name="DebtorNo" value="' . $_POST['debtorno'] . '" />';
		echo '<fieldset>
				<legend>', _('Edit existing customer note'), '</legend>
				<field>
					<label for="Noteid">' .  _('Note ID').':</label>
					<fieldtext>' . $_POST['Noteid'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', _('Create new customer note'), '</legend>';
	}

	echo '<field>
			<label for="Note">' . _('Contact Note'). '</label>';
	if (isset($_POST['Note'])) {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' .$_POST['Note'] . '</textarea>
			<fieldhelp>', _('Write the customer note here'), '</fieldhelp>
		</field>';
	} else {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32"></textarea>
			<fieldhelp>', _('Write the customer note here'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="Href">' .  _('WWW') . '</label>';
	if (isset($_POST['Href'])) {
		echo '<input type="url" name="Href" value="'.$_POST['Href'].'" size="35" maxlength="100" />
			<fieldhelp>', _('Any website associated with this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="url" name="Href" size="35" maxlength="100" />
			<fieldhelp>', _('Any website associated with this note'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="NoteDate">' . _('Date') . '</label>';
	if (isset($_POST['NoteDate'])) {
		echo '<input type="date" required name="NoteDate"  value="' . FormatDateForSQL($_POST['NoteDate']) . '" size="11" maxlength="10" />
			<fieldhelp>', _('The date of this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="date" required name="NoteDate" value="' . date('Y-m-d') . '" size="11" maxlength="10" />
			<fieldhelp>', _('The date of this note'), '</fieldhelp>
		</field>';
	}
	echo '<field>
			<label for="Priority">' .  _('Priority'). '</label>';
	if (isset($_POST['Priority'])) {
		echo '<input type="text" class="number" required="required" name="Priority" class="number" value="' . $_POST['Priority']. '" size="1" maxlength="3" />
			<fieldhelp>', _('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="text" class="number" required="required"  name="Priority" value="1"  size="1" maxlength="3"/>
			<fieldhelp>', _('The priority level for this note, between 1 and 9'), '</fieldhelp>
		</field>';
	}
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="submit" value="'._('Enter Information').'" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
