<?php
/* $Revision: 1.4 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Check Sheets Entry');

include('includes/header.inc');

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo "<CENTER>Stock Check Counts at Location:<SELECT NAME='Location'>";
$sql = 'SELECT loccode, locationname FROM locations';
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if ($myrow['loccode']==$_POST['Location']){
		echo "<OPTION SELECTED VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	} else {
		echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}

echo '</SELECT>';

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


if (isset($_POST['EnterCounts'])){

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
} // end of if enter counts button hit
echo '</FORM></CENTER>';
include('includes/footer.inc');

?>