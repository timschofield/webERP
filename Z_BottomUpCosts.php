<?php

/* Script to update costs for all BOM items, from the bottom up */

require(__DIR__ . '/includes/session.php');

$Title = __('Recalculate BOM costs');
$ViewTopic = 'SpecialUtilities'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_BottomUpCosts'; // Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['Run'])){
	$Run = $_GET['Run'];
} elseif (isset($_POST['Run'])){
	$Run = $_POST['Run'];
}

if (isset($Run)) { //start bom processing

	// Get all bottom level components
	$SQL = "SELECT DISTINCT b1.component
			FROM bom as b1
			left join bom as b2 on b2.parent=b1.component
			WHERE b2.parent is null;" ;

	$ErrMsg =  __('An error occurred selecting all bottom level components');

	$Result = DB_query($SQL, $ErrMsg);

	while ($Item = DB_fetch_array($Result)) {
		$InputError=UpdateCost($Item['component']);
		if ($InputError==0) {
			prnMsg( __('Component') .' ' . $Item['component']  . ' '. __('has been processed'),'success');
		} else {
			break;
		}
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on item') . ' ' . $Item['component']. ' ' . __('Cost update has been rolled back'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( __('All cost updates committed to the database.'),'success');
	}

} else {

	echo '<br />
		<br />';
	prnMsg(__('This script will not update the General Ledger stock balances for the changed costs. If you use integrated stock then do not use this utility'),'warn');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Update costs for all items listed in a bill of materials') . '<br />
		</p>
		<div class="centre">
			<input type="submit" name="Run" value="' . __('Run') . '" />
		</div>
        </div>
		</form>';
}

include('includes/footer.php');
