<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Tax Authorities');
$ViewTopic = 'Tax';
$BookMark = 'TaxAuthorities';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Tax Authorities Maintenance') . '" />' . ' ' .
		__('Tax Authorities Maintenance') . '</p>';

if(isset($_POST['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID =$_POST['SelectedTaxAuthID'];
} elseif(isset($_GET['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID =$_GET['SelectedTaxAuthID'];
}

if(isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( __('The tax type description may not be empty'), 'error');
	}

	if(isset($SelectedTaxAuthID)) {

		/*SelectedTaxAuthID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE taxauthorities
					SET taxglcode ='" . $_POST['TaxGLCode'] . "',
					purchtaxglaccount ='" . $_POST['PurchTaxGLCode'] . "',
					description = '" . $_POST['Description'] . "',
					bank = '" . $_POST['Bank'] . "',
					bankacctype = '". $_POST['BankAccType'] . "',
					bankacc = '". $_POST['BankAcc'] . "',
					bankswift = '". $_POST['BankSwift'] . "'
				WHERE taxid = '" . $SelectedTaxAuthID . "'";

		$ErrMsg = __('The update of this tax authority failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Msg = __('The tax authority for record has been updated');

	} elseif($InputError !=1) {

	/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$SQL = "INSERT INTO taxauthorities (
						taxglcode,
						purchtaxglaccount,
						description,
						bank,
						bankacctype,
						bankacc,
						bankswift)
			VALUES (
				'" . $_POST['TaxGLCode'] . "',
				'" . $_POST['PurchTaxGLCode'] . "',
				'" . $_POST['Description'] . "',
				'" . $_POST['Bank'] . "',
				'" . $_POST['BankAccType'] . "',
				'" . $_POST['BankAcc'] . "',
				'" . $_POST['BankSwift'] . "'
				)";

		$Errmsg = __('The addition of this tax authority failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Msg = __('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID('taxauthorities','taxid');

		$SQL = "INSERT INTO taxauthrates (
					taxauthority,
					dispatchtaxprovince,
					taxcatid
					)
				SELECT
					'" . $NewTaxID  . "',
					taxprovinces.taxprovinceid,
					taxcategories.taxcatid
				FROM taxprovinces,
					taxcategories";

			$InsertResult = DB_query($SQL);
	}
	//run the SQL from either of the above possibilites
	if(isset($InputError) and $InputError !=1) {
		unset($_POST['TaxGLCode']);
		unset($_POST['PurchTaxGLCode']);
		unset($_POST['Description']);
		unset($SelectedTaxID);
	}

	prnMsg($Msg);

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$SQL= "SELECT COUNT(*)
			FROM taxgrouptaxes
		WHERE taxauthid='" . $SelectedTaxAuthID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if($MyRow[0]>0) {
		prnmsg(__('Cannot delete this tax authority because there are tax groups defined that use it'),'warn');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$Result = DB_query("DELETE FROM taxauthrates WHERE taxauthority= '" . $SelectedTaxAuthID . "'");
		$Result = DB_query("DELETE FROM taxauthorities WHERE taxid= '" . $SelectedTaxAuthID . "'");
		prnMsg(__('The selected tax authority record has been deleted'),'success');
		unset ($SelectedTaxAuthID);
	} // end of related records testing
}

if(!isset($SelectedTaxAuthID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT taxid,
				description,
				taxglcode,
				purchtaxglaccount,
				bank,
				bankacc,
				bankacctype,
				bankswift
			FROM taxauthorities";

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The defined tax authorities could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn" >' . __('ID') . '</th>
				<th class="SortedColumn" >' . __('Tax Authority') . '</th>
				<th class="SortedColumn" >' . __('Input Tax') . '<br />' . __('GL Account') . '</th>
				<th class="SortedColumn" >' . __('Output Tax') . '<br />' . __('GL Account') . '</th>
				<th class="SortedColumn" >' . __('Bank') . '</th>
				<th class="SortedColumn" >' . __('Bank Account') . '</th>
				<th class="SortedColumn" >' . __('Bank Act Type') . '</th>
				<th class="SortedColumn" >' . __('Bank Swift') . '</th>
				<th colspan="4">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while($MyRow = DB_fetch_row($Result)) {
		echo  '<tr class="striped_row">
				<td class="number">', $MyRow[0], '</td>
				<td>', $MyRow[1], '</td>
				<td class="number">', $MyRow[3], '</td>
				<td class="number">', $MyRow[2], '</td>
				<td>', $MyRow[4], '</td>
				<td>', $MyRow[5], '</td>
				<td>', $MyRow[6], '</td>
				<td>', $MyRow[7], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxAuthID=', $MyRow[0], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxAuthID=', $MyRow[0], '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this tax authority?') . '\');">' . __('Delete') . '</a></td>
				<td><a href="', $RootPath . '/TaxAuthorityRates.php?TaxAuthority=', $MyRow[0], '">' . __('Edit Rates') . '</a></td>
			</tr>';

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</tbody></table>';
}

if(isset($SelectedTaxAuthID)) {
	echo '<div class="centre">
			<a href="' .  htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Review all defined tax authority records') . '</a>
		</div>';
}


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if(isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$SQL = "SELECT taxglcode,
				purchtaxglaccount,
				description,
				bank,
				bankacc,
				bankacctype,
				bankswift
			FROM taxauthorities
			WHERE taxid='" . $SelectedTaxAuthID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['TaxGLCode']	= $MyRow['taxglcode'];
	$_POST['PurchTaxGLCode']= $MyRow['purchtaxglaccount'];
	$_POST['Description']	= $MyRow['description'];
	$_POST['Bank']		= $MyRow['bank'];
	$_POST['BankAccType']	= $MyRow['bankacctype'];
	$_POST['BankAcc'] 	= $MyRow['bankacc'];
	$_POST['BankSwift']	= $MyRow['bankswift'];


	echo '<input type="hidden" name="SelectedTaxAuthID" value="' . $SelectedTaxAuthID . '" />';

	echo '<fieldset>
			<legend>', __('Edit Tax Authority Details'), '</legend>';

}  //end of if $SelectedTaxAuthID only do the else when a new record is being entered
else {

	if(!isset($_POST['Description'])) {
		$_POST['Description']='';
	}
	echo '<fieldset>
		<legend>', __('Create New Tax Authority Details'), '</legend>';
}

$SQL = "SELECT accountcode,
				accountname
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl=0
		ORDER BY accountcode";
$Result = DB_query($SQL);

echo '<field>
		<label for="Description">' . __('Tax Type Description') . ':</label>
		<input type="text" pattern="(?!^ +$)[^><+-]+" title="" placeholder="'.__('Within 20 characters').'" required="required" name="Description" size="21" maxlength="20" value="' . $_POST['Description'] . '" />
		<fieldhelp>'.__('No illegal characters allowed and should not be blank').'</fieldhelp>
	</field>';

echo '<field>
		<label for="PurchTaxGLCode">' . __('Input tax GL Account') . ':</label>
		<select name="PurchTaxGLCode">';
while($MyRow = DB_fetch_array($Result)) {
	if(isset($_POST['PurchTaxGLCode']) and $MyRow['accountcode']==$_POST['PurchTaxGLCode']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . ' ('.$MyRow['accountcode'].')' . '</option>';
} //end while loop
echo '</select>
	</field>';

DB_data_seek($Result,0);

echo '<field>
		<label for="TaxGLCode">' . __('Output tax GL Account') . ':</label>
		<select name="TaxGLCode">';
while($MyRow = DB_fetch_array($Result)) {
	if(isset($_POST['TaxGLCode']) and $MyRow['accountcode']==$_POST['TaxGLCode']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $MyRow['accountcode'] . '">' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . ' ('.$MyRow['accountcode'].')' . '</option>';
} //end while loop
if(!isset($_POST['Bank'])) {
	$_POST['Bank']='';
}
if(!isset($_POST['BankAccType'])) {
	$_POST['BankAccType']='';
}
if(!isset($_POST['BankAcc'])) {
	$_POST['BankAcc']='';
}
if(!isset($_POST['BankSwift'])) {
	$_POST['BankSwift']='';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Bank">' . __('Bank Name') . ':</label>
		<input type="text" name="Bank" size="41" maxlength="40" value="' . $_POST['Bank'] . '" placeholder="'.__('Not more than 40 chacraters').'" />
	</field>
	<field>
		<label for="BankAccType">' . __('Bank Account Type') . ':</label>
		<input type="text" name="BankAccType" size="15" maxlength="20" value="' . $_POST['BankAccType'] . '" placeholder="'.__('No more than 20 characters').'" />
	</field>
	<field>
		<label for="BankAcc">' . __('Bank Account') . ':</label>
		<input type="text" name="BankAcc" size="21" maxlength="20" value="' . $_POST['BankAcc'] . '" placeholder="'.__('No more than 20 characters').'" />
	</field>
	<field>
		<label for="BankSwift">' . __('Bank Swift No') . ':</label>
		<input type="text" name="BankSwift" size="15" maxlength="14" value="' . $_POST['BankSwift'] . '" placeholder="'.__('No more than 15 characters').'" />
	</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
</form>';

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxGroups.php">' . __('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . __('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . __('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
