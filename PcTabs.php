<?php
/* $Id$ */

include('includes/session.inc');
$title = _('Maintenance Of Petty Cash Tabs');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Payment Entry')
	. '" alt="" />' . ' ' . $title . '</p>';

if (isset($_POST['SelectedTab'])){
	$SelectedTab = strtoupper($_POST['SelectedTab']);
} elseif (isset($_GET['SelectedTab'])){
	$SelectedTab = strtoupper($_GET['SelectedTab']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if ($_POST['TabCode']=='' OR $_POST['TabCode']==' ' OR $_POST['TabCode']=='  ') {
		$InputError = 1;
		prnMsg('<br>' . _('The Tab code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	} elseif (strlen($_POST['TabCode']) >20) {
		$InputError = 1;
		echo prnMsg(_('The Tab code must be twenty characters or less long'),'error');
		$Errors[$i] = 'TabCode';
		$i++;
	}

	if (isset($SelectedTab) AND $InputError !=1) {

		$sql = "UPDATE pctabs
				SET usercode = '" . $_POST['SelectUser'] . "',
				typetabcode = '" . $_POST['SelectTabs'] . "',
				currency = '" . $_POST['SelectCurrency'] . "',
				tablimit = '" . $_POST['TabLimit'] . "',
				authorizer = '" . $_POST['SelectAuthorizer'] . "',
				glaccountassignment = '" . $_POST['glaccountcash'] . "',
				glaccountpcash = '" . $_POST['GLAccountPcashTab'] . "'
				WHERE tabcode = '".$SelectedTab."'";

		$msg = _('The Tabs Of Code') . ' ' . $SelectedTab . ' ' .  _('has been updated');
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

			$sql = "INSERT INTO pctabs
						(tabcode,
			 			 usercode,
						 typetabcode,
						 currency,
						 tablimit,
						 authorizer,
						 glaccountassignment,
						 glaccountpcash)
				VALUES ('" . $_POST['TabCode'] . "',
					'" . $_POST['SelectUser'] . "',
					'" . $_POST['SelectTabs'] . "',
					'" . $_POST['SelectCurrency'] . "',
					'" . $_POST['TabLimit'] . "',
					'" . $_POST['SelectAuthorizer'] . "',
					'" . $_POST['glaccountcash'] . "',
					'" . $_POST['GLAccountPcashTab'] . "')";

			$msg = _('Tab with Code ') . ' ' . $_POST['TabCode'] .  ' ' . _('has been created');

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset($SelectedTab);
		unset($_POST['SelectUser'] );
		unset($_POST['SelectTabs']);
		unset($_POST['SelectCurrency']);
		unset($_POST['TabLimit']);
		unset($_POST['SelectAuthorizer']);
		unset($_POST['glaccountcash']);
		unset($_POST['GLAccountPcashTab']);


	}

} elseif ( isset($_GET['delete']) ) {

			$sql="DELETE FROM pctabs WHERE tabcode='".$SelectedTab."'";
			$ErrMsg = _('The Tab record could not be deleted because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('Tab type') .  ' ' . $SelectedTab  . ' ' . _('has been deleted') ,'success');
			unset ($SelectedTab);
			unset($_GET['delete']);
}

if (!isset($SelectedTab)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTab will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT *
		FROM pctabs
		ORDER BY tabcode';
	$result = DB_query($sql,$db);

	echo '<br><table class=selection>';
	echo '<tr>
			<th>' . _('Tab Code') . '</th>
			<th>' . _('User Name') . '</th>
			<th>' . _('Type Of Tab') . '</th>
			<th>' . _('Currency') . '</th>
			<th>' . _('Limit') . '</th>
			<th>' . _('Authorizer') . '</th>
			<th>' . _('GL Account For Cash Assignment') . '</th>
			<th>' . _('GL Account Petty Cash Tab') . '</th>
		</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	$sqldes="SELECT accountname
				FROM chartmaster
				WHERE accountcode='". $myrow[6] . "'";

	$ResultDes = DB_query($sqldes,$db);
	$Description=DB_fetch_array($ResultDes);

	$sqlname="SELECT accountname
				FROM chartmaster
				WHERE accountcode='". $myrow[7] . "'";

	$ResultName = DB_query($sqlname,$db);
	$DescriptionName=DB_fetch_array($ResultName);


	printf('<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td class=number>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href="%sSelectedTab=%s">' . _('Edit') . '</td>
		<td><a href="%sSelectedTab=%s&delete=yes" onclick=\' return confirm("' . _('Are you sure you wish to delete this tab code?') . '");\'>' . _('Delete') . '</td>
		</tr>',
		$myrow[0],
		$myrow[1],
		$myrow[2],
		$myrow[3],
		number_format($myrow[4],2),
		$myrow[5],
		$myrow[6].' - '.$Description[0],
		$myrow[7].' - '.$DescriptionName[0],
		$_SERVER['PHP_SELF'] . '?', $myrow[0],
		$_SERVER['PHP_SELF'] . '?', $myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedTab)) {

	echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Show All Tabs Defined') . '</a></div><p>';
}
if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p><table class=selection>'; //Main table


	if ( isset($SelectedTab) AND $SelectedTab!='' ) {

		$sql = "SELECT * FROM pctabs
				WHERE tabcode='".$SelectedTab."'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TabCode'] = $myrow['tabcode'];
		$_POST['SelectUser']  = $myrow['usercode'];
		$_POST['SelectTabs']  = $myrow['typetabcode'];
		$_POST['SelectCurrency']  = $myrow['currency'];
		$_POST['TabLimit']  = $myrow['tablimit'];
		$_POST['SelectAuthorizer']  = $myrow['authorizer'];
		$_POST['glaccountcash']  = $myrow['glaccountassignment'];
		$_POST['GLAccountPcashTab']  = $myrow['glaccountpcash'];


		echo '<input type=hidden name="SelectedTab" value="' . $SelectedTab . '">';
		echo '<input type=hidden name="TabCode" value="' . $_POST['TabCode']. '">';
		echo '<table class="selection"> <tr><td>' . _('Tab Code') . ':</td><td>';

		// We dont allow the user to change an existing type code

		echo $_POST['TabCode'] . '</td></tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code
		echo '<table class="selection">
				<tr><td>' . _('Tab Code') . ':</td>
					<td><input type="Text"' . (in_array('TypeTabCode',$Errors) ? 'class="inputerror"' : '' ) .' name="TabCode"></td></tr>';

	}

	if (!isset($_POST['typetabdescription'])) {
		$_POST['typetabdescription']='';
	}

	echo '<tr><td>' . _('User Name') . ':</td>
			<td><select name="SelectUser">';

	DB_free_result($result);
	$SQL = "SELECT userid
			FROM www_users ORDER BY userid";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectUser']) and $myrow['userid']==$_POST['SelectUser']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['userid'] . '">' . $myrow['userid'] . '</option>';

	} //end while loop get user

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Type Of Tab') . ':</td>
			<td><select name="SelectTabs">';

	DB_free_result($result);
	$SQL = "SELECT typetabcode FROM pctypetabs ORDER BY typetabcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['typetabcode']==$_POST['SelectTabs']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['typetabcode'] . '">' . $myrow['typetabcode'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td></tr>';

	echo '<tr><td>' . _('Currency') . ':</td>
			<td><select name="SelectCurrency">';

	DB_free_result($result);
	$SQL = "SELECT currency, currabrev FROM currencies";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectCurrency']) and $myrow['currabrev']==$_POST['SelectCurrency']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td></tr>';

	if (!isset($_POST['TabLimit'])) {
		$_POST['TabLimit']=0;
	}

	echo '<tr><td>' . _('Limit Of Tab') . ':</td>
			<td><input type="Text" class="number" name="TabLimit" size="12" maxlength="11" value="' . $_POST['TabLimit'] . '"></td></tr>';

	echo '<tr><td>' . _('Authorizer') . ':</td>
			<td><select name="SelectAuthorizer">';

	DB_free_result($result);
	$SQL = "SELECT userid
			FROM www_users
			ORDER BY userid";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectAuthorizer']) and $myrow['userid']==$_POST['SelectAuthorizer']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['userid'] . '">' . $myrow['userid'] . '</option>';

	} //end while loop get authorizer

	echo '</select></td></tr>';

	echo '<tr><td>' . _('GL Account Cash Assignment') . ':</td>
			<td><select name="glaccountcash">';

	DB_free_result($result);
	$SQL = "SELECT chartmaster.accountcode, 
					chartmaster.accountname
			FROM chartmaster INNER JOIN bankaccounts
			ON chartmaster.accountcode = bankaccounts.accountcode
			ORDER BY chartmaster.accountcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['glaccountcash']) and $myrow['accountcode']==$_POST['glaccountcash']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';

	echo '<tr><td>' . _('GL Account Petty Cash Tab') . ':</td>
			<td><select name="GLAccountPcashTab">';

	DB_free_result($result);
	$SQL = "SELECT accountcode, accountname
			FROM chartmaster
			ORDER BY accountcode";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['GLAccountPcashTab']) and $myrow['accountcode']==$_POST['GLAccountPcashTab']) {
			echo '<option selected value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' - ' . $myrow['accountname'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';
   	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=submit value="' . _('Accept') . '"><input type=submit name=Cancel value="' . _('Cancel') . '"></div>';

	echo '</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>