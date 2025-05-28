<?php
/* Utility to change a location code. */

include ('includes/session.php');
$Title = _('UTILITY PAGE Change A Location Code');// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeLocationCode';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	_('Change A Location Code'), '" /> ',// Icon title.
	_('Change A Location Code'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');

if(isset($_POST['ProcessLocationChange'])) {

	$InputError =0;

	$_POST['NewLocationID'] = mb_strtoupper($_POST['NewLocationID']);

/*First check the location code exists */
	$Result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'");
	if(DB_num_rows($Result)==0) {
		prnMsg(_('The location code') . ': ' . $_POST['OldLocationID'] . ' ' . _('does not currently exist as a location code in the system'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewLocationID'])) {
		prnMsg(_('The new location code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewLocationID']=='') {
		prnMsg(_('The new location code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewLocationName'])) {
		prnMsg(_('The new location name to change the old name to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewLocationName']=='') {
		prnMsg(_('The new location name to change the old name to must be entered as well'),'error');
		$InputError =1;
	}
/*Now check that the new code doesn't already exist */
	$Result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['NewLocationID'] . "'");
	if(DB_num_rows($Result)!=0) {
		echo '<br /><br />';
		prnMsg(_('The replacement location code') . ': ' . $_POST['NewLocationID'] . ' ' . _('already exists as a location code in the system') . ' - ' . _('a unique location code must be entered for the new code'),'error');
		$InputError =1;
	}

	if($InputError ==0) {// no input errors
		DB_Txn_Begin();
		DB_IgnoreForeignKeys();

		echo '<br />' . _('Adding the new location record');
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

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the new location record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the BOM table records');
		$SQL = "UPDATE bom SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the BOM records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the config table records');
		$SQL = "UPDATE config SET confvalue='" . $_POST['NewLocationID'] . "' WHERE confvalue='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the BOM records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the contracts table records');
		$SQL = "UPDATE contracts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the contracts records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the custbranch table records');
		$SQL = "UPDATE custbranch SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the custbranch records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the freightcosts table records');
		$SQL = "UPDATE freightcosts SET locationfrom='" . $_POST['NewLocationID'] . "' WHERE locationfrom='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the freightcosts records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the locationusers table records');
		$SQL = "UPDATE locationusers SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update users records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stock location records');
		$SQL = "UPDATE locstock SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stock location records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing location transfer information (Shipping location)');
		$SQL = "UPDATE loctransfers SET shiploc='" . $_POST['NewLocationID'] . "' WHERE shiploc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the loctransfers records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing location transfer information (Receiving location)');
		$SQL = "UPDATE loctransfers SET recloc='" . $_POST['NewLocationID'] . "' WHERE recloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the loctransfers records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		//check if MRP tables exist before assuming

		$Result = DB_query("SELECT COUNT(*) FROM mrpparameters",'','',false,false);
		if(DB_error_no()==0) {
			echo '<br />' . _('Changing MRP parameters information');
			$SQL = "UPDATE mrpparameters SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
			$ErrMsg = _('The SQL to update the mrpparameters records failed');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			echo ' ... ' . _('completed');
		}

		echo '<br />' . _('Changing purchase orders information');
		$SQL = "UPDATE purchorders SET intostocklocation='" . $_POST['NewLocationID'] . "' WHERE intostocklocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the purchase orders records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing recurring sales orders information');
		$SQL = "UPDATE recurringsalesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the recurring sales orders records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing  sales orders information');
		$SQL = "UPDATE salesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the  sales orders records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stock check freeze records');
		$SQL = "UPDATE stockcheckfreeze SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stock check freeze records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockcounts records');
		$SQL = "UPDATE stockcounts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockcounts records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockmoves records');
		$SQL = "UPDATE stockmoves SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockmoves records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockrequest records');
		$SQL = "UPDATE stockrequest SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockrequest records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockserialitems records');
		$SQL = "UPDATE stockserialitems SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockserialitems records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing tenders records');
		$SQL = "UPDATE tenders SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update tenders records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing workcentres records');
		$SQL = "UPDATE workcentres SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update workcentres records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing workorders records');
		$SQL = "UPDATE workorders SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update workorders records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing users records');
		$SQL = "UPDATE www_users SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update users records failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		DB_ReinstateForeignKeys();

		DB_Txn_Commit();

		echo '<br />' . _('Deleting the old location record');
		$SQL = "DELETE FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to delete the old location record failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');


		echo '<p>' . _('Location code') . ': ' . $_POST['OldLocationID'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewLocationID'];
	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>', _('Location Code To Change'), '</legend>
	<field>
		<label>' . _('Existing Location Code') . ':</label>
		<input type="text" name="OldLocationID" size="5" maxlength="5" />
	</field>
	<field>
		<label>' . _('New Location Code') . ':</label>
		<input type="text" name="NewLocationID" size="5" maxlength="5" />
	</field>
	<field>
		<label>' . _('New Location Name') . ':</label>
		<input type="text" name="NewLocationName" size="50" maxlength="50" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ProcessLocationChange" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>