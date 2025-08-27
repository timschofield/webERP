<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Tax Categories');
$ViewTopic = 'Tax';
$BookMark = 'TaxCategories';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Tax Category Maintenance') . '" />' . ' ' .
		__('Tax Category Maintenance') . '</p>';

if( isset($_GET['SelectedTaxCategory']) )
	$SelectedTaxCategory = $_GET['SelectedTaxCategory'];
elseif(isset($_POST['SelectedTaxCategory']))
	$SelectedTaxCategory = $_POST['SelectedTaxCategory'];

if(isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if(ContainsIllegalCharacters($_POST['TaxCategoryName'])) {
		$InputError = 1;
		prnMsg( __('The tax category name cannot contain the character') . " '&amp;' " . __('or the character') ." ' " . __('or a space') ,'error');
	}
	if(trim($_POST['TaxCategoryName']) == '') {
		$InputError = 1;
		prnMsg( __('The tax category name may not be empty'), 'error');
	}

	if($_POST['SelectedTaxCategory']!='' AND $InputError !=1) {

		/*SelectedTaxCategory could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$SQL = "SELECT count(*) FROM taxcategories
				WHERE taxcatid <> '" . $SelectedTaxCategory ."'
				AND taxcatname ".LIKE." '" . $_POST['TaxCategoryName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The tax category cannot be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists

			$SQL = "SELECT taxcatname FROM taxcategories
					WHERE taxcatid = '" . $SelectedTaxCategory . "'";
			$Result = DB_query($SQL);
			if( DB_num_rows($Result) != 0 ) {
				// This is probably the safest way there is
				$MyRow = DB_fetch_row($Result);
				$OldTaxCategoryName = $MyRow[0];
				$SQL = "UPDATE taxcategories
						SET taxcatname='" . $_POST['TaxCategoryName'] . "'
						WHERE taxcatname ".LIKE." '".$OldTaxCategoryName."'";
				$ErrMsg = __('The tax category could not be updated');
				$Result = DB_query($SQL, $ErrMsg);
			} else {
				$InputError = 1;
				prnMsg( __('The tax category no longer exists'),'error');
			}
		}
		$Msg = __('Tax category name changed');
	} elseif($InputError !=1) {
		/*SelectedTaxCategory is null cos no item selected on first time round so must be adding a record*/
		$SQL = "SELECT count(*) FROM taxcategories
				WHERE taxcatname " .LIKE. " '".$_POST['TaxCategoryName'] ."'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if( $MyRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The tax category cannot be created because another with the same name already exists'),'error');
		} else {
			DB_Txn_Begin();
			$SQL = "INSERT INTO taxcategories (
						taxcatname )
					VALUES (
						'" . $_POST['TaxCategoryName'] ."'
						)";
			$ErrMsg = __('The new tax category could not be added');
			$Result = DB_query($SQL, $ErrMsg,'', true);

			$LastTaxCatID = DB_Last_Insert_ID('taxcategories','taxcatid');

			$SQL = "INSERT INTO taxauthrates (taxauthority,
					dispatchtaxprovince,
					taxcatid)
				SELECT taxauthorities.taxid,
 					taxprovinces.taxprovinceid,
					'" . $LastTaxCatID . "'
				FROM taxauthorities CROSS JOIN taxprovinces";
			$Result = DB_query($SQL, $ErrMsg,'', true);

			DB_Txn_Commit();
		}
		$Msg = __('New tax category added');
	}

	if($InputError!=1) {
		prnMsg($Msg,'success');
	}
	unset ($SelectedTaxCategory);
	unset ($_POST['SelectedTaxCategory']);
	unset ($_POST['TaxCategoryName']);

} elseif(isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax category the ID is just a secure way to find the tax category
	$SQL = "SELECT taxcatname FROM taxcategories
		WHERE taxcatid = '" . $SelectedTaxCategory . "'";
	$Result = DB_query($SQL);
	if( DB_num_rows($Result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( __('Cannot delete this tax category because it no longer exists'),'warn');
	} else {
		$MyRow = DB_fetch_array($Result);
		$TaxCatName = $MyRow['taxcatname'];
		$SQL= "SELECT COUNT(*) FROM stockmaster WHERE taxcatid = '" . $SelectedTaxCategory . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if($MyRow[0]>0) {
			prnMsg( __('Cannot delete this tax category because inventory items have been created using this tax category'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('inventory items that refer to this tax category') . '</font>';
		} else {
			$SQL = "DELETE FROM taxauthrates WHERE taxcatid  = '" . $SelectedTaxCategory . "'";
			$Result = DB_query($SQL);
			$SQL = "DELETE FROM taxcategories WHERE taxcatid = '" . $SelectedTaxCategory . "'";
			$Result = DB_query($SQL);
			prnMsg( $TaxCatName . ' ' . __('tax category and any tax rates set for it have been deleted'),'success');
		}
	} //end if
	unset ($SelectedTaxCategory);
	unset ($_GET['SelectedTaxCategory']);
	unset($_GET['delete']);
	unset ($_POST['SelectedTaxCategory']);
	unset ($_POST['TaxCategoryName']);
}

 if(!isset($SelectedTaxCategory)) {

/* An tax category could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedTaxCategory will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$SQL = "SELECT taxcatid,
			taxcatname
			FROM taxcategories
			ORDER BY taxcatid";

	$ErrMsg = __('Could not get tax categories because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Tax Category') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while($MyRow = DB_fetch_row($Result)) {
		echo '<tr class="striped_row">';

		if($MyRow[1]!='Freight') {
			// Uses gettext() to translate 'Exempt' and 'Handling':
			echo '<td>' . __($MyRow[1]) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxCategory=' . $MyRow[0] . '">' . __('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedTaxCategory=' . $MyRow[0] . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this tax category?') . '\');">' .
					__('Delete')  . '</a></td>';
		} else {
			echo '<td>' . __($MyRow[1]) . '</td><td>&nbsp;</td><td>&nbsp;</td>';// Uses gettext() to translate 'Freight'.
		}
		echo '</tr>';
	} //END WHILE LIST LOOP

	echo '</tbody></table>';
} //end of ifs and buts!


if(isset($SelectedTaxCategory)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Tax Categories') . '</a>
		</div>';
}

if(! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedTaxCategory)) {
		//editing an existing section

		$SQL = "SELECT taxcatid,
				taxcatname
				FROM taxcategories
				WHERE taxcatid='" . $SelectedTaxCategory . "'";

		$Result = DB_query($SQL);
		if( DB_num_rows($Result) == 0 ) {
			prnMsg( __('Could not retrieve the requested tax category, please try again.'),'warn');
			unset($SelectedTaxCategory);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['TaxCategoryName']  = $MyRow['taxcatname'];

			echo '<input type="hidden" name="SelectedTaxCategory" value="' . $MyRow['taxcatid'] . '" />';
			echo '<fieldset>
					<legend>', __('Edit Tax Category'), '</legend>';
		}

	}  else {
		$_POST['TaxCategoryName']='';
		echo '<fieldset>
				<legend>', __('Create Tax Category'), '</legend>';
	}
	echo '<field>
			<label for="TaxCategoryName">' . __('Tax Category Name') . ':' . '</label>
			<input pattern="(?!^ +$)[^><+-]+" required="required" placeholder="'.__('No more than 30 characters').'" type="text" title="" name="TaxCategoryName" size="30" maxlength="30" value="' . $_POST['TaxCategoryName'] . '" />
			<fieldhelp>'.__('No illegal characters allowed and cannot be blank').'</fieldhelp>
		</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . __('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . __('Tax Group Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . __('Dispatch Tax Province Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
