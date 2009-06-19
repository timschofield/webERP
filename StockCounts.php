<?php
/* $Revision: 1.11 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Check Sheets Entry');

include('includes/header.inc');

echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";

echo "<br>";

if (!isset($_POST['Action']) and !isset($_GET['Action'])) {
	$_GET['Action'] = 'Enter';
}
if (isset($_POST['Action'])) { 
	$_GET['Action'] = $_POST['Action']; 
}

if ($_GET['Action']!='View' and $_GET['Action']!='Enter'){ 
	$_GET['Action'] = 'Enter'; 
}

if ($_GET['Action']=='View'){
	echo '<a href="' . $rootpath . '/StockCounts.php?' . SID . '&Action=Enter">' . _('Resuming Entering Counts') . '</a> <b>|</b>' . _('Viewing Entered Counts') . '<br><br>';
} else {
	echo _('Entering Counts') .'<b>|</b> <a href="' . $rootpath . '/StockCounts.php?' . SID . '&Action=View">' . _('View Entered Counts') . '</a><br><br>';
}

if ($_GET['Action'] == 'Enter'){

	if (isset($_POST['EnterCounts'])){

		$Added=0;
		for ($i=1;$i<=10;$i++){
			$InputError =False; //always assume the best to start with

			$Quantity = 'Qty_' . $i;
			$StockID = 'StockID_' . $i;
			$Reference = 'Ref_' . $i;

			if (strlen($_POST[$StockID])>0){
				if (!is_numeric($_POST[$Quantity])){
					prnMsg(_('The quantity entered for line') . ' ' . $i . ' ' . _('is not numeric') . ' - ' . _('this line was for the part code') . ' ' . $_POST[$StockID] . '. ' . _('This line will have to be re-entered'),'warn');
					$InputError=True;
				}
			$SQL = "SELECT stockid FROM stockcheckfreeze WHERE stockid='" . $_POST[$StockID] . "'";
				$result = DB_query($SQL,$db);
				if (DB_num_rows($result)==0){
					prnMsg( _('The stock code entered on line') . ' ' . $i . ' ' . _('is not a part code that has been added to the stock check file') . ' - ' . _('the code entered was') . ' ' . $_POST[$StockID] . '. ' . _('This line will have to be re-entered'),'warn');
					$InputError = True;
				}

				if ($InputError==False){
					$Added++;
					$sql = "INSERT INTO stockcounts (stockid,
									loccode,
									qtycounted,
									reference)
								VALUES ('" . $_POST[$StockID] . "',
									'" . $_POST['Location'] . "',
									" . $_POST[$Quantity] . ",
									'" . $_POST[$Reference] . "')";

					$ErrMsg = _('The stock count line number') . ' ' . $i . ' ' . _('could not be entered because');
					$EnterResult = DB_query($sql, $db,$ErrMsg);
				}
			}
		} // end of loop
		prnMsg($Added . _(' Stock Counts Entered'), 'success' );
		unset($_POST['EnterCounts']);
	} // end of if enter counts button hit

	echo _('Stock Check Counts at Location') . ":<select name='Location'>";
	$sql = 'SELECT loccode, locationname FROM locations';
	$result = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($result)){

		if (isset($_POST['Location']) and $myrow['loccode']==$_POST['Location']){
			echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
			echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	}
	echo '</select><br><br>';

	echo '<table cellpadding=2 BORDER=1>';
	echo "<tr>
		<th>" . _('Stock Code') . "</th>
		<th>" . _('Quantity') . "</th>
		<th>" . _('Reference') . '</th></tr>';

	for ($i=1;$i<=10;$i++){

		echo "<tr>
			<td><input type=TEXT name='StockID_" . $i . "' maxlength=20 size=20></td>
			<td><input type=TEXT name='Qty_" . $i . "' maxlength=10 size=10></td>
			<td><input type=TEXT name='Ref_" . $i . "' maxlength=20 size=20></td></tr>";

	}

	echo "</table><br><input type=submit name='EnterCounts' VALUE='" . _('Enter Above Counts') . "'>";

//END OF action=ENTER
} elseif ($_GET['Action']=='View'){

	if (isset($_POST['DEL']) && is_array($_POST['DEL']) ){
		foreach ($_POST['DEL'] as $id=>$val){
			if ($val == 'on'){
				$sql = "DELETE FROM stockcounts WHERE id=$id";
				$ErrMsg = _('Failed to delete StockCount ID #').' '.$i;
				$EnterResult = DB_query($sql, $db,$ErrMsg);
				prnMsg( _('Deleted Id #') . ' ' . $id, 'success');
			}
		}
	}
	
	//START OF action=VIEW
	$SQL = "select * from stockcounts";
	$result = DB_query($SQL, $db);
	echo '<input type=hidden name=Action Value="View">';
	echo '<table cellpadding=2 BORDER=1>';
	echo "<tr>
		<th>" . _('Stock Code') . "</th>
		<th>" . _('Location') . "</th>
		<th>" . _('Qty Counted') . "</th>
		<th>" . _('Reference') . "</th>
		<th>" . _('Delete?') . '</th></tr>';
	while ($myrow=DB_fetch_array($result)){
		echo "<tr>
			<td>".$myrow['stockid']."</td>
			<td>".$myrow['loccode']."</td>
			<td>".$myrow['qtycounted']."</td>
			<td>".$myrow['reference']."</td>
			<td><input type=CHECKBOX name='DEL[" .$myrow['id']."]' maxlength=20 size=20></td></tr>";

	}
	echo "</table><br><input type=submit name='SubmitChanges' VALUE='" . _('Save Changes') . "'>";

//END OF action=VIEW
}

echo '</form>';
include('includes/footer.inc');

?>