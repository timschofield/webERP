<?php
/* $Revision: 1.0 $ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Maintenance Of Petty Cash Expenses For a Type Tab');
include('includes/header.inc');

if (isset($_POST['SelectedType'])){
	$SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = strtoupper($_GET['SelectedType']);
}

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = strtoupper($_GET['SelectedTabs']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	if ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM pctabexpenses
			     WHERE typetabcode= '" .  $_POST['SelectedTabs'] . "'
				 AND codeexpense = '" .  $_POST['SelectedExpense'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] >0) {
			$InputError = 1;
			prnMsg( _('The Expense ') . $_POST['codeexpense'] . _(' already exist in this Type of Tab.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pctabexpenses
						(typetabcode,
						codeexpense)
				VALUES ('" . $_POST['SelectedTabs'] . "',
						'" . $_POST['SelectedExpense'] . "')";

			$msg = _('Expense code:') . ' ' . $_POST["SelectedExpense"].' '._('for Type of Tab:') . $_POST["SelectedTabs"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(typetabcode)
			     FROM pctypetabs";
			$result = DB_query($checkSql, $db);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');

		unset($_POST['SelectedExpense']);
	}

} elseif ( isset($_GET['delete']) ) {


			$sql="DELETE FROM pctabexpenses
				WHERE typetabcode='$SelectedTabs'
				AND codeexpense='$SelectedType'";
			$ErrMsg = _('The Tab Type record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Expense code').' '. $SelectedTabs . _('for type of tab').' '. $SelectedType .' '. _('has been deleted') ,'success');
			unset ($SelectedType);
			unset($_GET['delete']);


}

if (!isset($SelectedTabs)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<p><table border=1>'; //Main table
	echo '<td><table>'; // First column

echo '<tr><td>' . _('Select Type of Tab') . ":</td><td><select name='SelectedTabs'>";

	DB_free_result($result);
	$SQL = "SELECT typetabcode,typetabdescription
		FROM pctypetabs";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectedTabs']) and $myrow['typetabcode']==$_POST['SelectedTabs']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['typetabcode'] . "'>" . $myrow['typetabcode'] . ' - ' . $myrow['typetabdescription'];

	} //end while loop

	echo '</select></td></tr>';

	   	echo '</table>'; // close table in first column
   	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=process VALUE="' . _('Accept') . '"><input type=submit name=Cancel VALUE="' . _('Cancel') . '"></div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process'])OR isset($SelectedTabs)) {

	echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Expense Codes for Type of Tab ') . '' .$SelectedTabs. '<a/></div><p>';

	$sql = "SELECT pctabexpenses.codeexpense, pcexpenses.description
			FROM pctabexpenses,pcexpenses
			WHERE pctabexpenses.codeexpense=pcexpenses.codeexpense
				AND pctabexpenses.typetabcode='$SelectedTabs'
			ORDER BY pctabexpenses.codeexpense ASC";

	$result = DB_query($sql,$db);

	echo '<br><table BORDER=1>';
	echo "<tr>
		<th>" . _('Expense Code') . "</th>
		<th>" . _('Description') . "</th>
	</tr>";

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf("<td>%s</td>
		<td>%s</td>
		<td><a href='%sSelectedType=%s&delete=yes&SelectedTabs=$_POST[SelectedTabs]' onclick=\"return confirm('" . _('Are you sure you wish to delete this code and the expense it may have set up?') . "');\">" . _('Delete') . "</td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';




	if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<p><table border=1>'; //Main table
	echo '<td><table>'; // First column



	echo '<tr><td>' . _('Select Expense Code') . ":</td><td><select name='SelectedExpense'>";

	DB_free_result($result);
	$SQL = "SELECT codeexpense,description
		FROM pcexpenses";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectedExpense']) and $myrow['codeexpense']==$_POST['SelectedExpense']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['codeexpense'] . "'>" . $myrow['codeexpense'] . ' - ' . $myrow['description'];

	} //end while loop

	echo '</select></td></tr>';


	echo "<input type=hidden name='SelectedTabs' VALUE=" . $SelectedTabs . ">";

   	echo '</table>'; // close table in first column
   	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=submit VALUE="' . _('Accept') . '"><input type=submit name=Cancel VALUE="' . _('Cancel') . '"></div>';

	echo '</form>';

} // end if user wish to delete


}



include('includes/footer.inc');
?>