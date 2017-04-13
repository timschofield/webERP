<?php
/* $Id: Z_ChangeLocationCode.php 5296 2012-04-29 15:28:19Z vvs2012 $*/
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

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['ProcessLocationChange'])) {

	$InputError =0;

	$_POST['NewLocationID'] = mb_strtoupper($_POST['NewLocationID']);

/*First check the location code exists */
	$result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'");
	if(DB_num_rows($result)==0) {
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
	$result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['NewLocationID'] . "'");
	if(DB_num_rows($result)!=0) {
		echo '<br /><br />';
		prnMsg(_('The replacement location code') . ': ' . $_POST['NewLocationID'] . ' ' . _('already exists as a location code in the system') . ' - ' . _('a unique location code must be entered for the new code'),'error');
		$InputError =1;
	}

	if($InputError ==0) {// no input errors
		$result = DB_Txn_Begin();
		DB_IgnoreForeignKeys();

		echo '<br />' . _('Adding the new location record');
		$sql = "INSERT INTO locations (loccode,
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
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the BOM table records');
		$sql = "UPDATE bom SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the BOM records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the config table records');
		$sql = "UPDATE config SET confvalue='" . $_POST['NewLocationID'] . "' WHERE confvalue='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the BOM records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the contracts table records');
		$sql = "UPDATE contracts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the contracts records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the custbranch table records');
		$sql = "UPDATE custbranch SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the custbranch records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing the freightcosts table records');
		$sql = "UPDATE freightcosts SET locationfrom='" . $_POST['NewLocationID'] . "' WHERE locationfrom='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the freightcosts records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stock location records');
		$sql = "UPDATE locstock SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stock location records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing location transfer information (Shipping location)');
		$sql = "UPDATE loctransfers SET shiploc='" . $_POST['NewLocationID'] . "' WHERE shiploc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the loctransfers records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing location transfer information (Receiving location)');
		$sql = "UPDATE loctransfers SET recloc='" . $_POST['NewLocationID'] . "' WHERE recloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the loctransfers records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		//check if MRP tables exist before assuming

		$result = DB_query("SELECT COUNT(*) FROM mrpparameters",'','',false,false);
		if(DB_error_no()==0) {
			echo '<br />' . _('Changing MRP parameters information');
			$sql = "UPDATE mrpparameters SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
			$ErrMsg = _('The SQL to update the mrpparameters records failed');
			$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
			echo ' ... ' . _('completed');
		}

		echo '<br />' . _('Changing purchase orders information');
		$sql = "UPDATE purchorders SET intostocklocation='" . $_POST['NewLocationID'] . "' WHERE intostocklocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the purchase orders records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing recurring sales orders information');
		$sql = "UPDATE recurringsalesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the recurring sales orders records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing  sales orders information');
		$sql = "UPDATE salesorders SET fromstkloc='" . $_POST['NewLocationID'] . "' WHERE fromstkloc='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update the  sales orders records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stock check freeze records');
		$sql = "UPDATE stockcheckfreeze SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stock check freeze records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockcounts records');
		$sql = "UPDATE stockcounts SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockcounts records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockmoves records');
		$sql = "UPDATE stockmoves SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockmoves records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockrequest records');
		$sql = "UPDATE stockrequest SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockrequest records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing stockserialitems records');
		$sql = "UPDATE stockserialitems SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update stockserialitems records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing tenders records');
		$sql = "UPDATE tenders SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update tenders records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing workcentres records');
		$sql = "UPDATE workcentres SET location='" . $_POST['NewLocationID'] . "' WHERE location='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update workcentres records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing workorders records');
		$sql = "UPDATE workorders SET loccode='" . $_POST['NewLocationID'] . "' WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update workorders records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<br />' . _('Changing users records');
		$sql = "UPDATE www_users SET defaultlocation='" . $_POST['NewLocationID'] . "' WHERE defaultlocation='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to update users records failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		DB_ReinstateForeignKeys();

		$result = DB_Txn_Commit();

		echo '<br />' . _('Deleting the old location record');
		$sql = "DELETE FROM locations WHERE loccode='" . $_POST['OldLocationID'] . "'";
		$ErrMsg = _('The SQL to delete the old location record failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');


		echo '<p>' . _('Location code') . ': ' . $_POST['OldLocationID'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewLocationID'];
	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>' . _('Existing Location Code') . ':</td>
		<td><input type="text" name="OldLocationID" size="5" maxlength="5" /></td>
	</tr>
	<tr>
		<td>' . _('New Location Code') . ':</td>
		<td><input type="text" name="NewLocationID" size="5" maxlength="5" /></td>
	</tr>
	<tr>
		<td>' . _('New Location Name') . ':</td>
		<td><input type="text" name="NewLocationName" size="50" maxlength="50" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessLocationChange" value="' . _('Process') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>