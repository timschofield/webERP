<?php
/* $Revision: 1.6 $ */

$PageSecurity = 15;

include('includes/session.inc');

$title = _('Dispatch Tax Provinces');

include('includes/header.inc');

if ( isset($_GET['SelectedTaxProvince']) )
	$SelectedTaxProvince = $_GET['SelectedTaxProvince'];
elseif (isset($_POST['SelectedTaxProvince']))
	$SelectedTaxProvince = $_POST['SelectedTaxProvince'];

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['TaxProvinceName'],'&')>0 OR strpos($_POST['TaxProvinceName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('The tax province name cannot contain the character') . " '&' " . _('or the character') ." '",'error');
	}
	if (trim($_POST['TaxProvinceName']) == '') {
		$InputError = 1;
		prnMsg( _('The tax province name may not be empty'), 'error');
	}

	if ($_POST['SelectedTaxProvince']!='' AND $InputError !=1) {

		/*SelectedTaxProvince could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM taxprovinces
				WHERE taxprovinceid <> " . $SelectedTaxProvince ."
				AND taxprovincename " . LIKE . " '" . $_POST['TaxProvinceName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The tax province cannot be renamed because another with the same name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exists
			$sql = "SELECT taxprovincename FROM taxprovinces 
						WHERE taxprovinceid = " . $SelectedTaxProvince;
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldTaxProvinceName = $myrow[0];
				$sql = "UPDATE taxprovinces
					SET taxprovincename='" . $_POST['TaxProvinceName'] . "'
					WHERE taxprovincename ".LIKE." '".$OldTaxProvinceName."'";
				$ErrMsg = _('Could not update tax province');
				$result = DB_query($sql,$db, $ErrMsg);
				if (!$result){
					prnMsg(_('Tax province name changed'),'success');
				}
			} else {
				$InputError = 1;
				prnMsg( _('The tax province no longer exists'),'error');
			}
		}
	} elseif ($InputError !=1) {
		/*SelectedTaxProvince is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM taxprovinces 
				WHERE taxprovincename " .LIKE. " '".$_POST['TaxProvinceName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The tax province cannot be created because another with the same name already exists'),'error');
		} else {
			$sql = "INSERT INTO taxprovinces (
							taxprovincename )
				VALUES (
					'" . $_POST['TaxProvinceName'] ."'
					)";
			$ErrMsg = _('Could not add tax province');
			$result = DB_query($sql,$db, $ErrMsg);
			
			$TaxProvinceID = DB_Last_Insert_ID($db, 'taxprovinces', 'taxprovinceid');
			$sql = 'INSERT INTO taxauthrates (taxauthority, dispatchtaxprovince, taxcatid)
					SELECT taxauthorities.taxid, ' . $TaxProvinceID . ', taxcategories.taxcatid
						FROM taxauthorities CROSS JOIN taxcategories';
			$ErrMsg = _('Could not add tax authority rates for the new dispatch tax province. The rates of tax will not be able to be added - manual database interaction will be required to use this dispatch tax province');
			$result = DB_query($sql,$db, $ErrMsg);
		}
		
		if (!$result){
			prnMsg(_('Errors were encountered adding this tax province'),'error');
		} else {
			prnMsg(_('New tax province added'),'success');
		}
	}
	unset ($SelectedTaxProvince);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax province the ID is just a secure way to find the tax province
	$sql = "SELECT taxprovincename FROM taxprovinces 
		WHERE taxprovinceid = " . $SelectedTaxProvince;
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('Cannot delete this tax province because it no longer exists'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldTaxProvinceName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM locations WHERE taxprovinceid " . LIKE . " '" . $OldTaxProvinceName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this tax province because at least one stock location is defined to be inside this province'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('stock locations that refer to this tax province') . '</font>';
		} else {
			$sql = 'DELETE FROM taxauthrates WHERE dispatchtaxprovince = ' . $SelectedTaxProvince;
			$result = DB_query($sql,$db);
			$sql = 'DELETE FROM taxprovinces WHERE taxprovinceid = ' .$SelectedTaxProvince;;
			$result = DB_query($sql,$db);
			prnMsg( $OldTaxProvinceName . ' ' . _('tax province and any tax rates set for it have been deleted'),'success');
		}
	} //end if 
	unset ($SelectedTaxProvince);
	unset ($_GET['SelectedTaxProvince']);
	unset($_GET['delete']);
	unset ($_POST['SelectedTaxProvince']);
	unset ($_POST['TaxProvinceName']);
}

 if (!isset($SelectedTaxProvince)) {

/* An tax province could be posted when one has been edited and is being updated 
  or GOT when selected for modification
  SelectedTaxProvince will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT taxprovinceid,
			taxprovincename
			FROM taxprovinces
			ORDER BY taxprovinceid";

	$ErrMsg = _('Could not get tax categories because');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<table>
		<tr>
		<th>" . _('Tax Provinces') . "</th>
		</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTaxProvince=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTaxProvince=' . $myrow[0] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedTaxProvince)) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Review Tax Provinces') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedTaxProvince)) {
		//editing an existing section

		$sql = "SELECT taxprovinceid,
				taxprovincename
				FROM taxprovinces
				WHERE taxprovinceid=" . $SelectedTaxProvince;

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested tax province, please try again.'),'warn');
			unset($SelectedTaxProvince);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['TaxProvinceName']  = $myrow['taxprovincename'];

			echo "<input type=hidden name='SelectedTaxProvince' VALUE='" . $myrow['taxprovinceid'] . "'>";
			echo "<table>";
		}

	}  else {
		$_POST['TaxProvinceName']='';
		echo "<table>";
	}
	echo "<tr>
		<td>" . _('Tax Province Name') . ':' . "</td>
		<td><input type='Text' name='TaxProvinceName' size=30 maxlength=30 value='" . $_POST['TaxProvinceName'] . "'></td>
		</tr>";
	echo '</table>';

	echo '<div class="centre"><input type=Submit name=submit value=' . _('Enter Information') . '></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

echo '<div class="centre"';
echo '<br><a href="' . $rootpath . '/TaxAuthorities.php?' . SID . '">' . _('Edit/Review Tax Authorities') .  '</a>';
echo '<br><a href="' . $rootpath . '/TaxGroups.php?' . SID . '">' . _('Edit/Review Tax Groupings') .  '</a>';
echo '<br><a href="' . $rootpath . '/TaxCategories.php?' . SID . '">' . _('Edit/Review Tax Categories') .  '</a>';
echo '</div>';

include('includes/footer.inc');
?>