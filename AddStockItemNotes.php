<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Item Notes');
$ViewTopic = 'Inventory';
$BookMark = 'ItemNotes';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_POST['NoteDate'])) {
	$_POST['NoteDate'] = ConvertSQLDate($_POST['NoteDate']);
}

if (isset($_GET['Id'])) {
	$Id = (int)$_GET['Id'];
} elseif (isset($_POST['Id'])) {
	$Id = (int)$_POST['Id'];
}
if (isset($_POST['StockID'])) {
	$StockID = $_POST['StockID'];
} elseif (isset($_GET['StockID'])) {
	$StockID = $_GET['StockID'];
}

echo '<a class="toplink" href="' . $RootPath . '/SelectProduct.php?StockID=' . $StockID . '">' . __('Back to Select Product') . '</a>';

if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (trim($_POST['Note']) == '') {
		$InputError = 1;
		prnMsg( __('The item note may not be empty'), 'error');
	}

	if (isset($Id) and $InputError != 1) {

		$SQL = "UPDATE stockitemnotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "'
				WHERE stockid ='" . $StockID . "'
				AND noteid='" . $Id . "'";
		$Msg = __('Stock Item Notes') . ' ' . $StockID  . ' ' . __('has been updated');
	} elseif ($InputError != 1) {

		$SQL = "INSERT INTO stockitemnotes (stockid,
										note,
										date)
				VALUES ('" . $StockID. "',
						'" . $_POST['Note'] . "',
						'" . FormatDateForSQL($_POST['NoteDate']) . "')";
		$Msg = __('The item note record has been added');
	}

	if ($InputError != 1) {
		$Result = DB_query($SQL);
				//echo '<br />' . $SQL;

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['Noteid']);
		unset($_POST['NoteDate']);
	}
} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL = "DELETE FROM stockitemnotes
			WHERE noteid='".$Id."'
			AND stockid='".$StockID."'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg( __('The item note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);
}

if (!isset($Id)) {
	$SQLname = "SELECT description FROM stockmaster
				WHERE stockid='".$StockID."'";
	$Result = DB_query($SQLname);
	$Row = DB_fetch_array($Result);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . __('Notes for Item').': <b>' .$Row['description'] . '</b></p>';

	$SQL = "SELECT noteid,
					stockid,
					note,
					date
				FROM stockitemnotes
				WHERE stockid='".$StockID."'
				ORDER BY date DESC";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
			<tr>
				<th>' . __('Date') . '</th>
				<th>' . __('Note') . '</th>
				<th colspan="2"></th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', ConvertSQLDate($MyRow['date']), '</td>
					<td>', $MyRow['note'], '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&StockID=', $MyRow['stockid'], '">' .  __('Edit').' </td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Id=', $MyRow['noteid'], '&StockID=', $MyRow['stockid'], '&delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this item note?') . '\');">' .  __('Delete'). '</td>
				</tr>';

		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="'.htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?StockID='.$StockID.'">' . __('Review all notes for this Item') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?StockID=' . $StockID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . __('Notes for Item').': <b>' .$StockID . '</b></p>';

		$SQL = "SELECT noteid,
						stockid,
						note,
						date
					FROM stockitemnotes
					WHERE noteid='".$Id."'
						AND stockid='".$StockID."'";

		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);

		$_POST['Noteid'] = $MyRow['noteid'];
		$_POST['Note']	= $MyRow['note'];
		$_POST['NoteDate']  = $MyRow['date'];
		$_POST['StockID']  = $MyRow['stockid'];
		echo '<input type="hidden" name="Id" value="'. $Id .'" />';
		echo '<input type="hidden" name="StockID" value="' . $_POST['StockID'] . '" />';
		echo '<fieldset>
				<legend>', __('Edit existing item note'), '</legend>
				<field>
					<label for="Noteid">' .  __('Note ID').':</label>
					<fieldtext>' . $_POST['Noteid'] . '</fieldtext>
				</field>';
	} else {
		echo '<fieldset>
				<legend>', __('Create new item note'), '</legend>';
	}

	echo '<field>
			<label for="Note">' . __('Item Note'). '</label>';
	if (isset($_POST['Note'])) {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32">' .$_POST['Note'] . '</textarea>
			<fieldhelp>', __('Write the item note here'), '</fieldhelp>
		</field>';
	} else {
		echo '<textarea name="Note" autofocus="autofocus" required="required" rows="3" cols="32"></textarea>
			<fieldhelp>', __('Write the item note here'), '</fieldhelp>
		</field>';
	}

	echo '<field>
			<label for="NoteDate">' . __('Date') . '</label>';
	if (isset($_POST['NoteDate'])) {
		echo '<input type="date" required name="NoteDate"  value="' . FormatDateForSQL($_POST['NoteDate']) . '" size="11" maxlength="10" />
			<fieldhelp>', __('The date of this note'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="date" required name="NoteDate" value="' . date('Y-m-d') . '" size="11" maxlength="10" />
			<fieldhelp>', __('The date of this note'), '</fieldhelp>
		</field>';
	}
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="submit" value="'.__('Enter Information').'" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');
