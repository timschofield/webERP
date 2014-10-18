<?php

/* $Id: PcExpensesTypeTab.php 5785 2012-12-29 04:47:42Z daintree $ */

include('includes/session.inc');
$Title = _('Maintenance Of Petty Cash Expenses For a Type Tab');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
} else {
	$SelectedType='';
}

if (!isset($_GET['delete']) and (ContainsIllegalCharacters($SelectedType) OR mb_strpos($SelectedType,' ')>0)){
	$InputError = 1;
	prnMsg(_('The petty cash tab type contain any of the following characters " \' - &amp; or a space'),'error');
}

if (isset($_POST['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($SelectedType);
}

if (isset($_POST['Process'])) {

	if ($_POST['SelectedTab'] == '') {
		echo prnMsg(_('You have not selected a tab to maintain the expenses on'),'error');
		echo '<br />';
		unset($SelectedTab);
		unset($_POST['SelectedTab']);
	}
}

if (isset($_POST['submit'])) {

	$InputError=0;

	if ($_POST['SelectedExpense']=='') {
		$InputError=1;
		echo prnMsg(_('You have not selected an expense to add to this tab'),'error');
		echo '<br />';
		unset($SelectedTab);
	}

	if ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM pctabexpenses
			     WHERE typetabcode= '" .  $_POST['SelectedTab'] . "'
				 AND codeexpense = '" .  $_POST['SelectedExpense'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] >0) {
			$InputError = 1;
			prnMsg( _('The Expense') . ' ' . $_POST['codeexpense'] . ' ' ._('already exists in this Type of Tab'),'error');
		} else {
			// Add new record on submit
			$sql = "INSERT INTO pctabexpenses (typetabcode,
												codeexpense)
										VALUES ('" . $_POST['SelectedTab'] . "',
												'" . $_POST['SelectedExpense'] . "')";

			$msg = _('Expense code:') . ' ' . $_POST['SelectedExpense'].' '._('for Type of Tab:') .' '. $_POST['SelectedTab'] .  ' ' . _('has been created');
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
		WHERE typetabcode='".$SelectedTab."'
		AND codeexpense='".$SelectedType."'";

	$ErrMsg = _('The Tab Type record could not be deleted because');
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Expense code').' '. $SelectedType .' '. _('for type of tab').' '. $SelectedTab .' '. _('has been deleted') ,'success');
	unset($_GET['delete']);
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	echo '<tr><td>' . _('Select Type of Tab') . ':</td><td><select name="SelectedTab">';

	$SQL = "SELECT typetabcode,
					typetabdescription
			FROM pctypetabs";

	$result = DB_query($SQL,$db);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($SelectedTab) and $myrow['typetabcode']==$SelectedTab) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['typetabcode'] . '">' . $myrow['typetabcode'] . ' - ' . $myrow['typetabdescription'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';

   	echo '</table>'; // close main table
    DB_free_result($result);

	echo '<br /><div class="centre"><input type="submit" name="Process" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';

	echo '</div>
          </form>';

}

//end of ifs and buts!
if (isset($_POST['process'])OR isset($SelectedTab)) {

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Expense Codes for Type of Tab ') . ' ' .$SelectedTab. '</a></div>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="SelectedTab" value="' . $SelectedTab . '" />';

	$sql = "SELECT pctabexpenses.codeexpense,
					pcexpenses.description
			FROM pctabexpenses INNER JOIN pcexpenses
			ON pctabexpenses.codeexpense=pcexpenses.codeexpense
			WHERE pctabexpenses.typetabcode='".$SelectedTab."'
			ORDER BY pctabexpenses.codeexpense ASC";

	$result = DB_query($sql,$db);

	echo '<br />
			<table class="selection">';
	echo '<tr><th colspan="3"><h3>' . _('Expense Codes for Type of Tab ') . ' ' .$SelectedTab. '</h3></th></tr>';
	echo '<tr>
			<th>' . _('Expense Code') . '</th>
			<th>' . _('Description') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td><a href="%s?SelectedType=%s&amp;delete=yes&amp;SelectedTab=' . $SelectedTab . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this expense code?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['codeexpense'],
			$myrow['description'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['codeexpense'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow['codeexpense']);
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (! isset($_GET['delete'])) {


		echo '<br /><table  class="selection">'; //Main table

		echo '<tr><td>' . _('Select Expense Code') . ':</td><td><select name="SelectedExpense">';

		$SQL = "SELECT codeexpense,
						description
				FROM pcexpenses";

		$result = DB_query($SQL,$db);
		if (!isset($_POST['SelectedExpense'])){
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($myrow = DB_fetch_array($result)) {
			if (isset($_POST['SelectedExpense']) AND $myrow['codeexpense']==$_POST['SelectedExpense']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['codeexpense'] . '">' . $myrow['codeexpense'] . ' - ' . $myrow['description'] . '</option>';

		} //end while loop

		echo '</select></td></tr>';

	   	echo '</table>'; // close main table
        DB_free_result($result);

		echo '<br /><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" />
									<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';

		echo '</div>
              </form>';

	} // end if user wish to delete
}

include('includes/footer.inc');
?>