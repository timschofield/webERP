<?php
/* $Id: ItemTags.php 6941 2014-10-26 23:18:08Z daintree $*/

include('includes/session.php');
$Title = _('Item Tags') . ' / ' . _('Maintenance');
include('includes/header.php');

if (isset($_POST['SelectedTag'])){
	$SelectedTag = mb_strtoupper($_POST['SelectedTag']);
} elseif (isset($_GET['SelectedTag'])){
	$SelectedTag = mb_strtoupper($_GET['SelectedTag']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Item Tags') .
	'" alt="" />' . _('Item Tags Setup') . '</p>';
echo '<div class="page_help_text">' . _('Add/edit/delete Item Tags') . '</div>';
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TagName']) >100) {
		$InputError = 1;
		prnMsg(_('The tag name in English must be 100 characters or less long'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagNameBahasa']) >100) {
		$InputError = 1;
		prnMsg(_('The tag name in Bahasa must be 100 characters or less long'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The tag name in English must contain at least one character'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

	if (mb_strlen($_POST['TagNameBahasa'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The tag name in Bahasa must contain at least one character'),'error');
		$Errors[$i] = 'ItemTag';
		$i++;
	}

 	$checksql = "SELECT count(*)
		     FROM stocktags
		     WHERE tagname = '" . $_POST['TagName'] . "'
				OR tagnamebahasa = '" . $_POST['TagNameBahasa'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedTag)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a tag called').' '.$_POST['TagName'].' or '.$_POST['TagNameBahasa'],'error');
		$Errors[$i] = 'TagName';
		$i++;
	}

	if (isset($SelectedTag) AND $InputError !=1) {

		$sql = "UPDATE stocktags
			SET tagname = LOWER('" . $_POST['TagName'] . "'),
				tagnamebahasa = LOWER('" . $_POST['TagNameBahasa'] . "')
			WHERE tagid = '" .$SelectedTag."'";

		$msg = _('The tag') . ' ' . $SelectedTag . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM stocktags
			     WHERE tagname = '" . $_POST['TagName'] . "'
					OR tagnamebahasa = '" . $_POST['TagNameBahasa'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The tag') . ' ' . $_POST['tagid'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO stocktags
						(tagname,
						tagnamebahasa)
					VALUES (LOWER('" . $_POST['TagName'] . "'),
							LOWER('" . $_POST['TagNameBahasa'] . "')
						   )";

			$msg = _('Item tag') . ' ' . $_POST["tagname"] .  ' - ' . $_POST["tagnamebahasa"] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
		$result = DB_query($sql);
		echo '<br />';
		prnMsg($msg,'success');
		unset($SelectedTag);
		unset($_POST['tagid']);
		unset($_POST['TagName']);
		unset($_POST['TagNameBahasa']);
	}

} elseif ( isset($_GET['delete']) ) {

	$result = DB_query("SELECT tagname, tagnamebahasa FROM stocktags WHERE tagid='".$SelectedTag."'");
	if (DB_Num_Rows($result)>0){
		$TypeRow = DB_fetch_array($result);
		$TagName = $TypeRow['tagname'];
		$TagNameBahasa = $TypeRow['tagnamebahasa'];

		$sql="DELETE FROM stocktags WHERE tagid='".$SelectedTag."'";
		$ErrMsg = _('The tag record could not be deleted because');
		$result = DB_query($sql,$ErrMsg);
		echo '<br />';
		prnMsg(_('Item tag') . ' ' . $TagName  . ' - ' . $TagNameBahasa  . ' ' . _('has been deleted') ,'success');
	}
	unset ($SelectedTag);
	unset($_GET['delete']);
}

if (!isset($SelectedTag)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTag will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT tagid, tagname, tagnamebahasa FROM stocktags ORDER BY tagname";
	$result = DB_query($sql);

	echo '<br /><table>
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Tag English') . '</th>
					<th class="SortedColumn">' . _('Tag Bahasa') . '</th>
				</tr>
			</thead>
			<tbody>';

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	printf('<tr class="striped_row">
			<td>%s</td>
			<td>%s</td>
			<td><a href="%sSelectedTag=%s">' . _('Edit') . '</a></td>
			<td><a href="%sSelectedTag=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Tag?') . '");\'>' . _('Delete') . '</a></td>
			</tr>',
			$myrow[1],
			$myrow[2],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow[0],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
			$myrow[0]);
	}
	echo '</tbody>
		</table>';
}

//end of ifs and buts!
if (isset($SelectedTag)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Item Tags Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing type
	if ( isset($SelectedTag) AND $SelectedTag!='' ) {

		$sql = "SELECT tagid,
			       tagname,
				   tagnamebahasa
		        FROM stocktags
		        WHERE tagid='".$SelectedTag."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['tagid'] = $myrow['tagid'];
		$_POST['TagName']  = $myrow['tagname'];
		$_POST['TagNameBahasa']  = $myrow['tagnamebahasa'];

		echo '<input type="hidden" name="SelectedTag" value="' . $SelectedTag . '" />
			<input type="hidden" name="tagid" value="' . $_POST['tagid'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing type code

		echo '<tr>
				<td>' . _('Tag ID') . ': ' . $_POST['tagid'] . '</td>
			</tr>';
	} else 	{
		// This is a new type so the user may volunteer a type code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TagName'])) {
		$_POST['TagName']='';
	}
	if (!isset($_POST['TagNameBahasa'])) {
		$_POST['TagNameBahasa']='';
	}
	echo '<tr>
			<td>' . _('Tag English') . ':</td>
			<td><input type="text" name="TagName"  required="required" title="' . _('The tag name in English is required') . '" value="' . $_POST['TagName'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Tag Bahasa') . ':</td>
			<td><input type="text" name="TagNameBahasa"  required="required" title="' . _('The tag name is in Bahasa required') . '" value="' . $_POST['TagNameBahasa'] . '" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
		</div>
	</div>
	</form>';

} // end if user wish to delete

include('includes/footer.php');
?>
