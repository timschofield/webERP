<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Fixed Asset Category Maintenance');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetCategories';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . __('Fixed Asset Categories') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['CategoryID'] = mb_strtoupper($_POST['CategoryID']);

	if (mb_strlen($_POST['CategoryID']) > 6) {
		$InputError = 1;
		prnMsg(__('The Fixed Asset Category code must be six characters or less long'),'error');
	} elseif (mb_strlen($_POST['CategoryID'])==0) {
		$InputError = 1;
		prnMsg(__('The Fixed Asset Category code must be at least 1 character but less than six characters long'),'error');
	} elseif (mb_strlen($_POST['CategoryDescription']) >20) {
		$InputError = 1;
		prnMsg(__('The Fixed Asset Category description must be twenty characters or less long'),'error');
	}

	if ($_POST['CostAct'] == $_SESSION['CompanyRecord']['debtorsact']
			OR $_POST['CostAct'] == $_SESSION['CompanyRecord']['creditorsact']
			OR $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['debtorsact']
			OR $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['creditorsact']
			OR $_POST['CostAct'] == $_SESSION['CompanyRecord']['grnact']
			OR $_POST['AccumDepnAct'] == $_SESSION['CompanyRecord']['grnact']){

		prnMsg(__('The accounts selected to post cost or accumulated depreciation to cannot be either of the debtors control account, creditors control account or GRN suspense accounts'),'error');
		$InputError =1;
	}
	/*Make an array of the defined bank accounts */
	$SQL = "SELECT bankaccounts.accountcode
			FROM bankaccounts INNER JOIN chartmaster
			ON bankaccounts.accountcode=chartmaster.accountcode";
	$Result = DB_query($SQL);
	$BankAccounts = array();
	$i=0;

	while ($Act = DB_fetch_row($Result)){
		$BankAccounts[$i]= $Act[0];
		$i++;
	}
	if (in_array($_POST['CostAct'], $BankAccounts)) {
		prnMsg(__('The asset cost account selected is a bank account - bank accounts are protected from having any other postings made to them. Select another balance sheet account for the asset cost'),'error');
		$InputError=1;
	}
	if (in_array($_POST['AccumDepnAct'], $BankAccounts)) {
		prnMsg( __('The accumulated depreciation account selected is a bank account - bank accounts are protected from having any other postings made to them. Select another balance sheet account for the asset accumulated depreciation'),'error');
		$InputError=1;
	}

	if (isset($SelectedCategory) AND $InputError !=1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE fixedassetcategories
					SET categorydescription = '" . $_POST['CategoryDescription'] . "',
						costact = '" . $_POST['CostAct'] . "',
						depnact = '" . $_POST['DepnAct'] . "',
						disposalact = '" . $_POST['DisposalAct'] . "',
						accumdepnact = '" . $_POST['AccumDepnAct'] . "'
				WHERE categoryid = '".$SelectedCategory . "'";

		$ErrMsg = __('Could not update the fixed asset category') . $_POST['CategoryDescription'] . __('because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('Updated the fixed asset category record for') . ' ' . $_POST['CategoryDescription'],'success');

	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO fixedassetcategories (categoryid,
												categorydescription,
												costact,
												depnact,
												disposalact,
												accumdepnact)
								VALUES ('" . $_POST['CategoryID'] . "',
										'" . $_POST['CategoryDescription'] . "',
										'" . $_POST['CostAct'] . "',
										'" . $_POST['DepnAct'] . "',
										'" . $_POST['DisposalAct'] . "',
										'" . $_POST['AccumDepnAct'] . "')";
		$ErrMsg = __('Could not insert the new fixed asset category') . $_POST['CategoryDescription'] . __('because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(__('A new fixed asset category record has been added for') . ' ' . $_POST['CategoryDescription'],'success');

	}
	//run the SQL from either of the above possibilites

	unset($_POST['CategoryID']);
	unset($_POST['CategoryDescription']);
	unset($_POST['CostAct']);
	unset($_POST['DepnAct']);
	unset($_POST['DisposalAct']);
	unset($_POST['AccumDepnAct']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'fixedassets'

	$SQL= "SELECT COUNT(*) FROM fixedassets WHERE fixedassets.assetcategoryid='" . $SelectedCategory . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this fixed asset category because fixed assets have been created using this category') .
			'<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('fixed assets referring to this category code'),'warn');

	} else {
		$SQL="DELETE FROM fixedassetcategories WHERE categoryid='" . $SelectedCategory . "'";
		$Result = DB_query($SQL);
		prnMsg(__('The fixed asset category') . ' ' . $SelectedCategory . ' ' . __('has been deleted'),'success');
		unset ($SelectedCategory);
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory) or isset($_POST['submit'])) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT categoryid,
				categorydescription,
				costact,
				depnact,
				disposalact,
				accumdepnact
			FROM fixedassetcategories";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Cat Code') . '</th>
			<th>' . __('Description') . '</th>
			<th>' . __('Cost GL') . '</th>
			<th>' . __('P &amp; L Depn GL') . '</th>
			<th>' . __('Disposal GL') . '</th>
			<th>' . __('Accum Depn GL') . '</th>
			<th colspan="2"></th>
		  </tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['categoryid'], '</td>
				<td>', $MyRow['categorydescription'], '</td>
				<td class="number">', $MyRow['costact'], '</td>
				<td class="number">', $MyRow['depnact'], '</td>
				<td class="number">', $MyRow['disposalact'], '</td>
				<td class="number">', $MyRow['accumdepnact'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedCategory=', $MyRow['categoryid'], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedCategory=', $MyRow['categoryid'], '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this fixed asset category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">' . __('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedCategory)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .__('Show All Fixed Asset Categories') . '</a></div>';
}

