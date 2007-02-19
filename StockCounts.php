<?php
/* $Revision: 1.9 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Check Sheets Entry');

include('includes/header.inc');

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo "<BR>";

if (isset($_POST['Action'])) { 
	$_GET['Action'] = $_POST['Action']; 
}

if ($_GET['Action'] != 'View' && $_GET['Action'] != 'Enter'){ 
	$_GET['Action'] = 'Enter'; 
}

if ($_GET['Action']=='View'){
	echo '<a href="' . $rootpath . '/StockCounts.php?' . PID . '&Action=Enter">' . _('Resuming Entering Counts') . '</a> <b>|</b>' . _('Viewing Entered Counts') . '<BR><BR>';
} else {
	echo _('Entering Counts') .'<b>|</b> <a href="' . $rootpath . '/StockCounts.php?' . PID . '&Action=View">' . _('View Entered Counts') . '</a><BR><BR>';
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

	echo _('Stock Check Counts at Location') . ":<SELECT NAME='Location'>";
	$sql = 'SELECT loccode, locationname FROM locations';
	$result = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($result)){

		if ($myrow['loccode']==$_POST['Location']){
			echo "<OPTION SELECTED VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
			echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	}
	echo '</SELECT><BR><BR>';

	echo '<TABLE CELLPADDING=2 BORDER=1>';
	echo "<TR>
		<TD class='tableheader'>" . _('Stock Code') . "</TD>
		<TD class='tableheader'>" . _('Quantity') . "</TD>
		<TD class='tableheader'>" . _('Reference') . '</TD></TR>';

	for ($i=1;$i<=10;$i++){

		echo "<TR>
			<TD><INPUT TYPE=TEXT NAME='StockID_" . $i . "' MAXLENGTH=20 SIZE=20></TD>
			<TD><INPUT TYPE=TEXT NAME='Qty_" . $i . "' MAXLENGTH=10 SIZE=10></TD>
			<TD><INPUT TYPE=TEXT NAME='Ref_" . $i . "' MAXLENGTH=20 SIZE=20></TD></TR>";

	}

	echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='EnterCounts' VALUE='" . _('Enter Above Counts') . "'>";

//END OF ACTION=ENTER
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
	
	//START OF ACTION=VIEW
	$SQL = "select * from stockcounts";
	$result = DB_query($SQL, $db,$ErrMsg);
	echo '<INPUT TYPE=HIDDEN NAME=Action Value="View">';
	echo '<TABLE CELLPADDING=2 BORDER=1>';
	echo "<TR>
		<TD class='tableheader'>" . _('Stock Code') . "</TD>
		<TD class='tableheader'>" . _('Location') . "</TD>
		<TD class='tableheader'>" . _('Qty Counted') . "</TD>
		<TD class='tableheader'>" . _('Reference') . "</TD>
		<TD class='tableheader'>" . _('Delete?') . '</TD></TR>';
	while ($myrow=DB_fetch_array($result)){
		echo "<TR>
			<TD>".$myrow['stockid']."</TD>
			<TD>".$myrow['loccode']."</TD>
			<TD>".$myrow['qtycounted']."</TD>
			<TD>".$myrow['reference']."</TD>
			<TD><INPUT TYPE=CHECKBOX NAME='DEL[" .$myrow['id']."]' MAXLENGTH=20 SIZE=20></TD></TR>";

	}
	echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='SubmitChanges' VALUE='" . _('Save Changes') . "'>";

//END OF ACTION=VIEW
}

echo '</FORM>';
include('includes/footer.inc');

?>