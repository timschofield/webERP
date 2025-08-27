<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Dispatch Tax Provinces');
$ViewTopic = 'Tax';
$BookMark = 'TaxProvinces';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Dispatch Tax Province Maintenance') . '" />' . ' ' .
		__('Dispatch Tax Province Maintenance') . '</p>';

if( isset($_GET['SelectedTaxProvince']) )
	$SelectedTaxProvince = $_GET['SelectedTaxProvince'];
elseif(isset($_POST['SelectedTaxProvince']))
	$SelectedTaxProvince = $_POST['SelectedTaxProvince'];

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if(ContainsIllegalCharacters($_POST['TaxProvinceName'])) {
		$InputError = 1;
		prnMsg( __('The tax province name cannot contain any of the illegal characters') . ' ' . '" \' - &amp; or a space','error');
	}
	if(trim($_POST['TaxProvinceName']) == '') {
		$InputError = 1;
		prnMsg( __('The tax province name may not be empty'), 'error');
	}

	if($_POST['SelectedTaxProvince']!='' AND $InputError !=1) {

		/*SelectedTaxProvince could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$SQL = "SELECT count(*) FROM taxprovinces
				WHERE taxprovinceid <> '" . $SelectedTaxProvince ."'
				AND taxprovincename " . LIKE . " '" . $_POST['TaxProvinceName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The tax province cannot be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists
			$SQL = "SELECT taxprovincename FROM taxprovinces
						WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			if( DB_num_rows($Result) != 0 ) {
				// This is probably the safest way there is
				$MyRow = DB_fetch_row($Result);
				$OldTaxProvinceName = $MyRow[0];
				$SQL = "UPDATE taxprovinces
					SET taxprovincename='" . $_POST['TaxProvinceName'] . "'
					WHERE taxprovincename ".LIKE." '".$OldTaxProvinceName."'";
				$ErrMsg = __('Could not update tax province');
				$Result = DB_query($SQL, $ErrMsg);
				if(!$Result) {
					prnMsg(__('Tax province name changed'),'success');
				}
			} else {
				$InputError = 1;
				prnMsg( __('The tax province no longer exists'),'error');
			}
		}
	} elseif($InputError !=1) {
		/*SelectedTaxProvince is null cos no item selected on first time round so must be adding a record*/
		$SQL = "SELECT count(*) FROM taxprovinces
				WHERE taxprovincename " .LIKE. " '".$_POST['TaxProvinceName'] ."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		if( $MyRow[0] > 0 ) {

			$InputError = 1;
			prnMsg( __('The tax province cannot be created because another with the same name already exists'),'error');

		} else {

			$SQL = "INSERT INTO taxprovinces (taxprovincename )
					VALUES ('" . $_POST['TaxProvinceName'] ."')";

			$ErrMsg = __('Could not add tax province');
			$Result = DB_query($SQL, $ErrMsg);

			$TaxProvinceID = DB_Last_Insert_ID('taxprovinces', 'taxprovinceid');
			$SQL = "INSERT INTO taxauthrates (taxauthority, dispatchtaxprovince, taxcatid)
					SELECT taxauthorities.taxid, '" . $TaxProvinceID . "', taxcategories.taxcatid
					FROM taxauthorities CROSS JOIN taxcategories";
			$ErrMsg = __('Could not add tax authority rates for the new dispatch tax province. The rates of tax will not be able to be added - manual database interaction will be required to use this dispatch tax province');
			$Result = DB_query($SQL, $ErrMsg);
		}

		if(!$Result) {
			prnMsg(__('Errors were encountered adding this tax province'),'error');
		} else {
			prnMsg(__('New tax province added'),'success');
		}
	}
	unset ($SelectedTaxProvince);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax province the ID is just a secure way to find the tax province
	$SQL = "SELECT taxprovincename FROM taxprovinces
		WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
	$Result = DB_query($SQL);
	if( DB_num_rows($Result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( __('Cannot delete this tax province because it no longer exists'),'warn');
	} else {
		$MyRow = DB_fetch_row($Result);
		$OldTaxProvinceName = $MyRow[0];
		$SQL= "SELECT COUNT(*) FROM locations WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if($MyRow[0]>0) {
			prnMsg( __('Cannot delete this tax province because at least one stock location is defined to be inside this province'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('stock locations that refer to this tax province') . '</font>';
		} else {
			$SQL = "DELETE FROM taxauthrates WHERE dispatchtaxprovince = '" . $SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			$SQL = "DELETE FROM taxprovinces WHERE taxprovinceid = '" .$SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			prnMsg( $OldTaxProvinceName . ' ' . __('tax province and any tax rates set for it have been deleted'),'success');
		}
	} //end if
	unset ($SelectedTaxProvince);
	unset ($_GET['SelectedTaxProvince']);
	unset($_GET['delete']);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);
}

if(!isset($SelectedTaxProvince)) {

/* An tax province could be posted when one has been edited and is being updated
or GOT when selected for modification
SelectedTaxProvince will exist because it was sent with the page in a GET .
If its the first time the page has been displayed with no parameters
then none of the above are true and the list of account groups will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT taxprovinceid,
			taxprovincename
			FROM taxprovinces
			ORDER BY taxprovinceid";

	$ErrMsg = __('Could not get tax categories because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Tax Province') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while($MyRow = DB_fetch_row($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow[1] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxProvince=' . $MyRow[0] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxProvince=' . $MyRow[0] . '&amp;delete=1">' . __('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody></table>';
} //end of ifs and buts!


if(isset($SelectedTaxProvince)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Tax Provinces') . '</a>
		</div>';
}

if(! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedTaxProvince)) {
		//editing an existing section

		$SQL = "SELECT taxprovinceid,
				taxprovincename
				FROM taxprovinces
				WHERE taxprovinceid='" . $SelectedTaxProvince . "'";

		$Result = DB_query($SQL);
		if( DB_num_rows($Result) == 0 ) {
			prnMsg( __('Could not retrieve the requested tax province, please try again.'),'warn');
			unset($SelectedTaxProvince);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['TaxProvinceName']  = $MyRow['taxprovincename'];

			echo '<input type="hidden" name="SelectedTaxProvince" value="' . $MyRow['taxprovinceid'] . '" />';
			echo '<fieldset>
					<legend>', __('Edit Tax Province'), '</legend>';
		}

	}  else {
		$_POST['TaxProvinceName']='';
		echo '<fieldset>
					<legend>', __('Create Tax Province'), '</legend>';
	}
	echo '<field>
			<td>' . __('Tax Province Name') . ':' . '</td>
			<td><input type="text" pattern="(?!^ *$)[^\\><+-]+" required="true" title="'.__('The tax province cannot be left blank and includes illegal characters').'" placeholder="'.__('Within 30 legal characters').'" name="TaxProvinceName" size="30" maxlength="30" value="' . $_POST['TaxProvinceName'] . '" /></td>
		</field>
		</fieldset>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . __('Enter Information') . '" />
			</div>';

	echo '</div>
</form>';

} //end if record deleted no point displaying form to add record

echo '<br />
	<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . __('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . __('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . __('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
