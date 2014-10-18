<?php
/* $Id: PcTabs.php 6153 2013-07-28 10:16:57Z daintree $ */

include('includes/session.inc');
$Title = _('Maintenance Of Petty Cash Tabs');
/* webERP manual links before header.inc */
$ViewTopic= 'PettyCash';
$BookMark = 'PCTabSetup';
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Entry') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = mb_strtoupper($_GET['SelectedTab']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTab);
	unset($_POST['TabCode'] );
	unset($_POST['SelectUser'] );
	unset($_POST['SelectTabs']);
	unset($_POST['SelectCurrency']);
	unset($_POST['TabLimit']);
	unset($_POST['SelectAssigner']);
	unset($_POST['SelectAuthoriser']);
	unset($_POST['GLAccountCash']);
	unset($_POST['GLAccountPcashTab']);
}


if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if ($_POST['TabCode']=='' OR $_POST['TabCode']==' ' OR $_POST['TabCode']=='  ') {
		$InputError = 1;
		prnMsg('<br />' . _('The Tab code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	} elseif (mb_strlen($_POST['TabCode']) >20) {
		$InputError = 1;
		echo prnMsg(_('The Tab code must be twenty characters or less long'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	} elseif (($_POST['SelectUser'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User for this tab'),'error');
		$Errors[$i] = 'UserName';
		$i++;
	} elseif (($_POST['SelectTabs'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a type of tab from the list'),'error');
		$Errors[$i] = 'TabType';
		$i++;
	} elseif (($_POST['SelectAssigner'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User to assign cash to this tab'),'error');
		$Errors[$i] = 'AssignerName';
		$i++;
	} elseif (($_POST['SelectAuthoriser'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a User to authorise this tab'),'error');
		$Errors[$i] = 'AuthoriserName';
		$i++;
	} elseif (($_POST['GLAccountCash'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a General ledger code for the cash to be assigned from'),'error');
		$Errors[$i] = 'GLCash';
		$i++;
	} elseif (($_POST['GLAccountPcashTab'])=='') {
		$InputError = 1;
		echo prnMsg(_('You must select a General ledger code for this petty cash tab'),'error');
		$Errors[$i] = 'GLTab';
		$i++;
	}

	if (isset($SelectedTab) AND $InputError !=1) {

		$sql = "UPDATE pctabs SET usercode = '" . $_POST['SelectUser'] . "',
									typetabcode = '" . $_POST['SelectTabs'] . "',
									currency = '" . $_POST['SelectCurrency'] . "',
									tablimit = '" . filter_number_format($_POST['TabLimit']) . "',
									assigner = '" . $_POST['SelectAssigner'] . "',
									authorizer = '" . $_POST['SelectAuthoriser'] . "',
									glaccountassignment = '" . $_POST['GLAccountCash'] . "',
									glaccountpcash = '" . $_POST['GLAccountPcashTab'] . "'
				WHERE tabcode = '".$SelectedTab."'";

		$msg = _('The Petty Cash Tab') . ' ' . $SelectedTab . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
					 FROM pctabs
					 WHERE tabcode = '" . $_POST['TabCode'] . "'";

		$CheckResult = DB_query($checkSql,$db);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Tab ') .' ' . $_POST['TabCode'] . ' ' . _(' already exists'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO pctabs	(tabcode,
							 			 usercode,
										 typetabcode,
										 currency,
										 tablimit,
										 assigner,
										 authorizer,
										 glaccountassignment,
										 glaccountpcash)
								VALUES ('" . $_POST['TabCode'] . "',
									'" . $_POST['SelectUser'] . "',
									'" . $_POST['SelectTabs'] . "',
									'" . $_POST['SelectCurrency'] . "',
									'" . filter_number_format($_POST['TabLimit']) . "',
									'" . $_POST['SelectAssigner'] . "',
									'" . $_POST['SelectAuthoriser'] . "',
									'" . $_POST['GLAccountCash'] . "',
									'" . $_POST['GLAccountPcashTab'] . "')";

			$msg = _('The Petty Cash Tab') . ' ' . $_POST['TabCode'] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset($SelectedTab);
		unset($_POST['SelectUser']);
		unset($_POST['TabCode']);
		unset($_POST['SelectTabs']);
		unset($_POST['SelectCurrency']);
		unset($_POST['TabLimit']);
		unset($_POST['SelectAssigner']);
		unset($_POST['SelectAuthoriser']);
		unset($_POST['GLAccountCash']);
		unset($_POST['GLAccountPcashTab']);
	}

} elseif ( isset($_GET['delete']) ) {

			$sql="DELETE FROM pctabs WHERE tabcode='".$SelectedTab."'";
			$ErrMsg = _('The Tab record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('The Petty Cash Tab') .  ' ' . $SelectedTab  . ' ' . _('has been deleted') ,'success');
			unset ($SelectedTab);
			unset($_GET['delete']);
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT tabcode,
					usercode,
					typetabdescription,
					currabrev,
					tablimit,
					assigner,
					authorizer,
					glaccountassignment,
					glaccountpcash,
					currencies.decimalplaces,
					chartmaster1.accountname AS glactassigntname,
					chartmaster2.accountname AS glactpcashname
				FROM pctabs INNER JOIN currencies
				ON pctabs.currency=currencies.currabrev
				INNER JOIN pctypetabs
				ON pctabs.typetabcode=pctypetabs.typetabcode
				INNER JOIN chartmaster AS chartmaster1 ON
				pctabs.glaccountassignment = chartmaster1.accountcode
				INNER JOIN chartmaster AS chartmaster2 ON
				pctabs.glaccountpcash = chartmaster2.accountcode
				ORDER BY tabcode";
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)>0){
		echo '<br /><table class="selection">';
		echo '<tr>
				<th>' . _('Tab Code') . '</th>
				<th>' . _('User Name') . '</th>
				<th>' . _('Type Of Tab') . '</th>
				<th>' . _('Currency') . '</th>
				<th>' . _('Limit') . '</th>
				<th>' . _('Assigner') . '</th>
				<th>' . _('Authoriser') . '</th>
				<th>' . _('GL Account For Cash Assignment') . '</th>
				<th>' . _('GL Account Petty Cash Tab') . '</th>
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
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%sSelectedTab=%s">' . _('Edit') . '</a></td>
					<td><a href="%sSelectedTab=%s&amp;delete=yes" onclick=\' return confirm("' . _('Are you sure you wish to delete this tab code?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					$myrow['tabcode'],
					$myrow['usercode'],
					$myrow['typetabdescription'],
					$myrow['currabrev'],
					locale_number_format($myrow['tablimit'],$myrow['decimalplaces']),
					$myrow['assigner'],
					$myrow['authorizer'],
					$myrow['glaccountassignment'].' - '. $myrow['glactassigntname'],
					$myrow['glaccountpcash'].' - '.$myrow['glactpcashname'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['tabcode'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['tabcode']);
		}
		//END WHILE LIST LOOP
		echo '</table>';
	} //if there are tabs to show
}

//end of ifs and buts!
if (isset($SelectedTab)) {

	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Tabs Defined') . '</a></div>';
}
if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
			<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<br />'; //Main table

	if ( isset($SelectedTab) AND $SelectedTab!='' ) {

		$sql = "SELECT * FROM pctabs
				WHERE tabcode='".$SelectedTab."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TabCode'] = $myrow['tabcode'];
		$_POST['SelectUser']  = $myrow['usercode'];
		$_POST['SelectTabs']  = $myrow['typetabcode'];
		$_POST['SelectCurrency']  = $myrow['currency'];
		$_POST['TabLimit']  = locale_number_format($myrow['tablimit']);
		$_POST['SelectAssigner']  = $myrow['assigner'];
		$_POST['SelectAuthoriser']  = $myrow['authorizer'];
		$_POST['GLAccountCash']  = $myrow['glaccountassignment'];
		$_POST['GLAccountPcashTab']  = $myrow['glaccountpcash'];


		echo '<input type="hidden" name="SelectedTab" value="' . $SelectedTab . '" />';
		echo '<input type="hidden" name="TabCode" value="' . $_POST['TabCode']. '" />';
		echo '<table class="selection">
				<tr>
				<td>' . _('Tab Code') . ':</td>
				<td>' . $_POST['TabCode'] . '</td>
				</tr>';
	} else 	{
		// This is a new type so the user must enter a type code
		echo '<table class="selection">
				<tr><td>' . _('Tab Code') . ':</td>
					<td><input type="text"' . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) .' name="TabCode" required="required" autofocus="autofocus" /></td></tr>';

	}

	if (!isset($_POST['typetabdescription'])) {
		$_POST['typetabdescription']='';
	}

	echo '<tr>
			<td>' . _('User Name') . ':</td>
			<td><select name="SelectUser">';

	$SQL = "SELECT userid,
					realname
			FROM www_users ORDER BY userid";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectUser']) and $myrow['userid']==$_POST['SelectUser']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['userid'] . '">' . $myrow['userid'] . ' - ' . $myrow['realname'] . '</option>';

	} //end while loop get user

	echo '</select></td>
		</tr>';
    DB_free_result($result);

	echo '<tr>
			<td>' . _('Type Of Tab') . ':</td>
			<td><select name="SelectTabs">';

	$SQL = "SELECT typetabcode,
					typetabdescription
			FROM pctypetabs
			ORDER BY typetabcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['typetabcode']==$_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['typetabcode'] . '">' . $myrow['typetabcode'] . ' - ' . $myrow['typetabdescription'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td></tr>';
    DB_free_result($result);

	echo '<tr>
			<td>' . _('Currency') . ':</td>
			<td><select name="SelectCurrency">';

	$SQL = "SELECT currency, currabrev FROM currencies";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectCurrency']) and $myrow['currabrev']==$_POST['SelectCurrency']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td>
		</tr>';
    DB_free_result($result);

	if (!isset($_POST['TabLimit'])) {
		$_POST['TabLimit']=0;
	}

	echo '<tr>
			<td>' . _('Limit Of Tab') . ':</td>
			<td><input type="text" class="integer" name="TabLimit" size="12" maxlength="11" value="' . $_POST['TabLimit'] . '" /></td></tr>';

	echo '<tr>
			<td>' . _('Assigner') . ':</td>
			<td><select name="SelectAssigner">';

	$SQL = "SELECT userid,
					realname
			FROM www_users
			ORDER BY userid";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectAssigner']) and $myrow['userid']==$_POST['SelectAssigner']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['userid'] . '">' . $myrow['userid'] . ' - ' . $myrow['realname'] . '</option>';

	} //end while loop get assigner

    echo '</select></td>
		</tr>';
    DB_free_result($result);

	echo '<tr>
			<td>' . _('Authoriser') . ':</td>
			<td><select name="SelectAuthoriser">';

	$SQL = "SELECT userid,
					realname
			FROM www_users
			ORDER BY userid";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectAuthoriser']) and $myrow['userid']==$_POST['SelectAuthoriser']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['userid'] . '">' . $myrow['userid'] . ' - ' . $myrow['realname'] . '</option>';

	} //end while loop get authoriser

    echo '</select></td>
		</tr>';
    DB_free_result($result);

	echo '<tr>
			<td>' . _('GL Account Cash Assignment') . ':</td>
			<td><select name="GLAccountCash">';

	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname
			FROM chartmaster INNER JOIN bankaccounts
			ON chartmaster.accountcode = bankaccounts.accountcode
			ORDER BY chartmaster.accountcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['GLAccountCash']) and $myrow['accountcode']==$_POST['GLAccountCash']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

	} //end while loop

    echo '</select></td>
		</tr>';
    DB_free_result($result);

	echo '<tr>
			<td>' . _('GL Account Petty Cash Tab') . ':</td>
			<td><select name="GLAccountPcashTab">';

	$SQL = "SELECT accountcode, accountname
			FROM chartmaster
			ORDER BY accountcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['GLAccountPcashTab']) and $myrow['accountcode']==$_POST['GLAccountPcashTab']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

	} //end while loop

    echo '</select></td>
		</tr>
		</table>'; // close main table
    DB_free_result($result);

	echo '<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>
		</div>
	</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>