echo '<form id="CategoryForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedCategory) and !isset($_POST['submit'])) {
	//editing an existing fixed asset category
		$SQL = "SELECT categoryid,
					categorydescription,
					costact,
					depnact,
					disposalact,
					accumdepnact
				FROM fixedassetcategories
				WHERE categoryid='" . $SelectedCategory . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

	$_POST['CategoryID'] = $MyRow['categoryid'];
	$_POST['CategoryDescription']  = $MyRow['categorydescription'];
	$_POST['CostAct']  = $MyRow['costact'];
	$_POST['DepnAct']  = $MyRow['depnact'];
	$_POST['DisposalAct']  = $MyRow['disposalact'];
	$_POST['AccumDepnAct']  = $MyRow['accumdepnact'];

	echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
	echo '<input type="hidden" name="CategoryID" value="' . $_POST['CategoryID'] . '" />';
	echo '<fieldset>
			<legend>', __('Amend Category Details'), '</legend>
			<field>
				<label for="CategoryID">' . __('Category Code') . ':</label>
				<fieldtext>' . $_POST['CategoryID'] . '</fieldtext>
			</field>';

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID'] = '';
	}
	echo '<fieldset>
			<legend>', __('Create Category Details'), '</legend>
			<field>
				<label for="CategoryID">' . __('Category Code') . ':</label>
				<input type="text" name="CategoryID" required="required" title="" data-type="no-illegal-chars" size="7" maxlength="6" value="' . $_POST['CategoryID'] . '" />
				<fieldhelp>' . __('Enter the asset category code. Up to 6 alpha-numeric characters are allowed') . '</fieldhelp>
			</field>';
}

//SQL to poulate account selection boxes
$SQL = "SELECT accountcode,
				 accountname
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl=0
		ORDER BY accountcode";

$BSAccountsResult = DB_query($SQL);

$SQL = "SELECT accountcode,
				 accountname
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl!=0
		ORDER BY accountcode";

$PnLAccountsResult = DB_query($SQL);

if (!isset($_POST['CategoryDescription'])) {
	$_POST['CategoryDescription'] = '';
}

echo '<field>
		<label for="CategoryDescription">' . __('Category Description') . ':</label>
		<input type="text" name="CategoryDescription" required="required" title="" size="22" maxlength="20" value="' . $_POST['CategoryDescription'] . '" />
		<fieldhelp>' . __('Enter the asset category description up to 20 characters') . '</fieldhelp>
	</field>
	<field>
		<label for="CostAct">' . __('Fixed Asset Cost GL Code') . ':</label>
		<select name="CostAct" required="required" title="" >';

while ($MyRow = DB_fetch_array($BSAccountsResult)){

	if (isset($_POST['CostAct']) and $MyRow['accountcode']==$_POST['CostAct']) {
		echo '<option selected="selected" value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')</option>';
	} else {
		echo '<option value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . __('Select the general ledger account where the cost of assets of this category should be posted to. Only balance sheet accounts can be selected') . '</fieldhelp>
</field>';

echo '<field>
		<label for="DepnAct">' . __('Profit and Loss Depreciation GL Code') . ':</label>
		<select name="DepnAct" required="required" title="" >';

while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['DepnAct']) and $MyRow['accountcode']==$_POST['DepnAct']) {
		echo '<option selected="selected" value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')</option>';
	} else {
		echo '<option value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . __('Select the general ledger account where the depreciation of assets of this category should be posted to. Only profit and loss accounts can be selected') . '</fieldhelp>
</field>';

DB_data_seek($PnLAccountsResult,0);
echo '<field>
		<label for="DisposalAct">' .  __('Profit or Loss on Disposal GL Code') . ':</label>
		<select name="DisposalAct" required="required" title="" >';
while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['DisposalAct']) and $MyRow['accountcode']==$_POST['DisposalAct']) {
		echo '<option selected="selected" value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')' . '</option>';
	} else {
		echo '<option value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')' . '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . __('Select the general ledger account where the profit or loss on disposal on assets of this category should be posted to. Only profit and loss accounts can be selected') . '</fieldhelp>
</field>';

DB_data_seek($BSAccountsResult,0);
echo '<field>
		<label for="AccumDepnAct">' . __('Balance Sheet Accumulated Depreciation GL Code') . ':</label>
		<select name="AccumDepnAct" required="required" title="" >';

while ($MyRow = DB_fetch_array($BSAccountsResult)) {

	if (isset($_POST['AccumDepnAct']) and $MyRow['accountcode']==$_POST['AccumDepnAct']) {
		echo '<option selected="selected" value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')' . '</option>';
	} else {
		echo '<option value="'.$MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . ' ('.$MyRow['accountcode'].')' . '</option>';
	}

} //end while loop


echo '</select>
	<fieldhelp>' . __('Select the general ledger account where the accumulated depreciation on assets of this category should be posted to. Only balance sheet accounts can be selected') . '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
</form>';

include('includes/footer.php');
