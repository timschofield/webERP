<?php
/* $Id$*/
/* Script to update costs for all BOM items, from the bottom up */

//$PageSecurity = 15;
include('includes/session.inc');
$title = _('Recalculate BOM costs');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Run'])){
	$Run = $_GET['Run'];
} elseif (isset($_POST['Run'])){
	$Run = $_POST['Run'];
}

if (isset($Run)) { //start bom processing

	// Get all bottom level components
	$sql = "SELECT DISTINCT b1.component
			FROM bom as b1
			left join bom as b2 on b2.parent=b1.component
			WHERE b2.parent is null;" ;

	$ErrMsg =  _('An error occurred selecting all bottom level components');
	$DbgMsg =  _('The SQL that was used to select bottom level components and failed in the process was');

	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	while ($item = DB_fetch_array($result)) {
		$inputerror=UpdateCost($db, $item['component']);
		if ($inputerror==0) {
			prnMsg( _('Component') .' ' . $item['component']  . ' '. _('has been processed'),'success');
		} else {
			break;
		}
	}

	if ($inputerror == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on item '. $item['component']. '. Cost update has been rolled back.'),'error');
		DB_Txn_Rollback($db);
	} else { //all good so commit data transaction
		DB_Txn_Commit($db);
		prnMsg( _('All cost updates committed to the database.'),'success');
	}

} else { //show file upload form

	echo "<form action=" . $_SERVER['PHP_SELF'] . '?' . sid . " method=post name='form'>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Search') . '" alt="">' . ' ' . _('Update costs for all items listed in a bill of materials').'<br></p>';
	echo "<div class=centre><input type='submit' name='Run' value='" . _('Run') . "'></div></form>";

}

include('includes/footer.inc');
?>