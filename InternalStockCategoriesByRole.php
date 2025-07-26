<?php

/* Functionas as described in https://www.weberp.org/forum/showthread.php?tid=171 */

include('includes/session.php');
$Title = _('Internal Stock Categories Requests By Security Role Maintenance ');

$ViewTopic = 'Inventory';
$BookMark = 'InventoryRequests';

include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
} else {
	$SelectedType='';
}

if (!isset($_GET['delete']) AND (ContainsIllegalCharacters($SelectedType) OR mb_strpos($SelectedType,' ')>0)){
	$InputError = 1;
	prnMsg(_('The Selected type cannot contain any of the following characters') . ' " \' - &amp; ' . _('or a space'),'error');
}
if (isset($_POST['SelectedRole'])){
	$SelectedRole = mb_strtoupper($_POST['SelectedRole']);
} elseif (isset($_GET['SelectedRole'])){
	$SelectedRole = mb_strtoupper($_GET['SelectedRole']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedRole);
	unset($SelectedType);
}

if (isset($_POST['Process'])) {

	if ($_POST['SelectedRole'] == '') {
		prnMsg(_('You have not selected a security role to maintain the internal stock categories on'),'error');
		echo '<br />';
		unset($SelectedRole);
		unset($_POST['SelectedRole']);
	}
}

if (isset($_POST['submit'])) {

	$InputError=0;

	if ($_POST['SelectedCategory']=='') {
		$InputError=1;
		prnMsg(_('You have not selected a stock category to be added as internal to this security role'),'error');
		echo '<br />';
		unset($SelectedRole);
	}

	if ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM internalstockcatrole
			     WHERE secroleid= '" .  $_POST['SelectedRole'] . "'
				 AND categoryid = '" .  $_POST['SelectedCategory'] . "'";

		$Checkresult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($Checkresult);

		if ( $CheckRow[0] >0) {
			$InputError = 1;
			prnMsg( _('The Stock Category') . ' ' . $_POST['categoryid'] . ' ' ._('already allowed as internal for this security role'),'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO internalstockcatrole (secroleid,
												categoryid)
										VALUES ('" . $_POST['SelectedRole'] . "',
												'" . $_POST['SelectedCategory'] . "')";

			$Msg = _('Stock Category:') . ' ' . $_POST['SelectedCategory'].' '._('has been allowed to user role') .' '. $_POST['SelectedRole'] .  ' ' . _('as internal');
			$CheckSQL = "SELECT count(secroleid)
							FROM securityroles";
			$Result = DB_query($CheckSQL);
			$Row = DB_fetch_row($Result);
		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg,'success');
		unset($_POST['SelectedCategory']);
	}

} elseif ( isset($_GET['delete']) ) {
	$SQL="DELETE FROM internalstockcatrole
		WHERE secroleid='".$SelectedRole."'
		AND categoryid='".$SelectedType."'";

	$ErrMsg = _('The Stock Category by Role record could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);
	prnMsg(_('Internal Stock Category').' '. $SelectedType .' '. _('for user role').' '. $SelectedRole .' '. _('has been deleted') ,'success');
	unset($_GET['delete']);
}

if (!isset($SelectedRole)){

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Select a User Role'), '</legend>'; //Main table

	echo '<field>
			<label for="SelectedRole">' . _('Select User Role') . ':</label>
			<select name="SelectedRole">';

	$SQL = "SELECT secroleid,
					secrolename
			FROM securityroles";

	$Result = DB_query($SQL);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedRole) AND $MyRow['secroleid']==$SelectedRole) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['secroleid'] . '">' . $MyRow['secroleid'] . ' - ' . $MyRow['secrolename'] . '</option>';

	} //end while loop

	echo '</select>
		</field>';

   	echo '</Fieldset>'; // close main table
    DB_free_result($Result);

	echo '<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="reset" name="Cancel" value="' . _('Cancel') . '" />
		</div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process'])OR isset($SelectedRole)) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';

	$SQL = "SELECT internalstockcatrole.categoryid,
					stockcategory.categorydescription
			FROM internalstockcatrole INNER JOIN stockcategory
			ON internalstockcatrole.categoryid=stockcategory.categoryid
			WHERE internalstockcatrole.secroleid='".$SelectedRole."'
			ORDER BY internalstockcatrole.categoryid ASC";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr><th colspan="3">' . _('Internal Stock Categories Allowed to user role') . ' ' .$SelectedRole. '</th></tr>';
	echo '<tr>
			<th>' . _('Category Code') . '</th>
			<th>' . _('Description') . '</th>
			<th></th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
			<td>', $MyRow['categoryid'], '</td>
			<td>', $MyRow['categorydescription'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedType=', $MyRow['categoryid'], '&amp;delete=yes&amp;SelectedRole=' . $SelectedRole . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this internal stock category code?') . '\');">' . _('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';

	if (! isset($_GET['delete'])) {


		echo '<fieldset>
				<legend>', _('Select A Stock Category'), '</legend>'; //Main table

		echo '<field>
				<label for="SelectedCategory">' . _('Select Stock Category Code') . ':</label>
				<select name="SelectedCategory">';

		$SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory";

		$Result = DB_query($SQL);
		if (!isset($_POST['SelectedCategory'])){
			echo '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedCategory']) AND $MyRow['categoryid']==$_POST['SelectedCategory']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';

		} //end while loop

		echo '</select>
			</field>';

	   	echo '</fieldset>'; // close main table
        DB_free_result($Result);

		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Accept') . '" />
				<input type="reset" name="Cancel" value="' . _('Cancel') . '" />
			</div>';

		echo '</form>';

	} // end if user wish to delete
}
include('includes/footer.php');
