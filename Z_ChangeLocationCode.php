<?php

/* Utility to change a location code. */

require(__DIR__ . '/includes/session.php');

$Title = __('UTILITY PAGE Change A Location Code');
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_ChangeLocationCode';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	__('Change A Location Code'), '" /> ',// Icon title.
	__('Change A Location Code'), '</p>';// Page title.

if(isset($_POST['ProcessLocationChange'])) {

	$InputError =0;

	$_POST['NewLocationID'] = mb_strtoupper($_POST['NewLocationID']);

/*First check the location code exists */
	$Result = DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'");
	if(DB_num_rows($Result)==0) {
		prnMsg(__('The location code') . ': ' . $_POST['OldLocationID'] . ' ' . __('does not currently exist as a location code in the system'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewLocationID'])) {
		prnMsg(__('The new location code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewLocationID']=='') {
		prnMsg(__('The new location code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewLocationName'])) {
		prnMsg(__('The new location name to change the old name to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewLocationName']=='') {
		prnMsg(__('The new location name to change the old name to must be entered as well'),'error');
		$InputError =1;
	}
/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['NewLocationID'] . "'");
	if(DB_num_rows($Result)!=0) {
		echo '<br /><br />';
		prnMsg(__('The replacement location code') . ': ' . $_POST['NewLocationID'] . ' ' . __('already exists as a location code in the system') . ' - ' . __('a unique location code must be entered for the new code'),'error');
		$InputError =1;
	}

	if($InputError ==0) {// no input errors
		DB_Txn_Begin();
		DB_IgnoreForeignKeys();

		echo '<br />' . __('Adding the new location record');
		$SQL = "INSERT INTO locations (loccode,
										locationname,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										tel,
										fax,
										email,
										contact,
										taxprovinceid,
										managed,
										cashsalecustomer,
										cashsalebranch,
										internalrequest,
										usedforwo,
										glaccountcode,
										allowinvoicing
										)
				SELECT '" . $_POST['NewLocationID'] . "',
					    '" . $_POST['NewLocationName'] . "',
						deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						fax,
						email,
						contact,
						taxprovinceid,
						managed,
						cashsalecustomer,
						cashsalebranch,
						internalrequest,
						usedforwo,
						glaccountcode,
						allowinvoicing
				FROM locations
				WHERE loccode='" . $_POST['OldLocationID'] . "'";

		$ErrMsg =__('The SQL to insert the new location record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the BOM table records');
		$SQL = "UPDATE bom SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the BOM records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the config table records');
		$SQL = "UPDATE config SET confvalue='" . $_POST['NewLocationID'] . "' WHERE confvalue='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the BOM records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the contracts table records');
		$SQL = "UPDATE contracts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the contracts records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the custbranch table records');
		$SQL = "UPDATE custbranch SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the custbranch records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the freightcosts table records');
		$SQL = "UPDATE freightcosts SET locationfrom='" . $_POST['NewLocationID'] . "' WHERE locationfrom='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the freightcosts records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing the locationusers table records');
		$SQL = "UPDATE locationusers SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update users records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stock location records');
		$SQL = "UPDATE locstock SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stock location records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing location transfer information (Shipping location)');
		$SQL = "UPDATE loctransfers SET shiploc='" . $_POST['NewLocationID'] . "' WHERE shiploc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the loctransfers records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing location transfer information (Receiving location)');
		$SQL = "UPDATE loctransfers SET recloc='" . $_POST['NewLocationID'] . "' WHERE recloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the loctransfers records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		//check if MRP tables exist before assuming

		$Result = DB_query("SELECT COUNT(*) FROM mrpparameters",'','',false,false);
		if(DB_error_no()==0) {
			echo '<br />' . __('Changing MRP parameters information');
			$SQL = "UPDATE mrpparameters SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
			$ErrMsg = __('The SQL to update the mrpparameters records failed');
			$Result = DB_query($SQL, $ErrMsg, '', true);
			echo ' ... ' . __('completed');
		}

		echo '<br />' . __('Changing purchase orders information');
		$SQL = "UPDATE purchorders SET intostocklocation='" . $_POST['NewLocationID'] . "' WHERE intostocklocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the purchase orders records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing recurring sales orders information');
		$SQL = "UPDATE recurringsalesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the recurring sales orders records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing  sales orders information');
		$SQL = "UPDATE salesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update the  sales orders records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stock check freeze records');
		$SQL = "UPDATE stockcheckfreeze SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stock check freeze records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stockcounts records');
		$SQL = "UPDATE stockcounts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stockcounts records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stockmoves records');
		$SQL = "UPDATE stockmoves SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stockmoves records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stockrequest records');
		$SQL = "UPDATE stockrequest SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stockrequest records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing stockserialitems records');
		$SQL = "UPDATE stockserialitems SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update stockserialitems records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing tenders records');
		$SQL = "UPDATE tenders SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update tenders records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing workcentres records');
		$SQL = "UPDATE workcentres SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update workcentres records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing workorders records');
		$SQL = "UPDATE workorders SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update workorders records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		echo '<br />' . __('Changing users records');
		$SQL = "UPDATE www_users SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to update users records failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');

		DB_ReinstateForeignKeys();

		DB_Txn_Commit();

		echo '<br />' . __('Deleting the old location record');
		$SQL = "DELETE FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = __('The SQL to delete the old location record failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		echo ' ... ' . __('completed');


		echo '<p>' . __('Location code') . ': ' . $_POST['OldLocationID'] . ' ' . __('was successfully changed to') . ' : ' . $_POST['NewLocationID'];
	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', __('Location Code To Change'), '</legend>
	<field>
		<label>' . __('Existing Location Code') . ':</label>
		<input type="text" name="OldLocationID" size="5" maxlength="5" />
	</field>
	<field>
		<label>' . __('New Location Code') . ':</label>
		<input type="text" name="NewLocationID" size="5" maxlength="5" />
	</field>
	<field>
		<label>' . __('New Location Name') . ':</label>
		<input type="text" name="NewLocationName" size="50" maxlength="50" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessLocationChange" value="' . __('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
