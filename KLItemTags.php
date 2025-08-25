<?php

include('includes/session.php');

$Title = __('Item Tags') . ' / ' . __('Maintenance');
include('includes/header.php');

include('includes/UIGeneralFunctions.php');
include('includes/KUIGeneralFunctions.php');

if (isset($_POST['SelectedTag'])){
	$SelectedTag = mb_strtoupper($_POST['SelectedTag']);
} elseif (isset($_GET['SelectedTag'])){
	$SelectedTag = mb_strtoupper($_GET['SelectedTag']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Item Tags') .
	'" alt="" />' . __('Item Tags Setup') . '</p>';
echo '<div class="page_help_text">' . __('Add/edit/delete Item Tags') . '</div>';
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TagName']) >100) {
		$InputError = 1;
		prnMsg(__('The tag name in English must be 100 characters or less long'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagNameBahasa']) >100) {
		$InputError = 1;
		prnMsg(__('The tag name in Bahasa must be 100 characters or less long'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(__('The tag name in English must contain at least one character'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagNameBahasa'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(__('The tag name in Bahasa must contain at least one character'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

 	$CheckSQL = "SELECT count(*)
		     FROM stocktags
		     WHERE tagname = '" . $_POST['TagName'] . "'
				OR tagnamebahasa = '" . $_POST['TagNameBahasa'] . "'";
	$Checkresult=DB_query($CheckSQL);
	$CheckRow=DB_fetch_row($Checkresult);
	if ($CheckRow[0]>0 and !isset($SelectedTag)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(__('You already have a tag called').' '.$_POST['TagName'].' or '.$_POST['TagNameBahasa'],'error');
		$Errors[$i] = 'TagName';
		$i++;
	}

	if (isset($SelectedTag) AND $InputError !=1) {

		$SQL = "UPDATE stocktags
			SET tagname = LOWER('" . $_POST['TagName'] . "'),
				tagnamebahasa = LOWER('" . $_POST['TagNameBahasa'] . "')
			WHERE tagid = '" .$SelectedTag."'";

		$Msg = __('The tag') . ' ' . $SelectedTag . ' ' .  __('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM stocktags
			     WHERE tagname = '" . $_POST['TagName'] . "'
					OR tagnamebahasa = '" . $_POST['TagNameBahasa'] . "'";

		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The tag') . ' ' . $_POST['tagid'] . __(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO stocktags
						(tagname,
						tagnamebahasa)
					VALUES (LOWER('" . $_POST['TagName'] . "'),
							LOWER('" . $_POST['TagNameBahasa'] . "')
						   )";

			$Msg = __('Item tag') . ' ' . $_POST["tagname"] .  ' - ' . $_POST["tagnamebahasa"] .  ' ' . __('has been created');

		}
	}

	if ( $InputError !=1) {
		$Result = DB_query($SQL);
		echo '<br />';
		prnMsg($Msg,'success');
		unset($SelectedTag);
		unset($_POST['tagid']);
		unset($_POST['TagName']);
		unset($_POST['TagNameBahasa']);
	}

} elseif ( isset($_GET['delete']) ) {

	$Result = DB_query("SELECT tagname, tagnamebahasa FROM stocktags WHERE tagid='".$SelectedTag."'");
	if (DB_Num_Rows($Result)>0){
		$TypeRow = DB_fetch_array($Result);
		$TagName = $TypeRow['tagname'];
		$TagNameBahasa = $TypeRow['tagnamebahasa'];

		$SQL="DELETE FROM stocktags WHERE tagid='".$SelectedTag."'";
		$ErrMsg = __('The tag record could not be deleted because');
		$Result = DB_query($SQL,$ErrMsg);
		echo '<br />';
		prnMsg(__('Item tag') . ' ' . $TagName  . ' - ' . $TagNameBahasa  . ' ' . __('has been deleted') ,'success');
	}
	unset ($SelectedTag);
	unset($_GET['delete']);
}

if (!isset($SelectedTag)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTag will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT tagid, tagname, tagnamebahasa FROM stocktags ORDER BY tagname";
	$Result = DB_query($SQL);

	echo '<br /><table>
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Tag English') . '</th>
					<th class="SortedColumn">' . __('Tag Bahasa') . '</th>
				</tr>
			</thead>
			<tbody>';

$k=0; //row colour counter

while ($MyRow = DB_fetch_row($Result)) {
	echo '<tr class="striped_row">
			<td>' . $MyRow[1] . '</td>
			<td>' . $MyRow[2] . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTag=' . $MyRow[0] . '">' . __('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTag=' . $MyRow[0] . '&amp;delete=yes" onclick=\'return confirm("' . __('Are you sure you wish to delete this Tag?') . '");\'>' . __('Delete') . '</a></td>
			</tr>';
	}
	echo '</tbody>
		</table>';
}

//end of ifs and buts!
if (isset($SelectedTag)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show All Item Tags Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing type
	if ( isset($SelectedTag) AND $SelectedTag!='' ) {

		$SQL = "SELECT tagid,
			       tagname,
				   tagnamebahasa
		        FROM stocktags
		        WHERE tagid='".$SelectedTag."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['tagid'] = $MyRow['tagid'];
		$_POST['TagName']  = $MyRow['tagname'];
		$_POST['TagNameBahasa']  = $MyRow['tagnamebahasa'];

		echo '<input type="hidden" name="SelectedTag" value="' . $SelectedTag . '" />
			<input type="hidden" name="tagid" value="' . $_POST['tagid'] . '" />';

		echo '<fieldset>
				<legend>' . __('Edit Tag Details') . '</legend>';

	}
	else {
		echo '<fieldset>
				<legend>' . __('New Tag Details') . '</legend>';

	}

	if (!isset($_POST['TagName'])) {
		$_POST['TagName']='';
	}
	if (!isset($_POST['TagNameBahasa'])) {
		$_POST['TagNameBahasa']='';
	}

	echo FieldToSelectOneText('TagName', $_POST['TagName'], 50, 100, __('Tag English'), '', '', '', true, false);
	echo FieldToSelectOneText('TagNameBahasa', $_POST['TagNameBahasa'], 50, 100, __('Tag Bahasa'), '', '', '', true, false);
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Accept'));

	echo '</div>
	</form>';

} // end if user wish to delete

include('includes/footer.php');

